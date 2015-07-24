<?php

	# 靜態化工具
	class STATICTOOL{

		public static 
			$db,
			$cfg,
			$tpl,
			$main,
			$msg,
			$ws;

		function __construct(){
			self::auto_include();
		}

		# 載入相關模組
		private static function auto_include(){
			$this_path = realpath(dirname(__file__)).DIRECTORY_SEPARATOR;
			$files = glob($this_path.'*.php');
			foreach($files as $file){
				$exist = file_exists($file);
				$file_name = str_replace($this_path, '', $file);
				
				if($file_name != 'index.php' && $exist){
					include_once $file;
				}
			}
		}

		# 全域變數轉靜態
		public static function stalize(){
			global $db,$cms_cfg,$tpl,$main,$TPLMSG,$ws_array;
			
			self::$db = $db;
			self::$cfg = $cms_cfg;
			self::$tpl = $tpl;
			self::$main = $main;
			self::$msg = $TPLMSG;
			self::$ws = $ws_array;
		}
	}

	new STATICTOOL;
?>