<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(88522820@qq.com)
// +----------------------------------------------------------------------

$payment_lang = array(
	'name'	=>	'财付通即时到账支付',
	'tencentpay_id'	=>	'商户ID',
	'tencentpay_key'	=>	'商户密钥',
	'tencentpay_sign'	=>	'自定义签名',
	'VALID_ERROR'	=>	'支付验证失败',
	'PAY_FAILED'	=>	'支付失败',
	'GO_TO_PAY'	=>	'前往财付通支付',
);
$config = array(
	'tencentpay_id'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //商户ID
	'tencentpay_key'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //商户密钥
	'tencentpay_sign'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //自定义签名
);
/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'Tenpay';

    /* 名称 */
    $module['name']    = $payment_lang['name'];


    /* 支付方式：1：在线支付；0：线下支付 */
    $module['online_pay'] = '1';

    /* 配送 */
    $module['config'] = $config;
    
    $module['lang'] = $payment_lang;
    return $module;
}

// 财付通支付模型
require_once(APP_ROOT_PATH.'system/libs/payment.php');
class Tenpay_payment implements payment { 

	public function get_payment_code($user_data,$order_type,$order_sn,$subject,$money)
	{
		require APP_ROOT_PATH."system/payment/Tenpay/classes/RequestHandler.class.php";
		require_once APP_ROOT_PATH."system/libs/transaction.php";
		$payment_notice_sn = Transaction::make_payment($user_data, $order_type, $order_sn, $money);
		$GLOBALS['db']->query("update ".DB_PREFIX."payment_notice set payment_class='Tenpay',payment_name='财付通即时到账支付' where notice_sn = '".$payment_notice_sn."'");
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where notice_sn = '".$payment_notice_sn."'");
		
		$money = format_price_to_display($payment_notice['money']);
		$payment_info = $GLOBALS['db']->getRow("select id,config,logo from ".DB_PREFIX."payment where class_name = 'Tenpay' ");
		if(empty($payment_info))app_redirect(url("index"));
		$payment_info['config'] = unserialize($payment_info['config']);

		
		$data_return_url = url("transaction#response",array("class_name"=>"Tenpay"));
		$data_notify_url = url("transaction#notify",array("class_name"=>"Tenpay"));
		
		
		$cmd_no = '1';
		
		/* 获得订单的流水号，补零到10位 */
		$sp_billno = $payment_notice_sn;
		
		$spbill_create_ip =  CLIENT_IP;
		
		/* 交易日期 */
		$today = to_date($payment_notice['create_time'],'YmdHis');
		
		
		/* 将商户号+年月日+流水号 */
		$out_trade_no = $payment_notice['notice_sn'];
		
		/* 银行类型:支持纯网关和财付通 */
		$bank_type = "DEFAULT";
		
		
		$desc = $subject;
		$attach = $payment_info['config']['tencentpay_sign'];
		
		
		/* 返回的路径 */
		$return_url = $data_return_url;
		
		/* 总金额 */
		$total_fee = $payment_notice['money'];
		
		/* 货币类型 */
		$fee_type = '1';
		
		
		$reqHandler = new RequestHandler();
		$reqHandler->init();
		$reqHandler->setKey($payment_info['config']['tencentpay_key']);
		$reqHandler->setGateUrl("https://gw.tenpay.com/gateway/pay.htm");
		
		//----------------------------------------
		//设置支付参数
		//----------------------------------------
		$reqHandler->setParameter("partner", $payment_info['config']['tencentpay_id']);
		$reqHandler->setParameter("out_trade_no", $out_trade_no);
		$reqHandler->setParameter("total_fee", $total_fee);  //总金额
		$reqHandler->setParameter("return_url", $return_url);
		$reqHandler->setParameter("notify_url", $data_notify_url);
		$reqHandler->setParameter("body", $desc);
		$reqHandler->setParameter("bank_type", $bank_type);  	  //银行类型，默认为财付通
		//用户ip
		$reqHandler->setParameter("spbill_create_ip", get_client_ip());//客户端IP
		$reqHandler->setParameter("fee_type", $fee_type);               //币种
		$reqHandler->setParameter("subject",$desc);          //商品名称，（中介交易时必填）
		
		//系统可选参数
		$reqHandler->setParameter("sign_type", "MD5");  	 	  //签名方式，默认为MD5，可选RSA
		$reqHandler->setParameter("service_version", "1.0"); 	  //接口版本号
		$reqHandler->setParameter("input_charset", "utf-8");   	  //字符集
		$reqHandler->setParameter("sign_key_index", "1");    	  //密钥序号
		
		//业务可选参数
		$reqHandler->setParameter("attach", $attach);             	  //附件数据，原样返回就可以了
		$reqHandler->setParameter("product_fee", "");        	  //商品费用
		$reqHandler->setParameter("transport_fee", "0");      	  //物流费用
		$reqHandler->setParameter("time_start", $today);  //订单生成时间
		$reqHandler->setParameter("time_expire", "");             //订单失效时间
		$reqHandler->setParameter("buyer_id", "");                //买方财付通帐号
		$reqHandler->setParameter("goods_tag", "");               //商品标记
		$reqHandler->setParameter("trade_mode",$cmd_no);              //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
		$reqHandler->setParameter("transport_desc","");              //物流说明
		$reqHandler->setParameter("trans_type","1");              //交易类型
		$reqHandler->setParameter("agentid","");                  //平台ID
		$reqHandler->setParameter("agent_type","");               //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
		$reqHandler->setParameter("seller_id","");                //卖家的商户号
		
		
		
		//请求的URL
		$reqUrl = $reqHandler->getRequestURL();
		if($_REQUEST['v']==1){
			$debugInfo = $reqHandler->getDebugInfo();
			echo "<br/>" . $reqUrl . "<br/>";
			echo "<br/>" . $debugInfo . "<br/>";
		}
		$payLinks = '<form id="payform"  action="'.$reqHandler->getGateUrl().'" style="margin:0px;padding:0px" method="post" >';
		$params = $reqHandler->getAllParameters();
		foreach($params as $k => $v) {
			$payLinks.="<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\" />\n";
		}
		
		$payLinks .= "<input type='submit' class='paybutton' style='display:none;' />前往财付通在线支付</form>";
		
		
		
		$payLinks=$payLinks."<script>document.getElementById('payform').submit();</script>";
        return $payLinks;
	}
	
