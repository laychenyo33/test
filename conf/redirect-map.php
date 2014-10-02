<?php
/*說明:
 * 陣列索引是比對pattern，不需加^$。
 * 陣列值是比對成功後要轉址的地址。可跨語言版本轉址，新網址前面請不要加 /
 */
return array(
    'cate1-1/prod[4-5].html' => 'cate1-1/prod2.html',
    'xxyy.htm'               => 'cate1-1/12341234.html',
    'cate4233.htm'           => 'cate1.htm',
    'news.html'              => 'cate1.htm',
);