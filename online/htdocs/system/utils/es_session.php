<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------

//引入数据库的系统配置及定义配置函数
require_once APP_ROOT_PATH."system/utils/es_cookie.php";
if(!function_exists("app_conf"))
{
	$sys_config = require APP_ROOT_PATH.'system/config.php';
	function app_conf($name)
	{
		return stripslashes($GLOBALS['sys_config'][$name]);
	}
}

class es_session 
{	
	static function id()
	{		
		return session_id();
	}
	static function start()
	{
		if(isset($_REQUEST['FANWE_SESSID']))
		{
			session_id($_REQUEST['FANWE_SESSID']);
		}
		
		$domain= "";
		if(app_conf("DOMAIN_ROOT")!=""){
			$domain = explode(":",app_conf("DOMAIN_ROOT"));
			session_set_cookie_params(0,app_conf("COOKIE_PATH"),$domain[0]);
		}
		else{
			session_set_cookie_params(0,app_conf("COOKIE_PATH"),$domain);
		}
		
		@session_start();
	}
	
    // 判断session是否存在
    static function is_set($name) {    	
        return isset($_SESSION[app_conf("AUTH_KEY").$name]);
    }

    // 获取某个session值
    static function get($name) {

    	if(isset($_SESSION[app_conf("AUTH_KEY").$name]))
    	{
        	$value   = $_SESSION[app_conf("AUTH_KEY").$name];
        	return $value;
    	}

    }

    // 设置某个session值
    static function set($name,$value) {   

        $_SESSION[app_conf("AUTH_KEY").$name]  =   $value;
    }

    // 删除某个session值
    static function delete($name) {

        unset($_SESSION[app_conf("AUTH_KEY").$name]);
    }

    // 清空session
    static function clear() {

        session_destroy();
    }
    
    //关闭session的读写
    static function close()
    {

    	@session_write_close();
    }
    
    static function  is_expired()
    {

        if (isset($_SESSION[app_conf("AUTH_KEY")."expire"]) && $_SESSION[app_conf("AUTH_KEY")."expire"] < NOW_TIME) {
            return true;
        } else {        	
        	$_SESSION[app_conf("AUTH_KEY")."expire"] = NOW_TIME+(intval(app_conf("EXPIRED_TIME"))*60);
            return false;
        }
    }
}
?>