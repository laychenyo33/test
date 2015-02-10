<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$download = new DOWNLOAD;
class DOWNLOAD{
    protected $need_login;
    protected $member_download;
    protected $download_on;
    protected $is_login;
    function DOWNLOAD(){
        global $db,$cms_cfg,$tpl,$main,$TPLMSG;
        $this->op_limit=$cms_cfg["dlop_limit"];
        $this->jp_limit=$cms_cfg['jp_limit'];
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->ps = $cms_cfg['path_separator'];
        $this->need_login = $cms_cfg['ws_module']['ws_download_login'];
        $this->member_download = $cms_cfg['ws_module']['ws_member_download'];
        $this->download_on = $cms_cfg['ws_module']['ws_member_download_on'];
        $this->is_login = isset($_SESSION[$cms_cfg["sess_cookie_name"]]["MEMBER_ID"]);
        //需要會員權限,則顯示登入表單
        if(!$this->is_login && $this->need_login){
            $this->ws_tpl_file = "templates/ws-login-form-tpl.html";
            $this->ws_load_tp($this->ws_tpl_file);
            $main->layer_link($TPLMSG["MEMBER_LOGIN"]);
            $tpl->assignGlobal( "MSG_MEMBER_LOGIN",$TPLMSG["MEMBER_LOGIN"]);
            $tpl->assignGlobal( "MSG_LOGIN_NOTICE1",$TPLMSG['LOGIN_NOTICE1']);
            $this->ws_tpl_type = 1;
        }else{
            switch($_GET['func']){
                case "dl":
                    $this->ws_tpl_type=0;
                    $this->download_file($_GET['d_id']);
                    break;
                case "tlist":
                    $this->ws_tpl_type=1;
                    $this->ws_tpl_file = "templates/ws-download-withtime-tpl.html";
                    $this->ws_load_tp($this->ws_tpl_file);
                    $this->download_list_withtime();
                    break;                
                case "search":
                    $condition = " and d_subject like '%{$_GET['kw']}%' ";
                default:
                    $this->ws_tpl_type=1;
                    if(!empty($_REQUEST["type"])){
                        $_REQUEST["func"]="d_".$_REQUEST["type"];
                        $this->func_str=$cms_cfg['base_root']."download/".$_REQUEST["f"];
                    }else{
                        $_REQUEST["f"]="download";
                        $this->func_str=$cms_cfg['base_root'].$_REQUEST["f"];
                    }
                    $this->ws_tpl_file = "templates/ws-download-tpl.html";
                    $this->ws_load_tp($this->ws_tpl_file);
                    $this->download_list($condition);
            }
        }        
        if($this->ws_tpl_type){
            $main->layer_link();
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
        $tpl->assignInclude( "AD_H", "templates/ws-fn-ad-h-tpl.html"); //橫式廣告模板
        $tpl->assignInclude( "AD_V", "templates/ws-fn-ad-v-tpl.html"); //直式廣告模板     
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["DOWNLOAD"]);
        $tpl->assignGlobal( "TAG_LAYER" , $TPLMSG["DOWNLOAD"]);
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["download"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["download"]);//左方menu title
        $tpl->assignGlobal( "TAG_DOWNLOAD_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["download"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-download"); //主要顯示區域的css設定
        $tpl->assignGlobal( "SUBMENU_TYPE" , "submenu02"); //左側選單的容器類別
//        if($_REQUEST["f"]=="download"){
//        }
        //$main->header_footer("download", $TPLMSG["DOWNLOAD"]);
        $main->google_code(); //google analystics code , google sitemap code
    }

//檔案下載--列表================================================================
    function download_list($con){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //檔案下載列表
        $crow = $this->left_cate_list();
        if($crow && $this->ws_seo){
            $meta_array=array(
                "meta_title"=>$crow["dc_seo_title"],
                "meta_keyword"=>$crow["dc_seo_keyword"],
                "meta_description"=>$crow["dc_seo_description"],
                "seo_short_desc" => $crow["dc_seo_short_desc"],
                "seo_h1"=>(trim($crow["dc_seo_h1"])=="")?$crow["dc_subject"]:$crow["dc_seo_h1"],
            );
            $main->header_footer($meta_array);
        }else{
            $h1 = $crow['dc_subject']?$crow['dc_subject']:$TPLMSG['DOWNLOAD'];
            $main->header_footer("download",$h1);
        }
        $dc_id=$crow["dc_id"];        
        $main->pageview_history($main->get_main_fun(),$dc_id,App::getHelper('session')->MEMBER_ID);
        
        if($dc_id!=0){
            $and_str=" and d.dc_id='".$dc_id."'";
            $ext=($this->ws_seo)?".htm":".php";
            $main->layer_link($TPLMSG['DOWNLOAD'],$cms_cfg['base_root']."download".$ext)->layer_link($crow['dc_subject']);
        }else{
            $main->layer_link($TPLMSG['DOWNLOAD']);
        }
        $and_str .= $con;
        $sql="select d.*,dc.*,'0' as dtype from ".$cms_cfg['tb_prefix']."_download as d left join ".$cms_cfg['tb_prefix']."_download_cate as dc on d.dc_id=dc.dc_id
                  where  d.d_status='1' and d.d_public='1' ".$and_str;
        if($this->member_download && $this->is_login){
            if($this->download_on=="member"){
                $m_id = $_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID'];
                $sql .= " union select d.*,dc.*,'1' as dtype from ".$cms_cfg['tb_prefix']."_download as d inner join ".$cms_cfg['tb_prefix']."_member_download_map as mdm 
                        inner join ".$cms_cfg['tb_prefix']."_download_cate as dc on d.d_id=mdm.d_id and d.dc_id=dc.dc_id 
                        where d.d_public='0' and mdm.m_id='".$m_id."'".$and_str;                        
            }else{
                $mc_id = $_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_CATE_ID'];
                $sql .= " union select d.*,dc.*,'2' as dtype from ".$cms_cfg['tb_prefix']."_download as d inner join ".$cms_cfg['tb_prefix']."_member_download_map as mdm 
                        inner join ".$cms_cfg['tb_prefix']."_download_cate as dc on d.d_id=mdm.d_id and d.dc_id=dc.dc_id 
                        where d.d_public='0' and mdm.mc_id='".$mc_id."'".$and_str;         
            }
        }
        $sql .= " order by dtype desc,d_sort ".$cms_cfg['sort_pos'];
        //取得總筆數
        $selectrs = $db->query($sql,true);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結且重新組合包含limit的sql語法
        if($this->ws_seo==1  && trim($_REQUEST["f"])!=""){
            //$func_str="download/dlist-".$dc_id;
            $func_str=$this->func_str;
            $sql=$main->pagination_rewrite($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        }else{
            $func_str=$cms_cfg["base_root"]."download.php?func=d_list&dc_id=".$dc_id;
            $sql=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        }
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("MSG_SUBJECT" => $TPLMSG['SUBJECT'],
                                  "MSG_CATE"    => $TPLMSG['CATE'],
                                  "MSG_THUMB"   => $TPLMSG['THUMB'],
                                  "MSG_DATE"    => $TPLMSG['DATE'],
                                  "MSG_CONTENT" => $TPLMSG['CONTENT']
        ));
        //如果下載顯示縮圖，開啟縮圖標題
        if($cms_cfg['ws_module']['ws_download_thumb']){
            $tpl->newBlock("THUMB_TITLE");
        }
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "DOWNLOAD_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            if($this->ws_seo==1 ){
                if(trim($row["dc_seo_filename"])==""){
                    $cate_link=$cms_cfg["base_root"]."download/dlist-".$row["dc_id"].".htm";
                }else{
                    $cate_link=$cms_cfg["base_root"]."download/".$row["dc_seo_filename"].".htm";
                }
            }else{
                $cate_link=$cms_cfg["base_root"]."download.php?func=d_list&dc_id=".$row["dc_id"];
            }
            $filepath = $cms_cfg['file_root'].$row["d_filepath"];
            $dllink = $cms_cfg['base_root']."download.php?func=dl&d_id=".$row['d_id'];
            $tpl->assign( array("VALUE_DC_ID"  => $row["dc_id"],
                                "VALUE_DC_LINK"  => $cate_link,
                                "VALUE_DC_SUBJECT"  => $row["dc_subject"],
                                "VALUE_D_ID"  => $row["d_id"],
                                "VALUE_D_SUBJECT" => $row["d_subject"],
                                "VALUE_D_CONTENT" => $row["d_content"],
                                "VALUE_D_MODIFYDATE" => date("Y-m-d",strtotime($row["d_modifydate"])),
                                "VALUE_D_SERIAL" => $i,
                                "VALUE_DC_SUBJECT"  => $row["dc_subject"],
                                "TAG_DTYPE"         => $row['dtype']?"<span class='dtype'>*</span>":"",
                                "VALUE_D_LINK"  => $row['d_type']?$filepath:$dllink,
                                "VALUE_D_TIMES"  => $row["d_times"],
            ));
            //如果下載顯示縮圖，開啟縮圖欄位
            if($cms_cfg['ws_module']['ws_download_thumb']){
                $tpl->newBlock("THUMB_COLUMN");
                $tpl->assign(array(
                    "VALUE_D_SUBJECT" => $row["d_subject"],
                    "VALUE_D_THUMB" => trim($row["d_thumb"])?$cms_cfg['file_root'].$row["d_thumb"]:$cms_cfg['default_ebook_pic'],                    
                ));
            }
        }
    }
