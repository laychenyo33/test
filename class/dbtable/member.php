<?php
class Dbtable_Member extends Dbtable_Abstract{
    //可修改的欄位
    protected $table = "member";
    protected $pk = "m_id";
    protected $sort_col = "m_sort";

    
    //取得post資料欄位
    protected function _retrieve_cols($post){
        parent::_retrieve_cols($post);
        $this->values['m_modifydate'] = date("Y-m-d H:i:s");
        $password = $this->_get_password($post);
        if($password)$this->values['m_password'] = $password;
    }
    
    protected function _get_password($post){
        if($post['m_password'] && $post['v_password']){
            if($post['m_password']==$post['v_password']){
                //return md5(trim($post['m_password']));
                return trim($post['m_password']);
            }
        }
    }    
}
?>
