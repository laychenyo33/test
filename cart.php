<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$cart = new CART;
class CART{
    function CART(){
        global $db,$cms_cfg,$tpl,$main;
        $this->m_id =$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ID"];
        $this->cart_type =$_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"];
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->contact_s_style = $cms_cfg['ws_module']['ws_contactus_s_style'];
        switch($_REQUEST["func"]){
            case "ajax_get_charge_fee":
                $this->ajax_get_charge_fee();
                break;
            case "ajax_show_ship_price":
                $this->ajax_show_ship_price();
                break;
            case "c_list"://購物車列表
                $this->ws_tpl_file = "templates/ws-cart".$this->cart_type."-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_POP_IMG");
                $tpl->newBlock("JQUERY_UI_SCRIPT");
                $this->cart_list();
                $this->ws_tpl_type=1;
                break;
            case "c_add"://新增購物項目
                if(!$_POST['via_ajax']){
                    $this->ws_tpl_file = "templates/ws-cart".$this->cart_type."-tpl.html";
                    $this->ws_load_tp($this->ws_tpl_file);
                    $tpl->newBlock("JS_MAIN");
                    $tpl->newBlock("JS_POP_IMG");
                    $tpl->newBlock("JQUERY_UI_SCRIPT");
                    $this->ws_tpl_type=1;
                }
                $this->cart_add($_POST['via_ajax']);
                break;
            case "c_list_add"://新增購物項目(產品列表)
                $this->ws_tpl_file = "templates/ws-cart".$this->cart_type."-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->cart_list_add();
                $this->ws_tpl_type=1;
                break;
            case "c_quick_add"://快速購物項目
                $this->ws_tpl_file = "templates/ws-cart".$this->cart_type."-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_POP_IMG");
                $this->cart_quick_add();
                $this->ws_tpl_type=1;
                break;
            case "c_mod"://購物車列表
                if(!$_POST['via_ajax']){
                    $this->ws_tpl_file = "templates/ws-cart".$this->cart_type."-tpl.html";
                    $this->ws_load_tp($this->ws_tpl_file);
                    $tpl->newBlock("JS_MAIN");
                    $tpl->newBlock("JS_POP_IMG");
                    $this->ws_tpl_type=1;
                }
                $this->cart_modify($_POST['via_ajax']);
                break;
            case "c_del"://刪除購物項目
                $this->ws_tpl_file = "templates/ws-cart".$this->cart_type."-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_POP_IMG");
                $this->cart_del();
                $this->ws_tpl_type=1;
                break;
            case "c_full_del"://清空購物車
                $this->cart_full_del($_POST['via_ajax']);
                break;
            case "c_finish"://結帳
                if($_POST['shipment_type']){
                    $_SESSION[$cms_cfg['sess_cookie_name']]["shipment_type"] = $_POST['shipment_type'];
                }
                if(empty($this->m_id) && $cms_cfg["ws_module"]["ws_cart_login"]==1 && empty($_POST['shop_and_register'])){
                    $this->ws_tpl_file = "templates/ws-cart-login-tpl.html";
                    $this->ws_load_tp($this->ws_tpl_file);
                    $this->member_login();
                }else{
                    $this->ws_tpl_file = "templates/ws-cart-finish".$this->cart_type."-tpl.html";
                    $this->ws_load_tp($this->ws_tpl_file);
                    $tpl->newBlock("JS_FORMVALID");
                    $tpl->newBlock("JQUERY_UI_SCRIPT");
                    $tpl->newBlock("DATEPICKER_SCRIPT_IN_CART");
                    if($cms_cfg['ws_module']['ws_address_type']=='tw')$main->res_init("zone",'box');
                    $this->cart_finish();
                }
                $this->ws_tpl_type=1;
                break;
            case "preview": //預覽購物訂單
                $this->ws_tpl_file = "templates/ws-cart-preview-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->cart_preview();
                $main->load_privacy_term();
                $this->ws_tpl_type=1;
                break;
            case "c_replace"://存成訂單或詢價單
                $this->cart_replace();
                break;
            case "s_inquiry": //單一詢問車
                $this->ws_tpl_file = "templates/ws-single-inquiry-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                //$tpl->newBlock("JS_POP_IMG");
                $this->inquiry_form();
                $this->ws_tpl_type=1;
                break;
            case "s_inquiry_replace": //存成單一詢價單
                $this->inquiry_replace();
                break;
            default: //購物車列表
                $this->ws_tpl_file = "templates/ws-cart".$this->cart_type."-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_POP_IMG");
                $tpl->newBlock("JQUERY_UI_SCRIPT");
                $this->cart_list();
                $this->ws_tpl_type=1;
                break;
        }
        if($this->ws_tpl_type){
            //$this->new_products_list();   //最新產品
            //$this->hot_products_list();   //熱門產品
            //$this->promotion_products_list(); //促銷產品
            $main->layer_link();
            $tpl->printToScreen();
        }
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$ws_array,$db,$TPLMSG,$main;
        $tpl = new TemplatePower( $cms_cfg['base_all_tpl'] );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方首頁表單
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        $tpl->assignInclude( "AD_H", "templates/ws-fn-ad-h-tpl.html"); //橫式廣告模板
        $tpl->assignInclude( "AD_V", "templates/ws-fn-ad-v-tpl.html"); //直式廣告模板
        $tpl->assignInclude( "CONTACT_S", "templates/ws-fn-contact-s-style".$this->contact_s_style."-tpl.html"); //稱呼樣版      
        $tpl->assignInclude( "N_CONTACT_S", "templates/ws-fn-contact-s-style".$this->contact_s_style."-tpl.html"); //稱呼樣版      
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["products"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["products"]);//左方menu title
        $tpl->assignGlobal( "TAG_PRODUCTS_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["products"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-products"); //主要顯示區域的css設定
        $tpl->assignGlobal( "TAG_LAYER" , $TPLMSG['CART_INQUIRY']); //麵包屑
        $main->header_footer("");
        $main->google_code(); //google analystics code , google sitemap code
        //$main->left_fix_cate_list();
        $leftmenu = new Leftmenu_Products($tpl);
        $leftmenu->make();        
    }

    function cart_add($via_ajax){
        global $cms_cfg,$db;
        $_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL']=$_SERVER['HTTP_REFERER'];
        $amount_arr = is_array($_REQUEST["amount"])?$_REQUEST["amount"]:(array)$_REQUEST["amount"];
        $p_id_arr = is_array($_REQUEST["p_id"])?$_REQUEST["p_id"]:(array)$_REQUEST["p_id"];
        foreach($p_id_arr as $k => $p_id){
            if($p_id){
                $amount = $amount_arr[$k]?$amount_arr[$k]:1;
                $_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"][$p_id]=1;
                if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$p_id])){
                    $_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$p_id]=$amount;
                }else{
                    $_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$p_id]=$_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$p_id]+$amount;
                }
            }
        }
        if(!$via_ajax){
            $this->cart_list();
        }else{
            $res['code'] = 1;
            $res['cart_nums'] = count($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"]);
            echo json_encode($res);
        }
    }
    function cart_list_add(){
        global $cms_cfg;
        $_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL']=$_SERVER['HTTP_REFERER'];
        $amount=1;
        if(is_array($_REQUEST["p_id"])) {
            //產品列表勾選式加入詢價車
            foreach($_REQUEST["p_id"] as $key => $value){
                $_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"][$value]=1;
                if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$value])){
                    $_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$value]=$amount;
                }else{
                    $_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$value]=$_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$value]+$amount;
                }
            }
        }else{
            //產品列表連結式加入詢價車
            $p_id=$_REQUEST["p_id"];
            $_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"][$p_id]=1;
            if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$p_id])){
                $_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$p_id]=$amount;
            }else{
                $_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$p_id]=$_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$p_id]+$amount;
            }
        }
        $this->cart_list();
    }
    function cart_quick_add(){
        global $cms_cfg;
        $_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL']=$_SERVER['HTTP_REFERER'];
        $amount=$_REQUEST["amount"];
        foreach($_REQUEST["amount"] as $key => $value){
            if($value !=""){
                $_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"][$key]=1;
                if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$key])){
                    $_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$key]=$value;
                }else{
                    $_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$key]=$_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$key]+$value;
                }
            }
        }
        $this->cart_list();
    }
    function cart_list(){
        global $db,$tpl,$TPLMSG,$ws_array,$shopping,$inquiry,$cms_cfg,$main;
        if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"])){ //空購物車時，回到前一頁
            $main->js_notice($TPLMSG['CART_EMPTY'],$cms_cfg['base_root']."products.htm");
            die();
        }             
        //取得目前的 cart type，以及運費相關欄位
        $sql="select sc_cart_type,sc_shipping_price,sc_shipping_price2,sc_shipping_price3,sc_no_shipping_price,sc_service_fee from ".$cms_cfg['tb_prefix']."_system_config where sc_id='1'";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]=($row["sc_cart_type"]=="")?0:$row["sc_cart_type"];
        $main->layer_link($ws_array["cart_type"][$_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]]);
        //購物車使用的運費info
        $tpl->assignGlobal(array(
            "VALUE_SC_SHIPPING_PRICE"    => $row['sc_shipping_price'],
            "VALUE_SC_SHIPPING_PRICE2"   => $row['sc_shipping_price2'],
            "VALUE_SC_SHIPPING_PRICE3"   => $row['sc_shipping_price3'],
            "VALUE_SC_NO_SHIPPING_PRICE" => $row['sc_no_shipping_price'],
            "VALUE_SC_SERVICE_FEE"       => $row['sc_service_fee'],
        ));
        //欄位名稱
        $tpl->assignGlobal( array("MSG_NAME"  => $TPLMSG['MEMBER_NAME'],
                                  "MSG_CONTENT"  => $TPLMSG['CONTENT'],
                                  "MSG_MODIFY" => $TPLMSG['MODIFY'],
                                  "MSG_DEL" => $TPLMSG['DEL'],
                                  "MSG_TOTAL" => $TPLMSG['CART_TOTAL'],
                                  "MSG_PLUS_FEE" => $TPLMSG["PLUS_FEE"],
                                  "MSG_SUBTOTAL" => $TPLMSG['CART_SUBTOTAL'],
                                  "MSG_AMOUNT" => $TPLMSG['CART_AMOUNT'],
                                  "MSG_PRODUCT" => $TPLMSG['PRODUCT'],
                                  "MSG_PRODUCT_IMAGE" => $TPLMSG['PRODUCT_IMG'],
                                  "VALUE_MODIFY_AMOUNT" => $TPLMSG['CART_MODIFY_AMOUNT'],
                                  //"CART_IMG_TITLE"=> $ws_array["cart_img"][$_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]]["title_img"],
                                  //"CART_IMG_SUB"=> $ws_array["cart_img"][$_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]]["sub_img"],
        ));
        $pid_array = array();
        if(!empty($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"])){
            foreach($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"] as $key => $value){
                if($key){
                    $pid_array[]=$key;
                }
            }
        }
        if(!empty($pid_array)){
            $pid_array_str="(".implode(",",$pid_array).")";
            //$sql="select p_id,p_name,p_special_price,p_type,p_show_price,p_small_img,p_seo_filename from ".$cms_cfg['tb_prefix']."_products where p_id in ".$pid_array_str." ";
            $sql="select p.pc_id,p.p_id,p.p_name,p.p_name_alias,p.p_serial,p.p_small_img,p.p_special_price,p.p_list_price,p.p_seo_filename,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p.p_id in ".$pid_array_str." ";
            $selectrs = $db->query($sql);
            $show_price=$_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"];
            while ( $row = $db->fetch_array($selectrs,1) ) {
                if($show_price==1){
                    $shopping[]=$row;
                }
                if($show_price==0){
                    $inquiry[]=$row;
                }
            }
            /*
            //如果不需要會員登入，直接顯示表單
            if($cms_cfg["ws_module"]["ws_cart_login"]==0){
                //付款說明
                $this->get_terms();
                $tpl->newBlock("MEMBER_FORM");
                //欄位名稱
                $tpl->assign( array(  "MSG_MEMBER_NAME"  => $TPLMSG['MEMBER_NAME'],
                                      "MSG_VALID_PASSWORD" => $TPLMSG['MEMBER_CHECK_PASSWORD'],
                                      "MSG_BIRTHDAY" => $TPLMSG["BIRTHDAY"],
                                      "MSG_SEX" => $TPLMSG["SEX"],
                                      "MSG_MALE" => $TPLMSG["MALE"],
                                      "MSG_FEMALE" => $TPLMSG["FEMALE"],
                                      "MSG_ZIP" => $TPLMSG["ZIP"],
                                      "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                                      "MSG_TEL" => $TPLMSG["TEL"],
                                      "MSG_FAX" => $TPLMSG["FAX"],
                                      "MSG_EMAIL" => $TPLMSG["EMAIL"],
                                      "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
                                      "STR_M_SEX_CK1" => "",
                                      "STR_M_SEX_CK0" => "checked",

                ));
                //定義目前語系的表單檢查JS
                $tpl->assignGlobal("TAG_LANG",$cms_cfg['language']);
                $tpl->assignGlobal("TAG_FORM_CHECK","onSubmit=\"return CartMemberFormCheck(myform)\"");
                $tpl->assignGlobal("FINISH_TAG","javascript:CartAction(myform,'c_replace');");
            }else{
                $tpl->assignGlobal("FINISH_TAG","javascript:location.href=c_finish");
            }
            */
        }else{
            $tpl->assignGlobal("MSG_CART_EMPTY" ,$TPLMSG['NO_DATA']);
        }

        //顯示購物清單
        if(!empty($shopping)){
            //H1 TAG
            $tpl->assignGlobal("TAG_MAIN_FUNC" , $TPLMSG['CART_SHOPPING']);
            $tpl->newBlock( "SHOPPING_CART_ZONE" );
            $tpl->assignGlobal( array(
                "MSG_CONTINUE_SHOPPING"  => $TPLMSG['CART_CONTINUE_SHOPPING'],
                "MSG_FINISH_SHOPPING"  => $TPLMSG['CART_FINISH_SHOPPING'],
                "LINK_CONTINUE" => $_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL'],
                "MSG_SHIPPING_PRICE"  => $TPLMSG['SHIPPING_PRICE'],
            ));
            //送貨區域
            App::getHelper('main')->multiple_radio("shipment_type",$ws_array["shippment_type"],"",$tpl);                 
            for($i=0;$i<count($shopping);$i++){
                $tpl->newBlock( "SHOPPING_CART_LIST" );
                $pid=$shopping[$i]["p_id"];
                $amount=$_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$pid];
                $price = $shopping[$i]["p_special_price"]?$shopping[$i]["p_special_price"]:$shopping[$i]["p_list_price"];
                if(!empty($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]) && $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]!=100){
                    $tpl->assignGlobal("MSG_PRODUCT_SPECIAL_PRICE" , $TPLMSG['PRODUCT_DISCOUNT_PRICE']);
                    $tpl->assignGlobal("VALUE_P_DISCOUNT",$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]."%");
                    $special_price=floor($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]/100*$price);
                }else{
                    $tpl->assignGlobal("MSG_PRODUCT_SPECIAL_PRICE" , $TPLMSG['PRODUCT_SPECIAL_PRICE']);
                    $tpl->assignGlobal("VALUE_P_DISCOUNT","");
                    $special_price=$price;
                }
                if($this->ws_seo){
                    $dirname=(trim($shopping[$i]["pc_seo_filename"]))?$shopping[$i]["pc_seo_filename"]:"products";
                    if(trim($shopping[$i]["p_seo_filename"]) !=""){
                        $p_link=$cms_cfg['base_url'].$dirname."/".$shopping[$i]["p_seo_filename"].".html";
                    }else{
                        $p_link=$cms_cfg['base_url'].$dirname."/"."products-".$shopping[$i]["p_id"]."-".$shopping[$i]["pc_id"].".html";
                    }
                }else{
                    $p_link=$cms_cfg['base_url']."products.php?func=p_detail&p_id=".$shopping[$i]["p_id"]."&pc_parent=".$shopping[$i]["pc_id"];
                }
                $sub_total_price = $special_price * $amount;
                $total_price += $sub_total_price;
                $tpl->assign( array("VALUE_P_ID"  => $shopping[$i]["p_id"],
                                    "VALUE_P_NAME"  => $shopping[$i]["p_name"],
                                    "VALUE_P_SMALL_IMG" => (trim($shopping[$i]["p_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_url"].$shopping[$i]["p_small_img"],
                                    "VALUE_P_AMOUNT"  => $amount,
                                    "VALUE_P_LINK" => $p_link,
                                    "VALUE_P_SPECIAL_PRICE"  => $special_price,
                                    "VALUE_P_SUBTOTAL_PRICE"  => $sub_total_price,
                                    "VALUE_P_SERIAL"  => $i+1,
                                    "TAG_DELETE_CHECK_STR" => $TPLMSG['CART_DELETE_CHECK']
                ));
                $tpl->gotoBlock( "SHOPPING_CART_ZONE" );
            }
            $shipping_price = $this->shipping_price($total_price,$_SESSION[$cms_cfg['sess_cookie_name']]["shipment_type"]);
			
            $subtotal_money=$total_price;
            $total_money=$subtotal_money+$shipping_price;
            $tpl->assignGlobal("VALUE_SHIPPING_PRICE",$shipping_price);
            $tpl->assignGlobal("VALUE_SHIPPING_PRICE_STR",($_POST['shipment_type']<3 && $shipping_price===0)?"滿額免運費":$shipping_price);
            $tpl->assignGlobal("VALUE_MINUS_PRICE",0);
            $tpl->assignGlobal("VALUE_SUBTOTAL",$subtotal_money);
            $tpl->assignGlobal("VALUE_TOTAL",$total_money);
            //購物說明
            $this->load_term($tpl);
        }
        //顯示詢價清單
        if(!empty($inquiry)){
            //H1 TAG
            $tpl->assignGlobal("TAG_MAIN_FUNC" , $TPLMSG['CART_INQUIRY']);
            $tpl->assignGlobal("TAG_DISPLAY" , "style='display:none'");
            $tpl->newBlock( "INQUIRY_CART_ZONE" );
            $tpl->assign( array(
                "MSG_CONTINUE_INQUIRY"  => $TPLMSG['CART_CONTINUE_INQUIRY'],
                "MSG_FINISH_INQUIRY"  => $TPLMSG['CART_FINISH_INQUIRY'],
                "LINK_CONTINUE" => $_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL'],
                "MSG_QUANTITY_UPDATED" => $TPLMSG["INQUIRY_QUANTITY_UPDATED"],
            ));
            for($i=0;$i<count($inquiry);$i++){
                $tpl->newBlock( "INQUIRY_CART_LIST" );
                $pid=$inquiry[$i]["p_id"];
                $amount=$_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$pid];
                if($this->ws_seo){
                    $dirname=(trim($inquiry[$i]["pc_seo_filename"]))?$inquiry[$i]["pc_seo_filename"]:"products";
                    if(trim($inquiry[$i]["p_seo_filename"]) !=""){
                        $p_link=$cms_cfg['base_url'].$dirname."/".$inquiry[$i]["p_seo_filename"].".html";
                    }else{
                        $p_link=$cms_cfg['base_url'].$dirname."/"."products-".$inquiry[$i]["p_id"]."-".$inquiry[$i]["pc_id"].".html";
                    }
                }else{
                    $p_link=$cms_cfg['base_url']."products.php?func=p_detail&p_id=".$inquiry[$i]["p_id"]."&pc_parent=".$inquiry[$i]["pc_id"];
                }
                $tpl->assign( array("VALUE_P_ID"  => $inquiry[$i]["p_id"],
                                    "VALUE_P_NAME"  => $inquiry[$i]["p_name"],
                                    "VALUE_P_SMALL_IMG" => (trim($inquiry[$i]["p_small_img"])=="")?"http://".$cms_cfg['server_name'].$cms_cfg['default_preview_pic']:$cms_cfg["file_url"].$inquiry[$i]["p_small_img"],
                                    "VALUE_P_AMOUNT"  => $amount,
                                    "VALUE_P_LINK" => $p_link,
                                    "VALUE_P_SERIAL"  => $i+1,
                                    "TAG_DELETE_CHECK_STR" => $TPLMSG['CART_DELETE_CHECK']
                ));
                $tpl->gotoBlock( "INQUIRY_CART_ZONE" );
            }
        }
    }

    function cart_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $p_id=$_REQUEST["p_id"];
        if(!empty($p_id)){
            unset($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"][$p_id]);
            unset($_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$p_id]);
        }
        if(count($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"])){
            header("location:".$_SERVER['PHP_SELF']);
            die();
        }else{
            header("location:products.htm");
            die();
        }
    }  
    function cart_full_del($via_ajax){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        unset($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"]);
        unset($_SESSION[$cms_cfg['sess_cookie_name']]["amount"]);
        if(!$via_ajax){
            header("location:products.htm");
            die();
        }
    }  
    function cart_modify($via_ajax){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if(!empty($_REQUEST["shop_value"])){
            foreach($_REQUEST["shop_value"] as $key =>$value){
                    $_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$key]=intval($value);
            }
        }
        if(!$via_ajax){
            $this->cart_list();
        }else{
            $res['code'] = 1;
            $res = array_merge($res,$this->checkout($_POST['shipment_type'],0));
            echo json_encode($res);
        }
    }
    function cart_finish(){
        global $db,$tpl,$TPLMSG,$ws_array,$shopping,$inquiry,$cms_cfg,$main;
        $tmpForm = App::getHelper('session')->tmpForm;
        //載入購物車列表
        $this->cart_list();
        //顯示表單資料
        $tpl->newBlock( "MEMBER_DATA_FORM" );        
        $tpl->assignGlobal( array(
            "MSG_MODE"  => $TPLMSG['SEND'],
            "MSG_MEMBER_NAME"  => $TPLMSG['MEMBER_NAME'],
            "MSG_CONTACT_PERSON" =>$TPLMSG['CONTACT_PERSON'],
            "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
            "MSG_ZIP" => $TPLMSG["ZIP"],
            "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
            "MSG_TEL" => $TPLMSG["TEL"],
            "MSG_FAX" => $TPLMSG["FAX"],
            "MSG_EMAIL" => $TPLMSG["EMAIL"],
            "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"]
        ));
        if($cms_cfg['ws_module']['ws_delivery_timesec']){
            $tpl->newBlock("TIME_SEC_ZONE");
            //配送時段
            App::getHelper('main')->multiple_radio("deliver_timesec",$ws_array["deliery_timesec"],'',$tpl);
        }
        //會員區資訊
        if($_POST['shop_and_register']){
            $tpl->newBlock("REGISTER_INFO");
            $tpl->assign(array(
                //第一次註冊
                "MSG_ACCOUNT"        => $TPLMSG["LOGIN_ACCOUNT"],
                "MSG_PASSWORD"       => $TPLMSG["LOGIN_PASSWORD"],
                "MSG_VALID_PASSWORD" => $TPLMSG['MEMBER_CHECK_PASSWORD'],
                "MSG_MEMBER_NAME"    => $TPLMSG['MEMBER_NAME'],
                "MSG_CONTACT_PERSON" =>$TPLMSG['CONTACT_PERSON'],       
                "VALUE_M_NAME"       => $row["m_name"],
                "VALUE_M_CONTACT_S"  => $row["m_contact_s"],   
                "VALUE_M_EMAIL"      => $row["m_email"],
            ));
        }else{
            $tpl->newBlock("NORMAL_INFO");
        }           
        if($this->m_id){
            $sql="select * from ".$cms_cfg['tb_prefix']."_member where m_id='".$this->m_id."'";
            $selectrs = $db->query($sql);
            $memRow = $db->fetch_array($selectrs,1);
            $tpl->assignGlobal( array( 
                 "VALUE_M_NAME" => sprintf($TPLMSG["MEMBER_NAME_IN_CART_TEMPLATE"],$memRow["m_fname"],$memRow["m_lname"]),
                 "VALUE_M_COMPANY_NAME" => $memRow["m_company_name"],
                 "VALUE_M_ZIP" => $memRow["m_zip"],
                 "VALUE_M_ADDRESS" => $memRow["m_address"],
                 "VALUE_M_TEL" => $memRow["m_tel"],
                 "VALUE_M_FAX" => $memRow["m_fax"],
                 "VALUE_M_EMAIL" => $memRow["m_email"],
                 "VALUE_M_CELLPHONE" => $memRow["m_cellphone"]
            ));
        }
        if($tmpForm){
            $tpl->assignGlobal(array(
                 "VALUE_M_COMPANY_NAME" => $tmpForm["m_company_name"],                
                 "VALUE_M_VAT_NUMBER" => $tmpForm["m_vat_number"],
                 "VALUE_CONTENT" => $tmpForm["content"],
            ));
        }
        //國家下拉選單
        if($cms_cfg["ws_module"]["ws_country"]==1) {
            $main->country_select($row["m_country"]);
        }
        //稱謂下拉選單
        $ordererField = new ContactfieldWithCourtesyTitle(array(
            'view'      => 'orderer',
            'blockName' => 'Orderer',
            'fieldData' => array(
                'contact' => array(
                    'name' => sprintf($TPLMSG["MEMBER_NAME_IN_CART_TEMPLATE"],$memRow['m_fname'],$memRow['m_lname']),
                ),
                'courtesyTitle' => $memRow['m_contact_s'],
            ),
        ));
        $tpl->assignGlobal("TAG_CONTACT_WITH_S",$ordererField->get_html());
        $receiveField = new ContactfieldWithCourtesyTitle(array(
            'view'      => 'orderreceiver',
            'blockName' => 'Receiver',
        ));
        $tpl->assignGlobal("TAG_CONTACTR_WITH_S",$receiveField->get_html());
        //地址欄位格式
        if($cms_cfg['ws_module']['ws_address_type']=='tw'){
            $tpl->newBlock("TW_ADDRESS");
        }else{
            $tpl->newBlock("SINGLE_ADDRESS");
        }
        if(!empty($shopping)){
            //收件者地址欄位格式
            if($cms_cfg['ws_module']['ws_address_type']=='tw'){
                $tpl->newBlock("TW_ADDRESS_RECI");
            }else{
                $tpl->newBlock("SINGLE_ADDRESS_RECI");
            }              
            //運送區域
            $tpl->assignGlobal("VALUE_SHIPMENT_TYPE",$_SESSION[$cms_cfg['sess_cookie_name']]["shipment_type"]);
            $tpl->assignGlobal("VALUE_SHIPMENT_ZONE",$ws_array["shippment_type"][$_SESSION[$cms_cfg['sess_cookie_name']]["shipment_type"]]);
            if($_POST['shipment_type']==3){
                $tpl->assignGlobal("VALUE_SHIPPING_PRICE_STR",$TPLMSG["ALI_SHIP_MSG"]);
            }
            //顯示付款方式
            $tpl->newBlock("PAYMENT_TYPE");
            $tpl->assign("MSG_PAYMENT_TYPE" , $TPLMSG["PAYMENT_TYPE"]);
            $tpl->assign("MSG_PAYMENT_ATM" , $TPLMSG["PAYMENT_ATM"]);
            foreach($ws_array["payment_type"] as $i => $v){
                $tpl->newBlock("PAYMENT_TYPE_ITEMS");
                $tpl->assign("VALUE_PAYMENT_TYPE_ID" , $i);
                $tpl->assign("VALUE_PAYMENT_TYPE" , $v);
            }
            $tpl->gotoBlock("PAYMENT_TYPE");
            $tpl->gotoBlock("MEMBER_DATA_FORM");
            //發票類型
            $main->multiple_radio("invoice",$ws_array['invoice_type'],$tmpForm['o_invoice_type']?$tmpForm['o_invoice_type']:2,$tpl);
            //付款說明
            $this->load_term($tpl);
        }    
    }
    //載入付款說明
    function load_term($tpl){
        $db = App::getHelper('db');
        $sql="select st_payment_term,st_shopping_term from ".$db->prefix("service_term")."  where st_id='1'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $row = $db->fetch_array($selectrs,1);
        $payment_term=trim($row["st_payment_term"]);
        if(!empty($payment_term)){
            $tpl->assignGlobal("MSG_PAYMENT_TERM",$row["st_payment_term"]);
            $tpl->assignGlobal("MSG_SHOPPING_TERM",$row["st_shopping_term"]);
        }        
    }
    //預覽訂單
    function cart_preview(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$shopping,$inquiry,$main,$ws_array;
        App::getHelper('session')->tmpForm = $_POST;
        $this->cart_list();
        $tpl->newBlock( "MEMBER_DATA_FORM" );
        $tpl->assign( array("MSG_MEMBER_NAME"  => $TPLMSG['MEMBER_NAME'],
                            "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                            "MSG_ZIP" => $TPLMSG["ZIP"],
                            "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                            "MSG_TEL" => $TPLMSG["TEL"],
                            "MSG_FAX" => $TPLMSG["FAX"],
                            "MSG_EMAIL" => $TPLMSG["EMAIL"],
                            "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
                            "VALUE_M_COMPANY_NAME" => $_REQUEST["m_company_name"],
                            "VALUE_M_VAT_NUMBER" => $_REQUEST["m_vat_number"],
                            "VALUE_M_INVOICE_TYPE" => $ws_array['invoice_type'][$_REQUEST['o_invoice_type']],
                            "VALUE_M_ZIP" => $_REQUEST["m_zip"],
                            "VALUE_M_ADDRESS" => $_REQUEST["m_city"].$_REQUEST["m_area"].$_REQUEST["m_address"],
                            "VALUE_M_TEL" => $_REQUEST["m_tel"],
                            "VALUE_M_FAX" => $_REQUEST["m_fax"],
                            "VALUE_M_EMAIL" => $_REQUEST["m_email"],
                            "VALUE_M_CELLPHONE" => $_REQUEST["m_cellphone"],
                            "VALUE_M_RECI_CONTACT_S" => $_REQUEST["m_reci_contact_s"],
                            "VALUE_M_RECI_NAME" => $_REQUEST["m_reci_name"],
                            "VALUE_M_RECI_ZIP" => $_REQUEST["m_reci_zip"],
                            "VALUE_M_RECI_ADDRESS" => $_REQUEST["m_reci_city"].$_REQUEST["m_reci_area"].$_REQUEST["m_reci_address"],
                            "VALUE_M_RECI_TEL" => $_REQUEST["m_reci_tel"],
                            "VALUE_M_RECI_EMAIL" => $_REQUEST["m_reci_email"],
                            "VALUE_M_RECI_CELLPHONE" => $_REQUEST["m_reci_cellphone"],
                            "VALUE_CONTENT" => nl2br($_REQUEST["content"]),
                            "VALUE_O_ID" => $this->o_id,
                            "VALUE_SHIPPMENT_TYPE" => $ws_array["shippment_type"][$_SESSION[$cms_cfg['sess_cookie_name']]["shipment_type"]],
                            "VALUE_O_INVOICE_TYPE" => $ws_array['invoice_type'][$_REQUEST['o_invoice_type']],
        ));
        //訂購人
        if($cms_cfg['ws_module']['ws_contactus_s_style']==1){//西式稱謂
            $tpl->newBlock("CART_S_STYLE_1");
        }elseif($cms_cfg['ws_module']['ws_contactus_s_style']==2){//中式稱謂
            $tpl->newBlock("CART_S_STYLE_2");
        }
        $tpl->assign(array(
            "MSG_CONTACT_PERSON" => $TPLMSG['CONTACT_PERSON'],     
            "VALUE_M_NAME"       => $_REQUEST["m_name"],    
            "VALUE_M_CONTACT_S"  => $ws_array["contactus_s"][$_REQUEST["m_contact_s"]],                    
        ));        
        //收件人
        if($cms_cfg['ws_module']['ws_contactus_s_style']==1){//西式稱謂
            $tpl->newBlock("RECI_CART_S_STYLE_1");
        }elseif($cms_cfg['ws_module']['ws_contactus_s_style']==2){//中式稱謂
            $tpl->newBlock("RECI_CART_S_STYLE_2");
        }
        $tpl->assign(array(
            "MSG_CONTACT_PERSON" => $TPLMSG['CONTACT_PERSON'],     
            "VALUE_M_NAME"       => $_REQUEST["m_reci_name"],    
            "VALUE_M_CONTACT_S"  => $ws_array["contactus_s"][$_REQUEST["m_reci_contact_s"]],                    
        ));        
        //是否顯示配送欄位
        if($cms_cfg['ws_module']['ws_delivery_timesec']){
            $tpl->newBlock("DELIVERY_ZONE");
            $dt_key = $_POST['o_deliver_time_sec'];
            $tpl->assign(array(
               "VALUE_M_DELIVER_DATE"    => $_POST['o_deliver_date'], 
               "VALUE_M_DELIVER_TIMESEC" => $ws_array["deliery_timesec"][$dt_key], 
            ));
        }
        //國家欄位
        if($cms_cfg["ws_module"]["ws_country"]==1) {
            $tpl->newBlock("MEMBER_DATA_COUNTRY_ZONE");
            $tpl->assign(array("MSG_COUNTRY" =>$TPLMSG['COUNTRY'],
                               "VALUE_M_COUNTRY" =>$_REQUEST["m_country"]
            ));
        }
        if(!empty($shopping)){
            //結帳，計算訂單金額
            $billList = $this->checkout($_SESSION[$cms_cfg['sess_cookie_name']]["shipment_type"],$_REQUEST["o_payment_type"]);
            //手續費
            if($billList['charge_fee']){   
                $tpl->newBlock("CHARGE_FEE_ROW");
                $tpl->assign("VALUE_CHARGE_FEE",$billList['charge_fee']);
            }           
            $ts = time();
            $tpl->gotoBlock("SHOPPING_CART_ZONE");            
            $tpl->assign("VALUE_TOTAL",$billList['total_price']);
            //$this->o_id=$db->get_insert_id();
            //產生ATM虛擬帳號
            if($cms_cfg["ws_module"]["ws_vaccount"]==1 & $TPLMSG["PAYMENT_ATM"]==$_REQUEST["o_payment_type"]) {
            $v_account = $this->get_vaccount($_REQUEST["o_subtotal_price"]);
            //在確認信中加入虛擬帳號
            $tpl->newBlock("VIRTUAL_ACCOUNT");
            $tpl->assignGlobal( array("MSG_TRANSFER_BANK" => $TPLMSG['TRANSFER_BANK'],
                                      "VALUE_TRANSFER_BANK_CODE" => $TPLMSG['TRANSFER_BANK_CODE'],
                                      "MSG_TRANSFER_ACCOUNT" => $TPLMSG['TRANSFER_ACCOUNT'],
                                      "VALUE_VIRTUAL_ACCOUNT" => $v_account ));
            }
            //顯示付款方式
            $tpl->newBlock("PAYMENT_TYPE");
            $tpl->assign("MSG_PAYMENT_TYPE" , $TPLMSG["PAYMENT_TYPE"]);
            $tpl->assign("VALUE_PAYMENT_TYPE" , $main->multi_map_value($ws_array["payment_type"],$_REQUEST["o_payment_type"]));
            if($_REQUEST["o_payment_type"]==1){ //ATM轉帳
                $tpl->newBlock("ATM_LAST_FIVE");
                $tpl->assign("VALUE_ATM_LAST5",$_REQUEST["o_atm_last5"]);
            }            
            $tpl->gotoBlock( "MEMBER_DATA_FORM" );
            $tpl->assignGlobal("VALUE_VIRTUAL_ACCOUNT" , $v_account);
            
            //輸出post暫存
            foreach ($_POST as $k => $v) {
                    $tpl -> newBlock("TMP_POST_FIELD");
                    $tpl -> assign(array("TAG_POST_KEY" => $k, "TAG_POST_VALUE" => $v, ));
            }            

        }
    }    
    //資料更新================================================================
    function cart_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$shopping,$inquiry,$main,$ws_array;
