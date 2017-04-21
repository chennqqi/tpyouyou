<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(88522820@qq.com)
// +----------------------------------------------------------------------

interface payment{	
	/**
	 * 生成支付单，并获取支付代码或提示信息
	 	 * @param array $user_data 用户数据，主要是包含id，表示user_id
		 * @param int $order_type  订单类型1.线路2.门票3.酒店 4.充值单 ...后续可扩展
		 * @param string $order_sn
		 * @param string $subject 传到支付接口的订单标题
		 * @param float $money  需支付的金额，元，入库前需转为分
	 */
	function get_payment_code($user_data,$order_type,$order_sn,$subject,$money);
	
	//响应支付
	function response($request);
	
	//响应通知
	function notify($request);
	
	//获取接口的显示
	function get_display_code();	
}
?>