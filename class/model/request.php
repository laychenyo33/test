<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of request
 *
 * @author Administrator
 */
class Model_Request extends Model_Modules{
    //put your code here
    protected $_get;
    protected $_post;
    
    function __construct() {
        $this->_get = &$_GET;
        $this->_post = &$_POST;
    }
    
    function isPost(){
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }
    function isGet(){
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }
    function isAjax(){
        return $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    }
    
    function get_link($type,$data){
        return $this->getModule('link')->getModule($type)->get_link($data);
    }
    
    /**
     * 將主機端路徑改成URL，Model_Request::createURL的別名
     * @param string $localPath 主機端路徑
     * @return string 傳回主機端路徑的URL
     * @author 俊信 <chunhsin@allmarketing.com.tw>
     */    
    function createURL($localPath){
        $localPath = str_replace(App::configs()->file_root . 'upload_files/', '', $localPath, $replace_nums);
        if($replace_nums){
            return App::configs()->file_url . 'upload_files/' . $localPath;
        }else{
            $localPath = str_replace(App::configs()->base_root, '', $localPath);           
            return App::configs()->base_url . $localPath;
        }
    }
}
