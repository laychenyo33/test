<?php

	# 罐頭 SEO 描述

	$real_path = realpath(dirname(__FILE__).'/../').DIRECTORY_SEPARATOR;
	include_once($real_path."libs/libs-sysconfig.php");
	
	class CAN{
		
		private static $db; // db 包裝成靜態
		private static $cms_cfg; // cms_cfg 包裝成靜態
		private static $field_array; // 所有搜尋到的資料表與欄位名稱
		private static $can; // 所有的罐頭資料
		private static $desc; // 所有的罐頭內容 (取得帶入字用)
		private static $lang; // 所有的罐頭語系
		private static $now_lang; // 目前選擇語系
		protected static $config = array(
			"temp" => 'temp/',
			"lang" => array(
				'eng' => '英文',
				'cht' => '繁體中文',
				'eng' => '英文',
				'cht' => '繁體中文',
				'chs' => '簡體中文',
				'jap' => '日文',
				'spa' => '西班牙文',
				'por' => '葡萄牙文',
				'ger' => '德文',
				'fre' => '法文',
				'tha' => '泰文',
			),
		);
		
		function __construct(){
			global $db,$cms_cfg;
			
			include_once dirname(__FILE__).'/view.php';
			
			self::$db = $db;
			self::$cms_cfg = $cms_cfg;
			self::$now_lang = (!empty($_REQUEST["lang"]))?$_REQUEST["lang"]:'eng';
			$base = 'can-index-tpl.html';
			
			switch($_REQUEST["func"]){
				case "preview":
					self::get_far_can();
					self::preview();
					$temp = array("MAIN" => 'can-preview-tpl.html');
				break;
				case "output":
					self::output();
					$temp = array("MAIN" => 'can-report-tpl.html');
				break;
				default:
					self::get_far_can();
					self::can_word();
					self::lang();
					self::desc_list();
					$temp = array("MAIN" => 'can-edit-tpl.html');
				break;
			}
			
			new VIEW($base,$temp);
		}
		
		# 取得遠端罐頭資料
		private function get_far_can(){
			
			$url = "http://assistant.allmarketing.com.tw/can/?func=fly_can";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  //SSL
			$xml_response = curl_exec($ch);
			if(curl_errno($ch)){
				print curl_error($ch);
				curl_close($ch);
				return false;
			}else{
				curl_close($ch);
				$reponse = true;
			}
			if(!empty($xml_response) && $reponse){
				$xml = @simplexml_load_string($xml_response);
				$i = 0;
				
				while($i < count($xml->content)){
					$array = get_object_vars($xml->content[$i]);
					self::$can[$array["id"]] = $array;
					self::$desc[$i] = $array["desc"];
					self::$lang[$array["lang"]] = true;
					$i++;
				}
			}
		}

		# 帶入字列表
		function can_word(){
			
			static $word_array = array();
			static $none = true;
			
			if(is_array(self::$desc) && count(self::$desc)){
				foreach(self::$desc as $can_desc){
					preg_match_all('/\{[^\}]+\}/',$can_desc,$word);
					$word_array = array_merge($word_array,$word[0]);
				}
				
				if(is_array($word_array) && count($word_array)){
					$word_array = array_flip($word_array);
					$word_array = array_flip($word_array);
					VIEW::newBlock("TAG_CAN_WORD");
					
					foreach($word_array as $can_word){
						$no_tag_word = preg_replace('/[\{\}]+/','',$can_word);
						
						VIEW::newBlock("TAG_CAN_WORD_LIST");
						VIEW::assign(array(
							"TAG_CAN_WORD" => $can_word,
							"NO_TAG_WORD" => $no_tag_word,
							"VALUE_CAN_WORD" => (!empty($_SESSION["can_word"][$no_tag_word]))?$_SESSION["can_word"][$no_tag_word]:'',
						));
					}
					
					$none = false;
				}
			}
			
			if($none){
				VIEW::newBlock("TAG_CAN_WORD_NONE");
			}
		}
		
		# 語系選單
		private function lang(){
			if(is_array(self::$lang)){
				$lang_array = array_keys(self::$lang);
				foreach($lang_array as $lang){
					VIEW::newBlock("TAG_LANG_LIST");
					VIEW::assign(array(
						"VALUE_LANG_SUBJECT" => $lang,
						"VALUE_LANG_STR" => (isset(self::$config["lang"][$lang]))?self::$config["lang"][$lang]:$lang,
						"VALUE_LANG_NOW" => (self::$now_lang && self::$now_lang == $lang)?'class="green"':'',
					));
				}

				VIEW::assignGlobal("VALUE_CAN_LANG",self::$now_lang);
			}
		}
		
		# 搜尋整個所有資料表 , 找出未填描述
		private function field_search(){
			
			# 確認有開啟 SEO
			if(self::$cms_cfg["ws_module"]["ws_seo"]){
				
				# 查詢所有資料表
				$selectrs = mysql_list_tables(self::$cms_cfg["db_name"],self::$db->connection);
				$i = 0;
				while($row = self::$db->fetch_array($selectrs)){
					$sql_field = "select * from ".$row[0];
					$selectrs_field = self::$db->query($sql_field);
					while($field = mysql_fetch_field($selectrs_field)){
						if($field->primary_key) $p_key[$field->table] = $field->name;
						if(preg_match('/([A-Za-z])+_seo_description/',$field->name)){
							self::$field_array[$i]['tb'] = $row[0];
							self::$field_array[$i]['field'] = $field->name;
							self::$field_array[$i]["id"] = $p_key[$row[0]];
							$i++;
						}
					}
				}
			}
		}
		
		# 行銷罐頭列表
		private function desc_list(){
			if(is_array(self::$can) && count(self::$can)){
				foreach(self::$can as $var_id => $var_array){
					if($var_array["lang"] == self::$now_lang){
						VIEW::newBlock("TAG_CAN_LIST");
						VIEW::assign(array(
							"VALUE_CAN_ID" => $var_id,
							"VALUE_CAN_DESC" => $var_array["desc"],
						));
					}
				}
			}
		}
		
		//-------------------------------------------------------------------------------
		
		# 預覽已輸入帶入字罐頭描述
		private function preview(){
			
			if(is_array(self::$can) && is_array($_REQUEST["can_id"]) && is_array($_REQUEST["can_word"]) && !empty($_REQUEST["can_lang"])){
				unset($_SESSION["can_word"]);
				$_SESSION["can_word"] = $_REQUEST["can_word"]; # 記憶帶入字
				
				foreach(self::$can as $can_id => $can_row){
					if(in_array($can_id,$_REQUEST["can_id"])){

						# 加入帶入字
						foreach($_REQUEST["can_word"] as $word_key => $word_str){
							$can_row["desc"] = preg_replace('/{'.$word_key.'}/',$word_str,$can_row["desc"]);
						}
						
						VIEW::newBlock("TAG_CAN_LIST");
						VIEW::assign("VALUE_CAN_DESC",$can_row["desc"]);
					}
				}
			}
			
			VIEW::assignGlobal(array(
				//"VALUE_CAN_LANG" => $_REQUEST["can_lang"],
				"VALUE_BACK_LINK" => self::$cms_cfg["base_root"].'can/',
			));
		}
		
		# 輸出至資料庫
		private function output(){
			self::field_search();
			
			if(is_array(self::$field_array) && count(self::$field_array)){
				foreach(self::$field_array as $tb_row){
	                $sql = "select ".$tb_row["field"].",".$tb_row["id"]." from ".$tb_row["tb"]." where ".$tb_row["field"]." = ''";
	                $selectrs = self::$db->query($sql);
	                $rsnum    = self::$db->numRows($selectrs);
					
					if(!empty($rsnum) && preg_match('/^'.self::$cms_cfg["tb_prefix"].'_/', $tb_row["tb"])){
						while($row = self::$db->fetch_array($selectrs,1)){
							$rand_desc = $_REQUEST["can_desc"][rand(0,(count($_REQUEST["can_desc"]) - 1))]; // 隨機輸出罐頭
							$sql = "update ".$tb_row["tb"]." set ".$tb_row["field"]."='".$rand_desc."' where ".$tb_row["id"]." = '".$row[$tb_row["id"]]."'";
							$rs = self::$db->query($sql);
							$db_msg = self::$db->report();
							
							if(!empty($db_msg)){
								VIEW::assignGlobal("MSG_REPORT",'<p>'.$db_msg.'</p>');
								return false;
							}else{
								$db_report[] = $sql;
							}
						}
					}
				}
				
				if(is_array($db_report)){
					$report_str = '<p>'.implode("</p><br /><p>",$db_report).'</p>';
					VIEW::assignGlobal("MSG_REPORT",'<p>執行完成，以下為所有執行的 sql 字段</p><br />'.$report_str);
				}else{
					$none = true;
				}
			}else{
				$none = true;
			}
			
			if($none) VIEW::assignGlobal("MSG_REPORT",'<p>無執行項目</p>');
			if(!self::$cms_cfg["ws_module"]["ws_seo"]) VIEW::assignGlobal("MSG_REPORT",'<p>非 SEO 案件</p>');
			VIEW::assignGlobal("VALUE_BACK_LINK",self::$cms_cfg["base_root"].'can/');
		}
	}

	new CAN;
?>