<?php
class ONLINELOGGER {
    //put your code here
    static protected $online_log = "conf/onlinelogger.dat"; //儲存人數的檔,
    static protected $timeout = 30;//30秒內沒動作者,認為掉線
    static function count(){
        if(!file_exists(self::$online_log)){
            $fp = fopen(self::$online_log, "w");
            fclose($fp);
        }
        $entries = file(self::$online_log);
        $temp = array();
        for ($i=0;$i<count($entries);$i++) {
            $entry = explode(",",trim($entries[$i]));
            if (($entry[0] != getenv('REMOTE_ADDR')) && (trim($entry[1]) > time())) {
                array_push($temp,implode(",",$entry)); //取出其他流覽者的資訊,並去掉逾時者,儲存進$temp
            }
        }

        array_push($temp,getenv('REMOTE_ADDR').",".(time() + (self::$timeout))." "); //更新流覽者的時間
        $users_online = count($temp); //計算線上人數

        $entries = implode("\n",$temp);
        //寫入檔案
        $fp = fopen(self::$online_log,"w");
        flock($fp,LOCK_EX); //flock() 不能在NFS以及其他的一些網路檔系統中標準工作
        fputs($fp,$entries);
        flock($fp,LOCK_UN);
        fclose($fp);        
        return $users_online;
    }
    
}
