<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class indexModule extends AuthModule
{
	public function index()
	{		
		init_app_page();

		//输出菜单列表 
		$nav_list = require APP_ROOT_PATH."system/admnav_cfg.php";
		foreach($nav_list as $k=>$v)
		{
			foreach($v as $kk=>$vv)
			{
				foreach($vv as $kkk=>$vvv)
				{
					$nav_list[$k][$kk][$kkk]['url'] = admin_url($vvv['module']."#".$vvv['action']);
				}
			}
		}
		
		$GLOBALS['tmpl']->assign("nav_list",$nav_list);
		
		$GLOBALS['tmpl']->assign("logout_url",admin_url("login#logout"));
		
		$GLOBALS['tmpl']->assign("login_url",admin_url("login"));
		
		$GLOBALS['tmpl']->assign("file_upload_url",admin_url("file#upload",array("FANWE_SESSID"=>es_session::id())));
		
		$GLOBALS['tmpl']->assign("attachment_upload_url",admin_url("file#uploadfile"));
		
		$GLOBALS['tmpl']->assign("flash_upload_url",admin_url("file#uploadflash"));
		
		$GLOBALS['tmpl']->assign("video_upload_url",admin_url("file#uploadvideo"));
		
		$GLOBALS['tmpl']->assign("img_upload_url",admin_url("file#uploadimg"));
		
		$GLOBALS['tmpl']->assign("file_manage_url",admin_url("file#manage"));

		$GLOBALS['tmpl']->assign("changepwdurl",admin_url("index#change_password"));
		
		$GLOBALS['tmpl']->assign("cacheurl",admin_url("cache"));
		
		$GLOBALS['tmpl']->assign("deal_msg_list_url",url("cron#deal_msg_list"));
		
		$GLOBALS['tmpl']->assign("promote_msg_list_url",url("cron#promote_msg_list"));
		
		//开始输出统计
		$stat = array();
		$stat['regist_count'] =  intval($GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."user"));   //注册人数统计
		$stat['incharge_count'] = intval($GLOBALS['db']->getOne("select count(user_id) from ".DB_PREFIX."user_incharge  where is_paid = 1 group by user_id")); //充值过的会员数
		$stat['incharge_amount'] = format_price_to_display(intval($GLOBALS['db']->getOne("select sum(pay_money) from ".DB_PREFIX."user_incharge  where is_paid = 1 "))); //充值总额
		$stat['incharge_order_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_incharge where is_paid = 1"));
		
		$stat['tourline_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tourline where is_effect = 1"));
		$stat['spot_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."spot"));
		$stat['ticket_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ticket where is_effect = 1"));
		$stat['tuan_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tuan where is_effect = 1 and (end_time = 0 or end_time >".NOW_TIME.")"));
		
		$stat['tourline_order_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tourline_order where pay_status = 1"));
		$stat['tourline_order_amount'] = format_price_to_display(intval($GLOBALS['db']->getOne("select sum(pay_amount) from ".DB_PREFIX."tourline_order")));
		$stat['tourline_order_online_pay'] = format_price_to_display(intval($GLOBALS['db']->getOne("select sum(online_pay) from ".DB_PREFIX."tourline_order")));
		$stat['tourline_order_account_pay'] = format_price_to_display(intval($GLOBALS['db']->getOne("select sum(account_pay) from ".DB_PREFIX."tourline_order")));
		$stat['tourline_order_voucher_pay'] = format_price_to_display(intval($GLOBALS['db']->getOne("select sum(voucher_pay) from ".DB_PREFIX."tourline_order")));
		$stat['tourline_order_refund_amount'] = format_price_to_display(intval($GLOBALS['db']->getOne("select sum(refund_amount) from ".DB_PREFIX."tourline_order")));
		
		$stat['ticket_order_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ticket_order where pay_status = 1"));
		$stat['ticket_order_amount'] = format_price_to_display(intval($GLOBALS['db']->getOne("select sum(pay_amount) from ".DB_PREFIX."ticket_order")));
		$stat['ticket_order_online_pay'] = format_price_to_display(intval($GLOBALS['db']->getOne("select sum(online_pay) from ".DB_PREFIX."ticket_order")));
		$stat['ticket_order_account_pay'] = format_price_to_display(intval($GLOBALS['db']->getOne("select sum(account_pay) from ".DB_PREFIX."ticket_order")));
		$stat['ticket_order_voucher_pay'] = format_price_to_display(intval($GLOBALS['db']->getOne("select sum(voucher_pay) from ".DB_PREFIX."ticket_order")));
		$stat['ticket_order_refund_amount'] = format_price_to_display(intval($GLOBALS['db']->getOne("select sum(refund_amount) from ".DB_PREFIX."ticket_order")));
		
		
		
		$stat['order_count'] = $stat['tourline_order_count'] + $stat['ticket_order_count'] + $stat['incharge_order_count'];
		$stat['order_amount'] = $stat['tourline_order_amount'] + $stat['ticket_order_amount'] + $stat['incharge_amount'];
		$stat['order_online_pay'] = $stat['tourline_order_online_pay'] + $stat['ticket_order_online_pay'] + $stat['incharge_amount'];
		$stat['order_account_pay'] =  $stat['tourline_order_account_pay'] + $stat['ticket_order_account_pay'];
		$stat['order_voucher_pay'] = $stat['tourline_order_voucher_pay'] + $stat['ticket_order_voucher_pay'];
		$stat['order_refund_amount'] = $stat['tourline_order_refund_amount'] + $stat['ticket_order_refund_amount'];
		
		$stat['review_total'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."review where is_verify = 1"));
		$stat['tourline_review_total'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."review where is_verify = 1 and review_type = 1"));
		$stat['ticket_review_total'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."review where is_verify = 1 and review_type = 2"));
		
		$stat['guide_total']  = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tour_guide where is_public = 2"));
		
		$stat['return_amount'] = format_price_to_display(intval($GLOBALS['db']->getOne("select sum(return_money_total) from ".DB_PREFIX."tourline_order")) + intval($GLOBALS['db']->getOne("select sum(return_money_total) from ".DB_PREFIX."ticket_order")));
		$stat['rebate_amount'] = format_price_to_display(intval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."user_rebate where pay_time > 0")));
		$GLOBALS['tmpl']->assign("stat",$stat);
		$GLOBALS['tmpl']->display("core/index/index.html");
	}	
	
	
	//修改管理员密码
	public function change_password()
	{
		$adm_session = es_session::get(md5(app_conf("AUTH_KEY")));		
		$GLOBALS['tmpl']->assign("adm_data",$adm_session);
		$GLOBALS['tmpl']->assign("dochangeurl",admin_url("index#do_change_password",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/index/change_password.html");
	}
	
	
	public function do_change_password()
	{
		$ajax = intval($_REQUEST['ajax']);
		$adm_id = intval($_REQUEST['adm_id']);
		if(!check_empty('adm_password'))
		{
			showErr(lang("ADM_PASSWORD_EMPTY_TIP"),$ajax);
		}
		if(!check_empty('adm_new_password'))
		{
			showErr(lang("ADM_NEW_PASSWORD_EMPTY_TIP"),$ajax);
		}
		if(trim($_REQUEST['adm_confirm_password'])!=trim($_REQUEST['adm_new_password']))
		{
			showErr(lang("ADM_NEW_PASSWORD_NOT_MATCH_TIP"),$ajax);
		}
		
		$admin_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."admin where id = ".$adm_id);
		if($admin_data['adm_password']!=md5(trim($_REQUEST['adm_password'])))
		{
			showErr(lang("ADM_PASSWORD_ERROR"),$ajax);
		}
		$GLOBALS['db']->query("update ".DB_PREFIX."admin set adm_password = '".md5(trim($_REQUEST['adm_new_password']))."' where id = ".$admin_data['id']);
		save_log($admin_data['adm_name'].lang("CHANGE_SUCCESS"),1);
		showSuccess(lang("CHANGE_SUCCESS"),$ajax);	
	
	}
	
	
	public function reset_sending()
	{
		$ajax = intval($_REQUEST['ajax']);
		$field = strim($_REQUEST['field']);
		if($field=='DEAL_MSG_LOCK'||$field=='PROMOTE_MSG_LOCK'||$field=='APNS_MSG_LOCK')
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."conf set value = 0 where name = '".$field."'");
			showSuccess(lang("RESET_SUCCESS"),$ajax);
		}
		else
		{
			showErr(lang("INVALID_OPERATION"),$ajax);
		}
	}

}
?>