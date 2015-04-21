<?php
class Model_Session_Cart_Giftor extends Model_Modules
{
    protected $_gift = array(
        -1 => array(
                'p_id'           => -1,
                'p_name'         => '開光卡(贈品)',
                'p_small_img'    => 'upload_files/Desert.jpg',
                'price'          => 0,
                'discount'       => 1,
                'amount'         => 1,
                'subtotal_price' => 0,            
        ),
    );
    
    function getGift($p_id){
        if(App::configs()->ws_module->ws_cart_gift){
            return $this->_gift[$p_id];
        }
    }
}
