<?php
//error_reporting(15);
//ob_start();
/*
規格 中文標題
spec.php 檔案名稱
products_spec_cate 類別資料表名
products_spec_title 資料表名
spec 功能名稱小寫
SPEC 功能名稱大寫
psc 類別頭文字小寫
PSC 類別頭文字大寫
pst 頭文字小寫
PST 頭文字大寫
*/
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])  || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_products"]==0){
    header("location: ".$cms_cfg['manage_root']);
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$spec = new SPEC;
class SPEC{
    function SPEC(){
        global $db,$cms_cfg,$tpl;
        $this->seo=($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_seo"] && $cms_cfg["ws_module"]["ws_seo"])?1:0;
        switch($_REQUEST["func"]){
            case "psc_list"://規格分類列表
                $this->current_class="PSC";
                $this->ws_tpl_file = "templates/ws-manage-spec-cate-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->spec_cate_list();
                $this->ws_tpl_type=1;
                break;
            case "psc_add"://規格分類新增
                $this->current_class="PSC";
                $this->ws_tpl_file = "templates/ws-manage-spec-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->spec_cate_form("add");
                $this->ws_tpl_type=1;
                break;
            case "psc_mod"://規格分類修改
                $this->current_class="PSC";
                $this->ws_tpl_file = "templates/ws-manage-spec-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->spec_cate_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "psc_replace"://規格分類更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->spec_cate_replace();
                $this->ws_tpl_type=1;
                break;
            case "psc_del"://規格分類刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->spec_cate_del();
                $this->ws_tpl_type=1;
                break;
            case "pst_list"://規格列表
                $this->current_class="PST";
                $this->ws_tpl_file = "templates/ws-manage-spec-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->spec_list();
                $this->ws_tpl_type=1;
                break;
            case "pst_add"://規格新增
                $this->current_class="PST";
                $this->ws_tpl_file = "templates/ws-manage-spec-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $this->spec_form("add");
                $this->ws_tpl_type=1;
                break;
            case "pst_mod"://規格修改
                $this->current_class="PST";
                $this->ws_tpl_file = "templates/ws-manage-spec-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $this->spec_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "pst_replace"://規格更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->spec_replace();
                $this->ws_tpl_type=1;
                break;
            case "pst_del"://規格刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->spec_del();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //規格列表
                $this->current_class="PST";
                $this->ws_tpl_file = "templates/ws-manage-spec-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->spec_list();
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
        $tpl->assignGlobal("CSS_BLOCK_PRODUCTS","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }

    //規格分類--列表
    function spec_cate_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $sql="select * from ".$cms_cfg['tb_prefix']."_products_spec_cate where psc_id > '0'";
        $and_str = "";
        if(!empty($_REQUEST["sk"])){
            $and_str = " and psc_subject like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by psc_sort ".$cms_cfg['sort_pos']." ";
        //取得總筆數
        $total_records=$main->count_total_records($sql);
        //取得分頁連結
        $func_str="spec.php?func=psc_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        //分頁且重新組合包含limit的sql語法
        $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("MSG_SUBJECT"  => $TPLMSG['CATE'].$TPLMSG['NAME'],
                                  "MSG_STATUS" => $TPLMSG['STATUS'],
                                  "MSG_MODE" => $TPLMSG['MANAGE_CATE'],
                                  "MSG_ADD" => $TPLMSG['ADD'],
                                  "MSG_MODIFY" => $TPLMSG['MODIFY'],
                                  "MSG_DEL" => $TPLMSG['DEL'],
                                  "MSG_COPY" => $TPLMSG['COPY'],
                                  "MSG_SORT" => $TPLMSG['SORT'],
                                  "MSG_ON" => $TPLMSG['ON'],
                                  "MSG_OFF" => $TPLMSG['OFF'],
                                  "MSG_KEYWORD" => $TPLMSG['KEYWORD'],
                                  "MSG_LOGIN_LANGUAGE" => $TPLMSG['LOGIN_LANGUAGE'],
                                  "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                  "VALUE_TOTAL_BOX" => $rsnum,
                                  "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],
        ));
        //分類列表
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "SPEC_CATE_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_PSC_ID"  => $row["psc_id"],
                                "VALUE_PSC_SORT"  => $row["psc_sort"],
                                "VALUE_PSC_SUBJECT" => $row["psc_subject"],
                                "VALUE_PSC_SERIAL" => $i,
                                "VALUE_STATUS_IMG" => ($row["psc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                "VALUE_STATUS_IMG_ALT" => ($row["psc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

            ));
        }
    }
    //規格分類--表單
    function spec_cate_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'], //表單預設模式為『新增』
                                  "NOW_PSC_ID"  => 0,
                                  "VALUE_PSC_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_products_spec_cate","psc","","",0),
                                  "STR_PSC_STATUS_CK1" => "checked",//啟用狀態預設為『啟用』
                                  "STR_PSC_STATUS_CK0" => "",
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
        if($action_mode=="mod" && !empty($_REQUEST["psc_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_products_spec_cate where psc_id='".$_REQUEST["psc_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("NOW_PSC_ID"  => $row["psc_id"],
                                          "VALUE_PSC_ID"  => $row["psc_id"],
                                          "VALUE_PSC_SORT"  => $row["psc_sort"],
                                          "VALUE_PSC_SUBJECT" => $row["psc_subject"],
                                          "STR_PSC_STATUS_CK1" => ($row["psc_status"])?"checked":"",
                                          "STR_PSC_STATUS_CK0" => ($row["psc_status"])?"":"checked",
                                          "STR_PSC_TYPE_CK1" => ($row["psc_type"])?"checked":"",
                                          "STR_PSC_TYPE_CK0" => ($row["psc_type"])?"":"checked",
                                          "MSG_MODE" => $TPLMSG['MODIFY'] //表單模式變更為『修改』
                ));
            }else{
                header("location: spec.php?func=psc_list");
            }
        }
    }
    //規格分類--資料更新
    function spec_cate_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_products_spec_cate (
                        psc_status,
						psc_type,
                        psc_sort,
                        psc_subject
                    ) values (
                        '".$_REQUEST["psc_status"]."',
						'".$_REQUEST["psc_type"]."',
                        '".$_REQUEST["psc_sort"]."',
                        '".$_REQUEST["psc_subject"]."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_products_spec_cate set
                        psc_status='".$_REQUEST["psc_status"]."',
						psc_type='".$_REQUEST["psc_type"]."',
                        psc_sort='".$_REQUEST["psc_sort"]."',
                        psc_subject='".$_REQUEST["psc_subject"]."'
                    where psc_id='".$_REQUEST["psc_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."spec.php?func=psc_list&psc_id=".$_REQUEST["psc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //規格分類--刪除
    function spec_cate_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["psc_id"]){
            $psc_id=array(0=>$_REQUEST["psc_id"]);
        }else{
            $psc_id=$_REQUEST["id"];
        }
        if(!empty($psc_id)){
            $psc_id_str = implode(",",$psc_id);
            //清空分類底下的規格
            $sql="delete from ".$cms_cfg['tb_prefix']."_products_spec_title where psc_id in (".$psc_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                //刪除分類
                $sql="delete from ".$cms_cfg['tb_prefix']."_products_spec_cate where psc_id in (".$psc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."spec.php?func=psc_list&psc_id=".$_REQUEST["psc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//規格--列表================================================================
    function spec_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $sql="select * from ".$cms_cfg['tb_prefix']."_products_spec_cate where psc_id > '0' and psc_type='0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        //沒有分類先建立分類
        if($rsnum<1){
            $tpl->assignGlobal( "MSG_CREATE_CATE_FIRST" , $TPLMSG["CREATE_CATE_FIRST"]);
            $goto_url=$cms_cfg["manage_url"]."spec.php?func=psc_add";
            $this->goto_target_page($goto_url);
        }else{
            //規格分類
            $i=0;
            $tpl->assignGlobal("TAG_NOW_CATE" ,$TPLMSG["NO_CATE"]);
			$tpl->assignGlobal("VALUE_PSC_ID" ,$_REQUEST["psc_id"]);
            while($row = $db->fetch_array($selectrs,1)){
                $i++;
                $tpl->newBlock( "SPEC_CATE_LIST" );
                $tpl->assign( array( "VALUE_PSC_SUBJECT"  => $row["psc_subject"],
                                     "VALUE_PSC_ID" => $row["psc_id"],
                                     "VALUE_PSC_SERIAL" => $i,
                                     "VALUE_STATUS_IMG" => ($row["psc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                     "VALUE_STATUS_IMG_ALT" => ($row["psc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
                ));
                if($i%4==0){
                    $tpl->assign("TAG_SPEC_CATE_TRTD","</tr><tr>");
                }
                if($row["psc_id"]==$_REQUEST["psc_id"]){
                    $tpl->assignGlobal("TAG_NOW_CATE",$row["psc_subject"]);
                }
            }
            //規格列表
            $sql="select pst.*,psc.psc_subject from ".$cms_cfg['tb_prefix']."_products_spec_title as pst left join ".$cms_cfg['tb_prefix']."_products_spec_cate as psc on pst.psc_id=psc.psc_id where pst.pst_id > '0'";
            //附加條件
            $and_str="";
            if(!empty($_REQUEST["psc_id"])){
                $and_str .= " and pst.psc_id = '".$_REQUEST["psc_id"]."'";
            }
            $and_str .= " and (pst.pst_subject like '%".$_REQUEST["sk"]."%')";
            $sql .= $and_str." order by pst.pst_sort ".$cms_cfg['sort_pos'].",pst.pst_modifydate desc ";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records    = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="spec.php?func=pst_list&psc_id=".$_REQUEST["psc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
            //分頁且重新組合包含limit的sql語法
            $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $tpl->assignGlobal( array("VALUE_TOTAL_BOX" => $rsnum,
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                      "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],
            ));

            $tpl->assignGlobal( "VALUE_NOW_PSC_ID" , $_REQUEST["psc_id"]);
            $i=$page["start_serial"];
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "SPEC_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow'");
                }
                $img = $row['pst_img']?App::configs()->file_root.$row['pst_img']:'';
                $tpl->assign( array("VALUE_PSC_ID"  => $row["psc_id"],
                                    "VALUE_PST_ID"  => $row["pst_id"],
                                    "VALUE_PST_SORT"  => $row["pst_sort"],
                                    "VALUE_PST_SUBJECT" => $row["pst_subject"],
                                    "TAG_PST_IMG" => ($img)?"<img src='{$img}' height='30'/>":"",
                                    "VALUE_PST_SERIAL" => $i,
                                    "VALUE_PSC_SUBJECT"  => $row["psc_subject"],
                                    "VALUE_STATUS_IMG" => ($row["pst_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                    "VALUE_STATUS_IMG_ALT" => ($row["pst_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

                ));

            }
        }
    }
//規格--表單================================================================
    function spec_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //欄位名稱
        $cate=(trim($_REQUEST["psc_id"])!="")?1:0;
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "VALUE_PST_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_products_spec_title","pst","psc_id",$_REQUEST["psc_id"],$cate),
                                  "STR_PST_STATUS_CK1" => "checked",
                                  "STR_PST_STATUS_CK0" => "",
                                  "VALUE_PIC_PREVIEW1" => $cms_cfg['default_preview_pic'],
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
        if($action_mode=="mod" && !empty($_REQUEST["pst_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_products_spec_title where pst_id='".$_REQUEST["pst_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("VALUE_PST_ID"  => $row["pst_id"],
                                          "VALUE_PST_SORT"  => $row["pst_sort"],
                                          "VALUE_PST_SUBJECT" => $row["pst_subject"],
                                          "VALUE_PST_CONTENT" => $row["pst_content"],
                                          "STR_PST_STATUS_CK1" => ($row["pst_status"]==1)?"checked":"",
                                          "STR_PST_STATUS_CK0" => ($row["pst_status"]==0)?"checked":"",
                                          "VALUE_PST_IMG" => (trim($row["pst_img"])=="")?"":$cms_cfg["file_root"].$row["pst_img"],
                                          "VALUE_PIC_PREVIEW1" => (trim($row["pst_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["pst_img"],
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
            }else{
                header("location: spec.php?func=pst_list");
            }
        }
        //規格圖欄位
        imagefield::setValues(array(
            "TAG_IMG_TITLE"         => "規格圖片",
            "TAG_IMG_FIELD_NAME"    => "pst_img",
            "TAG_IMG_FIELD_ID"      => "pst_img",
            "TAG_IMG_FIELD_VALUE"   => $row['pst_img'],
            "TAG_PREVIEW_IMG_ID"    => "pic_preview1",
            "TAG_PREVIEW_IMG_VALUE" => ($row['pst_img'])?App::configs()->file_root. $row['pst_img'] : App::configs()->default_preview_pic,            
        ));
        $tpl->assignGlobal("TAG_IMAGE_FIELD",imagefield::get_html());
        //規格分類
        $sql="select * from ".$cms_cfg['tb_prefix']."_products_spec_cate where psc_id > '0' and psc_type='0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        while($row1 = $db->fetch_array($selectrs,1)){
            $tpl->newBlock( "TAG_SELECT_SPEC_CATE" );
            $tpl->assign( array( "TAG_SELECT_SPEC_CATE_NAME"  => $row1["psc_subject"],
                                 "TAG_SELECT_SPEC_CATE_VALUE" => $row1["psc_id"],
                                 "STR_PSC_SEL"       => (($action_mode=="mod" && $row["psc_id"]==$row1["psc_id"]) || 
                                                         ($action_mode=="add" && $_GET["psc_id"]==$row1["psc_id"]))?"selected":"",
            ));
        }
    }
//規格--資料更新================================================================
    function spec_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_products_spec_title (
                        psc_id,
                        pst_status,
                        pst_sort,
                        pst_subject,
						pst_img,
                        pst_modifydate
                    ) values (
                        '".$_REQUEST["psc_id"]."',
                        '".$_REQUEST["pst_status"]."',
                        '".$_REQUEST["pst_sort"]."',
                        '".$_REQUEST["pst_subject"]."',
						'".$main->file_str_replace($_REQUEST["pst_img"])."',
                        '".date("Y-m-d H:i:s")."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_products_spec_title set
                        psc_id='".$_REQUEST["psc_id"]."',
                        pst_status='".$_REQUEST["pst_status"]."',
                        pst_sort='".$_REQUEST["pst_sort"]."',
                        pst_subject='".$_REQUEST["pst_subject"]."',
						pst_img='".$main->file_str_replace($_REQUEST["pst_img"])."',
                        pst_modifydate='".date("Y-m-d H:i:s")."'
                    where pst_id='".$_REQUEST["pst_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."spec.php?func=pst_list&psc_id=".$_REQUEST["psc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//規格--刪除--資料刪除可多筆處理================================================================
    function spec_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["pst_id"]){
            $pst_id=array(0=>$_REQUEST["pst_id"]);
        }else{
            $pst_id=$_REQUEST["id"];
        }
        if(!empty($pst_id)){
            $pst_id_str = implode(",",$pst_id);
            //刪除勾選的規格
            $sql="delete from ".$cms_cfg['tb_prefix']."_products_spec_title where pst_id in (".$pst_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."spec.php?func=pst_list&psc_id=".$_REQUEST["psc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //規格分類更改狀態
        if($ws_table=="psc"){
            if($_REQUEST["psc_id"]){
                $psc_id=array(0=>$_REQUEST["psc_id"]);
            }else{
                $psc_id=$_REQUEST["id"];
            }
            if(!empty($psc_id)){
                $psc_id_str = implode(",",$psc_id);
                //更改分類底下的規格狀態
                $sql="update ".$cms_cfg['tb_prefix']."_products_spec_title set pst_status='".$value."' where psc_id in (".$psc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    //狀態分類狀態
                    $sql="update ".$cms_cfg['tb_prefix']."_products_spec_cate set psc_status='".$value."' where psc_id in (".$psc_id_str.")";
                    $rs = $db->query($sql);
                    $db_msg = $db->report();
                    if ( $db_msg == "" ) {
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                        $goto_url=$cms_cfg["manage_url"]."spec.php?func=psc_list&psc_id=".$_REQUEST["psc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                        $this->goto_target_page($goto_url);
                    }else{
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                    }
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //規格更改狀態
        if($ws_table=="pst"){
            if($_REQUEST["pst_id"]){
                $pst_id=array(0=>$_REQUEST["pst_id"]);
            }else{
                $pst_id=$_REQUEST["id"];
            }
            if(!empty($pst_id)){
                $pst_id_str = implode(",",$pst_id);
                //刪除勾選的規格
                $sql="update ".$cms_cfg['tb_prefix']."_products_spec_title set pst_status='".$value."' where pst_id in (".$pst_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."spec.php?func=pst_list&psc_id=".$_REQUEST["psc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //規格分類更改排序值
        if(!empty($_REQUEST["sort_value"]) && !empty($ws_table)){
            if($ws_table=="psc"){
                $table_name=$cms_cfg['tb_prefix']."_products_spec_cate";
            }
            if($ws_table=="pst"){
                $table_name=$cms_cfg['tb_prefix']."_products_spec_title";
            }
            foreach($_REQUEST["id"] as $key => $value){
                $sql="update ".$table_name." set ".$ws_table."_sort='".$_REQUEST["sort_value"][$value]."' where ".$ws_table."_id='".$value."'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
            }
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."spec.php?func=".$ws_table."_list&psc_id=".$_REQUEST["psc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //複製單筆資料
    function copy_data($ws_table){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //規格分類複製
        if($ws_table=="fc"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_products_spec_cate where psc_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_products_spec_cate (
                        psc_status,
                        psc_sort,
                        psc_subject
                    ) values (
                        '".$row["psc_status"]."',
                        '".$row["psc_sort"]."',
                        '".$row["psc_subject"]."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."spec.php?func=psc_list&psc_id=".$_REQUEST["psc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //規格複製
        if($ws_table=="f"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_products_spec_title where pst_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_products_spec_title (
                        psc_id,
                        pst_status,
                        pst_sort,
                        pst_subject,
                        pst_content,
                        pst_modifydate
                    ) values (
                        '".$row["psc_id"]."',
                        '".$row["pst_status"]."',
                        '".$row["pst_sort"]."',
                        '".$row["pst_subject"]."',
                        '".$row["pst_content"]."',
                        '".$row["pst_modifydate"]."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."spec.php?func=pst_list&psc_id=".$_REQUEST["psc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
                if($_REQUEST["ws_table"]=="fc"){
                    $this->spec_cate_del();
                }
                if($_REQUEST["ws_table"]=="f"){
                    $this->spec_del();
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
