<?php
$ws_array = array();
$ws_array["lang_version"]=array("cht"=>$TPLMSG["LANG_CHT"],"chs"=>$TPLMSG['LANG_CHS'],"eng"=>$TPLMSG["LANG_ENG"],"jap"=>$TPLMSG["LANG_JAP"],"spa"=>$TPLMSG['LANG_SPA'],'fre'=>$TPLMSG['LANG_FRE'],'ger'=>$TPLMSG['LANG_GER']);
$ws_array["order_status"]=array(0=>$TPLMSG["ORDER_NEW"],1=>$TPLMSG["ORDER_DEALING"],2=>$TPLMSG["ORDER_PRODUCTS_SEND"],3=>$TPLMSG["ORDER_COMPLETED"],9=>$TPLMSG["ORDER_CANCEL"],10=>$TPLMSG["ORDER_REJECT"],20=>$TPLMSG['AUTHORIZE_TERMINATE'],21=>$TPLMSG['AUTHORIZE_FAILED']);
$ws_array["order_paid_status"]=array(0=>$TPLMSG["ORDER_UNPAID"],1=>$TPLMSG["ORDER_PAID"]);
$ws_array["inquiry_status"]=array(0=>$TPLMSG["REPLY_NO"],1=>$TPLMSG["REPLY_YES"]);
$ws_array["contactus_status"]=array(0=>$TPLMSG["REPLY_NO"],1=>$TPLMSG["REPLY_YES"]);
$ws_array["yesno_status"]=array(0=>$TPLMSG["NO"],1=>$TPLMSG["YES"]);
$ws_array['default_status'] = array($TPLMSG['STATUS_OFF'],$TPLMSG['STATUS_ON']);
$ws_array["images_type"]=array(".jpg",".gif",".png",".bmp");
$ws_array["cart_type"]=array(0=>$TPLMSG['CART_INQUIRY'],1=>$TPLMSG['CART_SHOPPING']);
$ws_array["download_type"]=array(0=>$TPLMSG['DOWNLOAD_AFTER_READ'],1=>$TPLMSG['DOWNLOAD_DIRECTLY']);
$ws_array["service_term"]=array("st_contactus_term"=>$TPLMSG["CONTACTUS_TERM"],
                                "st_join_member_mail"=>$TPLMSG["JOIN_MEMBER_MAIL"],
                                "st_payment_term" => $TPLMSG["PAYMENT_TERM"],
                                "st_privacy_policy" => $TPLMSG["PRIVACY_POLICY"],
                                "st_service_term" => $TPLMSG["SERVICE_TERM"],
                                "st_shipping_term" => $TPLMSG["SHIPPING_TERM"],
                                "st_shopping_term" => $TPLMSG["SHOPPING_TERM"],
                                "st_inquiry_mail" => $TPLMSG["INQUIRY_MAIL"],
                                "st_order_mail" => $TPLMSG["ORDER_MAIL"]
                                );
$ws_array["service_term_left_cate"]=array("st_payment_term" => $TPLMSG["PAYMENT_TERM"],
                                          "st_privacy_policy" => $TPLMSG["PRIVACY_POLICY"],
                                          "st_service_term" => $TPLMSG["SERVICE_TERM"],
                                          "st_shipping_term" => $TPLMSG["SHIPPING_TERM"],
                                          "st_shopping_term" => $TPLMSG["SHOPPING_TERM"],
                                         );
$ws_array["front_page"]=array("index.html" => $TPLMSG["CUSTOM_INDEX_PAGE"],
                              "main.php" => $TPLMSG["SYSTEM_INDEX_PAGE"],
                              "aboutus.php"=>$TPLMSG["ABOUT_US"],
                              "news.php"=>$TPLMSG["NEWS"],
                              "products.php" =>$TPLMSG["PRODUCT_LIST"],
                              "faq.php" => $TPLMSG["FAQ"]
                             );

