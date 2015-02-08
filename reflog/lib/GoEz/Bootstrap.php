<?php
/**
 * GoEz
 *
 * @package    GoEz
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 * @version    $Id$
 */

/**
 * Bootstrap 類別
 *
 * @package    GoEz
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 */
class GoEz_Bootstrap
{
    protected static $_debug = false;

    /**
     * 設定
     *
     * 用來存放應用程式的設定
     *
     * @var array
     */
    protected $_config = array();

    /**
     * Router
     *
     * 存放解析網址後的 Controller 與 Action
     *
     * @var GoEz_Router
     */
    protected $_router = null;

    /**
     * Request
     *
     * 用來處理 $_POST 與 $_GET
     *
     * @var GoEz_Request
     */
    protected $_request = null;

    /**
     * View
     *
     * 用來輸出 Template 或是 JSON
     *
     * @var GoEz_View
     */
    protected $_view = null;

    /**
     * 執行動作
     *
     * 派送 Action 至使用者定義的 Controller
     *
     * @param $configFile 外部 INI 檔案路徑
     * @param $env 應用程式執行環境
     */
    public final function run($configFile, $env = null)
    {
        $config = self::_loadConfig($configFile, $env);
        self::$_debug = (isset($config['bootstrap']['debug'])) ? (bool) $config['bootstrap']['debug'] : false;
        $bootstrapClass = 'GoEz_Bootstrap';
        if (isset($config['bootstrap']['class'])) {
            $tempClass = trim($config['bootstrap']['class']);
            $classExists = class_exists($tempClass, true);
            $isSubClass = is_subclass_of($tempClass, $bootstrapClass);
            if ($classExists && $isSubClass) {
                $bootstrapClass = $tempClass;
            }
        }
        $bootstrap = new $bootstrapClass($config);
        $bootstrap->_dispatch();
    }

    /**
     * 載入網址設定檔
     *
     * @param string $configFile 外部檔案路徑
     */
    protected static function _loadConfig($configFile, $env)
    {
        $resultConfig = array();
        if (is_array($configFile)) {
            $resultConfig = $configFile;
        } elseif (file_exists((string) $configFile)) {
            $ini = new GoEz_Config($configFile);
            $config = $ini->toArray();
            $resultConfig = $config[$env];
        } else {
            throw new Exception("無法正確讀取設定檔");
        }
        if (!isset($resultConfig['bootstrap'])) {
            $resultConfig['bootstrap'] = array();
        }
        if (!isset($resultConfig['view'])) {
            $resultConfig['view'] = array();
        }
        return $resultConfig;
    }

    /**
     * 不可初始化
     *
     * 主要進行以下動作：
     * 1. 取得設定
     * 2. 初始化 Router 以解析網址
     * 3. 初始化 Request
     * 4. 初始化 View
     *
     * @param $config 外部 INI 檔案路徑
     */
    protected function __construct($config)
    {
        $this->_config = $config;
        $this->_initRequest();
        $this->_initRouter();
        $this->_initView();
    }

    /**
     * 初始化 Router
     *
     * 預設用 GoEz_Router_Rewrite 來解析網址，格式為：
     *
     * <code>
     * http://xxx/baseurl/controller/action
     * </code>
     *
     * controller 和 action 都是 index 的話，就不需要寫在網址上，例如：
     *
     * <code>
     * http://xxx/baseurl/
     * </code>
     *
     * 但如果 action 不為 index ，而 controller 為 index ，那麼就要寫上 controller ，例如：
     *
     * <code>
     * http://xxx/baseurl/index/action
     * </code>
     *
     * 如果採用 GoEz_Router 來解析網址的話，格式為：
     *
     * <code>
     * http://xxx/baseurl/?controller=xxx&action=yyy
     * </code>
     */
    protected function _initRouter()
    {
        if ('cli' === strtolower(PHP_SAPI)) {
            $routerName = 'GoEz_Router_Cli';
        } else {
            $routerName = $this->_getClassInConfig('router', 'GoEz_Router_Rewrite');
        }

        $this->_router = new $routerName($this->_request);
    }

