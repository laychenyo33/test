<?php
include_once "class/ghelper/itranslator.php";
include_once "class/ghelper/googletranslator.php";

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

?>
<!DOCTYPE html>
<html>
    <head>
        <title>翻譯語系檔</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            .form-table{
                border-collapse: collapse;
                border:1px solid grey;
            }
            .form-table th,.form-table td{padding:3px;font-size:14px;}
            .form-table th{background-color:#0a0;color:white}
            #desc-box{
                list-style:disc;
                list-style-position: outside;
                margin:0 0 0 25px;
                padding:0;
                width:245px;
            }
            #desc-box li{}
        </style>
        <script type="text/javascript" src="js/jquery/jquery-1.8.3.min.js"></script>
    </head>
    <body>
       <?php
       switch($_GET['action']){
           case "translate":
                /*載入欲改翻的檔案*/
                $origin_lang_files = "lang/".$_POST['sourcefile'];
                $out_lang_files = "upload_files/".$_POST['targetfile'];
                include_once $origin_lang_files;
                set_time_limit(0);
                $txt = implode("|",$TPLMSG);
                $for_trans = count($TPLMSG);

                $gt = new Ghelper_GoogleTranslator();
                /*翻譯寫入的檔案*/
                $fpIn = fopen($origin_lang_files, 'r');
                $fpOut = fopen($out_lang_files, 'w');
                echo <<<CON
                <div style="width:500px;margin:150px auto 0">
                    <div id="">總筆數:{$for_trans}，已翻譯筆數:<span id="translated">0</span></div>
                    <div style='width:95%;height:50px;margin:10px auto;padding:5px;border:1px solid grey;'>
                        <div id='bar' style='width:0;height:100%;background-color:#0f0;'></div>
                    </div>
                    <div id="finish" style="display:none;">翻譯完成</div>
                    <div id="writing" style="display:none;">開始寫入</div>
                    <div id="complete" style="display:none;">寫入完成</div>
                </div>
CON;
                flush();
                //ob_flush();        
                $i=0;
                foreach($TPLMSG as $name => $value){
                    $v1 = v_split($value);
                    /*翻譯參數設定*/
                    $trl_txt = $gt->Translate($v1, $_POST['tlc'],$_POST['slc']);
                    $v2 = v_split($trl_txt, true);
                    $TPLMSG[$name] = $v2;
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
                echo <<<JSJ
             <script type="text/javascript">
                $("#writing").show();
             </script>
JSJ;
                flush();
                while(($buffer = fgets($fpIn, 4069))!==false){
                    if(preg_match('/^\$TPLMSG\[["\'](.+)["\']\]/i', $buffer, $matches)){
                        $line = '$TPLMSG[\''.$matches[1].'\'] = "'.$TPLMSG[$matches[1]].'";'."\r\n";
                    }else{
                        $line = $buffer;
                    }
                    fwrite($fpOut, $line);
                }
                echo <<<JSJ
             <script type="text/javascript">
                $("#complete").show();
                setTimeout(function(){location.replace('{$_SERVER['PHP_SELF']}')},1000);
             </script>
JSJ;
                flush();
                fclose($fpIn);
                fclose($fpOut);
                break;
            default:
                $langfiles = scandir("lang/");
        ?>
        <form name="dataform" action="<?=$_SERVER['PHP_SELF']?>?action=translate" method="post">
            <table align="center" class="form-table">
                <tr>
                    <td width="100"></td>
                    <th width="190">檔名</th>
                    <th width="80">語言代碼</th>
                </tr>
                <tr>
                    <th>來源語系</th>
                    <td>
                        <select name="sourcefile">
                            <option value="">選擇來源語系</option>
                       <? for($idx=2;$idx<count($langfiles);$idx++): ?>
                            <option value="<?=$langfiles[$idx]?>"><?=$langfiles[$idx]?></option>
                        <? endfor; ?>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="slc" size="5"/>
                    </td>
                </tr>
                <tr>
                    <th valign="top">目的語系</th>
                    <td width="190" valign="top">
                        <input type="text" name="targetfile" />
                    </td>
                    <td valign="top">
                        <input type="text" name="tlc" size="5"/>
                    </td>
                </tr>
                <tr>
                    <th valign="top">注意事項</th>
                    <td colspan="2">
                        <div>
                            <ul id="desc-box">
                                <li>目的語系會儲存在upload_files裡，這裡只要輸出檔名即可。</li>
                                <li>語言代碼請參考:<a href="https://developers.google.com/translate/v2/using_rest#language-params" target="_blank">Google API</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" align="center">
                        <input type="button" id="goTranslate" value="開始翻譯"/>
                    </td>
                </tr>
            </table>
            <script type="text/javascript">
                jQuery(function($){
                   $("#goTranslate").click(function(){
                       var empty=0;
                       $(dataform).find("select,input[type=text]").each(function(idx,elm){
                           if($(elm).val()==""){
                               empty++;
                           }
                       });
                       if(empty){
                           alert("請填妥項目再提交");
                       }else{
                           $(dataform).submit();
                       }
                   });
                });
            </script>
        </form>
        <?
       }
       ?>
    </body>
</html>