//        $this->ws_tpl_file = "templates/mail/cart".$this->cart_type."-finish.html";
//        $tpl = new TemplatePower( $this->ws_tpl_file );
//        $tpl->prepare();
//        $tpl->assignGlobal("TAG_BASE_CSS", $cms_cfg['base_mail_css']);
        $mail_tpl = array("inquiry",'shopping');
        $tpl = App::getHelper('main')->get_mail_tpl($mail_tpl[$this->cart_type]);
        $this->cart_list();
        $tpl->newBlock( "MEMBER_DATA_FORM" );
        $tpl->assign( array("MSG_MEMBER_NAME"  => $TPLMSG['MEMBER_NAME'],
                            "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                            "MSG_ZIP" => $TPLMSG["ZIP"],
                            "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                            "MSG_TEL" => $TPLMSG["TEL"],
                            "MSG_FAX" => $TPLMSG["FAX"],
                            "MSG_EMAIL" => $TPLMSG["EMAIL"],
                            "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
                            "VALUE_M_COMPANY_NAME" => $_REQUEST["m_company_name"],
                            "VALUE_M_VAT_NUMBER" => $_REQUEST["m_vat_number"],
                            "VALUE_M_INVOICE_TYPE" => $ws_array['invoice_type'][$_REQUEST['o_invoice_type']],
                            "VALUE_M_ZIP" => $_REQUEST["m_zip"],
                            "VALUE_M_ADDRESS" => $_REQUEST["m_city"].$_REQUEST["m_area"].$_REQUEST["m_address"],
                            "VALUE_M_TEL" => $_REQUEST["m_tel"],
                            "VALUE_M_FAX" => $_REQUEST["m_fax"],
                            "VALUE_M_EMAIL" => $_REQUEST["m_email"],
                            "VALUE_M_CELLPHONE" => $_REQUEST["m_cellphone"],
                            "VALUE_M_RECI_CONTACT_S" => $_REQUEST["m_reci_contact_s"],
                            "VALUE_M_RECI_NAME" => $_REQUEST["m_reci_name"],
                            "VALUE_M_RECI_ZIP" => $_REQUEST["m_reci_zip"],
                            "VALUE_M_RECI_ADDRESS" => $_REQUEST["m_reci_city"].$_REQUEST["m_reci_area"].$_REQUEST["m_reci_address"],
                            "VALUE_M_RECI_TEL" => $_REQUEST["m_reci_tel"],
                            "VALUE_M_RECI_EMAIL" => $_REQUEST["m_reci_email"],
                            "VALUE_M_RECI_CELLPHONE" => $_REQUEST["m_reci_cellphone"],
                            "VALUE_CONTENT" => nl2br($_REQUEST["content"]),
                            "VALUE_O_INVOICE_TYPE" => $ws_array['invoice_type'][$_REQUEST['o_invoice_type']],
        ));
        //訂購人
        if($cms_cfg['ws_module']['ws_contactus_s_style']==1){//西式稱謂
            $tpl->newBlock("CART_S_STYLE_1");
        }elseif($cms_cfg['ws_module']['ws_contactus_s_style']==2){//中式稱謂
            $tpl->newBlock("CART_S_STYLE_2");
        }
        $tpl->assign(array(
            "MSG_CONTACT_PERSON" => $TPLMSG['CONTACT_PERSON'],     
            "VALUE_M_NAME"       => $_REQUEST["m_name"],    
            "VALUE_M_CONTACT_S"  => $ws_array["contactus_s"][$_REQUEST["m_contact_s"]],                    
        ));        
        //收件人
        if($cms_cfg['ws_module']['ws_contactus_s_style']==1){//西式稱謂
            $tpl->newBlock("RECI_CART_S_STYLE_1");
        }elseif($cms_cfg['ws_module']['ws_contactus_s_style']==2){//中式稱謂
            $tpl->newBlock("RECI_CART_S_STYLE_2");
        }
        $tpl->assign(array(
            "MSG_CONTACT_PERSON" => $TPLMSG['CONTACT_PERSON'],     
            "VALUE_M_NAME"       => $_REQUEST["m_reci_name"],    
            "VALUE_M_CONTACT_S"  => $ws_array["contactus_s"][$_REQUEST["m_reci_contact_s"]],                    
        ));        
        //是否顯示配送欄位
        if($cms_cfg['ws_module']['ws_delivery_timesec']){
            $tpl->newBlock("DELIVERY_ZONE");
            $dt_key = $_POST['o_deliver_time_sec'];
            $tpl->assign(array(
               "VALUE_M_DELIVER_DATE"    => $_POST['o_deliver_date'], 
               "VALUE_M_DELIVER_TIMESEC" => $ws_array["deliery_timesec"][$dt_key], 
            ));
        }
        //國家欄位
        if($cms_cfg["ws_module"]["ws_country"]==1) {
            $tpl->newBlock("MEMBER_DATA_COUNTRY_ZONE");
            $tpl->assign(array("MSG_COUNTRY" =>$TPLMSG['COUNTRY'],
                               "VALUE_M_COUNTRY" =>$_REQUEST["m_country"]
            ));
        }
        //如果是註冊會員,新增一筆會員資料
        if($_POST['reg_mem'] && !empty($shopping)){
            $main->check_duplicate_member_account($_REQUEST["m_email"]);            
            $sql="
                insert into ".$cms_cfg['tb_prefix']."_member (
                    mc_id,
                    m_status,
                    m_sort,
                    m_account,
                    m_password,
                    m_modifydate,
                    m_company_name,
                    m_contact_s,
                    m_fname,
                    m_birthday,
                    m_sex,
                    m_country,
                    m_zip,
                    m_address,
                    m_tel,
                    m_fax,
                    m_cellphone,
                    m_email,
                    m_epaper_status
                ) values (
                    '1',
                    '1',
                    '".$_REQUEST["m_sort"]."',
                    '".$_REQUEST["m_email"]."',
                    '".$_REQUEST["m_password"]."',
                    '".date("Y-m-d H:i:s")."',
                    '".$_REQUEST["m_company_name"]."',
                    '".$_REQUEST["m_contact_s"]."',
                    '".$_REQUEST["m_name"]."',
                    '".$_REQUEST["m_birthday"]."',
                    '".$_REQUEST["m_sex"]."',
                    '".$_REQUEST["m_country"]."',
                    '".$_REQUEST["m_zip"]."',
                    '".$_REQUEST["m_address"]."',
                    '".$_REQUEST["m_tel"]."',
                    '".$_REQUEST["m_fax"]."',
                    '".$_REQUEST["m_cellphone"]."',
                    '".$_REQUEST["m_email"]."',
                    '".$_REQUEST["m_epaper_status"]."'
                )";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $this->m_id=$db->get_insert_id();
            }else{
                $this->m_id=0;
            }
        }
        if(!empty($shopping)){
            //寫入訂單
            ////取得訂單號碼
            $oid=$this->get_oid();
            //結帳，計算訂單金額
            $billList = $this->checkout(App::getHelper('session')->shipment_type,$_POST['o_payment_type']);
            //手續費
            if($billList['charge_fee']){    
                $tpl->newBlock("CHARGE_FEE_ROW");
                $tpl->assign("VALUE_CHARGE_FEE",$billList['charge_fee']);
            }
            $tpl->gotoBlock( "MEMBER_DATA_FORM" );
            $tpl->assign(array(
                "VALUE_O_ID" => $oid,
                "VALUE_SHIPPMENT_TYPE" => $ws_array["shippment_type"][$_SESSION[$cms_cfg['sess_cookie_name']]["shipment_type"]],                
            ));
            $ts = time();
            $tpl->gotoBlock("SHOPPING_CART_ZONE");            
            $tpl->assign("VALUE_TOTAL",$billList['total_price']);            
            $sql="
                insert into ".$cms_cfg['tb_prefix']."_order (
                    m_id,
                    o_id,
                    o_status,
                    o_createdate,
                    o_modifydate,
                    o_account,
                    o_company_name,
                    o_vat_number,
                    o_country,
                    o_contact_s,
                    o_name,
                    o_zip,
                    o_city,
                    o_area,
                    o_address,
                    o_tel,
                    o_fax,
                    o_cellphone,
                    o_email,
                    o_reci_contact_s,
                    o_reci_name,
                    o_reci_zip,
                    o_reci_city,
                    o_reci_area,
                    o_reci_address,
                    o_reci_tel,
                    o_reci_cellphone,
                    o_reci_email,
                    o_plus_price,
                    o_charge_fee,
                    o_subtotal_price,
                    o_minus_price,
                    o_total_price,
                    o_content,
                    o_shippment_type,
                    o_payment_type,
                    o_invoice_type,
                    o_atm_last5,
                    o_deliver_date,
                    o_deliver_time_sec
                ) values (
                    '".$this->m_id."',
                    '".$oid."',    
                    '0',
                    '".date("Y-m-d H:i:s",$ts)."',
                    '".date("Y-m-d H:i:s",$ts)."',
                    '".$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ACCOUNT"]."',
                    '".$_REQUEST["m_company_name"]."',
                    '".$_REQUEST["m_vat_number"]."',
                    '".$_REQUEST["m_country"]."',
                    '".$_REQUEST["m_contact_s"]."',
                    '".$_REQUEST["m_name"]."',
                    '".$_REQUEST["m_zip"]."',
                    '".$_REQUEST["m_city"]."',
                    '".$_REQUEST["m_area"]."',
                    '".$_REQUEST["m_address"]."',
                    '".$_REQUEST["m_tel"]."',
                    '".$_REQUEST["m_fax"]."',
                    '".$_REQUEST["m_cellphone"]."',
                    '".$_REQUEST["m_email"]."',
                    '".$_REQUEST["m_reci_contact_s"]."',
                    '".$_REQUEST["m_reci_name"]."',
                    '".$_REQUEST["m_reci_zip"]."',
                    '".$_REQUEST["m_reci_city"]."',
                    '".$_REQUEST["m_reci_area"]."',
                    '".$_REQUEST["m_reci_address"]."',
                    '".$_REQUEST["m_reci_tel"]."',
                    '".$_REQUEST["m_reci_cellphone"]."',
                    '".$_REQUEST["m_reci_email"]."',
                    '".$billList['shipping_price']."',
                    '".$billList['charge_fee']."',
                    '".$billList['sub_total_price']."',
                    '0',
                    '".$billList['total_price']."',
                    '".$_REQUEST["content"]."',
                    '".$_SESSION[$cms_cfg['sess_cookie_name']]["shipment_type"]."',
                    '".$_REQUEST["o_payment_type"]."',
                    '".$_REQUEST["o_invoice_type"]."',
                    '".$_REQUEST["o_atm_last5"]."',
                    '".$_REQUEST["o_deliver_date"]."',
                    '".$_REQUEST["o_deliver_time_sec"]."'
                )";
            $rs = $db->query($sql,true);
            //$this->o_id=$db->get_insert_id();
            //產生ATM虛擬帳號
            if($cms_cfg["ws_module"]["ws_vaccount"]==1 & $TPLMSG["PAYMENT_ATM"]==$_REQUEST["o_payment_type"]) {
            $v_account = $this->get_vaccount($_REQUEST["o_subtotal_price"]);
            $sql="
                update ".$cms_cfg['tb_prefix']."_order
                    set o_virtual_account='".$v_account."'
                where o_id='".$oid."'";
            $db->query($sql);
            //在確認信中加入虛擬帳號
            $tpl->newBlock("VIRTUAL_ACCOUNT");
            $tpl->assignGlobal( array("MSG_TRANSFER_BANK" => $TPLMSG['TRANSFER_BANK'],
                                      "VALUE_TRANSFER_BANK_CODE" => $TPLMSG['TRANSFER_BANK_CODE'],
                                      "MSG_TRANSFER_ACCOUNT" => $TPLMSG['TRANSFER_ACCOUNT'],
                                      "VALUE_VIRTUAL_ACCOUNT" => $v_account ));
            }
            //寫入購買產品
            for($i=0;$i<count($shopping);$i++){
                $pid=$shopping[$i]["p_id"];
                $pname=$shopping[$i]["p_name"];
                $amount=$_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$pid];
                $price = $shopping[$i]["p_special_price"]?$shopping[$i]["p_special_price"]:$shopping[$i]["p_list_price"];
                if(!empty($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]) && $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]!=100){
                    $special_price=floor($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]/100*$price);
                }else{
                    $special_price=$price;
                }
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_order_items (
                        m_id,
                        o_id,
                        p_id,
                        p_name,
                        p_sell_price,
                        oi_amount
                    ) values (
                        '".$this->m_id."',
                        '".$oid."',
                        '".$pid."',
                        '".$pname."',
                        '".$special_price."',
                        '".$amount."'
                    )";
                $rs = $db->query($sql);
            }
            //顯示付款方式
            $tpl->newBlock("PAYMENT_TYPE");
            $tpl->assign("MSG_PAYMENT_TYPE" , $TPLMSG["PAYMENT_TYPE"]);
            $tpl->assign("VALUE_PAYMENT_TYPE" , $main->multi_map_value($ws_array["payment_type"],$_REQUEST["o_payment_type"]));
            App::getHelper("session")->{paymentType} = $_REQUEST["o_payment_type"];
            if($_REQUEST["o_payment_type"]==1){ //ATM轉帳
                $tpl->newBlock("ATM_LAST_FIVE");
                $tpl->assign("VALUE_ATM_LAST5",$_REQUEST["o_atm_last5"]);
            }            
            $tpl->gotoBlock( "MEMBER_DATA_FORM" );
            $func_str="func=m_zone&mzt=order";
            //寄送訊息
            $sql="select st_order_mail from ".$cms_cfg['tb_prefix']."_service_term where st_id='1'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $tpl->assignGlobal( "VALUE_TERM" , $row['st_order_mail']);
            $tpl->assignGlobal("VALUE_VIRTUAL_ACCOUNT" , $v_account);
            $mail_content=$tpl->getOutputContent();
            App::getHelper('session')->mailContent = $mail_content;
