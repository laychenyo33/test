<?php
class My_baseController extends GoEz_Controller {
    protected $_bootstrap;
    protected $_session;

    function setConfig($config) {
        if(is_a($config['bootstrap']['instance'],'GoEz_Bootstrap')){
            $this->_bootstrap = $config['bootstrap']['instance'];
            unset($config['bootstrap']['instance']);
        }
        parent::setConfig($config);
    }
    
    function setRequest(My_Request $request) {
        $request->setController($this);
        parent::setRequest($request);
    }
    
    function init(){
        if(!session_id()){
            session_start();
        }
        $this->_session = &$_SESSION['app_reflog'];
        if(!isset($this->_session['memberData'])){
            $json = file_get_contents($this->_config['memberdata']['source']);
            if($json){
                $data = json_decode($json,true);
                $this->_session['memberData'] = $data;
            }else{
                trigger_error("Can't retrieve member data from remote ", E_WARNING);
            }
        }
        $this->_view->login = $this->_session['login'];
    }
    
}
