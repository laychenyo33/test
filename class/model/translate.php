<?php
class Model_Translate{
        static $_map = array();
	static function translate($tl,$key){
            //APP_ROOT_PATH設定於conf/libs-sysconfig.php
            if(!isset(self::$_map[$tl])){
                include APP_ROOT_PATH . "lang" . DIRECTORY_SEPARATOR . $tl ."-utf8.php";
                self::$_map[$tl] = $TPLMSG;
            }
            return self::$_map[$tl][$key];
	}
}
?>