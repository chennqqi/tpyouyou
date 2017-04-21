<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class dealmsglistModule extends AuthModule
{
	public function index()
	{		

		$param = array();
		
		//条件
		$condition = " 1 = 1 ";
		if(isset($_REQUEST['dest']))
		$dest = strim($_REQUEST['dest']);
		else
		$dest = "";
		$param['dest'] = $dest;
		if($dest!='')
		{
			$condition.=" and dest like '%".$dest."%' ";
		}		
		
		if(isset($_REQUEST['content']))
			$content = strim($_REQUEST['content']);
		else
			$content = "";
		$param['content'] = $content;
		if($content!='')
		{
			$condition.=" and content like '%".$content."%' ";
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
			$param['orderField'] = "create_time";
		
		if(isset($_REQUEST['orderDirection']))
			$param['orderDirection'] = strim($_REQUEST['orderDirection'])=="asc"?"asc":"desc";
		else
			$param['orderDirection'] = "desc";
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_msg_list where ".$condition." order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_msg_list where ".$condition);
		
		foreach($list as $k=>$v)
		{
			$list[$k]['send_type'] = $v['send_type']==0?lang("SMS_SEND"):lang("MAIL_SEND");
			$list[$k]['create_time'] = to_date($v['create_time']);
			$list[$k]['user_id'] = get_user_name($v['user_id']);
			$list[$k]['send_time'] = to_date($v['send_time']);
			$list[$k]['is_send'] = get_status($v['is_send']);
			$list[$k]['is_success'] = $v['is_success']==0?lang("FAILED"):lang("SUCCESS");
			$list[$k]['content'] = htmlspecialchars($v['content']);
		}
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("dealmsglist"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("dealmsglist#foreverdelete",array('ajax'=>1)));
		$GLOBALS['tmpl']->assign("viewurl",admin_url("dealmsglist#show_content",array('ajax'=>1)));
		$GLOBALS['tmpl']->assign("sendurl",admin_url("dealmsglist#send",array('ajax'=>1)));
		$GLOBALS['tmpl']->assign("reseturl",admin_url("index#reset_sending",array('field'=>"DEAL_MSG_LOCK","ajax"=>1)));
		$GLOBALS['tmpl']->display("core/dealmsglist/index.html");
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
				$sql = "delete from ".DB_PREFIX."deal_msg_list where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				save_log(lang("DEL")."ID:".strim($_REQUEST ['id']), 1);
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
	
	
	public function send()
	{
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);		
		$msg_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_msg_list where id = ".$id);
		if($msg_item)
		{
			if($msg_item['send_type']==0)
			{
				//短信
				require_once APP_ROOT_PATH."system/utils/es_sms.php";
				$sms = new sms_sender();
	
				$result = $sms->sendSms($msg_item['dest'],$msg_item['content']);
				$msg_item['result'] = $result['msg'];
				$msg_item['is_success'] = intval($result['status']);
				$msg_item['send_time'] = NOW_TIME;
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_item,"UPDATE","id=".$id);
				if($result['status'])
				{
					showSuccess(lang("SEND_NOW").lang("SUCCESS"),$ajax);
				}
				else
				{
						
					showErr(lang("SEND_NOW").lang("FAILED").$result['msg'],$ajax);
				}
			}
			else
			{
				//邮件
				require_once APP_ROOT_PATH."system/utils/es_mail.php";
				$mail = new mail_sender();
	
				$mail->AddAddress($msg_item['dest']);
				$mail->IsHTML($msg_item['is_html']); 				  // 设置邮件格式为 HTML
				$mail->Subject = $msg_item['title'];   // 标题
				$mail->Body = $msg_item['content'];  // 内容
				$result = $mail->Send();
	
				$msg_item['result'] = $mail->ErrorInfo;
				$msg_item['is_success'] = intval($result);
				$msg_item['send_time'] = NOW_TIME;
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_item,"UPDATE","id=".$id);
				if($result)
				{
					showSuccess(lang("SEND_NOW").lang("SUCCESS"),$ajax);
				}
				else
				{
						
					showErr(lang("SEND_NOW").lang("FAILED").$mail->ErrorInfo,$ajax);
				}
	
			}
		}
		else
		{
			showErr(lang("SEND_NOW").lang("FAILED"),$ajax);
		}
	}
	
	public function show_content()
	{
		$id = intval($_REQUEST['id']);
		$content = $GLOBALS['db']->getOne("select content from ".DB_PREFIX."deal_msg_list where id = ".$id);
		$content = htmlspecialchars($content);
		$GLOBALS['tmpl']->assign("content",$content);
		$GLOBALS['tmpl']->display("core/dealmsglist/show_content.html");
	}

}
?>