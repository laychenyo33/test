<?php
	### 請先查看  config.php 後再使用 ###
	
	include_once ("../libs/libs-sysconfig.php");
	$cart = new CART;
	
	class CART {
                protected $container;
                protected $name_s_struct = array(
                    1 => '%1$s %2$s',
                    2 => '%2$s %1$s',
                );
                protected $activateStockChecker;
                protected $giftId = -1;
		function __construct() {
			global $cms_cfg, $tpl, $TPLMSG;
			include_once (dirname(__FILE__)."/config.php");
			$this->m_id = $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ID"];
			$this->ws_seo = ($cms_cfg["ws_module"]["ws_seo"])?1:0;
			$this->order = ($cms_cfg["ws_module"]["ws_select_order"] == 1)?"desc":"asc";
                        $this->container = App::getHelper('session')->modules()->cart;
                        $this->activateStockChecker = App::configs()->ws_module->ws_products_stocks;
                        //$this->container->empty_cart();
			switch($_REQUEST["func"]) {
                            case "ajax":
                                $this->ajax();
                                break;
                                case "c_del"://刪除購物項目
                                    if(!$_POST['via_ajax']){
                                        $this->ws_tpl_file = "templates/ws-cart-tpl.html";
                                        $this->ws_load_tp($this->ws_tpl_file);
                                        $tpl->newBlock("JS_MAIN");
                                        $tpl->newBlock("JS_POP_IMG");
                                        $this->ws_tpl_type=1;
                                    }
                                    $this->cart_del($_POST['via_ajax']);
                                    break;                            
				case "c_add" :
//					$this -> ws_tpl_file = "templates/ws-cart-tpl.html";
//					$this -> ws_load_tp($this -> ws_tpl_file);
//					$this -> ws_tpl_type = 1;
                                        $this -> cart_add();
                                        break;
				case "c_quick_add" :
					//快速購物項目
					$this->cart_quick_add();        
					break;
				case "c_finish" :
					$this->ws_tpl_file = "templates/ws-cart-finish-tpl.html";
					$this->ws_load_tp($this->ws_tpl_file);
					$tpl->newBlock("JS_FORMVALID");
					App::getHelper('main')->res_init("date", "zone", "get", "box");
					$this->cart_finish();
					$this->ws_tpl_type = 1;
					break;
				case "c_preview" :
					$this->ws_tpl_file = "templates/ws-cart-preview-tpl.html";
					$this->ws_load_tp($this->ws_tpl_file);
					$this->cart_preview();
                                        App::getHelper('main')->load_privacy_term();
					$this->ws_tpl_type = 1;
					break;
				case "c_replace" :
					$this->cart_replace();
					break;
				case "c_ajax" :
					$form = $this->ajax_form();
	
					switch($form["ajax_act"]) {
						case "c_mod" :
							$this->cart_mod($form);
							break;
						case "c_del" :
							$this->cart_del($form);
							break;
					}
					echo $form["ajax_top"];
					break;
				case "c_order" :
					if (!$this->m_id) {
						$this->ws_tpl_file = "../templates/ws-login-form-tpl.html";
						$this->ws_load_tp($this->ws_tpl_file);
						$this->member_login();
					} else {
						$this->ws_tpl_file = "templates/ws-cart-order-tpl.html";
						$this->ws_load_tp($this->ws_tpl_file, 1);
						$this->cart_order();
					}
					$this->ws_tpl_type = 1;
					break;
				case "c_order_detial" :
					if (!$this->m_id) {
						$this->ws_tpl_file = "../templates/ws-login-form-tpl.html";
						$this->ws_load_tp($this->ws_tpl_file);
						$this->member_login();
					} else {
						$this->ws_tpl_file = "templates/ws-cart-order-tpl.html";
						$this->ws_load_tp($this->ws_tpl_file, 1);
						$this->cart_order_detail();
					}
					$this->ws_tpl_type = 1;
					break;
				case "c_check" :
					$this->cart_check();
					break;
				/*
				 case "c_clear":
				 session_unset();
				 break;
				 */
				default :
					$this->ws_tpl_file = "templates/ws-cart-tpl.html";
					$this->ws_load_tp($this->ws_tpl_file);
                                        $tpl->newBlock("JQUERY_UI_SCRIPT");
					$this->cart_list();
					$this->ws_tpl_type = 1;
					break;
			}
	
			if ($this->ws_tpl_type) {
				App::getHelper('main')->layer_link();
				$tpl->printToScreen();
			}
		}
	
		function ws_load_tp($ws_tpl_file, $member_left = 0) {
			global $tpl, $cms_cfg, $ws_array, $db, $TPLMSG, $main;
			$tpl = new TemplatePower('../'.$cms_cfg['base_all_tpl']);
			$tpl->assignInclude("HEADER", '../'.$cms_cfg['base_header_tpl']);
			//頭檔title,meta,js,css
			if (empty($member_left)) {
				$tpl->assignInclude("LEFT", '../'.$cms_cfg['base_left_normal_tpl']);
				//左方首頁表單
			} else {
				$tpl->assignInclude("LEFT", '../'.$cms_cfg['base_left_member_tpl']);
				//左方首頁表單
			}
			$tpl->assignInclude("TOP", $cms_cfg['base_top_tpl']);
			// 上方選單
			$tpl->assignInclude("FOOTER", $cms_cfg['base_footer_tpl']);
			// 版權宣告
			$tpl->assignInclude("MAIN", $ws_tpl_file);
			//主功能顯示區
			$tpl->prepare();
			if (empty($member_left)) {
				$tpl->assignGlobal("TAG_CATE_TITLE", $ws_array["left"]["products"]);
			} else {
				$tpl->assignGlobal("TAG_CATE_TITLE", $ws_array["left"]["member"]);
			}
			//左方menu title
			$tpl->assignGlobal("TAG_PRODUCTS_CURRENT", "class='current'");
			//上方menu current
			$tpl->assignGlobal("TAG_MAIN", $ws_array["main"]["products"]);
			//此頁面對應的flash及圖檔名稱
			$tpl->assignGlobal("TAG_MAIN_CLASS", "main-products");
			//主要顯示區域的css設定
			$main->layer_link(($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"])?$TPLMSG['CART_SHOPPING']:$TPLMSG['CART_INQUIRY']);
			$main->header_footer("");
			$main->google_code();
			//google analystics code , google sitemap code
			$main->left_fix_cate_list();
	
			$tpl->newBlock("JS_MAIN");
			$tpl->newBlock("JS_POP_IMG");
	
			// 共通使用程式
			//$main->share_function();
		}
	
		## 主要功能 ######################################################################################
	
		// 增加購買產品
		function cart_add() {
			global $cms_cfg,$TPLMSG;
                        App::getHelper('session')->CONTINUE_SHOPPING_URL=$_SERVER['HTTP_REFERER'];
                        $amount_arr = is_array($_REQUEST["amount"])?$_REQUEST["amount"]:(array)$_REQUEST["amount"];
                        $p_id_arr = is_array($_REQUEST["p_id"])?$_REQUEST["p_id"]:(array)$_REQUEST["p_id"];
                        $ps_id_arr = is_array($_REQUEST["ps_id"])?$_REQUEST["ps_id"]:(array)$_REQUEST["ps_id"];
                        $c_id_arr = is_array($_REQUEST["c_id"])?$_REQUEST["c_id"]:(array)$_REQUEST["c_id"];
                        $stockStatus = array();
                        foreach($p_id_arr as $k => $p_id){
                            if($p_id){
                                $amount = $amount_arr[$k]?$amount_arr[$k]:1;
                                if(isset($_GET['addpurchase'])){
                                    $result = (int)$this->container->put_addPurchase($c_id_arr[$k],$p_id,$amount);
                                }else{
                                    if($cms_cfg['ws_module']['ws_cart_spec']){
                                        $result = (int)$this->container->put($p_id,$amount,$ps_id_arr[$k]);
                                    }else{
                                        $result = (int)$this->container->put($p_id,$amount);
                                    }
                                }
                                $stockStatus[$result]+=1;
                            }
                        }
			if (!App::getHelper('request')->isAjax()) {
                                if(App::getHelper('session')->sc_cart_type==1 && $stockStatus[0]>0){
                                    App::getHelper('main')->js_notice($TPLMSG['INVENTORY_SHORTAG_NOTIFY'],$_SERVER['HTTP_REFERER']);
                                    die();
                                }
				if( $this->container->count() ){
					header('location:'.$_SERVER['PHP_SELF']);
                                        die();
				} else {
					$this->error_handle();
				}
			} else {
                                $res['code'] = 1;
                                $res['cart_nums'] = $this->container->count();
                                $res['cart_info'] = $this->container->get_cart_info();
                                if(App::getHelper('session')->sc_cart_type==1 && $stockStatus[0]>0){
                                    $res['code'] = 0;
                                    $res['msg'] = $TPLMSG['INVENTORY_SHORTAG_NOTIFY'];
                                }
                                echo json_encode($res);
                                die();
			}
		}
	
		//快速購物
		function cart_quick_add() {
			global $cms_cfg;
			$_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL'] = $_SERVER['HTTP_REFERER'];
			$option = "";
			foreach ($_REQUEST["amount"] as $key => $value) {
				if ($value != "") {
					$sess = $this->sess_code($key, $option);
					$_SESSION[$cms_cfg['sess_cookie_name']]["id"][$sess] = $key;
					$_SESSION[$cms_cfg['sess_cookie_name']]["num"][$sess] = $value;
				}
			}
			header("location:index.php");
			die();
		}
	
		// 清單顯示
		function cart_list() {
			global $cms_cfg, $tpl, $TPLMSG, $ws_array;
                        if(!App::gethelper('request')->isAjax() && $this->container->count()==0){ //空購物車時，回到前一頁
                            App::getHelper('main')->js_notice($TPLMSG['CART_EMPTY'],$cms_cfg['base_root']."products.htm");
                            die();
                        }  
			$tpl->assignGlobal(array(
                            "MSG_ID" => ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) ? $TPLMSG["ORDER_ID"] : $TPLMSG["INQUIRY_ID"], 
                            "MSG_NAME" => $TPLMSG['MEMBER_NAME'], 
                            "MSG_CONTENT" => $TPLMSG['CONTENT'], 
                            "MSG_MODIFY" => $TPLMSG['MODIFY'], 
                            "MSG_DEL" => $TPLMSG['DEL'], 
                            "MSG_TOTAL" => $TPLMSG['CART_TOTAL'], 
                            "MSG_SHIPPING_PRICE" => $TPLMSG['SHIPPING_PRICE'], 
                            "MSG_SUBTOTAL" => $TPLMSG['CART_SUBTOTAL'], 
                            "MSG_AMOUNT" => $TPLMSG['CART_AMOUNT'], 
                            "MSG_PRODUCT" => $TPLMSG['PRODUCT'], 
                            "MSG_SPEC" => $TPLMSG['CART_SPEC_TITLE'],
                            "MSG_PRODUCT_IMAGE" => $TPLMSG['PRODUCT_IMG'], 
                            "VALUE_MODIFY_AMOUNT" => $TPLMSG['CART_MODIFY_AMOUNT'], 
                            "TAG_DELETE_CHECK_STR" => $TPLMSG['CART_DELETE_CHECK'], 
                            "TAG_MAIN_FUNC" => ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) ? $TPLMSG['CART_SHOPPING'] : $TPLMSG['CART_INQUIRY'], 
                            "TAG_SCROLL_TOP" => $_REQUEST["top"], 
                            "MSG_PRODUCT_PRICE" => $TPLMSG['PRODUCT_SPECIAL_PRICE'],
                            "MSG_DISCOUNT" => $TPLMSG['QUANTITY_DISCOUNT'],
                        ));
	
			if ($this->container->count() > 0) {
				$tpl->newBlock("TAG_CART_ZONE");
				$tpl->assignGlobal(array(
                                    "MSG_CONTINUE_SHOPPING" => $TPLMSG['CART_CONTINUE_SHOPPING'], 
                                    "MSG_FINISH_SHOPPING" => $TPLMSG['CART_FINISH_SHOPPING'], 
                                    "LINK_CONTINUE" => $_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL'], 
                                    'MSG_NEXT_STEP' => $TPLMSG['CART_STEP_NEXT'],
                                    'MSG_DEL_DIALOG_TITLE'   => $TPLMSG['DEL_CART_ITEM'],
                                    'MSG_DEL_DIALOG_CONTENT' => $TPLMSG['SURE_TO_DELETE'],
                                    'MSG_SHIP_ZONE' => $TPLMSG['ORDER_SHIP_ZONE'],
                                    'STR_BTN_DEL_CONFIRM' => $TPLMSG['OK'] ,
                                    'STR_BTN_DEL_CANCEL'  => $TPLMSG['CANCEL'] ,
                                ));
                                //送貨區域
                                $source_of_shipment = Model_Shipprice::getShipmentSource();
                                App::getHelper('main')->multiple_radio("shipment_type",$source_of_shipment,$this->container->get_shipment_type(),$tpl);                                
                                if($cms_cfg['ws_module']['ws_cart_spec']){
                                    $tpl->assignGlobal("CART_FIELDS_NUMS",6);
                                    $tpl->newBlock("SPEC_TITLE");
                                }else{
                                    $tpl->assignGlobal("CART_FIELDS_NUMS",5);
                                }                                
                                $gift = $this->container->getModule("giftor")->getGift($this->giftId);
                                $this->container->calculate();
                                $cartProducts = $this->container->get_cart_products();
                                if(App::configs()->ws_module->ws_cart_plus_shopping){
                                    $additionalPurchaseProducts = $this->container->getModule("conditioner")->getAdditionalPurchaseProducts();
                                }
				foreach ($cartProducts as $p_id =>  $row) {
	
					$tpl->newBlock("TAG_CART_LIST");
					$p_link = $this->p_link_handle($row);
                                        $valid_stocks = !App::configs()->ws_module->ws_products_stocks || $this->container->stockChecker->check($row['p_id'],$row['amount'],$row['ps_id']);        
					$tpl->assign(array(
                                            "VALUE_P_ID" => $row['p_id'], 
                                            "VALUE_P_NAME" => $row["p_name"] . (!$valid_stocks?"<span>庫存不足</span>":""),
                                            "VALUE_P_SMALL_IMG" => (trim($row["p_small_img"]) == "") ? $cms_cfg['default_preview_pic'] : $cms_cfg["file_url"] . $row["p_small_img"], 
                                            "VALUE_P_SPEC" => $row["spec"], 
                                            "VALUE_P_AMOUNT" => $row["amount"], 
                                            "TAG_QUANTITY_DISCOUNT" => ($row['discount']<1)?$row['discount']:'',
                                            "VALUE_P_SPECIAL_PRICE" => $row['price'],
                                            "VALUE_P_SUBTOTAL_PRICE" => $row['subtotal_price'],
                                            "VALUE_P_LINK" => $p_link, 
                                            "VALUE_P_SERIAL" => $i + 1, 
                                            "VALUE_P_SESS" => $sess, 
                                            "CART_P_ID" => $p_id,
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
                                        if($row['addPurchase']){
                                            $tpl->assign(array(
                                                "TAG_C_ID" => "cid='".$row['c_id']."'",
                                            ));
                                        }
                                        if($cms_cfg['ws_module']['ws_cart_spec']){
                                            $tpl->newBlock("SPEC_FIELD");
                                            $tpl->assign("VALUE_SPEC",$row["spec"]);
                                        }
                                        
                                        $c_num_top = $row['limit']?$row['limit']:$this->c_num;
					for ($c_num = $this->c_num_set; $c_num <= $c_num_top; $c_num++) {
						$tpl->newBlock("TAG_CART_NUM");
						$tpl->assign(array(
                                                    "VALUE_CART_NUM" => $c_num, 
                                                    "VALUE_CART_CURRENT" => ($c_num == $row["amount"]) ? 'selected' : '', 
                                                ));
					}

					$i++;
					$tpl->gotoBlock("SHOPPING_CART_ZONE");
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
                                        "VALUE_P_SERIAL" => $i+1, 
                                        "CART_P_ID" => $p_id,                                             
                                    ));

                                    if($cms_cfg['ws_module']['ws_cart_spec']){
                                        $tpl->newBlock("GIFT_SPEC_FIELD");
                                        $tpl->assign("VALUE_SPEC",$gift["spec"]);
                                    }                                    
                                }                                           
				//購物車時輸出服務條款
				if ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) {
					$this->service_rule();
				}
                                
                                if($additionalPurchaseProducts){
                                    $tpl->newBlock("ADD_PURCHASE_ZONE");
                                    foreach($additionalPurchaseProducts as $c_id => $condition){
                                        $tpl->newBlock("CONDITION_LIST");
                                        $tpl->assign(array(
                                            'price' => $condition['price'],
                                        ));
                                        foreach($condition['products'] as $addProd){
                                            $tpl->newBlock("ADD_PROD");
                                            $img = $addProd['p_small_img']?App::configs()->file_root . $addProd['p_small_img']: App::configs()->default_preview_pic;
                                            $dimension = App::getHelper('main')->resizeto($img,190,190);
                                            $tpl->assign(array(
                                                "VALUE_C_ID"   => $c_id,
                                                "VALUE_P_ID" => $addProd['p_id'],
                                                "VALUE_P_NAME" => $addProd['p_name'],
                                                "VALUE_P_LINK" => App::getHelper('request')->get_link("products",$addProd),
                                                "VALUE_P_SMALL_IMG" => $img,
                                                "VALUE_P_SMALL_IMG_W" => $dimension['width'],
                                                "VALUE_P_SMALL_IMG_H" => $dimension['height'],
                                                "VALUE_P_SPECIAL_PRICE" => $addProd['p_special_price'],
                                                "VALUE_P_ADD_PRICE"     => $addProd['price'],
                                            ));
                                        }
                                    }
                                }
                                //輸出額外費用及總價
                                $cartInfo = $this->container->get_cart_info();
                                $tpl->newBlock("TAG_PLUS_FEE");
                                $tpl->assign(array(
                                    "MSG_PLUS_FEE" => $TPLMSG["PLUS_FEE"], 
                                    "VALUE_PLUS_FEE" => $cartInfo['charge_fee'], 
                                ));
                                $tpl->newBlock("TAG_PRICE_TOTAL");
				$tpl->assign(array(
                                    "VALUE_SHIPPING_PRICE" => $cartInfo['shipping_price'], 
                                    "VALUE_SUBTOTAL" => $cartInfo['subtotal_price'], 
                                    "VALUE_TOTAL" => $cartInfo['total_price'], 
                                ));
                                //購物車時輸出服務條款
                                if ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) {
                                        $this->service_rule();
                                }
	
				if ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"] && ($_REQUEST["func"] == "c_add" || empty($_REQUEST["func"]))) {
					// 顯示付款方式					
					$tpl->newBlock("PAYMENT_TYPE");
					
					// 7-11 關閉代碼付費提示
					if(class_exists('ALLPAY',false)){
						$cvs_mark[] = array_search($TPLMSG["CVS"], $ws_array["payment_type"]);
						$cvs_mark[] = array_search($TPLMSG["BARCODE"], $ws_array["payment_type"]);
						
						if(is_array($cvs_mark)){
							foreach($cvs_mark as $payment_key){
								$ws_array["payment_type"][$payment_key] = '<span><img src="'.$cms_cfg["base_images"].'cvs_store.png" width="100" border="0" style="display: inline-block; vertical-align: top;"></span>'.$ws_array["payment_type"][$payment_key];
							}
						}
						
						$tpl->newBlock("7_11_CVS_CLOSE");
						$tpl->gotoBlock("PAYMENT_TYPE");
					}
					
					$tpl->assign("MSG_PAYMENT_TYPE", $TPLMSG["PAYMENT_TYPE"]);
					foreach ($ws_array["payment_type"] as $key => $payment) {
						$tpl->newBlock("PAYMENT_TYPE_ITEMS");
						$tpl->assign(array(
                                                        "VALUE_PAYMENT_TYPE" => $key, 
                                                        "VALUE_PAYMENT_TYPE_STR" => $payment, 
                                                        "VALUE_PAYMENT_CURRENT" => (strcmp($key , $this->container->get_payment_type())==0) ? 'checked' : '', 
                                                ));
					}
				}
			} else {
				$this->error_handle();
			}
		}
	
		// 完成訂單清單
		function cart_finish() {
			global $tpl, $db, $cms_cfg, $TPLMSG, $main, $ws_array;
                        if($this->activateStockChecker && $this->container->checkCartStocks()===false){ //l購物車裡有產品庫存不足
                            App::getHelper('main')->js_notice($TPLMSG['INVENTORY_SHORTAG_NOTIFY'],$_SERVER['PHP_SELF']);
                            die();
                        }
                        $payment_type = $this->container->get_payment_type();                        
			if (empty($this->m_id) && $cms_cfg["ws_module"]["ws_cart_login"] == 1 && empty($_REQUEST["first"])) {
				//驗証碼
				$_SESSION[$cms_cfg['sess_cookie_name']]["ERROR_MSG"] = "";
				//清空錯誤訊息
				$tpl->newBlock("MEMBER_LOGIN_FORM");
				$tpl->assignGlobal(array(
                                    "MSG_LOGIN_ACCOUNT" => $TPLMSG["LOGIN_ACCOUNT"], 
                                    "MSG_LOGIN_PASSWORD" => $TPLMSG["LOGIN_PASSWORD"], 
                                    "MSG_LOGIN_BTN" => $TPLMSG["LOGIN_BUTTON"], 
                                    "MSG_FIRST_BTN" => ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"])?$TPLMSG['FIRST_S_BTN']:$TPLMSG['FIRST_I_BTN'], 
                                    "MSG_MEMBER_LOGIN" => $TPLMSG["MEMBER_LOGIN"], "TAG_FS_SHOPPING" => $TPLMSG['FIRST_TIME_SHOPPING'], 
                                    'TAG_LOGIN_MESSAGE1' => $TPLMSG['CART_LOGIN_MESSAGE1'],
                                    'TAG_LOGIN_MESSAGE2' => $TPLMSG['CART_LOGIN_MESSAGE2'],
                                ));
	
				//載入驗証碼
				$main->security_zone();
			} else {
				$this->cart_list();
	
				$tpl->newBlock("MEMBER_DATA_FORM");
				$tpl->assignGlobal($this->basic_lang);
	
				$this->member_detail();
				$this->taiwan_zone_select();
                                //地址欄位格式
                                if($cms_cfg['ws_module']['ws_address_type']=='tw'){
                                    $tpl->newBlock("TW_ADDRESS");
                                }else{
                                    $tpl->newBlock("SINGLE_ADDRESS");
                                }
				if ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) {
					// 顯示付款方式
					// 檢查是否選擇付款方式
					if ($payment_type=="") {
						$tpl -> assignGlobal("MSG_PAYMENT_ALERT", 'alert("' . $TPLMSG['NO_PAYMENT'] . '"); location.href = "' . $cms_cfg["base_root"] . 'cart/"');
					}
	
					$tpl->newBlock("PAYMENT_TYPE");
					$tpl->assign(array("MSG_PAYMENT_TYPE" => $TPLMSG["PAYMENT_TYPE"], "VALUE_PAYMENT_TYPE" => $ws_array["payment_type"][$payment_type], ));
                                        
                                        //運送區域
                                        $source_of_shipment = Model_Shipprice::getShipmentSource();          
                                        $shipment_type = $this->container->get_shipment_type();
                                        $tpl->assignGlobal("VALUE_SHIPMENT_ZONE",$source_of_shipment[$shipment_type]);

					// 購物收件人表單
					$tpl->newBlock("TAG_ADDRESSEE_BLOCK");
					$tpl->assignGlobal($this->adv_lang);
                                        //收件者地址欄位格式
                                        if($cms_cfg['ws_module']['ws_address_type']=='tw'){
                                            $tpl->newBlock("TW_ADDRESS_RECI");
                                        }else{
                                            $tpl->newBlock("SINGLE_ADDRESS_RECI");
                                        }
					// 到貨日期
					$tpl->assignGlobal(array("VALUE_ARRIVAL_START" => $this->arrival_start, "VALUE_ARRIVAL_RANGE" => $this->arrival_range, ));
					//發票類型
					foreach ($ws_array['invoice_type'] as $type_id => $type_label) {
						$tpl->newBlock("INVOICE_TYPE_LIST");
						$tpl->assign(array("VALUE_INVOICE_ID" => $type_id, "VALUE_INVOICE_LABEL" => $type_label, ));
					}
				}
	
				if (empty($this->m_id) && !empty($cms_cfg["ws_module"]["ws_cart_login"])) {
					$tpl->newBlock("TAG_NEW_MEMBER_REGIST");
					$tpl->assign(array("MSG_ACCOUNT" => $TPLMSG["LOGIN_ACCOUNT"], "MSG_PASSWORD" => $TPLMSG["LOGIN_PASSWORD"], "MSG_VALID_PASSWORD" => $TPLMSG['MEMBER_CHECK_PASSWORD'], ));
				}
			}
		}
	
		// 預覽訂單
                function cart_preview() {
			global $db, $tpl, $cms_cfg, $TPLMSG, $main, $allpay, $ws_array;
                        if($this->activateStockChecker && $this->container->checkCartStocks()===false){ //l購物車裡有產品庫存不足
                            App::getHelper('main')->js_notice($TPLMSG['INVENTORY_SHORTAG_NOTIFY'],$_SERVER['PHP_SELF']);
                            die();
                        }
                        $main->magic_gpc($_POST);
			$this->cart_list();
                        $payment_type = $this->container->get_payment_type();

                        //處理地址欄位
                        $map = array('target'=>'address','rmTarget'=>array('city','area'));
                        $type = array('o_','o_add_');
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
                        $tpl->assignGlobal(array(
                            'MSG_ORDER_INFO' => $TPLMSG['ORDER_BLOCK_TITLE_ORDER'],
                            'BTN_MODIFY' => $TPLMSG['ORDER_PREVIEW_MODIFY'],
                            'BTN_FINISH' => $TPLMSG['ORDER_PREVIEW_FINISH'],
                        ));
			$tpl->newBlock("MEMBER_DATA_FORM");
			$tpl->assignGlobal($this->basic_lang);
                        $m_name = sprintf($TPLMSG["MEMBER_NAME_IN_CART_TEMPLATE"],$_POST['m_fname'],$_POST['m_lname']);
                        $contact_s = App::getHelper('main')->multi_map_value($ws_array["contactus_s"],$_POST["m_contact_s"]);
                        $name_template = $this->name_s_struct[App::configs()->ws_module->ws_contactus_s_style];
                        $name_with_s = sprintf($name_template,$contact_s,$m_name);
			$tpl->assign(array("VALUE_O_ID" => $this->o_id, "VALUE_M_COMPANY_NAME" => $_POST["m_company_name"],
                            //"VALUE_M_CONTACT_S" => $this->gender_list($_POST["m_contact_s"],1),
                            //"VALUE_M_NAME" => $_POST["m_name"],
                            "VALUE_M_NAME" => $name_with_s, 
                            "VALUE_M_ZIP" => $_POST["o_zip"], 
                            "VALUE_M_ADDRESS" => $_POST["o_city"].$_POST["o_area"].$_POST["o_address"], 
                            "VALUE_M_TEL" => $_POST["m_tel"], 
                            "VALUE_M_FAX" => $_POST["m_fax"], 
                            "VALUE_M_EMAIL" => $_POST["m_email"], 
                            "VALUE_M_CELLPHONE" => $_POST["m_cellphone"], 
                            "VALUE_CONTENT" => $_POST["content"], 
                        ));
	
			if ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"] == 1) {
				// 顯示付款方式
				$tpl->newBlock("PAYMENT_TYPE");
				$tpl->assign(array(
                                    "MSG_PAYMENT_TYPE" => $TPLMSG["PAYMENT_TYPE"], 
                                    "VALUE_PAYMENT_TYPE" => $ws_array["payment_type"][$payment_type], 
                                ));
                                
                                //運送區域
                                $source_of_shipment = Model_Shipprice::getShipmentSource();          
                                $shipment_type = $this->container->get_shipment_type();
                                $tpl->assignGlobal("VALUE_SHIPMENT_ZONE",$source_of_shipment[$shipment_type]);
                                
				// 收件人資訊
                                $m_reci_name = sprintf($TPLMSG["MEMBER_NAME_IN_CART_TEMPLATE"],$_POST['m_reci_fname'],$_POST['m_reci_lname']);
                                $reci_contact_s = App::getHelper('main')->multi_map_value($ws_array["contactus_s"],$_POST["m_reci_contact_s"]);
                                $reci_name_with_s = sprintf($name_template,$reci_contact_s,$m_reci_name);	
				$tpl->newBlock("TAG_ADDRESSEE_BLOCK");
				$tpl->assignGlobal($this->adv_lang);
				$tpl->assign(array(
                                    //"VALUE_ADD_NAME" => $_POST["o_add_name"], 
                                    "VALUE_ADD_NAME" => $reci_name_with_s, 
                                    "VALUE_ADD_TEL" => $_POST["o_add_tel"],
                                    "VALUE_ADD_CELLPHONE" => $_POST["o_add_cellphone"], 
                                    "VALUE_ADD_ZIP" => $_POST["o_add_zip"],
                                    "VALUE_ADD_ADDRESS" => $_POST["o_add_city"].$_POST["o_add_area"].$_POST["o_add_address"],
                                    "VALUE_ADD_MAIL" => $_POST["o_add_mail"], 
                                    "VALUE_INVOICE_TYPE" => $ws_array['invoice_type'][$_POST['o_invoice_type']], 
                                    "VALUE_INVOICE_NAME" => $_POST["o_invoice_name"], 
                                    "VALUE_INVOICE_VAT" => $_POST["o_invoice_vat"], 
                                    "VALUE_INVOICE_TEXT" => $_POST["o_invoice_text"], 
                                ));
			}
	
			// 到貨時間
			if (is_array($_POST["o_arrival_time"])) {
				$o_arrival_time = implode("-", $_POST["o_arrival_time"]);
				$tpl->assign("VALUE_ARRIVAL_TIME", $o_arrival_time);
			}
	
			// 國家欄位
			if ($cms_cfg["ws_module"]["ws_country"] == 1) {
				$tpl->newBlock("MEMBER_DATA_COUNTRY_ZONE");
				$tpl->assign(array("MSG_COUNTRY" => $TPLMSG['COUNTRY'], "VALUE_M_COUNTRY" => $_POST["m_country"]));
			}
			//輸出post暫存
			foreach ($_POST as $k => $v) {
				if (!is_array($v)) {
					$tpl->newBlock("TMP_POST_FIELD");
					$tpl->assign(array("TAG_POST_KEY" => $k, "TAG_POST_VALUE" => htmlspecialchars($v), ));
				} else {
					foreach ($v as $split_v) {
						$tpl->newBlock("TMP_POST_FIELD");
						$tpl->assign(array("TAG_POST_KEY" => $k.'[]', "TAG_POST_VALUE" => htmlspecialchars($split_v), ));
					}
				}
			}
	
		}
	
		// 送出訂單
		function cart_replace() {
			global $db, $tpl, $cms_cfg, $TPLMSG, $main, $allpay, $ws_array;
	
                        if($this->activateStockChecker && $this->container->checkCartStocks()===false){ //l購物車裡有產品庫存不足
                            App::getHelper('main')->js_notice($TPLMSG['INVENTORY_SHORTAG_NOTIFY'],$_SERVER['PHP_SELF']);
                            die();
                        }
                        $main->magic_gpc($_REQUEST);                        
			$this->o_id = $this->o_id_generator();
                        $shipment_type = $this->container->get_shipment_type();
                        $payment_type = $this->container->get_payment_type();
                        
			$this->ws_tpl_file = "templates/ws-mail-tpl.html";
			$tpl = new TemplatePower($this->ws_tpl_file);
			$tpl->prepare();
			$tpl->assignGlobal("TAG_BASE_CSS", $cms_cfg['base_mail_css']);
	
			$this->cart_list();
	
			// 台灣區域選擇
                        $o_address = $_REQUEST["o_city"].$_REQUEST["o_area"].$_REQUEST["o_address"];
	
			$tpl->newBlock("MEMBER_DATA_FORM");
                        $o_name = sprintf($TPLMSG["MEMBER_NAME_IN_CART_TEMPLATE"],$_REQUEST['m_fname'],$_REQUEST['m_lname']);
                        $contact_s = App::getHelper('main')->multi_map_value($ws_array["contactus_s"],$_REQUEST["m_contact_s"]);
                        $name_template = $this->name_s_struct[App::configs()->ws_module->ws_contactus_s_style];
                        $name_with_s = sprintf($name_template,$contact_s,$o_name);
			$tpl->assignGlobal($this->basic_lang);
			$tpl->assign(array(
                            "VALUE_O_ID" => $this->o_id, 
                            "VALUE_M_COMPANY_NAME" => $_REQUEST["m_company_name"],
                            //"VALUE_M_CONTACT_S" => $this->gender_list($_REQUEST["m_contact_s"],1),
                            //"VALUE_M_NAME" => $_REQUEST["m_name"],
                            //"VALUE_M_NAME" => (empty($this->gender_select))?$this->gender_list($_REQUEST["m_contact_s"], 1).'&nbsp;'.$_REQUEST["m_name"]:$_REQUEST["m_name"].'&nbsp;'.$this->gender_list($_REQUEST["m_contact_s"], 1), 
                            "VALUE_M_NAME" => $name_with_s, 
                            "VALUE_M_ZIP" => $_REQUEST["o_zip"], 
                            "VALUE_M_ADDRESS" => $o_address, 
                            "VALUE_M_TEL" => $_REQUEST["m_tel"], 
                            "VALUE_M_FAX" => $_REQUEST["m_fax"], 
                            "VALUE_M_EMAIL" => $_REQUEST["m_email"], 
                            "VALUE_M_CELLPHONE" => $_REQUEST["m_cellphone"], 
                            "VALUE_CONTENT" => $_REQUEST["content"], 
                        ));
	
			// 新增會員
			$this->new_member();
	
			if (App::getHelper('session')->sc_cart_type == 1) {
				// 顯示付款方式
				$tpl->newBlock("PAYMENT_TYPE");
				$tpl->assign(array(
                                    "MSG_PAYMENT_TYPE" => $TPLMSG["PAYMENT_TYPE"], 
                                    "VALUE_PAYMENT_TYPE" => $ws_array["payment_type"][$payment_type], 
                                ));
                                
                                //運送區域
                                $source_of_shipment = Model_Shipprice::getShipmentSource();          
                                $tpl->assignGlobal("VALUE_SHIPMENT_ZONE",$source_of_shipment[$shipment_type]);                                

				// 收件人資訊
                                $m_reci_name = sprintf($TPLMSG["MEMBER_NAME_IN_CART_TEMPLATE"],$_REQUEST['m_reci_fname'],$_REQUEST['m_reci_lname']);
                                $reci_contact_s = App::getHelper('main')->multi_map_value($ws_array["contactus_s"],$_REQUEST["m_reci_contact_s"]);
                                $o_add_name = sprintf($name_template,$reci_contact_s,$m_reci_name);                                
				$tpl->newBlock("TAG_ADDRESSEE_BLOCK");
				$tpl->assignGlobal($this->adv_lang);
				$tpl->assign(array(
                                    //"VALUE_ADD_NAME" => $_REQUEST["o_add_name"], 
                                    "VALUE_ADD_NAME" => $o_add_name,
                                    "VALUE_ADD_TEL" => $_REQUEST["o_add_tel"], 
                                    "VALUE_ADD_CELLPHONE" => $_REQUEST["o_add_cellphone"], 
                                    "VALUE_ADD_ZIP" => $_REQUEST["o_add_zip"], 
                                    "VALUE_ADD_ADDRESS" => $_REQUEST["o_add_city"].$_REQUEST["o_add_area"].$_REQUEST["o_add_address"],
                                    "VALUE_ADD_MAIL" => $_REQUEST["o_add_mail"], 
                                    "VALUE_INVOICE_TYPE" => $ws_array['invoice_type'][$_REQUEST['o_invoice_type']], 
                                    "VALUE_INVOICE_NAME" => $_REQUEST["o_invoice_name"], 
                                    "VALUE_INVOICE_VAT" => $_REQUEST["o_invoice_vat"], 
                                    "VALUE_INVOICE_TEXT" => $_REQUEST["o_invoice_text"], 
                                ));
			}
	
			// 到貨時間
			if (is_array($_REQUEST["o_arrival_time"])) {
				$o_arrival_time = implode("-", $_REQUEST["o_arrival_time"]);
				$tpl->assign("VALUE_ARRIVAL_TIME", $o_arrival_time);
			}
	
			// 國家欄位
			if ($cms_cfg["ws_module"]["ws_country"] == 1) {
				$tpl->newBlock("MEMBER_DATA_COUNTRY_ZONE");
				$tpl->assign(array(
                                    "MSG_COUNTRY" => $TPLMSG['COUNTRY'], 
                                    "VALUE_M_COUNTRY" => $_REQUEST["m_country"]
                                ));
			}
                        //將m_換成o_
                        foreach($_REQUEST as $k=>$v){
                            if(preg_match("/^m_(\w+)$/", $k,$match)){
                                $_REQUEST['o_'.$match[1]] = $v;
                            }
                        }
                        
                        $billList = $this->container->get_cart_info();                    
	
			// 寫入訂單
                        $orderData = array_merge($_REQUEST,array(
                            'o_id'         => $this->o_id,
                            'm_id'         => $this->m_id,
                            'o_status'     => 0,
                            'o_createdate' => date("Y-m-d H:i:s"),
                            'o_account'    => App::getHelper('session')->MEMBER_ACCOUNT,
                            'o_ship_price'     => $billList['shipping_price'],
                            'o_fee_price'     => $billList['charge_fee'],
                            'o_subtotal_price' => $billList['subtotal_price'],
                            'o_total_price'    => $billList['total_price'],
                            'o_shipment_type' => $shipment_type,
                            'o_payment_type'   => $payment_type,
                            'o_arrival_time'   => $o_arrival_time,
                            'o_address'        => $o_address,
                            'o_content'        => $_REQUEST["content"],
                            'o_name'           => $o_name,
                            'o_add_name'       => $o_add_name,
                        ));
                        //啟用美安訂單及有RID才寫入rid
                        if(App::configs()->ws_module->ws_rid_order && App::getHelper('session')->RID){
                            $orderData = array_merge($orderData,array(
                                'rid' => App::getHelper('session')->RID,
                            ));
                        }
                        $shopping = $this->container->get_cart_products();
                        //如果必要欄位為空值，導回購物車列表
                        if(!$this->checkRequireFields($orderData)){
                            ob_start();
                            print_r($_SERVER);
                            echo "\n";
                            echo "訂單資料:\n";
                            print_r($orderData);
                            $serverInfo = ob_get_clean();
                            file_put_contents('../upload_files/'.date("YmdHis")."-".$_SERVER['REMOTE_ADDR'].'.log', $serverInfo);
                            $main->js_notice($TPLMSG['ORDER_DATA_SHORTAGE'],$_SERVER['PHP_SELF']);
                            die();
                        }
                        //寫入購買產品
                        //有贈品的話就寫入贈品
                        if($gift = $this->container->getModule("giftor")->getGift($this->giftId)){
                            $shopping[$this->giftId] = $gift;
                        }                        
                        foreach($shopping as $p_id => $prod_row){
                            $prod_row['m_id'] = $this->m_id;  //寫入記錄用
                            $prod_row['p_sell_price'] = $prod_row['price']; //寫入記錄用
                            $prod_row['oi_amount'] = $prod_row['amount'];  //寫入記錄用
                            $prod_row['p_price'] = $prod_row['price']; //給歐付寶資訊用
                            $prod_row['p_num'] = $prod_row['amount'];  //給歐付寶資訊用
                            $shopping[$p_id] = $prod_row;
                        }
                        App::getHelper('dbtable')->order->writeDataWithItems($orderData,$shopping,true);
                        
			App::getHelper('session')->paymentType = $payment_type;
			$this->mail_handle();
			switch(App::getHelper('session')->paymentType) {
				case 1 ://atm
				case 2 :
					//貨到付款
					$goto_url = $cms_cfg["base_url"]."shopping-result.php?status=OK&pno=".$this->o_id;
					header("location:".$goto_url);
					break;
			}
			// ALLPAY (歐付寶)
			if ($allpay->allpay_switch && App::getHelper('session')->sc_cart_type==1) {
				foreach ($allpay->all_cfg["allpay_type"] as $type => $str) {
					if ($payment_type == $type) {
						$allpay_payment = $type;
                                                break;
					}
				}
	
				if (!empty($allpay_payment)) {
					$allpay->allpay_send($this->o_id, // 訂單編號
						$billList['total_price'], // 交易總金額
						0, // 交易描述 (不可空值)
						$shopping, // 商品資訊 (array)
						$allpay_payment, // 交易方式
						0 // 選擇預設付款子項目
					);
	
					//$mail_goto = 1;
				}
			}
	
			$db_msg = $db->report();
			if ($db_msg == "") {
				$this->container->empty_cart();
			} else {
				$tpl->assignGlobal("MSG_ACTION_TERM", "DB Error: $db_msg, please contact MIS");
			}
		}
	
		// 修改數量
		function cart_mod($form = 0) {
			global $cms_cfg;
	
			if (!empty($form)) {
                                $stockStatus = array();
				foreach ($form as $sess_str => $num) {
					unset($sess_array);
					//echo $pass_num = (!is_int($num))?round($num):$num;
					$sess_array = explode("|", $sess_str);
                                        if(count($sess_array)==2){ //一般購物
                                            $key = explode(":",$sess_array[1]);
                                            $result = (int)$this->container->update($key[0],$num,$key[1]);
                                            $stockStatus[$result]+=1;
                                        }elseif(count($sess_array)==3){ //加購產品
                                            //update_addPurchase( $c_id, $p_id , $amount)
                                            $result = (int)$this->container->update_addPurchase($sess_array[2],$sess_array[1],$num);
                                        }
				}
                                $this->container->set_payment_type($form["o_payment_type"]);
                                $this->container->set_shipment_type($form["o_shipment_type"]);
			} else {
				$this->error_handle();
			}
		}
	
		// 刪除產品
