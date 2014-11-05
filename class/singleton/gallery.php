<?php
class Singleton_Gallery{
    public $extension = "jpg,jpeg,png,JPG,JPEG,PNG";
    public $tpl;
    function __construct(TemplatePower $tpl){
        $this->tpl = $tpl;
    }

    function gallery_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //活動剪影列表
        if(!empty($_REQUEST["gc_id"])){
            $gc_id=$_REQUEST["gc_id"];
            $and_str="and gc_id='".$_REQUEST["gc_id"]."'";
        }else{
            $gc_id=0;
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_gallery where g_status='1'  ".$and_str." order by g_sort ".$cms_cfg['sort_pos'].",g_modifydate desc";
        $selectrs = $db->query($sql,true);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結，且重新組合包含limit的sql語法
        if($this->ws_seo==1 ){
            $func_str=$cms_cfg['base_root']."gallery/glist-".$gc_id;
            $sql=$main->pagination_rewrite($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        }else{
            $func_str=$cms_cfg['base_root']."gallery.php?func=g_list&gc_id=".$gc_id;
            $sql=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        }
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $i=0;
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $this->tpl->newBlock( "GALLERY_LIST" );
            $simg = $row["g_s_pic"]?$cms_cfg['file_root'].$row["g_s_pic"]:$cms_cfg['default_preview_pic'];
            $dimension = $main->resizeto($simg,$cms_cfg['gallery_cate_img_width'],$cms_cfg['gallery_cate_img_height']);
            $this->tpl->assign( array(
                "VALUE_GC_ID"  => $row["gc_id"],
                "VALUE_G_ID"  => $row["g_id"],
                "VALUE_G_SUBJECT" => $row["g_subject"],
                "VALUE_G_LINK" => $this->get_link($row,true),
                "VALUE_G_MODIFYDATE" => substr($row["g_modifydate"],0,10),
                "VALUE_G_TARGET" => ($row["g_pop"])?"_blank":"_parent",
                "VALUE_G_SERIAL" => $i,
                "VALUE_G_S_PIC" => $simg,
                "VALUE_G_S_PIC_W" => $dimension['width'],
                "VALUE_G_S_PIC_H" => $dimension['height'],
                "VALUE_G_STRIP_CONTENT" => str_replace("\r\n","",strip_tags($main->content_file_str_replace($row["g_content"],'out'))),
            ));
        }
    }
//活動剪影--顯示================================================================
    function gallery_show(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //活動剪影內容
        $sql="select * from ".$cms_cfg['tb_prefix']."_gallery where g_id='".$_REQUEST["g_id"]."'";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $main->header_footer("gallery",$row["g_subject"]);
        $main->layer_link($row["g_subject"]);
        $this->tpl->newBlock( "GALLERY_SHOW" );
        $this->tpl->assign( array(
            "VALUE_G_ID"  => $row["g_id"],
            "VALUE_G_SUBJECT" => $row["g_subject"],
            "VALUE_G_CONTENT" => $main->content_file_str_replace($row["g_content"],'out'),
            "VALUE_G_MODIFYDATE" => $row["g_modifydate"],
        ));
        for($i=1;$i<=$cms_cfg['gallery_img_limit'];$i++){
            if(trim($row["g_b_pic".$i])!=""){
                $big_img_array[]=$row["g_b_pic".$i];
            }
        }
        if(count($big_img_array) >0){
            $k=1;
            foreach($big_img_array as $key => $value){
               //顯示大圖
               $this->tpl->newBlock("GALLERY_PIC_LIST");
               $img = $value?$cms_cfg["file_root"].$value:$cms_cfg['default_preview_pic'];
               $dimensions = $main->resizeto($img,$cms_cfg['gallery_img_width'],$cms_cfg['gallery_img_height']);
               $this->tpl->assign("VALUE_BIG_PIC",$img);
               $this->tpl->assign("VALUE_BIG_PIC_W",$dimensions['width']);
               $this->tpl->assign("VALUE_BIG_PIC_H",$dimensions['height']);
            }
        }
        $this->tpl->assignGlobal("GO_BACK",$TPLMSG['PAGE_BACK']);
    }

    function get_link($row,$item=false){
        global $cms_cfg;
        if($item){
            if($this->ws_seo){
                $link = $cms_cfg["base_root"]."gallery/gdetail-".$row["gc_id"]."-".$row["g_id"].".html";
            }else{
                $link = $cms_cfg['base_root']."gallery.php?func=g_show&gc_id=".$row["gc_id"]."&g_id=".$row["g_id"];
            }
        }else{
            if($this->ws_seo==1 ){
                $link=$cms_cfg['base_root']."gallery/glist-".$row["gc_id"].".htm";
            }else{
                $link=$cms_cfg['base_root']."gallery.php?func=g_list&gc_id=".$row["gc_id"];
            }        
        }
        return $link;
    }
    
