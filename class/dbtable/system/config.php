<?php
class Dbtable_System_Config extends Dbtable_Abstract{
    //可修改的欄位
    protected $table = "system_config";
    protected $pk = "sc_id";
    
    protected function _retrieve_cols($post) {
        parent::_retrieve_cols($post);
        if(isset($post["sc_im_starttime_h"]) && isset($post["sc_im_starttime_i"]))$this->values["sc_im_starttime"] = $post["sc_im_starttime_h"].":".$post["sc_im_starttime_i"].":00";
        if(isset($post["sc_im_endtime_h"]) && isset($post["sc_im_endtime_i"]))$this->values["sc_im_endtime"] = $post["sc_im_endtime_h"].":".$post["sc_im_endtime_i"].":00";     
    }
     
}
?>
