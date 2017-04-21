<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(88522820@qq.com)
// +----------------------------------------------------------------------

$api_lang = array(
	'name'	=>	'新浪微博api登录接口',
	'app_key'	=>	'新浪API应用APP_KEY',
	'app_secret'	=>	'新浪API应用APP_SECRET',
	'app_url'	=>	'回调地址',
);

$config = array(
	'app_key'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //新浪API应用的KEY值
	'app_secret'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //新浪API应用的密码值
	'app_url'	=>	array(
		'INPUT_TYPE'	=>	'0'
	),
);

/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{

    $module['class_name']    = 'sina';

    /* 名称 */
    $module['name']    = $api_lang['name'];

	$module['config'] = $config;
	$module['is_weibo'] = 1;  //可以同步发送微博
	
	$module['lang'] = $api_lang;
    
    return $module;
}

// 新浪的api登录接口
require_once(APP_ROOT_PATH.'system/libs/api_login.php');
class sina_api implements api_login { 
	
	private $api;
	
	public function __construct($api)
	{
		$api['config'] = unserialize($api['config']);
		$this->api = $api;
	}
	
	public function get_api_url()
	{
		require_once APP_ROOT_PATH.'system/api_login/sina/saetv2.ex.class.php';
		$o = new SaeTOAuthV2($this->api['config']['app_key'],$this->api['config']['app_secret']);

		if($this->api['config']['app_url']=="")
		{
			$app_url = url("api_callback#sina");
		}
		else
		{
			$app_url = $this->api['config']['app_url'];
		}
		$aurl = $o->getAuthorizeURL($app_url);

		
		return $aurl;
	}
	
	
	public function callback()
	{
		require_once APP_ROOT_PATH.'system/api_login/sina/saetv2.ex.class.php';

		$o = new SaeTOAuthV2($this->api['config']['app_key'],$this->api['config']['app_secret']);
		if (isset($_REQUEST['code'])) {
			$keys = array();
			$keys['code'] = $_REQUEST['code'];
			if($this->api['config']['app_url']=="")
			{
				$app_url = url("api_callback#sina");
			}
			else
			{
				$app_url = $this->api['config']['app_url'];
			}
			$keys['redirect_uri'] = $app_url;
			try {
				$token = $o->getAccessToken( 'code', $keys ) ;
			} catch (OAuthException $e) {
				//print_r($e);exit;
				showErr("授权失败,错误信息：".$e->getMessage());
				die();
			}
		}
		
		
		$c = new SaeTClientV2($this->api['config']['app_key'],$this->api['config']['app_secret'] ,$token['access_token'] );
		$ms  = $c->home_timeline(); // done
		$uid_get = $c->get_uid();
		$uid = $uid_get['uid'];
		$msg = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息
		
		if(intval($msg['error_code'])!=0){
			showErr("授权失败,错误代码:".$msg['error_code']);
			die();
		}
		

		//print_r($msg);die();
		$user_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where sina_id = '".$msg['id']."' and sina_id <> ''");	
		
		if($user_data)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."user set sina_token = '".$token['access_token']."',sina_expire_time = '".(NOW_TIME+intval($token['expires_in']))."' where id = ".$user_data['id']);
			User::do_login_save($user_data);
			app_redirect_preview();	
		}
		else
		{
			//开始自动创建用户
			$user_data = array();
			$user_data['user_name'] = $user_data['nickname'] = $msg['screen_name'];			
			$user_data['salt'] = USER_SALT;
			$user_data['user_pwd'] = md5(rand(1111,9999).$user_data['salt']);
			$user_data['is_effect'] = 1;
			$user_data['create_time'] = NOW_TIME;	
			
			$user_data['source'] = empty($GLOBALS['ref'])?"native":$GLOBALS['ref'];  //来路
			$user_data['pid'] = intval($GLOBALS['ref_pid']); //推荐人
			$user_data['regist_ip'] = CLIENT_IP;
			$user_data['sina_id'] = $msg['id'];
			$user_data['sina_token'] = $token['access_token'];
			$user_data['sina_expire_time'] = NOW_TIME+intval($token['expires_in']);
			$user_data['is_temp'] = 1;
			require_once APP_ROOT_PATH."system/libs/city.php";
			$user_data['regist_city'] = City::locate_city_name(CLIENT_IP);
			$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_data,"INSERT","","SILENT");
			$user_data['id'] = intval($GLOBALS['db']->insert_id());
			if($user_data['id']==0)
			{			
				do
				{			
					$user_data['user_name'] = $user_data['nickname'] = $msg['screen_name']."_".rand(0,999);
					$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_data,"INSERT","","SILENT");
					$user_data['id'] = intval($GLOBALS['db']->insert_id());
				}while($user_data['id']==0);
			}
			User::do_login_save($user_data);
			app_redirect(url("profile"));
			
		}
		
		
	}

	public function get_title()
	{
		return '新浪微博登录';
	}
	
	//同步发表到新浪微博
	public function send_message($data)
	{
		static $client = NULL;
		if($client === NULL)
		{
			require_once APP_ROOT_PATH.'system/api_login/sina/saetv2.ex.class.php';
			$uid = intval($GLOBALS['user']['id']);
			$udata = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$uid);
			if(empty($udata)||$udata['sina_expire_time']<NOW_TIME)
			{
				$result['status'] = false;
				$result['msg'] = "新浪微博授权已超时，请重新登录获取新的授权";
				return $result;
				//ajax_return($result);
			}
			else
			{
				$client = new SaeTClientV2($this->api['config']['app_key'],$this->api['config']['app_secret'],$udata['sina_token']);
			}
		}
		try
		{
			if(empty($data['img']))
				$msg = $client->update($data['content']);
			else
				$msg = $client->upload($data['content'],$data['img']);


			if($msg['error'])
			{
				$result['status'] = false;
				$result['msg'] = "新浪微博同步失败，请偿试重新通过腾讯微博登录或得新授权。";
				return $result;
				//ajax_return($result);
			}
			else
			{
				$result['status'] = true;
				$result['msg'] = "success";
				return $result;
				//ajax_return($result);
			}

		}
		catch(Exception $e)
		{
			$result['status'] = false;
			$result['msg'] = "新浪微博同步失败";
			return $result;
			//ajax_return($result);
		}
	}
	
}

?>