    function galler_dir_list($gc_id){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        if($gc_id){
            $and_str = " and gc_id='".$db->quote($gc_id)."'";
            $method = "dirshow";
            $this->tpl->newBlock("JS_POP_IMG");
            $this->tpl->newBlock("JS_LAZYLOAD");
        }else{
            $method = "dirlist";
        }
        $sql = "select * from ".$cms_cfg['tb_prefix']."_gallery_cate where gc_status='1' ".$and_str." order by gc_sort ".$cms_cfg['sort_pos'];
        $res = $db->query($sql,true);
        while($row = $db->fetch_array($res,1)){
            $this->{$method}($row);
        }
    }
    function dirlist($cate){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        if($cms_cfg["ws_module"]['ws_gallery_update_db']){
            $sql = "select gp_file from ".$cms_cfg['tb_prefix']."_gallery_pics where gc_id='".$cate['gc_id']."' limit 1";
            list($gp_file)=$db->query_firstRow($sql,0);   
            if($gp_file){
                $thumb = $main->file_str_replace($gp_file);
            }
        }else{
            $dir = $_SERVER['DOCUMENT_ROOT'].$cms_cfg['file_root'].$cate['gc_dir'];
            if(is_dir($dir)){
                $pattern = $dir . "/*.{".$this->extension."}";
                $imgs = glob($pattern,GLOB_BRACE);
                $thumb = $main->file_str_replace($imgs[0]);
            }
        }
        $simg = $thumb?$cms_cfg['file_root'].$thumb:$cms_cfg['default_preview_pic'];
        $this->tpl->newBlock( "GALLERY_CATE_LIST" );
        $dimension = $main->resizeto($simg,$cms_cfg['gallery_img_width'],$cms_cfg['gallery_img_height']);
        $this->tpl->assign( array(
            "VALUE_GC_SUBJECT" => $cate['gc_subject'],
            "VALUE_GC_LINK" => $this->get_link($cate),
            "VALUE_GC_S_PIC" => $simg,
            "VALUE_GC_S_PIC_W" => $dimension['width'],
            "VALUE_GC_S_PIC_H" => $dimension['height'],
        ));
        
    }
    function dirshow($cate){
        global $db,$cms_cfg,$TPLMSG,$main,$ws_array;
        if($cate['gc_desc']){
            $this->tpl->newBlock("GALLERY_CATE_DESC");
            $this->tpl->assign("VALUE_GC_DESC",$main->content_file_str_replace($cate['gc_desc'],'out'));
        }
        if($cms_cfg["ws_module"]['ws_gallery_update_db']){
            $sql = "select * from ".$cms_cfg['tb_prefix']."_gallery_pics where gc_id='".$cate['gc_id']."' order by sort ";
            $res = $db->query($sql,true);
            if($db->numRows($res)){
                while($row = $db->fetch_array($res,1)){
                    $i++;
                    $thumb = $main->file_str_replace($row['gp_file']);
                    $this->tpl->newBlock( "GALLERY_BATCH_LIST" );
                    $simg = $cms_cfg['file_root'].$thumb;
                    $dimension = $main->resizeto($simg,$cms_cfg['thumbs_img_width'],$cms_cfg['thumbs_img_height']);
                    $this->tpl->assign( array(
                        "VALUE_G_LINK" => $simg,
                        "VALUE_G_S_PIC" => $simg,
                        "VALUE_G_S_PIC_W" => $dimension['width'],
                        "VALUE_G_S_PIC_H" => $dimension['height'],
                        "TAG_CURRENT"      => ($i==1)?"current":"normal",
                    ));
                    $this->tpl->newBlock( "GALLERY_BATCH_LIST_BIG" );
                    $dimension = $main->resizeto($simg,369,295);
                    $this->tpl->assign( array(
                        "VALUE_G_LINK" => $simg,
                        "VALUE_G_S_PIC" => $simg,
                        "VALUE_G_S_PIC_W" => $dimension['width'],
                        "VALUE_G_S_PIC_H" => $dimension['height'],
                        "VALUE_GP_DESC"   => $row['gp_desc'],
                    ));
                    $this->tpl->newBlock( "GALLERY_BATCH_LIST_STR" );
                    $this->tpl->assign( array(
                        "VALUE_GP_DESC"   => $row['gp_desc'],
                        "TAG_CURRENT"      => ($i==1)?"current":"normal",
                    ));
                }            
            }            
        }else{
            $dir = $_SERVER['DOCUMENT_ROOT'].$cms_cfg['file_root'].$cate['gc_dir'];
            if(is_dir($dir)){
                $pattern = $dir . "/*.{".$this->extension."}";
                $imgs = glob($pattern,GLOB_BRACE);
                foreach($imgs  as $full_path_img){
                    $thumb = $main->file_str_replace($full_path_img);
                    $this->tpl->newBlock( "GALLERY_BATCH_LIST" );
                    $simg = $cms_cfg['file_root'].$thumb;
                    $dimension = $main->resizeto($simg,$cms_cfg['gallery_img_width'],$cms_cfg['gallery_img_height']);
                    $this->tpl->assign( array(
                        "VALUE_G_LINK" => $simg,
                        "VALUE_G_S_PIC" => $simg,
                        "VALUE_G_S_PIC_W" => $dimension['width'],
                        "VALUE_G_S_PIC_H" => $dimension['height'],
                    ));
                }
            }
        }
    }
}
?>
