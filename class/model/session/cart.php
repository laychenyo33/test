<?php
class Model_Session_Cart extends Model_Modules {
    protected $handler;
    protected $cart;
    protected $need_charge_fee_payment = array(2);
    protected $translatorPath;
    protected $discounterPath;
    protected $translator;
    protected $discounter = array(
        'quantity' => 'quantity',
    );
    protected $activateStockChecker;
    function __construct($model , $options=array()) {
        $this->handler = $model;
        if(!isset($this->handler->cartContainer)){
            $this->_init_cart();
        }
        $this->cart = &$this->handler->cartContainer;
        $this->translatorPath = dirname(__FILE__) . DIRECTORY_SEPARATOR ."cart" . DIRECTORY_SEPARATOR . "translator" . DIRECTORY_SEPARATOR;
        $this->discounterPath = dirname(__FILE__) . DIRECTORY_SEPARATOR ."cart" . DIRECTORY_SEPARATOR . "discounter" . DIRECTORY_SEPARATOR;
        if(!empty($options) && is_array($options)){
            foreach($options as $name => $value){
                switch($name){
                    case "translator":
                        $this->translator = $value;
                        break;
                    case "discounter":
                        $this->discounter = array_merge($this->discounter,$value);
                        break;
                }
            }
        }
        //襖始化折扣器
        $this->initDiscounter();
        //是否啟用庫存檢查
        $this->activateStockChecker = App::configs()->ws_module->ws_products_stocks;
    }
    //初始化cart
    function _init_cart(){
        $cart_sess = array(
            'cart_info' => array(
                'items' => 0,
            ),
            'products'=>array(
                'raw_id' => array(),
                'amount' => array(),
                'data'   => array(),
                'lists'   => array(),
            ),
        );
        if($this->handler->sc_cart_type==1){
            $cart_sess['cart_info'] = array_merge( $cart_sess['cart_info'] ,array(
                'shipment_type'  => 0,
                'payment_type'   => 0,
                'subtotal_price' => 0,
                'shipping_price' => 0,
                'charge_fee'     => 0,
                'minus_price'    => 0,
                'total_price'    => 0,
            ));
        }
        $this->handler->cartContainer = $cart_sess;
    }    
    //放入購物車
    function put($p_id,$amount,$extra_id=null){
        $this->cart['products']['raw_id'][$p_id]['id'] = $p_id;
        if($extra_id){
            $this->cart['products']['raw_id'][$p_id]['sub'][$extra_id] = $extra_id;
        }
        if($extra_id){
            $base_data_id = $this->combine_id($p_id, 0); //主要檔案內容
            $extra_data_id = $this->combine_id($p_id, $extra_id);
            //檢查庫存
            if($this->activateStockChecker && !$this->stockChecker->check(
                    $p_id,
                    $this->cart['products']['lists'][$extra_data_id]['amount'] + $amount,
                    $extra_id )){
                return false;
            }
            if(!isset($this->cart['products']['data'][$base_data_id])){
                $this->cart['products']['data'][$base_data_id] = $this->_query_product($p_id);
            }
            if(!isset($this->cart['products']['lists'][$extra_data_id])){
                $row = $this->make_extra_data($p_id,$extra_id,$amount);
                $this->cart['products']['lists'][$extra_data_id] = $row;
            }else{
                $this->cart['products']['lists'][$extra_data_id]['amount'] += $amount;
                if($this->handler->sc_cart_type==1){
                    $this->discounter['quantity']->checkout($this->cart['products']['lists'][$extra_data_id]);
                }
            }
            $this->cart['products']['amount'][$extra_data_id]+=$amount;
        }else{
            //檢查庫存
            if(!$this->stockChecker->check(
                    $p_id,
                    $this->cart['products']['lists'][$p_id]['amount'] + $amount )){
                return false;
            }
            if(!isset($this->cart['products']['lists'][$p_id])){
                $prod = $this->query_product($p_id,$amount);
                $this->cart['products']['lists'][$p_id] = $prod;
            }else{
                $this->cart['products']['lists'][$p_id]['amount'] += $amount;
                if($this->handler->sc_cart_type==1){
                    $this->discounter['quantity']->checkout($this->cart['products']['lists'][$p_id]);
                }
            }
            $this->cart['products']['amount'][$p_id]+=$amount;
        }
        $this->count(true);
        if($this->handler->sc_cart_type==1){
            $this->calculate();//累計價格
        }
        return true;
    }
    //更新購物車項目
    function update($p_id,$amount,$extra_id=null){
        //檢查庫存
        if($this->activateStockChecker && !$this->stockChecker->check( $p_id, $amount, $extra_id )){
            return false;
        }
        if($extra_id){
            $p_id = $this->combine_id($p_id, $extra_id);
        }
        $this->cart['products']['lists'][$p_id]['amount'] = $amount;
        if($this->handler->sc_cart_type==1){
            $this->discounter['quantity']->checkout($this->cart['products']['lists'][$p_id]);            
            $this->calculate();//累計價格
        }
        return true;
    }
    //購物車產品品項數目
    function count($reCount=false){
        if($reCount){
            $this->cart['cart_info']['items'] = count($this->cart['products']['lists']);
        } 
        return $this->cart['cart_info']['items'];
    }
    //取得購物車產品資料
    function get_cart_products($p_id=null,$ps_id=null){
        if(empty($p_id)){
            return $this->cart['products']['lists'];
        }else{
            if(!empty($ps_id)){
                $p_id = $this->combine_id($p_id, $ps_id);
            }
            return $this->cart['products']['lists'][$p_id];
        }
    }
    function get_cart_info(){
        return $this->cart['cart_info'];
    }
    function set_shipment_type($shipment_type){
        $this->cart['cart_info']['shipment_type'] = $shipment_type;
        $this->calculate();
    }
    function get_shipment_type(){
        return $this->cart['cart_info']['shipment_type'];
    }
    function set_payment_type($payment_type){
        $this->cart['cart_info']['payment_type'] = $payment_type;
        $this->calculate();
    }
    function get_payment_type(){
        return $this->cart['cart_info']['payment_type'];
    }
    function get_subtotal_price(){
        return $this->cart['cart_info']['subtotal_price'];
    }
    //查詢產品
    function query_product($p_id,$amount){
        $row = $this->_query_product($p_id);
        if($row){
            $row['amount'] = $amount;
            if($this->handler->sc_cart_type==1){
                $row['price'] = $row['p_special_price']?$row['p_special_price']:$row['p_list_price'];
                $this->discounter['quantity']->checkout($row);
            }
            return $row;
        }else{
            throw new Exception('no product data for storing');
        }
    }
    //db操作
    function _query_product($p_id){
        $db = App::getHelper('db');
        $sql="select * from ".$db->prefix("products")." as p left join ".$db->prefix("products_cate")." as pc on p.pc_id=pc.pc_id where p.p_id = '".$p_id."'";
        return $db->query_firstRow($sql,true);
    }
    //計算價格
    function calculate(){
        $cart_subtotal_price = 0;
        $advance_ship_price = false;
        if(!empty($this->cart['products']['lists']) && is_array($this->cart['products']['lists'])){
            foreach($this->cart['products']['lists'] as $index_id => $dataRow){
                $cart_subtotal_price+=$dataRow['subtotal_price'];                      
            }
        }
        $shipping_price = Model_Shipprice::calculate($cart_subtotal_price, $this->cart['cart_info']['shipment_type']);
        $this->cart['cart_info']['shipping_price'] = ($advance_ship_price)?-1:$shipping_price;
        $this->cart['cart_info']['charge_fee'] = (in_array($this->cart['cart_info']['payment_type'],$this->need_charge_fee_payment))?Model_Chargefee::calculate($this->cart['cart_info']['subtotal_price']):0;
        $this->cart['cart_info']['subtotal_price'] = $cart_subtotal_price;
        $this->cart['cart_info']['total_price'] = $this->cart['cart_info']['subtotal_price']+($this->cart['cart_info']['shipping_price']<0?0:$this->cart['cart_info']['shipping_price'])+$this->cart['cart_info']['charge_fee']-$this->cart['cart_info']['minus_price'];
    }
    function empty_cart(){
        $this->_init_cart();
    }
    //產生檔產品資料
    function make_extra_data($p_id,$extra_id,$amount){
        $base_data_id = $this->combine_id($p_id, 0);
        $extra_data_id = $this->combine_id($p_id, $extra_id);
        $row = $this->cart['products']['data'][$base_data_id];
        if($row){
            $row['ps_id'] = $extra_id;
            $row['amount'] = $amount;
            $row = $this->_translator()->translate($row);
            if($this->handler->sc_cart_type==1){
                if(!$row['price']){
                    $row['price'] = $row['p_special_price']?$row['p_special_price']:$row['p_list_price'];
                }
                $this->discounter['quantity']->checkout($row);
            }
            return $row;
        }else{
            throw new Exception('no base data!');
        }
    }
    //移除購物車項目
    function rm($p_id,$extra_id=null){
        if($extra_id){
            unset($this->cart['products']['raw_id'][$p_id]['sub'][$extra_id]);
            if(count($this->cart['products']['raw_id'][$p_id]['sub'])==0){
                unset($this->cart['products']['raw_id'][$p_id]);
            }
        }else{
            unset($this->cart['products']['raw_id'][$p_id]);
        }
        if($extra_id){
            $p_id = $this->combine_id($p_id, $extra_id);
        }
        unset($this->cart['products']['amount'][$p_id]);
        unset($this->cart['products']['lists'][$p_id]);
        $this->count(true);
        if($this->handler->sc_cart_type==1){
            $this->calculate();//累計價格
        }   
    }    
    protected function _translator(){
        if(is_a($this->translator,'Model_Session_Cart_Translator_Interface')){
            return $this->translator;
        }else{
            if(is_string($this->translator)){
                $class = $this->_get_translator_class($this->translator);
                $translator = new $class;
                $this->translator = $translator;
                return $translator;
            }elseif(is_array($this->translator)){
                $class = $this->_get_translator_class($this->translator['class']);
                $translator = new $class($this->translator['options']);
                $this->translator = $translator;
                return $translator;
            }else{
                throw new Exception('translator should implement Model_Session_Cart_Translator_Interface');
            }
        }
    }
    
