<?php 
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------

//交易处理类
class Transaction{
	
		/**
		 * 产生交易单号
		 * @param array $user_data 用户数据，主要是包含id，表示user_id
		 * @param int $order_type  订单类型1.线路2.门票3.酒店 4.充值单 ...后续可扩展
		 * @param string $order_sn 订单编号
		 * @param float $money  需支付的金额，元，入库前需转为分
		 * @param boolean $is_yuan  true 元，入库前需转为分；false 分，直接入库
		 * 返回支付单的唯一单号 notice_sn
		 */
		public static function make_payment($user_data,$order_type,$order_sn,$money,$is_yuan = true)
		{
			$notice['create_time'] = NOW_TIME;
			$notice['order_type'] = $order_type;
			$notice['order_sn'] = $order_sn;
			$notice['user_id'] = $user_data['id'];
			if ($is_yuan){
				$notice['money'] = format_price_to_db($money);
			}else{
				$notice['money'] = $money;
			}
			
			do{
				$notice['notice_sn'] = to_date(NOW_TIME,"Ymdhis").rand(10,99);
				$GLOBALS['db']->autoExecute(DB_PREFIX."payment_notice",$notice,'INSERT','','SILENT');
				$notice_id = intval($GLOBALS['db']->insert_id());
			}while($notice_id==0);
			return $notice['notice_sn'];
		}
		
		/**
		 * 支付成功后，支付的回调中为支付单付款
		 * @param string $payment_notice 支付单的数据对象
		 * @param string $payment_outer_sn 外部的支付单号
		 * @param string $class 产生支付的支付接口名
		 * 
		 * 返回 array(status=>,message=>)  1.支付成功 2.已经支付 3.数据库更新失败
		 */
		public static function pay_payment($payment_notice,$payment_outer_sn,$class="")
		{
			$result = array("status"=>0,"message"=>"");
			if($class!="")
			{
				$payment = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where class_name = '".$class."'");
			}
			$payment_notice_sn = $payment_notice['notice_sn'];
			if($payment)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."payment_notice set is_paid = 1,outer_notice_sn = '".$payment_outer_sn."',pay_time = '".NOW_TIME."',payment_class='".$payment['class_name']."',payment_name = '".$payment['name']."' where is_paid = 0 and notice_sn = '".$payment_notice_sn."' ","SILENT");
			}
			else
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."payment_notice set is_paid = 1,outer_notice_sn = '".$payment_outer_sn."',pay_time = '".NOW_TIME."' where is_paid = 0 and notice_sn = '".$payment_notice_sn."' ","SILENT");
			}
			
			if($GLOBALS['db']->error()=="")
			{
				if($GLOBALS['db']->affected_rows()>0)
				{
					$result["status"] = 1;
					$result["message"] = "支付成功";					
					return $result;
				}
				else
				{
					$result["status"] = 2;
					$result["message"] = "已经支付过，重复通知了";					
					return $result;
				}
			}
			else
			{
				$result["status"] = 3;
				$result["message"] = "数据库出错".$GLOBALS['db']->error();
				return $result;
			}
			
			
		}
}
?>