<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of chargefee
 *
 * @author Administrator
 */
class Model_Chargefee {
    static function calculate($price){
        global $db,$cms_cfg;
        if($cms_cfg['ws_module']['ws_multi_chargefee']){
            $dataRow = App::getHelper('dbtable')->chargefee->getDataList("'".$price."' >=`pricefloor` and '".$price."'<=`priceceil`","fee","",'1');
            if($dataRow){
                if($dataRow[0]['fee']>=1){
                    return $dataRow[0]['fee'];
                }else{
                    return round($price * $dataRow[0]['fee'],0);
                }
            }else{
                return 0;
            }            
        }else{
            $sql = "select sc_service_fee from ".$db->prefix("system_config")." where sc_id='1'";
            list($sc_service_fee) = $db->query_firstRow($sql,false);
            return $sc_service_fee;
        }
    }
}
