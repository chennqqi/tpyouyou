<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class user_confModule extends AuthModule
{	
	public function index()
	{				
		$user_conf = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_conf limit 1");
		if($user_conf)
		{
			$user_conf['USER_REG_MONEY'] = format_price_to_display($user_conf['USER_REG_MONEY']);
			$user_conf['USER_LOGIN_MONEY'] = format_price_to_display($user_conf['USER_LOGIN_MONEY']);
		}
		else
		{
			$user_conf['USER_REG_MONEY'] = 0;
			$user_conf['USER_REG_EXP'] = 0;
			$user_conf['USER_REG_SCORE'] = 0;
		}
		
		$voucher_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."voucher_type where is_promote = 1 and is_effect = 1");
		$GLOBALS['tmpl']->assign("voucher_list",$voucher_list);
		$GLOBALS['tmpl']->assign("user_conf",$user_conf);
		$GLOBALS['tmpl']->assign("formaction",admin_url("user_conf#update",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/user_conf/index.html");
	}	
	

	
	
	
	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		
		$data['USER_REG_MONEY'] = format_price_to_db(floatval($_REQUEST['USER_REG_MONEY']));
		$data['USER_REG_SCORE'] = intval($_REQUEST['USER_REG_SCORE']);
		$data['USER_ALLOW_DEPOSIT'] = intval($_REQUEST['USER_ALLOW_DEPOSIT']);
		$data['USER_REG_EXP'] = intval($_REQUEST['USER_REG_EXP']);
		$data['USER_EMAIL_VERIFY'] = intval($_REQUEST['USER_EMAIL_VERIFY']);
		$data['USER_MOBILE_VERIFY'] = intval($_REQUEST['USER_MOBILE_VERIFY']);
		$data['USER_REG_VOUCHER']	= intval($_REQUEST['USER_REG_VOUCHER']);
		$data['USER_REG_LICENSE'] = btrim($_REQUEST['USER_REG_LICENSE']);
        $data['USER_ACTIVE_PAGE_LOAD_GOUNT']	= intval($_REQUEST['USER_ACTIVE_PAGE_LOAD_GOUNT']);
        $data['USER_ACTIVE_PAGE_ITEM_COUNT']	= intval($_REQUEST['USER_ACTIVE_PAGE_ITEM_COUNT']);
        $data['USER_REGIST_IP_SPAN']	=	intval($_REQUEST['USER_REGIST_IP_SPAN']);
        $data['USER_LOGIN_MONEY'] = format_price_to_db(floatval($_REQUEST['USER_LOGIN_MONEY']));
        $data['USER_LOGIN_SCORE']	= intval($_REQUEST['USER_LOGIN_SCORE']);
        $data['USER_LOGIN_POINT']	= intval($_REQUEST['USER_LOGIN_POINT']);
		// 更新数据
		$GLOBALS['db']->query("delete from ".DB_PREFIX."user_conf");
		$GLOBALS['db']->autoExecute(DB_PREFIX."user_conf",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_sys_config();
			save_log(lang("UPDATE_SUCCESS"),1);
			showSuccess(lang("UPDATE_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
}
?>