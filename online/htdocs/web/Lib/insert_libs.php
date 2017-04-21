<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------

/*以下为动态载入的函数库*/
function insert_current_city_name()
{
	return $GLOBALS['city']['name'];
}


function insert_user_tip()
{
	$GLOBALS['tmpl']->assign("user",$GLOBALS['user']);
	return $GLOBALS['tmpl']->fetch("inc/user_tip.html");
}

function insert_drop_nav()
{
	//$GLOBALS['city'] = City::locate_city();
	$GLOBALS['tmpl']->assign("drop_nav",load_auto_cache("cache_drop_nav_list",array("city_py"=>$GLOBALS['city']['py'])));
	return $GLOBALS['tmpl']->fetch("inc/drop_nav.html");
}

/**
 * 频道页动态加载游记瀑布流
 * @param array $param p为逗号分隔的关键词
 */
function insert_guide_pin($p)
{
	$insert_param = $p['p']; //参数占未使用
        $GUIDE_PAGE_LOAD_GOUNT = APP_CONF("GUIDE_PAGE_LOAD_GOUNT");
        $GUIDE_PAGE_ITEM_COUNT = APP_CONF("GUIDE_PAGE_ITEM_COUNT");
        $GLOBALS['tmpl']->assign('GUIDE_PAGE_LOAD_GOUNT',$GUIDE_PAGE_LOAD_GOUNT);
        $GLOBALS['tmpl']->assign('GUIDE_PAGE_ITEM_COUNT',$GUIDE_PAGE_ITEM_COUNT);
        $GLOBALS['tmpl']->assign('default_ajax_url',url("guide#waterfull_init_data"));
        $GLOBALS['tmpl']->assign('insert_param',$insert_param);
	return $GLOBALS['tmpl']->fetch("waterfall/guide_list.html");
}

/**
 * 首页动态加载游记瀑布流
 * @param array $param p为逗号分隔的关键词
 */
function insert_index_guide_pin($p)
{
	$insert_param = $p['p']; //参数占未使用
        $GUIDE_PAGE_LOAD_GOUNT = APP_CONF("GUIDE_PAGE_LOAD_GOUNT");
        $GUIDE_PAGE_ITEM_COUNT = APP_CONF("GUIDE_PAGE_ITEM_COUNT");
        $GLOBALS['tmpl']->assign('GUIDE_PAGE_LOAD_GOUNT',$GUIDE_PAGE_LOAD_GOUNT);
        $GLOBALS['tmpl']->assign('GUIDE_PAGE_ITEM_COUNT',$GUIDE_PAGE_ITEM_COUNT);
        $GLOBALS['tmpl']->assign('default_ajax_url',url("guide#waterfull_init_data"));
        $GLOBALS['tmpl']->assign('insert_param',$insert_param);
        
	return $GLOBALS['tmpl']->fetch("waterfall/index_guide_list.html");
}
/**
 * 详细页瀑布流
 * @param type $p
 * @return type
 */
function insert_view_guide($p){
        $insert_param = "tags-".$p['p'];        
        $GUIDE_PAGE_ITEM_COUNT = APP_CONF("GUIDE_PAGE_ITEM_COUNT");
        $GLOBALS['tmpl']->assign('GUIDE_PAGE_ITEM_COUNT',$GUIDE_PAGE_ITEM_COUNT);
        $GLOBALS['tmpl']->assign('view_guide_ajax_url',url("guide#view_waterfull_init_data"));
        $GLOBALS['tmpl']->assign('insert_param',$insert_param);
        
	return $GLOBALS['tmpl']->fetch("waterfall/view_guide_list.html");
}

/**
 * 动态加载首页右侧点评模块
 * @param array $param p为逗号分隔的关键词
 */
function insert_index_right_review()
{
    require_once APP_ROOT_PATH."system/libs/review.php";
    $list = Review::get_review_limit();
    $GLOBALS['tmpl']->assign("list",$list);
    return $GLOBALS['tmpl']->fetch("inc/index_right_review.html");
}

