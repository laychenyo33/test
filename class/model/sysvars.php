<?php
class Model_Sysvars {
    //put your code here
    static $_varsmap = array();
    
    function __construct() {
        $sysvars = App::getHelper('dbtable')->system_vars->getDataList();
        if(!empty($sysvars)){
            foreach($sysvars as $var){
                $key = count(self::$_varsmap['pattern']);
                //pattern
                self::$_varsmap['pattern'][$key] = '%{'.$var['name'].'}%';
                //replace
                self::$_varsmap['replace'][$key] = $var['value'];
            }
        }
    }
    
    function getPattern(){
        return self::$_varsmap['pattern'];
    }
    
    function getReplace(){
        return self::$_varsmap['replace'];
    }
}
