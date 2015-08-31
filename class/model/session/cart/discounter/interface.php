<?php
interface Model_Session_Cart_Discounter_Interface {
    public function checkout(&$products);
    public function run();
}
