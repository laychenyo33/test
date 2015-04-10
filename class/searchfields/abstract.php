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
class searchFields_abstract {
    /*
     * $search_fields 
     * 型態：陣列
     * 說明：作用的關聯索引及其值用意說明(型態)
     *      title：搜尋標題(字串)
     *      fields：該搜尋標題要比對的資料庫欄位(陣列)
     *      compare：搜尋值與資料庫欄位比對的方式，是物件內的方法名稱(字串)
     */
    protected $search_fields = array(); 
    protected $field_template = "./templates/ws-manage-fn-search-field-tpl.html";
    protected $default_compare = "default_compare";
    protected $exportUrl;
    
    function __construct() {
        
    }
    
    protected function _init_tpl(){
        $tpl = new TemplatePower($this->field_template);
        $tpl->prepare();
        if($this->exportUrl){
            $tpl->newBlock("EXPORT_BTN");
            $tpl->assign('exportUrl',$this->exportUrl);
        }
        return $tpl;
    }
    
    function list_search_fields($st,$sk){
        $tpl = $this->_init_tpl();
        $tpl->newBlock("SINGLE_FIELD");
        if(is_array($this->search_fields) && !empty($this->search_fields)){
            $tpl->newBlock("SEARCH_FIELD_OPTION");
            $tpl->assign(array(
               "TAG_FIELD_TITLE"   => "全部", 
               "TAG_FIELD_VALUE"   => "all", 
            ));            
            foreach($this->search_fields as $key => $field){
                $tpl->newBlock("SEARCH_FIELD_OPTION");
                $tpl->assign(array(
                   "TAG_FIELD_TITLE"   => $field['title'], 
                   "TAG_FIELD_VALUE"   => $key, 
                   "TAG_FIELD_SELECTED" => (!empty($st) && $key==$st)?"selected":"", 
                ));
                $tpl->assignGlobal(array("VALUE_SEARCH_KEYWORD" => trim($sk), ));
            }
        }
        return $tpl->getOutputContent();
    }
    
    function list_multiple_search_fields(){
        $tpl = $this->_init_tpl();        
        $tpl->newBlock("MULTIPLE_FIELDS");
        if(is_array($this->search_fields) && !empty($this->search_fields)){
            foreach($this->search_fields as $key => $field){
                $tpl->newBlock("MULTIPLE_FIELD_LIST");
                $tpl->assign(array(
                    'FIELD_LABEL' => $field['title'],
                    'TAG_FIELD'   => $this->make_field($key,$field),
                ));
            }
        }
        return $tpl->getOutputContent();
    }
    
    function find_search_value_sql($and_str,$st,$sk){
        $sk = trim($sk);
        if(is_array($and_str)){
            $and_str = implode(" and ",$and_str);
        }        
        if(isset($sk)){
            if($st=="all"){
                $tmp = "";
                foreach($this->search_fields as $key=>$field){
                    if($key!="all"){
                        if(is_array($field['fields'])){
                            $return = $this->compare_field($field, $sk);
                            if($return){
                                $tmp .= (($tmp)?" or ":"").$return;
                            }
                        }
                    }
                }
            }else{
                if(is_array($this->search_fields[$st]['fields'])){
                    $tmp = $this->compare_field($this->search_fields[$st], $sk);
                }
            }
        }
        if($tmp){
            $and_str .=  ((trim($and_str))?" and ":"")." (".$tmp.")";
        }
        return $and_str;
    }
    
    function find_multiple_search_value($and_str){
        if(is_array($and_str)){
            $and_str = implode(" and ",$and_str);
        }
        foreach($this->search_fields as $key=>$field){
            $return = '';
            if(is_array($field['fields'])){
                if($field['fieldType']!='date'){
                    if(isset($_REQUEST[$key])){
                        $return = $this->compare_field($field, $_REQUEST[$key]);
                    }
                }else{
                    $return = $this->compare_field($field, '');
                }
                if($return){
                    $tmp .= (($tmp)?" and ":"").$return;
                }
            }
        }
        if($tmp){
            $and_str .=  ((trim($and_str))?" and ":"")." (".$tmp.")";
        }
        return $and_str;        
    }
    
