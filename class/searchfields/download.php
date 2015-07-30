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
class searchFields_download extends searchFields_abstract{
    protected $search_fields = array(
        'subject' => array("title"=>"標題","fields"=>array("d_subject")),
        'content' => array("title"=>"內容","fields"=>array("d_content")),
    ); 
}
?>
