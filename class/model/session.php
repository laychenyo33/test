<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of session
 *
 * @author Administrator
 */
class Model_Session {
    //put your code here
    static protected $_handler;
    protected $_session;
    function __construct($sess_cookie_name) {
        if(!session_id()){
            session_start();
        }
        $this->_session =&$_SESSION[$sess_cookie_name];
    }
    function __get($name){
        return $this->_session[$name];
    }
    function __set($name,$value){
        $this->_session[$name] = $value;
    }
    static function factory($sess_cookie_name){
        if(self::$_handler === null){
            self::$_handler = new self($sess_cookie_name);
        }
        return self::$_handler;
    }
    function getValue($name){
        return $this->_session[$name];
    }
    function setValue($name,$value){
        $this->_session[$name] = $value;
    }
    function isValueExists($name){
        return isset($this->_session[$name]);
    }
}
