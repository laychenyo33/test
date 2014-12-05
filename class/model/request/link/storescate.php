<?php
class Model_Request_Link_Storescate extends Model_Request_Link{
    //put your code here
    function get_link($row) {
        if(App::configs()->ws_module->ws_seo){
            if(trim($row["sdc_seo_filename"])==""){
                $link = App::configs()->base_root . "stores/nlist-".$row["sdc_id"].".htm";
            }else{
                $link = App::configs()->base_root . "stores/".$row["sdc_seo_filename"].".htm";
            }
        }else{
            $link = App::configs()->base_root . "stores.php?func=sd_list&sdc_id=".$row["sdc_id"];
        }
        return $link;
    }
}
