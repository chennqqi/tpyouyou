<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// 后台用于权限分配的切点列表
// +----------------------------------------------------------------------

return array(
		"admin"	=> array(
			"name"	=>	"管理员",
			"node" 	=>	array(
				"index"=>array("name"=>"管理员列表","action"=>"index"),
				"set_effect"=>array("name"=>"设置生效","action"=>"set_effect"),
				"insert"=>array("name"=>"添加管理员","action"=>"insert"),
				"update"=>array("name"=>"更新管理员","action"=>"update"),
				"foreverdelete"=>array("name"=>"删除管理员","action"=>"foreverdelete"),
				"set_default"=>array("name"=>"设为默认管理员","action"=>"set_default"),
			)
		),		
		"adv"	=> array(
			"name"	=>	"广告模块",
			"node" 	=>	array(
					"index"=>array("name"=>"广告列表","action"=>"index"),
					"insert"=>array("name"=>"添加广告","action"=>"insert"),
					"update"=>array("name"=>"更新广告","action"=>"update"),
					"foreverdelete"=>array("name"=>"删除广告","action"=>"foreverdelete"),
			)
		),
		"company_tour"	=> array(
			"name"	=>	"公司旅游",
			"node" 	=>	array(
					"index"=>array("name"=>"公司旅游列表","action"=>"index"),
					"foreverdelete"=>array("name"=>"删除公司旅游","action"=>"foreverdelete"),
			)
		),
		"api_login"	=> array(
				"name"	=>	"第三方登录接口",
				"node" 	=>	array(
						"index"=>array("name"=>"接口列表","action"=>"index"),
						"insert"=>array("name"=>"添加接口","action"=>"insert"),
						"update"=>array("name"=>"更新接口","action"=>"update"),
						"uninstall"=>array("name"=>"卸载接口","action"=>"uninstall"),
				)
		),
		"cache"	=> array(
			"name"	=>	"缓存处理",
			"node" 	=>	array(
					"index"=>array("name"=>"缓存处理","action"=>"index"),
					"clear_tmpl"=>array("name"=>"清空模板缓存","action"=>"clear_tmpl"),
					"clear_data"=>array("name"=>"清空数据缓存","action"=>"clear_data"),
					"clear_image"=>array("name"=>"清空规格图片","action"=>"clear_image"),
					"clear_admin"=>array("name"=>"清空后台缓存","action"=>"clear_admin"),
			)
		),
		"conf"	=> array(
			"name"	=>	"系统配置",
			"node" 	=>	array(
					"index"=>array("name"=>"系统配置","action"=>"index"),
					"update"=>array("name"=>"更新配置","action"=>"update"),
			)
		),
		"database"	=> array(
			"name"	=>	"数据库",
			"node" 	=>	array(
					"index"=>array("name"=>"数据库备份列表","action"=>"index"),
					"dump"=>array("name"=>"备份数据","action"=>"dump"),
					"delete"=>array("name"=>"删除备份","action"=>"delete"),
					"restore"=>array("name"=>"恢复备份","action"=>"restore"),
					"sql"=>array("name"=>"SQL操作","action"=>"sql"),
			)
		),
		"dealmsglist"	=> array(
			"name"	=>	"业务队列管理",
			"node" 	=>	array(
					"index"=>array("name"=>"业务队列列表","action"=>"index"),
					"show_content"=>array("name"=>"显示内容","action"=>"show_content"),
					"send"=>array("name"=>"手动发送","action"=>"send"),
					"foreverdelete"=>array("name"=>"删除","action"=>"foreverdelete"),
			)
		),
		"file"	=> array(
			"name"	=>	"文件操作",
			"node" 	=>	array(
					"upload"=>array("name"=>"图片上传","action"=>"upload"),
					"uploadfile"=>array("name"=>"附件上传","action"=>"uploadfile"),
					"uploadflash"=>array("name"=>"flash上传","action"=>"uploadflash"),
					"uploadvideo"=>array("name"=>"flv视频上传","action"=>"uploadvideo"),
					"uploadimg"=>array("name"=>"广告图上传","action"=>"uploadimg"),
					"manage"=>array("name"=>"浏览服务器图片","action"=>"manage"),
			)
		),
		"log"	=> array(
			"name"	=>	"系统日志",
			"node" 	=>	array(
					"index"=>array("name"=>"系统日志列表","action"=>"index"),
					"view"=>array("name"=>"查看日志详情","action"=>"view"),
					"foreverdelete"=>array("name"=>"删除日志","action"=>"foreverdelete"),
			)
		),
		"mailserver"	=> array(
			"name"	=>	"邮件服务器",
			"node" 	=>	array(
					"index"=>array("name"=>"邮件服务器列表","action"=>"index"),
					"insert"=>array("name"=>"添加邮件服务器","action"=>"insert"),
					"update"=>array("name"=>"更新邮件服务器","action"=>"update"),
					"set_effect"=>array("name"=>"设置生效","action"=>"set_effect"),
					"foreverdelete"=>array("name"=>"删除","action"=>"foreverdelete"),
					"send_demo"=>array("name"=>"发送测试邮件","action"=>"send_demo"),
			)
		),
		"nav"	=> array(
			"name"	=>	"导航菜单",
			"node" 	=>	array(
					"index"=>array("name"=>"导航菜单列表","action"=>"index"),
					"insert"=>array("name"=>"添加导航","action"=>"insert"),
					"update"=>array("name"=>"更新导航","action"=>"update"),
					"set_effect"=>array("name"=>"设置生效","action"=>"set_effect"),
					"set_sort"=>array("name"=>"设置排序","action"=>"set_sort"),
					"foreverdelete"=>array("name"=>"删除","action"=>"foreverdelete"),
			)
		),
		"link"	=> array(
				"name"	=>	"友情链接",
				"node" 	=>	array(
						"index"=>array("name"=>"友情链接表","action"=>"index"),
						"insert"=>array("name"=>"添加友情链接","action"=>"insert"),
						"update"=>array("name"=>"更新友情链接","action"=>"update"),
						"set_effect"=>array("name"=>"设置生效","action"=>"set_effect"),
						"set_sort"=>array("name"=>"设置排序","action"=>"set_sort"),
						"foreverdelete"=>array("name"=>"删除","action"=>"foreverdelete"),
				)
		),
		"role"	=> array(
			"name"	=>	"权限管理",
			"node" 	=>	array(
					"index"=>array("name"=>"管理员分组列表","action"=>"index"),
					"insert"=>array("name"=>"添加角色","action"=>"insert"),
					"update"=>array("name"=>"更新角色","action"=>"update"),
					"set_effect"=>array("name"=>"设置生效","action"=>"set_effect"),
					"foreverdelete"=>array("name"=>"删除","action"=>"foreverdelete"),
			)
		),
		"sms"	=> array(
			"name"	=>	"短信接口",
			"node" 	=>	array(
					"index"=>array("name"=>"短信接口列表","action"=>"index"),
					"insert"=>array("name"=>"安装接口","action"=>"insert"),
					"update"=>array("name"=>"更新接口","action"=>"update"),
					"set_effect"=>array("name"=>"设置生效","action"=>"set_effect"),
					"uninstall"=>array("name"=>"卸载","action"=>"uninstall"),
					"send_demo"=>array("name"=>"发送测试短信","action"=>"send_demo"),
			)
		),
		"promotemsglist"	=> array(
				"name"	=>	"群发队列管理",
				"node" 	=>	array(
						"index"=>array("name"=>"业务队列列表","action"=>"index"),
						"show_content"=>array("name"=>"显示内容","action"=>"show_content"),
						"send"=>array("name"=>"手动发送","action"=>"send"),
						"foreverdelete"=>array("name"=>"删除","action"=>"foreverdelete"),
				)
		),
		"province"	=> array(
				"name"	=>	"省份配置",
				"node" 	=>	array(
						"index"=>array("name"=>"省份列表","action"=>"index"),
						"insert"=>array("name"=>"添加省份","action"=>"insert"),
						"update"=>array("name"=>"更新省份","action"=>"update"),
						"foreverdelete"=>array("name"=>"删除省份","action"=>"foreverdelete")
				)
		),
		"city"	=> array(
				"name"	=>	"城市配置",
				"node" 	=>	array(
						"index"=>array("name"=>"城市列表","action"=>"index"),
						"insert"=>array("name"=>"添加城市","action"=>"insert"),
						"update"=>array("name"=>"更新城市","action"=>"update"),
						"foreverdelete"=>array("name"=>"删除城市","action"=>"foreverdelete")
				)
		),
		"tour_city"	=> array(
				"name"	=>	"旅游城市配置",
				"node" 	=>	array(
						"index"=>array("name"=>"城市列表","action"=>"index"),
						"insert"=>array("name"=>"添加城市","action"=>"insert"),
						"update"=>array("name"=>"更新城市","action"=>"update"),
						"set_effect"=>array("name"=>"设置生效","action"=>"set_effect"),
						"set_default"=>array("name"=>"设置默认城市","action"=>"set_default"),
						"foreverdelete"=>array("name"=>"删除城市","action"=>"foreverdelete")
				)
		),
		"tour_area"	=> array(
				"name"	=>	"旅游大区管理",
				"node" 	=>	array(
						"index"=>array("name"=>"大区列表","action"=>"index"),
						"insert"=>array("name"=>"添加大区","action"=>"insert"),
						"update"=>array("name"=>"更新大区","action"=>"update"),
						"foreverdelete"=>array("name"=>"删除大区","action"=>"foreverdelete")
				)
		),
		"tour_place"	=> array(
				"name"	=>	"旅游小区管理",
				"node" 	=>	array(
						"index"=>array("name"=>"小区列表","action"=>"index"),
						"insert"=>array("name"=>"添加小区","action"=>"insert"),
						"update"=>array("name"=>"更新小区","action"=>"update"),
						"foreverdelete"=>array("name"=>"删除小区","action"=>"foreverdelete")
				)
		),
		"tour_place_tag"	=> array(
				"name"	=>	"景点标签管理",
				"node" 	=>	array(
						"index"=>array("name"=>"标签列表","action"=>"index"),
						"insert"=>array("name"=>"添加标签","action"=>"insert"),
						"update"=>array("name"=>"更新标签","action"=>"update"),
						"set_sort"=>array("name"=>"设置排序","action"=>"set_sort"),
						"foreverdelete"=>array("name"=>"删除标签","action"=>"foreverdelete")
				)
		),
		"supplier"	=> array(
				"name"	=>	"供应商管理",
				"node" 	=>	array(
						"index"=>array("name"=>"供应商列表","action"=>"index"),
						"insert"=>array("name"=>"添加供应商","action"=>"insert"),
						"update"=>array("name"=>"更新供应商","action"=>"update"),
						"set_sort"=>array("name"=>"设置排序","action"=>"set_sort"),
						"foreverdelete"=>array("name"=>"删除城市","action"=>"foreverdelete")
				)
		),
		"user_group"	=> array(
				"name"	=>	"会员组管理",
				"node" 	=>	array(
						"index"=>array("name"=>"会员组列表","action"=>"index"),
						"insert"=>array("name"=>"添加会员组","action"=>"insert"),
						"update"=>array("name"=>"更新会员组","action"=>"update"),
						"foreverdelete"=>array("name"=>"删除会员组","action"=>"foreverdelete")
				)
		),
		"user_level"	=> array(
				"name"	=>	"会员等级管理",
				"node" 	=>	array(
						"index"=>array("name"=>"会员等级列表","action"=>"index"),
						"insert"=>array("name"=>"添加会员等级","action"=>"insert"),
						"update"=>array("name"=>"更新会员等级","action"=>"update"),
						"foreverdelete"=>array("name"=>"删除会员等级","action"=>"foreverdelete")
				)
		),
		
		"user_incharge"	=> array(
				"name"	=>	"会员充值管理",
				"node" 	=>	array(
						"index"=>array("name"=>"会员充值列表","action"=>"index"),
						"foreverdelete"=>array("name"=>"删除","action"=>"foreverdelete")
				)
		),
		
		"user_deposit"	=> array(
				"name"	=>	"会员提现管理",
				"node" 	=>	array(
						"index"=>array("name"=>"会员充值列表","action"=>"index"),
						"dodeposit"=>array("name"=>"确认提现","action"=>"dodeposit"),
						"foreverdelete"=>array("name"=>"删除","action"=>"foreverdelete")
				)
		),
		
		"user_msg"	=> array(
				"name"	=>	"会员消息管理",
				"node" 	=>	array(
						"index"=>array("name"=>"会员消息列表","action"=>"index"),
						"foreverdelete"=>array("name"=>"删除","action"=>"foreverdelete")
				)
		),
		
		"rebate"	=> array(
				"name"	=>	"返利记录",
				"node" 	=>	array(
						"index"=>array("name"=>"返利记录列表","action"=>"index"),
						"foreverdelete"=>array("name"=>"删除","action"=>"foreverdelete")
				)
		),
		
		"voucher_type"	=> array(
				"name"	=>	"代金券设置",
				"node" 	=>	array(
						"index"=>array("name"=>"代金券列表","action"=>"index"),
						"insert"=>array("name"=>"添加代金券","action"=>"insert"),
						"update"=>array("name"=>"更新代金券","action"=>"update"),
						"set_effect"=>array("name"=>"设置生效","action"=>"set_effect"),
						"set_sort"=>array("name"=>"设置排序","action"=>"set_sort"),
						"foreverdelete"=>array("name"=>"删除代金券","action"=>"foreverdelete")
				)
		),		
		"voucher"	=> array(
				"name"	=>	"已发放代金券管理",
				"node" 	=>	array(
						"index"=>array("name"=>"代金券列表","action"=>"index"),						
						"set_effect"=>array("name"=>"设置生效","action"=>"set_effect"),					
						"foreverdelete"=>array("name"=>"删除代金券","action"=>"foreverdelete")
				)
		),
		"return_conf"	=> array(
				"name"	=>	"返利设置",
				"node" 	=>	array(
						"index"=>array("name"=>"查看返利设置","action"=>"index"),
						"update"=>array("name"=>"设置返利","action"=>"update")
				)
		),
		"payment"	=> array(
				"name"	=>	"支付接口",
				"node" 	=>	array(
						"index"=>array("name"=>"支付接口列表","action"=>"index"),
						"insert"=>array("name"=>"安装支付接口","action"=>"insert"),
						"update"=>array("name"=>"更新支付接口","action"=>"update"),
						"viewlog"=>array("name"=>"查看记录","action"=>"viewlog"),
						"set_effect"=>array("name"=>"设置生效","action"=>"set_effect"),
						"set_sort"=>array("name"=>"设置排序","action"=>"set_sort"),
						"uninstall"=>array("name"=>"卸载接口","action"=>"uninstall")
				)
		),
		"payment_notice"	=> array(
				"name"	=>	"支付单记录",
				"node" 	=>	array(
						"index"=>array("name"=>"支付单列表","action"=>"index"),					
						"foreverdelete"=>array("name"=>"删除代支付单","action"=>"foreverdelete")
				)
		),
		"mail_msg"	=> array(
				"name"	=>	"邮件群发管理",
				"node" 	=>	array(
						"index"=>array("name"=>"邮件列表","action"=>"index"),
						"insert"=>array("name"=>"邮件添加","action"=>"insert"),
						"update"=>array("name"=>"邮件更新","action"=>"update"),
						"foreverdelete"=>array("name"=>"删除邮件","action"=>"foreverdelete")
				)
		),
		"msg_template"	=> array(
				"name"	=>	"消息模板设置",
				"node" 	=>	array(
						"index"=>array("name"=>"模板设置","action"=>"index"),						
						"update"=>array("name"=>"更新设置","action"=>"update"),
				)
		),
		"sms_msg"	=> array(
				"name"	=>	"短信群发管理",
				"node" 	=>	array(
						"index"=>array("name"=>"短信列表","action"=>"index"),
						"insert"=>array("name"=>"短信添加","action"=>"insert"),
						"update"=>array("name"=>"短信更新","action"=>"update"),
						"foreverdelete"=>array("name"=>"删除短信","action"=>"foreverdelete")
				)
		),
		"spot"	=> array(
				"name"	=>	"景点门票设置",
				"node" 	=>	array(
						"index"=>array("name"=>"景点门票","action"=>"index"),			
						"insert"=>array("name"=>"添加设置","action"=>"insert"),			
						"update"=>array("name"=>"更新设置","action"=>"update"),
				)
		),
		"spot_cate"	=> array(
				"name"	=>	"景点分类设置",
				"node" 	=>	array(
						"index"=>array("name"=>"景点分类","action"=>"index"),			
						"insert"=>array("name"=>"添加设置","action"=>"insert"),			
						"update"=>array("name"=>"更新设置","action"=>"update"),
				)
		),
		"spot_supplier"	=> array(
				"name"	=>	"景点提交设置",
				"node" 	=>	array(
						"index"=>array("name"=>"提交列表","action"=>"index"),				
						"update"=>array("name"=>"发布设置","action"=>"update"),
				)
		),
		"system_msg"	=> array(
				"name"	=>	"消息群发管理",
				"node" 	=>	array(
						"index"=>array("name"=>"消息列表","action"=>"index"),
						"insert"=>array("name"=>"消息添加","action"=>"insert"),
						"update"=>array("name"=>"消息更新","action"=>"update"),
						"foreverdelete"=>array("name"=>"删除消息","action"=>"foreverdelete")
				)
		),
		"tuan"	=> array(
				"name"	=>	"团购列表设置",
				"node" 	=>	array(
						"index"=>array("name"=>"提交列表","action"=>"index"),	
						"insert"=>array("name"=>"添加设置","action"=>"insert"),						
						"update"=>array("name"=>"发布设置","action"=>"update"),
				)
		),
		"tuan_cate"	=> array(
				"name"	=>	"团购分类设置",
				"node" 	=>	array(
						"index"=>array("name"=>"提交列表","action"=>"index"),	
						"insert"=>array("name"=>"添加设置","action"=>"insert"),						
						"update"=>array("name"=>"发布设置","action"=>"update"),
				)
		),
		"news_cate"	=> array(
				"name"	=>	"资讯分类设置",
				"node" 	=>	array(
						"index"=>array("name"=>"分类列表","action"=>"index"),
						"insert"=>array("name"=>"添加分类","action"=>"insert"),
						"update"=>array("name"=>"更新分类","action"=>"update"),
						"set_recommend"=>array("name"=>"推荐到资讯首页","action"=>"set_recommend"),
						"set_focus"=>array("name"=>"推荐为资讯公告","action"=>"set_focus"),
						"set_index"=>array("name"=>"推荐到网站首页","action"=>"set_index"),
						"set_sort"=>array("name"=>"设置排序","action"=>"set_sort"),
						"foreverdelete"=>array("name"=>"删除分类","action"=>"foreverdelete")
				)
		),
		"news"	=> array(
				"name"	=>	"资讯设置",
				"node" 	=>	array(
						"index"=>array("name"=>"资讯列表","action"=>"index"),
						"insert"=>array("name"=>"添加资讯","action"=>"insert"),
						"update"=>array("name"=>"更新资讯","action"=>"update"),
						"set_recommend"=>array("name"=>"推荐到首页","action"=>"set_recommend"),
						"set_sort"=>array("name"=>"设置排序","action"=>"set_sort"),
						"foreverdelete"=>array("name"=>"删除分类","action"=>"foreverdelete")
				)
		),
		"help_cate"	=> array(
				"name"	=>	"帮助分类设置",
				"node" 	=>	array(
						"index"=>array("name"=>"分类列表","action"=>"index"),
						"insert"=>array("name"=>"添加分类","action"=>"insert"),
						"update"=>array("name"=>"更新分类","action"=>"update"),					
						"set_sort"=>array("name"=>"设置排序","action"=>"set_sort"),
						"foreverdelete"=>array("name"=>"删除分类","action"=>"foreverdelete")
				)
		),
		"help"	=> array(
				"name"	=>	"帮助设置",
				"node" 	=>	array(
						"index"=>array("name"=>"帮助列表","action"=>"index"),
						"insert"=>array("name"=>"添加帮助","action"=>"insert"),
						"update"=>array("name"=>"更新帮助","action"=>"update"),
						"set_effect"=>array("name"=>"设置生效","action"=>"set_effect"),
						"set_sort"=>array("name"=>"设置排序","action"=>"set_sort"),
						"foreverdelete"=>array("name"=>"删除帮助","action"=>"foreverdelete")
				)
		),
		"user"	=> array(
				"name"	=>	"会员管理",
				"node" 	=>	array(
						"index"=>array("name"=>"会员列表","action"=>"index"),
						"export_csv"=>array("name"=>"会员导出","action"=>"export_csv"),
						"insert"=>array("name"=>"添加会员","action"=>"insert"),
						"update"=>array("name"=>"更新会员","action"=>"update"),
						"set_effect"=>array("name"=>"设置生效","action"=>"set_effect"),
						"account"=>array("name"=>"会员帐户查看","action"=>"account"),
						"do_op_account"=>array("name"=>"帐户变更","action"=>"do_op_account"),
						"account_log"=>array("name"=>"帐户变更日志","action"=>"account_log"),
						"del_account_log"=>array("name"=>"删除帐户变更日志","action"=>"del_account_log"),
						"foreverdelete"=>array("name"=>"删除会员","action"=>"foreverdelete")
				)
		),
		"user_conf"	=> array(
				"name"	=>	"会员设置",
				"node" 	=>	array(
						"index"=>array("name"=>"查看会员设置","action"=>"index"),
						"update"=>array("name"=>"提交设置","action"=>"update")
				)
		),
		"spot_order"	=> array(
				"name"	=>	"门票订单",
				"node" 	=>	array(
						"index"=>array("name"=>"订单列表","action"=>"index"),
						"export_csv"=>array("name"=>"导出","action"=>"export_csv"),
						"del_order"=>array("name"=>"删除订单","action"=>"del_order"),
						"do_delivery"=>array("name"=>"门票发货","action"=>"do_delivery"),
						"do_order_status"=>array("name"=>"审核订单","action"=>"do_order_status"),
						"do_re_appoint_status"=>array("name"=>"门票改签","action"=>"do_re_appoint_status"),
						"do_refund_status"=>array("name"=>"门票退票","action"=>"do_refund_status"),
						"pay_order"=>array("name"=>"订单收款","action"=>"pay_order"),
				)
		),
		"tourline"	=> array(
				"name"	=>	"旅游线路",
				"node" 	=>	array(
						"index"=>array("name"=>"线路列表","action"=>"index"),
						"insert"=>array("name"=>"添加线路","action"=>"insert"),
						"update"=>array("name"=>"更新线路","action"=>"update"),
						"set_effect"=>array("name"=>"设置生效","action"=>"set_effect"),
						"set_sort"=>array("name"=>"设置排序","action"=>"set_effect"),
						"foreverdelete"=>array("name"=>"删除线路","action"=>"foreverdelete"),
						"export_csv"=>array("name"=>"导出","action"=>"export_csv")
				)
		),
		"tourline_insurance"	=> array(
				"name"	=>	"旅游保险",
				"node" 	=>	array(
						"index"=>array("name"=>"保险列表","action"=>"index"),
						"insert"=>array("name"=>"添加保险","action"=>"insert"),
						"update"=>array("name"=>"更新保险","action"=>"update"),
						"foreverdelete"=>array("name"=>"删除保险","action"=>"foreverdelete"),
				)
		),
		"tourline_supplier"	=> array(
				"name"	=>	"商家提交",
				"node" 	=>	array(
						"index"=>array("name"=>"保险列表","action"=>"index"),
						"set_sort"=>array("name"=>"设置排序","action"=>"set_effect"),
						"set_sort"=>array("name"=>"设置排序","action"=>"set_effect"),
						"foreverdelete"=>array("name"=>"删除保险","action"=>"foreverdelete"),
				)
		),
		"tourline_rec_config"	=> array(
				"name"	=>	"线路推荐列表",
				"node" 	=>	array(
						"index"=>array("name"=>"线路推荐列表","action"=>"index"),
						"insert"=>array("name"=>"添加线路推荐","action"=>"insert"),
						"update"=>array("name"=>"更新线路推荐","action"=>"update"),
						"set_sort"=>array("name"=>"设置排序","action"=>"set_effect"),
						"foreverdelete"=>array("name"=>"删除推荐列表","action"=>"foreverdelete"),
				)
		),
		"tourline_order"	=> array(
				"name"	=>	"线路订单",
				"node" 	=>	array(
						"index"=>array("name"=>"订单列表","action"=>"index"),
						"export_csv"=>array("name"=>"导出","action"=>"export_csv"),
						"del_order"=>array("name"=>"删除订单","action"=>"del_order"),
						"do_order_status"=>array("name"=>"审核订单","action"=>"do_order_status"),
						"do_refund_status"=>array("name"=>"订单退款","action"=>"do_refund_status"),
						"pay_order"=>array("name"=>"订单收款","action"=>"pay_order"),
				)
		),
		"drop_nav"	=> array(
				"name"	=>	"下拉导航",
				"node" 	=>	array(
						"index"=>array("name"=>"下拉导航列表","action"=>"index"),
						"insert"=>array("name"=>"添加下拉导航","action"=>"insert"),
						"update"=>array("name"=>"更新下拉导航","action"=>"update"),
						"set_sort"=>array("name"=>"设置排序","action"=>"set_effect"),
						"foreverdelete"=>array("name"=>"删除下拉导航","action"=>"foreverdelete"),
				)
		),
                "review"        =>array(
                                "name"  =>      "用户点评",
                                "node"  =>      array(
                                                "index"=>array("name"=>"用户点评列表","action"=>"index"),
						"check_save"=>array("name"=>"点评审核","action"=>"check_save"),
						"foreverdelete"=>array("name"=>"删除点评","action"=>"foreverdelete"),
                                )
                ),
                "review_field"        =>array(
                                "name"  =>      "点评字段配置",
                                "node"  =>      array(
                                                "index"=>array("name"=>"点评字段列表","action"=>"index"),
						"insert"=>array("name"=>"新增点评字段","action"=>"insert"),
						"update"=>array("name"=>"更新点评字段","action"=>"update"),
                                )
                ),
                "review_conf"        =>array(
                                "name"  =>      "点评设置",
                                "node"  =>      array(
                                                "index"=>array("name"=>"点评设置","action"=>"index"),
						"save"=>array("name"=>"点评配置保存","action"=>"save"),
                                )
                ),
                "comment"        =>array(
                                "name"  =>      "评论管理",
                                "node"  =>      array(
                                                "index"=>array("name"=>"评论列表","action"=>"index"),
						"foreverdelete"=>array("name"=>"评论删除","action"=>"foreverdelete"),
                                )
                ),
                "comment_conf"        =>array(
                                "name"  =>      "评论设置",
                                "node"  =>      array(
                                                "index"=>array("name"=>"评论设置","action"=>"index"),
						"save"=>array("name"=>"评论设置保存","action"=>"save"),
                                )
                ),
                "tour_guide"        =>array(
                                "name"  =>      "游记管理",
                                "node"  =>      array(
                                                "index"=>array("name"=>"评论设置","action"=>"index"),
						"check"=>array("name"=>"审核游记","action"=>"check"),
                                                "check_list"=>array("name"=>"游记待审核列表","action"=>"check_list"),
                                                "foreverdelete"=>array("name"=>"永久删除游记","action"=>"foreverdelete"),
                                                "foreverdelete_2"=>array("name"=>"永久删除未审核游记","action"=>"foreverdelete_2"),
                                                "is_hot"=>array("name"=>"是否最热","action"=>"is_hot"),
                                                "is_index"=>array("name"=>"是否首页显示","action"=>"is_index"),
                                                "is_recommend"=>array("name"=>"是否推荐","action"=>"is_recommend"),
                                )
                ),
                "tour_guide_conf"        =>array(
                                "name"  =>      "评论设置",
                                "node"  =>      array(
                                                "index"=>array("name"=>"游记设置","action"=>"index"),
						"save"=>array("name"=>"游记设置保存","action"=>"save"),
                                )
                ),
                "ask"        =>array(
                                "name"  =>      "问答管理",
                                "node"  =>      array(
                                                "index"=>array("name"=>"问答列表","action"=>"index"),
						"save"=>array("name"=>"问答回复","action"=>"save_ask"),
                                                "foreverdelete"=>array("name"=>"永久删除问答","action"=>"foreverdelete"),
                                )
                ),
                "ask_conf"        =>array(
                                "name"  =>      "问答设置",
                                "node"  =>      array(
                                                "index"=>array("name"=>"问答设置","action"=>"index"),
						"update"=>array("name"=>"保存问答设置","action"=>"update"),
                                )
                ),
                "ask_type"        =>array(
                                "name"  =>      "问答类型管理",
                                "node"  =>      array(
                                                "index"=>array("name"=>"问答类型列表","action"=>"index"),
						"insert"=>array("name"=>"新增问答类型","action"=>"insert"),
						"update"=>array("name"=>"更新问答类型","action"=>"update"),
                                )
                ),
                "word"        =>array(
                                "name"  =>      "敏感词管理",
                                "node"  =>      array(
                                                "index"=>array("name"=>"敏感词列表","action"=>"index"),
						"save_word"=>array("name"=>"保存敏感词","action"=>"save_word"),
                                                "set_status"=>array("name"=>"敏感词状态","action"=>"set_status"),
						"foreverdelete"=>array("name"=>"永久删除敏感词","action"=>"foreverdelete"),
                                )
                ),
                "word_type"        =>array(
                                "name"  =>      "敏感词分类管理",
                                "node"  =>      array(
                                                "index"=>array("name"=>"敏感词分类列表","action"=>"index"),
						"insert"=>array("name"=>"新增敏感词分类","action"=>"insert"),
                                                "update"=>array("name"=>"更新敏感词分类","action"=>"update"),
						"foreverdelete"=>array("name"=>"永久删除敏感词分类","action"=>"foreverdelete"),
                                )
                ),
);
?>