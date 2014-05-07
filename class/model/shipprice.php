<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of shipprice
 *
 * @author Administrator
 */
class Model_Shipprice {
    static function calculate($price,$ship_zone=""){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        if($cms_cfg['ws_module']['ws_multi_shipprice']){
            $dataRow = App::getHelper('dbtable')->shipprice->getDataList("'".$price."' >=`pricefloor` and '".$price."'<=`priceceil`","shipprice","",'1');
            if($dataRow){
                return $dataRow[0]['shipprice'];
            }else{
                return 0;
            }
        }else{
            $sql = "select sc_shipping_price,sc_shipping_price2,sc_shipping_price3,sc_no_shipping_price from ".$cms_cfg['tb_prefix']."_system_config where sc_id='1'";
            list($a,$b,$c,$d) = $db->query_firstRow($sql,false);
            switch($ship_zone){
                default:
                case 1:
                    $ship_price = $a;
                    break;
                case 2:
                    $ship_price = $b;
                    break;
                case 3:
                    return  $c;
                    break;
            }
            if($price < $d){
                return $ship_price;
            }else{
                return 0;
            }
        }        
    }
}
