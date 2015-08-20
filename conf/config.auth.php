<?
$cms_cfg['ws_version'] = "cms-ips-v2";
$cms_cfg['ws_level'] = 20;
$cms_cfg['ws_online'] = 0; //0:下線;1:上線
$cms_cfg['ws_activate_mobile'] = 0; //啟用手機版，0:關閉, 1:啟用
$cms_cfg['ws_ismobile'] = 0;  //執行系統為手機版，0:否, 1:是
$cms_cfg["ws_module"]=array(
/*      BASIC(IPB&IPS) SETUP        */
        "ws_aboutus"=>1,            //關於我們(公司簡介)
        "ws_aboutus_au_cate"=>0,    //關於我們(公司簡介)設為獨立類別
        "ws_aboutus_au_cate_input"=>0,    //關於我們(公司簡介)設為獨立類別輸入區塊
        "ws_aboutus_au_subcate"=>0,  //關於我們獨立類別次分類
        "ws_aboutus_au_subcate_effect"=>0,  //關於我們獨立類別次分類選單特效
        "ws_video"=>0,            //youtube影片
        "ws_blog"=>0,               //部落格管理(留言版)
        "ws_contactus" =>1,          //聯絡我們
        "ws_contactus_login" =>0,    //聯絡我們需登入
        "ws_contactus_s_style" =>1,  //聯絡我們稱謂樣式1為西式(尊稱在前)，2為中式(尊稱在後)
        "ws_contactus_form_style" =>1,  //聯絡我們表單樣式:1為單欄，2為雙欄
        "ws_contactus_upfiles" =>0,  //聯絡我們表單可附檔上傳
        "ws_contactus_ipmap"   =>0,    //聯絡我們表單記錄ip國家對映
        "ws_contactus_inquiry" =>0,    //聯絡我們表單也顯示產品
        "ws_contactus_position"=>0,    //聯絡我們表單顯示職稱&部門欄位
        "ws_contactus_mail_title_mapto" =>"",  //聯絡我們通知信標題對應語系，留空不使用對應文字，以lang/eng-utf8.php為例，填入eng即可使用
        "ws_comment"           =>0,    //評論管理
        "ws_download"          =>0,    //檔案下載
        "ws_download_thumb"    =>0,    //檔案下載顯示縮圖
        "ws_ebook"=>0,              //電子型錄管理
        "ws_faq"=>0,                //FAQ管理
        "ws_forum"=>0,              //討論區管理
        "ws_gallery"=>0,            //活動剪影
        "ws_gallery_scan_dir"  =>0, //活動剪影，直接從資料夾讀取圖片
        "ws_gallery_update_db" =>0, //活動剪影，從資料夾將圖片匯人資料庫
        "ws_goodlink"=>0,           //好站連結
        "ws_guestbook"=>0,          //留言版管理
        "ws_stores"=>0,             //門市介紹
        "ws_factory"=>0,            //觀光工廠    
        "ws_inquiry"=>1,            //詢問信管理
        "ws_inquiry_type"=>0,       //0=>批次詢問車, 1=>單一詢問車
        "ws_news"=>1,               //最新消息
        "ws_news_unique_cate"=>0,   //最新消息獨立類別，類似au_cate，將特定news類別集合為獨立頁面
        "ws_news_upfiles"=>0,       //最新消息上傳附檔
        "ws_news_nextlink"=>0,      //最新消息詳細頁--上下筆連結
        "ws_new_product"=>0,        //最新產品
        "ws_products"=>1,           //產品管理
        "ws_products_related"=>0,   //產品詳細頁--相關產品
        "ws_products_related_effect"=>1,   //產品詳細頁--相關產品的特效: 0=>原始,1=>chcarousel
        "ws_products_nextlink"=>0,  //產品詳細頁--上下筆連結
        "ws_products_title"=>0,     //自訂產品說明標題(產品敘述、規格、特性) 0 =>預設語系 1 => 自定說明
        "ws_products_application" =>0, //產品應用領域
        "ws_application_cates"    =>0, //產品應用領域用在分類
        "ws_application_products" =>1, //產品應用領域用在產品    
        "ws_products_desc_style"  =>0, //前台產品敘述的格式，0是預設樣式，1是domtab,
        "ws_products_mv"          =>0, //產品影片,
        "ws_products_mv_youtube"  =>0, //產品影片，只適用youtube影片,
        "ws_products_mv_link"     =>0, //產品影片，連到影片網站觀看,
        "ws_products_upfiles"     =>0, //產品附檔,
        "ws_products_ca"=>0,           //產品認證標章管理
        "ws_products_info_fields" =>0, //額外的產品敘述欄位數量
        "ws_products_preview"     =>1, //產品前台預覽功能
        "ws_products_custom_inquiry" =>0, //產品自訂頁詢價
        "ws_products_collect"     =>0, //登入會員產品收藏記錄
        "ws_products_stocks"      =>0, //產品庫存機制
        "ws_pageview_history"     =>0, //記錄瀏覽歷程

/*      IPC SETUP                   */
        "ws_ad"=>1,                     //廣告管理(IPC專用)
        "ws_bonus"=>0,                  //紅利點數(IPC專用)
        "ws_epaper"=>0,                 //電子報管理
        "ws_epaper_attach_products"=>1, //電子報夾帶產品列表
        "ws_epaper_queue"=>1,           //電子報使用佇列發送
        "ws_order"=>0,                  //訂單管理
        "ws_order_cancel" => 0,         //新訂單可由消費者自行取消
        "ws_order_export" => 1,         //匯出訂單為excel，需另外加入phpexcel類別在class資料夾
        "ws_rid_order"    => 0,         //啟用美安記錄
        "ws_multi_shipprice"=>0,        //依訂單金額區段計算運費
        "ws_multi_chargefee"=>0,        //依訂單金額區段計算手續費
        "ws_multi_discount" =>0,        //依訂單金額區段使用折扣率
        "ws_temp_store"     =>0,        //商品寄放功能
        "ws_service"=>0,                //服務條款(IPC專用)
        "ws_vaccount"=>0,               //台銀虛擬帳號

/*      MEMBER SETUP                   */
        "ws_member"=>0,                 //會員管理
        "ws_member_msg" => 0,           //會員公告
        "ws_member_manipulate"=>1,      //會員資料匯出匯入
        "ws_member_country"   =>0,      //會員表單顯示國家下拉式選單
        "ws_member_download"  =>0,      //會員下載
        "ws_member_download_on"   => "", //會員下載依類別cate或會員member，預設是類別(cate)，留空也是類別
        "ws_member_show_discount" => 0, //後台會員類別不顯示折扣欄位
        "ws_member_multi_cate"    => 0, //會員使用多重類別
        "ws_member_join_validation"  => "", //會員加入驗證，留空不驗證,manual是手動驗證(驗證預設動作),email是指驗證email
        "ws_member_company"       => 1, //會員公司欄位
        "ws_member_social_login"  => '', //啟用社群登入工具，目前可用: fb，留空不啟用，啟用後，登入表單會顯示社群工具登入按鈕
        "ws_cart_login"=>0,             //購物車或詢價車是否需要會員登入
//        'ws_shopping_cart_module' => 'cart+allpay.index',  //購物車功能模組，留空使用預設值，以+組合字串，+前是購物車模組資料夾，+後是使用的類別路徑
        'ws_shopping_cart_module' => '',  //購物車功能模組，留空使用預設值，以+組合字串，+前是購物車模組資料夾，+後是使用的類別路徑
        "ws_cart_spec"=>0,              //購物車帶規格欄位
        "ws_cart_gift"=>0,              //購物贈品機制
        "ws_cart_plus_shopping"=>0,     //加價購機制
        "ws_delivery_timesec"=>0,       //購物車或詢價車是否顯示配送欄位
        "ws_download_login"=>0,         //檔案下載是否需要會員登入
        "ws_new_product_login"=>0,      //最新產品是否需要會員登入

/*      OTHER SETUP                 */
        "ws_admin"=>1,              //後台管理員管理
        "ws_im_msn"=>0,             //MSN帳號
        "ws_im_skype"=>0,           //SKYPE帳號
        "ws_left_main_au"=>1,       //左方menu顯示Aboutus項目
        "ws_left_main_st"=>0,       //左方menu顯示Service項目
        "ws_left_main_pc"=>1,       //左方menu顯示產品主分類
        "ws_left_sub_pc"=>1,        //左方menu顯示產品次分類
        "ws_left_products" =>0,     //左方menu顯示產品
        "ws_listpage_cart"=>0,      //產品列表直接勾選inquiry項目
        "ws_pop_bigimg"=>1,         //產品內頁小圖,點選後效果(0,無效果,1:彈跳出視窗顯示大圖,2:局部放大)
        "ws_sitemap_product"=>1,    //sitemap 是否顯示產品連結
        "ws_security"=>1,           //驗証碼(聯絡我們、留言版)
        "ws_country"=>1,            //國家下拉選單(會員,聯絡我們,詢價車,購物車)
        "ws_address_type"=>'tw',    //表單地址欄位格式，tw是使用台灣區域，其餘為單一地址欄位
        "ws_left_menu_effects"=>1,  //左方產品選單下拉開合特效
        "ws_left_menu_type"=>1,     //0=>over menu,1=>click menu (左方menu顯示產品次分類時啟用)
        "ws_on_contextmenu"=>0,     //禁滑鼠右鍵
        "ws_on_copy"=>0,            //禁複製
        "ws_on_selectstart"=>0,     //禁選擇
        "ws_on_dragstart"=>0,       //禁拖拉
        "ws_statistic"=>0,          //統計報表
        "ws_sysconfig"=>1,          //系統設定
        "ws_index_banner"=>0,       //自訂首頁banner
        "ws_seo"=>1,                //0=>取消rewrite , 1=>啟用rewrite
        "ws_version"=>"ips",        //系統版本:IPS,IPB,IPC
        "ws_wysiwyg"=>"tinymce"     //tinymce 編輯器
)
?>