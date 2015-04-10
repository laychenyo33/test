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
        'o_createdate' => array("title"=>"訂單建立月份","fields"=>array("o_createdate"),'compare'=>"date_interval",'fieldType'=>'date','urlParam'=>'o_createdate'),
        'o_name' => array("title"=>"訂購人","fields"=>array("o_name")),
        'o_tel' => array("title"=>"電話","fields"=>array("o_tel")),
        'o_cellphone' => array("title"=>"手機","fields"=>array("o_cellphone")),
        'o_status' => array("title"=>"訂單狀態","fields"=>array("o_status"),'fieldType'=>'select'),
        'o_payment_type' => array("title"=>"付款方式","fields"=>array("o_payment_type"),'fieldType'=>'select'),
    ); 
    
    function __construct() {
        global $ws_array;
        $this->exportUrl = 'order.php?func=o_ex2';
        //設定欄位資料來源
        $this->search_fields['o_status']['dataSource'] = $ws_array["order_status"];
        $this->search_fields['o_payment_type']['dataSource'] = $ws_array["payment_type"];
    }
}
?>
