<?php
class Indexmenu_Techdata extends Indexmenu_Base {
    protected $blockname = "TECHDATA";    

    public function listmenu(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;        
        $sql="select * from ".$cms_cfg['tb_prefix']."_news where (n_status='1' or (n_status='2' and n_startdate <= '".date("Y-m-d")."' and n_enddate >= '".date("Y-m-d")."')) AND nc_id in (select nc_id from ".$cms_cfg['tb_prefix']."_news_cate where nc_seo_filename='technical-data') order by n_sort ".$cms_cfg['sort_pos'].",n_modifydate desc";
        $selectrs = $db->query($sql);
        while($row = $db->fetch_array($selectrs,1)){
            if(trim($row["n_seo_filename"])==""){
                $link = $cms_cfg["base_root"]."news/ndetail-".$row["nc_id"]."-".$row["n_id"].".html";
            }else{
                $link = $cms_cfg["base_root"]."news/".$row["n_seo_filename"].".html";
            }         
            $menuItem = array(
                'label' => $row['n_subject'],
                'link' => $link,
            );
            $this->menuItems[] = $menuItem;
        }
        $this->_list();
    }
}
