<?php
class Leftmenu_Catewithitems extends Leftmenu_Abstract {
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
    //項目資訊
    protected $items = array(
        'linkType'  => '',
        'labelField' => '',
        'query' => array(
            'table'   => '',
            'alias'  => '',
            'cateField' => '',
            'select' => '',
            'condition' => '',
            'order' => '',
        ),
    );
    //是否一併取得item項目
    protected $menuWithItems;
    //遞迴的層次
    protected $maxDigs;  
    
    function __construct(TemplatePower $tpl,$maxDigs=2,$menuWithItems=false) {
        if($this->cate['query']['parentField']==''){
            throw new Exception("please assign parent field of cate table");
        }
        if($this->items['query']['cateField']==''){
            throw new Exception("please assign cate field of items table");
        }
        $this->maxDigs = $maxDigs;
        $this->menuWithItems = $menuWithItems;
        parent::__construct($tpl);
    }
    
    function getCateTable($alias=false){
        if($alias && $this->cate['query']['alias']){
            return $this->cate['query']['alias'];
        }
        return App::getHelper('db')->prefix($this->cate['query']['table']);
    }
    
    function getItemTable($alias=false){
        if($alias && $this->items['query']['alias']){
            return $this->items['query']['alias'];
        }        
        return App::getHelper('db')->prefix($this->items['query']['table']);
    }
    
    function getCateTableAlias(){
        return $this->cate['query']['alias']?" as ".$this->cate['query']['alias']." ":"";
    }
    
    function getItemTableAlias(){
        return $this->items['query']['alias']?" as ".$this->items['query']['alias']." ":"";
    }
    
    protected function _getItems() {
        $menuItems = array();       
        $this->_digData($menuItems, 1, $this->maxDigs);
        return $menuItems;
    }
    
    protected function _digData(&$container,$digs,$maxDigs,$cateId=0){
        $sql = "select ".$this->cate['query']['select']." from ".$this->getCateTable()." where ".$this->_initQueryCondition('cate', $cateId) . $this->_initQueryOrder('cate');
        $res = App::getHelper('db')->query($sql,true);
        while($cateRow = App::getHelper('db')->fetch_array($res,1)){
            $cateLink = App::getHelper('request')->get_link($this->cate['linkType'],$cateRow);
            $tmp = array(
                'name' => $cateRow[$this->cate['labelField']],
                'link' => $cateLink,
                'tag_cur' => (strcasecmp($_SERVER['REQUEST_URI'], $cateLink)==0)?"class='current'":"",
            );
            if($digs<$maxDigs){
                $sub = array();
                $this->_digData($sub, $digs+1, $maxDigs, $cateRow[$this->cate['query']['pkField']]);
                //選中左選單項目的次選單，或是擁有被選中的次選單項目，才將次選單加進顯示列表
                if($sub && ($tmp['tag_cur']!='' || $sub['active'] )){
                    $tmp['sub'] = $sub;
                    $tmp['tag_cur'] = "class='current'";
                }
            }
            if($tmp['tag_cur']!=''){
                $container['active'] = true;
            }
            $container[] = $tmp;
        }
        //處理item項目
        if($this->menuWithItems){
            $sql = "select ".$this->items['query']['select']." from ".$this->getItemTable()." ".$this->getItemTableAlias()." left join ".$this->getCateTable()." ".$this->getCateTableAlias().
                    "on ".$this->getItemTable(true).".".$this->items['query']['cateField']."=".$this->getCateTable(true).".".$this->cate['query']['pkField'].
                    " where ".$this->_initQueryCondition('items', $cateId) . $this->_initQueryOrder('items');
            $res2 = App::getHelper('db')->query($sql,true);
            while($itemRow = App::getHelper('db')->fetch_array($res2,1)){
                $p_link = App::getHelper('request')->get_link($this->items['linkType'],$itemRow);
                $tmp = array(
                    'name' => $itemRow[$this->items['labelField']],
                    'link' => $p_link,
                    'tag_cur' => (strcasecmp($_SERVER['REQUEST_URI'], $p_link)==0)?"class='current'":"",
                );
                if($tmp['tag_cur']!=''){
                    $container['active'] = true;
                }                
                $container[] = $tmp;
            }
        }
    }
    
    protected function _initQueryCondition($type,$cateId){
        switch($type){
            case "cate":
                $condition = $this->cate['query']['parentField']."='{$cateId}'";
                if($this->cate['query']['condition']){
                    $condition .= " and " . $this->cate['query']['condition'];   
                }
                break;
            case "items":
                $condition = $this->getItemTable(true).".".$this->items['query']['cateField']."='{$cateId}'";
                if($this->items['query']['condition']){
                    $condition .= " and " . $this->items['query']['condition'];   
                }
                break;
        }
        return $condition;
    }
    
    protected function _initQueryOrder($type){
        switch($type){
            case "cate":
                if($this->cate['query']['order']){
                    $order = " order by " . $this->cate['query']['order'];   
                }
                break;
            case "items":
                if($this->items['query']['condition']){
                    $order = " order by " . $this->items['query']['order'];   
                }
                break;
        }
        return $order;        
    }
}
