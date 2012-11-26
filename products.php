<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$products = new PRODUCTS;
class PRODUCTS{
    function PRODUCTS(){
        global $db,$cms_cfg,$tpl,$main,$TPLMSG;
        $this->op_limit=($_SESSION[$cms_cfg['sess_cookie_name']]["sc_one_page_limit"])?$_SESSION[$cms_cfg['sess_cookie_name']]["sc_one_page_limit"]:$cms_cfg["op_limit"];
        $this->jp_limit=$cms_cfg["jp_limit"];
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->ps = $cms_cfg['path_separator'];
        switch($_REQUEST["func"]){
            case "p_list"://產品列表
                $this->ws_tpl_file = "templates/ws-products-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->products_list("");
                $this->ws_tpl_type=1;
                break;
            case "p_new"://最新產品列表
                //需要會員權限,則顯示登入表單
                if(empty($_SESSION[$cms_cfg["sess_cookie_name"]]["MEMBER_ID"]) && $cms_cfg["ws_module"]["ws_new_product_login"]==1){
                    $this->ws_tpl_file = "templates/ws-login-form-tpl.html";
                    $this->ws_load_tp($this->ws_tpl_file);
                    $tpl->assignGlobal( "MSG_MEMBER_LOGIN",$TPLMSG["MEMBER_LOGIN"]);
                    $tpl->assignGlobal( "MSG_LOGIN_NOTICE1",$TPLMSG['LOGIN_NOTICE1']);
                }else{
                    $this->ws_tpl_file = "templates/ws-products-tpl.html";
                    $this->ws_load_tp($this->ws_tpl_file);
                    $this->products_list("p_new");
                }
                $this->ws_tpl_type=1;
                break;
            case "p_pro"://促銷產品列表
                $this->ws_tpl_file = "templates/ws-products-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->products_list("p_pro");
                $this->ws_tpl_type=1;
                break;
            case "p_hot"://熱門產品列表
                $this->ws_tpl_file = "templates/ws-products-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->products_list("p_hot");
                $this->ws_tpl_type=1;
                break;
            case "p_detail"://產品詳細資料
                //$this->ws_tpl_file = "templates/ws-products-detail-tpl.html";
                //$this->ws_load_tp($this->ws_tpl_file);
                $this->load_product_detail_template();
                $tpl->newBlock("JS_POP_IMG");
                $tpl->newBlock("JS_MAIN");
                $this->products_show();
                $this->ws_tpl_type=1;
                break;
            case "search"://產品搜尋
                $this->ws_tpl_file = "templates/ws-products-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $main->header_footer("",$TPLMSG["PRODUCTS"]);
                $this->products_search();
                $this->ws_tpl_type=1;
                break;
            default:    //產品分類列表
                $this->p_homepage=1;
                $this->ws_tpl_file = "templates/ws-products-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->products_list("");
                $this->ws_tpl_type=1;
                break;
        }
        if($this->ws_tpl_type){
            //有廣告模組才啟動廣告
            if($cms_cfg["ws_module"]["ws_ad"]==1){
                $main->ad_list($this->parent);
            }
            $tpl->printToScreen();
        }
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$ws_array,$db,$TPLMSG,$main;
        $ext=($this->ws_seo)?"htm":"php";
        $this->top_layer_link="<a href='".$cms_cfg['base_root']."products.".$ext."'>".$TPLMSG["PRODUCTS"]."</a>";
        $tpl = new TemplatePower( $cms_cfg['base_all_tpl'] );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方一般表單
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        $tpl->assignInclude( "AD_IMG", "templates/ws-fn-ad-image-tpl.html"); //圖片廣告模板
        $tpl->assignInclude( "AD_TXT", "templates/ws-fn-ad-txt-tpl.html"); //文字廣告模板        
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["PRODUCTS"]);
        $tpl->assignGlobal( "TAG_LAYER" , $this->top_layer_link);
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["products"]);//左方menu title
        $tpl->assignGlobal( "TAG_PRODUCTS_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["products"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-products"); //主要顯示區域的css設定
        $main->google_code(); //google analystics code , google sitemap code
        $main->left_fix_cate_list();
    }
    function load_product_detail_template(){
        global $db,$cms_cfg;
        if($this->ws_seo==1 && trim($_REQUEST["f"])!=""){
            $sql="select p_show_style from ".$cms_cfg['tb_prefix']."_products where p_seo_filename='".$_REQUEST["f"]."' and p_status='1' ";
        }else{
            $sql="select p_show_style from ".$cms_cfg['tb_prefix']."_products where p_id='".$_REQUEST["p_id"]."' and p_status='1' ";
        }
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $p_show_style=($row["p_show_style"])?$row["p_show_style"]:1;
        $this->ws_tpl_file = "templates/ws-products-detail".$p_show_style."-tpl.html";
        $this->ws_load_tp($this->ws_tpl_file);
    }