	public function response($request)
	{
		require (APP_ROOT_PATH."system/payment/Tenpay/classes/ResponseHandler.class.php");
		require (APP_ROOT_PATH."system/payment/Tenpay/classes/function.php");
		$return_res = array(
			'info'=>'',
			'status'=>false,
		);
		$payment = $GLOBALS['db']->getRow("select id,config from ".DB_PREFIX."payment where class_name='Tenpay'");  
    	$payment['config'] = unserialize($payment['config']);
    	
    	unset($_GET['act']);
		unset($_GET['ctl']);
		unset($_GET['class_name']);		
		unset($_GET['city_py']);	   
    	 
    	$resHandler = new ResponseHandler();
    	$resHandler->setKey($payment['config']['tencentpay_key']);
    	
		
		
    	//判断签名
    	if($resHandler->isTenpaySign())
    	{		

    		//通知id
    		$notify_id = $resHandler->getParameter("notify_id");
    		//商户订单号
    		$payment_notice_sn = $resHandler->getParameter("out_trade_no");
    		//财付通订单号
    		$outer_notice_sn = $resHandler->getParameter("transaction_id");
    		//金额,以分为单位
    		$total_fee = $resHandler->getParameter("total_fee");
    		//如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
    		$discount = $resHandler->getParameter("discount");
    		//支付结果
    		$trade_state = $resHandler->getParameter("trade_state");
    		//交易模式,1即时到账
    		$trade_mode = $resHandler->getParameter("trade_mode");
    			
    		
    		require_once APP_ROOT_PATH."system/libs/transaction.php";
    		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where notice_sn = '".$payment_notice_sn."'");
    		if(empty($payment_notice))
    		{
    			$logstr = "内部单号：".$payment_notice_sn."的支付单丢失，外部单号：".$outer_notice_sn." 支付时间：".to_date(NOW_TIME);
    			logger::write($logstr,logger::ERR,logger::FILE,"payment");
    			//支付单号不存在，被删除或者是非法的支付单号,提供日志类，记录日志
    			showErr("支付单不存在，非法的支付单号");
    		}
    		$pay_result = Transaction::pay_payment($payment_notice, $outer_notice_sn,"Tenpay");
    		if($pay_result['status']==1)
    		{
    			//支付成功，为订单充值，不同的支付单对同一个订单的多次支付，都要更新到订单表的online_pay中
    			//此处需要根据payment_notice的实际内容，调用不同的order处理函数，并根据不同的类型作页面跳转
    			//order_type:订单类型1.线路2.门票3.酒店 4.充值单 ...后续可扩展
    			if($payment_notice['order_type']==4)//用户充值单
    			{
    				require_once APP_ROOT_PATH."system/libs/user.php";
    				$result = User::doincharge($payment_notice);
    				if(!$result['status'])
    				{
    					logger::write($result['message'],logger::ERR,logger::FILE,"payment");
    				}
    				app_redirect(url("transaction#done",array("sn"=>$payment_notice['order_sn'],"ot"=>4))); //跳转到支付成功页面
    			}else if ($payment_notice['order_type']==1){
    				require_once APP_ROOT_PATH."system/libs/tourline.php";
    				tourline_order_paid($payment_notice['order_sn'],$payment_notice['money']);
    				app_redirect(url("transaction#done",array("sn"=>$payment_notice['order_sn'],"ot"=>1)));
    			}
    			else if($payment_notice['order_type']==2){
    				require_once APP_ROOT_PATH."system/libs/spot.php";
    				ticket_order_online_pay($payment_notice['order_sn'],$payment_notice['money']);
    				app_redirect(url("transaction#done",array("sn"=>$payment_notice['order_sn'],"ot"=>2)));
    			}
    		}
    		elseif($pay_result['status']==2)
    		{
    			//已经通知并支付过的支付号，无需处理，但需根据不同的类型作页面跳转
    			app_redirect(url("transaction#done",array("sn"=>$payment_notice['order_sn'],"ot"=>4))); //跳转到支付成功页面
    		}
    		else
    		{
    			//status==3，数据库更新失败，提供日志类，记录日志
    			$logstr = "内部单号：".$payment_notice_sn."的支付单数据库更新出错，外部单号：".$outer_notice_sn." 支付时间：".to_date(NOW_TIME)." ".$pay_result['message'];
    			logger::write($logstr,logger::ERR,logger::FILE,"payment");
    			showErr("系统繁忙，请联系客服");
    		}
    		
    	}else{
    		showErr("支付失败");
    		//showErr($resHandler->getDebugInfo() );
    	}
    	
	}
	
