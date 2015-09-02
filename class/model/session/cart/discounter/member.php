<?php
class Model_Session_Cart_Discounter_Member extends Model_Session_Cart_Discounter_Abstract_Pre {
    
    function getDiscount(&$products){
        $db = App::getHelper("db");
        //適用多重會員分類，單一分類也可以
        $sql = "select min(mc_discount) from ".$db->prefix("member_cate")." as a "
                . "inner join ".$db->prefix("member")." as b "
                . "on find_in_set(a.mc_id,b.mc_id) "
                . "where m_id='".App::getHelper("session")->MEMBER_ID."'";
        list($discount) = $db->query_firstRow($sql,0);
        $discount /= 100;
        return $discount;
    }
}
