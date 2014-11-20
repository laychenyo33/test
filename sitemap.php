<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$sitemap = new SITEMAP;
class SITEMAP{
    function SITEMAP(){
        global $db,$cms_cfg,$tpl,$main;
        //等級大於10啟動seo
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->ws_load_tp("templates/ws-sitemap-tpl.html");
        $this->sitemap_list();
        $main->pageview_history($main->get_main_fun(),0,App::getHelper('session')->MEMBER_ID);
        $main->layer_link();
        $tpl->printToScreen();
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$db,$TPLMSG,$ws_array,$main;
        $tpl = new TemplatePower( $cms_cfg['base_all_tpl'] );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方一般表單
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        $tpl->assignInclude( "AD_H", "templates/ws-fn-ad-h-tpl.html"); //橫式廣告模板
        $tpl->assignInclude( "AD_V", "templates/ws-fn-ad-v-tpl.html"); //直式廣告模板   
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["SITEMAP"]);
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["products"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["products"]);//左方menu title
        $tpl->assignGlobal( "TAG_SITEMAP_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["sitemap"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_IMG" , $ws_array["main_img"]["sitemap"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-sitemap"); //主要顯示區域的css設定
        $main->layer_link($TPLMSG["SITEMAP"]);
        $main->header_footer("sitemap", $TPLMSG["SITEMAP"]);
        //$main->left_fix_cate_list(); //顯示產品分類
        $leftmenu = new Leftmenu_Products($tpl);
        $leftmenu->make();        
        $main->google_code(); //google analystics code , google sitemap code
    }

//網站地圖================================================================
    function sitemap_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$ws_array;
        $ext=($this->ws_seo)?".htm":".php";
        ($cms_cfg["ws_module"]["ws_aboutus"])?$tpl->newBlock( "SITEMAP_ABOUTUS" ):"";
        ($cms_cfg["ws_module"]["ws_download"])?$tpl->newBlock( "SITEMAP_DOWNLOAD" ):"";
        ($cms_cfg["ws_module"]["ws_faq"])?$tpl->newBlock( "SITEMAP_FAQ" ):"";
        ($cms_cfg["ws_module"]["ws_news"])?$tpl->newBlock( "SITEMAP_NEWS" ):"";
        ($cms_cfg["ws_module"]["ws_products"])?$tpl->newBlock( "SITEMAP_PRODUCTS" ):"";
        ($cms_cfg["ws_module"]["ws_products_application"])?$tpl->newBlock( "SITEMAP_APPLICATION" ):"";
        ($cms_cfg["ws_module"]["ws_new_product"])?$tpl->newBlock( "SITEMAP_NEW_PRODUCT" ):"";
        ($cms_cfg["ws_module"]["ws_new_product"])?$tpl->newBlock( "SITEMAP_HOT_PRODUCT" ):"";
        ($cms_cfg["ws_module"]["ws_new_product"])?$tpl->newBlock( "SITEMAP_PRO_PRODUCT" ):"";
        ($cms_cfg["ws_module"]["ws_video"])?$tpl->newBlock( "SITEMAP_VIDEO" ):"";
        ($cms_cfg["ws_module"]["ws_ebook"])?$tpl->newBlock( "SITEMAP_EBOOK" ):"";
        ($cms_cfg["ws_module"]["ws_guestbook"])?$tpl->newBlock( "SITEMAP_GUESTBOOK" ):"";
        ($cms_cfg["ws_module"]["ws_stores"])?$tpl->newBlock( "SITEMAP_STORES" ):"";
        ($cms_cfg["ws_module"]["ws_factory"])?$tpl->newBlock( "SITEMAP_FACTORY" ):"";
        ($cms_cfg["ws_module"]["ws_gallery"])?$tpl->newBlock( "SITEMAP_GALLERY" ):"";
        $tpl->assignGlobal(array("VALUE_STR_ABOUTUS" =>$TPLMSG["ABOUT_US"],
                                 "VALUE_STR_DOWNLOAD" =>$TPLMSG["DOWNLOAD"],
                                 "VALUE_STR_FAQ" =>$TPLMSG["FAQ"],
                                 "VALUE_STR_VIDEO" =>$TPLMSG["VIDEO"],
                                 "VALUE_STR_EBOOK" =>$TPLMSG["EBOOK"],
                                 "VALUE_STR_GUESTBOOK" =>$TPLMSG["GUESTBOOK"],
                                 "VALUE_STR_STORES" =>$TPLMSG["STORES"],
            
                                 "VALUE_STR_FACTORY" =>$TPLMSG["FACTORY"],
                                 "VALUE_STR_NEWS" =>$TPLMSG["NEWS"],
                                 "VALUE_STR_PRODUCTS" =>$TPLMSG["PRODUCTS"],
                                 "VALUE_STR_APPLICATION" =>$TPLMSG["APPLICATION"],
                                 "VALUE_STR_NEW_PRODUCT" =>$TPLMSG["PRODUCT_NEW"],
                                 "VALUE_STR_HOT_PRODUCT" =>$TPLMSG["PRODUCT_HOT"],
                                 "VALUE_STR_PRO_PRODUCT" =>$TPLMSG["PRODUCT_PROMOTION"],
                                 "VALUE_STR_SITEMAP" =>$TPLMSG["SITEMAP"],
                                 "VALUE_STR_GALLERY" =>$TPLMSG["GALLERY"],
                                 "VALUE_STR_CONTACTUS" =>$TPLMSG["CONTACT_US"],
                                 "VALUE_STR_HOME" =>$TPLMSG["HOME"],
                                 "VALUE_ABOUTUS_LINK" =>$cms_cfg["base_root"]."aboutus".$ext,
                                 "VALUE_DOWNLOAD_LINK" =>$cms_cfg["base_root"]."download".$ext,
                                 "VALUE_FAQ_LINK" =>$cms_cfg["base_root"]."faq".$ext,
                                 "VALUE_VIDEO_LINK" =>$cms_cfg["base_root"]."video".$ext,
                                 "VALUE_EBOOK_LINK" =>$cms_cfg["base_root"]."ebook".$ext,
                                 "VALUE_GUESTBOOK_LINK" =>$cms_cfg["base_root"]."guestbook".$ext,
                                 "VALUE_STORES_LINK" =>$cms_cfg["base_root"]."stores".$ext,
//                                 "VALUE_FACTORY_LINK" =>$cms_cfg["base_root"]."factory".$ext,
                                 "VALUE_FACTORY_LINK" =>"#",
                                 "VALUE_NEWS_LINK" =>$cms_cfg["base_root"]."news".$ext,
                                 "VALUE_PRODUCTS_LINK" =>$cms_cfg["base_root"]."products".$ext,
                                 "VALUE_APPLICATION_LINK" =>$cms_cfg["base_root"]."application".$ext,
                                 "VALUE_NEW_PRODUCT_LINK" =>$cms_cfg["base_root"]."new-products.htm",
                                 "VALUE_HOT_PRODUCT_LINK" =>$cms_cfg["base_root"]."hot-products.htm",
                                 "VALUE_PRO_PRODUCT_LINK" =>$cms_cfg["base_root"]."pro-products.htm",
                                 "VALUE_SITEMAP_LINK" =>$cms_cfg["base_root"]."sitemap".$ext,
                                 "VALUE_CONTACTUS_LINK" =>$cms_cfg["base_root"]."contactus".$ext,
                                 "VALUE_GALLERY_LINK" =>$cms_cfg["base_root"]."gallery".$ext,
        ));
        $this->aboutus_list();
        $map = $this->get_product_layer();
        $this->print_product_layer($map);
    }
    
