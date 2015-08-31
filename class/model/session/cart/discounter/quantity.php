<?php
class Model_Session_Cart_Discounter_Quantity extends Model_Session_Cart_Discounter_Abstract_Pre {
    
    function getDiscount(&$products) {
        return App::getHelper('dbtable')->products_discount->getDiscount($products['discount_sets'],$products['amount']);
    }
}
