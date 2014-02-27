<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of contactus
 *
 * @author chunhsin
 */
class searchFields_products extends searchFields_abstract{
    protected $search_fields = array(
        'p_name' => array("title"=>"產品名稱","fields"=>array("p_name")),
        'p_desc' => array("title"=>"產品敘述","fields"=>array("p_desc")),
        'p_spec' => array("title"=>"產品規格","fields"=>array("p_spec")),
        'p_character' => array("title"=>"產品特性","fields"=>array("p_character")),
        'p_modifydate' => array("title"=>"編輯月份","fields"=>array("p_modifydate"),'compare'=>"between_date"),
        'p_main_cate' => array("title"=>"主分類","fields"=>array("pc_layer"),'compare'=>"in_main_cate"),
        'p_cate_of' => array("title"=>"所屬分類","fields"=>array("pc_id"),'compare'=>"in_cate"),
    ); 
    function in_main_cate($fields,$val){
        global $db,$cms_cfg;
        $con = array();
        $val = explode(',',$val);
        foreach($val as $v){
            if($v=trim($v)){
                $con[] = " pc_name like '%".$v."%' ";
            }
        }
        if($con){
            $sql = "select pc_layer from ".$db->prefix("products_cate")." where ".implode(' or ',$con);
            $res = $db->query($sql);
            $subCon = array();
            while(list($pc_layer)=$db->fetch_array($res,0)){
                $subCon[] = " p.pc_layer regexp '{$pc_layer}-([0-9]+-*)+' ";
            }
            if($subCon){
                return "(". implode(' or ',$subCon) .' )';
            }
        }
    }
    function in_cate($fields,$val){
        global $db,$cms_cfg;
        $con = array();
        $val = explode(',',$val);
        foreach($val as $v){
            $con[] = " pc_name like '%".trim($v)."%' ";
        }
        if($con){
            $sql = "select pc_id from ".$db->prefix("products_cate")." where ".implode(" or ",$con);
            return " ( p.{$fields[0]} in( {$sql} ) )";
        }
    }
}
?>
