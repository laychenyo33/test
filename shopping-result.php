<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$cart = new CART;
class CART{
    function CART(){
        global $db,$cms_cfg,$tpl,$main;
        $this->m_id =$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ID"];
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->contact_s_style = $cms_cfg['ws_module']['ws_contactus_s_style'];
        $this->ws_tpl_file = "templates/ws-cart-result-tpl.html";
        $this->ws_load_tp($this->ws_tpl_file);
        $tpl->newBlock("JS_MAIN");
        $tpl->assignGlobal("CURRENT_STEP3","class='current'");
        $this->cart_result();
        $this->ws_tpl_type=1;
        if($this->ws_tpl_type){
            $main->layer_link();
            $tpl->printToScreen();
        }
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$ws_array,$db,$TPLMSG,$main;
        $tpl = new TemplatePower( $cms_cfg['base_all_tpl'] );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        if($this->m_id){
            $tpl->assignInclude( "LEFT", $cms_cfg['base_left_member_tpl']); //左方首頁表單
        }else{
            $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方首頁表單
        }
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        $tpl->assignInclude( "AD_H", "templates/ws-fn-ad-h-tpl.html"); //橫式廣告模板
        $tpl->assignInclude( "AD_V", "templates/ws-fn-ad-v-tpl.html"); //直式廣告模板
        $tpl->assignInclude( "CONTACT_S", "templates/ws-fn-contact-s-style".$this->contact_s_style."-tpl.html"); //稱呼樣版      
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["products"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["products"]);//左方menu title
        $tpl->assignGlobal( "TAG_PRODUCTS_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["products"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-products"); //主要顯示區域的css設定
        $tpl->assignGlobal( "TAG_LAYER" , $TPLMSG['CART_INQUIRY']); //麵包屑
        $main->header_footer("");
        $main->google_code(); //google analystics code , google sitemap code
//        $main->left_fix_cate_list();
    }
    function cart_result(){
        global $tpl,$ali_note,$cms_cfg,$db;
        if($_GET['status']){
            $tpl->newBlock("ORDER_".strtoupper($_GET['status']));
        }
        if(empty($_REQUEST['channel_id'])){
            $sql = "select * from ".$cms_cfg['tb_prefix']."_order where o_id='".$db->quote($_GET['pno'])."'";
            $res = $db->query($sql,true);
            if($db->numRows($res)){
                $order = $db->fetch_array($res,1);
                $tpl->newBlock("ALI_TABLE");
                $tpl->assign(array( 
                    "VALUE_ALI_PNO" => $order['o_id'],
                    "VALUE_ALI_DESC" => $order['o_content'],
                    "VALUE_ALI_NTD" => $order['o_total_price'],
                    "VALUE_ALI_LINK" => $cms_cfg['base_root']."member.php?func=m_zone&mzt=order&type=detail&o_id=".$order['o_id']
                ));         
            }else{
                header("location:".$cms_cfg['base_root']);
                die();
            }
        }else{
//            $ali_note->ali_note_switch(); //支付寶note接收
        }
    }
}
?>