<?php
include_once("libs/libs-sysconfig.php");
if(App::getHelper('request')->isPost()){
    switch($_GET['action']){
        case "exportDb":
            if(App::getHelper('session')->allowExportOperate){
                $spit_nums = 30;  //記錄切割限制
                $spit_symbol1 = '::::';  //內容分隔項目索引及對應內容分隔字元
                $spit_symbol2 = '~~~~';  //內容分隔項目分隔字元
                $tmp_name = 'upload_files/'.md5(time());//暫存檔名，輸出sql後會刪除
                $fp = fopen($tmp_name, 'w');
                $res = $db->query("show tables from ".$cms_cfg['db_name']." like '{$_POST['tbl_prefix']}%'");
                if($_POST['ghelper']){
                    $dataMap = include("conf/ghelper.php");
                    $translator = Ghelper_TranslatorFactory::CreateTranslator("Google");
                    set_time_limit(0);
                }
                while(list($tablename) = $db->fetch_array($res,false)){
                    $simple_table_name = str_replace($_POST['tbl_prefix'],'',$tablename);
                    if(!$_POST['ghelper'] || $dataMap[$simple_table_name]){
                        //資料表結構
                        if($_POST['structure']){
                            $sql = "show create table ".$tablename;
                            $result = $db->query_firstRow($sql,0);
                            fwrite($fp,"--\n");
                            fwrite($fp,"-- 資料表結構 `{$tablename}`\n");
                            fwrite($fp,"--\n\n");
                            fwrite($fp,$result[1].";\n\n");
                        }
                        //資料表欄位
                        $sql = "SHOW COLUMNS FROM ".$tablename;
                        $res2 = $db->query($sql);
                        $fields = array();
                        while($field = $db->fetch_array($res2,1)){
                            $fields[] = sprintf("`%s`",$field['Field']);
                        }
                        $sql = "select * from ".$tablename;
                        $res3 = $db->query($sql);
                        $values = array();
                        while($datarow = $db->fetch_array($res3,1)){
                            $tz=0;
                            foreach($datarow as $k => $v){
                                $v = trim($v);
                                if($translator && $v){
                                    $v_split = array();
                                    $transBox = array();
                                    if(isset($dataMap[$simple_table_name]) && in_array($k,$dataMap[$simple_table_name])){
                                        $tz++;
                                        //依html結構切割文字
                                        $v_split = wptexturize($v) ; 
                                        foreach($v_split as $h => $n_v){
                                            //有內容的部份才處理
                                            if(($n_v = trim($n_v))){
                                                //針對html結構屬性，像是alt 或title進行翻譯。但太麻煩了，放棄
                                                if(!preg_match('/^</i',$n_v)){
                                                    $v_split[$h] = $translator->Translate($n_v, $_POST['tl'], $_POST['sl']);
                                                    sleep(1);
                                                }
                                            }
                                        }
                                        //合併分割陣列
                                        $v = implode('',$v_split);
                                    }
                                }
                                //給sql用的整理
                                $v = str_replace("'", "''", $v);
                                $v = str_replace("\r", "\\r", $v);
                                $v = str_replace("\n", "\\n", $v);
                                $datarow[$k] = sprintf("'%s'",$v);
                                //每翻譯三個欄位休息1秒
                                //if($tz%3==0)sleep(1);
                            }
                            $values[] = sprintf("(%s)",implode(",",$datarow));
                            //有翻譯過的記錄且未休息過，休息1秒
                            //if($tz && $tz%3>0)sleep(1);
                        }
                        if(!empty($values)){
                            fwrite($fp,"--\n");
                            fwrite($fp,"-- 資料表匯出資料 `{$tablename}`\n");
                            fwrite($fp,"--\n\n");
                            //按 $spit_nums 值分割匯出資料，避免單一query太長而無法匯入。
                            foreach($values as $idx => $sql_value){
                                if($idx%$spit_nums==0){
                                    fwrite($fp,"insert into `{$tablename}`(".implode(',',$fields).")values\n");
                                }
                                fwrite($fp,$sql_value);
                                //輸出行結尾
                                if($idx<count($values)-1){//還有後面的資料
                                    if($idx%$spit_nums==($spit_nums-1)){//下一個記錄要重新insert
                                        fwrite($fp,";\n");
                                    }else{//一般接續記錄
                                        fwrite($fp,",\n");
                                    }
                                }else{//沒有後面的資料
                                    fwrite($fp,";\n\n");
                                }
                            }
                        }
                    }
                }
            }
            //關閉暫存檔案
            fclose($fp);
            //準備輸出
            header("content-type: text/x-sql;charset=utf8");
            header("content-disposition: attachment; filename=export-".rtrim($_POST['tbl_prefix'],'_').".sql");
            readfile($tmp_name);
            unlink($tmp_name);
            die();
            break;
        case "login":
            if($_POST['password'] && $_POST['password']==$cms_cfg['db_password']){
               App::getHelper('session')->allowExportOperate = true;
               header("location:".$_SERVER['PHP_SELF']);
               die();
            }
            $error_msg = "密碼有誤!";
            break;
    }
}

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

