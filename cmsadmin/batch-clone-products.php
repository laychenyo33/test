<?php
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])  || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_products"]==0){
    header("location: ".$cms_cfg['manage_root']);
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");

switch($_GET['action']){
    case "clone":
        $cates = $_SESSION['clone_date']['cates'];
        $products = $_SESSION['clone_date']['products'];
        $sql = "select * from ".$_POST['to_lang']."products_cate where pc_id='{$_POST['to_pc_id']}'";
        $to_cate = $db->query_firstRow($sql,true);
        if($cates){
            foreach($cates as $pc_id => $cate){
                if(in_array($pc_id,$_POST['clone_pc_id'])){
                    unset($cate['pc_id']);
                    $cate['pc_level'] = $to_cate['pc_level'] + 1;
                    $cate['pc_parent'] = $to_cate['pc_id'];
                    $db_fields = array();
                    $db_values = array();
                    foreach($cate as $field => $value){
                        $db_fields[] = "`".$field."`";
                        $db_values[] = "'". mysql_real_escape_string($value) ."'";
                    }
                    $sql = "insert into ".$_POST['to_lang']."products_cate(".implode(",",$db_fields).")values(".implode(",",$db_values).")";
                    $db->query($sql,true);
                    $new_pc_id = $db->get_insert_id();
                    $new_pc_layer = $to_cate['pc_layer'] . "-" . $new_pc_id;
                    $sql = "update ".$_POST['to_lang']."products_cate set pc_layer='".$new_pc_layer."' where pc_id='".$new_pc_id."'";
                    $db->query($sql,true);
                }
            }
        }
        if($products){
            $pc_id = ($new_pc_id)? $new_pc_id : $to_cate['pc_id'];
            $pc_layer = ($new_pc_layer)? $new_pc_layer : $to_cate['pc_layer'];
            foreach($products as $p_id => $product){
                if(in_array($p_id,$_POST['clone_p_id'])){
                    unset($product['p_id']);
                    $product['pc_id'] = $pc_id;
                    $product['pc_layer'] = $pc_layer;
                    $db_fields = array();
                    $db_values = array();
                    foreach($product as $field => $value){
                        $db_fields[] = "`".$field."`";
                        $db_values[] = "'". mysql_real_escape_string($value) . "'";
                    }
                    $sql = "insert into ".$_POST['to_lang']."products(".implode(",",$db_fields).")values(".implode(",",$db_values).")";
                    $db->query($sql,true);
                }
            }
        }
        header("location:".$_SERVER['PHP_SELF']);
        die();
        break;
    case "get_to_lang_cate":
        products_cate_select($_POST['to_lang']);die();
        break;
    case "clone-preview":
        //取得來源資料
        $cates = array();
        $products = array();
        if($_POST['clone_mode']=="p"){
            $sql = "select * from ".$_POST['from_lang']."products where p_id in(".$_POST['id'].") ";
            $res = $db->query($sql,true);
            while($row = $db->fetch_array($res,1)){
                $products[$row['p_id']] = $row;
            }
        }elseif($_POST['clone_mode']=="pc"){
            $pc_id = $_POST['id'];
            if($_POST['with_products']){
                $tmp_id = explode(',',$_POST['id']);
                $pc_id = $tmp_id[0];
            }
            $sql = "select * from ".$_POST['from_lang']."products_cate where pc_id in(".$pc_id.") ";
            $res = $db->query($sql,true);
            while($row = $db->fetch_array($res,1)){
                $cates[$row['pc_id']] = $row;
            }
            if($_POST['with_products']){
                $sql = "select * from ".$_POST['from_lang']."products where pc_id in(".$pc_id.") ";
                $res = $db->query($sql,true);
                while($row = $db->fetch_array($res,1)){
                    $products[$row['p_id']] = $row;
                }
            }
        }
        if(empty($cates) && empty($products)){
            header("location:".$_SERVER['PHP_SELF']);
            die();
        }else{
            $_SESSION['clone_date']['cates'] = $cates;
            $_SESSION['clone_date']['products'] = $products;
        }
        $clone_mode = ($_POST['clone_mode']=='p')?"產品":"分類";
        $sql = "select pc_name from ".$_POST['to_lang']."products_cate where pc_id='{$_POST['to_pc_id']}'";
        list($to_pc_name) = $db->query_firstRow($sql,false);
        break;
}

