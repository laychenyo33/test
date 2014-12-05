<?php
class Model_Request_Link_Stores extends Model_Request_Link{
    //put your code here
    function get_link($row) {
        if(App::configs()->ws_module->ws_seo){
            if(trim($row["sd_seo_filename"])==""){
                $link = App::configs()->base_root . "stores/ndetail-".$row["sdc_id"]."-".$row["sd_id"].".html";
            }else{
                $link = App::configs()->base_root . "stores/".$row["sd_seo_filename"].".html";
            }
        }else{
            $link = App::configs()->base_root . "stores.php?func=sd_show&sdc_id=".$row["sdc_id"]."&sd_id=".$row["sd_id"];
        }
        return $link;
    }
}
