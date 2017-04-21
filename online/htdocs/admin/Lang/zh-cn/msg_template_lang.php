<?php
return array(
		"TPL_MAIL_USER_VERIFY"	=>	'注册验证邮件模板',
		"TPL_MAIL_USER_VERIFY_VAR_DESC"	=>	'{$user.user_name} 用户名, {$user.verify_url} 验证地址',
		"TPL_SMS_USER_VERIFY"	=>	'手机注册验证模板',
		"TPL_SMS_USER_VERIFY_VAR_DESC"	=>	'{$user.user_name} 用户名, {$user.mobile} 手机号, {$user.code} 验证码',
		"TPL_MAIL_USER_GETPWD"	=>	'会员重置密码邮件模板',
		"TPL_MAIL_USER_GETPWD_VAR_DESC"	=>	'{$user.user_name} 用户名, {$user.verify_url} 重置地址',
		"TPL_SMS_USER_GETPWD"	=>	'会员重置密码短信模板',
		"TPL_SMS_USER_GETPWD_VAR_DESC"	=>	'{$user.user_name} 用户名, {$user.mobile} 手机号, {$user.code} 验证码',
		"TPL_SMS_MODIFY"	=>	'会员手机重置模板',
		"TPL_SMS_MODIFY_VAR_DESC"	=>	'{$user.user_name} 用户名, {$user.mobile} 手机号, {$user.code} 验证码',
		"TPL_MAIL_MODIFY"	=>	'会员邮箱重置模板',
		"TPL_MAIL_MODIFY_VAR_DESC"	=>	'{$user.user_name} 用户名, {$user.email} 新邮箱地址, {$user.verify_url} 验证地址',
		
		
		"TPL_SMS_ORDER"	=>	'下单成功短信模板',
		"TPL_SMS_ORDER_VAR_DESC"	=>	'{$order.user_name}用户名,{$order.order_sn}订单编号,{$order.pay_amount_format}支付金额, {$order.pay_time_format}支付时间,{$order.supplier_phone}商家电话',
		"TPL_MAIL_ORDER"	=>	'下单成功邮箱模板',
		"TPL_MAIL_ORDER_VAR_DESC"	=>	'{$order.user_name}用户名,{$order.order_sn}订单编号,{$order.pay_amount_format}支付金额, {$order.pay_time_format}支付时间',
		
		"TPL_SMS_ORDER_REFUND"	=>	'订单退款短信模板',
		"TPL_SMS_ORDER_REFUND_VAR_DESC"	=>	'{$order.user_name}用户名,{$order.order_sn}订单编号',
		"TPL_MAIL_ORDER_REFUND"	=>	'订单退款邮箱模板',
		"TPL_MAIL_ORDER_REFUND_VAR_DESC"	=>	'{$order.user_name}用户名,{$order.order_sn}订单编号',
		
		"TPL_SMS_ORDER_REJECT_REFUND"	=>	'拒绝退款短信模板',
		"TPL_SMS_ORDER_REJECT_REFUND_VAR_DESC"	=>	'{$order.user_name}用户名,{$order.order_sn}订单编号,{$order.refuse_reason}拒绝原因',
		"TPL_MAIL_ORDER_REJECT_REFUND"	=>	'拒绝退款邮箱模板',
		"TPL_MAIL_ORDER_REJECT_REFUND_VAR_DESC"	=>	'{$order.user_name}用户名,{$order.order_sn}订单编号,{$order.refuse_reason}拒绝原因',
		
		
		"TPL_SMS_USE_COUPON"	=>	'验证码使用短信模板',
		"TPL_SMS_USE_COUPON_VAR_DESC"	=>	'{$user_name}用户名,{$verify_code}验证码,{$verify_time_format}验证时间',
		"TPL_MAIL_USE_COUPON"	=>	'验证码使用邮箱模板',
		"TPL_MAIL_USE_COUPON_VAR_DESC"	=>	'{$user_name}用户名,{$verify_code}验证码,{$verify_time_format}验证时间',
		
		"TPL_SMS_ORDER_DELIVERY"	=>	'订单发货短信模板',
		"TPL_SMS_ORDER_DELIVERY_VAR_DESC"	=>	'{$order.user_name}用户名,{$order.order_sn}订单编号,{$order.delivery_time_format}发货日期,{$order.delivery_sn}发货单号',
		"TPL_MAIL_ORDER_DELIVERY"	=>	'订单发货邮箱模板',
		"TPL_MAIL_ORDER_DELIVERY_VAR_DESC"	=>	'{$order.user_name}用户名,{$order.order_sn}订单编号,{$order.delivery_time_format}发货日期,{$order.delivery_sn}发货单号',
		

		"TPL_SMS_ORDER_RE_APPOINT"	=>	'订单改签短信模板',
		"TPL_SMS_ORDER_RE_APPOINT_VAR_DESC"	=>	'{$order.user_name}用户名,{$order.order_sn}订单编号,{$order.appoint_time_format}改签时间',
		"TPL_MAIL_ORDER_RE_APPOINT"	=>	'订单改签邮箱模板',
		"TPL_MAIL_ORDER_RE_APPOINT_VAR_DESC"	=>	'{$order.user_name}用户名,{$order.order_sn}订单编号,{$order.appoint_time_format}改签时间',

		"TPL_SMS_ORDER_REJECT_RE_APPOINT"	=>	'拒绝改签短信模板',
		"TPL_SMS_ORDER_REJECT_RE_APPOINT_VAR_DESC"	=>	'{$order.user_name}用户名,{$order.order_sn}订单编号,{$order.re_appoint_refuse_reason}拒绝原因',
		"TPL_MAIL_ORDER_REJECT_RE_APPOINT"	=>	'拒绝改签邮箱模板',
		"TPL_MAIL_ORDER_REJECT_RE_APPOINT_VAR_DESC"	=>	'{$order.user_name}用户名,{$order.order_sn}订单编号,{$order.re_appoint_refuse_reason}拒绝原因',		
		
		"TPL_SMS_SUPPLIER_ORDER"	=>	'商家订单提醒短信',
		"TPL_SMS_SUPPLIER_ORDER_VAR_DESC" =>	'{$supplier_name}商家名称，{$product_name}预订的产品名称，{$order_sn}订单号',
		"TPL_MAIL_SUPPLIER_ORDER"	=>	'商家订单提醒邮件',
		"TPL_MAIL_SUPPLIER_ORDER_VAR_DESC" =>	'{$supplier_name}商家名称，{$product_name}预订的产品名称，{$order_sn}订单号',
		);
?>