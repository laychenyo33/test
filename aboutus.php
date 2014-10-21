<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$aboutus = new ABOUTUS;
class ABOUTUS{
    function ABOUTUS(){
        global $db,$cms_cfg,$tpl,$main;
        //show page
        $this->ws_tpl_file = "templates/ws-aboutus-tpl.html";
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->au_cate = $_REQUEST['au_cate']?$_REQUEST['au_cate']:'aboutus';        
        $this->ws_load_tp($this->ws_tpl_file);
        $this->aboutus_list();
        $main->layer_link();
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
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["ABOUT_US"]);
        $tpl->assignGlobal( "TAG_LAYER" , $TPLMSG["ABOUT_US"]);
        $tpl->assignGlobal( "TAG_".  strtoupper($this->au_cate)."_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["aboutus"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-aboutus"); //主要顯示區域的css設定
        //$main->header_footer("aboutus");
        $main->google_code(); //google analystics code , google sitemap code
//        if($this->au_cate!="aboutus"){
//            $main->layer_link($ws_array["main"][$this->au_cate],$cms_cfg['base_root'].$this->au_cate.".htm");
//        }
        if($cms_cfg["ws_module"]["ws_left_main_au"]==0){
            $main->left_fix_cate_list();
            $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["products"]);//左方menu title
            $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["products"]);//左方menu title
        }else{
            $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"][$this->au_cate]);//左方menu title
            $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"][$this->au_cate]);//左方menu title
        }
    }
    //前台關於我們--列表================================================================
    function aboutus_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //左側選單
        $row = $this->left_cate_list();
        $main->pageview_history($main->get_main_fun(),$row['au_id'],$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID']);        
        if($row){
            $main->layer_link($row["au_subject"]);
            $tpl->assignGlobal( "VALUE_AU_CONTENT" , $main->content_file_str_replace($row["au_content"],'out2'));
        }else{
            $main->js_notice($TPLMSG["PAGE_NO_EXITS"],$cms_cfg['base_root']);
        }
    }
    function left_cate_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //前台關於我們列表
        if($cms_cfg['ws_activate_mobile']){
            $and_str = ($cms_cfg['ws_ismobile'])?"and mobilehide='0'":"and mobileonly='0'";
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_aboutus  where au_status='1' and au_cate = '".$this->au_cate."' {$and_str} order by au_sort ".$cms_cfg['sort_pos'].",au_modifydate desc";
        $selectrs = $db->query($sql);
        if(empty($_REQUEST["au_id"]) && empty($_REQUEST["f"])){
           $sel_top_record=true;
        }
        if($cms_cfg['ws_module']['ws_aboutus_au_subcate'] && $cms_cfg['ws_module']['ws_aboutus_au_subcate_effect']){        
            $tpl->newBlock("JS_LEFT_AU_SUBCATE");
        }
        $current_row = null;
        $i=0;
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $menuItem = array();
            $menuItem['name'] = $row['au_subject'];
            $menuItem['link'] = ($i==1)?$cms_cfg["base_root"].$this->au_cate.".htm":$this->get_link($row);
            if(($i==1 && $sel_top_record) || ($_REQUEST["au_id"]==$row["au_id"]) || ($this->ws_seo && ($_REQUEST["f"] && $_REQUEST["f"]==$row["au_seo_filename"]))){
                $menuItem['tag_cur'] = "class='current'";
                $current_row = $row;
                if($this->ws_seo){
                    $meta_array=array("meta_title"=>$row["au_seo_title"],
                                      "meta_keyword"=>$row["au_seo_keyword"],
                                      "meta_description"=>$row["au_seo_description"],
                                      "seo_h1"=>(trim($row["au_seo_h1"])=="")?$row["au_subject"]:$row["au_seo_h1"],
                    );
                    $main->header_footer($meta_array);
                }else{
                    $main->header_footer("aboutus",$row["au_subject"]);
                }
            }            
            if($cms_cfg["ws_module"]["ws_left_main_au"]==1){
                if($cms_cfg['ws_module']['ws_aboutus_au_subcate']){
                    if(!empty($row['au_subcate'])){
                        if(!isset($left_menu[$row['au_subcate']])){
                            $left_menu[$row['au_subcate']]['name'] = App::defaults()->au_subcate[$row['au_subcate']];
                            $left_menu[$row['au_subcate']]['link'] = '#';
                        }
                        if(isset($menuItem['tag_cur'])){
                            $left_menu[$row['au_subcate']]['tag_cur'] = "class='current'";
                        }
                        $left_menu[$row['au_subcate']]['sub'][]=$menuItem;
                    }else{
                        $left_menu[]=$menuItem;
                    }
                }else{
                    $left_menu[]=$menuItem;
                }
            }
        }
        if($left_menu){
            App::getHelper('main')->new_left_menu($left_menu);
        }
        return $current_row;
    }
    //取得aboutus連結
    function get_link($row){
        global $cms_cfg;
        if($this->ws_seo==1 ){
            if($row["au_seo_filename"]){
                $cate_link=$cms_cfg["base_root"].$this->au_cate."/".$row["au_seo_filename"].".html";
            }else{
                $cate_link=$cms_cfg["base_root"].$this->au_cate."-".$row["au_id"].".html";
            }
        }else{
            if($cms_cfg["ws_module"]['ws_aboutus_au_cate']){
                $cate_link=$cms_cfg["base_root"]."aboutus.php?au_cate=".$row['au_cate']."&au_id=".$row["au_id"];
            }else{
                $cate_link=$cms_cfg["base_root"]."aboutus.php?au_id=".$row["au_id"];
            }
        } 
        return $cate_link;
    }
}
?>