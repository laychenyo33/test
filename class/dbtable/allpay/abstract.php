<?php
class Dbtable_Allpay_Abstract extends Dbtable_Abstract{
    function writeData($post,$act='update') {
        $method = "_mk_".$act."_sql";
        if(method_exists($this, $method)){
            if($act=='update' && empty($this->values[$this->pk])){
                throw new Exception("no pk exists!");
            }
            $this->_retrieve_cols($post);
            $sql = $this->{$method}();
            $this->_query($sql);
        }else{
            throw new Exception("method : {$method} doesn't exists!");
        }
    }
      
}
?>
