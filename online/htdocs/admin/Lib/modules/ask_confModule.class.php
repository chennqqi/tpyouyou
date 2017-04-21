<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ask_confModule extends AuthModule{
    public function index()
	{				
		$ask_conf = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ask_conf limit 1");
		if($ask_conf)
		{
			$ask_conf['ASK_MONEY'] = format_price_to_display($ask_conf['ASK_MONEY']);
		}
		else
		{
			$ask_conf['ASK_MONEY'] = 0;
			$ask_conf['ASK_EXP'] = 0;
			$ask_conf['ASK_SCORE'] = 0;
		}
		$GLOBALS['tmpl']->assign("ask_conf",$ask_conf);
		$GLOBALS['tmpl']->assign("formaction",admin_url("ask_conf#update",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/ask_conf/index.html");
	}	
	

	
	
	
	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		
		$data['ASK_MONEY'] = format_price_to_db(floatval($_REQUEST['ASK_MONEY']));
		$data['ASK_SCORE'] = intval($_REQUEST['ASK_SCORE']);
		$data['ASK_EXP'] = intval($_REQUEST['ASK_EXP']);

		// 更新数据
		$GLOBALS['db']->query("delete from ".DB_PREFIX."ask_conf");
		$GLOBALS['db']->autoExecute(DB_PREFIX."ask_conf",$data,"INSERT","","SILENT");
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