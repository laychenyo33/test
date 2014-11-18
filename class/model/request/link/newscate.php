<?php
class Model_Request_Link_Newscate extends Model_Request_Link{
    //put your code here
    function get_link($row) {
        if(App::configs()->ws_module->ws_seo){
            if(trim($row["nc_seo_filename"])==""){
                $link = App::configs()->base_root."news/nlist-".$row["nc_id"].".htm";
            }else{
                $link = App::configs()->base_root."news/".$row["nc_seo_filename"].".htm";
            }         
        }else{
            $link = App::configs()->base_root."news.php?func=n_list&nc_id=".$row["nc_id"];
        }  
        return $link;
    }
}