//            if($cms_cfg["ws_module"]["ws_cart_login"]==0){
//                $goto_url=$_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL'];
//            }else{
//                $goto_url=$cms_cfg["base_url"]."shopping-result.php?status=OK&pno=".$oid;
//            }
            $goto_url=$cms_cfg["base_url"]."shopping-result.php?status=OK&pno=".$oid;
            if(3 == $_REQUEST["o_payment_type"]){
                    $mail_header = 1;
            }else{
                    $mail_header = 0;
            }
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                unset($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"]);
                unset($_SESSION[$cms_cfg['sess_cookie_name']]["amount"]);
                unset($_SESSION[$cms_cfg['sess_cookie_name']]["shipment_type"]);
                //$tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                //$goto_url=$cms_cfg["base_url"]."member.php?".$func_str;
                //$this->goto_target_page($goto_url,2);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }            
            header("location:".$goto_url);
            die();
            //$main->ws_mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$_REQUEST["m_email"],$mail_content,$TPLMSG["ORDER_MAIL_TITLE"],"shopping",$goto_url,null,$mail_header);
        }

        if(!empty($inquiry)){
            //寫入詢價單
            $sql="
                insert into ".$cms_cfg['tb_prefix']."_inquiry (
                    m_id,
                    i_status,
                    i_createdate,
                    i_modifydate,
                    i_account,
                    i_company_name,
                    i_country,
                    i_contact_s,
                    i_name,
                    i_zip,
                    i_address,
                    i_tel,
                    i_fax,
                    i_cellphone,
                    i_email,
                    i_content
                ) values (
                    '".$this->m_id."',
                    '0',
                    '".date("Y-m-d H:i:s")."',
                    '".date("Y-m-d H:i:s")."',
                    '".$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ACCOUNT"]."',
                    '".$_REQUEST["m_company_name"]."',
                    '".$_REQUEST["m_country"]."',
                    '".$_REQUEST["m_contact_s"]."',
                    '".$_REQUEST["m_name"]."',
                    '".$_REQUEST["m_zip"]."',
                    '".$_REQUEST["m_address"]."',
                    '".$_REQUEST["m_tel"]."',
                    '".$_REQUEST["m_fax"]."',
                    '".$_REQUEST["m_cellphone"]."',
                    '".$_REQUEST["m_email"]."',
                    '".$_REQUEST["content"]."'
                )";
            $rs = $db->query($sql);
            $this->i_id=$db->get_insert_id();
            //寫入購買產品
            for($i=0;$i<count($inquiry);$i++){
                $pid=$inquiry[$i]["p_id"];
                $pname=$inquiry[$i]["p_name"];
                $amount=$_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$pid];
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_inquiry_items (
                        m_id,
                        i_id,
                        p_id,
                        p_name,
                        ii_amount
                    ) values (
                        '".$this->m_id."',
                        '".$this->i_id."',
                        '".$pid."',
                        '".$pname."',
                        '".$amount."'
                    )";
                $rs = $db->query($sql);
            }
            $func_str="func=m_zone&mzt=inquiry";
            //寄送訊息
            $sql="select st_inquiry_mail from ".$cms_cfg['tb_prefix']."_service_term where st_id='1'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $tpl->assignGlobal( "VALUE_TERM" , $row['st_inquiry_mail']);
            $mail_content=$tpl->getOutputContent();
            if($cms_cfg["ws_module"]["ws_cart_login"]==0){
                $goto_url=$_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL'];
            }else{
                $goto_url=$cms_cfg["base_url"]."member.php?".$func_str;
            }
            $main->ws_mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$_REQUEST["m_email"],$mail_content,$TPLMSG["INQUIRY_MAIL_TITLE"],"inquiry",$goto_url);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                unset($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"]);
                unset($_SESSION[$cms_cfg['sess_cookie_name']]["amount"]);
                unset($_SESSION[$cms_cfg['sess_cookie_name']]["shipment_type"]);
                unset(App::getHelper('session')->tmpForm);//結帳表單暫存
                //$tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                //$goto_url=$cms_cfg["base_url"]."member.php?".$func_str;
                //$this->goto_target_page($goto_url,2);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //顯示訊息並重新導向
    function goto_target_page($url,$sec=2){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }
    //購物說明及運送說明
    function get_terms(){
        global $db,$tpl;
        $sql="select st_shipping_term,st_shopping_term from ws_service_term where ws_id='".$this->ws_id."'";
        $selectrs = $db->query($sql);
          $rsnum    = $db->numRows($selectrs);
          if($rsnum > 0){
              $tpl->assignGlobal( "MSG_SHIPPING_TERM",$row["st_shipping_term"] );
            $tpl->assignGlobal( "MSG_SHOPPING_TERM",$row["st_st_shopping_term"] );
          }
    }

    function get_vaccount($amount="0") {
        global $db;
        //$this->o_id=1;

        //$livetime = time() + (30*24*60*60);
        //$livetime = time() + (60*24*60*60);
        //$livetime = time() + (90*24*60*60);
        $livetime = time() + 8640000; //100天後時間
        $account['code'] = "3254";
        $account['year'] = substr(date("Y" , $livetime), -1); //取西元年最後一位
        $account['date'] = str_pad(date("z", $livetime), 3, "0", STR_PAD_LEFT); //取轉換成萬年歷的日期
        $account['serial'] = str_pad($this->o_id, 6, "0", STR_PAD_LEFT);
        $account['amount'] = str_pad($amount, 10, "0", STR_PAD_LEFT);
        $str = "";
        foreach($account as $key => $data) {
            $str .= $data;
        }
        $multi_str1 = str_pad("", 24, "12");
        $multi_str2 = str_pad("", 24, "137");
        $a = str_split($str);
        $b = str_split($multi_str1);
        $c = str_split($multi_str2);

        for($i=0;$i < count($a);$i++) {
            $amass1[] = $a[$i] * $b[$i];
            $amass2[] = $a[$i] * $c[$i];
        }
        $account['chk1'] = $this->get_chk1($amass1);
        $account['chk2'] = $this->get_chk2($amass2);
        $vaccount = $account['code'].$account['chk1'].$account['chk2'].$account['year'].$account['date'].$account['serial'];
        return $vaccount;
    }

    function get_chk1($amass) {
        $sum = 0;
        foreach($amass as $key => $value){
          if($value >= 10) {
              $sum += (substr($value, -1) + substr($value, -2, 1));
          }else{
              $sum += $value;
          }
        }

        return ($sum >= 10) ? substr($sum, -1) : $sum;
    }

    function get_chk2($amass) {
        $sum = 0;
        foreach($amass as $key => $value){
            $sum += $value;
        }

        return ($sum >= 10) ? substr($sum, -1) : $sum;
    }

    //單一詢問車表單
    function inquiry_form(){
        global $db,$tpl,$TPLMSG,$ws_array,$shopping,$inquiry,$cms_cfg,$main;
        $sql = "select p_id, p_name from ".$cms_cfg['tb_prefix']."_products where p_id=".$_REQUEST['p_id'];
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        //顯示表單資料
        $tpl->newBlock("MEMBER_DATA_FORM");
        $tpl->assign( array("MSG_MODE"  => $TPLMSG['SEND'],
                            "MSG_PRODUCT" => $TPLMSG['PRODUCT'],
                            "MSG_CONTACT_PERSON" =>$TPLMSG['CONTACT_PERSON'],
                            "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                            "MSG_NAME"  => $TPLMSG['MEMBER_NAME'],
                            "MSG_ZIP" => $TPLMSG["ZIP"],
                            "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                            "MSG_TEL" => $TPLMSG["TEL"],
                            "MSG_EMAIL" => $TPLMSG["EMAIL"],
                            "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
                            "MSG_CONTENT" => $TPLMSG["CONTENT"],
                            "VALUE_P_NAME" => $row['p_name']));
    }

    //單一詢問車資料更新
    function inquiry_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $this->ws_tpl_file = "templates/ws-mail-tpl.html";
        $tpl = new TemplatePower( $this->ws_tpl_file );
        $tpl->prepare();
        $tpl->assignGlobal("TAG_BASE_CSS", $cms_cfg['base_mail_css']);
        $tpl->newBlock( "MEMBER_DATA_FORM" );
        $tpl->assign( array("MSG_CONTACT_PERSON" =>$TPLMSG['CONTACT_PERSON'],
                            "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                            "MSG_NAME"  => $TPLMSG['MEMBER_NAME'],
                            "MSG_ZIP" => $TPLMSG["ZIP"],
                            "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                            "MSG_TEL" => $TPLMSG["TEL"],
                            "MSG_EMAIL" => $TPLMSG["EMAIL"],
                            "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
                            "MSG_CONTENT" => $TPLMSG["CONTENT"],
                            "VALUE_M_COMPANY_NAME" => $_REQUEST["m_company_name"],
                            "VALUE_M_CONTACT_S" => $_REQUEST["m_contact_s"],
                            "VALUE_M_NAME" => $_REQUEST["m_name"],
                            "VALUE_M_ZIP" => $_REQUEST["m_zip"],
                            "VALUE_M_ADDRESS" => $_REQUEST["m_address"],
                            "VALUE_M_TEL" => $_REQUEST["m_tel"],
                            "VALUE_M_FAX" => $_REQUEST["m_fax"],
                            "VALUE_M_EMAIL" => $_REQUEST["m_email"],
                            "VALUE_M_CELLPHONE" => $_REQUEST["m_cellphone"],
                            "VALUE_CONTENT" => $_REQUEST["content"]));
        //如果不需要會員登入,新增一筆會員資料
        if($cms_cfg["ws_module"]["ws_cart_login"]==0){
            $sql="
                insert into ".$cms_cfg['tb_prefix']."_member (
                    mc_id,
                    m_status,
                    m_sort,
                    m_modifydate,
                    m_company_name,
                    m_contact_s,
                    m_name,
                    m_birthday,
                    m_sex,
                    m_zip,
                    m_address,
                    m_tel,
                    m_fax,
                    m_cellphone,
                    m_email,
                    m_epaper_status
                ) values (
                    '0',
                    '0',
                    '".$_REQUEST["m_sort"]."',
                    '".date("Y-m-d H:i:s")."',
                    '".$_REQUEST["m_company_name"]."',
                    '".$_REQUEST["m_contact_s"]."',
                    '".$_REQUEST["m_name"]."',
                    '".$_REQUEST["m_birthday"]."',
                    '".$_REQUEST["m_sex"]."',
                    '".$_REQUEST["m_zip"]."',
                    '".$_REQUEST["m_address"]."',
                    '".$_REQUEST["m_tel"]."',
                    '".$_REQUEST["m_fax"]."',
                    '".$_REQUEST["m_cellphone"]."',
                    '".$_REQUEST["m_email"]."',
                    '".$_REQUEST["m_epaper_status"]."'
                )";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $this->m_id=$db->get_insert_id();
            }else{
                $this->m_id=0;
            }
        }

        //寫入詢價單
        $sql="
            insert into ".$cms_cfg['tb_prefix']."_inquiry (
                m_id,
                i_status,
                i_createdate,
                i_modifydate,
                i_account,
                i_company_name,
                i_contact_s,
                i_name,
                i_zip,
                i_address,
                i_tel,
                i_fax,
                i_cellphone,
                i_email,
                i_content
            ) values (
                '".$this->m_id."',
                '0',
                '".date("Y-m-d H:i:s")."',
                '".date("Y-m-d H:i:s")."',
                '".$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ACCOUNT"]."',
                '".$_REQUEST["m_company_name"]."',
                '".$_REQUEST["m_contact_s"]."',
                '".$_REQUEST["m_name"]."',
                '".$_REQUEST["m_zip"]."',
                '".$_REQUEST["m_address"]."',
                '".$_REQUEST["m_tel"]."',
                '".$_REQUEST["m_fax"]."',
                '".$_REQUEST["m_cellphone"]."',
                '".$_REQUEST["m_email"]."',
                '".$_REQUEST["content"]."'
            )";
        $rs = $db->query($sql);
        $this->i_id=$db->get_insert_id();

        //寫入購買產品
        $pid=$_REQUEST["p_id"];
        $pname=$_REQUEST["p_name"];
        $amount=$_REQUEST["ii_amount"];
        $sql="
            insert into ".$cms_cfg['tb_prefix']."_inquiry_items (
                m_id,
                i_id,
                p_id,
                p_name,
                ii_amount
            ) values (
                '".$this->m_id."',
                '".$this->i_id."',
                '".$pid."',
                '".$pname."',
                '".$amount."'
            )";
        $rs = $db->query($sql);

        //寄送訊息
        $sql="select st_inquiry_mail from ".$cms_cfg['tb_prefix']."_service_term where st_id='1'";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $goto_url = $cms_cfg["base_url"]."cart.php?func=s_inquiry";
        $tpl->assignGlobal( "VALUE_TERM" , $row['st_inquiry_mail']);
        $mail_content=$tpl->getOutputContent();

        $db_msg = $db->report();
        $this->ws_tpl_file = "templates/ws-single-inquiry-tpl.html";
        $tpl = new TemplatePower($this->ws_tpl_file);
        $tpl->prepare();
        if ( $db_msg == "" ) {
            $main->ws_mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$_REQUEST["m_email"],$mail_content,$TPLMSG["INQUIRY_MAIL_TITLE"],"inquiry",$goto_url);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
        $tpl->printToScreen();
    }
    function member_login(){
        global $main,$ws_array,$cms_cfg,$tpl,$TPLMSG;
        $main->layer_link($ws_array["cart_type"][$_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]]);  
        $tpl->assignGlobal("TAG_MAIN_FUNC",$TPLMSG['MEMBER_LOGIN']);        
        $tpl->assignGlobal("TAG_RETURN_URL",$_SERVER['REQUEST_URI']);
        $tpl->assignGlobal("TAG_FS_SHOPPING",$TPLMSG['FIRST_TIME_SHOPPING']);
    }    
    //取得最新訂單號碼
    function get_oid(){
        global $db,$cms_cfg;
        $today_str = date("Ymd");
        $pattern = $today_str."([0-9]{4})";
        $sql = "SELECT o_id FROM `".$cms_cfg['tb_prefix']."_order` where  o_id  regexp '".$pattern."' order by o_id desc limit 1";
        $row = $db->query_firstrow($sql);
        if($row){
            preg_match(sprintf("/%s/i",$pattern), $row['o_id'], $matches);
            $serial = intval($matches[1])+1;
        }else{
            $serial = 1;
        }
        $new_o_id = sprintf("%s%04d",date("Ymd"),$serial);
        return $new_o_id;
    }    
    function checkout($shipmentType,$paymentType){
        global $cms_cfg,$db;
        //取得購物車的產品id
        $pid_array=array_keys($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"]);     
        $pid_array_str = implode(',',$pid_array);
        $sql="select p.pc_id,p.p_id,p.p_name,p.p_name_alias,p.p_serial,p.p_small_img,p.p_list_price,p.p_special_price,p.p_seo_filename,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p.p_id in (".$pid_array_str.") ";
        $selectrs = $db->query($sql);   
        $return['sub_total_price'] = 0;//訂單小計
        $return['total_price'] = 0;//訂單總價
        $return['charge_fee'] = 0;//手續費
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $pid=$row['p_id'];
            $amount=$_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$pid];        
            $base_price = $row["p_special_price"]?$row["p_special_price"]:$row["p_list_price"];
            if(!empty($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]) && $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]!=100){
                $special_price=floor($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]/100*$base_price);
            }else{
                $special_price=$base_price;
            }
            $return['sub_total_price'] += $special_price * $amount;
        }
        //計算運費
        if(App::getHelper ('session')->advance_ship_price){
            $return['shipping_price'] = -1;
        }else{
            $return['shipping_price'] = $this->shipping_price($return['sub_total_price'],$shipmentType);
        }
        //計算手續費
        if($paymentType==2){
            $return['charge_fee'] = $this->service_fee($return['sub_total_price']);   
        }
        //計算訂單總價
        $return['total_price'] = $return['sub_total_price'] + ($return['shipping_price']<0?0:$return['shipping_price']) + $return['charge_fee'];
        return $return;
    }        
    function shipping_price($price, $ship_zone) {
        return Model_Shipprice::calculate($price, $ship_zone);
    }  
    function service_fee($price=null){
        return Model_Chargefee::calculate($price);
    }   
    //動態取得運費
    function ajax_show_ship_price(){
        if(App::getHelper('request')->isAjax()){
            $res['code'] = 1;
            $res = array_merge($res , $this->checkout($_POST['shipment_type'],0) );
            echo json_encode($res);
        }
    }
    //動態取得手續費
    function ajax_get_charge_fee(){
        if(App::getHelper('request')->isAjax()){
            $res['code'] = 1;
            $res = array_merge($res, $this->checkout(App::getHelper('session')->shipment_type,$_POST['payment_type']));
            echo json_encode($res);
        }
    }
}

