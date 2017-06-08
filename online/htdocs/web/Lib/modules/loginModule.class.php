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
		$GLOBALS['tmpl']->display("login_qq.html");
	}
	
	public function qq()
	{		
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("site_name","qq登录 - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword","qq登录,".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description","qq登录,".app_conf("SITE_DESCRIPTION"));
		$GLOBALS['tmpl']->assign("preview", get_gopreview());
		$GLOBALS['tmpl']->display("login_qq.html");
	}

	public function check()
	{
		$ajax = intval($_REQUEST['ajax']);
		$user_openid = strim($_POST['openid']);
		$user_nickname = strim($_POST['nickname']);
		$user_figureurl = strim($_POST['figureurl']);
		$user_accesstoken = strim($_POST['accesstoken']);
		$checkresult = User::checkqq($user_openid, $user_nickname,$user_figureurl, $user_accesstoken);
		es_session::start();
		es_session::set("openid", $user_openid);
		es_session::set("qq_img", $user_figureurl);
		es_session::set("qq_name", $user_nickname);
		es_session::set("login_type","qq");
		es_session::close();
		if ($checkresult['status'] == 2) {
			$user_qq = $checkresult['user'];
			$user_id = $user_qq['user_id'];
			User::loginByUserId($user_id);
		  ajax_return(array("status"=>1,"info"=>"恭喜您！登录成功","jump"=>get_gopreview()));
		} elseif ($result['status'] == 1 || $result['status'] == 0) {
			$qquser = $this->qq_auto_user($user_openid);
			if ($qquser['status'] == 1) {
				$user = $qquser['user'];
				$relresult = User::do_rel($user['user_name'],$user['user_pwd'],$user_openid);
				ajax_return(array("status"=>2,"info"=>"第一次使用qq登录本平台，登录成功","jump"=>get_gopreview())); 
			} else {
				ajax_return(array("status"=>3,"info"=>"登录失败","jump"=>""));
			}
		}
	}

	/**
	 * 将qq登录后自动创建user信息， 并将userid保存在对应的user_qq中
	 * @param string $openid
	 * 返回 @param array (status,info,jump,user)
	 * array("status"=>1,"info"=>"恭喜您！注册成功","jump"=>get_gopreview(), 'user'=>$user_data);
	 * status: 0 失败 1 创建成功
	 */
	public function qq_auto_user($openid)
	{
		$user_name = 'qq_'.$openid;
		$user_pwd  = '123';

		$ck = User::checkfield("user_name", $user_name);		
		if($ck['status']==0)
		{
			return array("status"=>0,"info"=>$ck['info'],"field"=>"user_name");
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
					return array("status"=>0,"info"=>$ck['info'],"field"=>$ck['field']);
				}
			}
		}
		
		$user_data = array();
		$user_data['user_name'] = $user_name;
		
		$user_data['salt'] = USER_SALT;
		$user_data['user_pwd'] = md5($user_pwd.$user_data['salt']);
		$user_data['is_effect'] = 1;
		$user_data['create_time'] = NOW_TIME;
		$user_data['integrate_id'] = intval($ck['data']);
		
		$user_data['source'] = empty($GLOBALS['ref'])?"native":$GLOBALS['ref'];  //来路
		$user_data['pid'] = intval($GLOBALS['ref_pid']); //推荐人
		$user_data['nickname'] = $user_data['user_name'];
		$user_data['regist_ip'] = CLIENT_IP;
		require_once APP_ROOT_PATH."system/libs/city.php";
		$user_data['regist_city'] = City::locate_city_name(CLIENT_IP);
		$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_data,"INSERT","","SILENT");
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

			return array("status"=>1,"info"=>"恭喜您！注册成功","jump"=>get_gopreview(), 'user'=>$user_data);
		}
		else
		{
			return array("status"=>0,"info"=>"服务器繁忙，请重试","field"=>"","jump"=>"");
		}
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