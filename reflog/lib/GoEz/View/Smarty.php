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
 * @see Smarty
 */
require_once dirname(dirname(dirname(__FILE__))) . '/Smarty/Smarty.class.php';

/**
 * View Engine - Smarty
 *
 * @package    GoEz
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 */
class GoEz_View_Smarty implements GoEz_View_Engine
{
    /**
     * Smarty 樣版引擎
     *
     * @var Smarty
     */
    protected $_engine = null;

    /**
     * 建立 Smarty 實體
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->_engine = new Smarty();
        foreach ($config as $attr => $value) {
        	if (property_exists($this->_engine, $attr)) {
        	    if (is_string($value)) {
                    $this->_engine->$attr = $value;
        	    } elseif (is_array($value)) {
        	        $this->_engine->$attr = array_merge($this->_engine->$attr, $value);
        	    }
        	}
        }
    }

    /**
     * 指定樣版變數
     *
     * @param mixed $name
     * @param mixed $value (可省略)
     */
    public function assign($name, $value = null)
    {
        $this->_engine->assign($name, $value);
    }

    /**
     * 取得解析後的樣版內容
     *
     * @param string $file
     * @return string
     */
    public function fetch($file)
    {
        return $this->_engine->fetch($file);
    }
}