<?php
class My_Request extends GoEz_Request {
    //put your code here
    public $controller;
    
    function _init() {
        parent::_init();
    }
    
    function setController(GoEz_Controller $controller){
        $this->controller = $controller;
    }
}
