<?php

class tourline_orderModule extends AuthModule
{
    function index() {
    	$param = array();		
		//条件
		$condition = " 1 = 1 ";
		
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
		

				
		//线路ID
		if(isset($_REQUEST['tourline_id']))
			$tourline_id = strim($_REQUEST['tourline_id']);
		else
			$tourline_id = "";
		$param['tourline_id'] = $tourline_id;
		if($tourline_id!='' && intval($tourline_id) > 0)
		{
			$condition.=" and t.tourline_id = ".intval($tourline_id)." ";
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
				
		//验证码
		if(isset($_REQUEST['verify_code']))
			$verify_code = strim($_REQUEST['verify_code']);
		else
			$verify_code = "";
		$param['verify_code'] = $verify_code;
		if($verify_code!='')
		{
			$condition.=" and t.verify_code = '".$verify_code."' ";
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
		
		
		//会员ID
		if(isset($_REQUEST['user_id']))
			$user_id = strim($_REQUEST['user_id']);
		else
			$user_id = "";
		$param['user_id'] = $user_id;
		if($user_id!='' && intval($user_id) > 0)
		{
			$condition.=" and t.user_id = ".intval($user_id)." ";
		}

		//下单时间
		$create_time_begin  = strim($_REQUEST['create_time_begin']);
		$param['create_time_begin'] = $create_time_begin;
		
		$create_time_end  = strim($_REQUEST['create_time_end']);
		$param['create_time_end'] = $create_time_end;
		
		if(!empty($create_time_begin) && !empty($create_time_end))
		{
			$condition.=" and t.create_time >= '".to_timespan($create_time_begin)."' and t.create_time <='". (to_timespan($create_time_end) + 3600 * 24 - 1)."' ";
		
		}
		
		//出发时间
		$end_time_begin  = strim($_REQUEST['end_time_begin']);
		$param['end_time_begin'] = $end_time_begin;
		
		$end_time_end  = strim($_REQUEST['end_time_end']);
		$param['end_time_end'] = $end_time_end;
		
		if(!empty($end_time_begin) && !empty($end_time_end))
		{
			$condition.=" and t.end_time >= '".$end_time_begin."' and t.end_time <='". $end_time_end."' ";
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
				
		$totalCount = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."tourline_order t where ".$condition);
		if($totalCount > 0){
			$sql = "select t.*,u.user_name,u.mobile,s.user_name as supplier_name  from ".DB_PREFIX."tourline_order t left outer join ".DB_PREFIX."user u on u.id = t.user_id left outer join ".DB_PREFIX."supplier s on s.id = t.supplier_id where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit;
			//echo $sql;
			//die();
			$list = $GLOBALS['db']->getAll($sql);	

			require_once APP_ROOT_PATH."system/libs/tourline.php";
			
			foreach($list as $k=>$v)
			{
				tourline_order_format($list[$k]);
				//print_r($v);
				
				//print_r($list[$k]);
				/*
				$list[$k]['create_time_format'] = to_date($v['create_time']);
				$list[$k]['total_price_format'] = format_price($v['total_price']);
				$list[$k]['pay_amount_format'] = format_price($v['pay_amount']);
				
				//支付状态
				if ($v['pay_status'] == 1){
					$list[$k]['pay_status_format'] = '已支付';
				}else{
					$list[$k]['pay_status_format'] = '未支付';
				}
				
				//订单状态(流程)1.新订单 2.已确认 3.已完成 4.作废\r\n新订单：未确认（包含已付款）的都表示为新订单\r\n已确认：表示为商家或管理员查看，确认手动修改\r\n新订单、已确认均可申请退款，否则不可',
				if ($v['order_status'] == 1){
					$list[$k]['order_status_format'] = '新订单';
				}else if ($v['order_status'] == 2){
					$list[$k]['order_status_format'] = '已确认';
				}else if ($v['order_status'] == 3){
					$list[$k]['order_status_format'] = '作废';
				}else {
					$list[$k]['order_status_format'] = '未知';
				}*/
				
				$list[$k]['tourline_url']=admin_url("tourline#edit",array("ajax"=>1,"id"=>$v['tourline_id']));
				$list[$k]['user_url']=admin_url("user#index",array("ajax"=>1,"user_name"=>$v['user_name']));
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
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_order"));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("tourline_order#order"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("tourline_order#del_order",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("exporturl",admin_url("tourline_order#export_csv"));
		
		$GLOBALS['tmpl']->display("core/tourline_order/index.html");
    }
    
    public function export_csv($page = 1)
    {
    	$param = array();		
		//条件
		$condition = " 1 = 1 ";
		
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
		
		//线路ID
		if(isset($_REQUEST['tourline_id']))
			$tourline_id = strim($_REQUEST['tourline_id']);
		else
			$tourline_id = "";
		$param['tourline_id'] = $tourline_id;
		if($tourline_id!='' && intval($tourline_id) > 0)
		{
			$condition.=" and t.tourline_id = ".intval($tourline_id)." ";
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
				
		//验证码
		if(isset($_REQUEST['verify_code']))
			$verify_code = strim($_REQUEST['verify_code']);
		else
			$verify_code = "";
		$param['verify_code'] = $verify_code;
		if($verify_code!='')
		{
			$condition.=" and t.verify_code = '".$verify_code."' ";
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
		
		//会员ID
		if(isset($_REQUEST['user_id']))
			$user_id = strim($_REQUEST['user_id']);
		else
			$user_id = "";
		$param['user_id'] = $user_id;
		if($user_id!='' && intval($user_id) > 0)
		{
			$condition.=" and t.user_id = ".intval($user_id)." ";
		}		
		
		//下单时间
		$create_time_begin  = strim($_REQUEST['create_time_begin']);
		$param['create_time_begin'] = $create_time_begin;
		
		$create_time_end  = strim($_REQUEST['create_time_end']);
		$param['create_time_end'] = $create_time_end;
		
		if(!empty($create_time_begin) && !empty($create_time_end))
		{
			$condition.=" and t.create_time >= '".to_timespan($create_time_begin)."' and t.create_time <='". (to_timespan($create_time_end) + 3600 * 24 - 1)."' ";
		
		}
		
		//出发时间
		$end_time_begin  = strim($_REQUEST['end_time_begin']);
		$param['end_time_begin'] = $end_time_begin;
		
		$end_time_end  = strim($_REQUEST['end_time_end']);
		$param['end_time_end'] = $end_time_end;
		
		if(!empty($end_time_begin) && !empty($end_time_end))
		{
			$condition.=" and t.end_time >= '".$end_time_begin."' and t.end_time <='". $end_time_end."' ";
		}
    
    	$param['pageSize'] = 100;
    	//分页
    	$limit = (($page-1)*$param['pageSize']).",".$param['pageSize'];
    
    	$totalCount = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."tourline_order t where ".$condition);
    	if($totalCount > 0){
    		$sql = "select t.*,u.user_name,u.mobile,u.email as user_email,u.paper_type as user_paper_type,u.paper_sn as user_paper_sn,s.user_name as supplier_name  from ".DB_PREFIX."tourline_order t left outer join ".DB_PREFIX."user u on u.id = t.user_id left outer join ".DB_PREFIX."supplier s on s.id = t.supplier_id where ".$condition." limit ".$limit;
    		//echo $sql;
    		//die();
    		$list = $GLOBALS['db']->getAll($sql);
    	
    		require_once APP_ROOT_PATH."system/libs/tourline.php";
    			
    		foreach($list as $k=>$v)
    		{
    			tourline_order_format($list[$k]);
    		}
    		
    		if($page == 1)
    		{
    			$content = iconv("utf-8","gbk","订单ID,订单编号,门票名称,商家名称,购买会员,预定人姓名,预定人手机,邮箱,会员证件号,出发日期,下单时间,订单金额,付款时间,已付金额,是否验证,支付状态,订单状态,退款状态,订单备注");
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
    				if($v['appoint_email'] !='')
    					$email_val="(预定人)".$v['appoint_email'];
    				elseif($v['user_email'] !='')
    					$email_val="(会员)".$v['user_email'];
    				else
    					$email_val='';
    				$order_value['email_val']='"' . iconv('utf-8','gbk',$email_val) . '"';
    				
    				if($v['user_paper_sn'] != '' )
    					$user_paper_sn="(".get_paper_type_name($v['user_paper_type']).")".$v['user_paper_sn'];
    				else
    					$user_paper_sn ='';
    				$order_value['user_paper_sn']='"' . iconv('utf-8','gbk',$user_paper_sn) . '"';
    				
    				$order_value['end_time'] = '"' . $v['end_time'] . '"';
    				$order_value['create_time_format'] = '"' . $v['create_time_format'] . '"';
    				$order_value['total_price_format'] = '"' . iconv('utf-8','gbk',$v['total_price_format']) . '"';
    				$order_value['pay_time_format'] = '"' . $v['pay_time_format'] . '"';
    				$order_value['pay_amount_format'] = '"' . iconv('utf-8','gbk',$v['pay_amount_format']) . '"';
    				
    				$order_value['is_verify'] = '"' . iconv('utf-8','gbk',$v['is_verify']) . '"';
    				$order_value['pay_status_format'] = '"' . iconv('utf-8','gbk',$v['pay_status_format']) . '"';
    				$order_value['order_status_format'] = '"' . iconv('utf-8','gbk',$v['order_status_format']) . '"';
    				$order_value['refund_status_format'] = '"' . iconv('utf-8','gbk',$v['refund_status_format']) . '"';
    				
    			
    				
    				$order_value['order_memo'] = '"' . iconv('utf-8','gbk',$v['order_memo']) . '"';
    				
    				$content .= implode(",", $order_value) . "\n";
    			}
    		}		    		
    	}
    
		
		header("Content-type:application/vnd.ms-excel");
    	header("Content-Disposition: attachment; filename=tourline_order.csv");
    	echo $content;
    
     }
            
    public function order()
    {
    	$ajax = intval($_REQUEST['ajax']);
    	$id = intval($_REQUEST['id']);
    	
    	$sql = "select t.*,u.user_name,u.mobile,s.user_name as supplier_name  from ".DB_PREFIX."tourline_order t left outer join ".DB_PREFIX."user u on u.id = t.user_id left outer join ".DB_PREFIX."supplier s on s.id = t.supplier_id where  t.id = ".$id;
    		
    	$order = $GLOBALS['db']->getRow($sql);//"select * from ".DB_PREFIX."tourline_order where id = ".$id);
    	if(empty($order))
    	{
    		showErr("订单不存在",$ajax)	;
    	}
    
    	require_once APP_ROOT_PATH."system/libs/tourline.php";
    	
    	tourline_order_format($order);
    	
    	//print_r($order);
    	$order['tourline_url']=admin_url("tourline#edit",array("id"=>$v['tourline_id']));
		$order['user_url']=admin_url("user#index",array("user_name"=>$v['user_name']));
    	$GLOBALS['tmpl']->assign("order",$order);
    
    
    	//订单日志;
    	$order_log = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tourline_order_log where order_id = ".intval($id)."  order by log_time");
    	foreach($order_log as $k=>$v)
    	{
    		$order_log[$k]['log_time_format'] = to_date($v['log_time']);
    		//0：会员; 1：商家; 2:管理员
    		if ($v['is_supplier'] == 1){
    			$order_log[$k]['is_supplier_format'] = '商家';
    		}else if ($v['is_supplier'] == 2){
    			$order_log[$k]['is_supplier_format'] = '管理员';
    		}else{
    			$order_log[$k]['is_supplier_format'] = '会员';
    		}
    	}    	
    	$GLOBALS['tmpl']->assign('order_log',$order_log);
    	
    	
    	//参团人员;
    	$namelist = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tourline_order_namelist where tourline_order_id = ".intval($id));
    	foreach($namelist as $k=>$v)
    	{
    		
    		//证件类型(1:身份证2:护照3:军官证4:港澳通行证5:台胎证6:其他)
    		if ($v['paper_type'] == 1){
    			$namelist[$k]['paper_type_format'] = '身份证';
    		}else if ($v['paper_type'] == 2){
    			$namelist[$k]['paper_type_format'] = '护照';
    		}else if ($v['paper_type'] == 3){
    			$namelist[$k]['paper_type_format'] = '军官证';
    		}else if ($v['paper_type'] == 4){
    			$namelist[$k]['paper_type_format'] = '港澳通行证';
    		}else if ($v['paper_type'] == 5){
    			$namelist[$k]['paper_type_format'] = '台胎证';
    		}else if ($v['paper_type'] == 6){
    			$namelist[$k]['paper_type_format'] = '其他';
    		}else{
    			$namelist[$k]['paper_type_format'] = '其他';
    		}
    		
    		//是否有效 1是 0否
    		if ($v['status'] == 1){
    			$namelist[$k]['status_format'] = '是';
    		}else{
    			$namelist[$k]['status_format'] = '否';
    		}    		
    	}
    	$GLOBALS['tmpl']->assign('namelist',$namelist);    	
    	
    	
    	//订单支付日志;
    	$payment_notice = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment_notice where order_type = 1 and order_sn = '".$order['sn']."'  order by create_time");
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
    	
    	
    	$GLOBALS['tmpl']->assign("orderlogurl",admin_url("tourline_order#order_log",array("ajax"=>1,id=>$id)));
    
    
    	$GLOBALS['tmpl']->assign("pay_order_url",admin_url("tourline_order#pay_order",array("ajax"=>1,id=>$id)));
    	$GLOBALS['tmpl']->assign("order_status_url",admin_url("tourline_order#do_order_status",array("ajax"=>1,id=>$id)));
    	
    	$GLOBALS['tmpl']->assign("refund_url",admin_url("tourline_order#refund",array("ajax"=>1,id=>$id)));
    	
    	$GLOBALS['tmpl']->assign("use_verify_code_url",admin_url("tourline_order#use_verify_code",array("ajax"=>1,id=>$id,'verify_code'=>$order['verify_code'])));
    	
    	$GLOBALS['tmpl']->assign("sendurl",admin_url("tourline_order#send_sms_mail",array("ajax"=>1,'id'=>$id)));
    	
    	$GLOBALS['tmpl']->assign("refuse_refund_url",admin_url("tourline_order#refuse_refund",array("ajax"=>1,id=>$id)));
    	
    	$GLOBALS['tmpl']->display("core/tourline_order/order.html");
    }    
    
    
    public function del_order()
    {
    	$ajax = intval($_REQUEST['ajax']);
    	if (isset ( $_REQUEST ['id'] ))
    	{
    		$id = strim($_REQUEST ['id']);
    		$id = format_ids_str($id);
    		if($id)
    		{
    			$sql = "select id,sn from ".DB_PREFIX."tourline_order where order_status = 4 and id in (".$id.")";
    			$list = $GLOBALS['db']->getAll($sql);
    			$id = "";
    			$order_sn = "";
    			foreach($list as $k=>$v)
    			{
    				if($order_sn=="")
    				{
    					$order_sn = $v['sn'];
    				}
    				else
    				{
    					$order_sn.=",".$v['sn'];
    				}
    				if ($id == "")
    					$id = $v['id'];
    				else	
    					$id .= ",".$v['id'];
    			}
    			
    			if ($id != ""){
	    			$sql = "delete from ".DB_PREFIX."tourline_order where order_status = 4 and id in (".$id.")";
	    			$GLOBALS['db']->query($sql);
	    			if($GLOBALS['db']->affected_rows()>0)
	    			{
	    				save_log($order_sn." ".lang("DEL"), 1);
	    				
	    				
	    				$sql = "delete from ".DB_PREFIX."tourline_order_namelist where tourline_order_id in (".$id.")";
	    				$GLOBALS['db']->query($sql);
	    				
	    				$sql = "delete from ".DB_PREFIX."tourline_order_log where order_id in (".$id.")";
	    				$GLOBALS['db']->query($sql);
	    				
	    				/*
	    				foreach($list as $k=>$v)
	    				{
	    					save_tourline_order_log($v['id'],lang("DEL"),1);	    					
	    				}*/
	    			}
	    			showSuccess(lang("FOREVER_DELETE_SUCCESS"),$ajax);
    			}else{
    				save_log(lang("DEL")."ID:".strim($_REQUEST ['id']), 0);
    				showErr(lang("INVALID_OPERATION"),$ajax);
    			}
    		}
    		else
    		{
    			showErr(lang("INVALID_OPERATION"),$ajax);
    		}
    	}
    	else
    	{
    		showErr(lang("INVALID_OPERATION"),$ajax);
    	}
    } 

    //管理员，后台直接点：收款
    public function pay_order()
    {
    	$id = intval($_REQUEST['id']);
    	$ajax = intval($_REQUEST['ajax']);
    	$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where pay_status = 0 and id = ".$id);
    	if(empty($order))
    	{
    		showErr("订单不存在或已收款",$ajax,admin_url("tourline_order#order",array(id=>$id)))	;
    	}
    	//1、生成一张收款单
    	//2、完成收款
    	//3、订单状态变成：已确认
    	//4、插入日志
    	//5、调用订单完成的动作
    	require_once APP_ROOT_PATH."system/libs/tourline.php";
    	require_once APP_ROOT_PATH."system/libs/transaction.php";
    	
    	
    	$user_data['id'] = $order['user_id'];
    	$money = floatval($order['total_price']) - floatval($order['pay_amount']);  
    	  	
    	$payment_notice_sn = Transaction::make_payment($user_data, 1, $order['sn'], $money, false);    	
    	$GLOBALS['db']->query("update ".DB_PREFIX."payment_notice set is_paid = 1,payment_name = '手工收款',pay_time = '".NOW_TIME."' where is_paid = 0 and notice_sn = '".$payment_notice_sn."' ","SILENT");
    	 
    	tourline_order_paid($order['sn'],$money);
    	
    	//`order_status` tinyint(1) NOT NULL default '1' COMMENT '订单状态(流程)1.新订单 2.已确认 3.已完成 4.作废\r\n新订单：未确认（包含已付款）的都表示为新订单\r\n已确认：表示为商家或管理员查看，确认手动修改\r\n新订单、已确认均可申请退款，否则不可',
    	$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set order_status = 2,confirm_time = '".NOW_TIME."' where order_status = 1 and id = ".$id." ","SILENT");
    	 
    	save_tourline_order_log($id,'管理员后台订单收款:'.format_price(format_price_to_display($money)),2);
    	
    	showSuccess('收款成功',$ajax,admin_url("tourline_order#order",array(id=>$id)));
    }
    
	
    
    //确认订单，完成订单，订单作废
    public function do_order_status()
    {
    	$id = intval($_REQUEST['id']);
    	$order_status = intval($_REQUEST['order_status']);
    	$ajax = intval($_REQUEST['ajax']);
    	require_once APP_ROOT_PATH."system/libs/tourline.php";
    	require_once APP_ROOT_PATH."system/libs/user.php";
    	if ($order_status == 2 || $order_status == 5){
    		//`order_status` tinyint(1) NOT NULL default '1' COMMENT '订单状态(流程)1.新订单 2.已确认 3.已完成 4.作废\r\n新订单：未确认（包含已付款）的都表示为新订单\r\n已确认：表示为商家或管理员查看，确认手动修改\r\n新订单、已确认均可申请退款，否则不可',
    		tourline_order_confirm($id,$order_status,2);
    		
    		showSuccess('确认订单成功',$ajax,admin_url("tourline_order#order",array(id=>$id)));
    	}else if ($order_status == 3){
    		//完成订单
    		if (tourline_order_complete($id,2)){
    			showSuccess('完成订单成功',$ajax,admin_url("tourline_order#order",array(id=>$id)));    			
    		}else{
    			showErr("不是已经确认的订单,不能直接完成",$ajax,admin_url("tourline_order#order",array(id=>$id)))	;
    		}
    	}else if ($order_status == 4){
    		//订单作废
    		if (tourline_order_invalid($id,2)){
    			showSuccess('完成订单作废',$ajax,admin_url("tourline_order#order",array(id=>$id)));
    		}else{
    			showErr("订单不存在或已被作废",$ajax,admin_url("tourline_order#order",array(id=>$id)));    			
    		}
    	}
    	
    }   

    //验证码标识使用
    public function use_verify_code()
    {
    	$id = intval($_REQUEST['id']);
    	$ajax = intval($_REQUEST['ajax']);
    	$verify_code = intval($_REQUEST['verify_code']);
    	
    	require_once APP_ROOT_PATH."system/libs/tourline.php";
    	
    	$res = tourline_order_use_verify_code($id,$verify_code,2);

    	if($res['return']){
    		tourline_order_complete($id, 2);
    		showSuccess('管理员后台把验证码标识使用',$ajax,admin_url("tourline_order#order",array(id=>$id)));
    	}else{
    		showSuccess('管理员后台把验证码标识使用失败:'.$res['message'],$ajax,admin_url("tourline_order#order",array(id=>$id)));
    	}
    }   
    
    //拒绝退款
    public function refuse_refund()
    {
    	$id = intval($_REQUEST['id']);
    	$ajax = intval($_REQUEST['ajax']);
    	//refuse_reason
    	$order = $GLOBALS['db']->getRow("select refuse_reason from ".DB_PREFIX."tourline_order where refund_status = 1 and id = ".$id);
    	
    	if(empty($order))
    	{
    		showErr("用户未申请退款或退款单已被处理",$ajax)	;
    	}
    	
    	$GLOBALS['tmpl']->assign("id",$id);
    	$GLOBALS['tmpl']->assign("refuse_reason",$order['refuse_reason']);
    	
    	$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_order#do_refund_status",array("ajax"=>1,"refund_status"=>3,id=>$id)));
    	$GLOBALS['tmpl']->assign("accounturl",admin_url("tourline_order#order",array("ajax"=>1,id=>$id)));
    	//$GLOBALS['tmpl']->display("core/user/op_account.html");
    	$GLOBALS['tmpl']->display("core/tourline_order/refuse_refund.html");
    	 
    }	
    
    
    public function refund()
    {
    
    	$id = intval($_REQUEST['id']);
    	$ajax = intval($_REQUEST['ajax']);
    	//refuse_reason
    	$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where refund_status = 1 and id = ".$id);
    
    	if(empty($order))
    	{
    		showErr("用户未申请退款或退款单已被处理",$ajax)	;
    	}
    
    	//开始计算可退的金额
    	$refund_child_count = intval($order['refund_child_count']);
    	$refund_adult_count = intval($order['refund_adult_count']);
    	$refund_visa_count = intval($order['refund_visa_count']);
    	 
    	$adult_count = intval($order['adult_count']);
    	$child_count = intval($order['child_count']);
    	$visa_count = intval($order['visa_count']);
    	 
    	$child_sale_price = intval($order['child_sale_price']);
    	$adult_sale_price = intval($order['adult_sale_price']);
    	$visa_price = intval($order['visa_price']);
    	 
    	$voucher_pay = intval($order['voucher_pay']);
    	 
    	$total_price = intval($order['total_price']);
    	$refund_amount = $refund_child_count * $child_sale_price + $refund_adult_count * $adult_sale_price + $refund_visa_count * $visa_price;
    	 
    	//退款金额，不能超过实际支付的金额(即：需要扣除代金券部分)
    	if ($refund_amount > $total_price - $voucher_pay)
    		$refund_amount = $total_price - $voucher_pay;
    	 
    	$refund_amount = format_price_to_display($refund_amount);
    	$GLOBALS['tmpl']->assign("refund_amount",$refund_amount);
    	
    	require_once APP_ROOT_PATH."system/libs/tourline.php";
    	tourline_order_format($order);
    	
    	
    	
    	$GLOBALS['tmpl']->assign("order",$order);
    
    
    	$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_order#do_refund_status",array("ajax"=>1,id=>$id,"refund_status"=>2)));
    	$GLOBALS['tmpl']->display("core/tourline_order/refund.html");
    
    }
    
    
    //确认退款,拒绝退款
    public function do_refund_status()
    {
    	$id = intval($_REQUEST['id']);
    	$refund_status = intval($_REQUEST['refund_status']);    	 
    	$ajax = intval($_REQUEST['ajax']);
    	$admin_msg = strim($_REQUEST['admin_msg']);
    	
    	$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where refund_status = 1 and id = ".$id);
    	if(empty($order))
    	{
    		showErr("用户未申请退款或退款单已被处理",$ajax,admin_url("tourline_order#order",array(id=>$id)))	;
    	}    	
    	
    	require_once APP_ROOT_PATH."system/libs/tourline.php";
    	
    	//0:未申请退款;1:申请退款中;2:确认退款;3:拒退
    	if ($refund_status == 2){
    		$refund_amount = format_price_to_db(floatval($_REQUEST['refund_amount']));
    		if($refund_amount<0)$refund_amount = 0;
    		$res = tourline_order_refund($id,2,$refund_amount);
    		 
    		if($res['return']){
    			$msg="后台管理同意退款";
    			if($admin_msg !='')
    			{
    				$msg .=",备注：".$admin_msg."";
    			}
    			save_tourline_order_log($id,$msg,2);
    			showSuccess('退款成功',$ajax,admin_url("tourline_order#order",array(id=>$id)));
    		}else{ 
    			showSuccess($res['message'],$ajax,admin_url("tourline_order#order",array(id=>$id)));
    		}    		
    	}else if ($refund_status == 3){
    		$refuse_reason = strim($_REQUEST['refuse_reason']);
    		
    		$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set refund_status = 3,refuse_reason='".$refuse_reason."' where refund_status = 1 and id = ".$id." ","SILENT");
    		if($GLOBALS['db']->affected_rows()>0){
    			save_tourline_order_log($id,'管理员后台拒绝退款',2);
    			 
    			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where id = '".$id."'");
    			//订单拒绝退款，发短信
    			send_order_reject_refund_sms($order_info);
    			//订单拒绝退款，发邮件
    			send_order_reject_refund_mail($order_info);
    			    			
    			showSuccess('管理员后台拒绝退款',$ajax,admin_url("tourline_order#order",array(id=>$id)));
    		}else{
    			save_tourline_order_log($id,'管理员后台拒绝退款失败',2);
    			 
    			showSuccess('管理员后台拒绝退款失败',$ajax,admin_url("tourline_order#order",array(id=>$id)));
    		}    		
    	}
    }  

    public function send_sms_mail()
    {
    	$id = intval($_REQUEST['id']);
    	$ajax = intval($_REQUEST['ajax']);
    
    	//1:短信;2:邮件
    	$send_type = intval($_REQUEST['send_type']);
    
    	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where id = '".$id."'");
    	//订单支付成功，发短信
    	if ($send_type == 1){
    		if (send_order_sms($order_info,1) == 1){
    			showSuccess("已将发送内容,添加到队列",$ajax);
    		}else{
    			showErr("添加发送队列失败",$ajax);
    		}
    	}
    
    	//订单支付成功，发邮件
    	if ($send_type == 2){
    		if (send_order_mail($order_info,1) == 1){
    			showSuccess("已将发送内容,添加到队列",$ajax);
    		}else{
    			showErr("添加发送队列失败",$ajax);
    		}
    	}
    }    
}
?>