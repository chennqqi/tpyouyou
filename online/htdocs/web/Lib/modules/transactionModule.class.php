<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------
require_once 'ChuanglanSmsHelper/ChuanglanSmsApi.php';		
		$clapi  = new ChuanglanSmsApi();
 		$appoint_mobile = $_SESSION['mobile'];
class transactionModule extends BaseModule
{
	public function pay()
	{		
		global_run();
		init_app_page();
		$order_type = intval($_REQUEST['ot']);
		$order_sn = strim($_REQUEST['sn']);
		$GLOBALS['tmpl']->assign("order_type",$order_type);
		$GLOBALS['tmpl']->assign("order_sn",$order_sn);
		
		$payment_list = load_auto_cache("payment_list");  
		//加载支付接口列表
		$GLOBALS['tmpl']->assign("payment_list",$payment_list);
		/*加载短信接口*/
		require_once 'ChuanglanSmsHelper/ChuanglanSmsApi.php';
		$clapi  = new ChuanglanSmsApi();
 		$appoint_mobile = $_SESSION['mobile'];
		// $result = $clapi->sendSMS($appoint_mobile,'提示您，您有一条未支付的订单，请前往支付。客服热线：400-8881583','true');
		// $result = $clapi->sendSMS('15900513753','提示您，有一条未支付的订单，请前往后台查看。','true');
		$result = $clapi->execResult($result);
		//订单类型u: 充值单
		if($order_type==4)
		{
			$order_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_incharge where order_sn = '".$order_sn."'");
			if(empty($order_data))
			{
				app_redirect(url("index"));
			}
			else
			{
				if($order_data['is_paid']==1)
				{
					app_redirect(url("transaction#done",array("sn"=>$order_sn,"ot"=>4))); //跳转到支付成功页面
				}
				else
				{
					$GLOBALS['tmpl']->assign("order_data",$order_data);
					$GLOBALS['tmpl']->assign("subject","用户充值&yen;".format_price_to_display($order_data['money'])."元");
					$order_display = $GLOBALS['tmpl']->fetch("inc/order_display/order_display_4.html");
					$GLOBALS['tmpl']->assign("order_display",$order_display);
				}
			}
			//输出相应的订单显示
		}
		elseif($order_type==2) //门票订单
		{
			require APP_ROOT_PATH."system/libs/spot.php";
			$order_data = ticket_order_info($order_sn);
			
			if(empty($order_data))
			{
				app_redirect(url("index"));
			}
			elseif($order_data['order_status']==4)
			{
					showErr("订单：".$order_sn." 已作废了",0,url("uc_order#ticket_order"));
			}
			elseif($order_data['end_time'] < NOW_TIME && $order_data['end_time']  > 0 )
			{
				if(count($order_data['order_list'])>1)
				{
					$order_sn = "";
					foreach($order_data['order_list'] as $k=>$v)
					{
						if($k==0)$order_sn.=$v['sn'];
						else
						$order_sn.=",".$v['sn'];
						
					}
				}
				showErr("订单：".$order_sn." 门票过期了",0,url("uc_order#ticket_order"));
			}
			else
			{
				if($order_data['pay_status']==1)
				{					
					app_redirect(url("transaction#done",array("sn"=>$order_sn,"ot"=>2))); //跳转到支付成功页面
				}
				else
				{
					$GLOBALS['tmpl']->assign("order_data",$order_data);
					$GLOBALS['tmpl']->assign("subject","门票订单&yen;".format_price_to_display($order_data['money'])."元");
					$order_display = $GLOBALS['tmpl']->fetch("inc/order_display/order_display_2.html");
					$GLOBALS['tmpl']->assign("order_display",$order_display);
				}
			}
			//输出相应的订单显示
		}
		elseif($order_type==1) //线路订单
		{
			$order_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where sn = '".$order_sn."'");
			$order_data['money'] = $order_data['total_price'] - $order_data['pay_amount'];
			if(empty($order_data))
			{
				app_redirect(url("index"));
			}
			else
			{   
				$end_time=to_timespan($order_data['end_time']) +24*60*60;
				if($order_data['order_status']==4)
				{
					showErr("订单：".$order_sn." 已作废了",0,url("uc_order#tourline_order"));
				}
				elseif( $end_time < NOW_TIME)
				{
					showErr("订单：".$order_sn." 出游时间已过",0,url("uc_order#tourline_order"));
				}
				elseif($order_data['pay_status']==1)
				{
					app_redirect(url("transaction#done",array("sn"=>$order_sn,"ot"=>1))); //跳转到支付成功页面
				}
				elseif($order_data['pay_status']==0 && $order_data['order_confirm_type']==2 && $order_data['order_status']=1 && $order_data['confirm_time'] <=0){
				    //订单确认方式 1.付款后手动确认 2.手动确认后付款3.自动确认
					app_redirect(url("transaction#order_save_success",array("sn"=>$order_sn,"ot"=>1))); //跳转到订单提交成功页面
				}
				else{
					$GLOBALS['tmpl']->assign("order_data",$order_data);
					$GLOBALS['tmpl']->assign("subject","线路订单&yen;".format_price_to_display($order_data['money'])."元");
					$order_display = $GLOBALS['tmpl']->fetch("inc/order_display/order_display_1.html");
					$GLOBALS['tmpl']->assign("order_display",$order_display);
				}
			}
		}
		
		
		
		$GLOBALS['tmpl']->display("transaction_pay.html");
	}
	
	
	
