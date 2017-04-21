<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class accountModule extends BaseModule
{
	public function index()
	{
		app_redirect(url("account#money"));
	}
	
	/**
	 * 充值记录
	 */
	public function money()
	{
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			app_redirect(url("user#login"));
		}		
		require_once APP_ROOT_PATH.APP_NAME."/Lib/page.php";
		
		init_app_page();		
				
		$condition = " user_id = ".$GLOBALS['user']['id']." ";
		if(isset($_REQUEST['begin_time']))
		{
			$begin_time = strim($_REQUEST['begin_time']);
			if($begin_time!="")$begin_time_span = to_timespan($begin_time);
			if($begin_time_span)
				$condition.=" and pay_time >= ".$begin_time_span." ";
			$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		}
		if(isset($_REQUEST['end_time']))
		{
			$end_time = strim($_REQUEST['end_time']);
			if($end_time!="")$end_time_span = to_timespan($end_time)+3600*24;
			if($end_time_span)
				$condition.=" and pay_time < ".$end_time_span." ";
			$GLOBALS['tmpl']->assign("end_time",$end_time);
		}
		
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*USER_PAGE_SIZE).",".USER_PAGE_SIZE;
		
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_incharge where ".$condition);
		if($total>0)
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_incharge where ".$condition." order by pay_time desc limit ".$limit);
		
		$page = new Page($total,USER_PAGE_SIZE);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
	
		foreach($list as $k=>$v)
		{
			$list[$k]['money'] = format_price_to_display($v['money']);
			$list[$k]['pay_money'] = format_price_to_display($v['pay_money']);
			$list[$k]['create_time'] = to_date($v['create_time']);
			$list[$k]['pay_time'] = to_date($v['pay_time']);
		}
		$GLOBALS['tmpl']->assign("list",$list);
		
		$GLOBALS['tmpl']->display("account_money.html");
	}
	
	
	/**
	 * 提现记录
	 */
	public function deposit()
	{
		if(app_conf("USER_ALLOW_DEPOSIT")==0)
		{
			app_redirect(url("account#money"));
		}
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			app_redirect(url("user#login"));
		}
		require_once APP_ROOT_PATH.APP_NAME."/Lib/page.php";
	
		init_app_page();
		
	
		$condition = " user_id = ".$GLOBALS['user']['id']." ";
		if(isset($_REQUEST['begin_time']))
		{
			$begin_time = strim($_REQUEST['begin_time']);
			if($begin_time!="")$begin_time_span = to_timespan($begin_time);
			if($begin_time_span)
				$condition.=" and pay_time >= ".$begin_time_span." ";
			$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		}
		if(isset($_REQUEST['end_time']))
		{
			$end_time = strim($_REQUEST['end_time']);
			if($end_time!="")$end_time_span = to_timespan($end_time)+3600*24;
			if($end_time_span)
				$condition.=" and pay_time < ".$end_time_span." ";
			$GLOBALS['tmpl']->assign("end_time",$end_time);
		}
	
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*USER_PAGE_SIZE).",".USER_PAGE_SIZE;
	
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_deposit where ".$condition);
		if($total>0)
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_deposit where ".$condition." order by pay_time desc limit ".$limit);
		
		$page = new Page($total,USER_PAGE_SIZE);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
	
		if($list){
			foreach($list as $k=>$v)
			{
				$list[$k]['money'] = format_price_to_display($v['money']);
				$list[$k]['pay_money'] = format_price_to_display($v['pay_money']);
				$list[$k]['create_time'] = to_date($v['create_time']);
				$list[$k]['pay_time'] = to_date($v['pay_time']);
			}
			$GLOBALS['tmpl']->assign("list",$list);
		}
	
		$GLOBALS['tmpl']->display("account_deposit.html");
	}
	
	/**
	 * 现金账单
	 */
	public function money_log()
	{
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			app_redirect(url("user#login"));
		}
		require_once APP_ROOT_PATH.APP_NAME."/Lib/page.php";
	
		init_app_page();
		
		
		$condition = " user_id = ".$GLOBALS['user']['id']." and log_type = 1 ";
		if(isset($_REQUEST['begin_time']))
		{
			$begin_time = strim($_REQUEST['begin_time']);
			if($begin_time!="")$begin_time_span = to_timespan($begin_time);
			if($begin_time_span)
				$condition.=" and log_time >= ".$begin_time_span." ";
			$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		}
		if(isset($_REQUEST['end_time']))
		{
			$end_time = strim($_REQUEST['end_time']);
			if($end_time!="")$end_time_span = to_timespan($end_time)+3600*24;
			if($end_time_span)
				$condition.=" and log_time < ".$end_time_span." ";
			$GLOBALS['tmpl']->assign("end_time",$end_time);
		}
	
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*USER_PAGE_SIZE).",".USER_PAGE_SIZE;
	
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_log where ".$condition);
		if($total>0)
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_log where ".$condition." order by log_time desc limit ".$limit);

		$page = new Page($total,USER_PAGE_SIZE);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);

		foreach($list as $k=>$v)
		{
			$list[$k]['money'] = format_price_to_display($v['money']);
			$list[$k]['log_time'] = to_date($v['log_time']);
		}
		$GLOBALS['tmpl']->assign("list",$list);
	
		$GLOBALS['tmpl']->display("account_money_log.html");
	}
	
	
	public function do_incharge()
	{
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			app_redirect(url("user#login"));
		}
		
		$order_sn = strim($_REQUEST['order_sn']);
		$money = format_price_to_db(floatval($_REQUEST['money']));
		
		if($order_sn)
		{
			$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_incharge where user_id = ".$GLOBALS['user']['id']." and order_sn = '".$order_sn."' and is_paid = 0 ");
			
		}
		if(empty($order))
		{
			if($money<=0)
			{
				showErr("充值金额有错");
			}
			else
			{
				$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_incharge where user_id = ".$GLOBALS['user']['id']." and is_paid = 0 and money = ".$money);			
			}		
		}		
		
		if(empty($order))
		{
			$order = array();
			$order['user_id'] = $GLOBALS['user']['id'];
			$order['create_time'] = NOW_TIME;
			$order['money'] = $money;		
			$order['is_paid'] = 0;
			$order['pay_time'] = 0;
			$order['pay_money'] = 0;
			do{
				$order['order_sn'] = "U_".NOW_TIME.rand(111, 999);
				$GLOBALS['db']->autoExecute(DB_PREFIX."user_incharge",$order,"INSERT","","SILENT");
				$order['id'] = intval($GLOBALS['db']->insert_id());
			}while($order['id']==0);
		}
		else
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."user_incharge set create_time = ".NOW_TIME." where id = ".$order['id']);
			$order['create_time'] = NOW_TIME;
		}
		
		app_redirect(url("transaction#pay",array("ot"=>"4","sn"=>$order['order_sn'])));
		
	}
	
	
	public function do_deposit()
	{
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			showErr("登录超时",1,url("user#login"));
		}
		if(app_conf("USER_ALLOW_DEPOSIT")==0)
		{
			showErr("提现功能关闭",1);
		}
		
		$deposit_account = strim($_REQUEST['deposit_account']);
		$deposit_bank = strim($_REQUEST['deposit_bank']);
		$deposit_name = strim($_REQUEST['deposit_name']);
		$user_pwd = strim($_REQUEST['user_pwd']);
		$money = floatval($_REQUEST['money']);
		$money = format_price_to_db($money);
		if($deposit_bank=="")
		{
			showErr("请输入开户行",1);
		}
		if($deposit_account=="")
		{
			showErr("请输入提现帐号",1);
		}
		if($deposit_name=="")
		{
			showErr("请填写收款人真实姓名",1);
		}
		if($user_pwd=="")
		{
			showErr("请输入会员密码",1);
		}
		if(md5($user_pwd.$GLOBALS['user']['salt'])!=$GLOBALS['user']['user_pwd'])
		{
			showErr("会员密码错误",1);
		}
		if($money<=0)
		{
			showErr("提现金额出错",1);
		}
		if($money>$GLOBALS['user']['money'])
		{
			showErr("提现超额",1);
		}
		
		
		$data['user_id'] = $GLOBALS['user']['id'];
		$data['money'] = $money;
		$data['create_time'] = NOW_TIME;
		$data['deposit_account'] = $deposit_account;
		$data['deposit_bank'] = $deposit_bank;
		$data['deposit_name'] = $deposit_name;
		$GLOBALS['db']->autoExecute(DB_PREFIX."user_deposit",$data,"INSERT","","SILENT");
		$id = $GLOBALS['db']->insert_id();
		if($id>0)
			showSuccess("提现成功",1);
		else
			showErr("数据库繁忙，请重试",1);
		
	}

	public function del_deposit()
	{
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			showErr("登录超时",1,url("user#login"));
		}
		
		$id = intval($_REQUEST['id']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."user_deposit where user_id = ".$GLOBALS['user']['id']." and id = ".$id." and is_paid = 0");
		if($GLOBALS['db']->affected_rows()>0)
		{
			showSuccess("删除成功",1);
		}
		else
		{
			showSuccess("删除失败",1);
		}
		
	}
	
	
	//代金券
	public function voucher()
	{
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			app_redirect(url("user#login"));
		}
		require_once APP_ROOT_PATH.APP_NAME."/Lib/page.php";
		
		init_app_page();
		
		
		
		$condition = " deliver_type <> 3 and is_effect = 1 ";

		
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*USER_PAGE_SIZE).",".USER_PAGE_SIZE;
		
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."voucher_type where ".$condition);
		if($total>0)
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."voucher_type where ".$condition." order by sort desc limit ".$limit);
		
		$page = new Page($total,USER_PAGE_SIZE);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		
		foreach($list as $k=>$v)
		{
			$list[$k]['money'] = format_price_to_display($v['money']);
			$list[$k]['deliver_end_time'] = to_date($v['deliver_end_time']);
			$list[$k]['voucher_end_time'] = to_date($v['voucher_end_time']);
			$list[$k]['deliver_end_time']=empty($list[$k]['deliver_end_time'])?"无限期":$list[$k]['deliver_end_time'];
			$list[$k]['voucher_end_time']=empty($list[$k]['voucher_end_time'])?"无限期":$list[$k]['voucher_end_time'];
			$deliver_type = "所有会员";
			if($v['deliver_rel_id']>0)
			{
				if($v['deliver_type']==1)
				{
					//会员等级
					$deliver_type = $user_level[$v['deliver_rel_id']]['name'];
				}
				if($v['deliver_type']==2)
				{
					//会员组
					$deliver_type = $user_group[$v['deliver_rel_id']]['name'];
				}
			}
			$list[$k]['deliver_type'] = $deliver_type;
			$list[$k]['remain'] = $v['deliver_limit']==0?"无限量":$v['deliver_limit'] - $v['deliver_count'];
			if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."voucher where user_id = ".$GLOBALS['user']['id']." and voucher_type_id = ".$v['id'])>0)
			{
				$list[$k]['is_get'] = 1;
			}
			else
			{
				$list[$k]['is_get'] = 0;
			}
		}
		$GLOBALS['tmpl']->assign("list",$list);
		
		$GLOBALS['tmpl']->display("account_voucher.html");
	}
	
	public function getvoucher()
	{
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			showErr("登录超时",1,url("user#login"));
		}
		$id = intval($_REQUEST['id']);
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."voucher where user_id = ".$GLOBALS['user']['id']." and voucher_type_id = ".$id)>0)
		{
			showErr("您已经领取过该代金券",1);
		}
		else
		{
			require_once APP_ROOT_PATH."system/libs/voucher.php";
			$res = Voucher::gen($id, $GLOBALS['user']);
			if($res['status'])
			{
				User::modify_account($GLOBALS['user']['id'], 4, $res['data']['money'], "领取了".$res['data']['voucher_name']);
				showSuccess("领取成功",1);
			}
			else
			{
				showErr($res['message'],1);
			}
			
		}
				
	}
	
	
	//代金券
	public function myvoucher()
	{
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			app_redirect(url("user#login"));
		}
		require_once APP_ROOT_PATH.APP_NAME."/Lib/page.php";
	
		init_app_page();
		
	
		$condition = " is_effect = 1 and user_id = ".$GLOBALS['user']['id'];
		
		if(isset($_REQUEST['use']))
		{
			$use = intval($_REQUEST['use']);
			if($use ==2)
				$use=0;
		}
		else
		{
			$use = "-1";
		}
		if($use>=0)$condition.=" and is_used = ".$use." ";
		$GLOBALS['tmpl']->assign('use',$use);
	
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*USER_PAGE_SIZE).",".USER_PAGE_SIZE;
	
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."voucher where ".$condition);
		if($total>0)
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."voucher where ".$condition." order by create_time desc limit ".$limit);
	
		$page = new Page($total,USER_PAGE_SIZE);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
	
	
		foreach($list as $k=>$v)
		{
			$list[$k]['money'] = format_price_to_display($v['money']);
			$list[$k]['create_time'] = to_date($v['create_time']);
			$list[$k]['end_time'] = to_date($v['end_time']);
			$list[$k]['use_time'] = to_date($v['use_time']);
			$list[$k]['end_time']=empty($list[$k]['end_time'])?"无限期":$list[$k]['end_time'];
			$list[$k]['use_time']=empty($list[$k]['use_time'])?"未使用":$list[$k]['use_time'];
			
		}
		$GLOBALS['tmpl']->assign("list",$list);
	
		$GLOBALS['tmpl']->display("account_myvoucher.html");
	}
	
	
	/**
	 * 代金券账单
	 */
	public function voucher_log()
	{
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			app_redirect(url("user#login"));
		}
		require_once APP_ROOT_PATH.APP_NAME."/Lib/page.php";
	
		init_app_page();
		
	
		$condition = " user_id = ".$GLOBALS['user']['id']." and log_type = 4 ";
		if(isset($_REQUEST['begin_time']))
		{
			$begin_time = strim($_REQUEST['begin_time']);
			if($begin_time!="")$begin_time_span = to_timespan($begin_time);
			if($begin_time_span)
				$condition.=" and log_time >= ".$begin_time_span." ";
			$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		}
		if(isset($_REQUEST['end_time']))
		{
			$end_time = strim($_REQUEST['end_time']);
			if($end_time!="")$end_time_span = to_timespan($end_time)+3600*24;
			if($end_time_span)
				$condition.=" and log_time < ".$end_time_span." ";
			$GLOBALS['tmpl']->assign("end_time",$end_time);
		}
	
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*USER_PAGE_SIZE).",".USER_PAGE_SIZE;
	
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_log where ".$condition);
		if($total>0)
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_log where ".$condition." order by log_time desc limit ".$limit);
	
		$page = new Page($total,USER_PAGE_SIZE);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
	
		foreach($list as $k=>$v)
		{
			$list[$k]['voucher_money'] = format_price_to_display($v['voucher_money']);
			$list[$k]['log_time'] = to_date($v['log_time']);
		}
		$GLOBALS['tmpl']->assign("list",$list);
	
		$GLOBALS['tmpl']->display("account_voucher_log.html");
	}
	
	public function score()
	{
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			app_redirect(url("user#login"));
		}
		require_once APP_ROOT_PATH.APP_NAME."/Lib/page.php";
		
		init_app_page();
		
		
		$condition = " user_id = ".$GLOBALS['user']['id']." and log_type = 2 ";
		if(isset($_REQUEST['begin_time']))
		{
			$begin_time = strim($_REQUEST['begin_time']);
			if($begin_time!="")$begin_time_span = to_timespan($begin_time);
			if($begin_time_span)
				$condition.=" and log_time >= ".$begin_time_span." ";
			$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		}
		if(isset($_REQUEST['end_time']))
		{
			$end_time = strim($_REQUEST['end_time']);
			if($end_time!="")$end_time_span = to_timespan($end_time)+3600*24;
			if($end_time_span)
				$condition.=" and log_time < ".$end_time_span." ";
			$GLOBALS['tmpl']->assign("end_time",$end_time);
		}
		
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*USER_PAGE_SIZE).",".USER_PAGE_SIZE;
		
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_log where ".$condition);
		if($total>0)
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_log where ".$condition." order by log_time desc limit ".$limit);
		
		$page = new Page($total,USER_PAGE_SIZE);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		foreach($list as $k=>$v)
		{
			$list[$k]['log_time'] = to_date($v['log_time']);
		}
		$GLOBALS['tmpl']->assign("list",$list);
		
		$GLOBALS['tmpl']->display("account_score.html");
	}
	
}
?>