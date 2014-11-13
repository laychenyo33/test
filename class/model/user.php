<?php
class Model_User extends Model_Modules {
    function __construct( $options = "") {
        parent::__construct($this, $options);
    }

    function authenticate(){
        $authtype = $this->_options['authtype'];
        if($this->$authtype->authenticate()) return true;
    }    
    
}
