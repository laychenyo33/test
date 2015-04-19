<?php
class Model_User extends Model_Modules {
    function __construct( $options = "") {
        parent::__construct($this, $options);
    }

    function authenticate(){
        $authtype = $this->_options['authtype'];
        if($this->$authtype->authenticate()) return true;
    }
    
    function login($local_member,$return=''){
        global $TPLMSG;
        $contact_s = App::configs()->ws_module->ws_contactus_s_style;
        App::getHelper('session')->MEMBER_ID=$local_member["m_id"];
        App::getHelper('session')->MEMBER_ACCOUNT=$local_member["m_account"];
        App::getHelper('session')->MEMBER_NAME=sprintf($TPLMSG['MEMBER_NAME_SET_'.$contact_s],$local_member["m_fname"],$local_member["m_lname"]);
        App::getHelper('session')->MEMBER_CATE_ID=$local_member["mc_id"];
        App::getHelper('session')->MEMBER_CATE=$local_member["mc_subject"];
        App::getHelper('session')->MEMBER_DISCOUNT=$local_member["mc_discount"];       
        //寫入登入記錄
        $sql="
            insert into ".App::getHelper('db')->prefix("login_history")." (
                m_id,lh_success,lh_modifydate
            ) values (
                '".$local_member["m_id"]."','1','".date("Y-m-d H:i:s")."'
            )";
        App::getHelper('db')->query($sql);
        $sql = "SELECT COUNT( * ) FROM  ".App::getHelper('db')->prefix("login_history")." WHERE m_id = '{$local_member['m_id']}' AND lh_success = '1'";
        list($login_times) = App::getHelper('db')->query_firstRow($sql,0);
        App::getHelper('dbtable')->member->update(array('login_times'=>$login_times),"m_id='{$local_member['m_id']}'");
        App::getHelper('session')->ERROR_MSG="";
        if(empty($return)){
            header("location: ".$_SERVER['HTTP_REFERER']);
        }else{
            header("location: ".$return);
        }
        die();
    }
    
    function logout(){
        App::getHelper('session')->destroy();
        header("location: ".App::configs()->base_root);
    }
    
}
