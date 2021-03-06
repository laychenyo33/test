<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])  || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_order"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$order = new ORDER;
class ORDER{
    protected $activateStockChecker;
    function ORDER(){
        global $db,$cms_cfg,$tpl;
        $this->activateStockChecker = App::configs()->ws_module->ws_products_stocks;
        switch($_REQUEST["func"]){
            case "ajax_del_order_item":
                $this->ajax_del_order_items();            
                break;            
            case "o_ex"://匯出新訂單
                if($_GET['act']){
                    $this->export_order();
                }else{
                    $this->current_class="OE";
                    $this->ws_tpl_file = "templates/ws-manage-export-form-tpl.html";
                    $this->ws_load_tp($this->ws_tpl_file);
                    $tpl->newBlock("JS_MAIN");
                    $this->export_form();
                    $this->ws_tpl_type=1;
                }
                break;
            case "o_ex2"://匯出新訂單
                $this->export_order2();
                break;
            case "o_ex3"://匯出美安訂單
                $this->export_order3();
                break;
            case "o_list"://訂單列表
                $this->current_class="O";
                $this->ws_tpl_file = "templates/ws-manage-order-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_JQ_UI");
                $tpl->newBlock("DATEPICKER_SCRIPT");
                if($cms_cfg["ws_module"]["ws_vaccount"]==1) {
                    $this->check_atm();//檢查新匯款紀錄
                }
                $this->order_list();
                $this->ws_tpl_type=1;
                break;
            case "o_replace"://訂單更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->order_replace();
                $this->ws_tpl_type=1;
                break;
            case "o_reply"://訂單檢視及更新狀態
                $this->current_class="O";
                $this->ws_tpl_file = "templates/ws-manage-order-reply-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                if($cms_cfg['ws_module']['ws_address_type']=='tw')App::getHelper ('main')->res_init("zone",'box');
                $this->order_reply_form();
                $this->ws_tpl_type=1;
                break;
            case "o_del"://訂單刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->order_del();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //訂單列表
                $this->current_class="O";
                $this->ws_tpl_file = "templates/ws-manage-order-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_JQ_UI");
                $tpl->newBlock("DATEPICKER_SCRIPT");
                if($cms_cfg["ws_module"]["ws_vaccount"]==1) {
                    $this->check_atm();//檢查新匯款紀錄
                }
                $this->order_list();
                $this->ws_tpl_type=1;
                break;
        }
        if($this->ws_tpl_type){
            $tpl->printToScreen();
        }
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$db,$main;
        $tpl = new TemplatePower( $cms_cfg['manage_all_tpl'] );
        $tpl->assignInclude( "LEFT", $cms_cfg['manage_left_tpl']);
        $tpl->assignInclude( "TOP_MENU", $cms_cfg['manage_top_menu_tpl']);
        $tpl->assignInclude( "MAIN", $ws_tpl_file);
        $tpl->prepare();
        $tpl->assignGlobal("TAG_".$this->current_class."_CURRENT","class=\"current\"");
        $tpl->assignGlobal("CSS_BLOCK_ORDER","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }
//訂單--列表================================================================
    function order_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //顯示ATM匯款標題
        if($cms_cfg["ws_module"]["ws_vaccount"]) {
            $tpl->newBlock("TITLE_ATM_TRANSFER");
        }
        //訂單列表
        $sql="select * from ".$db->prefix("order")." where o_id > '0'";
        $searchfields = new searchFields_order();
        //附加條件
        $and_str=array();
        if(!$_GET['showdel']){
            $and_str[] = "del='0'";
        }
        $and_str = $searchfields->find_multiple_search_value($and_str);
        $sql .= ($and_str?" and ".$and_str:"")." order by o_modifydate desc ";
        //$sql .= $and_str." order by o_modifydate desc ";
        //取得總筆數
        $selectrs = $db->query($sql);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結
        parse_str($_SERVER['QUERY_STRING'],$q);
        foreach($q as $k=>$v){
            if($k!='nowp' && $k!='jp'){
                $qs[] = sprintf("%s=%s",$k,$v);
            }
        }
        $func_str= $_SERVER['PHP_SELF']."?".implode('&',(array)$qs);
        //分頁且重新組合包含limit的sql語法
        $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("VALUE_TOTAL_BOX" => $rsnum,
                                  "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                  "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],
                                  'TAG_SEARCH_FIELD' => $searchfields->list_multiple_search_fields(),
        ));
        $i = $main->get_pagination_offset($cms_cfg["op_limit"]);
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "ORDER_LIST" );
            if($i%2){
                $rowClass[] = 'altrow';
            }
            if($row['del']){
                $rowClass[] = 'del';
            }
            if($rowClass){
                $tpl->assign("TAG_TR_CLASS","class='".implode(" ",$rowClass)."'");
            }
            $tpl->assign( array(
                "VALUE_O_ID"  => $row["o_id"],
                "VALUE_O_NAME" => $row["o_name"],
                "VALUE_O_CREATEDATE" => $row["o_createdate"],
                "VALUE_O_MODIFYDATE" => $row["o_modifydate"],
                "VALUE_O_TOTAL_PRICE" => $row["o_total_price"],
                "VALUE_O_STATUS" => $ws_array["order_status"][$row["o_status"]],
                "VALUE_O_SERIAL" => $i, 
                "STATUS_CLASS"   => "order_status_".$row['o_status'],
            ));

            //顯示ATM匯款狀態
            if($cms_cfg["ws_module"]["ws_vaccount"]) {
                $tpl->newBlock("ATM_TRANSFER_STATE");
                $tpl->assign( array(
                    "VALUE_O_REMIT_STATUS" => ($row["o_payment_type"] == 1) ? $row["o_remit_status"] ? "完成匯款":"<font color=\"#ff0000\">未匯款</font>" :"",
                    "VALUE_O_CURAMT" => $row["o_curamt"],
                    "VALUE_O_TRN_TIME" => $row["o_trn_time"] 
                ));
            }
        }
    }

