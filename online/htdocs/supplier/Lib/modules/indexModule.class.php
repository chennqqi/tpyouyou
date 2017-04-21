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
		$nav_list = require APP_ROOT_PATH.APP_NAME."/admnav_cfg.php";
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
		
		$GLOBALS['tmpl']->assign("file_manage_url",admin_url("file#manage"));

		$GLOBALS['tmpl']->assign("changepwdurl",admin_url("index#change_password"));
		
		
		//开始输出统计
		$stat = array();

		$stat['tourline_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tourline where is_effect = 1 and supplier_id = ".$this->supplier_id));
		$stat['spot_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."spot where supplier_id = ".$this->supplier_id));
		$stat['ticket_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ticket where is_effect = 1 and supplier_id = ".$this->supplier_id));
		$stat['tuan_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tourline where is_effect = 1 and is_tuan = 1 and tuan_end_time > ".NOW_TIME." and supplier_id = ".$this->supplier_id))
		+ intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ticket where is_effect = 1 and is_tuan = 1 and tuan_end_time > ".NOW_TIME." and supplier_id = ".$this->supplier_id));
		
		$stat['tourline_order_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tourline_order where pay_status = 1 and supplier_id = ".$this->supplier_id));
		$stat['tourline_order_amount'] = format_price_to_display(intval($GLOBALS['db']->getOne("select sum(pay_amount) from ".DB_PREFIX."tourline_order where supplier_id = ".$this->supplier_id)));
		$stat['tourline_order_refund_amount'] = format_price_to_display(intval($GLOBALS['db']->getOne("select sum(refund_amount) from ".DB_PREFIX."tourline_order where supplier_id = ".$this->supplier_id)));
		
		$stat['ticket_order_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ticket_order where pay_status = 1 and supplier_id = ".$this->supplier_id));
		$stat['ticket_order_amount'] = format_price_to_display(intval($GLOBALS['db']->getOne("select sum(pay_amount) from ".DB_PREFIX."ticket_order where supplier_id = ".$this->supplier_id)));
		$stat['ticket_order_refund_amount'] = format_price_to_display(intval($GLOBALS['db']->getOne("select sum(refund_amount) from ".DB_PREFIX."ticket_order where supplier_id = ".$this->supplier_id)));
		
		
		
		$stat['order_count'] = $stat['tourline_order_count'] + $stat['ticket_order_count'];
		$stat['order_amount'] = $stat['tourline_order_amount'] + $stat['ticket_order_amount'];
		$stat['order_refund_amount'] = $stat['tourline_order_refund_amount'] + $stat['ticket_order_refund_amount'];
		
		$GLOBALS['tmpl']->assign("stat",$stat);
	
		$GLOBALS['tmpl']->display("core/index/index.html");
	}	
	
	
	//修改管理员密码
	public function change_password()
	{
		$adm_session = es_session::get(md5(app_conf("AUTH_KEY")."supplier"));		
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
		
		$admin_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".$adm_id);
		if($admin_data['user_pwd']!=md5(trim($_REQUEST['adm_password'])))
		{
			showErr(lang("ADM_PASSWORD_ERROR"),$ajax);
		}
		$GLOBALS['db']->query("update ".DB_PREFIX."supplier set user_pwd = '".md5(trim($_REQUEST['adm_new_password']))."' where id = ".$admin_data['id']);
		showSuccess(lang("CHANGE_SUCCESS"),$ajax);	
	
	}
	

}
?>