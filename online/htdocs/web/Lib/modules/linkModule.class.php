<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class linkModule extends BaseModule
{
	public function index()
	{		
		global_run();
		init_app_page();
		
		$GLOBALS['tmpl']->assign("site_name","友情链接 - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword","友情链接,".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description","友情链接,".app_conf("SITE_DESCRIPTION"));
				
		$GLOBALS['tmpl']->display("link.html");
	}
	
	
}
?>