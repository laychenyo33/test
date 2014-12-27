<?php
class Dbtable_Aboutus extends Dbtable_Abstract{
    //可修改的欄位
    protected $table = "aboutus";
    protected $pk = "au_id";
    protected $sort_col = "au_sort";
    
    protected function _retrieve_cols($post){
        parent::_retrieve_cols($post);
        $au_cate = $post['au_cate_input']?$post['au_cate_input']:$post['au_cate_select'];
        $au_cate = $au_cate?$au_cate:"aboutus";
        $this->values['au_cate'] = $this->db->quote($au_cate);
    }    
}
?>
