<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
include_once("libs/libs-security-image.php");
$si = new securityImage();
$si->setFontColor("222222");
$si->setFontSize(5);
$si->setCodeLength(4);
$si->inputParam = "style='color:blue;'";
$login= new LOGIN();
class LOGIN {
    //主程式
    function Login(){
        global $si;
        switch($_REQUEST["func"]){
            case "check_data":
                $this->valid_pass=$si->isValid();
                $this->Check_Login_Data();
                break;
            case "check_novalid":
                $_POST['callback']=1;
                $this->valid_pass=1;
                $this->Check_Login_Data();
                break;
            case "logout":
                $this->Logout();
                break;
        }
    }

    //檢查輸入的帳號密碼
    function Check_Login_Data(){
        global $db,$tpl,$TPLMSG,$cms_cfg,$si;

        if (isset($_POST['callback']) && $this->valid_pass) {
            $sql="select m.m_id,m.m_account,m.m_fname,m.m_lname,mc.mc_id,mc.mc_subject,mc.mc_discount from ".$cms_cfg['tb_prefix']."_member as m left join ".$cms_cfg['tb_prefix']."_member_cate as mc on mc.mc_id=m.mc_id
                     where m.m_account='".$_REQUEST["mm_account"]."' and
                           m.m_password='".$_REQUEST["mm_password"]."' and
                           m.m_status='1'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum = $db->numRows($selectrs);
            if ($rsnum > 0) {
                Model_User::login($row,$_POST['return_url']);
            }else{
                //echo "2";
                $_SESSION[$cms_cfg['sess_cookie_name']]["ERROR_MSG"]=$TPLMSG['LOGIN_ERROR'];
                header("location: ".$_SERVER['HTTP_REFERER']."");
            }
        }else{
            //echo "3";
            $_SESSION[$cms_cfg['sess_cookie_name']]["ERROR_MSG"]=$TPLMSG['SECURITY_ERROR'];
            header("location: ".$_SERVER['HTTP_REFERER']);
        }
    }
    //登出
    function Logout(){
        Model_User::logout();
    }
}


?>
