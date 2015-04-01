<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$login= new LOGIN();
class LOGIN {
    //主程式
    function __construct(){
        if($_GET['fb_uid']){
            $sql = "select * from ".App::getHelper('db')->prefix("member")." where fb_uid='{$_GET['fb_uid']}'";
            if($local_member = App::getHelper('db')->query_firstRow($sql,1)){
                Model_User::login($local_member,$_GET['return']);
            }else{
                header("location:".App::configs()->base_root . "member.php?func=m_add&tool=fb&fb_uid=".$_GET['fb_uid']."&return=".urlencode($_GET['return']));
            }
        }else{
            header("location:".App::configs()->base_root);
        }
    }

}


?>
