<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$ad = new AD;
class AD{
    function AD(){
        global $db,$cms_cfg,$tpl,$main;
        //show page
        echo App::getHelper('ad')->getAdOne($cms_cfg['index_pop_ad_cate']);
    }
}
?>