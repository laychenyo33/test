<?php
class Model_Session_Cart_Discounter_Abstract_Pre extends Model_Session_Cart_Discounter_Abstract {
    /**
     *前折扣(pre)或後折扣(post)
     * @var string
     */
    public $position = "pre";
    
    static $discountedId = array();
    
    public function run() {
        return $this->runDiscountOnProducts();
    }    
    
    public function runDiscountOnProducts() {
        $cart_produdts = $this->_model->get_cart_products();
        if($cart_produdts){
            foreach($cart_produdts as $p_id => $productInfo){
                if(!isset(self::$discountedId[$p_id])){
                    self::$discountedId[$p_id] = true;
                    $productInfo['discount'] = 1;
                }
                if($this->checkout($productInfo)){
                    $this->_model->updateCartProduct($p_id,$productInfo);
                }
            }
        }
        return true;
    }     
    
    public function checkout(&$products){
        $products['subtotal_price'] = $products['amount'] * $products['price'];
        $discount = $this->getDiscount($products);
        if($discount>0 && $discount<1){
            $products['discount'] = $products['discount']*$discount ;
        }
        $products['subtotal_price'] = round($products['subtotal_price']*$products['discount']);
        //有折扣回傳true，才會更新購物車資訊
        return true; 
    }    
    
    public function getDiscount(&$products){
        throw new Exception("please custom method getDiscount when extending Model_Session_Cart_Discounter_Abstract_Pre");
    }
    
}
