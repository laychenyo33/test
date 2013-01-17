<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$video = new VIDEO;
class VIDEO{
    function VIDEO(){
        global $db,$cms_cfg,$tpl,$main;
        //show page
        $this->ws_tpl_file = "templates/ws-video-tpl.html";
        $this->ws_load_tp($this->ws_tpl_file);
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->video_list();
        //page view record --ph_type,ph_type_id,m_id
        $main->pageview_history("v",$_REQUEST["v_id"],$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID']);
        $tpl->printToScreen();
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$db,$ws_array,$TPLMSG,$main;
        $tpl = new TemplatePower( $cms_cfg['base_all_tpl'] );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方一般表單
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["VIDEO"]);
        $tpl->assignGlobal( "TAG_LAYER" , $TPLMSG["VIDEO"]);
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["video"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["video"]);//左方menu title
        $tpl->assignGlobal( "TAG_VIDEO_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["video"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-video"); //主要顯示區域的css設定
        $main->google_code(); //google analystics code , google sitemap code
        $main->login_zone();
    }
    //前台關於我們--列表================================================================
    function video_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //前台關於我們列表
        $sql="select * from ".$cms_cfg['tb_prefix']."_video  where v_status='1' order by v_sort ".$cms_cfg['sort_pos'].",v_modifydate desc";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        if(empty($_REQUEST["v_id"]) && empty($_REQUEST["f"])){
           $sel_top_record=true;
        }
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            if($this->ws_seo==1 ){
                $cate_link=$cms_cfg["base_root"]."video/".$row["v_seo_filename"].".html";
                $ext="htm";
            }else{
                $cate_link=$cms_cfg["base_root"]."video.php?v_id=".$row["v_id"];
                $ext="php";
            }
            $tpl->newBlock( "LEFT_CATE_LIST" );
            $tpl->assign( array( "VALUE_CATE_NAME" => $row["v_subject"],
                                 "VALUE_CATE_LINK"  => ($i==1)?$cms_cfg["base_root"]."video.".$ext:$cate_link,
            ));
            if(($i==1 && $sel_top_record) || ($_REQUEST["v_id"]==$row["v_id"]) || ($this->ws_seo && ($_REQUEST["f"]==$row["v_seo_filename"]))){
                if($this->ws_seo){
                    $meta_array=array("meta_title"=>$row["v_seo_title"],
                                      "meta_keyword"=>$row["v_seo_keyword"],
                                      "meta_description"=>$row["v_seo_description"],
                                      "seo_h1"=>(trim($row["v_seo_h1"])=="")?$row["v_subject"]:$row["v_seo_h1"],
                    );
                    $main->header_footer($meta_array);
                }else{
                    $main->header_footer("video",$TPLMSG["VIDEO"]);
                }
                //$tpl->assignGlobal( "TAG_SUB_FUNC"  , "--&nbsp;&nbsp;".$row["v_subject"]);
                $row["v_content"]=preg_replace("/src=\"([^>]+)upload_files/","src=\"".$cms_cfg["file_root"]."upload_files",$row["v_content"]);
                //$row["v_content"]=preg_replace("/..\/upload_files/",$cms_cfg["file_root"]."upload_files",$row["v_content"]);
                $tpl->assignGlobal( "TAG_LAYER" , "<a href='".$cms_cfg['base_root']."video.htm'>".$TPLMSG['VIDEO']."</a>" . $cms_cfg['path_separator'] . $row["v_subject"]);
                $tpl->assignGlobal( "VALUE_V_CONTENT" , $row["v_content"]);
            }
        }
    }
 
}
?>