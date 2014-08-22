<?php
class Model_Request_Link_Products extends Model_Request_Link{
    //put your code here
    function get_link($row) {
        if(App::configs()->ws_module->ws_seo){
            $dirname=(trim($row["pc_seo_filename"]))?$row["pc_seo_filename"]:"products";
            if(trim($row["p_seo_filename"]) !=""){
                $link=App::configs()->base_root.$dirname."/".$row["p_seo_filename"].".html";
            }else{
                $link=App::configs()->base_root.$dirname."/"."products-".$row["p_id"]."-".$row["pc_id"].".html";
            }
        }else{
            $link=App::configs()->base_root."products.php?func=p_detail&p_id=".$row["p_id"]."&pc_parent=".$row["pc_id"];
        }  
        return $link;
    }
}
