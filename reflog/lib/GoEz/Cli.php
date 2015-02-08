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
 * Cli 類別
 *
 * @package    GoEz
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 * @see        http://pwfisher.com/nucleus/index.php?itemid=45
 * @author     Patrick Fisher <patrick@pwfisher.com>
 */
class GoEz_Cli
{
    /**
     * Arguments
     *
     * @var array
     */
    public static $args;

    /**
     * 轉換命令列參數
     *
     * This command line option parser supports any combination of three types
     * of options (switches, flags and arguments) and returns a simple array.
     *
     * 本方法可以將命令列參數轉換為陣列，參考範例如下：
     *
     * <code>
     * $args = GoEz_Cli::parseArgs($_SERVER['argv']);
     *
     * [pfisher ~]$ php test.php --foo --bar=baz
     *   ["foo"]   => true
     *   ["bar"]   => "baz"
     *
     * [pfisher ~]$ php test.php -abc
     *   ["a"]     => true
     *   ["b"]     => true
     *   ["c"]     => true
     *
     * [pfisher ~]$ php test.php arg1 arg2 arg3
     *   [0]       => "arg1"
     *   [1]       => "arg2"
     *   [2]       => "arg3"
     *
     * [pfisher ~]$ php test.php plain-arg --foo --bar=baz --funny="spam=eggs" --also-funny=spam=eggs \
     * > 'plain arg 2' -abc -k=value "plain arg 3" --s="original" --s='overwrite' --s
     *   [0]       => "plain-arg"
     *   ["foo"]   => true
     *   ["bar"]   => "baz"
     *   ["funny"] => "spam=eggs"
     *   ["also-funny"] => "spam=eggs"
     *   [1]       => "plain arg 2"
     *   ["a"]     => true
     *   ["b"]     => true
     *   ["c"]     => true
     *   ["k"]     => "value"
     *   [2]       => "plain arg 3"
     *   ["s"]     => "overwrite"
     * </code>
     *
     * @param array $argv
     */
    public static function parseArgs($argv)
    {
        array_shift($argv);
        $out = array();

        foreach ($argv as $arg) {
            // --foo --bar=baz
            if ('--' === substr($arg, 0, 2)) {
                $eqPos = strpos($arg, '=');
                if (false === $eqPos) { // --foo
                    $key = substr($arg,2);
                    $value = isset($out[$key]) ? $out[$key] : true;
                    $out[$key] = $value;
                } else { // --bar=baz
                    $key = substr($arg, 2, $eqPos - 2);
                    $value = substr($arg, $eqPos + 1);
                    $out[$key] = $value;
                }
            } elseif (substr($arg, 0, 1) == '-') { // -k=value -abc
                if ('=' === substr($arg, 2, 1)){ // -k=value
                    $key = substr($arg, 1, 1);
                    $value = substr($arg, 3);
                    $out[$key] = $value;
                } else { // -abc
                    $chars = str_split(substr($arg, 1));
                    foreach ($chars as $char) {
                        $key = $char;
                        $value = isset($out[$key]) ? $out[$key] : true;
                        $out[$key] = $value;
                    }
                }
            } else { // plain-arg
                $value = $arg;
                $out[] = $value;
            }
        }
        self::$args = $out;
        return $out;
    }

    /**
     * 將對應的選項轉換為 bool 值
     *
     * @param string $key
     * @param mixed $default 找不到對應的選項時所要回傳的值
     * @return bool
     */
    public static function getBoolean($key, $default = false)
    {
        if (!isset(self::$args[$key])) {
            return $default;
        }
        $value = self::$args[$key];

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            $value = strtolower($value);
            $map = array(
                'y'     => true,
                'n'     => false,
                'yes'   => true,
                'no'    => false,
                'true'  => true,
                'false' => false,
                '1'     => true,
                '0'     => false,
                'on'    => true,
                'off'   => false,
            );
            if (isset($map[$value])) {
                return $map[$value];
            }
        }
        return $default;
    }
}