    function get_product_layer($parent=0,$container=array()){
        global $cms_cfg;
        $db = App::getHelper("db");
        $sql = "select * from ".$db->prefix("products_cate")." where pc_status='1' and pc_parent='{$parent}' order by pc_up_sort desc,pc_sort ".$cms_cfg['sort_pos'].",pc_modifydate desc ";
        $res = $db->query($sql);
        while($cate = $db->fetch_array($res,1)){
            $item = array(
                'name' => $cate['pc_name'],
                'link' => App::getHelper('request')->get_link("productscate",$cate),
            );
            if($sub = $this->get_product_layer($cate['pc_id'])){
                $item['sub'] = $sub;
            }
            $container[] = $item;
        }
        if($cms_cfg['ws_module']['ws_sitemap_product']){
            $sql = "select p.*,pc.pc_seo_filename from ".$db->prefix("products")." as p inner join ".$db->prefix("products_cate")." as pc on p.pc_id=pc.pc_id where p_status='1' and p.pc_id='{$parent}' order by p_sort ".$cms_cfg['sort_pos'].",p_modifydate desc ";
            $res1 = $db->query($sql,true);
            $products = array();
            while($prod = $db->fetch_array($res1,1)){
                $item = array(
                    'name' => $prod['p_name'],
                    'link' => App::getHelper('request')->get_link("products",$prod),
                );
                $products[] = $item;
            }
            if($products){
                $container['products'] = $products;
            }
        }
        return $container;
    }
    //輸出產品結構
    function print_product_layer($layer,$deep=0){
        global $tpl;
        if(isset($layer['products'])){
            $products = $layer['products'];
            unset($layer['products']);
        }
        $zoneBlockName = "PRODUCTS_CATE".$deep."_ZONE";
        $listBlockName = "PRODUCTS_CATE".$deep."_LIST";
        $prodZoneBlockName = "PRODUCTS_CATE".$deep."_PROD_ZONE";
        $prodListBlockName = "PRODUCTS_CATE".$deep."_PROD_LIST";
        $tpl->newBlock($zoneBlockName);
        $tpl->assign("LAYER_ID","layer".$deep);
        if($layer){
            foreach($layer as $item){
                $tpl->newBlock($listBlockName);
                $tpl->assign(array(
                    "VALUE_PC_LINK" => $item['link'],
                    "VALUE_PC_NAME" => $item['name'],
                ));
                if($item['sub']){
                    $this->print_product_layer($item['sub'], $deep+1);
                }
            }
            
        }
        if($products){
            $tpl->newBlock($prodZoneBlockName);
            $tpl->assign(array(
                "TAG_PRODUCTS_CLASS" => 'class="sitemap-products"',                
            ));
            foreach($products as $item){
                $tpl->newBlock($prodListBlockName);
                $tpl->assign(array(
                    "VALUE_P_LINK" => $item['link'],
                    "VALUE_P_NAME" => $item['name'],
                ));
            }
        }
    }
    
    function aboutus_list(){
        global $tpl;
        $sql = "select  distinct au_cate from ".App::getHelper('db')->prefix("aboutus");
        $res = App::getHelper('db')->query($sql,1);
        while($row = App::getHelper('db')->fetch_array($res,1)){
            $tpl->newBlock("ABOUTUS_LIST");
            $tpl->assign(array(
                "TAG_NAME" => App::defaults()->main[$row['au_cate']],
                "TAG_LINK" => App::configs()->base_root . $row['au_cate'] . ".htm",
            ));
        }
    }
}
?>