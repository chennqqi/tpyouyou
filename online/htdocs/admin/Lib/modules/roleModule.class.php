<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class roleModule extends AuthModule
{
	public function index()
	{		
		
		$param = array();

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
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."role  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."role");
		
		foreach($list as $k=>$v)
		{
			$list[$k]['is_effect_show'] = lang("IS_EFFECT_".$v['is_effect']);
		}
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("role"));
		$GLOBALS['tmpl']->assign("seteffecturl",admin_url("role#set_effect",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("delurl",admin_url("role#foreverdelete",array('ajax'=>1)));
		$GLOBALS['tmpl']->assign("editurl",admin_url("role#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("role#add"));
		$GLOBALS['tmpl']->display("core/role/index.html");
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
				$sql = "delete from ".DB_PREFIX."role where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				save_log(lang("DEL")."ID:".strim($_REQUEST ['id']), 1);
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
		//输出module与action		
		$access_list = $GLOBALS['access_list'];
		$GLOBALS['tmpl']->assign("access_list",$access_list);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("role#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/role/add.html");
	}
	
	
	public function insert() {
		$ajax = intval($_REQUEST['ajax']);
		if(!check_empty("name"))
		{
			showErr(lang("ROLE_NAME_EMPTY_TIP"),$ajax);
		}
		// 更新数据
		$log_info = strim($_REQUEST['name']);
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."role where name = '".$log_info."'")>0) showErr(lang("ROLE_NAME_EXIST"),$ajax);
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['is_effect'] = intval($_REQUEST['is_effect']);
		$GLOBALS['db']->autoExecute(DB_PREFIX."role",$data,"INSERT","","SILENT");
		$role_id = intval($GLOBALS['db']->insert_id());
		if ($role_id>0) {
			//开始关联节点
			if(isset($_REQUEST['role_access']))
			{
				$role_access =  $_REQUEST['role_access'];
				foreach($role_access as $k=>$v)
				{
					//开始提交关联
					$v = strim($v);
					$item = explode("|",$v);
					if(empty($item[1]))
					{					
						//模块授权
						$GLOBALS['db']->query("delete from ".DB_PREFIX."role_access where role_id = ".$role_id." and module = '".$item[0]."'");
					}
					else
					{
						//节点授权
						$GLOBALS['db']->query("delete from ".DB_PREFIX."role_access where role_id = ".$role_id." and module = '".$item[0]."' and node = '".$item[1]."'");
					}
					$access_item['role_id'] = $role_id;
					$access_item['node'] = empty($item[1])?"":$item[1];
					$access_item['module'] = $item[0];
					$GLOBALS['db']->autoExecute(DB_PREFIX."role_access",$access_item,"INSERT","","SILENT");
				}
			}
			//成功提示
			save_log($log_info.lang("INSERT_SUCCESS"),1);
			showSuccess(lang("INSERT_SUCCESS"),$ajax);
		} else {
			//错误提示
			save_log($log_info.lang("INSERT_FAILED"),0);
			showErr(lang("INSERT_FAILED"),$ajax);
		}
	}
	
	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."role where id = ".$id);
		$GLOBALS['tmpl']->assign ( 'vo', $vo );
		//输出module与action
		
		$access_list = $GLOBALS['access_list'];
		foreach($access_list as $k=>$v)
		{
			
			if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."role_access where role_id = ".$vo['id']." and module = '".$k."' and node = ''")>0)
			{
				$access_list[$k]['module_auth'] = 1;  //当前模块被授权
			}
			else
			{
				$access_list[$k]['module_auth'] = 0; 
			}
			
			$node_list = $v['node'];

			foreach($node_list as $kk=>$vv)
			{				
				if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."role_access where role_id = ".$vo['id']." and module = '".$k."' and node = '".$vv['action']."'")>0)
				{
					$node_list[$kk]['node_auth'] = 1;
				}
				else
				{
					$node_list[$kk]['node_auth'] = 0;
				}
			}
			$access_list[$k]['node'] = $node_list;
			//非模块授权时的是否全选
			$r1 = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."role_access where role_id = ".$vo['id']." and module = '".$k."' and node <> ''");
			$r2 = count($v['node']);
			if($r1==$r2&&$r2!=0)
			{
				//全选
				$access_list[$k]['check_all'] = 1;
			}
			else
			{
				$access_list[$k]['check_all'] = 0;
			}
		}		
		$GLOBALS['tmpl']->assign("access_list",$access_list);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("role#update",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/role/edit.html");
	}

	
	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		if(!check_empty("name"))
		{
			showErr(lang("ROLE_NAME_EMPTY_TIP"),$ajax);
		}
		$id = intval($_REQUEST['id']);
		// 更新数据
		$log_info = strim($_REQUEST['name']);
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."role where name = '".$log_info."' and id <> ".$id)>0) showErr(lang("ROLE_NAME_EXIST"),$ajax);
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['is_effect'] = intval($_REQUEST['is_effect']);
		$GLOBALS['db']->autoExecute(DB_PREFIX."role",$data,"UPDATE","id=".$id,"SILENT");
		
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			$role_id = $id;
			$GLOBALS['db']->query("delete from ".DB_PREFIX."role_access where role_id = ".$role_id);
			//开始关联节点
			$role_access = $_REQUEST['role_access'];
			foreach($role_access as $k=>$v)
			{
				//开始提交关联
					$v = strim($v);
					$item = explode("|",$v);
					if(empty($item[1]))
					{					
						//模块授权
						$GLOBALS['db']->query("delete from ".DB_PREFIX."role_access where role_id = ".$role_id." and module = '".$item[0]."'");
					}
					else
					{
						//节点授权
						$GLOBALS['db']->query("delete from ".DB_PREFIX."role_access where role_id = ".$role_id." and module = '".$item[0]."' and node = '".$item[1]."'");
					}
					$access_item['role_id'] = $role_id;
					$access_item['node'] = empty($item[1])?"":$item[1];
					$access_item['module'] = $item[0];
					$GLOBALS['db']->autoExecute(DB_PREFIX."role_access",$access_item,"INSERT","","SILENT");
			}
			save_log($log_info.lang("UPDATE_SUCCESS"),1);
			showSuccess(lang("UPDATE_SUCCESS"),$ajax);
		} else {
			//错误提示
			save_log($log_info.lang("UPDATE_FAILED"),0);
			showErr(lang("UPDATE_FAILED"),$ajax);
		}
	}
	
	
	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."role where id = ".$id);
		$c_is_effect =  $GLOBALS['db']->getOne("select is_effect from ".DB_PREFIX."role where id = ".$id); //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		$GLOBALS['db']->query("update ".DB_PREFIX."role set is_effect = ".$n_is_effect." where id = ".$id);
		save_log($info.lang("SET_EFFECT_".$n_is_effect),1);
		showSuccess(lang("SET_EFFECT_".$n_is_effect),$ajax)	;
	}
}
?>