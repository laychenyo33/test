<?php
class Model_Request_Link_Aboutus extends Model_Request_Link {
    function get_link($data) {
        if(App::configs()->ws_module->ws_seo == 1 ){
            if($data["au_seo_filename"]){
                $link = App::configs()->base_root . $data['au_cate']."/" . $data["au_seo_filename"] . ".html";
            }else{
                $link = App::configs()->base_root . $data['au_cate']."-" . $data["au_id"] . ".html";
            }
        }else{
            if(App::configs()->ws_module->ws_aboutus_au_cate){
                $link = App::configs()->base_root . "aboutus.php?au_cate=".$data['au_cate'] . "&au_id=".$data["au_id"];
            }else{
                $link = App::configs()->base_root . "aboutus.php?au_id=" . $data["au_id"];
            }
        }
        return $link;
    }
}
