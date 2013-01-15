<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])  || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_member"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$member = new MEMBER;
class MEMBER{
    //會員資料欄位
    protected $columns = array(
        'm_company_name' => array('name'=>"公司",'gc'=>"Company"),
        'm_name'         => array('name'=>"名字",'gc'=>"First Name"),
        'm_birthday'     => array('name'=>"生日",'gc'=>'Birthday'),
        'm_sex'          => array("name"=>"性別","gc"=>"Gender","map"=>array(0=>'女',1=>'男')),
        'm_country'      => array("name"=>"國家","gc"=>"Home Country"),
        'm_zip'          => array("name"=>"郵遞區號","gc"=>"Home Postal Code"),
        'm_address'      => array("name"=>"地址","gc"=>"Home Address"),
        'm_tel'          => array("name"=>"電話","gc"=>"Home Phone"),
        'm_fax'          => array("name"=>"傳真","gc"=>"Home Fax"),
        'm_cellphone'    => array('name'=>'手機',"gc"=>"Mobile Phone"),
        'm_url'          => array("name"=>"主機","gc"=>"Web Page"),          
        'm_email'        => array('name'=>"電子郵件","gc"=>"E-mail Address"),   
    );
    function MEMBER(){
        global $db,$cms_cfg,$tpl;
        switch($_REQUEST["func"]){
            case "m_import":
                if($cms_cfg['ws_module']['ws_member_manipulate']!=1){
                    header("location:member.php?func=m_list");
                    die();
                }                
                $this->current_class="M_IM";
                $this->ws_tpl_file = "templates/ws-manage-member-data-import-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->member_data_import($_GET['act']);
                $this->ws_tpl_type=1;                
                break;
            case "m_export":
                if($cms_cfg['ws_module']['ws_member_manipulate']!=1){
                    header("location:member.php?func=m_list");
                    die();
                }
                $this->current_class="M_EX";
                $this->ws_tpl_file = "templates/ws-manage-member-data-export-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->member_data_export();
                $this->ws_tpl_type=1;                
                break;
            case "mc_list"://會員分類列表
                $this->current_class="MC";
                $this->ws_tpl_file = "templates/ws-manage-member-cate-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->member_cate_list();
                $this->ws_tpl_type=1;
                break;
            case "mc_add"://會員分類新增
                $this->current_class="MC";
                $this->ws_tpl_file = "templates/ws-manage-member-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $this->member_cate_form("add");
                $this->ws_tpl_type=1;
                break;
            case "mc_mod"://會員分類修改
                $this->current_class="MC";
                $this->ws_tpl_file = "templates/ws-manage-member-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $this->member_cate_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "mc_replace"://會員分類更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->member_cate_replace();
                $this->ws_tpl_type=1;
                break;
            case "mc_del"://會員分類刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->member_cate_del();
                $this->ws_tpl_type=1;
                break;
            case "m_list"://會員列表
                $this->current_class="M";
                $this->ws_tpl_file = "templates/ws-manage-member-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->member_list();
                $this->ws_tpl_type=1;
                break;
            case "m_add"://會員新增
                $this->current_class="M";
                $this->ws_tpl_file = "templates/ws-manage-member-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->member_form("add");
                $this->ws_tpl_type=1;
                break;
            case "m_mod"://會員修改
                $this->current_class="M";
                $this->ws_tpl_file = "templates/ws-manage-member-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->member_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "m_replace"://會員更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->member_replace();
                $this->ws_tpl_type=1;
                break;
            case "m_del"://會員刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->member_del();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //會員列表
                $this->current_class="M";
                $this->ws_tpl_file = "templates/ws-manage-member-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->member_list();
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
        $tpl->assignGlobal("CSS_BLOCK_MEMBER","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }

    //會員分類--列表
    function member_cate_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //IPB 不顯示會員折扣
        if($cms_cfg["ws_module"]["ws_version"]!="ipb") {
            $tpl->newBlock("SHOW_LIST_MC_DISCOUNT"); //會員分類列表折扣欄位
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_member_cate where mc_id > '0'";
        $and_str = "";
        if(!empty($_REQUEST["sk"])){
            $and_str = " and mc_subject like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by mc_sort ".$cms_cfg['sort_pos']." ";
        //取得總筆數
        $total_records=$main->count_total_records($sql);
        //取得分頁連結
        $func_str="member.php?func=mc_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        $page=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records);
        //重新組合包含limit的sql語法
        $sql=$main->sqlstr_add_limit($cms_cfg["op_limit"],$_REQUEST["nowp"],$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                  "VALUE_TOTAL_BOX" => $rsnum
        ));
        //分類列表
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "MEMBER_CATE_LIST" );
            $tpl->assign( array("VALUE_MC_ID"  => $row["mc_id"],
                                "VALUE_MC_STATUS"  => $row["mc_status"],
                                "VALUE_MC_SORT"  => $row["mc_sort"],
                                "VALUE_MC_SUBJECT" => $row["mc_subject"],
                                "VALUE_MC_SERIAL" => $i,
                                "VALUE_STATUS_IMG" => ($row["mc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                "VALUE_STATUS_IMG_ALT" => ($row["mc_status"])?$TPLMSG['ON']:$TPLMSG['OFF']));
            //IPB 不顯示會員折扣
            if($cms_cfg["ws_module"]["ws_version"]!="ipb") {
                $tpl->newBlock("SHOW_VALUE_MC_DISCOUNT");
                $tpl->assign("VALUE_MC_DISCOUNT", $row["mc_discount"]);
            }
        }
        if($i==0){
            $tpl->assignGlobal("MSG_NO_DATA",$TPLMSG['NO_DATA']);
        }else{
            //分頁顯示項目
            $tpl->newBlock( "PAGE_DATA_SHOW" );
            $tpl->assign( array("VALUE_TOTAL_RECORDS"  => $page["total_records"],
                                "VALUE_TOTAL_PAGES"  => $page["total_pages"],
                                "VALUE_PAGES_STR"  => $page["pages_str"],
                                "VALUE_PAGES_LIMIT"=>$cms_cfg["op_limit"]
            ));
            if($page["bj_page"]){
                $tpl->newBlock( "PAGE_BACK_SHOW" );
                $tpl->assign( "VALUE_PAGES_BACK"  , $page["bj_page"]);
                $tpl->gotoBlock("PAGE_DATA_SHOW");
            }
            if($page["nj_page"]){
                $tpl->newBlock( "PAGE_NEXT_SHOW" );
                $tpl->assign( "VALUE_PAGES_NEXT"  , $page["nj_page"]);
                $tpl->gotoBlock("PAGE_DATA_SHOW");
            }
        }
    }
    //會員分類--表單
    function member_cate_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //IPB 不顯示會員折扣
        if($cms_cfg["ws_module"]["ws_version"]!="ipb") {
            $tpl->newBlock("SHOW_FORM_MC_DISCOUNT"); //會員分類表單折扣欄位
        }else{
            $tpl->newBlock("SHOW_DEFAULT_FORM_MC_DISCOUNT");
        }
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "NOW_MC_ID"  => 0,
                                  "VALUE_MC_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_member_cate","mc","","",0),
                                  "STR_MC_STATUS_CK1" => "checked",
                                  "STR_MC_STATUS_CK0" => "",
                                  "VALUE_ACTION_MODE" => $action_mode
        ));
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        //如果為修改模式,帶入資料庫資料
        if($action_mode=="mod" && !empty($_REQUEST["mc_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_member_cate where mc_id='".$_REQUEST["mc_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("VALUE_MC_ID"  => $row["mc_id"],
                                          "VALUE_MC_SORT"  => $row["mc_sort"],
                                          "VALUE_MC_SUBJECT" => $row["mc_subject"],
                                          "VALUE_MC_DISCOUNT" => $row["mc_discount"],
                                          "STR_MC_STATUS_CK1" => ($row["mc_status"])?"checked":"",
                                          "STR_MC_STATUS_CK0" => ($row["mc_status"])?"":"checked",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
            }else{
                header("location : member.php?func=mc_list");
            }
        }
    }
    //會員分類--資料更新
    function member_cate_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_member_cate (
                        mc_status,
                        mc_sort,
                        mc_subject,
                        mc_discount
                    ) values (
                        '".$_REQUEST["mc_status"]."',
                        '".$_REQUEST["mc_sort"]."',
                        '".$_REQUEST["mc_subject"]."',
                        '".$_REQUEST["mc_discount"]."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_member_cate set
                        mc_status=".$_REQUEST["mc_status"].",
                        mc_sort='".$_REQUEST["mc_sort"]."',
                        mc_subject='".$_REQUEST["mc_subject"]."',
                        mc_discount='".$_REQUEST["mc_discount"]."'
                    where mc_id='".$_REQUEST["mc_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."member.php?func=mc_list&mc_id=".$_REQUEST["mc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //會員分類--刪除
    function member_cate_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["mc_id"]){
            $mc_id=array(0=>$_REQUEST["mc_id"]);
        }else{
            $mc_id=$_REQUEST["id"];
        }
        if(!empty($mc_id)){
            $mc_id_str = implode(",",$mc_id);
            //清空分類底下的會員
            $sql="delete from ".$cms_cfg['tb_prefix']."_member where mc_id in (".$mc_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                //刪除分類
                $sql="delete from ".$cms_cfg['tb_prefix']."_member_cate where mc_id in (".$mc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."member.php?func=mc_list&mc_id=".$_REQUEST["mc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//會員--列表================================================================
    function member_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $sql="select * from ".$cms_cfg['tb_prefix']."_member_cate where mc_id > '0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        //沒有分類先建立分類
        if($rsnum < 1){
            $tpl->assignGlobal( "MSG_CREATE_CATE_FIRST" , $TPLMSG["CREATE_CATE_FIRST"]);
            $goto_url=$cms_cfg["manage_url"]."member.php?func=mc_add";
            $this->goto_target_page($goto_url);
        }else{
            //會員分類
            $i=0;
            $tpl->assignGlobal("TAG_NOW_CATE" ,$TPLMSG["NO_CATE"]);
            while($row = $db->fetch_array($selectrs,1)){
                $i++;
                $tpl->newBlock( "MEMBER_CATE_LIST" );
                $tpl->assign( array( "VALUE_MC_SUBJECT"  => $row["mc_subject"],
                                     "VALUE_MC_ID" => $row["mc_id"],
                                     "VALUE_MC_SERIAL" => $i,
                                     "VALUE_STATUS_IMG" => ($row["mc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                     "VALUE_STATUS_IMG_ALT" => ($row["mc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
                ));
                if($i%4==0){
                    $tpl->assign("TAG_MEMBER_CATE_TRTD","</tr><tr>");
                }
                if($row["mc_id"]==$_REQUEST["mc_id"]){
                    $tpl->assignGlobal("TAG_NOW_CATE",$row["mc_subject"]);
                }
            }
            //會員列表
            $sql="select m.*,mc.mc_subject from ".$cms_cfg['tb_prefix']."_member as m left join ".$cms_cfg['tb_prefix']."_member_cate as mc on m.mc_id=mc.mc_id where m.m_id > '0'";
            //附加條件
            $and_str="";
            if(!empty($_REQUEST["mc_id"])){
                $and_str .= " and m.mc_id = '".$_REQUEST["mc_id"]."'";
            }
            if($_REQUEST["st"]=="m_name"){
                $and_str .= " and m.m_name like '%".$_REQUEST["sk"]."%'";
            }
            $sql .= $and_str." order by m.m_sort ".$cms_cfg['sort_pos'].",m.m_modifydate desc ";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records    = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="member.php?func=m_list&mc_id=".$_REQUEST["mc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
            $page=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records);
            //重新組合包含limit的sql語法
            $sql=$main->sqlstr_add_limit($cms_cfg["op_limit"],$_REQUEST["nowp"],$sql);
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $tpl->assignGlobal( array("VALUE_TOTAL_BOX" => $rsnum,
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                      "TAG_SELECT_SEARCH_TARGET_NAME" => $TPLMSG['NAME'],
                                      "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],

            ));
            $tpl->assignGlobal( "VALUE_NOW_MC_ID" , $_REQUEST["mc_id"]);
            $i=$page["start_serial"];
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "MEMBER_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow'");
                }
                $tpl->assign( array("VALUE_MC_ID"  => $row["mc_id"],
                                    "VALUE_M_ID"  => $row["m_id"],
                                    "VALUE_M_SORT"  => $row["m_sort"],
                                    "VALUE_M_NAME" => $row["m_name"],
                                    "VALUE_M_SERIAL" => $i,
                                    "VALUE_MC_SUBJECT"  => $row["mc_subject"],
                                    "VALUE_STATUS_IMG" => ($row["m_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                    "VALUE_STATUS_IMG_ALT" => ($row["m_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

                ));

            }
            if($i==0){
                $tpl->assignGlobal("MSG_NO_DATA",$TPLMSG['NO_DATA']);
            }else{
                $tpl->newBlock( "PAGE_DATA_SHOW" );
                $tpl->assign( array("VALUE_TOTAL_RECORDS"  => $page["total_records"],
                                    "VALUE_TOTAL_PAGES"  => $page["total_pages"],
                                    "VALUE_PAGES_STR"  => $page["pages_str"],
                                    "VALUE_PAGES_LIMIT"=>$cms_cfg["op_limit"]
                ));
                if($page["bj_page"]){
                    $tpl->newBlock( "PAGE_BACK_SHOW" );
                    $tpl->assign( "VALUE_PAGES_BACK"  , $page["bj_page"]);
                    $tpl->gotoBlock("PAGE_DATA_SHOW");
                }
                if($page["nj_page"]){
                    $tpl->newBlock( "PAGE_NEXT_SHOW" );
                    $tpl->assign( "VALUE_PAGES_NEXT"  , $page["nj_page"]);
                    $tpl->gotoBlock("PAGE_DATA_SHOW");
                }
            }
        }
    }
//會員--表單================================================================
    function member_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $main->load_js_msg();
        //欄位名稱
        $cate=(trim($_REQUEST["mc_id"])!="")?1:0;
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "VALUE_M_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_member","m","mc_id",$_REQUEST["mc_id"],$cate),
                                  "STR_M_STATUS_CK2" => "",
                                  "STR_M_STATUS_CK1" => "checked",
                                  "STR_M_STATUS_CK0" => "",
                                  "STR_M_CS_CK1" =>"selected",
                                  "STR_M_CS_CK2" => "",
                                  "STR_M_CS_CK3" => "",
                                  "STR_M_EPAPER_STATUS_CK1" => "checked",
                                  "STR_M_EPAPER_STATUS_CK0" => "",
                                  "VALUE_ACTION_MODE" => $action_mode
        ));
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        //如果為修改模式,帶入資料庫資料
        if($action_mode=="mod" && !empty($_REQUEST["m_id"])){
            $tpl->newBlock( "MEMBER_MOD_MODE" );
            $sql="select * from ".$cms_cfg['tb_prefix']."_member where m_id='".$_REQUEST["m_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("VALUE_M_ID"  => $row["m_id"],
                                          "VALUE_M_SORT"  => $row["m_sort"],
                                          "VALUE_M_ACCOUNT" => $row["m_account"],
                                          "VALUE_M_PASSWORD" => $row["m_password"],
                                          "VALUE_M_COMPANY_NAME" => $row["m_company_name"],
                                          "VALUE_M_NAME" => $row["m_name"],
                                          "VALUE_M_BIRTHDAY" => $row["m_birthday"],
                                          "VALUE_M_ZIP" => $row["m_zip"],
                                          "VALUE_M_ADDRESS" => $row["m_address"],
                                          "VALUE_M_TEL" => $row["m_tel"],
                                          "VALUE_M_FAX" => $row["m_fax"],
                                          "VALUE_M_EMAIL" => $row["m_email"],
                                          "VALUE_M_CELLPHONE" => $row["m_cellphone"],
                                          "STR_M_CS_CK1" => ($row["m_contact_s"]=="Mr.")?"selected":"",
                                          "STR_M_CS_CK2" => ($row["m_contact_s"]=="Miss.")?"selected":"",
                                          "STR_M_CS_CK3" => ($row["m_contact_s"]=="Mrs.")?"selected":"",
                                          "STR_M_STATUS_CK2" => ($row["m_status"]==2)?"checked":"",
                                          "STR_M_STATUS_CK1" => ($row["m_status"]==1)?"checked":"",
                                          "STR_M_STATUS_CK0" => ($row["m_status"]==0)?"checked":"",
                                          "STR_M_EPAPER_STATUS_CK1" => ($row["m_epaper_status"]==1)?"checked":"",
                                          "STR_M_EPAPER_STATUS_CK0" => ($row["m_epaper_status"]==0)?"checked":"",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
            }else{
                header("location : member.php?func=m_list");
            }
        }else{
            $tpl->newBlock( "MEMBER_ADD_MODE" );
        }
        //國家下拉選單
        if($cms_cfg["ws_module"]["ws_member_country"]==1) {
            $main->country_select($row["m_country"]);
        }        
        //會員分類
        $sql="select * from ".$cms_cfg['tb_prefix']."_member_cate where mc_id > '0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        while($row1 = $db->fetch_array($selectrs,1)){
            $tpl->newBlock( "SELECT_OPTION_MEMBER_CATE" );
            $tpl->assign( array( "OPTION_MEMBER_CATE_NAME"  => $row1["mc_subject"],
                                 "OPTION_MEMBER_CATE_VALUE" => $row1["mc_id"],
                                 "STR_MC_SEL"       => ($row1["mc_id"]==$row["mc_id"])?"selected":""
            ));
        }
    }
//會員--資料更新================================================================
    function member_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_member (
                        mc_id,
                        m_status,
                        m_sort,
                        m_modifydate,
                        m_account,
                        m_password,
                        m_company_name,
                        m_country,
                        m_contact_s,
                        m_name,
                        m_birthday,
                        m_sex,
                        m_zip,
                        m_address,
                        m_tel,
                        m_fax,
                        m_cellphone,
                        m_email,
                        m_epaper_status
                    ) values (
                        '".$_REQUEST["mc_id"]."',
                        ".$_REQUEST["m_status"].",
                        '".$_REQUEST["m_sort"]."',
                        '".date("Y-m-d H:i:s")."',
                        '".$_REQUEST["m_account"]."',
                        '".$_REQUEST["m_password"]."',
                        '".$_REQUEST["m_company_name"]."',
                        '".$_REQUEST["m_country"]."',
                        '".$_REQUEST["m_contact_s"]."',
                        '".$_REQUEST["m_name"]."',
                        '".$_REQUEST["m_birthday"]."',
                        '".$_REQUEST["m_sex"]."',
                        '".$_REQUEST["m_zip"]."',
                        '".$_REQUEST["m_address"]."',
                        '".$_REQUEST["m_tel"]."',
                        '".$_REQUEST["m_fax"]."',
                        '".$_REQUEST["m_cellphone"]."',
                        '".$_REQUEST["m_email"]."',
                        '".$_REQUEST["m_epaper_status"]."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_member set
                        mc_id='".$_REQUEST["mc_id"]."',
                        m_status='".$_REQUEST["m_status"]."',
                        m_sort='".$_REQUEST["m_sort"]."',
                        m_modifydate='".date("Y-m-d H:i:s")."',
                        m_password='".$_REQUEST["m_password"]."',
                        m_company_name='".$_REQUEST["m_company_name"]."',
                        m_country='".$_REQUEST["m_country"]."',
                        m_contact_s='".$_REQUEST["m_contact_s"]."',
                        m_name='".$_REQUEST["m_name"]."',
                        m_birthday='".$_REQUEST["m_birthday"]."',
                        m_sex='".$_REQUEST["m_sex"]."',
                        m_zip='".$_REQUEST["m_zip"]."',
                        m_address='".$_REQUEST["m_address"]."',
                        m_tel='".$_REQUEST["m_tel"]."',
                        m_fax='".$_REQUEST["m_fax"]."',
                        m_cellphone='".$_REQUEST["m_cellphone"]."',
                        m_email='".$_REQUEST["m_email"]."',
                        m_epaper_status='".$_REQUEST["m_epaper_status"]."'
                    where m_id='".$_REQUEST["m_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."member.php?func=m_list&mc_id=".$_REQUEST["mc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//會員--刪除--資料刪除可多筆處理================================================================
    function member_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["m_id"]){
            $m_id=array(0=>$_REQUEST["m_id"]);
        }else{
            $m_id=$_REQUEST["id"];
        }
        if(!empty($m_id)){
            $m_id_str = implode(",",$m_id);
            //刪除勾選的會員
            $sql="delete from ".$cms_cfg['tb_prefix']."_member where m_id in (".$m_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."member.php?func=m_list&mc_id=".$_REQUEST["mc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //顯示訊息並重新導向
    function goto_target_page($url,$sec=0){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }
    //更改狀態
    function change_status($ws_table,$value){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //會員分類更改狀態
        if($ws_table=="mc"){
            if($_REQUEST["mc_id"]){
                $mc_id=array(0=>$_REQUEST["mc_id"]);
            }else{
                $mc_id=$_REQUEST["id"];
            }
            if(!empty($mc_id)){
                $mc_id_str = implode(",",$mc_id);
                //更改分類底下的會員狀態
                $sql="update ".$cms_cfg['tb_prefix']."_member set m_status='".$value."' where mc_id in (".$mc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    //狀態分類狀態
                    $sql="update ".$cms_cfg['tb_prefix']."_member_cate set mc_status='".$value."' where mc_id in (".$mc_id_str.")";
                    $rs = $db->query($sql);
                    $db_msg = $db->report();
                    if ( $db_msg == "" ) {
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                        $goto_url=$cms_cfg["manage_url"]."member.php?func=mc_list&mc_id=".$_REQUEST["mc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                        $this->goto_target_page($goto_url);
                    }else{
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                    }
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //會員更改狀態
        if($ws_table=="m"){
            if($_REQUEST["m_id"]){
                $m_id=array(0=>$_REQUEST["m_id"]);
            }else{
                $m_id=$_REQUEST["id"];
            }
            if(!empty($m_id)){
                $m_id_str = implode(",",$m_id);
                //刪除勾選的會員
                $sql="update ".$cms_cfg['tb_prefix']."_member set m_status='".$value."' where m_id in (".$m_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."member.php?func=m_list&mc_id=".$_REQUEST["mc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
    }
    //更改排序值
    function change_sort($ws_table){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //會員分類更改排序值
        if(!empty($_REQUEST["sort_value"]) && !empty($ws_table)){
            if($ws_table=="mc"){
                $table_name=$cms_cfg['tb_prefix']."_member_cate";
            }
            if($ws_table=="m"){
                $table_name=$cms_cfg['tb_prefix']."_member";
            }
            foreach($_REQUEST["id"] as $key => $value){
                $sql="update ".$table_name." set ".$ws_table."_sort='".$_REQUEST["sort_value"][$value]."' where ".$ws_table."_id='".$value."'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
            }
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."member.php?func=".$ws_table."_list&mc_id=".$_REQUEST["mc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //複製單筆資料
    function copy_data($ws_table){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //會員分類複製
        if($ws_table=="mc"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_member_cate where mc_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_member_cate (
                        mc_status,
                        mc_sort,
                        mc_subject,
                        mc_discount
                    ) values (
                        '".$row["mc_status"]."',
                        '".$row["mc_sort"]."',
                        '".$row["mc_subject"]."',
                        '".$row["mc_discount"]."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."member.php?func=mc_list&mc_id=".$_REQUEST["mc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //會員複製
        if($ws_table=="m"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_member where m_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ws_member (
                        mc_id,
                        m_status,
                        m_sort,
                        m_modifydate,
                        m_account,
                        m_password,
                        m_company_name,
                        m_contact_s,
                        m_name,
                        m_birthday,
                        m_sex,
                        m_zip,
                        m_address,
                        m_tel,
                        m_fax,
                        m_cellphone,
                        m_email,
                        m_epaper_status
                    ) values (
                        '".$row["mc_id"]."',
                        ".$row["m_status"].",
                        '".$row["m_sort"]."',
                        '".date("Y-m-d H:i:s")."',
                        '".$row["m_account"]."',
                        '".$row["m_password"]."',
                        '".$row["m_company_name"]."',
                        '".$row["m_contact_s"]."',
                        '".$row["m_name"]."',
                        '".$row["m_birthday"]."',
                        '".$row["m_sex"]."',
                        '".$row["m_zip"]."',
                        '".$row["m_address"]."',
                        '".$row["m_tel"]."',
                        '".$row["m_fax"]."',
                        '".$row["m_cellphone"]."',
                        '".$row["m_email"]."',
                        '".$row["m_epaper_status"]."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."member.php?func=m_list&mc_id=".$_REQUEST["mc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }

    }

    //資料處理
    function data_processing(){
        switch ($_REQUEST["process_type"]){
            case "del":
                if($_REQUEST["ws_table"]=="mc"){
                    $this->member_cate_del();
                }
                if($_REQUEST["ws_table"]=="m"){
                    $this->member_del();
                }
                break;
            case "copy":
                $this->copy_data($_REQUEST["ws_table"]);
                break;
            case "status":
                $this->change_status($_REQUEST["ws_table"],$_REQUEST["value"]);
                break;
            case "sort":
                $this->change_sort($_REQUEST["ws_table"]);
                break;
        }
    }
    //會員資料匯入
    function member_data_import($act){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $target_csv = $_SERVER['DOCUMENT_ROOT'].$cms_cfg['file_root']."upload_files/wait_to_map.csv";
        switch($act){
            case "preview":
                $tpl->assignGlobal("IMPORT_ACTION","選擇匯入筆數");                
                $tpl->newBlock("PREVIEW_LIST");
                if(is_array($_POST['mapto'])){
                    $tpl->newBlock("SEND_TO_SAVE");
                    $tpl->assign("COL_SPAN",count($_POST['mapto'])+1);
                    $colkeys = array_keys($_POST['mapto']);
                    $res = fopen($target_csv,'r');
                    $s=0;
                    while($tmp = fgets($res, 3000)){
                        $csv_row = explode(',',$tmp);
                        if($s==0){
                            $row_type = "TITLE_ROW";
                            $data_type = "SELECTED_COLUMN";
                        }else{
                            $row_type = "DATA_ROW";
                            $data_type = "SELECTED_DATA";
                        }
                        $tpl->newBlock($row_type);
                        $tpl->assign("VALUE_ROW_INDEX",$s);
                        foreach($colkeys as $k){
                            $tpl->newBlock($data_type);
                            $tpl->assign("VALUE_COL_DATA",  $csv_row[$k]);
                            if($s==0){
                                $tpl->assign(array(
                                   "VALUE_MAPTO_CNAME" => $_POST['mapto'][$k],
                                   "VALUE_COL_INDEX" => $k,
                                ));
                            }
                        }
                        $s++;
                    }                    
                }else{ //沒有選擇任何對應欄位
                    $tpl->newBlock("NO_COLUMN_SELECTED");
                }
                break;
            case "select":
                $tpl->assignGlobal("IMPORT_ACTION","選擇匯入欄位");
                if($this->_save_csv_file($_FILES['csvfile']['tmp_name'],$target_csv)){
                    $tpl->newBlock("SELECT_IMPORT_COLUMN");
                    $res = fopen($target_csv,'r');
                    $tmp = fgets($res, 3000);
                    $csv_title = explode(',',$tmp);
                    $nums_csv_col = count($csv_title);
                    if($nums_csv_col){
                        foreach($csv_title as $k=>$title){
                            $tpl->newBlock("CSV_COLUMNS");
                            $tpl->assign(array(
                                "VALUE_COL_INDEX" => $k,
                                "VALUE_COL_NAME"  => $title,
                            ));
                        }
                        //顯示資料欄位
                        $dbcol = array();
                        foreach($this->columns as $info){
                            $dbcol[] = $info['name'];
                        }
                        if(count($dbcol)){
                            $tpl->newBlock("DATA_COLUMN");
                            $tpl->assign("VALUE_DATA_COLUMN",implode(",",$dbcol));
                        }
                        fclose($res);                        
                        $tpl->newBlock("SEND_TO_MAP");
                    }else{
                        $tpl->newBlock("NO_DATA_TO_IMPORT");
                    }
                }else{
                    header("location:member.php?func=m_list");
                    die();
                }                
                break;
            case "save":
                if($_POST['mapto'] && $_POST['row_id']){
                    $tpl->assignGlobal("IMPORT_ACTION","儲存對應");
                    $tpl->newBlock("SAVING_RESULT");
                    $msg="";
                    $res = fopen($target_csv,'r');
                    $i=0;
                    $wNums = 0; //寫入筆數
                    $cNums = 0; //衝突筆數
                    while($tmp = fgets($res, 2000)){
                        //$enc_type = mb_detect_encoding($tmp)?mb_detect_encoding($tmp):"big-5";
                        if($i>0 && in_array($i,$_POST['row_id'])){
                            $csv = explode(',',$tmp);
                            $columns = array('mc_id','m_status');
                            $values = array('1','0');
                            $conflic = false;
                            foreach($_POST['mapto'] as $idx => $col){
                                if($col=="m_email" && $csv[$idx]!=''){
                                    $sql = "select * from ".$cms_cfg['tb_prefix']."_member where m_account='".$csv[$idx]."'";
                                    $res_m = $db->query($sql,true);
                                    $conflic = ($db->numRows($res_m))?true:false;
                                    $columns[] = 'm_account';
                                    $values[] = "'".$csv[$idx]."'";
                                }
                                $columns[] = $col;
                                $values[] = "'".$csv[$idx]."'";
                            }
                            if($conflic){
                                $tpl->newBlock("CONFLIC_RECORD");
                                $cNums++;
                            }else{
                                $tpl->newBlock("WRITED_RECORD");
                                $wNums++;
                                $sql = "insert into ".$cms_cfg['tb_prefix']."_member(".implode(',',$columns).")values(".implode(',',$values).")";
                                $db->query($sql,true);
//                                $tpl->assign("VALUE_QUERY",$sql);
                            }
                            $tpl->assign("VALUE_RECORD",implode(',',$values));
                        }
                        $i++;
                    }
                    $tpl->gotoBlock("SAVING_RESULT");
                    $tpl->assign(array(
                        "VALUE_SUCCESS_NUMS"  => $wNums, 
                        "VALUE_CONFLICT_NUMS" => $cNums 
                    ));
                    unlink($target_csv);
                }else{
                    unlink($target_csv);
                    header('location:member.php?func=m_list');
                    die();
                }
                break;
            case "map":
                if( file_exists($target_csv) && is_array($_POST['csvcol'])){
                    $tpl->assignGlobal("IMPORT_ACTION","csv欄位對應");
                    $tpl->newBlock("COLUMN_MAP");
                    $res = fopen($target_csv,'r');
                    $tmp = fgets($res, 3000);
                    $csv_title = explode(',',$tmp);
                    $columns = array_keys($this->columns);
                    foreach($_POST['csvcol'] as $colkey){
                        $tpl->newBlock("CSV_COLUMN");
                        $tpl->assign(array(
                            "VALUE_COL_INDEX" => $colkey,
                            "VALUE_COL_NAME"  => $csv_title[$colkey],
                        ));
                        foreach($columns as $s => $col){
                            $tpl->newBlock('MAPTO_LIST');
                            $tpl->assign(array(
                               "SERIAL"            => $s, 
                               "VALUE_COL_INDEX"   => $colkey, 
                               "VALUE_MAPTO_CNAME" => $col, 
                               "VALUE_MAPTO_NAME"  => $this->columns[$col]['name'], 
                            ));
                        }
                    }                    
                }else{
                    header("location:member.php?func=m_list");
                    die();
                }
                break;
            default:
                $tpl->assignGlobal("IMPORT_ACTION","上傳csv");
                $tpl->newBlock("SELECT_CSV_FILE");
        }
    }
    //會員資料匯出
    function member_data_export(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_POST){
            //有勾選mc_id才繼續處理
            if($_POST['mc_id'] && $_POST['columns']){
                //取得勾選的欄位及匯出抬頭
                header('content-type:text/csv');
                header("content-disposition: attachment; filename=memberdata.csv");                
                $wanted_column = $_POST['columns'];
                array_walk($wanted_column,array($this,"_format_csv_title"),$this->columns);
                $this->_format_csv_line($wanted_column);
                //取得欲匯出的會員類別
                $sql = "select m.* from ".$cms_cfg['tb_prefix']."_member as m inner join ".$cms_cfg['tb_prefix']."_member_cate as mc on m.mc_id=mc.mc_id where mc_status='1' and m_status='1' and mc.mc_id in(".implode(',',$_POST['mc_id']).")";
                $res = $db->query($sql);
                while($row = $db->fetch_array($res,1)){
                    $wanted_column = array();
                    foreach($_POST['columns'] as $col){
                        $value = (is_array($this->columns[$col]['map']))?$this->columns[$col]['map'][$row[$col]]:$row[$col];
                        $wanted_column[] = "".$value."";
                    }
                    //匯出資料
                    $this->_format_csv_line($wanted_column);
                }
            }
            die();   
        }
        //會員分類
        $sql = "select * from ".$cms_cfg['tb_prefix']."_member_cate where mc_status='1' order by mc_sort ".$cms_cfg['sort_pos'];
        $res = $db->query($sql);
        while($row = $db->fetch_array($res,1)){
            $tpl->newBlock("MEMBER_CATE_LIST");
            $tpl->assign(array(
                "VALUE_MC_ID"      => $row['mc_id'],
                "VALUE_MC_SUBJECT" => $row['mc_subject'],
            ));
        }
        foreach($this->columns as $col=>$info){
            $tpl->newBlock("DATA_COLUMN_LIST");
            $tpl->assign(array(
                "VALUE_M_COLUMN"      => $col, 
                "VALUE_M_COLUMN_NAME" => $info['name'], 
            ));
        }
    }

    function _format_csv_title(&$value,$key,$columns){
        $column_name = ($columns[$value]['gc'])?$columns[$value]['gc']:$columns[$value]['name'];
        $value = $column_name;
    }
    
    function _format_csv_column(&$value,$key){
        $value = "".$value."";
    }
    
    function _format_csv_line($line_data){
        echo implode(',',$line_data)."\r\n";
    }
    
    function _save_csv_file($tmp_name,$new_file_name){
        //$_POST['charset_of_file'];
        if(is_uploaded_file($tmp_name)){
            $fp = fopen($tmp_name,'r');
            $fp2 = fopen($new_file_name,'w');
            while($str = fgets($fp, 3000)){
                fwrite($fp2, mb_convert_encoding($str, "utf-8",$_POST['charset_of_file']));
            }
            fclose($fp);
            fclose($fp2);
            return $new_file_name;
        }
    }
    
}
//ob_end_flush();
?>