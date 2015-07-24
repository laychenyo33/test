<?php
ob_start("ob_gzhandler");
//ini_set('session.cache_limiter', 'private');
session_cache_limiter('private_no_expire, must-revalidate');
if($_GET['sess']){
    session_id($_GET['sess']);
}
session_start();
define('APP_ROOT_PATH', realpath(dirname(__FILE__).'/../') . DIRECTORY_SEPARATOR);
include_once(APP_ROOT_PATH."conf/config.inc.php");
include_once(APP_ROOT_PATH."libs/libs-mysql.php");
$db = new DB($cms_cfg['db_host'],$cms_cfg['db_user'],$cms_cfg['db_password'],$cms_cfg['db_name'],$cms_cfg['tb_prefix']);
$_SESSION[$cms_cfg['sess_cookie_name']]['SERVER_ID']=1;
//設定錯誤報告
ini_set("display_errors",$cms_cfg['debug']);
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
ini_set("log_errors",$cms_cfg['log_errors']);
ini_set("error_log","log/".$cms_cfg['error_log']);
//取得網站的設定
$sql="select * from ".$cms_cfg['tb_prefix']."_system_config where sc_id='1'";
$selectrs = $db->query($sql);
$row = $db->fetch_array($selectrs,1);
$rsnum = $db->numRows($selectrs);
if($rsnum >0 ){
	foreach($row as $key => $value){
		$_SESSION[$cms_cfg['sess_cookie_name']][$key]=$value;
	}
}
if($cms_cfg['ws_activate_mobile']){
    include_once APP_ROOT_PATH . 'class/Mobile_Detect.php';
    $detect = new Mobile_Detect;
    if($detect->isMobile() && !$cms_cfg['ws_ismobile'] && !isset($_COOKIE['USE_COM_VER'])){
        header("location:".$cms_cfg['mobile_url']);
        die();
    }
}
include_once(APP_ROOT_PATH."libs/libs-main.php");
$mainfunc_class = class_exists("MAINFUNC_NEW")?"MAINFUNC_NEW":"MAINFUNC";
$main = new $mainfunc_class;
include_once(APP_ROOT_PATH."TP/class.TemplatePower.inc.php");
include_once(APP_ROOT_PATH."conf/sms.php");
include_once(APP_ROOT_PATH."lang/".$cms_cfg['language']."-utf8.php");
//處理串接購物車模組
if($cms_cfg['ws_module']['ws_shopping_cart_module']){
    $tmp = explode('+',$cms_cfg['ws_module']['ws_shopping_cart_module']);
    if(is_dir($_SERVER['DOCUMENT_ROOT'] . $cms_cfg['base_root'] .  $tmp[0])){
        $cms_cfg['new_cart_path'] = $cms_cfg['base_root']. $tmp[0] . '/';
    }
    $tmp[1] = str_replace('.', '/', $tmp[1]);
    $new_cart_class = $_SERVER['DOCUMENT_ROOT'] . $cms_cfg['base_root'] . $tmp[1].".php";
    if(file_exists($new_cart_class)){
        //引用歐付寶類別
        include_once( $new_cart_class);
    }
}
include_once(APP_ROOT_PATH."conf/default-items.php");
include_once(APP_ROOT_PATH."static/index.php");
//autoload class
require APP_ROOT_PATH."class/autoloader.php";
$autoloader = new autoloader();
spl_autoload_register(array($autoloader,"load"));
//儲存設定
App::configs($cms_cfg);
App::defaults($ws_array);
//session handler
$sessHandler = Model_Session::factory($cms_cfg['sess_cookie_name'],$ws_array['models_options']['session']);
//helper機制
App::addHelper('db', $db);
App::addHelper('main', $main);
App::addHelper('session', $sessHandler);
App::addHelper('request', new Model_Request());
App::addHelper('dbtable', new Model_Dbtable($db));
App::addHelper('ad', new Model_Ad($db,$_SERVER['DOCUMENT_ROOT'].$cms_cfg['base_root'],$cms_cfg['sort_pos']));
if(App::configs()->ws_module->ws_seo){
    App::addHelper('sysvars', new Model_Sysvars());
}
//網站關閉時
if(App::getHelper('session')->sc_status==0 && App::getHelper('session')->USER_ACCOUNT==""){
    $tpl = new TemplatePower("templates/ws-web-close.html");
    $tpl->prepare();
    App::getHelper('main')->header_footer("");
    $tpl->assignGlobal("MAIN_MESSAGE",$TPLMSG['WEB_CLOSE_MSG']);
    $tpl->printToScreen();
    die();
}
?>