<?php

class orderModule extends AuthModule
{
    function index() {
    	/*
    	select id, 1 as order_type,sn,tourline_name as name,user_id,total_price,pay_amount,pay_time,create_time, order_status,refund_status from fanwe_tourline_order
    	union ALL
    	select id, 2 as order_type,sn,ticket_name as name,user_id,total_price,pay_amount,pay_time,create_time,order_status,refund_status from fanwe_ticket_order
    	union ALL
    	select id, 3 as order_type,order_sn,'充值单' as name,user_id,money,pay_money,pay_time,create_time,is_paid as order_status, 0 as refund_status from fanwe_user_incharge
    	*/
    	
    	
    	$param = array();		
		//条件
		$condition1 = " 1 = 1 ";
		$condition2 = " 1 = 1 ";
		$condition3 = " 1 = 1 ";
		
		//订单号
		if(isset($_REQUEST['sn']))
			$sn = strim($_REQUEST['sn']);
		else
			$sn = "";
		$param['sn'] = $sn;
		if($sn!='')
		{
			$condition1.=" and t.sn = '".$sn."' ";
			$condition2.=" and t.sn = '".$sn."' ";
			$condition3.=" and t.order_sn = '".$sn."' ";
		}
		
		//线路ID
		if(isset($_REQUEST['supplier_id']))
			$supplier_id = strim($_REQUEST['supplier_id']);
		else
			$supplier_id = "";
		$param['supplier_id'] = $supplier_id;
		if($supplier_id!='' && intval($supplier_id) > 0)
		{
			$condition1.=" and t.supplier_id = ".intval($supplier_id)." ";
			$condition2.=" and t.supplier_id = ".intval($supplier_id)." ";
			$condition3.=" and 1 = 0 ";
		}		
					
		//线路/门票ID
		if(isset($_REQUEST['ticket_id']))
			$ticket_id = strim($_REQUEST['ticket_id']);
		else
			$ticket_id = "";
		$param['ticket_id'] = $ticket_id;
		if($ticket_id!='' && intval($ticket_id) > 0)
		{
			$condition1.=" and t.tourline_id = ".intval($ticket_id)." ";
			$condition2.=" and t.ticket_id = ".intval($ticket_id)." ";
			$condition3.=" and 1 = 0 ";
		}		
		
						
		//支付状态
		$pay_status = -1;
		if(isset($_REQUEST['pay_status']) && strim($_REQUEST['pay_status'])!="")
			$pay_status = intval($_REQUEST['pay_status']);
		
		$param['pay_status'] = $pay_status;
		if($pay_status !=-1)
		{
			$condition1 .=" and t.pay_status=$pay_status ";
			$condition2 .=" and t.pay_status=$pay_status ";
			$condition3 .=" and t.is_paid=$pay_status ";
		}
		
		//订单状态
		$order_status = -1;
		if(isset($_REQUEST['order_status']) && strim($_REQUEST['order_status'])!="")
			$order_status = intval($_REQUEST['order_status']);
		
		$param['order_status'] = $order_status;
		if($order_status !=-1)
		{
			$condition1 .=" and t.order_status=$order_status ";
			$condition2 .=" and t.order_status=$order_status ";
			$condition3 .=" and 1<>1 ";
		}
		
		//退款状态
		$refund_status = -1;
		if(isset($_REQUEST['refund_status']) && strim($_REQUEST['refund_status'])!="")
			$refund_status = intval($_REQUEST['refund_status']);
		
		$param['refund_status'] = $refund_status;
		if($refund_status !=-1)
		{
			$condition1 .=" and t.refund_status=$refund_status ";
			$condition2 .=" and t.refund_status=$refund_status ";
			$condition3 .=" and 1<>1 ";
		}
		
		$create_time_begin  = strim($_REQUEST['create_time_begin']);
		$param['create_time_begin'] = $create_time_begin;
		
		$create_time_end  = strim($_REQUEST['create_time_end']);
		$param['create_time_end'] = $create_time_end;
		
		if(!empty($create_time_begin) && !empty($create_time_end))
		{		
			$condition1.=" and t.create_time >= '".to_timespan($create_time_begin)."' and t.create_time <='". (to_timespan($create_time_end) + 3600 * 24 - 1)."' ";
			$condition2.=" and t.create_time >= '".to_timespan($create_time_begin)."' and t.create_time <='". (to_timespan($create_time_end) + 3600 * 24 - 1)."' ";
			$condition3.=" and t.create_time >= '".to_timespan($create_time_begin)."' and t.create_time <='". (to_timespan($create_time_end) + 3600 * 24 - 1)."' ";
				
		}		

		
		$pay_time_begin  = strim($_REQUEST['pay_time_begin']);
		$param['pay_time_begin'] = $pay_time_begin;
		
		$pay_time_end  = strim($_REQUEST['pay_time_end']);
		$param['pay_time_end'] = $pay_time_end;
				
		if(!empty($pay_time_begin) && !empty($pay_time_end))
		{
			$condition1.=" and t.pay_time >= '".to_timespan($pay_time_begin)."' and t.pay_time <='". (to_timespan($pay_time_end) + 3600 * 24 - 1)."' ";
			$condition2.=" and t.pay_time >= '".to_timespan($pay_time_begin)."' and t.pay_time <='". (to_timespan($pay_time_end) + 3600 * 24 - 1)."' ";
			$condition3.=" and t.pay_time >= '".to_timespan($pay_time_begin)."' and t.pay_time <='". (to_timespan($pay_time_end) + 3600 * 24 - 1)."' ";				
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
		
		/*
		//排序
		if(isset($_REQUEST['orderField']))
			$param['orderField'] = strim($_REQUEST['orderField']);
		else
			$param['orderField'] = "t.id";
		
		if(isset($_REQUEST['orderDirection']))
			$param['orderDirection'] = strim($_REQUEST['orderDirection'])=="asc"?"asc":"desc";
		else
			$param['orderDirection'] = "desc";
		*/	

		$sql = "		
		select id, '线路' as order_type,sn,tourline_name as name,user_id,supplier_id,total_price,pay_amount,pay_time,create_time, order_status,pay_status from ".DB_PREFIX."tourline_order as t where $condition1
		union ALL
		select id, '门票' as order_type,sn,ticket_name as name,user_id,supplier_id,total_price,pay_amount,pay_time,create_time,order_status,pay_status from ".DB_PREFIX."ticket_order as t where $condition2
		union ALL
		select id, '充值单' as order_type,order_sn,'充值单' as name,user_id,0 as supplier_id, money,pay_money,pay_time,create_time,0 as order_status, is_paid as pay_status from ".DB_PREFIX."user_incharge  as t where $condition3
		";
		
		
		$totalCount = $GLOBALS['db']->getOne("select count(*) from (".$sql.") as t ");
		if($totalCount){
			$sql = "select t.*,u.user_name,u.mobile,s.user_name as supplier_name  from (".$sql.") as t left outer join ".DB_PREFIX."user u on u.id = t.user_id left outer join ".DB_PREFIX."supplier s on s.id = t.supplier_id order by t.create_time desc limit ".$limit;
			//echo $sql;
			//die();
			$list = $GLOBALS['db']->getAll($sql);	

			require_once APP_ROOT_PATH."system/libs/tourline.php";
			
			foreach($list as $k=>$v)
			{
				tourline_order_format($list[$k]);
				
				
			}
		}

		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("order"));		
		
		$GLOBALS['tmpl']->assign("editurl1",admin_url("tourline_order#order"));
		$GLOBALS['tmpl']->assign("editurl2",admin_url("spot_order#order"));
		//$GLOBALS['tmpl']->assign("editurl",admin_url("order#order"));
		
		$GLOBALS['tmpl']->assign("exporturl",admin_url("order#export_csv"));
		$GLOBALS['tmpl']->display("core/order/index.html");
    }
    
