<?php
class Model_Request_Link_News extends Model_Request_Link{
    //put your code here
    function get_link($row) {
        if($row["n_content_type"]==1) {
            if(App::configs()->ws_module->ws_seo){
                if(trim($row["n_seo_filename"])==""){
                    $link = App::configs()->base_root."news/ndetail-".$row["nc_id"]."-".$row["n_id"].".html";
                }else{
                    $link = App::configs()->base_root."news/".$row["n_seo_filename"].".html";
                }            
            }else{
                $link = App::configs()->base_root."news.php?func=n_show&nc_id=".$row["nc_id"]."&n_id=".$row["n_id"];
            }  
        }else{
            $link = $row["n_url"];
        }
        return $link;
    }
}
