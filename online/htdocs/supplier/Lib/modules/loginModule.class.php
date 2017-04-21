<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class loginModule extends BaseModule
{
	public function index()
	{

		//验证是否已登录
		//管理员的SESSION
		$adm_session = es_session::get(md5(app_conf("AUTH_KEY")."supplier"));
		$adm_name = $adm_session['user_name'];
		$adm_id = intval($adm_session['id']);
		
		if($adm_id != 0)
		{
			app_redirect(admin_url("index"));
		}
		
		$GLOBALS['tmpl']->assign("login_gate",admin_url("login#dologin"));
		$GLOBALS['tmpl']->assign("regist_gate",url("join"));
		$GLOBALS['tmpl']->display("core/login/index.html");
	}
	
	//登录函数
	public function dologin()
	{

		$adm_name = strim($_REQUEST['user_name']);
		$adm_password = strim($_REQUEST['user_pwd']);
		$ajax = intval($_REQUEST['ajax']);  //是否ajax提交

		if($adm_name == '')
		{
			showErr(lang("ADM_NAME_EMPTY"),$ajax);
		}
		if($adm_password == '')
		{
			showErr(lang("ADM_PASSWORD_EMPTY"),$ajax);
		}
		if(es_session::get("verify") != md5($_REQUEST['adm_verify'])) {
			showErr(lang("ADM_VERIFY_ERROR"),$ajax);
		}
	
		$adm_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where user_name = '".$adm_name."' and is_verify = 1");
		if($adm_data) //有用户名的用户
		{
				if($adm_data['user_pwd']==md5($adm_password))
				{
					$adm_session['id'] = $adm_data['id'];
					$adm_session['user_name'] = $adm_data['user_name'];
					$adm_session['company_name'] = $adm_data['company_name'];
					
					es_session::set(md5(app_conf("AUTH_KEY")."supplier"),$adm_session);
					showSuccess(lang("LOGIN_SUCCESS"),$ajax);
				}
				else
				{
					showErr(lang("ADM_PASSWORD_ERROR"),$ajax);
				}
			
		}
		else
		{

			showErr(lang("ADM_NAME_ERROR"),$ajax);
		}
	}
	
	//登出函数
	public function logout()
	{
		//验证是否已登录
		//管理员的SESSION
		$adm_session = es_session::get(md5(app_conf("AUTH_KEY")."supplier"));
		$adm_id = intval($adm_session['id']);
	
		if($adm_id == 0)
		{
			//已登录
			app_redirect(admin_url("login"));
		}
		else
		{
			es_session::delete(md5(app_conf("AUTH_KEY")."supplier"));
			app_redirect(admin_url("login"));;
		}
	}

}
?>