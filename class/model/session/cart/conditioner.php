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
            $sql = "select b.*,c_id from ".App::getHelper('db')->prefix("shopping_condition_products")." as a "
                    . "inner join ".App::getHelper('db')->prefix("products")." as b on a.p_id=b.p_id where c_id='{$row['id']}' and b.p_status='1' and b.onsale='1' ";
            $res2 = App::getHelper('db')->query($sql,true);
            while($p = App::getHelper('db')->fetch_array($res2,1)){
                $p['p_name'] = $p['p_name']."(加購品)";
                $p['price'] = $this->condition[$row['id']]['price'];
                $p['limit'] = $this->condition[$row['id']]['limit'];
                $p['addPurchase'] = true;
                $this->condition[$row['id']]['products'][$p['p_id']] = $p;
            }
        }
    }
    //取得加價購產品
    function getAdditionalPurchaseProducts(){
        $cart_products = $this->_model->get_cart_products();
        $cart_info = $this->_model->get_cart_info();
        $additionalPurchaseProducts = array();
        if($this->condition){
            foreach($this->condition as $c_id=>$cond){
                foreach($cart_products as $cp){
                    if( $this->isQualified($cp, $cart_info, $cond) ) {
                        $additionalPurchaseProducts[$cond['id']] = $cond;
                        $this->condition[$c_id]['pick']=true;
                    }
                }
            }
        }
        return $additionalPurchaseProducts;
    }
    /*
         *  $product: 加入購物車的產品
         *  $subtotal_price: 訂單小計金額
         *  $condition 加購設定項目
         *  符合條件的產品，或訂單小計金額符合選項設定
         */
    function isQualified($product,$cartInfo,$condition){
        if(($this->isQualifiedProduct($product, $condition) || 
            ($cartInfo['subtotal_price']>=$condition['amount'] && $condition['type']=="order")) && 
           !$condition['pick']){
            return true;
        }
        return false;
    }
    /*
         *  $product: 加入購物車的產品
         *  $condition 加購設定項目
         *  產品為套用加購所屬分類或產品， 且購買數大於等於條件數量，
         */    
    function isQualifiedProduct($product,$condition){
        if( $product['amount']>=$condition['quantity'] && in_array($condition['type'],array('cate','product')) && 
            (in_array($product['pc_id'],(array)$condition['map']['pc_id']) || in_array($product['p_id'],(array)$condition['map']['p_id']) )
          ){
            return true;
        }
        return false;
    }
    
    function hasAddPurchaseProduct($c_id,$p_id){
        return isset($this->condition[$c_id]['products'][$p_id]);
    }
    
    function getAddPurchaseProduct($c_id,$p_id){
        return $this->condition[$c_id]['products'][$p_id];
    }
    //取得使用中的加價購條件
    function getUsingConditions(){
        $cart_products = $this->_model->get_cart_products();
        $cart_info = $this->_model->get_cart_info();
        $usingConditions = array();
        if($this->condition){
            foreach($this->condition as $c_id=>$cond){
                foreach($cart_products as $cp){
                    if( !isset($cp['addPurchase']) && $this->isQualified($cp, $cart_info, $cond) ) {
                        $usingConditions[$cond['id']] = $cond;
                    }
                }
            }
        }
        return $usingConditions;
    }    
}