$ws_array["contactus_cate"]=array( 1 =>$TPLMSG['CONTACT_US_PRODUCTS'],2 =>$TPLMSG['CONTACT_ERROR_REPORTING'],3 =>$TPLMSG['CONTACT_SUGGESTION'],10=>$TPLMSG['CONTACT_US_OTHERS']);
$ws_array["contactus_s"]=array( 1 =>$TPLMSG['CONTACT_S_1'],2 =>$TPLMSG['CONTACT_S_2'],3 =>$TPLMSG['CONTACT_S_3']);

$ws_array['product_quantity_discount_options'] = array($TPLMSG['NO'],$TPLMSG['YES']);
$ws_array["epaper_order_cate"]=array( 1 =>$TPLMSG['EPAPER_ORDER_NORMAL'],2 =>$TPLMSG['EPAPER_ORDER_MEMBER'],3 =>$TPLMSG['EPAPER_ORDER_FIRST']);
$ws_array["ad_cate"]=array( 1 =>$TPLMSG['AD_INDEX_BANNER'],2 =>$TPLMSG['AD_INSIDE_BANNER'],3 =>$TPLMSG['AD_INSIDE_LEFT'],4 =>$TPLMSG['AD_INSIDE_RIGHT'],5=>$TPLMSG['AD_PRODUCTS'],6=>$TPLMSG['AD_INDEX_POPUP']);
if($cms_cfg['ws_module']['ws_shopping_cart_module']){
    if($allpay->allpay_switch && !empty($allpay->all_cfg["allpay_type"])){
        $ws_array["payment_type"] = array( 2 => $TPLMSG["PAYMENT_CASH_ON_DELIVERY"] ) + $allpay->all_cfg["allpay_type"];
    }else{
        $ws_array["payment_type"] = array( 1 => $TPLMSG["PAYMENT_ATM"] , 2 => $TPLMSG["PAYMENT_CASH_ON_DELIVERY"] );
    }    
}else{
    $ws_array["payment_type"]=array( 1 =>$TPLMSG["PAYMENT_ATM"],2 =>$TPLMSG["PAYMENT_CASH_ON_DELIVERY"]);
}
$ws_array["shippment_type"]=array( 1=>$TPLMSG['SHIPPMENT_1'], 2=>$TPLMSG['SHIPPMENT_2'], /*3=>$TPLMSG['SHIPPMENT_3']*/);
if($cms_cfg['ws_module']['ws_shopping_cart_module']){
    $ws_array['invoice_type'] = array(1=>$TPLMSG['DUP_INVOICE'] ,2=>$TPLMSG['TRI_INVOICE'],3=>$TPLMSG['DONATE']);
}else{
    $ws_array['invoice_type'] = array(2=>$TPLMSG['INVOICE_TYPE_2'],3=>$TPLMSG['INVOICE_TYPE_3']);
}
$ws_array["deliery_timesec"]=array( 0 =>"不指定",1 =>"中午前",2=>"12時-17時",3=>"17時-20時");
$ws_array['week_day'] = array($TPLMSG['W_SUNDAY'] ,$TPLMSG['W_MONDAY'],$TPLMSG['W_TUESDAY'],$TPLMSG['W_WEDNESDAY'],$TPLMSG['W_THRUSDAY'],$TPLMSG['W_FRIDAY'],$TPLMSG['W_SATURDAY']);
$ws_array['week_day_short'] = array($TPLMSG['W_SUN'],$TPLMSG['W_MON'],$TPLMSG['W_TUE'],$TPLMSG['W_WED'],$TPLMSG['W_THR'],$TPLMSG['W_FRI'],$TPLMSG['W_SAT'] );
$ws_array["main"]=array("index" => $TPLMSG['HOME'],
                        "aboutus" => $TPLMSG["ABOUT_US"],
                        "products" => $TPLMSG["PRODUCTS"],
                        "application" => $TPLMSG["APPLICATION"],
                        "ebook" => $TPLMSG["EBOOK"],
                        "stores" => $TPLMSG["STORES"],
                        "download" => $TPLMSG["DOWNLOAD"],
                        "news" => $TPLMSG["NEWS"],
                        "video" => $TPLMSG["VIDEO"],
                        "faq" => $TPLMSG["FAQ"],
                        "service" => $TPLMSG["SERVICE_TERM"],
                        "contactus" => $TPLMSG["CONTACT_US"],
                        "guestbook" => $TPLMSG['GUESTBOOK'],
                        "sitemap" => $TPLMSG["SITEMAP"],
                    );
