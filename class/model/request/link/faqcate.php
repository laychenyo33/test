<?php
class Model_Request_Link_Faqcate extends Model_Request_Link{
    //put your code here
    function get_link($row) {
        if(App::configs()->ws_module->ws_seo){      
            if(trim($row["fc_seo_filename"])==""){
                $link = App::configs()->base_root."faq/flist-".$row["fc_id"].".htm";
            }else{
                $link = App::configs()->base_root."faq/".$row["fc_seo_filename"].".htm";
            }            
        }else{
            $link = App::configs()->base_root."faq.php?func=f_list&fc_id=".$row["fc_id"];
        }  
        return $link;
    }
}
