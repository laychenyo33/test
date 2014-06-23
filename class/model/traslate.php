<?php
class Model_Translate{
	static function translate($tl,$key){
            //APP_ROOT_PATH設定於conf/libs-sysconfig.php
            include APP_ROOT_PATH . "lang" . DIRECTORY_SEPARATOR . $hl ."-utf8.php";
            return $TPLMSG[$key];
	}
}
?>