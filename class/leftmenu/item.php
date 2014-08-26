<?php
class Leftmenu_Item extends Leftmenu_Abstract {
    //分類資訊
    protected $item = array(
        'linkType'    => '',
        'labelField'    => '',
        'query' => array(
            'table'     => '',
            'select' => '',
            'condition' => '',
            'order' => '',
        ),
    );
    
    function getItemTable(){
        return App::getHelper('db')->prefix($this->item['query']['table']);
    }
    
    
    protected function _getItems() {
        $menuItems = array();       
        $this->_digData($menuItems);
        return $menuItems;
    }
    
    protected function _digData(&$container){
        $sql = "select ".$this->item['query']['select']." from ".$this->getItemTable()." where ".$this->_initQueryCondition() . $this->_initQueryOrder();
        $res = App::getHelper('db')->query($sql,true);
        while($itemRow = App::getHelper('db')->fetch_array($res,1)){
            $itemLink = App::getHelper('request')->get_link($this->item['linkType'],$itemRow);
            $tmp = array(
                'name' => $itemRow[$this->item['labelField']],
                'link' => $itemLink,
                'tag_cur' => (strcasecmp($_SERVER['REQUEST_URI'], $itemLink)==0)?"class='current'":"",
            );
            $container[] = $tmp;
        }
    }
    
    protected function _initQueryCondition(){
        if($this->item['query']['condition']){
            $condition .= " and " . $this->item['query']['condition'];   
        }
        return $condition;
    }
    
    protected function _initQueryOrder(){
        if($this->item['query']['order']){
            $order = " order by " . $this->item['query']['order'];   
        }
        return $order;        
    }
}
