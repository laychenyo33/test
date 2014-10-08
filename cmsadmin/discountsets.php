<?php
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])  || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_products"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$spec = new DISCOUNTSETS;
class DISCOUNTSETS{
    function DISCOUNTSETS(){
        global $db,$cms_cfg,$tpl;
        $this->seo=($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_seo"] && $cms_cfg["ws_module"]["ws_seo"])?1:0;
        switch($_REQUEST["func"]){
            case "del"://規格分類刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->discountsets_del();
                $this->ws_tpl_type=1;
                break;            
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            case "add"://規格分類新增
            case "mod"://規格分類修改
                $this->current_class="PDS";
                $this->ws_tpl_file = "templates/ws-manage-discountsets-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_JQ_UI");
                $tpl->newBlock("JS_APPEND_GRID");
                $this->discountsets_form($_REQUEST["func"]);
                $this->ws_tpl_type=1;
                break;
            case "replace"://規格分類更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->discountsets_replace();
                $this->ws_tpl_type=1;
                break;
            case "del"://規格分類刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->discountsets_del();
                $this->ws_tpl_type=1;
                break;
            case "list"://規格分類列表
            default:    //規格列表
                $this->current_class="PDS";
                $this->ws_tpl_file = "templates/ws-manage-discountsets-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->discountsets_list();
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
    function discountsets_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $sql="select * from ".$db->prefix("products_discountsets");
        //取得總筆數
        $total_records=$main->count_total_records($sql);
        //取得分頁連結
        $func_str="discountsets.php?func=list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        //分頁且重新組合包含limit的sql語法
        $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array(
            "MSG_SUBJECT"  => $TPLMSG['CATE'].$TPLMSG['NAME'],
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
        $i=$main->get_pagination_offset($cms_cfg["op_limit"]);
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "DISCOUNTSETS_LIST" );
            $tpl->assign( array(
                "ID"  => $row["id"],
                "NAME" => $row["name"],
                "TAG_SERIAL" => $i,
                "VALUE_STATUS_IMG" => ($row["status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                "VALUE_STATUS_IMG_ALT" => ($row["status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
            ));
        }
    }
    //規格分類--表單
    function discountsets_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //欄位名稱
        $tpl->assignGlobal( array(
            "MSG_MODE" => $TPLMSG['ADD'], //表單預設模式為『新增』
            "VALUE_ACTION_MODE" => $action_mode,
        ));
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array(
                "VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                "VALUE_JUMP_PAGE" => $_REQUEST['jp'],
            ));
        }
        //如果為修改模式,帶入資料庫資料
        if($action_mode=="mod" && !empty($_REQUEST["id"])){
            $row = App::getHelper('dbtable')->products_discountsets->getData($_REQUEST["id"])->getDataRow();
            if ($row) {
                $tpl->assignGlobal( array(
                    "ID"       => $row["id"],
                    "NAME"     => $row["name"],
                    "MSG_MODE" => $TPLMSG['MODIFY'] //表單模式變更為『修改』
                ));
            }else{
                header("location : discountsets.php?func=list");
                die();
            }
        }
        //狀態radio
        App::getHelper('main')->multiple_radio("status",$ws_array["default_status"],isset($row['status'])?$row['status']:1,$tpl);
        //折扣組合
        $discountData = App::getHelper('dbtable')->products_discount->getDataList("sets='".$row['id']."'","qtyfloor,qtyceil,discount,id"," sort ");
        $tpl->assignGlobal("TAG_DATA_JSON",  json_encode($discountData));        
    }
    //規格分類--資料更新
    function discountsets_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        App::getHelper('dbtable')->products_discountsets->writeData($_POST);
        $sets_id = $_POST['id']?$_POST['id']:App::getHelper('dbtable')->products_discountsets->get_insert_id();
        $db_msg = App::getHelper('dbtable')->products_discountsets->report();
        if ( $db_msg == "" ) {
            foreach($_POST as $k => $v){
                if(preg_match("/^appendGridTable_/i",$k)){
                    $nk = explode("_",$k);
                    if(count($nk)==3){ //避免rowOrder也被當資料寫入
                        $new_data_array[$nk[2]][$nk[1]] = $v;
                        if($nk[1]=='id' && $v!=0){
                            $update_id[] = $v;
                        }
                    }
                }
            }
            //刪除不在$update_id裡的記錄
            if(!empty($update_id)){
                App::getHelper('dbtable')->products_discount->deleteByCon("sets='".$sets_id."' and  id not in(".implode(',',$update_id).") ");
            }
            $n_sort=1;
            if(!empty($new_data_array)){
                foreach($new_data_array as $record){
                    $record['sets']=$sets_id;
                    $record['sort']=$n_sort;
                    App::getHelper('dbtable')->products_discount->writeData($record);
                    $n_sort++;
                }
            }            
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$cms_cfg["manage_url"]."discountsets.php?func=list&id=".$_REQUEST["id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
            $this->goto_target_page($goto_url);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
    //規格分類--刪除
    function discountsets_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $id=(array)$_REQUEST["id"];
        if(!empty($id)){
            $id_str = implode(",",$id);
            //清空分類底下的規格
//            $sql="delete from ".$cms_cfg['tb_prefix']."_products_spec_title where id in (".$id_str.")";
//            $rs = $db->query($sql);
//            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                //刪除組合
                App::getHelper('dbtable')->products_discountsets->deleteByCon("id in (".$id_str.")");
                $db_msg = App::getHelper('dbtable')->products_discountsets->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."discountsets.php?func=list&id=".$_REQUEST["id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        $sql="select * from ".$cms_cfg['tb_prefix']."_products_spec_cate where id > '0' and type='0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        //沒有分類先建立分類
        if($rsnum<1){
            $tpl->assignGlobal( "MSG_CREATE_CATE_FIRST" , $TPLMSG["CREATE_CATE_FIRST"]);
            $goto_url=$cms_cfg["manage_url"]."discountsets.php?func=add";
            $this->goto_target_page($goto_url);
        }else{
            //規格分類
            $i=0;
            $tpl->assignGlobal("TAG_NOW_CATE" ,$TPLMSG["NO_CATE"]);
			$tpl->assignGlobal("VALUE_PSC_ID" ,$_REQUEST["id"]);
            while($row = $db->fetch_array($selectrs,1)){
                $i++;
                $tpl->newBlock( "DISCOUNTSETS_CATE_LIST" );
                $tpl->assign( array( "VALUE_PSC_SUBJECT"  => $row["subject"],
                                     "VALUE_PSC_ID" => $row["id"],
                                     "VALUE_PSC_SERIAL" => $i,
                                     "VALUE_STATUS_IMG" => ($row["status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                     "VALUE_STATUS_IMG_ALT" => ($row["status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
                ));
                if($i%4==0){
                    $tpl->assign("TAG_DISCOUNTSETS_CATE_TRTD","</tr><tr>");
                }
                if($row["id"]==$_REQUEST["id"]){
                    $tpl->assignGlobal("TAG_NOW_CATE",$row["subject"]);
                }
            }
            //規格列表
            $sql="select pst.*,psc.subject from ".$cms_cfg['tb_prefix']."_products_spec_title as pst left join ".$cms_cfg['tb_prefix']."_products_spec_cate as psc on pst.id=psc.id where pst.pst_id > '0'";
            //附加條件
            $and_str="";
            if(!empty($_REQUEST["id"])){
                $and_str .= " and pst.id = '".$_REQUEST["id"]."'";
            }
            $and_str .= " and (pst.pst_subject like '%".$_REQUEST["sk"]."%')";
            $sql .= $and_str." order by pst.pst_sort ".$cms_cfg['sort_pos'].",pst.pst_modifydate desc ";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records    = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="discountsets.php?func=pst_list&id=".$_REQUEST["id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
            //分頁且重新組合包含limit的sql語法
            $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $tpl->assignGlobal( array("VALUE_TOTAL_BOX" => $rsnum,
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                      "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],
            ));

            $tpl->assignGlobal( "VALUE_NOW_PSC_ID" , $_REQUEST["id"]);
            $i=$page["start_serial"];
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "DISCOUNTSETS_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow'");
                }
                $tpl->assign( array("VALUE_PSC_ID"  => $row["id"],
                                    "VALUE_PST_ID"  => $row["pst_id"],
                                    "VALUE_PST_SORT"  => $row["pst_sort"],
                                    "VALUE_PST_SUBJECT" => $row["pst_subject"],
                                    "VALUE_PST_SERIAL" => $i,
                                    "VALUE_PSC_SUBJECT"  => $row["subject"],
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
        $cate=(trim($_REQUEST["id"])!="")?1:0;
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "VALUE_PST_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_products_spec_title","pst","id",$_REQUEST["id"],$cate),
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
                header("location : discountsets.php?func=pst_list");
            }
        }
        //規格分類
        $sql="select * from ".$cms_cfg['tb_prefix']."_products_spec_cate where id > '0' and type='0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        while($row1 = $db->fetch_array($selectrs,1)){
            $tpl->newBlock( "TAG_SELECT_DISCOUNTSETS_CATE" );
            $tpl->assign( array( "TAG_SELECT_DISCOUNTSETS_CATE_NAME"  => $row1["subject"],
                                 "TAG_SELECT_DISCOUNTSETS_CATE_VALUE" => $row1["id"],
                                 "STR_PSC_SEL"       => ($_GET["id"]==$row1["id"])?"selected":""
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
                        id,
                        pst_status,
                        pst_sort,
                        pst_subject,
						pst_img,
                        pst_modifydate
                    ) values (
                        '".$_REQUEST["id"]."',
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
                        id='".$_REQUEST["id"]."',
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
                $goto_url=$cms_cfg["manage_url"]."discountsets.php?func=pst_list&id=".$_REQUEST["id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
                $goto_url=$cms_cfg["manage_url"]."discountsets.php?func=pst_list&id=".$_REQUEST["id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
    function change_status($value){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $id=(array)$_REQUEST["id"];
        if(!empty($id)){
            $id_str = implode(",",$id);
            //更改分類底下的規格狀態
//            $sql="update ".$cms_cfg['tb_prefix']."_products_spec_title set pst_status='".$value."' where id in (".$id_str.")";
//            $rs = $db->query($sql);
//            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                App::getHelper('dbtable')->products_discountsets->update(array('status'=>$value),"id in (".$id_str.")");
                $db_msg = App::getHelper('dbtable')->products_discountsets->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."discountsets.php?func=list&id=".$_REQUEST["id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
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
                $goto_url=$cms_cfg["manage_url"]."discountsets.php?func=".$ws_table."_list&id=".$_REQUEST["id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
            $sql="select * from ".$cms_cfg['tb_prefix']."_products_spec_cate where id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_products_spec_cate (
                        status,
                        sort,
                        subject
                    ) values (
                        '".$row["status"]."',
                        '".$row["sort"]."',
                        '".$row["subject"]."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."discountsets.php?func=list&id=".$_REQUEST["id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
                        id,
                        pst_status,
                        pst_sort,
                        pst_subject,
                        pst_content,
                        pst_modifydate
                    ) values (
                        '".$row["id"]."',
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
                    $goto_url=$cms_cfg["manage_url"]."discountsets.php?func=pst_list&id=".$_REQUEST["id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
                $this->discountsets_del();
                break;
            case "copy":
                $this->copy_data($_REQUEST["ws_table"]);
                break;
            case "status":
                $this->change_status($_REQUEST["value"]);
                break;
            case "sort":
                $this->change_sort($_REQUEST["ws_table"]);
                break;
        }
    }
}
//ob_end_flush();
?>
