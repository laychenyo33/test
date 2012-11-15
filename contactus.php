<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$cus = new CONTACTUS;
class CONTACTUS{
    function CONTACTUS(){
        global $db,$cms_cfg,$tpl;
        $this->m_id=$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ID"];
        switch($_REQUEST["func"]){
            case "cu_add"://聯絡我們新增
                $this->ws_tpl_file = "templates/ws-contactus-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $this->contactus_form();
                $this->ws_tpl_type=1;
                break;
            case "cu_replace"://聯絡我們更新資料(replace)
                $this->ws_tpl_file = "templates/ws-mail-tpl.html";
                $tpl = new TemplatePower( $this->ws_tpl_file );
                $tpl->prepare();
                $this->contactus_replace();
                $this->ws_tpl_type=0;
                break;
            default:    //聯絡我們列表
                $this->ws_tpl_file = "templates/ws-contactus-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $this->contactus_form();
                $this->ws_tpl_type=1;
                break;
        }
        if($this->ws_tpl_type){
            $tpl->printToScreen();
        }
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$db,$ws_array,$TPLMSG,$main;
        $tpl = new TemplatePower( $cms_cfg['base_all_tpl'] );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方一般表單
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        $tpl->assignInclude( "AD_IMG", "templates/ws-fn-ad-image-tpl.html"); //圖片廣告模板
        $tpl->assignInclude( "AD_TXT", "templates/ws-fn-ad-txt-tpl.html"); //文字廣告模板        
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["CONTACT_US"]);
        $tpl->assignGlobal( "TAG_LAYER" , $TPLMSG["CONTACT_US"]);
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["contactus"]);//左方menu title
        $tpl->assignGlobal( "TAG_CONTACTUS_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["contactus"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-contactus"); //主要顯示區域的css設定
        $main->header_footer("contactus", $TPLMSG["CONTACT_US"]);
        $main->google_code(); //google analystics code , google sitemap code
        $main->math_security();
        if($cms_cfg["ws_module"]["ws_left_main_pc"]==1){
            $main->left_fix_cate_list();
        }
    }

