<?php
class Leftmenu_Cate extends Leftmenu_Abstract {
    //分類資訊
    protected $cate = array(
        'linkType'    => '',
        'labelField'    => '',
        'query' => array(
            'table'     => '',
            'alias'  => '',
            'pkField' => '',
            'parentField' => '',
            'select' => '',
            'condition' => '',
            'order' => '',
        ),
    );
    //遞迴的層次
    protected $maxDigs;  
    
    function __construct(TemplatePower $tpl,$maxDigs=2) {
        if($this->cate['query']['parentField']==''){
            throw new Exception("please assign parent field of cate table");
        }
        $this->maxDigs = $maxDigs;
        parent::__construct($tpl);
    }
    
    function getCateTable($alias=false){
        if($alias && $this->cate['query']['alias']){
            return $this->cate['query']['alias'];
        }
        return App::getHelper('db')->prefix($this->cate['query']['table']);
    }
    
    function getCateTableAlias(){
        return $this->cate['query']['alias']?" as ".$this->cate['query']['alias']." ":"";
    }
    
    protected function _getItems() {
        $menuItems = array();       
        $this->_digData($menuItems, 1, $this->maxDigs);
        return $menuItems;
    }
    
    protected function _digData(&$container,$digs,$maxDigs,$cateId=0){
        $sql = "select ".$this->cate['query']['select']." from ".$this->getCateTable()." where ".$this->_initQueryCondition($cateId) . $this->_initQueryOrder();
        $res = App::getHelper('db')->query($sql,true);
        while($cateRow = App::getHelper('db')->fetch_array($res,1)){
            $cateLink = App::getHelper('request')->get_link($this->cate['linkType'],$cateRow);
            $tmp = array(
                'name' => $cateRow[$this->cate['labelField']],
                'link' => $cateLink,
                'tag_cur' => (strcasecmp($_SERVER['REQUEST_URI'], $cateLink)==0)?"class='".$this->currentClass."'":"",
            );
            if($digs<$maxDigs){
                $sub = array();
                $this->_digData($sub, $digs+1, $maxDigs, $cateRow[$this->cate['query']['pkField']]);
                //選中左選單項目的次選單，或是擁有被選中的次選單項目，才將次選單加進顯示列表
                if($sub && ($tmp['tag_cur']!='' || $sub['active'] )){
                    $tmp['sub'] = $sub;
                    $tmp['tag_cur'] = "class='".$this->currentClass."'";
                }
            }
            if($tmp['tag_cur']!=''){
                $container['active'] = true;
            }
            $container[] = $tmp;
        }
    }
    
    protected function _initQueryCondition($cateId){
        $condition = $this->cate['query']['parentField']."='{$cateId}'";
        if($this->cate['query']['condition']){
            $condition .= " and " . $this->cate['query']['condition'];   
        }
        return $condition;
    }
    
    protected function _initQueryOrder(){
        if($this->cate['query']['order']){
            $order = " order by " . $this->cate['query']['order'];   
        }
        return $order;        
    }
}
