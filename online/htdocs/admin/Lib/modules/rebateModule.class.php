<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class rebateModule extends AuthModule
{	
	public function index()
	{				
		$param = array();		
		//条件
		$condition = " 1 = 1 ";
		
		if(isset($_REQUEST['user_name']))
			$user_name_key = strim($_REQUEST['user_name']);
		else
			$user_name_key = "";
		$param['user_name'] = $user_name_key;
		if($user_name_key!='')
		{
			$user_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where user_name = '".$user_name_key."'");
			$condition.=" and user_id = '".$user_id."' ";
		}
		
		
		if(isset($_REQUEST['begin_time']))
			$begin_time  = strim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		else
			$begin_time = 0;
		$param['begin_time'] = to_date($begin_time);
		
		if(isset($_REQUEST['end_time']))
			$end_time  = strim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		else
			$end_time = 0;
		$param['end_time'] = to_date($end_time);
		
		if($end_time==0)
		{
			$condition.=" and create_time > ".$begin_time;
		}
		else
		{
			$condition.=" and create_time between ".$begin_time." and ".$end_time;
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
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_rebate where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_rebate where ".$condition);
		
		foreach($list as $k=>$v)
		{
			$list[$k]['money'] = format_price(format_price_to_display($v['money']));
			$list[$k]['user_name'] = get_user_name($v['user_id']);
			$list[$k]['create_time'] = to_date($v['create_time']);
			$list[$k]['pay_time'] = to_date($v['pay_time']);
			if($v['from_otype']==1)
			{
				$from_otype = "旅游线路";
				$order_sn = $GLOBALS['db']->getOne("select sn from ".DB_PREFIX."tourline_order where id = ".$v['from_oid']);
			}
			elseif($v['from_otype']==2)
			{
				$from_otype = "景点门票";	
				$order_sn = $GLOBALS['db']->getOne("select sn from ".DB_PREFIX."ticket_order where id = ".$v['from_oid']);
			}	
			$list[$k]['from_otype'] = $from_otype;
			$list[$k]['order_sn'] = $order_sn;			
			
		}
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("rebate"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("rebate#foreverdelete",array('ajax'=>1)));	
		$GLOBALS['tmpl']->display("core/rebate/index.html");
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
				$sql = "delete from ".DB_PREFIX."user_rebate where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->affected_rows()>0)
				{					
					save_log(lang("DEL"), 1);
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

	
	
	
	
}
?>