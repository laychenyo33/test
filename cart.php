<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$cart = new CART;
class CART{
    protected $container;
    protected $m_id;
    protected $cart_type;
    protected $ws_seo;
    protected $contact_s_style;
    protected $activateStockChecker;
    protected $giftId = -1;
    function CART(){
        global $db,$cms_cfg,$tpl,$main;
        $this->m_id =$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ID"];
        $this->cart_type =$_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"];
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->contact_s_style = $cms_cfg['ws_module']['ws_contactus_s_style'];
        $this->container = App::getHelper('session')->modules()->cart;
        $this->activateStockChecker = App::configs()->ws_module->ws_products_stocks;
        switch($_REQUEST["func"]){
            case "ajax_cart_list":
                $this->ws_tpl_file = "templates/ws-minicart-tpl.html";
                $tpl = new TemplatePower( $this->ws_tpl_file );
                $tpl->prepare();
                $main->header_footer("cart");
                $this->cart_list();
                $this->ws_tpl_type=1;
                break;
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
                if(!$_POST['via_ajax']){
                    $this->ws_tpl_file = "templates/ws-cart".$this->cart_type."-tpl.html";
                    $this->ws_load_tp($this->ws_tpl_file);
                    $tpl->newBlock("JS_MAIN");
                    $tpl->newBlock("JS_POP_IMG");
                    $this->ws_tpl_type=1;
                }
                $this->cart_del($_POST['via_ajax']);
                break;
            case "c_full_del"://清空購物車
                $this->cart_full_del($_POST['via_ajax']);
                break;
            case "c_finish"://結帳
                if(isset($_POST['shipment_type'])){
                    $this->container->set_shipment_type($_POST['shipment_type']);
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
        App::getHelper('session')->CONTINUE_SHOPPING_URL=$_SERVER['HTTP_REFERER'];
        $amount_arr = is_array($_REQUEST["amount"])?$_REQUEST["amount"]:(array)$_REQUEST["amount"];
        $p_id_arr = is_array($_REQUEST["p_id"])?$_REQUEST["p_id"]:(array)$_REQUEST["p_id"];
        $ps_id_arr = is_array($_REQUEST["ps_id"])?$_REQUEST["ps_id"]:(array)$_REQUEST["ps_id"];
        foreach($p_id_arr as $k => $p_id){
            if($p_id){
                $amount = $amount_arr[$k]?$amount_arr[$k]:1;
                if($cms_cfg['ws_module']['ws_cart_spec']){
                    $this->container->put($p_id,$amount,$ps_id_arr[$k]);
                }else{
                    $this->container->put($p_id,$amount);
                }
            }
        }
        if(!$via_ajax){
            $this->cart_list();
        }else{
            $res['code'] = 1;
            $res['cart_nums'] = $this->container->count();
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
        App::getHelper('session')->CONTINUE_SHOPPING_URL=$_SERVER['HTTP_REFERER'];
        foreach($_REQUEST["amount"] as $p_id => $amount){
            if($amount !=""){
                $this->container->put($p_id,$amount);
            }
        }
        header("location: ".$_SERVER['PHP_SELF']);
        die();
    }
    function cart_list(){
        global $db,$tpl,$TPLMSG,$ws_array,$cms_cfg,$main;
        if(!App::gethelper('request')->isAjax() && $this->container->count()==0){ //空購物車時，回到前一頁
            $main->js_notice($TPLMSG['CART_EMPTY'],$cms_cfg['base_root']."products.htm");
            die();
        }
        if($_POST['o_payment_type']){
            $this->container->set_payment_type($_POST['o_payment_type']);
        }else{
            $this->container->calculate();
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
                                  "MSG_OPERATION" => $TPLMSG['OPERATION'],
                                  "MSG_TOTAL" => $TPLMSG['CART_TOTAL'],
                                  "MSG_PLUS_FEE" => $TPLMSG["PLUS_FEE"],
                                  "MSG_SUBTOTAL" => $TPLMSG['CART_SUBTOTAL'],
                                  "MSG_AMOUNT" => $TPLMSG['CART_AMOUNT'],
                                  "MSG_PRODUCT" => $TPLMSG['CART_PRODUCT_NAME'],
                                  "MSG_SPEC" => $TPLMSG['CART_SPEC_TITLE'],
                                  "MSG_PRODUCT_IMAGE" => $TPLMSG['PRODUCT_IMG'],
                                  "MSG_DISCOUNT" => $TPLMSG['QUANTITY_DISCOUNT'],
                                  "VALUE_MODIFY_AMOUNT" => $TPLMSG['CART_MODIFY_AMOUNT'],
                                  "MSG_SHIP_ZONE" => $TPLMSG['ORDER_SHIP_ZONE'],
                                  'MSG_DEL_DIALOG_TITLE'   => $TPLMSG['DEL_CART_ITEM'],
                                  'MSG_DEL_DIALOG_CONTENT' => $TPLMSG['SURE_TO_DELETE'],
                                  'STR_BTN_DEL_CONFIRM' => $TPLMSG['OK'] ,
                                  'STR_BTN_DEL_CANCEL'  => $TPLMSG['CANCEL'] ,            
                                  //"CART_IMG_TITLE"=> $ws_array["cart_img"][$_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]]["title_img"],
                                  //"CART_IMG_SUB"=> $ws_array["cart_img"][$_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]]["sub_img"],
        ));
        //圖片處理器
        $imgHandler = Model_Image::factory(60,60);
        if($this->container->count()){
            $show_price=$_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"];
            if($show_price==1){
                $shopping = $this->container->get_cart_products();
            }elseif($show_price==0){
                $inquiry = $this->container->get_cart_products();
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
                'MSG_NEXT_STEP' => $TPLMSG['CART_STEP_NEXT'],
                "LINK_CONTINUE" => $_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL'],
                "MSG_SHIPPING_PRICE"  => $TPLMSG['SHIPPING_PRICE'],
                'TAG_COLLECTION' => $TPLMSG['COLLECTION'],
            ));
            //送貨區域
            $source_of_shipment = Model_Shipprice::getShipmentSource();
            App::getHelper('main')->multiple_radio("shipment_type",$source_of_shipment,$this->container->get_shipment_type(),$tpl);
            if($cms_cfg['ws_module']['ws_cart_spec']){
                $tpl->assignGlobal("CART_FIELDS_NUMS",7);
                $tpl->newBlock("SPEC_TITLE");
            }else{
                $tpl->assignGlobal("CART_FIELDS_NUMS",6);
                
            }
            $gift = $this->container->getModule("giftor")->getGift($this->giftId);
            $i=1;
            foreach($shopping as $p_id => $prod_row){
                $tpl->newBlock( "SHOPPING_CART_LIST" );
//                if(!empty($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]) && $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]!=100){
//                    $tpl->assignGlobal("MSG_PRODUCT_SPECIAL_PRICE" , $TPLMSG['PRODUCT_DISCOUNT_PRICE']);
//                    $tpl->assignGlobal("VALUE_P_DISCOUNT",$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]."%");
//                    $special_price=floor($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]/100*$price);
//                }else{
//                    $tpl->assignGlobal("MSG_PRODUCT_SPECIAL_PRICE" , $TPLMSG['PRODUCT_SPECIAL_PRICE']);
//                    $tpl->assignGlobal("VALUE_P_DISCOUNT","");
//                    $special_price=$price;
//                }
                $tpl->assignGlobal("MSG_PRODUCT_SPECIAL_PRICE" , $TPLMSG['PRODUCT_SPECIAL_PRICE']);
                $tpl->assignGlobal("VALUE_P_DISCOUNT","");
                if($this->ws_seo){
                    $dirname=(trim($prod_row["pc_seo_filename"]))?$prod_row["pc_seo_filename"]:"products";
                    if(trim($prod_row["p_seo_filename"]) !=""){
                        $p_link=$cms_cfg['base_url'].$dirname."/".$prod_row["p_seo_filename"].".html";
                    }else{
                        $p_link=$cms_cfg['base_url'].$dirname."/"."products-".$p_id."-".$prod_row["pc_id"].".html";
                    }
                }else{
                    $p_link=$cms_cfg['base_url']."products.php?func=p_detail&p_id=".$p_id."&pc_parent=".$prod_row["pc_id"];
                }
                $valid_stocks = !App::configs()->ws_module->ws_products_stocks  || $this->container->stockChecker->check($prod_row['p_id'],$prod_row['amount'],$prod_row['ps_id']);
                $imgInfo = $imgHandler->parse($prod_row["p_small_img"]);
                $tpl->assign( array("VALUE_P_ID"  => $prod_row["p_id"],
                                    "VALUE_P_NAME"  => $prod_row["p_name"] . (!$valid_stocks?"<span>庫存不足</span>":""),
                                    "VALUE_P_SMALL_IMG" => App::createURL($imgInfo[0]),
                                    "VALUE_P_SMALL_IMG_W" => $imgInfo['width'],
                                    "VALUE_P_SMALL_IMG_H" => $imgInfo['height'],
                                    "VALUE_P_SMALL_IMG_M" => $imgHandler->getTypedImg('medium'),
                                    "VALUE_P_AMOUNT"  => $prod_row['amount'],
                                    "TAG_QUANTITY_DISCOUNT" => ($prod_row['discount']<1)?$prod_row['discount']:'',
                                    "VALUE_P_LINK" => $p_link,
                                    "VALUE_P_SPECIAL_PRICE"  => $prod_row['price'],
                                    "VALUE_P_SUBTOTAL_PRICE"  => $prod_row["subtotal_price"],
                                    "VALUE_P_SERIAL"  => $i++,
                                    "TAG_DELETE_CHECK_STR" => $TPLMSG['CART_DELETE_CHECK'],
                                    "INVENTORY_SHORT_CLASS" => (!$valid_stocks)?"stocks_short":"",
                ));
                $id_sets = explode(":",$p_id);
                if(count($id_sets)==2){
                    $tpl->assign(array(
                        "TAG_PS_ID_STR"   => "[".$id_sets[1]."]",
                        "TAG_PS_ID_QUERY" => "&ps_id=".$id_sets[1],
                        "TAG_PS_ID" => "psid='".$id_sets[1]."'",
                    ));
                }                
                if($cms_cfg['ws_module']['ws_cart_spec']){
                    $tpl->newBlock("SPEC_FIELD");
                    $tpl->assign("VALUE_SPEC",$prod_row["spec"]);
                }
                if($cms_cfg['ws_module']['ws_products_collect']){
                    $tpl->newBlock("TAG_COLLECTION_LINK");
                    $tpl->assignGlobal(array(
                        "VALUE_P_ID" => $prod_row["p_id"],
                    ));
                }
                $tpl->gotoBlock( "SHOPPING_CART_ZONE" );
            }
            if($gift){
                $tpl->newBlock("TAG_CART_GIFT");
                $tpl -> assign(array(
                    "VALUE_P_ID" => $gift['p_id'], 
                    "VALUE_P_NAME" => $gift["p_name"], 
                    "VALUE_P_SMALL_IMG" => (trim($gift["p_small_img"]) == "") ? $cms_cfg['base_url'].'images/ws-no-image.jpg' : $cms_cfg["file_url"] . $gift["p_small_img"],
                    "VALUE_P_SMALL_IMG_ALT" => strip_tags($gift["p_name"]),
                    "VALUE_P_AMOUNT" => $gift["amount"], 
                    "TAG_QUANTITY_DISCOUNT" => ($gift['discount']<1)?$gift['discount']:'',
                    "VALUE_P_SPECIAL_PRICE" => $gift['price'],
                    "VALUE_P_SUBTOTAL_PRICE" => $gift['subtotal_price'],
                    "VALUE_P_SERIAL" => $i++, 
                    "CART_P_ID" => $p_id,                                             
                ));

                if($cms_cfg['ws_module']['ws_cart_spec']){
                    $tpl->newBlock("GIFT_SPEC_FIELD");
                    $tpl->assign("VALUE_SPEC",$gift["spec"]);
                }                                    
            }            
            $order_info = $this->container->get_cart_info();

            $tpl->assignGlobal("VALUE_SHIPPING_PRICE",$order_info['shipping_price']);
            $tpl->assignGlobal("VALUE_SHIPPING_PRICE_STR",($order_info['shipping_price']==0)?"滿額免運費":$order_info['shipping_price']);
            $tpl->assignGlobal("VALUE_MINUS_PRICE",0);
            $tpl->assignGlobal("VALUE_SUBTOTAL",$order_info['subtotal_price']);
            $tpl->assignGlobal("VALUE_TOTAL",$order_info['total_price']);
            //購物說明
            $this->load_term($tpl);
        }elseif(!empty($inquiry)){  //顯示詢價清單
            //H1 TAG
            $tpl->assignGlobal("TAG_MAIN_FUNC" , $TPLMSG['CART_INQUIRY']);
            $tpl->assignGlobal("TAG_DISPLAY" , "style='display:none'");
            $tpl->newBlock( "INQUIRY_CART_ZONE" );
            $tpl->assign( array(
                "MSG_CONTINUE_INQUIRY"  => $TPLMSG['CART_CONTINUE_INQUIRY'],
                "MSG_FINISH_INQUIRY"  => $TPLMSG['CART_FINISH_INQUIRY'],
                "LINK_CONTINUE" => $_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL'],
                "MSG_QUANTITY_UPDATED" => $TPLMSG["INQUIRY_QUANTITY_UPDATED"],
                "MSG_OK" => $TPLMSG['OK'],
                "MSG_CANCEL" => $TPLMSG['CANCEL'],
                "TAG_DELETE_CHECK_STR" => $TPLMSG['CART_DELETE_CHECK'],
            ));
            if($cms_cfg['ws_module']['ws_cart_spec']){
                $tpl->newBlock("SPEC_TITLE");
            }
            $i=1;
            foreach($inquiry as $p_id => $prod_row){
                $tpl->newBlock( "INQUIRY_CART_LIST" );
                if($this->ws_seo){
                    $dirname=(trim($inquiry[$p_id]["pc_seo_filename"]))?$prod_row["pc_seo_filename"]:"products";
                    if(trim($prod_row["p_seo_filename"]) !=""){
                        $p_link=$cms_cfg['base_url'].$dirname."/".$prod_row["p_seo_filename"].".html";
                    }else{
                        $p_link=$cms_cfg['base_url'].$dirname."/"."products-".$prod_row["p_id"]."-".$prod_row["pc_id"].".html";
                    }
                }else{
                    $p_link=$cms_cfg['base_url']."products.php?func=p_detail&p_id=".$prod_row["p_id"]."&pc_parent=".$prod_row["pc_id"];
                }
                $imgInfo = $imgHandler->parse($prod_row["p_small_img"]);
                $tpl->assign( array("VALUE_P_ID"  => $prod_row["p_id"],
                                    "VALUE_P_NAME"  => $prod_row["p_name"],
                                    "VALUE_P_SMALL_IMG" => App::createURL($imgInfo[0]),
                                    "VALUE_P_SMALL_IMG_W" => $imgInfo['width'],
                                    "VALUE_P_SMALL_IMG_H" => $imgInfo['height'],
                                    "VALUE_P_SMALL_IMG_M" => $imgHandler->getTypedImg('medium'),
                                    "VALUE_P_AMOUNT"  => $prod_row['amount'],
                                    "VALUE_P_LINK" => $p_link,
                                    "VALUE_P_SERIAL"  => $i++,
                ));
                $id_sets = explode(":",$p_id);
                if(count($id_sets)==2){
                    $tpl->assign(array(
                        "TAG_PS_ID_STR"   => "[".$id_sets[1]."]",
                        "TAG_PS_ID_QUERY" => "&ps_id=".$id_sets[1],
                        "TAG_PS_ID" => "psid='".$id_sets[1]."'",
                    ));
                }
                if($cms_cfg['ws_module']['ws_cart_spec']){
                    $tpl->newBlock("SPEC_FIELD");
                    $tpl->assign("VALUE_SPEC",$prod_row["spec"]);
                }                
                $tpl->gotoBlock( "INQUIRY_CART_ZONE" );
            }
        }
    }

    function cart_del($via_ajax){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_POST["p_id"]){
            if($_POST["ps_id"]){
                $this->container->rm($_POST["p_id"],$_POST["ps_id"]);
            }else{
                $this->container->rm($_POST["p_id"]);
            }
        }
        if($via_ajax){
            $result['code']=1;
            $result = array_merge($result,$this->container->get_cart_info());
            echo json_encode($result);
        }else{
            if($this->container->count()){
                header("location:".$_SERVER['PHP_SELF']);
            }else{
                header("location:products.htm");
            }
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
        if($this->cart_type==1){
            $this->container->set_shipment_type($_POST['shipment_type']);
            $this->container->set_payment_type(0);
        }
        if(!empty($_REQUEST["shop_value"])){
            foreach($_REQUEST["shop_value"] as $key =>$value){
                if(is_array($value)){
                    foreach($value as $ps_id => $subvalue){
                        $this->container->update($key,$subvalue,$ps_id);
                        $res['product'] = $this->container->get_cart_products($key,$ps_id);
                    }
                }else{
                    $this->container->update($key,$value);
                    $res['product'] = $this->container->get_cart_products($key);
                }
            }
        }
        if(!$via_ajax){
            $this->cart_list();
        }else{
            $res['code'] = 1;
            $res = array_merge($res,$this->container->get_cart_info());
            echo json_encode($res);
        }
    }
    function cart_finish(){
        global $db,$tpl,$TPLMSG,$ws_array,$cms_cfg,$main;
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
            "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
            'MSG_CHOOSE_PAYMENT_TYPE' => $TPLMSG['CHOOSE_PAYMENT_TYPE'],
            /*結帳表單區塊標題*/
            'MSG_BLOCK_ORDER'       => $TPLMSG['ORDER_BLOCK_TITLE_ORDER'],
            'MSG_BLOCK_ORDERBY'     => $TPLMSG['ORDER_BLOCK_TITLE_ORDERBY'],
            'MSG_BLOCK_SENDTO'      => $TPLMSG['ORDER_BLOCK_TITLE_SENDTO'],
            'MSG_BLOCK_VAT_RECEIPT' => $TPLMSG['ORDER_BLOCK_TITLE_VAT_RECEIPT'],
            'MSG_VAT_NUMBER'        => $TPLMSG['VAT_NUMBER'],
            'MSG_INVOICE_TYPE'      => $TPLMSG['INVOICE_TYPE'],
            'MSG_ORDER_MESSAGE'     => $TPLMSG['ORDER_MEMO'],
            "MSG_SAME_AS_ORDERBY"   => $TPLMSG['SAM'],
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
                 "VALUE_CONTENT" => $tmpForm["o_content"],
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
                    //'name' => sprintf($TPLMSG["MEMBER_NAME_IN_CART_TEMPLATE"],$memRow['m_fname'],$memRow['m_lname']),
                    'fname' => $memRow['m_fname'],
                    'lname' => $memRow['m_lname'],
                    'msg_first_name' => $TPLMSG['MEMBER_FNAME'],
                    'msg_last_name'  => $TPLMSG['MEMBER_LNAME'],                    
                ),
                'courtesyTitle' => $memRow['m_contact_s'],
            ),
        ));
        $tpl->assignGlobal("TAG_CONTACT_WITH_S",$ordererField->get_html());
        $receiveField = new ContactfieldWithCourtesyTitle(array(
            'view'      => 'orderreceiver',
            'blockName' => 'Receiver',
            'fieldData' => array(
                'contact' => array(
                    'msg_first_name' => $TPLMSG['MEMBER_FNAME'],
                    'msg_last_name'  => $TPLMSG['MEMBER_LNAME'],                    
                ),
            ),            
        ));
        $tpl->assignGlobal("TAG_CONTACTR_WITH_S",$receiveField->get_html());
        //地址欄位格式
        if($cms_cfg['ws_module']['ws_address_type']=='tw'){
            $tpl->newBlock("TW_ADDRESS");
        }else{
            $tpl->newBlock("SINGLE_ADDRESS");
        }
        if($this->cart_type==1){
            //收件者地址欄位格式
            if($cms_cfg['ws_module']['ws_address_type']=='tw'){
                $tpl->newBlock("TW_ADDRESS_RECI");
            }else{
                $tpl->newBlock("SINGLE_ADDRESS_RECI");
            }              
            //運送區域
            $source_of_shipment = Model_Shipprice::getShipmentSource();          
            $shipment_type = $this->container->get_shipment_type();
            $tpl->assignGlobal("VALUE_SHIPMENT_TYPE",$shipment_type);
            $tpl->assignGlobal("VALUE_SHIPMENT_ZONE",$source_of_shipment[$shipment_type]);
            if($shipment_typ==3){
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
        global $db,$tpl,$cms_cfg,$TPLMSG,$inquiry,$main,$ws_array;
        $main->magic_gpc($_POST);
        App::getHelper('session')->tmpForm = $_POST;
        $this->cart_list();
        $tpl->assignGlobal(array(
            'MSG_ORDER_INFO'        => $TPLMSG['ORDER_BLOCK_TITLE_ORDER'],
            'BTN_MODIFY'            => $TPLMSG['ORDER_PREVIEW_MODIFY'],
            'BTN_FINISH'            => $TPLMSG['ORDER_PREVIEW_FINISH'],
            'MSG_BLOCK_ORDER'       => $TPLMSG['ORDER_BLOCK_TITLE_ORDER'],
            'MSG_BLOCK_ORDERBY'     => $TPLMSG['ORDER_BLOCK_TITLE_ORDERBY'],
            'MSG_BLOCK_SENDTO'      => $TPLMSG['ORDER_BLOCK_TITLE_SENDTO'],
            'MSG_BLOCK_VAT_RECEIPT' => $TPLMSG['ORDER_BLOCK_TITLE_VAT_RECEIPT'],
            'MSG_VAT_NUMBER'        => $TPLMSG['VAT_NUMBER'],
            'MSG_INVOICE_TYPE'      => $TPLMSG['INVOICE_TYPE'],
            'MSG_ORDER_MESSAGE'     => $TPLMSG['ORDER_MEMO'],            
        ));        
        $tpl->newBlock( "MEMBER_DATA_FORM" );
        $shipment_type = $this->container->get_shipment_type();
        //處理地址欄位
        $map = array('target'=>'address','rmTarget'=>array('city','area'));
        $type = array('m_','m_reci_');
        //訂購人地址
        foreach($type as $t){
            $target = $t.$map['target'];
            if($_POST[$target]){
                foreach($map['rmTarget'] as $rt){
                    $rmTarget = $t.$rt;
                    $_POST[$target] = str_replace($_POST[$rmTarget], '', $_POST[$target]);
                }
            }    
        }
        $source_of_shipment = Model_Shipprice::getShipmentSource();
        $tpl->assign( array("MSG_MEMBER_NAME"  => $TPLMSG['MEMBER_NAME'],
                            "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                            "MSG_ZIP" => $TPLMSG["ZIP"],
                            "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                            "MSG_TEL" => $TPLMSG["TEL"],
                            "MSG_FAX" => $TPLMSG["FAX"],
                            "MSG_EMAIL" => $TPLMSG["EMAIL"],
                            "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
                            "VALUE_M_COMPANY_NAME" => $_POST["m_company_name"],
                            "VALUE_M_VAT_NUMBER" => $_POST["m_vat_number"],
                            "VALUE_M_INVOICE_TYPE" => $ws_array['invoice_type'][$_POST['o_invoice_type']],
                            "VALUE_M_ZIP" => $_POST["m_zip"],
                            "VALUE_M_ADDRESS" => $_POST["m_city"].$_POST["m_area"].$_POST["m_address"],
                            "VALUE_M_TEL" => $_POST["m_tel"],
                            "VALUE_M_FAX" => $_POST["m_fax"],
                            "VALUE_M_EMAIL" => $_POST["m_email"],
                            "VALUE_M_CELLPHONE" => $_POST["m_cellphone"],
                            "VALUE_M_RECI_CONTACT_S" => $_POST["m_reci_contact_s"],
                            "VALUE_M_RECI_NAME" => $_POST["m_reci_name"],
                            "VALUE_M_RECI_ZIP" => $_POST["m_reci_zip"],
                            "VALUE_M_RECI_ADDRESS" => $_POST["m_reci_city"].$_POST["m_reci_area"].$_POST["m_reci_address"],
                            "VALUE_M_RECI_TEL" => $_POST["m_reci_tel"],
                            "VALUE_M_RECI_EMAIL" => $_POST["m_reci_email"],
                            "VALUE_M_RECI_CELLPHONE" => $_POST["m_reci_cellphone"],
                            "VALUE_O_CONTENT" => nl2br($_POST["m_content"]),
                            "VALUE_O_ID" => $this->o_id,
                            "VALUE_SHIPPMENT_TYPE" => $source_of_shipment[$shipment_type],
                            "VALUE_O_INVOICE_TYPE" => $ws_array['invoice_type'][$_POST['o_invoice_type']],
        ));
        $m_name = sprintf($TPLMSG["MEMBER_NAME_IN_CART_TEMPLATE"],$_POST['m_fname'],$_POST['m_lname']);
        $m_reci_name = sprintf($TPLMSG["MEMBER_NAME_IN_CART_TEMPLATE"],$_POST['m_reci_fname'],$_POST['m_reci_lname']);        
        //訂購人
        if($cms_cfg['ws_module']['ws_contactus_s_style']==1){//西式稱謂
            $tpl->newBlock("CART_S_STYLE_1");
        }elseif($cms_cfg['ws_module']['ws_contactus_s_style']==2){//中式稱謂
            $tpl->newBlock("CART_S_STYLE_2");
        }
        $tpl->assign(array(
            "MSG_CONTACT_PERSON" => $TPLMSG['CONTACT_PERSON'],     
            "VALUE_M_NAME"       => $m_name,    
            "VALUE_M_CONTACT_S"  => $ws_array["contactus_s"][$_POST["m_contact_s"]],                    
        ));        
        //收件人
        if($cms_cfg['ws_module']['ws_contactus_s_style']==1){//西式稱謂
            $tpl->newBlock("RECI_CART_S_STYLE_1");
        }elseif($cms_cfg['ws_module']['ws_contactus_s_style']==2){//中式稱謂
            $tpl->newBlock("RECI_CART_S_STYLE_2");
        }
        $tpl->assign(array(
            "MSG_CONTACT_PERSON" => $TPLMSG['CONTACT_PERSON'],     
            "VALUE_M_NAME"       => $m_reci_name,    
            "VALUE_M_CONTACT_S"  => $ws_array["contactus_s"][$_POST["m_reci_contact_s"]],                    
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
                               "VALUE_M_COUNTRY" =>$_POST["m_country"]
            ));
        }
        
        if($this->container->count()){
            //結帳，計算訂單金額
            $billList = $this->container->get_cart_info();
            //手續費
            if($billList['charge_fee']){   
                $tpl->newBlock("CHARGE_FEE_ROW");
                $tpl->assign("VALUE_CHARGE_FEE",$billList['charge_fee']);
            }           
            $tpl->gotoBlock("SHOPPING_CART_ZONE");            
            $tpl->assign("VALUE_TOTAL",$billList['total_price']);
            //$this->o_id=$db->get_insert_id();
            //產生ATM虛擬帳號
            if($cms_cfg["ws_module"]["ws_vaccount"]==1 & $_POST["o_payment_type"]==2) {
                $v_account = $this->get_vaccount($_POST["o_subtotal_price"]);
                //在確認信中加入虛擬帳號
                $tpl->newBlock("VIRTUAL_ACCOUNT");
                $tpl->assignGlobal( array(
                    "MSG_TRANSFER_BANK" => $TPLMSG['TRANSFER_BANK'],
                    "VALUE_TRANSFER_BANK_CODE" => $TPLMSG['TRANSFER_BANK_CODE'],
                    "MSG_TRANSFER_ACCOUNT" => $TPLMSG['TRANSFER_ACCOUNT'],
                    "VALUE_VIRTUAL_ACCOUNT" => $v_account
                ));
            }
            //顯示付款方式
            $tpl->newBlock("PAYMENT_TYPE");
            $tpl->assign("MSG_PAYMENT_TYPE" , $TPLMSG["PAYMENT_TYPE"]);
            $tpl->assign("VALUE_PAYMENT_TYPE" , $main->multi_map_value($ws_array["payment_type"],$_POST["o_payment_type"]));
            if($_POST["o_payment_type"]==1){ //ATM轉帳
                $tpl->newBlock("ATM_LAST_FIVE");
                $tpl->assign("VALUE_ATM_LAST5",$_POST["o_atm_last5"]);
            }            
            $tpl->gotoBlock( "MEMBER_DATA_FORM" );
            $tpl->assignGlobal("VALUE_VIRTUAL_ACCOUNT" , $v_account);
            
            //輸出post暫存
            foreach ($_POST as $k => $v) {
                if($k!=='func'){  //需先判斷此條件，避免又導到preview裡去了
                    $tpl->newBlock("TMP_POST_FIELD");
                    $tpl->assign(array("TAG_POST_KEY" => $k, "TAG_POST_VALUE" => htmlspecialchars($v), ));
                }
            }
        }
    }
    //資料更新================================================================
    function cart_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$shopping,$inquiry,$main,$ws_array;
        if($this->activateStockChecker && $this->container->checkCartStocks()===false){ //l購物車裡有產品庫存不足
            App::getHelper('main')->js_notice($TPLMSG['INVENTORY_SHORTAG_NOTIFY'],$_SERVER['PHP_SELF']);
            die();
        }
        $main->magic_gpc($_REQUEST);
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
                            'MSG_BLOCK_ORDER'       => $TPLMSG['ORDER_BLOCK_TITLE_ORDER'],
                            'MSG_BLOCK_ORDERBY'     => $TPLMSG['ORDER_BLOCK_TITLE_ORDERBY'],
                            'MSG_BLOCK_SENDTO'      => $TPLMSG['ORDER_BLOCK_TITLE_SENDTO'],
                            'MSG_BLOCK_VAT_RECEIPT' => $TPLMSG['ORDER_BLOCK_TITLE_VAT_RECEIPT'],
                            'MSG_VAT_NUMBER'        => $TPLMSG['VAT_NUMBER'],
                            'MSG_INVOICE_TYPE'      => $TPLMSG['INVOICE_TYPE'],
                            'MSG_ORDER_MESSAGE'     => $TPLMSG['ORDER_MEMO'],              
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
                            "VALUE_O_CONTENT" => nl2br($_REQUEST["m_content"]),
                            "VALUE_O_INVOICE_TYPE" => $ws_array['invoice_type'][$_REQUEST['o_invoice_type']],
        ));
        $o_name = sprintf($TPLMSG["MEMBER_NAME_IN_CART_TEMPLATE"],$_POST['m_fname'],$_POST['m_lname']);
        $o_reci_name = sprintf($TPLMSG["MEMBER_NAME_IN_CART_TEMPLATE"],$_POST['m_reci_fname'],$_POST['m_reci_lname']);          
        //訂購人
        if($cms_cfg['ws_module']['ws_contactus_s_style']==1){//西式稱謂
            $tpl->newBlock("CART_S_STYLE_1");
        }elseif($cms_cfg['ws_module']['ws_contactus_s_style']==2){//中式稱謂
            $tpl->newBlock("CART_S_STYLE_2");
        }
        $tpl->assign(array(
            "MSG_CONTACT_PERSON" => $TPLMSG['CONTACT_PERSON'],     
            "VALUE_M_NAME"       => $o_name,    
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
            "VALUE_M_NAME"       => $o_reci_name,    
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
        if($_POST['reg_mem']){
            $main->check_duplicate_member_account($_REQUEST["m_email"]);      
            $memberData = array_merge($_POST,array(
                'mc_id'     => '1',
                'm_status'  => '1',
                'm_account' => $_POST['m_email'],
                'm_sort'    => App::getHelper('dbtable')->member->get_max_sort_value(),
            ));
            App::getHelper('dbtable')->member->writeData($memberData);
            $db_msg = App::getHelper('dbtable')->member->report();
            if ( $db_msg == "" ) {
                $this->m_id = App::getHelper('dbtable')->member->get_insert_id();
            }else{
                $this->m_id=0;
            }
        }
        if($this->cart_type == 1 && ($shopping = $this->container->get_cart_products())){
            foreach($_POST as $k=>$v){
                if(preg_match("/^m_(\w+)$/", $k,$match)){
                    $_POST['o_'.$match[1]] = $v;
                }
            }
            $shipment_type = $this->container->get_shipment_type();
            $payment_type = $this->container->get_payment_type();            
            //寫入訂單
            ////取得訂單號碼
            $oid=$this->get_oid();
            //結帳，計算訂單金額
            $billList = $this->container->get_cart_info();
            //手續費
            if($billList['charge_fee']){    
                $tpl->newBlock("CHARGE_FEE_ROW");
                $tpl->assign("VALUE_CHARGE_FEE",$billList['charge_fee']);
            }
            $tpl->gotoBlock( "MEMBER_DATA_FORM" );
            //配送地區
            $source_of_shipment = Model_Shipprice::getShipmentSource();
            $tpl->assign(array(
                "VALUE_O_ID" => $oid,
                "VALUE_SHIPPMENT_TYPE" => $main->multi_map_value($source_of_shipment,$shipment_type),
            ));
            $ts = time();
            $tpl->gotoBlock("SHOPPING_CART_ZONE");            
            $tpl->assign("VALUE_TOTAL",$billList['total_price']);            
            $orderData = array_merge($_POST,array(
                'o_id'         => $oid,
                'm_id'         => $this->m_id,
                'o_status'     => 0,
                'o_createdate' => date("Y-m-d H:i:s",$ts),
                'o_account'    => $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ACCOUNT"],
                'o_plus_price'     => $billList['shipping_price'],
                'o_charge_fee'     => $billList['charge_fee'],
                'o_subtotal_price' => $billList['subtotal_price'],
                'o_minus_price'    => 0,
                'o_total_price'    => $billList['total_price'],
                'o_shippment_type' => $shipment_type,
                'o_payment_type'   => $payment_type,
                'o_name'           => $o_name,
                'o_reci_name'      => $o_reci_name,
            ));
            //啟用美安訂單及有RID才寫入rid
            if(App::configs()->ws_module->ws_rid_order && App::getHelper('session')->RID){
                $orderData = array_merge($orderData,array(
                    'rid' => App::getHelper('session')->RID,
                ));
            }
            //如果必要欄位為空值，導回購物車列表
            if(!$this->checkRequireFields($orderData)){
                ob_start();
                print_r($_SERVER);
                echo "\n";
                echo "訂單資料:\n";
                print_r($orderData);
                $serverInfo = ob_get_clean();
                file_put_contents('upload_files/'.date("YmdHis")."-".$_SERVER['REMOTE_ADDR'].'.log', $serverInfo);
                $main->js_notice($TPLMSG['ORDER_DATA_SHORTAGE'],$_SERVER['PHP_SELF']);
                die();
            }
            //寫入購買產品
            //有贈品的話就寫入贈品
            if($gift = $this->container->getModule("giftor")->getGift($this->giftId)){
                $shopping[$this->giftId] = $gift;
            }
            foreach($shopping as $p_id => $prod_row){
                $prod_row['m_id'] = $this->m_id;
                $shopping[$p_id] = $prod_row;
            }            
            App::getHelper('dbtable')->order->writeDataWithItems($orderData,$shopping,true);
            //$this->o_id=$db->get_insert_id();
            //產生ATM虛擬帳號
            if($cms_cfg["ws_module"]["ws_vaccount"]==1 & $TPLMSG["PAYMENT_ATM"]==$_REQUEST["o_payment_type"]) {
                $v_account = $this->get_vaccount($billList['subtotal_price']);
                $v_account_data = array('o_id'=>$oid,'o_virtual_account'=>$v_account);
                App::getHelper('dbtable')->order->writeData($v_account_data);
                //在確認信中加入虛擬帳號
                $tpl->newBlock("VIRTUAL_ACCOUNT");
                $tpl->assignGlobal( array(
                    "MSG_TRANSFER_BANK" => $TPLMSG['TRANSFER_BANK'],
                    "VALUE_TRANSFER_BANK_CODE" => $TPLMSG['TRANSFER_BANK_CODE'],
                    "MSG_TRANSFER_ACCOUNT" => $TPLMSG['TRANSFER_ACCOUNT'],
                    "VALUE_VIRTUAL_ACCOUNT" => $v_account 
                ));
            }
            //顯示付款方式
            $tpl->newBlock("PAYMENT_TYPE");
            $tpl->assign("MSG_PAYMENT_TYPE" , $TPLMSG["PAYMENT_TYPE"]);
            $tpl->assign("VALUE_PAYMENT_TYPE" , $main->multi_map_value($ws_array["payment_type"],$payment_type));
            App::getHelper("session")->{paymentType} = $payment_type;
            if($payment_type==1){ //ATM轉帳
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
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $this->container->empty_cart();
                unset(App::getHelper('session')->tmpForm);//結帳表單暫存
                header("location:".$goto_url);
                die();
//                unset($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"]);
//                unset($_SESSION[$cms_cfg['sess_cookie_name']]["amount"]);
//                unset($_SESSION[$cms_cfg['sess_cookie_name']]["shipment_type"]);
                //$tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                //$goto_url=$cms_cfg["base_url"]."member.php?".$func_str;
                //$this->goto_target_page($goto_url,2);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
            //$main->ws_mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$_REQUEST["m_email"],$mail_content,$TPLMSG["ORDER_MAIL_TITLE"],"shopping",$goto_url,null,$mail_header);
        }else if( $this->cart_type ==0 && ($inquiry = $this->container->get_cart_products())){
            foreach($_POST as $k=>$v){
                if(preg_match("/^m_(\w+)$/", $k,$match)){
                    $_POST['i_'.$match[1]] = $v;
                }
            }            
            //寫入詢價單
            $inquiryData = array_merge($_POST,array(
                'i_id'         => $this->get_iid(),
                'm_id'         => $this->m_id,
                'i_status'     => 0,
                'i_createdate' => date("Y-m-d H:i:s"),
                'i_account'    => $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ACCOUNT"],
                'i_name'       => $o_name,
            ));
            //寫入購買產品
            foreach($inquiry as $p_id => $prod_row){
                $prod_row['m_id'] = $this->m_id;
                $inquiry[$p_id] = $prod_row;
            }
            App::getHelper('dbtable')->inquiry->writeDataWithItems($inquiryData,$inquiry,true);
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
                $this->container->empty_cart();                
                unset(App::getHelper('session')->tmpForm);//結帳表單暫存
//                unset($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"]);
//                unset($_SESSION[$cms_cfg['sess_cookie_name']]["amount"]);
//                unset($_SESSION[$cms_cfg['sess_cookie_name']]["shipment_type"]);
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
        $tpl->assignGlobal(array(
            "TAG_MAIN_FUNC"      => $TPLMSG['MEMBER_LOGIN'],
            "TAG_RETURN_URL"     => $_SERVER['REQUEST_URI'],
            "TAG_FS_SHOPPING"    => $TPLMSG['FIRST_TIME_SHOPPING'],
            'TAG_LOGIN_MESSAGE1' => $TPLMSG['CART_LOGIN_MESSAGE1'],
            'TAG_LOGIN_MESSAGE2' => $TPLMSG['CART_LOGIN_MESSAGE2'],
        ));
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
    //取得最新詢價單號碼
    function get_iid(){
        global $db,$cms_cfg;
        $today_str = date("Ymd");
        $pattern = $today_str."([0-9]{4})";
        $sql = "SELECT i_id FROM `".$cms_cfg['tb_prefix']."_inquiry` where  i_id  regexp '".$pattern."' order by i_id desc limit 1";
        $row = $db->query_firstrow($sql);
        if($row){
            preg_match(sprintf("/%s/i",$pattern), $row['i_id'], $matches);
            $serial = intval($matches[1])+1;
        }else{
            $serial = 1;
        }
        $new_i_id = sprintf("%s%04d",date("Ymd"),$serial);
        return $new_i_id;
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
            $this->container->set_shipment_type($_POST['shipment_type']);
            $this->container->set_payment_type(0);
            $this->container->calculate();
            $res['code'] = 1;
            $res = array_merge($res , $this->container->get_cart_info() );
            echo json_encode($res);
        }
    }
    //動態取得手續費
    function ajax_get_charge_fee(){
        if(App::getHelper('request')->isAjax()){
            $res['code'] = 1;
            $this->container->set_payment_type($_POST['payment_type']);
            $this->container->calculate();
            $res = array_merge($res, $this->container->get_cart_info() );
            echo json_encode($res);
        }
    }
    /**
     * 檢查必要欄位
     * @param Array $data  要寫入訂單的資料
     * @return boolean
     * @author 林俊信 <chunhsin@allmarketing.com.tw>
     */
    function checkRequireFields($data){
        $pass=true;
        $require_fields = array(
            'o_shippment_type','o_payment_type','o_name','o_email','o_address',
            'o_tel','o_cellphone','o_reci_name','o_reci_email','o_reci_address',
            'o_reci_tel','o_reci_cellphone','o_invoice_type','o_subtotal_price','o_total_price',
        );
        if($require_fields){
            foreach($require_fields as $f){
                if(empty($data[$f])){
                    $pass=false;
                    break;
                }
                //檢查發票資訊
                if($f=="o_invoice_type" && $data[$f]==3 && (empty($data['o_company_name']) || empty($data['o_vat_number']))){
                    $pass=false;
                    break;
                }
            }
        }
        return $pass;
    }
}

?>