    /**
     * 初始化 Request
     *
     * 預設為 GoEz_Request
     */
    protected function _initRequest()
    {
        $requestName = $this->_getClassInConfig('request', 'GoEz_Request');
        $this->_request = new $requestName();
    }

    /**
     * 初始化 View
     *
     * GoEz_View 採用 Smarty 2.6 當做 Render engine ，
     * 在這裡初始化時，會預先把 baseUrl 放在 $frontendVars 這個樣版陣列變數裡
     */
    protected function _initView()
    {
        $this->_view = new GoEz_View($this->_config['view']);
        $this->_view->setFrontendVars('baseUrl', $this->_request->getBaseUrl());
    }

    /**
     * 使用者定義的 Controller
     *
     * @var GoEz_Controller
     */
    protected $_userController = null;

    /**
     * Dispatch
     *
     * Dispatch 執行流程如下：
     *
     * 1. 找出目前網址選擇的 controller
     * 2. 將 config 轉交給 controller
     * 3. 將 request 轉交給 controller
     * 4. 將 view 轉交給 controller
     * 5. 執行 controller 的 init()
     * 6. 執行 controller 的 beforeDispatch()
     * 7. 執行目前網址選擇的 action
     * 8. 執行 controller 的 afterDispatch()
     */
    protected function _dispatch()
    {
        $this->_userController = $this->_getUserController();
        $this->_userController->setConfig($this->_config);
        $this->_userController->setRequest($this->_request);
        $this->_userController->setView($this->_view);
        $this->_userController->init();
        $this->_userController->beforeDispatch();
        $this->_userController->{$this->_getUserAction()}();
        $this->_userController->afterDispatch();
    }

    /**
     * 取得使用者定義的 Controller
     *
     * @return GoEz_Controller
     * @throws Excetion
     */
    protected function _getUserController()
    {
        $userNamespace = 'My_';
        if (array_key_exists('userNamespace', $this->_config['bootstrap'])) {
            $userNamespace = rtrim(ucfirst($this->_config['bootstrap']['userNamespace']), '_') . '_';
        }
        $controllerName = $userNamespace . ucfirst($this->_router->getController()) . 'Controller';
        try {
            return new $controllerName();
        } catch (Exception $e) {
            throw new Exception("Controller \"$controllerName\" 不存在。");
        }
    }

    /**
     * 取得使用者定義的動作
     *
     * @return string
     * @throws Excetion
     */
    protected function _getUserAction()
    {
        $action = $this->_router->getAction() . 'Action';
        if (method_exists($this->_userController, $action)) {
            return $action;
        } else {
            $controllerName = get_class($this->_userController);
            throw new Exception("Action \"$controllerName::$action\" 不存在。");
        }
    }

    /**
     * 載入類別名稱
     *
     * @param string $key
     * @param string $defaultClassName
     * @return string
     * @throws Excetion
     */
    private function _getClassInConfig($key, $defaultClassName)
    {
        $className = $defaultClassName;
        if (array_key_exists($key, $this->_config['bootstrap'])) {
            $className = trim($this->_config['bootstrap'][$key]);
            if (!class_exists($className, true)) {
                throw new Exception("\"$className\" 不存在。");
            }
        }
        return $className;
    }

    /**
     * 顯示異常
     *
     * @param Exception $e
     */
    public static function displayException(Exception $e)
    {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
        '<html xmlns="http://www.w3.org/1999/xhtml">',
        '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />',
        '<title>程式發生錯誤</title></head><body>',
        '<h1>程式發生錯誤</h1>';
        if (self::$_debug) {
            echo '<p>', $e->getMessage(), '</p>';
            echo self::displayTrace($e->getTrace());
        } else {
            echo '<p>您提供的網址或是您的操作造成了系統無法正確回應。</p>';
        }
        echo '</body></html>';
    }

    /**
     * 顯示錯誤流程
     *
     * @param array $traceList
     * @return string
     */
    protected static function displayTrace($traceList)
    {
        $result = '<hr />';
        foreach ($traceList as $trace) {
            foreach ($trace as $col => $value) {
                $result .= '<strong>' . $col . '</strong>: ' . htmlspecialchars(print_r($value, true)) . "\n";
            }
            $result .= '<hr />';
        }
        return '<pre>' . $result . '</pre>';
    }
}