$ws_array["left"]=array("aboutus" => $TPLMSG["ABOUT_US"],
                        "products" => $TPLMSG["PRODUCTS"],
                        "application" => $TPLMSG["APPLICATION"],
                        "ebook" => $TPLMSG["EBOOK"],
                        "stores" => $TPLMSG["STORES"],
                        "download" => $TPLMSG["DOWNLOAD"],
                        "news" => $TPLMSG["NEWS"],
                        "video" => $TPLMSG["VIDEO"],
                        "faq" => $TPLMSG["FAQ"],
                        "service" => $TPLMSG["SERVICE_TERM"],
                        "contactus" => $TPLMSG["CONTACT_US"],
                        "guestbook" => $TPLMSG['GUESTBOOK'],
                        "sitemap" => $TPLMSG["SITEMAP"],
                    );
$ws_array["left_desc"]=array("aboutus" => $TPLMSG["ABOUT_US_CATE_DESC"],
                        "products" => $TPLMSG["PRODUCTS_CATE_DESC"],
                        "application" => $TPLMSG["APPLICATION_CATE_DESC"],
                        "ebook" => $TPLMSG["EBOOK_CATE_DESC"],
                        "stores" => $TPLMSG["STORES_CATE_DESC"],
                        "download" => $TPLMSG["DOWNLOAD_CATE_DESC"],
                        "news" => $TPLMSG["NEWS_CATE_DESC"],
                        "video" => $TPLMSG["VIDEO_CATE_DESC"],
                        "faq" => $TPLMSG["FAQ_CATE_DESC"],
                        "service" => $TPLMSG["SERVICE_CATE_DESC"],
                        "contactus" => $TPLMSG["CONTACT_US_CATE_DESC"],
                        "guestbook" => $TPLMSG['GUESTBOOK_CATE_DESC'],
                        "sitemap" => $TPLMSG["SITEMAP_CATE_DESC"],
                    );
