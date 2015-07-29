<?php
class Model_Session_Cart_Stockchecker extends Model_Modules {
    function check($p_id,$order_amount,$ps_id=null){
        if(App::getHelper('session')->sc_cart_type==1){
            $activeStocks = $this->getStocks($p_id, $ps_id);
            if($activeStocks>=$order_amount){
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }
    }
    
    function getStocks($p_id,$ps_id=null,$with_cart_amounts=false){
        //實際庫存
        if($ps_id){
            $real_stocks = $this->getStocksFromProudctSpec($ps_id);
        }else{
            $real_stocks = $this->getStocksFromProudct($p_id);
        }
        //未出貨數量
        $unDeliveryAmounts = $this->getUnDeliveryAmounts($p_id, $ps_id);
        // 購物車數量
        if($with_cart_amounts){
            $in_cart_prod = $this->_model->get_cart_products($p_id,$ps_id);
        }
        //可用庫存
        return $real_stocks - $unDeliveryAmounts - (int)$in_cart_prod['amount'];
        
    }
    
    function getStocksFromProudct($p_id){
        $prod = App::getHelper('dbtable')->products->getData($p_id)->getDataRow('stocks');
        return $prod['stocks'];
    }
    
    function getStocksFromProudctSpec($ps_id){
        $db = App::getHelper('db');
        $sql = "select quantity from ".$db->prefix("products_spec_attributes")." where ps_id='".$ps_id."'";
        list($quantity) = $db->query_firstRow($sql,false);
        return $quantity;
    }
    
    function getUnDeliveryAmounts($p_id,$ps_id){
        $db = App::getHelper('db');
        $sql = "select sum(amount) as amounts from ".$db->prefix("order_items")." as oi inner join ".$db->prefix("order"). " as o on oi.o_id=o.o_id where o.o_status<3 and (o.del='0' and oi.del='0') and oi.p_id='".$p_id."' and oi.ps_id='".(int)$ps_id."' ";
        list($unDeliveryAmounts) = $db->query_firstRow($sql,false);
        return $unDeliveryAmounts;
    }
    
    function runStocks($p_id,$ps_id,$amount,$minus=true){
        //實際庫存
        if($ps_id){
            return $this->runStocksInProudctSpec($ps_id,$amount,$minus);
        }else{
            return $this->runStocksInProudct($p_id,$amount,$minus);
        }
    }
    
    function runStocksInProudct($p_id,$amount,$minus=true){
        $prod = App::getHelper('dbtable')->products->getData($p_id)->getDataRow('p_id,stocks');
        if($minus){
            $prod['stocks'] -= $amount;
        }else{
            $prod['stocks'] += $amount;
        }
        App::getHelper('dbtable')->products->writeData($prod);
        return $prod['stocks'];
    }
    
    function runStocksInProudctSpec($ps_id,$amount,$minus=true){
        $db = App::getHelper('db');
        $sql = "select quantity from ".$db->prefix("products_spec_attributes")." where ps_id='".$ps_id."'";
        list($quantity) = $db->query_firstRow($sql,false);
        if($minus){
            $quantity -= $amount;
        }else{
            $quantity += $amount; 
        }
        $sql = "update ".$db->prefix("products_spec_attributes")." set quantity='{$quantity}' where ps_id='".$ps_id."'";
        $db->query($sql);
        return $quantity;
    }    
    
    function returnStocks($p_id,$ps_id,$amount){
        //實際庫存
        $this->runStocks($p_id, $ps_id, $amount,false);      
    }
}
