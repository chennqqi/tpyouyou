<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------
require APP_ROOT_PATH . "system/libs/hotel.php";

class hotelModule extends BaseModule
{
	// public function index()
	// {
 //    $ipInfos = GetIpLookup('112.233.52.210'); //baidu.com IP地址
 //    $GLOBALS['tmpl']->assign("ipInfos",$ipInfos);
	// 	global_run();
	// 	$GLOBALS['tmpl']->caching = true;
	// 	$GLOBALS['tmpl']->cache_lifetime = 600;  //首页缓存10分钟
	// 	$cache_id  = md5(MODULE_NAME.ACTION_NAME);
	// 	$GLOBALS['tmpl']->display("hotel_index.html",$cache_id);
	// }

  public function index(){
    global_run();
    $page=intval($_REQUEST['p']);
    if($page==0)
    		$page=1;
    		
		$pagesize = 5;
		
  	$filter_parm = array();
  	$filter_parm['cate'] = $cate_id = intval($_GET['cate']);
  	$filter_parm['area'] = $area = strim($_GET['area']);
  	$filter_parm['place'] = $place = strim($_GET['place']);
  	$filter_parm['tag'] = $tag = strim($_GET['tag']);
  	$filter_parm['level'] = $level = intval($_GET['level']);
  	$filter_parm['status'] = $status = intval($_GET['status']);
  	$filter_parm['order'] = $order = intval($_GET['order']); //0 DESC  1 ASC
  	$min_price = intval($_REQUEST['min_price']);
  	$max_price = intval($_REQUEST['max_price']);
  	if($min_price > $max_price && $max_price > 0)
  	{
  		$min_price = intval($_REQUEST['max_price']);
  		$max_price = intval($_REQUEST['min_price']);
  	}
    	
  	$filter_parm['min_price'] = $min_price;
  	$filter_parm['max_price'] = $max_price;
  	if($min_price!=0 || $max_price!=0)
  	{
  		$filter_parm['price'] = $price = 0;
  	}
  	else{    	
  		$filter_parm['price'] = $price = intval($_GET['price']);
  	}
  	
  	$keyword = strim($_REQUEST['keyword']);
    
		$tour_area_list = load_auto_cache("tour_area_list");
		$tour_place_list = load_auto_cache("tour_place_list");

		//组装景点价格开始
		$sfilter_parm = $filter_parm;
		$sfilter_parm['price'] = 0;
		$all_price['name'] = "全部";
		$all_price['level'] = 0;
		$all_price['filter_url'] = url("spot#cat",$sfilter_parm);
		$GLOBALS['tmpl']->assign("all_price",$all_price);
		unset($all_price);
		$tour_price = array(
			array(
				"price"=>1,
				"name" => "200元以下"
			),
			array(
				"price"=>2,
				"name" => "200-400元"
			),
			array(
				"price"=>3,
				"name" => "400元以上"
		  )
		);
		foreach($tour_price as $k=>$v){
			$sfilter_parm['price'] = $v['price'];
			$tour_price[$k]['filter_url'] = url("spot#cat",$sfilter_parm);
		}
		
		$GLOBALS['tmpl']->assign("tour_price",$tour_price);
		unset($tour_price);
		//组装景点价格结束
		
		//组装景点等级开始
		$sfilter_parm = $filter_parm;
		$sfilter_parm['level'] = 0;
		$all_level['name'] = "全部";
		$all_level['level'] = 0;
		$all_level['filter_url'] = url("spot#cat",$sfilter_parm);
		$GLOBALS['tmpl']->assign("all_level",$all_level);
		unset($all_level);
		
		$tour_level = array(
			array(
				"level"=>7,
				"name" => lang("七星")
			),
			array(
				"level"=>6,
				"name" => lang("六星")
			),
			array(
				"level"=>5,
				"name" => lang("五星")
			),
			array(
				"level"=>4,
				"name" => lang("四星")
			),
			array(
				"level"=>3,
				"name" => lang("三星")
			),
			array(
				"level"=>2,
				"name" => lang("二星")
			),
			array(
				"level"=>1,
				"name" => lang("一星")
			),
		);
		foreach($tour_level as $k=>$v){
			$sfilter_parm['level'] = $v['level'];
			$tour_level[$k]['filter_url'] = url("hotel#cat",$sfilter_parm);
		}
		
		$GLOBALS['tmpl']->assign("tour_level",$tour_level);
		unset($tour_level);
		//组装景点等级结束
		
		init_app_page();
		
		$GLOBALS['tmpl']->assign("cate_id",$cate_id);
		$GLOBALS['tmpl']->assign("area_py",$area);
		$GLOBALS['tmpl']->assign("place_py",$place);
		$GLOBALS['tmpl']->assign("tag_py",$tag);
		$GLOBALS['tmpl']->assign("price_id",$price);
		$GLOBALS['tmpl']->assign("level_id",$level);
		$GLOBALS['tmpl']->assign("status",$status);
		$GLOBALS['tmpl']->assign("order",$order);
		if($min_price > 0)
			$GLOBALS['tmpl']->assign("min_price",$min_price);
		if($max_price > 0)
			$GLOBALS['tmpl']->assign("max_price",$max_price);
		$GLOBALS['tmpl']->assign("filter_parm",$filter_parm);
		
		$GLOBALS['tmpl']->assign("spot_cate",$spot_cate['list']);
		
		$GLOBALS['tmpl']->assign("tour_area_list",$tour_area_list);
			
		//输出SEO元素
		$GLOBALS['tmpl']->assign("site_name","酒店列表 - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword","酒店列表,".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description","酒店列表,".app_conf("SITE_DESCRIPTION"));
				
		//获取列表
		$limit  = (($page - 1) *$pagesize) .",$pagesize";
		
		$conditions = "";
		
		if($level > 0){
			$conditions .= " and star_level=".$level;
		}
		
		if($keyword !=""){
			$conditions .=" and (name like '%".$keyword."%') ";
		}

		$result = get_hotel_list($GLOBALS['city']['py'],$conditions,$order_by,$limit);;
		foreach($result['list'] as $k=>$v){
		}
		$GLOBALS['tmpl']->assign("spot_cat_list",$result['list']);
		require APP_ROOT_PATH.'web/Lib/right_page.php';
		$page = new Page($result['rs_count'],$pagesize);   //初始化分页对象 		
		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$right_page = new RightPage($result['rs_count'],$pagesize);
    $right_pages  =  $right_page->show();
	  $GLOBALS['tmpl']->assign('right_pages',$right_pages);
		
		unset($result);
		unset($tour_province_list);
  	$GLOBALS['tmpl']->display("hotel_index.html");
  }

  function cat(){
    global_run();
    $page=intval($_REQUEST['p']);
    if($page==0)
    		$page=1;
    		
		$pagesize = 5;
		
  	$filter_parm = array();
  	$filter_parm['cate'] = $cate_id = intval($_GET['cate']);
  	$filter_parm['area'] = $area = strim($_GET['area']);
  	$filter_parm['place'] = $place = strim($_GET['place']);
  	$filter_parm['tag'] = $tag = strim($_GET['tag']);
  	$filter_parm['level'] = $level = intval($_GET['level']);
  	$filter_parm['status'] = $status = intval($_GET['status']);
  	$filter_parm['order'] = $order = intval($_GET['order']); //0 DESC  1 ASC
  	$min_price = intval($_REQUEST['min_price']);
  	$max_price = intval($_REQUEST['max_price']);
  	if($min_price > $max_price && $max_price > 0)
  	{
  		$min_price = intval($_REQUEST['max_price']);
  		$max_price = intval($_REQUEST['min_price']);
  	}
    	
  	$filter_parm['min_price'] = $min_price;
  	$filter_parm['max_price'] = $max_price;
  	if($min_price!=0 || $max_price!=0)
  	{
  		$filter_parm['price'] = $price = 0;
  	}
  	else{    	
  		$filter_parm['price'] = $price = intval($_GET['price']);
  	}
  	
  	$keyword = strim($_REQUEST['keyword']);
    
		$tour_area_list = load_auto_cache("tour_area_list");
		$tour_place_list = load_auto_cache("tour_place_list");

		//组装景点价格开始
		$sfilter_parm = $filter_parm;
		$sfilter_parm['price'] = 0;
		$all_price['name'] = "全部";
		$all_price['level'] = 0;
		$all_price['filter_url'] = url("spot#cat",$sfilter_parm);
		$GLOBALS['tmpl']->assign("all_price",$all_price);
		unset($all_price);
		$tour_price = array(
			array(
				"price"=>1,
				"name" => "200元以下"
			),
			array(
				"price"=>2,
				"name" => "200-400元"
			),
			array(
				"price"=>3,
				"name" => "400元以上"
		  )
		);
		foreach($tour_price as $k=>$v){
			$sfilter_parm['price'] = $v['price'];
			$tour_price[$k]['filter_url'] = url("spot#cat",$sfilter_parm);
		}
		
		$GLOBALS['tmpl']->assign("tour_price",$tour_price);
		unset($tour_price);
		//组装景点价格结束
		
		//组装景点等级开始
		$sfilter_parm = $filter_parm;
		$sfilter_parm['level'] = 0;
		$all_level['name'] = "全部";
		$all_level['level'] = 0;
		$all_level['filter_url'] = url("spot#cat",$sfilter_parm);
		$GLOBALS['tmpl']->assign("all_level",$all_level);
		unset($all_level);
		
		$tour_level = array(
			array(
				"level"=>7,
				"name" => lang("七星")
			),
			array(
				"level"=>6,
				"name" => lang("六星")
			),
			array(
				"level"=>5,
				"name" => lang("五星")
			),
			array(
				"level"=>4,
				"name" => lang("四星")
			),
			array(
				"level"=>3,
				"name" => lang("三星")
			),
			array(
				"level"=>2,
				"name" => lang("二星")
			),
			array(
				"level"=>1,
				"name" => lang("一星")
			),
		);
		foreach($tour_level as $k=>$v){
			$sfilter_parm['level'] = $v['level'];
			$tour_level[$k]['filter_url'] = url("hotel#cat",$sfilter_parm);
		}
		
		$GLOBALS['tmpl']->assign("tour_level",$tour_level);
		unset($tour_level);
		//组装景点等级结束
		
		init_app_page();
		
		$GLOBALS['tmpl']->assign("cate_id",$cate_id);
		$GLOBALS['tmpl']->assign("area_py",$area);
		$GLOBALS['tmpl']->assign("place_py",$place);
		$GLOBALS['tmpl']->assign("tag_py",$tag);
		$GLOBALS['tmpl']->assign("price_id",$price);
		$GLOBALS['tmpl']->assign("level_id",$level);
		$GLOBALS['tmpl']->assign("status",$status);
		$GLOBALS['tmpl']->assign("order",$order);
		if($min_price > 0)
			$GLOBALS['tmpl']->assign("min_price",$min_price);
		if($max_price > 0)
			$GLOBALS['tmpl']->assign("max_price",$max_price);
		$GLOBALS['tmpl']->assign("filter_parm",$filter_parm);
		
		$GLOBALS['tmpl']->assign("spot_cate",$spot_cate['list']);
		
		$GLOBALS['tmpl']->assign("tour_area_list",$tour_area_list);
			
		//输出SEO元素
		$GLOBALS['tmpl']->assign("site_name","酒店列表 - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword","酒店列表,".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description","酒店列表,".app_conf("SITE_DESCRIPTION"));
				
		//获取列表
		$limit  = (($page - 1) *$pagesize) .",$pagesize";
		
		$conditions = "";
		
		if($level > 0){
			$conditions .= " and star_level=".$level;
		}
		
		if($keyword !=""){
			$conditions .=" and (name like '%".$keyword."%') ";
		}

		$result = get_hotel_list($GLOBALS['city']['py'],$conditions,$order_by,$limit);;
		foreach($result['list'] as $k=>$v){
		}
		$GLOBALS['tmpl']->assign("spot_cat_list",$result['list']);
		require APP_ROOT_PATH.'web/Lib/right_page.php';
		$page = new Page($result['rs_count'],$pagesize);   //初始化分页对象 		
		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$right_page = new RightPage($result['rs_count'],$pagesize);
    $right_pages  =  $right_page->show();
	  $GLOBALS['tmpl']->assign('right_pages',$right_pages);
		
		unset($result);
		unset($tour_province_list);
  	$GLOBALS['tmpl']->display("hotel_index.html");
  }
        
  public function gohotel(){
    if($_REQUEST['act']==gohotel){
			$cityname=$_REQUEST['cityname'];
			$checkindate=$_REQUEST['checkindate'];
			$checkoutdate=$_REQUEST['checkoutdate'];
			$GLOBALS['tmpl']->assign('cityname',$cityname);
			$GLOBALS['tmpl']->assign('checkindate',$checkindate);
			$GLOBALS['tmpl']->assign('checkoutdate',$checkoutdate);
			$GLOBALS['tmpl']->display("gohotel.html");
    }     
  }

	/*
	 * 详情
	 */
	function view(){
	  	global_run();
	  	
	  	$id = intval($_REQUEST['id']);
	  	$sid = intval($_REQUEST['sid']);
	  	if($id == 0 && $sid==0){
	  		showErr("数据错误",0,url("spot#cat"));
	  	}
	  	if($sid > 0){
	  		$spot = get_supplier_spot($sid);
	  	}
	  	else
	  		$spot = get_spot($id);
	  	if(!$spot){
	  		showErr("酒店不存在",0,url("spot#cat"));
	  	}
	  	
	  	$ur_here[] = array("name"=>"酒店列表","url"=>url("spot#cat"));
	  	$ur_here[] = array("name"=>$spot['name']);
	  	
	  	$GLOBALS['tmpl']->assign("ur_here",$ur_here);
	  	
	  	if($sid == 0){
	    	//相册
	    	$images = $GLOBALS['db']->getAll("SELECT `image` FROM ".DB_PREFIX."hotel_image WHERE hotel_id = $id ORDER BY sort ASC");
	  	}
	  	else{
	  		//相册
	  		$temp_images = unserialize($spot['image_list']);
	  		$images = array();
	  		foreach($temp_images as $k=>$v){
	  			$images[$k]['image'] = $v;
	  		}
	  	}
	  	$GLOBALS['tmpl']->assign("images",$images);
	  	$GLOBALS['tmpl']->assign("spot",$spot);
	  	
	  	//获取商家信息
	  	if($spot['supplier_id'] > 0){
	  		require APP_ROOT_PATH.'system/libs/supplier.php';
	  		$supplier = get_supplier($spot['supplier_id']);
	  		$GLOBALS['tmpl']->assign("supplier",$supplier);
	  	}
	  	
	  	$tour_area_list = load_auto_cache("tour_area_list");
	  	$tour_place_list = load_auto_cache("tour_place_list");
	  	
	  	if($tour_area_list){
	    	foreach($tour_area_list as $k=>$v){
	    		$tour_area_list[$k]['places'] = $tour_place_list['areas'][$v['py']]['place'];
	    		if($tour_area_list[$k]['places']){
		    		foreach($tour_area_list[$k]['places'] as $kk=>$vv){
		    			$tour_area_list[$k]['places'][$kk]['url'] = url("spot#cat",array("area"=>$v['py'],"place"=>$vv['py']));
		    		}
	    		}
	    	}
	  	}
	  	
	  	$GLOBALS['tmpl']->assign("tour_area_list",$tour_area_list);
	  	
	  	$rand_spot = get_rand_spot(5,$id);
	  	$GLOBALS['tmpl']->assign("rand_spot",$rand_spot);
	  	//成交记录
	  	if($spot['show_sale_list']){
	  		$sale_result = spotModule::ajax_sale_list();
	  		$GLOBALS['tmpl']->assign("sale_result",$sale_result);
	  		unset($sale_result);
	  	}
	  	
	  	if($id > 0)
	  	{
	  		//点评
	    	require_once APP_ROOT_PATH.'system/libs/review.php';
	        $review_html = Review::init_review(2,$id);
	        $GLOBALS['tmpl']->assign('review_html',$review_html);
	  	}
	  	
	  	init_app_page();
	  	//输出SEO元素
	  	if($spot['seo_title']!='')
	  		$seo_title = $spot['seo_title'];
	  	else
	  		$seo_title = $spot['name'];
	  	
	  	if($spot['seo_keywords']!='')
	  		$seo_keywords = $spot['seo_keywords'];
	  	else
	  		$seo_keywords = $spot['cate_match_row'].",".$spot['city_match_row'].",".$spot['area_match_row'].",".$spot['place_match_row'].",".$spot['tag_match_row'].",景点门票";
	  		
	  	if($spot['seo_description']!='')
	  		$seo_description = $spot['seo_description'];
	  	else
	  		$seo_description = $spot['brief'];
	  	
	  	$GLOBALS['tmpl']->assign("id",$id);
	  	$GLOBALS['tmpl']->assign("sid",$sid);
		  $GLOBALS['tmpl']->assign("site_name",$seo_title." - 景点门票 - ".app_conf("SITE_NAME"));
		  $GLOBALS['tmpl']->assign("site_keyword",$seo_keywords.",".app_conf("SITE_KEYWORD"));
		  $GLOBALS['tmpl']->assign("site_description",$seo_description.",".app_conf("SITE_DESCRIPTION"));
	  	$GLOBALS['tmpl']->display("hotel_view.html");
	}
	
}
?>