$ws_array['products_info_fields_title'] = array();//例如：array('自訂1','自訂2')
$ws_array['products_info_fields_sort'] = array();//因為內建欄位是3個，所以原則上從4開始編, 例如：array(4,5)
//model類別初始化選項
$ws_array['models_options'] = array(
    'session' => array(
        'modules' => array(
            'cart' => array(
                'translator' => array(
                    'class'   => 'spec',
                    'options' => array('db'=>$db),
                ),
                //'translator' => 'classname',
            ),
        ),
    ),
);
$ws_array['season_month'] = array(
    'label' => array('year'=>'年份','season'=>'季別','month'=>'月別'),
    'season' => array(1=>"第一季",2=>"第二季",3=>"第三季",4=>"第四季"),
    'month' => array( 
        1 => array('label'=>'1月','htmlOptions'=>array('rel'=>1)),
        2 => array('label'=>'2月','htmlOptions'=>array('rel'=>1)),
        3 => array('label'=>'3月','htmlOptions'=>array('rel'=>1)),
        4 => array('label'=>'4月','htmlOptions'=>array('rel'=>2)),
        5 => array('label'=>'5月','htmlOptions'=>array('rel'=>2)),
        6 => array('label'=>'6月','htmlOptions'=>array('rel'=>2)),
        7 => array('label'=>'7月','htmlOptions'=>array('rel'=>3)),
        8 => array('label'=>'8月','htmlOptions'=>array('rel'=>3)),
        9 => array('label'=>'9月','htmlOptions'=>array('rel'=>3)),
        10 => array('label'=>'10月','htmlOptions'=>array('rel'=>4)),
        11 => array('label'=>'11月','htmlOptions'=>array('rel'=>4)),
        12 => array('label'=>'12月','htmlOptions'=>array('rel'=>4)),
    ),
);
$ws_array['download_time_sets'] = array(
    'xkdfkja' => array('label'=>'財務報表','time_fields'=>array('year','season'),'dc_id'=>array(2,1)),
    'xjdfkjb' => array('label'=>'公司年報','time_fields'=>array('year'),'dc_id'=>array(3)),
);
$ws_array['shopping_cond_type'] = array('order'=>'訂單','cate'=>'產品分類','product'=>'產品');
$ws_array["country_array"]=array(
"Afghanistan",
"Albania",
"Algeria",
"Andorra",
"Angola",
"Antigua & Deps",
"Argentina",
"Armenia",
"Australia",
"Austria",
"Azerbaijan",
"Bahamas",
"Bahrain",
"Bangladesh",
"Barbados",
"Belarus",
"Belgium",
"Belize",
"Benin",
"Bhutan",
"Bolivia",
"Bosnia Herzegovina",
"Botswana",
"Brazil",
"Brunei",
"Bulgaria",
"Burkina",
"Burundi",
"Cambodia",
"Cameroon",
"Canada",
"Cape Verde",
"Central African Rep",
"Chad",
"Chile",
"China",
"Colombia",
"Comoros",
"Congo",
"Congo {Democratic Rep}",
"Costa Rica",
"Croatia",
"Cuba",
"Cyprus",
"Czech Republic",
"Denmark",
"Djibouti",
"Dominica",
"Dominican Republic",
"East Timor",
"Ecuador",
"Egypt",
"El Salvador",
"Equatorial Guinea",
"Eritrea",
"Estonia",
"Ethiopia",
"Fiji",
"Finland",
"France",
"Gabon",
"Gambia",
"Georgia",
"Germany",
"Ghana",
"Greece",
"Grenada",
"Guatemala",
"Guinea",
"Guinea-Bissau",
"Guyana",
"Haiti",
"Honduras",
"Hungary",
"Iceland",
"India",
"Indonesia",
"Iran",
"Iraq",
"Ireland {Republic}",
"Israel",
"Italy",
"Ivory Coast",
"Jamaica",
"Japan",
"Jordan",
"Kazakhstan",
"Kenya",
"Kiribati",
"Korea North",
"Korea South",
"Kosovo",
"Kuwait",
"Kyrgyzstan",
"Laos",
"Latvia",
"Lebanon",
"Lesotho",
"Liberia",
"Libya",
"Liechtenstein",
"Lithuania",
"Luxembourg",
"Macedonia",
"Madagascar",
"Malawi",
"Malaysia",
"Maldives",
"Mali",
"Malta",
"Marshall Islands",
"Mauritania",
"Mauritius",
"Mexico",
"Micronesia",
"Moldova",
"Monaco",
"Mongolia",
"Montenegro",
"Morocco",
"Mozambique",
"Myanmar,
 {Burma}",
"Namibia",
"Nauru",
"Nepal",
"Netherlands",
"New Zealand",
"Nicaragua",
"Niger",
"Nigeria",
"Norway",
"Oman",
"Pakistan",
"Palau",
"Panama",
"Papua New Guinea",
"Paraguay",
"Peru",
"Philippines",
"Poland",
"Portugal",
"Qatar",
"Romania",
"Russian Federation",
"Rwanda",
"St Kitts & Nevis",
"St Lucia",
"Saint Vincent & the Grenadines",
"Samoa",
"San Marino",
"Sao Tome & Principe",
"Saudi Arabia",
"Senegal",
"Serbia",
"Seychelles",
"Sierra Leone",
"Singapore",
"Slovakia",
"Slovenia",
"Solomon Islands",
"Somalia",
"South Africa",
"South Sudan",
"Spain",
"Sri Lanka",
"Sudan",
"Suriname",
"Swaziland",
"Sweden",
"Switzerland",
"Syria",
"Taiwan",
"Tajikistan",
"Tanzania",
"Thailand",
"Togo",
"Tonga",
"Trinidad & Tobago",
"Tunisia",
"Turkey",
"Turkmenistan",
"Tuvalu",
"Uganda",
"Ukraine",
"United Arab Emirates",
"United Kingdom",
"United States",
"Uruguay",
"Uzbekistan",
"Vanuatu",
"Vatican City",
"Venezuela",
"Vietnam",
"Yemen",
"Zambia",
"Zimbabwe");
?>