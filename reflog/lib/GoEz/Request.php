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
 * Request 類別
 *
 * @package    GoEz
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 */
class GoEz_Request
{
    /**
     * Base Url
     *
     * @var string
     */
    protected $_baseUrl = '';

    /**
     * 初始化
     *
     */
    public function __construct()
    {
        $this->_init();
    }

    /**
     * 初始化 BaseUrl
     *
     * 例如當前的應用程式目錄名稱為 project ，
     * 而網址為 http://localhost/project 時，
     * 那麼 BaseUrl 就是 project
     *
     * 設置 BaseUrl 主要的目的是為了讓應用程式可以任意搬移
     *
     */
    protected function _init()
    {
        $this->_baseUrl = rtrim(str_replace('index.php', '', $_SERVER['SCRIPT_NAME']), '/');
    }

    /**
     * 設定 BaseUrl
     *
     * 如果程式自動取得的 BaseUrl 不正確時，可以用 setBaseUrl 設定
     *
     * @return string
     */
    public function setBaseUrl($baseUrl)
    {
        $this->_baseUrl = (string) $baseUrl;
    }

    /**
     * 取得 BaseUrl
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }

    /**
     * 是否為 POST 要求
     *
     * @return bool
     */
    public function isPost()
    {
        return (bool) ('POST' == $_SERVER['REQUEST_METHOD']);
    }

    /**
     * 是否為 AJAX 要求 (XmlHttpReqeust)
     *
     * @return bool
     */
    public function isAjax()
    {
        $flag = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : false;
        return (bool) ('XMLHttpRequest' == $flag);
    }

    /**
     * 取得 POST 值
     *
     * @param string $key
     * @param bool $stripTags 設為 false 時會回傳原始的 POST 值，不會把 html tag 去掉
     * @return string
     */
    public function getPost($key, $stripTags = true)
    {
        return isset($_POST[$key]) ? ($stripTags ? strip_tags(trim($_POST[$key])) : trim($_POST[$key])) : null;
    }

    /**
     * 取得 GET 值
     *
     * @param string $key
     * @param bool $stripTags 設為 false 時會回傳原始的 POST 值，不會把 html tag 去掉
     * @return string
     */
    public function getQuery($key, $stripTags = true)
    {
        return isset($_GET[$key]) ? ($stripTags ? strip_tags(trim($_GET[$key])) : trim($_GET[$key])) : null;
    }

    /**
     * 取得 COOKIE 值
     *
     * @param string $key
     * @param bool $stripTags 設為 false 時會回傳原始的 POST 值，不會把 html tag 去掉
     * @return string
     */
    public function getCookie($key)
    {
        return isset($_COOKIE[$key]) ? trim($_COOKIE[$key]) : null;
    }

    /**
     * 程式參數
     *
     * @var array
     */
    protected $_params = array();

    /**
     * 設定參數
     *
     * @param string $key
     * @param mixed $value
     */
    public function setParam($key, $value)
    {
        $this->_params[$key] = $value;
    }

    /**
     * 取得參數
     *
     * @param string $key
     * @return mixed
     */
    public function getParam($key)
    {
        return isset($this->_params[$key]) ? $this->_params[$key] : null;
    }

    /**
     * 取得所有參數
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }
}