<?php
/*
 * 用來取得圖片檔名及縮圖尺寸
 * 透過Model_Image::factory()取得物件，可傳入寬度及高度
 * ============
 * 範例:
 * 取得物件
 *  $imgHandler = Model_Image::factory(150,150);
 * 產生縮圖資訊
 * $imgInfo = $imgHandler->parse('PAHT_TO_IMAGE');
 * $imgInfo = $imgHandler->parse('PAHT_TO_IMAGE','medium');
 * ============
 * 重新指定縮圖尺寸
 * $imgHandler->setDimension(200,200);
 * ============
 * 取得原圖尺寸路徑
 * $filename = $imgHandler->getOriginImg();
 * ============
 * 取得指定格式圖檔路徑
 * $filename = $imgHandler->getTypedImg('big');
 * ============
 * 取得所有可用格式圖檔路徑
 * $filename_array = $imgHandler->getTypedImg();
 * ============
 * 縮圖資訊$imgInfo結構:
 * $imgInfo[0] 圖檔路徑
 * $imgInfo['width'] 等比縮圖寬度
 * $imgInfo['height'] 等比縮圖寬度
 * 
 * @author 俊信 <chunhsin@allmarketing.com.tw>
*/

class Model_Image {

    static protected $instance;
    static protected $imageType = array('small','medium','big');
    protected $width;
    protected $height;
    protected $active_file;
    protected $parsed_typed_image = array();

    protected function __construct($width=0, $height=0) {
        $this->setDimension($width, $height);
    }
    
    /**
     * 取得model_image物件
     * @param int $width，縮圖寬度
     * @param int $height，縮圖高度
     * @return Model_Image 傳回Model_Image的實體
     */
    static function factory($width=0,$height=0){
        if(is_null(self::$instance)){
            self::$instance = new self($width,$height);
        }else{
            self::$instance->setDimension($width, $height);
        }
        return self::$instance;
    }
    
    /**
      * 設定縮圖尺寸
      * @param type $width 縮圖寬度
      * @param type $height 縮圖高度
      * @author 俊信 <chunhsin@allmarketing.com.tw>
      */
    function setDimension($width, $height) {
        $this->width = $width;
        $this->height = $height;
    }
    
    /**
      * 取得傳入圖檔之檔名及相應等比縮圖尺寸
      * @param string $filename 圖檔檔名，傳入空白就使用預設圖片
      * @param string $type 格式字串，可用字串參考Model_Image::$imageType
      * @return Array 傳回陣列索引 0為檔名，width為縮圖尺寸寬度，height為縮圖尺寸高度
      * @author 俊信 <chunhsin@allmarketing.com.tw>
      */
    function parse($filename,$type='small') {
        $filename = $this->_parse($filename,$type);
        $dimension = App::getHelper('main')->resizeto($filename, $this->width, $this->height);
        return array_merge(array($filename), $dimension);
    }

    /**
      * 取得傳入圖檔之檔名整理過後的結果，檔名為空值，或不存在則傳回預設圖片
      * @param string $filename 圖檔檔名，傳入空白就使用預設圖片
      * @param string $type 格式字串，可用字串參考Model_Image::$imageType
      * @return string 傳回整理過後的檔名
      * @author 俊信 <chunhsin@allmarketing.com.tw>
      */
    protected function _parse($filename,$type) {
        $filename = App::getHelper('main')->file_str_replace($filename);
        $this->active_file = $filename;
        if(!in_array($type,self::$imageType)){
            return $filename;
        }
        //解析格式圖檔路徑
        if(self::$imageType && is_array(self::$imageType)){
            foreach(self::$imageType as $imgType){
                $this->parsed_typed_image[$imgType] = $this->_getTypedImg($filename, $imgType);
            }
        }
        if (empty($filename) || !$this->_exists( App::configs()->file_root . $filename)) {
            $newfilename = App::configs()->default_preview_pic;
        } else {
            $newfilename = $this->parsed_typed_image[$type];
        }
        return $newfilename;
    }
    
    /**
      * 依指定格式取得縮圖，檔名不存在則傳回原來的檔名
      * @param string $filename 原始圖檔檔名
      * @param string $type 格式字串，可用字串參考Model_Image::$imageType
      * @return string 圖片路徑
      * @author 俊信 <chunhsin@allmarketing.com.tw>
      */
    protected function _getTypedImg($filename,$type){
        $typedFilename = $this->_make_typed_filename($filename, $type);
        if($this->_exists( App::configs()->file_root . $typedFilename)){
            return App::configs()->file_root . $typedFilename;
        }else{
            return App::configs()->file_root . $filename;
        }
    }
    
    /**
      * 取得現有圖片的其他格式圖片
      * @param string $type 格式字串，可用字串參考Model_Image::$imageType
      * @return stirng 圖片路徑
      * @author 俊信 <chunhsin@allmarketing.com.tw>
      */
    function getTypedImg($type=""){
        if($type){
            if(in_array($type,self::$imageType)){
                return $this->parsed_typed_image[$type];
            }
        }else{
            return $this->parsed_typed_image;
        }
    }
    
    /**
      * 產生帶格式化後的圖片路徑
      * @param string $filename 原始檔名
      * @param string $type 格式字串，可用字串參考Model_Image::$imageType
      * @return string 傳回原始檔名格式化的路徑
      * @author 俊信 <chunhsin@allmarketing.com.tw>
      */
    protected function _make_typed_filename($filename,$type){
        $path = dirname($filename);
        $basename = basename($filename);
        return $path . "/_" . $type . "_/" . $basename;
    }
    
    /**
      * 檢查傳入檔名是否存在
      * @param string $filename 圖檔檔名
      * @return boolean true or false
      * @author 俊信 <chunhsin@allmarketing.com.tw>
      */
    protected function _exists($filename){
        return (file_exists($_SERVER['DOCUMENT_ROOT'] . $filename))?true:false;
    }
    
    /**
      * 取得原始圖檔路徑，空值時，傳回預設圖片
      * @return string 圖檔路徑
      * @author 俊信 <chunhsin@allmarketing.com.tw>
      */
    function getOriginImg(){
        return $this->active_file? App::configs()->file_root . $this->active_file : App::configs()->default_preview_pic;
    }

}
