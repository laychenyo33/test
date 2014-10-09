<?php
class curlClass {
    protected static $data_fields;
    static function send($url,$postfields=""){
        self::$data_fields = $postfields;
        $urlinfo = parse_url($url);
        //$headers = array("Host: ".$urlinfo['host']);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  //SSL
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);        
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  //連線逾期
        $curl_respose = self::_send($ch);
        curl_close($ch);
        return $curl_respose;
    }
    static function _send($ch){
        if(self::$data_fields){
            curl_setopt($ch, CURLOPT_POST, 1);
            if(is_array(self::$data_fields)){
                $post_fields = array();
                foreach(self::$data_fields as $k=>$v){
                    if(isset($v)){
                        $post_fields[] = sprintf('%s=%s',$k,  urlencode($v));
                    }
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, implode("&",$post_fields));
            }elseif(is_string(self::$data_fields)){
                curl_setopt($ch, CURLOPT_POSTFIELDS, self::$data_fields);
            }
        }
        $curl_respose = curl_exec($ch);
        if(curl_errno($ch)){
            $curl_respose .= "<div class='error'>".curl_error($ch)."</div>";
        }
        return $curl_respose;
    }   
}
