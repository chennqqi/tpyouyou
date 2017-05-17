<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------

class userModule extends BaseModule
{
	// 测试中心
	public function index()
	{
		set_gopreview();
		global_run();
		init_app_page();	
		$GLOBALS['tmpl']->display("test_index.html");
	}
}

?>