function wptexturize($text){
	$tagregexp = "(?:$tagregexp)(?![\\w-])"; // Excerpt of get_shortcode_regex().

	$comment_regex =
		  '!'           // Start of comment, after the <.
		. '(?:'         // Unroll the loop: Consume everything until --> is found.
		.     '-(?!->)' // Dash not followed by end of comment.
		.     '[^\-]*+' // Consume non-dashes.
		. ')*+'         // Loop possessively.
		. '-->';        // End of comment.

	$regex =  '/('			// Capture the entire match.
		.	'<'		// Find start of element.
		.	'(?(?=!--)'	// Is this a comment?
		.		$comment_regex	// Find end of comment
		.	'|'
		.		'[^>]+>'	// Find end of element
		.	')'
		. '|'
		.	'\['		// Find start of shortcode.
		.	'[\/\[]?'	// Shortcodes may begin with [/ or [[
		.	$tagregexp	// Only match registered shortcodes, because performance.
		.	'(?:'
		.		'[^\[\]<>]+'	// Shortcodes do not contain other shortcodes. Quantifier critical.
		.	'|'
		.		'<[^\[\]>]*>' 	// HTML elements permitted. Prevents matching ] before >.
		.	')*+'		// Possessive critical.
		.	'\]'		// Find end of shortcode.
		.	'\]?'		// Shortcodes may end with ]]
		. ')/s';

	$textarr = preg_split( $regex, $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
        return $textarr;
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>TODO supply a title</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            #configFormZone{width:600px;margin:100px auto 0 auto;}
            #ghelper-options{display:none}
            #ghelper-options .important{color:#f00}
            #ghelper-options .note{color: #008800}
        </style>
        <script type="text/javascript" src="js/jquery/jquery-1.8.3.min.js"></script>
    </head>
    <body>
        <? if(App::getHelper('session')->allowExportOperate): 
                $res = $db->query("show tables from ".$cms_cfg['db_name']);
                while(list($tablename) = $db->fetch_array($res,false)){
                    $tmpArr = explode('_',$tablename);
                    $prefix[$tmpArr[0]] = $tmpArr[0]."_";
                }            
        ?>
        <div id="configFormZone">
            <form name="tblprefixfrm" id="tblprefixfrm" action="<?=$_SERVER['PHP_SELF']?>?action=exportDb" method="post" target="_blank">
                匯出語言:<select name="tbl_prefix" id="tbl_prefix">
                    <option value=''>選擇語言</option>
                <?php foreach($prefix as $id=>$tbl_prefix): ?>
                    <option value='<?=$tbl_prefix?>'><?=$id?></option>
                <?php endforeach; ?>
                </select><br/>
                <label><input type="checkbox" name="structure" value="1" checked/>結構</label><br/>
                <label><input type="checkbox" name="ghelper" value="1" id="ghelper" />使用google翻譯</label><br/>
                <div id="ghelper-options">
                    <div id="ghelper-options-desc">
                        <span>說明:</span>
                        <ul>
                            <li>語言代碼請參考:<a href="https://developers.google.com/translate/v2/using_rest#language-params" target="_blank">Google API</a></li>
                            <li>因Google可能有擋大流量query，所以每次翻譯的query後先暫時一秒，因此匯出時間將會大大拉長</li>
                            <li class="note">翻譯前請再檢查conf/ghelper.php裡的設定和現有資料庫是否一致，可能有新增需要翻譯的欄位</li>
                            <li class="important">使用Google翻譯時，只會輸出conf/ghelper.php裡有設定的資料表。因為翻譯過後的文字編碼可能會和未翻譯的有衝突，結果導致匯入資料庫失敗!!</li>
                            <li class="important">因為html直接送出去翻譯的結果可能會大亂，所以在翻譯前會將單一欄位依html拆解，送出翻譯的部份是沒有html的。另外先前有將拆解後的文組合在一起一次送出翻譯，結果回傳結果異常，所以現在是逐一將拆解後的文字傳給google翻譯，又大大增加翻譯完成的時間。</li>
                        </ul>
                    </div>
                    原始語言:<input type="text" name="sl" size="10"/>&nbsp;&nbsp;
                    目地語言:<input type="text" name="tl" size="10"/>
                </div>
                <input type='button' id="export" value='匯出'/>
            </form>
            <script type="text/javascript">
                jQuery(function($){
                    $("#export").click(function(evt){
                        if($("#tbl_prefix").val()==''){
                            alert("請選擇語言!");
                            return false;
                        }
                        tblprefixfrm.submit();
                    });
                    $("#ghelper").click(function(evt){
                        if($(this).attr('checked')){
                            $("#ghelper-options").show();
                        }else{
                            $("#ghelper-options").hide();
                        }
                    });
                });
            </script>
        </div>
        <? else: ?>
        <div id="login-area">
            <form name="loginfrm" action="<?=$_SERVER['PHP_SELF']?>?action=login" method="post">
                <h2>登入表單</h2>
                <div class="message"><?=$error_msg?></div>
                請輸入密碼:<input type="password" name="password" value=""/>
                <input type="submit" value="登入"/>
            </form>
        </div>
        <? endif; ?>
    </body>
</html>