class CART_WITH_SERIAL extends CART{
    function CART_WITH_SERIAL(){
        global $db,$cms_cfg,$tpl,$main;
        $this->m_id =$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ID"];
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        switch($_REQUEST["func"]){
            case "c_list"://購物車列表
                $this->ws_tpl_file = "templates/ws-cart-serial-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_POP_IMG");
                $this->cart_list();
                $this->ws_tpl_type=1;
                break;
            case "c_add"://新增購物項目
                $this->ws_tpl_file = "templates/ws-cart-serial-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_POP_IMG");
                $this->cart_add();
                $this->ws_tpl_type=1;
                break;
            case "c_list_add"://新增購物項目(產品列表)
                $this->ws_tpl_file = "templates/ws-cart-serial-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->cart_list_add();
                $this->ws_tpl_type=1;
                break;
            case "c_quick_add"://快速購物項目
                $this->ws_tpl_file = "templates/ws-cart-serial-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_POP_IMG");
                $this->cart_quick_add();
                $this->ws_tpl_type=1;
                break;
            case "c_mod"://購物車列表
                $this->ws_tpl_file = "templates/ws-cart-serial-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_POP_IMG");
                $this->cart_modify();
                $this->ws_tpl_type=0;
                break;
            case "c_del"://刪除購物項目
                $this->ws_tpl_file = "templates/ws-cart-serial-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_POP_IMG");
                $this->cart_del();
                $this->ws_tpl_type=0;
                break;
            case "c_finish"://結帳
                $this->ws_tpl_file = "templates/ws-cart-serial-finish-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                //$tpl->newBlock("JS_POP_IMG");
                $this->cart_finish();
                $this->ws_tpl_type=1;
                break;
            case "c_replace"://存成訂單或詢價單
                $this->cart_replace();
                break;
            case "s_inquiry": //單一詢問車
                $this->ws_tpl_file = "templates/ws-single-inquiry-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                //$tpl->newBlock("JS_POP_IMG");
                $this->inquiry_form();
                $this->ws_tpl_type=1;
                break;
            case "s_inquiry_replace": //存成單一詢價單
                $this->inquiry_replace();
                break;
            default: //購物車列表
                $this->ws_tpl_file = "templates/ws-cart-serial-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_POP_IMG");
                $this->cart_list();
                $this->ws_tpl_type=1;
                break;
        }
        if($this->ws_tpl_type){
            //$this->new_products_list();   //最新產品
            //$this->hot_products_list();   //熱門產品
            //$this->promotion_products_list(); //促銷產品
            $tpl->printToScreen();
        }
    }

    function cart_add(){
        global $cms_cfg;
        $_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL']=$_SERVER['HTTP_REFERER'];
        $amount_arr = is_array($_REQUEST["amount"])?$_REQUEST["amount"]:(array)$_REQUEST["amount"];
        $p_id_arr = is_array($_REQUEST["p_id"])?$_REQUEST["p_id"]:(array)$_REQUEST["p_id"];
        $p_serial_arr = is_array($_REQUEST["p_serial"])?$_REQUEST["p_serial"]:(array)$_REQUEST["p_serial"];
        foreach($p_id_arr as $k => $p_id){
            $amount = $amount_arr[$k];
            $_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"][$p_id]=1;
            if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$p_id])){
                if(isset($p_serial_arr[$k])){
                    $_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$p_id][$p_serial_arr[$k]]=$amount;
                }else{
                    $_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$p_id]=$amount;
                }
            }else{
                if(isset($p_serial_arr[$k])){
                    $_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$p_id][$p_serial_arr[$k]]+=$amount;
                }else{
                    $_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$p_id]+=$amount;
                }
            }            
        }
        $this->cart_list();
    }


    function cart_list(){
        global $db,$tpl,$TPLMSG,$ws_array,$shopping,$inquiry,$cms_cfg;
        //取得目前的 cart type
        $sql="select sc_cart_type from ".$cms_cfg['tb_prefix']."_system_config where sc_id='1'";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]=($row["sc_cart_type"]=="")?0:$row["sc_cart_type"];
        //欄位名稱
        $tpl->assignGlobal( array("MSG_NAME"  => $TPLMSG['MEMBER_NAME'],
                                  "MSG_CONTENT"  => $TPLMSG['CONTENT'],
                                  "MSG_MODIFY" => $TPLMSG['MODIFY'],
                                  "MSG_DEL" => $TPLMSG['DEL'],
                                  "MSG_TOTAL" => $TPLMSG['CART_TOTAL'],
                                  "MSG_SUBTOTAL" => $TPLMSG['CART_SUBTOTAL'],
                                  "MSG_AMOUNT" => $TPLMSG['CART_AMOUNT'],
                                  "MSG_PRODUCT" => $TPLMSG['PRODUCT'],
                                  "MSG_SERIAL" => $TPLMSG['PRODUCT_SERIAL'],
                                  "MSG_PRODUCT_IMAGE" => $TPLMSG['PRODUCT_IMG'],
                                  "VALUE_MODIFY_AMOUNT" => $TPLMSG['CART_MODIFY_AMOUNT'],
                                  //"CART_IMG_TITLE"=> $ws_array["cart_img"][$_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]]["title_img"],
                                  //"CART_IMG_SUB"=> $ws_array["cart_img"][$_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]]["sub_img"],
        ));
        if(!empty($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"])){
            foreach($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"] as $key => $value){
                $pid_array[]=$key;
            }
        }
        if(!empty($pid_array)){
            $pid_array_str="(".implode(",",$pid_array).")";
            //$sql="select p_id,p_name,p_special_price,p_type,p_show_price,p_small_img,p_seo_filename from ".$cms_cfg['tb_prefix']."_products where p_id in ".$pid_array_str." ";
            $sql="select p.pc_id,p.p_id,p.p_name,p.p_name_alias,p.p_serial,p.p_small_img,p.p_special_price,p.p_seo_filename,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p.p_id in ".$pid_array_str." ";
            $selectrs = $db->query($sql);
            $show_price=$_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"];
            while ( $row = $db->fetch_array($selectrs,1) ) {
                if($show_price==1){
                    $shopping[]=$row;
                }
                if($show_price==0){
                    $inquiry[]=$row;
                }
            }
            /*
            //如果不需要會員登入，直接顯示表單
            if($cms_cfg["ws_module"]["ws_cart_login"]==0){
                //付款說明
                $this->get_terms();
                $tpl->newBlock("MEMBER_FORM");
                //欄位名稱
                $tpl->assign( array(  "MSG_MEMBER_NAME"  => $TPLMSG['MEMBER_NAME'],
                                      "MSG_VALID_PASSWORD" => $TPLMSG['MEMBER_CHECK_PASSWORD'],
                                      "MSG_BIRTHDAY" => $TPLMSG["BIRTHDAY"],
                                      "MSG_SEX" => $TPLMSG["SEX"],
                                      "MSG_MALE" => $TPLMSG["MALE"],
                                      "MSG_FEMALE" => $TPLMSG["FEMALE"],
                                      "MSG_ZIP" => $TPLMSG["ZIP"],
                                      "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                                      "MSG_TEL" => $TPLMSG["TEL"],
                                      "MSG_FAX" => $TPLMSG["FAX"],
                                      "MSG_EMAIL" => $TPLMSG["EMAIL"],
                                      "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
                                      "STR_M_SEX_CK1" => "",
                                      "STR_M_SEX_CK0" => "checked",

                ));
                //定義目前語系的表單檢查JS
                $tpl->assignGlobal("TAG_LANG",$cms_cfg['language']);
                $tpl->assignGlobal("TAG_FORM_CHECK","onSubmit=\"return CartMemberFormCheck(myform)\"");
                $tpl->assignGlobal("FINISH_TAG","javascript:CartAction(myform,'c_replace');");
            }else{
                $tpl->assignGlobal("FINISH_TAG","javascript:location.href=c_finish");
            }
            */
        }else{
            $tpl->assignGlobal("MSG_CART_EMPTY" ,$TPLMSG['NO_DATA']);
        }

        //顯示購物清單
        if(!empty($shopping)){
            //H1 TAG
            $tpl->assignGlobal("TAG_MAIN_FUNC" , $TPLMSG['CART_SHOPPING']);
            $tpl->assignGlobal("TAG_LAYER" , $TPLMSG['CART_SHOPPING']);
            $tpl->newBlock( "SHOPPING_CART_ZONE" );
            $tpl->assign( array("MSG_CONTINUE_SHOPPING"  => $TPLMSG['CART_CONTINUE_SHOPPING'],
                                "MSG_FINISH_SHOPPING"  => $TPLMSG['CART_FINISH_SHOPPING'],
                                "LINK_CONTINUE" => $_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL'],
                                "MSG_SHIPPING_PRICE"  => $TPLMSG['SHIPPING_PRICE'],
            ));
            for($i=0;$i<count($shopping);$i++){
                $tpl->newBlock( "SHOPPING_CART_LIST" );
                $pid=$shopping[$i]["p_id"];
                $amount=$_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$pid];
                if(!empty($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]) && $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]!=100){
                    $tpl->assignGlobal("MSG_PRODUCT_SPECIAL_PRICE" , $TPLMSG['PRODUCT_DISCOUNT_PRICE']);
                    $tpl->assignGlobal("VALUE_P_DISCOUNT",$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]."%");
                    $special_price=floor($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]/100*$shopping[$i]["p_special_price"]);
                }else{
                    $tpl->assignGlobal("MSG_PRODUCT_SPECIAL_PRICE" , $TPLMSG['PRODUCT_SPECIAL_PRICE']);
                    $tpl->assignGlobal("VALUE_P_DISCOUNT","");
                    $special_price=$shopping[$i]["p_special_price"];
                }
                if($this->ws_seo){
                    $dirname=(trim($shopping[$i]["pc_seo_filename"]))?$shopping[$i]["pc_seo_filename"]:"products";
                    if(trim($shopping[$i]["p_seo_filename"]) !=""){
                        $p_link=$cms_cfg['base_url'].$dirname."/".$shopping[$i]["p_seo_filename"].".html";
                    }else{
                        $p_link=$cms_cfg['base_url'].$dirname."/"."products-".$shopping[$i]["p_id"]."-".$shopping[$i]["pc_id"].".html";
                    }
                }else{
                    $p_link=$cms_cfg['base_url']."products.php?func=p_detail&p_id=".$shopping[$i]["p_id"]."&pc_parent=".$shopping[$i]["pc_id"];
                }
                $sub_total_price = $special_price * $amount;
                $total_price = $total_price+$sub_total_price;
                $tpl->assign( array("VALUE_P_ID"  => $shopping[$i]["p_id"],
                                    "VALUE_P_NAME"  => $shopping[$i]["p_name"],
                                    "VALUE_P_SMALL_IMG" => (trim($shopping[$i]["p_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_url"].$shopping[$i]["p_small_img"],
                                    "VALUE_P_AMOUNT"  => $amount,
                                    "VALUE_P_LINK" => $p_link,
                                    "VALUE_P_SPECIAL_PRICE"  => $special_price,
                                    "VALUE_P_SUBTOTAL_PRICE"  => $sub_total_price,
                                    "VALUE_P_SERIAL"  => $i+1,
                                    "TAG_DELETE_CHECK_STR" => $TPLMSG['CART_DELETE_CHECK']
                ));
                $tpl->gotoBlock( "SHOPPING_CART_ZONE" );
            }
            if($total_price > $_SESSION[$cms_cfg['sess_cookie_name']]["sc_no_shipping_price"]){
                $shipping_price=0;
            }else{
                $shipping_price=$_SESSION[$cms_cfg['sess_cookie_name']]["sc_shipping_price"];
            }
            $subtotal_money=$total_price;
            $total_money=$subtotal_money+$shipping_price;
            $tpl->assignGlobal("VALUE_SHIPPING_PRICE",$shipping_price);
            $tpl->assignGlobal("VALUE_SUBTOTAL",$subtotal_money);
            $tpl->assignGlobal("VALUE_TOTAL",$total_money);
        }
        //顯示詢價清單
        if(!empty($inquiry)){
            //H1 TAG
            $tpl->assignGlobal("TAG_MAIN_FUNC" , $TPLMSG['CART_INQUIRY']);
            $tpl->assignGlobal("TAG_LAYER" , $TPLMSG['CART_INQUIRY']);
            $tpl->newBlock( "INQUIRY_CART_ZONE" );
            $tpl->assign( array("MSG_CONTINUE_INQUIRY"  => $TPLMSG['CART_CONTINUE_INQUIRY'],
                                "MSG_FINISH_INQUIRY"  => $TPLMSG['CART_FINISH_INQUIRY'],
                                "LINK_CONTINUE" => $_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL']
            ));
            $j=0;
            for($i=0;$i<count($inquiry);$i++){                
                $pid=$inquiry[$i]["p_id"];
                //如果產品有分型號
                if(is_array($_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$pid])){
                    $serial_amount = $_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$pid];
                    $sArr = explode(',',$inquiry[$i]["p_serial"]);
                    foreach($serial_amount as $p_serial_key => $amount){
                        $j++;
                        $tpl->newBlock( "INQUIRY_CART_LIST" );
                        if($this->ws_seo){
                            $dirname=(trim($inquiry[$i]["pc_seo_filename"]))?$inquiry[$i]["pc_seo_filename"]:"products";
                            if(trim($inquiry[$i]["p_seo_filename"]) !=""){
                                $p_link=$cms_cfg['base_url'].$dirname."/".$inquiry[$i]["p_seo_filename"].".html";
                            }else{
                                $p_link=$cms_cfg['base_url'].$dirname."/"."products-".$inquiry[$i]["p_id"]."-".$inquiry[$i]["pc_id"].".html";
                            }
                        }else{
                            $p_link=$cms_cfg['base_url']."products.php?func=p_detail&p_id=".$inquiry[$i]["p_id"]."&pc_parent=".$inquiry[$i]["pc_id"];
                        }
                        $tpl->assign( array("VALUE_P_ID"  => $inquiry[$i]["p_id"]."_".$p_serial_key,
                                            "VALUE_P_NAME"  => $inquiry[$i]["p_name"],
                                            "VALUE_P_SMALL_IMG" => (trim($inquiry[$i]["p_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_url"].$inquiry[$i]["p_small_img"],
                                            "VALUE_P_AMOUNT"  => $amount,
                                            "VALUE_P_LINK" => $p_link,
                                            "VALUE_P_SERIAL"  => $j,
                                            "VALUE_P_SERIAL_LIST"  => $p_serial_key,
                                            "TAG_DELETE_CHECK_STR" => $TPLMSG['CART_DELETE_CHECK']
                        ));
                        $tpl->gotoBlock( "INQUIRY_CART_ZONE" );
                    }
                }else{
                    $amount=$_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$pid];
                    $tpl->newBlock( "INQUIRY_CART_LIST" );
                    if($this->ws_seo){
                        $dirname=(trim($inquiry[$i]["pc_seo_filename"]))?$inquiry[$i]["pc_seo_filename"]:"products";
                        if(trim($inquiry[$i]["p_seo_filename"]) !=""){
                            $p_link=$cms_cfg['base_url'].$dirname."/".$inquiry[$i]["p_seo_filename"].".html";
                        }else{
                            $p_link=$cms_cfg['base_url'].$dirname."/"."products-".$inquiry[$i]["p_id"]."-".$inquiry[$i]["pc_id"].".html";
                        }
                    }else{
                        $p_link=$cms_cfg['base_url']."products.php?func=p_detail&p_id=".$inquiry[$i]["p_id"]."&pc_parent=".$inquiry[$i]["pc_id"];
                    }
                    $tpl->assign( array("VALUE_P_ID"  => $inquiry[$i]["p_id"],
                                        "VALUE_P_NAME"  => $inquiry[$i]["p_name"],
                                        "VALUE_P_SMALL_IMG" => (trim($inquiry[$i]["p_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_url"].$inquiry[$i]["p_small_img"],
                                        "VALUE_P_AMOUNT"  => $amount,
                                        "VALUE_P_LINK" => $p_link,
                                        "VALUE_P_SERIAL_LIST"  => "-",
                                        "VALUE_P_SERIAL"  => $i+1,
                                        "TAG_DELETE_CHECK_STR" => $TPLMSG['CART_DELETE_CHECK']
                    ));
                    $tpl->gotoBlock( "INQUIRY_CART_ZONE" );
                }
            }
        }
    }

    function cart_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $p_id=$_REQUEST["p_id"];
        if(!empty($p_id)){
            $tmp = explode("_",$p_id);// demo: (p_id)_(serial_index)
            $p_id = $tmp[0];
            if(count($tmp)>1){
                unset($_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$p_id][$tmp[1]]);
                if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$p_id])){
                    unset($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"][$p_id]);
                }
            }else{
                unset($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"][$p_id]);
                unset($_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$p_id]);
            }
        }
