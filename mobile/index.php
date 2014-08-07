<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$maindefault = new MAINDEFAULT;
class MAINDEFAULT{
    function MAINDEFAULT(){
        global $main,$cms_cfg,$tpl;
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        //show page
        $this->ws_tpl_file = "templates/ws-index-tpl.html";
        $this->ws_load_tp($this->ws_tpl_file);
        $tpl->printToScreen();
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$ws_array,$db,$TPLMSG,$main;
        $tpl = new TemplatePower( $this->ws_tpl_file );
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_INDEX_CURRENT" , "class='current'");
        $main->header_footer("");
    }
}
?>