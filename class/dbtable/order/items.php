<?php
class Dbtable_Order_Items extends Dbtable_Abstract{
    //可修改的欄位
    protected $table = "order_items";
    protected $pk = "oi_id";
    
    function delete($pk) {
        $writeData = array(
            'oi_id' => $pk,
            'del'   => 1,
        );
        $this->writeData($writeData);
    }
     
}
?>
