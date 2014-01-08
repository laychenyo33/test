<?
/*
回傳XML
<rdf:RDF 
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:dcq="http://purl.org/dc/terms/"
	xmlns="http://www.skype.com/go/skypeweb"
>
 <Status rdf:about="urn:skype:skype.com:skypeweb/1.1">
  <statusCode
	rdf:datatype="http://www.skype.com/go/skypeweb"
	>1</statusCode>
  <presence xml:lang="NUM">1</presence>
  <presence xml:lang="en">Offline</presence>
  <presence xml:lang="fr">Déconnecté</presence>
  <presence xml:lang="de">Offline</presence>
  <presence xml:lang="ja">オフライン</presence>
  <presence xml:lang="zh-cn">離線</presence>
  <presence xml:lang="zh-tw">脱机</presence>
  <presence xml:lang="pt">Offline</presence>
  <presence xml:lang="pt-br">Offline</presence>
  <presence xml:lang="it">Non in linea</presence>
  <presence xml:lang="es">Desconectado</presence>
  <presence xml:lang="pl">Niepodłączony</presence>
  <presence xml:lang="se">Offline</presence>
 </Status>
</rdf:RDF>
回傳結果的xml物件
SimpleXMLElement Object
(
    [Status] => SimpleXMLElement Object
        (
            [statusCode] => 1
            [presence] => Array
                (
                    [0] => 1
                    [1] => Offline
                    [2] => Déconnecté
                    [3] => Offline
                    [4] => オフライン
                    [5] => 離線
                    [6] => 脱机
                    [7] => Offline
                    [8] => Offline
                    [9] => Non in linea
                    [10] => Desconectado
                    [11] => Niepodłączony
                    [12] => Offline
                )
        )
)
/***************************************
Possible status  values:
 NUM        TEXT                DESCRIPTION
* 0     UNKNOWN             Not opted in or no data available. 
* 1     OFFLINE                 The user is Offline 
* 2     ONLINE                  The user is Online 
* 3     AWAY                    The user is Away 
* 4     NOT AVAILABLE       The user is Not Available 
* 5     DO NOT DISTURB  The user is Do Not Disturb (DND) 
* 6     INVISIBLE               The user is Invisible or appears Offline 
* 7     SKYPE ME                The user is in Skype Me mode
****************************************/
class SkypeChecker{
    protected $username;
    protected $statusXML;
    function __construct($username) {
        $this->username = $username;
        $this->load_xml();
    }
    function load_xml(){
        $url = "http://mystatus.skype.com/".$this->username.".xml";
        //getting contents
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        curl_close($curl);
        $this->statusXML = simplexml_load_string($data);
    }
    //取得狀態圖片
    function get_status_img($nums=1){
        switch($nums){
            case '2':
                return "http://mystatus.skype.com/".$this->username;
                break;
            case "1":
            default:
                return "http://mystatus.skype.com/smallicon/".$this->username;
                break;
        }
    }
    //取得狀態碼
    function get_status_code(){
        return $this->statusXML->Status->statusCode;
    }
    //取得狀態值，含status code及各種語言的狀態訊息
    function get_status($id){
        return $this->statusXML->Status->presence[$id];
    }
    //取得skype連結
    //可用$action值:chat,call,voicemail
    function get_link($action="chat"){
        return "skype:".$this->username."?".$action;
    }
}
?>