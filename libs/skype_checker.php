<?
function get_skype_status($username, $image = 0){
    global $tpl,$cms_cfg;
    //creating url
    //if you need small icon
    /*
    //getting skype status icon
    $ico = get_skype_status("ar2rsawseen", true, true);
    echo "<p>Skype icon:</p>";
    echo "<p><img src='".$ico."'/></p>";

    //getting skype status image
    $image = get_skype_status("ar2rsawseen", true);
    echo "<p>Skype image:</p>";
    echo "<p><img src='".$image."'/></p>";

    //getting skype status text
    $status = get_skype_status("ar2rsawseen");
    echo "<p>Skype status:</p>";
    echo "<p>".$status."</p>";
    */
    if($image==1){	//icon
    /***************************************
        Possible types of images:

        * balloon            - Balloon style 
        * bigclassic        - Big Classic Style 
        * smallclassic        - Small Classic Style 
        * smallicon        - Small Icon (transparent background) 
        * mediumicon        - Medium Icon 
        * dropdown-white    - Dropdown White Background 
        * dropdown-trans    - Dropdown Transparent Background
        ****************************************/
        return "http://mystatus.skype.com/smallicon/".$username;
    }else if($image==2){     //if you need image
        return "http://mystatus.skype.com/".$username;
    }else if($image==3){     //自訂
        $url = "http://mystatus.skype.com/".$username.".xml";
        //getting contents
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        curl_close($curl);

        $pattern = '/xml:lang="en">(.*)</';
        preg_match($pattern,$data, $match); 
        if($match[1]=="Online"){
            return $cms_cfg["default_theme"]."skype.png";
        }else{
            return $cms_cfg["default_theme"]."skype_off.png";
        }
    }else{ //or just text
        /***************************************
        Possible status  values:
         NUM        TEXT                DESCRIPTION
        * 0     UNKNOWN             Not opted in or no data available. 
        * 1     OFFLINE                 The user is Offline 
        * 2     ONLINE                  The user is Online 
        * 3     AWAY                    The user is Away 
        * 4     NOT AVAILABLE       The user is Not Available 
        * 5     DO NOT DISTURB  The user is Do Not Disturb (DND) 
        * 6     INVISIBLE               The user is Invisible or appears Offline 
        * 7     SKYPE ME                The user is in Skype Me mode
        ****************************************/
        $url = "http://mystatus.skype.com/".$username.".xml";
        //getting contents
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        curl_close($curl);

        $pattern = '/xml:lang="en">(.*)</';
        preg_match($pattern,$data, $match); 
        return $match[1];   
    }
}
?>