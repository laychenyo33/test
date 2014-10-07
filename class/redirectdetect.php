<?php
class Redirectdetect{
    function __construct(){
        header("HTTP/1.0 404 Not Found");
        $this->ws_load_tp('templates/404/'.App::configs()->language.'.html');
        $tpl = $GLOBALS['tpl'];
        App::getHelper('main')->header_footer('404');
        $sitemap = new Singleton_Sitemap;
        $sitemap->sitemap_list();
        App::getHelper('main')->layer_link("error 404");
        App::getHelper('main')->layer_link();
        $tpl->printToScreen();
    }    
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$ws_array,$db,$TPLMSG,$main;
        $ext=($this->ws_seo)?"htm":"php";
        $this->top_layer_link="<a href='".$cms_cfg['base_root']."products.".$ext."'>".$TPLMSG["PRODUCTS"]."</a>";
        $tpl = new TemplatePower( "templates/ws-fn-404-tpl.html" );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方一般表單
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        $tpl->assignInclude( "AD_H", "templates/ws-fn-ad-h-tpl.html"); //橫式廣告模板
        $tpl->assignInclude( "AD_V", "templates/ws-fn-ad-v-tpl.html"); //直式廣告模板        
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["PRODUCTS"]);
        $tpl->assignGlobal( "TAG_LAYER" , $this->top_layer_link);
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["products"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["products"]);//左方menu title
        if($_GET['func']=="p_new"){
            $tpl->assignGlobal( "TAG_PRODUCTS_NEW_CURRENT" , "class='current'"); //上方menu current
        }else{
            $tpl->assignGlobal( "TAG_PRODUCTS_CURRENT" , "class='current'"); //上方menu current
        }
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["products"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-products"); //主要顯示區域的css設定
        $main->google_code(); //google analystics code , google sitemap code
        //$main->left_fix_cate_list();
        $leftmenu = new Leftmenu_Products($tpl,2,false);
        $leftmenu->make();
    }
}