//首页团购热卖区块
function insert_index_tuan(){
	$tuan_city = strim(format_fulltext_key($GLOBALS['city']['py']));
	$sql="SELECT id,name,image,origin_price,current_price,image,brief,sale_total,end_time,begin_time FROM ".DB_PREFIX."tuan where (begin_time < ".NOW_TIME." or begin_time=0 or (is_pre=1 and begin_time>".NOW_TIME.") ) and (end_time > ".NOW_TIME." or end_time=0) and (match(city_match) against('".$tuan_city."' IN BOOLEAN MODE) ) order by sale_total desc,create_time desc";
	$index_tuan = $GLOBALS['db']->getRow($sql);
	
	if($index_tuan['begin_time'] > NOW_TIME)
		$index_tuan['sale_total']=0;
	$index_tuan['url']=url("tuan#detail",array("did"=>$index_tuan['id']));
	$index_tuan['brief']=msubstr($index_tuan['brief'],0,35,'utf-8');
	$index_tuan['current_price']=format_price_to_display($index_tuan['current_price']);
	$index_tuan['origin_price']=format_price_to_display($index_tuan['origin_price']);
	$GLOBALS['tmpl']->assign("index_tuan",$index_tuan);	
	return $GLOBALS['tmpl']->fetch("inc/index_right_tuan.html");
	
}

function insert_verifyimg($p)
{
	$vid = $p['vid'];
	$w = $p['w'];
	$h = $p['h'];
	$imgurl = APP_ROOT."/verify.php?vid=".$vid."&w=".$w."&h=".$h;
	$html = '<img src="'.$imgurl.'&r='.rand().'" rel="'.$imgurl.'" title="看不清楚？换一张" style="cursor:pointer; margin-right:5px;" onclick="refresh_verify(this);"  />';
	return $html;
}

function insert_user_active($param){
    $uid = $param['uid'];
    $USER_ACTIVE_PAGE_LOAD_GOUNT = APP_CONF("USER_ACTIVE_PAGE_LOAD_GOUNT");
    $USER_ACTIVE_PAGE_ITEM_COUNT = APP_CONF("USER_ACTIVE_PAGE_ITEM_COUNT");
    $GLOBALS['tmpl']->assign('USER_ACTIVE_PAGE_LOAD_GOUNT',$USER_ACTIVE_PAGE_LOAD_GOUNT);
    $GLOBALS['tmpl']->assign('USER_ACTIVE_PAGE_ITEM_COUNT',$USER_ACTIVE_PAGE_ITEM_COUNT);
    $GLOBALS['tmpl']->assign("default_ajax_url",url("user#init_user_active",array('uid'=>$uid)));
    
    return  $GLOBALS['tmpl']->fetch("waterfall/user_active_list.html");
}
/**
 * 用户主页动态
 * @param type $param
 * @return type
 */
function insert_user_home_active($param){
    $uid = $param['uid'];
    $USER_ACTIVE_PAGE_LOAD_GOUNT = APP_CONF("USER_ACTIVE_PAGE_LOAD_GOUNT");
    $USER_ACTIVE_PAGE_ITEM_COUNT = APP_CONF("USER_ACTIVE_PAGE_ITEM_COUNT");
    $GLOBALS['tmpl']->assign('USER_ACTIVE_PAGE_LOAD_GOUNT',$USER_ACTIVE_PAGE_LOAD_GOUNT);
    $GLOBALS['tmpl']->assign('USER_ACTIVE_PAGE_ITEM_COUNT',$USER_ACTIVE_PAGE_ITEM_COUNT);
    $GLOBALS['tmpl']->assign("default_ajax_url",url("user#init_user_home_active",array('uid'=>$uid,"is_follow_user_active"=>0)));
    
    return  $GLOBALS['tmpl']->fetch("waterfall/user_home_active_list.html");
}


function insert_side_review($param){
    $page = $param['p'];
    require_once APP_ROOT_PATH."system/libs/review.php";
    $list = Review::get_review_limit($page);
    $GLOBALS['tmpl']->assign("list",$list);
    return $GLOBALS['tmpl']->fetch("inc/insert_side_review.html");
}


/**
 * 显示第三方登录接口
 * $type:0：会员登录页 1:ajax弹出页
 */
