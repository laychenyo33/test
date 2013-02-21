<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$download = new DOWNLOAD;
class DOWNLOAD{
    function DOWNLOAD(){
        global $db,$cms_cfg,$tpl,$main,$TPLMSG;
        $this->op_limit=$cms_cfg["dlop_limit"];
        $this->jp_limit=$cms_cfg['jp_limit'];
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->ps = $cms_cfg['path_separator'];
        if(!empty($_REQUEST["type"])){
            $_REQUEST["func"]="d_".$_REQUEST["type"];
            $this->func_str=$cms_cfg['base_root']."download/".$_REQUEST["f"];
        }else{
            $_REQUEST["f"]="download";
            $this->func_str=$cms_cfg['base_root'].$_REQUEST["f"];
        }
        //需要會員權限,則顯示登入表單
        if(empty($_SESSION[$cms_cfg["sess_cookie_name"]]["MEMBER_ID"]) && $cms_cfg["ws_module"]["ws_download_login"]==1){
            $this->ws_tpl_file = "templates/ws-login-form-tpl.html";
            $this->ws_load_tp($this->ws_tpl_file);
            $tpl->assignGlobal( "MSG_MEMBER_LOGIN",$TPLMSG["MEMBER_LOGIN"]);
            $tpl->assignGlobal( "MSG_LOGIN_NOTICE1",$TPLMSG['LOGIN_NOTICE1']);
        }else{
            $this->ws_tpl_file = "templates/ws-download-tpl.html";
            $this->ws_load_tp($this->ws_tpl_file);
            $this->download_list();
        }
        //page view record --ph_type,ph_type_id,m_id
        $main->pageview_history("dc",$_REQUEST["dc_id"],$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID']);
        $tpl->printToScreen();
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
        if($_REQUEST["f"]=="download"){
            $main->header_footer("download", $TPLMSG["DOWNLOAD"]);
        }
        $main->google_code(); //google analystics code , google sitemap code
    }

//檔案下載--列表================================================================
    function download_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $dc_id=0;
        $ext=($this->ws_seo)?".htm":".php";
        $download_link="<a href=\"".$cms_cfg["base_root"]."download".$ext."\">".$TPLMSG["DOWNLOAD"]."</a>";
        //檔案下載分類
        $sql="select * from ".$cms_cfg['tb_prefix']."_download_cate where dc_status='1' order by dc_sort ".$cms_cfg['sort_pos']." ";
        $selectrs = $db->query($sql);
        $i=0;
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
            //顯示左方分類
            $tpl->newBlock( "LEFT_CATE_LIST" );
            $tpl->assign( array( "VALUE_CATE_NAME" => $row["dc_subject"],
                                 "VALUE_CATE_LINK"  => $cate_link,
            ));
            if($_REQUEST["dc_id"]==$row["dc_id"]  || ($_REQUEST["f"]==$row["dc_seo_filename"])){
                $tpl->assign( "TAG_CURRENT_CLASS"  , "class='current'");
                $download_link .= $this->ps."<a href=\"".$cate_link."\">".$row["dc_subject"]."</a>";
                if($this->ws_seo){
                    $meta_array=array("meta_title"=>$row["dc_seo_title"],
                                      "meta_keyword"=>$row["dc_seo_keyword"],
                                      "meta_description"=>$row["dc_seo_description"],
                                      "seo_short_desc" => $row["dc_seo_short_desc"],
                                      "seo_h1"=>(trim($row["dc_seo_h1"])=="")?$row["dc_subject"]:$row["dc_seo_h1"],
                    );
                    echo $row["dc_seo_short_desc"];
                    $main->header_footer($meta_array);
                }else{
                    $main->header_footer("download",$TPLMSG["DOWNLOAD"]);
                }
                $dc_id=$row["dc_id"];
            }
        }
        $tpl->assignGlobal("TAG_LAYER",$download_link);
        //檔案下載列表
        if($dc_id!=0){
            $and_str="and d.dc_id='".$dc_id."'";
        }
        $sql="select d.*,dc.dc_subject from ".$cms_cfg['tb_prefix']."_download as d left join ".$cms_cfg['tb_prefix']."_download_cate as dc on d.dc_id=dc.dc_id
                  where  d.d_status='1' ".$and_str." order by d.d_sort ".$cms_cfg['sort_pos']." ";
        //取得總筆數
        $selectrs = $db->query($sql);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結
        if($this->ws_seo==1  && trim($_REQUEST["f"])!=""){
            //$func_str="download/dlist-".$dc_id;
            $func_str=$this->func_str;
            $page=$main->pagination_rewrite($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records);
        }else{
            $func_str=$cms_cfg["base_root"]."download.php?func=d_list&dc_id=".$dc_id;
            $page=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records);
        }
        //重新組合包含limit的sql語法
        $sql=$main->sqlstr_add_limit($this->op_limit,$_REQUEST["nowp"],$sql);
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
            $tpl->assign( array("VALUE_DC_ID"  => $row["dc_id"],
                                "VALUE_DC_LINK"  => $cate_link,
                                "VALUE_DC_SUBJECT"  => $row["dc_subject"],
                                "VALUE_D_ID"  => $row["d_id"],
                                "VALUE_D_SUBJECT" => $row["d_subject"],
                                "VALUE_D_CONTENT" => $row["d_content"],
                                "VALUE_D_FILEPATH" => $cms_cfg['file_root'].$row["d_filepath"],
                                "VALUE_D_MODIFYDATE" => $row["d_modifydate"],
                                "VALUE_D_SERIAL" => $i,
                                "VALUE_DC_SUBJECT"  => $row["dc_subject"],
            ));
            //如果下載顯示縮圖，開啟縮圖欄位
            if($cms_cfg['ws_module']['ws_download_thumb']){
                $tpl->newBlock("THUMB_TITLE");
                $tpl->assign(array(
                    "VALUE_D_THUMB" => trim($row["d_thumb"])?$cms_cfg['file_root'].$row["d_thumb"]:$cms_cfg['default_ebook_pic'],                    
                ));
            }
        }
        //分頁
        if($i==0){
            $tpl->assignGlobal("MSG_NO_DATA",$TPLMSG['NO_DATA']);
        }else{
            $tpl->newBlock( "PAGE_DATA_SHOW" );
            $tpl->assign( array("VALUE_TOTAL_RECORDS"  => $page["total_records"],
                                "VALUE_TOTAL_PAGES"  => $page["total_pages"],
                                "VALUE_PAGES_STR"  => $page["pages_str"],
                                "VALUE_PAGES_LIMIT"=> $this->op_limit
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
?>
