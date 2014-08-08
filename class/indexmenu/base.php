<?php
class Indexmenu_Base {
    static $tpl;
    protected $blockname;    
    protected $menuItems;
    
    public function listmenu(){}
    
    protected function _list(){
        if(!empty($this->menuItems)){
            $tpl = self::$tpl;
            foreach($this->menuItems as $item){
                $tpl->newBlock(strtoupper($this->blockname)."_MENU_ITEM");
                $tpl->assign(array(
                    "TAG_MENU_LINK"  => $item['link'],
                    "TAG_MENU_LABEL" => $item['label'],
                ));
            }
        }
    }
}
