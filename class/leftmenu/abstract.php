<?php
abstract class Leftmenu_Abstract {
    public $tpl;
    public $currentRow;
    protected $menuItems;
    protected $currentClass = "current";
    
    function __construct(TemplatePower $tpl) {
        $this->tpl = $tpl;
        $this->menuItems = $this->_getItems();
        $this->_init();
    }
    
    protected function _getItems(){

    }
    
    protected function _init(){

    }
    
    //增加左側主選單
    protected function _make_menu(array $menu_items,$blockname="CATE",$sub=false,$deep=""){
        $deep = $deep?"S".$deep:"SUB";
        $sub_cate_name = $deep."CATE";        
        foreach($menu_items as $item){
            if(is_array($item)){
                $this->tpl->newBlock( "LEFT_".$blockname."_LIST" );
                $this->tpl->assign( array( 
                    "VALUE_".$blockname."_NAME" => $item['name'],
                    "VALUE_".$blockname."_LINK" => $item['link'],
                    "WRAPPER_CLASS"             => $item['class'],
                    "TAG_CURRENT_CLASS"         => $item['tag_cur'],
                ));        
                if($item['sub']){
                    $this->tpl->newBlock("LEFT_".$sub_cate_name.'ZONE');
                    $this->_make_menu($item['sub'],$sub_cate_name,true,$deep);
                }
            }
        }
    }
    
    public function make(){
        $this->_make_menu($this->menuItems);
    }
}
