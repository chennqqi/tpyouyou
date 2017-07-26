<?php

class wanyitongModule extends BaseModule{

    function index() {
        echo "string";
    }
    
    /**
     * [login description]
     * @return [type] [description]
     */
    function login(){
        $phone = floor($_REQUEST['phone']);        
        $GLOBALS['tmpl']->assign("phone", $phone);
        $GLOBALS['tmpl']->display("wanyitong_login.html");
    }

    /**
     * [dologin description]
     * @return [type] [description]
     */
    public function dologin()
    {
        $ajax = 1;
        $user_key = strim($_REQUEST['user_key']);
        $user_pwd = strim($_REQUEST['user_pwd']);

        $result = User::mobile_login($user_key, $user_pwd);
        if($result['status']==4)
        {           
            $result['user_key'] = md5($result['user']['id']);
            $result['info'] = $result['message'];
            unset($result['user']);
            unset($result['script']);
            unset($result['message']);
            header("Content-Type:text/html; charset=utf-8");
            echo(json_encode($result));
            exit;
        }
        elseif($result['status']==1)
        {
            if($result['user']['email']!="")$type="email";
            if($result['user']['mobile']!="")$type="mobile";

            showSuccess($result['message'],$ajax,url("user#doverify",array("un"=>$result['user']['user_name'],"t"=>$type)));
        }
        else
        {
            showErr($result['message'],$ajax);
        }
    }
    
    
    /**
     * [regist description]
     * @return [type] [description]
     */
    function regist(){
        $phone = strim($_REQUEST['phone']);        
        $GLOBALS['tmpl']->assign("phone", $phone);
        $GLOBALS['tmpl']->display("wanyitong_register.html");
    }
}
?>