//檔案下載-(依時間)-列表================================================================
    function download_list_withtime(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //檔案下載列表
        $crow = $this->left_cate_list();
        $main->header_footer("download",$TPLMSG["DOWNLOAD"]);
        $dc_id=$crow["dc_id"];        
        if(isset($ws_array['download_time_sets'][$_GET['sets']])){
            $data_title = array();
            $cate_arr = $ws_array['download_time_sets'][$_GET['sets']]['dc_id'];
            $time_fields = $ws_array['download_time_sets'][$_GET['sets']]['time_fields'];
            foreach($time_fields as $tfield){
                $data_title[] = $ws_array['season_month']['label'][$tfield];
                $query_fields[] = $db->fieldname($tfield);
                $query_cond[] = $db->fieldname($tfield)." is not null";
                $order_fields[] = $db->fieldname($tfield)." desc ";
            }
            $query_fields_str = implode(',',$query_fields);
            $query_cond_str = implode(' and ',$query_cond);
            $order_fields_str = implode(',',$order_fields);
        }else{
            header("location:".$cms_cfg['base_root']);
            die();
        }
        if($dc_id!=0){
            $and_str=" and d.dc_id='".$dc_id."'";
            $ext=($this->ws_seo)?".htm":".php";
            $main->layer_link($TPLMSG['DOWNLOAD'],$cms_cfg['base_root']."download".$ext)->layer_link($crow['dc_subject']);
        }else{
            $main->layer_link($TPLMSG['DOWNLOAD'])->layer_link($ws_array['download_time_sets'][$_GET['sets']]['label']);
            $tpl->assignGlobal("TAG_MAIN_FUNC",$ws_array['download_time_sets'][$_GET['sets']]['label']);
        }
        //輸出欄位
        //輸出分類欄位
        foreach($cate_arr as $dc_id){
            $sql = "select dc_subject from ".$db->prefix("download_cate")." where dc_id='{$dc_id}'";
            list($dc_subject) = $db->query_firstRow($sql,0);
            $data_title[] = $dc_subject;
        }
        foreach($data_title as $dtitle){
            $tpl->newBlock("CATE_TITLE");
            $tpl->assign("TITLE_NAME",$dtitle);
        }
        $sql = "select {$query_fields_str},group_concat(d_id) as d_ids from ".$db->prefix("download")." where dc_id in (".implode(',',$cate_arr).") and {$query_cond_str} group by {$query_fields_str} order by {$order_fields_str}";
        $res = $db->query($sql,true);
        while($row = $db->fetch_array($res,1)){
            $dbox = array();
            $tpl->newBlock("DOWNLOAD_LIST");
            foreach($time_fields as $tfield){
                if($tfield=='season'){
                    $dbox[$tfield] = $ws_array['season_month']['season'][$row[$tfield]];
                }else{
                    $dbox[$tfield] = $row[$tfield];
                }
            }
            foreach($cate_arr as $dc_id){
                $dbox[$dc_id] = array();
            }
            //取得下載連結
            $sql = "select * from ".$db->prefix("download")." where d_id in(".$row['d_ids'].")";
            $res1 = $db->query($sql,true);
            while($row2 = $db->fetch_array($res1,1)){
                //$dbox[$row2['dc_id']][] = $main->mk_link('下載',$cms_cfg['base_root']."download.php?func=dl&d_id=".$row2['d_id'],array('title'=>$row2['d_subject']));
                $dbox[$row2['dc_id']][] = $main->mk_link('下載',$cms_cfg['file_root'].$row2["d_filepath"],array('title'=>$row2['d_subject']));
            }
            //輸出下載連結
            foreach($dbox as $field_data){
                $tpl->newBlock("CATE_VALUE");
                if(is_array($field_data)){
                    $tpl->assign("FIELD_DATA",implode(',',$field_data));
                }else{
                    $tpl->assign("FIELD_DATA",$field_data);
                }
            }
        }

    }
    
    function left_cate_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //檔案下載分類
