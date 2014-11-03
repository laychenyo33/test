<?php
class Leftmenu_Products extends Leftmenu_Catewithitems {
    //分類資訊
    protected $cate = array(
        'linkType'    => 'productscate',
        'labelField'    => 'pc_name',
        'query' => array(
            'table'     => 'products_cate',
            'alias'  => 'b',
            'pkField' => 'pc_id',
            'parentField' => 'pc_parent',
            'select' => '*',
            'condition' => "pc_status='1'",
            'order' => 'pc_up_sort desc,pc_sort',
        ),
    );
    //項目資訊
    protected $items = array(
        'linkType'  => 'products',
        'labelField' => 'p_name',
        'query' => array(
            'table'   => 'products',
            'alias'  => 'a',
            'cateField' => 'pc_id',
            'select' => 'a.*,b.pc_seo_filename',
            'condition' => "p_status='1'",
            'order' => 'p_sort',
        ),
    );
    
    protected $cateLayer = 2;
    
    function __construct(TemplatePower $tpl) {
        $maxDigs = App::configs()->ws_module->ws_left_sub_pc? $this->cateLayer : 1;
        parent::__construct($tpl, $maxDigs, App::configs()->ws_module->ws_left_products);
    }
    
    function checkCurrent($cateLink,$cateRow,$chkCate=true){
        if(parent::checkCurrent($cateLink, $cateRow , $chkCate)){
            return true;
        }else{
            if($chkCate){
                if(App::configs()->ws_module->ws_seo){
                    return App::getHelper('main')->is_current_or_child_cate($_GET['d'],$cateRow['pc_seo_filename'],true);
                }else{
                    return App::getHelper('main')->is_current_or_child_cate($_GET['pc_parent'],$cateRow['pc_id']);
                }
            }
        }
    }
    
}
