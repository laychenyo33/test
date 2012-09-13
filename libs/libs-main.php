<?php
class MAINFUNC{
    //分頁
    function pagination($op_limit=10,$jp_limit=10,$nowp=1,$jp=0,$func_str,$total){
        $Page["total_records"]=$total;
        //Total Pages
        $Page["total_pages"]=($total%$op_limit)? $total/$op_limit +1 : $total/$op_limit;
        //New Sql
        $start_pages=($nowp>=1)?$nowp-1:0;
        $Page["start_serial"]=$start_pages*$op_limit;
        $ppages=floor($Page["total_pages"]/$jp_limit);
        if($jp<$ppages){
            $page_start=$jp*$jp_limit+1;
            $page_end=$page_start+$jp_limit;
        }else{
            $page_start=$jp*$jp_limit+1;
            $page_end=$page_start+$Page["total_pages"]%$jp_limit;
        }
        //沒有上跳頁也沒有下跳頁
        if($ppages <= 1 && $Page["total_pages"]<$jp_limit+1){
            $Page["bj_page"]="";
            $Page["nj_page"]="";
            $page_start=1;
            $page_end=($total%$op_limit)?$Page["total_pages"] : $Page["total_pages"]+1;
        }else{
            //有上跳頁沒有下跳頁
            if($jp>= $ppages){ //最後下跳頁
                $bp=$jp-1;
                $prev=$page_start-1;
                $Page["bj_page"]=$func_str."&nowp=".$prev."&jp=".$bp;
                $Page["nj_page"]="";
            }
            //有上跳頁也有下跳頁
            if($jp < $ppages && $jp!=0){
                $bp=$jp-1;
                $np=$jp+1;
                $prev=$page_start-1;
                $Page["bj_page"]=$func_str."&nowp=".$prev."&jp=".$bp;
                $Page["nj_page"]=$func_str."&nowp=".$page_end."&jp=".$np;
            }
            //沒有上跳頁有下跳頁
            if($jp ==0){//第1頁
                $np=$jp+1;
                $Page["bj_page"]="";
                $Page["nj_page"]=$func_str."&nowp=".$page_end."&jp=".$np;
            }
        }
        //分頁選單PAGE_OPTION
        $nowp_option=array();
        for($i=$page_start;$i<$page_end;$i++){
            //$line1=($i==floor($page_end))?"":" | ";
            $nowp_option[] = ($i==$nowp || ($i==$page_start && $nowp==0))?"<span class='current'>".$i."</span>" : "<a href=\"".$func_str."&nowp=".$i."&jp=".$jp."\"> ".$i." </a>";
        }
        if($Page["total_pages"]>=2){
            $page_option=implode("&nbsp;|&nbsp;",$nowp_option);
        }else{
            $page_option="";
        }
        $Page["pages_str"]=$page_option;
        $Page["total_pages"]=floor($Page["total_pages"]);
        return $Page;
    }
    //SEO rewrite分頁
    function pagination_rewrite($op_limit=10,$jp_limit=10,$nowp=1,$jp=0,$func_str,$total){
        $nowp=($nowp)?$nowp:0;
        $jp=($jp)?$jp:0;
        $Page["total_records"]=$total;
        //Total Pages
        $Page["total_pages"]=($total%$op_limit)? $total/$op_limit +1 : $total/$op_limit;
        //New Sql
        $start_pages=($nowp>=1)?$nowp-1:0;
        $Page["start_serial"]=$start_pages*$op_limit;
        $ppages=floor($Page["total_pages"]/$jp_limit);
        if($jp<$ppages){
            $page_start=$jp*$jp_limit+1;
            $page_end=$page_start+$jp_limit;
        }else{
            $page_start=$jp*$jp_limit+1;
            $page_end=$page_start+$Page["total_pages"]%$jp_limit;
        }
        //沒有上跳頁也沒有下跳頁
        if($ppages <= 1 && $Page["total_pages"]<$jp_limit+1){
            $Page["bj_page"]="";
            $Page["nj_page"]="";
            $page_start=1;
            $page_end=($total%$op_limit)?$Page["total_pages"] : $Page["total_pages"]+1;
        }else{
            //有上跳頁沒有下跳頁
            if($jp>= $ppages){ //最後下跳頁
                $bp=$jp-1;
                $prev=$page_start-1;
                $Page["bj_page"]=$func_str."-pages-".$prev."-".$bp.".htm";
                $Page["nj_page"]="";
            }
            //有上跳頁也有下跳頁
            if($jp < $ppages && $jp!=0){
                $bp=$jp-1;
                $np=$jp+1;
                $prev=$page_start-1;
                $Page["bj_page"]=$func_str."-pages-".$prev."-".$bp.".htm";
                $Page["nj_page"]=$func_str."-pages-".$page_end."-".$np.".htm";
            }
            //沒有上跳頁有下跳頁
            if($jp ==0){//第1頁
                $np=$jp+1;
                $Page["bj_page"]="";
                $Page["nj_page"]=$func_str."-pages-".$page_end."-".$np.".htm";
            }
        }
        //分頁選單PAGE_OPTION
        $nowp_option=array();
        for($i=$page_start;$i<$page_end;$i++){
            //$line1=($i==floor($page_end))?"":" | ";
            if($i==$nowp || ($i==$page_start && $nowp==0)){
                $nowp_option[] = "<span class='current'>P".$i."</span>";
            }else{
                if($i==1){
                    $nowp_option[] = "<a href=\"".$func_str.".htm\"> P".$i." </a>";
                }else{
                    $nowp_option[] = "<a href=\"".$func_str."-pages-".$i."-".$jp.".htm\"> P".$i." </a>";
                }
            }
        }
        if($Page["total_pages"]>=2){
            $page_option=implode("&nbsp;|&nbsp;",$nowp_option);
        }else{
            $page_option="";
        }
        $Page["pages_str"]=$page_option;
        $Page["total_pages"]=floor($Page["total_pages"]);
        return $Page;
    }
    function sqlstr_add_limit($op_limit=10,$nowp=1,$sql){
        $p=($nowp>=1)?$nowp-1:0;
        $start=$p*$op_limit;
        if(!empty($sql)){
            $sql .= " limit ".$start.",".$op_limit;
        }
        return $sql;
    }

