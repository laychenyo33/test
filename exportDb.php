<?php
include_once("libs/libs-sysconfig.php");
if(App::getHelper('request')->isPost()){
    switch($_GET['action']){
        case "exportDb":
            if(App::getHelper('session')->allowExportOperate){
                $res = $db->query("show tables from ".$cms_cfg['db_name']." like '{$_POST['tbl_prefix']}%'");
                header("content-type: text/x-sql;charset=utf8");
                header("content-disposition: attachment; filename=export-".rtrim($_POST['tbl_prefix'],'_').".sql");
                if($_POST['ghelper']){
                    $dataMap = include("conf/ghelper.php");
                    $translator = Ghelper_TranslatorFactory::CreateTranslator("Google");
                    set_time_limit(0);
                }
                while(list($tablename) = $db->fetch_array($res,false)){
                    //資料表結構
                    if($_POST['structure']){
                        $sql = "show create table ".$tablename;
                        $result = $db->query_firstRow($sql,0);
                        echo "--\n";
                        echo "-- 資料表結構 `{$tablename}`\n";
                        echo "--\n\n";
                        echo $result[1].";\n\n";
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
                                $simple_table_name = str_replace($_POST['tbl_prefix'],'',$tablename);
                                if(isset($dataMap[$simple_table_name]) && in_array($k,$dataMap[$simple_table_name])){
                                    $tz++;
									
									$v = v_split($v); // 拆解內容
									
                                    $v = $translator->Translate($v, $_POST['tl'], $_POST['sl']);
									
									$v = v_split($v,true); // 重組內容
									
                                    sleep(1);
                                }
                            }
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
                        echo "--\n";
                        echo "-- 資料表匯出資料 `{$tablename}`\n";
                        echo "--\n\n";
                        echo sprintf("insert into `%s`(%s)values\n%s;\n\n",$tablename,implode(',',$fields),implode(",\n",$values));
                    }
                }
            }
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

?>
<!DOCTYPE html>
<html>
    <head>
        <title>TODO supply a title</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            #ghelper-options{display:none}
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
