<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class adminModule extends AuthModule
{
	public function index()
	{		
		
		$param = array();
		
		//条件
		$condition = " 1 = 1 ";
		if(isset($_REQUEST['adm_name']))
		$adm_name = strim($_REQUEST['adm_name']);
		else
		$adm_name = "";
		$param['adm_name'] = $adm_name;
		if($adm_name!='')
		{
			$condition.=" and adm_name = '".$adm_name."' ";
		}		
		

		//分页
		if(isset($_REQUEST['numPerPage']))
		{			
			$param['pageSize'] = intval($_REQUEST['numPerPage']);
			if($param['pageSize'] <=0||$param['pageSize'] >200)
				$param['pageSize'] = ADMIN_PAGE_SIZE;
		}
		else
			$param['pageSize'] = ADMIN_PAGE_SIZE;
			
		if(isset($_REQUEST['pageNum']))
			$page = intval($_REQUEST['pageNum']);
		else
			$page = 0;
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$param['pageSize']).",".$param['pageSize'];
		$param['pageNum'] = $page;
		
		
		//排序
		if(isset($_REQUEST['orderField']))
			$param['orderField'] = strim($_REQUEST['orderField']);
		else
			$param['orderField'] = "id";
		
		if(isset($_REQUEST['orderDirection']))
			$param['orderDirection'] = strim($_REQUEST['orderDirection'])=="asc"?"asc":"desc";
		else
			$param['orderDirection'] = "desc";
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."admin where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."admin where ".$condition);
		
		foreach($list as $k=>$v)
		{
			$list[$k]['is_effect_show'] = lang("IS_EFFECT_".$v['is_effect']);
			$role_name = load_dynamic_cache("ROLE_NAME_".$v['role_id']);
			if($role_name===false)
			{				
				$role_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."role where id = ".$v['role_id']);
				set_dynamic_cache("ROLE_NAME_".$v['role_id'], $role_name);				
			}
			if($v['adm_name']==app_conf("DEFAULT_ADMIN"))
			{
				$role_name = lang("DEFAULT_ADMIN");
				$list[$k]['is_default'] = 1;
			}
			$list[$k]['role_name'] = $role_name;
			$list[$k]['login_time'] = to_date($v['login_time']);
		}
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("admin"));
		$GLOBALS['tmpl']->assign("seteffecturl",admin_url("admin#set_effect",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("setdefaulturl",admin_url("admin#set_default",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("delurl",admin_url("admin#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("admin#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("admin#add"));
		$GLOBALS['tmpl']->display("core/admin/index.html");
	}	
	
	
	public function foreverdelete()
	 {
		
		$ajax = intval($_REQUEST['ajax']);		
		if (isset ( $_REQUEST ['id'] ))
		{
			$id = strim($_REQUEST ['id']);			
			$id = format_ids_str($id);
			if($id)
			{
				$default_adm_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."admin where adm_name ='".app_conf("DEFAULT_ADMIN")."'");				
				$del_adm_name = $GLOBALS['db']->getOne("select group_concat(adm_name) from ".DB_PREFIX."admin where id in (".$id.") and id <> ".$default_adm_id);
				$sql = "delete from ".DB_PREFIX."admin where id in (".$id.") and id <> ".$default_adm_id;
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->affected_rows()>0)
				save_log(lang("DEL").":".$del_adm_name, 1);
				showSuccess(lang("FOREVER_DELETE_SUCCESS"),$ajax);				
			}
			else
			{
				save_log(lang("DEL")."ID:".strim($_REQUEST ['id']), 0);
				showErr(lang("INVALID_OPERATION"),$ajax);
			}			
		}
		else
		{
			showErr(lang("INVALID_OPERATION"),$ajax);
		}

	}
	
	
	public function add()
	{		
		$role_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."role");
		$GLOBALS['tmpl']->assign("role_list",$role_list);
		$GLOBALS['tmpl']->assign("formaction",admin_url("admin#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/admin/add.html");
	}
	
	
	public function insert() {
		$ajax = intval($_REQUEST['ajax']);
		if(!check_empty("adm_name"))
		{
			showErr(lang("ADM_NAME_EMPTY_TIP"),$ajax);
		}
		if(!check_empty("adm_password"))
		{
			showErr(lang("ADM_PASSWORD_EMPTY_TIP"),$ajax);
		}
		// 更新数据
		$log_info = strim($_REQUEST['adm_name']);
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."admin where adm_name = '".$log_info."'")>0){ showErr(lang("ADMIN_EXIST_TIP"),$ajax);}
		$data = array();
		$data['adm_name'] = strim($_REQUEST['adm_name']);
		$data['adm_password'] = md5(trim($_REQUEST['adm_password']));
		$data['is_effect'] = intval($_REQUEST['is_effect']);
		$data['role_id'] = intval($_REQUEST['role_id']);
		$GLOBALS['db']->autoExecute(DB_PREFIX."admin",$data,"INSERT","","SILENT");		
		if ($GLOBALS['db']->error()=="") {			
			//成功提示
			save_log($log_info.lang("INSERT_SUCCESS"),1);
			showSuccess(lang("INSERT_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}
	}
	
	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."admin where id = ".$id);
		$GLOBALS['tmpl']->assign ( 'vo', $vo );
		
		$role_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."role");
		$GLOBALS['tmpl']->assign("role_list",$role_list);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("admin#update",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/admin/edit.html");
	}

	
	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		if(!check_empty("adm_name"))
		{
			showErr(lang("ADM_NAME_EMPTY_TIP"),$ajax);
		}
		$id = intval($_REQUEST['id']);
		// 更新数据
		$log_info = strim($_REQUEST['adm_name']);
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."admin where adm_name = '".$log_info."' and id <>".$id)>0){ showErr(lang("ADMIN_EXIST_TIP"),$ajax);}
		$data = array();
		$data['adm_name'] = strim($_REQUEST['adm_name']);
		if(trim($_REQUEST['adm_password'])!="")
		{
			$data['adm_password'] = md5(trim($_REQUEST['adm_password']));
		}
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."admin",$data,"UPDATE","id=".$id,"SILENT");
		
		if ($GLOBALS['db']->error()=="") {			
			save_log($log_info.lang("UPDATE_SUCCESS"),1);
			showSuccess(lang("UPDATE_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}
	}
	
	
	public function set_effect()
	{
		$default_adm_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."admin where adm_name ='".app_conf("DEFAULT_ADMIN")."'");
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		if($id==$default_adm_id)
		{
			showErr(lang("DEFAULT_ADMIN_CANNOT_EFFECT"),$ajax);
		}		
		$info = $GLOBALS['db']->getOne("select adm_name from ".DB_PREFIX."admin where id = ".$id);
		$c_is_effect =  $GLOBALS['db']->getOne("select is_effect from ".DB_PREFIX."admin where id = ".$id); //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		$GLOBALS['db']->query("update ".DB_PREFIX."admin set is_effect = ".$n_is_effect." where id = ".$id);
		save_log($info.lang("SET_EFFECT_".$n_is_effect),1);
		showSuccess(lang("SET_EFFECT_".$n_is_effect),$ajax)	;
	}
	
	
	public function set_default()
	{
		$ajax = intval($_REQUEST['ajax']);
		$adm_id = intval($_REQUEST['id']);
		$admin = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."admin where id = ".$adm_id);
		if($admin)
		{
			$origin_default = $GLOBALS['db']->getOne("select value from ".DB_PREFIX."conf where name = 'DEFAULT_ADMIN' ");
			$GLOBALS['db']->query("update ".DB_PREFIX."conf set value ='".$admin['adm_name']."' where name ='DEFAULT_ADMIN' ");
			//开始写入配置文件
			$sys_configs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."conf");
			$config_str = "<?php\n";
			$config_str .= "return array(\n";
			foreach($sys_configs as $k=>$v)
			{
				$config_str.="'".$v['name']."'=>'".addslashes($v['value'])."',\n";
			}
			$config_str.=");\n ?>";
	
			$filename = APP_ROOT_PATH."public/sys_config.php";
				
			if (!$handle = fopen($filename, 'w')) {
				$GLOBALS['db']->query("update ".DB_PREFIX."conf set value ='".$origin_default."' where name ='DEFAULT_ADMIN' ");
				showErr(lang("OPEN_FILE_ERROR").$filename,$ajax);
			}
				
			 
			if (fwrite($handle, $config_str) === FALSE) {
				$GLOBALS['db']->query("update ".DB_PREFIX."conf set value ='".$origin_default."' where name ='DEFAULT_ADMIN' ");
				showErr(lang("WRITE_FILE_ERROR").$filename,$ajax);
			}
				
			fclose($handle);
		  
			$GLOBALS['db']->query("update ".DB_PREFIX."admin set is_effect = 1 where id = ".$admin['id']);
			save_log(lang("CHANGE_DEFAULT_ADMIN"),1);
			showSuccess(lang("SET_DEFAULT_SUCCESS"),$ajax);
		}
		else
		{
			showErr(lang("NO_ADMIN"),$ajax);
		}
	}
}
?>