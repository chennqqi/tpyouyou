<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class logModule extends AuthModule
{
	public function index()
	{		
		
		$param = array();
		
		//条件
		$condition = " 1 = 1 ";
		if(isset($_REQUEST['log_info']))
		$log_info_key = strim($_REQUEST['log_info']);
		else
		$log_info_key = "";
		$param['log_info'] = $log_info_key;
		if($log_info_key!='')
		{
			$condition.=" and log_info like '%".$log_info_key."%' ";
		}		
		if(isset($_REQUEST['log_begin_time']))
			$log_begin_time  = strim($_REQUEST['log_begin_time'])==''?0:to_timespan($_REQUEST['log_begin_time']);
		else 
			$log_begin_time = 0;		
		$param['log_begin_time'] = to_date($log_begin_time);
		
		if(isset($_REQUEST['log_end_time']))
			$log_end_time  = strim($_REQUEST['log_end_time'])==''?0:to_timespan($_REQUEST['log_end_time']);
		else
			$log_end_time = 0;
		$param['log_end_time'] = to_date($log_end_time);
		
		if($log_end_time==0)
		{
			$condition.=" and log_time > ".$log_begin_time;
		}
		else
		{
			$condition.=" and log_time between ".$log_begin_time." and ".$log_end_time;
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
			$param['orderField'] = "log_time";
		
		if(isset($_REQUEST['orderDirection']))
			$param['orderDirection'] = strim($_REQUEST['orderDirection'])=="asc"?"asc":"desc";
		else
			$param['orderDirection'] = "desc";
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."log where ".$condition." order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."log where ".$condition);
		
		foreach($list as $k=>$v)
		{
			$list[$k]['log_admin'] = get_admin_name($v['log_admin']);
			$list[$k]['log_status'] = lang("LOG_STATUS_".$v['log_status']);
		}
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("log"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("log#foreverdelete",array('ajax'=>1)));
		$GLOBALS['tmpl']->assign("viewurl",admin_url("log#view",array('ajax'=>1)));
		$GLOBALS['tmpl']->display("core/log/index.html");
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
				$sql = "delete from ".DB_PREFIX."log where id in (".$id.")";
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
	
	
	public function view()
	{
		$id = intval($_REQUEST['id']);
		$ajax =intval($_REQUEST['ajax']);
		$log_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."log where id = ".$id);
		if($log_info)
		{
			$log_info['log_admin'] = get_admin_name($log_info['log_admin']);
			$log_info['log_status'] = lang("LOG_STATUS_".$log_info['log_status']);
			$GLOBALS['tmpl']->assign("log_info",$log_info);
			$GLOBALS['tmpl']->display("core/log/view.html");
		}
		else
		{
			showErr("abc",$ajax);
		}
	}


}
?>