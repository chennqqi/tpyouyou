<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(88522820@qq.com)
// +----------------------------------------------------------------------

$api_lang = array(
	'name'	=>	'腾讯微博登录插件',
	'app_key'	=>	'腾讯API应用APP_KEY',
	'app_secret'	=>	'腾讯API应用APP_SECRET',
	'app_url'	=>	'回调地址',
);

$config = array(
	'app_key'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //腾讯API应用的KEY值
	'app_secret'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //腾讯API应用的密码值
	'app_url'	=>	array(
		'INPUT_TYPE'	=>	'0'
	),
);

/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'tencent';

    /* 名称 */
    $module['name']    = $api_lang['name'];

	$module['config'] = $config;
	$module['is_weibo'] = 1;  //可以同步发送微博
	
	$module['lang'] = $api_lang;
    
    return $module;
}

// 腾讯的api登录接口
require_once(APP_ROOT_PATH.'system/libs/api_login.php');
class tencent_api implements api_login {
	
	private $api;
	
	public function __construct($api)
	{		
		$api['config'] = unserialize($api['config']);
		$this->api = $api;		
	}
	
	public function get_api_url()
	{
		require_once APP_ROOT_PATH.'system/api_login/tencent/Tencent.php';

		OAuth::init($this->api['config']['app_key'], $this->api['config']['app_secret']);
		if($this->api['config']['app_url']=="")
		{
			$app_url = url("api_callback#tencent");
		}
		else
		{
			$app_url = $this->api['config']['app_url'];
		}
		$aurl = OAuth::getAuthorizeURL($app_url);
			
		
		return $aurl;
	}
		
	public function callback()
	{
		es_session::start();		
		require_once APP_ROOT_PATH.'system/api_login/Tencent/Tencent.php';
		OAuth::init($this->api['config']['app_key'], $this->api['config']['app_secret']);
		
		$code = trim(addslashes($_REQUEST['code']));
		$openid = trim(addslashes($_REQUEST['openid']));
		$openkey = trim(addslashes($_REQUEST['openkey']));
		
		if($this->api['config']['app_url']=="")
		{
			$app_url = url("api_callback#tencent");
		}
		else
		{
			$app_url = $this->api['config']['app_url'];
		}
		
		$token_url = OAuth::getAccessToken($code,$app_url);
		$result = Http::request($token_url);
		$result = preg_replace('/[^\x20-\xff]*/', "", $result); //清除不可见字符
        $result = iconv("utf-8", "utf-8//ignore", $result); //UTF-8转码
        
        parse_str($result,$msg);

		
		
		if ($msg['openid']) 
		{
			$user_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where tqq_id = '".$msg['openid']."' and tqq_id <> ''");	
			if($user_data)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."user set tqq_token = '".$msg['access_token']."',tqq_expire_time = '".(NOW_TIME+intval($msg['expires_in']))."' where id = ".$user_data['id']);
				User::do_login_save($user_data);
				app_redirect_preview();	
			}
			else{
				//开始自动创建用户
				$user_data = array();
				$user_data['user_name'] = $user_data['nickname'] = $msg['nick'];
				$user_data['salt'] = USER_SALT;
				$user_data['user_pwd'] = md5(rand(1111,9999).$user_data['salt']);
				$user_data['is_effect'] = 1;
				$user_data['create_time'] = NOW_TIME;
					
				$user_data['source'] = empty($GLOBALS['ref'])?"native":$GLOBALS['ref'];  //来路
				$user_data['pid'] = intval($GLOBALS['ref_pid']); //推荐人
				$user_data['regist_ip'] = CLIENT_IP;
				$user_data['tqq_id'] = $msg['openid'];
				$user_data['tqq_token'] = $msg['access_token'];
				$user_data['tqq_expire_time'] = NOW_TIME+intval($msg['expires_in']);
				$user_data['is_temp'] = 1;
				require_once APP_ROOT_PATH."system/libs/city.php";
				$user_data['regist_city'] = City::locate_city_name(CLIENT_IP);
				$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_data,"INSERT","","SILENT");
				$user_data['id'] = intval($GLOBALS['db']->insert_id());
				if($user_data['id']==0)
				{
					do
					{
						$user_data['user_name'] = $user_data['nickname'] = $msg['nick']."_".rand(0,999);
						$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_data,"INSERT","","SILENT");
						$user_data['id'] = intval($GLOBALS['db']->insert_id());
					}while($user_data['id']==0);
				}
				User::do_login_save($user_data);
				app_redirect(url("profile"));
			}
		}
		else
		{
			showErr("授权失败");
		}
		

		
		
	}
	
	public function get_title()
	{
		return '腾讯微博登录';
	}
	
	public function send_message($data)
	{
		
			require_once APP_ROOT_PATH.'system/api_login/tencent/Tencent.php';
			OAuth::init($this->api['config']['app_key'], $this->api['config']['app_secret']);		
			
			$uid = intval($GLOBALS['user']['id']);
			$udata = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$uid);
			
			if(empty($udata)||$udata['tqq_expire_time']<NOW_TIME)
			{
				$result['status'] = false;
				$result['msg'] = "新浪微博授权已超时，请重新登录获取新的授权";
				return $result;
				//ajax_return($result);
			}
			else
			{
				es_session::set("t_access_token",$udata['tqq_token']);
				es_session::set("t_openid",$udata['tqq_id']);
				es_session::set("t_openkey",$this->api['config']['app_key']);
				if (es_session::get("t_access_token")|| (es_session::get("t_openid")&&es_session::get("t_openkey")))
				{
					if(!empty($data['img']))
					{
						$params = array(
								'content' => $data['content'],
								'clientip'	=>	CLIENT_IP,
								'format'	=>	'json'
						);
						$multi = array('pic' => $data['img']);
						$r = Tencent::api('t/add_pic', $params, 'POST', $multi);
					}
					else
					{
						$params = array(
								'content' => $data['content'],
								'clientip'	=>	CLIENT_IP,
								'format'	=>	'json'
						);
						$r = Tencent::api('t/add', $params, 'POST');
					}
				
				
					$msg = json_decode($r,true);
				
				
				
					if(intval($msg['errcode'])==0)
					{
						$result['status'] = true;
						$result['msg'] = "success";
						return $result;
						//ajax_return($result);
					}
					else
					{
						$result['status'] = false;
						$result['msg'] = "腾讯微博".$msg['msg'];
						return $result;
						//ajax_return($result);
					}
				
				}
			}
			

	
	}
	
   
}
?>