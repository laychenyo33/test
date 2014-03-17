<?php
class App {
    //put your code here
    protected static $_helper = array();
    
    static function addHelper($id,$obj){
        self::$_helper[$id] = $obj;
    }
    
    static function getHelper($id){
        if(isset(self::$_helper[$id])){
            return self::$_helper[$id];
        }
    }
    
}
