<?php
class Model_Session_Cart_Discounter_Member extends Model_Session_Cart_Discounter_Abstract_Pre {
    
    function getDiscount(&$products){
        $discount = $this->_model->getSessionHandler()->MEMBER_DISCOUNT/100;
        return $discount;
    }
}
