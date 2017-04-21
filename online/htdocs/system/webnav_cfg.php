<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// 前端可配置的导航菜单
// +----------------------------------------------------------------------

return array(
				'index' => array(
						'name'	=>	'首页',  //首页
				),
				'spot' => array(  //景点门票
						'name'	=>	'景点门票',
						'acts'	=> array(
								'index'	=>	'景点门票首页',
								'cat'	=>	'景点门票列表',
						),
				),
                 'guide'=>array(
                           'name'=>'游记',
                           'acts'	=> array(
								'index'	=>	'游记首页',
								'writethread'	=>'发布游记',
						),
                 ),
				'user' => array(  //会员模块
						'name'	=>	'会员模块',
						'acts'	=> array(
								'login'	=>	'会员登录',
								'regist'	=>	'会员注册',
						),
				),
				'help' => array(  //帮助中心
						'name'	=>	'帮助中心',
						'acts'	=> array(
								'index'	=>	'帮助中心首页',
								'show'	=>	'帮助中心内容',
						),
				),
				'link' => array(  //友情链接
						'name'	=>	'友情链接',
						'acts'	=> array(
								'index'	=>	'友情链接',
						),
				),
				'news' => array(  //新闻模块
						'name'	=>	'新闻模块',
						'acts'	=> array(
								'index'	=>	'新闻首页',
								'cat'	=>	'新闻分类',
								'show'	=>	'新闻内容页',
						),
				),
				'join' => array(  //商家入驻
						'name'	=>	'商家入驻',
						'acts'	=> array(
								'index'	=>	'商家入驻',
						),
				),
				 'domestic' => array(  //国内线路
						'name'	=>	'国内游',
						'acts'	=> array(
								'index'	=>	'国内线路首页',
						),
				),
				'outbound' => array(  //出境线路
						'name'	=>	'出境游',
						'acts'	=> array(
								'index'	=>	'出境线路首页',
						),
				),
				'tours' => array(  //跟团游
						'name'	=>	'跟团游',
						'acts'	=> array(
								'index'	=>	'跟团游首页',
						),
				),
				'diy' => array(  //自助游
						'name'	=>	'自助游',
						'acts'	=> array(
								'index'	=>	'自助游首页',
						),
				),
				'drive' => array(  //自驾游
						'name'	=>	'自驾游',
						'acts'	=> array(
								'index'	=>	'自驾游首页',
						),
				),
				'around' => array(  //周边游
						'name'	=>	'周边游',
						'acts'	=> array(
								'index'	=>	'周边游首页',
						),
				),
				'tuan' => array(  //团购
						'name'	=>	'团购',
						'acts'	=> array(
								'index'	=>	'团购首页',
						),
				),
				'company_tour' => array(  //公司旅游
						'name'	=>	'公司旅游',
						'acts'	=> array(
								'index'	=>	'公司旅游',
						),
				),
    
		);
?>