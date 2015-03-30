<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$aboutus = new CONFIG;
class CONFIG{
    function CONFIG(){
        global $db,$cms_cfg,$tpl;
        $this->seo=($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_seo"] && $cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->current_class="OSC";
        switch($_REQUEST["func"]){
            case "add"://加價購新增
            case "mod"://加價購修改
                $this->ws_tpl_file = "templates/ws-manage-osc-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->config_form($_REQUEST["func"]);
                $this->ws_tpl_type=1;
                break;
            case "replace"://加價購更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->config_replace();
                $this->ws_tpl_type=1;
                break;
            case "del"://加價購刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->config_del();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //加價購列表
                $this->ws_tpl_file = "templates/ws-manage-osc-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->config_list();
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


    //加價購--列表================================================================
    function config_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
    
        //加價購列表
        $sql="select * from ".$db->prefix("shopping_condition")." where id > '0'";
        //附加條件
        $sql .= " order by type ";
        //取得總筆數
        $selectrs = $db->query($sql,true);
        $pagination = new Pagination_Dastool($selectrs);
        $tpl->assignGlobal( array(
            "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],
            "TAG_PAGINATION"       => $pagination->getPagination(),
        ));
        $i=$main->get_pagination_offset($cms_cfg["op_limit"]);
        $dataList = $pagination->getDataList();
        foreach ( $dataList as $idx => $row  ) {
            $i++;
            $tpl->newBlock( "CONFIG_LIST" );
            $tpl->assign('serial',$idx);
            foreach($row as $k=>$v){
                if($k=="status"){
                    $tpl->assign(array(
                        "VALUE_STATUS_IMG" => ($row["status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                        "VALUE_STATUS_IMG_ALT" => ($row["status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
                    ));
                }elseif($k=="type"){
                    $v = $main->multi_map_value($ws_array['shopping_cond_type'],$v);
                }
                $tpl->assign($k,$v);
            }
        }
    }
//加價購--表單================================================================
    function config_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        if($this->seo){
            $tpl->newBlock("SEO_EDIT_ZONE");
        }
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "NOW_AU_ID"  => 0,
                                  "VALUE_AU_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_aboutus","au","","",0),
                                  "STR_AU_STATUS_CK1" => "checked",
                                  "STR_AU_STATUS_CK0" => "",
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
        if($action_mode=="mod" && !empty($_REQUEST["id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_aboutus where id='".$_REQUEST["id"]."'";
            $row = App::getHelper('dbtable')->shopping_condition->getData($_REQUEST["id"])->getDataRow();
            if ($row) {
                foreach($row as $k=>$v){
                    $tpl->assignGlobal( $k,$v);
                }
                $tpl->assignGlobal( array(
                    "STR_AU_STATUS_CK1" => ($row["status"]==1)?"checked":"",
                    "STR_AU_STATUS_CK0" => ($row["status"]==0)?"checked":"",
                    "MSG_MODE" => $TPLMSG['MODIFY'],
                ));
            }else{
                header("location : ".$_SERVER['PHP_SELF']."?func=list");
            }
        }
        //狀態
        $main->multiple_radio('status',$ws_array['default_status'],$row['status'],$tpl);
        //類型
        $main->multiple_select("type",$ws_array['shopping_cond_type'],$row['type'],$tpl);
    }
//加價購--資料更新================================================================
    function config_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        App::getHelper('dbtable')->shopping_condition->writeData($_POST);
        $db_msg = App::getHelper('dbtable')->shopping_condition->report();
        if ( $db_msg == "" ) {
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$_SERVER['PHP_SELF']."?func=list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
            $this->goto_target_page($goto_url);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
//加價購--刪除--資料刪除可多筆處理================================================================
    function config_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if(!empty($_REQUEST["id"])){
            //刪除勾選的最新消息
            App::getHelper('dbtable')->shopping_condition->del($_REQUEST["id"]);
            $db_msg = App::getHelper('dbtable')->shopping_condition->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$_SERVER['PHP_SELF']."?func=list&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //加價購更改狀態
        if($ws_table=="au"){
            if($_REQUEST["id"]){
                $id=array(0=>$_REQUEST["id"]);
            }else{
                $id=$_REQUEST["id"];
            }
            if(!empty($id)){
                $id_str = implode(",",$id);
                //更改勾選的加價購狀態
                $sql="update ".$cms_cfg['tb_prefix']."_aboutus set status='".$value."' where id in (".$id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."aboutus.php?func=list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //加價購更改排序值
        if(!empty($_REQUEST["sort_value"]) && !empty($ws_table)){
            if($ws_table=="au"){
                $table_name=$cms_cfg['tb_prefix']."_aboutus";
            }
            foreach($_REQUEST["id"] as $key => $value){
                $sql="update ".$table_name." set ".$ws_table."_sort='".$_REQUEST["sort_value"][$value]."' where ".$ws_table."_id='".$value."'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
            }
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."aboutus.php?func=".$ws_table."_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //複製單筆資料
    function copy_data($ws_table){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //加價購複製
        if($ws_table=="au"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_aboutus where id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_aboutus (
                        status,
                        sort,
                        subject,
                        content,
                        modifydate
                    ) values (
                        ".$row["status"].",
                        '".$row["sort"]."',
                        '".$row["subject"]."',
                        '".$row["content"]."',
                        '".date("Y-m-d H:i:s")."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."aboutus.php?func=list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }

    //資料處理
    function data_processing(){
        switch ($_REQUEST["process_type"]){
            case "del":
                if($_REQUEST["ws_table"]=="au"){
                    $this->config_del();
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
    
    //取得cate
    function get_cate_option($rdata){
        global $db,$tpl,$cms_cfg;
        $tpl->assignGlobal("CATE_AREA_TYPE",($rdata && $rdata['cate']!='aboutus')?"block":"none");
        $tpl->assignGlobal("AU_CATE_CHECKED",($rdata && $rdata['cate']!='aboutus')?"checked":"");
        $sql = "select distinct cate from ".$cms_cfg['tb_prefix']."_aboutus where cate<>'aboutus'";
        $res = $db->query($sql,true);
        while($row = $db->fetch_array($res,1)){
            $tpl->newBlock("AU_CATE_OPTION");
            $tpl->assign("VALUE_AU_CATE",$row['cate']);
            $tpl->assign("VALUE_AU_OPTION_SELECTED",($row['cate']==$rdata['cate'])?"selected":"");
        }
    }
    //取得cate
    function get_subcate_option($rdata){
        global $db,$tpl,$cms_cfg;
        $sql = "select distinct subcate from ".$cms_cfg['tb_prefix']."_aboutus where subcate<>'' ";
        $res = $db->query($sql,true);
        while($row = $db->fetch_array($res,1)){
            $tpl->newBlock("AU_SUBCATE_OPTION");
            $tpl->assign("VALUE_AU_SUBCATE",$row['subcate']);
            $tpl->assign("VALUE_AU_OPTION_SELECTED",($row['subcate']==$rdata['subcate'])?"selected":"");
        }
    }
}
//ob_end_flush();
?>
