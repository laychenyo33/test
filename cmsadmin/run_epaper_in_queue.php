<?php
include_once("../conf/config.inc.php");
include_once("../libs/libs-manage-sysconfig.php");
include_once("../lang/".$cms_cfg['language']."-utf8.php");
set_time_limit(0);
//取得佇列項目
$sql = "select * from ".$db->prefix("epaper_queue")." where eq_send_time <= now()";
$res = $db->query($sql);
if($db->numRows($res)){
    //取得寄件資訊
    $from_sql="select sc_company,sc_email from ".$cms_cfg['tb_prefix']."_system_config where sc_id = '1'";
    $from_res = $db->query($from_sql);
    $fromRow = $db->fetch_array($from_res,1);
    $from_mail=$fromRow["sc_email"]; 
    $from_name=$fromRow["sc_company"];
    while($qRow = $db->fetch_array($res,1)){
        //取得寄送名單
        $sql = "select email from ".$db->prefix("epaper_queue_emaillist")." where eq_id='{$qRow['eq_id']}'";
        $eres = $db->query($sql);
        $mail_array=array();
        $mx_arr=array();
        while(list($email) = $db->fetch_array($eres,0)){
            if(trim($email) && strpos($email, '@')!==false){
                $mail_array[$email] += 1;
                if($mail_array[$email]==1){
                    $tmp = explode('@',$email);
                    $mx_arr[$tmp[1]][] = $email;
                }
            }
        }
        if(!empty($mx_arr)){
            //取得電子報內容
            $mail_subject=$qRow["e_subject"];
            $mail_content=$qRow["eq_content"];
            //初始化電子報樣版
            $mtpl = $main->get_epaper_template($qRow['eq_template']);
            if(is_a($mtpl,'TemplatePower')){
                //取得電子報頁首、頁尾
                $sql = "select st_epaper_header,st_epaper_footer from ".$cms_cfg['tb_prefix']."_service_term where st_id='1'";
                list($e_header,$e_footer) = $db->query_firstrow($sql,0);
                $mtpl->assignGlobal("MSG_EPAPER_HEADER",App::getHelper('main')->content_file_str_replace($e_header,'out'));
                $mtpl->assignGlobal("MSG_EPAPER_FOOTER",App::getHelper('main')->content_file_str_replace($e_footer,'out'));            
                $mtpl->assign("_ROOT.EPAPER_PAGE_TITLE",$qRow["e_subject"]);
                $mtpl->assign("_ROOT.EPAPER_TITLE",$qRow["e_subject"]);
                $mtpl->assign("_ROOT.EPAPER_CONTENT",$mail_content);
                if(trim($qRow['eq_attach_products'])){
                    $sql = "select p.*,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p_status='1' and p_id in(".$qRow['eq_attach_products'].")";
                    $p_rs = $db->query($sql);
                    while($p_row = $db->fetch_array($p_rs,1)){
                        $mtpl->newBlock("ATTACH_PRODUCT_LIST");
                        if($cms_cfg['ws_module']['ws_seo']){
                            $dirname = ($p_row['pc_seo_filename']?$p_row['pc_seo_filename']:"products")."/";
                            $p_link = $cms_cfg['base_url'].$dirname. $p_row['p_seo_filename'].".html";
                        }else{
                            $p_link = $cms_cfg['base_url']."products.php?func=p_detail&p_id=".$p_row['p_id'];
                        }
                        $simg = $p_row['p_small_img']?$cms_cfg['file_root'].$p_row['p_small_img']:$cms_cfg['default_preview_pic'];
                        $dimension = $main->resizeto($simg,219,171);
                        $mtpl->assign(array(
                           "VALUE_P_LINK"      => $p_link, 
                           "VALUE_P_SMALL_IMG" => $p_row['p_small_img']?$cms_cfg['file_url'].$p_row['p_small_img']:$cms_cfg['server_url'].$cms_cfg['default_preview_pic'], 
                           "VALUE_P_SMALL_IMG_W" => $dimension['width'], 
                           "VALUE_P_SMALL_IMG_H" => $dimension['height'], 
                           "VALUE_P_NAME"      => $p_row['p_name'], 
                           "VALUE_P_DESC"      => $p_row['p_desc'], 
                        ));
                    }
                }
                //寫入發送記錄
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_epaper_send (
                        e_id,
                        es_modifydate,
                        es_group,
                        e_subject
                    ) values (
                        '".$qRow["e_id"]."',
                        '".date("Y-m-d H:i:s")."',
                        '".$qRow['eq_group']."',
                        '".$qRow["e_subject"]."'
                    )";
                $rs = $db->query($sql);
                $es_id = $db->get_insert_id();                
                while(!empty($mx_arr)){
                    foreach($mx_arr as $mx => $email_list){
                        $i=0;
                        $nums = count($email_list);
                        while(($mail_str = array_shift($email_list))!==null){
                            $i++;
                            $mtpl->assignGlobal("CURRENT_RECEIVER",$mail_str);
                            $mail_content = $mtpl->getOutputContent();
                            $main->ws_mail_send_simple($from_mail,$mail_str,$mail_content,$mail_subject,$from_name);
                            $db->query("insert ".$db->prefix("epaper_send_emaillist")."(es_id,email)values('$es_id','$mail_str')");
                            /* 寄出50次後仍有待寄信件時，將剩餘的信件再存回原來的$mx_arr
                                                                 * 往下個$mx_arr迴圈繼續執行，就是先跑執行其他網域，其餘的部份會在while(!empty($mx_arr))時再繼續跑下去
                                                                 */
                            if($i==50 && $i<$nums){
                                $mx_arr[$mx] = $email_list;
                                sleep(60);
                                continue 2;
                            }
                        }
                        /*完成跑完$email_list之後，把$email_list的父容器刪除，以避免再次跑一次*/
                        unset($mx_arr[$mx]);
                        sleep(3);
                    }
                }
            }
        }
    }
    //刪除過期的佇列
    $sql = "delete from a, b using ".$db->prefix("epaper_queue")." as a, ".$db->prefix("epaper_queue_emaillist")." as b  where a.eq_id=b.eq_id and a.eq_send_time <= now()";
    $db->query($sql,true);
    $sql = "OPTIMIZE TABLE  `".$cms_cfg['tb_prefix']."_epaper_queue`";
    $db->query($sql);
    echo "done!";
}
?>
