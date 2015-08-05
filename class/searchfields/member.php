<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of contactus
 *
 * @author chunhsin
 */
class searchFields_member extends searchFields_abstract{
    protected $search_fields = array(
        'm_country' => array("title"=>"國家","fields"=>array("m_country")),
        'm_company_name' => array("title"=>"公司","fields"=>array("m_company_name")),
        'm_name' => array("title"=>"姓名","fields"=>array("m_fname","m_lname")),
        'm_email' => array("title"=>"電子郵件","fields"=>array("m_email","m_account")),
        'm_modifydate' => array("title"=>"編輯月份","fields"=>array("m_modifydate"),"compare"=>"between_date"),
        //'m_createdate' => array("title"=>"建立月份","fields"=>array("m_createdate"),"compare"=>"between_date"),
        'm_sub_epaper' => array("title"=>"訂閱電子報","fields"=>array("m_epaper_status"),"compare"=>"subscribe_epaper"),
    ); 
    function subscribe_epaper($fields,$val){
        global $db,$cms_cfg;
        $vMap = array(
            0 => array('','0','no','否','沒有'),
            1 => array('1','yes','是','有'),
        );
        foreach($vMap as $key => $mapArr){
            if(in_array(strtolower($val), $mapArr)){
                return sprintf("(%s='%d')",$fields[0],$key);
            }
        }
    }
}
?>