    function count_total_records($sql){
        global $db;
        if(!empty($sql)){
            $sql = str_replace("*","count(*) as total_records",$sql);
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
        }
        return $row["total_records"];
    }
    function load_js_msg(){
        global $tpl,$TPLMSG;
        $tpl->assignGlobal(array("JSMSG_PLEASE_INPUT" => $TPLMSG['JSMSG_PLEASE_INPUT'],
                                 "JSMSG_SUBJECT" => $TPLMSG['SUBJECT'],
                                 "JSMSG_ACCOUNT" => $TPLMSG['LOGIN_ACCOUNT'],
                                 "JSMSG_PASSWORD" => $TPLMSG['LOGIN_PASSWORD'],
                                 "JSMSG_NAME" => $TPLMSG['MEMBER_NAME'],
                                 "JSMSG_ADDRESS" => $TPLMSG['ADDRRESS'],
                                 "JSMSG_BIRTHDAY" => $TPLMSG['BIRTHDAY'],
                                 "JSMSG_TEL" => $TPLMSG['TEL'],
                                 "JSMSG_PASSWORD_ERROR" => $TPLMSG['JSMSG_VALID_PASSWORD_ERROR'],

        ));
    }
    //登入專區
    function login_zone(){
        global $tpl,$cms_cfg,$TPLMSG;
        if(empty($_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID'])){
            $tpl->newBlock( "LOGIN_ZONE" );
            $tpl->assignGlobal( "MSG_ERROR_MESSAGE",$_SESSION[$cms_cfg['sess_cookie_name']]["ERROR_MSG"]);
            $_SESSION[$cms_cfg['sess_cookie_name']]["ERROR_MSG"]=""; //清空錯誤訊息
            $tpl->assignGlobal( "MSG_LOGIN_ACCOUNT",$TPLMSG["LOGIN_ACCOUNT"]);
            $tpl->assignGlobal( "MSG_LOGIN_PASSWORD",$TPLMSG["LOGIN_PASSWORD"]);
            $tpl->assignGlobal( "MSG_LOGIN_BUTTON",$TPLMSG["LOGIN_BUTTON"]);
            $tpl->assignGlobal( "MSG_LOGIN_FORGOT_PASSWORD",$TPLMSG["LOGIN_FORGOT_PASSWORD"]);
            $tpl->assignGlobal( "MSG_LOGIN_REGISTER",$TPLMSG["LOGIN_REGISTER"]);
            //載入驗証碼
            $this->security_zone($cms_cfg['security_image_width'],$cms_cfg['security_image_height']);
        }else{
            $tpl->newBlock( "MEMBER_INFO" );
            $tpl->assign("TAG_LOGIN_MEMBER_CATE",$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_CATE']);
            $tpl->assign("TAG_LOGIN_MEMBER_NAME",$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_NAME']);
            $tpl->assign("TAG_LOGIN_MEMBER_DATA",$TPLMSG['MEMBER_ZONE_DATA']);
            $tpl->assign("TAG_LOGIN_MEMBER_ORDER",$TPLMSG['MEMBER_ZONE_ORDER']);
            $tpl->assign("TAG_LOGIN_MEMBER_INQUIRY",$TPLMSG['MEMBER_ZONE_INQUIRY']);
            $tpl->assign("TAG_LOGIN_MEMBER_CONTACTUS",$TPLMSG['MEMBER_ZONE_CONTACTUS']);
        }
    }
    function security_zone($si_w="90", $si_h="25"){
        global $tpl,$cms_cfg,$TPLMSG;
        if($cms_cfg["ws_module"]["ws_security"]==1){
            //驗証碼
            require_once("libs-security-image.php");
            $si = new securityImage();
            $si->setImageSize($si_w, $si_h);
            $tpl->assignGlobal( "MSG_LOGIN_SECURITY",$TPLMSG["LOGIN_SECURITY"]);
            $tpl->assignGlobal( "TAG_INPUT_SECURITY",$si->showFormInput());
            $tpl->assignGlobal( "TAG_IMAGE_SECURITY_IMAGE",$si->showFormImage());
        }
    }
    //頭尾檔設定
    function header_footer($meta_array,$seo_h1=""){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($cms_cfg["ws_module"]["ws_seo"] ==0 ){
            unset($meta_array);
            // IPB META SETUP
            $sql ="select sc_meta_title,sc_meta_keyword,sc_meta_description from ".$cms_cfg['tb_prefix']."_system_config where sc_status='1' and sc_id='1'";
            $selectrs = $db->query($sql);
            $rsnum = $db->numRows($selectrs);
            if($rsnum > 0) {
                $row = $db->fetch_array($selectrs,1);
                $tpl->assignGlobal(array(
                        "HEADER_META_TITLE" => $row["sc_meta_title"],
                        "HEADER_META_KEYWORD" => $row["sc_meta_keyword"],
                        "HEADER_META_DESCRIPTION" => $row["sc_meta_description"],
                        "TAG_MAIN_FUNC" => $seo_h1,
                ));
            }
        }else{
            //各項功能主頁專屬的seo 設定
            if(!is_array($meta_array)){
                //頭檔
                $meta_array=$this->func_metatitle($meta_array);
            }
            $tpl->assignGlobal(array("TAG_BASE_CSS" => $cms_cfg['base_css'],
                                     "HEADER_META_TITLE" => ($meta_array["meta_title"])?$meta_array["meta_title"]:$_SESSION[$cms_cfg['sess_cookie_name']]["sc_meta_title"],
                                     "HEADER_META_KEYWORD" => ($meta_array["meta_keyword"])?$meta_array["meta_keyword"]:$_SESSION[$cms_cfg['sess_cookie_name']]["sc_meta_keyword"],
                                     "HEADER_META_DESCRIPTION" => ($meta_array["meta_description"])?$meta_array["meta_description"]:$_SESSION[$cms_cfg['sess_cookie_name']]["sc_meta_description"],
                                     "HEADER_SHORT_DESC" => ($meta_array["seo_short_desc"])?$meta_array["seo_short_desc"]:"",
                                     "TAG_MAIN_FUNC" => ($meta_array["seo_h1"])?$meta_array["seo_h1"]:$seo_h1,
            ));
            $tpl->newBlock("SEO_SHORT_DESC");
            $tpl->assign("VALUE_SEO_SHORT_DESC",$meta_array["seo_short_desc"]);
        }
        if($_SESSION[$cms_cfg['sess_cookie_name']]["sc_im_status"]==1 && $_SESSION[$cms_cfg['sess_cookie_name']]["sc_im_starttime"] < date("H:i:s") && $_SESSION[$cms_cfg['sess_cookie_name']]["sc_im_endtime"] > date("H:i:s")){
            $tpl->newBlock( "IM_ZONE" );
            $tpl->assign(array("VALUE_SC_IM_SKYPE" =>"skype:<a href=\"callto:".$_SESSION[$cms_cfg['sess_cookie_name']]["sc_im_skype"]."\"><img src=\"".$cms_cfg['base_images']."skype_call_me.png\" alt=\"Skype Me™!\" border='0' width='70' height='23'/></a>",
                               "VALUE_SC_IM_MSN" =>"msn:".$_SESSION[$cms_cfg['sess_cookie_name']]["sc_im_msn"],
            ));
        }
        $tpl->assignGlobal("MSG_HOME",$TPLMSG['HOME']);
        $tpl->assignGlobal("TAG_THEME_PATH" , $cms_cfg['default_theme']);
        $tpl->assignGlobal("TAG_ROOT_PATH" , $cms_cfg['base_root']);
        $tpl->assignGlobal("TAG_FILE_ROOT" , $cms_cfg['file_root']);
        $tpl->assignGlobal("TAG_BASE_URL" ,$cms_cfg["base_url"]);
        $tpl->assignGlobal("TAG_LANG",$cms_cfg['language']);
        $tpl->assignGlobal("MSG_SITEMAP",$TPLMSG["SITEMAP"]);
        //設定主選單變數
        $tpl->assignGlobal("TAG_MENU_ABOUTUS",$TPLMSG['INTRODUCTION']);
        $tpl->assignGlobal("TAG_MENU_PRODUCTS",$TPLMSG['PRODUCTS']);
        $tpl->assignGlobal("TAG_MENU_NEWS",$TPLMSG['NEWS']);
        $tpl->assignGlobal("TAG_MENU_QUALITY",$TPLMSG['QUALITY'] );
        $tpl->assignGlobal("TAG_MENU_EQUIPMENT",$TPLMSG['EQUIPMENT']);
        $tpl->assignGlobal("TAG_MENU_CONTACTUS",$TPLMSG['CONTACT_US']);
        //設定頁腳變數
        $tpl->assignGlobal("TAG_FOOTER_ADDRESS",$TPLMSG['CUS_ADDRESS']);
        $tpl->assignGlobal("TAG_FOOTER_FAX",$TPLMSG['CUS_FAX']);
        $tpl->assignGlobal("TAG_FOOTER_TEL",$TPLMSG['CUS_TEL']);
        $tpl->assignGlobal("TAG_FOOTER_EMAIL",$TPLMSG['CUS_EMAIL']);
        //有會員即顯示會員登入區
        if($cms_cfg["ws_module"]["ws_member"]==1){
            $this->login_zone();
        }
        $this->mouse_disable(); //鎖滑鼠右鍵功能
        //$this->float_menu();
        //$this->goodlink_select();
        //尾檔
        //$tpl->assignGlobal("VALUE_SC_FOOTER" ,$_SESSION[$cms_cfg['sess_cookie_name']]["sc_footer"]);
    }
    function func_metatitle($func){
        global $db,$cms_cfg;
        $sql="select * from ".$cms_cfg['tb_prefix']."_metatitle where mt_name='".$func."'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $meta_array=array();
        if($rsnum >0){
            $row = $db->fetch_array($selectrs,1);
            $meta_array["meta_title"]=$row["mt_seo_title"];
            $meta_array["meta_keyword"]=$row["mt_seo_keyword"];
            $meta_array["meta_description"]=$row["mt_seo_description"];
            $meta_array["seo_short_desc"]=$row["mt_seo_short_desc"];
            $meta_array["seo_h1"]=$row["mt_seo_h1"];
        }
        return $meta_array;
    }
    //固定顯示主分類及次分類的左方menu
    function left_fix_cate_list(){
        global $tpl,$db,$main,$cms_cfg,$TPLMSG;
        $tpl->assignGlobal("LEFT_CATE_TITLE_IMG",$cms_cfg['base_images']."left-title-products.png");
        //判斷是否顯示主分類
        if($cms_cfg["ws_module"]["ws_left_main_pc"]==1) {
            $sql="select * from ".$cms_cfg['tb_prefix']."_products_cate where pc_parent='0' and pc_status='1' order by pc_up_sort desc,pc_sort desc";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
        }else{
            $rsnum = 0;
        }
        if($rsnum > 0 ){ //有主分類
            //有次分類或主分類產品
            if($cms_cfg["ws_module"]["ws_left_menu_effects"]==1) {
                $tpl->newBlock("JS_LEFT_MENU");
            }
            $i=0;
            while($row = $db->fetch_array($selectrs,1)){
                if($cms_cfg['ws_module']['ws_seo']==1){
                    if(trim($row["pc_seo_filename"]) !=""){
                        $dirname1=$row["pc_seo_filename"];
                        $pc_link=$cms_cfg["base_root"].$row["pc_seo_filename"].".htm";
                    }else{
                        $dirname1=$row["pc_id"];
                        $pc_link=$cms_cfg["base_root"]."category-".$row["pc_id"].".htm";
                    }
                }else{
                    $pc_link=$cms_cfg["base_root"]."products.php?func=p_list&pc_parent=".$row["pc_id"];
                }
                $tpl->newBlock( "LEFT_CATE_LIST" );
                $tpl->assign( array( "VALUE_CATE_NAME" => $row["pc_name"],
                                     "VALUE_CATE_LINK"  => $pc_link,
                                     "VALUE_CATE_LINK_CLASS" => ($_REQUEST['pc_parent']==$row['pc_id']?"current":""),
                ));
                //左方產品次分類為click menu
                if($cms_cfg['ws_module']['ws_seo']==1){
                    if($_REQUEST["f"]!="") {
                        if($cms_cfg["ws_module"]["ws_left_menu_type"]==1 && $row["pc_seo_filename"]==$_REQUEST["f"]) {
                            $tpl->assignGlobal("CLICK_NUM1", $i);
                        }
                    }else{
                        if($cms_cfg["ws_module"]["ws_left_menu_type"]==1 && $row["pc_id"]==$_REQUEST["pc_parent"]) {
                            $tpl->assignGlobal("CLICK_NUM1", $i);
                        }
                    }
                }else{
                    if($cms_cfg["ws_module"]["ws_left_menu_type"]==1 && $row["pc_id"]==$_REQUEST["pc_parent"]) {
                        $tpl->assignGlobal("CLICK_NUM1", $i);
                    }
                }
                //判斷是否顯示次分類
                if($cms_cfg["ws_module"]["ws_left_sub_pc"]==1){
                    $sql1="select * from ".$cms_cfg['tb_prefix']."_products_cate where pc_parent='".$row["pc_id"]."' and pc_status='1' order by pc_up_sort desc,pc_sort desc ";
                    $selectrs1 = $db->query($sql1);
                    $rsnum1    = $db->numRows($selectrs1);
                }else{
                    $rsnum1 = 0;
                }
                if($rsnum1 > 0 ){ //有次分類
                    if($cms_cfg["ws_module"]["ws_left_menu_type"]==1) {
                        $tpl->assignGlobal("TAG_LEFT_MENU_TYPE", "id=\"firstpane\""); //click menu
                    }else{
                        $tpl->assignGlobal("TAG_LEFT_MENU_TYPE", "id=\"secondpane\""); //over menu
                    }
                    while($row1 = $db->fetch_array($selectrs1,1)){
                        if($cms_cfg['ws_module']['ws_seo']==1){
                            if(trim($row1["pc_seo_filename"]) !=""){
                                $dirname1=$row1["pc_seo_filename"];
                                $pc_link1=$cms_cfg["base_root"].$row1["pc_seo_filename"].".htm";
                            }else{
                                $dirname1=$row1["pc_id"];
                                $pc_link1=$cms_cfg["base_root"]."category-".$row1["pc_id"].".htm";
                            }
                        }else{
                            $pc_link1=$cms_cfg["base_root"]."products.php?func=p_list&pc_parent=".$row1["pc_id"];
                        }
                        $tpl->newBlock("LEFT_SUBCATE_LIST");
                        $tpl->assign( array( "VALUE_SUBCATE_NAME" => $row1["pc_name"],
                                             "VALUE_SUBCATE_LINK"  => $pc_link1,
                        ));
                        //左方產品次分類為click menu
                        if($cms_cfg['ws_module']['ws_seo']==1){
                            if($_REQUEST["f"]!="") {
                                if($cms_cfg["ws_module"]["ws_left_menu_type"]==1 && $row1["pc_seo_filename"]==$_REQUEST["f"]) {
                                    $tpl->assignGlobal("CLICK_NUM1", $i);
                                }
                            }else{
                                if($cms_cfg["ws_module"]["ws_left_menu_type"]==1 && $row1["pc_id"]==$_REQUEST["pc_parent"]) {
                                    $tpl->assignGlobal("CLICK_NUM1", $i);
                                }
                            }
                        }else{
                            if($cms_cfg["ws_module"]["ws_left_menu_type"]==1 && $row1["pc_id"]==$_REQUEST["pc_parent"]) {
                                $tpl->assignGlobal("CLICK_NUM1", $i);
                            }
                        }
                    }
                    $tpl->gotoBlock("LEFT_CATE_LIST");
                    $tpl->assign("TAG_SUB_UL1","<div class=\"menu_body\"><ul>");
                    $tpl->assign("TAG_SUB_UL2","</ul></div>");
                    $tpl->assign("VALUE_CATE_LINK_CLASS" ,$_REQUEST['pc_parent']==$row['pc_id']?"current":"");
                }else{ //無次分類
                    //判斷是否顯示次分類的產品
                    if($cms_cfg["ws_module"]["ws_left_products"]==1){
                        $sql2="select * from ".$cms_cfg['tb_prefix']."_products where pc_id='".$row["pc_id"]."' and p_status='1' order by p_up_sort desc,p_sort desc";
                        $selectrs2 = $db->query($sql2);
                        $rsnum2    = $db->numRows($selectrs2);
                    }else{
                        $rsnum2 = 0;
                    }
                    if($rsnum2 > 0 ){ //有次分類產品
                        if($cms_cfg["ws_module"]["ws_left_menu_type"]==1) {
                            $tpl->assignGlobal("TAG_LEFT_MENU_TYPE", "id=\"firstpane\""); //click menu
                        }else{
                            $tpl->assignGlobal("TAG_LEFT_MENU_TYPE", "id=\"secondpane\""); //over menu
                        }
                        while($row2 = $db->fetch_array($selectrs2,1)){
                            if($cms_cfg['ws_module']['ws_seo']==1){
                                if(trim($row2["p_seo_filename"]) !=""){
                                    $p_link=$cms_cfg["base_root"].$dirname1."/".$row2["p_seo_filename"].".html";
                                }else{
                                    $p_link=$cms_cfg["base_root"].$dirname1."/products-".$row2["p_id"]."-".$row2["pc_id"].".html";
                                }
                            }else{
                                $p_link=$cms_cfg["base_root"]."products.php?func=p_detail&p_id=".$row2["p_id"]."&pc_parent=".$row2["pc_id"];
                            }
                            $tpl->newBlock( "LEFT_SUBCATE_LIST" );
                            $tpl->assign( array( "VALUE_SUBCATE_NAME" => $row2["p_name"],
                                                 "VALUE_SUBCATE_LINK"  => $p_link,
                            ));
                            //左方產品次分類為click menu
                            if($cms_cfg['ws_module']['ws_seo']==1){
                                if($_REQUEST["f"]!="") {
                                    if($cms_cfg["ws_module"]["ws_left_menu_type"]==1 && $row2["p_seo_filename"]==$_REQUEST["f"]) {
                                        $tpl->assignGlobal("CLICK_NUM1", $i);
                                    }
                                }else{
                                    if($cms_cfg["ws_module"]["ws_left_menu_type"]==1 && $row2["pc_id"]==$_REQUEST["pc_parent"]) {
                                        $tpl->assignGlobal("CLICK_NUM1", $i);
                                    }
                                }
                            }else{
                                if($cms_cfg["ws_module"]["ws_left_menu_type"]==1 && $row2["pc_id"]==$_REQUEST["pc_parent"]) {
                                    $tpl->assignGlobal("CLICK_NUM1", $i);
                                }
                            }
                        }
                        $tpl->gotoBlock("LEFT_CATE_LIST");
                        $tpl->assign("TAG_SUB_UL1","<div class=\"menu_body\"><ul>");
                        $tpl->assign("TAG_SUB_UL2","</ul></div>");
                        $tpl->assign("VALUE_CATE_LINK_CLASS" ,$_REQUEST['pc_parent']==$row2['pc_id']?"current":"");
                    }
                }
                $i++;
            }
        }else{//無主分類,顯示未分類產品
            if($cms_cfg["ws_module"]["ws_left_products"]==1){
                $sql3="select * from ".$cms_cfg['tb_prefix']."_products where pc_id='0' and p_status='1' order by p_up_sort desc,p_sort desc";
                $selectrs3 = $db->query($sql3);
                $rsnum3    = $db->numRows($selectrs3);
            }else{
                $rsnum3 = 0;
            }
            if($rsnum3 > 0 ){
                //顯示左方次分類
                while($row3 = $db->fetch_array($selectrs3,1)){
                    if($cms_cfg['ws_module']['ws_seo']==1){
                        if(trim($row3["p_seo_filename"]) !=""){
                            $p_link=$cms_cfg["base_root"]."products/".$row3["p_seo_filename"].".html"; //未分類產品資料夾預設為products
                        }else{
                            $p_link=$cms_cfg["base_root"]."products/products-".$row3["p_id"]."-".$row3["pc_id"].".html";//未分類產品資料夾預設為products
                        }
                    }else{
                        $p_link=$cms_cfg["base_root"]."products.php?func=p_detail&p_id=".$row3["p_id"]."&pc_parent=".$row3["pc_id"];
                    }
                    $tpl->newBlock("LEFT_PRODUCTS_LIST");
                    $tpl->assign( array( "VALUE_PRODUCTS_NAME" => $row3["p_name"],
                                         "VALUE_PRODUCTS_LINK"  => $p_link,
                                         "VALUE_CATE_LINK_CLASS" => ($_REQUEST['pc_parent']==$row3['pc_id']?"current":""),
                    ));
                }
            }
        }
    }
    //後台管理權限
    function mamage_authority(){
        global $tpl,$ws_array,$cms_cfg;
        $tpl->assignGlobal(array("TAG_LANG_VERSION" => $ws_array["lang_version"][$cms_cfg['language']],
                                 "TAG_USER_NAME"   => $_SESSION[$cms_cfg['sess_cookie_name']]["USER_NAME"],
                                 "TAG_USER_ACCOUNT"   => $_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]
                          ));
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_aboutus"] && $cms_cfg["ws_module"]["ws_aboutus"])?$tpl->newBlock( "AUTHORITY_ABOUTUS" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_ad"]  && $cms_cfg["ws_module"]["ws_ad"])?$tpl->newBlock( "AUTHORITY_AD" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_admin"])?$tpl->newBlock( "AUTHORITY_ADMIN" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_blog"] && $cms_cfg["ws_module"]["ws_blog"])?$tpl->newBlock( "AUTHORITY_BLOG" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_bonus"] && $cms_cfg["ws_module"]["ws_bonus"])?$tpl->newBlock( "AUTHORITY_BONUS" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_contactus"] && $cms_cfg["ws_module"]["ws_contactus"])?$tpl->newBlock( "AUTHORITY_CONTACTUS" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_download"] && $cms_cfg["ws_module"]["ws_download"])?$tpl->newBlock( "AUTHORITY_DOWNLOAD" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_ebook"] && $cms_cfg["ws_module"]["ws_ebook"])?$tpl->newBlock( "AUTHORITY_EBOOK" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_epaper"] && $cms_cfg["ws_module"]["ws_epaper"])?$tpl->newBlock( "AUTHORITY_EPAPER" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_faq"] && $cms_cfg["ws_module"]["ws_faq"])?$tpl->newBlock( "AUTHORITY_FAQ" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_forum"] && $cms_cfg["ws_module"]["ws_forum"])?$tpl->newBlock( "AUTHORITY_FORUM" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_gallery"] && $cms_cfg["ws_module"]["ws_gallery"])?$tpl->newBlock( "AUTHORITY_GALLERY" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_goodlink"] && $cms_cfg["ws_module"]["ws_goodlink"])?$tpl->newBlock( "AUTHORITY_GOODLINK" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_guestbook"] && $cms_cfg["ws_module"]["ws_guestbook"])?$tpl->newBlock( "AUTHORITY_GUESTBOOK" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_inquiry"] && $cms_cfg["ws_module"]["ws_inquiry"])?$tpl->newBlock( "AUTHORITY_INQUIRY" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_member"] && $cms_cfg["ws_module"]["ws_member"])?$tpl->newBlock( "AUTHORITY_MEMBER" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_news"] && $cms_cfg["ws_module"]["ws_news"])?$tpl->newBlock( "AUTHORITY_NEWS" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_order"] && $cms_cfg["ws_module"]["ws_order"])?$tpl->newBlock( "AUTHORITY_ORDER" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_products"] && $cms_cfg["ws_module"]["ws_products"])?$tpl->newBlock( "AUTHORITY_PRODUCTS" ):"";
        if($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_products_cate"] && $cms_cfg["ws_module"]["ws_products"]){
            $tpl->newBlock("AUTHORITY_PRODUCTS_CATE");
            $tpl->gotoBlock( "AUTHORITY_PRODUCTS" );
        }
        if($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_products"] && $cms_cfg["ws_module"]["ws_new_product"]){
            $tpl->newBlock("AUTHORITY_NEW_PRODUCTS");
            $tpl->gotoBlock( "AUTHORITY_PRODUCTS" );
        }
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_systool"] && $cms_cfg["ws_module"]["ws_systool"])?$tpl->newBlock( "AUTHORITY_SYSTOOL" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_sysconfig"]  && $cms_cfg["ws_module"]["ws_sysconfig"])?$tpl->newBlock( "AUTHORITY_SYSCONFIG" ):"";
        ($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_seo"] && $cms_cfg["ws_module"]["ws_seo"])?$tpl->newBlock( "AUTHORITY_SEO" ):"";
        if($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_seo"] && $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_google_sitemap"] && $cms_cfg["ws_module"]["ws_seo"]){
            $tpl->newBlock( "AUTHORITY_GOOGLE_SITEMAP" );
            $tpl->gotoBlock( "AUTHORITY_SEO" );
        }
        if($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_seo"] && $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_google_analytics"] && $cms_cfg["ws_module"]["ws_seo"]){
            $tpl->newBlock( "AUTHORITY_GOOGLE_ANALYTICS" );
            $tpl->gotoBlock( "AUTHORITY_SEO" );
        }
        ($cms_cfg["ws_module"]["ws_statistic"])?$tpl->newBlock( "AUTHORITY_STATISTIC" ):"";
        ($cms_cfg["ws_module"]["ws_service"])?$tpl->newBlock( "AUTHORITY_SERVICE" ):"";
        $tpl->assignGlobal("TAG_ROOT_PATH" , $cms_cfg['base_root']);
        $tpl->assignGlobal("TAG_FILE_ROOT" , $cms_cfg['file_root']);
    }
    //取得分類層次列====================================================================
    function get_layer($tablename,$show_fieldname,$id_str,$id,$func_str,$last_link=0){
        global $db;
        $id_parent=$id_str."_parent";
        $id_fieldname=$id_str."_id";
        $k=1;
        $j=0;
        $Layer=array();
        while($k==1){
            $sql="select ".$show_fieldname." , ".$id_parent." , ".$id_fieldname." from ".$tablename." where ".$id_fieldname."='".$id."'";
            $selrs = $db->query($sql);
            $row = $db->fetch_array($selrs,1);
            $id =  $row[$id_parent];
            if($row[$id_fieldname]==""){
                $k=0;
            }else{
                if($j==0 && $last_link==0){
                    $Layer[$j] =$row[$show_fieldname];
                }elseif($last_link==2){
                    $Layer[$j] =$row[$show_fieldname];
                }else{
                    $Layer[$j] = "<a href=\"".$func_str."&".$id_parent."=".$row[$id_fieldname]."\">".$row[$show_fieldname]."</a>";
                }
            }
            unset($row);
            $j++;
        }
        //陣列反轉
        if(!empty($Layer)){
            $Layer=array_reverse($Layer);
        }
        //$Layer=$this->replace_for_mod_rewrite($Layer);
        return $Layer;
    }
    //取得分類層次列====================================================================
    function get_layer_rewrite($tablename,$show_fieldname,$id_str,$id,$func_str,$last_link=0){
        global $cms_cfg,$db;
        $id_parent=$id_str."_parent";
        $id_fieldname=$id_str."_id";
        $k=1;
        $j=0;
        $Layer=array();
        while($k==1){
            $sql="select ".$show_fieldname." ,pc_seo_filename   , ".$id_parent." , ".$id_fieldname." from ".$tablename." where ".$id_fieldname."='".$id."'";
            $selrs = $db->query($sql);
            $row = $db->fetch_array($selrs,1);
            $id =  $row[$id_parent];
            if($row[$id_fieldname]==""){
                $k=0;
            }else{
                if($j==0 && $last_link==0){
                    $Layer[$j] = "<a href='javascript:avoid(0)'>".$row[$show_fieldname]."</a>";
                }elseif($last_link==2){
                    $Layer[$j] ="<a href='javascript:avoid(0)'>".$row[$show_fieldname]."</a>";
                }else{
                    if($cms_cfg["ws_module"]["ws_seo"]==1){
                        if(trim($row["pc_seo_filename"]) !=""){
                            $pc_link=$cms_cfg["base_root"].$row["pc_seo_filename"].".htm";
                        }else{
                            $pc_link=$cms_cfg["base_root"]."category-".$row["pc_id"].".htm";
                        }
                    }else{
                        $pc_link=$cms_cfg["base_root"]."products.php?func=p_list&pc_parent=".$row["pc_id"];
                    }
                    $Layer[$j] = "<a href=\"".$pc_link."\">".$row[$show_fieldname]."</a>";
                }
            }
            unset($row);
            $j++;
        }
        //陣列反轉
        if(!empty($Layer)){
            $Layer=array_reverse($Layer);
        }
        //$Layer=$this->replace_for_mod_rewrite($Layer);
        return $Layer;
    }
    //寄送確認信,電子報
    function ws_mail_send($from,$to,$mail_content,$mail_subject,$mail_type,$goto_url){
        global $TPLMSG,$cms_cfg;
        if($mail_type =="epaper"){
            set_time_limit(0);
        }
        $from_email=explode(",",$from);
        $from_name=(trim($_SESSION[$cms_cfg['sess_cookie_name']]["sc_company"]))?$_SESSION[$cms_cfg['sess_cookie_name']]["sc_company"]:$from_email[0];
        $mail_subject = "=?UTF-8?B?".base64_encode($mail_subject)."?=";
        //寄給送信者
        $MAIL_HEADER   = "MIME-Version: 1.0\n";
        $MAIL_HEADER  .= "Content-Type: text/html; charset=\"utf-8\"\n";
        $MAIL_HEADER  .= "From: =?UTF-8?B?".base64_encode($from_name)."?= <".$from_email[0].">"."\n";
        $MAIL_HEADER  .= "Reply-To: ".$from_email[0]."\n";
        $MAIL_HEADER  .= "Return-Path: ".$from_email[0]."\n";    // these two to set reply address
        $MAIL_HEADER  .= "X-Priority: 1\n";
        $MAIL_HEADER  .= "Message-ID: <".time()."-".$from_email[0].">\n";
        $MAIL_HEADER  .= "X-Mailer: PHP v".phpversion()."\n";          // These two to help avoid spam-filters
        $to_email = explode(",",$to);
        for($i=0;$i<count($to_email);$i++){
            if($i!=0 && $i%2==0){
                sleep(2);
            }
            if($i!=0 && $i%5==0){
                sleep(10);
            }
            if($i!=0 && $i%60==0){
                sleep(300);
            }
            if($i!=0 && $i%600==0){
                sleep(2000);
            }
            if($i!=0 && $i%1000==0){
                sleep(10000);
            }
            @mail($to_email[$i], $mail_subject, $mail_content,$MAIL_HEADER);
        }
        //除了電子報、忘記密碼外寄給管理者
        if($mail_type !="epaper" && $mail_type!="pw"){
            $MAIL_HEADER   = "MIME-Version: 1.0\n";
            $MAIL_HEADER  .= "Content-Type: text/html; charset=\"utf-8\"\n";
            $MAIL_HEADER  .= "From: =?UTF-8?B?".base64_encode($to_email[0])."?= <".$to_email[0].">"."\n";
            $MAIL_HEADER  .= "Reply-To: ".$to_email[0]."\n";
            $MAIL_HEADER  .= "Return-Path: ".$to_email[0]."\n";    // these two to set reply address
            $MAIL_HEADER  .= "X-Priority: 1\n";
            $MAIL_HEADER  .= "Message-ID: <".time()."-".$to_email[0].">\n";
            $MAIL_HEADER  .= "X-Mailer: PHP v".phpversion()."\n";          // These two to help avoid spam-filters
            $mail_subject .= " from ".$_SERVER["HTTP_HOST"]."--[For Administrator]";
            for($i=0;$i<count($from_email);$i++){
                @mail($from_email[$i], $mail_subject, $mail_content,$MAIL_HEADER);
            }
        }
        $goto_url=(empty($goto_url))?$cms_cfg["base_url"]:$goto_url;
        echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
        echo "<script language=javascript>";
        echo "Javascript:alert('".$TPLMSG['ACTION_TERM_JS']."')";
        echo "</script>";
        echo "<script language=javascript>";
        echo "document.location='".$goto_url."'";
        echo "</script>";
    }
    function CreateLayer($id,$relative,$top,$left,$width,$height,$css,$bgColor,$bgImage,$visible,$zIndex,$html,$events){
        $src = "";
        $src.="<div ";
        $src.=$this->Param("id",$id,"=","\""," ");
        $src.=$this->Param("class",$css,"=","\""," ");
        $style="";
        $style.=$this->Param("position",$relative?"relative":"absolute",":","",";");
        $style.=$this->Param("overflow","hidden",":","",";");
        $style.=$this->Param("visibility",$visible==true?"visible":"hidden",":","",";");
        $style.=$this->Param("display",$visible==true?"block":"none",":","",";");
        $style.=$this->Param("top","0",":","",";");
        $style.=$this->Param("left","0",":","",";");
        $style.=$this->Param("width",$width,":","",";");
        $style.=$this->Param("height",$height,":","",";");
        $style.=$this->Param("z-index",$zIndex,":","",";");
        $style.=$this->Param("background-color",$bgColor,":","",";");
        $style.=$this->Param("background-image",$bgImage,":","",";");


        // Do we need clip?
        //$style.=$this->Param("clip","rect(0,".$width.",".$height.",0)",":","",";");

        // Add events

        $src.=$this->Param("style",$style,"=","\"","");
        $src.=">";
        $src.=$html;
        $src.="</div>\n";

        return($src);
    }
    function CreateItem($id,$text,$url,$target,$css,$subitems,$level,$pc_parent,$tree_type="normal",$id_array){
        $img_item = "images/ws-text-file.gif";
        $img_dir_close = "images/fc.gif";
        $image = "\"".($subitems==""?$img_item:$img_dir_close)."\"";
        $imgtag = "<img border=0 id=\"".$id."_codethat_image\" src=".$image.">";
        if($tree_type=="checkbox"){
            if (in_array($id, $id_array)){
                $checked_str="checked";
            }
            $imgtag = "<input type='checkbox' name=related_id[] value='".$id."' ".$checked_str.">".$imgtag;
        }
        $td="";
        for($i=0;$i<$level;$i++)
            $td.="<td width=20px></td>";
        $atag = "href=\"".$url."\" ".($target==""?"":("target=\"".$target."\""));
        if($subitems=="")
            $html="<table cellpadding=0 cellspacing=0 border=0><tr>".$td."<td><a ".$atag.">".$imgtag."</a></td><td align=left><a ".$atag." class=".$css."><p class=".$css.">".$text."</p></a></td></tr></table></a>";
        else
            $html="<table cellpadding=0 cellspacing=0 border=0><tr>".$td."<td><a href=\"javascript:toggleNode('".$id."');\">".$imgtag."</a></td><td align=left><a ".$atag." onClick=\"toggleNode('".$id."');\" class=".$css."><p class=".$css.">".$text."</p></a></td></tr></table></a>";
        // We create item as one main layer
        $src=$this->CreateLayer(
                    $id,  // Id
                    true, // Relative
                    "",   // Top
                    "",   // Left
                    "",   // Width
                    "",   // Height
                    "",   // Css class
                    "",   // Background color
                    "",   // URL of background image
                    true, // Is it visible?
                    1,    // Z index
                    $html,// HTML text
                    ""    // Events
                    );
        if($subitems!="")
            $TF=($id==$pc_parent)?false:true;
            $src.=$this->CreateLayer($id."_codethat_subitems",true,"","","","","","","",$TF,1,$subitems,"");
        return($src);
    }
    function Preface(){
        $img_dir_open = "images/fe.gif";
        $img_dir_close = "images/fc.gif";
        $img_item = "images/ws-text-icon.gif";
            $str ="<script language='javascript'>\r\n";
        $str.="var ct_image_dir = new Image();ct_image_dir.src=\"".$img_dir_open."\";\r\n";
        $str.="var ct_image_diropen = new Image();ct_image_dir.src=\"".$img_dir_close."\";\r\n";
        $str.="var ct_image_item = new Image();ct_image_dir.src=\"".$img_item."\";\r\n";
        $str.="function setExpandedIco(id){var i=document.getElementById(id+'_codethat_image');i.src='".$img_dir_open."';}\r\n" ;
            $str.="function setCollapsedIco(id){var i=document.getElementById(id+'_codethat_image');i.src='".$img_dir_close."';}\r\n" ;
            $str.="function toggleNode(id){if(toggleLayer(id+'_codethat_subitems'))setExpandedIco(id);else setCollapsedIco(id);}\r\n" ;
            $str.="function toggleLayer(id){var l=document.getElementById(id);var s=l.style||l;if(s.visibility=='hidden'){s.visibility='visible';s.display='block';return true;}else{s.visibility='hidden';s.display='none';return false;}}\r\n";
        $str.="</script>\r\n";
        return ($str);
    }
    //Js 樹狀選單
    function Param($name,$value,$equal,$brackets,$post){
        $str="";
        if($value!="")
        {
            $str=$name.$equal.$brackets.$value.$brackets.$post;
        }
        return($str);
    }
    //pageview history
    function pageview_history($ph_type,$ph_type_id=0,$m_id=0){
        global $db,$cms_cfg;
        $ip=$_SERVER["REMOTE_ADDR"];
        //$ip="59.126.50.204"; //taiwan
        //$ip="137.153.10.110";
        if($ip!="127.0.0.1"){
            $ph_ip_number = sprintf("%u", ip2long($ip));
            //get ip country
            $sql="SELECT country_name FROM ".$cms_cfg['tb_prefix']."_ip_country WHERE ip_from <= inet_aton('".$ip."') AND ip_to >= inet_aton('".$ip."') ";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            if(empty($row["country_name"])){
                $row["country_name"]="UNKNOWN";
            }
            $sql="
                insert into ".$cms_cfg['tb_prefix']."_pageview_history (
                    m_id,
                    ph_ip_number,
                    ph_country,
                    ph_type,
                    ph_type_id,
                    ph_modifydate,
                    ph_dateY,
                    ph_dateM,
                    ph_dateD
                ) values (
                    '".$m_id."',
                    '".$ph_ip_number."',
                    '".$row["country_name"]."',
                    '".$ph_type."',
                    '".$ph_type_id."',
                    '".date("Y-m-d H:i:s")."',
                    '".date("Y")."',
                    '".date("m")."',
                    '".date("d")."'
                )";
            $db->query($sql);
        }
    }
    function js_notice($msg,$goto_url){
        echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
        echo "<script language=javascript>";
        echo "Javascript:alert('".$msg."')";
        echo "</script>";
        echo "<script language=javascript>";
        echo "document.location='".$goto_url."'";
        echo "</script>";
    }
    function replace_html_tags($str){
        $str=str_replace(" ","",strip_tags($str));
        $str=str_replace("\r\n","",$str);
        return $str;
    }
    //counter
    /**
     *  $num : 計數器位數
     *  $session_on : 使用session控制重新整理是否計數
     */
    function counter($num=10, $session_on=0) {
        global $tpl,$cms_cfg;
        $fh = fopen("conf/counter.txt", "r+");
        $count = fgets($fh, 4096);
        if($session_on) {
            if(empty($_SESSION["visited"])) {
                $_SESSION["visited"] = 1;
                $count++;
                fseek($fh, 0);
                if(fputs($fh, $count)===false) return "Counter update error!";
            }
        }else{
            $count++;
            fseek($fh, 0);
            if(fputs($fh, $count)===false) return "Counter update error!";
        }
        fclose($fh);
        $count_dig = str_pad($count, $num, "0", STR_PAD_LEFT);
        $c_arr = str_split($count_dig);
        $c_str ="";
        foreach($c_arr as $key => $data) {
            $c_str .= "<img border=\"0\" src=".$cms_cfg["base_root"]."images/".$c_arr["$key"].".gif />&nbsp;";
        }
        $tpl->assignGlobal("TAG_COUNTER_PIC", $c_str);
        return true;
    }
    //Mathematics security code
    function math_security() {
        global $tpl,$ws_array,$cms_cfg,$TPLMSG;
        $digital1 = rand(1,10);
        $digital2 = rand(1,10);
        $_SESSION["securityCode_math"] = $digital1+$digital2;
        $math_str = $digital1." + ".$digital2." =";
        $tpl->assignGlobal( array("MSG_LOGIN_SECURITY" => $TPLMSG["LOGIN_SECURITY"],
                                  "TAG_MATH_SECURITY" => $math_str,
                                  "TAG_MATH_INPUT" => "<input type=\"text\" name=\"security\" size=\"4\" />"
        ));
    }
    //Mathematics security code is value
    function math_security_isvalue() {
        global $tpl,$ws_array,$cms_cfg,$TPLMSG;
        return $_REQUEST["security"] == $_SESSION["securityCode_math"];
    }
    //國家下拉選單
    function country_select($country="") {
        global $tpl,$ws_array,$TPLMSG;
        $tpl->newBlock("SELECT_COUNTRY");
        $tpl->assign("MSG_COUNTRY", $TPLMSG['COUNTRY']);
        $str = "<option value=\"\">-- Select Country --</option>\n";
        foreach($ws_array["country_array"] as $key => $value) {
            $sel = ($value==$country) ? "selected":"";
            $str .= "<option value=\"".$value."\" ".$sel.">".$value."</option>\n";
        }
        $tpl->assignGlobal("TAG_SELECT_OPTION_COUNTRY", $str);
    }
    function google_code(){
        global $tpl,$db,$cms_cfg;
        $sql="select sc_ga_code,sc_gs_code,sc_gs_datetime,sc_gs_filename from ".$cms_cfg['tb_prefix']."_system_config";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        if(trim($row["sc_ga_code"])!=""){
            $tpl->newBlock("GOOGLE_ANALYTICS");
            $tpl->assign("VALUE_GA_CODE",$row["sc_ga_code"]);
        }
        if(trim($row["sc_gs_code"])!=""){
            $tpl->newBlock("GOOGLE_SITEMAP_METATAG");
            $tpl->assign("VALUE_GS_CODE",$row["sc_gs_code"]);
        }
    }
    //圖檔檔案路徑替換避免破圖
    function file_str_replace($input_path){
        global $cms_cfg;
        $non_www_url=str_replace("www.","",$cms_cfg['file_url']);
        $input_path=str_replace($cms_cfg['file_url'],"",$input_path);
        $input_path=str_replace($non_www_url,"",$input_path);
        $input_path=str_replace($cms_cfg['file_root']."upload_files/","upload_files/",$input_path);
        return $input_path;
    }
    //鎖滑鼠右鍵功能
    function mouse_disable() {
        global $tpl,$cms_cfg;
        $str = "";
        if($cms_cfg["ws_module"]["ws_on_contextmenu"]==1) $str .="onContextMenu=\"return false\" ";  //禁滑鼠右鍵
        if($cms_cfg["ws_module"]["ws_on_copy"]==1) $str .="onCopy=\"return false\" "; //禁複製
        if($cms_cfg["ws_module"]["ws_on_selectstart"]==1) $str .="onSelectStart=\"return false\"";  //禁選擇
        $tpl->assignGlobal("TAG_MOUSE_DISABLE", $str);
    }
    //取得最大排序值
    function get_max_sort_value($table_name,$table_prefix,$field,$id,$cate){
        global $db;
        if($cate){ //是否有上層分類
            $sql="select MAX(".$table_prefix."_sort) as max_value from ".$table_name." where ".$field."='".$id."'";
        }else{
            $sql="select MAX(".$table_prefix."_sort) as max_value from ".$table_name;
        }
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $sort_value=$row["max_value"]+1;
        return $sort_value;
    }
    function ad_list($id){
        global $db,$tpl,$cms_cfg;
        //排序方式
        switch($_SESSION[$cms_cfg['sess_cookie_name']]["sc_ad_sort_type"]){
            case 0 :
                $orderby=" order by rand() ";
                break;
            case 1 :
                $orderby=" order by ad_modifydate desc ";
                break;
            case 2 :
                $orderby=" order by ad_sort desc ";
                break;
            default :
                $orderby=" order by rand() ";
        }
        //上方橫幅廣告 寬580 X 高120
        $ad_up_banner_limit=($cms_cfg['ad_up_banner_limit'])?$cms_cfg['ad_up_banner_limit']:1;
        $sql="select * from ".$cms_cfg['tb_prefix']."_ad where ad_cate='1' and (ad_status='1' or (ad_status='2' and ad_startdate <= '".date("Y-m-d")."' and ad_enddate >= '".date("Y-m-d")."') ) ".$orderby." limit 0,".$ad_up_banner_limit;
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        if($rsnum >0){
            while($row = $db->fetch_array($selectrs,1)){
                $tpl->newBlock("AD_ZONE_580_120");
                switch($row["ad_file_type"]){
                    case "image" :
                        $tpl->newBlock("AD_TYPE_IMAGE_580_120");
                        $tpl->assign("VALUE_AD_SUBJECT",$row["ad_subject"]);
                        $tpl->assign("VALUE_AD_LINK",$row["ad_link"]);
                        $tpl->assign("VALUE_AD_FILE",$row["ad_file"]);
                        break;
                    case "flash" :
                        $tpl->newBlock("AD_TYPE_FLASH_580_120");
                        $tpl->assign("VALUE_AD_LINK",$row["ad_link"]);
                        if(!empty($row["ad_file"])){
                            $piece=explode(".swf",$row["ad_file"]);
                            $tpl->assign("VALUE_AD_FILE",$piece[0]);
                        }
                        break;
                    case "txt" :
                        $tpl->newBlock("AD_TYPE_TXT_580_120");
                        $tpl->assign("VALUE_AD_SUBJECT",$row["ad_subject"]);
                        $tpl->assign("VALUE_AD_LINK",$row["ad_link"]);
                        break;
                }
                $tpl->gotoBlock("AD_ZONE_580_120");
            }
        }
        //側邊廣告 寬150 X 高150
        $ad_left_button_limit=($cms_cfg['ad_left_button_limit'])?$cms_cfg['ad_left_button_limit']:1;
        $sql="select * from ".$cms_cfg['tb_prefix']."_ad where ad_cate='2' and (ad_status='1' or (ad_status='2' and ad_startdate <= '".date("Y-m-d")."' and ad_enddate >= '".date("Y-m-d")."') ) ".$orderby." limit 0,".$ad_left_button_limit;
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        if($rsnum >0){
            while($row = $db->fetch_array($selectrs,1)){
                $tpl->newBlock("AD_ZONE_150_150");
                switch($row["ad_file_type"]){
                    case "image" :
                        $tpl->newBlock("AD_TYPE_IMAGE_150_150");
                        $tpl->assign("VALUE_AD_SUBJECT",$row["ad_subject"]);
                        $tpl->assign("VALUE_AD_LINK",$row["ad_link"]);
                        $tpl->assign("VALUE_AD_FILE",$cms_cfg["file_root"].$row["ad_file"]);
                        break;
                    case "flash" :
                        $tpl->newBlock("AD_TYPE_FLASH_150_150");
                        $tpl->assign("VALUE_AD_LINK",$row["ad_link"]);
                        if(!empty($row["ad_file"])){
                            $piece=explode(".swf",$row["ad_file"]);
                            $tpl->assign("VALUE_AD_FILE",$cms_cfg["file_root"].$piece[0]);
                        }
                        break;
                    case "txt" :
                        $tpl->newBlock("AD_TYPE_TXT_150_150");
                        $tpl->assign("VALUE_AD_SUBJECT",$row["ad_subject"]);
                        $tpl->assign("VALUE_AD_LINK",$row["ad_link"]);
                        break;
                }
                $tpl->gotoBlock("AD_ZONE_150_150");
            }
        }
        //側邊廣告 寬150 X 高50
        $ad_left_button_limit=($cms_cfg['ad_left_button_limit'])?$cms_cfg['ad_left_button_limit']:1;
        $sql="select * from ".$cms_cfg['tb_prefix']."_ad where ad_cate='3' and (ad_status='1' or (ad_status='2' and ad_startdate <= '".date("Y-m-d")."' and ad_enddate >= '".date("Y-m-d")."') ) ".$orderby." limit 0,".$ad_left_button_limit;
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        if($rsnum >0){
            while($row = $db->fetch_array($selectrs,1)){
                $tpl->newBlock("AD_ZONE_150_50");
                switch($row["ad_file_type"]){
                    case "image" :
                        $tpl->newBlock("AD_TYPE_IMAGE_150_50");
                        $tpl->assign("VALUE_AD_SUBJECT",$row["ad_subject"]);
                        $tpl->assign("VALUE_AD_LINK",$row["ad_link"]);
                        $tpl->assign("VALUE_AD_FILE",$cms_cfg["file_root"].$row["ad_file"]);
                        break;
                    case "flash" :
                        $tpl->newBlock("AD_TYPE_FLASH_150_50");
                        $tpl->assign("VALUE_AD_LINK",$row["ad_link"]);
                        if(!empty($row["ad_file"])){
                            $piece=explode(".swf",$row["ad_file"]);
                            $tpl->assign("VALUE_AD_FILE",$cms_cfg["file_root"].$piece[0]);
                        }
                        break;
                    case "txt" :
                        $tpl->newBlock("AD_TYPE_TXT_150_50");
                        $tpl->assign("VALUE_AD_SUBJECT",$row["ad_subject"]);
                        $tpl->assign("VALUE_AD_LINK",$row["ad_link"]);
                        break;
                }
                $tpl->gotoBlock("AD_ZONE_150_50");
            }
        }
        //首頁跑馬燈
        $ad_left_button_limit=($cms_cfg['ad_left_button_limit'])?$cms_cfg['ad_left_button_limit']:1;
        $sql="select * from ".$cms_cfg['tb_prefix']."_ad where ad_cate='4' and (ad_status='1' or (ad_status='2' and ad_startdate <= '".date("Y-m-d")."' and ad_enddate >= '".date("Y-m-d")."') ) ".$orderby." limit 0,".$ad_left_button_limit;
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        if($rsnum >0){
            while($row = $db->fetch_array($selectrs,1)){
                $tpl->newBlock("AD_ZONE_MARQUEE");
                switch($row["ad_file_type"]){
                    case "txt" :
                        $tpl->newBlock("AD_TYPE_TXT_MARQUEE");
                        $tpl->assign("VALUE_AD_SUBJECT",$row["ad_file"]);
                        $tpl->assign("VALUE_AD_LINK",$row["ad_link"]);
                        break;
                }
                $tpl->gotoBlock("AD_ZONE_MARQUEE");
            }
        }

    }
    //取得主功能類別，如：abouts,service,products,news,faq,case,contactus
    function get_main_fun(){     
        global $cms_cfg;
        return  preg_replace("#^".preg_quote($cms_cfg['base_root'],"/")."#","",preg_replace("/\.php$/","", $_SERVER['SCRIPT_NAME']));
    }
    function dropdown_menu(){
        global $cms_cfg,$tpl,$db;
        $tpl->newBlock("DROPDOWN_MENU");//載入下拉式功能的JS
        //撈取下拉式功能表項目
        /////產品介紹 的下拉式選單
        $sql = "select * from ".$cms_cfg['tb_prefix']."_products_cate where pc_parent = '0' order by pc_sort desc ";
        $res = $db->query($sql);
        if($db->numRows($res)){
            while($row = $db->fetch_array($res,true)){
                $tpl->newBlock("DROPDOWN_MENU_PRODUCT");
                $tpl->assign("MENU_ITEM_NAME",$row['pc_name']);
                $tpl->assign("MENU_ITEM_LINK","products.php?func=p_list&pc_parent=".$row['pc_id']);
            }
        }        
    }        
    /*相關網站
    **將 tempaltes/ws-fn-goodlink-select-tpl 引入為區塊
    */
    function goodlink_select(){
        global $db,$tpl,$cms_cfg;
        $sql = "select * from ".$cms_cfg['tb_prefix']."_goodlink where l_status='1' order by l_sort desc ";
        $res = $db->query($sql);
        if($db->numRows($res)){
            while($row = $db->fetch_array($res,1)){
                $tpl->newBlock("GOODLINK_SELECT_OPTION");
                $tpl->assign(array(
                    'GOODLINK_URL'  => $row['l_url'],
                    "GOODLINK_NAME" => $row['l_subject'],
                    "GOODLINK_POP"  => $row['l_pop'],
                ));
            }
        }
    }   
    
    function float_menu(){
        global $tpl;
        $tpl->newBlock("SCROLL_FLOAT_SCRIPT");
    }     
}
?>