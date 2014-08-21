<?php
class Model_Request_Link_Application extends Model_Request_Link {
    function get_link($row) {
        if(App::configs()->ws_module->ws_seo){
            if($row['pc_id']){
                if(trim($row["pc_seo_filename"]) !=""){
                    $link=App::configs()->base_root.$row["pc_seo_filename"].".htm";
                }else{
                    $link=App::configs()->base_root."category-".$row["pc_id"].".htm";
                }
            }else{
                if(trim($row["pa_seo_filename"]) !=""){
                    $link=App::configs()->base_root.'application/'.$row["pa_seo_filename"].".htm";
                }else{
                    $link=App::configs()->base_root."application-".$row["pa_id"].".htm";
                }
            }
        }else{
            if($row['pc_id']){
                $link=App::configs()->base_root."products.php?func=p_list&pc_parent=".$row["pc_id"];
            }else{
                $link=App::configs()->base_root."application.php?pa_id=".$row["pa_id"];
            }
        }
        return $link;          
    }
}
