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
		'旅游产品管理'=>array(
				'线路管理'	=>	array(
						array("name"=>"旅游线路","module"=>"tourline","action"=>"index"),
						array("name"=>"旅游保险","module"=>"tourline_insurance","action"=>"index"),
						array("name"=>"旅游订单","module"=>"tourline_order","action"=>"index"),
						array("name"=>"商家提交","module"=>"tourline_supplier","action"=>"index"),
						array("name"=>"公司旅游","module"=>"company_tour","action"=>"index")
				),
				'线路频道推荐设置'	=>	array(
						array("name"=>"线路推荐列表","module"=>"tourline_rec_config","action"=>"index"),
				),
				'景点门票管理'	=>	array(
						array("name"=>"景点门票","module"=>"spot","action"=>"index"),
						array("name"=>"景点分类","module"=>"spot_cate","action"=>"index"),
						array("name"=>"门票订单","module"=>"spot_order","action"=>"index"),
						array("name"=>"商家提交","module"=>"spot_supplier","action"=>"index"),
				),
				/*
				'酒店管理'	=>	array(
						array("name"=>"酒店","module"=>"hotel","action"=>"index"),
						array("name"=>"酒店订单","module"=>"hotel_order","action"=>"index"),
						array("name"=>"商家提交","module"=>"hotel_supplier","action"=>"index"),				
				),*/
				'团购管理'	=>	array(
						array("name"=>"团购列表","module"=>"tuan","action"=>"index"),
						array("name"=>"团购分类","module"=>"tuan_cate","action"=>"index"),
				),
				'订单管理'	=>	array(
						array("name"=>"所有订单","module"=>"order","action"=>"index"),						
				),				
		),
		'会员管理'=>array(
				'会员管理'	=>	array(
						array("name"=>"会员列表","module"=>"user","action"=>"index"),
						array("name"=>"会员组","module"=>"user_group","action"=>"index"),
						array("name"=>"会员等级","module"=>"user_level","action"=>"index"),
						array("name"=>"会员充值","module"=>"user_incharge","action"=>"index"),
						array("name"=>"会员提现","module"=>"user_deposit","action"=>"index"),
						array("name"=>"会员消息","module"=>"user_msg","action"=>"index"),
						array("name"=>"会员配置","module"=>"user_conf","action"=>"index"),
				),
				'会员点评'	=>	array(
						array("name"=>"点评列表","module"=>"review","action"=>"index"),
						array("name"=>"点评字段配置","module"=>"review_field","action"=>"index"),
						array("name"=>"点评设置","module"=>"review_conf","action"=>"index"),
				),
				'会员评论'	=>	array(
						array("name"=>"评论列表","module"=>"comment","action"=>"index"),
						array("name"=>"评论设置","module"=>"comment_conf","action"=>"index"),
				),
				'会员游记'	=>	array(
						array("name"=>"游记列表","module"=>"tour_guide","action"=>"index"),
                                                array("name"=>"待审核游记列表","module"=>"tour_guide","action"=>"check_list"),
						array("name"=>"游记设置","module"=>"tour_guide_conf","action"=>"index"),
				),
				'会员问答'	=>	array(
						array("name"=>"会员提问","module"=>"ask","action"=>"index"),
						array("name"=>"问答类型","module"=>"ask_type","action"=>"index"),
						array("name"=>"问答设置","module"=>"ask_conf","action"=>"index"),
				),
				'会员整合'	=>	array(
						array("name"=>"会员整合设置","module"=>"integrate","action"=>"index"),
						array("name"=>"第三方登录","module"=>"api_login","action"=>"index"),
				),
				'群发管理'	=>	array(
						array("name"=>"消息群发","module"=>"system_msg","action"=>"index"),
						array("name"=>"邮件群发","module"=>"mail_msg","action"=>"index"),
						array("name"=>"短信群发","module"=>"sms_msg","action"=>"index"),
				),
		),
		'资讯文章管理'=>array(
				'资讯文章管理'	=>	array(
						array("name"=>"资讯列表","module"=>"news","action"=>"index"),
						array("name"=>"资讯分类","module"=>"news_cate","action"=>"index"),
				),
				'帮助管理'	=>	array(
						array("name"=>"帮助列表","module"=>"help","action"=>"index"),
						array("name"=>"帮助分类","module"=>"help_cate","action"=>"index"),
				)
		),
		'接口管理'=>array(
				'支付接口管理'	=>	array(
						array("name"=>"支付接口","module"=>"payment","action"=>"index"),
						array("name"=>"支付记录","module"=>"payment_notice","action"=>"index"),
				),				
				'邮件管理'	=>	array(
						array("name"=>"邮件服务器列表","module"=>"mailserver","action"=>"index")
				),
				'短信管理'	=>	array(
						array("name"=>"短信接口列表","module"=>"sms","action"=>"index")
				),
				'消息模板管理'	=>	array(
						array("name"=>"模板管理","module"=>"msg_template","action"=>"index")
				),
		),
		'营销策略'=>array(
				'代金券管理'	=>	array(
						array("name"=>"代金券设置","module"=>"voucher_type","action"=>"index"),
						array("name"=>"代金券发放记录","module"=>"voucher","action"=>"index"),
				),
				'返利设置'	=>	array(
						array("name"=>"返利设置","module"=>"return_conf","action"=>"index"),
						array("name"=>"返利记录","module"=>"rebate","action"=>"index"),
				)
		),
		'供应商管理'=>array(
				'供应商管理'	=>	array(
						array("name"=>"供应商管理","module"=>"supplier","action"=>"index"),
				),
		),
		'系统设置'=>array(
				'系统设置'	=>	array(
						array("name"=>"系统设置","module"=>"conf","action"=>"index")
				),		
				'管理员'	=>	array(
						array("name"=>"管理员分组列表","module"=>"role","action"=>"index"),
						array("name"=>"管理员列表","module"=>"admin","action"=>"index"),
				),
				'数据库操作'	=>	array(
						array("name"=>"数据库备份","module"=>"database","action"=>"index"),
						array("name"=>"SQL操作","module"=>"database","action"=>"sql"),
				),
				'系统日志'	=>	array(
						array("name"=>"系统日志列表","module"=>"log","action"=>"index"),
				),
				'导航设置'	=>	array(
						array("name"=>"导航菜单","module"=>"nav","action"=>"index"),
						array("name"=>"下拉导航","module"=>"drop_nav","action"=>"index"),
				),
				
				'友情链接设置'	=>	array(
						array("name"=>"友情链接","module"=>"link","action"=>"index"),
				),
				'广告设置'	=>	array(
						array("name"=>"页面广告列表","module"=>"adv","action"=>"index"),
				),
				'配送城市设置'	=>	array(
						array("name"=>"省份设置","module"=>"province","action"=>"index"),
						array("name"=>"城市设置","module"=>"city","action"=>"index"),
				),
				'旅游地区设置'	=>	array(
						array("name"=>"省份设置","module"=>"tour_province","action"=>"index"),
						array("name"=>"城市设置","module"=>"tour_city","action"=>"index"),
						array("name"=>"大区设置","module"=>"tour_area","action"=>"index"),
						array("name"=>"小区设置","module"=>"tour_place","action"=>"index"),
						array("name"=>"小区域标签","module"=>"tour_place_tag","action"=>"index"),
				),
				'队列管理'	=>	array(
						array("name"=>"业务队列列表","module"=>"dealmsglist","action"=>"index"),
						array("name"=>"群发队列列表","module"=>"promotemsglist","action"=>"index"),
				),
                                '敏感词管理'    =>      array(
                                                array("name"=>"敏感词列表","module"=>"word","action"=>"index"),
                                                array("name"=>"敏感词分类列表","module"=>"word_type","action"=>"index"),
                                                
                                ),
		)
		
);
?>