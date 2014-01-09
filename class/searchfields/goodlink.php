<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of contactus
 *
 * @author chunhsin
 */
class searchFields_goodlink extends searchFields_abstract{
    protected $search_fields = array(
        'l_subject' => array("title"=>"標題","fields"=>array("l_subject")),
        'l_content' => array("title"=>"內容","fields"=>array("l_content")),
        'l_modifydate' => array("title"=>"編輯月份","fields"=>array("l_modifydate"),"compare"=>"between_date"),
    ); 
}
?>
