<?php
class My_Bootstrap extends GoEz_Bootstrap{
    
    function __construct($config) {
        $config['bootstrap']['instance'] = $this;
        if($config['bootstrap']['db']['dsn']){
            try{
                $db = new PDO($config['bootstrap']['db']['dsn']);
                $config['bootstrap']['db'] = $db;
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }
        parent::__construct($config);
    }
}
