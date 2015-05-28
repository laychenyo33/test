<?php
	$allpay = new ALLPAY($cms_cfg['allpay']);

	Class ALLPAY{
		function __construct($config){
			global $db,$cms_cfg,$ws_array,$TPLMSG;
			include_once(dirname(__FILE__)."/config.php");
			
			/* 這樣真的很多餘 = ="
            if(!empty($config['MerchantID']) && !empty($config['HashKey']) && !empty($config['HashIV'])){
                $this->all_cfg["MerchantID"] = $config['MerchantID']; // 特店編號
                $this->all_cfg["HashKey"] = $config['HashKey']; // Hash key
                $this->all_cfg["HashIV"] = $config['HashIV']; // Hash IV
            }else{
                throw new Exception("Missing some ALLPAY initial option");
            }
			*/
			
//			// ReturnURL
//			if(!empty($_POST["MerchantTradeNo"]) && empty($_REQUEST["o_id"]) && empty($_REQUEST["ap_retrun"])){
//				$this->allpay_respone(0);
//			}
//			
//			// PaymentInfoURL
//			if(!empty($_POST["MerchantTradeNo"]) && empty($_REQUEST["o_id"]) && !empty($_REQUEST["ap_retrun"])){
//				$this->allpay_respone(1);
//			}
		}
		
		#################################################
		
		// 訂單送出
		function allpay_send(
			$o_id=0, // 訂單編號
			$price=0, // 交易總金額
			$pay_desc=0, // 交易描述 (不可空值)
			$shopping=0, // 商品資訊 (array)
			$c_pay=0, // 交易方式
			$c_s_pay=0, // 選擇預設付款子項目
			//$i_rul=0, // 商品促銷網址
			//$remark=0, // 備註
			$discount=false // 總金額已折扣
		){
			global $db,$cms_cfg;

			// 基本設定
			$this->all_cfg["MerchantTradeNo"] = $o_id;
			$this->all_cfg["TotalAmount"] = $price;
			$this->all_cfg["ChoosePayment"] = $c_pay;
			$this->all_cfg["ChooseSubPayment"] = $c_s_pay;
			//$this->all_cfg["OrderResultURL"] = ($this->allpay_switch)?$cms_cfg["base_url"].'cart/?func=c_order_detial&o_id='.$o_id:$cms_cfg["base_url"].'member.php?func=m_zone&mzt=order&type=detail&o_id='.$o_id;
			$this->all_cfg["PaymentInfoURL"] = $cms_cfg['base_url']."shopping-result3.php?sess=".session_id();

			// 交易描述
			if(!empty($pay_desc)){
				$this->all_cfg["TradeDesc"] = $pay_desc;
			}else{
				$this->all_cfg["TradeDesc"] = $_SESSION[$cms_cfg['sess_cookie_name']]["sc_company"].'_交易訂單_'.$this->all_cfg["MerchantTradeNo"];
			}
			
			//取得商品資訊
			if(!empty($shopping) && is_array($shopping)){
				if($discount){
					$shopping_num = count($shopping);
					$price_split = round($this->all_cfg["TotalAmount"] / $shopping_num);
					$price_sum = $price_split * $shopping_num;
					
					if($price_sum != $this->all_cfg["TotalAmount"]){
						$first_price_split = $price_sum - $this->all_cfg["TotalAmount"];
						$first_price_split = $first_price_split + $price_split;
					}
				}
				
				foreach($shopping as $sess_code => $row){
					$shopping_row++;
					
					$p_name_array[] = $row["p_name"];
					$p_num_array[] = $row["p_num"];
					//$p_price_array[] = $row["p_price"];
					
					if(!empty($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]) && $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]!=100){
                                            $p_price_get = floor($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"] / 100 * $row["p_special_price"]);
					}else{
                                            $p_price_get = $row['price'];
					}
					
					if($discount){
						if($shopping_row == 1 && !empty($first_price_split)){
							$p_price_get = $first_price_split;
						}elseif($shopping_row == 1 && !empty($price_split)){
							$p_price_get = $price_split;
						}elseif($shopping_row > 1 && !empty($price_split)){
							$p_price_get = $price_split;
						}
					}
					
					$p_price_array[] = $p_price_get;
				}
				
				foreach($p_name_array as $p_key => $p_val){
					if($this->anonymous_switch){
						$p_array[] = 'P'.$p_key.'X'.$p_num_array[$p_key];
						$p_ali_array[] = 'P'.$p_key;
					}else{
						$p_array[] = $p_val.'X'.$p_num_array[$p_key];
					}
				}
				
				$this->all_cfg["ItemName"] = implode("#",$p_array);
				
				// 判斷是否為支付寶
				if($c_pay == "Alipay"){
					if($this->anonymous_switch){
						$this->all_cfg["AlipayItemName"] = implode("#",$p_ali_array);
					}else{
						$this->all_cfg["AlipayItemName"] = implode("#",$p_name_array);
					}
					$this->all_cfg["AlipayItemCounts"] = implode("#",$p_num_array);
					$this->all_cfg["AlipayItemPrice"] = implode("#",$p_price_array);
					
					foreach($p_price_array as $key => $single_price){
						$sub_total = $single_price * $p_num_array[$key];
						$full_total = $full_total + $sub_total;
					}
					
					if($full_total != $this->all_cfg["TotalAmount"]){
						$fee = ($this->all_cfg["TotalAmount"] > $full_total)?$this->all_cfg["TotalAmount"] - $full_total:$full_total - $this->all_cfg["TotalAmount"];
						$this->all_cfg["AlipayItemName"] .= '#fee';
						$this->all_cfg["AlipayItemCounts"] .= '#1';
						$this->all_cfg["AlipayItemPrice"] .= '#'.$fee;
					}
					
					$this->all_cfg["Email"] = $_REQUEST["m_email"];
					$this->all_cfg["PhoneNo"] = $_REQUEST["m_tel"];
					$this->all_cfg["UserName"] = $_REQUEST["m_name"];
				}else{
					($this->anonymous_switch && !empty($this->anonymous_str))?$this->all_cfg["ItemName"] = $this->anonymous_str:'';
				}
			}
			
			// 組合所有參數
			ksort($this->all_cfg);
			foreach($this->all_cfg as $key => $value){
				if($key != "POST" && $key != "PATH" && $key != "HashIV" && $key != "HashKey" && $key != "allpay_type" && !empty($value)){
					$all_value_array[$key] = $value;
					$all_code_array[] = $key.'='.$value;
				}
			}
			
			// 取得檢查碼
			$this->allpay_code = $this->allpay_checkcode($all_code_array);
                        
                        //更新訂單為授權中斷
                        App::getHelper('dbtable')->order->writeData(array('o_id'=>$o_id,'o_status'=>20));
			
			// 組合訂單資訊
			$this->allpay_send_form($all_value_array);
			
//			if($allpay_send_ck){
//	            $sql="
//	                update ".$cms_cfg['tb_prefix']."_order
//	                    set o_status='4'
//	                where o_id='".$this->all_cfg["MerchantTradeNo"]."'";
//	            $db->query($sql);
//			}else{
//	            $sql="
//	                update ".$cms_cfg['tb_prefix']."_order
//	                    set o_status='10'
//	                where o_id='".$this->all_cfg["MerchantTradeNo"]."'";
//	            $db->query($sql);
//				
//				//? mail 訂單錯誤處理
//			}
		}

		// 組合訂單資訊
		function allpay_send_form($all_val=0){
			
			if(!empty($all_val) && is_array($all_val)){
				unset($input_str);
				foreach($all_val as $key => $val){
					$input_str[$key] = '<input type="hidden" name="'.$key.'" value="'.$val.'">';
				}
				
				if(count($input_str) > 0){
					$input_add = implode('',$input_str);
				}else{
					return false;
				}
				
				$form = '
					<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">
					<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
					
					<body>
					<form name="allpay_form" method="post" action="'.$this->all_cfg["POST"].$this->all_cfg["PATH"].'">
						<input type="hidden" name="CheckMacValue" value="'.$this->allpay_code.'">
						'.$input_add.'
					</form>
					</body>
					</html>
					
					<script>
						document.allpay_form.submit();
					</script>
				';
				
				echo $form;
			}
		}
		
		// 結果接收
		function allpay_respone($switch=0){
			global $db,$cms_cfg,$ws_array,$TPLMSG;
			
			if($this->isValidData($_POST)){
				switch($switch){
					default:
                                            App::getHelper('dbtable')->allpay_order->writeData($_POST,'insert');
//			            $sql="
//			                insert into ".$cms_cfg['tb_prefix']."_allpay_order (
//			                    o_id,
//			                    MerchantID,
//			                    RtnCode,
//			                    RtnMsg,
//			                    TradeNo,
//			                    TradeAmt,
//			                    PaymentDate,
//			                    PaymentType,
//			                    PaymentTypeChargeFee,
//			                    TradeDate,
//			                    SimulatePaid,
//			                    CheckMacValue
//			                ) values (
//			                    '".$_POST["MerchantTradeNo"]."',
//			                    '".$_POST["MerchantID"]."',
//			                    '".$_POST["RtnCode"]."',
//			                    '".$_POST["RtnMsg"]."',
//			                    '".$_POST["TradeNo"]."',
//			                    '".$_POST["TradeAmt"]."',
//			                    '".$_POST["PaymentDate"]."',
//			                    '".$_POST["PaymentType"]."',
//			                    '".$_POST["PaymentTypeChargeFee"]."',
//			                    '".$_POST["TradeDate"]."',
//			                    '".$_POST["SimulatePaid"]."',
//			                    '".$_POST["CheckMacValue"]."'
//			                )";
//			            $db->query($sql);
			            
			            if($_POST["RtnCode"] == 1){
                                        $updateOrder['o_id'] = $_POST["MerchantTradeNo"];
                                        $updateOrder['o_status'] = 1;
                                        App::getHelper('dbtable')->order->writeData($updateOrder);
//				            $sql="
//				                update ".$cms_cfg['tb_prefix']."_order
//				                    set o_status='1'
//				                where o_id='".$_POST["MerchantTradeNo"]."'";
//				            $db->query($sql);
			            }
					break;
					case 1:
                                            App::getHelper('dbtable')->allpay_payinfo->writeData($_POST,'insert');
//			            $sql="
//			                insert into ".$cms_cfg['tb_prefix']."_allpay_payinfo (
//			                    o_id,
//			                    MerchantID,
//			                    RtnCode,
//			                    RtnMsg,
//			                    TradeNo,
//			                    TradeAmt,
//			                    PaymentType,
//			                    TradeDate,
//			                    CheckMacValue,
//			                    BankCode,
//			                	vAccount,
//			                	PaymentNo,
//			                	Barcode1,
//			                	Barcode2,
//			                	Barcode3,
//			                	ExpireDate
//			                ) values (
//			                    '".$_POST["MerchantTradeNo"]."',
//			                    '".$_POST["MerchantID"]."',
//			                    '".$_POST["RtnCode"]."',
//			                    '".$_POST["RtnMsg"]."',
//			                    '".$_POST["TradeNo"]."',
//			                    '".$_POST["TradeAmt"]."',
//			                    '".$_POST["PaymentType"]."',
//			                    '".$_POST["TradeDate"]."',
//			                    '".$_POST["CheckMacValue"]."',
//			                    '".$_POST["BankCode"]."',
//			                	'".$_POST["vAccount"]."',
//			                	'".$_POST["PaymentNo"]."',
//			                	'".$_POST["Barcode1"]."',
//			                	'".$_POST["Barcode2"]."',
//			                	'".$_POST["Barcode3"]."',
//			                	'".$_POST["ExpireDate"]."'
//			                )";
//			            $db->query($sql);
					break;
				}
	            
	            echo '1|OK';
            }else{
            	echo '0|ErrorMessage';
            }
            
            if($_POST["RtnCode"] != 1 && $_POST["RtnCode"] != 2 && $_POST["RtnCode"] != "10100073" || $ckmac_key != $_POST["CheckMacValue"]){
                $updataOrder = array(
                    'o_status' => 21,
                    'o_id'     => $_POST["MerchantTradeNo"],
                );
                App::getHelper('dbtable')->order->writeData($updataOrder);
//	            $sql="
//	                update ".$cms_cfg['tb_prefix']."_order
//	                    set o_status='21'
//	                where o_id='".$_POST["MerchantTradeNo"]."'";
//	            $db->query($sql);
	            
				//? mail 訂單錯誤處理
            }
           	
            exit;
		}
		
		// 檢查碼生成
		function allpay_checkcode($all_code_array){
			
			#檢查用
			#$this->ck[1] = 'HashKey='.$this->all_cfg["HashKey"].'&'.implode("&",$all_value_array).'&HashIV='.$this->all_cfg["HashIV"];
			#$this->ck[2] = urlencode('HashKey='.$this->all_cfg["HashKey"].'&'.implode("&",$all_value_array).'&HashIV='.$this->all_cfg["HashIV"]);
			#$this->ck[3] = strtolower(urlencode('HashKey='.$this->all_cfg["HashKey"].'&'.implode("&",$all_value_array).'&HashIV='.$this->all_cfg["HashIV"]));
			#echo implode('<br /><br />',$this->ck);

                        $combineStr = strtolower(urlencode('HashKey='.$this->all_cfg["HashKey"].'&'.implode("&",$all_code_array).'&HashIV='.$this->all_cfg["HashIV"]));
                        //調整成.net的編碼結果
                        $combineStr2 = preg_replace(array('/%21/','/%2a/','/%28/','/%29/'), array('!','*','(',')'), $combineStr); 
                        $encode = md5($combineStr2);
			return $encode;
		}
		
		// 會員訂單管理顯示資料
		function member_allapy_detail($o_id=0){
			global $tpl,$db,$cms_cfg,$TPLMSG;
			
			$sql="select * from ".$db->prefix("allpay_payinfo")." where o_id='".$o_id."'";
			$selectrs = $db->query($sql);
			$rsnum    = $db->numRows($selectrs);
			
			if(!empty($rsnum)){
				$row = $db->fetch_array($selectrs,1);
				$type_array = explode("_",$row["PaymentType"]);
				switch($type_array[0]){
					case "ATM":
						$value_array[$TPLMSG['ALLPAY_BANK_CODE']] = $row["BankCode"];
						$value_array[$TPLMSG['ALLPAY_VACCOUNT']] = $row["vAccount"];
						$value_array[$TPLMSG['ALLPAY_EXPIRE']] = $row["ExpireDate"];
					break;
					case "CVS":
						$value_array[$TPLMSG['ALLPAY_CVS_NO']] = $row["PaymentNo"];
						$value_array[$TPLMSG['ALLPAY_EXPIRE']] = $row["ExpireDate"];
					break;
					case "BARCODE":
						$value_array[$TPLMSG['ALLPAY_CVS_BAR_1']] = $row["Barcode1"];
						$value_array[$TPLMSG['ALLPAY_CVS_BAR_2']] = $row["Barcode2"];
						$value_array[$TPLMSG['ALLPAY_CVS_BAR_3']] = $row["Barcode3"];
						$value_array[$TPLMSG['ALLPAY_EXPIRE']] = $row["ExpireDate"];
					break;
				}
				
				foreach($value_array as $key => $value){
					$tpl->newBlock("TAG_ALLPAY_DETAIL");
					$tpl->assign(array(
						"VALUE_ALLPAY_TITLE" => $key,
						"VALUE_ALLPAY_VALUE" => $value,
					));
				}
			}
		}
                //驗證回傳結果
                function isValidData($post){
                    ksort($post);
                    foreach($post as $key => $value){
                            if($key != "CheckMacValue"){
                                    $all_post_array[] = $key.'='.$value;
                            }
                    }

                    $ckmac_key = strtoupper($this->allpay_checkcode($all_post_array));

                    return ($ckmac_key == $post["CheckMacValue"]);
                }
                //更新訂單資料
                function updateOrder($post){
                    global $TPLMSG;
                    if($this->isValidData($post)){
                        if($post["RtnCode"] != 2 && $post["RtnCode"] != "10100073"){
                            if($post["RtnCode"] == 1){
                                $updateOrder['o_id'] = $post["MerchantTradeNo"];
                                $updateOrder['o_status'] = 1;
                                $updateOrder['o_paid'] = 1;
                                App::getHelper('dbtable')->order->writeData($updateOrder);     
                                $mtpl = App::getHelper('main')->get_mail_tpl("receipt-notification");
                                $mtpl->newBlock("SHOPPING_ORDER");
                                $mtpl->assignGlobal("MSG_O_ID",$post["MerchantTradeNo"]);
                                $mcontent = $mtpl->getOutputContent();
                                $order = App::getHelper('dbtable')->order->getData($post["MerchantTradeNo"])->getDataRow("o_email");
                                App::getHelper('main')->ws_mail_send(App::getHelper('session')->sc_email,$order["o_email"],$mcontent,$TPLMSG["ORDER_RECEIPT_NOTIFY_TITLE"],"order","","",1);                                
                            }elseif($post["RtnCode"] !='10100054'){ //非訂單重複的錯誤，更新訂單
                                $updateOrder['o_id'] = $post["MerchantTradeNo"];
                                $updateOrder['o_status'] = 21;
                                App::getHelper('dbtable')->order->writeData($updateOrder);
                            }
                            $post['o_id'] = $post["MerchantTradeNo"]; 
                            App::getHelper('dbtable')->allpay_order->writeData($post,'insert');
                            if(($err = App::getHelper('dbtable')->order->report())!=''){
                                echo "0|db error";
                            }else{
                                echo "1|OK";
                            }
                        }
                    }else{
                        echo "0|invalid check value";
                    }
                }
                //更新付款資訊
                function updatePayInfo($post){
                    global $TPLMSG;
                    if($this->isValidData($post)){
                        if($post["RtnCode"]!=1){
                            if($post["RtnCode"] == 2 || $post["RtnCode"] == "10100073"){                      
                                $updateOrder['o_id'] = $post["MerchantTradeNo"];
                                $updateOrder['o_status'] = 0;
                                App::getHelper('dbtable')->order->writeData($updateOrder); 
                                $mail=true;
                            }elseif($post["RtnCode"] !='10100054'){ //非訂單重複的錯誤，更新訂單
                                $updateOrder['o_id'] = $post["MerchantTradeNo"];
                                $updateOrder['o_status'] = 21;
                                App::getHelper('dbtable')->order->writeData($updateOrder);
                            }
                            $post['o_id'] = $post["MerchantTradeNo"];
                            App::getHelper('dbtable')->Allpay_Payinfo->writeData($post,'insert');
                            if(($err = App::getHelper('dbtable')->order->report())!=''){
                                echo "0|db error";
                            }else{
                                //寄發通知信
                                if($mail){
                                    //$mail_header = ($sessHandler->paymentType == 3)? 1 : 0;
                                    $sessHandler = App::getHelper('session');
                                    $order = App::getHelper('dbtable')->order->getdata($post["MerchantTradeNo"])->getdatarow('o_email');
                                    if($sessHandler['mailContent']){
                                        $mail_content = $sessHandler->mailContent;
                                        //ws_mail_send($from,$to,$mail_content,$mail_subject,$mail_type,$goto_url,$admin_subject=null,$none_header=0)
                                        App::getHelper('main')->ws_mail_send($sessHandler['sc_email'],$order["o_email"],$mail_content,$TPLMSG["ORDER_MAIL_TITLE"],"order","","",1);
                    //                    App::getHelper('main')->ws_mail_send_simple($sessHandler['sc_email'],$order["o_email"],$mail_content,$TPLMSG["ORDER_MAIL_TITLE"]);
                    //                    App::getHelper('main')->ws_mail_send_simple($order["o_email"],$sessHandler['sc_email'],$mail_content,$TPLMSG["ORDER_MAIL_TITLE"]);
                                    }
                                    unset($sessHandler['mailContent']);
                                }
                                echo "1|OK";
                            }                            
                        }
                    }else{
                        echo "0|invalid check value";  
                    }
                }
	}
?>