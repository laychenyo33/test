<?php
class Pagination_Abstract {
    protected $query_resource; //query資源
    protected $rows_nums = 0; //總記錄數
    protected $page_records = 20;//每頁記錄數
    protected $pages_nums = 0;  //總頁數   
    protected $current_page = 1;//目前頁數
    protected $page_var = 'nowp'; //頁數變數名
    protected $query_url = ''; //分頁網址
    protected $url_params;
    //    樣式設定
    protected $link_current_wrapper = "<span class='current'>%s</span>";
    protected $first_page_class = "first";
    protected $last_page_class = "last";
    protected $page_link_class = "link";
    //樣版
    protected $template_path;
    protected $template_ext = '.html';
    protected $pager_template;
    
    
    function __construct($query_resource,$options = array()) {
        if(is_resource($query_resource)){
            $this->query_resource = $query_resource;
        }else{
            throw new Exception("assigned resource is not valid!");
        }
        //設定樣版
        $this->template_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR ;
        $tmp = array_pop(explode("_",get_class($this)));
        $this->pager_template = strtolower($tmp);
        $this->query_url = $_SERVER['REQUEST_URI'];
        $this->setOptions($options);
        $this->init();
    }
    
    function setOptions($options){
        if(is_array($options) && !empty($options)){
            foreach($options as $k => $v ){
                if(property_exists($this, $k)){
                    $this->$k = $v;
                }
            }
        }
    }
    
    protected function init(){
        //取得總記錄筆數
        $this->rows_nums = App::getHelper('db')->numRows($this->query_resource);        
        $this->pages_nums = ceil($this->rows_nums / $this->page_records);
        $this->current_page = !empty($_GET[$this->page_var])?$_GET[$this->page_var] : $this->current_page;
    }
    
    function getDataRows(){
        return $this->rows_nums;
    }
    
    //取得分頁記錄
    function getDataList(){
        $dataList = array();
        if($this->rows_nums>0){
            if($this->rows_nums > (($this->current_page-1) * $this->page_records)){
                $rowId = ($this->current_page - 1) * $this->page_records ;
            }else{
                $rowId = 0 ;
            }
            App::getHelper('db')->seek($rowId,$this->query_resource);
            $i=0;
            $offset = $this->getCurPageOffset();
            while($row = App::getHelper('db')->fetch_array($this->query_resource,1)){
                $i++;
                $dataIndex = $offset+$i;
                $dataList[$dataIndex] = $row;
                if($i==$this->page_records)break;
            }
        }
        return $dataList;
    }
    
    //取得分頁連結
    function getPagination(){
        throw new Exception('please override this method!');
    }
    
    protected function getUrlParams(){
        if($this->url_params===null){
            $parsed_url = parse_url($this->query_url);
            parse_str($parsed_url['query'],$query);
            unset($query[$this->page_var]);
            foreach($query as $k=>$v){
                if($v==='')
                    unset($query[$k]);
            }
            $parsed_url['query'] = $query;
            $this->url_params = $parsed_url;
        }
        return $this->url_params;
    }
    
    protected function getBaseUrl(){
        $parsed_url = $this->getUrlParams();
        return $parsed_url['path'];
    }
    
    protected function format_url($url_params){
        return $url_params['path'] . (($url_params['query'])?  "?" . http_build_query($url_params['query']) : '');
    }
    
    protected function makePageLink($params=array()){
        $parsed_url = $this->getUrlParams();
        $parsed_url['query'] = array_merge($parsed_url['query'],$params);
        return $this->format_url($parsed_url);
    }
    
    protected function getPageLink($pageId,array $options=array()){
        $lnkOptions = array('class'=>$this->page_link_class);
        if(!empty($options)){
            foreach($options as $k => $v){
                if(isset($lnkOptions[$k])){
                    $lnkOptions[$k] .= " ".$v;
                }else{
                    $lnkOptions[$k] = $v;
                }
            }
        }
        if($pageId>0){
            $pageId = intval($pageId);
            if(isset($lnkOptions['label'])){
                $label = $lnkOptions['label'];
                unset($lnkOptions['label']);
            }else{
                $label = $pageId;
            }
            
            if($pageId==1){
                $url = $this->makePageLink();
            }else{
                $params[$this->page_var] = $pageId;
                $url = $this->makePageLink($params);
            }
            return App::getHelper("main")->mk_link($label,$url,$lnkOptions);   
        }
    }
    
    protected function getFirstPageLink(){
        return $this->getPageLink(1, array('class'=>$this->first_page_class));
    }
    
    protected function getLastPageLink(){
        return $this->getPageLink($this->pages_nums, array('class'=>$this->last_page_class));
    }
    //取得目前頁數的第一筆記錄的索引
    protected function getCurPageOffset(){
        return ($this->current_page-1)*$this->page_records;
    }
    
    function getCurrentWapper($page_id){
        return sprintf($this->link_current_wrapper,$page_id);
    }

    function getTemplate(){
        $tpl = new TemplatePower($this->template_path . $this->pager_template . $this->template_ext  );
        $tpl->prepare();
        return $tpl;
    }    
}
