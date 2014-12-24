<?php
class Model_Session_Cart_Discounter_Quantity implements Model_Session_Cart_Discounter_Interface {
    public function checkout(&$products){
        $db = App::getHelper('db');
        $products['subtotal_price'] = $products['amount'] * $products['price'];
        $products['discount'] = 1;
        if($products['quantity_discount']){
            $discount = App::getHelper('dbtable')->products_discount->getDiscount($products['discount_sets'],$products['amount']);
            if(!empty($discount)){
                $products['discount'] = $discount;
                $products['subtotal_price'] = round($products['subtotal_price']*$discount);
            }
        }
    }
}
