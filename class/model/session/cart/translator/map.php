<?php
class Model_Session_Cart_Translator_Map extends Model_Session_Cart_Translator_Abstract{
    protected $_maps;
    function translate($origin_data) {
        $origin_data['spec'] = $this->_maps[$origin_data['ps_id']];
        return $origin_data;
    }
}
