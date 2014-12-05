<?php
/*
 * 操作範例
$exchanger = new Model_Currencyex('TWD','USD');
$dollar = 655;

echo $exchanger->getExRateMsg();
echo "<hr>";
echo $exchanger->getExMsg($dollar);
*/
class Model_Currencyex {
    //來源貨幣名稱
    protected $from; 
    //結果貨幣名稱
    protected $to;
    //匯率資訊
    protected $rate;
    //查詢匯率uri
    protected $uri;

    function __construct($from,$to) {
        $this->setUri($from, $to);
        $this->getExRate();
    }
    //設定換算來源
    function setUri($from,$to){
        $this->from = $from;
        $this->to = $to;
        $uri = "http://finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s={$from}{$to}=X";
        $this->uri = $uri;
    }
    //取得匯率資訊
    function getExRate(){
        $raw_content = file_get_contents($this->uri);
        $content = explode(',', $raw_content);
        $content[2] = trim(trim($content[2]),'"');
        $content[3] = trim(trim($content[3]),'"');
        $this->rate = $content;
    }
    //依匯率進行幣值換算
    function exchange($amt){
        return $this->rate[1] * $amt;
    }
    //取得匯率敘述
    function getExRateMsg(){
        return $this->from . " : " . $this->to . " = 1 : " . $this->rate[1] . " @ " . $this->rate[2] . " " . $this->rate[3];
    }
    //取得換算結果敘述
    function getExMsg($amt){
        return $amt . $this->getFrom() . " are ".$this->exchange($amt) . $this->getTo();
    }
    //取得來源幣別
    function getFrom(){
        return $this->from;
    }
    //取得結果幣別
    function getTo(){
        return $this->to;
    }
}
