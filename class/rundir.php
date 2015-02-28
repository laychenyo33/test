<?php
/*
 * 呼叫範例: rundir::run("./upload_files/*");
 * 因為會使用到$db, $main實例，所以呼叫前需先引用libs/libs-sysconfig.php
 * 
 * 說明:
 * 目前預設的操作是找特定資料夾下的png圖檔，然後比對資料庫裡的產品分類圖片、產品小圖、產品大圖欄位是否有相同的主要檔名，
 * 如果有的話就將資料庫的內容替代為png路徑。
 * 如果有不一樣的處理方式，建議的增加新的方法，然後修改 rundir::$processHandler的值。
 * 
 * 如果不限制為png，或是要限制為其他格式，請修改 rundir::$runPattern的值
 */
class rundir{
    //檔案清單裡要處理的格式
    static $runPattern = '#^(.*/)(.+)(\.png)$#';
    //檔案清單裡要處理的操作流程(方法名稱)
    static $processHandler = 'rendbimg';
    static function run($pattern){
        echo '==========<br/>';
        echo 'reading '.dirname($pattern)."<br/>";
        echo '-----------<br/>';
        //處理檔案
        foreach(glob($pattern) as $filename){
            if(is_dir($filename) && basename($filename)!=='mcith'){
                self::run($filename."/*");
            }else{
                //符合pattern要求才處理
                if(preg_match(self::$runPattern, $filename, $matches)){
                    call_user_func('rundir::'.self::$processHandler, $matches);
                }
            }
        }
        echo '==========<br/>';

    }

    static function rendbimg($matches){
        global $db,$cms_cfg,$db,$main;
        //分類圖片 pc_cate_img
        echo "處理分類圖片<br/>";
        $sql = "update ".$cms_cfg['tb_prefix']."_products_cate set pc_cate_img='".$main->file_str_replace($matches[0])."' where pc_cate_img regexp '{$matches[2]}.jpg$'";
        $db->query($sql,true);
        echo $sql."<br/>";
        //產品圖片
        //小圖 p_small_img
        echo "處理產品小圖<br/>";
        $sql = "update ".$cms_cfg['tb_prefix']."_products set p_small_img='".$main->file_str_replace($matches[0])."' where p_small_img regexp '{$matches[2]}.jpg$'";
        $db->query($sql,true);
        echo $sql."<br/>";
        //大圖 p_big_img[1-8]
        echo "處理產品大圖<br/>";
        for($i=1;$i<=$cms_cfg['big_img_limit'];$i++){
            $sql = "update ".$cms_cfg['tb_prefix']."_products_img set p_big_img{$i}='".$main->file_str_replace($matches[0])."' where p_big_img{$i} regexp '{$matches[2]}.jpg$'";
            $db->query($sql,true);
            echo $sql."<br/>";
        }
    }
    
}
