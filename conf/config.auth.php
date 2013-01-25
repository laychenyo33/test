<?
$cms_cfg['ws_version'] = "cms-ips-v2";
$cms_cfg['ws_level'] = 20;
$cms_cfg["ws_module"]=array(
/*      BASIC(IPB&IPS) SETUP        */
        "ws_aboutus"=>1,            //關於我們(公司簡介)
        "ws_video"=>0,            //youtube影片
        "ws_blog"=>0,               //部落格管理(留言版)
        "ws_contactus"=>1,          //聯絡我們
        "ws_contactus_upfiles"=>0,  //聯絡我們表單可附檔上傳
        "ws_contactus_ipmap"=>0,    //聯絡我們表單記錄ip國家對映
        "ws_download"=>0,           //檔案下載
        "ws_ebook"=>1,              //電子型錄管理
        "ws_faq"=>0,                //FAQ管理
        "ws_forum"=>0,              //討論區管理
        "ws_gallery"=>0,            //活動剪影
        "ws_goodlink"=>0,           //好站連結
        "ws_guestbook"=>0,          //留言版管理
        "ws_stores"=>0,             //門市介紹
        "ws_factory"=>0,            //觀光工廠    
        "ws_inquiry"=>1,            //詢問信管理
        "ws_inquiry_type"=>0,       //0=>批次詢問車, 1=>單一詢問車
        "ws_news"=>1,               //最新消息
        "ws_new_product"=>1,        //最新產品
        "ws_products"=>1,           //產品管理
        "ws_products_related"=>1,   //產品詳細頁--相關產品
        "ws_products_nextlink"=>1,  //產品詳細頁--上下筆連結
        "ws_products_title"=>0,     //自訂產品說明標題(產品敘述、規格、特性) 0 =>預設語系 1 => 自定說明
        "ws_products_application" =>1, //產品應用領域
        "ws_application_cates"    =>1, //產品應用領域用在分類
        "ws_application_products" =>0, //產品應用領域用在產品    
        "ws_products_desc_style"  =>0, //前台產品敘述的格式，0是預設樣式，1是domtab,
        "ws_products_mv"          =>0, //產品影片，只適用youtube影片,
        "ws_products_upfiles"     =>1, //產品附檔,

/*      IPC SETUP                   */
        "ws_ad"=>1,                     //廣告管理(IPC專用)
        "ws_bonus"=>0,                  //紅利點數(IPC專用)
        "ws_epaper"=>1,                 //電子報管理
        "ws_epaper_attach_products"=>1, //電子報夾帶產品列表
        "ws_epaper_queue"=>1,           //電子報使用佇列發送
        "ws_order"=>0,                  //訂單管理
        "ws_service"=>0,                //服務條款(IPC專用)
        "ws_vaccount"=>0,               //台銀虛擬帳號

/*      MEMBER SETUP                */
        "ws_member"=>1,             //會員管理
        "ws_member_manipulate"=>0,  //會員資料匯出匯入
        "ws_member_country"   =>1,  //會員表單顯示國家下拉式選單
        "ws_cart_login"=>0,         //購物車或詢價車是否需要會員登入
        "ws_delivery_timesec"=>1,   //購物車或詢價車是否顯示配送欄位
        "ws_download_login"=>0,     //檔案下載是否需要會員登入
        "ws_new_product_login"=>0,  //最新產品是否需要會員登入

/*      OTHER SETUP                 */
        "ws_admin"=>1,              //後台管理員管理
        "ws_im_msn"=>0,             //MSN帳號
        "ws_im_skype"=>0,           //SKYPE帳號
        "ws_left_main_au"=>1,       //左方menu顯示Aboutus項目
        "ws_left_main_st"=>0,       //左方menu顯示Service項目
        "ws_left_main_pc"=>1,       //左方menu顯示產品主分類
        "ws_left_sub_pc"=>1,        //左方menu顯示產品次分類
        "ws_left_products" =>0,     //左方menu顯示產品
        "ws_listpage_cart"=>1,      //產品列表直接勾選inquiry項目
        "ws_pop_bigimg"=>0,         //產品列表小圖,點選後彈跳出視窗顯示大圖
        "ws_sitemap_product"=>1,    //sitemap 是否顯示產品連結
        "ws_security"=>1,           //驗証碼(聯絡我們、留言版)
        "ws_country"=>1,            //國家下拉選單(會員,聯絡我們,詢價車,購物車)
        "ws_left_menu_effects"=>0,  //左方產品選單下拉開合特效
        "ws_left_menu_type"=>1,     //0=>over menu,1=>click menu (左方menu顯示產品次分類時啟用)
        "ws_on_contextmenu"=>0,     //禁滑鼠右鍵
        "ws_on_copy"=>0,            //禁複製
        "ws_on_selectstart"=>0,     //禁選擇
        "ws_statistic"=>0,          //統計報表
        "ws_sysconfig"=>1,          //系統設定
        "ws_index_banner"=>0,       //自訂首頁banner
        "ws_seo"=>1,                //0=>取消rewrite , 1=>啟用rewrite
        "ws_version"=>"ips",        //系統版本:IPS,IPB,IPC
        "ws_wysiwyg"=>"tinymce"     //tinymce 編輯器
)
?>