//        $this->cart_list();
        $this->alert_msg("deleted!");
    }
    function cart_modify(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if(!empty($_REQUEST["shop_value"])){
            foreach($_REQUEST["shop_value"] as $key =>$value){
                $tmp = explode("_",$key);
                if(count($tmp)>1){
                    $_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$tmp[0]][$tmp[1]]=$value;
                }else{
                    $_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$tmp[0]]=$value;
                }
            }
        }
//        $this->cart_list();
        $this->alert_msg("modify!");
    }
    function cart_finish(){
        global $db,$tpl,$TPLMSG,$ws_array,$shopping,$inquiry,$cms_cfg,$main;
        //無登入會員,顯示登入表單
        if(empty($this->m_id) && $cms_cfg["ws_module"]["ws_cart_login"]==1){
            //驗証碼
            $tpl->newBlock( "MEMBER_LOGIN_FORM" );
            $tpl->assignGlobal("MSG_ERROR_MESSAGE", $_SESSION[$cms_cfg['sess_cookie_name']]["ERROR_MSG"]);
            $_SESSION[$cms_cfg['sess_cookie_name']]["ERROR_MSG"]=""; //清空錯誤訊息
            $tpl->assignGlobal( "MSG_LOGIN_ACCOUNT",$TPLMSG["LOGIN_ACCOUNT"]);
            $tpl->assignGlobal( "MSG_LOGIN_PASSWORD",$TPLMSG["LOGIN_PASSWORD"]);
            //載入驗証碼
            $main->security_zone();
        }else{
            //載入購物車列表
            $this->cart_list();
            //顯示表單資料
            $tpl->newBlock( "MEMBER_DATA_FORM" );
            $tpl->assign( array("MSG_MODE"  => $TPLMSG['SEND'],
                                "MSG_MEMBER_NAME"  => $TPLMSG['MEMBER_NAME'],
                                "MSG_CONTACT_PERSON" =>$TPLMSG['CONTACT_PERSON'],
                                "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                                "MSG_ZIP" => $TPLMSG["ZIP"],
                                "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                                "MSG_TEL" => $TPLMSG["TEL"],
                                "MSG_FAX" => $TPLMSG["FAX"],
                                "MSG_EMAIL" => $TPLMSG["EMAIL"],
                                "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"]));
            if($this->m_id){
                $sql="select * from ".$cms_cfg['tb_prefix']."_member where m_id='".$this->m_id."'";
                $selectrs = $db->query($sql);
                $row = $db->fetch_array($selectrs,1);
                $tpl->assign( array( "VALUE_M_NAME" => $row["m_name"],
                                     "VALUE_M_CONTACT_S" => $row["m_contact_s"],
                                     "VALUE_M_COMPANY_NAME" => $row["m_company_name"],
                                     "VALUE_M_ZIP" => $row["m_zip"],
                                     "VALUE_M_ADDRESS" => $row["m_address"],
                                     "VALUE_M_TEL" => $row["m_tel"],
                                     "VALUE_M_FAX" => $row["m_fax"],
                                     "VALUE_M_EMAIL" => $row["m_email"],
                                     "VALUE_M_CELLPHONE" => $row["m_cellphone"]));
            }
            //國家下拉選單
            if($cms_cfg["ws_module"]["ws_country"]==1) {
                $main->country_select($row["m_country"]);
            }
            if(!empty($shopping)){
                //顯示付款方式
                $tpl->newBlock("PAYMENT_TYPE");
                $tpl->assign("MSG_PAYMENT_TYPE" , $TPLMSG["PAYMENT_TYPE"]);
                for($i=0;$i<count($ws_array["payment_type"]);$i++){
                    $tpl->newBlock("PAYMENT_TYPE_ITEMS");
                    $tpl->assign("VALUE_PAYMENT_TYPE" , $ws_array["payment_type"][$i]);
                }
                $tpl->gotoBlock("PAYMENT_TYPE");
                $tpl->gotoBlock("MEMBER_DATA_FORM");
                //付款說明
                $sql="select st_payment_term,st_shopping_term from ".$cms_cfg['tb_prefix']."_service_term  where st_id='1'";
                $selectrs = $db->query($sql);
                $rsnum    = $db->numRows($selectrs);
                $row = $db->fetch_array($selectrs,1);
                $payment_term=trim($row["st_payment_term"]);
                if(!empty($payment_term)){
                    $tpl->assignGlobal("MSG_PAYMENT_TERM",$row["st_payment_term"]);
                    $tpl->assignGlobal("MSG_SHOPPING_TERM",$row["st_shopping_term"]);
                }
            }
        }
    }  

    //資料更新================================================================
    function cart_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$shopping,$inquiry,$main;
        $this->ws_tpl_file = "templates/ws-mail-serial-tpl.html";
        $tpl = new TemplatePower( $this->ws_tpl_file );
        $tpl->prepare();
        $tpl->assignGlobal("TAG_BASE_CSS", $cms_cfg['base_mail_css']);
        $this->cart_list();
        $tpl->newBlock( "MEMBER_DATA_FORM" );
        $tpl->assign( array("MSG_MEMBER_NAME"  => $TPLMSG['MEMBER_NAME'],
                            "MSG_CONTACT_PERSON" =>$TPLMSG['CONTACT_PERSON'],
                            "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                            "MSG_ZIP" => $TPLMSG["ZIP"],
                            "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                            "MSG_TEL" => $TPLMSG["TEL"],
                            "MSG_FAX" => $TPLMSG["FAX"],
                            "MSG_EMAIL" => $TPLMSG["EMAIL"],
                            "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
                            "VALUE_M_COMPANY_NAME" => $_REQUEST["m_company_name"],
                            "VALUE_M_CONTACT_S" => $_REQUEST["m_contact_s"],
                            "VALUE_M_NAME" => $_REQUEST["m_name"],
                            "VALUE_M_ZIP" => $_REQUEST["m_zip"],
                            "VALUE_M_ADDRESS" => $_REQUEST["m_address"],
                            "VALUE_M_TEL" => $_REQUEST["m_tel"],
                            "VALUE_M_FAX" => $_REQUEST["m_fax"],
                            "VALUE_M_EMAIL" => $_REQUEST["m_email"],
                            "VALUE_M_CELLPHONE" => $_REQUEST["m_cellphone"],
                            "VALUE_CONTENT" => $_REQUEST["content"],
        ));
        //國家欄位
        if($cms_cfg["ws_module"]["ws_country"]==1) {
            $tpl->newBlock("MEMBER_DATA_COUNTRY_ZONE");
            $tpl->assign(array("MSG_COUNTRY" =>$TPLMSG['COUNTRY'],
                               "VALUE_M_COUNTRY" =>$_REQUEST["m_country"]
            ));
        }
        //如果不需要會員登入,新增一筆會員資料
        if($cms_cfg["ws_module"]["ws_cart_login"]==0 && $this->m_id<1  && !empty($shopping)){
            $sql="
                insert into ".$cms_cfg['tb_prefix']."_member (
                    mc_id,
                    m_status,
                    m_sort,
                    m_modifydate,
                    m_company_name,
                    m_contact_s,
                    m_name,
                    m_birthday,
                    m_sex,
                    m_country,
                    m_zip,
                    m_address,
                    m_tel,
                    m_fax,
                    m_cellphone,
                    m_email,
                    m_epaper_status
                ) values (
                    '0',
                    '0',
                    '".$_REQUEST["m_sort"]."',
                    '".date("Y-m-d H:i:s")."',
                    '".$_REQUEST["m_company_name"]."',
                    '".$_REQUEST["m_contact_s"]."',
                    '".$_REQUEST["m_name"]."',
                    '".$_REQUEST["m_birthday"]."',
                    '".$_REQUEST["m_sex"]."',
                    '".$_REQUEST["m_country"]."',
                    '".$_REQUEST["m_zip"]."',
                    '".$_REQUEST["m_address"]."',
                    '".$_REQUEST["m_tel"]."',
                    '".$_REQUEST["m_fax"]."',
                    '".$_REQUEST["m_cellphone"]."',
                    '".$_REQUEST["m_email"]."',
                    '".$_REQUEST["m_epaper_status"]."'
                )";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $this->m_id=$db->get_insert_id();
            }else{
                $this->m_id=0;
            }
        }
        if(!empty($shopping)){
            //寫入訂單
            $sql="
                insert into ".$cms_cfg['tb_prefix']."_order (
                    m_id,
                    o_status,
                    o_createdate,
                    o_modifydate,
                    o_account,
                    o_company_name,
                    o_contact_s,
                    o_name,
                    o_zip,
                    o_address,
                    o_tel,
                    o_fax,
                    o_cellphone,
                    o_email,
                    o_plus_price,
                    o_subtotal_price,
                    o_total_price,
                    o_content,
                    o_payment_type
                ) values (
                    '".$this->m_id."',
                    '0',
                    '".date("Y-m-d H:i:s")."',
                    '".date("Y-m-d H:i:s")."',
                    '".$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ACCOUNT"]."',
                    '".$_REQUEST["m_company_name"]."',
                    '".$_REQUEST["m_contact_s"]."',
                    '".$_REQUEST["m_name"]."',
                    '".$_REQUEST["m_zip"]."',
                    '".$_REQUEST["m_address"]."',
                    '".$_REQUEST["m_tel"]."',
                    '".$_REQUEST["m_fax"]."',
                    '".$_REQUEST["m_cellphone"]."',
                    '".$_REQUEST["m_email"]."',
                    '".$_REQUEST["o_plus_price"]."',
                    '".$_REQUEST["o_subtotal_price"]."',
                    '".$_REQUEST["o_total_price"]."',
                    '".$_REQUEST["content"]."',
                    '".$_REQUEST["o_payment_type"]."'
                )";
            $rs = $db->query($sql);
            $this->o_id=$db->get_insert_id();
            //產生ATM虛擬帳號
            if($cms_cfg["ws_module"]["ws_vaccount"]==1 & $TPLMSG["PAYMENT_ATM"]==$_REQUEST["o_payment_type"]) {
            $v_account = $this->get_vaccount($_REQUEST["o_subtotal_price"]);
            $sql="
                update ".$cms_cfg['tb_prefix']."_order
                    set o_virtual_account='".$v_account."'
                where o_id='".$this->o_id."'";
            $db->query($sql);
            //在確認信中加入虛擬帳號
            $tpl->newBlock("VIRTUAL_ACCOUNT");
            $tpl->assignGlobal( array("MSG_TRANSFER_BANK" => $TPLMSG['TRANSFER_BANK'],
                                      "VALUE_TRANSFER_BANK_CODE" => $TPLMSG['TRANSFER_BANK_CODE'],
                                      "MSG_TRANSFER_ACCOUNT" => $TPLMSG['TRANSFER_ACCOUNT'],
                                      "VALUE_VIRTUAL_ACCOUNT" => $v_account ));
            }
            //寫入購買產品
            for($i=0;$i<count($shopping);$i++){
                $pid=$shopping[$i]["p_id"];
                $pname=$shopping[$i]["p_name"];
                $amount=$_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$pid];
                if(!empty($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]) && $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]!=100){
                    $special_price=floor($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]/100*$shopping[$i]["p_special_price"]);
                }else{
                    $special_price=$shopping[$i]["p_special_price"];
                }
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_order_items (
                        m_id,
                        o_id,
                        p_id,
                        p_name,
                        p_sell_price,
                        oi_amount
                    ) values (
                        '".$this->m_id."',
                        '".$this->o_id."',
                        '".$pid."',
                        '".$pname."',
                        '".$special_price."',
                        '".$amount."'
                    )";
                $rs = $db->query($sql);
            }
            //顯示付款方式
            $tpl->newBlock("PAYMENT_TYPE");
            $tpl->assign("MSG_PAYMENT_TYPE" , $TPLMSG["PAYMENT_TYPE"]);
            $tpl->assign("VALUE_PAYMENT_TYPE" , $_REQUEST["o_payment_type"]);
            $tpl->gotoBlock( "MEMBER_DATA_FORM" );

            $func_str="func=m_zone&mzt=order";
            //寄送訊息
            $sql="select st_order_mail from ".$cms_cfg['tb_prefix']."_service_term where st_id='1'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $tpl->assignGlobal( "VALUE_TERM" , $row['st_order_mail']);
            $tpl->assignGlobal("VALUE_VIRTUAL_ACCOUNT" , $v_account);
            $mail_content=$tpl->getOutputContent();
            if($cms_cfg["ws_module"]["ws_cart_login"]==0){
                $goto_url=$_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL'];
            }else{
                $goto_url=$cms_cfg["base_url"]."member.php?".$func_str;
            }
            $main->ws_mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$_REQUEST["m_email"],$mail_content,$TPLMSG["ORDER_MAIL_TITLE"],"shopping",$goto_url);
        }

        if(!empty($inquiry)){
            //寫入詢價單
            $sql="
                insert into ".$cms_cfg['tb_prefix']."_inquiry (
                    m_id,
                    i_status,
                    i_createdate,
                    i_modifydate,
                    i_account,
                    i_company_name,
                    i_contact_s,
                    i_name,
                    i_zip,
                    i_address,
                    i_tel,
                    i_fax,
                    i_cellphone,
                    i_email,
                    i_content
                ) values (
                    '".$this->m_id."',
                    '0',
                    '".date("Y-m-d H:i:s")."',
                    '".date("Y-m-d H:i:s")."',
                    '".$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ACCOUNT"]."',
                    '".$_REQUEST["m_company_name"]."',
                    '".$_REQUEST["m_contact_s"]."',
                    '".$_REQUEST["m_name"]."',
                    '".$_REQUEST["m_zip"]."',
                    '".$_REQUEST["m_address"]."',
                    '".$_REQUEST["m_tel"]."',
                    '".$_REQUEST["m_fax"]."',
                    '".$_REQUEST["m_cellphone"]."',
                    '".$_REQUEST["m_email"]."',
                    '".$_REQUEST["content"]."'
                )";
            $rs = $db->query($sql);
            $this->i_id=$db->get_insert_id();
            //寫入購買產品
            for($i=0;$i<count($inquiry);$i++){
                $pid=$inquiry[$i]["p_id"];
                $pname=$inquiry[$i]["p_name"];
                if(is_array($_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$pid])){
                    $sArr = explode(',',$inquiry[$i]["p_serial"]);
                    foreach($_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$pid] as $skey => $amount){
                        $sql="
                            insert into ".$cms_cfg['tb_prefix']."_inquiry_items (
                                m_id,
                                i_id,
                                p_id,
                                p_name,
                                ii_amount
                            ) values (
                                '".$this->m_id."',
                                '".$this->i_id."',
                                '".$pid."',
                                '".$pname."(".$sArr[$skey].")',
                                '".$amount."'
                            )";
                        $rs = $db->query($sql);
                    }
                }else{
                    $amount=$_SESSION[$cms_cfg['sess_cookie_name']]["amount"][$pid];
                    $sql="
                        insert into ".$cms_cfg['tb_prefix']."_inquiry_items (
                            m_id,
                            i_id,
                            p_id,
                            p_name,
                            ii_amount
                        ) values (
                            '".$this->m_id."',
                            '".$this->i_id."',
                            '".$pid."',
                            '".$pname."',
                            '".$amount."'
                        )";
                    $rs = $db->query($sql);
                }
            }
            $func_str="func=m_zone&mzt=inquiry";
            //寄送訊息
            $sql="select st_inquiry_mail from ".$cms_cfg['tb_prefix']."_service_term where st_id='1'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $tpl->assignGlobal( "VALUE_TERM" , $row['st_inquiry_mail']);
            $mail_content=$tpl->getOutputContent();
            if($cms_cfg["ws_module"]["ws_cart_login"]==0){
                $goto_url=$_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL'];
            }else{
                $goto_url=$cms_cfg["base_url"]."member.php?".$func_str;
            }
            $main->ws_mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$_REQUEST["m_email"],$mail_content,$TPLMSG["INQUIRY_MAIL_TITLE"],"inquiry",$goto_url);
        }
        $db_msg = $db->report();
        if ( $db_msg == "" ) {
            unset($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"]);
            unset($_SESSION[$cms_cfg['sess_cookie_name']]["amount"]);
            //$tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            //$goto_url=$cms_cfg["base_url"]."member.php?".$func_str;
            //$this->goto_target_page($goto_url,2);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
    
    function alert_msg($msg){
        echo <<<SSS
   <script type="text/javascript">
        alert("{$msg}");
        location.href="cart.php";
   </script>
SSS;
    }
}
?>