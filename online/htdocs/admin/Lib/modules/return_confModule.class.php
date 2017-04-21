<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class return_confModule extends AuthModule
{	
	public function index()
	{				
		$return_conf = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."return_conf limit 1");
		if($return_conf)
		{
			if($return_conf['BUY_RETURN_MONEY_TYPE']==0)
				$return_conf['BUY_RETURN_MONEY'] = format_price_to_display($return_conf['BUY_RETURN_MONEY']);
			if($return_conf['REBATE_TYPE']==0)
				$return_conf['REBATE_MONEY'] = format_price_to_display($return_conf['REBATE_MONEY']);
		}
		$GLOBALS['tmpl']->assign("return_conf",$return_conf);
		$GLOBALS['tmpl']->assign("formaction",admin_url("return_conf#update",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/return_conf/index.html");
	}	
	

	
	
	
	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		
		$data['BUY_RETURN_MONEY_TYPE'] = intval($_REQUEST['BUY_RETURN_MONEY_TYPE']);
		if($data['BUY_RETURN_MONEY_TYPE']==0)
			$data['BUY_RETURN_MONEY'] = format_price_to_db(floatval($_REQUEST['BUY_RETURN_MONEY']));
		else
			$data['BUY_RETURN_MONEY'] = intval($_REQUEST['BUY_RETURN_MONEY']);
		$data['BUY_RETURN_SCORE_TYPE'] = intval($_REQUEST['BUY_RETURN_SCORE_TYPE']);
		$data['BUY_RETURN_SCORE'] = intval($_REQUEST['BUY_RETURN_SCORE']);
		$data['BUY_RETURN_EXP_TYPE'] = intval($_REQUEST['BUY_RETURN_EXP_TYPE']);
		$data['BUY_RETURN_EXP'] = intval($_REQUEST['BUY_RETURN_EXP']);
		$data['REBATE_TYPE'] = intval($_REQUEST['REBATE_TYPE']);
		if($data['REBATE_TYPE']==0)
			$data['REBATE_MONEY'] = format_price_to_db(floatval($_REQUEST['REBATE_MONEY']));
		else
			$data['REBATE_MONEY'] = intval($_REQUEST['REBATE_MONEY']);

		// 更新数据
		$GLOBALS['db']->query("delete from ".DB_PREFIX."return_conf");
		$GLOBALS['db']->autoExecute(DB_PREFIX."return_conf",$data,"INSERT","","SILENT");
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