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

		$adm_session = es_session::get(md5(app_conf("AUTH_KEY")));

		$adm_name = $adm_session['adm_name'];

		$adm_id = intval($adm_session['adm_id']);

		

		if($adm_id != 0)

		{

			app_redirect(admin_url("index"));

		}

		

		$GLOBALS['tmpl']->assign("login_gate",admin_url("login#dologin"));

		$GLOBALS['tmpl']->display("core/login/index.html");

	}	

	

	

	//登录函数

	public function dologin()

	{

		$adm_name = strim($_REQUEST['adm_name']);

		$adm_password = strim($_REQUEST['adm_password']);

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

			/*showErr(lang("ADM_VERIFY_ERROR"),$ajax);*/

		}

	

		$adm_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."admin where adm_name = '".$adm_name."' and is_effect = 1");

		if($adm_data) //有用户名的用户

		{

			if($adm_data['adm_password']!=md5($adm_password))

			{

				save_log($adm_name.lang("ADM_PASSWORD_ERROR"),0); //记录密码登录错误的LOG

				showErr(lang("ADM_PASSWORD_ERROR"),$ajax);

			}

			else

			{

				if($adm_data['adm_name'] != app_conf("DEFAULT_ADMIN"))

				{

					$role = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."role where id = ".$adm_data['role_id']);

					if($role&&$role['is_effect']==0)

					{

						save_log($adm_name.lang("ADM_ROLE_ERROR"),0); //记录密码登录错误的LOG

						showErr(lang("ADM_ROLE_ERROR"),$ajax);

					}

				}

				

				//登录成功

				$adm_session['adm_name'] = $adm_data['adm_name'];

				$adm_session['adm_id'] = $adm_data['id'];

	

	

				es_session::set(md5(app_conf("AUTH_KEY")),$adm_session);

	

				//重新保存记录

				$adm_data['login_ip'] = CLIENT_IP;

				$adm_data['login_time'] = NOW_TIME;



				$GLOBALS['db']->query("update ".DB_PREFIX."admin set login_ip = '".CLIENT_IP."',login_time = '".NOW_TIME."' where id = ".$adm_data['id']);

				save_log($adm_data['adm_name'].lang("LOGIN_SUCCESS"),1);

				showSuccess(lang("LOGIN_SUCCESS"),$ajax);

			}

		}

		else

		{

			save_log($adm_name.lang("ADM_NAME_ERROR"),0); //记录用户名登录错误的LOG

			showErr(lang("ADM_NAME_ERROR"),$ajax);

		}

	}

	

	//登出函数

	public function logout()

	{

		//验证是否已登录

		//管理员的SESSION

		$adm_session = es_session::get(md5(app_conf("AUTH_KEY")));

		$adm_id = intval($adm_session['adm_id']);

	

		if($adm_id == 0)

		{

			//已登录

			app_redirect(admin_url("login"));

		}

		else

		{

			es_session::delete(md5(app_conf("AUTH_KEY")));

			app_redirect(admin_url("login"));;

		}

	}



}

?>