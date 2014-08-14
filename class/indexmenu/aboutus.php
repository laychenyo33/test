<?php
class Indexmenu_Aboutus extends Indexmenu_Base {
    protected $blockname = "ABOUTUS";    
    
    public function listmenu(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;        
        $sql="select * from ".$cms_cfg['tb_prefix']."_aboutus  where au_status='1' and mobilehide='0' order by au_sort ".$cms_cfg['sort_pos'].",au_modifydate desc";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $menuItem = array(
                'label' => $row['au_subject'],
                'link' => ($i==1) ? $cms_cfg['base_root']."aboutus.htm" : $cms_cfg["base_root"]."aboutus/".$row["au_seo_filename"].".html",
            );
            $this->menuItems[] = $menuItem;
        }
        $this->_list();
    }
}
