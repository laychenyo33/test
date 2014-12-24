<?php
class Model_Session_Cart_Translator_Spec extends Model_Session_Cart_Translator_Db{
    function translate($origin_data) {
        $origin_data['spec'] = $this->_translate($origin_data['ps_id']);
        $origin_data['price'] = $this->_get_price($origin_data['ps_id']);
        $origin_data['stocks'] = $this->_get_quantity($origin_data['ps_id']);
        return $origin_data;
    }
    //組合spec layer
    protected function _translate($ps_id){
        $spec_layer = $this->_get_spec_layer($ps_id);
        $spec_translated = '';
        if(is_array($spec_layer) && !empty($spec_layer)){
            do{
                $spec_translated .= (($spec_translated!='')?"-":""). $spec_layer['pst_subject'];
            }while($spec_layer = $spec_layer['child']);
        }
        return $spec_translated;
    }
    //取得spec alyer
    protected function _get_spec_layer($ps_id,$child=null){
        $spec_data = $this->_get_spec_data($ps_id);
        if($child){
            $spec_data['child'] = $child;
        }
        if($spec_data['parent']>0){
            return $this->_get_spec_layer($spec_data['parent'],$spec_data);
        }else{
            return $spec_data;
        }
    }
    //query products_spec_title
    protected function _get_spec_data($ps_id){
        $sql = "select b.pst_subject,a.parent from ".$this->_db->prefix("products_spec")." as a inner join ".$this->_db->prefix("products_spec_title")." as b on a.pst_id=b.pst_id where a.ps_id='".$ps_id."'";
        return $this->_db->query_firstRow($sql);
    }
    //取得價格
    protected function _get_price($ps_id){
        $sql = "select price from ".$this->_db->prefix("products_spec_attributes")." where ps_id='".$ps_id."'";
        list($price) = $this->_db->query_firstRow($sql,false);
        return $price;
    }
    //取得庫存
    protected function _get_quantity($ps_id){
        $sql = "select quantity from ".$this->_db->prefix("products_spec_attributes")." where ps_id='".$ps_id."'";
        list($quantity) = $this->_db->query_firstRow($sql,false);
        return $quantity;
    }
}