//        $sql="select dc.* from ".$cms_cfg['tb_prefix']."_download_cate as dc left join ".$cms_cfg['tb_prefix']."_download as d on dc.dc_id=d.dc_id where dc.dc_status='1' and d.d_public='1' group by dc.dc_id ";
//        if($this->member_download && $this->is_login){
//            $sql.=" union ";
//            $sql.="select dc.* from ".$cms_cfg['tb_prefix']."_download_cate as dc left join ".$cms_cfg['tb_prefix']."_download as d on dc.dc_id=d.dc_id where dc.dc_status='1' and d.d_public='0' group by dc.dc_id order by dc_sort ".$cms_cfg['sort_pos']." ";
//        }       
        $sql="select dc.* from ".$cms_cfg['tb_prefix']."_download_cate as dc  where dc.dc_status='1' order by dc_sort ".$cms_cfg['sort_pos'];
        $selectrs = $db->query($sql,true);
        $i=0;
        $menu=array();
        while($row = $db->fetch_array($selectrs,1)){
            $i++;
            if($this->ws_seo==1 ){
                if(trim($row["dc_seo_filename"])==""){
                    $cate_link=$cms_cfg["base_root"]."download/dlist-".$row["dc_id"].".htm";
                }else{
                    $cate_link=$cms_cfg["base_root"]."download/".$row["dc_seo_filename"].".htm";
                }
            }else{
                $cate_link=$cms_cfg["base_root"]."download.php?func=d_list&dc_id=".$row["dc_id"];
            }
            $tmp=array(
                'name'=>$row["dc_subject"],
                'link'=>$cate_link
            );
            if($_REQUEST["dc_id"]==$row["dc_id"]  || $_REQUEST["f"]==$row["dc_seo_filename"]){
                $tmp['tag_cur'] = "class='current'";
                $current_row = $row;
            }
            $menu[] = $tmp;
        }
        $main->new_left_menu($menu);
        return $current_row;     
    }
    //下載檔案
    function download_file($d_id){
        global $db,$cms_cfg,$main;
        if(App::configs()->ws_module->ws_member_download){
            $m_id = App::getHelper('session')->MEMBER_ID;
            $mc_id = App::getHelper('session')->MEMBER_CATE_ID;
            switch(App::configs()->ws_module->ws_member_download_on){
                case "member":
                    $cond_member_download = " and (d_public=1 || (d_public='0' and m_id='{$m_id}'))";
                    break;
                case "cate":
                default:
                    $cond_member_download = " and (d_public=1 || (d_public='0' and mc_id='{$mc_id}'))";
                    break;
            }
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_download as a left join ".$db->prefix("member_download_map")." as b "
            . "on a.d_id=b.d_id where d_status='1' and a.d_id='".$d_id."'".$cond_member_download;
        $row = $db->query_firstrow($sql,true);
        if($row){
            $filepath = $_SERVER['DOCUMENT_ROOT'].$cms_cfg['file_root'].$row['d_filepath'];
            if(file_exists($filepath)){
                $sql="update ".$cms_cfg['tb_prefix']."_download set d_times = d_times+1 where d_id='".$d_id."'";
                $db->query($sql);
                $file = $main->file_str_replace($filepath,"#(.+/)([^/]+)$#i");
                header("content-type: application/force-download");
                header("content-disposition: attachment; filename=".$file);
                readfile($filepath);
            }
        }
    }
}
?>
