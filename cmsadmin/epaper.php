<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])  || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_epaper"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$epaper = new EPAPER;
class EPAPER{
    function EPAPER(){
        global $db,$cms_cfg,$tpl;
        switch($_REQUEST["func"]){
            case "ec_list"://電子報分類列表
                $this->current_class="EC";
                $this->ws_tpl_file = "templates/ws-manage-epaper-cate-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->epaper_cate_list();
                $this->ws_tpl_type=1;
                break;
            case "ec_add"://電子報分類新增
                $this->current_class="EC";
                $this->ws_tpl_file = "templates/ws-manage-epaper-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->epaper_cate_form("add");
                $this->ws_tpl_type=1;
                break;
            case "ec_mod"://電子報分類修改
                $this->current_class="EC";
                $this->ws_tpl_file = "templates/ws-manage-epaper-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->epaper_cate_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "ec_replace"://電子報分類更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->epaper_cate_replace();
                $this->ws_tpl_type=1;
                break;
            case "ec_del"://電子報分類刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->epaper_cate_del();
                $this->ws_tpl_type=1;
                break;
            case "e_list"://電子報列表
                $this->current_class="E";
                $this->ws_tpl_file = "templates/ws-manage-epaper-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->epaper_list();
                $this->ws_tpl_type=1;
                break;
            case "e_add"://電子報新增
                $this->current_class="E";
                $this->ws_tpl_file = "templates/ws-manage-epaper-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_TINYMCE_EPAPER");
                $this->epaper_form("add");
                $this->ws_tpl_type=1;
                break;
            case "e_mod"://電子報修改
                $this->current_class="E";
                $this->ws_tpl_file = "templates/ws-manage-epaper-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_TINYMCE_EPAPER");
                $this->epaper_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "e_send"://電子報發送
                $this->current_class="ES";
                $this->ws_tpl_file = "templates/ws-manage-epaper-send-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->epaper_send();
                $this->ws_tpl_type=1;
                break;
            case "e_preview"://電子報預覽
                $this->ws_tpl_file = "templates/ws-manage-epaper-preview-tpl.html";
                $tpl = new TemplatePower( $this->ws_tpl_file );
                $tpl->prepare();
                $this->epaper_preview();
                $this->ws_tpl_type=1;
                break;
            case "e_replace"://電子報更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->epaper_replace();
                $this->ws_tpl_type=1;
                break;
            case "e_del"://電子報刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->epaper_del();
                $this->ws_tpl_type=1;
                break;
            case "eo_list"://訂閱名單列表
                $this->current_class="EO";
                $this->ws_tpl_file = "templates/ws-manage-epaper-order-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->epaper_order_list();
                $this->ws_tpl_type=1;
                break;
            case "es_history"://電子報發送記錄
                $this->current_class="ES";
                $this->ws_tpl_file = "templates/ws-manage-epaper-send-history-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->epaper_send_history();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //電子報列表
                $this->current_class="E";
                $this->ws_tpl_file = "templates/ws-manage-epaper-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->epaper_list();
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
        $tpl->assignGlobal("CSS_BLOCK_EPAPER","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }

    //電子報分類--列表
    function epaper_cate_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $sql="select * from ".$cms_cfg['tb_prefix']."_epaper_cate where ec_id > '0'";
        $and_str = "";
        if(!empty($_REQUEST["sk"])){
            $and_str = " and ec_subject like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by ec_sort  ";
        //取得總筆數
        $total_records=$main->count_total_records($sql);
        //取得分頁連結
        $func_str="epaper.php?func=ec_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        $page=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records);
        //重新組合包含limit的sql語法
        $sql=$main->sqlstr_add_limit($cms_cfg["op_limit"],$_REQUEST["nowp"],$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                  "VALUE_TOTAL_BOX" => $rsnum,
                                  "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR']
        ));
        //分類列表
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "EPAPER_CATE_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_EC_ID"  => $row["ec_id"],
                                "VALUE_EC_SORT"  => $row["ec_sort"],
                                "VALUE_EC_SUBJECT" => $row["ec_subject"],
                                "VALUE_EC_SERIAL" => $i,
                                "VALUE_STATUS_IMG" => ($row["ec_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                "VALUE_STATUS_IMG_ALT" => ($row["ec_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

            ));
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
    //電子報分類--表單
    function epaper_cate_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "STR_EC_STATUS_CK1" => "",
                                  "STR_EC_STATUS_CK0" => "checked",
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
        if($action_mode=="mod" && !empty($_REQUEST["ec_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_epaper_cate where ec_id='".$_REQUEST["ec_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("VALUE_EC_ID"  => $row["ec_id"],
                                          "VALUE_EC_SORT"  => $row["ec_sort"],
                                          "VALUE_EC_SUBJECT" => $row["ec_subject"],
                                          "STR_EC_STATUS_CK1" => ($row["ec_status"])?"checked":"",
                                          "STR_EC_STATUS_CK0" => ($row["ec_status"])?"":"checked",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
            }else{
                header("location : epaper.php?func=ec_list");
            }
        }
    }
    //電子報分類--資料更新
    function epaper_cate_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_epaper_cate (
                        ec_status,
                        ec_sort,
                        ec_subject
                    ) values (
                        '".$_REQUEST["ec_status"]."',
                        '".$_REQUEST["ec_sort"]."',
                        '".$_REQUEST["ec_subject"]."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_epaper_cate set
                        ec_status='".$_REQUEST["ec_status"]."',
                        ec_sort='".$_REQUEST["ec_sort"]."',
                        ec_subject='".$_REQUEST["ec_subject"]."'
                    where ec_id='".$_REQUEST["ec_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."epaper.php?func=ec_list&ec_id=".$_REQUEST["ec_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //電子報分類--刪除
    function epaper_cate_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["ec_id"]){
            $ec_id=array(0=>$_REQUEST["ec_id"]);
        }else{
            $ec_id=$_REQUEST["id"];
        }
        if(!empty($ec_id)){
            $ec_id_str = implode(",",$ec_id);
            //清空分類底下的電子報
            $sql="delete from ".$cms_cfg['tb_prefix']."_epaper where ec_id in (".$ec_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                //刪除分類
                $sql="delete from ".$cms_cfg['tb_prefix']."_epaper_cate where ec_id in (".$ec_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."epaper.php?func=ec_list&ec_id=".$_REQUEST["ec_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//電子報--列表================================================================
    function epaper_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $sql="select * from ".$cms_cfg['tb_prefix']."_epaper_cate where ec_id > '0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        //沒有分類先建立分類
        if($rsnum < 1){
            $tpl->assignGlobal( "MSG_CREATE_CATE_FIRST" , $TPLMSG["CREATE_CATE_FIRST"]);
            $goto_url=$cms_cfg["manage_url"]."epaper.php?func=ec_add";
            $this->goto_target_page($goto_url);
        }else{
            $i=0;
            $tpl->assignGlobal("TAG_NOW_CATE" ,$TPLMSG["NO_CATE"]);
            while($row = $db->fetch_array($selectrs,1)){
                $i++;
                $tpl->newBlock( "EPAPER_CATE_LIST" );
                $tpl->assign( array( "VALUE_EC_SUBJECT"  => $row["ec_subject"],
                                     "VALUE_EC_ID" => $row["ec_id"],
                                     "VALUE_EC_SERIAL" => $i,
                                     "VALUE_STATUS_IMG" => ($row["ec_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                     "VALUE_STATUS_IMG_ALT" => ($row["ec_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
                ));
                if($i%4==0){
                    $tpl->assign("TAG_EPAPER_CATE_TRTD","</tr><tr>");
                }
                if($row["ec_id"]==$_REQUEST["ec_id"]){
                    $tpl->assignGlobal("TAG_NOW_CATE",$row["ec_subject"]);
                }
            }
            //電子報列表
            $sql="select e.*,ec.ec_subject from ".$cms_cfg['tb_prefix']."_epaper as e left join ".$cms_cfg['tb_prefix']."_epaper_cate as ec on e.ec_id=ec.ec_id where e.e_id > '0'";
            //附加條件
            $and_str="";
            if(!empty($_REQUEST["ec_id"])){
                $and_str .= " and e.ec_id = '".$_REQUEST["ec_id"]."'";
            }
            if($_REQUEST["st"]=="all"){
                $and_str .= " and (e.e_subject like '%".$_REQUEST["sk"]."%' or e.e_content like '%".$_REQUEST["sk"]."%')";
            }
            if($_REQUEST["st"]=="e_subject"){
                $and_str .= " and e.e_subject like '%".$_REQUEST["sk"]."%'";
            }
            if($_REQUEST["st"]=="e_content"){
                $and_str .= " and e.e_content like '%".$_REQUEST["sk"]."%'";
            }
            $sql .= $and_str." order by e.e_sort ".$cms_cfg['sort_pos'].",e.e_modifydate desc ";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records    = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="epaper.php?func=e_list&ec_id=".$_REQUEST["ec_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
            $page=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records);
            //重新組合包含limit的sql語法
            $sql=$main->sqlstr_add_limit($cms_cfg["op_limit"],$_REQUEST["nowp"],$sql);
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $tpl->assignGlobal( array("VALUE_TOTAL_BOX" => $rsnum,
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                      "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR']

            ));
            switch($_REQUEST["st"]){
                case "all" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK0", "selected");
                    break;
                case "e_subject" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK1", "selected");
                    break;
                case "e_content" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK2", "selected");
                    break;
            }
            $i=$page["start_serial"];
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "EPAPER_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow'");
                }
                $tpl->assign( array("VALUE_EC_ID"  => $row["e_id"],
                                    "VALUE_E_ID"  => $row["e_id"],
                                    "VALUE_E_SORT"  => $row["e_sort"],
                                    "VALUE_E_SUBJECT" => $row["e_subject"],
                                    "VALUE_E_SERIAL" => $i,
                                    "VALUE_EC_SUBJECT"  => $row["ec_subject"],
                                    "VALUE_STATUS_IMG" => ($row["e_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                    "VALUE_STATUS_IMG_ALT" => ($row["e_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

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
//電子報--表單================================================================
    function epaper_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "STR_E_STATUS_CK1" => "",
                                  "STR_E_STATUS_CK0" => "checked",
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
        if($action_mode=="mod" && !empty($_REQUEST["e_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_epaper where e_id='".$_REQUEST["e_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("VALUE_E_ID"  => $row["e_id"],
                                          "VALUE_E_SORT"  => $row["e_sort"],
                                          "VALUE_E_SUBJECT" => $row["e_subject"],
                                          "STR_E_STATUS_CK1" => ($row["e_status"]==1)?"checked":"",
                                          "STR_E_STATUS_CK0" => ($row["e_status"]==0)?"checked":"",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
            }else{
                header("location : epaper.php?func=e_list");
            }
        }
        //電子報分類
        $sql="select * from ".$cms_cfg['tb_prefix']."_epaper_cate where ec_id > '0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        while($row1 = $db->fetch_array($selectrs,1)){
            $tpl->newBlock( "TAG_SELECT_EPAPER_CATE" );
            $tpl->assign( array( "TAG_SELECT_EPAPER_CATE_NAME"  => $row1["ec_subject"],
                                 "TAG_SELECT_EPAPER_CATE_VALUE" => $row1["ec_id"],
                                 "STR_EC_SEL"       => ($row1["ec_id"]==$row["ec_id"])?"selected":""
            ));
        }
        if($cms_cfg["ws_module"]["ws_wysiwyg"]=="tinymce"){
            $tpl->newBlock("TINYMCE_JS");
            $tpl->newBlock("WYSIWYG_TINYMCE1");
            $tpl->assign( "VALUE_E_CONTENT" , $row["e_content"] );
        }
    }
    //電子報--發送表單================================================================
    function epaper_send(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        if(!empty($_REQUEST["e_id"])){
            $sql="select e_id,e_subject from ".$cms_cfg['tb_prefix']."_epaper where e_id='".$_REQUEST["e_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("MSG_MODE"  => $TPLMSG["SEND"],
                                          "VALUE_E_ID"  => $row["e_id"],
                                          "VALUE_E_SUBJECT" => $row["e_subject"],
                ));
            }else{
                header("location : epaper.php?func=e_list");
            }
            //相關參數
            if(!empty($_REQUEST['nowp'])){
                $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                          "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                          "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                          "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

                ));
            }
            //列出分類表單
            $sql="select mc.mc_id,mc.mc_subject,count(*) as e_subtotal from ".$cms_cfg['tb_prefix']."_member as m left join ".$cms_cfg['tb_prefix']."_member_cate as mc on mc.mc_id=m.mc_id where m.m_epaper_status='1' group by mc.mc_id order by m.mc_id";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $total= 0;
            while($row = $db->fetch_array($selectrs,1)){
                //未分類會員不顯示
                if($row["mc_id"] != NULL){
                    $tpl->newBlock("MEMBER_CATE");
                    $tpl->assign( array("VALUE_MC_ID"  => $row["mc_id"],
                                        "VALUE_MC_SUBJECT" => $row["mc_subject"],
                                        "VALUE_E_SUBTOTAL" => $row["e_subtotal"],
                    ));
                }
                //總數包含未分類會員
                $total= $total+$row["e_subtotal"];
            }
            $tpl->assignGlobal("VALUE_E_TOTAL" , $total);
            //顯示發送記錄
            //取得記錄
            $sql="select * from ".$cms_cfg['tb_prefix']."_epaper_send where e_id='".$_REQUEST["e_id"]."' order by es_modifydate desc ";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            //名單列表
            $i=0;
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "EPAPER_SEND_HISTORY_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow'");
                }
                $tpl->assign( array("VALUE_E_ID"  => $row["e_id"],
                                    "VALUE_ES_MODIFYDATE"  => $row["es_modifydate"],
                                    "VALUE_ES_GROUP"  => $row["es_group"],
                                    "VALUE_E_SUBJECT" => $row["e_subject"],
                                    "VALUE_ES_SERIAL" => $i,
                ));
            }
        }
    }

    function epaper_preview(){
        global $db,$tpl,$cms_cfg;
        if(!empty($_REQUEST["e_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_epaper where e_id='".$_REQUEST["e_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal("VALUE_E_CONTENT" , $row["e_content"]);
            }else{
                header("location : epaper.php?func=e_list");
            }
        }
    }
//電子報--資料更新================================================================
    function epaper_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_epaper (
                        ec_id,
                        e_status,
                        e_sort,
                        e_subject,
                        e_content,
                        e_modifydate
                    ) values (
                        '".$_REQUEST["ec_id"]."',
                        '".$_REQUEST["e_status"]."',
                        '".$_REQUEST["e_sort"]."',
                        '".$_REQUEST["e_subject"]."',
                        '".$_REQUEST["e_content"]."',
                        '".date("Y-m-d H:i:s")."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_epaper set
                        ec_id='".$_REQUEST["ec_id"]."',
                        e_status='".$_REQUEST["e_status"]."',
                        e_sort='".$_REQUEST["e_sort"]."',
                        e_subject='".$_REQUEST["e_subject"]."',
                        e_content='".$_REQUEST["e_content"]."',
                        e_modifydate='".date("Y-m-d H:i:s")."'
                    where e_id='".$_REQUEST["e_id"]."'";
                break;
            case "send":
                //取得寄送名單
                if($_REQUEST["e_st"]="1"){
                    $sql="select m.m_email,mc.mc_subject from ".$cms_cfg['tb_prefix']."_member as m left join ".$cms_cfg['tb_prefix']."_member_cate as mc on m.mc_id = mc.mc_id  where m.m_epaper_status='1'";
                }
                if($_REQUEST["e_st"]="2" && !empty($_REQUEST["mc_id"])){
                    $mc_id_str=" and m.mc_id in (".implode(",",$_REQUEST["mc_id"]).")";
                    $sql="select m.m_email,mc.mc_subject from ".$cms_cfg['tb_prefix']."_member as m left join ".$cms_cfg['tb_prefix']."_member_cate as mc on m.mc_id = mc.mc_id  where m.m_epaper_status='1'".$mc_id_str ;
                }
                $selectrs = $db->query($sql);
                $rsnum    = $db->numRows($selectrs);
                if($rsnum > 0){
                    $mail_array=array();
                    while($row = $db->fetch_array($selectrs,1)){
                        $piece=explode(",",$row["m_email"]);
                        foreach($piece as $key => $value){
                            $mail_array[$value]=1;
                        }
                        $member_cate[$row["mc_subject"]]=1;
                        unset($piece);
                    }
                    foreach ($mail_array as $key =>$value){
                        $new_mail_array[]=$key;
                    }
                    foreach ($member_cate as $key =>$value){
                        $new_member_cate[]=$key;
                    }
                    if(!empty($new_mail_array)){
                        $mail_str=implode(",",$new_mail_array);
                        $member_cate_str=implode(",",$new_member_cate);
                        unset($new_mail_array);
                        //取得寄件資訊
                        $sql="select sc_company,sc_email from ".$cms_cfg['tb_prefix']."_system_config where sc_id = '1'";
                        $selectrs = $db->query($sql);
                        $row = $db->fetch_array($selectrs,1);
                        $from_mail=$row["sc_email"];
                        $_SESSION[$cms_cfg['sess_cookie_name']]["sc_company"]=$row["sc_company"];
                        //取得電子報內容
                        $sql="select e_subject,e_content from ".$cms_cfg['tb_prefix']."_epaper where e_id='".$_REQUEST["e_id"]."'";
                        $selectrs = $db->query($sql);
                        $row = $db->fetch_array($selectrs,1);
                        $rsnum    = $db->numRows($selectrs);
                        $goto_url=$cms_cfg["manage_url"]."epaper.php?func=e_list";
                        if($rsnum > 0){
                            $mail_subject=$row["e_subject"];
                            $mail_content=str_replace("=\"../upload_files/","=\"".$cms_cfg['file_url']."upload_files/",$row["e_content"]);
                            //寫入發送記錄
                            $sql="
                                insert into ".$cms_cfg['tb_prefix']."_epaper_send (
                                    e_id,
                                    es_modifydate,
                                    es_group,e_subject
                                ) values (
                                    '".$_REQUEST["e_id"]."',
                                    '".date("Y-m-d H:i:s")."',
                                    '".$member_cate_str."',
                                    '".$row["e_subject"]."'
                                )";
                            $rs = $db->query($sql);
                            $main->ws_mail_send($from_mail,$mail_str,$mail_content,$mail_subject,"epaper",$goto_url);
                        }else{
                            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                            $this->goto_target_page($goto_url);
                        }
                    }
                }
                $sql="";
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."epaper.php?func=e_list&ec_id=".$_REQUEST["ec_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//電子報--刪除--資料刪除可多筆處理================================================================
    function epaper_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["e_id"]){
            $e_id=array(0=>$_REQUEST["e_id"]);
        }else{
            $e_id=$_REQUEST["id"];
        }
        if(!empty($e_id)){
            $e_id_str = implode(",",$e_id);
            //刪除勾選的電子報
            $sql="delete from ".$cms_cfg['tb_prefix']."_epaper where e_id in (".$e_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."epaper.php?func=e_list&ec_id=".$_REQUEST["ec_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
 ///////////////////////////////////////////////////////////////////////////////////////
 //訂閱名單----列表
    function epaper_order_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //會員才有訂閱電子報的權限
        $sql="select * from ".$cms_cfg['tb_prefix']."_member where m_status='1' and m_epaper_status='1' ";
        $sql .= " order by m_sort ".$cms_cfg['sort_pos'].",m_modifydate desc ";
        //取得總筆數
        $total_records=$main->count_total_records($sql);
        //取得分頁連結
        $func_str="epaper.php?func=eo_list";
        $page=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records);
        //重新組合包含limit的sql語法
        $sql=$main->sqlstr_add_limit($cms_cfg["op_limit"],$_REQUEST["nowp"],$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                  "VALUE_TOTAL_BOX" => $rsnum,
        ));
        //名單列表
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "EPAPER_ORDER_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_M_ID"  => $row["m_id"],
                                "VALUE_M_ACCOUNT"  => $row["m_account"],
                                "VALUE_M_NAME"  => $row["m_name"],
                                "VALUE_M_EMAIL" => $row["m_email"],
                                "VALUE_M_SERIAL" => $i,
            ));
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
    //電子報發送記錄
    function epaper_send_history(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //會員才有訂閱電子報的權限
        $sql="select * from ".$cms_cfg['tb_prefix']."_epaper_send order by es_modifydate desc ";
        //取得總筆數
        $total_records=$main->count_total_records($sql);
        //取得分頁連結
        $func_str="epaper.php?func=es_history";
        $page=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records);
        //重新組合包含limit的sql語法
        $sql=$main->sqlstr_add_limit($cms_cfg["op_limit"],$_REQUEST["nowp"],$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                  "VALUE_TOTAL_BOX" => $rsnum,
        ));
        //名單列表
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "EPAPER_SEND_HISTORY_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_E_ID"  => $row["e_id"],
                                "VALUE_ES_MODIFYDATE"  => $row["es_modifydate"],
                                "VALUE_ES_GROUP"  => $row["es_group"],
                                "VALUE_E_SUBJECT" => $row["e_subject"],
                                "VALUE_ES_SERIAL" => $i,
            ));
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
////////////////////////////////////////////////////////////////////////////////////////////////////
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
        //電子報分類更改狀態
        if($ws_table=="ec"){
            if($_REQUEST["ec_id"]){
                $ec_id=array(0=>$_REQUEST["ec_id"]);
            }else{
                $ec_id=$_REQUEST["id"];
            }
            if(!empty($ec_id)){
                $ec_id_str = implode(",",$ec_id);
                //更改分類底下的電子報狀態
                $sql="update ".$cms_cfg['tb_prefix']."_epaper set e_status='".$value."' where ec_id in (".$ec_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    //狀態分類狀態
                    $sql="update ".$cms_cfg['tb_prefix']."_epaper_cate set ec_status='".$value."' where ec_id in (".$ec_id_str.")";
                    $rs = $db->query($sql);
                    $db_msg = $db->report();
                    if ( $db_msg == "" ) {
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                        $goto_url=$cms_cfg["manage_url"]."epaper.php?func=ec_list&ec_id=".$_REQUEST["ec_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                        $this->goto_target_page($goto_url);
                    }else{
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                    }
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //電子報更改狀態
        if($ws_table=="e"){
            if($_REQUEST["e_id"]){
                $e_id=array(0=>$_REQUEST["e_id"]);
            }else{
                $e_id=$_REQUEST["id"];
            }
            if(!empty($e_id)){
                $e_id_str = implode(",",$e_id);
                //刪除勾選的電子報
                $sql="update ".$cms_cfg['tb_prefix']."_epaper set e_status='".$value."' where e_id in (".$e_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."epaper.php?func=e_list&ec_id=".$_REQUEST["ec_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //電子報分類更改排序值
        if(!empty($_REQUEST["sort_value"]) && !empty($ws_table)){
            if($ws_table=="ec"){
                $table_name=$cms_cfg['tb_prefix']."_epaper_cate";
            }
            if($ws_table=="e"){
                $table_name=$cms_cfg['tb_prefix']."_epaper";
            }
            foreach($_REQUEST["id"] as $key => $value){
                $sql="update ".$table_name." set ".$ws_table."_sort='".$_REQUEST["sort_value"][$value]."' where  ".$ws_table."_id='".$value."'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
            }
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."epaper.php?func=".$ws_table."_list&ec_id=".$_REQUEST["ec_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //複製單筆資料
    function copy_data($ws_table){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //電子報分類複製
        if($ws_table=="ec"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_epaper_cate where ec_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_epaper_cate (
                        ec_status,
                        ec_sort,
                        ec_subject
                    ) values (
                        '".$row["ec_status"]."',
                        '".$row["ec_sort"]."',
                        '".$row["ec_subject"]."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."epaper.php?func=ec_list&ec_id=".$_REQUEST["ec_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //電子報複製
        if($ws_table=="e"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_epaper where e_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ws_epaper (
                        ec_id,
                        e_status,
                        e_sort,
                        e_subject,
                        e_content,
                        e_modifydate
                    ) values (
                        '".$row["ec_id"]."',
                        '".$row["e_status"]."',
                        '".$row["e_sort"]."',
                        '".$row["e_subject"]."',
                        '".$row["e_content"]."',
                        '".$row["e_modifydate"]."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."epaper.php?func=e_list&ec_id=".$_REQUEST["ec_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
                if($_REQUEST["ws_table"]=="ec"){
                    $this->epaper_cate_del();
                }
                if($_REQUEST["ws_table"]=="e"){
                    $this->epaper_del();
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
}
//ob_end_flush();
?>
