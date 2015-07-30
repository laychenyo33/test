<?php
class Dbtable_Order extends Dbtable_Abstract{
    //可修改的欄位
    protected $table = "order";
    protected $pk = "o_id";

    function delete($pk) {
        $writeData = array(
            'o_id' => $pk,
            'del'   => 1,
        );
        $this->writeData($writeData);    
        App::getHelper('dbtable')->order_items->update(array('del'=>1),"o_id='".$pk."'");
    }
}
?>
