<?php
include_once("libs/libs-sysconfig.php");
new epaper_unregister();
class epaper_unregister {
    function __construct() {
        global $db,$cms_cfg,$TPLMSG,$main;
        if($_GET['email']){
            $sql = "select * from ".$cms_cfg['tb_prefix']."_member where m_email='".$db->quote($_GET['email'])."'";
            $res = $db->query($sql);
            if($db->numRows($res)){
                //取消註冊
                $sql = "update ".$cms_cfg['tb_prefix']."_member set m_epaper_status='0' where m_email='".$db->quote($_GET['email'])."'";
                $db->query($sql);
                if($db->report()==""){
                    $tpl = $main->get_mail_tpl("epaper");
                    $tpl->newBlock("EPAPER_UNSUB_NOTIFY");
                    $tpl->assign(array(
                        "TAG_VERSION"   => $TPLMSG['LANG_'.strtoupper($cms_cfg['language'])],
                        "VALUE_M_EMAIL" => $_GET['email'],
                    ));
                    $mailContent = $tpl->getOutputContent();
                    $main->ws_mail_send_simple($_GET['email'],$_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$mailContent,"取消訂閱電子報通知");
                    $main->js_notice($TPLMSG['EPAPER_UNSUB_SUCCESS'],$cms_cfg['base_root']);
                }              
            }
        }
    }
}
?>