    function translator(){
        return $this->_translator();
    }
    
    protected function _get_translator_class($name){
        if(file_exists($this->translatorPath  . $name.".php")){
            require_once $this->translatorPath  . $name.".php";
        }else{
            throw new Exception("Cart Translator file doesn't exists!");
        }
        $className = "Model_Session_Cart_Translator_".strtoupper($name);
        if(class_exists($className)){
            return $className;
        }else{
            throw new Exception(" Cart Translator class doesn't exists!" );
        }
    }
    public function initDiscounter(){
        foreach($this->discounter as $type => $discounterInfo){
            if(is_string($discounterInfo)){
                $this->discounter[$type] = $this->_loadDiscount($discounterInfo);
            }elseif(is_array($discounterInfo)){
                $this->discounter[$type] = $this->_loadDiscount($discounterInfo['class'],$discounterInfo['options']);
            }
        }
    }
    protected function _loadDiscount($name,$options=null){
        if(!file_exists($this->discounterPath . $name . ".php")){
            throw new Exception("file of discounter done't exists!");
        }
        require_once $this->discounterPath . $name . ".php";
        $class = "Model_Session_Cart_Discounter_".ucfirst($name);
        if(!class_exists($class)){
            throw new Exception("cart discounter class not find!");
        }
        if($options){
            $discounter = new $class($options);
        }else{
            $discounter = new $class;
        }
        return $discounter;
    }
    
    protected function combine_id($p_id,$ps_id){
        return $p_id . ":" . $ps_id;        
    }
    
    function checkLocalOnly(){
        foreach($this->get_cart_products() as $prod){
            if($prod['local_only']){
                return true;
            }
        }
        return false;
    }
    
    function checkCartStocks(){
        if(count($this->cart['products']['lists']) && $this->handler->sc_cart_type==1){
            foreach($this->cart['products']['lists'] as $cartProd){
                if(!$this->stockChecker->check( $cartProd['p_id'], $cartProd['amount'], $cartProd['ps_id'] )){
                    return false;
                }
            }
        }
        return true;
    }
}
