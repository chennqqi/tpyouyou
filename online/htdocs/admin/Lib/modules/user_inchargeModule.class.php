<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class user_inchargeModule extends AuthModule
{	
	public function index()
	{				
		$param = array();		
		//条件
		$condition = " 1 = 1 ";
		if(isset($_REQUEST['order_sn']))
			$order_sn_key = strim($_REQUEST['order_sn']);
		else
			$order_sn_key = "";
		$param['order_sn'] = $order_sn_key;
		if($order_sn_key!='')
		{
			$condition.=" and order_sn = '".$order_sn_key."' ";
		}
		
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
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_incharge where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_incharge where ".$condition);
		
		foreach($list as $k=>$v)
		{
			$list[$k]['user_name'] = get_user_name($v['user_id']);
			$list[$k]['create_time'] = to_date($v['create_time']);
			$list[$k]['pay_time'] = to_date($v['pay_time']);
			$list[$k]['money'] = format_price(format_price_to_display($v['money']));
			$list[$k]['pay_money'] = format_price(format_price_to_display($v['pay_money']));
		}
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("user_incharge"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("user_incharge#foreverdelete",array('ajax'=>1)));	
		$GLOBALS['tmpl']->display("core/user_incharge/index.html");
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
				$del_name = $GLOBALS['db']->getOne("select group_concat(order_sn) from ".DB_PREFIX."user_incharge where id in (".$id.")");			
				$sql = "delete from ".DB_PREFIX."user_incharge where id in (".$id.")";
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

	
	
	
	
}
?>