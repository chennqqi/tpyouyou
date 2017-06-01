<?php

class spot_orderModule extends AuthModule
{
	
	function new_index() {
		$_REQUEST['is_new'] = 1;
		$this->index();
	}
		
	function index() {
		$param = array();
		//条件
		$condition = " t.supplier_id = ".$this->supplier_id;
		
		//`order_confirm_type` tinyint(1) NOT NULL COMMENT '订单确认方式 1.付款后确认 2.确认后付款,3.自动确认',
		$condition .=" and t.pay_status=1 and t.order_status <> 4 ";
		
		//order_status 订单状态(流程)1.新订单 2.已确认 3.已完成 4.作废
		
		$is_new = intval($_REQUEST['is_new']);
		
		if(isset($_REQUEST['is_new']) && $is_new > 0){
			$condition .=" and ((t.refund_status = 1 and t.supplier_confirm = 0) or t.re_appoint_status = 1 or (t.order_status = 1 and t.pay_status = 1)) ";
			$param['is_new'] = 1;
		}else{
			$param['is_new'] = 0;
		}
				
		//订单号
		if(isset($_REQUEST['sn']))
			$sn = strim($_REQUEST['sn']);
		else
			$sn = "";
		$param['sn'] = $sn;
		if($sn!='')
		{
			$condition.=" and t.sn = '".$sn."' ";
		}
		
		//门票ID
		if(isset($_REQUEST['ticket_id']))
			$ticket_id = strim($_REQUEST['ticket_id']);
		else
			$ticket_id = "";
		$param['ticket_id'] = $ticket_id;
		if($ticket_id!='' && intval($ticket_id) > 0)
		{
			$condition.=" and t.ticket_id = ".intval($ticket_id)." ";
		}		
		
	
		//预定人姓名
		if(isset($_REQUEST['appoint_name']))
			$appoint_name = strim($_REQUEST['appoint_name']);
		else
			$appoint_name = "";
		$param['appoint_name'] = $appoint_name;
		if($appoint_name!='')
		{
			$condition.=" and t.appoint_name = '".$appoint_name."' ";
		}
	
		//预定人手机
		if(isset($_REQUEST['appoint_mobile']))
			$appoint_mobile = strim($_REQUEST['appoint_mobile']);
		else
			$appoint_mobile = "";
		$param['appoint_mobile'] = $appoint_mobile;
		if($appoint_mobile!='')
		{
			$condition.=" and t.appoint_mobile = '".$appoint_mobile."' ";
		}
	
		//发货状态
		$delivery_status = -2;
		if(isset($_REQUEST['delivery_status']) && strim($_REQUEST['delivery_status'])!="")
			$delivery_status = intval($_REQUEST['delivery_status']);
		
		$param['delivery_status'] = $delivery_status;
		if($delivery_status !=-2)
		{
			$condition .=" and t.delivery_status=$delivery_status ";
		}
	
	
		//支付状态
		$pay_status = -1;
		if(isset($_REQUEST['pay_status']) && strim($_REQUEST['pay_status'])!="")
			$pay_status = intval($_REQUEST['pay_status']);
	
		$param['pay_status'] = $pay_status;
		if($pay_status !=-1)
		{
			$condition .=" and t.pay_status=$pay_status ";
		}
	
	
		//改签申请
		$re_appoint_status = -1;
		if(isset($_REQUEST['re_appoint_status']) && strim($_REQUEST['re_appoint_status'])!="")
			$re_appoint_status = intval($_REQUEST['re_appoint_status']);
		
		$param['re_appoint_status'] = $re_appoint_status;
		if($re_appoint_status !=-1)
		{
			$condition .=" and t.re_appoint_status=$re_appoint_status ";
		}
				
		//退款状态
		$refund_status = -1;
		if(isset($_REQUEST['refund_status']) && strim($_REQUEST['refund_status'])!="")
			$refund_status = intval($_REQUEST['refund_status']);
	
		$param['refund_status'] = $refund_status;
		if($refund_status !=-1)
		{
			$condition .=" and t.refund_status=$refund_status ";
		}
	
		//订单状态
		$order_status = 0;
		if(isset($_REQUEST['order_status']) && strim($_REQUEST['order_status'])!="")
			$order_status = intval($_REQUEST['order_status']);
	
		$param['order_status'] = $order_status;
		if($order_status !=0)
		{
			$condition .=" and t.order_status=$order_status ";
		}
	
		//是否验证
		$is_verify = intval($_REQUEST['is_verify']);
		if ($is_verify == 1){
			$condition .=" and t.verify_time=0";
		}else if ($is_verify == 2){
			$condition .=" and t.verify_time>0";
		}
		$param['is_verify'] = $is_verify;
	
		//下单时间
		$create_time_begin  = strim($_REQUEST['create_time_begin']);
		$param['create_time_begin'] = $create_time_begin;
		
		$create_time_end  = strim($_REQUEST['create_time_end']);
		$param['create_time_end'] = $create_time_end;
		
		if(!empty($create_time_begin) && !empty($create_time_end))
		{
			$condition.=" and t.create_time >= '".to_timespan($create_time_begin)."' and t.create_time <='". (to_timespan($create_time_end) + 3600 * 24 - 1)."' ";
		
		}
		
		//支付时间
		$pay_time_begin  = strim($_REQUEST['pay_time_begin']);
		$param['pay_time_begin'] = $pay_time_begin;
		
		$pay_time_end  = strim($_REQUEST['pay_time_end']);
		$param['pay_time_end'] = $pay_time_end;
		
		if(!empty($pay_time_begin) && !empty($pay_time_end))
		{
			$condition.=" and t.pay_time >= '".to_timespan($pay_time_begin)."' and t.pay_time <='". (to_timespan($pay_time_end) + 3600 * 24 - 1)."' ";
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
			$param['orderField'] = "t.id";
	
		if(isset($_REQUEST['orderDirection']))
			$param['orderDirection'] = strim($_REQUEST['orderDirection'])=="asc"?"asc":"desc";
		else
			$param['orderDirection'] = "desc";
	
		$totalCount = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."ticket_order t where ".$condition);
		if($totalCount){
			$sql = "select t.*,u.user_name,u.mobile,s.user_name as supplier_name  from ".DB_PREFIX."ticket_order t left outer join ".DB_PREFIX."user u on u.id = t.user_id left outer join ".DB_PREFIX."supplier s on s.id = t.supplier_id where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit;
			//echo $sql;
			//die();
			$list = $GLOBALS['db']->getAll($sql);
	
			require APP_ROOT_PATH . "system/libs/spot.php";
				
			foreach($list as $k=>$v)
			{
				ticket_order_format($list[$k]);
			}
		}
		/*
			线路名称:tourline_name
		订单号:sn
		购买会员:user_name
		下单时间:create_time
		订单状态:order_status
		支付状态:pay_status
		订单金额：total_price
		已付金额：pay_amount
		已退金额：refund_amount
		*/
	
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
	
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("spot_order"));
		$GLOBALS['tmpl']->assign("editurl",admin_url("spot_order#order"));
		//$GLOBALS['tmpl']->display("core/spot_order/index.html");
		$GLOBALS['tmpl']->assign("exporturl",admin_url("spot_order#export_csv"));
		$GLOBALS['tmpl']->display("core/spot_order/index.html");
	}
	
	function export_csv($page = 1) {
	$param = array();
		//条件
		$condition = " t.supplier_id = ".$this->supplier_id;
		
		//`order_confirm_type` tinyint(1) NOT NULL COMMENT '订单确认方式 1.付款后确认 2.确认后付款,3.自动确认',
		$condition .=" and t.pay_status=1 and t.order_status <> 4 ";
		
		//order_status 订单状态(流程)1.新订单 2.已确认 3.已完成 4.作废
		
		$is_new = intval($_REQUEST['is_new']);
		
		if(isset($_REQUEST['is_new']) && $is_new > 0){
			$condition .=" and ((t.refund_status = 1 and t.supplier_confirm = 0) or t.re_appoint_status = 1 or (t.order_status = 1 and t.pay_status = 1)) ";
			$param['is_new'] = 1;
		}else{
			$param['is_new'] = 0;
		}
				
	
		//订单号
		if(isset($_REQUEST['sn']))
			$sn = strim($_REQUEST['sn']);
		else
			$sn = "";
		$param['sn'] = $sn;
		if($sn!='')
		{
			$condition.=" and t.sn = '".$sn."' ";
		}
	
	
		//门票ID
		if(isset($_REQUEST['ticket_id']))
			$ticket_id = strim($_REQUEST['ticket_id']);
		else
			$ticket_id = "";
		$param['ticket_id'] = $ticket_id;
		if($ticket_id!='' && intval($ticket_id) > 0)
		{
			$condition.=" and t.ticket_id = ".intval($ticket_id)." ";
		}		
		
	
		//预定人姓名
		if(isset($_REQUEST['appoint_name']))
			$appoint_name = strim($_REQUEST['appoint_name']);
		else
			$appoint_name = "";
		$param['appoint_name'] = $appoint_name;
		if($appoint_name!='')
		{
			$condition.=" and t.appoint_name = '".$appoint_name."' ";
		}
	
		//预定人手机
		if(isset($_REQUEST['appoint_mobile']))
			$appoint_mobile = strim($_REQUEST['appoint_mobile']);
		else
			$appoint_mobile = "";
		$param['appoint_mobile'] = $appoint_mobile;
		if($appoint_mobile!='')
		{
			$condition.=" and t.appoint_mobile = '".$appoint_mobile."' ";
		}
	
		//发货状态
		$delivery_status = -2;
		if(isset($_REQUEST['delivery_status']) && strim($_REQUEST['delivery_status'])!="")
			$delivery_status = intval($_REQUEST['delivery_status']);
		
		$param['delivery_status'] = $delivery_status;
		if($delivery_status !=-2)
		{
			$condition .=" and t.delivery_status=$delivery_status ";
		}
	
	
		//支付状态
		$pay_status = -1;
		if(isset($_REQUEST['pay_status']) && strim($_REQUEST['pay_status'])!="")
			$pay_status = intval($_REQUEST['pay_status']);
	
		$param['pay_status'] = $pay_status;
		if($pay_status !=-1)
		{
			$condition .=" and t.pay_status=$pay_status ";
		}
	
	
		//改签申请
		$re_appoint_status = -1;
		if(isset($_REQUEST['re_appoint_status']) && strim($_REQUEST['re_appoint_status'])!="")
			$re_appoint_status = intval($_REQUEST['re_appoint_status']);
		
		$param['re_appoint_status'] = $re_appoint_status;
		if($re_appoint_status !=-1)
		{
			$condition .=" and t.re_appoint_status=$re_appoint_status ";
		}
				
		//退款状态
		$refund_status = -1;
		if(isset($_REQUEST['refund_status']) && strim($_REQUEST['refund_status'])!="")
			$refund_status = intval($_REQUEST['refund_status']);
	
		$param['refund_status'] = $refund_status;
		if($refund_status !=-1)
		{
			$condition .=" and t.refund_status=$refund_status ";
		}
	
		//订单状态
		$order_status = 0;
		if(isset($_REQUEST['order_status']) && strim($_REQUEST['order_status'])!="")
			$order_status = intval($_REQUEST['order_status']);
	
		$param['order_status'] = $order_status;
		if($order_status !=0)
		{
			$condition .=" and t.order_status=$order_status ";
		}
	
		//是否验证
		$is_verify = intval($_REQUEST['is_verify']);
		if ($is_verify == 1){
			$condition .=" and t.verify_time=0";
		}else if ($is_verify == 2){
			$condition .=" and t.verify_time>0";
		}
		$param['is_verify'] = $is_verify;
	
		//下单时间
		$create_time_begin  = strim($_REQUEST['create_time_begin']);
		$param['create_time_begin'] = $create_time_begin;
		
		$create_time_end  = strim($_REQUEST['create_time_end']);
		$param['create_time_end'] = $create_time_end;
		
		if(!empty($create_time_begin) && !empty($create_time_end))
		{
			$condition.=" and t.create_time >= '".to_timespan($create_time_begin)."' and t.create_time <='". (to_timespan($create_time_end) + 3600 * 24 - 1)."' ";
		
		}
		
		//支付时间
		$pay_time_begin  = strim($_REQUEST['pay_time_begin']);
		$param['pay_time_begin'] = $pay_time_begin;
		
		$pay_time_end  = strim($_REQUEST['pay_time_end']);
		$param['pay_time_end'] = $pay_time_end;
		
		if(!empty($pay_time_begin) && !empty($pay_time_end))
		{
			$condition.=" and t.pay_time >= '".to_timespan($pay_time_begin)."' and t.pay_time <='". (to_timespan($pay_time_end) + 3600 * 24 - 1)."' ";
		}
			
		$param['pageSize'] = 100;
		//分页
		$limit = (($page-1)*$param['pageSize']).",".$param['pageSize'];			
	
		$totalCount = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."ticket_order t where ".$condition);
		if($totalCount > 0){
			$sql = "select t.*,u.user_name,u.mobile,s.user_name as supplier_name  from ".DB_PREFIX."ticket_order t left outer join ".DB_PREFIX."user u on u.id = t.user_id left outer join ".DB_PREFIX."supplier s on s.id = t.supplier_id where ".$condition."  limit ".$limit;
			//echo $sql;
			//die();
			$list = $GLOBALS['db']->getAll($sql);
	
			require_once  APP_ROOT_PATH . "system/libs/spot.php";
	
			foreach($list as $k=>$v)
			{
				ticket_order_format($list[$k]);
			}
				
			if($page == 1)
			{
				$content = iconv("utf-8","gbk","订单ID,订单编号,线路名称,商家名称,购买会员,预定人姓名,预定人手机,预定时间,下单时间,订单金额,付款时间,已付金额,支付状态,订单状态,退款状态,发货状态,收件人,收件电话,邮编,配送地址,订单备注");
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
					$order_value['tourline_name'] = '"' . iconv('utf-8','gbk',$v['tourline_name']) . '"';
					$order_value['supplier_name'] = '"' . iconv('utf-8','gbk',$v['supplier_name']) . '"';
					$order_value['user_name'] = '"' .iconv('utf-8','gbk',$v['user_name']) . '"';
					$order_value['appoint_name'] = '"' . iconv('utf-8','gbk',$v['appoint_name']) . '"';
					$order_value['appoint_mobile'] = '"' . $v['appoint_mobile'] . '"';
					$order_value['appoint_time_format'] = '"' . $v['appoint_time_format'] . '"';
						
					$order_value['create_time_format'] = '"' . $v['create_time_format'] . '"';
					$order_value['total_price_format'] = '"' . iconv('utf-8','gbk',$v['total_price_format']) . '"';
					$order_value['pay_time_format'] = '"' . $v['pay_time_format'] . '"';
					$order_value['pay_amount_format'] = '"' . iconv('utf-8','gbk',$v['pay_amount_format']) . '"';
						
					$order_value['pay_status_format'] = '"' . iconv('utf-8','gbk',$v['pay_status_format']) . '"';
					$order_value['order_status_format'] = '"' . iconv('utf-8','gbk',$v['order_status_format']) . '"';
					$order_value['refund_status_format'] = '"' . iconv('utf-8','gbk',$v['refund_status_format']) . '"';
					$order_value['delivery_status_format'] = '"' . iconv('utf-8','gbk',$v['delivery_status_format']) . '"';
						
	
					$order_value['delivery_name'] = '"' . iconv('utf-8','gbk',$v['delivery_name']) . '"';
					$order_value['delivery_mobile'] = '"' . iconv('utf-8','gbk',$v['delivery_mobile']) . '"';
					$order_value['zip'] = '"' . iconv('utf-8','gbk',$v['zip']) . '"';
						
					$addr = $v['province_name']." ".$v['city_name']." ".$v['address'];
						
					$order_value['addr'] = '"' . iconv('utf-8','gbk',$addr) . '"';
	
					$order_value['order_memo'] = '"' . iconv('utf-8','gbk',$v['order_memo']) . '"';
					$content .= implode(",", $order_value) . "\n";
				}
			}
		}
	
	
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=spot_order.csv");
		echo $content;
	
	}
		
	public function order()
	{
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);
		 
		$sql = "select t.*,u.user_name,u.mobile,s.user_name as supplier_name  from ".DB_PREFIX."ticket_order t left outer join ".DB_PREFIX."user u on u.id = t.user_id left outer join ".DB_PREFIX."supplier s on s.id = t.supplier_id where  t.id = ".$id." and t.supplier_id = ".$this->supplier_id;
	
		$order = $GLOBALS['db']->getRow($sql);//"select * from ".DB_PREFIX."spot_order where id = ".$id);
		if(empty($order))
		{
			showErr("订单不存在".$sql,$ajax)	;
		}
	
		require APP_ROOT_PATH . "system/libs/spot.php";
		 
		ticket_order_format($order);
		 
		//print_r($order);
		 
		$GLOBALS['tmpl']->assign("order",$order);
		
		//门票列表;
		$ticketlist = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."ticket_order_item where order_id = ".intval($id));
		foreach($ticketlist as $k=>$v)
		{		
			ticket_order_item_format($ticketlist[$k]);
		}
		$GLOBALS['tmpl']->assign('ticketlist',$ticketlist);
				
		//订单日志;
		$order_log = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."ticket_order_log where order_id = ".intval($id)."  order by log_time");
		foreach($order_log as $k=>$v)
		{
			$order_log[$k]['log_time_format'] = to_date($v['log_time']);
			//是否由商家操作 1是 0否(管理员)
			if ($v['is_supplier'] == 1){
				$order_log[$k]['is_supplier_format'] = '是';
			}else{
				$order_log[$k]['is_supplier_format'] = '否';
			}
		}
		$GLOBALS['tmpl']->assign('order_log',$order_log);
		 
		 
		//订单支付日志;
		$payment_notice = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment_notice where order_type = 2 and order_sn = '".$order['sn']."'  order by create_time");
		foreach($payment_notice as $k=>$v)
		{
			$payment_notice[$k]['create_time_format'] = to_date($v['create_time']);
			$payment_notice[$k]['pay_time_format'] = to_date($v['pay_time']);
			$payment_notice[$k]['money_format'] = format_price(format_price_to_display($v['money']));
	
	
			//是否支付成功 1是 0否
			if ($v['is_paid'] == 1){
				$payment_notice[$k]['is_paid_format'] = '是';
			}else{
				$payment_notice[$k]['is_paid_format'] = '否';
			}
		}
		$GLOBALS['tmpl']->assign('payment_notice',$payment_notice);
		 
		 
		$GLOBALS['tmpl']->assign("pay_order_url",admin_url("spot_order#pay_order",array("ajax"=>1,id=>$id)));
		$GLOBALS['tmpl']->assign("order_status_url",admin_url("spot_order#do_order_status",array("ajax"=>1,id=>$id)));
		$GLOBALS['tmpl']->assign("refund_status_url",admin_url("spot_order#do_refund_status",array("ajax"=>1,id=>$id)));
		$GLOBALS['tmpl']->assign("use_verify_code_url",admin_url("spot_order#use_verify_code",array("ajax"=>1,id=>$id,'verify_code'=>$order['verify_code'])));
		 
		 
		$GLOBALS['tmpl']->assign("refuse_refund_url",admin_url("spot_order#refuse_refund",array("ajax"=>1,id=>$id)));
		 
		
		$GLOBALS['tmpl']->assign("order_item_url",admin_url("spot_order#order_item",array("ajax"=>1,'order_id'=>$id)));
		
		//发货
		$GLOBALS['tmpl']->assign("order_delivery_url",admin_url("spot_order#delivery",array("ajax"=>1,'id'=>$id)));
		//标识用户收货
		$GLOBALS['tmpl']->assign("order_delivery2_url",admin_url("spot_order#do_delivery",array("ajax"=>1,id=>$id)));
		
		$GLOBALS['tmpl']->display("core/spot_order/order.html");
	}
	
	
	public function order_item()
	{
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);
			
		$sql = "select t.* from ".DB_PREFIX."hotel_room_order_item t where t.id = ".$id;
	
		$item = $GLOBALS['db']->getRow($sql);//"select * from ".DB_PREFIX."spot_order where id = ".$id);
		if(empty($item))
		{
			showErr("订单不存在",$ajax)	;
		}
	
		require APP_ROOT_PATH . "system/libs/spot.php";
			
		ticket_order_item_format($item);

		$GLOBALS['tmpl']->assign('item',$item);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("spot_order#do_re_appoint_status",array("ajax"=>1,"order_id"=>$item['order_id'],id=>$id)));
		$GLOBALS['tmpl']->assign("accounturl",admin_url("spot_order#order",array("ajax"=>1,id=>$id)));
				
		$GLOBALS['tmpl']->display("core/spot_order/order_item.html");
	}
		
	public function delivery()
	{
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);
			
		$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ticket_order where id = ".$id." and supplier_id = ".$this->supplier_id);
		if(empty($order))
		{
			showErr("订单不存在或已经被处理",$ajax)	;
		}
	
		require APP_ROOT_PATH . "system/libs/spot.php";
			
		ticket_order_format($order);
	
		$NOW_DATE = to_date(NOW_TIME,"Y-m-d");
		$GLOBALS['tmpl']->assign("NOW_DATE",$NOW_DATE);
		
		$GLOBALS['tmpl']->assign('order',$order);
	
		$GLOBALS['tmpl']->assign("formaction",admin_url("spot_order#do_delivery",array("ajax"=>1,id=>$id)));
		$GLOBALS['tmpl']->assign("accounturl",admin_url("spot_order#order",array("ajax"=>1,id=>$id)));
	
		$GLOBALS['tmpl']->display("core/spot_order/delivery.html");
	}	
	
	public function do_delivery()
	{
		$ajax = intval($_REQUEST['ajax']);
		
		$id = intval($_REQUEST['id']);
		
		
		$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ticket_order where id = ".$id." and supplier_id = ".$this->supplier_id);
		if(empty($order))
		{
			showErr("订单不存在",$ajax,admin_url("spot_order#order",array(id=>$id)))	;
		}else{
			require_once APP_ROOT_PATH."system/libs/spot.php";
			
			if ($order['delivery_status'] == 0){
				if(trim($_REQUEST['delivery_sn'])==''){
					showErr("请输入发货单号",$ajax);
				}
				
				if(trim($_REQUEST['delivery_time'])==''){
					showErr("请输入发货时间",$ajax);
				}
				
				//delivery_status: 发货状态：0未发货 1已发货 2已收货 -1无需发货
				$data['delivery_status'] = 1;
				$data['delivery_sn'] = trim($_REQUEST['delivery_sn']);
				$data['delivery_time']= to_timespan($_REQUEST['delivery_time']);

				
				$GLOBALS['db']->autoExecute(DB_PREFIX."ticket_order",$data,"UPDATE","id=".$id,"SILENT");
				
				save_ticket_order_log($id,'订单发货:'.$data['delivery_sn'],1);
				
				$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ticket_order where id = '".$id."'");
				//订单发货 短信通知
				send_order_delivery_sms($order_info);
				//订单发货 发邮件 通知
				send_order_delivery_mail($order_info);
								
				showSuccess('发货成功',$ajax,admin_url("spot_order#order",array(id=>$id)));

			}else if ($order['delivery_status'] == 1){
				$data['delivery_status'] = 2;
								
				$GLOBALS['db']->autoExecute(DB_PREFIX."ticket_order",$data,"UPDATE","id=".$id,"SILENT");
				
				save_ticket_order_log($id,'订单确认收货',1);
				showSuccess('订单确认收货成功',$ajax,admin_url("spot_order#order",array(id=>$id)));
			}	
		}
	}
	
	//re_appoint_status: 0:未申请改期;1:申请改期中;2:确认改期;3:拒改期
	public function do_re_appoint_status()
	{
		$id = intval($_REQUEST['id']);
		$re_appoint_status = intval($_REQUEST['re_appoint_status']);
		$re_appoint_refuse_reason = strim($_REQUEST['re_appoint_refuse_reason']);
		$order_id = intval($_REQUEST['order_id']);
		$ajax = intval($_REQUEST['ajax']);
		 
		$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ticket_order where id = ".$order_id." and supplier_id = ".$this->supplier_id);
		if(empty($order))
		{
			showErr("订单不存在或已经被处理",$ajax)	;
		}
				
		$order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ticket_order_item where re_appoint_status = 1 and id = ".$id);
		if(empty($order_item))
		{
			showErr("用户未申请改约时间或已被处理",$ajax,admin_url("spot_order#order",array(id=>$order_id)))	;
		}
		 
		require_once APP_ROOT_PATH."system/libs/spot.php";
		 
		//re_appoint_status: 0:未申请改期;1:申请改期中;2:确认改期;3:拒改期
		if ($re_appoint_status == 2){
			
			$begin_time = to_timespan(to_date($order_item['re_action_time'],"Y-m-d"),"Y-m-d");
			$end_time = to_timespan(to_date($order_item['re_action_time'],"Y-m-d"),"Y-m-d") + 24*3600 -1;
	
			$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order_item set begin_time = $begin_time,end_time = $end_time, appoint_time = re_action_time, re_appoint_status = 2,re_appoint_refuse_reason='".$re_appoint_refuse_reason."' where re_appoint_status = 1 and id = ".$id." ","SILENT");
		
			 
			if($GLOBALS['db']->affected_rows()>0){
				
				$sql = "select count(*) from ".DB_PREFIX."ticket_order_item where re_appoint_status = 1 and order_id = ".$order_id;
				$num = intval($GLOBALS['db']->getOne($sql));
				//echo "num:".$num.";sql:".$sql; 
				//die();
				if ($num == 0){
					//`re_appoint_status` tinyint(1) NOT NULL COMMENT '是否有门票改签申请，0无 1有',
					$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order set re_appoint_status = 0 where id = ".$order_id." ","SILENT");					
				}
				
				
				save_ticket_order_log($order_id,$id.':改期:'.to_date($order_item['re_action_time'],"Y-m-d"),1);
	
				//订单改签通过  短信通知
				send_order_re_appoint_sms($order,to_date($order_item['re_action_time'],"Y-m-d"));
				
				//订单改签通过 发邮件 通知
				send_order_re_appoint_mail($order,to_date($order_item['re_action_time'],"Y-m-d"));
								
				showSuccess('改期成功',$ajax,admin_url("spot_order#order",array('id'=>$order_id)));
			}else{
				showSuccess('改期失败',$ajax,admin_url("spot_order#order",array('id'=>$order_id)));
			}
		}else if ($re_appoint_status == 3){
	
			$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order_item set re_appoint_status = 3,re_appoint_refuse_reason='".$re_appoint_refuse_reason."' where re_appoint_status = 1 and id = ".$id." ","SILENT");
			if($GLOBALS['db']->affected_rows()>0){
				save_ticket_order_log($order_id,'商家拒绝改期。',1);
				
				$i = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ticket_order_item where re_appoint_status = 1 and order_id = ".$order_id." ","SILENT"));
				if ($i == 0){
					//`re_appoint_status` tinyint(1) NOT NULL COMMENT '是否有门票改签申请，0无 1有',
					$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order set re_appoint_status = 0 where id = ".$order_id." ","SILENT");
				}
				
				//订单拒绝改签  短信通知
				send_order_reject_re_appoint_sms($order,$re_appoint_refuse_reason);
				//订单拒绝改签 发邮件 通知
				send_order_reject_re_appoint_mail($order,$re_appoint_refuse_reason);
								
				showSuccess('拒绝成功',$ajax,admin_url("spot_order#order",array('id'=>$order_id)));
			}else{
				save_tourline_order_log($order_id,'商家拒绝改期失败',1);
	
				showSuccess('拒绝失败',$ajax,admin_url("spot_order#order",array('id'=>$order_id)));
			}
		}
	}
	
	//确认订单，完成订单，订单作废
	public function do_order_status()
	{
		$id = intval($_REQUEST['id']);			
		$order_status = intval($_REQUEST['order_status']);
		$ajax = intval($_REQUEST['ajax']);
		
		$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ticket_order where id = ".$id." and supplier_id = ".$this->supplier_id);
		if(empty($order))
		{
			showErr("订单不存在或已经被处理",$ajax)	;
		}
				
		require_once APP_ROOT_PATH."system/libs/spot.php";
		require_once APP_ROOT_PATH."system/libs/user.php";
		if ($order_status == 2 || $order_status == 5){
			//`order_status` tinyint(1) NOT NULL default '1' COMMENT '订单状态(流程)1.新订单 2.已确认 3.已完成 4.作废\r\n新订单：未确认（包含已付款）的都表示为新订单\r\n已确认：表示为商家或管理员查看，确认手动修改\r\n新订单、已确认均可申请退款，否则不可',
			
			ticket_order_confirm($id,$order_status,1);
				
			showSuccess('确认订单成功',$ajax,admin_url("spot_order#order",array(id=>$id)));
		}
		 
	}
	
	
	//退款
	public function refuse_refund()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		//refuse_reason
		$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ticket_order where refund_status = 1 and id = ".$id." and supplier_id = ".$this->supplier_id);
		if(empty($order))
		{
			showErr("用户未申请退款或退款单已被处理",$ajax)	;
		}
		 
		
		require_once APP_ROOT_PATH."system/libs/spot.php";
		//门票列表;
		$ticketlist = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."ticket_order_item where refund_status = 1 and order_id = ".intval($id));
		foreach($ticketlist as $k=>$v)
		{
			ticket_order_item_format($ticketlist[$k]);
		}
		$GLOBALS['tmpl']->assign('ticketlist',$ticketlist);

	//	print_r($order);
		
		ticket_order_format($order);
		$GLOBALS['tmpl']->assign("order",$order);
	
		
		$GLOBALS['tmpl']->assign("id",$id);
		$GLOBALS['tmpl']->assign("refuse_reason",$order['refuse_reason']);
		 
		$GLOBALS['tmpl']->assign("formaction",admin_url("spot_order#do_refund_status",array("ajax"=>1,id=>$id)));
		$GLOBALS['tmpl']->assign("accounturl",admin_url("spot_order#order",array("ajax"=>1,id=>$id)));
		//$GLOBALS['tmpl']->display("core/user/op_account.html");
		$GLOBALS['tmpl']->display("core/spot_order/refuse_refund.html");
	
	}
	
	//确认退款,拒绝退款
	public function do_refund_status()
	{
		$id = intval($_REQUEST['id']);
		$supplier_confirm = intval($_REQUEST['supplier_confirm']);
		$ajax = intval($_REQUEST['ajax']);
		 
		$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ticket_order where refund_status = 1 and id = ".$id." and supplier_id = ".$this->supplier_id);
		if(empty($order))
		{
			showErr("用户未申请退款或退款单已被处理:".$id,$ajax,admin_url("spot_order#order",array(id=>$id)))	;
		}
		 
		require_once APP_ROOT_PATH."system/libs/spot.php";
		 
		$refuse_reason = strim($_REQUEST['refuse_reason']);
		
		$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order set supplier_confirm = $supplier_confirm, supplier_confirm_time = ".NOW_TIME.",refuse_reason='".$refuse_reason."' where refund_status = 1 and id = ".$id." ","SILENT");
		if($GLOBALS['db']->affected_rows()>0){
			save_ticket_order_log($id,'用户申请退款,商家确认',1);
			
			showSuccess('商家确认成功',$ajax,admin_url("spot_order#order",array('id'=>$id)));
		}else{				
			showSuccess('商家确认失败',$ajax,admin_url("spot_order#order",array('id'=>$id)));
		}
		
	}
}
?>