	public function notify($request)
	{
		require (APP_ROOT_PATH."system/payment/Tenpay/classes/ResponseHandler.class.php");
		require (APP_ROOT_PATH."system/payment/Tenpay/classes/function.php");
		$return_res = array(
			'info'=>'',
			'status'=>false,
		);
		$payment = $GLOBALS['db']->getRow("select id,config from ".DB_PREFIX."payment where class_name='Tenpay'");  
    	$payment['config'] = unserialize($payment['config']);
    	
    	unset($_GET['act']);
		unset($_GET['ctl']);
		unset($_GET['class_name']);		
		unset($_GET['city_py']);	
    	 
    	$resHandler = new ResponseHandler();
    	$resHandler->setKey($payment['config']['tencentpay_key']);
    	
    	

		if($resHandler->isTenpaySign())
		{
			
			//通知id
			$notify_id = $resHandler->getParameter("notify_id");
			//商户订单号
			$payment_notice_sn = $out_trade_no = $resHandler->getParameter("out_trade_no");
			//财付通订单号
			$outer_notice_sn = $resHandler->getParameter("transaction_id");
			//金额,以分为单位
			$total_fee = $resHandler->getParameter("total_fee");
			//如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
			$discount = $resHandler->getParameter("discount");
			//支付结果
			$trade_state = $resHandler->getParameter("trade_state");
			//交易模式,1即时到账
			$trade_mode = $resHandler->getParameter("trade_mode");
			
			require_once APP_ROOT_PATH."system/libs/transaction.php";
			$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where notice_sn = '".$payment_notice_sn."'");
			if(empty($payment_notice))
			{
				//支付单号不存在，被删除或者是非法的支付单号,提供日志类，记录日志，无需再次通知
				echo "success";
			}
			$pay_result = Transaction::pay_payment($payment_notice, $outer_notice_sn,"Tenpay");
			
			if($pay_result['status']==1)
			{
				//支付成功，为订单充值，不同的支付单对同一个订单的多次支付，都要更新到订单表的online_pay中
				//此处需要根据payment_notice的实际内容，调用不同的order处理函数
				if($payment_notice['order_type']==4)//用户充值单
				{
					require_once APP_ROOT_PATH."system/libs/user.php";
					$result = User::doincharge($payment_notice);
					if(!$result['status'])
					{
						logger::write($result['message'],logger::ERR,logger::FILE,"payment");
					}
				}
				else if($payment_notice['order_type']==2){
					require_once APP_ROOT_PATH."system/libs/spot.php";					
					$pay_status = ticket_order_online_pay($payment_notice['order_sn'],$payment_notice['money']);
					
				}else if ($payment_notice['order_type']==1){
					require_once APP_ROOT_PATH."system/libs/tourline.php";					
					$pay_status = tourline_order_paid($payment_notice['order_sn'],$payment_notice['money']);
				}		
				
				//订单支付成功后，针对虚拟即非配送商品，通知自动发货，发货单号可自行定义
				//$this->do_send_goods($payment_notice, $invoice_no);
				echo "success";
			
			}
			elseif($pay_result['status']==2)
			{
				//已经通知并支付过的支付号，无需处理
				echo "success";
			}
			else
			{
				//status==3，数据库更新失败，提供日志类，记录日志，返回失败，等待再次通知
				echo "fail";
			}
			
		}else{
		   echo "fail";
		}   
	}
	
	public function get_display_code()
	{
		$payment_item = $GLOBALS['db']->getRow("select id,logo,name,class_name from ".DB_PREFIX."payment where class_name='Tenpay'");
		if($payment_item)
		{	
			return $payment_item;
		}
		else
		{
			return '';
		}
	}
	
	
}
?>