<?php
class Model_Session_Cart_Discounter_Abstract extends Model_Modules implements Model_Session_Cart_Discounter_Interface {
    /**
     *前折扣(pre)或後折扣(post)
     * @var string
     */
    public $position;
    
    /**
     * 
     * @param Model_Session_Cart $model
     * @param type $options
     */
    public function __construct(Model_Session_Cart $model = null, $options = "") {
        if(empty($this->position)){
            throw new Exception("please assign value (pre or post) to member attribute \$position ");
        }
        parent::__construct($model, $options);
        $this->init();
    }
    
    protected function init(){}
    
    public function checkout(&$products) {
        throw new Exception("need to implements custom method checkout");
    }

    public function run() {
        throw new Exception("need to implements custom method checkout");
    }
       

}
