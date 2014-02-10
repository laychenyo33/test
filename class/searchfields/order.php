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
class searchFields_order extends searchFields_abstract{
    protected $search_fields = array(
        'o_id' => array("title"=>"訂單編號","fields"=>array("o_id")),
        'o_name' => array("title"=>"訂購人","fields"=>array("o_name")),
        'o_tel' => array("title"=>"電話","fields"=>array("o_tel")),
        'o_cellphone' => array("title"=>"手機","fields"=>array("o_cellphone")),
        'o_createdate' => array("title"=>"訂單建立月份","fields"=>array("o_createdate"),'compare'=>"between_date"),
    ); 
}
?>
