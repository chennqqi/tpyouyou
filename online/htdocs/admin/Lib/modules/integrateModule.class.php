<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class integrateModule extends AuthModule
{
	
	private function read_modules()
	{
		$directory = APP_ROOT_PATH."system/integrate/";
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
		$db_modules = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."integrate");
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
		$GLOBALS['tmpl']->assign("integrate_list",$modules);
				
		$GLOBALS['tmpl']->assign("formaction",admin_url("integrate"));
		$GLOBALS['tmpl']->assign("uninstallurl",admin_url("integrate#uninstall",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("integrate#edit",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("installurl",admin_url("integrate#install",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/integrate/index.html");
	}	
	
	
	
	
	public function install()
	{
		$ajax = intval($_REQUEST['ajax']);
		$class_name = strim($_REQUEST['class_name']);
		$directory = APP_ROOT_PATH."system/integrate/";
		$read_modules = true;
	
		$file = $directory.$class_name."_integrate.php";
		if(file_exists($file))
		{
			$module = require_once($file);
			
			$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."integrate where class_name ='".$class_name."'");
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
		$GLOBALS['tmpl']->assign("formaction",admin_url("integrate#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/integrate/install.html");
	
	}
	
	
	public function insert()
	{
		$ajax = intval($_REQUEST['ajax']);			
		
		$data['name'] = strim($_REQUEST['name']);
		$class_name = $data['class_name'] = strim($_REQUEST['class_name']);
		$data['config'] = serialize($_REQUEST['config']);


		$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."integrate where class_name ='".$data['class_name']."'");
		if($rs > 0)
		{
			showErr("接口已安装",$ajax);
		}
		
		// 更新数据
		$log_info = $data['name'];
		
		$directory = APP_ROOT_PATH."system/integrate/";
		
		$file = $directory.$class_name."_integrate.php";
	
		if(file_exists($file))
		{
			require_once($file);
			$integrate_class = $class_name."_integrate";
			$integrate_item = new $integrate_class;
			$rs = $integrate_item->install($data['config']);
			if(intval($rs['status'])==0)
			{
				showErr($rs['msg'],$ajax);
			}
		}
		else
		{
			showErr("接口文件不存在",$ajax);
		}
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."integrate",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			save_log($log_info.lang("INSTALL_SUCCESS"),1);
			showSuccess(lang("INSTALL_SUCCESS"),$ajax,admin_url("integrate"));
		} else {
			//错误提示
			showErr(lang("INSTALL_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	
	
	public function uninstall()
	{
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST ['id']);
		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."integrate where id = ".$id);
		if($data)
		{
			$info = $data['class_name'];
			
			$directory = APP_ROOT_PATH."system/integrate/";
			$file = $directory.$data['class_name']."_integrate.php";
			if(file_exists($file))
			{
				require_once($file);
				$integrate_class = $data['class_name']."_integrate";
				$integrate_item = new $integrate_class;
				$integrate_item->uninstall();
			}
			
			$GLOBALS['db']->query("delete from ".DB_PREFIX."integrate where id = ".$data['id']);
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