<?php
include_once("libs/libs-sysconfig.php");
$res = $db->query("show tables from ".$cms_cfg['db_name']." like 'cht_%'");
header("content-type: text/x-sql;charset=utf8");
header("content-disposition: attachment; filename=export.sql");
while(list($tablename) = $db->fetch_array($res,false)){
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
        foreach($datarow as $k => $v){
            $v = str_replace("'", "\'", $v);
            $datarow[$k] = sprintf("'%s'",$v);
        }
        $values[] = sprintf("(%s)",implode(",",$datarow));
    }
    if(!empty($values)){
        echo sprintf("insert into `%s`(%s)values %s;\n",$tablename,implode(',',$fields),implode(',',$values));
    }
}
?>