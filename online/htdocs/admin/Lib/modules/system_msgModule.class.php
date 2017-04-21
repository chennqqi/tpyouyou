<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class system_msgModule extends AuthModule
{		
	
	public function index()
	{				
		$param = array();		
		
		//条件
		$condition = " 1 = 1 ";
		if(isset($_REQUEST['msg_title']))
			$name_key = strim($_REQUEST['msg_title']);
		else
			$name_key = "";
		$param['msg_title'] = $name_key;
		if($name_key!='')
		{
			$condition.=" and msg_title like '%".$name_key."%' ";
		}
		
		if(isset($_REQUEST['group_id']))
			$group_id = intval($_REQUEST['group_id']);
		else
			$group_id = "";
		$param['group_id'] = $group_id;
		if($group_id>0)
		{
			$condition.=" and group_id = '".$group_id."' ";
		}
		
		if(isset($_REQUEST['level_id']))
			$level_id = intval($_REQUEST['level_id']);
		else
			$level_id = "";
		$param['level_id'] = $level_id;
		if($level_id>0)
		{
			$condition.=" and level_id = '".$level_id."' ";
		}
		
		if(isset($_REQUEST['user_name']))
			$user_name = strim($_REQUEST['user_name']);
		else
			$user_name = "";
		$param['user_name'] = $user_name;
		if($user_name!="")
		{
			$user_name = str_to_unicode_string_depart($user_name);
			$condition.=" and match(`username_match`) against('".$user_name."' IN BOOLEAN MODE) ";
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
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."system_msg where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."system_msg where ".$condition);
		
		$group_list = load_auto_cache("user_group");
		$GLOBALS['tmpl']->assign("group_list",$group_list);
		
		$level_list = load_auto_cache("user_level");
		$GLOBALS['tmpl']->assign("level_list",$level_list);
		
		foreach($list as $k=>$v)
		{
			$list[$k]['end_time'] = to_date($v['end_time']);
			$list[$k]['send_time'] = to_date($v['send_time']);
			$list[$k]['group_name'] = $group_list[$v['group_id']]['name']?$group_list[$v['group_id']]['name']:"全部";
			$list[$k]['level_name'] = $level_list[$v['level_id']]['name']?$level_list[$v['level_id']]['name']:"全部";
		}
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("system_msg"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("system_msg#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("system_msg#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("system_msg#add"));
		$GLOBALS['tmpl']->display("core/system_msg/index.html");
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
				$del_name = $GLOBALS['db']->getOne("select group_concat(msg_title) from ".DB_PREFIX."system_msg where id in (".$id.")");			
				$sql = "delete from ".DB_PREFIX."system_msg where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->affected_rows()>0)
				{
					save_log(lang("DEL").":".$del_name, 1);
				}
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
		$GLOBALS['tmpl']->assign("NOW_TIME",to_date(NOW_TIME,"Y-m-d"));
		$group_list = load_auto_cache("user_group");
		$GLOBALS['tmpl']->assign("group_list",$group_list);
		
		$level_list = load_auto_cache("user_level");
		$GLOBALS['tmpl']->assign("level_list",$level_list);
		
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("system_msg#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/system_msg/add.html");
	}
	
	
	public function insert() {
		$ajax = intval($_REQUEST['ajax']);
		if(!check_empty("msg_title"))
		{
			showErr("标题不能为空",$ajax);
		}
		if(!check_empty("msg_content"))
		{
			showErr("内容不能为空",$ajax);
		}
		
		if(!check_empty("send_time"))
		{
			showErr("请指定发送开始时间",$ajax);
		}
		
		if(!check_empty("end_time"))
		{
			showErr("请指定过期时间",$ajax);
		}
		$data = array();
		$data['msg_title'] = strim($_REQUEST['msg_title']);
		$data['msg_content'] = strim($_REQUEST['msg_content']);
		$data['group_id'] = intval($_REQUEST['group_id']);
		$data['level_id'] = intval($_REQUEST['level_id']);
		$data['username_match'] = str_to_unicode_string_depart(strim($_REQUEST['username']));
		$data['username_match_row'] = strim($_REQUEST['username']);
		$data['send_time'] = to_timespan(strim($_REQUEST['send_time']));
		$data['end_time'] = to_timespan(strim($_REQUEST['end_time']));
		
		// 更新数据
		
		$log_info = $data['msg_title'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."system_msg",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log($log_info.lang("INSERT_SUCCESS"),1);
			showSuccess(lang("INSERT_SUCCESS"),$ajax,admin_url("system_msg#add"));
		} else {
			//错误提示
			showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."system_msg where id = ".$id);
		$vo['send_time'] = to_date($vo['send_time']);
		$vo['end_time'] = to_date($vo['end_time']);
		$GLOBALS['tmpl']->assign ( 'vo', $vo );
		
		$GLOBALS['tmpl']->assign("NOW_TIME",to_date(NOW_TIME,"Y-m-d"));
		$group_list = load_auto_cache("user_group");
		$GLOBALS['tmpl']->assign("group_list",$group_list);
		
		$level_list = load_auto_cache("user_level");
		$GLOBALS['tmpl']->assign("level_list",$level_list);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("system_msg#update",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/system_msg/edit.html");
	}

	
	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);
		if(!check_empty("msg_title"))
		{
			showErr("标题不能为空",$ajax);
		}
		if(!check_empty("msg_content"))
		{
			showErr("内容不能为空",$ajax);
		}
		
		if(!check_empty("send_time"))
		{
			showErr("请指定发送开始时间",$ajax);
		}
		
		if(!check_empty("end_time"))
		{
			showErr("请指定过期时间",$ajax);
		}
		$data = array();
		$data['msg_title'] = strim($_REQUEST['msg_title']);
		$data['msg_content'] = strim($_REQUEST['msg_content']);
		$data['group_id'] = intval($_REQUEST['group_id']);
		$data['level_id'] = intval($_REQUEST['level_id']);
		$data['username_match'] = str_to_unicode_string_depart(strim($_REQUEST['username']));
		$data['username_match_row'] = strim($_REQUEST['username']);
		$data['send_time'] = to_timespan(strim($_REQUEST['send_time']));
		$data['end_time'] = to_timespan(strim($_REQUEST['end_time']));
		
		$log_info = $data['msg_title'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."system_msg",$data,"UPDATE","id=".$id,"SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log($log_info.lang("UPDATE_SUCCESS"),1);
			showSuccess(lang("UPDATE_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	
}
?>