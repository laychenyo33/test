<?php
class Model_Dbtable {
    //put your code here
    protected $_db;
    protected $_dbtable = array();
    protected $_basePath;
    
    function __construct($db) {
        $this->_basePath = realpath(dirname(__FILE__)."/../");
        $this->_db = $db;
    }

    function __get($name) {
        return $this->getDbtable($name);
    }
    function __set($name, $obj) {
        if(is_a($value,'Dbtable_abstract')){
            $name = strtolower($name);
            $this->_dbtable[$name] = $obj;
        }else{
            throw new Exception("assign value not instace of class Dbtable_Abstract!");
        }
    }
    function getDbtable($id){
        $id = strtolower($id);
        if(!isset($this->_dbtable[$id])){
            $class = $this->loadClass($id);
            if(class_exists($class)){
                $dbtable = new $class($this->_db,$this->_db->prefix);
                $this->_dbtable[$id] = $dbtable;
            }else{
                throw new Exception("class :".$class." doesn't exists!");
            }            
        }
        if(is_a($this->_dbtable[$id],'Dbtable_abstract')){
            return $this->_dbtable[$id];
        }else{
            throw new Exception('not instance of Dbtable_Abstract');
        }
    }
    
    function loadClass($id){
        $filename = str_replace("_", "/", $id);
        if(file_exists($this->_basePath . "/dbtable/".$filename.".php")){
            include_once $this->_basePath . "/dbtable/".$filename.".php";
            return "Dbtable_".ucfirst($id);
        }else{
            throw new Exception("file: {$id}.php doesn't exists!");
        }
    }
}
