<?php
class Model_User_Frontend extends Model_Modules implements Model_User_Iauthenticate {
    
    function authenticate() {
        if(isset(App::getHelper('session')->MEMBER_ID)){
            return true;
        }
        $this->ws_load_tp($this->_options['login_form_template']);
    }
    
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$db,$TPLMSG,$ws_array,$main;
        $tpl = new TemplatePower( $cms_cfg['base_all_tpl'] );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        if(!empty(App::getHelper('session')->MEMBER_ID)){
            $tpl->assignInclude( "LEFT", $cms_cfg['base_left_member_tpl']); //左方會員專區表單
        }else{
            $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方一般選單
        }
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區     
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["member"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["member"]);//左方menu title
        $tpl->assignGlobal( "TAG_MEMBER_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["member"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-member"); //主要顯示區域的css設定
        $main->header_footer("");
        //定義目前語系的表單檢查JS
        $tpl->assignGlobal("TAG_LANG",$cms_cfg['language']);
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["MEMBER_ZONE"]);
        $tpl->assignGlobal( "TAG_RETURN_URL" , $_SERVER['REQUEST_URI']);
        if($_GET){
            $main->layer_link($TPLMSG["MEMBER_ZONE"],$cms_cfg['base_root']."member.php");
        }else{
            $main->layer_link($TPLMSG["MEMBER_ZONE"]);
        }
        //頁首會員登入區
        if(empty(App::getHelper('session')->MEMBER_ID)){
            $tpl->newBlock("INDEX_LOGIN_ZONE");
        }else{
            $tpl->newBlock("INDEX_LOGOUT_ZONE");
        }
        //未登入會員左選單
        if(empty(App::getHelper('session')->MEMBER_ID)){
            $leftMenu = new Leftmenu_Nonemember($tpl);
            $leftMenu->make();
        }
        $tpl->printToScreen();
    }
    
}
