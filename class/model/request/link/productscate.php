<?php
class Model_Request_Link_Productscate extends Model_Request_Link{
    //put your code here
    function get_link($row) {      
        if(App::configs()->ws_module->ws_seo){
            if(trim($row["pc_seo_filename"]) !=""){
                //$dirname=$row["pc_seo_filename"];
                $link=App::configs()->base_root.$row["pc_seo_filename"].".htm";
            }else{
                //$dirname=$row["pc_id"];
                $link=App::configs()->base_root."category-".$row["pc_id"].".htm";
            }
        }else{
            $link=App::configs()->base_root."products.php?func=p_list&pc_parent=".$row["pc_id"];
        }  
        return $link;
    }
}
