<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cate_tree
 *
 * @author chunhsin
 */
class catetree_productsCate extends catetree_abstract {
    //put your code here
    protected $_db;
    protected $_cfg;
    protected $_cate_table = "products_cate";
    protected $_catename_column = "name";
    protected $_parent_column = "parent";
    protected $_prefix = "pc_";
    protected $_id_tree = array();
    protected $_tree_structure = "";
    protected $_templates = "templates/ws-manage-fn-cate-tree-tpl.html";
    protected $_cate_link_str = "";
    protected $_build = false;
    protected $_varname = "pc_parent";
   
    public function orderfields() {
        return "pc_up_sort desc,  ". parent::orderfields();
    }
}

?>