function insert_load_api($param)
{
	$type= intval($param['type']);
	$api_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."api_login");
	$html = "";
	foreach($api_list as $k=>$v)
	{
		$c_name = $v['class_name']."_api";
		$file = APP_ROOT_PATH."system/api_login/".$c_name.".php";
		require_once $file;
		$c_object = new $c_name($v);
		$url = $c_object->get_api_url();
		$name = $c_object->get_title();
		if($type==0)
		{
			$html.="<a target='_blank' href='".$url."' style='display:inline-block; width:34px; height:24px;  background:url(".APP_ROOT."/system/api_login/".strtolower($v['class_name'])."/slogo.gif) no-repeat;'></a>";
		}
		if($type==1)
		{
			$html.="<a target='_blank' href='".$url."' style='border:#ccc solid 1px;box-shadow: #f2f2f2 0px 5px 5px 0px; border-radius:5px; display:block; width:205px; height:40px; text-decoration:none; line-height:40px; font-size:14px; margin-bottom:10px;  padding-left:45px; background:url(".APP_ROOT."/system/api_login/".strtolower($v['class_name'])."/slogo.gif) no-repeat 8px 8px;'>".$name."</a>";
			
		}
	}
	if($type==0)
	return $html;
	if($type==1)
	{
		if($html!="")
			$html = "<div style='height:40px; line-height:40px;'>登用常用社区帐号登录</div>".$html;
		return $html;
	}
}

function insert_load_uc_common_header()
{
	$user_group = load_auto_cache("user_group");		
	$user_level = load_auto_cache("user_level");
	$GLOBALS['user']['level_name'] = $user_level[$GLOBALS['user']['level_id']]['name'];
	$GLOBALS['user']['group_name'] = $user_group[$GLOBALS['user']['group_id']]['name'];
	$GLOBALS['user']['money_format'] = format_price_to_display($GLOBALS['user']['money']);
	$voucher_money = $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."voucher where user_id = ".intval($GLOBALS['user']['id'])." and is_used = 0 and is_effect = 1");
	$GLOBALS['user']['voucher_money_format'] =  format_price_to_display($voucher_money);
	$GLOBALS['tmpl']->assign("user",$GLOBALS['user']);
	
	$nextlevel =  $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_level where exp > ".$GLOBALS['user']['exp']." order by exp asc");
	$GLOBALS['tmpl']->assign("nextlevel",$nextlevel);
	if($nextlevel['exp'] != 0)
		$GLOBALS['tmpl']->assign("percent",intval($GLOBALS['user']['exp']/$nextlevel['exp']*100));
	else
		$GLOBALS['tmpl']->assign("percent",0);
		
	
	return $GLOBALS['tmpl']->fetch("inc/uc_common_header.html");
}

//加载每日登录
function insert_load_daily_login()
{
	if($GLOBALS['user']['is_daily_login']==1)
	{
		if(app_conf("USER_LOGIN_MONEY")>0)
		{
			User::modify_account($GLOBALS['user']['id'], 1, app_conf("USER_LOGIN_MONEY"), "每日签到获取现金");
			$login_get.= " ".format_price_to_display(app_conf("USER_LOGIN_MONEY"))."元现金";
		}
		if(app_conf("USER_LOGIN_SCORE")>0)
		{
			User::modify_account($GLOBALS['user']['id'], 2, app_conf("USER_LOGIN_SCORE"), "每日签到获取积分");
			$login_get.= " ".app_conf("USER_LOGIN_SCORE")."积分";
		}
		if(app_conf("USER_LOGIN_POINT")>0)
		{
			User::modify_account($GLOBALS['user']['id'], 3, app_conf("USER_LOGIN_POINT"), "每日签到获取经验");
			$login_get.= " ".app_conf("USER_LOGIN_POINT")."点经验";
		}
		$GLOBALS['db']->query("update ".DB_PREFIX."user set is_daily_login = 0 where id = ".$GLOBALS['user']['id']);
		
		if($login_get!="")
		{
			$login_get = "每日签到获得：".$login_get;
			return "<script type='text/javascript'>
			$(document).ready(function(){
				$.showSuccess('".$login_get."');
			});
			</script>";
		}
	}
	
	
}
?>