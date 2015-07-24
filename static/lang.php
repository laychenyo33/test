<?php

	# 主選單名詞標籤
	class LANG extends STA{

		public static $prefix = 'LANG'; # 指定輸出標籤前綴詞
		private static $output;

		function __construct(){
			self::fetch();
		}

		# 組合語系標籤
		public static function fetch(){
			self::$output = false;

			$args = func_get_args();
			if(is_array($args) && count($args)){
				foreach($args as $index => $var){
					$msg = self::$msg[$var];
					if(empty($msg)){
						$msg = $var;
						$var = $index;
					}

					self::$output[$var] = $msg;
				}
			}else{
				foreach(self::$ws["default_words"] as $index){
					self::$output[$index] = self::$msg[$index];
				}
			}

			self::output();
		}

		# 輸出
		public static function output(){
			if(is_array(self::$output)){
				foreach(self::$output as $index => $var){
					self::$tpl->assignGlobal(strtoupper(self::$prefix)."_".strtoupper($index),$var);
				}
			}
		}
	}

	new LANG;
?>