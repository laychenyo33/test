<?php
	include_once "class/ghelper/itranslator.php";
	include_once "class/ghelper/googletranslator.php";
        /*載入欲改翻的檔案*/
	include_once "lang/tmp.php";
        
        function v_split($v='',$switch=false){
                static $v_array;
                static $v_row;

                // 拆解內容，分離 html tag 為 $v_array
                if(!$switch){
                        preg_match_all('/<[^>]*>/', $v ,$match);
                        $v_array = $match[0];

                        return preg_replace('/<[^>]*>/', '||', $v);
                }

                // 重新組合翻譯過的內容
                if($switch && is_array($v_array)){
                        foreach($v_array as $key => $tag){
                                $v = preg_replace('/[|]{2}/', $tag, $v,1);
                        }

                        $v_array = ''; // 清空 v_array
                        return $v;
                }
        }
        set_time_limit(0);
	$txt = implode("|",$TPLMSG);
        $for_trans = count($TPLMSG);
	
	$gt = new Ghelper_GoogleTranslator();
	/*翻譯寫入的檔案*/
        $fp = fopen('upload_files/tai.php', 'w');
        fwrite($fp, '<?php'."\r\n");
        header("content-type:text/html;charset=utf8");
        echo <<<CON
        <script type="text/javascript" src="js/jquery/jquery-1.8.3.min.js"></script>
        <div style="width:500px;margin:150px auto 0">
            <div id="">總筆數:{$for_trans}，已翻譯筆數:<span id="translated">0</span></div>
            <div style='width:95%;height:50px;margin:10px auto;padding:5px;border:1px solid grey;'>
                <div id='bar' style='width:0;height:100%;background-color:#0f0;'></div>
            </div>
            <div id="finish" style="display:none;">完成</div>
        </div>
CON;
        flush();
        //ob_flush();        
        $i=0;
        foreach($TPLMSG as $name => $value){
            $v1 = v_split($value);
            /*翻譯參數設定*/
            $trl_txt = $gt->Translate($v1,'th','zh-tw');
            $v2 = v_split($trl_txt, true);
            $line = '$TPLMSG[\''.$name.'\'] = "'.$v2.'"'.";\r\n";
            fwrite($fp, $line);
            $i++;
            $p = round(($i / $for_trans)*100);
            echo <<<JSJ
     <script type="text/javascript">
            $("#translated").text({$i});
            $("#bar").css("width","{$p}%");
            if({$p}==100)$("#finish").show();
     </script>
JSJ;
            flush();
            $s = rand(1,3);
            sleep($s);
        }
        fclose($fp);
?>