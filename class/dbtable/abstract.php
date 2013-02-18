<?php
abstract class Dbtable_Abstract {
    //可修改的欄位
    protected $prefix;
    protected $table;
    protected $db;
    protected $pk;
    protected $post_cols = array(); 
    protected $values = array();
    protected $con = array();
    
    public function __construct(DB $db,$prefix) {
        $this->db = $db;
        $this->prefix = $prefix;
    }
    
    public function __get($name) {
        return $this->values[$name];
    }
    
    public function writeData($post){
        $this->_retrieve_cols($post); 
        if(isset($this->values[$this->pk])){
            $sql = $this->_mk_update_sql();
        }else{
            $sql = $this->_mk_insert_sql();
        }
        $this->db->query($sql,true);
    }
    
    public function getData($pk,$cols="*"){
        if(is_array($cols)){
            $cols = implode(',',$cols);
        }
        $sql_tpl = "select %s from ".$this->tablename()." where `%s`='%s'";
        $sql = sprintf($sql_tpl,$cols,$this->pk,$pk);
        $res = $this->db->query($sql,true);
        $row = $this->db->fetch_array($res, 1);
        $this->values = $row;
        return $this;
    }
    
    public function getDataRow(){
        return $this->values;    
    }
    
    public function report(){
        return $this->db->report();
    }
    //取得post資料欄位
    protected function _retrieve_cols($post){
        foreach($post as $k=>$v){
            if(isset($this->post_cols[$k])){
                if(is_array($v)){
                    $this->values[$k] = implode(',',$v);
                }else{
                    $this->values[$k] = mysql_real_escape_string(trim($v));
                }
            }
        }
    }
    //製作新增sql
    protected function _mk_insert_sql(){
        $sql_tpl = "insert into ".$this->tablename()."(%s)values(%s)";
        foreach($this->values as $k=>$v){
            $columns[]=sprintf("`%s`",$k);
            $values[]=sprintf('%s',$v);
        }
        return sprintf($sql_tpl,implode(',',$columns),implode(',',$values));
        
    }
    
    //製作新增sql
    protected function _mk_update_sql(){
        global $cms_cfg;
        $sql_tpl = "update ".$this->tablename()." set %s where %s";
        foreach($this->values as $k=>$v){
            if($k!=$this->pk){
                $updates[] = sprintf("`%s`='%s'",$k,$v);
            }
        }
        $this->con[] = sprintf("`%s`='%s'",$this->pk,$this->values[$this->pk]);
        return sprintf($sql_tpl,implode(',',$updates),implode(' and ',$this->con));
    } 
    
    protected function tablename(){
        return $this->prefix."_".$this->table;
    }
}

?>
