<?php
	
	# 建立、讀取、更新、刪除 資料庫補助 class

	class CRUD extends STA{

		public static 
			$id, # 取得 insert id
			$rs, # 取得insert & update 結果
			$sql, # 紀錄組合成的搜尋字串
			$data; # 紀錄取得資料

		function __construct(){}

		# 引號處理
		private static function strslashes($var=false){
			if(!get_magic_quotes_gpc()){
				return addslashes($var);
			}else{
				return $var;
			}
		}

		private static function insert($tbl_name,array $input){
			
			if(is_array($input) && count($input)){
				foreach($input as $field => $value){
					$field_array[] = $field;
					$value_array[] = ($value === 'null')?'NULL':"'".$value."'";
				}
				
				$field_str = implode(",",$field_array);
				$value_str = implode(",",$value_array);
				
				$sql = "INSERT INTO ".$tbl_name." ({$field_str}) VALUES ({$value_str})";
				$selectrs = self::$db->query($sql);
				return self::$db->report();
			}
		}
		
		private static function update($tbl_name,array $input,$target=false){
			
			if(is_array($input) && count($input)){

				if(is_array($target)){
					# 指定目標欄位
					list($last_key) = array_keys($target);
					$where_handle = $last_key." = '".$target[$last_key]."'"; # 組建 where 字串
				}else{
					# 未指定目標欄位取最後一項
					$input_keys = array_keys($input); # 輸出所有欄位名稱
					$last_key = array_pop($input_keys); # 取出最後一位
					$where_handle = $last_key." = '".$input[$last_key]."'"; # 組建 where 字串
					unset($input[$last_key]); # 刪除陣列最後一位
				}
				
				foreach($input as $field => $value){
					$value_str = ($value === 'null')?'NULL':"'".$value."'";
					$sql_array[] = "{$field} = {$value_str}";
				}
				
				$sql_str = implode(",",$sql_array);
				
				$sql = "UPDATE ".$tbl_name." SET ".$sql_str." WHERE ".$where_handle;
				$selectrs = self::$db->query($sql);
				return self::$db->report();
			}
		}

		# 檢查欄位是否符合資料表
		private static function match_field($tb_name,array $args){
			self::$sql = "SHOW FULL FIELDS FROM ".self::$cfg['tb_prefix'].'_'.$tb_name;
			$selectrs = self::$db->query(self::$sql);
			$rsnum = self::$db->numRows($selectrs);

			if(!empty($rsnum)){
				while($row = self::$db->fetch_array($selectrs,1)){
					$args_field = array_keys($args);
					if(in_array($row["Field"],$args_field)){
						$var = self::strslashes($args[$row["Field"]]);
						$new_args[$row["Field"]] = $var;
					}
				}

				if(is_array($new_args) && count($new_args)){
					return $new_args;
				}else{
					return false;
				}
			}

			return false;
		}

		# 篩選條件處理
		private static function sk_handle($sk=false){
			if(is_array($sk) && count($sk)){
				foreach($sk as $field => $var){
					switch($field){
						case "sk":
							if(!empty($var)) $where_array[] = $var;
						break;
						case "custom":
							$where_array[] = $var;
						break;
						default:
							switch($var){
								case "null":
									$where_array[] = $field.' IS NULL';
								break;
								default:
									if(preg_match("/%([^%])+/", $var) || preg_match("/([^%])+%/", $var)){
										$equation = 'like';
									}

									if(is_array($var)){
										$equation = 'in';
										$var = "('".implode("','",$var)."')";
									}

									$var = self::strslashes($var);

									$equation = (isset($equation))?" {$equation} ":' = ';
									$where_array[] = $field.$equation."'{$var}'";
								break;
							}
						break;
					}
				}

				if(is_array($where_array)){
					return "where ".implode(" and ",$where_array);
				}
			}

			return false;
		}

		# 取得欄位設定
		private static function field_handle($fetch_array=false){
			if(!is_array($fetch_array)) return '*';
			return implode(",",$fetch_array);
		}

		# 排序方法
		private static function order_handle($order_array=false){
			if(is_array($order_array) && count($order_array)){
				foreach($order_array as $order_field => $order_sort){
					$order_str_array[] = $order_field.' '.$order_sort;
				}
			}

			if(is_array($order_str_array)){
				return "order by ".implode(",",$order_str_array);
			}else{
				return false;
			}
		}

		# 清除紀錄資料列紀錄並轉出
		public static function dataClear(){
			parent::stalize();

			$data = self::$data;
			self::$data = false;
			return $data;
		}

		# 取得各表資料
		public static function dataFetch($tb_name,$sk=false,$fetch=false,$sort=false,$limit=false){
			self::dataClear();

			$where = self::sk_handle($sk);
			$field = self::field_handle($fetch);
			$order = self::order_handle($sort);

			$select = array(
				'table' => self::$cfg['tb_prefix'].'_'.$tb_name,
				'field' => $field,
				'where' => $where,
				'order' => $order,
				'limit' => (!empty($limit))?"limit ".$limit:'',
			);

			self::$sql = "select {$select["field"]} from {$select["table"]} {$select["where"]} {$select["order"]} {$select["limit"]}";
			$selectrs = self::$db->query(self::$sql);
			$rsnum    = self::$db->numRows($selectrs);

			switch($rsnum){
				case 0:
				break;
				case 1:
					self::$data[0] = self::$db->fetch_array($selectrs,1);
				break;
				default:
					while($row = self::$db->fetch_array($selectrs,1)){
						$all_row[] = $row;
					}

					self::$data = $all_row;
				break;
			}

			return $rsnum;
		}

		# 新增資料
		public static function dataInsert($tb_name,array $args){
			parent::stalize();

			$new_args = self::match_field($tb_name,$args);

			if(is_array($new_args) && count($new_args)){
				self::$rs = self::insert(self::$cfg['tb_prefix'].'_'.$tb_name,$new_args);
				self::$id = self::$db->get_insert_id();
			}else{
				return false;
			}
		}

		# 修改資料
		public static function dataUpdate($tb_name,array $args){
			parent::stalize();

			$new_args = self::match_field($tb_name,$args);

			if(is_array($new_args) && count($new_args)){
				$new_args = array_reverse($new_args);
				self::$rs = self::update(self::$cfg['tb_prefix'].'_'.$tb_name,$new_args);
			}else{
				return false;
			}
		}
		
		# 刪除資料
		public static function dataDel($tb_name,array $args){
			parent::stalize();

			$rsnum = CRUD::dataFetch($tb_name,$args);
			if(!empty($rsnum)){
				list($row) = CRUD::$data;

				if(is_array($args) && count($args)){
					$field_array = array_keys($args);
					$field = array_shift($field_array);
					$value = $args[$field];
					
					$sql = "DELETE FROM ".self::$cfg['tb_prefix']."_".$tb_name." WHERE ".$field." = '".$value."'";
					$selectrs = self::$db->query($sql);
					self::$rs = self::$db->report();
				}

				return true;
			}else{
				return false;
			}
		}		
	}

	new CRUD;