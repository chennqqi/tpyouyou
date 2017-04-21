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
	//会员中心
	public function index()
	{
		set_gopreview();
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			app_redirect(url("user#login"));
		}		
		init_app_page();		
		
		$GLOBALS['tmpl']->display("user_index.html");
	}
	
	public function login()
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
		$GLOBALS['tmpl']->display("user_login.html",$cache_id);
	}
	
	
	public function dologin()
	{
		$ajax = intval($_REQUEST['ajax']);
		$user_key = strim($_REQUEST['user_key']);
		$user_pwd = strim($_REQUEST['user_pwd']);
		$user_verify = strim($_REQUEST['user_verify']);
		$save_user = intval($_REQUEST['save_user']);
		es_session::start();
		$verify = es_session::get("verify");
		es_session::close();
		if($verify!=md5($user_verify))
		{
			showErr("验证码不配匹",$ajax);
		}
		$result = User::do_login($user_key, $user_pwd);
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
	
	
	public function logout()
	{
		$integrate  = $GLOBALS['db']->getRow("select class_name from ".DB_PREFIX."integrate");
		if($integrate)
		{
			$directory = APP_ROOT_PATH."system/integrate/";
			$file = $directory.$integrate['class_name']."_integrate.php";
			if(file_exists($file))
			{
				require_once($file);
				$integrate_class = $integrate['class_name']."_integrate";
				$integrate_item = new $integrate_class;
				$res = $integrate_res = $integrate_item->logout();
			}
		}
		User::do_logout();
		if($res['msg'])
		app_redirect(get_gopreview(),0,$res['msg']);
		else
		app_redirect(get_gopreview());
		
	}
	
	public function regist()
	{
		$cron_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_msg_list where is_send = 0");
		$GLOBALS['tmpl']->assign("CRON_COUNT",intval($cron_count));	
	
		$GLOBALS['tmpl']->caching = true;
		$GLOBALS['tmpl']->cache_lifetime = 600;  //关于用会登录页的缓存
		$cache_id  = md5(MODULE_NAME.ACTION_NAME);
		if (!$GLOBALS['tmpl']->is_cached('user_regist.html', $cache_id))
		{
			init_app_page();
			$GLOBALS['tmpl']->assign("site_name","会员注册 - ".app_conf("SITE_NAME"));
			$GLOBALS['tmpl']->assign("site_keyword","会员注册,".app_conf("SITE_KEYWORD"));
			$GLOBALS['tmpl']->assign("site_description","会员注册,".app_conf("SITE_DESCRIPTION"));
		}
		$GLOBALS['tmpl']->display("user_regist.html",$cache_id);
	}
	
	public function load_regform()
	{
		$type = strim($_REQUEST['type']);
		$file = "inc/".$type."form.html";
		$GLOBALS['tmpl']->display($file);
	}
	
	public function checkfield()
	{
		$field = strim($_REQUEST['field']);
		$val = strim($_REQUEST['val']);
		if($field=="user_verify")
		{
			es_session::start();
			$verify = es_session::get("verify");
			es_session::close();
			if(md5($val)!=$verify)
			{
				ajax_return(array("status"=>0,"info"=>"验证码错误"));
			}
			else
			{
				ajax_return(array("status"=>1));
			}
		}
		else
		{
			$res = User::checkfield($field, $val);
			ajax_return($res);
		}
	}
	
	
	public function doregist()
	{
		$ip_user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where regist_ip = '".CLIENT_IP."' order by create_time desc");
		if($ip_user&&(NOW_TIME-intval($ip_user['create_time']))<intval(app_conf("USER_REGIST_IP_SPAN")))
		{
			ajax_return(array("status"=>0,"info"=>"请勿频繁注册","field"=>"","jump"=>""));
		}
		$user_name = strim($_POST['user_name']);
		$email = strim($_POST['email']);
		$mobile = strim($_POST['mobile']);
		$user_pwd  = strim($_POST['user_pwd']);
		$cfm_user_pwd  = strim($_POST['cfm_user_pwd']);
		$user_verify = strim($_POST['user_verify']);
		$type = strim($_GET['type']);		
		
		$ck = User::checkfield("user_name", $user_name);		
		if($ck['status']==0)
		{
			ajax_return(array("status"=>0,"info"=>$ck['info'],"field"=>"user_name"));
		}
		
		if($type=="mobile")
		{
			if($mobile=="")
			{
				ajax_return(array("status"=>0,"info"=>"请输入手机号码","field"=>"mobile"));
			}
			$ck = User::checkfield("mobile", $mobile);
			if($ck['status']==0)
			{
				ajax_return(array("status"=>0,"info"=>$ck['info'],"field"=>"mobile"));
			}
		}
		
		if($type=="email")
		{
			if($email=="")
			{
				ajax_return(array("status"=>0,"info"=>"请输入电子邮箱","field"=>"email"));
			}
			$ck = User::checkfield("email", $email);
			if($ck['status']==0)
			{
				ajax_return(array("status"=>0,"info"=>$ck['info'],"field"=>"email"));
			}
		}
		
		if($user_pwd == "")
		{
			ajax_return(array("status"=>0,"info"=>"密码不能为空","field"=>"user_pwd"));
		}
		
		if($user_pwd != $cfm_user_pwd)
		{
			ajax_return(array("status"=>0,"info"=>"密码确认失败","field"=>"cfm_user_pwd"));
		}
		
		es_session::start();
		$verify = es_session::get("verify");
		es_session::close();
		
		if(md5($user_verify)!=$verify)
		{
			ajax_return(array("status"=>0,"info"=>"验证码不匹配","field"=>"user_verify"));
		}
		
		//会员注册时通知uc添加用户
		$integrate  = $GLOBALS['db']->getRow("select class_name from ".DB_PREFIX."integrate");
		if($integrate)
		{
			$directory = APP_ROOT_PATH."system/integrate/";
			$file = $directory.$integrate['class_name']."_integrate.php";
			if(file_exists($file))
			{
				require_once($file);
				$integrate_class = $integrate['class_name']."_integrate";
				$integrate_item = new $integrate_class;
				$ck = $integrate_item->add_user($user_name,$user_pwd,$email);
				if($ck['status']==0)
				{
					ajax_return(array("status"=>0,"info"=>$ck['info'],"field"=>$ck['field']));
				}
			}
		}
		
		
		$user_data = array();
		$user_data['user_name'] = $user_name;
		if($type=="email")
			$user_data['email'] = $email;
		if($type=="mobile")
			$user_data['mobile'] = $mobile;
		
		$user_data['salt'] = USER_SALT;
		$user_data['user_pwd'] = md5($user_pwd.$user_data['salt']);
		$user_data['is_effect'] = 1;
		$user_data['create_time'] = NOW_TIME;
		$user_data['integrate_id'] = intval($ck['data']);
		
		if($type=="mobile")
		{
			if(app_conf("USER_MOBILE_VERIFY")==1)
			{
				//手机验证
				$user_data['is_verify'] = 0;
			}
			else
			{
				//自动生效
				$user_data['is_verify'] = 1;
			}
		}
		else if($type=="email")
		{
			if(app_conf("USER_EMAIL_VERIFY")==1)
			{
				//邮箱验证
				$user_data['is_verify'] = 0;
			}
			else
			{
				//自动生效
				$user_data['is_verify'] = 1;
			}
		}
		
		$user_data['source'] = empty($GLOBALS['ref'])?"native":$GLOBALS['ref'];  //来路
		$user_data['pid'] = intval($GLOBALS['ref_pid']); //推荐人
		$user_data['nickname'] = $user_data['user_name'];
		$user_data['regist_ip'] = CLIENT_IP;
		require_once APP_ROOT_PATH."system/libs/city.php";
		$user_data['regist_city'] = City::locate_city_name(CLIENT_IP);
		$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_data,"INSERT","","SILENT");
		es_session::start();
		es_session::delete("verify");
		es_session::close();
		if($GLOBALS['db']->error()=="")
		{
			$user_id = $GLOBALS['db']->insert_id();
			$user_data['id'] = $user_id;
			//发放注册奖劢
			if(app_conf("USER_REG_MONEY")>0)
			{
				USER::modify_account($user_id, 1, app_conf("USER_REG_MONEY"), "注册获赠现金");
			}
			if(app_conf("USER_REG_SCORE")>0)
			{
				USER::modify_account($user_id, 2, app_conf("USER_REG_SCORE"), "注册获赠积分");
			}
			if(app_conf("USER_REG_EXP")>0)
			{
				USER::modify_account($user_id, 3, app_conf("USER_REG_EXP"), "注册获赠经验");
			}
			if(app_conf("USER_REG_VOUCHER")>0)
			{
				require_once APP_ROOT_PATH."system/libs/voucher.php";
				$voucher_data = Voucher::gen(app_conf("USER_REG_VOUCHER"), $user_data);
				if($voucher_data['status'])
				USER::modify_account($user_id, 4, $voucher_data['data']['money'], "注册获赠代金券");
			}
			User::user_level_locate($user_id);
			//数据生成成功
			if($user_data['is_verify']==0)
			{
				if($type=="mobile")
				{
					//手机验证
					User::send_verify_mobile($user_data);
					ajax_return(array("status"=>0,"field"=>"", "jump"=>url("user#doverify",array("t"=>"mobile","un"=>$user_data['user_name']))));
									
				}
				else if($type=="email")
				{
					//邮箱验证
					User::send_verify_email($user_data);					
					ajax_return(array("status"=>0,"field"=>"", "jump"=>url("user#doverify",array("t"=>"email","un"=>$user_data['user_name']))));
				}
			}
			else
			{
				//登录
				User::do_login($user_name, $user_pwd);
				ajax_return(array("status"=>1,"info"=>"恭喜您！注册成功","jump"=>get_gopreview()));
			}
		}
		else
		{
			ajax_return(array("status"=>0,"info"=>"服务器繁忙，请重试","field"=>"","jump"=>""));
		}
		
		
	}
	
	//会员验证
	public function doverify()
	{
		
		
		$username = strim($_REQUEST['un']);
		$type = strim($_REQUEST['t']);
		$code = strim($_REQUEST['c']);
		
		$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where user_name ='".$username."'");
		if(empty($user)||$user['is_verify']==1||$user['is_effect']==0)
		{
			app_redirect(url("index")); //无会员或者已验证或者被禁用的会员跳过
		}
		else
		{
			if($type=="email")
			{
				if($user['email']!="")
				{
					//邮箱验证
					if(empty($code))
					{
						$this->show_verify_page($user, "email","验证邮件已发送到您的邮箱，请注意查收");
					}
					else
					{
						if($user['email_code']==$code&&$user['email_code_time']>NOW_TIME)
						{
							//验证成功
							$GLOBALS['db']->query("update ".DB_PREFIX."user set is_verify = 1 where id = ".$user['id']);
							User::do_login_save($user);
							app_redirect(get_gopreview());
						}
						else
						{
							//验证失败
							$this->show_verify_page($user, "email","验证失败，请重新验证");
						}
					}
				}
				else if($user['mobile']!="")
				{
					//手机验证
					if(empty($code))
					{
						$this->show_verify_page($user, "mobile","请输入收到的验证码");
					}
					else
					{
						if($user['mobile_code']==$code&&$user['mobile_code_time']>NOW_TIME)
						{
							//验证成功
							$GLOBALS['db']->query("update ".DB_PREFIX."user set is_verify = 1 where id = ".$user['id']);
							User::do_login_save($user);
							showSuccess("验证成功",1,get_gopreview());
						}
						else
						{
							//验证失败
							showErr("验证失败",1);
						}
					}
				}
				else
				{
					app_redirect(url("index"));
				}
			}
			elseif($type=="mobile")
			{
				if($user['mobile']!="")
				{					
					//手机验证
					if(empty($code))
					{
						$this->show_verify_page($user, "mobile","请输入收到的验证码");
					}
					else
					{
						if($user['mobile_code']==$code&&$user['mobile_code_time']>NOW_TIME)
						{
							//验证成功
							$GLOBALS['db']->query("update ".DB_PREFIX."user set is_verify = 1 where id = ".$user['id']);
							User::do_login_save($user);
							showSuccess("验证成功",1,get_gopreview());
						}
						else
						{
							//验证失败
							showErr("验证失败",1);
						}
					}
				}
				else if($user['email']!="")
				{
					//邮箱验证
					if(empty($code))
					{
						$this->show_verify_page($user, "email","验证邮件已发送到您的邮箱，请注意查收");
					}
					else
					{
						if($user['email_code']==$code&&$user['email_code_time']>NOW_TIME)
						{
							//验证成功
							User::do_login_save($user);
							app_redirect(get_gopreview());
						}
						else
						{
							//验证失败
							$this->show_verify_page($user, "email","验证失败，请重新验证");
						}
					}
				}
				else
				{
					app_redirect(url("index"));
				}
			}
			else
			{
				app_redirect(url("index"));
			}
		}
	}
	
	public function resend_mail()
	{
		$ajax = intval($_REQUEST['ajax']);
		$username = strim($_REQUEST['un']);				
		$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where user_name ='".$username."'");
		if($user['is_effect']==0)
		{
			showErr("会员被禁用",$ajax);
		}
		if($user['is_verify'])
		{
			showErr("会员已经验证",$ajax);
		}
		if(NOW_TIME-($user['email_code_time']-7200)>60)
		{
			User::send_verify_email($user);
			app_redirect(url("user#doverify",array("t"=>"email","un"=>$user['user_name'])));
		}
		else
		{
			showErr("发送间隔不能低于一分钟",$ajax);
		}
	}
	
	public function resend_mobile()
	{
		$ajax = intval($_REQUEST['ajax']);
		$username = strim($_REQUEST['un']);
		$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where user_name ='".$username."'");
		if($user['is_effect']==0)
		{
			showErr("会员被禁用",$ajax);
		}
		if($user['is_verify'])
		{
			showErr("会员已经验证",$ajax);
		}
		if(NOW_TIME-($user['mobile_code_time']-7200)>60)
		{
			User::send_verify_mobile($user);
			showSuccess("发送成功，请输入收到的验证码",$ajax);
		}
		else
		{
			showErr("发送间隔不能低于一分钟",$ajax);
		}
	}
	
	private function show_verify_page($user,$type,$message)
	{
		$cron_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_msg_list where is_send = 0");
		$GLOBALS['tmpl']->assign("CRON_COUNT",intval($cron_count));
		$GLOBALS['tmpl']->assign("site_name","会员验证 - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword","会员验证,".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description","会员验证,".app_conf("SITE_DESCRIPTION"));
		
		$GLOBALS['tmpl']->assign("user",$user);
		$GLOBALS['tmpl']->assign("type",$type);
		$GLOBALS['tmpl']->assign("message",$message);
		if($type=="email")
		{
			//开始关于跳转地址的解析
			$domain = explode("@",$user['email']);
			$domain = $domain[1];
			$gocheck_url = '';
			switch($domain)
			{
				case '163.com':
					$gocheck_url = 'http://mail.163.com';
					break;
				case '126.com':
					$gocheck_url = 'http://www.126.com';
					break;
				case 'sina.com':
					$gocheck_url = 'http://mail.sina.com';
					break;
				case 'sina.com.cn':
					$gocheck_url = 'http://mail.sina.com.cn';
					break;
				case 'sina.cn':
					$gocheck_url = 'http://mail.sina.cn';
					break;
				case 'qq.com':
					$gocheck_url = 'http://mail.qq.com';
					break;
				case 'foxmail.com':
					$gocheck_url = 'http://mail.foxmail.com';
					break;
				case 'gmail.com':
					$gocheck_url = 'http://www.gmail.com';
					break;
				case 'yahoo.com':
					$gocheck_url = 'http://mail.yahoo.com';
					break;
				case 'yahoo.com.cn':
					$gocheck_url = 'http://mail.cn.yahoo.com';
					break;
				case 'hotmail.com':
					$gocheck_url = 'http://www.hotmail.com';
					break;
				default:
					$gocheck_url = "";
					break;
			}
			$GLOBALS['tmpl']->assign("gocheck_url",$gocheck_url);
		}
		
		$GLOBALS['tmpl']->display("user_doverify.html");
	}
	
	
	public function getpwd()
	{
		init_app_page();
		$GLOBALS['tmpl']->assign("site_name","找回密码 - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword","找回密码,".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description","找回密码,".app_conf("SITE_DESCRIPTION"));		
		$GLOBALS['tmpl']->assign("step_count",1);
		$GLOBALS['tmpl']->display("user_getpwd.html");
	}
	
	
	public function getpwd_verifyuser()
	{
		$verify = strim($_REQUEST['user_verify']);
		$user_key = strim($_REQUEST['user_key']);
		es_session::start();
		$sess_verify = es_session::get("verify");
		es_session::delete("verify");
		es_session::close();
		if(md5($verify)!=$sess_verify)
		{
			ajax_return(array("status"=>0,"info"=>"验证码不正确","field"=>"user_verify","jump"=>""));
		}
		else
		{
			if($user_key=="")
			{
				ajax_return(array("status"=>0,"info"=>"帐号名不能为空","field"=>"user_key","jump"=>""));
			}
			else
			{
				$user = $GLOBALS['db']->getRow("select  *  from ".DB_PREFIX."user where user_name = '".$user_key."' or email='".$user_key."' or mobile='".$user_key."'"); 
				if($user)
				{
					if($user['is_effect']==0)
					{
						ajax_return(array("status"=>0,"info"=>"帐号被禁用","field"=>"user_key","jump"=>""));
					}
					else
					{
							$type = "email"; //取回方式
							if($user_key==$user['user_name'])
							{
								if($user['email']!="")$type="email";
								else	if($user['mobile']!="")$type="mobile";
							}
							elseif($user_key==$user['email'])
							{
								$type="email";
							}
							else
							{
								$type="mobile";
							}
							if($type=="email")
							{
								if(app_conf("MAIL_ON")==1)
								User::send_getpwd_email($user);
								else
								{
									ajax_return(array("status"=>0,"info"=>"邮件功能未开启，无法取回密码，请联系管理员","field"=>"","jump"=>""));
								}
							}
							else 
							{
								if(app_conf("SMS_ON")==1)
								User::send_getpwd_mobile($user);
								else
								{
									ajax_return(array("status"=>0,"info"=>"短信功能未开启，无法取回密码，请联系管理员","field"=>"","jump"=>""));
								}
							}
							ajax_return(array("status"=>1,"info"=>"成功发送验证码","jump"=>url("user#getpwd2",array("t"=>$type,"un"=>$user['user_name']))));
							
					}
				}
				else
				{
					ajax_return(array("status"=>0,"info"=>"帐号不存在","field"=>"user_key","jump"=>""));
				}
			}
		}		
	}
	
	
	public function getpwd2()
	{
		$user_name = strim($_REQUEST['un']);
		$type = strim($_REQUEST['t']);
		$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where user_name = '".$user_name."'");
		if(empty($user)||$user['is_effect']==0||$user['pwd_code']==""||$user['pwd_code_time']<NOW_TIME)
		{
			app_redirect(url("user#getpwd"));
		}

		init_app_page();
		if($type=="email")
		{
			//开始关于跳转地址的解析
			$domain = explode("@",$user['email']);
			$domain = $domain[1];
			$gocheck_url = '';
			switch($domain)
			{
				case '163.com':
					$gocheck_url = 'http://mail.163.com';
					break;
				case '126.com':
					$gocheck_url = 'http://www.126.com';
					break;
				case 'sina.com':
					$gocheck_url = 'http://mail.sina.com';
					break;
				case 'sina.com.cn':
					$gocheck_url = 'http://mail.sina.com.cn';
					break;
				case 'sina.cn':
					$gocheck_url = 'http://mail.sina.cn';
					break;
				case 'qq.com':
					$gocheck_url = 'http://mail.qq.com';
					break;
				case 'foxmail.com':
					$gocheck_url = 'http://mail.foxmail.com';
					break;
				case 'gmail.com':
					$gocheck_url = 'http://www.gmail.com';
					break;
				case 'yahoo.com':
					$gocheck_url = 'http://mail.yahoo.com';
					break;
				case 'yahoo.com.cn':
					$gocheck_url = 'http://mail.cn.yahoo.com';
					break;
				case 'hotmail.com':
					$gocheck_url = 'http://www.hotmail.com';
					break;
				default:
					$gocheck_url = "";
					break;
			}
			$GLOBALS['tmpl']->assign("gocheck_url",$gocheck_url);
		}
		$user['mobile'] = preg_replace("/(\d{3})(\d+)(\d{3})/", "\$1*****\$3", $user['mobile']);
		$GLOBALS['tmpl']->assign("site_name","找回密码 - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword","找回密码,".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description","找回密码,".app_conf("SITE_DESCRIPTION"));
		
		$GLOBALS['tmpl']->assign("user",$user);
		$GLOBALS['tmpl']->assign("type",$type);
		$GLOBALS['tmpl']->assign("step_count",2);
		
		$cron_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_msg_list where is_send = 0");
		$GLOBALS['tmpl']->assign("CRON_COUNT",intval($cron_count));
		$GLOBALS['tmpl']->display("user_getpwd.html");
	}
	
	
	public function getpwd_verifycode()
	{
		$type = strim($_REQUEST['t']);
		$user_name = strim($_REQUEST['un']);
		$code = strim($_REQUEST['c']);
		$ajax = intval($_REQUEST['ajax']);
		
		$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where user_name = '".$user_name."' ");
		if(empty($user))
		{
			showErr("会员不存在",$ajax,url("user#getpwd"));
		}
		else
		{
			
			
			if($user['is_effect']==0)
				showErr("会员被禁用",$ajax,url("user#getpwd"));
			else
			{
				if($user['pwd_code']!=$code)
				{
					if($ajax==1)
					{
						ajax_return(array("status"=>0,"info"=>"验证失败","field"=>"c","jump"=>""));
					}
					else
					{
						showErr("验证失败",0,url("user#getpwd"));
					}
				}
				elseif($user['pwd_code_time']<NOW_TIME)
				{
					if($ajax==1)
					{
						ajax_return(array("status"=>0,"info"=>"验证码过期","field"=>"c","jump"=>url("user#getpwd")));
					}
					else
					{
						showErr("验证码过期",0,url("user#getpwd"));
					}
				}
				else
				{
					$jumpurl = url("user#getpwd3",array("un"=>$user_name,"c"=>$code));
					if($ajax==1)
					{
						ajax_return(array("status"=>1,"jump"=>$jumpurl));
					}
					else 
					{
						app_redirect($jumpurl);
					}
				}
			}
			
			
		}
	}
	
	
	public function getpwd3()
	{
		$user_name = strim($_REQUEST['un']);
		$code = strim($_REQUEST['c']);
		
		
		$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where user_name = '".$user_name."' ");
		if(empty($user)||$user['is_effect']==0||$user['pwd_code']!=$code||$user['pwd_code_time']<NOW_TIME)
		{
			app_redirect(url("user#getpwd"));
		}
		
		$cron_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_msg_list where is_send = 0");
		$GLOBALS['tmpl']->assign("CRON_COUNT",intval($cron_count));
		
		$GLOBALS['tmpl']->assign("user",$user);
		$GLOBALS['tmpl']->assign("code",$code);
		$GLOBALS['tmpl']->assign("step_count",3);
		
		$GLOBALS['tmpl']->assign("site_name","找回密码 - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword","找回密码,".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description","找回密码,".app_conf("SITE_DESCRIPTION"));
		init_app_page();
		$GLOBALS['tmpl']->display("user_getpwd.html");
	}
	
	
	public function getpwd_savepwd()
	{
		$user_name = strim($_REQUEST['un']);
		$code = strim($_REQUEST['c']);
		
		$new_pwd = strim($_REQUEST['new_pwd']);
		$cfm_new_pwd = strim($_REQUEST['cfm_new_pwd']);
		
		if($new_pwd=="")
		{
			ajax_return(array("status"=>0,"info"=>"密码不能为空","field"=>"new_pwd","jump"=>""));
		}
		elseif($new_pwd!=$cfm_new_pwd)
		{
			ajax_return(array("status"=>0,"info"=>"密码确认失败","field"=>"cfm_new_pwd","jump"=>""));
		}
		else
		{
			$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where user_name = '".$user_name."' ");
			
			
			if(empty($user))
			{
				ajax_return(array("status"=>0,"info"=>"会员不存在","field"=>"","jump"=>url("user#getpwd")));
			}
			else
			{
					
					
				if($user['is_effect']==0)
					ajax_return(array("status"=>0,"info"=>"会员被禁用","field"=>"","jump"=>url("user#getpwd")));
				else
				{
					if($user['pwd_code']!=$code)
					{
						ajax_return(array("status"=>0,"info"=>"验证失败","field"=>"","jump"=>url("user#getpwd")));
					}
					elseif($user['pwd_code_time']<NOW_TIME)
					{
						ajax_return(array("status"=>0,"info"=>"验证码过期","field"=>"","jump"=>url("user#getpwd")));
					}
					else
					{
						//开始修改密码
						$result = User::modify_pwd($user, $new_pwd);
						if($result['status'])
						{
							$GLOBALS['db']->query("update ".DB_PREFIX."user set pwd_code = '',pwd_code_time = 0 where id = ".$user['id']);
							ajax_return(array("status"=>1,"info"=>"修改成功","field"=>"","jump"=>url("user#getpwd4")));
							
						}
						else
						{
							ajax_return(array("status"=>0,"info"=>$result['info'],"field"=>"","jump"=>""));
						}
						
					}
				}
					
					
			}
			
		}//
			
		
	}
	
	
	public function getpwd4()
	{
		$cron_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_msg_list where is_send = 0");
		$GLOBALS['tmpl']->assign("CRON_COUNT",intval($cron_count));
	
		$GLOBALS['tmpl']->assign("step_count",4);
		$GLOBALS['tmpl']->assign("site_name","密码重置成功 - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword","密码重置成功,".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description","密码重置成功,".app_conf("SITE_DESCRIPTION"));
		init_app_page();
		$GLOBALS['tmpl']->display("user_getpwd.html");
	}
	
	
	public function ajax_login()
	{
		//用于响应跨域的html页，返回json中包含html属性
		$GLOBALS['tmpl']->jsonp_display("inc/ajax_login.html");
	}
	
	public function user_tip()
	{
		global_run();
		$GLOBALS['tmpl']->assign("user",$GLOBALS['user']);
		$GLOBALS['tmpl']->jsonp_display("inc/user_tip.html");
	}
	
	
	
	public function msg()
	{
		set_gopreview();
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			app_redirect(url("user#login"));
		}
		require_once APP_ROOT_PATH.APP_NAME."/Lib/page.php";		
		init_app_page();
		
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*USER_PAGE_SIZE).",".USER_PAGE_SIZE;
		
		$condition = "user_id = ".$GLOBALS['user']['id']." and is_delete = 0 ";
		
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_msg where ".$condition);
		if($total>0)
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_msg where ".$condition." order by msg_time desc limit ".$limit);		
		$page = new Page($total,USER_PAGE_SIZE);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);		
		foreach($list as $k=>$v)
		{
			$list[$k]['msg_time'] = to_date($v['msg_time']);
		}
		$GLOBALS['tmpl']->assign("list",$list);
		
		
		$GLOBALS['tmpl']->display("user_msg.html");
	}
	
	public function loadmsg()
	{
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			$data['status'] = false;
			$data['info'] = "请选登录";
			$data['jump'] = url("user#login");
			ajax_return($data);
		}
		$id = intval($_REQUEST['id']);
		$msg = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_msg where user_id = ".$GLOBALS['user']['id']." and id= ".$id);
		if($msg)
		{
			if($msg['is_read']==0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."user_msg set is_read = 1 where id = ".$id);
				$GLOBALS['db']->query("update ".DB_PREFIX."user set new_msg_count = new_msg_count - 1 where id = ".$GLOBALS['user']['id']);
			}
			$data['status'] = true;
			$data['content'] = $msg['msg_content'];		
			ajax_return($data);
		}
		else
		{
			$data['status'] = false;
			$data['info'] = "消息不存在";
			$data['jump'] = "";
			ajax_return($data);
		}
	}
	
	public function delmsg()
	{
		//set_gopreview();
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			showErr("登录超时",1,url("user#login"));
		}
		
		$id = intval($_REQUEST['id']);
		$msg = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_msg where user_id = ".$GLOBALS['user']['id']." and id= ".$id);
		$GLOBALS['db']->query("update ".DB_PREFIX."user_msg set is_delete = 1 where user_id = ".$GLOBALS['user']['id']." and id = ".$id);
		if($GLOBALS['db']->affected_rows()>0)
		{
			if($msg['is_read']==0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."user set new_msg_count = new_msg_count - 1,msg_count = msg_count -1 where id = ".$GLOBALS['user']['id']);
					
			}	
			else
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."user set msg_count = msg_count -1 where id = ".$GLOBALS['user']['id']);
				
			}
		}
		showSuccess("删除成功",1);
	}
	
	public function rebate()
	{
		set_gopreview();
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			app_redirect(url("user#login"));
		}
		require_once APP_ROOT_PATH.APP_NAME."/Lib/page.php";
		
		init_app_page();
		
		
		
		$condition = " user_id = ".$GLOBALS['user']['id']." ";
		if(isset($_REQUEST['begin_time']))
		{
			$begin_time = strim($_REQUEST['begin_time']);
			if($begin_time!="")$begin_time_span = to_timespan($begin_time);
			if($begin_time_span)
				$condition.=" and create_time >= ".$begin_time_span." ";
			$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		}
		if(isset($_REQUEST['end_time']))
		{
			$end_time = strim($_REQUEST['end_time']);
			if($end_time!="")$end_time_span = to_timespan($end_time)+3600*24;
			if($end_time_span)
				$condition.=" and create_time < ".$end_time_span." ";
			$GLOBALS['tmpl']->assign("end_time",$end_time);
		}
		
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*USER_PAGE_SIZE).",".USER_PAGE_SIZE;
		
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_rebate where ".$condition);
		if($total>0)
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_rebate where ".$condition." order by create_time desc limit ".$limit);
		
		$page = new Page($total,USER_PAGE_SIZE);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		foreach($list as $k=>$v)
		{
			$list[$k]['create_time'] = to_date($v['create_time']);
			$list[$k]['pay_time'] = to_date($v['pay_time']);
			$list[$k]['money'] = format_price_to_display($v['money']);
		}
		$GLOBALS['tmpl']->assign("list",$list);
		
		$GLOBALS['tmpl']->display("user_rebate.html");
	}
        public function init_user_active(){
            global_run();
            $uid = intval($_GET['uid']);
            $is_follow_user_active = $_GET['is_follow_user_active'] == 0?0:1;
            
            if(!empty($GLOBALS['user'])) //验证是否登录
            {
                 $home_id = $GLOBALS['user']['id']; //登录用户自己的ID是
            }
            if($uid>0){
                if($uid == $home_id){
                    $user_info = $GLOBALS['user'];
                }else{
                    $user_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user WHERE id=".$uid);
                }
            }else{
                if($home_id>0){
                    $uid = $home_id;
                    $user_info = $GLOBALS['user'];
                }  else {
                    $result['status'] = 2;
                }
            }
            

            if($uid>0){
                $load_index = $_REQUEST['load_index'];
            
                //limit
                $page = $_GET['p']>1?$_GET['p']:1;

                $USER_ACTIVE_PAGE_LOAD_GOUNT = APP_CONF("USER_ACTIVE_PAGE_LOAD_GOUNT");
                $USER_ACTIVE_PAGE_ITEM_COUNT = APP_CONF("USER_ACTIVE_PAGE_ITEM_COUNT");
                $waterfull_count =  $GUIDE_PAGE_LOAD_GOUNT*$GUIDE_PAGE_ITEM_COUNT;
                if($load_index>1){
                    $limit_start = ($load_index-1)*$USER_ACTIVE_PAGE_ITEM_COUNT;
                }else{
                    $limit_start = 0;
                }
                if($page>1){
                    $limit_start = ($page-1)*$waterfull_count+$limit_start;
                }

                $limit = " limit ".$limit_start.",".$USER_ACTIVE_PAGE_ITEM_COUNT;

                $page_site = $USER_ACTIVE_PAGE_LOAD_GOUNT*$USER_ACTIVE_PAGE_ITEM_COUNT;
                
                if($is_follow_user_active){
                    $total_count = $GLOBALS['db']->getOne("SELECT count(ua.id) FROM ".DB_PREFIX."user_active as ua LEFT JOIN ".DB_PREFIX."user_follow as uf on (uf.user_id =".$uid." ) WHERE ua.user_id = uf.follow_id or ua.user_id =".$uid." GROUP BY ua.id");
                }else{
                    $total_count = $GLOBALS['db']->getOne("SELECT count(ua.id) FROM ".DB_PREFIX."user_active as ua WHERE ua.user_id =".$uid);
                }
                

                $param = array();
                $pager = buildPage("user#init_user_active",$param,$total_count,$page,$page_site,1);

                $GLOBALS['tmpl']->assign("pager",$pager);

                $result['pager'] = $GLOBALS['tmpl']->fetch("inc/pages.html");
                //排序
                $orderby = ' ORDER BY id DESC ';
                if($is_follow_user_active){
                    $sql = "SELECT ua.* FROM ".DB_PREFIX."user_active as ua LEFT JOIN ".DB_PREFIX."user_follow as uf on (uf.user_id =".$uid." ) WHERE ua.user_id = uf.follow_id or ua.user_id =".$uid." GROUP BY ua.id ".$orderby.$limit;
                }else{
                    $sql = "SELECT ua.* FROM ".DB_PREFIX."user_active as ua  WHERE  ua.user_id =".$uid.$orderby.$limit;
                }
                
                $list = $GLOBALS['db']->getAll($sql);
                if($list){
                    foreach($list as $k=>$v){
                        $images = unserialize($v['image_list']);
                        if(count($images)>0){
                            $v['image'] = $images[0]['src'];
                        }
                        $list[$k] = $v;
                        $userids[] = $v['user_id'];
                    }
                    $userids = array_unique($userids);

                    require_once APP_ROOT_PATH."system/libs/user.php";
                    $user_avatars = User::get_user_avatar($userids);
                    //定义跳转的module_action 1.购物分享 2.游记发表 3.线路点评 4.门票点评
                    $ma_arr = array(
                        '1'=>"",
                        '2'=>"guide#show",
                        '3'=>"tours#view",
                        '4'=>"spot#view",
                    );
                    foreach($list as $k=>$v){
                        $v['avatar'] = $user_avatars[$v['user_id']]['avatar'] ;

                        $v['url'] = url($ma_arr[$v['from_type']],array("id"=>$v['from_id']));
                        $list[$k] = $v;
                    }
                    $result['total_count'] = $total_count;
                    $GLOBALS['tmpl']->assign("list",$list);
                    $result['html'] = $GLOBALS['tmpl']->fetch("waterfall/user_active_item.html");
                    $result['status'] = 1;
                }else{
                    //空数据
                    $result['html'] = '';
                }
            }else{
                $result['html'] = '';
            }
            ajax_return($result);
            
        }
    /**
     * 用户个人主页
     */    
    public function home(){
        set_gopreview();
        global_run();
        $uid = intval($_GET['uid']);
        if(!empty($GLOBALS['user'])) //验证是否登录
        {
             $home_id = $GLOBALS['user']['id']; //登录用户自己的ID是
        }
        if($uid>0){
            if($uid == $home_id){
                $user_info = $GLOBALS['user'];
            }else{
                $user_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user WHERE id=".$uid);
            }
        }else{
            if($home_id>0){
                $uid = $home_id;
                $user_info = $GLOBALS['user'];
            }  else {
                app_redirect(url("user#login"));
                exit;
            }
        }
        $GLOBALS['tmpl']->assign('uid',$uid);
        
        
        if(strim($_REQUEST['home_nav'])){
            $home_nav = $_REQUEST['home_nav'];
        }else{
            $home_nav = "share";
        }
        
        $province_name = $user_info['province_id']?$GLOBALS['db']->getOne("SELECT name FROM ".DB_PREFIX."province WHERE id=".intval($user_info['province_id'])):'';
        $city_name = $user_info['city_id']?$GLOBALS['db']->getOne("SELECT name FROM ".DB_PREFIX."city WHERE id=".intval($user_info['city_id'])):'';
        
        //判断是关注或者粉丝
        if($home_nav == "follow" ||  $home_nav=="fans"){
            require_once APP_ROOT_PATH.APP_NAME."/Lib/page.php";

            $page = intval($_REQUEST['p']);
            if($page==0)
                    $page = 1;
            $limit = (($page-1)*USER_PAGE_SIZE).",".USER_PAGE_SIZE;
            
            if($home_nav=="follow"){
                $field_name = "GROUP_CONCAT(convert(follow_id,char)) as id";
                $condition = " WHERE user_id=".$user_info['id'];
                
            }elseif($home_nav=="fans"){
                $field_name = "user_id as id,is_followme";
                $condition = " WHERE follow_id=".$user_info['id'];
              
            }

            $total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_follow ".$condition);
            
            if($total>0){
                $user_level = load_auto_cache("user_level");
                if($home_nav=="fans"){
                    $fans_users = $GLOBALS['db']->getAll("SELECT ".$field_name." FROM ".DB_PREFIX."user_follow ".$condition." LIMIT ".$limit);
                    foreach($fans_users as $k=>$v){
                        $f_fans_user[$v['id']] = $v['is_followme'];
                        $user_ids_arr[] = $v['id'];
                    }
                    $user_ids = implode(',', $user_ids_arr);
                    $user_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."user WHERE id in(".$user_ids.")");
                    foreach($user_list as $k=>$v){
                        $user_list[$k]['is_follow'] = $f_fans_user[$v['id']];
                        $user_list[$k]['level_name'] = $user_level[$v['level_id']]['name'];
                    }
                }else{
                    $user_ids = $GLOBALS['db']->getOne("SELECT ".$field_name." FROM ".DB_PREFIX."user_follow ".$condition." LIMIT ".$limit);
                    $user_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."user WHERE id in(".$user_ids.")");
 
                    foreach($user_list as $k=>$v){
                        $user_list[$k]['level_name'] = $user_level[$v['level_id']]['name'];
                        $user_list[$k]['is_follow'] = 1;
                    }
                }

            }
            $page = new Page($total,USER_PAGE_SIZE);   //初始化分页对象
            $p  =  $page->show();
            $GLOBALS['tmpl']->assign('pages',$p);
            $GLOBALS['tmpl']->assign('user_list',$user_list);    
        }

        $GLOBALS['tmpl']->assign("home_nav",$home_nav);
        $GLOBALS['tmpl']->assign("province_name",$province_name);
        $GLOBALS['tmpl']->assign("city_name",$city_name);
        $GLOBALS['tmpl']->assign("user_info",$user_info);
        
        init_app_page();
        $GLOBALS['tmpl']->display("uc_home.html");
    }
    public function init_user_home_active(){
            global_run();
            $uid = intval($_GET['uid']);
            $is_follow_user_active = $_GET['is_follow_user_active'] == 0?0:1;
            
            if(!empty($GLOBALS['user'])) //验证是否登录
            {
                 $home_id = $GLOBALS['user']['id']; //登录用户自己的ID是
            }
            if($uid>0){
                if($uid == $home_id){
                    $user_info = $GLOBALS['user'];
                }else{
                    $user_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user WHERE id=".$uid);
                }
            }else{
                if($home_id>0){
                    $uid = $home_id;
                    $user_info = $GLOBALS['user'];
                }  else {
                    $result['status'] = 2;
                }
            }
            

            if($uid>0){
                $load_index = $_REQUEST['load_index'];
            
                //limit
                $page = $_GET['p']>1?$_GET['p']:1;

                $USER_ACTIVE_PAGE_LOAD_GOUNT = APP_CONF("USER_ACTIVE_PAGE_LOAD_GOUNT");
                $USER_ACTIVE_PAGE_ITEM_COUNT = APP_CONF("USER_ACTIVE_PAGE_ITEM_COUNT");
                $waterfull_count =  $GUIDE_PAGE_LOAD_GOUNT*$GUIDE_PAGE_ITEM_COUNT;
                if($load_index>1){
                    $limit_start = ($load_index-1)*$USER_ACTIVE_PAGE_ITEM_COUNT;
                }else{
                    $limit_start = 0;
                }
                if($page>1){
                    $limit_start = ($page-1)*$waterfull_count+$limit_start;
                }

                $limit = " limit ".$limit_start.",".$USER_ACTIVE_PAGE_ITEM_COUNT;

                $page_site = $USER_ACTIVE_PAGE_LOAD_GOUNT*$USER_ACTIVE_PAGE_ITEM_COUNT;
                
                if($is_follow_user_active){
                    $total_count = $GLOBALS['db']->getOne("SELECT count(ua.id) FROM ".DB_PREFIX."user_active as ua LEFT JOIN ".DB_PREFIX."user_follow as uf on (uf.user_id =".$uid." ) WHERE ua.user_id = uf.follow_id or ua.user_id =".$uid." GROUP BY ua.id");
                }else{
                    $total_count = $GLOBALS['db']->getOne("SELECT count(ua.id) FROM ".DB_PREFIX."user_active as ua WHERE ua.user_id =".$uid);
                }
                

                $param = array();
                $pager = buildPage("user#init_user_active",$param,$total_count,$page,$page_site,1);

                $GLOBALS['tmpl']->assign("pager",$pager);

                $result['pager'] = $GLOBALS['tmpl']->fetch("inc/pages.html");
                //排序
                $orderby = ' ORDER BY id DESC ';
                if($is_follow_user_active){
                    $sql = "SELECT ua.* FROM ".DB_PREFIX."user_active as ua LEFT JOIN ".DB_PREFIX."user_follow as uf on (uf.user_id =".$uid." ) WHERE ua.user_id = uf.follow_id or ua.user_id =".$uid." GROUP BY ua.id ".$orderby.$limit;
                }else{
                    $sql = "SELECT ua.* FROM ".DB_PREFIX."user_active as ua  WHERE  ua.user_id =".$uid.$orderby.$limit;
                }
                
                $list = $GLOBALS['db']->getAll($sql);
                if($list){
                    foreach($list as $k=>$v){
                        $images = unserialize($v['image_list']);
                        if(count($images)>0){
                            $v['image'] = $images[0]['src'];
                        }
                        $list[$k] = $v;
                        $userids[] = $v['user_id'];
                    }
                    $userids = array_unique($userids);

                    require_once APP_ROOT_PATH."system/libs/user.php";
                    $user_avatars = User::get_user_avatar($userids);
                    //定义跳转的module_action 1.购物分享 2.游记发表 3.线路点评 4.门票点评
                    $ma_arr = array(
                        '1'=>"",
                        '2'=>"guide#show",
                        '3'=>"tours#view",
                        '4'=>"spot#view",
                    );
                    foreach($list as $k=>$v){
                        $v['avatar'] = $user_avatars[$v['user_id']]['avatar'] ;

                        $v['url'] = url($ma_arr[$v['from_type']],array("id"=>$v['from_id']));
                        $list[$k] = $v;
                    }
                    $result['total_count'] = $total_count;
                    $GLOBALS['tmpl']->assign("list",$list);
                    $result['html'] = $GLOBALS['tmpl']->fetch("waterfall/user_home_active_item.html");
                    $result['status'] = 1;
                }else{
                    //空数据
                    $result['html'] = '';
                }
            }else{
                $result['html'] = '';
            }
            ajax_return($result);
            
        }
    
    public function user_home_follow($uid){
        
    }
    public function user_home_fans($uid){
        
    }
    
    public function get_user_info_tip(){
        global_run();
        if(empty($GLOBALS['user'])) //验证是否登录
        {
             $GLOBALS['tmpl']->assign("home_id",$GLOBALS['user']['id']);
        }
        $uid = intval($_REQUEST['uid']);
        $home_id = $GLOBALS['user']['id'];
        $is_me = $uid == $home_id?1:0;
        if(!$is_me){
            if($home_id>0){
                $is_follow = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user_follow WHERE user_id=".$home_id." AND follow_id=".$uid)>0?1:0;  
            }else{
                $is_follow = 0;
            }
            $user_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user WHERE id=".$uid);
        }else{
            $user_info = $GLOBALS['user'];
        }
        
        $user_level = load_auto_cache("user_level");
        $user_info['level_name'] = $user_level[$user_info['level_id']]['name'];
        
        $GLOBALS['tmpl']->assign("is_me",$is_me);
        $GLOBALS['tmpl']->assign("is_follow",$is_follow);
        $GLOBALS['tmpl']->assign("user_info",$user_info);
        echo  $GLOBALS['tmpl']->fetch("inc/user_info_tip.html");
        exit;
    }
    
    public function check_login(){
        global_run();
        if(empty($GLOBALS['user'])) //验证是否登录
        {
            $result['status'] = 0;
            $result['uid'] = 0;
        }else{
            $result['status'] = 1;
            $result['uid'] = $GLOBALS['user']['id'];
        }
        ajax_return($result);
    }
    //关注用户
    public function user_follow(){
        global_run();
        $uid = intval($_REQUEST['uid']);
        if(empty($GLOBALS['user'])) //验证是否登录
        {
            $result['status'] = 2; //未登录
        }elseif($uid>0){
            $home_id = $GLOBALS['user']['id'];
            
            $is_follow = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user_follow WHERE user_id=".$home_id." AND follow_id=".$uid);
            if($is_follow){ //一关注就删除
                $GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."user_follow WHERE user_id=".$home_id." AND follow_id=".$uid);
                $result['status'] = -1;
                $follow_status = "-1";
                $follow_where_str = " AND follow_count>0 ";
                $fans_where_str = " AND fans_count>0 ";
                $is_followme_status = 0;
            }else{
                $user_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user WHERE id=".$uid);
                if(!empty($user_info)){
                    //是否也关注我了
                    $is_followme = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user_follow WHERE user_id=".$uid." AND follow_id=".$home_id);

                    $data = array(
                        'user_id'=>$home_id,
                        'follow_id'=>$uid,
                        'nickname'=>$user_info['nickname'],
                        'create_time'=>NOW_TIME,
                        'is_followme'=>$is_followme
                        );
                    $GLOBALS['db']->autoExecute(DB_PREFIX."user_follow",$data,"INSERT");
                    $result['status'] =1;
                    $follow_status = "+1";
                    $where_str = "";
                }
                $is_followme_status=1;
            } 
            //更新关注数
            $GLOBALS['db']->query("UPDATE ".DB_PREFIX."user set follow_count = follow_count".$follow_status." WHERE id =".$home_id.$follow_where_str);
            
            //更新粉丝数
            $GLOBALS['db']->query("UPDATE ".DB_PREFIX."user set fans_count = fans_count".$follow_status." WHERE id =".$uid.$fans_where_str);
            
            //同步关注表 是否互相关注
            $GLOBALS['db']->query("UPDATE ".DB_PREFIX."user_follow set is_followme = ".$is_followme_status." WHERE user_id =".$uid);
        }else{
                $result['status'] =3;//用户不存在
        }
        
        ajax_return($result);
    }
    /**
     * 预留多个关注
     */
    public function user_follows(){
        
    }
    
    /**
     * 删除粉丝
     */
    public function remove_fans(){
        
    }
}
?>