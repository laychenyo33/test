<?php
/**
 * 自訂程式
 *
 */
class My_IndexController extends My_baseController
{
    protected $version;
    protected $operation;
            
    function init(){
        parent::init();
        $this->version = new My_Version($this->_config['bootstrap']['db']);
        $this->operation = new My_Operation($this->_config['bootstrap']['db']);
    }
    /**
     * 預設動作
     *
     */
    public function indexAction()
    {
        $this->_view->title = "專案修改歷程";
        $this->_view->versionList = $this->version->getList();
        $this->_view->operationList = $this->operation->getList();
        $this->_view->token = $this->_session['login']['token'];
        $this->_view->isLogin = isset($this->_session['login']);
        $this->_view->content = $this->_view->fetchTemplate("listform-editable.html");
        $this->_view->renderTemplate('index.tpl.htm');
    }

    /**
     * Cron
     *
     */
    public function cronAction()
    {
        echo "<pre>";
        print_r($this->_request->getParams());
        echo "</pre>";
        
    }
    
    /*
         * 登入
         */
    function loginAction(){
        $account = $this->_request->getPost('account');
        if($this->_session['memberData'] && isset($this->_session['memberData'][$account])){
            if($this->_session['memberData'][$account]['active']){
                $this->_session['login'] = $this->_session['memberData'][$account];
                $this->_session['login']['token'] = md5($account.time());
                $this->redirect('/');
            }else{
                $this->_view->errorMessage = "無效帳號";
            }
        }else{
            $this->_view->errorMessage = "帳號不存在";
        }
        $this->_view->content = $this->_view->fetchTemplate("login-error.html");
        $this->_view->renderTemplate('index.tpl.htm');            
    }
    
    /*
         * 登出
         */
    function logoutAction(){
        unset($this->_session['login']);//清除登入session
        unset($this->_session['memberData']);//清除暫存的遠端會員記錄
        $this->redirect('/');        
    }
    
    /*
         * 登出
         */
    function saveAction(){
        $object = $this->_request->getParam('object');
        $tokenValid = ($this->_session['login']['token'] && $this->_session['login']['token']==$this->_request->getPost("token"))?true:false;
        if($tokenValid){
            $data = $_POST[$object];
            if($data){
                switch($object){
                    case "version":
                        $result = $this->version->save($data);
                        break;
                    case "operation":
                        if($data['description']){
                            $data['operator'] = $this->_session['login']['name'];
                            $result = $this->operation->save($data);
                        }else{
                            $this->redirect('/');                            
                        }
                        break;
                }
            }
        }
        if($this->_request->isAjax()){
            $return['code'] = $result;
            if($tokenValid){
                if(!$result){
                    $errorInfo = $this->operation->getErrorInfo();
                    $return['error'] = $errorInfo[2];
                }else{
                    $return['description'] = nl2br($data['description']);
                }
            }else{
                $return['error'] = "無效token";
            }
            echo json_encode($return);
        }else{
            if($tokenValid){
                if(!$result){
                    $errorInfo = $this->operation->getErrorInfo();
                    $this->_view->errorMessage = $errorInfo[2];
                }else{
                    $this->redirect('/');
                }
            }else{
                $this->_view->errorMessage = "無效token";
            }
            $this->_view->content = $this->_view->fetchTemplate("save-error.html");
            $this->_view->renderTemplate('index.tpl.htm');           
        }
    }
    
    function deleteAction(){
        if($this->_request->isAjax()){
            $tokenValid = ($this->_session['login']['token'] && $this->_session['login']['token']==$this->_request->getPost("token"))?true:false;
            $return['code'] = 0;
            if($tokenValid){
                $result = $this->version->delete($this->_request->getPost('dir'));
                $errorInfo = $this->version->getErrorInfo();
                $return['code'] = $result?1:0;
                if($errorInfo[2]){
                    $return['error'] = $errorInfo[2];
                }
            }else{
                $return['error'] = "無效token"; 
            }
            echo json_encode($return);
        }
    }
}