<?php
include_once("libs/libs-sysconfig.php");
if($_FILES['sql']){
    $sql_content = file_get_contents($_FILES['sql']['tmp_name']);
    $sql_arr = explode(';',$sql_content);
    foreach($sql_arr as $sql){
        $db->query($sql);
        if($err = $db->report()){
            echo $err;
            die();
        }
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <meta name="generator" content="PSPad editor, www.pspad.com">
  <title></title>
  </head>
  <body>
      <form action="import-sql.php" method="post" enctype="multipart/form-data">
             <input type="file" name="sql" /><input type="submit" value="上傳"/>
      </form>
  </body>
</html>
