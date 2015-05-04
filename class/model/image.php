<?php

/*
 * 用來取得圖片檔名及縮圖尺寸
 */

class Model_Image {

    static protected $instance;
    protected $width;
    protected $height;

    protected function __construct($width=0, $height=0) {
        $this->setDimension($width, $height);
    }
    
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
      */
    function setDimension($width, $height) {
        $this->width = $width;
        $this->height = $height;
    }
    
    /**
      * 取得傳入圖檔之檔名及相應等比縮圖尺寸
      * @param string $filename 圖檔檔名，傳入空白就使用預設圖片
      * @return Array 傳回陣列索引 0為檔名，width為縮圖尺寸寬度，height為縮圖尺寸高度
      */
    function parse($filename) {
        $filename = $this->_parse($filename);
        $dimension = App::getHelper('main')->resizeto($filename, $this->width, $this->height);
        return array_merge(array($filename), $dimension);
    }

    /**
      * 取得傳入圖檔之檔名整理過後的結果，檔名為空值，或不存在則傳回預設圖片
      * @param string $filename 圖檔檔名，傳入空白就使用預設圖片
      * @return string 傳回整理過後的檔名
      */
    protected function _parse($filename) {
        $filename = App::getHelper('main')->file_str_replace($filename);
        if (empty($filename) || !file_exists($_SERVER['DOCUMENT_ROOT'] . App::configs()->file_root . $filename)) {
            $filename = App::configs()->default_preview_pic;
        } else {
            $filename = App::configs()->file_root . $filename;
        }
        return $filename;
    }

}
