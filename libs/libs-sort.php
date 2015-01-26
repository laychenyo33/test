<?php

	# 自動排序調整功能
	
	class SORT{
		private static $pos;
		private static $tb;
		private static $prefix;
		private static $id;
		private static $data;
		private static $key;
		private static $sort;
		
		# 初始化
		function __construct($tb_name,$prefix,$id,$key){
			global $cms_cfg;
			
			self::$pos = $cms_cfg["sort_pos"]; // 排序順序
			self::$tb = $tb_name; // 資料表
			self::$prefix = $prefix; // 資料表前綴字 (例：p,pc,nc 等...)
			self::$id = $id; // 資料 primary key
			self::$key = $key; // 修改的 sort key
			
			$rs_bool = self::get_date();
			
			if($rs_bool){
				switch(self::$prefix){
					case "p":
					case "pc":
					case "pa":
					case "n":
						self::has_cate();
					break;
					case "nc":
						self::none_cate();
					break;
					default:
						return false;
					break;
				}
			}else{
				# 找不到資料
			}
		}
		
		# 取得資料
		protected static function get_date(){
			global $db,$cms_cfg;
			
			if(is_array(self::$id)){
				$where = " where ".self::$prefix."_id in ('".implode("','",self::$id)."')";
			}else{
				$where = " where ".self::$prefix."_id = '".self::$id."'";
			}
			
            $sql = "select * from ".self::$tb." ".$where;
            $selectrs = $db->query($sql);
			$rsnum    = $db->numRows($selectrs);
			
			if(!empty($rsnum)){
            	while($row = $db->fetch_array($selectrs,1)){
            		if(isset($_REQUEST["sort_value"][$row[self::$prefix."_id"]])){
            			self::$sort[$row[self::$prefix."_id"]] = $_REQUEST["sort_value"][$row[self::$prefix."_id"]];
					}else{
						self::$sort[$row[self::$prefix."_id"]] = self::$key;
					}
					
					self::$data[$row[self::$prefix."_id"]] = $row;
				}
				
				return true;
			}else{
				return false;
			}
		}
		
		# 儲存排序
		protected static function sort_replace(array $sort){
			global $db,$cms_cfg;
			
			foreach($sort as $id => $sort_key){
				$sql = "update ".self::$tb." set ".self::$prefix."_sort='".++$i."' where ".self::$prefix."_id = '".$id."'";
				$rs = $db->query($sql);
				$db_msg = $db->report();
			}
		}
		
		//------------------------------------------------------------
		#依照不同功能 執行不同的排序篩檢法
		
		# 有分類的排序處理
		protected static function has_cate(){
			global $db,$cms_cfg;
			
			// 排序同分類產品
			foreach(self::$data as $data_row){
				if(isset($data_row[self::$prefix."_parent"])){
					$cate_field = self::$prefix."_parent";
				}else{
					$cate_field = self::$prefix."c_id";
				}
				
				$cate_array[] = $data_row[$cate_field];
			}
			
			$cate_array = array_flip($cate_array);
			$cate_array = array_flip($cate_array);
			
			// 分類分開計算
			foreach($cate_array as $cate_id){
				unset($jump_sort,$id_array,$sort,$sort_count);
				
				$sql = "select ".self::$prefix."_id from ".self::$tb." where ".$cate_field."='".$cate_id."' order by ".self::$prefix."_sort ".self::$pos;
				$selectrs = $db->query($sql);
				$rsnum    = $db->numRows($selectrs);
				
				// 進行排序
				if(!empty($rsnum)){
					while($row = $db->fetch_array($selectrs,1)){
						
						if(isset(self::$sort[$row[self::$prefix."_id"]])){
							$jump_sort[] = self::$sort[$row[self::$prefix."_id"]];
						}
						
						$id_array[] = $row[self::$prefix."_id"];
					}
					
					$sort_count = 1;
					foreach($id_array as $id){
						if(in_array($sort_count,$jump_sort)){
							++$sort_count;
						}
						
						if(isset(self::$sort[$id])){
							$sort[$id] = self::$sort[$id];
						}else{
							$sort[$id] = $sort_count;
							$sort_count++;
						}
					}
					
					asort($sort,SORT_NUMERIC);
					self::sort_replace($sort);
				}
			}
		}

		# 無分類的排序處理
		protected static function none_cate(){
			global $db,$cms_cfg;
			
			$sql = "select ".self::$prefix."_id from ".self::$tb." order by ".self::$prefix."_sort ".self::$pos;
			$selectrs = $db->query($sql);
			$rsnum    = $db->numRows($selectrs);
			
			// 進行排序
			if(!empty($rsnum)){
				while($row = $db->fetch_array($selectrs,1)){
					
					if(isset(self::$sort[$row[self::$prefix."_id"]])){
						$jump_sort[] = self::$sort[$row[self::$prefix."_id"]];
					}
					
					$id_array[] = $row[self::$prefix."_id"];
				}
				
				$sort_count = 1;
				foreach($id_array as $id){
					if(in_array($sort_count,$jump_sort)){
						++$sort_count;
					}
					
					if(isset(self::$sort[$id])){
						$sort[$id] = self::$sort[$id];
					}else{
						$sort[$id] = $sort_count;
						$sort_count++;
					}
				}
				
				asort($sort,SORT_NUMERIC);
				self::sort_replace($sort);
			}
		}
	}

?>