    function compare_field($field,$input){
        if($field['compare']){
            $callback = array($this,$field['compare']);
        }else{
            $callback = array($this,$this->default_compare);
        }
        return call_user_func($callback,$field,$input);        
    }
    
    function default_compare($fields,$val){
        global $db;
        $tmp = "";
        $absolute = $fields['dataSource']?true:false;
        foreach($fields['fields'] as $f){
            if($absolute){
                $tmp .= (($tmp)?" or ":"").$f." ='".$db->quote($val)."'";
            }else{
                $tmp .= (($tmp)?" or ":"").$f." like '%".$db->quote($val)."%'";
            }
        }
        $tmp = "(".$tmp.")";
        return $tmp;
    }
    
    function between_date($fields,$val){
        $dateinfo = getdate(strtotime($val));
        $field = $fields['fields'][0]; //cu_modifydate
        $ts1 = mktime(0,0,0,$dateinfo['mon'],$dateinfo['mday'],$dateinfo['year']);
        $ts2 = mktime(0,0,0,$dateinfo['mon']+1,$dateinfo['mday'],$dateinfo['year']);      
        $tmp = "(".$field . " between '".date("Y-m-01",$ts1)."' and '".date("Y-m-01",$ts2)."') ";
        return $tmp;
    }
    
    function date_interval($fields,$val){
        $field = $fields['fields'][0]; //cu_modifydate
        if(($ts1 = strtotime($_REQUEST[$fields['urlParam'].'_1'])) && ($ts2 = strtotime($_REQUEST[$fields['urlParam'].'_2']))){
            $ts2 = strtotime('+86400 seconds',$ts2);
            if($ts2<$ts1){
                $gg = $ts1;
                $ts1 = $ts2;
                $ts2 = $gg;
            }
            $tmp = "(".$field . " between '".date("Y-m-d H:i:s",$ts1)."' and '".date("Y-m-d H:i:s",$ts2)."') ";
        }elseif($ts1 = strtotime($_REQUEST[$fields['urlParam'].'_1'])){
            $tmp = "(".$field . " >= '".date("Y-m-d H:i:s",$ts1)."') ";
        }elseif($ts2 = strtotime($_REQUEST[$fields['urlParam'].'_2'])){
            $ts2 = strtotime('+86400 seconds',$ts2);
            $tmp = "(".$field . " <= '".date("Y-m-d H:i:s",$ts2)."') ";
        }
        return $tmp;
    }
    
    function make_field($fieldName,$field){
        if(!$field['fieldType'] || in_array($field['fieldType'],array('input','date','select'))){
            if(!$field['fieldType'] || $field['fieldType']!=="date"){
                $tag_name = $field['fieldType']?$field['fieldType']:"input";
                $field_tag = "<".$tag_name." id=\"{$fieldName}\" class=\"seinput\" name=\"{$fieldName}\" ";
                if($field['fieldType']=="select"){
                    $field_tag .= ">";
                    $field_tag .= "<option value=\"\">請選擇</option>";
                    if(is_array($field['dataSource'])){
                        foreach($field['dataSource'] as $source_id => $source_value){
                            $tag_selected = ($source_id==$_REQUEST[$fieldName] && isset($_REQUEST[$fieldName]))?'selected':'';
                            $field_tag .= "<option value=\"{$source_id}\" {$tag_selected}>{$source_value}</option>";
                        }
                    }
                    $field_tag .= "</".$tag_name .">";
                }else{
                    $field_tag .= "type=\"text\" value=\"".$_REQUEST[$fieldName]."\" size=\"10\"/>";  
                }
            }else{
                $fieldName = $field['urlParam'];
                $field_tag  = "開始:<input type=\"text\" id=\"{$fieldName}_1\" class=\"seinput datepicker\" name=\"{$fieldName}_1\" value=\"".$_REQUEST[$fieldName.'_1']."\" size=\"10\"/>";
                $field_tag .= " - ";
                $field_tag .= "結束:<input type=\"text\" id=\"{$fieldName}_2\" class=\"seinput datepicker\" name=\"{$fieldName}_2\" value=\"".$_REQUEST[$fieldName.'_2']."\" size=\"10\"/>";
            }
            return $field_tag;
        }
    }
}
?>
