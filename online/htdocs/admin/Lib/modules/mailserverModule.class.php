<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class mailserverModule extends AuthModule
{
	public function index()
	{		
		
		$param = array();
						

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
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."mail_server  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."mail_server");
		
		foreach($list as $k=>$v)
		{
			$list[$k]['is_effect_show'] = lang("IS_EFFECT_".$v['is_effect']);
			$list[$k]['is_reset_show'] = get_status($v['is_reset']);
		}
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("mailserver"));
		$GLOBALS['tmpl']->assign("senddemourl",admin_url("mailserver#send_demo",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("seteffecturl",admin_url("mailserver#set_effect",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("delurl",admin_url("mailserver#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("mailserver#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("mailserver#add"));
		$GLOBALS['tmpl']->display("core/mailserver/index.html");
	}	
	
	public function add()
	{
		$GLOBALS['tmpl']->assign("formaction",admin_url("mailserver#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/mailserver/add.html");
	}
	
	
	public function insert() {
		$ajax = intval($_REQUEST['ajax']);
		
		if(!check_empty("smtp_server"))
		{
			showErr(lang("SMTP_SERVER_EMPTY_TIP"),$ajax);
		}
		if(!check_empty("smtp_name"))
		{
			showErr(lang("SMTP_NAME_EMPTY_TIP"),$ajax);
		}
		// 更新数据
		$log_info = strim($_REQUEST['smtp_name']);
		$data = array();
		$data['smtp_server'] = strim($_REQUEST['smtp_server']);
		$data['smtp_name'] = strim($_REQUEST['smtp_name']);
		$data['smtp_pwd'] = strim($_REQUEST['smtp_pwd']);
		$data['is_verify'] = intval($_REQUEST['is_verify']);
		$data['smtp_port'] = intval($_REQUEST['smtp_port']);
		$data['is_ssl'] = intval($_REQUEST['is_ssl']);
		$data['total_use'] = intval($_REQUEST['total_use']);
		$data['use_limit'] = intval($_REQUEST['use_limit']);
		$data['is_reset'] = intval($_REQUEST['is_reset']);
		$data['is_effect'] = intval($_REQUEST['is_effect']);
		$GLOBALS['db']->autoExecute(DB_PREFIX."mail_server",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log($log_info.lang("INSERT_SUCCESS"),1);
			showSuccess(lang("INSERT_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
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
				$log_info = $GLOBALS['db']->getOne("select group_concat(smtp_name) from ".DB_PREFIX."mail_server where id in (".$id.")");
				$sql = "delete from ".DB_PREFIX."mail_server where id in (".$id.") ";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->affected_rows()>0)
				save_log(lang("DEL").":".$log_info, 1);
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
	
	
	
	
	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."mail_server where id = ".$id);
		$GLOBALS['tmpl']->assign ( 'vo', $vo );		
	
		$GLOBALS['tmpl']->assign("formaction",admin_url("mailserver#update",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/mailserver/edit.html");
	}

	
	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		
		if(!check_empty("smtp_server"))
		{
			showErr(lang("SMTP_SERVER_EMPTY_TIP"),$ajax);
		}
		if(!check_empty("smtp_name"))
		{
			showErr(lang("SMTP_NAME_EMPTY_TIP"),$ajax);
		}
		// 更新数据
		$log_info = strim($_REQUEST['smtp_name']);
		$data = array();
		$data['smtp_server'] = strim($_REQUEST['smtp_server']);
		$data['smtp_name'] = strim($_REQUEST['smtp_name']);
		$data['smtp_pwd'] = strim($_REQUEST['smtp_pwd']);
		$data['is_verify'] = intval($_REQUEST['is_verify']);
		$data['smtp_port'] = intval($_REQUEST['smtp_port']);
		$data['is_ssl'] = intval($_REQUEST['is_ssl']);
		$data['total_use'] = intval($_REQUEST['total_use']);
		$data['use_limit'] = intval($_REQUEST['use_limit']);
		$data['is_reset'] = intval($_REQUEST['is_reset']);
		$data['is_effect'] = intval($_REQUEST['is_effect']);
		
		
		$id = intval($_REQUEST['id']);
		// 更新数据		
		$GLOBALS['db']->autoExecute(DB_PREFIX."mail_server",$data,"UPDATE","id=".$id,"SILENT");
		
		if ($GLOBALS['db']->error()=="") {			
			save_log($log_info.lang("UPDATE_SUCCESS"),1);
			showSuccess(lang("UPDATE_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}
	}
	
	
	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = $GLOBALS['db']->getOne("select smtp_name from ".DB_PREFIX."mail_server where id = ".$id);
		$c_is_effect =  $GLOBALS['db']->getOne("select is_effect from ".DB_PREFIX."mail_server where id = ".$id); //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		$GLOBALS['db']->query("update ".DB_PREFIX."mail_server set is_effect = ".$n_is_effect." where id = ".$id);
		save_log($info.lang("SET_EFFECT_".$n_is_effect),1);
		showSuccess(lang("SET_EFFECT_".$n_is_effect),$ajax)	;
	}
	
	
	public function send_demo()
	{
		es_session::close();
		$ajax = intval($_REQUEST['ajax']);
		$test_mail = strim($_REQUEST['test_mail']);
		require_once APP_ROOT_PATH."system/utils/es_mail.php";
		$mail = new mail_sender();
	
		$mail->AddAddress($test_mail);
		$mail->IsHTML(0); 				  // 设置邮件格式为 HTML
		$mail->Subject = lang("DEMO_MAIL");   // 标题
		$mail->Body = lang("DEMO_MAIL");  // 内容
	
		if(!$mail->Send())
		{
			showErr(lang("ERROR_INFO").$mail->ErrorInfo,$ajax);			
		}
		else
		{
			showSuccess(lang("SEND_SUCCESS").$mail->ErrorInfo,$ajax);
		}
	}
}
?>