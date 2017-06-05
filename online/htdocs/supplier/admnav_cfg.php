<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// 后台结点配置
// +----------------------------------------------------------------------

return array(
		'验证管理'=>array(
				'线路报名验证'	=>	array(
						array("name"=>"线路报名验证","module"=>"tourline","action"=>"verify")
				),
				'门票验证'	=>	array(
						array("name"=>"门票验证","module"=>"ticket","action"=>"verify")
				)
		),
		'旅游产品管理'=>array(
				'线路管理'	=>	array(
						array("name"=>"销售中的线路","module"=>"tourline","action"=>"index"),
						array("name"=>"审核中的线路","module"=>"tourline_supplier","action"=>"index"),
						array("name"=>"线路订单","module"=>"tourline_order","action"=>"index"),
						array("name"=>"待处理订单","module"=>"tourline_order","action"=>"new_index"),
				),
				'门票管理'	=>	array(
						array("name"=>"销售中的景点门票","module"=>"ticket","action"=>"index"),
						array("name"=>"审核中的景点门票","module"=>"ticket_supplier","action"=>"index"),
						array("name"=>"门票订单","module"=>"spot_order","action"=>"index"),
						//array("name"=>"出票记录","module"=>"ticket_list","action"=>"index"),
						array("name"=>"待处理订单","module"=>"spot_order","action"=>"new_index"),
				),
				'酒店管理'	=>	array(
						array("name"=>"销售中的酒店","module"=>"hotel","action"=>"index"),
						array("name"=>"审核中的酒店","module"=>"hotel_supplier","action"=>"index"),
						array("name"=>"酒店订单","module"=>"hotel_order","action"=>"index")
				),
				'邮轮管理'	=>	array(
						array("name"=>"销售中的邮轮仓位","module"=>"cruise","action"=>"index"),
						array("name"=>"审核中的邮轮仓位","module"=>"cruise_supplier","action"=>"index"),
						array("name"=>"邮轮订单","module"=>"cruise_order","action"=>"index"),
						array("name"=>"待处理订单","module"=>"cruise_order","action"=>"new_index"),
				)
		),
		'商家设置'=>array(
				'资料管理'	=>	array(
						array("name"=>"资料管理","module"=>"supplier","action"=>"index")
				),	
				'运费设置'	=>	array(
						array("name"=>"运费设置","module"=>"freight","action"=>"index")
				)
		)
		
		
);
?>