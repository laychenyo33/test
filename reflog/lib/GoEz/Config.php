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
 * Ini 設定類別
 *
 * @package    GoEz
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 */
class GoEz_Config
{
    /**
     * 存放設定的陣列
     *
     * @var array
     */
    protected $_config = array();

    /**
     * 解析
     *
     * 使用 PHP 原生的 parse_ini_file 來解析 INI 檔，
     * 並且再做一些特別的處理
     *
     * @param string $configFile 外部檔案路徑
     */
    public function __construct($configFile)
    {
        $result = array();
        if ($result = parse_ini_file($configFile, true)) {
            $result = $this->_processExtends($result);
            $result = $this->_processSection($result);
        }
        $this->_config = $result;
    }

    /**
     * 處理區段
     *
     * @param array $oldConfig
     * @return array
     */
    protected function _processSection($oldConfig)
    {
        $newConfig = array();
        foreach ($oldConfig as $section => $settings) {
            $newConfig[$section] = array();
        	foreach ($settings as $key => $value) {
                $newConfig[$section] = $this->_processKey($newConfig[$section], $key, $value);
        	}
        }
        return $newConfig;
    }

    /**
     * 處理每個設定
     *
     * 將：
     *
     * <code>
     * xxx.yyy = 123
     * xxx.zzz = 456
     * </code>
     *
     * 轉換為：
     *
     * <code>
     * [xxx] => array(
     *     [yyy] => 123,
     *     [zzz] => 456,
     * );
     * </code>
     *
     * @param array $config
     * @param string $key
     * @param string $value
     * @return array
     * @throws Excetion
     */
    protected function _processKey($config, $key, $value)
    {
        if (strpos($key, '.') !== false) {
            $pieces = explode('.', $key, 2);
            if (strlen($pieces[0]) && strlen($pieces[1])) {
                if (!isset($config[$pieces[0]])) {
                    if ($pieces[0] === '0' && !empty($config)) {
                        // convert the current values in $config into an array
                        $config = array($pieces[0] => $config);
                    } else {
                        $config[$pieces[0]] = array();
                    }
                } elseif (!is_array($config[$pieces[0]])) {
                    throw new Exception("Cannot create sub-key for '{$pieces[0]}' as key already exists");
                }
                $config[$pieces[0]] = $this->_processKey($config[$pieces[0]], $pieces[1], $value);
            } else {
                throw new Exception("Invalid key '$key'");
            }
        } else {
            $config[$key] = $value;
        }
        return $config;
    }

    /**
     * 處理繼承關係
     *
     * 解析 [xxx : yyy] 的繼承關係
     *
     * @param array $oldConfig
     * @return array
     */
    protected function _processExtends($config)
    {
        foreach ($config as $namespace => $properties) {
            $nameList = explode(':', $namespace);
            if (isset($nameList[1])) {
                $name = trim($nameList[0]);
                $extends = trim($nameList[1]);
                $config[$name] = array();

                if (isset($config[$extends])) {
                    foreach ($config[$extends] as $prop => $val) {
                        if (!isset($config[$namespace][$prop])) {
                            $config[$namespace][$prop] = $val;
                        }
                    }
                    foreach ($config[$extends] as $prop => $val) {
                    	$config[$name][$prop] = $config[$namespace][$prop];
                    }
                }
                unset($config[$namespace]);
            }
        }
        return $config;
    }

    /**
     * 輸出陣列
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_config;
    }
}