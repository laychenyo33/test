<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$news = new GALLERY;
class GALLERY{
    function GALLERY(){
        global $db,$cms_cfg,$tpl,$main;
        $this->op_limit=$cms_cfg['newsop_limit'];
        $this->jp_limit=$cms_cfg['jp_limit'];
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->ps = $cms_cfg['path_separator'];
        switch($_REQUEST["func"]){
            case "g_list"://活動剪影列表
                $this->ws_tpl_file = "templates/ws-gallery-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->gallery_list();
                //page view record --ph_type,ph_type_id,m_id
                $main->pageview_history("gc",$_REQUEST["gc_id"],$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID']);
                $this->ws_tpl_type=1;
                break;
            case "g_show"://活動剪影顯示
                $this->ws_tpl_file = "templates/ws-gallery-show-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_POP_IMG");
                $this->gallery_show();
                //page view record --ph_type,ph_type_id,m_id
                $main->pageview_history("g",$_REQUEST["g_id"],$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID']);
                $this->ws_tpl_type=1;
                break;
            default:    //活動剪影列表
                $this->ws_tpl_file = "templates/ws-gallery-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->gallery_list();
                //page view record --ph_type,ph_type_id,m_id
                $main->pageview_history("gc",$_REQUEST["gc_id"],$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID']);
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
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , "活動剪影");
        $tpl->assignGlobal( "TAG_GALLERY_CURRENT" , "class='current'");
        $tpl->assignGlobal( "TAG_CATE_TITLE", "活動剪影");
        $tpl->assignGlobal( "TAG_LAYER" , "活動剪影");
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "album"); //主要顯示區域的css設定
        $main->header_footer("");
    }

//活動剪影--列表================================================================
    function gallery_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $ext=($this->ws_seo)?".html":".php";
        $gallery_link="<a href=\"".$cms_cfg["base_root"]."gallery".$ext."\">活動剪影</a>";
        //活動剪影分類
        $sql="select * from ".$cms_cfg['tb_prefix']."_gallery_cate where nc_status='1' order by nc_sort ".$cms_cfg['sort_pos']." ";
        $selectrs = $db->query($sql);
        $i=0;
        while($row = $db->fetch_array($selectrs,1)){
            $i++;
            if($this->ws_seo==1 ){
                $cate_link="gallery-glist-".$row["gc_id"].".htm";
            }else{
                $cate_link="gallery.php?func=g_list&gc_id=".$row["gc_id"];
            }
            $tpl->newBlock( "LEFT_CATE_LIST" );
            $tpl->assign( array( "VALUE_CATE_NAME" => $row["gc_subject"],
                                 "VALUE_CATE_LINK"  => $cate_link,
            ));
            if($_REQUEST["gc_id"]==$row["gc_id"]){
                //$tpl->assignGlobal("TAG_SUB_FUNC"  , "--&nbsp;&nbsp;".$row["gc_subject"]);
                $gallery_link .= $this->ps."<a href=\"".$cate_link."\">".$row["gc_subject"]."</a>";
            }
        }
        $tpl->assignGlobal("TAG_LAYER",$gallery_link);
        //活動剪影列表
        if(!empty($_REQUEST["gc_id"])){
            $gc_id=$_REQUEST["gc_id"];
            $and_str="and gc_id='".$_REQUEST["gc_id"]."'";
        }else{
            $gc_id=0;
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_gallery where (g_status='1' or (g_status='2' and g_startdate <= '".date("Y-m-d")."' and g_enddate >= '".date("Y-m-d")."')) ".$and_str." order by g_sort ".$cms_cfg['sort_pos'].",g_modifydate desc";
        $selectrs = $db->query($sql);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結
        if($this->ws_seo==1 ){
            $func_str="gallery-glist-".$gc_id;
            $page=$main->pagination_rewrite($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records);
        }else{
            $func_str="gallery.php?func=g_list&gc_id=".$gc_id;
            $page=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records);
        }
        //重新組合包含limit的sql語法
        $sql=$main->sqlstr_add_limit($this->op_limit,$_REQUEST["nowp"],$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $i=0;
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "GALLERY_LIST" );
            $tpl->assign( array("VALUE_GC_ID"  => $row["gc_id"],
                                "VALUE_G_ID"  => $row["g_id"],
                                "VALUE_G_SUBJECT" => $row["g_subject"],
                                "VALUE_G_LINK" => ($this->ws_seo)?$cms_cfg["base_root"]."gallery-gdetail-".$row["gc_id"]."-".$row["g_id"].".html":"gallery.php?func=g_show&gc_id=".$row["gc_id"]."&g_id=".$row["g_id"],
                                "VALUE_G_MODIFYDATE" => substr($row["g_modifydate"],0,10),
                                "VALUE_G_TARGET" => ($row["g_pop"])?"_blank":"_parent",
                                "VALUE_G_SERIAL" => $i,
                                "VALUE_G_S_PIC" => $row["g_s_pic"],
                                "VALUE_G_STRIP_CONTENT" => str_replace("\r\n","",strip_tags($row["g_content"])),
            ));
            if($i%3==0){
                $tpl->assign("TAG_TR","</tr><tr>");
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
                                "VALUE_PAGES_LIMIT"=>$this->op_limit
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
//活動剪影--顯示================================================================
    function gallery_show(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //活動剪影分類
        $ext=($this->ws_seo)?".html":".php";
        $gallery_link="<a href=\"".$cms_cfg["base_root"]."gallery".$ext."\">Gallery</a>";
        $sql="select * from ".$cms_cfg['tb_prefix']."_gallery_cate where nc_status='1' order by nc_sort ".$cms_cfg['sort_pos']." ";
        $selectrs = $db->query($sql);
        $i=0;
        while($row = $db->fetch_array($selectrs,1)){
            $i++;
            if($this->ws_seo==1 ){
                $cate_link="news-nlist-".$row["gc_id"].".htm";
            }else{
                $cate_link="gallery.php?func=g_list&gc_id=".$row["gc_id"];
            }
            $tpl->newBlock( "LEFT_CATE_LIST" );
            $tpl->assign( array( "VALUE_CATE_NAME" => $row["gc_subject"],
                                 "VALUE_CATE_LINK"  => $cate_link,
            ));
            if($_REQUEST["gc_id"]==$row["gc_id"]){
                $tpl->assignGlobal("TAG_SUB_TITLE"  , "--&nbsp;&nbsp;".$row["gc_subject"]);
                $gallery_link .= $this->ps."<a href=\"".$cate_link."\">".$row["gc_subject"]."</a>";
            }
        }
        //活動剪影內容
        $sql="select * from ".$cms_cfg['tb_prefix']."_gallery where n_id='".$_REQUEST["g_id"]."'";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $gallery_link .= $this->ps.$row["g_subject"];
        $tpl->newBlock( "GALLERY_SHOW" );
        //$row["g_content"]=preg_replace("/src=\"(.*)upload_files/","src=\"".$cms_cfg["file_root"]."upload_files",$row["g_content"]);
        $tpl->assign( array("VALUE_G_ID"  => $row["g_id"],
                            "VALUE_G_SUBJECT" => $row["g_subject"],
                            "VALUE_G_CONTENT" => $row["g_content"],
                            "VALUE_G_MODIFYDATE" => $row["g_modifydate"],
        ));
        for($i=1;$i<=10;$i++){
            if(trim($row["g_b_pic".$i])!=""){
                $big_img_array[]=$row["g_b_pic".$i];
            }
        }
        if(count($big_img_array) >0){
            $k=1;
            foreach($big_img_array as $key => $value){
               //顯示大圖
               $tpl->newBlock("GALLERY_PIC_LIST");
               $tpl->assign("VALUE_BIG_PIC",$cms_cfg["file_root"].$value);
               if($k%3==0){
                    $tpl->assign("TAG_TR","</tr><tr>");
               }
               $k++;
            }
        }
        $tpl->assignGlobal("TAG_LAYER",$gallery_link);
    }
}
?>
