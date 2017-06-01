<?php

class tourline_orderModule extends AuthModule
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
		$condition .=" and (t.pay_status=1 or t.order_confirm_type = 2) and t.order_status <> 4 ";
		
		//order_status 订单状态(流程)1.新订单 2.已确认 3.已完成 4.作废
		
		$is_new = intval($_REQUEST['is_new']);
		
		if(isset($_REQUEST['is_new']) && $is_new > 0){			
			$condition .=" and (t.refund_status = 1 or  (t.order_confirm_type = 2 and t.order_status = 1) or (t.order_confirm_type = 1 and t.order_status = 1 and t.pay_status = 1) ) ";
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
		if($totalCount){
			$sql = "select t.*,u.user_name,u.mobile,s.user_name as supplier_name  from ".DB_PREFIX."tourline_order t left outer join ".DB_PREFIX."user u on u.id = t.user_id left outer join ".DB_PREFIX."supplier s on s.id = t.supplier_id where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit;
			//echo $sql;
			//die();
			$list = $GLOBALS['db']->getAll($sql);	

			require_once APP_ROOT_PATH."system/libs/tourline.php";

			// 去除邮轮订单
			foreach ($list as $key => $value) {
				$isC = $GLOBALS['db']->getOne("select is_cruise from ".DB_PREFIX."tourline where id =".$value['tourline_id']);
				if ($isC != 1) {
				  $lists[] = $value;
				}
			}
			
			foreach($lists as $k=>$v)
			{
				tourline_order_format($lists[$k]);
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
		
		$GLOBALS['tmpl']->assign('list',$lists);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_order"));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("tourline_order#order"));
		$GLOBALS['tmpl']->assign("exporturl",admin_url("tourline_order#export_csv"));
		$GLOBALS['tmpl']->display("core/tourline_order/index.html");
    }
    
    public function export_csv($page = 1)
    {
    	$param = array();
    	$condition = " t.supplier_id = ".$this->supplier_id;
		
		//`order_confirm_type` tinyint(1) NOT NULL COMMENT '订单确认方式 1.付款后确认 2.确认后付款,3.自动确认',
		$condition .=" and (t.pay_status=1 or t.order_confirm_type = 2) and t.order_status <> 4 ";
		
		//order_status 订单状态(流程)1.新订单 2.已确认 3.已完成 4.作废
		
		$is_new = intval($_REQUEST['is_new']);
		
		if(isset($_REQUEST['is_new']) && $is_new > 0){			
			$condition .=" and (t.refund_status = 1 or  (t.order_confirm_type = 2 and t.order_status = 1) or (t.order_confirm_type = 1 and t.order_status = 1 and t.pay_status = 1) ) ";
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
    		$sql = "select t.*,u.user_name,u.mobile,s.user_name as supplier_name  from ".DB_PREFIX."tourline_order t left outer join ".DB_PREFIX."user u on u.id = t.user_id left outer join ".DB_PREFIX."supplier s on s.id = t.supplier_id where ".$condition." limit ".$limit;
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
    			$content = iconv("utf-8","gbk","订单ID,订单编号,门票名称,商家名称,购买会员,预定人姓名,预定人手机,出发日期,下单时间,订单金额,付款时间,已付金额,是否验证,支付状态,订单状态,退款状态,订单备注");
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
    	
    	$verify_time = intval($order['verify_time']);
    	if ($verify_time == 0)
    		$order['verify_code'] = '未验证';
    	
    	//print_r($order);
    	
    	$GLOBALS['tmpl']->assign("order",$order);
    
    	
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
    	
    	
    	
    	
    	$GLOBALS['tmpl']->assign("orderlogurl",admin_url("tourline_order#order_log",array("ajax"=>1,id=>$id)));
    
    
    	$GLOBALS['tmpl']->assign("pay_order_url",admin_url("tourline_order#pay_order",array("ajax"=>1,id=>$id)));
    	$GLOBALS['tmpl']->assign("order_status_url",admin_url("tourline_order#do_order_status",array("ajax"=>1,id=>$id)));
    	$GLOBALS['tmpl']->assign("refund_status_url",admin_url("tourline_order#do_refund_status",array("ajax"=>1,id=>$id)));
    	$GLOBALS['tmpl']->assign("use_verify_code_url",admin_url("tourline_order#use_verify_code",array("ajax"=>1,id=>$id,'verify_code'=>$order['verify_code'])));
    	
    	
    	$GLOBALS['tmpl']->assign("refuse_refund_url",admin_url("tourline_order#refuse_refund",array("ajax"=>1,id=>$id)));
    	
    	$GLOBALS['tmpl']->display("core/tourline_order/order.html");
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
    		tourline_order_confirm($id,$order_status,1);
    
    		showSuccess('确认订单成功',$ajax,admin_url("tourline_order#order",array(id=>$id)));
    	}
    	 
    } 
       
	//退款
	public function refuse_refund()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		//refuse_reason
		$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where refund_status = 1 and id = ".$id." and supplier_id = ".$this->supplier_id);
		if(empty($order))
		{
			showErr("用户未申请退款或退款单已被处理",$ajax)	;
		}
		 
		
		require_once APP_ROOT_PATH."system/libs/tourline.php";

	//	print_r($order);
		
		tourline_order_format($order);
		$GLOBALS['tmpl']->assign("order",$order);
	
		
		$GLOBALS['tmpl']->assign("id",$id);
		$GLOBALS['tmpl']->assign("refuse_reason",$order['refuse_reason']);
		 
		$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_order#do_refund_status",array("ajax"=>1,id=>$id)));
		$GLOBALS['tmpl']->assign("accounturl",admin_url("tourline_order#order",array("ajax"=>1,id=>$id)));
		//$GLOBALS['tmpl']->display("core/user/op_account.html");
		$GLOBALS['tmpl']->display("core/tourline_order/refuse_refund.html");
	
	}
	
	//确认退款,拒绝退款
	public function do_refund_status()
	{
		$id = intval($_REQUEST['id']);
		$supplier_confirm = intval($_REQUEST['supplier_confirm']);
		$ajax = intval($_REQUEST['ajax']);
		 
		$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where refund_status = 1 and id = ".$id." and supplier_id = ".$this->supplier_id);
		if(empty($order))
		{
			showErr("用户未申请退款或退款单已被处理:".$id,$ajax,admin_url("tourline_order#order",array(id=>$id)))	;
		}
		 
		require_once APP_ROOT_PATH."system/libs/tourline.php";
		 
		$refuse_reason = strim($_REQUEST['refuse_reason']);
		
		$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set supplier_confirm = $supplier_confirm, supplier_confirm_time = ".NOW_TIME.",refuse_reason='".$refuse_reason."' where refund_status = 1 and id = ".$id." ","SILENT");
		if($GLOBALS['db']->affected_rows()>0){
			save_tourline_order_log($id,'用户申请退款,商家确认',1);
			
			showSuccess('商家确认成功',$ajax,admin_url("tourline_order#order",array('id'=>$id)));
		}else{				
			showSuccess('商家确认失败',$ajax,admin_url("tourline_order#order",array('id'=>$id)));
		}
		
	}   
}
?>