//		function cart_del($form = 0) {
//			global $cms_cfg;
//	
//			if (!empty($form)) {
//				$sess_code = $form["ajax_del"];
//				unset($_SESSION[$cms_cfg['sess_cookie_name']]["id"][$sess_code]);
//				unset($_SESSION[$cms_cfg['sess_cookie_name']]["num"][$sess_code]);
//			} else {
//				$this->error_handle();
//			}
//		}
                
                function cart_del($via_ajax){
                    global $db,$tpl,$cms_cfg,$TPLMSG;
                    if($_POST["p_id"]){
                        if($_POST["ps_id"]){
                            $this->container->rm($_POST["p_id"],$_POST["ps_id"]);
                        }elseif($_POST["c_id"]){
                            $this->container->rm_addPurchase($_POST["p_id"],$_POST["c_id"]);
                        }else{
                            $this->container->rm($_POST["p_id"]);
                        }
                        //unset($_SESSION[$cms_cfg['sess_cookie_name']]["advance_ship_price"]);
                        if($this->container->count()==0){
                            $this->container->empty_cart();
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
                
	
		## 訂單紀錄顯示 ######################################################################################
	
		// 顯示訂單列表 , 取代原  member's orders
		function cart_order() {
			global $db, $tpl, $cms_cfg, $TPLMSG, $ws_array, $main;
	
			$sql = "select * from ".$db->prefix("order")." where m_id='".$this->m_id."' and del!='1' order by o_createdate desc";
			//取得總筆數
			$selectrs = $db->query($sql);
			$total_records = $db->numRows($selectrs);
	
			//取得分頁連結
			$func_str = $cms_cfg["base_root"]."cart/?func=c_order";
			$sql = $main->pagination($cms_cfg["op_limit"], $cms_cfg["jp_limit"], $_REQUEST["nowp"], $_REQUEST["jp"], $func_str, $total_records, $sql);
	
			$selectrs = $db->query($sql);
			$rsnum = $db->numRows($selectrs);
	
			if (!empty($rsnum)) {
				$tpl->newBlock("ORDER_LIST_ZONE");
				$tpl->assign(array("MSG_NAME" => $TPLMSG['MEMBER_NAME'], "MSG_STATUS" => $TPLMSG['STATUS'], "MSG_ORDER_ID" => $TPLMSG['ORDER_ID'], "MSG_CREATEDATE" => $TPLMSG['CREATEDATE'], "MSG_MODIFYDATE" => $TPLMSG['MODIFYDATE'], "MSG_VIEWS" => $TPLMSG['VIEWS'], ));
	
				if ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) {
					$tpl->newBlock("TAG_PRICE_TH");
					$tpl->assign("MSG_TOTAL_MONEY", $TPLMSG['ORDER_TOTAL_MONEY']);
					$main_func_str = $TPLMSG['MEMBER_ZONE_ORDER'];
				} else {
					$main_func_str = $TPLMSG['MEMBER_ZONE_INQUIRY'];
				}
	
				$tpl->assignGlobal(array("TAG_MAIN_FUNC" => $main_func_str, "TAG_LAYER" => $main_func_str, ));
	
				while ($row = $db->fetch_array($selectrs, 1)) {
					$i++;
	
					$tpl->newBlock("ORDER_LIST");
					$tpl->assign(array(
                                            "VALUE_O_ID" => $row["o_id"], 
                                            "VALUE_O_NAME" => $row["o_name"], 
                                            "VALUE_O_CREATEDATE" => $row["o_createdate"], 
                                            "VALUE_O_MODIFYDATE" => $row["o_modifydate"], 
                                            "VALUE_O_STATUS" => $ws_array["order_status"][$row["o_status"]], 
                                            "VALUE_O_SERIAL" => $i, 
                                            "VALUE_O_DETAIL" => $TPLMSG['DETAIL'], 
                                            "STATUS_CLASS"   => "order_status_".$row['o_status'],
                                        ));
	
					if ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) {
						$tpl->newBlock("TAG_PRICE_TD");
						$tpl->assign("VALUE_O_TOTAL_PRICE", $row["o_total_price"]);
					}
					if ($row['o_payment_type'] == 1 && $row['o_atm_last5'] == '' && $row['o_status'] == 0) {//新訂單未匯款的訂單
						$tpl->newBlock("UNATM_FIELD");
						$tpl->assign(array("VALUE_O_ID" => $row['o_id']));
					}
					if ($row['o_status'] == 0 && $cms_cfg['ws_module']['ws_order_cancel']) {
						$tpl->newBlock("BTN_CANCEL_ORDER");
						$tpl->assign("VALUE_O_ID", $row['o_id']);
					}
				}
	
				$tpl->newBlock("PAGE_DATA_SHOW");
				$tpl->assign(array("VALUE_TOTAL_RECORDS" => $page["total_records"], "VALUE_TOTAL_PAGES" => $page["total_pages"], "VALUE_PAGES_STR" => $page["pages_str"], "VALUE_PAGES_LIMIT" => $this->op_limit));
				if ($page["bj_page"]) {
					$tpl->newBlock("PAGE_BACK_SHOW");
					$tpl->assign("VALUE_PAGES_BACK", $page["bj_page"]);
					$tpl->gotoBlock("PAGE_DATA_SHOW");
				}
				if ($page["nj_page"]) {
					$tpl->newBlock("PAGE_NEXT_SHOW");
					$tpl->assign("VALUE_PAGES_NEXT", $page["nj_page"]);
					$tpl->gotoBlock("PAGE_DATA_SHOW");
				}
			} else {
				$tpl->assignGlobal("MSG_NO_DATA", $TPLMSG['NO_DATA']);
			}
		}
	
		// 顯示訂單詳細 , 取代原  member's orders
		function cart_order_detail() {
			global $db, $tpl, $cms_cfg, $TPLMSG, $ws_array;
	
			$sql = "select * from ".$db->prefix("order")." where m_id='".$this->m_id."' and o_id='".$_REQUEST["o_id"]."'";
			$selectrs = $db->query($sql);
			$rsnum = $db->numRows($selectrs);
	
			if (!empty($rsnum)) {
				$tpl->newBlock("ORDER_DETAIL_ZONE");
				$tpl->assignGlobal( array(
                                    "MSG_ORDER_DETAIL" => $TPLMSG['ORDER_DETAIL'], 
                                    "MSG_ORDER_CONTENT" => $TPLMSG['ORDER_CONTENT'], 
                                    "MSG_NAME" => $TPLMSG['NAME'], 
                                    "MSG_STATUS" => $TPLMSG['STATUS'], 
                                    "MSG_ORDER_ID" => $TPLMSG['ORDER_ID'], 
                                    "MSG_CONTENT" => $TPLMSG['CONTENT'], 
                                    "MSG_PAYMENT_TYPE" => $TPLMSG['PAYMENT_TYPE'], 
                                    "MSG_SHIP_ZONE" => $TPLMSG['ORDER_SHIP_ZONE'],                                    
                                    "MSG_TOTAL" => $TPLMSG['CART_TOTAL'], 
                                    "MSG_SUBTOTAL" => $TPLMSG['CART_SUBTOTAL'], 
                                    "MSG_AMOUNT" => $TPLMSG['CART_AMOUNT'], 
                                    "MSG_DISCOUNT" => $TPLMSG['QUANTITY_DISCOUNT'],
                                    "MSG_PRODUCT" => $TPLMSG['PRODUCT'], 
                                    "MSG_PRODUCT_PRICE" => $TPLMSG['PRODUCT_PRICE'], 
                                    "MSG_SHIPPING_PRICE" => $TPLMSG['SHIPPING_PRICE'], 
                                    "MSG_PLUS_FEE" => $TPLMSG["PLUS_FEE"],
                                    ) + $this->basic_lang
                                );
	
				$row = $db->fetch_array($selectrs, 1);
				foreach ($row as $key => $value) {
					if ($key == 'o_payment_type') {
						$value = $ws_array["payment_type"][$value];
					} else if ($key == 'o_shipment_type') {//配送地區
                                            $source_of_shipment = Model_Shipprice::getShipmentSource();                                            
                                            $value = $source_of_shipment[$value];
					} else if ($key == 'o_name') {
						$value = (empty($this->gender_select))?$this->gender_list($row["o_contact_s"], 1).'&nbsp;'.$value:$value.'&nbsp;'.$this->gender_list($row["o_contact_s"], 1);
					}
					$tpl->assignGlobal("VALUE_".strtoupper($key), $value);
				}
				$tpl->assignGlobal("VALUE_O_STATUS_SUBJECT", $ws_array["order_status"][$row["o_status"]]);
	
				if (!empty($row["o_payment_type"])) {
					$tpl->newBlock("TAG_PAYMENT_BLOCK");
					$tpl->newBlock("TAG_ADV_BLOCK");
					$tpl->assignGlobal($this->adv_lang);
	
					$invoice_type = $ws_array['invoice_type'][$row["o_invoice_type"]];
	
					$tpl->assignGlobal("VALUE_O_INVOICE_TYPE", $invoice_type);
	
					if ($row["o_payment_type"] == 1) {
						$tpl->newBlock("ATM_LAST5");
						$tpl->assign(array("VALUE_O_ATM_LAST5" => $row['o_atm_last5']?$row['o_atm_last5']:'NA', ));
					}
					if ($row["o_invoice_type"] == 2) {
						$tpl->newBlock("TAG_INVOICE_TRI");
					}
	
					$tpl->newBlock("TAG_ADV_TH");
					$tpl->newBlock("TAG_ADV_PRICE");
					// 顯示手續費
					if ($row['o_fee_price'])
						$tpl->newBlock("TAG_PLUS_FEE");
					$main_func_str = $TPLMSG['MEMBER_ZONE_ORDER'];
				} else {
					$main_func_str = $TPLMSG['MEMBER_ZONE_INQUIRY'];
				}
	
				$tpl->assignGlobal(array("TAG_MAIN_FUNC" => $main_func_str, "TAG_LAYER" => $main_func_str, ));
	
				$this->cart_order_detail_item($row);
			}
		}
	
		// 讀取訂單產品紀錄 , 取代原  member's orders
		function cart_order_detail_item($detail = 0) {
			global $db, $tpl, $cms_cfg, $TPLMSG;
	
			$sql = "select * from ".$db->prefix("order_items")." where o_id='".$detail["o_id"]."'";
			$selectrs = $db->query($sql);
			$rsnum = $db->numRows($selectrs);
	
			if (!empty($rsnum)) {
                                if($cms_cfg['ws_module']['ws_cart_spec']){
                                    $tpl->newBlock("SPEC_TITLE_ORDER");
                                    $tpl->assignGlobal("CART_FIELDS_NUMS",6);
                                }else{
                                    $tpl->assignGlobal("CART_FIELDS_NUMS",5);
                                }                            
				while ($row = $db->fetch_array($selectrs, 1)) {
					$i++;
					$tpl->newBlock("ORDER_ITEMS_LIST");
					$tpl->assign(array(
                                            "VALUE_P_ID" => $row["p_id"], 
                                            "VALUE_P_NAME" => $row["p_name"], 
                                            "VALUE_P_AMOUNT" => $row["amount"], 
                                            "VALUE_P_SERIAL" => $i, 
                                            "VALUE_P_SELL_PRICE" => $row["price"], 
                                            "TAG_QUANTITY_DISCOUNT" => ($row['discount']<1)?$row['discount']:'',
                                            "VALUE_P_SUBTOTAL_PRICE" => round($row["price"] * $row["amount"] * $row['discount']), 
                                        ));
                                        if($cms_cfg['ws_module']['ws_cart_spec']){
                                            $tpl->newBlock("SPEC_FIELD_ORDER");
                                            $tpl->assign("VALUE_SPEC",$row["spec"]);
                                        }                                        
				}
			}
		}
	
		## 附屬功能 ######################################################################################
	
		// 產品價格處理
		function price_counter($row = 0, $switch = 0) {
			global $cms_cfg, $tpl, $TPLMSG, $allpay;
	
			$p_price = (!empty($row["p_special_price"]))?$row["p_special_price"]:$row["p_list_price"];
	
			// 會員折價
			if (!empty($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]) && $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"] != 100) {
				$msg_price = $TPLMSG['PRODUCT_DISCOUNT_PRICE'];
				$msg_discount = $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]."%";
				$special_price = floor($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"] / 100 * $p_price);
			} else {
				$msg_price = $TPLMSG['PRODUCT_SPECIAL_PRICE'];
				$msg_discount = "";
				$special_price = $p_price;
			}
	
			// 產品計費
			$sub_total_price = $special_price * $row["p_num"];
			$total_price = $total_price + $sub_total_price;
	
			// 顯示產品單價
			if (empty($switch)) {
				$tpl->newBlock("TAG_PRICE_TD");
				$tpl->assign(array("VALUE_P_SPECIAL_PRICE" => $p_price, "VALUE_P_SUBTOTAL_PRICE" => $sub_total_price, ));
			}
	
			// 累計價格
			$this->subtotal_money = $this->subtotal_money + $total_price;
	
			$this->price_count++;
			//跑完購物車項目才進行輸出
			if (count($_SESSION[$cms_cfg['sess_cookie_name']]["id"]) == $this->price_count && empty($switch)) {
	
				// 運費
				$this->shipping_price = Model_Shipprice::calculate($this->subtotal_money);
	
				// 手續費
				if ($allpay->allpay_switch && ($_SESSION[$cms_cfg['sess_cookie_name']]["o_payment_type"] == '2' || $_SESSION[$cms_cfg['sess_cookie_name']]["o_payment_type"] == 'CVS' || $_SESSION[$cms_cfg['sess_cookie_name']]["o_payment_type"] == 'BARCODE')) {
					if ($_SESSION[$cms_cfg['sess_cookie_name']]["o_payment_type"] == 2) {
						$this->plus_fee = Model_Chargefee::calculate($this->subtotal_money);
					} else {
						$this->plus_fee = 30;
					}
				}
	
				// 總價
				$this->total_money = $this->subtotal_money + $this->shipping_price + $this->plus_fee;
	
				// 顯示價格抬頭
				$tpl->newBlock("TAG_PRICE_TH");
				$tpl->assign(array("MSG_PRODUCT_SPECIAL_PRICE" => $msg_price, "VALUE_P_DISCOUNT" => $msg_discount, ));
	
				// 顯示手續費
				if (!empty($this->plus_fee)) {
					$tpl->newBlock("TAG_PLUS_FEE");
					$tpl->assign(array("MSG_PLUS_FEE" => $TPLMSG["PLUS_FEE"], "VALUE_PLUS_FEE" => $this->plus_fee, ));
				}
	
				// 顯示總價
				$tpl->newBlock("TAG_PRICE_TOTAL");
				$tpl->assign(array("VALUE_SHIPPING_PRICE" => $this->shipping_price, "VALUE_SUBTOTAL" => $this->subtotal_money, "VALUE_TOTAL" => $this->total_money, ));
	
				$tpl->gotoBlock("SHOPPING_CART_ZONE");
			}
	
			return $special_price;
		}
	
		// 讀取產品資料
		function products_detail($id = 0) {
			global $db, $cms_cfg;
	
			$sql = "select * from ".$cms_cfg['tb_prefix']."_products as p 
							left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id 
							where p.p_id = '".$id."'";
	
			$selectrs = $db->query($sql);
			$rsnum = $db->numRows($selectrs);
	
			if (!empty($rsnum)) {
				return $db->fetch_array($selectrs, 1);
			} else {
				$this->error_handle();
			}
		}
	
		// 讀取會員資料
		function member_detail() {
			global $db, $cms_cfg, $tpl, $main,$TPLMSG;
	
			$sql = "select * from ".$db->prefix("member")." where m_id='".$this->m_id."'";
			$selectrs = $db->query($sql);
			$rsnum = $db->numRows($selectrs);
	
			if (!empty($rsnum)) {
				$row = $db->fetch_array($selectrs, 1);
				$tpl->assignGlobal(array(
                                    //"VALUE_M_NAME" => $row["m_lname"]." ".$row["m_fname"],
                                    //"VALUE_M_CONTACT_S" => $row["m_contact_s"],
                                    "VALUE_M_COMPANY_NAME" => $row["m_company_name"], 
                                    "VALUE_M_ZIP" => $row["m_zip"], 
                                    "VALUE_M_ADDRESS" => $row["m_address"], 
                                    "VALUE_M_TEL" => $row["m_tel"], 
                                    "VALUE_M_FAX" => $row["m_fax"], 
                                    "VALUE_M_EMAIL" => $row["m_email"], 
                                    "VALUE_M_CELLPHONE" => $row["m_cellphone"],
                                ));
			}
                        //稱謂下拉選單
                        $ordererField = new ContactfieldWithCourtesyTitle(array(
                            'view'      => 'orderer',
                            'blockName' => 'Orderer',
                            'fieldData' => array(
                                'contact' => array(
                                    'fname' => $row['m_fname'],
                                    'lname' => $row['m_lname'],
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
	
			// 國家下拉選單
			if ($cms_cfg["ws_module"]["ws_country"] == 1) {
				$main->country_select($row["m_country"]);
			}
	
			if (!empty($rsnum)) {
				$this->member = $row;
			}
		}
	
		// 新增購物會員
		function new_member() {
			global $cms_cfg, $db;
	
			if (empty($this->m_id) && !empty($cms_cfg["ws_module"]["ws_cart_login"])) {
				App::getHelper('main')->check_duplicate_member_account($_REQUEST["m_account"]);
                                foreach($_POST as $k=>$v){
                                    if(preg_match("/^o_(\w+)$/", $k,$match)){
                                        $_POST['m_'.$match[1]] = $v;
                                    }
                                }
                                $memberData = array_merge($_POST,array(
                                    'mc_id'     => '1',
                                    'm_status'  => '1',
                                    'm_sort'    => App::getHelper('dbtable')->member->get_max_sort_value(),
                                ));
                                App::getHelper('dbtable')->member->writeData($memberData);
                                $db_msg = App::getHelper('dbtable')->member->report();
                                if ( $db_msg == "" ) {
                                    $this->m_id = App::getHelper('dbtable')->member->get_insert_id();
                                }else{
                                    $this->m_id=0;
                                }
	
				$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ACCOUNT"] = $_REQUEST["m_account"];
				$this->mail_goto_url = $cms_cfg["base_root"];
			}
		}
	
		// 獨立辨識碼
		function sess_code($id = 0, $option = "") {
			global $cms_cfg;
	
			if (!empty($id)) {
				$sess_code_origin = $_SESSION[$cms_cfg['sess_cookie_name']].$id;
				$sess_code_combin = $sess_code_origin.$option;
	
				return substr(md5($sess_code_combin), 8, 16);
			}
		}
	
		// O_ID 生成
		function o_id_generator() {
			$day_pass = date("z") - 1;
			$h_s = date("H") * 60 * 60;
			$m_s = date("i") * 60;
			$s = date("s");
	
			$pass_s = $day_pass * 24 * 60 * 60;
			$now_s = $h_s + $m_s + $s;
	
			$all_s = $pass_s + $now_s;
	
			$all_s = str_pad($all_s, 8, "0", STR_PAD_LEFT);
	
			return date("y").$all_s;
		}
	
		// 產品連結
		function p_link_handle($row = 0) {
			global $cms_cfg;
			if ($this->ws_seo && is_array($row)) {
				$dirname = (trim($row["pc_seo_filename"]))?$row["pc_seo_filename"]:"products";
				if (trim($row["p_seo_filename"]) != "") {
					$p_link = $cms_cfg['base_url'].$dirname."/".$row["p_seo_filename"].".html";
				} else {
					$p_link = $cms_cfg['base_url'].$dirname."/"."products-".$row["p_id"]."-".$row["pc_id"].".html";
				}
			} else {
				$p_link = $cms_cfg['base_url']."products.php?func=p_detail&p_id=".$row["p_id"]."&pc_parent=".$row["pc_id"];
			}
	
			return $p_link;
		}
	
		// ajax 輸入處理
		function ajax_form() {
			if (is_array($_REQUEST["val"])) {
				foreach ($_REQUEST["val"] as $key => $array) {
					if (!empty($array["value"])) {
						if (empty($form[$array["name"]])) {
							$form[$array["name"]] = $array["value"];
						} else {
							if (!is_array($form[$array["name"]])) {
								$sub_array = $form[$array["name"]];
								unset($form[$array["name"]]);
	
								$form[$array["name"]][] = $sub_array;
								$form[$array["name"]][] = $array["value"];
							} else {
								$form[$array["name"]][] = $array["value"];
							}
						}
					}
				}
	
				return $form;
			} else {
				$this->error_handle();
			}
		}
	
		// 性別選單
		function gender_list($get_key = 0, $switch = 0) {
			global $ws_array, $tpl, $TPLMSG;
	
			if (!empty($switch)) {
				return $ws_array["contactus_s"][$get_key];
			}
	
			if (!empty($ws_array["contactus_s"]) && is_array($ws_array["contactus_s"])) {
				$tpl->newBlock("TAG_S_BLOCK_".$this->gender_select);
				$tpl->assign("MSG_MEMBER_NAME", $TPLMSG['MEMBER_NAME']);
				foreach ($ws_array["contactus_s"] as $s_key => $s_val) {
					$tpl->newBlock("TAG_S_OPTION_".$this->gender_select);
					$tpl->assign(array("VALUE_S_KEY" => $s_key, "VALUE_S_STR" => $s_val, "VALUE_S_CURRENT" => ($get_key == $s_key && !empty($get_key))?'selected':''));
				}
			}
		}
	
		// 台灣地區選單
		function taiwan_zone_select() {
			global $tpl, $cms_cfg;
	
			if (!empty($this->taiwan_zone)) {
				$tpl->newBlock("TAG_TAIWAN_ZONE");
				$tpl->assignGlobal("VALUE_TAIWAN_ZIP", $this->member["m_zip"]);
			}
		}
	
		// 寄送訊息
		function mail_handle($none_goto = 1) {
			global $db, $cms_cfg, $tpl, $main, $TPLMSG;
	
			if ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) {
				$sql = "select st_order_mail from ".$cms_cfg['tb_prefix']."_service_term where st_id='1'";
				$mail_title = $TPLMSG["ORDER_MAIL_TITLE"];
				$mail_func = "shopping";
				$selectrs = $db->query($sql, true);
				$row = $db->fetch_array($selectrs, 1);
				$tpl->assignGlobal("VALUE_TERM", $row['st_order_mail']);
			} else {
				$sql = "select st_inquiry_mail from ".$cms_cfg['tb_prefix']."_service_term where st_id='1'";
				$mail_title = $TPLMSG["INQUIRY_MAIL_TITLE"];
				$mail_func = "inquiry";
				$selectrs = $db->query($sql, true);
				$row = $db->fetch_array($selectrs, 1);
				$tpl->assignGlobal("VALUE_TERM", $row['st_inquiry_mail']);
			}
	
			//$mail_content=$tpl->getOutputContent();
	
			if ($cms_cfg["ws_module"]["ws_cart_login"] == 0) {
				$goto_url = $_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL'];
			} else {
				$goto_url = (!empty($this->mail_goto_url))?$this->mail_goto_url:$cms_cfg["base_url"]."cart/?func=c_order_detial&o_id=".$this->o_id;
			}
                        $mail_content = $tpl->getOutputContent();
                        if(!in_array(App::getHelper('session')->paymentType,array(1,2,'Credit'))){
                            $main->ws_mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$_REQUEST["m_email"],$mail_content,$mail_title,$mail_func,$goto_url,null,$none_goto);
                        }else{
                            App::getHelper('session')->mailContent = $mail_content;
                        }
		}
	
		// 讀取各式服務條款
		function service_rule() {
			global $db, $tpl, $cms_cfg;
	
			$sql = "select * from ".$cms_cfg['tb_prefix']."_service_term";
			$selectrs = $db->query($sql);
			$rsnum = $db->numRows($selectrs);
	
			if (!empty($rsnum)) {
				$row = $db->fetch_array($selectrs, 1);
				foreach ($row as $key => $value) {
					$tpl->assignGlobal("VALUE_".strtoupper($key), $value);
				}
			}
		}
	
		// 訂單狀態檢查 (防止瀏覽器上一頁導致流程失效)
		function cart_check() {	
			if ($this->container->count()) {
				echo true;
			} else {
				echo false;
			}
		}
	
		// 錯誤處理
		function error_handle($switch = 0) {
			global $tpl, $TPLMSG;
	
			switch($switch) {
				default :
					$tpl->assignGlobal("MSG_CART_EMPTY", $TPLMSG['NO_DATA']);
					break;
			}
		}
	
		function member_login() {
			global $main, $ws_array, $cms_cfg, $tpl, $TPLMSG;
			$main->layer_link($ws_array["cart_type"][$_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]]);
			$tpl->assignGlobal("TAG_MAIN_FUNC", $TPLMSG['MEMBER_LOGIN']);
			$tpl->assignGlobal("TAG_RETURN_URL", $_SERVER['REQUEST_URI']);
		}
	
		function ajax() {
			$method = __FUNCTION__."_".$_GET['action'];
			if (method_exists($this, $method)) {
				$this->$method();
			} else {
				throw new Exception($method."doesn't exists!");
			}
		}
	
		function ajax_write_last5() {
			global $db, $cms_cfg, $main;
			$res['code'] = 0;
			if ($_POST['o_id'] && $_POST['o_atm_last5']) {
				$sql = "select *  from ".$db->prefix("order")." where o_id='".$db->quote($_POST['o_id'])."'";
				$qs = $db->query($sql);
				if ($db->numRows($qs)) {
					$orderData = $db->fetch_array($qs, 1);
					$sql = "update ".$db->prefix("order")." set o_atm_last5='".$_POST['o_atm_last5']."' where o_id='".$_POST['o_id']."'";
					$db->query($sql);
					if ($err = $db->report()) {
						$res['msg'] = $err;
					} else {
						$res['code'] = 1;
						//寄發通知信
						$tpl = App::getHelper('main')->get_mail_tpl("remit-notification");
						$tpl->newBlock("REMIT_LAST5");
						$tpl->assign(array("MSG_O_ID" => $_POST['o_id'], "MSG_ATM_LAST5" => $_POST['o_atm_last5'], ));
						$mail_content = $tpl->getOutputContent();
						$main->ws_mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'], $orderData['o_email'], $mail_content, $_SESSION[$cms_cfg['sess_cookie_name']]['sc_company']."atm轉帳訂單匯款完成通知", 'atm', '', '', 1);
					}
				}
			}
			echo json_encode($res);
		}
	
		//取消訂單
		function ajax_cancel_order() {
			global $db, $cms_cfg, $main, $ws_array;
			$sql = "select * from ".$db->prefix("order")." where o_id='".$_POST['o_id']."'";
			$order = $db->query_firstrow($sql);
			if ($order) {
				$res['code'] = 1;
				if ($order['o_status'] == 0) {
					$order['o_status'] = 9;
					//取消訂單的狀態
					$sql = "update ".$db->prefix("order")." set o_status='9' where o_id='".$_POST['o_id']."'";
					$db->query($sql);
					if ($err = $db->report()) {
						$res['code'] = 0;
						$res['msg'] = $err;
					} else {
						//寄發通知信
						$tpl = App::getHelper('main')->get_mail_tpl("order-cancel");
						$tpl->newBlock("SHOPPING_ORDER");
						$tpl->assign(array("MSG_CANCEL_TIME" => date("Y-m-d H:i:s"), "MSG_O_ID" => $_POST['o_id'], ));
						$mail_content = $tpl->getOutputContent();
						//$main->ws_mail_send_simple($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$mail_content,$_SESSION[$cms_cfg['sess_cookie_name']]['sc_company']."購物訂單線上取消通知","系統通知");
						//ws_mail_send($from,$to,$mail_content,$mail_subject,$mail_type,$goto_url,$admin_subject=null,$none_header=0){
						$main->ws_mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'], $order['o_email'], $mail_content, $_SESSION[$cms_cfg['sess_cookie_name']]['sc_company']."購物訂單線上取消通知", "", "", null, 1);
						$res['msg'] = $ws_array["order_status"][$order['o_status']];
					}
				} else {
					$res['code'] = 0;
					$res['msg'] = "訂單非新訂單，無法線上取消，請聯絡客服";
				}
			} else {
				$res['code'] = 0;
				$res['msg'] = "訂單不存在!";
			}
			echo json_encode($res);
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
                        'o_payment_type','o_name','o_email','o_address','o_tel','o_cellphone',
                        'o_add_name','o_add_mail','o_add_address','o_add_tel','o_add_cellphone',
                        'o_invoice_type','o_subtotal_price','o_total_price',
                    );
                    if($require_fields){
                        foreach($require_fields as $f){
                            if(empty($data[$f])){
                                $pass=false;
                                break;
                            }
                            //檢查發票資訊
                            if($f=="o_invoice_type" && $data[$f]==3 && (empty($data['o_invoice_name']) || empty($data['o_invoice_vat']))){
                                $pass=false;
                                break;
                            }
                        }
                    }
                    return $pass;
                }                
	
	}
?>
