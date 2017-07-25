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
    	global_run();
        $GLOBALS['tmpl']->caching = true;
        $GLOBALS['tmpl']->cache_lifetime = 600;  //关于用会登录页的缓存
        $cache_id  = md5(MODULE_NAME.ACTION_NAME);
        if (!$GLOBALS['tmpl']->is_cached('wanyitong_login.html', $cache_id))
        {       
            init_app_page();
            $GLOBALS['tmpl']->assign("site_name","会员登录 - ".app_conf("SITE_NAME"));
            $GLOBALS['tmpl']->assign("site_keyword","会员登录,".app_conf("SITE_KEYWORD"));
            $GLOBALS['tmpl']->assign("site_description","会员登录,".app_conf("SITE_DESCRIPTION"));
        }
        // es_session::start();
        // $gopreview1 = es_session::get("gopreview");
        // es_session::close();
        $GLOBALS['tmpl']->display("wanyitong_login.html",$cache_id);
    }
    
    /**
     * 注册
     */
    
    function regist(){
    	$id = intval($_REQUEST['id']);
    	if($id==0){
    		showErr("删除失败",1);
    	}
    	
    	$GLOBALS['user'] = User::load_user();
		if(empty($GLOBALS['user']))User::auto_do_login();
		
		$session_id= es_session::id();
		
		$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."hotel_room_cart WHERE (user_id=".$GLOBALS['user']['id']." OR session_id='".$session_id."') and id=".$id." ");
		
		if($GLOBALS["db"]->affected_rows() > 0){
			showSuccess("删除成功",1);
		}
		else{
			showErr("删除失败",1);
		}
    }
}
?>