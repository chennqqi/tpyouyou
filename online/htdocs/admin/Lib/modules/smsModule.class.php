<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class smsModule extends AuthModule
{
	
	private function read_modules()
	{
		$directory = APP_ROOT_PATH."system/sms/";
		$read_modules = true;
		$dir = @opendir($directory);
		$modules     = array();
	
		while (false !== ($file = @readdir($dir)))
		{
			if (preg_match("/^.*?\.php$/", $file))
			{
				$modules[] = require_once($directory.$file);
			}
		}
		@closedir($dir);
		unset($read_modules);
	
		foreach ($modules AS $key => $value)
		{
			ksort($modules[$key]);
		}
		ksort($modules);
	
		return $modules;
	}
	
	public function index()
	{		
		
		$modules = $this->read_modules();
		$db_modules = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."sms");
		foreach($modules as $k=>$v)
		{
			$sms_info = array();
			foreach($db_modules as $kk=>$vv)
			{
				if($v['class_name']==$vv['class_name'])
				{
					//已安装
					$modules[$k]['id'] = $vv['id'];
					$modules[$k]['is_effect'] = $vv['is_effect'];
					$modules[$k]['description'] = $vv['description'];
					$modules[$k]['installed'] = 1;
					$vv['config'] = unserialize($vv['config']);
					$sms_info = $vv;
					break;
				}
			}
			if($modules[$k]['installed'] != 1)
			$modules[$k]['installed'] = 0;	
			else
			{
				if($modules[$k]['is_effect']==1)
				{
					include APP_ROOT_PATH."system/sms/".$modules[$k]['class_name']."_sms.php";
					$sms_class = $modules[$k]['class_name']."_sms";
					$sms_module = new $sms_class($sms_info);
					$modules[$k]['name'] = $sms_module->getSmsInfo();
				}
			}			
		}
		$GLOBALS['tmpl']->assign("sms_list",$modules);
				
		$GLOBALS['tmpl']->assign("formaction",admin_url("sms"));
		$GLOBALS['tmpl']->assign("senddemourl",admin_url("sms#send_demo",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("seteffecturl",admin_url("sms#set_effect",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("uninstallurl",admin_url("sms#uninstall",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("sms#edit",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("installurl",admin_url("sms#install",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/sms/index.html");
	}	
	
	
	
	
	public function install()
	{
		$ajax = intval($_REQUEST['ajax']);
		$class_name = strim($_REQUEST['class_name']);
		$directory = APP_ROOT_PATH."system/sms/";
		$read_modules = true;
	
		$file = $directory.$class_name."_sms.php";
		if(file_exists($file))
		{
			$module = require_once($file);
			$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."sms where class_name ='".$class_name."'");
			if($rs > 0)
			{
				showErr(lang("SMS_INSTALLED"),$ajax);
			}
		}
		else
		{
			showErr(lang("INVALID_OPERATION"),$ajax);
		}
	
	
		//开始插入数据
		$data['name'] = $module['name'];
		$data['class_name'] = $module['class_name'];
		$data['server_url'] = $module['server_url'];
		$data['lang'] = $module['lang'];
		$data['config'] = $module['config'];
		
		foreach($data['config'] as $k=>$v)
		{
			$data['config'][$k]['SHOW_TITLE'] = $data['lang'][$k];
			if(isset($data['config'][$k]['VALUES'])&&is_array($data['config'][$k]['VALUES']))
			{
				foreach($data['config'][$k]['VALUES'] as $kk=>$vv)
				{
					$val = array();
					$val['SHOW_TITLE'] = $data['lang'][$k."_".$vv];
					$val['VALUE'] = $vv;
					$data['config'][$k]['VALUES'][$kk] = $val;
				}
			}
		}
		

	
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->assign("formaction",admin_url("sms#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/sms/install.html");
	
	}
	
	
	public function insert()
	{
		$ajax = intval($_REQUEST['ajax']);		
		$data['name'] = strim($_REQUEST['name']);
		$data['class_name'] = strim($_REQUEST['class_name']);
		$data['user_name'] = strim($_REQUEST['user_name']);
		$data['password'] = strim($_REQUEST['password']);
		$data['description'] = strim($_REQUEST['description']);
		$data['config'] = serialize($_REQUEST['config']);
		$data['server_url'] = strim($_REQUEST['server_url']);
		// 更新数据
		$log_info = $data['name'];
		
		$GLOBALS['db']->query("delete from ".DB_PREFIX."sms where class_name = '".$data['class_name']."'");
		$GLOBALS['db']->autoExecute(DB_PREFIX."sms",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log($log_info.lang("INSTALL_SUCCESS"),1);
			showSuccess(lang("INSTALL_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("INSTALL_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	
	public function edit() {
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST ['id']);		
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms where id = ".$id);
	
		$directory = APP_ROOT_PATH."system/sms/";
		$read_modules = true;
	
		$file = $directory.$vo['class_name']."_sms.php";
		if(file_exists($file))
		{
			$module = require_once($file);
		}
		else
		{
			showErr(lang("INVALID_OPERATION"),$ajax);
		}
		$data = $vo;
		$vo['config'] = unserialize($vo['config']);
	
		$data['lang'] = $module['lang'];
		$data['config'] = $module['config'];
		
		foreach($data['config'] as $k=>$v)
		{
			$data['config'][$k]['SHOW_TITLE'] = $data['lang'][$k];
			$data['config'][$k]['VALUE'] = $vo['config'][$k];
			if(isset($data['config'][$k]['VALUES'])&&is_array($data['config'][$k]['VALUES']))
			{
				foreach($data['config'][$k]['VALUES'] as $kk=>$vv)
				{
					$val = array();
					$val['SHOW_TITLE'] = $data['lang'][$k."_".$vv];
					$val['VALUE'] = $vv;
					$data['config'][$k]['VALUES'][$kk] = $val;
				}
			}
		}
		

	
		$GLOBALS['tmpl']->assign ( 'vo', $vo );
		$GLOBALS['tmpl']->assign ( 'data', $data );
		$GLOBALS['tmpl']->assign("formaction",admin_url("sms#update",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("checkfeeurl",admin_url("sms#check_fee",array("ajax"=>1)));
		$GLOBALS['tmpl']->display ("core/sms/edit.html");
	}
	
	
	public function update()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$data['name'] = strim($_REQUEST['name']);
		$data['class_name'] = strim($_REQUEST['class_name']);
		$data['user_name'] = strim($_REQUEST['user_name']);
		$data['password'] = strim($_REQUEST['password']);
		$data['description'] = strim($_REQUEST['description']);
		$data['server_url'] = strim($_REQUEST['server_url']);
		$data['config'] = serialize($_REQUEST['config']);
		// 更新数据
		$log_info = $data['name'];
	
		$GLOBALS['db']->autoExecute(DB_PREFIX."sms",$data,"UPDATE","id=".$id,"SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log($log_info.lang("UPDATE_SUCCESS"),1);
			showSuccess(lang("UPDATE_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("UPDATE_SUCCESS")."<br />".$GLOBALS['db']->error(),$ajax);
		}	
	}
	
	
	
	
	public function uninstall()
	{
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST ['id']);
		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms where id = ".$id);
		if($data)
		{
			$info = $data['class_name'];
			$GLOBALS['db']->query("delete from ".DB_PREFIX."sms where id = ".$data['id']);
			if ($GLOBALS['db']->error()=="") {
					save_log($info.lang("UNINSTALL_SUCCESS"),1);
					showSuccess(lang("UNINSTALL_SUCCESS"),$ajax);
				} else {
					save_log($info.lang("UNINSTALL_FAILED"),0);
					showErr(lang("UNINSTALL_FAILED"),$ajax);
				}
		}
		else
		{
			showErr(lang("INVALID_OPERATION"),$ajax);
		}
	}
	
	
	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."sms where id = ".$id);
		$GLOBALS['db']->query("update ".DB_PREFIX."sms set is_effect = 0");
		$GLOBALS['db']->query("update ".DB_PREFIX."sms set is_effect =1 where id = ".$id);
		save_log($info.lang("SET_EFFECT_1"),1);
		showSuccess(lang("SET_EFFECT_1"),$ajax)	;
	}
	

	
	public function send_demo()
	{
		$ajax = intval($_REQUEST['ajax']);
		$test_mobile = strim($_REQUEST['test_mobile']);
		require_once APP_ROOT_PATH."system/utils/es_sms.php";
		$sms = new sms_sender();
	
		$result = $sms->sendSms($test_mobile,lang("DEMO_SMS"));
		if($result['status'])
		{
			showSuccess(lang("SEND_SUCCESS"),$ajax);
		}
		else
		{
			showErr(lang("ERROR_INFO").$result['msg'],$ajax);
		}
	}
	
	public function check_fee()
	{
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);
		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms where id =".$id);
		if($data)
		{
			include APP_ROOT_PATH."system/sms/".$data['class_name']."_sms.php";
			$sms_info = $data;
			$sms_info['config'] = unserialize($sms_info['config']);
			$sms_class = $data['class_name']."_sms";
			$sms_module = new $sms_class($sms_info);
			showSuccess($sms_module->check_fee(),$ajax);
		}
		else
		{
			showErr("接口不存在",$ajax);
		}
	}

}
?>