<?php
class Leftmenu_Stores extends Leftmenu_Abstract {
    protected function _getItems(){
        global $TPLMSG;
        $db = App::getHelper('db');
        $sd_type_arr = array(1=>$TPLMSG['STORE_CATE_1'],2=>$TPLMSG['STORE_CATE_2']);
        foreach($sd_type_arr  as $sd_type => $info){
            if($sd_type==1){
                $cate_link = "#";
            }elseif($sd_type==2){
                if($this->ws_seo){
                    $cate_link = App::configs()->base_root . "stores/webstores.htm";
                }else{
                    $cate_link = App::configs()->base_root . "stores.php?func=sd_list&sd_type=2";
                }
            }
            $menu_item = array(
                'name'    => $info,
                'link'    => $cate_link,
                'tag_cur' => ($_GET['f']=='webstores' || $_GET['sd_type']==2)?"class='current'":"",
            );       
            if($sd_type==1){
                $sql = "select * from ". $db->prefix("stores_cate")." where sdc_status='1' order by sdc_sort ".App::configs()->sort_pos;
                $res = $db->query($sql,true);
                if($db->numRows($res)){
                    while($row = $db->fetch_array($res,1)){
                        if($_GET['f']==$row['sdc_seo_filename'] || $_GET['sdc_id']==$row['sdc_id']){
                            $current_class="class='current'";    
                            $this->currentRow = $row;
                        }else{
                            $current_class="";
                        }
                        $menu_item['sub'][] = array(
                            'name'    => $row['sdc_subject'],
                            'link'    => App::getHelper('request')->get_link("storescate",$row),
                            'tag_cur' => $current_class,
                        );   
                    }
                    $left_menu[] = $menu_item;
                }
            }
        }      
        return $left_menu;
    }
}
