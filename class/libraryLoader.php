<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of libraryLoader
 *
 * @author Administrator
 */
class libraryLoader {
    //put your code here
    public static $library_base_path;
    static function load($lib_name,$filename,$classname){
        if(empty(self::$library_base_path)){
            self::$library_base_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . "libraries" . DIRECTORY_SEPARATOR;
        }
        $library_file = self::$library_base_path . $lib_name . DIRECTORY_SEPARATOR . $filename . ".php";
        require_once $library_file;
        if(!class_exists($classname)){
            throw new Exception($classname . "doesn't not exists!");
        }
    }
}
