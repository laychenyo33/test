<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$member = new MEMBER;
class MEMBER{
    function MEMBER(){
        global $db,$cms_cfg,$tpl,$main;
        $this->m_id=$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ID"];
        $this->contact_s_style = $cms_cfg['ws_module']['ws_contactus_s_style'];
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        switch($_REQUEST["func"]){
            case "ajax":
                $this->ajax();
                break;
            case "activate":
                $this->ws_tpl_file = "templates/ws-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->account_activate();
                $this->ws_tpl_type=1;
                break;
            case "m_zone"://會員專區
                if(empty($this->m_id)){
                    App::getHelper('session')->TAG_RETURN_URL = $_SERVER['REQUEST_URI'];
                    header("Location: member.php");
                    die();
                }
                $this->ws_tpl_file = "templates/ws-member-zone-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->member_zone();
                $this->ws_tpl_type=1;
                break;
            case "m_add"://會員管理新增
                if($this->m_id){
                    header("location:member.php?func=m_mod");
                    die();
                }
                if(App::configs()->ws_module->ws_member_social_login && $_GET['tool']){
                    $this->ws_tpl_file = "templates/ws-member-{$_GET['tool']}-form-tpl.html";
                }else{
                    $this->ws_tpl_file = "templates/ws-member-form-tpl.html";
                }
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                if(!isset($_SESSION[$cms_cfg['sess_cookie_name']]['JOIN_MEMBER'])){
                    $_SESSION[$cms_cfg['sess_cookie_name']]['JOIN_MEMBER'] = time();
                }                
                $tpl->newBlock("JQUERY_UI_SCRIPT");
                $tpl->newBlock("DATEPICKER_SCRIPT");
                if($cms_cfg['ws_module']['ws_address_type']=='tw')$main->res_init("zone",'box');
                $this->member_form("add");
                $this->ws_tpl_type=1;
                break;
            case "m_mod"://會員管理修改
                if(empty($this->m_id)){
                    header("Location: member.php?func=m_add");
                    die();
                }
                $this->ws_tpl_file = "templates/ws-member-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JQUERY_UI_SCRIPT");
                $tpl->newBlock("DATEPICKER_SCRIPT");
                if($cms_cfg['ws_module']['ws_address_type']=='tw')$main->res_init("zone",'box');
                $this->member_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "m_forget"://忘記密碼
                $this->member_forget_password();
                break;
            case "m_sps"://密碼寄送完成顯示訊息
                $this->ws_tpl_file = "templates/ws-member-forget-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->member_send_password_success_str();
                $this->ws_tpl_type=1;
                break;
            case "mm_list"://會員訊息公告
                if(empty($this->m_id)){
                    header("Location: member.php");
                    die();
                }
                $this->ws_tpl_file = "templates/ws-member-message-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->member_message_list();
                $this->ws_tpl_type=1;
                break;
            case "o_del"://取消訂單
                $this->ws_tpl_file = "templates/ws-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->order_del();
                $this->ws_tpl_type=1;
                break;
            case "o_replace"://編輯匯款帳號
                $this->ws_tpl_file = "templates/ws-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->order_replace();
                $this->ws_tpl_type=1;
                break;            
            case "m_replace"://會員管理更新資料(replace)
                $this->ws_tpl_file = "templates/ws-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->member_replace();
                $this->ws_tpl_type=1;
                break;
            default:    //會員專區
                if(!empty($this->m_id)){
                    header("Location: member.php?func=m_mod");
                    die();
                }
                $this->ws_tpl_file = "templates/ws-login-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                if(!isset(App::getHelper('session')->TAG_RETURN_URL)){
                    App::getHelper('session')->TAG_RETURN_URL = $_SERVER['HTTP_REFERER'];
                }
                $tpl->assignGlobal("TAG_RETURN_URL",App::getHelper('session')->TAG_RETURN_URL);
                $this->ws_tpl_type=1;
                break;
        }
        if($this->ws_tpl_type){
            $main->layer_link();
            $tpl->printToScreen();
        }
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$db,$TPLMSG,$ws_array,$main;
        $tpl = new TemplatePower( $cms_cfg['base_all_tpl'] );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        if(!empty($this->m_id)){
            $tpl->assignInclude( "LEFT", $cms_cfg['base_left_member_tpl']); //左方會員專區表單
        }else{
            $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方一般選單
        }
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區     
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["member"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["member"]);//左方menu title
        $tpl->assignGlobal( "TAG_MEMBER_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["member"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-member"); //主要顯示區域的css設定
        $main->header_footer("");
        $main->google_code(); //google analystics code , google sitemap code
        //定義目前語系的表單檢查JS
        $tpl->assignGlobal("TAG_LANG",$cms_cfg['language']);
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["MEMBER_ZONE"]);
        $tpl->assignGlobal( "MSG_MEMBER_LOGIN",$TPLMSG["MEMBER_LOGIN"]);
        $tpl->assignGlobal( "TAG_MEMBER_LOGIN" , $TPLMSG["MEMBER_LOGIN"]);
        $tpl->assignGlobal( "TAG_MEMBER_JOIN" , $TPLMSG["MEMBER_JOIN"]);
        $tpl->assignGlobal( "TAG_MEMBER_LOGOUT" , $TPLMSG["MEMBER_LOGOUT"]);
        if($_GET){
            $main->layer_link($TPLMSG["MEMBER_ZONE"],$cms_cfg['base_root']."member.php");
        }else{
            $main->layer_link($TPLMSG["MEMBER_ZONE"]);
        }
        //頁首會員登入區
        if(empty($_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID'])){
            $tpl->newBlock("INDEX_LOGIN_ZONE");
        }else{
            $tpl->newBlock("INDEX_LOGOUT_ZONE");
        }
        //未登入會員左選單
        if(empty($this->m_id)){
            $leftMenu = new Leftmenu_Nonemember($tpl);
            $leftMenu->make();
        }
    }

    //會員專區================================================================
    function member_zone(){
        global $db,$tpl;
        $_REQUEST["type"]=(empty($_REQUEST["type"]))?"list":$_REQUEST["type"];
        switch($_REQUEST["mzt"]){
            case "collect":
                $this->member_collect();
                break;
            case "pageview":
                $this->member_pageview();
                break;
            case "data"://基本資料修改
                $this->member_form("mod");
                break;
            case "tempstore"://商品寄放管理              
                $this->member_tempstore($_REQUEST["type"],$_REQUEST["id"]);
                break;            
            case "order"://訂單查詢
                $this->member_order($_REQUEST["type"],$_REQUEST["o_id"]);
                break;
            case "inquiry"://詢問信查詢
                $this->member_inquiry($_REQUEST["type"],$_REQUEST["i_id"]);
                break;
            case "contactus"://聯絡我們
                $this->member_contactus($_REQUEST["type"],$_REQUEST["cu_id"]);
                break;
            case "bonus"://紅利點數
                $this->member_bonus();
                break;
            default:
                $this->member_form("mod");
                break;
        }
    }

    //會員管理--表單================================================================
    function member_form($action_mode){
        global $tpl,$cms_cfg,$TPLMSG,$main;
        $main->load_js_msg();
        //欄位名稱
        $tpl->assignGlobal( array("TAG_MAIN_FUNC"  => $TPLMSG['MEMBER_JOIN'],
                                  "MSG_CHECK_ACCOUNT" => $TPLMSG['MEMBER_CHECK_ACCOUNT'],
                                  "MSG_MODE" => $TPLMSG['SEND'],
                                  "MSG_ACCOUNT" => $TPLMSG["LOGIN_ACCOUNT"],
                                  "MSG_PASSWORD" => $TPLMSG["LOGIN_PASSWORD"],
                                  "MSG_CONTACT_PERSON" =>$TPLMSG['CONTACT_PERSON'],
                                  "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                                  "MSG_VALID_PASSWORD" => $TPLMSG['MEMBER_CHECK_PASSWORD'],
                                  "MSG_BIRTHDAY" => $TPLMSG["BIRTHDAY"],
                                  "MSG_ZIP" => $TPLMSG["ZIP"],
                                  "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                                  "MSG_TEL" => $TPLMSG["TEL"],
                                  "MSG_MUTI_TEL_NOTICE" => $TPLMSG["MEMBER_MUTI_TEL_NOTICE"],
                                  "MSG_FAX" => $TPLMSG["FAX"],
                                  "MSG_MUTI_FAX_NOTICE" => $TPLMSG["MEMBER_MUTI_FAX_NOTICE"],
                                  "MSG_EMAIL" => $TPLMSG["EMAIL"],
                                  "MSG_MUTI_EMAIL_NOTICE" => $TPLMSG["MEMBER_MUTI_EMAIL_NOTICE"],
                                  "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
                                  "MSG_MUTI_CELLPHONE_NOTICE" => $TPLMSG["MEMBER_MUTI_CELLPHONE_NOTICE"],
                                  "MSG_YES" => $TPLMSG["YES"],
                                  "MSG_NO" => $TPLMSG["NO"],
                                  "MSG_SUBSCRIBE" => $TPLMSG["SUBSCRIBE"],
                                  "MSG_ACCEPT_SERVICE_TERM" => $TPLMSG["MEMBER_ACCEPT_SERVICE_TERM"],
                                  "MSG_ALLOW_EPAPER" => $TPLMSG["MEMBER_EPAPER"],
                                  "STR_M_CS_CK1" =>"selected",
                                  "STR_M_CS_CK2" => "",
                                  "STR_M_CS_CK3" => "",
                                  "STR_M_EPAPER_STATUS_CK1" => "checked",
                                  "STR_M_EPAPER_STATUS_CK0" => "",
                                  "TAG_SEND_SHOW" => "",
                                  "VALUE_ACTION_MODE" => $action_mode
        ));
        //如果有詢問信管理，顯示公司及傳真欄位
        if($cms_cfg["ws_module"]["ws_inquiry"] || App::configs()->ws_module->ws_member_company){
            $tpl->newBlock( "COMPANY_ZONE" );
        }
        //如果為修改模式,帶入資料庫資料
        if($action_mode=="mod" && !empty($this->m_id)){
            $row = App::getHelper('dbtable')->member->getData($this->m_id)->getDataRow();
            if ($row) {
                if(empty($row['fb_uid'])){
                    $tpl->newBlock("COMMON_USER_FIELDS");
                    $tpl->newBlock( "MEMBER_MOD_MODE" );
                }
                //修改會員，顯示e-mail欄位
                $main->layer_link( $TPLMSG['MEMBER_DATA_MOD']);
                $tpl->newBlock( "MOD_EMAIL" );
                $birthTS = strtotime($row["m_birthday"]);
                $tpl->assignGlobal( array("TAG_MAIN_FUNC"  => $TPLMSG['MEMBER_DATA_MOD'],
                                          "VALUE_M_ID"  => $row["m_id"],
                                          "VALUE_M_ACCOUNT" => $row["m_account"],
                                          "VALUE_M_PASSWORD" => $row["m_password"],
                                          "VALUE_M_COMPANY_NAME" => $row["m_company_name"],
                                          "VALUE_M_BIRTHDAY" => ($birthTS)?date("Y-m-d",$birthTS):"",
                                          "VALUE_M_ZIP" => $row["m_zip"],
                                          "VALUE_M_ADDRESS" => $row["m_address"],
                                          "VALUE_M_TEL" => $row["m_tel"],
                                          "VALUE_M_FAX" => $row["m_fax"],
                                          "VALUE_M_EMAIL" => $row["m_email"],
                                          "VALUE_M_CELLPHONE" => $row["m_cellphone"],
                                          "STR_M_EPAPER_STATUS_CK1" => ($row["m_epaper_status"]==1)?"checked":"",
                                          "STR_M_EPAPER_STATUS_CK0" => ($row["m_epaper_status"]==0)?"checked":"",
                                          "MSG_MODE" => $TPLMSG['MODIFY'],
                                          "TAG_SEND_SHOW" => "",
                ));
            }else{
                header("location: member.php?func=m_add");
                die();
            }
        }else{
            //$tpl->newBlock( "MEMBER_ADD_PIC" );
            //新增模式顯示服務條款
            $tpl->assignGlobal(array(
                "TAG_RETURN_URL" => $_GET['return'],
                "TAG_TOOL"       => $_GET['tool'],
            ));
            if(App::configs()->ws_module->ws_member_social_login && $_GET['tool']){
                switch($_GET['tool']){
                    case "fb":
                        $tpl->newBlock('FB_ID');
                        $tpl->assign('fb_uid',$_GET['fb_uid']);
                        break;
                }
            }else{
                $tpl->newBlock("COMMON_USER_FIELDS");
            }
            $tpl->newBlock( "MEMBER_ADD_MODE" );
            $main->layer_link($TPLMSG['MEMBER_JOIN']);
            //$tpl->newBlock( "SERVICE_TERM_SHOW" );
        }
        //國家下拉選單
        if($cms_cfg["ws_module"]["ws_country"]==1) {
            $main->country_select($row["m_country"]);
        }
        //稱謂下拉選單
        $memberField = new ContactfieldWithCourtesyTitle(array(
            'view'      => 'member',
            'blockName' => 'Member',
            'fieldData' => array(
                'contact' => array(
                    'fname' => $row["m_fname"],
                    'lname' => $row["m_lname"],
                    "MSG_FNAME"  => $TPLMSG['MEMBER_FNAME'],
                    "MSG_LNAME"  => $TPLMSG['MEMBER_LNAME'],                    
                ),
                'courtesyTitle' => $row["m_contact_s"],
            ),
        ));
        $tpl->assignGlobal("TAG_CONTACT_WITH_S",$memberField->get_html());
        //地址欄位格式
        if($cms_cfg['ws_module']['ws_address_type']=='tw'){
            $tpl->newBlock("TW_ADDRESS");
        }else{
            $tpl->newBlock("SINGLE_ADDRESS");
        }
    }
    //會員管理--資料更新================================================================
    function member_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        switch ($_REQUEST["action_mode"]){
            case "add":
                if(!isset($_SESSION[$cms_cfg['sess_cookie_name']]['JOIN_MEMBER']) || ($_SESSION[$cms_cfg['sess_cookie_name']]['JOIN_MEMBER']+20)>time()){
                    trigger_error("invalid join member",E_USER_ERROR);
                }             
                //判斷會員帳號是否已存在
                $main->check_duplicate_member_account($_REQUEST["m_account"]);
                $max_sort = $main->get_max_sort_value($cms_cfg['tb_prefix']."_member",'m');
                $m_status = ($cms_cfg["ws_module"]['ws_member_join_validation'])?0:1;
                $new_member_fields = array(
                    'mc_id'    => 1,
                    'm_sort'   => $max_sort,
                    'm_status' => $m_status,
                );
                if($_POST['social_login_tool']){
                    $goto_url=$cms_cfg["base_root"].$_POST['social_login_tool']."-login.php?".$_POST['social_login_tool']."_uid=".$_POST["fb_uid"]."&return=".urlencode($_POST["return"]);
                }else{
                    $goto_url=$cms_cfg["base_root"]."products.htm";
                }
                break;
            case "mod":
                $goto_url=$cms_cfg["base_url"]."member.php?func=m_mod";
                break;
        }
        $memberData = array_merge($_POST,(array)$new_member_fields);
        if($memberData){
            App::getHelper('dbtable')->member->writeData($memberData);
            $this->m_id = $_POST['m_id']? $_POST['m_id'] : App::getHelper('dbtable')->member->get_insert_id();
            $db_msg = App::getHelper('dbtable')->member->report();
            if ( $db_msg == "" ) {
                if($_REQUEST["action_mode"]=="add"){
                    unset($_SESSION[$cms_cfg['sess_cookie_name']]['JOIN_MEMBER']);
                    //已有購物或詢價時直接登入
                    if(!empty($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"])){
                        if($member = App::getHelper('member')->getData($this->m_id)->getDataRow()){
                            $goto_url = ($cms_cfg['new_cart_path'])? $cms_cfg['new_cart_path'] : $cms_cfg['base_root']."cart.php";
                            Model_User::login($member,$goto_url);
                        }
                    }
//                    $this->ws_tpl_file = "templates/ws-mail-tpl.html";
//                    $mtpl = new TemplatePower( $this->ws_tpl_file );
//                    $mtpl->prepare();
                    $mtpl = App::getHelper('main')->get_mail_tpl("member-join");
                    //寄送訊息
                    $sql="select st_join_member_mail from ".$cms_cfg['tb_prefix']."_service_term where st_id='1'";
                    $selectrs = $db->query($sql);
                    $row = $db->fetch_array($selectrs,1);
                    //如果有詢問信管理，顯示公司及傳真欄位
                    $mtpl->newBlock( "MEMBER_MAIL" );
                    $mtpl->assignGlobal( array("MSG_MEMBER_NAME"  => $TPLMSG['MEMBER_NAME'],
                                              "MSG_ACCOUNT" => $TPLMSG["LOGIN_ACCOUNT"],
                                              "MSG_PASSWORD" => $TPLMSG["LOGIN_PASSWORD"],
                                              "MSG_CONTACT_PERSON" =>$TPLMSG['CONTACT_PERSON'],
                                              "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                                              "MSG_BIRTHDAY" => $TPLMSG["BIRTHDAY"],
                                              "MSG_SEX" => $TPLMSG["SEX"],
                                              "MSG_ZIP" => $TPLMSG["ZIP"],
                                              "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                                              "MSG_TEL" => $TPLMSG["TEL"],
                                              "MSG_FAX" => $TPLMSG["FAX"],
                                              "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
                                              "MSG_SUBSCRIBE" => $TPLMSG["SUBSCRIBE"],
                                              "VALUE_M_ACCOUNT" => $_REQUEST["m_account"],
                                              "VALUE_M_PASSWORD" => $_REQUEST["m_password"],
                                              "VALUE_M_CONTACT_S" => $ws_array["contactus_s"][$_REQUEST["m_contact_s"]],
                                              "VALUE_M_COMPANY_NAME" => $_REQUEST["m_company_name"],
                                              "VALUE_M_FNAME" => $_REQUEST["m_fname"],
                                              "VALUE_M_LNAME" => $_REQUEST["m_lname"],
                                              "VALUE_M_BIRTHDAY" => $_REQUEST["m_birthday"],
                                              "VALUE_M_SEX" => ($_REQUEST["m_sex"]==1)?$TPLMSG["MALE"]:$TPLMSG["FEMALE"],
                                              "VALUE_M_ZIP" => $_REQUEST["m_zip"],
                                              "VALUE_M_ADDRESS" => $_REQUEST["m_city"].$_REQUEST["m_area"].$_REQUEST["m_address"],
                                              "VALUE_M_TEL" => $_REQUEST["m_tel"],
                                              "VALUE_M_FAX" => $_REQUEST["m_fax"],
                                              "VALUE_M_EMAIL" => $_REQUEST["m_account"],
                                              "VALUE_M_CELLPHONE" => $_REQUEST["m_cellphone"],
                                              "VALUE_M_EPAPER" => ($_REQUEST["m_epaper"]==1)?$TPLMSG["YES"]:$TPLMSG["NO"],
                    ));
                    if($cms_cfg["ws_module"]["ws_inquiry"] || App::configs()->ws_module->ws_member_company){
                        $mtpl->newBlock( "COMPANY_ZONE" );
                    }                    
                    //國家欄位
                    if($cms_cfg["ws_module"]["ws_country"]==1) {
                        $mtpl->newBlock("MEMBER_COUNTRY_ZONE");
                        $mtpl->assign(array("MSG_COUNTRY" =>$TPLMSG['COUNTRY'],
                                           "VALUE_M_COUNTRY" =>$_REQUEST["m_country"]
                        ));
                    }
                    //稱謂
                    $mtpl->newBlock("MEMBER_S_STYLE_".$this->contact_s_style);
                    $mtpl->assignGlobal( "VALUE_TERM" , $row['st_join_member_mail']);
                    //加入會員時驗證的方式
                    if(!empty($cms_cfg["ws_module"]['ws_member_join_validation'])){
                        switch($cms_cfg["ws_module"]['ws_member_join_validation']){
                            case "email"://帳號啟用連結
                                $act_link = $this->get_activate_link($_POST['m_account'],$this->m_id);      
                                $mtpl->newBlock("MEMBER_JOIN_VALIDATION_EMAIL_NOTIFICATION");
                                $mtpl->assign(array(
                                    "MSG_MEMBER_JOIN_VALIDATION_EMAIL" => $TPLMSG['MEMBER_JOIN_VALIDATE_EMAIL'],
                                    "ACTIVATE_LINK"                    => $act_link,
                                ));
                                break;
                            case "manual":
                            default:
                                $mtpl->newBlock("MEMBER_JOIN_VALIDATION_MANUAL_NOTIFICATION");
                                $mtpl->assign(array(
                                    "MSG_MEMBER_JOIN_VALIDATION_MANUAL" => $TPLMSG['MEMBER_JOIN_VALIDATE_MANUAL'],
                                ));
                                break;
                        }
                    }
                    //加入會員提示
                    if(!in_array($cms_cfg['language'],array('cht','chs'))){
                        $mtpl->newBlock("ENG_NOTIFY");
                    }else{
                        $mtpl->newBlock(strtoupper($cms_cfg['language'])."_NOTIFY");
                    }
                    $mail_content=$mtpl->getOutputContent();
                    $main->ws_mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$_REQUEST["m_account"],$mail_content,$TPLMSG['MEMBER_CONFIRM_MAIL'],"m",$goto_url);
                }else{
                  $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                  $this->goto_target_page($goto_url);
                }
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //會員訂單查詢
    function member_order($type="list",$o_id=""){
        global $db,$tpl,$main,$cms_cfg,$TPLMSG,$ws_array;
        $main->layer_link($TPLMSG['MEMBER_ZONE_ORDER']);
        if($type=="list"){
            $tpl->assignGlobal( array(
                "TAG_MAIN_FUNC"  => $TPLMSG['MEMBER_ZONE_ORDER']  
            ));
            $tpl->newBlock( "ORDER_LIST_ZONE" );
            $tpl->assign( array("MSG_NAME"  => $TPLMSG['MEMBER_NAME'],
                                "MSG_STATUS" => $TPLMSG['STATUS'],
                                "MSG_ORDER_ID" => $TPLMSG['ORDER_ID'],
                                "MSG_CREATEDATE" => $TPLMSG['CREATEDATE'],
                                "MSG_MODIFYDATE" => $TPLMSG['MODIFYDATE'],
                                "MSG_TOTAL_MONEY" => $TPLMSG['ORDER_TOTAL_MONEY'],
                                "MSG_VIEWS" => $TPLMSG['VIEWS'],
            ));
            $sql="select * from ".$db->prefix("order")." where m_id='".$this->m_id."' and del='0' order by o_createdate desc";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records    = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="member.php?func=m_zone&mzt=order&type=list";
            //重新組合包含limit的sql語法
            $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "ORDER_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow'");
                }
                $tpl->assign( array("VALUE_O_ID"  => $row["o_id"],
                                    "VALUE_O_NAME" => $row["o_name"],
                                    "VALUE_O_CREATEDATE" => $row["o_createdate"],
                                    "VALUE_O_MODIFYDATE" => $row["o_modifydate"],
                                    "VALUE_O_TOTAL_PRICE" => $row["o_total_price"],
                                    "VALUE_O_STATUS" => $ws_array["order_status"][$row["o_status"]],
                                    "VALUE_O_SERIAL" => $i,
                                    "VALUE_O_DETAIL" => $TPLMSG['DETAIL'],
                                    "STATUS_CLASS"   => "order_status_".$row['o_status'],
                ));
                if($row['o_payment_type']==1 && $row['o_atm_last5']=='' && $row['o_status']<2){ //未出貨訂單未匯款的訂單
                    $tpl->newBlock("UNATM_FIELD");
                    $tpl->assign(array(
                        "VALUE_O_ID" => $row['o_id']
                    ));
                }
                if($row['o_status']==0 && $cms_cfg['ws_module']['ws_order_cancel']){
                    $tpl->newBlock("BTN_CANCEL_ORDER");
                    $tpl->assign("VALUE_O_ID",$row['o_id']);
                }
            }
        }
        if($type=="detail"){
            $tpl->newBlock( "ORDER_DETAIL_ZONE" );
            //欄位名稱
            $tpl->assignGlobal( array("MSG_ORDER_DETAIL"  => $TPLMSG['ORDER_DETAIL'],
                                      "MSG_ORDER_CONTENT"  => $TPLMSG['ORDER_CONTENT'],
                                      "MSG_CONTACT_PERSON" =>$TPLMSG['CONTACT_PERSON'],
                                      "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                                      "MSG_NAME"  => $TPLMSG['NAME'],
                                      "MSG_ZIP" => $TPLMSG["ZIP"],
                                      "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                                      "MSG_TEL" => $TPLMSG["TEL"],
                                      "MSG_FAX" => $TPLMSG["FAX"],
                                      "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
                                      "MSG_STATUS" => $TPLMSG['STATUS'],
                                      "MSG_ORDER_ID" => $TPLMSG['ORDER_ID'],
                                      "MSG_MODE" => $TPLMSG['MODIFY'],
                                      "MSG_CONTENT" => $TPLMSG['CONTENT'],
                                      "MSG_PAYMENT_TYPE" => $TPLMSG['PAYMENT_TYPE'],
                                      "MSG_INVOICE_TYPE" => $TPLMSG['INVOICE_TYPE'],
                                      "MSG_TOTAL" => $TPLMSG['CART_TOTAL'],
                                      "MSG_SUBTOTAL" => $TPLMSG['CART_SUBTOTAL'],
                                      "MSG_AMOUNT" => $TPLMSG['CART_AMOUNT'],
                                      "MSG_PRODUCT" => $TPLMSG['PRODUCT'],
                                      "MSG_PRODUCT_SPECIAL_PRICE" => $TPLMSG['PRODUCT_SPECIAL_PRICE'],
                                      "MSG_SHIPPING_PRICE"  => $TPLMSG['SHIPPING_PRICE'],
                                      "MSG_BUYER_INFO"  => $TPLMSG['ORDER_BUYER_INFO'],
                                      "MSG_RECI_INFO"   => $TPLMSG['ORDER_RECI_INFO'],
                                      "MSG_INVOICE_INFO"   => $TPLMSG['ORDER_INVOICE_INFO'],
                                      "MSG_VAT_NUMBER"   => $TPLMSG['VAT_NUMBER'],
                                      "MSG_PAYMENT_TYPE"   => $TPLMSG['PAYMENT_TYPE'],
                                      "MSG_DELIVER_STR"   => $TPLMSG['DELIVER_STR'],
                                      "MSG_PLUS_FEE" => $TPLMSG["PLUS_FEE"],
                                      "MSG_SPEC" => $TPLMSG['CART_SPEC_TITLE'],
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
                $sql="select * from ".$db->prefix("order")." where m_id='".$this->m_id."' and o_id='".$_REQUEST["o_id"]."' and del='0' ";
                $selectrs = $db->query($sql);
                $row = $db->fetch_array($selectrs,1);
                $rsnum    = $db->numRows($selectrs);
                if ($rsnum > 0) {
                    $tpl->assignGlobal( array("VALUE_M_ID"  => $row["m_id"],
                                              "VALUE_O_ID"  => $row["o_id"],
                                              "VALUE_O_COMPANY_NAME" => $row["o_company_name"],
                                              "VALUE_O_VAT_NUMBER" => $row["o_vat_number"],
                                              "VALUE_O_TEL" => $row["o_tel"],
                                              "VALUE_O_FAX" => $row["o_fax"],
                                              "VALUE_O_CELLPHONE" => $row["o_cellphone"],
                                              "VALUE_O_ZIP" => $row["o_zip"],
                                              "VALUE_O_ADDRESS" => $row["o_city"].$row["o_area"].$row["o_address"],
                                              "VALUE_O_EMAIL" => $row["o_email"],
                                              "VALUE_O_RECI_CONTACT_S" => $row["o_reci_contact_s"],
                                              "VALUE_O_RECI_NAME" => $row["o_reci_name"],
                                              "VALUE_O_RECI_TEL" => $row["o_reci_tel"],
                                              "VALUE_O_RECI_FAX" => $row["o_reci_fax"],
                                              "VALUE_O_RECI_CELLPHONE" => $row["o_reci_cellphone"],
                                              "VALUE_O_RECI_ZIP" => $row["o_reci_zip"],
                                              "VALUE_O_RECI_ADDRESS" => $row["o_reci_city"].$row["o_reci_area"].$row["o_reci_address"],
                                              "VALUE_O_RECI_TEL" => $row["o_reci_tel"],
                                              "VALUE_O_RECI_EMAIL" => $row["o_reci_email"],
                                              "VALUE_O_PLUS_PRICE" => App::getHelper('main')->format_shipprice_str($row['o_shippment_type'],$row["o_plus_price"]),
                                              "VALUE_O_CHARGE_FEE" => $row["o_charge_fee"],
                                              "VALUE_O_MINUS_PRICE" => $row["o_minus_price"],
                                              "VALUE_O_SUBTOTAL_PRICE" => $row["o_subtotal_price"],
                                              "VALUE_O_TOTAL_PRICE" => $row["o_total_price"],
                                              "VALUE_O_STATUS_SUBJECT" => $ws_array["order_status"][$row["o_status"]],
                                              "VALUE_O_CONTENT" => $row["o_content"],
                                              "VALUE_O_PAYMENT_TYPE" => $main->multi_map_value($ws_array["payment_type"],$row['o_payment_type']),
                                              "VALUE_O_SHIPPMENT_TYPE" => $main->multi_map_value($ws_array["shippment_type"],$row['o_shippment_type']),
                                              "VALUE_O_INVOICE_TYPE" => $main->multi_map_value($ws_array["invoice_type"],$row['o_invoice_type']),
                                              "VALUE_O_VAT_NUMBER" => $row["o_vat_number"],
                                              "VALUE_O_DELIVER_DATE" => (strtotime($row["o_deliver_date"]))?date("Y年m月d日",strtotime($row["o_deliver_date"])):"",
                                              "VALUE_O_DELIVER_TIMESEC" => $ws_array["deliery_timesec"][$row["o_deliver_time_sec"]],                      
                                              "VALUE_O_ATM_LAST5" => $row['o_atm_last5'],                                           
                    ));
                    $tpl->newBlock("ORDER_S_".$this->contact_s_style);
                    $tpl->assign(array(
                          "VALUE_O_NAME"      => $row["o_name"],
                          "VALUE_O_CONTACT_S" => $ws_array['contactus_s'][$row["o_contact_s"]],
                    ));           
                    $tpl->newBlock("RECI_ORDER_S_".$this->contact_s_style);
                    $tpl->assign(array(
                          "VALUE_O_NAME"      => $row["o_reci_name"],
                          "VALUE_O_CONTACT_S" => $ws_array['contactus_s'][$row["o_reci_contact_s"]],
                    ));                          
                    //訂購產品列表
                    $sql="select * from ".$cms_cfg['tb_prefix']."_order_items where o_id='".$_REQUEST["o_id"]."' and del='0' ";
                    $selectrs = $db->query($sql);
                    $i=0;
                    if($cms_cfg['ws_module']['ws_cart_spec']){
                        $tpl->newBlock("SPEC_TITLE_ORDER");
                        $tpl->assignGlobal("CART_FIELDS_NUMS",6);
                    }else{
                        $tpl->assignGlobal("CART_FIELDS_NUMS",5);
                    }
                    while($row = $db->fetch_array($selectrs,1)){
                        $i++;
                        $sub_total_price = round($row["price"] * $row["amount"] * $row['discount']);
                        $tpl->newBlock( "ORDER_ITEMS_LIST" );
                        $tpl->assign( array("VALUE_P_ID"  => $row["p_id"],
                                            "VALUE_P_NAME" => $row["p_name"],
                                            "VALUE_P_SELL_PRICE" => $row["price"],
                                            "VALUE_P_AMOUNT" => $row["amount"],
                                            "TAG_QUANTITY_DISCOUNT" => ($row['discount']<1)?$row['discount']:'',
                                            "VALUE_P_SUBTOTAL_PRICE"  => $sub_total_price,
                                            "VALUE_P_SERIAL"  => $i,
                                            "VALUE_P_SPEC" => $row['spec'],
                        ));
                        if($cms_cfg['ws_module']['ws_cart_spec']){
                            $tpl->newBlock("SPEC_FIELD_ORDER");
                            $tpl->assign("VALUE_SPEC",$row["spec"]);
                        }
                    }
                }else{
                    header("location: member.php");
                }
            }
        }
    }
    //會員詢問信
    function member_inquiry($type="list",$i_id=""){
       global $db,$tpl,$main,$cms_cfg,$TPLMSG,$ws_array;
        if($type=="list"){
            $main->layer_link($TPLMSG['MEMBER_ZONE_INQUIRY']);
            $tpl->assignGlobal( array("TAG_MAIN_FUNC"  => $TPLMSG['MEMBER_ZONE_INQUIRY'] ));
            $tpl->newBlock( "INQUIRY_LIST_ZONE" );
            $tpl->assign( array("MSG_NAME"  => $TPLMSG['MEMBER_NAME'],
                                "MSG_STATUS" => $TPLMSG['STATUS'],
                                "MSG_INQUIRY_ID" => $TPLMSG['INQUIRY_ID'],
                                "MSG_CREATEDATE" => $TPLMSG['CREATEDATE'],
                                "MSG_MODIFYDATE" => $TPLMSG['MODIFYDATE'],
                                "MSG_VIEWS" => $TPLMSG['VIEWS'],
            ));
            $sql="select * from ".$db->prefix("inquiry")." where m_id='".$this->m_id."' and del='0'  order by i_createdate desc ";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="member.php?func=m_zone&mzt=inquiry&type=list";
            //重新組合包含limit的sql語法
            $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "INQUIRY_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow'");
                }
                $tpl->assign( array("VALUE_I_ID"  => $row["i_id"],
                                    "VALUE_I_NAME" => $row["i_name"],
                                    "VALUE_I_CREATEDATE" => $row["i_createdate"],
                                    "VALUE_I_MODIFYDATE" => $row["i_modifydate"],
                                    "VALUE_I_STATUS" => ($row["i_status"])?$TPLMSG["REPLY_YES"]:$TPLMSG["REPLY_NO"],
                                    "VALUE_I_SERIAL" => $i,
                                    "VALUE_I_DETAIL" => $TPLMSG['DETAIL'],
                ));
            }
        }
        if($type=="detail"){
            $tpl->newBlock( "INQUIRY_DETAIL_ZONE" );
            //欄位名稱
            $tpl->assignGlobal( array("MSG_INQUIRY_DETAIL"  => $TPLMSG['INQUIRY_DETAIL'],
                                      "MSG_INQUIRY_CONTENT"  => $TPLMSG['INQUIRY_CONTENT'],
                                      "MSG_CONTACT_PERSON" =>$TPLMSG['CONTACT_PERSON'],
                                      "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                                      "MSG_NAME"  => $TPLMSG['NAME'],
                                      "MSG_ZIP" => $TPLMSG["ZIP"],
                                      "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                                      "MSG_TEL" => $TPLMSG["TEL"],
                                      "MSG_FAX" => $TPLMSG["FAX"],
                                      "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
                                      "MSG_STATUS" => $TPLMSG['STATUS'],
                                      "MSG_INQUIRY_ID" => $TPLMSG['INQUIRY_ID'],
                                      "MSG_MODE" => $TPLMSG['MODIFY'],
                                      "MSG_CONTENT" => $TPLMSG['CONTENT'],
                                      "MSG_REPLY" => $TPLMSG['REPLY'],
                                      "MSG_AMOUNT" => $TPLMSG['CART_AMOUNT'],
                                      "MSG_PRODUCT" => $TPLMSG['PRODUCT'],
                                      "MSG_PRODUCT_SPECIAL_PRICE" => $TPLMSG['PRODUCT_DISCOUNT_PRICE'],
                                      "MSG_SPEC" => $TPLMSG['CART_SPEC_TITLE'],
            ));
            //相關參數
            if(!empty($_REQUEST['nowp'])){
                $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                          "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                          "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                          "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

                ));
            }
            //帶入要回覆的訂單資料
            if(!empty($_REQUEST["i_id"])){
                $sql="select * from ".$db->prefix("inquiry")." where m_id='".$this->m_id."' and del='0' and i_id='".$_REQUEST["i_id"]."'";
                $selectrs = $db->query($sql);
                $row = $db->fetch_array($selectrs,1);
                $rsnum    = $db->numRows($selectrs);
                if ($rsnum > 0) {
                    $tpl->assignGlobal( array("VALUE_M_ID"  => $row["m_id"],
                                              "VALUE_I_ID"  => $row["i_id"],
                                              "VALUE_I_COMPANY_NAME" => $row["i_company_name"],
                                              "VALUE_I_TEL" => $row["i_tel"],
                                              "VALUE_I_CELLPHONE" => $row["i_cellphone"],
                                              "VALUE_I_ZIP" => $row["i_zip"],
                                              "VALUE_I_ADDRESS" => $row["i_city"].$row["i_area"].$row["i_address"],
                                              "VALUE_I_EMAIL" => $row["i_email"],
                                              "VALUE_I_CONTENT" => $row["i_content"],
                                              "VALUE_I_REPLY" => $row["i_reply"],
                                              "VALUE_I_STATUS_SUBJECT" => $ws_array["inquiry_status"][$row["i_status"]],
//                                              "VALUE_I_STATUS_SUBJECT" => ($row["i_status"])?$TPLMSG["INQUIRY_REPLY"]:$TPLMSG["INQUIRY_NO_REPLY"],
                    ));
//                    $main->contact_s_select($row["i_contact_s"],"CART");
                    $tpl->newBlock("INQUIRY_S_".$this->contact_s_style);
                    $tpl->assign(array(
                          "VALUE_I_NAME"      => $row["i_name"],
                          "VALUE_I_CONTACT_S" => $ws_array['contactus_s'][$row["i_contact_s"]],
                    ));
                    //訂購產品列表
                    $sql="select * from ".$db->prefix("inquiry_items")." where i_id='".$_REQUEST["i_id"]."'";
                    $selectrs = $db->query($sql);
                    $i=0;
                    if($cms_cfg['ws_module']['ws_cart_spec']){
                        $tpl->newBlock("SPEC_TITLE");
                    }                    
                    while($row = $db->fetch_array($selectrs,1)){
                        $i++;
                        $tpl->newBlock( "INQUIRY_ITEMS_LIST" );
                        $tpl->assign( array("VALUE_P_ID"  => $row["p_id"],
                                            "VALUE_P_NAME" => $row["p_name"],
                                            "VALUE_P_AMOUNT" => $row["amount"],
                                            "VALUE_P_SERIAL"  => $i,
                                            "VALUE_P_SPEC" => $row["spec"],
                        ));
                        if($cms_cfg['ws_module']['ws_cart_spec']){
                            $tpl->newBlock("SPEC_FIELD");
                            $tpl->assign("VALUE_SPEC",$row["spec"]);
                        }                        
                    }
                }else{
                    header("location: member.php");
                    die();
                }
            }
        }
    }
    //會員聯絡我們
    function member_contactus($type="list",$cu_id=""){
        global $db,$tpl,$main,$cms_cfg,$TPLMSG,$ws_array;
        $main->layer_link($TPLMSG['MEMBER_ZONE_CONTACTUS']);
        if($type=="list"){
            $tpl->newBlock( "CONTACTUS_LIST_ZONE" );
            //聯絡我們分類
            $i=0;
            foreach($ws_array["contactus_cate"] as $key =>$value){
                $i++;
                $tpl->newBlock( "CONTACTUS_CATE_LIST" );
                $tpl->assign( array("VALUE_CUC_SUBJECT"  => $value,
                                    "VALUE_CU_CATE" => $key,
                                    "VALUE_CUC_SERIAL" => $i,
                ));
            }
            //聯絡我們列表
            $sql="select * from ".$db->prefix("contactus")." where m_id='".$this->m_id."' and del='0' order by cu_modifydate desc";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records    = $db->numRows($selectrs);
            //取得分頁連結          
            $func_str="member.php?func=m_zone&mzt=contactus&type=list";
            //重新組合包含limit的sql語法
            $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $tpl->assignGlobal( array("MSG_NAME"  => $TPLMSG['MEMBER_NAME'],
                                      "MSG_CONTENT"  => $TPLMSG['CONTENT'],
                                      "MSG_STATUS" => $TPLMSG['STATUS'],
                                      "MSG_MODE" => $TPLMSG['MANAGE_CATE'],
                                      "MSG_REPLY" => $TPLMSG['REPLY'],
                                      "MSG_CATE" => $TPLMSG['CATE'],
                                      "MSG_CREATEDATE" => $TPLMSG['CREATEDATE'],
                                      "MSG_VIEWS" => $TPLMSG['VIEWS'],
                                      "MSG_LOGIN_LANGUAGE" => $TPLMSG['LOGIN_LANGUAGE'],
                                      "VALUE_TOTAL_BOX" => $rsnum,
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],

            ));

            $i=$page["start_serial"];
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "CONTACTUS_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow'");
                }
                $tpl->assign( array("VALUE_CU_ID"  => $row["cu_id"],
                                    "VALUE_CU_NAME" => $row["cu_name"],
                                    "VALUE_CU_CONTENT" => strip_tags($row["cu_content"]),
                                    "VALUE_CU_MODIFYDATE" => $row["cu_modifydate"],
                                    "VALUE_CU_STATUS" => ($row["cu_status"])?$TPLMSG['REPLY_YES']:$TPLMSG['REPLY_NO'],
                                    "VALUE_CU_SERIAL" => $i,
                                    "VALUE_CUC_SUBJECT"  => $ws_array["contactus_cate"][$row["cu_cate"]],
                                    "VALUE_CU_DETAIL" => $TPLMSG['DETAIL'],
                ));
            }
        }
        if($type=="detail"){
            $tpl->newBlock( "CONTACTUS_DETAIL_ZONE" );
            //欄位名稱
            $tpl->assignGlobal( array("MSG_CONTACT_US_DETAIL"  => $TPLMSG['CONTACT_US_DETAIL'],
                                      "MSG_CONTACT_US_REPLY"  => $TPLMSG['CONTACT_US_REPLY'],
                                      "MSG_CONTACT_US_REPLY_CONTENT"  => $TPLMSG['CONTACT_US_REPLY_CONTENT'],
                                      "MSG_CONTACT_US_REPLY_TIME"  => $TPLMSG['CONTACT_US_REPLY_TIME'],
                                      "MSG_CATE"  => $TPLMSG['CATE'],
                                      "MSG_NAME"  => $TPLMSG['NAME'],
                                      "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                                      "MSG_TEL" => $TPLMSG["TEL"],
                                      "MSG_FAX" => $TPLMSG["FAX"],
                                      "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
                                      "MSG_STATUS" => $TPLMSG['STATUS'],
            ));
            //相關參數
            if(!empty($_REQUEST['nowp'])){
                $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                          "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                          "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                          "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

                ));
            }
            //帶入聯絡我們資料
            if(!empty($_REQUEST["cu_id"])){
                $sql="select * from ".$cms_cfg['tb_prefix']."_contactus where m_id='".$this->m_id."' and del='0' and cu_id='".$_REQUEST["cu_id"]."'";
                $selectrs = $db->query($sql);
                $row = $db->fetch_array($selectrs,1);
                $rsnum    = $db->numRows($selectrs);
                if ($rsnum > 0) {
                    $tpl->assignGlobal( array("VALUE_M_ID"  => $row["m_id"],
                                              "VALUE_CUC_SUBJECT"  => $ws_array["contactus_cate"][$row["cu_cate"]],
                                              "VALUE_CU_ID"  => $row["cu_id"],
                                              "VALUE_CU_NAME" => $row["cu_name"],
                                              "VALUE_CU_TEL" => $row["cu_tel"],
                                              "VALUE_CU_FAX" => $row["cu_fax"],
                                              "VALUE_CU_ADDRESS" => $row["cu_address"],
                                              "VALUE_CU_EMAIL" => $row["cu_email"],
                                              "VALUE_CU_STATUS_SUBJECT" => ($row["cu_status"])?$TPLMSG['REPLY_YES']:$TPLMSG["REPLY_NO"],
                    ));

                    //聯絡我們回覆
                    $sql="select * from ".$cms_cfg['tb_prefix']."_contactus_reply where cu_id='".$_REQUEST["cu_id"]."'";
                    $selectrs = $db->query($sql);
                    $total_price=0;
                    $i=0;
                    while($row = $db->fetch_array($selectrs,1)){
                        $i++;
                        $tpl->newBlock( "CONTACTUS_REPLY_LIST" );
                        $tpl->assign( array(
                                            "VALUE_CUR_CONTENT" => $row["cur_content"],
                                            "VALUE_CUR_MODIFYDATE" => $row["cur_modifydate"],
                                            "VALUE_CUR_SERIAL"  => $i,
                        ));
                    }
                }else{
                    header("location: member.php");
                    die();
                }
            }
        }
    }
    //會員忘記密碼
    function member_forget_password(){
        global $db,$tpl,$main,$cms_cfg,$TPLMSG,$ws_array;
        if(empty($_REQUEST["m_email"])){
            $this->ws_tpl_file = "templates/ws-member-forget-tpl.html";
            $this->ws_load_tp($this->ws_tpl_file);
            $main->layer_link($TPLMSG["FORGOT_PASSWORD"] );
            $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["FORGOT_PASSWORD"] );
            $tpl->newBlock("REGISTER_FORGET_PASSWORD");
            $tpl->newBlock("REGISTER_LANG_".strtoupper( $cms_cfg['language']) );
            $main->security_zone($cms_cfg['security_image_width'],$cms_cfg['security_image_height']);
            $this->ws_tpl_type=1;            
        }else{
            require_once("./libs/libs-security-image.php");
            $si = new securityImage();  
            if($si->isValid()){
                $sql="select m_fname,m_lname,m_account,m_password,m_email from ".$db->prefix("member")." where m_account='".$_REQUEST["m_email"]."'";
                $selectrs = $db->query($sql);
                $row = $db->fetch_array($selectrs,1);
                $rsnum    = $db->numRows($selectrs);
                if ($rsnum > 0) {
                    //重設密碼
                    $row['m_password'] = $main->rand_str(17);
                    $sql = "update ".$db->prefix("member")." set m_password='".$db->quote($row['m_password'])."' where m_account='".$db->quote($row['m_email'])."'";
                    $db->query($sql,true);
                    //寄出通知信
//                    $tpl = new TemplatePower( "templates/ws-mail-tpl.html" );
//                    $tpl->prepare();
                    $tpl = App::getHelper('main')->get_mail_tpl("member-forgetpass");
                    $tpl->newBlock( "MEMBER_FORGET_PASSWORD" );
                    $tpl->assignGlobal( array("MSG_TITLE"  => $TPLMSG['MEMBER_FORGET_TITLE'],
                                              "MSG_M_ACCOUNT" => $TPLMSG['MEMBER_FORGET_ACCOUNT'],
                                              "MSG_M_PASSWORD" => $TPLMSG['MEMBER_FORGET_PASSWORD'],
                                              "VALUE_M_NAME"  => $row["m_name"],
                                              "VALUE_M_ACCOUNT"  => $row["m_account"],
                                              "VALUE_M_PASSWORD"  => $row["m_password"],
                                              "TAG_BASE_URL"=>$cms_cfg["base_url"]
                    ));
                    $mail_content=$tpl->getOutputContent();
                    $goto_url=$cms_cfg["base_root"]."member.php?func=m_sps";
                    $main->ws_mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$row["m_email"],$mail_content,"Member's Password","pw",$goto_url);
                }else{
                    $goto_url=$cms_cfg["base_root"]."member.php?func=m_forget";
                    $main->js_notice($TPLMSG['MEMBER_ACCOUNT_NOT_EXISTS'],$goto_url);
                }
            }else{
                $goto_url=$cms_cfg["base_root"]."member.php?func=m_forget";
                $main->js_notice($TPLMSG['SECURITY_ERROR'],$goto_url);
            }
        }
    }    
    function member_message_list(){
        global $db,$tpl,$main,$cms_cfg,$TPLMSG,$ws_array;
        $sql="select * from ".$db->prefix("member_message")." order by mm_sort asc,mm_modifydate desc ";
        //取得總筆數
        $selectrs = $db->query($sql);
        $total_records = $db->numRows($selectrs);
        //取得分頁連結，重新組合包含limit的sql語法
        $func_str="member.php?func=mm_list";
        $main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "MESSAGE_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }    
            $tpl->assign( array(
                "VALUE_MM_ID"  => $row["mm_id"],
                "VALUE_MM_SUBJECT" => $row["mm_subject"],
                "VALUE_MM_CONTENT" => $main->content_file_str_replace($row["mm_content"],'out2'),
                "VALUE_MM_MODIFYDATE" => $row["mm_modifydate"],
            ));
        }
    }
     //寄送密碼完成訊息
    function member_send_password_success_str(){
        global $db,$tpl,$main,$cms_cfg,$TPLMSG,$ws_array;
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["FORGOT_PASSWORD"] );
        $tpl->newBlock( "REGISTER_SEND_PASSWORD_SUCCESSFULLY" );
        $tpl->newBlock( "SEND_LANG_".strtoupper($cms_cfg['language']) );
        $main->layer_link($TPLMSG["FORGOT_PASSWORD"]);
    }
    //會員紅利
    function member_bonus(){}
    //顯示訊息並重新導向
    function goto_target_page($url,$sec=2){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }
    //啟用帳號
    function account_activate(){
        global $db,$cms_cfg,$main,$TPLMSG,$tpl;
        if($_GET['account'] && $_GET['code']){
            $sql = "select m_id,m_account from ".$cms_cfg['tb_prefix']."_member where m_account='".$db->quote($_GET['account'])."'";
            $mRow = $db->query_firstRow($sql);
            if($mRow){
                $code = $this->_generate_activate_code($mRow['m_account'],$mRow['m_id']);
                if($code == $_GET['code']){
                    $sql = "update ".$cms_cfg['tb_prefix']."_member set m_status='1' where m_id='".$mRow['m_id']."'";
                    $res = $db->query($sql,1);
                    if(!$db->report()){
                        $main->meta_refresh($cms_cfg['base_root']."member.php",3);
                        $tpl->assignGlobal("MSG_ACTION_TERM",$TPLMSG['ACCOUNT_ACTIVATED']);
                        return;                        
                    }
                }
            }
        }
        $tpl->assignGlobal("MSG_ACTION_TERM",$TPLMSG['ACCOUNT_ACTIVATE_FAILED']);
    }
    function get_activate_link($acc,$mid){
        global $cms_cfg;
        $code = $this->_generate_activate_code($acc, $mid);
        return $cms_cfg['base_url']."member.php?func=activate&account=".urlencode($acc)."&code=".urlencode($code);
    }
    function _generate_activate_code($acc,$mid){
        $accPart = substr(md5($acc),0,10);
        $midPart = substr(md5($mid),0,10);
        return $accPart.$midPart;
    }     
    function ajax(){
        $method = __FUNCTION__."_".$_GET['action'];       
        if(method_exists($this, $method)){
            $this->$method();
        }else{
            throw new Exception($method."doesn't exists!");
        }        
    }
    function ajax_write_last5(){
        global $db,$cms_cfg,$main;
        $res['code']=0;
        if($_POST['o_id'] && $_POST['o_atm_last5']){
            $order = App::getHelper('dbtable')->order->getData($_POST['o_id'])->getDataRow('o_id,o_email,o_atm_last5,remit_amount');
            if($order){
                $o_email = $order['o_email'];
                unset($order['o_email']);
                $order = array_merge($order,$_POST);
                App::getHelper('dbtable')->order->writeData($order);
                $err = App::getHelper('dbtable')->order->report();
                if($err){
                    $res['msg'] = $err;
                }else{
                    $res['code']=1;
                    //寄發通知信
                    $tpl = App::getHelper('main')->get_mail_tpl("remit-notification");
                    $tpl->newBlock("REMIT_LAST5");
                    $tpl->assign(array(
                        "MSG_O_ID"      => $_POST['o_id'],
                        "MSG_ATM_LAST5" => $_POST['o_atm_last5'],
                    ));
                    $mail_content = $tpl->getOutputContent();
                    $main->ws_mail_send_simple($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$mail_content,$_SESSION[$cms_cfg['sess_cookie_name']]['sc_company']."atm轉帳訂單匯款完成通知","系統通知");                
                }
            }
        }
        echo json_encode($res);
    }
    //取消宅配箱訂單
    function ajax_cancel_order(){
        global $db,$cms_cfg,$main,$ws_array;
        $sql = "select * from ".$db->prefix("order")." where o_id='".$_POST['o_id']."'";
        $order = $db->query_firstrow($sql);
        if($order){
            $res['code'] = 1;
            if($order['o_status']==0){
                $order['o_status'] = 9;//取消訂單的狀態
                $sql = "update ".$db->prefix("order")." set o_status='9' where o_id='".$_POST['o_id']."'";
                $db->query($sql);
                if($err = $db->report()){
                    $res['code'] = 0;
                    $res['msg'] = $err;
                }else{
                    //寄發通知信
                    $tpl = App::getHelper('main')->get_mail_tpl("order-cancel");
                    $tpl->newBlock("SHOPPING_ORDER");
                    $tpl->assign(array(
                        "MSG_CANCEL_TIME" => date("Y-m-d H:i:s"),
                        "MSG_O_ID" => $_POST['o_id'],
                    ));
                    $mail_content = $tpl->getOutputContent();
                    //$main->ws_mail_send_simple($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$mail_content,$_SESSION[$cms_cfg['sess_cookie_name']]['sc_company']."購物訂單線上取消通知","系統通知");                
                    //ws_mail_send($from,$to,$mail_content,$mail_subject,$mail_type,$goto_url,$admin_subject=null,$none_header=0){
                    $main->ws_mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$order['o_email'],$mail_content,$_SESSION[$cms_cfg['sess_cookie_name']]['sc_company']."購物訂單線上取消通知","","",null,1);                
                    $res['msg'] = $ws_array["order_status"][$order['o_status']];                    
                }
            }else{
                $res['code'] = 0;
                $res['msg'] = "訂單非新訂單，無法線上取消，請聯絡客服";
            }
        }else{
            $res['code'] = 0;
            $res['msg'] = "訂單不存在!";
        }
        echo json_encode($res);
    }
    
    function ajax_collect(){
        $result['code']=1;
        if($this->m_id){
            $olddata = App::getHelper('dbtable')->member_collect->getDataList("m_id='{$this->m_id}' and p_id='{$_POST['p_id']}'","*","createdate desc");
            if(empty($olddata)){
                $newdata = App::getHelper('dbtable')->products->getData($_POST['p_id'])->getDataRow('p_id,p_name,p_small_img');
                $newdata['m_id'] = $this->m_id;
                $newdata['createdate'] = date("Y-m-d H:i:s");
                App::getHelper('dbtable')->member_collect->writeData($newdata);
                if(App::getHelper('dbtable')->member_collect->report()==""){
                    $result['msg']="成功收藏!";
                    $result['nums'] = App::getHelper('main')->collect_nums($_POST['p_id']);
                }else{
                    $result['code']=0;
                    $result['msg']="收藏失敗!";
                }
            }else{
                $result['msg']="已收藏過此產品!";
            }
        }else{
            $result['code']=0;
            $result['msg']="請先登入會員!";
        }
        echo json_encode($result);
    }
    
    function member_pageview(){
        global $tpl,$TPLMSG;
        App::gethelper("main")->layer_link($TPLMSG['MEMBER_FOOTPRINT']);
        $tpl->assignGlobal("TAG_MAIN_FUNC",$TPLMSG['MEMBER_FOOTPRINT']);
        $tpl->newBlock("MEMBER_PAGEVIEW");
        $sql = "select * from ".App::getHelper('db')->prefix("pageview_history")." where m_id='".$this->m_id."'  order by ph_modifydate desc limit 20";
        $res = App::getHelper('db')->query($sql);
//        $total_records = App::gethelper('db')->numRows($res);
//        $func_str="member.php?func=m_zone&mzt=pageview";
//        $sql = App::getHelper('main')->pagination(App::configs()->op_limit,App::configs()->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql,true);
//        $res2 = App::getHelper('db')->query($sql,true);
//        $offset = App::getHelper('main')->get_pagination_offset(App::configs()->op_limit)+1;
        $offset = 1;
        while($history = App::getHelper('db')->fetch_array($res,1)){
            $tpl->newBlock("HISTORY_LIST");
            $tpl->assign(array(
                "TAG_SERIAL" => $offset++,
                "MSG_PAGE_TYPE" => App::defaults()->main[$history['ph_type']],
                "MSG_REQUEST_URI" => App::getHelper('main')->content_file_str_replace($history['ph_request_uri'],'out'),
                "MSG_MODIFYDATE" => $history['ph_modifydate'],
            ));
        }
    }
    function member_collect(){
        global $tpl,$TPLMSG;
        if(App::getHelper('request')->isPost()){ 
            if(isset($_GET['act'])){
                switch($_GET['act']){
                    case "delete":
                        if(is_array($_POST['delete'])){
                            foreach($_POST['delete'] as $collect_id){
                                App::getHelper('dbtable')->member_collect->delete($collect_id);
                            }
                        }
                        break;
                }
            }
            header("location:".$_SERVER['PHP_SELF']."?func=m_zone&mzt=collect");
            die();
        }
        App::gethelper("main")->layer_link($TPLMSG['MEMBER_COLLECT']);
        $tpl->assignGlobal("TAG_MAIN_FUNC",$TPLMSG['MEMBER_COLLECT']);
        $tpl->newBlock("MEMBER_COLLECT");
        $sql = "select * from ".App::getHelper('db')->prefix("member_collect")." where m_id='".$this->m_id."'  order by createdate desc limit 20";
        $res = App::getHelper('db')->query($sql);
//        $total_records = App::gethelper('db')->numRows($res);
//        $func_str="member.php?func=m_zone&mzt=pageview";
//        $sql = App::getHelper('main')->pagination(App::configs()->op_limit,App::configs()->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql,true);
//        $res2 = App::getHelper('db')->query($sql,true);
//        $offset = App::getHelper('main')->get_pagination_offset(App::configs()->op_limit)+1;
        if(App::getHelper('session')->sc_cart_type==1){
            $tpl->newBlock("SHOP_TITLE");
        }
        $offset = 1;
        $imgHandler = Model_Image::factory(80,80);
        while($collect = App::getHelper('db')->fetch_array($res,1)){
            $prod = App::getHelper('dbtable')->products->getData($collect['p_id'])->getDataRow();
            if($prod){
                $tpl->newBlock("COLLECT_LIST");
                $imgInfo = $imgHandler->parse($collect['p_small_img']);
                $price = $prod['p_special_price']?$prod['p_special_price']:$prod['p_list_price'];
                $tpl->assign(array(
                    "TAG_SERIAL" => $offset++,
                    "COLLECT_ID" => $collect['id'],
                    "VALUE_P_NAME" => $collect['p_name'],
                    "VALUE_P_LINK" => App::getHelper('request')->get_link('products',$prod),
                    "VALUE_P_SMALL_IMG" => $imgInfo[0],
                    "VALUE_P_SMALL_IMG_W" => $imgInfo['width'],
                    "VALUE_P_SMALL_IMG_H" => $imgInfo['height'],
                    "MSG_CREATEDATE" => $collect['createdate'],
                ));
                if(App::getHelper('session')->sc_cart_type==1){
                    $tpl->newBlock("SHOP_FIELD");
                    $tpl->assign(array(
                        "VALUE_P_PRICE" => $prod['spec_sets']?'':$price,
                        "TAG_CART_LINK" => $prod['spec_sets']?App::gethelper('main')->mk_link("購買",App::getHelper('request')->get_link('products',$prod)):"<a href='#' class='prodToCart' rel='{$prod['p_id']}'>購買</a>",
                    ));
                }
            }
        }        
    }
    //會員訂單查詢
    function member_tempstore($type="list",$id=""){
        global $db,$tpl,$main,$cms_cfg,$TPLMSG,$ws_array;
        if($type=="list"){
            $main->layer_link($TPLMSG['TEMP_STORE']);
            $tpl->assignGlobal( array("TAG_MAIN_FUNC"  => $TPLMSG['TEMP_STORE']  ));
            $tpl->newBlock( "TEMPSTORE_LIST_ZONE" );
            $sql="select * from ".$db->prefix("temp_store")." as a inner join ".$db->prefix("products")." as b on a.p_id=b.p_id where m_id='".$this->m_id."' order by b.p_sort ";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="member.php?func=m_zone&mzt=tempstore&type=list";
            //重新組合包含limit的sql語法
            $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "TEMPSTORE_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow'");
                }
                foreach($row as $k => $v){
                    $tpl->assign(strtoupper($k),$v);
                }
                $tpl->assign(array(
                    "SERIAL" => $i,
                ));
            }
        }
        if($type=="history"){
            $tpl->newBlock( "TEMPSTORE_HISTORY_ZONE" );
            $sql="select a.*,b.createtime,b.amounts as op_amounts from (select a.*,b.p_name from ".$db->prefix("temp_store")." as a inner join ".$db->prefix('products')." as b on a.p_id=b.p_id ) as a inner join ".$db->prefix("temp_store_op")." as b on a.id=b.ts_id where a.id='".$id."' order by createtime desc";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $i=1;
                while($row = $db->fetch_array($selectrs,1)){
                    $tpl->newBlock( "TEMPSTORE_HISTORY_LIST" );
                    foreach($row as $k => $v){
                        if($k=="op_amounts"){
                            if($v>0){
                                $tpl->assign("IN_AMOUNTS",$v);
                            }else{
                                $tpl->assign("OUT_AMOUNTS",abs($v));
                            }
                        }else{
                            $tpl->assign(strtoupper($k),$v);
                        }
                    }
                    $tpl->assign(array(
                        "SERIAL" => $i++,
                    ));
                }
            }else{
                $url = $_SERVER['PHP_SELF']."?func=m_zone&mzt=tempstore";
                $main->js_notice("沒有記錄",$url);
            }
        }
    }       
}

?>