//產品--列表================================================================
    function products_list($mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //顯示資訊
        $show_style_str_pc="SHOW_STYLE_PC1";
        $show_style_str_pc_desc="SHOW_STYLE_PC1_DESC";
        $show_style_str_p="SHOW_STYLE_P1";
        $show_style_str_p_desc="SHOW_STYLE_P1_DESC";
        //一列顯示筆數
        $row_num=$cms_cfg["ws_products_row"];
        if($mode==""){
            //顯示模示: 1--圖文 2--文字 3--圖片
            //$this->show_style=1; //顯示模式固定為 圖文
            $this->parent=($_REQUEST["pc_parent"])?$_REQUEST["pc_parent"]:0;
            //顯示SEO 項目
            $sql="select pc_id,pc_name,pc_custom_status,pc_custom,pc_seo_title,pc_seo_keyword,pc_seo_description,pc_seo_short_desc,pc_seo_down_short_desc,pc_seo_h1,pc_seo_filename from ".$cms_cfg['tb_prefix']."_products_cate where pc_id > '0'";
            if($this->ws_seo==1 && trim($_REQUEST["f"])!=""){
                $sql .= " and pc_seo_filename='".$_REQUEST["f"]."' and pc_status='1' ";
            }else{
                $sql .= " and pc_id='".$this->parent."' and pc_status='1' ";
            }
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            if($rsnum > 0){
                $row = $db->fetch_array($selectrs,1);
                $seo_H1=$row["pc_name"];//預設h1
                //第一頁才顯示設定的meta,第二頁以後抓分類名稱或產品名稱做為meta title
                if(empty($_REQUEST["nowp"])){
                    $meta_array=array("meta_title"=>$row["pc_seo_title"],
                                      "meta_keyword"=>$row["pc_seo_keyword"],
                                      "meta_description"=>$row["pc_seo_description"],
                    );
                    $seo_H1=(trim($row["pc_seo_h1"])!="")?$row["pc_seo_h1"]:$row["pc_name"];
                    $main->header_footer($meta_array,$seo_H1);
                }
                if(!empty($row["pc_id"])){
                    $this->parent=$row["pc_id"];
                }
                $dirname=(trim($row["pc_seo_filename"]))?$row["pc_seo_filename"]:"products";
                //顯示上方簡述資料
                if(trim($row["pc_seo_short_desc"]) && empty($_REQUEST["nowp"]) && $row["pc_custom_status"]==0){//只在產品列表第一頁顯示上方簡述資料
                    $row["pc_seo_short_desc"]=preg_replace("/src=\"([^>]+)upload_files/","src=\"".$cms_cfg["file_root"]."upload_files",$row["pc_seo_short_desc"]);
                    $tpl->newBlock("PRODUCTS_CATE_SHORT_DESC");
                    $tpl->assign("VALUE_PC_SHORT_DESC",$row["pc_seo_short_desc"]);
                }
                //顯示下方簡述資料
                if(trim($row["pc_seo_down_short_desc"]) && empty($_REQUEST["nowp"]) && $row["pc_custom_status"]==0){//只在產品列表第一頁顯示下方簡述資料
                    $row["pc_seo_down_short_desc"]=preg_replace("/src=\"([^>]+)upload_files/","src=\"".$cms_cfg["file_root"]."upload_files",$row["pc_seo_down_short_desc"]);
                    $tpl->newBlock("PRODUCTS_CATE_DOWN_SHORT_DESC");
                    $tpl->assign("VALUE_PC_SHORT_DESC",$row["pc_seo_down_short_desc"]);
                }
            }else{
                if($this->p_homepage!=1){
                    include_once("404.htm");
                    exit();
                }
                $dirname="products";
                //顯示產品主頁 SEO H1 標題
                $sql = "select * from ".$cms_cfg['tb_prefix']."_metatitle where mt_name ='products' ";
                $selectrs = $db->query($sql);
                $row = $db->fetch_array($selectrs,1);
                $meta_array=array("meta_title"=>$row["mt_seo_title"],
                                  "meta_keyword"=>$row["mt_seo_keyword"],
                                  "meta_description"=>$row["mt_seo_description"],
                );
                $seo_H1=(trim($row["mt_seo_h1"]))?$row["mt_seo_h1"]:$TPLMSG["PRODUCTS"];
                $main->header_footer($meta_array,$seo_H1);
                //顯示產品主頁自訂頁
                if(trim($row["mt_seo_custom"])) {
                        $row["mt_seo_custom"]=preg_replace("/src=\"([^>]+)upload_files/","src=\"".$cms_cfg["file_root"]."upload_files",$row["mt_seo_custom"]);
                        $tpl->newBlock("PRODUCTS_CATE_CUSTOM");
                        $tpl->assign("VALUE_PC_CUSTOM",$row["mt_seo_custom"]);
                        $custom=1;
                }else{
                    //顯示產品主頁SEO簡述
                    if(trim($row["mt_seo_short_desc"])){
                        $row["mt_seo_short_desc"]=preg_replace("/src=\"([^>]+)upload_files/","src=\"".$cms_cfg["file_root"]."upload_files",$row["mt_seo_short_desc"]);
                        $tpl->newBlock("PRODUCTS_CATE_SHORT_DESC");
                        $tpl->assign("VALUE_PC_SHORT_DESC",$row["mt_seo_short_desc"]);
                    }
                }

            }
            $tpl->assignGlobal( array("VALUE_PC_PARENT" => $this->parent ,
                                      "MSG_PRODUCT_CATE" => $TPLMSG["PRODUCT_CATE"] ,
                                      "MSG_PRODUCT_LIST" => $TPLMSG["PRODUCT_LIST"] ,
            ));
            //非自訂頁面顯示分類列表
            if(!$row["pc_custom_status"] && trim($row["mt_seo_custom"])==""){
                //分類列表------------------------
                $sql="select * from ".$cms_cfg['tb_prefix']."_products_cate where pc_id >'0'";
                $sql .=  " and pc_parent='".$this->parent."' ";
                $and_str = " and pc_status='1' order by pc_up_sort desc,pc_sort ".$cms_cfg['sort_pos']." ";
                $sql .= $and_str;
                $selectrs = $db->query($sql);
                $rsnum    = $db->numRows($selectrs);
                if($rsnum > 0){
                    $tpl->newBlock( "TAG_PRODUCTS_CATE_LIST" );
                }
                $i=0;
                while($row = $db->fetch_array($selectrs,1)){
//                    if($this->ws_seo){
//                        if(trim($row["pc_seo_filename"]) !=""){
//                            //$dirname=$row["pc_seo_filename"];
//                            $pc_link=$cms_cfg["base_root"].$row["pc_seo_filename"].".htm";
//                        }else{
//                            //$dirname=$row["pc_id"];
//                            $pc_link=$cms_cfg["base_root"]."category-".$row["pc_id"].".htm";
//                        }
//                    }else{
//                        $pc_link=$cms_cfg["base_root"]."products.php?func=p_list&pc_parent=".$row["pc_id"];
//                    }
                    $pc_link = $this->get_link($row);
                    //收集第二頁以後pc_name 做為 meta title
                    if(!empty($_REQUEST["nowp"]) && $i<3){
                        $meta_title .=$row["pc_name"];
                    }
                    if(!empty($_REQUEST["nowp"]) && $i<6){
                        $meta_description .=$row["pc_name"];
                    }
                    $pc_img=(trim($row["pc_cate_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["pc_cate_img"];
                    $i++;
                    $tpl->newBlock( $show_style_str_pc );
                    $tpl->assign( array( "VALUE_PC_NAME"  => $row["pc_name"],
                                         "VALUE_PC_NAME_ALIAS" =>$row["pc_name_alias"],
                                         "VALUE_PC_LINK"  => $pc_link,
                                         "VALUE_PC_ID" => $row["pc_id"],
                                         "VALUE_P_TOTAL" => $p_total,
                                         "VALUE_PC_SHOW_STYLE" => $row["pc_show_style"],
                                         "VALUE_PC_CATE_IMG" => $pc_img,
                                         "VALUE_PC_SERIAL" => $i,
                    ));
                    $dimensions["width"]=$cms_cfg['small_img_width'];
                    $dimensions["height"]=$cms_cfg['small_img_height'];
                    if(is_file($_SERVER['DOCUMENT_ROOT'].$pc_img)){
                        list($width, $height) = getimagesize($_SERVER['DOCUMENT_ROOT'].$pc_img);
                        $dimensions = $main->resize_dimensions($cms_cfg['small_img_width'],$cms_cfg['small_img_height'],$width,$height);
                    }
                    $tpl->assign("VALUE_PC_SMALL_IMG_W",$dimensions["width"]);
                    $tpl->assign("VALUE_PC_SMALL_IMG_H",$dimensions["height"]);
                    if($row_num){
                        if($i%$row_num==0){
                            $tpl->assign("TAG_PRODUCTS_CATE_TRTD","</tr><tr>");
                        }
                    }
                    if($row["pc_id"]==$_REQUEST["pc_id"]){
                        $tpl->assignGlobal("TAG_NOW_CATE",$row["pc_name"]);
                    }
                }
            }
        }else{
            //最新產品、促銷產品、熱門產品
            $main->header_footer("");
        }
        //階層
        $func_str="";
        $products_cate_layer=$main->get_layer_rewrite($cms_cfg['tb_prefix']."_products_cate","pc_name","pc",$this->parent,$func_str);
        if(!empty($products_cate_layer)){
            $tpl->assignGlobal("TAG_LAYER",$this->top_layer_link . $this->ps . implode($this->ps,$products_cate_layer));
        }
        if($row["pc_custom_status"]==1){//自訂頁面
                $row["pc_custom"]=preg_replace("/src=\"([^>]+)upload_files/","src=\"".$cms_cfg["file_root"]."upload_files",$row["pc_custom"]);
                $tpl->newBlock("PRODUCTS_CATE_CUSTOM");
                $tpl->assign("VALUE_PC_CUSTOM",$row["pc_custom"]);
        }else{
            //產品列表
            //$sql="select * from ".$cms_cfg['tb_prefix']."_products where p_id > '0'";
            $sql="select p.pc_id,p.p_id,p.p_name,p.p_name_alias,p.p_serial,p.p_small_img,p.p_seo_filename,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p.p_status='1' ";

            //最新產品
            if($mode=="p_new"){
                $sql .=  " and p.p_type in ('1','3','5','7') ";
                $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG['PRODUCT_NEW']);
            //熱門產品
            }elseif($mode=="p_hot"){
                $sql .=  " and p.p_type in ('2','3','6','7') ";
                $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG['PRODUCT_HOT']);
            //促銷產品
            }elseif($mode=="p_pro"){
                $sql .=  " and p.p_type in ('4','5','6','7') ";
                $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG['PRODUCT_PROMOTION']);
            }else{
                $sql .=  " and p.pc_id = '".$this->parent."' ";
                if(empty($_SESSION[$cms_cfg["sess_cookie_name"]]["MEMBER_ID"]) && $cms_cfg["ws_module"]["ws_new_product_login"]==1){
                    $sql .=  " and p.p_type not in ('1','3','5','7') ";
                }
            }
            //附加條件
            if($mode == "p_new") {
                $and_str = " and p.p_status='1' order by p.p_up_sort desc,p.p_new_sort ".$cms_cfg['sort_pos'].",p.p_modifydate desc";
            }else{
                $and_str = " and p.p_status='1' order by p.p_up_sort desc,p.p_sort ".$cms_cfg['sort_pos'].",p.p_modifydate desc";
            }
            $sql .= $and_str;
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records    = $db->numRows($selectrs);
            //取得分頁連結
            if($mode==""){
                if($this->ws_seo==1 && trim($_REQUEST["f"])!=""){
                    $func_str=$_REQUEST["f"];
                    $page=$main->pagination_rewrite($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records);
                }else{
                    $func_str="products.php?func=p_list&pc_parent=".$this->parent;
                    $page=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records);
                }
            }else{
                    $func_str="products.php?func=".$mode;
                    $page=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records);
            }
            //重新組合包含limit的sql語法
            $sql=$main->sqlstr_add_limit($this->op_limit,$_REQUEST["nowp"],$sql);
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $tpl->assignGlobal( array("MSG_NAME"  => $TPLMSG['NAME'],
                                      "MSG_SUBJECT"  => $TPLMSG['SUBJECT'],
                                      "MSG_MODE" => $TPLMSG['MANAGE_CATE'],
                                      "MSG_MODIFY" => $TPLMSG['MODIFY'],
                                      "MSG_CATE" => $TPLMSG['CATE'],
                                      "MSG_SPECIAL_PRICE" => $TPLMSG['PRODUCT_SPECIAL_PRICE']
            ));

            //產品列表------------------------
            if($rsnum > 0){
                $tpl->newBlock( "TAG_PRODUCTS_LIST" );
                if($cms_cfg["ws_module"]["ws_listpage_cart"]==1){
                    $tpl->newBlock( "TAG_ADD_CART" );
                }
            }
            $j=0;
            $k=$page["start_serial"];
            while ( $row = $db->fetch_array($selectrs,1) ) {
//                if($this->ws_seo){
//                    $dirname=(trim($row["pc_seo_filename"]))?$row["pc_seo_filename"]:"products";
//                    if(trim($row["p_seo_filename"]) !=""){
//                        $p_link=$cms_cfg["base_root"].$dirname."/".$row["p_seo_filename"].".html";
//                    }else{
//                        $p_link=$cms_cfg["base_root"].$dirname."/"."products-".$row["p_id"]."-".$row["pc_id"].".html";
//                    }
//                }else{
//                    $p_link=$cms_cfg["base_root"]."products.php?func=p_detail&p_id=".$row["p_id"]."&pc_parent=".$row["pc_id"];
//                }
                $p_link = $this->get_link($row,true);
                //收集第二頁以後pc_name 做為 meta title
                if(!empty($_REQUEST["nowp"]) && $j<3){
                    $meta_title .=$row["p_name"];
                }
                if(!empty($_REQUEST["nowp"]) && $j<6){
                    $meta_description .=$row["p_name"];
                }
                $j++;
                $k++;
                $p_img=(trim($row["p_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["p_small_img"];
                $tpl->newBlock( $show_style_str_p );
                $tpl->assign( array("VALUE_P_NAME" =>$row["p_name"],
                                    "VALUE_P_NAME_ALIAS" =>$row["p_name_alias"],
                                    "VALUE_P_LINK"  => $p_link,
                                    "VALUE_P_SMALL_IMG" => $p_img,
                                    "VALUE_P_SPECIAL_PRICE" => $row["p_special_price"],
                                    "VALUE_P_SERIAL" => $row["p_serial"],
                                    "VALUE_P_NO" => $k,
                ));
                $dimensions["width"]=$cms_cfg['small_img_width'];
                $dimensions["height"]=$cms_cfg['small_img_height'];
                if(is_file($_SERVER['DOCUMENT_ROOT'].$p_img)){
                    list($width, $height) = getimagesize($_SERVER['DOCUMENT_ROOT'].$p_img);
                    $dimensions = $main->resize_dimensions($cms_cfg['small_img_width'],$cms_cfg['small_img_height'],$width,$height);
                }
                $tpl->assign("VALUE_P_SMALL_IMG_W",$dimensions["width"]);
                $tpl->assign("VALUE_P_SMALL_IMG_H",$dimensions["height"]);
                /*
                //點選跳大圖
                if($cms_cfg["ws_module"]["ws_pop_bigimg"]==1){
                    $big_img1_path=$this->get_pop_big_img($row["p_id"]);
                    $tpl->assign("VALUE_P_BIG_IMG1",$big_img1_path);
                }
                */
                //直接勾選產品Add Cart
                if($cms_cfg["ws_module"]["ws_listpage_cart"]==1){
                    $tpl->newBlock( "CART_CHECK_BOX" );
                    $tpl->assign( "VALUE_P_ID",$row["p_id"]);
                    $tpl->gotoBlock( $show_style_str_p );
                }

                //當後台系統設定為詢價車,則強制把所有的價格隱藏
                if($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]!=1){
                    $show_price=0;
                }else{
                    $show_price=1;
                }
                //詢價商品或是購物商品
                if($show_price==0){
                        $tpl->assign("MSG_SPECIAL_PRICE","");
                        $tpl->assign("VALUE_P_SPECIAL_PRICE" ,"");
                }else{
                    //購物車
                    //會員有登入改為顯示折扣價
                    if(!empty($this->discount)){
                        $tpl->assign("MSG_SPECIAL_PRICE",$TPLMSG["PRODUCT_DISCOUNT_PRICE"]);
                        if($this->discount!=100){ //無折扣也不顯示
                            $discount_price=floor($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]/100*$row["p_special_price"]);
                            $tpl->assign("VALUE_P_SPECIAL_PRICE",$discount_price);
                        }
                    }
                }
                if($row_num){
                    if($j%$row_num==0){
                        $tpl->assign("TAG_PRODUCTS_TRTD","</tr><tr>");
                    }
                }
            }
            //顯示第二頁以後的meta title
            if(!empty($_REQUEST["nowp"])){
                $meta_array=array("meta_title"=>$meta_title,
                                  "meta_keyword"=>$meta_title,
                                  "meta_description"=>$meta_description,
                );
                //$seo_H1  預設抓分類名稱
                $main->header_footer($meta_array,$seo_H1);
            }
            if($k==0 && $i==0){
                if($custom){
                    $tpl->assignGlobal("MSG_NO_DATA","");
                }else{
                    $tpl->assignGlobal("MSG_NO_DATA",$TPLMSG['NO_DATA']);
                }
            }elseif($j!=0){
                $tpl->newBlock( "PAGE_DATA_SHOW" );
                $tpl->assign( array("VALUE_TOTAL_RECORDS"  => $page["total_records"],
                                    "VALUE_TOTAL_PAGES"  => $page["total_pages"],
                                    "VALUE_PAGES_STR"  => $page["pages_str"],
                                    "VALUE_PAGES_LIMIT"=>$this->op_limit
                ));
                if($page["bj_page"]){
                    $tpl->newBlock( "PAGE_BACK_SHOW" );
                    $tpl->assign( "VALUE_PAGES_BACK"  , $page["bj_page"]);
                    $tpl->gotoBlock("PAGE_DATA_SHOW");
                }
                if($page["nj_page"]){
                    $tpl->newBlock( "PAGE_NEXT_SHOW" );
                    $tpl->assign( "VALUE_PAGES_NEXT"  , $page["nj_page"]);
                    $tpl->gotoBlock("PAGE_DATA_SHOW");
                }
            }
        }
    }
