<?php
class Leftmenu_Common extends Leftmenu_Abstract{
    function __construct(array $menuItems,TemplatePower $tpl) {
        parent::__construct($tpl);
        $this->menuItems = $menuItems;
    }
}
