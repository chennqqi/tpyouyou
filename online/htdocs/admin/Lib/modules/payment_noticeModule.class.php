<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class payment_noticeModule extends AuthModule
{	
	public function index()
	{
		$param = array();
		//条件
		$condition = " 1 = 1 ";
		if(isset($_REQUEST['class']))
			$class_key = strim($_REQUEST['class']);
		else
			$class_key = "";
		$param['class'] = $class_key;
		if($class_key!='')
		{
			$condition.=" and payment_class = '".$class_key."' ";
		}
	
		if(isset($_REQUEST['notice_sn']))
			$sn_key = strim($_REQUEST['notice_sn']);
		else
			$sn_key = "";
	
		if($sn_key!='')
		{
			$param['notice_sn'] = $sn_key;
			$condition.=" and notice_sn = '".$sn_key."' ";
		}
		
		if(isset($_REQUEST['outer_notice_sn']))
			$out_sn_key = strim($_REQUEST['outer_notice_sn']);
		else
			$out_sn_key = "";
		
		if($out_sn_key!='')
		{
			$param['outer_notice_sn'] = $out_sn_key;
			$condition.=" and outer_notice_sn = '".$out_sn_key."' ";
		}
		
		if(isset($_REQUEST['is_paid']))
			$is_paid_key = intval($_REQUEST['is_paid']);
		else
			$is_paid_key = -1;
		

		$param['is_paid'] = $is_paid_key;
		if($is_paid_key>=0)
		$condition.=" and is_paid = '".$is_paid_key."' ";
		
	
		
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
	
	
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment_notice where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."payment_notice where ".$condition);
		$totalAmount = $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."payment_notice where ".$condition);
		$totalAmount = format_price(format_price_to_display($totalAmount));
		
		foreach($list as $k=>$v)
		{
			$list[$k]['money'] = format_price(format_price_to_display($v['money']));
			$list[$k]['create_time'] = to_date($v['create_time']);
			$list[$k]['pay_time'] = $v['pay_time']==0?"未支付":to_date($v['pay_time']);
			$list[$k]['user_name'] = get_user_name($v['user_id']);
			$list[$k]['order_type'] = lang("ORDER_TYPE_".$v['order_type']);
		}
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
	
		$GLOBALS['tmpl']->assign("totalAmount",$totalAmount);
		$GLOBALS['tmpl']->assign("formaction",admin_url("payment_notice#index"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("payment_notice#foreverdelete",array('ajax'=>1)));
		$GLOBALS['tmpl']->display("core/payment_notice/index.html");
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
				$del_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment_notice where id in (".$id.")");						
				$sql = "delete from ".DB_PREFIX."payment_notice where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->affected_rows()>0)
				{
					foreach($del_list as $k=>$v)
					{
						$del_name = "删除了".get_user_name($v['user_id'])."的支付单，单号".$v['notice_sn'];
						save_log(lang("DEL").":".$del_name, 1);
					}					
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