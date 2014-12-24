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
class Model_Session extends Model_Modules implements arrayaccess {
    //put your code here
    static protected $_handler;
    protected $_session;
    function __construct($sess_cookie_name,$options=null) {
        parent::__construct($this, $options['modules']);
        if(!session_id()){
            session_start();
        }
        $this->_session =&$_SESSION[$sess_cookie_name];
    }
    function &__get($name){
        if(method_exists($this, 'getModule'.  ucfirst($name))){
            $method = 'getModule'.  ucfirst($name);
            return $this->$method();
        }else{
            return $this->_session[$name];
        }
    }
    function __set($name,$value){
        $this->_session[$name] = $value;
    }
    function __isset($name) {
        return isset($this->_session[$name]);
    }
    function __unset($name) {
        unset($this->_session[$name]);
    }
    static function factory($sess_cookie_name,$options){
        if(self::$_handler === null){
            self::$_handler = new self($sess_cookie_name,$options);
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
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->_session[] = $value;
        } else {
            $this->_session[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->_session[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->_session[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->_session[$offset]) ? $this->_session[$offset] : null;
    }    
    public function modules(){
        return $this;
    }
    public function getModuleCart(){
        return $this->getModule('cart');
    }
}
