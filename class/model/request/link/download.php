<?php
class Model_Request_Link_Download extends Model_Request_Link {
    function get_link($row) {
        if(App::configs()->ws_module->ws_seo==1 ){
            if(trim($row["dc_seo_filename"])==""){
                $cate_link=App::configs()->base_root."download/dlist-".$row["dc_id"].".htm";
            }else{
                $cate_link=App::configs()->base_root."download/".$row["dc_seo_filename"].".htm";
            }
        }else{
            $cate_link=App::configs()->base_root."download.php?func=d_list&dc_id=".$row["dc_id"];
        }        
        return $cate_link;        
    }
}