    public function export_csv($page = 1)
    {
  	  $param = array();		
		//条件
		$condition1 = " 1 = 1 ";
		$condition2 = " 1 = 1 ";
		$condition3 = " 1 = 1 ";
		
		//订单号
		if(isset($_REQUEST['sn']))
			$sn = strim($_REQUEST['sn']);
		else
			$sn = "";
		$param['sn'] = $sn;
		if($sn!='')
		{
			$condition1.=" and t.sn = '".$sn."' ";
			$condition2.=" and t.sn = '".$sn."' ";
			$condition3.=" and t.order_sn = '".$sn."' ";
		}
		
		//线路ID
		if(isset($_REQUEST['supplier_id']))
			$supplier_id = strim($_REQUEST['supplier_id']);
		else
			$supplier_id = "";
		$param['supplier_id'] = $supplier_id;
		if($supplier_id!='' && intval($supplier_id) > 0)
		{
			$condition1.=" and t.supplier_id = ".intval($supplier_id)." ";
			$condition2.=" and t.supplier_id = ".intval($supplier_id)." ";
			$condition3.=" and 1 = 0 ";
		}		
					
		//线路/门票ID
		if(isset($_REQUEST['ticket_id']))
			$ticket_id = strim($_REQUEST['ticket_id']);
		else
			$ticket_id = "";
		$param['ticket_id'] = $ticket_id;
		if($ticket_id!='' && intval($ticket_id) > 0)
		{
			$condition1.=" and t.tourline_id = ".intval($ticket_id)." ";
			$condition2.=" and t.ticket_id = ".intval($ticket_id)." ";
			$condition3.=" and 1 = 0 ";
		}		
		
						
		//支付状态
		$pay_status = -1;
		if(isset($_REQUEST['pay_status']) && strim($_REQUEST['pay_status'])!="")
			$pay_status = intval($_REQUEST['pay_status']);
		
		$param['pay_status'] = $pay_status;
		if($pay_status !=-1)
		{
			$condition1 .=" and t.pay_status=$pay_status ";
			$condition2 .=" and t.pay_status=$pay_status ";
			$condition3 .=" and t.is_paid=$pay_status ";
		}
		
		//订单状态
		$order_status = -1;
		if(isset($_REQUEST['order_status']) && strim($_REQUEST['order_status'])!="")
			$order_status = intval($_REQUEST['order_status']);
		
		$param['order_status'] = $order_status;
		if($order_status !=-1)
		{
			$condition1 .=" and t.order_status=$order_status ";
			$condition2 .=" and t.order_status=$order_status ";
			$condition3 .=" and 1<>1 ";
		}
		
		//退款状态
		$refund_status = -1;
		if(isset($_REQUEST['refund_status']) && strim($_REQUEST['refund_status'])!="")
			$refund_status = intval($_REQUEST['refund_status']);
		
		$param['refund_status'] = $refund_status;
		if($refund_status !=-1)
		{
			$condition1 .=" and t.refund_status=$refund_status ";
			$condition2 .=" and t.refund_status=$refund_status ";
			$condition3 .=" and 1<>1 ";
		}
		
		$create_time_begin  = strim($_REQUEST['create_time_begin']);
		$param['create_time_begin'] = $create_time_begin;
		
		$create_time_end  = strim($_REQUEST['create_time_end']);
		$param['create_time_end'] = $create_time_end;
		
		if(!empty($create_time_begin) && !empty($create_time_end))
		{		
			$condition1.=" and t.create_time >= '".to_timespan($create_time_begin)."' and t.create_time <='". (to_timespan($create_time_end) + 3600 * 24 - 1)."' ";
			$condition2.=" and t.create_time >= '".to_timespan($create_time_begin)."' and t.create_time <='". (to_timespan($create_time_end) + 3600 * 24 - 1)."' ";
			$condition3.=" and t.create_time >= '".to_timespan($create_time_begin)."' and t.create_time <='". (to_timespan($create_time_end) + 3600 * 24 - 1)."' ";
				
		}		

		
		$pay_time_begin  = strim($_REQUEST['pay_time_begin']);
		$param['pay_time_begin'] = $pay_time_begin;
		
		$pay_time_end  = strim($_REQUEST['pay_time_end']);
		$param['pay_time_end'] = $pay_time_end;
				
		if(!empty($pay_time_begin) && !empty($pay_time_end))
		{
			$condition1.=" and t.pay_time >= '".to_timespan($pay_time_begin)."' and t.pay_time <='". (to_timespan($pay_time_end) + 3600 * 24 - 1)."' ";
			$condition2.=" and t.pay_time >= '".to_timespan($pay_time_begin)."' and t.pay_time <='". (to_timespan($pay_time_end) + 3600 * 24 - 1)."' ";
			$condition3.=" and t.pay_time >= '".to_timespan($pay_time_begin)."' and t.pay_time <='". (to_timespan($pay_time_end) + 3600 * 24 - 1)."' ";				
		}
		
		$param['pageSize'] = 100;
		//分页
		$limit = (($page-1)*$param['pageSize']).",".$param['pageSize'];

		
		$sql = "
		select id, '线路' as order_type,sn,tourline_name as name,user_id,supplier_id,total_price,pay_amount,pay_time,create_time, order_status,pay_status from ".DB_PREFIX."tourline_order as t where $condition1
				union ALL
				select id, '门票' as order_type,sn,ticket_name as name,user_id,supplier_id,total_price,pay_amount,pay_time,create_time,order_status,pay_status from ".DB_PREFIX."ticket_order as t where $condition2
				union ALL
				select id, '充值单' as order_type,order_sn,'充值单' as name,user_id,0 as supplier_id, money,pay_money,pay_time,create_time,0 as order_status, is_paid as pay_status from ".DB_PREFIX."user_incharge  as t where $condition3
				";
		
		
		$totalCount = $GLOBALS['db']->getOne("select count(*) from (".$sql.") as t ");
		if($totalCount){
			$sql = "select t.*,u.user_name,u.mobile,s.user_name as supplier_name  from (".$sql.") as t left outer join ".DB_PREFIX."user u on u.id = t.user_id left outer join ".DB_PREFIX."supplier s on s.id = t.supplier_id order by create_time limit ".$limit;
			//echo $sql;
			//die();
			$list = $GLOBALS['db']->getAll($sql);
		
			require_once APP_ROOT_PATH."system/libs/tourline.php";
				
			foreach($list as $k=>$v)
			{
				tourline_order_format($list[$k]);
		
			}
		}
		
		$order_value = array(
				'id'=>'""',
				'sn'=>'""',
				'order_type'=>'""',
				'name'=>'""',
				'supplier_name'=>'""',
				'user_name'=>'""',
				'create_time_format'=>'""',
				'pay_time_format'=>'""',
				'total_price_format'=>'""',
				'pay_amount_format'=>'""',
				'order_status_format'=>'""');
		if($page == 1)
		{
			//线路/门票,
			$content = iconv("utf-8","gbk","订单ID,订单编号,订单类型,线路/门票,商家名称,购买会员,下单时间,付款时间,订单金额,已付金额,订单状态");
			$content = $content . "\n";
		}
		
		if($list)
		{
			register_shutdown_function(array(&$this, 'export_csv'), $page+1);
			foreach($list as $k=>$v)
			{
				$order_value = array();
				$order_value['id'] = '"' . $v['id'] . '"';
				$order_value['sn'] = '"' . $v['sn'] . '"';				
				$order_value['order_type'] = '"' . iconv('utf-8','gbk',$v['order_type']) . '"';
				$order_value['name'] = '"' . iconv('utf-8','gbk',$v['name']) . '"';
				$order_value['supplier_name'] = '"' .iconv('utf-8','gbk',$v['supplier_name']) . '"';
				$order_value['user_name'] = '"' . iconv('utf-8','gbk',$v['user_name']) . '"';
				
				$order_value['create_time_format'] = '"' . $v['create_time_format'] . '"';
				$order_value['pay_time_format'] = '"' . $v['pay_time_format'] . '"';
				$order_value['total_price_format'] =  '"' . iconv('utf-8','gbk',$v['total_price_format']) . '"';
				$order_value['pay_amount_format'] =  '"' . iconv('utf-8','gbk',$v['pay_amount_format']) . '"';
				$order_value['order_status_format'] =  '"' . iconv('utf-8','gbk',$v['order_status_format']) . '"';
		
				$content .= implode(",", $order_value) . "\n";
		
			}
		}
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=order.csv");
		echo $content;
		
    
    }    
}
?>