//訂單--刪除--資料刪除可多筆處理================================================================
    function order_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["o_id"]){
            $cu_id=array(0=>$_REQUEST["o_id"]);
        }else{
            $cu_id=$_REQUEST["id"];
        }
        if(!empty($cu_id)){
            foreach($cu_id as $k=>$v){
                $cu_id[$k] = sprintf("'%s'",$v);
            }
            $cu_id_str = implode(",",$cu_id);
            //刪除勾選的訂單
//            $sql="delete from ".$cms_cfg['tb_prefix']."_order where o_id in (".$cu_id_str.")";
//            $rs = $db->query($sql);
//            $sql="delete from ".$cms_cfg['tb_prefix']."_order_items where o_id in (".$cu_id_str.")";
            $sql = "UPDATE ".$db->prefix("order")." AS o INNER JOIN ".$db->prefix("order_items")." AS oi ON o.o_id = oi.o_id SET o.del = '1' , oi.del = '1' WHERE o.o_id in(".$cu_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$_SERVER['PHP_SELF']."?func=o_list&cuc_id=".$_REQUEST["cuc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //訂單回覆--表單================================================================
    function order_reply_form(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['MODIFY'],
                                  "MSG_PRODUCT_SPECIAL_PRICE" => $TPLMSG['PRODUCT_PRICE'],
                                  "MSG_DISCOUNT" => $TPLMSG['QUANTITY_DISCOUNT'],
        ));
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        if($cms_cfg["ws_module"]['ws_delivery_timesec']){ //是否顯示配送區間
            $tpl->newBlock("DELIVERY_TIMESEC");
        }
        //帶入要回覆的訂單資料
        if(!empty($_REQUEST["o_id"])){
            $sql="select * from ".$db->prefix("order")." where o_id='".$_REQUEST["o_id"]."'";
            if(!$_GET['showdel']){
                $sql .= " and del='0'";
            }
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $dts = strtotime($row['o_arrival_time']);
                //配送地區
                $source_of_shipment = Model_Shipprice::getShipmentSource();                
                $tpl->assignGlobal( array("VALUE_M_ID"  => $row["m_id"],
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
                                          "VALUE_O_ADD_ZIP" => $row["o_add_zip"],
                                          "VALUE_O_ADD_ADDRESS" => $row["o_add_address"],
                                          "VALUE_O_ADD_MAIL" => $row["o_add_mail"],
                                          "VALUE_O_CONTENT" => $row["o_content"],
                                          "VALUE_O_SHIP_PRICE" => $row["o_ship_price"],
                                          "VALUE_O_FEE_PRICE" => $row["o_fee_price"],
                                          "VALUE_O_MINUS_PRICE" => $row["o_minus_price"],
                                          "VALUE_O_SUBTOTAL_PRICE" => $row["o_subtotal_price"],
                                          "VALUE_O_TOTAL_PRICE" => $row["o_total_price"],
                                          "VALUE_O_STATUS" => $ws_array["order_status"][$row["o_status"]],
                                          "VALUE_O_PAYMENT_TYPE"=>$ws_array["payment_type"][$row["o_payment_type"]],
                                          "VALUE_O_SHIPMENT_TYPE" => $source_of_shipment[$row['o_shipment_type']],
                                          "VALUE_O_INVOICE_TYPE" => $ws_array["invoice_type"][$row['o_invoice_type']],
                                          "VALUE_O_ARRIVAL_TIME" => date("Y年m月d日",$dts),
                                          "VALUE_O_COMPANY_NAME" => $row["o_company_name"],
                                          "VALUE_O_INVOICE_NAME" => $row["o_invoice_name"],
                                          "VALUE_O_INVOICE_VAT" => $row["o_invoice_vat"],
                                          "VALUE_O_INVOICE_TEXT" => $row["o_invoice_text"],
                                          "ORIGIN_STATUS" => $row["o_status"],
                ));
                //一般atm匯款記錄
                if($row['o_payment_type']==1){
                    $tpl->newBlock("ATM_LAST5");
                    $tpl->assign(array(
                        "VALUE_O_ATM_LAST5" => $row["o_atm_last5"],
                    ));
                }
                //訂單狀態
                foreach($ws_array["order_status"] as $key =>$value){
                    $i++;
                    $tpl->newBlock( "ORDER_STATUS_LIST" );
                    $tpl->assign( array(
                        "VALUE_O_STATUS_SUBJECT"  => $value,
                        "VALUE_O_STATUS" => $key,
                        "VALUE_O_SERIAL" => $i,
                        "STATUS_CLASS"   => "order_status_".$key,
                        "TAG_DISABLED"   => ($row['o_status']==3 && $key!=9 || $row['o_status']>3)?"disabled":'',
                    ));
                    if($i%4==0){
                        $tpl->assign("TAG_BR","<br>");
                    }
                    if($key==$row["o_status"]){
                        $tpl->assign("TAG_STATUS_CHECKED","checked");
                    }
                }
                //付款狀態
                $main->multiple_radio("paid_status",$ws_array["order_paid_status"],$row['o_paid'],$tpl);
                //發票尉類型
                App::getHelper('main')->multiple_radio("invoice_type",$ws_array['invoice_type'],$row['o_invoice_type'],$tpl);
                require_once "AllpayInfo.php";
                //信用卡付款資訊
                $cardInfo = App::getHelper('dbtable')->allpay_order->getData($row['o_id'])->getDataRow();
                if($cardInfo){
                    $tpl->newBlock("CART_INFO");
                    foreach($cardInfo as $k => $v){
                        $tpl->assign(array(
                            "Msg_". $k => AllpayInfo::$map[$k],
                            "Val_". $k => $v,
                        ) );
                    }
                }
                //非信用卡付款資訊
                $cardInfo = App::getHelper('dbtable')->allpay_payinfo->getData($row['o_id'])->getDataRow();
                if($cardInfo){
                    $tpl->newBlock("NOTCART_INFO");
                    foreach($cardInfo as $k => $v){
                        $tpl->assign(array(
                            "Msg_". $k => AllpayInfo::$map[$k],
                            "Val_". $k => $v,
                        ) );
                    }
                }
                //地址欄位格式
                if($cms_cfg['ws_module']['ws_address_type']=='tw'){
                    $tpl->newBlock("TW_ADDRESS");
                }else{
                    $tpl->newBlock("SINGLE_ADDRESS");
                }                 
                //訂購產品列表
                $sql="select * from ".$cms_cfg['tb_prefix']."_order_items where o_id='".$_REQUEST["o_id"]."'";
                if(!$_GET['showdel']){
                    $sql .= " and del='0' ";
                }
                $selectrs = $db->query($sql);
                $total_price=0;
                $i=0;
                $cart_field_nums = 5;
                if($cms_cfg['ws_module']['ws_cart_spec']){
                    $tpl->newBlock("SPEC_TITLE");
                    $cart_field_nums++;
                }
                $order_status = $row['o_status'];
                if($order_status<3){
                    $tpl->newBlock("DEL_ORDER_ITEM_BUTTON");
                    $tpl->newBlock("CHECK_TO_DEL_TITLE");
                    $cart_field_nums++;
                }
                $tpl->assignGlobal("CART_FIELD_NUMS",$cart_field_nums);         
                while($row = $db->fetch_array($selectrs,1)){
                    $i++;
                    $sub_total_price = round($row["price"] * $row["amount"] * $row['discount']);
                    $total_price = $total_price+$sub_total_price;
                    $tpl->newBlock( "ORDER_ITEMS_LIST" );
                    $tpl->assign( array("VALUE_P_ID"  => $row["p_id"],
                                        "VALUE_P_NAME" => $row["p_name"],
                                        "VALUE_P_SELL_PRICE" => $row["price"],
                                        "VALUE_P_AMOUNT" => $row["amount"],
                                        "TAG_QUANTITY_DISCOUNT" => ($row['discount']<1)?$row['discount']:'',
                                        "VALUE_P_SUBTOTAL_PRICE"  => $sub_total_price,
                                        "VALUE_P_SERIAL"  => $i,
                    ));
                    if($cms_cfg['ws_module']['ws_cart_spec']){
                        $tpl->newBlock("SPEC_FIELD");
                        $tpl->assign("VALUE_SPEC",$row["spec"]);
                    }                     
                    if($order_status<3){
                        $tpl->newBlock("CHECK_TO_DEL_FIELD");
                        $tpl->assign(array(
                            "VALUE_OI_ID" => $row["oi_id"],
                        ));
                    }                    
                }
            }else{
                header("location: ".$_SERVER['PHP_SELF']."?func=o_list");
            }
        }
    }
