<?php
require APP_ROOT_PATH . "system/libs/spot.php";
class spotModule extends BaseModule{

    function index() {
    	global_run();
		$GLOBALS['tmpl']->caching = true;
		$GLOBALS['tmpl']->cache_lifetime = 600;  //首页缓存10分钟
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['city']['id']);	
			
		if (!$GLOBALS['tmpl']->is_cached('spot_index.html', $cache_id))
		{	
			$spot_cate = load_auto_cache("spot_cate_list");
			$tour_area_list = load_auto_cache("tour_area_list");
			$tour_place_list = load_auto_cache("tour_place_list");
				
			foreach($spot_cate['list'] as $k=>$v){
				$spot_cate['list'][$k]['url'] =  url("spot#cat",array("cate"=>$v['id']));	
				//获取该分类下的最受欢迎的景点
				$kw_unicode = str_to_unicode_string($v['name']);
				$condition ="  (match(cate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
				$spot_cate['list'][$k]['spots'] =  $GLOBALS['db']->getAll(" SELECT id,name FROM ".DB_PREFIX."spot WHERE $condition ORDER BY sort DESC LIMIT 10");
				foreach($spot_cate['list'][$k]['spots'] as $kk=>$vv){
					$spot_cate['list'][$k]['spots'][$kk]['url'] = url("spot#view",array("id"=>$vv['id']));	
				}
				
				//区域数量统计
				$tmp_area_list = $tour_area_list;
				foreach($tmp_area_list as $kk=>$vv){
					$tmp_area_list[$kk]['places'] = $tour_place_list['areas'][$vv['py']]['place'];
					if(count($tmp_area_list[$kk]['places']) > 0){
						foreach($tmp_area_list[$kk]['places'] as $kkk=>$vvv){
							$tmp_area_list[$kk]['places'][$kkk]['url'] = url("spot#cat",array("cate"=>$v['id'],"area"=>$vv['py'],"place"=>$vvv['py']));	
							$tmp_area_list[$kk]['places'][$kkk]['count'] = $GLOBALS['db']->getOne(" SELECT count(*) FROM ".DB_PREFIX."spot WHERE (match(cate_match) against('".$kw_unicode."' IN BOOLEAN MODE)) and (match(place_match) against('".format_fulltext_key($vvv['py'])."' IN BOOLEAN MODE)) and (match(city_match) against('".format_fulltext_key($GLOBALS['city']['py'])."' IN BOOLEAN MODE)) ");	
							if($tmp_area_list[$kk]['places'][$kkk]['count']==0){
								unset($tmp_area_list[$kk]['places'][$kkk]);
							}
						}
						
						if(count($tmp_area_list[$kk]['places']) == 0){
							unset($tmp_area_list[$kk]);
						}
						
					}
					else{
						unset($tmp_area_list[$kk]);
					}
				}
				
				$spot_cate['list'][$k]['areas'] = 	$tmp_area_list;
				
				unset($tmp_area_list);
			}
			
			$GLOBALS['tmpl']->assign("spot_cate_list",$spot_cate['list']);
			
			//获取全国销量排行
			$topsale_list = get_spots_top('','','','',''," has_ticket =1");
			$GLOBALS['tmpl']->assign("topsale_list",$topsale_list);
			
			//获取热门推荐标签
			$spot_tags = $GLOBALS['db']->getAll(" SELECT id,name FROM ".DB_PREFIX."tour_place_tag WHERE is_recommend = 1 ORDER BY sort DESC ");
			$spot_tags_arr = array(); 
			foreach($spot_tags as $k=>$v){
				$spot_tags_arr[] = $v['name'];
				$areas = array();
				$places = $tour_place_list['tags'][$v['name']]['place'];
				foreach($places as $kk=>$vv){
					if($vv['area_match']){
						$tmp_name_areas = explode(",",$vv['area_match_row']);
						$tmp_py_areas = explode(",",$vv['area_match']);
						foreach($tmp_name_areas as $kkk=>$vvv){
							$areas[unformat_fulltext_key($tmp_py_areas[$kkk])] = $vvv;
						}
					}
				}
				
				
				if(count($areas)==0)
					unset($spot_tags[$k]);
				else
					$spot_tags[$k]['areas']  = $areas;
				
			}
			
			if(count($spot_tags_arr) > 0)
				$tag_spots = $this->gettagspots(implode(",",$spot_tags_arr));
			
			$GLOBALS['tmpl']->assign("tag_spots",$tag_spots);
			$tags_spots_html = $GLOBALS['tmpl']->fetch("inc/spot/spot_tags_spots.html");
			
			$GLOBALS['tmpl']->assign("tags_spots_html",$tags_spots_html);
			$GLOBALS['tmpl']->assign("spot_tags",$spot_tags);
			
			
			$city_spots_html = $GLOBALS['tmpl']->fetch("inc/spot/spot_tags_spots.html");
			$GLOBALS['tmpl']->assign("city_spots_html",$city_spots_html);
			
			//热搜景点门票
			$hot_spots  = get_spots_list("","","","",""," has_ticket = 1 ","","0,8");;
			$GLOBALS['tmpl']->assign("hot_spots",$hot_spots['list']);
			
			//主题推荐
			$rec_spots = $spot_cate["recommend"];
			foreach($rec_spots as $k=>$v){
				$rec_spots[$k]['url'] = url("spot#cat",array("cate"=>$v['id']));
				$spots = get_spots_list($v['name'],"","","",""," has_ticket = 1 ","","0,4");
				$rec_spots[$k]['list'] = $spots['list'];
			}
			
			$GLOBALS['tmpl']->assign("rec_spots",$rec_spots);
			
			$GLOBALS['tmpl']->assign("tour_area_list",$tour_area_list);
			init_app_page();
			
			//输出SEO元素
			$GLOBALS['tmpl']->assign("site_name","景点门票 - ".app_conf("SITE_NAME"));
			$GLOBALS['tmpl']->assign("site_keyword","景点门票,".app_conf("SITE_KEYWORD"));
			$GLOBALS['tmpl']->assign("site_description","景点门票,".app_conf("SITE_DESCRIPTION"));
		}
    	$GLOBALS['tmpl']->display("spot_index.html",$cache_id);
    }
    
    /**
     * 点选省份和测试切换景点
     */
    function tagspots(){
    	$tag = strim($_POST['tag']);
    	$area_py = strim($_POST['ppy']);
    	if($area_py=="")
    		return "";
    	$place_py = strim($_POST['cpy']);
    	$tour_place_list = load_auto_cache("tour_place_list");
    	//获取该省份下所有的城市
    	$spot_place = $tour_place_list['areas'][$area_py]['place'];
		$has_place = 0;
		
		foreach($spot_place as $k=>$v){
			if($place_py == $v['py']){
				$spot_place[$k]['selected'] = 1;
				$has_place = 1;
			}
			
		}
		
		$tag_spots = $this->gettagspots($tag,$area_py,$place_py);
		
		$GLOBALS['tmpl']->assign("has_place",$has_place);
		$GLOBALS['tmpl']->assign("tag_spots",$tag_spots);
		$GLOBALS['tmpl']->assign("spot_place",$spot_place);
		
			
		echo $GLOBALS['tmpl']->fetch("inc/spot/spot_tags_spots.html");
    }
    
    private function gettagspots($tag,$area="",$place=""){
    	
    	if($tag=="" && $area=="" && $place==""){
    		return "";
    	}
    	$result = get_spots_list("","",$area,$place,$tag," has_ticket = 1 ","","0,8");
    	
    	return $result['list'];
    	
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
    	
    	
    	$spot_cate = load_auto_cache("spot_cate_list");
		$tour_area_list = load_auto_cache("tour_area_list");
		$tour_place_list = load_auto_cache("tour_place_list");
		
		//获取全国销量排行
		$topsale_list = get_spots_top();
		$GLOBALS['tmpl']->assign("topsale_list",$topsale_list);
		
		
		//生成搜索连接
		//组装分类搜索开始
		$sfilter_parm = $filter_parm;
		$sfilter_parm['cate'] = 0;
		$all_cate['name'] = "全部";
		$all_cate['filter_url'] = url("spot#cat",$sfilter_parm);
		$GLOBALS['tmpl']->assign("all_cate",$all_cate);
		
		$sfilter_parm = $filter_parm;
		foreach($spot_cate['list'] as $k=>$v){
			$sfilter_parm['cate'] = $v['id'];
			$spot_cate['list'][$k]['filter_url'] = url("spot#cat",$sfilter_parm);
		}
		//组装分类搜索结束
		
		//组装大区域搜索开始
		$sfilter_parm = $filter_parm;
		$sfilter_parm['area'] = "";
		$sfilter_parm['place'] = ""; 
		$sfilter_parm['tag'] = "";
		$all_area['name'] = "全部";
		$all_area['py'] = "";
		$all_area['filter_url'] = url("spot#cat",$sfilter_parm);
		$GLOBALS['tmpl']->assign("all_area",$all_area);
		unset($all_area);
		
		$sfilter_parm = $filter_parm;
		foreach($tour_area_list as $k=>$v){
			$sfilter_parm['area'] = $v['py'];
			$sfilter_parm['place'] = "";
			$sfilter_parm['tag'] = "";
			$tour_area_list[$k]['filter_url'] = url("spot#cat",$sfilter_parm);
		}
		
		//组装大区域搜索结束
		
		//组装小区域搜索开始
		if($area!=""){
			$sfilter_parm = $filter_parm;
			$sfilter_parm['place'] = "";
			$sfilter_parm['tag'] = "";
			$all_place['name'] = "全部";
			$all_place['py'] = "";
			$all_place['filter_url'] = url("spot#cat",$sfilter_parm);
			$GLOBALS['tmpl']->assign("all_place",$all_place);
			unset($all_place);
			$temptour_place_list  = $tour_place_list['areas'][$area]['place'];
			if($temptour_place_list){
				$sfilter_parm = $filter_parm;
				foreach($temptour_place_list as $k=>$v){
					$sfilter_parm['place'] = $v['py'];
					$sfilter_parm['tag'] = "";
					$temptour_place_list[$k]['filter_url'] = url("spot#cat",$sfilter_parm);
				}
				
				$GLOBALS['tmpl']->assign("tour_place_list",$temptour_place_list);
				unset($temptour_place_list);
			}
		}
		//组装小区域搜索结束
		
		//组装标签
		if($place!=""){
			$sfilter_parm = $filter_parm;
			$sfilter_parm['tag'] = "";
			$all_tag['name'] = "全部";
			$all_tag['py'] = "";
			$all_tag['filter_url'] = url("spot#cat",$sfilter_parm);
			$GLOBALS['tmpl']->assign("all_tag",$all_tag);
			unset($all_tag);
			$temptour_tag_list  = $tour_place_list['place_tags'][$place];
			
			if($temptour_tag_list){
				$sfilter_parm = $filter_parm;
				foreach($temptour_tag_list as $k=>$v){
					$sfilter_parm['tag'] = $v['name'];
					$temptour_tag_list[$k]['filter_url'] = url("spot#cat",$sfilter_parm);
				}
				
				$GLOBALS['tmpl']->assign("tour_tag_list",$temptour_tag_list);
				unset($temptour_tag_list);
			}
		}
		
		//组装标签搜索结束
		
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
							"name" => "50元以下"
						),
						array(
							"price"=>2,
							"name" => "50-100元"
						),
						array(
							"price"=>3,
							"name" => "100元以上"
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
							"level"=>5,
							"name" => lang("LEVEL_5")
						),
						array(
							"level"=>4,
							"name" => lang("LEVEL_4")
						),
						array(
							"level"=>3,
							"name" => lang("LEVEL_3")
						),
						array(
							"level"=>2,
							"name" => lang("LEVEL_2")
						),
						array(
							"level"=>1,
							"name" => lang("LEVEL_1")
						),
					);
		foreach($tour_level as $k=>$v){
			$sfilter_parm['level'] = $v['level'];
			$tour_level[$k]['filter_url'] = url("spot#cat",$sfilter_parm);
		}
		
		$GLOBALS['tmpl']->assign("tour_level",$tour_level);
		unset($tour_level);
		//组装景点等级结束
		
		//组装排序开始
		$sfilter_parm = $filter_parm;
		$sfilter_parm['status'] = 0;
		$sfilter_parm['order'] =  0;
		$GLOBALS['tmpl']->assign("status_0",0);
		
		$status_url[0] = url("spot#cat",$sfilter_parm);
		$GLOBALS['tmpl']->assign("status_0",$sfilter_parm['order'] == 0 ? 1 : 0);
		
		$sfilter_parm['status'] = 1;
		if($status == $sfilter_parm['status']){
			$sfilter_parm['order'] =  $order == 1 ? 0 : 1;
			$GLOBALS['tmpl']->assign("status_1",$order);
		}
		else{
			$sfilter_parm['order'] =  0;
			$GLOBALS['tmpl']->assign("status_1",0);
		}
		$status_url[1] = url("spot#cat",$sfilter_parm);
		
		
		$sfilter_parm['status'] = 2;
		if($status == $sfilter_parm['status']){
			$sfilter_parm['order'] =  $order == 1 ? 0 : 1;
			$GLOBALS['tmpl']->assign("status_2",$order);
		}
		else{
			$sfilter_parm['order'] =  1;
			$GLOBALS['tmpl']->assign("status_2",1);
		}
		$status_url[2] = url("spot#cat",$sfilter_parm);
		
		$sfilter_parm['status'] = 3;
		if($status == $sfilter_parm['status']){
			$sfilter_parm['order'] =  $order == 1 ? 0 : 1;
			$GLOBALS['tmpl']->assign("status_3",$order);
		}
		else{
			$sfilter_parm['order'] =  0;
			$GLOBALS['tmpl']->assign("status_3",0);
		}
		$status_url[3] = url("spot#cat",$sfilter_parm);
		
		$GLOBALS['tmpl']->assign("status_url",$status_url);
		//组装排序结束
		
		
		//注册当前地址
		$ur_here[] = array("name"=>"景点列表","url"=>url("spot#cat"));
		if($cate_id > 0){
			$ur_here[] = array("name"=>$spot_cate['list'][$cate_id]['name'],"url"=>url("spot#cat",array("cate"=>$cate_id)));;
		}
		
		if($area!=""){
			$ur_here[] = array("name"=>$tour_place_list['areas'][$area]['name'],"url"=>url("spot#cat",array("cate"=>$cate_id,"area"=>$area)));
			if($place!=""){
				$ur_here[] = array("name"=>$tour_place_list['pys'][$place]['name'],"url"=>url("spot#cat",array("cate"=>$cate_id,"area"=>$area,"place"=>$place)));
				if($tag!=""){
					$ur_here[] = array("name"=>$tag,"url"=>url("spot#cat",array("cate"=>$cate_id,"area"=>$area,"place"=>$place,"tag"=>$tag)));
				}
			}
		}
		
		
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
		$GLOBALS['tmpl']->assign("site_name","景点列表 - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword","景点列表,".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description","景点列表,".app_conf("SITE_DESCRIPTION"));
		
		//组装价格搜索的
		$price_action_parm = "";
		foreach($filter_parm as $k=>$v){
			if($k!="min_price" && $k!="max_price")
				$price_action_parm .=$k."=".$v."&";
		}
		$GLOBALS['tmpl']->assign("price_action_parm",$price_action_parm);
		$GLOBALS['tmpl']->assign("ur_here",$ur_here);
		
		//获取列表
		$limit  = (($page - 1) *$pagesize) .",$pagesize";
		
		
		$conditions = " has_ticket = 1  ";
		switch($price){
			case 1:
				$conditions .=" and ticket_price < ".format_price_to_db(50)." ";
				break;
			case 2:
				$conditions .=" and ticket_price between ".format_price_to_db(50)." and ".format_price_to_db(100)." ";
				break;
			case 3:
				$conditions .=" and ticket_price > ".format_price_to_db(100)." ";
				break;
		}
		
		if($level > 0){
			$conditions .= " and spot_level=".$level;
		}
		
		if($min_price !=0 || $max_price !=0){
			if($min_price >0 && $max_price > 0){
				$conditions .=" and ticket_price between ".format_price_to_db($min_price)." and ".format_price_to_db($max_price)." ";
			}
			elseif($min_price == 0 && $max_price > 0){
				$conditions .=" and ticket_price <= ".format_price_to_db($max_price)." ";
			}
			elseif($min_price > 0 && $max_price == 0){
				$conditions .=" and ticket_price >= ".format_price_to_db($min_price)." ";
			}
		}
		
		if($keyword !=""){
			$kw_unicode = str_to_unicode_string_depart($keyword);
			$conditions .=" and ( (match(tag_match,city_match,area_match,place_match,cate_match) against('".$kw_unicode."' IN BOOLEAN MODE)) or name like '%".$keyword."%')";
		}
		
		$order_by = " sort DESC, id DESC ";
		$DESC_ASC = $order== 0 ? "DESC" : "ASC";
		switch($status){
			//销量
			case "1":
				$order_by = " sale_total $DESC_ASC, sort DESC, id DESC ";
				break;
			//价格
			case "2":
				$order_by = " ticket_price $DESC_ASC, sort DESC, id DESC ";
				break;
			//评价
			case "2":
				$order_by = " satify $DESC_ASC sort ,DESC, id DESC ";
				break;
		}
		
		
				
		$result = get_spots_list($spot_cate['list'][$cate_id]['name'],$GLOBALS['city']['py'],$area,$place,$tag,$conditions,$order_by,$limit);;
		foreach($result['list'] as $k=>$v){
			if($v["city_match_row"]){
				$areas =  explode(",",$v["area_match_row"]);
				$areas_py =  explode(",",$v["area_match"]);
				foreach($areas as $ck=>$cv){
					$result["list"][$k]['area_list'][$ck]['name'] = $cv;
					$result["list"][$k]['area_list'][$ck]['py'] = $areas_py[$ck];
					$result["list"][$k]['area_list'][$ck]['url'] = url("spot#cat",array("area"=>$areas_py[$ck]));
				}
			}
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
		unset($spot_cate);
		unset($tour_province_list);
    	$GLOBALS['tmpl']->display("spot_cat.html");
    }
    
    /**
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
    		showErr("景点门票不存在",0,url("spot#cat"));
    	}
    	
    	$ur_here[] = array("name"=>"景点列表","url"=>url("spot#cat"));
    	$ur_here[] = array("name"=>$spot['name']);
    	
    	$GLOBALS['tmpl']->assign("ur_here",$ur_here);
    	
    	if($sid == 0){
	    	//相册
	    	$images = $GLOBALS['db']->getAll("SELECT `image` FROM ".DB_PREFIX."spot_image WHERE spot_id = $id ORDER BY sort ASC");
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
    	$GLOBALS['tmpl']->display("spot_view.html");
    }
    
    /**
     * 成交记录
     */
    function ajax_sale_list(){
    	$page=intval($_REQUEST['p']);
    	$is_ajax =intval($_REQUEST['is_ajax']);
    	if($page==0)
    		$page=1;
    		
		$pagesize = 5;
		$spotid = intval($_REQUEST['id']);
		if($spotid == 0)
			exit();
		$limit  = (($page - 1) *$pagesize) .",$pagesize";
		
    	$rs = get_sale_list($spotid,$limit);
		
		$pager = buildPage("spot#ajax_sale_list",array('id'=>$spotid,"is_ajax"=>1),$rs['rs_count'],$page,$pagesize,1);
      
        $GLOBALS['tmpl']->assign('pager',$pager);
        $result['pager'] = $GLOBALS['tmpl']->fetch("inc/pages.html");
		
		$GLOBALS['tmpl']->assign("is_ajax",$is_ajax);
		
		$GLOBALS['tmpl']->assign("sale_list",$rs['list']);
		
		$result['html'] = $GLOBALS['tmpl']->fetch("inc/spot/sale_list.html");
    	
    	if($is_ajax == 1){
    		ajax_return($result);
    	}
    	else{
    		return $result;
    	}
    }
    
    
}
?>