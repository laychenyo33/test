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
        'o_paid' => array("title"=>"付款狀態","fields"=>array("o_paid"),'fieldType'=>'select'),
        'o_payment_type' => array("title"=>"付款方式","fields"=>array("o_payment_type"),'fieldType'=>'select'),
        'rid' => array("title"=>"美安訂單","fields"=>array("rid"),'fieldType'=>'select','compare'=>"rid_order"),
    ); 
    
    protected $exportRIDUrl;
    
    function __construct() {
        global $ws_array,$cms_cfg;
        if(!App::configs()->ws_module->ws_rid_order){
            unset($this->search_fields['rid']);
        }else{
            if($cms_cfg['new_cart_path']){
                $this->exportRIDUrl = $cms_cfg['new_cart_path'].'admin.php?func=o_ex3';
            }else{
                $this->exportRIDUrl = $cms_cfg['manage_root'].'order.php?func=o_ex3';
            }
            $this->search_fields['rid']['dataSource'] = $ws_array['rid_order_cond'];
        }
        if($cms_cfg['new_cart_path']){
            $this->exportUrl = $cms_cfg['new_cart_path'].'admin.php?func=o_ex2';
        }else{
            $this->exportUrl = $cms_cfg['manage_root'].'order.php?func=o_ex2';
        }
        //設定欄位資料來源
        $this->search_fields['o_status']['dataSource'] = $ws_array["order_status"];
        $this->search_fields['o_payment_type']['dataSource'] = $ws_array["payment_type"];
        $this->search_fields['o_paid']['dataSource'] = $ws_array["order_paid_status"];
    }
    
    protected function _init_tpl(){
        $tpl = parent::_init_tpl();
        if($this->exportRIDUrl){
            $tpl->newBlock("EXPORT_RID_BTN");
            $tpl->assign("exportUrl",$this->exportRIDUrl);
        }
        return $tpl;
    }
    
    
    function rid_order($field,$input){
        if($input){
            return sprintf(" `%s`!='' ",$field['fields'][0]);
        }
    }
}
?>
