<?php
class Model_User_Backend extends Model_Modules implements Model_User_Iauthenticate  {
    function authenticate() {
        if(isset(App::getHelper('session')->USER_ACCOUNT)){
            return true;
        }
    }
}
