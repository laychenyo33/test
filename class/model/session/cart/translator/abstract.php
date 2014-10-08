<?php
abstract class Model_Session_Cart_Translator_Abstract implements Model_Session_Cart_Translator_Interface{
    function __construct($options) {
        if(is_array($options)){
            $class_vars = array_keys(get_object_vars($this));
            foreach($options as $k => $v){
                $var = '_'.$k;
                if(in_array($var,$class_vars)){
                    $this->{$var} = $v;
                }
            }
        }
    }
    function translate($origin_data) {
        throw new Exception("should override translate method");
    }
}