//聯絡我們--表單================================================================
    function contactus_form(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$ws_array,$main;
        $tpl->assignGlobal(array("VALUE_CU_NAME" => $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_name"],
                                 "VALUE_CU_COMPANY_NAME" => $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_company_name"],
                                 "VALUE_CU_ADDRESS" => $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_address"],
                                 "VALUE_CU_TEL" => $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_tel"],
                                 "VALUE_CU_FAX" => $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_fax"],
                                 "VALUE_CU_CONTENT" => $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_content"],
                                 "STR_M_CS_CK1" => ($_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_contact_s"]=="Mr.")?"selected":"",
                                 "STR_M_CS_CK2" => ($_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_contact_s"]=="Miss.")?"selected":"",
                                 "STR_M_CS_CK3" => ($_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_contact_s"]=="Mrs.")?"selected":"",
                                 "VALUE_CU_EMAIL" => $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_email"]));
        foreach($ws_array["contactus_cate"] as $key =>$value){
            $i++;
            $tpl->newBlock( "TAG_SELECT_CONTACTUS_CATE" );
            $tpl->assign( array("TAG_SELECT_CONTACTUS_CATE_NAME"  => $value,
                                "TAG_SELECT_CONTACTUS_CATE_VALUE" => $key));
        }
        //欄位名稱
        $tpl->assignGlobal( array("MSG_NAME"  => $TPLMSG['MEMBER_NAME'],
                                  "MSG_CONTACT_PERSON" =>$TPLMSG['CONTACT_PERSON'],
                                  "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                                  "MSG_MODE" => $TPLMSG['SEND'],
                                  "MSG_CATE" => $TPLMSG['CATE'],
                                  "MSG_ADDRESS" => $TPLMSG['ADDRESS'],
                                  "MSG_TEL" => $TPLMSG['TEL'],
                                  "MSG_FAX" => $TPLMSG['FAX'],
                                  "MSG_CONTENT" => $TPLMSG['CONTENT']));
        //國家下拉選單
        if($cms_cfg["ws_module"]["ws_country"]==1) {
            $main->country_select($_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_country"]);
        }
        //聯絡我們資料
        $sql="select st_contactus_term from ".$cms_cfg['tb_prefix']."_service_term  where st_id='1'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $row = $db->fetch_array($selectrs,1);
        $contentus_term=trim($row["st_contactus_term"]);
        if(!empty($contentus_term)){
            $row["st_contactus_term"]=preg_replace("/src=\"([^>]+)upload_files/","src=\"".$cms_cfg["file_root"]."upload_files",$row["st_contactus_term"]);
            $tpl->assignGlobal("MSG_CONTACTUS_TERM",$row["st_contactus_term"]);
        }
        //啟用驗証碼顯示錯誤訊息
        if($cms_cfg["ws_module"]["ws_security"]==1 && $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["security_error"]==1){
            $tpl->assignGlobal("MSG_ERROR_MESSAGE",$TPLMSG['SECURITY_ERROR']);
        }
    }
//聯絡我們--資料更新================================================================
    function contactus_replace(){
        global $db,$tpl,$cms_cfg,$ws_array,$TPLMSG,$main;
            $main->magic_gpc($_REQUEST);
            if($cms_cfg["ws_module"]["ws_security"]==1){
                $pass=(isset($_POST['callback']) && $main->math_security_isvalue())?1:0;
            }else{
                $pass=1;
            }
            if($pass){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_contactus (
                        m_id,
                        cu_cate,
                        cu_status,
                        cu_company_name,
                        cu_contact_s,
                        cu_name,
                        cu_tel,
                        cu_fax,
                        cu_country,
                        cu_address,
                        cu_email,
                        cu_content,
                        cu_modifydate
                    ) values (
                        '".$this->m_id."',
                        '".mysql_real_escape_string($_REQUEST["cu_cate"])."',
                        '0',
                        '".mysql_real_escape_string($_REQUEST["cu_company_name"])."',
                        '".mysql_real_escape_string($_REQUEST["cu_contact_s"])."',
                        '".mysql_real_escape_string($_REQUEST["cu_name"])."',
                        '".mysql_real_escape_string($_REQUEST["cu_tel"])."',
                        '".mysql_real_escape_string($_REQUEST["cu_fax"])."',
                        '".mysql_real_escape_string($_REQUEST["cu_country"])."',
                        '".mysql_real_escape_string($_REQUEST["cu_address"])."',
                        '".mysql_real_escape_string($_REQUEST["cu_email"])."',
                        '".mysql_real_escape_string($_REQUEST["cu_content"])."',
                        '".date("Y-m-d H:i:s")."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    unset($_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]);
                    //新增完成寄送確認信
                    //寄送訊息
                    $tpl->newBlock("CONTACTUS_MAIL");
                    $tpl->assign(array(
                            "MSG_NAME"  => $TPLMSG['MEMBER_NAME'],
                            "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                            "MSG_CONTACT_PERSON" =>$TPLMSG['CONTACT_PERSON'],
                            "MSG_CATE" => $TPLMSG['CATE'],
                            "MSG_ADDRESS" => $TPLMSG['ADDRESS'],
                            "MSG_TEL" => $TPLMSG['TEL'],
                            "MSG_FAX" => $TPLMSG['FAX'],
                            "MSG_CONTENT" => $TPLMSG['CONTENT'],
                            "VALUE_CUC_SUBJECT"  => $ws_array["contactus_cate"][$_REQUEST["cu_cate"]],
                            "VALUE_CU_COMPANY_NAME" => $_REQUEST["cu_company_name"],
                            "VALUE_CU_CONTACT_S" => $_REQUEST["cu_contact_s"],
                            "VALUE_CU_NAME" => $_REQUEST["cu_name"],
                            "VALUE_CU_FAX" => $_REQUEST["cu_fax"],
                            "VALUE_CU_TEL" => $_REQUEST["cu_tel"],
                            "VALUE_CU_ADDRESS" => $_REQUEST["cu_address"],
                            "VALUE_CU_EMAIL" => $_REQUEST["cu_email"],
                            "VALUE_CU_CONTENT" => (get_magic_quotes_gpc())?stripcslashes($_REQUEST["cu_content"]):$_REQUEST["cu_content"]
                    ));
                    //國家欄位
                    if($cms_cfg["ws_module"]["ws_country"]==1) {
                        $tpl->newBlock("CONTACTUS_COUNTRY_ZONE");
                        $tpl->assign(array("MSG_COUNTRY" =>$TPLMSG['COUNTRY'],
                                            "VALUE_CU_COUNTRY" =>$_REQUEST["cu_country"]
                        ));
                    }
                    //引入聯絡我們自動回覆說明
                    $sql="select st_inquiry_mail from ".$cms_cfg['tb_prefix']."_service_term where st_id='1'";
                    $selectrs = $db->query($sql);
                    $row = $db->fetch_array($selectrs,1);
                    $rsnum = $db->numRows($selectrs);
                    if(!$rsnum){
                        $tpl->assignGlobal( "VALUE_TERM" , $TPLMSG['CONTACT_US_MAIL_NOTICE']);
                    }else{
                        $tpl->assignGlobal( "VALUE_TERM" , $row['st_inquiry_mail']);
                    }
                    $mail_content=$tpl->getOutputContent();
                    $main->ws_mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$_REQUEST["cu_email"],$mail_content,$TPLMSG['CONTACT_US'],"cu","");
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }else{
                $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]=array();
                foreach($_REQUEST as $key => $value){
                    if(eregi("cu_",$key)){
                        $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["$key"]=$value;
                    }
                }
                $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["security_error"]=1;
                header("location:contactus.php");
            }
    }
    //顯示訊息並重新導向
    function goto_target_page($url,$sec=1){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }
}
?>