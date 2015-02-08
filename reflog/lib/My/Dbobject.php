<?php
class My_Dbobject {
    protected $_db;
    protected $_errorInfo;
    
    function __construct(PDO $db) {
        $this->_db = $db;
    }
    
    function getErrorInfo(){
        return $this->_errorInfo;
    }
    
    function _prepare($statement){
        $stmt = $this->_db->prepare( $statement );
        if(!is_a($stmt, 'PDOStatement')){
            throw new Exception("Can't prepare statement ");
        }    
        return $stmt;
    }
    
    function checkError(PDOStatement $stmt){
        $this->_errorInfo = $stmt->errorInfo();
        return (is_null($this->_errorInfo[1]))?true:false;
    }
}
