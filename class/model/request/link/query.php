<?php
class Model_Request_Link_Query extends Model_Request_Link {
    function get_link($data) {
        $query = http_build_query($data['params']);
        $link = $data['scriptName'];
        if($query){
            $link .= "?".$query;
        }
        return $link;
    }
}
