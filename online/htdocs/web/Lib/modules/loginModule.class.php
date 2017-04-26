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
		if (!$GLOBALS['tmpl']->is_cached('login_qq.html', $cache_id))
		{
			init_app_page();
			$GLOBALS['tmpl']->assign("site_name","qq登录 - ".app_conf("SITE_NAME"));
			$GLOBALS['tmpl']->assign("site_keyword","qq登录,".app_conf("SITE_KEYWORD"));
			$GLOBALS['tmpl']->assign("site_description","qq登录,".app_conf("SITE_DESCRIPTION"));
		}
		$GLOBALS['tmpl']->display("login_qq.html",$cache_id);
	}

	public function check()
	{
		$ajax = intval($_REQUEST['ajax']);
		$user_openid = strim($_POST['openid']);
		$user_nickname = strim($_POST['nickname']);
		$user_figureurl = strim($_POST['figureurl']);
		$user_accesstoken = strim($_POST['accesstoken']);
		$result = User::checkqq($user_openid, $user_nickname,$user_figureurl, $user_accesstoken);
		es_session::start();
		es_session::set("openid", $user_openid);
		es_session::set("qq_img", $user_figureurl);
		es_session::set("qq_name", $user_nickname);
		es_session::set("login_type","qq");
		es_session::close();
		if ($result['status'] == 2) {
			$user_qq = $result['user'];
			$user_id = $user_qq['user_id'];
			User::loginByUserId($user_id);
		}
		ajax_return($result);
	}

	public function dorel()
	{
		$ajax = intval($_REQUEST['ajax']);
		$user_key = strim($_REQUEST['user_key']);
		$user_pwd = strim($_REQUEST['user_pwd']);
		$user_verify = strim($_REQUEST['user_verify']);
		$save_user = intval($_REQUEST['save_user']);
		es_session::start();
		$verify = es_session::get("verify");
		$user_openid = es_session::get("openid");
		$login_type = es_session::get("login_type");
		es_session::close();
		if($verify!=md5($user_verify))
		{
			showErr("验证码不匹配",$ajax);
		}
		$result = User::do_rel($user_key, $user_pwd, $user_openid);
		if($result['status']==4)
		{
			es_session::start();
			es_session::delete("verify");
			es_session::close();
			
			if($save_user==1)
			{
				//保存cookie
				$cookie_key = md5(NOW_TIME.serialize($GLOBALS['user']));
				$cookie_expire = NOW_TIME+14*24*3600;
				$GLOBALS['db']->query("update ".DB_PREFIX."user set cookie_key ='".$cookie_key."',cookie_expire = ".$cookie_expire." where id = ".$GLOBALS['user']['id']);
				es_cookie::set("fanwetour_user_cookie", $cookie_key,$cookie_expire);
			}
			else
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."user set cookie_key ='',cookie_expire =0 where id = ".$GLOBALS['user']['id']);		
			}
			showSuccess($result['message'],$ajax,get_gopreview(),0,$result['script']);
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

	public function logintype()
	{
		$ajax = intval($_REQUEST['ajax']);
		es_session::start();
		$login_type = es_session::get("login_type");
		es_session::close();
		echo $login_type;
	}
	
}
?>