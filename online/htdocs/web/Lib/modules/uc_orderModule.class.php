<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class uc_orderModule extends BaseModule{
	public function __construct(){
		parent::__construct();
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			app_redirect(url("user#login"));
		}		
	}
	
	public function index(){
		$this->tourline_order();
	}
	
	/**
	 * 线路订单
	 */
	public function tourline_order(){
		
		$user_id = $GLOBALS['user']['id'];
		$condtion = "";
		
		if(strim($_REQUEST['sn'])!=""){
			$condtion .=" AND sn='".strim($_REQUEST['sn'])."' ";
		}
		
		$begin_time = strim($_REQUEST['begin_time']);
		$end_time = strim($_REQUEST['end_time']);
		$btime = 0;
		$etime =0;
		if($begin_time!=""){
			$btime = to_timespan($begin_time,"Y-m-d");
			$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		}
		if($end_time!=""){
			$etime = to_timespan($end_time,"Y-m-d");
			$GLOBALS['tmpl']->assign("end_time",$end_time);
		}
		
		if($btime > 0 && $etime>0){
			$condtion .=" and create_time between $btime and $etime ";
		}
		elseif($btime > 0 && $etime==0){
			$condtion .=" and create_time >= $btime ";
		}
		elseif($btime == 0 && $etime>0){
			$condtion .=" and create_time <= $btime ";
		}
		
		$page=intval($_REQUEST['p']);
    	if($page==0)
    		$page=1;
		
		$pagesize = 15;
		
		$rs_count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."tourline_order WHERE user_id=$user_id and 1=1 $condtion");
		
		if($rs_count > 0){
			require APP_ROOT_PATH . "system/libs/tourline.php";
			//获取列表
			$limit  = (($page - 1) *$pagesize) .",$pagesize";
			
			$list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."tourline_order WHERE user_id=$user_id and 1=1 $condtion order by id desc LIMIT $limit");
			$ids = array();
			foreach($list as $k=>$v){
				tourline_order_format($v);
				$order = $v;
				$list[$k]=$v;
				$list[$k]['del_url']=url("uc_order#canceltourline",array('id'=>$v['id']));
				$list[$k]['pay_url']=url("transaction#pay",array('ot'=>1,'sn'=>$v['sn']));
				$list[$k]['view_url']=url("uc_order#tourline",array('id'=>$v['id']));
				
				$allow_refund = check_allow_refund($order);
				
				$list[$k]['allow_refund'] = $allow_refund;
			}
			
			$GLOBALS['tmpl']->assign("list",$list);
			require APP_ROOT_PATH.APP_NAME.'/Lib/page.php';
			$page = new Page($rs_count,$pagesize);   //初始化分页对象 
			$p  =  $page->show();
			$GLOBALS['tmpl']->assign('pages',$p);	
		}
		init_app_page();
		$GLOBALS['tmpl']->assign("current","tourline_order");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc_order/tourline_order.html");
		$GLOBALS['tmpl']->display("uc_order.html");
	}
	/**
	 * 线路订单详情
	 */
	public static function tourline(){
		$id = intval($_REQUEST['id']);
	
		if($id == 0){
			showErr("未找到该订单",0);
		}
		
		$user_id = $GLOBALS['user']['id'];
		$order = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."tourline_order WHERE user_id=$user_id and order_status < 4 and id=".$id);
		
		if($order)
		{
			require APP_ROOT_PATH . "system/libs/tourline.php";
			tourline_order_format($order);
			$order_insurance = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tourline_insurance_order where order_id=".$order['id']);
			$insurance_name='';
			$insurance_price='';
			$insurance_total=0;
			$buy_count=$order['adult_count']+$order['child_count'];
			foreach($order_insurance as $k=>$v)
			{
				$f_insurance_price=format_price(format_price_to_display($v['insurance_price']));
				$insurance_name .="<br />".$v['insurance_name'];
				$insurance_price .=$buy_count."份 * ".format_price(format_price_to_display($v['insurance_price']))."<br />";
				$insurance_total +=$buy_count*$v['insurance_price'];
			}
			if($order_insurance)
				$order['is_insurance']=1;
			else
				$order['is_insurance']=0;
			$order['insurance_name']=$insurance_name;
			$order['insurance_price']=$insurance_price;
			$order['insurance_total']=format_price(format_price_to_display($insurance_total));
			$visa_total=$order['visa_count']*$order['visa_price'];
			$order['visa_total'] = format_price(format_price_to_display($visa_total));
			$order['yufu_hide']=is_yufu_hide($order['adult_count'],$order['child_count'],$order['adult_price'],$order['adult_sale_price'],$order['child_price'],$order['child_sale_price']);
			$order['t_total_price']=format_price(format_price_to_display($order['tourline_total_price']-$insurance_total-$visa_total));
			
			$order_namelist = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tourline_order_namelist where tourline_order_id=".$order['id']." order by status desc");
			foreach($order_namelist as $k=>$v)
			{
				$order_namelist[$k]['paper_type_val'] =get_paper_type_name($v['paper_type']);
			}
			
			$adult_count_array=array();
			$child_count_array=array();
			$visa_count_array=array();
			if($order['adult_count'] >0)
			{
				for($i=0; $i <= intval($order['adult_count']);$i++)
				{
					$adult_count_array[$i]['num']=$i;
				}
			}
			if($order['child_count'] >0)
			{
				for($i=0;$i <= $order['child_count'];$i++)
				{
					$child_count_array[$i]['num']=$i;
				}
			}
			if($order['visa_count'] >0)
			{
				for($i=0;$i <= $order['visa_count'];$i++)
				{
					$visa_count_array[$i]['num']=$i;
				}
			}
			
			//出发城市名称
			$order['out_city']=$GLOBALS['db']->getOne("select b.name FROM ".DB_PREFIX."tourline as a left join ".DB_PREFIX."tour_city as b on b.id=a.city_id where a.id=".intval($order['tourline_id'].""));
			
			$GLOBALS['tmpl']->assign("adult_count_array",$adult_count_array);
			$GLOBALS['tmpl']->assign("child_count_array",$child_count_array);
			$GLOBALS['tmpl']->assign("visa_count_array",$visa_count_array);
			$GLOBALS['tmpl']->assign("order_namelist",$order_namelist);
			$GLOBALS['tmpl']->assign("order",$order);
			$allow_refund = check_allow_refund($order);
			$GLOBALS['tmpl']->assign("allow_refund",$allow_refund);
			$ur_here[] = array("name"=>"会员中心","url"=>url("user"));
    		$ur_here[] = array("name"=>"我的订单","url"=>url("uc_order#tourline_order") );
    		$GLOBALS['tmpl']->assign("ur_here",$ur_here);
		}
		else
		{
			showErr("未找到该订单",0);
		}
		init_app_page();
		$GLOBALS['tmpl']->display("inc/uc_order/uc_tourline.html");
	}
	
	/*
	 * 退款申请
	 * */
	function tourline_refund(){
		$ajax=1;
		$order_id=intval($_REQUEST['order_id']);
		$refund_adult_count=intval($_REQUEST['refund_adult_count']);
		$refund_child_count=intval($_REQUEST['refund_child_count']);
		$refund_txt=strim($_REQUEST['refund_txt']);
		if($refund_adult_count <=0 && $refund_child_count<=0)
		{
			$return["info"]="请选择退款人员数";
			$return["status"]=0;
			ajax_return($return);
		}
		
		if($refund_txt=='')
		{
			$return["info"]="请输入退款原因！";
			$return["status"]=0;
			ajax_return($return);
		}
			
		$order=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where id=".$order_id);
		if($order)
		{ 
			if($order['refund_status']<2&&
					(($order['order_status']==1&&$order['pay_status']==1&&$order['total_price']>0) //已付款，有金额的新订单
							||($order['order_status']==2&&$order['is_refund']==1)	//已确认订单，并且支持退款
							||($order['order_status']<=2&&$order['end_time']>0&&(NOW_TIME-to_timespan($order['end_time'],"Y-m-d"))>24*3600&&$order['is_expire_refund']) //过期退
					)
			)
			{
				$GLOBALS['db']->query("UPDATE ".DB_PREFIX."tourline_order set re_action_time = ".NOW_TIME.",refund_adult_count=".$refund_adult_count.",refund_child_count=".$refund_child_count.",refund_txt='".$refund_txt."',refund_status=1  WHERE  id=".$order_id);
				$return["status"]=1;
				$return["成功"]=1;
				ajax_return($return);
			}
			else
			{
				$return["info"]="订单不允许退款";
				$return["status"]=0;
				ajax_return($return);
			}
			
			
		}
		else
		{
			$return["info"]="数据错误";
			$return["status"]=0;
			ajax_return($return);
		}
	}
	/**
	 * 取消线路订单
	 */
	function canceltourline(){
		$id = intval($_REQUEST['id']);
		$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where id = ".$id." and order_status = 1 and (pay_status = 0 or (pay_status = 1 and total_price = 0)) and user_id = '".$GLOBALS['user']['id']."'");
		if(empty($order)){
			showErr("数据错误",1);
		}
		//开始计算需要退回的款项		
		require APP_ROOT_PATH . "system/libs/tourline.php";		
		$ret=tourline_order_invalid($id,0,$order['account_pay']); //只退余额支付部份
		if($ret)
		{
			showSuccess("取消成功",1);
		}
		else
		{
			showErr("订单不存在,或已经作废",1)	;
		}
	}
	/**
	 * 门票订单
	 */
	public function ticket_order(){
		$user_id = $GLOBALS['user']['id'];
		$condtion = "";
		
		if(strim($_REQUEST['sn'])!=""){
			$condtion .=" AND tt.sn='".strim($_REQUEST['sn'])."' ";
		}
		
		$begin_time = strim($_REQUEST['begin_time']);
		$end_time = strim($_REQUEST['end_time']);
		$btime = 0;
		$etime =0;
		if($begin_time!=""){
			$btime = to_timespan($begin_time,"Y-m-d");
			$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		}
		if($end_time!=""){
			$etime = to_timespan($end_time,"Y-m-d");
			$GLOBALS['tmpl']->assign("end_time",$end_time);
		}
		
		if($btime > 0 && $etime>0){
			$condtion .=" and tt.create_time between $btime and $etime ";
		}
		elseif($btime > 0 && $etime==0){
			$condtion .=" and tt.create_time >= $btime ";
		}
		elseif($btime == 0 && $etime>0){
			$condtion .=" and tt.create_time <= $btime ";
		}
		
		$page=intval($_REQUEST['p']);
    	if($page==0)
    		$page=1;
		
		$pagesize = 15;
		
		$rs_count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."ticket_order WHERE user_id=$user_id $condtion");

		if($rs_count > 0){
			require APP_ROOT_PATH . "system/libs/spot.php";
			//获取列表
			$limit  = (($page - 1) *$pagesize) .",$pagesize";
			
			$list = $GLOBALS['db']->getAll("SELECT tt.*,t.spot_id,t.name_brief,s.image,s.name FROM ".DB_PREFIX."ticket_order tt LEFT JOIN  ".DB_PREFIX."ticket t ON tt.ticket_id = t.id LEFT JOIN ".DB_PREFIX."spot s ON s.id= t.spot_id WHERE  tt.user_id=$user_id $condtion ORDER BY tt.id DESC LIMIT $limit");
			$ids = array();
			foreach($list as $k=>$v){
				ticket_order_format($v);
				$v['ticket_url'] = bulid_spot_url($v['spot_id']);
				//支付
				$v['op_status_0'] = 0;
				//取消
				$v['op_status_1'] = 0;
				//退票 1退款 2门票列表
				$v['op_status_2'] = 0;
				
				if($v['order_status'] == 1 && $v['pay_status'] ==0 && ($v['end_time'] > NOW_TIME || $v['end_time'] ==0))
				{
					//新订单未支付  未过期
					$v['op_status_0'] = 1;
					$v['op_status_1'] = 1;
				}
				if($v['order_status'] == 1 && $v['pay_status']==1 && ($v['end_time'] > NOW_TIME || $v['end_time'] ==0))
				{
					//新订单 已支付 支付 0元
					$v['op_status_0'] = 0;
					if($v['total_price'] == 0)//支付0元
						$v['op_status_1'] = 1;
				}
				
				if(($v['order_status'] == 1 || ($v['order_status'] == 2 && $v['is_refund'] == 1 && ($v['end_time'] > NOW_TIME || $v['end_time'] == 0)) || ($v['order_status'] == 2 && $v['is_expire_refund'] == 1 && $v['end_time'] < NOW_TIME && $v['end_time'] >0)) && $v['pay_status'] == 1 && $v['refund_status'] == 0) {
					//1新单 2已确认 可退款  未过期  3。已确认过期退 已过期
					$v['op_status_2'] = 1;
				}
				elseif($v['pay_status'] == 1){
					$v['op_status_2'] = 2;
				}
					
					
				$list[$k]=$v;
			}
			
			$GLOBALS['tmpl']->assign("NOW_TIME",NOW_TIME);
			$GLOBALS['tmpl']->assign("list",$list);
			
			require APP_ROOT_PATH.APP_NAME.'/Lib/page.php';
			$page = new Page($rs_count,$pagesize);   //初始化分页对象 
			$p  =  $page->show();
			$GLOBALS['tmpl']->assign('pages',$p);
		}
		init_app_page();
		
		$GLOBALS['tmpl']->assign("current","ticket_order");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc_order/ticket_order.html");
		$GLOBALS['tmpl']->display("uc_order.html");
	}
	
	/**
	 * 订单详情
	 */
	function ticket(){
		$user_id = $GLOBALS['user']['id'];
		$id = intval($_REQUEST['id']);
		if($id == 0){
			showErr("数据错误",0,url("uc_order#ticket_order"));
		}
		require APP_ROOT_PATH . "system/libs/spot.php";
		$order = ticket_order_info_byid($id);
		if(!$order){
			showErr("订单不存在",0,url("uc_order#ticket_order"));
		}
		if($order['user_id']!=$user_id){
			showErr("不属于你的订单",0,url("uc_order#ticket_order"));
		}
		
		$order['other_info'] = $GLOBALS['db']->getRow("SELECT t.name_brief,t.spot_id,s.name,s.image,s.name FROM ".DB_PREFIX."ticket t LEFT join ".DB_PREFIX."spot s ON t.spot_id = s.id  WHERE t.id=".$order['ticket_id']);
		$order['other_info']['ticket_url'] = bulid_spot_url($order['other_info']['spot_id']);
		ticket_order_format($order);
		
		
    	$GLOBALS['tmpl']->assign("NOW_TIME",NOW_TIME);
    	$GLOBALS['tmpl']->assign("order",$order);
    	
		$ur_here[] = array("name"=>"会员中心","url"=>url("user"));
    	$ur_here[] = array("name"=>"我的订单");
    	
    	$GLOBALS['tmpl']->assign("ur_here",$ur_here);
		
		init_app_page();
		$GLOBALS['tmpl']->display("inc/uc_order/uc_ticket.html");
	}
	
	/**
	 * 退票操作
	 */
	function refundticket(){
		$user_id = $GLOBALS['user']['id'];
		$id = intval($_REQUEST['id']);
		if($id == 0){
			showErr("数据错误",0,url("uc_order#ticket_order"));
		}
		require APP_ROOT_PATH . "system/libs/spot.php";
		$order = ticket_order_info_byid($id);
		
		
		if(!$order){
			showErr("订单不存在",0,url("uc_order#ticket_order"));
		}
		
		if($order['user_id']!=$user_id){
			showErr("不属于你的订单",0,url("uc_order#ticket_order"));
		}
		
		if($order['order_status']==4)
		{
			showErr("订单已作废",0,url("uc_order#ticket_order"));
		}
		
		ticket_order_format($order);
		
		$ticket_info = $GLOBALS['db']->getRow("SELECT t.*,s.image from ".DB_PREFIX."ticket t LEFT JOIN ".DB_PREFIX."spot s on s.id =t.spot_id  where t.id=".$order['ticket_id']);
    	$can_refund_appoint = 0;
    	//获取订单下的门票
    	$tickets = $GLOBALS['db']->getAll("SELECT * FROM  ".DB_PREFIX."ticket_order_item WHERE order_id=".$order['id']);
    	foreach($tickets as $k=>$v){
    		ticket_order_item_format($v);
    		if ($v['is_verify_code_invalid'] == 0){
				
				//退票状态与改签状态同时只能更新一个
				$v['item_status_type_0'] = "";
				if ($v['refund_status']== 1)
					$v['item_status_type_0']="退票中";
				elseif ($v['refund_status']== 2)
					$v['item_status_type_0']="已退票";
				elseif ($v['refund_status']== 3)
					$v['item_status_type_0']="拒退票";
				
				$v['item_status_type_1'] = "";
				
				if ($v['re_appoint_status']== 1)
					$v['item_status_type_1']="改签中";
				elseif ($v['re_appoint_status']== 2)
					$v['item_status_type_1']="已改签";
				elseif ($v['re_appoint_status']== 3)
					$v['item_status_type_1']="拒改签";
				
				//end 退票状态与改签状态同时只能更新一个
				
				$v['item_status_type_2']	= "";
				$v['item_status_type_3'] = "";
				if($order["is_delivery"] == 1){
					if ($order['delivery_time'] <> 0){ //发货时，不可将有退票申请的退票设为已发货
						$v['item_status_type_2']	= "已配送";
					}else{
						$v['item_status_type_2']	= "未配送";
					}
				}
				else{
					if ($v['verify_time'] > 0)
						$v['item_status_type_3'] = "已验证";
					elseif($v['end_time'] > NOW_TIME ||  $v['end_time'] ==0)
						$v['item_status_type_3'] = "未验证";
					else
						$v['item_status_type_3'] = "已过期";
				}	
	    		
	    		//是否可退票
	    		$v['op_status_0'] = 0;
	    		$v['op_status_1'] = 0;
    		
				if (($order['order_status'] == 2 && $order['is_refund'] == 1 && $v['verify_time'] == 0) || ($order['order_status'] == 1)){
					//1.订单确认成功且验证码未使用（以避免部份已使用的券退款）并且支持退改 2.新订单 3.订单已完成（判断过期退）
					
					if ($v['refund_status'] ==  0){
					//只允许退一次，有退款就不再显示
					
						if (($order['order_status'] == 2 && $order['is_refund'] == 1&& $v['verify_time'] == 0 && ($v['end_time'] > NOW_TIME || $v['end_time'] == 0))){//1.订单确认成功并且支持退改					
							$v['op_status_0'] = 1;
							if ($v['appoint_time'] > 0){
								$v['op_status_1'] = 1;
							}
							
						}
						
						if ($order['order_status'] == 1){//2.新订单
							$v['op_status_0'] = 1;
							if ($v['appoint_time'] > 0 && ($v['end_time'] > NOW_TIME || $v['end_time'] == 0)){
								//有预约，并且预约时间大于等于现在
								$v['op_status_1'] = 1;
							}
						}
						
						if (($order['order_status'] == 2 && $v['verify_time'] == 0 && $v['end_time'] < NOW_TIME && $v['end_time'] >0)){
							//2.订单已确认，验证码未使用，并且超过有效期，表示为过期（后台操作为订单完成时，需要将未使用的验证码作废）
							if ($order['is_expire_refund'] == 1){
								$v['op_status_0'] = 1;
							}							
						}
						
					//end只允许退一次，有退款就不再显示
					}
					
				}
				
				//end 只有未作废的验证码可退改
			}
			if($v['op_status_0']==1 || $v['op_status_1'] == 1)
				$can_refund_appoint = 1;
			$tickets[$k] = $v;
    	}
	   $GLOBALS['tmpl']->assign("tickets",$tickets);
	   $GLOBALS['tmpl']->assign("ticket_info",$ticket_info);
	   $GLOBALS['tmpl']->assign("can_refund_appoint",$can_refund_appoint);
		
    	$GLOBALS['tmpl']->assign("NOW_TIME",NOW_TIME);
    	$GLOBALS['tmpl']->assign("order",$order);
    	
		init_app_page();
		$GLOBALS['tmpl']->assign("current","ticket_order_refund");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc_order/refund_ticket.html");
		$GLOBALS['tmpl']->display("uc_order.html");
	}
	
	/*退款操作*/
	
	function refund(){
		$user_id = $GLOBALS['user']['id'];
		$id = intval($_REQUEST['id']);
		if($id == 0){
			showErr("数据错误",1);
		}
		
		
		require APP_ROOT_PATH . "system/libs/spot.php";
		$order = ticket_order_info_byid($id);
		if(!$order){
			showErr("订单不存在",1);
		}
		
		if($order['user_id']!=$user_id){
			showErr("不属于你的订单",1);
		}
		
		$order['left_sale_count'] = $order['sale_count'];
		
		//获取订单下的门票
    	$tickets = $GLOBALS['db']->getAll("SELECT * FROM  ".DB_PREFIX."ticket_order_item WHERE order_id=".$order['id']);
    	foreach($tickets as $k=>$v){
    		
    		if ($v['is_verify_code_invalid'] == 0){
				
				//退票状态与改签状态同时只能更新一个
				$v['item_status_type_0'] = "";
				if ($v['refund_status']== 1)
					$v['item_status_type_0']="退票中";
				elseif ($v['refund_status']== 2)
					$v['item_status_type_0']="已退票";
				elseif ($v['refund_status']== 3)
					$v['item_status_type_0']="拒退票";
				
				$v['item_status_type_1'] = "";
				
				if ($v['re_appoint_status']== 1)
					$v['item_status_type_1']="改签中";
				elseif ($v['re_appoint_status']== 2)
					$v['item_status_type_1']="已改签";
				elseif ($v['re_appoint_status']== 3)
					$v['item_status_type_1']="拒改签";
				
				//end 退票状态与改签状态同时只能更新一个
				
				$v['item_status_type_2']	= "";
				$v['item_status_type_3'] = "";
				if($order["is_delivery"] == 1){
					if ($order['delivery_time'] <> 0){ //发货时，不可将有退票申请的退票设为已发货
						$v['item_status_type_2']	= "已配送";
					}else{
						$v['item_status_type_2']	= "未配送";
					}
				}
				else{
					if ($v['verify_time'] > 0)
						$v['item_status_type_3'] = "已验证";
					elseif($v['end_time'] > NOW_TIME ||  $v['end_time'] ==0)
						$v['item_status_type_3'] = "未验证";
					else
						$v['item_status_type_3'] = "已过期";
				}	
    		
	    		//是否可退票
	    		//退票
	    		$v['op_status_0'] = 0;
	    		//改签
	    		$v['op_status_1'] = 0;
    		
				if (($order['order_status'] == 2 && $order['is_refund'] == 1 && $v['verify_time'] == 0) || ($order['order_status'] == 1)){
					//1.订单确认成功且验证码未使用（以避免部份已使用的券退款）并且支持退改 2.新订单 3.订单已完成（判断过期退）
					
					if ($v['refund_status'] ==  0){
					//只允许退一次，有退款就不再显示
					
						if (($order['order_status'] == 2 && $order['is_refund'] == 1&& $v['verify_time'] == 0)){//1.订单确认成功并且支持退改					
							$v['op_status_0'] = 1;
							if ($v['appoint_time'] > 0){
								$v['op_status_1'] = 1;
							}
							
						}
						
						if ($order['order_status'] == 1){//2.新订单
							$v['op_status_0'] = 1;
							if ($v['appoint_time'] >0 && ($v['end_time'] > NOW_TIME || $v['end_time'] == 0)){
								$v['op_status_1'] = 1;
							}
						}
						
						if (($order['order_status'] == 2 && $v['verify_time'] == 0 && $v['end_time'] < NOW_TIME && $v['end_time'] >0)){
							//2.订单已确认，验证码未使用，并且超过有效期，表示为过期（后台操作为订单完成时，需要将未使用的验证码作废）
							if ($order['is_expire_refund'] == 1){
								$v['op_status_0'] = 1;
							}							
						}
						
					//end只允许退一次，有退款就不再显示
					}
					
				}
				
				//end 只有未作废的验证码可退改
			}
			
			$tickets[$k] = $v;
    	}
	    $GLOBALS['tmpl']->assign("tickets",$tickets);
		$GLOBALS['tmpl']->assign("id",$id);
		$GLOBALS['tmpl']->assign("order",$order);
		
		$info = $GLOBALS['tmpl']->fetch("inc/uc_order/do_refund.html");
		
		showSuccess($info,1);
	}
	
	function do_refund(){
		$user_id = $GLOBALS['user']['id'];
		$id = intval($_REQUEST['id']);
		$tid = $_REQUEST['tid'];
		
		if($id == 0){
			showErr("数据错误",1);
		}
		
		require APP_ROOT_PATH . "system/libs/spot.php";
		$order = ticket_order_info_byid($id);
		if(!$order){
			showErr("订单不存在",1);
		}
		if($order['user_id']!=$user_id){
			showErr("不属于你的订单",1);
		}
		
		if(strim($_POST['refund_txt'])==""){
			showErr("请输入退票原因",1);
		}
		
		if($order['is_divide'] == 1 && $order['is_delivery'] == 0){
			//个人票非配送
			if(count($tid) == 0){
				showErr("请选择要退的门票",1);
			}
    		$tickets = $GLOBALS['db']->getAll("SELECT * FROM  ".DB_PREFIX."ticket_order_item WHERE order_id=".$order['id']." AND id in (".implode(",",$tid).")");
		}
		else{
			$tickets = $GLOBALS['db']->getAll("SELECT * FROM  ".DB_PREFIX."ticket_order_item WHERE order_id=".$order['id']);
		}
		if(!$tickets){
			showErr("无可退的门票",1);
		}
		$do_refund_ids =array();
		foreach($tickets as $k=>$v){
    		
    		if ($v['is_verify_code_invalid'] == 0){
    		
	    		//是否可退票
	    		$v['op_status_0'] = 0;
    		
				if (($order['order_status'] == 2 && $order['is_refund'] == 1 && $v['verify_time'] == 0) || ($order['order_status'] == 1)){
					//1.订单确认成功且验证码未使用（以避免部份已使用的券退款）并且支持退改 2.新订单 3.订单已完成（判断过期退）
					
					if ($v['refund_status'] ==  0){
					//只允许退一次，有退款就不再显示
					
						if (($order['order_status'] == 2 && $order['is_refund'] == 1&& $v['verify_time'] == 0)){//1.订单确认成功并且支持退改					
							$v['op_status_0'] = 1;
							
						}
						
						if ($order['order_status'] == 1){//2.新订单
							$v['op_status_0'] = 1;
						}
						
						if (($order['order_status'] == 2 && $v['verify_time'] == 0 && $v['end_time'] < NOW_TIME && $v['end_time'] >0)){
							//2.订单已确认，验证码未使用，并且超过有效期，表示为过期（后台操作为订单完成时，需要将未使用的验证码作废）
							if ($order['is_expire_refund'] == 1){
								$v['op_status_0'] = 1;
							}							
						}
						//end只允许退一次，有退款就不再显示
					}
					
				}
				if($v['op_status_0'] == 0){
					if(count($tid) > 0)
						showErr("验证码为".$v['verify_code']."的门票无法退票。",1);
					else
						showErr("门票无法退票。",1);
				}
				else{
					$do_refund_ids[] = $v['id'];
				}
				//end 只有未作废的验证码可退改
			}
			else{
				if(count($tid) > 0)
					showErr("验证码为".$v['verify_code']."的门票无效。",1);
				else
					showErr("的门票无效。",1);
			}
			
    	}
    	
    	if(count($do_refund_ids) == 0){
    		showErr("无可退的门票",1);
    	}
		
		$update_data = array();
		$update_data['refund_status'] = 1;
		
		if($order['is_delivery']==1 || $order['is_divide'] == 0){
			if(intval($_POST['refund_count'])==""){
				showErr("请输入退票数量",1);
			}
			
			if(intval($_POST['refund_count']) > $order['sale_count'] ){
				showErr("超出可退数量",1);
			}
			
			
		}
		
		//团体票 或者实体票
		$GLOBALS['db']->autoExecute(DB_PREFIX."ticket_order_item",$update_data,"UPDATE","order_id=".$id." AND id in (".implode(",",$do_refund_ids).")");
		
			
		if($order['is_delivery']==1 || $order['is_divide'] == 0){
			$update_data['ref_refund_count'] = intval($_POST['refund_count']);
		}
		else{
			$update_data['ref_refund_count'] = count($do_refund_ids);
		}

		$update_data['refund_txt'] = $_POST['refund_txt'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."ticket_order",$update_data,"UPDATE","id=".$id);
		
		if($GLOBALS['db']->affected_rows() >0){
			showSuccess("退票成功，等待处理",1);
		}
		else{
			showErr("退票失败",1);
		}
	}
	
	function appoint(){
		$user_id = $GLOBALS['user']['id'];
		$id = intval($_REQUEST['id']);
		
		if($id == 0){
			showErr("数据错误",1);
		}
		
		require APP_ROOT_PATH . "system/libs/spot.php";
		$order = ticket_order_info_byid($id);
		if($order['user_id']!=$user_id){
			showErr("不属于你的订单",1);
		}
		
		
		//获取订单下的门票
    	$tickets = $GLOBALS['db']->getAll("SELECT * FROM  ".DB_PREFIX."ticket_order_item WHERE order_id=".$order['id']);
    	foreach($tickets as $k=>$v){
    		
    		if ($v['is_verify_code_invalid'] == 0){
				
				//退票状态与改签状态同时只能更新一个
				$v['item_status_type_0'] = "";
				if ($v['refund_status']== 1)
					$v['item_status_type_0']="退票中";
				elseif ($v['refund_status']== 2)
					$v['item_status_type_0']="已退票";
				elseif ($v['refund_status']== 3)
					$v['item_status_type_0']="拒退票";
				
				$v['item_status_type_1'] = "";
				
				if ($v['re_appoint_status']== 1)
					$v['item_status_type_1']="改签中";
				elseif ($v['re_appoint_status']== 2)
					$v['item_status_type_1']="已改签";
				elseif ($v['re_appoint_status']== 3)
					$v['item_status_type_1']="拒改签";
				
				//end 退票状态与改签状态同时只能更新一个
				
				$v['item_status_type_2']	= "";
				$v['item_status_type_3'] = "";
				if($order["is_delivery"] == 1){
					if ($order['delivery_time'] <> 0){ //发货时，不可将有退票申请的退票设为已发货
						$v['item_status_type_2']	= "已配送";
					}else{
						$v['item_status_type_2']	= "未配送";
					}
				}
				else{
					if ($v['verify_time'] > 0)
						$v['item_status_type_3'] = "已验证";
					elseif($v['end_time'] > NOW_TIME ||  $v['end_time'] ==0)
						$v['item_status_type_3'] = "未验证";
					else
						$v['item_status_type_3'] = "已过期";
				}	
    		
	    		//是否可退票
	    		$v['op_status_0'] = 0;
	    		$v['op_status_1'] = 0;
    		
				if (($order['order_status'] == 2 && $order['is_refund'] == 1 && $v['verify_time'] == 0) || ($order['order_status'] == 1)){
					//1.订单确认成功且验证码未使用（以避免部份已使用的券退款）并且支持退改 2.新订单 3.订单已完成（判断过期退）
					
					if ($v['refund_status'] ==  0){
					//只允许退一次，有退款就不再显示
					
						if (($order['order_status'] == 2 && $order['is_refund'] == 1&& $v['verify_time'] == 0)){//1.订单确认成功并且支持退改					
							$v['op_status_0'] = 1;
							if ($v['appoint_time'] > 0){
								$v['op_status_1'] = 1;
							}
							
						}
						
						if ($order['order_status'] == 1){//2.新订单
							$v['op_status_0'] = 1;
							if ($v['appoint_time'] > 0 && ($v['end_time'] > NOW_TIME || $v['end_time'] == 0)){
								$v['op_status_1'] = 1;
							}
						}
						
						if (($order['order_status'] == 2 && $v['verify_time'] == 0 && $v['end_time'] < NOW_TIME && $v['end_time'] >0)){
							//2.订单已确认，验证码未使用，并且超过有效期，表示为过期（后台操作为订单完成时，需要将未使用的验证码作废）
							if ($order['is_expire_refund'] == 1){
								$v['op_status_0'] = 1;
							}							
						}
						
					//end只允许退一次，有退款就不再显示
					}
					
				}
				
				//end 只有未作废的验证码可退改
			}
			
			$tickets[$k] = $v;
    	}
	    $GLOBALS['tmpl']->assign("tickets",$tickets);
	    
	    $ticket_info = $GLOBALS['db']->getRow("SELECT t.*,s.image from ".DB_PREFIX."ticket t LEFT JOIN ".DB_PREFIX."spot s on s.id =t.spot_id  where t.id=".$order['ticket_id']);
		$GLOBALS['tmpl']->assign("ticket_info",$ticket_info);
		if($ticket_info['end_time'] > 0){
			$GLOBALS['tmpl']->assign("maxdate",to_date($ticket_info['end_time'],"Y-m-d"));
		}
		
		
		$GLOBALS['tmpl']->assign("mindate",to_date(NOW_TIME,"Y-m-d"));
		$GLOBALS['tmpl']->assign("id",$id);
		$GLOBALS['tmpl']->assign("order",$order);
		
		$info = $GLOBALS['tmpl']->fetch("inc/uc_order/do_appoint.html");
		
		showSuccess($info,1);
		
	}
	
	function do_appoint(){
		$user_id = $GLOBALS['user']['id'];
		$id = intval($_REQUEST['id']);
		$tid = $_REQUEST['tid'];
		
		if($id == 0){
			showErr("数据错误",1);
		}
		
		require APP_ROOT_PATH . "system/libs/spot.php";
		$order = ticket_order_info_byid($id);
		if(!$order){
			showErr("订单不存在",1);
		}
		if($order['user_id']!=$user_id){
			showErr("不属于你的订单",1);
		}
		
		if($order['is_divide'] == 1 && $order['is_delivery'] == 0){
			//个人票非配送
			if(count($tid) == 0){
				showErr("请选择要改签的门票",1);
			}
    		$tickets = $GLOBALS['db']->getAll("SELECT * FROM  ".DB_PREFIX."ticket_order_item WHERE order_id=".$order['id']." AND id in (".implode(",",$tid).")");
		}
		else{
			$tickets = $GLOBALS['db']->getAll("SELECT * FROM  ".DB_PREFIX."ticket_order_item WHERE order_id=".$order['id']);
		}
		if(!$tickets){
			showErr("无可改签的门票",1);
		}
		
		$do_appoint_ids = array();
		//获取订单下的门票
    	$tickets = $GLOBALS['db']->getAll("SELECT * FROM  ".DB_PREFIX."ticket_order_item WHERE order_id=".$order['id']);
    	foreach($tickets as $k=>$v){
    		
    		if ($v['is_verify_code_invalid'] == 0){
				
	    		$v['op_status_1'] = 0;
    		
				if (($order['order_status'] == 2 && $order['is_refund'] == 1 && $v['verify_time'] == 0) || ($order['order_status'] == 1)){
					//1.订单确认成功且验证码未使用（以避免部份已使用的券退款）并且支持退改 2.新订单 3.订单已完成（判断过期退）
					
					if ($v['refund_status'] ==  0){
					//只允许退一次，有退款就不再显示
					
						if (($order['order_status'] == 2 && $order['is_refund'] == 1&& $v['verify_time'] == 0)){//1.订单确认成功并且支持退改					
							
							if ($v['appoint_time'] > 0){
								$v['op_status_1'] = 1;
							}
							
						}
						
						if ($order['order_status'] == 1){//2.新订单
						
							if ($v['appoint_time'] > 0 && ($v['end_time'] > NOW_TIME || $v['end_time'] == 0)){
								$v['op_status_1'] = 1;
							}
						}
						
						
					//end只允许退一次，有退款就不再显示
					}
					
				}
				if($v['op_status_1'] == 0){
					if(count($tid) > 0)
						showErr("验证码为".$v['verify_code']."的门票无法改签。",1);
					else
						showErr("门票无法改签。",1);
				}
				else{
					$do_appoint_ids[] = $v['id'];
				}
				//end 只有未作废的验证码可退改
			}
			else{
				if(count($tid) > 0)
					showErr("验证码为".$v['verify_code']."的门票无法改签。",1);
				else
					showErr("门票无法改签。",1);
			}
    	}
    	
    	if(count($do_appoint_ids) == 0){
    		showErr("无改签的门票",1);
    	}
		
		$item_update_data['re_appoint_time'] = to_timespan($_POST['re_appoint_time'],"Y-m-d");
		$item_update_data['re_appoint_status'] = 1;
		$item_update_data['re_action_time'] = NOW_TIME;
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."ticket_order_item",$item_update_data,"UPDATE","order_id=".$id." AND id in (".implode(",",$do_appoint_ids).") ","SILENT");
		
		$update_data['re_appoint_status'] = 1;
		$GLOBALS['db']->autoExecute(DB_PREFIX."ticket_order",$update_data,"UPDATE","id=".$id,"SILENT");
		
		if($GLOBALS['db']->affected_rows() >0){
			showSuccess("退票改签，等待处理",1);
		}
		else{
			showErr("退票失败",1);
		}
		
	}
	
	/**
	 * 取消订单
	 */
	function cancelticket(){
		$id = intval($_REQUEST['id']);
		if($id == 0){
			showErr("数据错误",1);
		}
		require APP_ROOT_PATH . "system/libs/spot.php";
		$order_data = ticket_order_info_byid($id);
		if(!$order_data || $order_data['user_id'] != $GLOBALS['user']['id']){
			showErr("数据错误",1);
		}
		
		if(
			($order_data['order_status'] == 1 && $order_data['pay_status'] == 0)//新订单 未支付
			||
			($order_data['order_status'] == 1 && $order_data['pay_status'] == 1 && $order_data['total_price'] == 0)//未确认 已支付 支付0
			
		){
			$res = ticket_order_invalid($id,0,$order_data['account_pay']);
			if($res){
// 				//更新余额
// 				if($order_data['account_pay'] > 0)
//     				User::modify_account($GLOBALS['user']['id'],1,$order_data['account_pay'],"订单 ".$order_data['sn']." 取消，返还余额".format_price_to_display($order_data['account_pay'])."元。");
// 				//更新代金券
// 				//$GLOBALS['db']->query("UPDATE ".DB_PREFIX."voucher SET is_used=0,use_oid=0,use_otype=0,use_time=0 WHERE use_otype=2 and use_oid = ".$id);
				
// 				//保存日志
// 				save_ticket_order_log($id,"会员取消订单",0);

				
				
				showSuccess("取消成功",1);
			}
			else{
				showErr("取消失败",1);
			}
		}
		else{
			showErr("当前订单状态无法取消",1);
		}
		
		
	}
}