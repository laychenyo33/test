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
 * View 類別
 *
 * @package    GoEz
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 */
class GoEz_View
{
    /**
     * 設定
     *
     * @var array
     */
    protected $_config = array();

    /**
     * 初始化
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->_config = (array) $config;
    }

    /**
     * 樣版變數
     *
     * @var array
     */
    protected $_vars = array();

    /**
     * 設定樣版變數
     *
     * 透過魔術方法 __set() ，我們可以用以下的方式來設定樣版變數：
     *
     * <code>
     * $view->abc = 123;
     * $view->def = 456;
     * </code>
     *
     * 然後在呼叫 renderTemplate() 方法時，就會自動把這些變數指定給 GoEz_View_Engine
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value = null)
    {
        $this->_setVars($this->_vars, $name, $value);
    }

    /**
     * HTML 前端樣版變數
     *
     * 主要會在呼叫 renderTemplate() 方法時，將這裡的變數傳送給 GoEz_View_Engine 應用
     *
     * @var array
     */
    protected $_frontendVars = array();

    /**
     * 設定前端樣版變數
     *
     * 用來指定 GoEz_View_Engine 專用的前端樣版變數
     *
     * @param string $name
     * @param mixed $value
     */
    public function setFrontendVars($name, $value = null)
    {
        $this->_setVars($this->_frontendVars, $name, $value);
    }

    /**
     * 設定變數
     *
     * @param array $vars
     * @param string $name
     * @param mixed $value
     */
    protected function _setVars(&$vars, $name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                if ($key != '') {
                    $vars[$key] = $val;
                }
            }
        } else {
            if ($name != '') {
                $vars[$name] = $value;
            }
        }
    }

    /**
     * 自動取得對應的樣版變數
     *
     * @param string $name
     * @return mixed
     */
    private function __get($name)
    {
        return isset($this->_vars[$name]) ? $this->_vars[$name] : null;
    }

    /**
     * 取得 View Engine
     *
     * 這裡會自動抓取 INI 檔案裡設定的 view config
     * 然後設定 GoEz_View_Engine 的初始值，然後回傳
     *
     * @return GoEz_View_Engine
     */
    public function getViewEngine()
    {
        static $engine = null;
        if (null === $engine) {
            $engineName = isset($this->_config['engine'])
                        ? $this->_config['engine']
                        : 'GoEz_View_Smarty';
            if (!class_exists($engineName, true)) {
                throw new Exception("View Engine \"$engineName\" 不存在。");
            }
            $engine = new $engineName($this->_config);
        }
        return $engine;
    }

    /**
     * 取得 Render 結果
     *
     * 先將一般的樣版變數以及 frontendVars 指定的樣版變數 assign 給 GoEz_View_Engine
     * 然後回傳 GoEz_View_Engine::fetch() 後的結果
     *
     * @param string $file
     * @return string
     */
    public function fetchTemplate($file)
    {
        $engine = $this->getViewEngine();
        $engine->assign($this->_vars);
        $engine->assign('frontendVars', $this->_frontendVars);
        return $engine->fetch($file);
    }

    /**
     * 輸出 Render 結果
     *
     * 直接顯示 fetchTemplate() 方法回傳的結果
     *
     * @param string $file
     * @return string
     */
    public function renderTemplate($file)
    {
        echo $this->fetchTemplate($file);
    }

    /**
     * 產生 JSON
     *
     * 注意：這個方法不會把 frontendVars 的內容包含進來
     *
     * @return string
     */
    public function fetchJson()
    {
        return json_encode($this->_vars);
    }

    /**
     * 顯示 JSON
     *
     * @return string
     */
    public function renderJson()
    {
        echo $this->fetchJson();
    }
}