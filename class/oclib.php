<?php
class OCLIB{
    //使用者設備檢測
    static function user_devices(){
		//Detect special conditions devices
		$user_devices=$_SERVER['HTTP_USER_AGENT'];
		$user_devices = (stripos($_SERVER['HTTP_USER_AGENT'],"iPod"))?"iPod":"";
		$user_devices = (stripos($_SERVER['HTTP_USER_AGENT'],"iPhone"))?"iPhone":"";
		$user_devices = (stripos($_SERVER['HTTP_USER_AGENT'],"iPad"))?"iPad":"";
		if(stripos($_SERVER['HTTP_USER_AGENT'],"Android") && stripos($_SERVER['HTTP_USER_AGENT'],"mobile")){
				$user_devices = "Android";
		}else if(stripos($_SERVER['HTTP_USER_AGENT'],"Android")){
				$user_devices = "AndroidTablet";
		}
		$user_devices = (stripos($_SERVER['HTTP_USER_AGENT'],"webOS"))?"webOS":"";
		$user_devices = (stripos($_SERVER['HTTP_USER_AGENT'],"BlackBerry"))?"BlackBerry":"";
		$user_devices = (stripos($_SERVER['HTTP_USER_AGENT'],"RIM Tablet"))?"RIM Tablet":"";		
		return $user_devices;
    }

    //最大字元截除ex.我愛...
    static function sub_string($str,$words){
		if (mb_strlen(strip_tags($str), "UTF8")>$words){
			$sub_string=mb_substr(strip_tags($str),0,$words,"UTF-8")."...";
		}else{
			$sub_string=$str;
		}
		return $sub_string;
    }
    //行動電話檢查
    static function cellphone_transform($num){
    	$phone_num="";
    	$phone_num_count=0;
        $num_ck=substr ($num, 0, 3); 
        if($num_ck=="886"){
                $num="0".substr($num,3);
        }
    	$num_len=mb_strlen($num, "UTF8");
        for($i=0;$i<$num_len;$i++){
                $str_temp= mb_substr($num, $i, 1,"UTF-8");
                if(preg_match("/^[0-9]+$/i", $str_temp)){
                        $phone_num_count++;
                        $phone_num.=$str_temp;
                        if($phone_num_count==1){
                                if($str_temp!="0"){
                                        $phone_num="error";
                                        break;
                                }
                        }
                        if($phone_num_count==2){
                                if($str_temp!="9"){
                                        $phone_num="error";
                                        break;
                                }
                        }
                }
        }
        if($phone_num_count!=10){
                $phone_num="error";
        }
        return $phone_num;
    }	
	
    //字元遮罩
    //str_mask(字串,起始字元位置,遮罩長度,遮罩前欲消去的字元)
    //遮罩長度為空時會遮罩到最後一個字元
    static function str_mask($str,$start_char,$length=0,$replace_array=array()){
        foreach($replace_array as $key =>$value){
                $str=str_replace($value,"",$str);
        }
    	$str_len=mb_strlen($str, "UTF8");
        if($length){
                $end_char=$start_char+$length;
        }else{
                $end_char=$str_len;
        }
        for($i=0;$i<$str_len;$i++){
                if($i>=$start_char&&$i<$end_char){
                        $str_temp="*";
                }else{
                        $str_temp= mb_substr($str, $i, 1,"UTF-8");
                }
                $str_mask.=$str_temp;
        }
        return $str_mask;
    }
}
?>