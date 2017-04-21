<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class sms_msgModule extends AuthModule
{		
	
	public function index()
	{				
		$param = array();		
		
		//条件
		$condition = " type = 0 ";
		
		
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
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."promote_msg where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."promote_msg where ".$condition);
		
		
		foreach($list as $k=>$v)
		{
			$list[$k]['send_time'] = to_date($v['send_time']);
			if($v['send_status']==0)
				$send_status = "未发送";
			else if($v['send_status']==1)
				$send_status = "发送中";
			else
				$send_status = "已发送";
			$list[$k]['send_status'] = $send_status; 
			if($v['send_type']==0)
				$send_type = "按会员组";
			else if($v['send_type']==1)
				$send_type = "按会员等级";
			else
				$send_type = "自定义发送";
			$list[$k]['send_type'] = $send_type;
		}
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("sms_msg"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("sms_msg#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("sms_msg#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("sms_msg#add"));
		$GLOBALS['tmpl']->display("core/sms_msg/index.html");
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
				$del_name = $id;		
				$sql = "delete from ".DB_PREFIX."promote_msg where id in (".$id.")";
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
		
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("sms_msg#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/sms_msg/add.html");
	}
	
	
	public function insert() {
		$ajax = intval($_REQUEST['ajax']);
		if(!check_empty("content"))
		{
			showErr("内容不能为空",$ajax);
		}
		
		if(!check_empty("send_time"))
		{
			showErr("请指定发送时间",$ajax);
		}
		

		$data = array();
		$data['content'] = strim($_REQUEST['content']);
		$data['send_type'] = intval($_REQUEST['send_type']);
		if($data['send_type']==0)
		$data['send_type_id'] = intval($_REQUEST['group_id']);
		elseif($data['send_type']==1)
		$data['send_type_id'] = intval($_REQUEST['level_id']);
		else
		$data['send_define_data'] = strim($_REQUEST['send_define_data']);

		$data['send_time'] = to_timespan(strim($_REQUEST['send_time']));
		$data['type'] = 0;
		// 更新数据
		
		$log_info = $data['content'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."promote_msg",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log($log_info.lang("INSERT_SUCCESS"),1);
			showSuccess(lang("INSERT_SUCCESS"),$ajax,admin_url("sms_msg#add"));
		} else {
			//错误提示
			showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."promote_msg where id = ".$id);
		$vo['send_time'] = to_date($vo['send_time']);
		$GLOBALS['tmpl']->assign ( 'vo', $vo );
		
		$GLOBALS['tmpl']->assign("NOW_TIME",to_date(NOW_TIME,"Y-m-d"));
		$group_list = load_auto_cache("user_group");
		$GLOBALS['tmpl']->assign("group_list",$group_list);
		
		$level_list = load_auto_cache("user_level");
		$GLOBALS['tmpl']->assign("level_list",$level_list);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("sms_msg#update",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/sms_msg/edit.html");
	}

	
	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);

		if(!check_empty("content"))
		{
			showErr("内容不能为空",$ajax);
		}
		
		if(!check_empty("send_time"))
		{
			showErr("请指定发送时间",$ajax);
		}
		

		$data = array();
		$data['content'] = strim($_REQUEST['content']);

		$data['send_type'] = intval($_REQUEST['send_type']);
		if($data['send_type']==0)
		$data['send_type_id'] = intval($_REQUEST['group_id']);
		elseif($data['send_type']==1)
		$data['send_type_id'] = intval($_REQUEST['level_id']);
		else
		$data['send_define_data'] = strim($_REQUEST['send_define_data']);

		$data['send_time'] = to_timespan(strim($_REQUEST['send_time']));
		$data['type'] = 0;
		// 更新数据
		
		$log_info = $data['content'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."promote_msg",$data,"UPDATE","id=".$id,"SILENT");
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