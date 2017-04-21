<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class msg_templateModule extends AuthModule
{	
	public function index()
	{				
		$templates = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."msg_template");
		foreach($templates as $k=>$v)
		{
			$templates[$k]['show_name'] = lang($v['name']);
		}
		$GLOBALS['tmpl']->assign("templates",$templates);
		$GLOBALS['tmpl']->assign("formaction",admin_url("msg_template#update",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("load_template_url",admin_url("msg_template#load_template",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/msg_template/index.html");
	}	
	
	public function load_template()
	{
		$ajax = intval($_REQUEST['ajax']);
		$name = strim($_REQUEST['name']);
		$template = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = '".$name."' ");

		if($template)
		{
			$template['var_desc'] = lang($template['name']."_VAR_DESC");
			$result['statusCode']=200;
			$result['template'] = $template;
		}
		else
		{
			$result['statusCode'] = 300;
			$result['message'] = "模板不存在";
		}
		ajax_return($result);
	}
	

	
	
	
	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		$data['name'] = strim($_REQUEST['name']);
		$data['is_html'] = intval($_REQUEST['is_html']);
		$data['content'] = btrim($_REQUEST['content']);
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."msg_template",$data,"UPDATE","`name`='".$data['name']."'","SILENT");

		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log(lang("UPDATE_SUCCESS"),1);
			showSuccess(lang("UPDATE_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
}
?>