//產品詳細資料================================================================
    function products_show(){
        global $db,$tpl,$cms_cfg,$ws_array,$TPLMSG,$main;
        $this->discount=$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"];
        //欄位名稱
        $tpl->assignGlobal( array("MSG_AMOUNT"  => $TPLMSG['AMOUNT'],
                                  "MSG_PRODUCT_NAME"  => $TPLMSG['PRODUCT_NAME'],
                                  "MSG_PRODUCT_PRICE" => $TPLMSG['PRODUCT_PRICE'],
                                  "MSG_PRODUCT_LIST_PRICE" => $TPLMSG['PRODUCT_LIST_PRICE'],
                                  "MSG_PRODUCT_SPECIAL_PRICE" => $TPLMSG['PRODUCT_SPECIAL_PRICE'],
                                  "MSG_PRODUCT_SERIAL" => $TPLMSG['PRODUCT_SERIAL'],
                                  "MSG_PRODUCT_IMAGE" => $TPLMSG['PRODUCT_IMG'],
                                  "MSG_PRODUCT_SPECIAL_PRICE" => $TPLMSG['PRODUCT_SPECIAL_PRICE'],
                                  "MSG_PRODUCT_CATE" => $TPLMSG["PRODUCT_CATE"],
                                  "MSG_PRODUCT_DISCOUNT_PRICE" => $TPLMSG["PRODUCT_DISCOUNT_PRICE"]));

        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],
            ));
        }
        //如果是rewrite過的網址,先取得pc_parent
        if($this->ws_seo==1 && trim($_REQUEST["f"])!=""){
            $sql="select * from ".$cms_cfg['tb_prefix']."_products where p_seo_filename='".$_REQUEST["f"]."' and p_status='1' ";
        }else{
            $sql="select * from ".$cms_cfg['tb_prefix']."_products where p_id='".$_REQUEST["p_id"]."' and p_status='1' ";
        }
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $rsnum    = $db->numRows($selectrs);
        if ($rsnum > 0) {
            //取得左方分類列表--brother layer
            //$this->left_cate_list($row["pc_id"]);
            $seo_H1=(trim($row["p_seo_h1"]))?$row["p_seo_h1"]:$row["p_name"];
            $func_str="";
            $products_cate_layer=$main->get_layer_rewrite($cms_cfg['tb_prefix']."_products_cate","pc_name","pc",$row["pc_id"],$func_str,1);
            if(!empty($products_cate_layer)){
                $tpl->assignGlobal("TAG_LAYER",$this->top_layer_link . $this->ps . implode($this->ps,$products_cate_layer) . $this->ps . $row["p_name"]);
            }
            $meta_array = array("meta_title"=>$row["p_seo_title"],
                                "meta_keyword"=>$row["p_seo_keyword"],
                                "meta_description"=>$row["p_seo_description"],
            );
            $main->header_footer($meta_array,$seo_H1);
            //顯示上一筆、下一筆連結
            if($cms_cfg["ws_module"]["ws_products_nextlink"]==1){
                    $this->products_next_previous($row["p_id"],$row["pc_id"],$row["p_sort"]);
            }			
            //是否為自訂頁面
            if($row["p_custom_status"]){
                $tpl->newBlock("PRODUCTS_DETAIL_CUSTOM");
                $row["p_custom"]=preg_replace("/src=\"([^>]+)upload_files/","src=\"".$cms_cfg["file_root"]."upload_files",$row["p_custom"]);
                $tpl->assign("VALUE_P_CUSTOM",$row["p_custom"]);
            }else{
                $this->show_style=($row["p_show_style"])?$row["p_show_style"]:1;
                $show_style_str_p="SHOW_STYLE_P".$this->show_style;
                $product_spec_style="PRODUCT_SPEC".$this->show_style;
                $product_desc_style="PRODUCT_DESC".$this->show_style;
                $product_character_style="PRODUCT_CHARACTER".$this->show_style;
                //圖片特效
                $tpl->newBlock("JS_".$show_style_str_p);
                $tpl->newBlock("PRODUCTS_DETAIL_DEFAULT");
                $tpl->assignGlobal( array("VALUE_P_ID"  => $row["p_id"],
                                          "VALUE_PC_ID"  => $row["pc_id"],
                                          "VALUE_P_NAME" => $row["p_name"],
                                          "VALUE_P_NAME_ALIAS" => $row["p_name_alias"],
                                          "VALUE_P_CUSTOM" => $row["p_custom"],
                                          "VALUE_P_SERIAL" => $row["p_serial"],
                                          "VALUE_P_LIST_PRICE" => $row["p_list_price"],
                                          "VALUE_P_SPECIAL_PRICE" => $row["p_special_price"],
                                          "VALUE_SMALL_IMG" => (trim($row["p_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["p_small_img"],
                                          "VALUE_P_SEO_SHORT_DESC" => $row["p_seo_short_desc"],
                                          "VALUE_P_CROSS_CATE" => $row["p_cross_cate"],
                ));
                $this->products_show_pic($row["p_id"]);//顯示大圖資料
                //當後台系統設定為詢價車,則強制把所有的價格隱藏
                if($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]!=1){
                    $show_price=0;
                }else{
                    $show_price=1;
                }
                //詢價商品或是購物商品
                if($show_price==0){
                    if($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]==0){
                        if($cms_cfg["ws_module"]["ws_inquiry_type"] != 1){
                            $tpl->assignGlobal("MSG_JOIN_CART",$TPLMSG["JOIN"].$TPLMSG["INQUIRY_CART"]);
                            $tpl->assignGlobal("VALUE_P_CART_TYPE","inquiry");
                            $tpl->assignGlobal("CART_ADD",$TPLMSG['CART_ADD'].$TPLMSG['CART_INQUIRY']);
                        }else{
                            $tpl->newBlock("SINGLE_INQUIRY");
                        }
                        //$tpl->gotoBlock("BIG_IMG".$this->template_str);
                    }else{
                        $tpl->assignGlobal("VALUE_P_LIST_PRICE" ,"");
                        $tpl->assignGlobal("VALUE_P_SPECIAL_PRICE","");
                    }
                }else{
                    $tpl->newBlock("CART_TYPE_SHOPPING");
                    //$tpl->gotoBlock($show_style_str_p);
                    //會員有登入顯示折扣價
                    if(!empty($this->discount)){
                        if($this->discount!=100){ //無折扣也不顯示
                            $discount_price=floor($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]/100*$row["p_special_price"]);
                            $tpl->newBlock("SHOW_DISCOUNT");
                            $tpl->assign("VALUE_P_DISCOUNT_PRICE",$discount_price);
                            //$tpl->gotoBlock("BIG_IMG".$this->template_str);
                        }
                    }
                    $tpl->assignGlobal("MSG_JOIN_CART",$TPLMSG["JOIN"].$TPLMSG["SHOPPING_CART"]);
                    $tpl->assignGlobal("VALUE_P_CART_TYPE","shopping");
                    $tpl->assignGlobal("CART_ADD",$TPLMSG['CART_ADD'].$TPLMSG['CART_SHOPPING']);
                    //$tpl->gotoBlock("BIG_IMG".$this->template_str);
                }
                //附件檔案1
                $ext_array= array("pdf","doc");
                if($row["p_attach_file1"]){
                    $icon = $this->select_icon($row["p_attach_file1"]);
                    $tpl->newBlock("ATTACH_FILE1");
                    $tpl->assign("VALUE_PAF_LINK",$cms_cfg["file_root"].$row["p_attach_file1"]);
                    $tpl->assign("ATTACH_ICON", $icon);
                    //$tpl->gotoBlock("BIG_IMG".$this->template_str);
                }
                //附件檔案2
                if($row["p_attach_file2"]){
                    $icon = $this->select_icon($row["p_attach_file2"]);
                    $tpl->newBlock("ATTACH_FILE2");
                    $tpl->assign("VALUE_PAF_LINK",$cms_cfg["file_root"].$row["p_attach_file2"]);
                    $tpl->assign("ATTACH_ICON", $icon);
                    //$tpl->gotoBlock("BIG_IMG".$this->template_str);
                }
                $ck_str=str_replace("&nbsp;","",strip_tags($row["p_desc"],"<img><iframe>"));
                //產品敘述
                if(trim($ck_str)!=""){
                        $row["p_desc"]=preg_replace("/src=\"([^>]+)upload_files/","src=\"".$cms_cfg["file_root"]."upload_files",$row["p_desc"]);
                        $tpl->newBlock($product_desc_style);
                        $tpl->assign("MSG_PRODUCT_DESC" ,  ($cms_cfg["ws_module"]["ws_products_title"]==1)?$row["p_desc_title"]:$TPLMSG['PRODUCT_DESCRIPTION']);
                        $tpl->assign("VALUE_P_DESC" , $row["p_desc"]);
                        $tpl->gotoBlock("PRODUCTS_DETAIL_DEFAULT");
                }
                $ck_str=str_replace("&nbsp;","",strip_tags($row["p_character"],"<img><iframe>"));
                //產品特性
                if(trim($ck_str)!=""){
                        $tpl->newBlock($product_character_style);
                        $row["p_character"]=preg_replace("/src=\"([^>]+)upload_files/","src=\"".$cms_cfg["file_root"]."upload_files",$row["p_character"]);
                        $tpl->assign("MSG_PRODUCT_CHARACTER" ,  ($cms_cfg["ws_module"]["ws_products_title"]==1)?$row["p_character_title"]:$TPLMSG['PRODUCT_CHARACTER']);
                        $tpl->assign("VALUE_P_CHARACTER" , $row["p_character"]);
                        $tpl->gotoBlock("PRODUCTS_DETAIL_DEFAULT");
                }
                $ck_str=str_replace("&nbsp;","",strip_tags($row["p_spec"],"<img><iframe>"));
                //產品規格
                if(trim($ck_str)!=""){
                        $tpl->newBlock($product_spec_style);
                        $row["p_spec"]=preg_replace("/src=\"([^>]+)upload_files/","src=\"".$cms_cfg["file_root"]."upload_files",$row["p_spec"]);
                        $tpl->assign("MSG_PRODUCT_SPEC" ,  ($cms_cfg["ws_module"]["ws_products_title"]==1)?$row["p_spec_title"]:$TPLMSG['PRODUCT_SPEC']);
                        $tpl->assign("VALUE_P_SPEC" , $row["p_spec"]);
                        $tpl->gotoBlock("PRODUCTS_DETAIL_DEFAULT");
                }
                //相關產品
                if($cms_cfg["ws_module"]["ws_products_related"]==1){
                    $this->related_products($row["p_related_products"],$row["pc_id"]);
                }
            }
        }else{
            include_once("404.htm");
            exit();
        }
    }
    function get_pop_big_img($p_id){
        global $db,$cms_cfg;
        //取得大圖資料
        $sql="select p_big_img1 from ".$cms_cfg['tb_prefix']."_products_img where p_id='".$p_id."'";
        $selectrs = $db->query($sql);
        $row2 = $db->fetch_array($selectrs,1);
        $rsnum  = $db->numRows($selectrs);
        $big_img1_path="";
        if ($rsnum > 0) {
            $big_img1_path=$cms_cfg["file_root"].$row2["p_big_img1"];
        }
        return $big_img1_path;
    }
    function left_cate_list($pc_id){
        global $tpl,$db,$main,$cms_cfg;
        $sql="select pc_parent from ".$cms_cfg['tb_prefix']."_products_cate where pc_id='".$pc_id."'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        if($rsnum > 0 ){
            $row = $db->fetch_array($selectrs,1);
            $sql="select * from ".$cms_cfg['tb_prefix']."_products_cate where pc_parent='".$row["pc_parent"]."' and pc_status='1' order by pc_up_sort desc,pc_sort ".$cms_cfg['sort_pos']." ";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            if($rsnum > 0 ){
                //顯示左方分類
                while($row = $db->fetch_array($selectrs,1)){
                    $sql="select * from ".$cms_cfg['tb_prefix']."_products_cate where pc_parent='".$row["pc_id"]."' and pc_status='1'";
                    $pc_subtotal=$main->count_total_records($sql);
                    $sql2="select * from ".$cms_cfg['tb_prefix']."_products where pc_id='".$row["pc_id"]."' and p_status='1'";
                    $p_total=$main->count_total_records($sql2)+$pc_subtotal; //次分類及所屬產品總合
//                    if($this->ws_seo){
//                        if(trim($row["pc_seo_filename"]) !=""){
//                            $pc_link=$cms_cfg["base_root"].$row["pc_seo_filename"].".htm";
//                        }else{
//                            $pc_link=$cms_cfg["base_root"]."category-".$row["pc_id"].".htm";
//                        }
//                    }else{
//                        $pc_link=$cms_cfg["base_root"]."products.php?func=p_list&pc_parent=".$row["pc_id"];
//                    }
                    $pc_link = $this->get_link($row);
                    $tpl->newBlock( "LEFT_CATE_LIST" );
                    $tpl->assign( array( "VALUE_CATE_NAME" => $row["pc_name"]."(".$p_total.")",
                                         "VALUE_CATE_LINK"  => $pc_link,
                    ));
                }
            }
        }
    }
    function products_search(){
        global $db,$tpl,$cms_cfg,$ws_array,$TPLMSG,$main;
        if(trim($_REQUEST["kw"])!=""){
            $tpl->newBlock( "TAG_PRODUCTS_SEARCH" );
            //產品管理列表
            $sql="select p.*,pc.pc_name from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p.p_id > '0' and p.p_status=1";
            //附加條件
            $and_str .= " and (p.p_name like '%".$_REQUEST["kw"]."%' or p.p_spec like '%".$_REQUEST["kw"]."%' or p.p_character like '%".$_REQUEST["kw"]."%' or p.p_desc like '%".$_REQUEST["kw"]."%')";
            $sql .= $and_str;
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records    = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="products.php?func=search&kw=".$_REQUEST["kw"];
            $page=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records);
            //重新組合包含limit的sql語法
            $sql=$main->sqlstr_add_limit($this->op_limit,$_REQUEST["nowp"],$sql);
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $tpl->assignGlobal( array("MSG_NAME"  => $TPLMSG['PRODUCT_NAME'],
                                      "MSG_SUBJECT"  => $TPLMSG['SUBJECT'],
                                      "MSG_MODE" => $TPLMSG['MANAGE_CATE'],
                                      "MSG_CATE" => $TPLMSG['PRODUCT']."&nbsp;".$TPLMSG['CATE'],
                                      "VALUE_KW" => $_REQUEST["kw"],
                                      "VALUE_TOTAL_BOX" => $rsnum,
            ));
            //產品列表
            $i=$page["start_serial"];
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "PRODUCTS_SEARCH_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow1'");
                }
                $tpl->assign( array("VALUE_PC_ID"  => $row["pc_id"],
                                    "VALUE_P_ID"  => $row["p_id"],
                                    "VALUE_P_NAME" => $row["p_name"],
                                    "VALUE_P_SERIAL" => $i,
                                    "VALUE_PC_NAME"  => ($row["pc_name"])?$row["pc_name"]:$TPLMSG['NO_CATE'],
                                    "VALUE_PC_LINK" => $cms_cfg["base_root"]."products.php?func=p_list&pc_parent=".$row["pc_id"],
                                    "VALUE_P_LINK" => $cms_cfg["base_root"]."products.php?func=p_detail&p_id=".$row["p_id"]."&pc_parent=".$row["pc_id"],
                ));
            }
            $tpl->gotoBlock("TAG_PRODUCTS_SEARCH");
            if($i==0){
                $tpl->assignGlobal("MSG_NO_DATA",$TPLMSG['NO_DATA']);
            }else{
                $tpl->newBlock( "PAGE_DATA_SHOW" );
                $tpl->assign( array("VALUE_TOTAL_RECORDS"  => $page["total_records"],
                                    "VALUE_TOTAL_PAGES"  => $page["total_pages"],
                                    "VALUE_PAGES_STR"  => $page["pages_str"],
                                    "VALUE_PAGES_LIMIT"=>$this->op_limit
                ));
                if($page["bj_page"]){
                    $tpl->newBlock( "PAGE_BACK_SHOW" );
                    $tpl->assign( "VALUE_PAGES_BACK"  , $page["bj_page"]);
                    $tpl->gotoBlock("PAGE_DATA_SHOW");
                }
                if($page["nj_page"]){
                    $tpl->newBlock( "PAGE_NEXT_SHOW" );
                    $tpl->assign( "VALUE_PAGES_NEXT"  , $page["nj_page"]);
                    $tpl->gotoBlock("PAGE_DATA_SHOW");
                }
            }
        }
    }
    //顯示大圖資料
    function products_show_pic($p_id) {
        global $db,$tpl,$cms_cfg,$main;
        $sql="select * from ".$cms_cfg['tb_prefix']."_products_img where p_id='".$p_id."'";
        $selectrs = $db->query($sql);
        $row2 = $db->fetch_array($selectrs,1);
        $rsnum  = $db->numRows($selectrs);
        if ($rsnum > 0) {
            for($i=1;$i<=$cms_cfg['big_img_limit'];$i++){
                if(trim($row2["p_big_img".$i])!=""){
                    $big_img_array[]=$row2["p_big_img".$i];
                }
            }
            $dimensions["width"]=$cms_cfg['big_img_width'][$this->show_style];
            $dimensions["height"]=$cms_cfg['big_img_height'][$this->show_style];
            $pic_num=count($big_img_array);
            if($pic_num >1){
                $this->template_str="_MULTI";
                $tpl->newBlock("BIG_IMG_MUTI");
                $k=0;
                foreach($big_img_array as $key => $value){
                   $k++;
                   //顯示大圖
                   $tpl->newBlock("BIG_IMG_LIST");
                   if(is_file($_SERVER['DOCUMENT_ROOT'].$cms_cfg["file_root"].$value)){
                       list($width, $height) = getimagesize($_SERVER['DOCUMENT_ROOT'].$cms_cfg["file_root"].$value);
                       $dimensions = $main->resize_dimensions($cms_cfg['big_img_width'][$this->show_style],$cms_cfg['big_img_height'][$this->show_style],$width,$height);
                   }
                   $tpl->assign("VALUE_P_BIG_IMG",$cms_cfg["file_root"].$value);
                   $tpl->assign("VALUE_P_BIG_IMG_W",$dimensions["width"]);
                   $tpl->assign("VALUE_P_BIG_IMG_H",$dimensions["height"]);
                   $tpl->assign("VALUE_P_BIG_IMG_SERIAL",$k);
                   //顯示小圖
                   $tpl->newBlock("SMALL_IMG_LIST");
                   $tpl->assign("TAG_CURRENT", ($k==1)? "current" : "normal");
                   $tpl->assign("VALUE_P_SMALL_IMG",$cms_cfg["file_root"].$value);
                   $tpl->assign("VALUE_P_SMALL_IMG_SERIAL",$k);
                }
            }else{
                //顯示預設單張大圖
                $dimensions["width"]=$cms_cfg['single_img_width'];
                $dimensions["height"]=$cms_cfg['single_img_height'];
                $this->template_str="_SINGLE";
                $tpl->newBlock("BIG_IMG_SINGLE");
                if($pic_num==1){
                   if(is_file($_SERVER['DOCUMENT_ROOT'].$cms_cfg["file_root"].$big_img_array[0])){
                       list($width, $height) = getimagesize($_SERVER['DOCUMENT_ROOT'].$cms_cfg["file_root"].$big_img_array[0]);
                       $dimensions = $main->resize_dimensions($cms_cfg['single_img_width'],$cms_cfg['single_img_height'],$width,$height);
                   }
                   $tpl->assign("VALUE_P_BIG_IMG",$cms_cfg["file_root"].$big_img_array[0]);
                }else{
                   $tpl->assign("VALUE_P_BIG_IMG",$cms_cfg['default_preview_pic']);
                }
                $tpl->assign("VALUE_P_BIG_IMG_W",$dimensions["width"]);
                $tpl->assign("VALUE_P_BIG_IMG_H",$dimensions["height"]);
            }
        }
    }
    function select_icon($file_type) {
        global $cms_cfg;
        $type = substr(strtolower($file_type),-3,3);
        $array_type=array("pdf","doc","xls","wmv");
        $icon = (in_array($type,$array_type))?$cms_cfg['base_images']."ws-icon-".$type.".jpg" : $cms_cfg['base_images']."ws-icon-other.jpg";
        return $icon;
    }
    //上下筆區域
    function products_next_previous($p_id,$pc_id,$p_sort){
        global $db,$tpl,$cms_cfg;
        if(strtolower($cms_cfg['sort_pos'])=="asc"){
            $pre_sql="select p.p_sort,p.pc_id,p.p_id,p.p_name,p.p_seo_filename,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id
                where p.pc_id='".$pc_id."' and p.p_id <> '".$p_id."' and p.p_status=1 and p.p_sort <= '".$p_sort."' order by p.p_sort desc limit 0,1 ";
            $next_sql="select p.p_sort,p.pc_id,p.p_id,p.p_name,p.p_seo_filename,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id
                where p.pc_id='".$pc_id."' and p.p_id <> '".$p_id."' and p.p_status=1 and p.p_sort >= '".$p_sort."' order by p.p_sort limit 0,1 ";
        }else{
            $next_sql="select p.p_sort,p.pc_id,p.p_id,p.p_name,p.p_seo_filename,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id
                where p.pc_id='".$pc_id."' and p.p_id <> '".$p_id."' and p.p_status=1 and p.p_sort <= '".$p_sort."' order by p.p_sort desc limit 0,1 ";
            $pre_sql="select p.p_sort,p.pc_id,p.p_id,p.p_name,p.p_seo_filename,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id
                where p.pc_id='".$pc_id."' and p.p_id <> '".$p_id."' and p.p_status=1 and p.p_sort >= '".$p_sort."' order by p.p_sort limit 0,1 ";
        }
        $selectrs = $db->query($pre_sql);
        $row = $db->fetch_array($selectrs,1);
        $rsnum  = $db->numRows($selectrs);
        if ($rsnum > 0) {
//            if($this->ws_seo){
//                $dirname=(trim($row["pc_seo_filename"]))?$row["pc_seo_filename"]:"products";
//                if(trim($row["p_seo_filename"]) !=""){
//                    $p_link=$cms_cfg["base_root"].$dirname."/".$row["p_seo_filename"].".html";
//                }else{
//                    $p_link=$cms_cfg["base_root"].$dirname."/"."products-".$row["p_id"]."-".$row["pc_id"].".html";
//                }
//            }else{
//                $p_link=$cms_cfg["base_root"]."products.php?func=p_detail&p_id=".$row["p_id"]."&pc_parent=".$row["pc_id"];
//            }
            $p_link = $this->get_link($row,true);
            $tpl->assignGlobal("TAG_PREVIOUS_PRODUCT","<a href='".$p_link."'><img src=\"".$cms_cfg['base_images'].$cms_cfg['language']."_prev.jpg\" border=\"0\" /></a>");
        }
        $selectrs = $db->query($next_sql);
        $row = $db->fetch_array($selectrs,1);
        $rsnum  = $db->numRows($selectrs);
        if ($rsnum > 0) {
//            if($this->ws_seo){
//                $dirname=(trim($row["pc_seo_filename"]))?$row["pc_seo_filename"]:"products";
//                if(trim($row["p_seo_filename"]) !=""){
//                    $p_link=$cms_cfg["base_root"].$dirname."/".$row["p_seo_filename"].".html";
//                }else{
//                    $p_link=$cms_cfg["base_root"].$dirname."/"."products-".$row["p_id"]."-".$row["pc_id"].".html";
//                }
//            }else{
//                $p_link=$cms_cfg["base_root"]."products.php?func=p_detail&p_id=".$row["p_id"]."&pc_parent=".$row["pc_id"];
//            }
            $p_link = $this->get_link($row,true);
            $tpl->assignGlobal("TAG_NEXT_PRODUCT","<a href='".$p_link."'><img src=\"".$cms_cfg['base_images'].$cms_cfg['language']."_next.jpg\" border=\"0\" /></a>");
        }
    }
    //相關產品
    function related_products($p_id_str,$pc_id){
        global $db,$cms_cfg,$tpl;
        if(trim($p_id_str)!=""){
            $sql="select p.p_id,p.p_name,p.p_name_alias,p.p_small_img,p.p_seo_filename,pc.pc_name,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id
            where p.p_id in (".$p_id_str.") and p.p_status='1' order by rand()";
        }else{
            $sql="select p.p_id,p.p_name,p.p_name_alias,p.p_small_img,p.p_seo_filename,pc.pc_name,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id
            where p.pc_id='".$pc_id."' and p.p_status='1' order by rand() limit 0,8";
        }
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $rsnum  = $db->numRows($selectrs);
        if ($rsnum > 0) {
            $tpl->newBlock("JS_IMAGE_FLOW");
            $tpl->newBlock("RELATED_PRODUCTS_ZONE");
            while($row = $db->fetch_array($selectrs,1)){
                $tpl->newBlock("RELATED_PRODUCTS");
//                if($this->ws_seo){
//                    $dirname=(trim($row["pc_seo_filename"]))?$row["pc_seo_filename"]:"products";
//                    if(trim($row["p_seo_filename"]) !=""){
//                        $p_link=$cms_cfg["base_root"].$dirname."/".$row["p_seo_filename"].".html";
//                    }else{
//                        $p_link=$cms_cfg["base_root"].$dirname."/"."products-".$row["p_id"]."-".$row["pc_id"].".html";
//                    }
//                }else{
//                    $p_link=$cms_cfg["base_root"]."products.php?func=p_detail&p_id=".$row["p_id"]."&pc_parent=".$row["pc_id"];
//                }
                $p_link = $this->get_link($row,true);
                $p_img=(trim($row["p_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["p_small_img"];
                $tpl->assign( array("VALUE_PC_NAME"  => $row["pc_name"],
                                    "VALUE_P_ID"  => $row["p_id"],
                                    "VALUE_P_NAME" => $row["p_name"],
                                    "VALUE_P_NAME_ALIAS" => $row["p_name_alias"],
                                    "VALUE_P_LINK" => $p_link,
                                    "VALUE_P_SMALL_IMG" => $p_img,
                ));
            }
        }
    }
    /*由$row取得該筆記錄的url
     */
    function get_link(&$row,$is_product=false){
        global $cms_cfg;
        $link = "";
        if($is_product){
            if($this->ws_seo){
                $dirname=(trim($row["pc_seo_filename"]))?$row["pc_seo_filename"]:"products";
                if(trim($row["p_seo_filename"]) !=""){
                    $link=$cms_cfg["base_root"].$dirname."/".$row["p_seo_filename"].".html";
                }else{
                    $link=$cms_cfg["base_root"].$dirname."/"."products-".$row["p_id"]."-".$row["pc_id"].".html";
                }
            }else{
                $link=$cms_cfg["base_root"]."products.php?func=p_detail&p_id=".$row["p_id"]."&pc_parent=".$row["pc_id"];
            }      
        }else{
            if($this->ws_seo){
                if(trim($row["pc_seo_filename"]) !=""){
                    //$dirname=$row["pc_seo_filename"];
                    $link=$cms_cfg["base_root"].$row["pc_seo_filename"].".htm";
                }else{
                    //$dirname=$row["pc_id"];
                    $link=$cms_cfg["base_root"]."category-".$row["pc_id"].".htm";
                }
            }else{
                $link=$cms_cfg["base_root"]."products.php?func=p_list&pc_parent=".$row["pc_id"];
            }
        }
        return $link;                  
    }    	
}
?>