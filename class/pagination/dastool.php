<?php
class Pagination_Dastool extends Pagination_Abstract {
    protected $page_nums_divisor = 10;
    protected $page_zones;
    protected $current_zone;
    //輸出分頁
    function getPagination() {
        $this->page_zones = ceil($this->pages_nums / $this->page_nums_divisor);
        $this->current_zone = ceil($this->current_page / $this->page_nums_divisor);
        $tpl = $this->getTemplate();
        $tpl->assignGlobal(array(
            "TOTAL_RECORDS" => $this->rows_nums,
            "TOTAL_PAGES"   => $this->pages_nums,
            "PAGE_RECORDS"  => $this->page_records,
            "FIRST_PAGE"    => $this->getFirstPageLink(),
            "LAST_PAGE"     => $this->getLastPageLink(),
            "PREV_ZONE"     => $this->getZoneLink('prev'),
            "NEXT_ZONE"     => $this->getZoneLink('next'),
        ));
        //輸出分頁
        if($this->pages_nums>1){
            $start = ($this->current_zone-1)*$this->page_nums_divisor+1;
            for($k=0;$k<$this->page_nums_divisor;$k++){
                $tpl->newBlock("PAGE_LIST");
                $page_id = $start+$k;
                if($page_id <= $this->pages_nums){
                    if($page_id==$this->current_page){
                        $tpl->assign("TAG_PAGE",$this->getCurrentWapper($page_id));
                    }else{
                        $tpl->assign("TAG_PAGE",$this->getPageLink($page_id));
                    }
                }else{
                    break;
                }
            }
        }
        return $tpl->getOutputContent();
    }
    
    protected function getZoneLink($direction){
        global $TPLMSG;
        switch($direction){
            case "prev":
                if($this->current_zone>1 && ($this->current_zone-1)>0){
                    $zone_page_id = ($this->current_zone-1)*$this->page_nums_divisor;
                    return $this->getPageLink($zone_page_id, array('label'=>sprintf($TPLMSG['PAGINATION_PREV_FEW_PAGE'],$this->page_nums_divisor)));
                }
                break;
            case "next":
                if($this->current_zone<$this->page_zones){
                    $zone_page_id = $this->current_zone*$this->page_nums_divisor+1;
                    return $this->getPageLink($zone_page_id, array('label'=>sprintf($TPLMSG['PAGINATION_NEXT_FEW_PAGE'],$this->page_nums_divisor)));
                }
                break;
        }
    }
    
    protected function getFirstPageLink(){
        global $TPLMSG;
        if($this->current_zone>1){
            return $this->getPageLink(1, array('class'=> $this->first_page_class,'label'=>$TPLMSG['PAGINATION_FIRST_PAGE']));
        }else{
            return;
        }
    }
    
    protected function getLastPageLink(){
        global $TPLMSG;
        if($this->current_zone<$this->page_zones){
            return $this->getPageLink($this->pages_nums, array('class'=> $this->last_page_class ,'label'=>$TPLMSG['PAGINATION_LAST_PAGE']));
        }else{
            return;
        }
    }    
}
