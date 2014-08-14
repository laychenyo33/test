<?php
class Indexmenu_News extends Indexmenu_Base {
    protected $blockname = "NEWS";    
    protected $iso_id = array(2);
    public function listmenu(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;        
        $sql2="select * from ".$cms_cfg['tb_prefix']."_news_cate where nc_status='1' and nc_id not in(".implode(',',$this->iso_id).") order by nc_sort ".$cms_cfg['sort_pos'];
        $selectrs = $db->query($sql2);
        while($row = $db->fetch_array($selectrs,1)){
            if(trim($row["nc_seo_filename"])==""){
                $link = $cms_cfg["base_root"]."news/nlist-".$row["nc_id"].".htm";
            }else{
                $link = $cms_cfg["base_root"]."news/".$row["nc_seo_filename"].".htm";
            }            
            $menuItem = array(
                'label' => $row['nc_subject'],
                'link' => $link,
            );
            $this->menuItems[] = $menuItem;
        }
        $this->_list();
    }
}
