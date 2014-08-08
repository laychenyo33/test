<?php
class Indexmenu_Products extends Indexmenu_Base {
    protected $blockname = "PRODUCTS";    
    
    public function listmenu(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;        
        $sql="select * from ".$cms_cfg['tb_prefix']."_products_cate where pc_parent='0' and pc_status='1' and pc_up_sort='0' and pc_id not in(2,3) order by pc_sort ";
        $sql2="select * from ".$cms_cfg['tb_prefix']."_products_cate where pc_id='3' ";
        $sql2.=" union ";
        $sql2.="select * from (select * from ".$cms_cfg['tb_prefix']."_products_cate where pc_parent='0' and pc_status='1' and pc_up_sort='1' order by pc_name) as b";
        $sql2.=" union ";
        $sql2.="select * from ".$cms_cfg['tb_prefix']."_products_cate where pc_id='2' ";
        $sql2.= "union ";
        $sql2.=$sql;
        $selectrs = $db->query($sql2);
        while($row = $db->fetch_array($selectrs,1)){
            if(trim($row["pc_seo_filename"]) !=""){
                $dirname1=$row["pc_seo_filename"];
                $pc_link=$cms_cfg["base_root"].$row["pc_seo_filename"].".htm";
            }else{
                $dirname1=$row["pc_id"];
                $pc_link=$cms_cfg["base_root"]."category-".$row["pc_id"].".htm";
            }            
            $menuItem = array(
                'label' => $row['pc_name'],
                'link' => $pc_link,
            );
            $this->menuItems[] = $menuItem;
        }        
        $this->_list();
    }
}
