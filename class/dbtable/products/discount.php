<?php
class Dbtable_Products_Discount extends Dbtable_Abstract{
    //可修改的欄位
    protected $table = "products_discount";
    protected $pk = "id";
    
    function getDiscountList($sets){
        $sql = "select a.* from ".$this->db->prefix("products_discount")." as a inner join ".
                $this->db->prefix("products_discountsets")." as b on a.sets=b.id where b.id='".$sets."' and b.status='1' ".
                "order by qtyfloor ";
        $list = array();
        $res = $this->db->query($sql);
        while($row = $this->db->fetch_array($res,1)){
            $list[] = $row;
        }
        return $list;
    }
    function getDiscount($sets,$amount){
        $sql = "select discount from ".$this->db->prefix("products_discount")." as a inner join ".
                $this->db->prefix("products_discountsets")." as b on a.sets=b.id where b.id='".$sets."' and b.status='1' and ('".$amount."'>=a.qtyfloor && '".$amount."'<=a.qtyceil)";
        list($discount) = $this->db->query_firstRow($sql,0);
        return $discount;
    } 
}
?>
