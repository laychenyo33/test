<?php
class Leftmenu_News extends Leftmenu_Abstract {
    protected function _getItems(){
        
        //最新消息分類
        $sql="select * from ".App::getHelper('db')->prefix("news_cate")." where nc_status='1' and nc_indep='0' order by nc_sort";
        $selectrs = App::getHelper('db')->query($sql);
        while($row = App::getHelper('db')->fetch_array($selectrs,1)){
            $menu_item = array(
                'name' =>  $row["nc_subject"],
                'link' => App::getHelper('request')->get_link('newscate',$row),
            );
            if($_GET["nc_id"]==$row["nc_id"] || $this->check_seo_name($_GET['type'],$_GET['f'],$row)){
                $menu_item['tag_cur'] = "class='".$this->currentClass."'";
                $this->currentRow = $row;
            }
            $left_menu[] = $menu_item;
        }
        return $left_menu;
    }
    
    function check_seo_name($type,$filename,$news){
        switch($type){
            case "list":
                return (trim($filename) && $filename==$news['nc_seo_filename'])? true : false;
            case "show":
                $news = App::getHelper('dbtable')->news->getDataList("n_seo_filename='{$filename}' and nc_id='{$news['nc_id']}'");
                if(count($news)){
                    return true;
                }else{
                    return false;
                }
        }
    }
}