	public function dopay()
	{
		global_run();
		$order_type = intval($_REQUEST['ot']);
		$order_sn = strim($_REQUEST['sn']);
		$money = floatval($_REQUEST['money']);
		$subject = strim($_REQUEST['subject']);
		$payment_class = strim($_REQUEST['payment_class']);
		
		require_once APP_ROOT_PATH."system/payment/".$payment_class."_payment.php";
		$cn = $payment_class."_payment";
		$payment_obj = new $cn();
		echo $payment_obj->get_payment_code($GLOBALS['user'],$order_type,$order_sn,$subject,$money);
	}
	
	public function response()
	{
		$payment_class = strim($_REQUEST['class_name']);
		require_once APP_ROOT_PATH."system/payment/".$payment_class."_payment.php";
		$cn = $payment_class."_payment";
		$payment_obj = new $cn();
		$payment_obj->response($_REQUEST);
	}
	
	public function notify()
	{
		$payment_class = strim($_REQUEST['class_name']);
		require_once APP_ROOT_PATH."system/payment/".$payment_class."_payment.php";
		$cn = $payment_class."_payment";
		$payment_obj = new $cn();
		$payment_obj->notify($_REQUEST);
	}
	
	public function done()
	{
		global_run();
		init_app_page();
		$order_type = intval($_REQUEST['ot']);
		$order_sn = strim($_REQUEST['sn']);
		if($order_type == 1)
		{
			$order_data = $GLOBALS['db']->getRow("select a.id,a.sn,b.company_name,b.contact_mobile,b.contact_tel from ".DB_PREFIX."tourline_order as a left join ".DB_PREFIX."supplier as b on b.id=a.supplier_id where sn = '".$order_sn."' and a.pay_status = 1");
			if($order_data)
			{
				$order_data['url']=url("uc_order#tourline",array('id'=>$order_data['id']));
				$GLOBALS['tmpl']->assign("order_data",$order_data);
		    	$GLOBALS['tmpl']->display("transaction_orderpaysuccess_1.html");
			}
			else
			{
				app_redirect(url("index"));
			}
		}
		if($order_type == 2){			
			require APP_ROOT_PATH."system/libs/spot.php";
			$order_data = ticket_order_info($order_sn);			
			if($order_data)
			{
				$order_data['url']=url("uc_order#ticket",array('id'=>$order_data['id']));
				foreach($order_data['order_list'] as $k=>$v)
				{
					if($v['pay_status']==1)
						$order_data['order_list'][$k]['url'] = url("uc_order#ticket",array('id'=>$v['id']));
					else
						$order_data['order_list'][$k]['url'] = url("transaction#pay",array('ot'=>2,"sn"=>$v['sn']));
					$order_data['order_list'][$k]['money'] = format_price_to_display($v['total_price'] - $v['pay_amount']);
					$order_data['order_list'][$k]['pay_amount'] = format_price_to_display($v['pay_amount']);
				}
				$GLOBALS['tmpl']->assign("order_data",$order_data);
		    	$GLOBALS['tmpl']->display("transaction_orderpaysuccess_2.html");
			}
			else
			{
				app_redirect(url("index"));
			}
		}
		if($order_type == 4){
			$order_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_incharge where order_sn = '".$order_sn."' and is_paid = 1 ");
			if($order_data)
			{
				$order_data['money'] = format_price_to_display($order_data['money']);
				$GLOBALS['tmpl']->assign("order_data",$order_data);
				$GLOBALS['tmpl']->display("transaction_orderpaysuccess_4.html");
			}
			else
			{
				app_redirect(url("index"));
			}
		}
		
	}

  	public  function order_save_success(){
  		global_run();
		init_app_page();
  		$order_type = intval($_REQUEST['ot']);
		$order_sn = strim($_REQUEST['sn']);
		
		$order_data = $GLOBALS['db']->getRow("select a.id,a.sn,b.company_name,b.contact_mobile,b.contact_tel from ".DB_PREFIX."tourline_order as a left join ".DB_PREFIX."supplier as b on b.id=a.supplier_id where sn = '".$order_sn."'");
		$order_data['url']=url("uc_order#tourline",array('id'=>$order_data['id']));
		$GLOBALS['tmpl']->assign("order_data",$order_data);
    	$GLOBALS['tmpl']->display("transaction_ordersavesuccess.html");
    }
	
}
?>