//訂單回覆--資料更新================================================================
    function order_replace(){
        global $tpl,$TPLMSG;
//        $sql="update ".$cms_cfg['tb_prefix']."_order set o_status='".$_REQUEST["o_status"]."' , o_modifydate='".date("Y-m-d H:i:s")."' where o_id='".$_REQUEST["o_id"]."'";
//        $rs = $db->query($sql);
        App::getHelper('dbtable')->order->writeData($_POST);
        $db_msg = App::getHelper('dbtable')->order->report();
        if ( $db_msg == "" ) {
            if($_REQUEST["o_status"] == 2){
                $this->mail_delivery_notice($_REQUEST["o_id"]); //寄送出貨通知信                
            }elseif($_REQUEST["o_status"] == 3){
                if($this->activateStockChecker){
                    $order_prods = App::getHelper('dbtable')->order_items->getDataList("o_id='{$_POST['o_id']}'");
                    foreach($order_prods as $prod){
                        App::getHelper('session')->getModule("cart")->stockchecker->runStocks($prod['p_id'],$prod['ps_id'],$prod['amount']);
                    }
                }
            }elseif($_REQUEST["o_status"] == 9 && $_REQUEST['origin_status']==3 && $this->activateStockChecker){ //如果完成訂單取消，回存產品庫存
                $order_items = App::getHelper('dbtable')->order_items->getDataList("o_id='".$_REQUEST["o_id"]."'");
                if($order_items){
                    foreach($order_items as $oItem){
                        App::getHelper('session')->cart->stockChecker->returnStocks($oItem['p_id'],$oItem['ps_id'],$oItem['amount']);
                    }
                }
            }
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$_SERVER['PHP_SELF']."?func=o_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
            $this->goto_target_page($goto_url,2);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
    //顯示訊息並重新導向
    function goto_target_page($url,$sec=0){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }

    //資料處理
    function data_processing(){
        switch ($_REQUEST["process_type"]){
            case "del":
                $this->order_del();
                break;
        }
    }

    //寄送出貨通知信
    function mail_delivery_notice($o_id) {
    	global $db,$TPLMSG,$cms_cfg;
//        $this->ws_tpl_file = "templates/ws-manage-mail-tpl.html";
//        $tpl = new TemplatePower( $this->ws_tpl_file );
//        $tpl->prepare();
        $tpl = App::getHelper('main')->get_mail_tpl("delivery-notification");
        $sql = "select * from ".$db->prefix("order")." where o_id='".$o_id."'";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        if($row){
            $tpl->newBlock( "DELIVERY_NOTICE" );
            $tpl->assign( array(
                "MSG_DELIVERY_ID" => $TPLMSG['DELIVERY_ID'],
                "MSG_DELIVERY_TOTAL_PRICE" => $TPLMSG['DELIVERY_TOTAL_PRICE'],
                "MSG_DELIVERY_DATE" => $TPLMSG['DELIVERY_DATE'],
                "VALUE_DELIVERY_ID" => $row['o_id'],
                "VALUE_DELIVERY_TOTAL_PRICE" => $row['o_total_price'],
                "VALUE_DELIVERY_DATE" => date("Y-m-d H:i:s") 
            ));
            $tpl->assignGlobal( "VALUE_TERM" , $TPLMSG['DELIVERY_NOTICE']);
            $mail_content=$tpl->getOutputContent();  
            App::getHelper('main')->ws_mail_send_simple(App::getHelper('session')->sc_email,$row['o_email'],$mail_content,$TPLMSG["DELIVERY_NOTICE"]);
        }
    }

    function mail_send($from,$to,$mail_content,$mail_subject){
        global $TPLMSG,$cms_cfg;
        $from_email=explode(",",$from);
        $from_name=(trim($_SESSION[$cms_cfg['sess_cookie_name']]["sc_company"]))?$_SESSION[$cms_cfg['sess_cookie_name']]["sc_company"]:$from_email[0];
        //寄給送信者
        $MAIL_HEADER   = "MIME-Version: 1.0\n";
        $MAIL_HEADER  .= "Content-Type: text/html; charset=\"utf-8\"\n";
        $MAIL_HEADER  .= "From: =?UTF-8?B?".base64_encode($from_name)."?= <".$from_email[0].">\n";
        $MAIL_HEADER  .= "X-Priority: 1\n";

        //$MAIL_HEADER   = "From: ".$from_name."<".$from_email[0].">"."\r\n";
        //$MAIL_HEADER  .= "Reply-To: ".$from_name."<".$from_email[0].">\r\n";
        //$MAIL_HEADER  .= "Return-Path: ".$from_name."<".$from_email[0].">\r\n";    // these two to set reply address
        //$MAIL_HEADER  .= "Content-Type: text/html; charset=\"utf-8\"\n";
        //$MAIL_HEADER  .= "X-Priority: 1\r\n";
        $MAIL_HEADER  .= "Message-ID: <".time()."-".$from_email[0].">\r\n";
        $MAIL_HEADER  .= "X-Mailer: PHP v".phpversion()."\r\n";          // These two to help avoid spam-filters
        
        $mail_subject = "=?UTF-8?B?".base64_encode($mail_subject)."?=";
        $to_email = explode(",",$to);
        for($i=0;$i<count($to_email);$i++){
            mail($to_email[$i], $mail_subject, $mail_content,$MAIL_HEADER);
        }
				return true;
    }

    //檢查匯款
    function check_atm(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $xml_dir = "../atm/";
        $xml_pack = "../atm_pack/";
        if(is_dir($xml_dir)) {
            if($dh = opendir($xml_dir)) {
                $i = 0;
                while(($xml_file = readdir($dh)) !== false) {
                    if(filetype($xml_dir.$xml_file) == "file") {
                        $i++;
                        $xml_str = file_get_contents($xml_dir.$xml_file);
                        $str = str_replace("<?xml version=\"1.0\" encoding=\"BIG5\"?>","",$xml_str);
                        $xml = simplexml_load_string($str);
                        foreach($xml as $text) {
                            foreach($text as $key => $data) {
                                $atm[$key] = $data;
                            }
                        }
                        //比對資料庫
                        $sql="select * from ".$db->prefix("order")." where o_virtual_account='".$atm["RCPTId"]."'";
                        $selectrs = $db->query($sql);
                        if($db->numRows($selectrs)) {
                            $row = $db->fetch_array($selectrs,1);
                            $atm["CurAmt"] = sprintf("%d", $atm["CurAmt"]);
                            if($row["o_total_price"] == $atm["CurAmt"]) {
                                $sql="update ".$db->prefix("order")." set o_curamt='".$atm["CurAmt"]."' , o_trn_time='".$atm["TrnDt"]." ".$atm["TrnTime"]."' , o_remit_status='1' where o_virtual_account='".$atm["RCPTId"]."'";
                                $rs = $db->query($sql);
                                $db_msg = $db->report();
                                if($db_msg != "") {
                                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                                }elseif(!file_exists($xml_pack)){
                                    mkdir($xml_pack);
                                    copy($xml_dir.$xml_file, $xml_pack.$xml_file);
                                    unlink($xml_dir.$xml_file);
                                }else{
                                    copy($xml_dir.$xml_file, $xml_pack.$xml_file);
                                    unlink($xml_dir.$xml_file);
                                }
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }
    }

    //輸出訂單
    function export_form(){
        
    }
    
    function export_order(){
        global $db,$cms_cfg,$ws_array;
        if(isset($_POST['exportAll'])){
            $type = "exportAll";
        }elseif(isset($_POST['exportNew'])){
            $type = "exportNew";
        }else{
            throw new Exception('no proper type of export order');
        }
        $exportData = $this->get_export_order_data($type);
        if($exportData){
            require_once "../class/phpexcel/PHPExcel.php";
            $xlsexpotor = new XLSExportor();
            $xlsexpotor->setTitle($exportData['title']);
            $xlsexpotor->setData($exportData['data']);
            $xlsexpotor->setFilename($exportData['filename']);
//            $xlsexpotor->setFontSize(10);
            $xlsexpotor->export();
        }
    }
    
    function export_order2(){
        global $db,$cms_cfg,$ws_array;
        $exportData = $this->get_export_order_data("exportAll");
        if($exportData['data']){
            require_once "../class/phpexcel/PHPExcel.php";
            $xlsexpotor = new XLSExportor();
            $xlsexpotor->setTitle($exportData['title']);
            $xlsexpotor->setData($exportData['data']);
            $xlsexpotor->setFilename($exportData['filename']);
//            $xlsexpotor->setFontSize(10);
            $xlsexpotor->export();
        }else{
            App::getHelper('main')->js_notice('無匯出資料', $cms_cfg['manage_root'].'order.php?func=o_ex');
        }
    }    
    
    //取得輸出的訂單資料
    function get_export_order_data($type){
        global $ws_array,$cms_cfg;
        $db = App::getHelper('db');
        $exportData = array(
            'exportAll' => array(
                'title' => array(
                    0 => array('data'=>"訂單編號",'width'=>16.5),
                    1 => array('data'=>"訂單狀態",'width'=>16.5),
                    2 => array('data'=>"付款方式",'width'=>22.5),
                    3 => array('data'=>"配送日期",'width'=>16.5),
                    4 => array('data'=>"備註",'width'=>16.5),
                    5 => array('data'=>"公司名稱",'width'=>16.5),
                    6 => array('data'=>"統一編號",'width'=>16.5),
                    7 => array('data'=>"傳真",'width'=>16.5),
                    8 => array('data'=>"會員編號",'width'=>16.5),
                    9 => array('data'=>"訂購者姓名",'width'=>16.5),
                    10 => array('data'=>"訂購者電話",'width'=>16.5),
                    11=> array('data'=>"訂購者手機",'width'=>16.5),
                    12 => array('data'=>"訂購者區號",'width'=>16.5),
                    13 => array('data'=>"訂購者住址",'width'=>16.5),
                    14 => array('data'=>"訂購者Email",'width'=>28),
                    15 => array('data'=>"收件者姓名",'width'=>16.5),
                    16 => array('data'=>"收件者電話",'width'=>16.5),
                    17 => array('data'=>"收件者手機",'width'=>16.5),
                    18 => array('data'=>"收件者地址",'width'=>16.5),
                    19 => array('data'=>"收件者Email",'width'=>28),
                    20 => array('data'=>"小計",'width'=>10),
                    21 => array('data'=>"手續費",'width'=>10),
                    22 => array('data'=>"運費",'width'=>10),
                    23 => array('data'=>"總價",'width'=>10),
                    24 => array('data'=>"發票",'width'=>16.5),
                    25 => array('data'=>"訂購商品",'width'=>25),
                ),
                'filename' => "full_order_".date("Y-m-d"),
                'data' => array(),
            ),
            'exportNew' => array(
                'title'    => array(
                    0 => array("data"=>'訂單編號','width'=>16.5),
                    1 => array("data"=>'訂貨人','width'=>16.5),
                    2 => array("data"=>'訂貨人電話','width'=>16.5),
                    3 => array("data"=>'訂貨人手機','width'=>16.5),
                    4 => array("data"=>'收件人','width'=>16.5),
                    5 => array("data"=>'收件人電話','width'=>16.5),
                    6 => array("data"=>'收件人手機','width'=>16.5),
                    7 => array("data"=>'收件人E-mail','width'=>28),
                    8 => array('data'=>'收件人住址','width'=>50),
                    9 => array("data"=>'訂購產品','width'=>28),
                ),
                'filename' => "new_order_".date("Y-m-d"),
                'data' => array(),
            )
        );
        switch($type){
            case "exportAll":
                //附加條件
                $searchfields = new searchFields_order();
                $and_str = $searchfields->find_multiple_search_value($and_str);
                $sql = "select o_id,o_status,o_payment_type,o_arrival_time,o_content,o_company_name,o_invoice_vat,o_fax,m_id,o_name,o_tel,o_cellphone,o_zip,o_address,o_email,o_add_name,o_add_tel,o_add_cellphone,o_add_address,o_add_mail,o_subtotal_price,o_fee_price,o_ship_price,o_total_price,o_invoice_type from ".$db->prefix("order")." where  del='0' ".($and_str?' and '.$and_str:'')." order by o_createdate ";
                $res = $db->query($sql,true);
                while($row = $db->fetch_array($res,0)){
                    $sql = "select * from ".$db->prefix("order_items")." where o_id='".$row[0]."' and del='0' order by oi_id ";
                    $res2 = $db->query($sql,true);
                    $row[0] = array('data'=>$row[0],'type'=>'s');
                    $row[1] = $ws_array["order_status"][$row[1]];
                    $row[2] = $ws_array["payment_type"][$row[2]];
                    $row[3] = ($ts=strtotime($row[3]))?date("Y-m-d",$ts):"";
                    $row[22] = $row[22]<0?"運費另議":$row[22];
                    $row[24] = $ws_array['invoice_type'][$row[24]];
                    $tmp = array();
                    while($prod = $db->fetch_array($res2,1)){
                        $tmp[] = sprintf("%s*%d",$prod['p_name'],$prod['amount']);
                    }
                    $row[] = array('data'=> implode("\n",$tmp),'type'=>'s','wrap'=>true);
                    $exportData[$type]['data'][] = $row;
                }
                break;
            case "exportNew":
                $sql = "select o_id,o_name,o_tel,o_cellphone,o_add_name,o_add_tel,o_add_cellphone,o_add_mail,o_add_address from ".$db->prefix("order")." where o_status='0' and del='0' order by o_createdate ";
                $res = $db->query($sql,true);
                while($row = $db->fetch_array($res,0)){
                    $sql = "select * from ".$db->prefix("order_items")." where o_id='".$row[0]."' and del='0' order by oi_id ";
                    $res2 = $db->query($sql,true);
                    $row[0] = array('data'=>$row[0],'type'=>'s');
                    $tmp = array();
                    while($prod = $db->fetch_array($res2,1)){
                        if($prod['spec']){
                            $tmp[] = sprintf("%s(%s)*%d",$prod['p_name'],$prod['spec'],$prod['amount']);
                        }else{
                            $tmp[] = sprintf("%s*%d",$prod['p_name'],$prod['amount']);
                        }
                    }
                    $row[] = array('data'=> implode("\n",$tmp),'type'=>'s','wrap'=>true);
                    $exportData[$type]['data'][] = $row;
                }
                break;
        }
        return $exportData[$type];
    }    
    
    function export_order3(){
        Model_Order_Rid::output();
    }
    
    function ajax_del_order_items(){
        $result['code'] = 0;
        if($_POST['toDelItem']){
            foreach($_POST['toDelItem'] as $oi_id){
                $oItem = App::getHelper('dbtable')->order_items->getData($oi_id)->getDataRow();
                if($oItem){
                    $orderData = App::getHelper('dbtable')->order->getData($oItem['o_id'])->getDataRow('o_id,o_status,o_plus_price,o_charge_fee,o_extra_price');
                    if($orderData['o_status']==3){
                        //回存產品庫存
                        App::getHelper('session')->cart->stockChecker->returnStocks($oItem['p_id'],$oItem['ps_id'],$oItem['amount']);                    
                    }
                    App::getHelper('dbtable')->order_items->del($oItem['oi_id']);
                    $oItemList = App::getHelper('dbtable')->order_items->getDataList("o_id='".$oItem['o_id']."' and del='0'");
                    //如果訂單項目所屬訂單還有其他項目，修改訂單價格
                    if($oItemList){
                        $subtotal_price = 0;
                        foreach($oItemList as $oItem){
                            $subtotal_price += round($oItem["price"] * $oItem["amount"] * $oItem['discount']);
                        }
                        $orderData['o_subtotal_price'] = $subtotal_price;
                        $orderData['o_total_price'] = $orderData['o_subtotal_price'] + $orderData['o_plus_price'] + $orderData['o_charge_fee'] + $orderData['o_extra_price']; 
                        App::getHelper('dbtable')->order->writeData($orderData);
                        $result['code'] = 1;
                        $result['msg'] = "已刪除指定訂單項目";
                    }else{ //沒有其他項目，刪除整筆訂單
                        $result['code'] = 2;
                        $result['msg'] = "此筆訂單已沒有其他項目，已刪除整筆訂單";
                        App::getHelper('dbtable')->order->del($oItem['o_id']);
                    }
                }
            }
        }else{
            $result['code'] = -1;//沒有傳oi_id
            $result['msg'] = "沒有傳oi_id";
        }
        echo json_encode($result);
    }    
}
//ob_end_flush();
?>