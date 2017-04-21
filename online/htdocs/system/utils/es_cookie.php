<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------

class es_cookie 
{
    // 判断Cookie是否存在
    static function is_set($name) {
        return isset($_COOKIE[$name]);
    }

    // 获取某个Cookie值
    static function get($name) {
        $value   = $_COOKIE[$name];
        return $value;
    }

    // 设置某个Cookie值
    static function set($name,$value,$expire='',$path='',$domain='') {   
    	$path = app_conf("COOKIE_PATH");
     	$domain = app_conf("DOMAIN_ROOT");
        $expire =   !empty($expire)?$expire:0;        
		if($domain!=""){
			$domain = explode(":",app_conf("DOMAIN_ROOT"));
			setcookie($name, $value,$expire,$path,$domain[0]);
		}
		else{
			setcookie($name, $value,$expire,$path,$domain);
		}
    }

    // 删除某个Cookie值
    static function delete($name) {
        es_cookie::set($name,'',0);
    }

    // 清空Cookie值
    static function clear() {
        unset($_COOKIE);
    }
}
?>