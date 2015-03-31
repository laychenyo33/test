<?php
class Model_Session_Cart_Conditioner extends Model_Modules
{
    protected $condition;
    
    function __construct($model = "", $options = "") {
        parent::__construct($model, $options);
        $today = date('Y-m-d');
        $sql = "select * from ".App::getHelper('db')->prefix("shopping_condition")
                . "where status='1' or (status='2' and start_date<='{$today}' and end_date>='{$today}' )";
        $res = App::getHelper('db')->query($sql);
        while($row = App::getHelper('db')->fetch_array($res,1)){
            $this->condition[$row['id']] = $row;
            //取得map
            $sql = "select * from ".App::getHelper('db')->prefix("shopping_condition_map")." where c_id='{$row['id']}'";
            $res2 = App::getHelper('db')->query($sql);
            while($m = App::getHelper('db')->fetch_array($res2,1)){
                if($m['pc_id']){
                    $this->condition[$row['id']]['map']['pc_id'][] = $m['pc_id'];
                }elseif($m['p_id']){
                    $this->condition[$row['id']]['map']['p_id'][] = $m['p_id'];
                }
            }
            //取得產品
            $sql = "select b.* from ".App::getHelper('db')->prefix("shopping_condition_products")." as a "
                    . "inner join ".App::getHelper('db')->prefix("products")." as b on a.p_id=b.p_id where c_id='{$row['id']}' and b.p_status='1' and b.onsale='1' ";
            $res2 = App::getHelper('db')->query($sql,true);
            while($p = App::getHelper('db')->fetch_array($res2,1)){
                $p['price'] = $this->condition[$row['id']]['price'];
                $this->condition[$row['id']]['products'][] = $p;
            }
        }
    }
    //取得加價購產品
    function getAdditionalPurchaseProducts(){
        $cart_products = $this->_model->get_cart_products();
        $cart_info = $this->_model->get_cart_info();
        $additionalPurchaseProducts = array();
        foreach($this->condition as $c_id=>$cond){
            foreach($cart_products as $cp){
                if( ( $cp['amount']>=$cond['quantity'] && in_array($cond['type'],array('cate','product')) || 
                      $cart_info['subtotal_price']>=$cond['amount'] && $cond['type']=="order" ) &&  
                    (in_array($cp['pc_id'],(array)$cond['map']['pc_id']) || in_array($cp['p_id'],(array)$cond['map']['p_id']) ) &&  
                    !$cond['pick'])
                {
                    $additionalPurchaseProducts[$cond['id']] = $cond;
                    $this->condition[$c_id]['pick']=true;
                }
            }
        }
        return $additionalPurchaseProducts;
    }
}
