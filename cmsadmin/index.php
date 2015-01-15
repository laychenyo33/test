<?php
session_start();
include_once("../conf/config.inc.php");
if(trim($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])==""){
    header("location: login.php");
}else{
    include_once("../libs/libs-manage-sysconfig.php");
    $tpl = new TemplatePower( $cms_cfg['manage_all_tpl'] );
    $tpl->assignInclude( "LEFT", $cms_cfg['manage_left_tpl']);
    $tpl->assignInclude( "TOP_MENU", $cms_cfg['manage_top_menu_tpl']);
    $tpl->assignInclude( "MAIN", "templates/ws-manage-index-tpl.html");
    $tpl->prepare();
	
	new SYS_CHECK();
    $main->mamage_authority();
    $tpl->printToScreen();
}

// 系統檢查
class SYS_CHECK{
	
	private static $msg;
	private static $title;
		
	function __construct(){
		$this->online_check();
		$this->version_check();
		$this->seo_check();
		$this->output();
	}
	
	// 檢查上線狀態
	private function online_check(){
		global $cms_cfg;
		
		self::$title[] = '上線狀態';
		if($cms_cfg['ws_online']){
			self::$msg[] = "目前上線 (ws_online)";
		}else{
			self::$msg[] = '目前下線 (ws_online) , <span style="color: red;">注意! 此狀態下 GA 設為關閉</span>';
		}
	}
	
	// 檢查系統版本
	private function version_check(){
		global $cms_cfg;
		
		self::$title[] = '系統版本';
		if(!empty($cms_cfg["ws_module"]["ws_version"])){
			self::$msg[] = $cms_cfg["ws_module"]["ws_version"].' (ws_version)';
		}else{
			self::$msg[] = '<span style="color: red;">未設定!<span>';
		}
	}
	
	// 檢查 SEO 狀態
	private function seo_check(){
		global $cms_cfg;
		
		self::$title[] = 'SEO 狀態';
		self::$msg[] = ($cms_cfg["ws_module"]["ws_seo"])?'啟動中 (ws_seo)':'未啟動 (ws_seo)';
	}
	
	// 檢查輸出
	private function output(){
		global $tpl;
		
		if(is_array(self::$msg) && is_array(self::$title)){
			foreach(self::$msg as $key => $msg){
				$tpl->newBlock("TAG_SYS_CHECK_LIST");
				$tpl->assign(array(
					"VALUE_SYS_TITLE" => self::$title[$key],
					"VALUE_SYS_MSG" => $msg,
				));
			}
		}
	}
}
?>