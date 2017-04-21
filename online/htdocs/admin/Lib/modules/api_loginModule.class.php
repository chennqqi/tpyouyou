<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class api_loginModule extends AuthModule
{
	
	private function read_modules()
	{
		$directory = APP_ROOT_PATH."system/api_login/";
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
		$db_modules = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."api_login");
		foreach($modules as $k=>$v)
		{
			foreach($db_modules as $kk=>$vv)
			{
				if($v['class_name']==$vv['class_name'])
				{
					//已安装
					$modules[$k]['id'] = $vv['id'];
					$modules[$k]['installed'] = 1;
					break;
				}
			}
			
			if($modules[$k]['installed'] != 1)
			$modules[$k]['installed'] = 0;
			
		}
		$GLOBALS['tmpl']->assign("api_list",$modules);
				
		$GLOBALS['tmpl']->assign("formaction",admin_url("api_login"));
		$GLOBALS['tmpl']->assign("uninstallurl",admin_url("api_login#uninstall",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("api_login#edit",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("installurl",admin_url("api_login#install",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/api_login/index.html");
	}	
	
	
	
	
	public function install()
	{
		$ajax = intval($_REQUEST['ajax']);
		$class_name = strim($_REQUEST['class_name']);
		$directory = APP_ROOT_PATH."system/api_login/";
		$read_modules = true;
	
		$file = $directory.$class_name."_api.php";
		if(file_exists($file))
		{
			$module = require_once($file);
			
			$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."api_login where class_name ='".$class_name."'");
			if($rs > 0)
			{
				showErr("接口已安装",$ajax);
			}
		}
		else
		{
			showErr(lang("INVALID_OPERATION"),$ajax);
		}
	
	
		$data['name'] = $module['name'];
		$data['class_name'] = $module['class_name'];
		$data['lang'] = $module['lang'];
		$data['config'] = $module['config'];
		$data['is_weibo'] = $module['is_weibo'];
		
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
		$GLOBALS['tmpl']->assign("formaction",admin_url("api_login#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/api_login/install.html");
	
	}
	
	
	public function insert()
	{
		$ajax = intval($_REQUEST['ajax']);			
		
		$data['name'] = strim($_REQUEST['name']);
		$data['class_name'] = strim($_REQUEST['class_name']);
		$data['config'] = serialize($_REQUEST['config']);
		$data['is_weibo'] = intval($_REQUEST['is_weibo']);


		$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."api_login where class_name ='".$data['class_name']."'");
		if($rs > 0)
		{
			showErr("接口已安装",$ajax);
		}
		
		// 更新数据
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."api_login",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			save_log($log_info.lang("INSTALL_SUCCESS"),1);
			showSuccess(lang("INSTALL_SUCCESS"),$ajax,admin_url("api_login"));
		} else {
			//错误提示
			showErr(lang("INSTALL_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	
	public function edit() {
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST ['id']);		
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."api_login where id = ".$id);
	
		$directory = APP_ROOT_PATH."system/api_login/";
		$read_modules = true;
	
		$file = $directory.$vo['class_name']."_api.php";
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
		$GLOBALS['tmpl']->assign("formaction",admin_url("api_login#update",array("ajax"=>1)));
		$GLOBALS['tmpl']->display ("core/api_login/edit.html");
	}
	
	
	public function update()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$data['name'] = strim($_REQUEST['name']);
		$data['class_name'] = strim($_REQUEST['class_name']);	
		$data['config'] = serialize($_REQUEST['config']);
		// 更新数据
		$log_info = $data['name'];
	
		$GLOBALS['db']->autoExecute(DB_PREFIX."api_login",$data,"UPDATE","id=".$id,"SILENT");
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
		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."api_login where id = ".$id);
		if($data)
		{
			$info = $data['class_name'];
			$GLOBALS['db']->query("delete from ".DB_PREFIX."api_login where id = ".$data['id']);
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

}
?>