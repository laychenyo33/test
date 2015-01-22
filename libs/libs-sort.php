<?php

	# 自動排序調整功能
	
	class SORT{
		private static $pos;
		private static $tb;
		private static $prefix;
		private static $id;
		private static $date;
		private static $key;
		
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
						self::p_row();
					break;
					case "pc":
						//self::pc_row();
					break;
				}
			}else{
				# 找不到資料
			}
		}
		
		# 取得資料
		protected static function get_date(){
			global $db,$cms_cfg;
			
            $sql = "select * from ".self::$tb." where ".self::$prefix."_id = '".self::$id."'";
            $selectrs = $db->query($sql);
			$rsnum    = $db->numRows($selectrs);
			
			if(!empty($rsnum)){
            	self::$date = $db->fetch_array($selectrs,1);
				return true;
			}else{
				return false;
			}
		}
		
		//------------------------------------------------------------
		#依照不同功能 執行不同的排序篩檢法
		
		# 產品排序
		protected static function p_row(){
			global $db,$cms_cfg;
			
            $sql = "select * from ".self::$tb." where pc_id = '".self::$date["pc_id"]."' order by p_sort ".self::$pos;
            $selectrs = $db->query($sql);
			$rsnum    = $db->numRows($selectrs);
			
			if(!empty($rsnum)){
				// 確認排序順序
				$order_pos = ($cms_cfg["sort_pos"] == "asc")?true:false;
				
				if(self::$date["p_sort"] >= self::$key){
					if($order_pos){
						// 往前移動
						$move = false;
					}else{
						// 往後移動
						$move = true;
					}
				}else{
					if($order_pos){
						// 往後移動
						$move = true;
					}else{
						// 往前移動
						$move = false;
					}
				}
				
				$sort_count = ($order_pos)?1:$rsnum;
				while($row = $db->fetch_array($selectrs,1)){
					
					// 插入修改位置
					if(self::$key == $sort_count){
						if($move){
							if((self::$date["p_id"] != $row["p_id"])){
								$sort[] = $row["p_id"];
							}
							$sort[] = self::$date["p_id"];
						}else{
							$sort[] = self::$date["p_id"];
							if((self::$date["p_id"] != $row["p_id"])){
								$sort[] = $row["p_id"];
							}
						}
					}else{
						if((self::$date["p_id"] != $row["p_id"])){
							$sort[] = $row["p_id"];
						}
					}
					
					// 壘算排序
					if($order_pos){
						$sort_count++;
					}else{
						$sort_count--;
					}
				}
				
				print_r($sort);
				
				exit;
			}
		}
	}

?>