function products_cate_select($lang,$pc_parent=0, $indent="") {
    global $db,$cms_cfg;
    $sql = "SELECT pc_id,pc_name FROM ".$lang."products_cate WHERE pc_parent='".$pc_parent."' order by pc_sort ".$cms_cfg['sort_pos'].",pc_modifydate desc";
    $selectrs = $db->query($sql);
    if($pc_parent==0){
        echo "<option value=''>請選擇類別</option>";
    }
    while ($row =  $db->fetch_array($selectrs,1)) {
        echo "<option value=\"".$row["pc_id"]."\" >".$indent."├".$row["pc_name"]."</option>";
        if($row["pc_id"]!=$pc_parent){
            products_cate_select($lang,$row["pc_id"],$indent."****");
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>複製產品記錄</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            .cate-options{display:none;}
        </style>
        <script type="text/javascript" src="../js/jquery/jquery-1.8.3.min.js"></script>
    </head>
    <body>
        <?php
        if($_GET['action']=="clone-preview"):
        ?>
        <form action="<?=$_SERVER['PHP_SELF']?>?action=clone" method="post" />
            <input type="hidden" name="clone_mode" value="<?=$_POST['clone_mode']?>"/>
            <input type="hidden" name="with_products" value="<?=$_POST['with_products']?>"/>
            <input type="hidden" name="from_lang" value="<?=$_POST['from_lang']?>"/>
            <input type="hidden" name="to_lang" value="<?=$_POST['to_lang']?>"/>
            <input type="hidden" name="to_pc_id" value="<?=$_POST['to_pc_id']?>"/>
            <div class="desc">
                複製選項:
                <ul>
                    <li>複製模式:<?=$clone_mode?></li>
                    <? if($_POST['clone_mode']=='pc'): ?>
                    <li>是否帶產品:<?=(($_POST['with_products'])?"是":"否")?></li>
                    <? endif; ?>
                    <li>來源語系:<?=$_POST['from_lang']?></li>
                    <li>目的語系:<?=$_POST['to_lang']?></li>
                    <li>id:<?=$_POST['id']?></li>
                    <li>目的分類:<?=$to_pc_name?></li>
                </ul>
            </div>
            <? if($cates): ?>
            <h3>分類</h3>
            <table>
                <? foreach($cates as $pc_id => $cate): ?>
                <tr>
                    <td><input type="checkbox" name="clone_pc_id[]" value="<?=$pc_id?>" checked/></td>
                    <td><?=$cate['pc_name']?></td>
                </tr>
                <? endforeach; ?>
            </table>
            <? endif; ?>
            
            <? if($products): ?>
            <h3>產品</h3>
            <table>
                <? foreach($products as $p_id => $product): ?>
                <tr>
                    <td><input type="checkbox" name="clone_p_id[]" value='<?=$p_id?>' checked/></td>
                    <td><?=$product['p_name']?></td>
                </tr>
                <? endforeach;?>
            </table>
            <? endif; ?>
            <input type="submit" id="clone" value="開始複製"/>
        </form>
        <?php
        else:
                $res = $db->query("show tables from `".$cms_cfg['db_name']."`");
                while(list($tablename) = $db->fetch_array($res,false)){
                    $tmpArr = explode('_',$tablename);
                    $prefix[$tmpArr[0]] = $tmpArr[0]."_";
                }
        ?>
        <form name="tblprefixfrm" id="tblprefixfrm" action="<?=$_SERVER['PHP_SELF']?>?action=clone-preview" method="post">
            <table width="650" align="center">
                <tr>
                    <th width="120">複製模式:</th>
                    <td>
                        <select name="clone_mode" id="clone_mode">
                            <option value="p">產品</option>
                            <option value="pc">分類</option>
                        </select>
                        <div class="cate-options">
                            <label><input type="checkbox" name="with_products" id="with_products" value="1"/>帶產品</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>來源語系:</th>
                    <td>
                        <select name="from_lang" id="from_lang">
                            <option value=''>選擇語言</option>
                        <?php foreach($prefix as $id=>$tbl_prefix): ?>
                            <option value='<?=$tbl_prefix?>'><?=$id?></option>
                        <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>來源id:</th>
                    <td>
                        <input type="text" name="id" id="id" value="" size="50"/>
                        <div class="desc">複製分類並帶產品時僅能複製一筆。複製多筆時，請使用 , 區隔。</div>
                    </td>
                </tr>
                <tr>
                    <th>目的語系:</th>
                    <td>
                        <select name="to_lang" id="to_lang">
                            <option value=''>選擇語言</option>
                        <?php foreach($prefix as $id=>$tbl_prefix): ?>
                            <option value='<?=$tbl_prefix?>'><?=$id?></option>
                        <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>目的分類:</th>
                    <td>
                        <select name="to_pc_id" id="to_pc_id">
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type='button' id="preview" value='預覽'/>    
                    </td>
                </tr>
            </table>
        </form>
        <script type="text/javascript">
            jQuery(function($){
               $("#clone_mode").change(function(evt){
                   if($(this).val()=="pc"){
                       $(".cate-options").show();
                   }else{
                       $(".cate-options").hide();
                       $("#with_products").attr('checked',false);
                   }
               });
               $("#to_lang").change(function(evt){
                   $.post("<?=$_SERVER['PHP_SELF']?>?action=get_to_lang_cate",{to_lang: $(this).val()},function(option){
                       $("#to_pc_id").html(option);
                   });
               });
               $("#preview").click(function(evt){
                   var err_msg = "";
                   if($("#from_lang").val()==""){
                       err_msg += "請選擇來源語系\n";
                   }
                   if($("#to_lang").val()==""){
                       err_msg += "請選擇目的語系\n";
                   }
                   if($("#id").val()==""){
                       err_msg += "請輸入來源id\n";
                   }
                   if(err_msg!=""){
                       alert(err_msg);
                       return false;
                   }
                   $(tblprefixfrm).submit();
               });
               $("#clone_mode").trigger('change');
               $("#to_lang").trigger('change');
            });
        </script>
        <?php
        endif;
        ?>
    </body>
</html>
