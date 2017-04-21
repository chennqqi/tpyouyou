<?php
// +----------------------------------------------------------------------
// | Author: 张子锐(2467254599@qq.com)
// +----------------------------------------------------------------------

class loginModule extends BaseModule
{
	public function index()
	{
		set_gopreview();
		global_run();
		init_app_page();		
		$GLOBALS['tmpl']->display("user_index.html");
	}
	
	public function qq()
	{		
		global_run();
		$GLOBALS['tmpl']->caching = true;
		$GLOBALS['tmpl']->cache_lifetime = 600;  //关于用会登录页的缓存
		$cache_id  = md5(MODULE_NAME.ACTION_NAME);		
		if (!$GLOBALS['tmpl']->is_cached('user_login.html', $cache_id))
		{
			init_app_page();
			$GLOBALS['tmpl']->assign("site_name","会员登录 - ".app_conf("SITE_NAME"));
			$GLOBALS['tmpl']->assign("site_keyword","会员登录,".app_conf("SITE_KEYWORD"));
			$GLOBALS['tmpl']->assign("site_description","会员登录,".app_conf("SITE_DESCRIPTION"));
		}
		$GLOBALS['tmpl']->display("login_qq.html",$cache_id);
	}
	
}
?>