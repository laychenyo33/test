<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]) ){
    header("location: ".$cms_cfg['manage_root']);
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$obj = new PRINT_PAGE();
class PRINT_PAGE{
    protected $_tpl;
    function PRINT_PAGE(){
        switch($_REQUEST["func"]){
            case "member_detail"://會員資料
                $this->ws_tpl_file = "templates/ws-manage-print-memberdetail-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->member_detail($_GET['m_id']);
                break;
            case "order_detail"://訂單列表
                $this->ws_tpl_file = "templates/ws-manage-print-orderdetail-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->order_detail($_GET['o_id']);
                break;
            case "new_order_detail"://訂單列表
                $this->ws_tpl_file = "templates/ws-manage-print-neworderdetail-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->new_order_detail($_GET['o_id']);
                break;
        }
        $this->_tpl->printToScreen();
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file,$css_block='NORMAL_CSS'){
        $this->_tpl = new TemplatePower( "templates/ws-manage-fn-print-tpl.html" );
        $this->_tpl->assignInclude( "MAIN", $ws_tpl_file);
        $this->_tpl->prepare();
        $this->_tpl->newBlock($css_block);
    }
    //訂單回覆--表單================================================================
    function order_detail($o_id){
        global $db,$cms_cfg,$TPLMSG,$main,$ws_array;
        if($cms_cfg["ws_module"]['ws_delivery_timesec']){ //是否顯示配送區間
            $this->_tpl->newBlock("DELIVERY_TIMESEC");
        }
        //帶入要回覆的訂單資料
        if(!empty($_REQUEST["o_id"])){
            $sql="select * from ".$db->prefix("order")." where o_id='".$o_id."' and del='0' ";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $dts = strtotime($row['o_deliver_date']);
                $this->_tpl->assignGlobal( array(
                    "VALUE_M_ID"  => $row["m_id"],
                    "VALUE_O_ID"  => $row["o_id"],
                    "VALUE_O_STATUS" => $ws_array["order_status"][$row["o_status"]],
                    "VALUE_O_NAME" => $row["o_name"],
                    "VALUE_O_TEL" => $row["o_tel"],
                    "VALUE_O_CELLPHONE" => $row["o_cellphone"],
                    "VALUE_O_ZIP" => $row["o_zip"],
                    "VALUE_O_ADDRESS" => $row["o_city"].$row["o_area"].$row["o_address"],
                    "VALUE_O_EMAIL" => $row["o_email"],
                    "VALUE_O_RECI_NAME" => $row["o_reci_name"],
                    "VALUE_O_RECI_TEL" => $row["o_reci_tel"],
                    "VALUE_O_RECI_CELLPHONE" => $row["o_reci_cellphone"],
                    "VALUE_O_RECI_ZIP" => $row["o_reci_zip"],
                    "VALUE_O_RECI_ADDRESS" => $row["o_reci_city"].$row["o_reci_area"].$row["o_reci_address"],
                    "VALUE_O_RECI_EMAIL" => $row["o_reci_email"],
                    "VALUE_O_CONTENT" => $row["o_content"],
                    "VALUE_O_PLUS_PRICE" => App::getHelper('main')->format_shipprice_str($row['o_shippment_type'],$row["o_plus_price"]),
                    "VALUE_O_CHARGE_FEE" => $row["o_charge_fee"],
                    "VALUE_O_MINUS_PRICE" => ($row["o_minus_price"]?"-":"").$row["o_minus_price"],
                    "VALUE_O_SUBTOTAL_PRICE" => $row["o_subtotal_price"],
                    "VALUE_O_TOTAL_PRICE" => $row["o_total_price"],
                    "VALUE_O_STATUS" => $ws_array["order_status"][$row["o_status"]],
                    "VALUE_O_PAYMENT_TYPE"=>$ws_array["payment_type"][$row["o_payment_type"]],
                    "VALUE_O_SHIPPMENT_TYPE" => $ws_array["shippment_type"][$row['o_shippment_type']],
                    "VALUE_O_INVOICE_TYPE" => $ws_array["invoice_type"][$row['o_invoice_type']],
                    "VALUE_O_ATM_LAST5" => $row["o_atm_last5"],
                    "VALUE_O_DELIVERY_STR" => sprintf("%s %s",date("Y年m月d日",$dts),$ws_array["deliery_timesec"][$row['o_deliver_time_sec']]),
                    "VALUE_O_COMPANY_NAME" => $row["o_company_name"],
                    "VALUE_O_VAT_NUMBER" => $row["o_vat_number"],
                ));
                if($cms_cfg["ws_module"]["ws_vaccount"] & $row["o_virtual_account"]) {
                $this->_tpl->newBlock("ATM_DATA");
                    $this->_tpl->assignGlobal( array(
                        "VALUE_O_VIRTUAL_ACCOUNT" => $row["o_virtual_account"],
                        "VALUE_O_CURAMT" => $row["o_curamt"]
                    ));
                }
                //訂購產品列表
                $sql="select oi.*,p.p_small_img,p.p_serial from ".$db->prefix("order_items")." as oi inner join ".$db->prefix("products")." as p on oi.p_id=p.p_id where oi.o_id='".$o_id."' and oi.del='0' ";
                $selectrs = $db->query($sql);
                $total_price=0;
                $i=0;
                while($row = $db->fetch_array($selectrs,1)){
                    $i++;
                    $sub_total_price = $row["p_sell_price"] * $row["oi_amount"];
                    $total_price = $total_price+$sub_total_price;
                    $this->_tpl->newBlock( "ORDER_ITEMS_LIST" );
                    $this->_tpl->assign( array(
                        "VALUE_P_ID"  => $row["p_id"],
                        "VALUE_P_NAME" => $row["p_name"],
                        "P_SERIAL" => $row["p_serial"],
                        "VALUE_P_SELL_PRICE" => $row["p_sell_price"],
                        "VALUE_P_AMOUNT" => $row["oi_amount"],
                        "VALUE_P_SUBTOTAL_PRICE"  => $sub_total_price,
                        "VALUE_P_SERIAL"  => $i,
                        "VALUE_P_SMALL_IMG" => $row['p_small_img']?$cms_cfg['file_root'].$row['p_small_img']:$cms_cfg['default_preview_pic'],
                        "VALUE_SPEC" => $row["spec"],
                        "VALUE_DISCOUNT" => ($row["discount"]<1)?$row["discount"]:'',
                    ));
                }
            }
        }
    }
    //新art訂單
    function new_order_detail($o_id){
        global $db,$cms_cfg,$TPLMSG,$ws_array;
        //欄位名稱
        $this->_tpl->assignGlobal( array(
            "MSG_PRODUCT_SPECIAL_PRICE" => $TPLMSG['PRODUCT_PRICE']
        ));
        if($cms_cfg["ws_module"]['ws_delivery_timesec']){ //是否顯示配送區間
            $this->_tpl->newBlock("DELIVERY_TIMESEC");
        }
        //帶入要回覆的訂單資料
        if(!empty($_REQUEST["o_id"])){
            $sql="select * from ".$db->prefix("order")." where o_id='".$_REQUEST["o_id"]."' and del='0' ";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $dts = strtotime($row['o_arrival_time']);
                $this->_tpl->assignGlobal( array(
                    "VALUE_M_ID"  => $row["m_id"],
                    "VALUE_O_ID"  => $row["o_id"],
                    "VALUE_O_NAME" => $row["o_name"],
                    "VALUE_O_TEL" => $row["o_tel"],
                    "VALUE_O_FAX" => $row["o_fax"],
                    "VALUE_O_CELLPHONE" => $row["o_cellphone"],
                    "VALUE_O_ZIP" => $row["o_zip"],
                    "VALUE_O_ADDRESS" => $row["o_address"],
                    "VALUE_O_EMAIL" => $row["o_email"],
                    "VALUE_O_ADD_NAME" => $row["o_add_name"],
                    "VALUE_O_ADD_TEL" => $row["o_add_tel"],
                    "VALUE_O_ADD_CELLPHONE" => $row["o_add_cellphone"],
                    "VALUE_O_ADD_ADDRESS" => $row["o_add_address"],
                    "VALUE_O_ADD_MAIL" => $row["o_add_mail"],
                    "VALUE_O_CONTENT" => $row["o_content"],
                    "VALUE_O_SHIP_PRICE" => $row["o_ship_price"],
                    "VALUE_O_FEE_PRICE" => $row["o_fee_price"],
                    "VALUE_O_MINUS_PRICE" => 0-$row["o_minus_price"],
                    "VALUE_O_SUBTOTAL_PRICE" => $row["o_subtotal_price"],
                    "VALUE_O_TOTAL_PRICE" => $row["o_total_price"],
                    "VALUE_O_STATUS" => $ws_array["order_status"][$row["o_status"]],
                    "VALUE_O_PAYMENT_TYPE"=>$ws_array["payment_type"][$row["o_payment_type"]],
                    "VALUE_O_SHIPPMENT_TYPE" => $ws_array["shippment_type"][$row['o_shippment_type']],
                    "VALUE_O_INVOICE_TYPE" => $ws_array["invoice_type"][$row['o_invoice_type']],
                    "VALUE_O_ATM_LAST5" => $row["o_atm_last5"],
                    "VALUE_O_ARRIVAL_TIME" => date("Y年m月d日",$dts),
                    "VALUE_O_COMPANY_NAME" => $row["o_company_name"],
                    "VALUE_O_INVOICE_NAME" => $row["o_invoice_name"],
                    "VALUE_O_INVOICE_VAT" => $row["o_invoice_vat"],
                    "VALUE_O_INVOICE_TEXT" => $row["o_invoice_text"],
                    "VALUE_O_STATUS" => $ws_array["order_status"][$row['o_status']],
                    "VALUE_INVOICE_TYPE" => $ws_array['invoice_type'][$row['o_invoice_type']],
                ));
                require_once "../cart/AllpayInfo.php";
                //信用卡付款資訊
                $cardInfo = App::getHelper('dbtable')->allpay_order->getData($row['o_id'])->getDataRow();
                if($cardInfo){
                    $this->_tpl->newBlock("CART_INFO");
                    foreach($cardInfo as $k => $v){
                        $this->_tpl->assign(array(
                            "Msg_". $k => AllpayInfo::$map[$k],
                            "Val_". $k => $v,
                        ) );
                    }
                }
                //非信用卡付款資訊
                $cardInfo = App::getHelper('dbtable')->allpay_payinfo->getData($row['o_id'])->getDataRow();
                if($cardInfo){
                    $this->_tpl->newBlock("NOTCART_INFO");
                    foreach($cardInfo as $k => $v){
                        $this->_tpl->assign(array(
                            "Msg_". $k => AllpayInfo::$map[$k],
                            "Val_". $k => $v,
                        ) );
                    }
                }
                //訂購產品列表
                $sql="select * from ".$db->prefix("order_items")." where o_id='".$_REQUEST["o_id"]."' and del='0' ";
                $selectrs = $db->query($sql);
                $total_price=0;
                $i=0;
                while($row = $db->fetch_array($selectrs,1)){
                    $i++;
                    $sub_total_price = $row["price"] * $row["amount"];
                    $total_price = $total_price+$sub_total_price;
                    $this->_tpl->newBlock( "ORDER_ITEMS_LIST" );
                    $this->_tpl->assign( array(
                        "VALUE_P_ID"  => $row["p_id"],
                        "VALUE_P_NAME" => $row["p_name"],
                        "VALUE_P_SELL_PRICE" => $row["price"],
                        "VALUE_P_AMOUNT" => $row["amount"],
                        "VALUE_P_SUBTOTAL_PRICE"  => $sub_total_price,
                        "VALUE_P_SERIAL"  => $i,
                    ));
                }
            }
        }
    }   
    
    function member_detail($m_id){
        global $TPLMSG;
        $db = App::getHelper('db');
        if($member = App::getHelper('dbtable')->member->getData($m_id)->getDataRow()){
            foreach($member as $k => $v){
                if($k=="mc_id"){
                    $sql = "select group_concat(mc_subject) as mc_subject from ".$db->prefix("member_cate")." as a inner join ".$db->prefix("member")." as b on find_in_set(a.mc_id,b.mc_id) where b.m_id='".$member['m_id']."' group by b.m_id";
                    list($mc_subject) = $db->query_firstRow($sql,0);
                    $this->_tpl->assignGlobal('VALUE_MC_SUBJECT' , $mc_subject);
                }elseif($k=="m_status"){
                    $v = App::getHelper('main')->multi_map_value(App::defaults()->default_status,$v);
                }elseif($k=="m_epaper_status"){
                    $v = App::getHelper('main')->multi_map_value(App::defaults()->yesno_status,$v);
                }elseif($k=="m_birthday"){
                    $ts = strtotime($v);
                    $v = ($ts)?date("Y-m-d",$ts):'';
                }elseif($k=='m_fname'){
                    $name_template = $TPLMSG['MEMBER_NAME_SET_'.App::configs()->ws_module->ws_contactus_s_style];
                    $m_name = sprintf($name_template,$member['m_fname'],$member['m_lname']);
                    $this->_tpl->assignGlobal('VALUE_M_NAME' , $m_name);
                }
                $this->_tpl->assignGlobal("VALUE_".strtoupper($k),$v);
            }
        }else{
            App::getHelper('main')->js_notice("無此會員",App::configs()->manage_root . "member.php");
        }
    }
}
//ob_end_flush();
?>