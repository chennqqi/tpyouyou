<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class user_depositModule extends AuthModule
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
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_deposit where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_deposit where ".$condition);
		
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
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("user_deposit"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("user_deposit#foreverdelete",array('ajax'=>1)));
		$GLOBALS['tmpl']->assign("depositurl",admin_url("user_deposit#deposit",array('ajax'=>1)));	
		$GLOBALS['tmpl']->display("core/user_deposit/index.html");
	}	
	
	public function deposit()
	{
		$id = intval($_REQUEST['id']);
		$deposit = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_deposit where id = ".$id);
		$deposit['money'] = format_price_to_display($deposit['money']);
		$GLOBALS['tmpl']->assign("deposit",$deposit);
		$GLOBALS['tmpl']->assign("formaction",admin_url("user_deposit#dodeposit",array('ajax'=>1)));
		$GLOBALS['tmpl']->assign("depositurl",admin_url("user_deposit"));
		$GLOBALS['tmpl']->display("core/user_deposit/deposit.html");
	}
	
	public function dodeposit()
	{
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);
		$pay_money = format_price_to_db(floatval($_REQUEST['pay_money']));
		$pay_memo = strim($_REQUEST['pay_memo']);
		if($pay_money<0)
		{
			showErr("提现金额不能为负数",$ajax);
		}
		
		$deposit = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_deposit where id = ".$id);
		if($deposit)
		{
			require_once APP_ROOT_PATH."system/libs/user.php";
			if($pay_money==0)
			{
				$result['status'] = true;
			}
			else
			$result = User::modify_account($deposit['user_id'], 1, "-".$pay_money, "会员提现");
			if($result['status'])
			{
				$deposit['pay_time'] = NOW_TIME;
				$deposit['pay_memo'] = $pay_memo;
				$deposit['pay_money'] = $deposit['pay_money']+$pay_money;
				$deposit['is_paid'] = 1;
				$GLOBALS['db']->autoExecute(DB_PREFIX."user_deposit",$deposit,"UPDATE","id=".$id,"SILENT");
				if($pay_money>0)
				User::send_message($deposit['user_id'], "提现已发放", "您的提现申请已审核通过，成功提现".format_price_to_display($pay_money)."元");
				showSuccess("提现成功",$ajax);
			}
			else
			{
				showErr($result['message'],$ajax);
			}
			
		}
		else
		{
			showErr("数据不存在",$ajax);
		}
		
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
				$sql = "delete from ".DB_PREFIX."user_deposit where id in (".$id.")";
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