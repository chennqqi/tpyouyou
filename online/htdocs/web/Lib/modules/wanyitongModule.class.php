<?php

class wanyitongModule extends BaseModule{

    /**
     * 
     */

    function index() {
        echo "string";
    }
    
    // login
    function login(){
        $phone = floor($_REQUEST['phone']);        
        $GLOBALS['tmpl']->assign("phone", $phone);
        $GLOBALS['tmpl']->display("wanyitong_login.html");
    }
    
    /**
     * 注册
     */
    
    function regist(){
        $phone = strim($_REQUEST['phone']);        
        $GLOBALS['tmpl']->assign("phone", $phone);
        $GLOBALS['tmpl']->display("wanyitong_register.html");
    }
}
?>