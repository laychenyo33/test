<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sendnotification
 *
 * @author Administrator
 */
global $sessHandler;
class Model_Sendnotification {
    //put your code here
    static public $sess_id = "sendnotification";
    static public $_handler;
    static public function setHandler($handler){
        self::$_handler = $handler;
    }
    static public function fill($id){
        $container = self::$_handler->{self::$sess_id};
        $container[$id] = time();
        self::$_handler->{self::$sess_id} = array_merge(self::$_handler->{self::$sess_id},$container);
    }
    static public function had($id){
        $container = self::$_handler->{self::$sess_id};
        return isset($container[$id]);
    }
}
Model_Sendnotification::setHandler($sessHandler);