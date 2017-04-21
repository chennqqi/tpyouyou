<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------

class helpModule extends BaseModule
{
	public function index()
	{		
		global_run();
		init_app_page();
		$id = intval($_REQUEST['id']);	
		if(!isset($GLOBALS['help_list'][$id]))
		{
			app_redirect(url("index"));
		}				
		
		$GLOBALS['tmpl']->assign("current_cate",$GLOBALS['help_list'][$id]);
		foreach($GLOBALS['help_list'][$id]['list'] as $k=>$v)
		{
			if($v['is_url']==0)
			{
				
				//注册当前地址
				$ur_here[] = array("name"=>$GLOBALS['help_list'][$id]['name'],"url"=>$GLOBALS['help_list'][$id]['url']);
				$ur_here[] = array("name"=>$v['name'],"url"=>$v['url']);			
				$GLOBALS['tmpl']->assign("ur_here",$ur_here);
				
				$GLOBALS['tmpl']->assign("site_name",$v['name']." - ".$GLOBALS['help_list'][$id]['name']." - 帮助中心 - ".app_conf("SITE_NAME"));
				$GLOBALS['tmpl']->assign("site_keyword","帮助中心,".$GLOBALS['help_list'][$id]['name'].",".$v['name'].",".app_conf("SITE_KEYWORD"));
				$GLOBALS['tmpl']->assign("site_description","帮助中心,".$GLOBALS['help_list'][$id]['name'].",".$v['name'].",".app_conf("SITE_DESCRIPTION"));
				
				$GLOBALS['tmpl']->assign("current_id",$k);
				break;
			}
		}
		$GLOBALS['tmpl']->display("help.html");
	}
	
	public function show()
	{		
		global_run();
		init_app_page();
		$id = intval($_REQUEST['cid']);	
		if(!isset($GLOBALS['help_list'][$id]))
		{
			app_redirect(url("index"));
		}				
		
		$GLOBALS['tmpl']->assign("current_cate",$GLOBALS['help_list'][$id]);
		$hid = intval($_REQUEST['id']);
		if(!isset($GLOBALS['help_list'][$id]["list"][$hid]))
		{
			app_redirect(url("index"));
		}
		
		$v = $GLOBALS['help_list'][$id]["list"][$hid];
		
		//注册当前地址
		$ur_here[] = array("name"=>$GLOBALS['help_list'][$id]['name'],"url"=>$GLOBALS['help_list'][$id]['url']);
		$ur_here[] = array("name"=>$v['name'],"url"=>$v['url']);
		$GLOBALS['tmpl']->assign("ur_here",$ur_here);
		
		$GLOBALS['tmpl']->assign("site_name",$v['name']." - ".$GLOBALS['help_list'][$id]['name']." - 帮助中心 - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword","帮助中心,".$GLOBALS['help_list'][$id]['name'].",".$v['name'].",".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description","帮助中心,".$GLOBALS['help_list'][$id]['name'].",".$v['name'].",".app_conf("SITE_DESCRIPTION"));
		
		$GLOBALS['tmpl']->assign("current_id",$hid);
		
		
		$GLOBALS['tmpl']->display("help.html");
	}
	
	
}
?>