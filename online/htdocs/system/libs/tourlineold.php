<?php

/**

 * $start_city出发城市id

 * $areas 大区拼音 多个,隔开

 * $places 小区拼音 多个,隔开

 * $belong_citys 城市拼音 多个,隔开(所属城市)

 */

function get_tourline_list($start_city=0,$areas="",$places="",$belong_citys="",$tags="",$conditions="",$order="",$limit='0,20'){

	$condition = " is_effect=1 and ( (is_tuan=1 and tuan_end_time < ".NOW_TIME." and tuan_end_time >0) <> true) and ((is_tuan=1 and tuan_is_pre=0 and tuan_begin_time >".NOW_TIME.") <> true) ";

	

	if($start_city >0 && $belong_citys!="")

		$condition .=" and ( city_id=".$start_city." or (match(city_match) against('".format_fulltext_key($belong_citys)."' IN BOOLEAN MODE)) )";

	elseif($start_city >0 && $belong_citys ="")

		$condition .=" and city_id=".format_fulltext_key($start_city)."";

	elseif($start_city =0 && $belong_citys !="")

		$condition .=" and (match(city_match) against('".format_fulltext_key($belong_citys)."' IN BOOLEAN MODE))";

	

	if($areas!=""){

		$condition .=" and (match(area_match) against('".format_fulltext_key($areas)."' IN BOOLEAN MODE))";

	}

	

	if($places!=""){

		$condition .=" and (match(place_match) against('".format_fulltext_key($places)."' IN BOOLEAN MODE))";

	}

	

	if($tags!=""){

		$kw_unicode = str_to_unicode_string_depart($tags);

		$condition .=" and (match(tag_match) against('".$kw_unicode."' IN BOOLEAN MODE))";

	}

	

	if($conditions!="")

		$condition .= " and ".$conditions;

	

	if($order == ""){

		$order = " is_recommend DESC,sort DESC,id DESC";

	}

	

	if($limit =='')

		$limit = "0,20";



	$result["rs_count"] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tourline where ".$condition." ");

	if( $result["rs_count"] >0)

	{

		$list = $GLOBALS['db']->getAll("SELECT id,name,city_id,origin_price,price,sale_total,satify,sale_virtual_total,supplier_id,image,brief,"

		."show_sale_list,is_tuan,tuan_is_pre,tuan_cate,tuan_begin_time,tuan_end_time,tuan_success_count,return_money,return_score,return_exp,is_review_return,review_return_money,review_return_score,"

		."review_return_exp,is_refund,is_expire_refund,is_visa,visa_price,tour_range,tour_type,is_rebate,tour_total_day FROM ".DB_PREFIX."tourline WHERE ".$condition." ORDER BY ".$order." LIMIT ".$limit." ");

	

		foreach($list  as $k=>$v){

			$list[$k]['origin_price']=format_price_to_display($v['origin_price']);

			$list[$k]['price']=format_price_to_display($v['price']);

			$list[$k]['return_money']=format_price_to_display($v['return_money']);

			$list[$k]['review_return_money']=format_price_to_display($v['review_return_money']);

			$list[$k]['format_satify']=$v['satify']/100;

			

			$list[$k]['url']=url("tours#view",array("id"=>$v['id']));

			if($v['is_tuan'] == 1)

			{

				if($v['tuan_begin_time'] >  NOW_TIME && $v['tuan_begin_time']>0)

					$list[$k]['tuan_is_pre'] =1;

				else

					$list[$k]['tuan_is_pre'] =0;

			}

			

		}

	}

	$result["list"]=$list;

	return $result;

}



/**

 * $start_city出发城市id

 * $areas 大区拼音 多个,隔开

 * $places 小区拼音 多个,隔开

 * $belong_citys 城市拼音 多个,隔开(所属城市)

 */

function get_number_tourline_list($start_city=0,$areas="",$places="",$belong_citys="",$tags="",$conditions="",$order="",$limit=8){

	$condition = " is_effect=1 and ( (is_tuan=1 and tuan_end_time < ".NOW_TIME." and tuan_end_time >0) <> true) and ((is_tuan=1 and tuan_is_pre=0 and tuan_begin_time >".NOW_TIME.") <> true) ";

	

	if($start_city >0 && $belong_citys!="")

		$condition .=" and ( city_id=".$start_city." or (match(city_match) against('".format_fulltext_key($belong_citys)."' IN BOOLEAN MODE)) )";

	elseif($start_city >0 && $belong_citys ="")

		$condition .=" and city_id=".$start_city."";

	elseif($start_city =0 && $belong_citys !="")

		$condition .=" and (match(city_match) against('".format_fulltext_key($belong_citys)."' IN BOOLEAN MODE))";

	

	if($areas!=""){

		$condition .=" and (match(area_match) against('".format_fulltext_key($areas)."' IN BOOLEAN MODE))";

	}

		

	if($areas!=""){

		$condition .=" and (match(area_match) against('".format_fulltext_key($areas)."' IN BOOLEAN MODE))";

	}

	

	if($places!=""){

		$condition .=" and (match(place_match) against('".format_fulltext_key($places)."' IN BOOLEAN MODE))";

	}

	

	if($tags!=""){

		$kw_unicode = str_to_unicode_string_depart($tags);

		$condition .=" and (match(tag_match) against('".$kw_unicode."' IN BOOLEAN MODE))";

	}

	

	if($conditions !="")

		$condition .= " and ".$conditions;

	

	if($order == ""){

		$order = " is_recommend DESC,sort DESC,id DESC";

	}

	

	if(!$limit)

		$limit = 8;

	

	$list = $GLOBALS['db']->getAll('SELECT id,name,city_id,origin_price,price,sale_total,sale_virtual_total,satify,supplier_id,image,brief,"

	."show_sale_list,is_tuan,tuan_is_pre,tuan_cate,tuan_begin_time,tuan_end_time,tuan_success_count,return_money,return_score,return_exp,is_review_return,review_return_money,review_return_score,"

	."review_return_exp,is_refund,is_expire_refund,is_visa,visa_price,tour_range,tour_type,is_rebate,tour_total_day FROM '.DB_PREFIX.'tourline WHERE '.$condition.' ORDER BY '.$order.' LIMIT '.$limit);

	foreach($list  as $k=>$v){

		

		$list[$k]['origin_price']=format_price_to_display($v['origin_price']);

		$list[$k]['price']=format_price_to_display($v['price']);

		$list[$k]['return_money']=format_price_to_display($v['return_money']);

		$list[$k]['review_return_money']=format_price_to_display($v['review_return_money']);

		

		$list[$k]['url']=url("tours#view",array("id"=>$v['id']));

		if($v['is_tuan'] == 1)

		{

			if($v['tuan_begin_time'] >  NOW_TIME && $v['tuan_begin_time']>0)

				$list[$k]['tuan_is_pre'] =1;

			else

				$list[$k]['tuan_is_pre'] =0;

		}

		

		

	}

	$result["list"]=$list;

	return $result;

}



function get_layer_number_tourline_list($start_city=0,$areas="",$places="",$belong_citys="",$tags="",$conditions="",$order="",$limit=8)

{

	$result=get_number_tourline_list($start_city,$areas,$places,$belong_citys,$tags="",$conditions,$order,$limit);

	$tourline=$result['list'];

	foreach($tourline as $k=>$v)

	{

		if($k <4)

		  $list['big_tourline_list'][]=$v;

		  

		if($k >3)

		  $list['small_tourline_list'][]=$v;

	}

	

	return $list;

}





/**

 * 获取推荐模块

 * 

 * @param int $rec_page  0首页 1国内游 2出境游 3周边游 4跟团游 5自助游 6自驾游 

 * @param int $rec_type  1国内游 2出境游 3周边游 4跟团游 5自助游 6自驾游  (1-6只有首页设置可用) 7.大区域 8.小区域 9.标签

 * @param int $rec_id    关联的ID

 * 

 * 

 * //相关的链接地址格式

	 普通的查询 http://www.fanwetour.com/fanwetour/tourlist

	 周边游查询 http://www.fanwetour.com/fanwetour/tourlist/around

	   参数

	 tag(标签)

	 type(线路范围 1.国内2.出境)

	 a_py(大区域拼音)

	 p_py(小区域拼音)

	 t_type(1.跟团 2.自助 3.自驾)

	 

 * 

 * 需返回的数据

 * 

 * array

 * (

 * 		"top_nav_more"=>array(

 * 							"b_cate"=>array(

 * 												"name"=>"xxx",

 * 												"url"=>"xxx",

 * 												"s_cate"=>array(

 * 																"name"=>"xxx",

 * 																"url"=>"xxx"

 * 															  )

 * 											)

 * 						),

 * 		"left_nav"=>array(

 * 							array("name"=>"xxx","url"=>"xxx"),

 * 						 ),

 * 		"left_nav"=>array(

 * 							array("name"=>"xxx","url"=>"xxx"),

 * 						 ),

 * 		"image_tour"=>array(

 * 							array("name"=>"xxx","url"=>"xxx","image"=>"xxx","price"=>"xxx")

 * 						   ),

 * 		"text_tour"=>array(

 * 							array("name"=>"xxx","url"=>"xxx","price"=>"xxx")

 * 						   ),

 * 		"more_url"	=> "xxx", 更多的链接

 * )

 * 

 */

function load_tourline_rec($rec_page,$rec_type,$rec_id)

{

	if($rec_type==1||$rec_type==2) //国内游，出境游

	{

		//获取top_nav_more

		$b_cate = $GLOBALS['db']->getAll("select id,name,py from ".DB_PREFIX."tour_area where type = $rec_type and is_recommend = 1 order by py asc"); //获取国内或境外大区域

		if($b_cate)

		{

			foreach($b_cate as $k=>$v)

			{

				$b_cate[$k]['url'] = url("tourlist",array("type"=>$rec_type,"a_py"=>$v['py']));

				$fullkey = format_fulltext_key($v['py']);

				$s_cate = $GLOBALS['db']->getAll("select id,name,py from ".DB_PREFIX."tour_place where is_recommend = 1 and (match(area_match) against('".$fullkey."' IN BOOLEAN MODE)) order by py asc");
             
				if($s_cate)

				{

					foreach($s_cate as $kk=>$vv)

					{

						$s_cate[$kk]['url'] = url("tourlist",array("type"=>$rec_type,"a_py"=>$v['py'],"p_py"=>$vv['py']));

					}

					$b_cate[$k]['s_cate'] = $s_cate;

				}

			}

		}

		//$top_nav_more['b_cate'] = $b_cate;

		

		//获取top_nav

		$top_nav = $GLOBALS['db']->getAll("select id,name,py from ".DB_PREFIX."tour_area where type = $rec_type and is_recommend = 1 order by py asc"); //获取国内或境外大区域

		foreach($top_nav as $k=>$v)

		{

			$top_nav[$k]['url'] = url("tourlist",array("type"=>$rec_type,"a_py"=>$v['py']));

		}

		

		//获取left_nav

		$left_nav = $GLOBALS['db']->getAll("select id,name,py from ".DB_PREFIX."tour_area where type = $rec_type and is_recommend = 1 order by py desc"); //获取国内或境外大区域

		foreach($left_nav as $k=>$v)

		{

			$left_nav[$k]['url'] = url("tourlist",array("type"=>$rec_type,"a_py"=>$v['py']));

		}

		

		

		$condition = " is_effect = 1 and is_recommend = 1 and tour_range = $rec_type ";

		$city_fullkey = format_fulltext_key($GLOBALS['city']['py']);

		$condition.=" and (match(city_match) against('".$city_fullkey."' IN BOOLEAN MODE)) ";

		$condition .= " and ( (tuan_begin_time < ".NOW_TIME." or tuan_is_pre = 1) and (tuan_end_time > ".NOW_TIME." or tuan_end_time = 0 or is_tuan = 0) ) ";

         //print_r($condition);
		//获取image_tour,text_tour

		$image_text_tour = $GLOBALS['db']->getAll("select id,name,price,image from ".DB_PREFIX."tourline where $condition order by sort desc limit 10");
        //print_r($image_text_tour);
		$image_tour=array();

		$text_tour=array();

		foreach($image_text_tour as $k=>$v)

		{

			$image_text_tour[$k]['url'] = url("tours#view",array("id"=>$v['id']));

			$image_text_tour[$k]['price'] = format_price_to_display($v['price']);

			

			if(count($image_tour) <8)

			{

				$image_tour[]=$image_text_tour[$k];//获取image_tour

			}

			else

			{

				$text_tour[]=$image_text_tour[$k];//获取text_tour

			}

		}

		

		$more_url = url("tourlist",array("type"=>$rec_type));

	

	}

	else if($rec_type==3) //周边游

	{

		//获取top_nav_more

		$b_cate = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."tour_place_tag where is_recommend = 1 order by sort asc"); //获取标签列表

		if($b_cate)

		{

			foreach($b_cate as $k=>$v)

			{

				$b_cate[$k]['url'] = url("tourlist#around",array("tag"=>$v['name']));

				$fullkey = str_to_unicode_string($v['name']);

				$s_cate = $GLOBALS['db']->getAll("select id,name,py from ".DB_PREFIX."tour_place where is_recommend = 1 and (match(tag_match) against('".$fullkey."' IN BOOLEAN MODE)) order by py asc");

				if($s_cate)

				{

					foreach($s_cate as $kk=>$vv)

					{

						$s_cate[$kk]['url'] = url("tourlist#around",array("tag"=>$v['name'],"p_py"=>$vv['py']));

					}

					$b_cate[$k]['s_cate'] = $s_cate;

				}

			}

		}

		//$top_nav_more['b_cate'] = $b_cate;

		

		//获取top_nav

		$top_nav = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."tour_place_tag where is_recommend = 1 order by sort asc"); //周边游获取标签

		foreach($top_nav as $k=>$v)

		{

			$top_nav[$k]['url'] = url("tourlist#around",array("tag"=>$v['name']));

		}

		

		//获取left_nav

		$left_nav = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."tour_place_tag where is_recommend = 1 order by sort desc"); //周边游获取标签

		foreach($left_nav as $k=>$v)

		{

			$left_nav[$k]['url'] = url("tourlist#around",array("tag"=>$v['name']));

		}

		

		

		$condition = " is_effect = 1 and is_recommend = 1 and tour_range = 3 ";

		$city_fullkey = format_fulltext_key($GLOBALS['city']['py']);

		$condition.=" and (match(city_match) against('".$city_fullkey."' IN BOOLEAN MODE)) ";

		$condition .= " and ( (tuan_begin_time < ".NOW_TIME." or tuan_is_pre = 1) and (tuan_end_time > ".NOW_TIME." or tuan_end_time = 0 or is_tuan = 0) ) ";

		

		//获取image_tour,text_tour

		$image_text_tour = $GLOBALS['db']->getAll("select id,name,price,image from ".DB_PREFIX."tourline where $condition order by sort desc limit 10");

		$image_tour=array();

		$text_tour=array();

		foreach($image_text_tour as $k=>$v)

		{

			$image_text_tour[$k]['url'] = url("tours#view",array("id"=>$v['id']));

			$image_text_tour[$k]['price'] = format_price_to_display($v['price']);

			

			if(count($image_tour) <8)

			{

				$image_tour[]=$image_text_tour[$k];//获取image_tour

			}

			else

			{

				$text_tour[]=$image_text_tour[$k];//获取text_tour

			}

		}

		

		$more_url = url("tourlist#around");

	

	} 

	else if($rec_type==4||$rec_type==5||$rec_type==6) //跟团游，自助游，户外游

	{

		$t_type = $rec_type - 3;

		//获取top_nav_more

		$b_cate = $GLOBALS['db']->getAll("select id,name,py from ".DB_PREFIX."tour_area where is_recommend = 1 order by py asc"); //获取全部大区域

		if($b_cate)

		{

			foreach($b_cate as $k=>$v)

			{

				$b_cate[$k]['url'] = url("tourlist",array("t_type"=>$t_type,"a_py"=>$v['py']));

				$fullkey = format_fulltext_key($v['py']);

				$s_cate = $GLOBALS['db']->getAll("select id,name,py from ".DB_PREFIX."tour_place where is_recommend = 1 and (match(area_match) against('".$fullkey."' IN BOOLEAN MODE)) order by py asc");

				if($s_cate)

				{

					foreach($s_cate as $kk=>$vv)

					{

						$s_cate[$kk]['url'] = url("tourlist",array("t_type"=>$t_type,"a_py"=>$v['py'],"p_py"=>$vv['py']));

					}

					$b_cate[$k]['s_cate'] = $s_cate;

				}

			}

		}

		//$top_nav_more['b_cate'] = $b_cate;

		

		//获取top_nav

		$top_nav = $GLOBALS['db']->getAll("select id,name,py from ".DB_PREFIX."tour_area where  is_recommend = 1 order by py asc"); //获取大区域

		foreach($top_nav as $k=>$v)

		{

			$top_nav[$k]['url'] = url("tourlist",array("t_type"=>$t_type,"a_py"=>$v['py']));

		}

		

		//获取left_nav

		$left_nav = $GLOBALS['db']->getAll("select id,name,py from ".DB_PREFIX."tour_area where  is_recommend = 1 order by py desc"); //获取大区域

		foreach($left_nav as $k=>$v)

		{

			$left_nav[$k]['url'] = url("tourlist",array("t_type"=>$t_type,"a_py"=>$v['py']));

		}

		

		

		$condition = " is_effect = 1 and is_recommend = 1 and tour_type = $t_type ";

		$city_fullkey = format_fulltext_key($GLOBALS['city']['py']);

		$condition.=" and (match(city_match) against('".$city_fullkey."' IN BOOLEAN MODE)) ";

		$condition .= " and ( (tuan_begin_time < ".NOW_TIME." or tuan_is_pre = 1) and (tuan_end_time > ".NOW_TIME." or tuan_end_time = 0 or is_tuan = 0) ) ";

		

		//获取image_tour,text_tour

		$image_text_tour = $GLOBALS['db']->getAll("select id,name,price,image from ".DB_PREFIX."tourline where $condition order by sort desc limit 10");

		$image_tour=array();

		$text_tour=array();

		foreach($image_text_tour as $k=>$v)

		{

			$image_text_tour[$k]['url'] = url("tours#view",array("id"=>$v['id']));

			$image_text_tour[$k]['price'] = format_price_to_display($v['price']);

			

			if(count($image_tour) <8)

			{

				$image_tour[]=$image_text_tour[$k];//获取image_tour

			}

			else

			{

				$text_tour[]=$image_text_tour[$k];//获取text_tour

			}

		}

		

		$more_url = url("tourlist",array("t_type"=>$t_type));

	

	}

	else if($rec_type==7) //大区推荐

	{

		$tour_area = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tour_area where id = ".$rec_id);

		$fullkey1 = format_fulltext_key($tour_area['py']);

		$city_fullkey = format_fulltext_key($GLOBALS['city']['py']);

		

		if($rec_page==0)

		{

			$route = "tourlist"; //首页

			$add_condition = " 1=1 ";

		}

		else if($rec_page==1||$rec_page==2)

		{

			$param['type'] = $rec_page; //国内，出境

			$route = "tourlist";

			$add_condition = " tour_range =  ".$rec_page." ";

		}

		else if($rec_page==3)

		{

			$route = "tourlist#around";

			$add_condition = " tour_range =  ".$rec_page."  and (match(around_city_match) against('".$city_fullkey."' IN BOOLEAN MODE)) ";

		}

		else 

		{

			$t_type = $rec_page - 3;

			$route = "tourlist";

			$param['t_type'] = $t_type; //跟团游，自助游，户外游

			$add_condition = " tour_type =  ".$t_type." ";

		}

			

			

		//获取top_nav_more

		$b_cate = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."tour_place_tag where is_recommend = 1 order by sort asc"); //标签

		if($b_cate)

		{

			foreach($b_cate as $k=>$v)

			{

				$url_param = $param;

				$url_param['tag'] = $v['name'];

				$b_cate[$k]['url'] = url($route,$url_param);

				

				$fullkey2 = str_to_unicode_string($v['name']);				

				$s_cate = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_place where is_recommend = 1 and (match(area_match) against('".$fullkey1."' IN BOOLEAN MODE)) and (match(tag_match) against('".$fullkey2."' IN BOOLEAN MODE)) order by py asc");

				if($s_cate)

				{

					foreach($s_cate as $kk=>$vv)

					{

						if($rec_page==3)

						{

							$url_param['tag'] = get_first_index($vv['tag_match_row']);

						}

						else

						$url_param['a_py'] = $tour_area['py'];

						$url_param['p_py'] = $vv['py'];

						$s_cate[$kk]['url'] = url($route,$url_param);

					}

					$b_cate[$k]['s_cate'] = $s_cate;

				}

			}

		}

		//$top_nav_more['b_cate'] = $b_cate;

		

		

		//获取top_nav

		$top_nav = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_place where is_recommend = 1 and (match(area_match) against('".$fullkey1."' IN BOOLEAN MODE)) order by py asc"); //获取大区下的小区

		foreach($top_nav as $k=>$v)

		{

			$url_param = $param;

			if($rec_page==3)

			{

				$url_param['tag'] = get_first_index($v['tag_match_row']);

			}

			else

			$url_param['a_py'] = $tour_area['py'];

			$url_param['p_py'] = $v['py'];

			$top_nav[$k]['url'] = url($route,$url_param);

		}

		

		//获取left_nav

		$left_nav = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_place where is_recommend = 1 and (match(area_match) against('".$fullkey1."' IN BOOLEAN MODE)) order by py desc"); //获取大区下的小区

		foreach($left_nav as $k=>$v)

		{

			$url_param = $param;

			if($rec_page==3)

			{

				$url_param['tag'] = get_first_index($v['tag_match_row']);

			}

			else

			$url_param['a_py'] = $tour_area['py'];

			$url_param['p_py'] = $v['py'];

			$left_nav[$k]['url'] = url($route,$url_param);

		}

		

		

		$condition = " is_effect = 1 and is_recommend = 1 and (match(area_match) against('".$fullkey1."' IN BOOLEAN MODE)) ";

		

		$condition.="  and (match(city_match) against('".$city_fullkey."' IN BOOLEAN MODE)) ";

		$condition.=" and ".$add_condition;

		$condition .= " and ( (tuan_begin_time < ".NOW_TIME." or tuan_is_pre = 1) and (tuan_end_time > ".NOW_TIME." or tuan_end_time = 0 or is_tuan = 0) ) ";

		

		//获取image_tour,text_tour

		$image_text_tour = $GLOBALS['db']->getAll("select id,name,price,image from ".DB_PREFIX."tourline where $condition order by sort desc limit 10");

		$image_tour=array();

		$text_tour=array();

		foreach($image_text_tour as $k=>$v)

		{

			$image_text_tour[$k]['url'] = url("tours#view",array("id"=>$v['id']));

			$image_text_tour[$k]['price'] = format_price_to_display($v['price']);

			

			if(count($image_tour) <8)

			{

				$image_tour[]=$image_text_tour[$k];//获取image_tour

			}

			else

			{

				$text_tour[]=$image_text_tour[$k];//获取text_tour

			}

		}

		

		$more_url = url($route,$param);

	

	}

	else if($rec_type==8) //小区推荐

	{

		$tour_place = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tour_place where id = ".$rec_id);

		$fullkey1 = format_fulltext_key($tour_place['py']);

		$city_fullkey = format_fulltext_key($GLOBALS['city']['py']);

		

		if($rec_page==0)

		{

			$route = "tourlist"; //首页

			$add_condition = " 1=1 ";

		}

		else if($rec_page==1||$rec_page==2)

		{

			$param['type'] = $rec_page; //国内，出境

			$route = "tourlist";

			$add_condition = " tour_range =  ".$rec_page." ";

		}

		else if($rec_page==3)

		{

			$route = "tourlist#around";

			$add_condition = " tour_range =  ".$rec_page." and (match(around_city_match) against('".$city_fullkey."' IN BOOLEAN MODE)) ";

		}

		else

		{

			$t_type = $rec_page - 3;

			$route = "tourlist";

			$param['t_type'] = $t_type; //跟团游，自助游，户外游

			$add_condition = " tour_type =  ".$t_type." ";

		}

			

	

	

		//获取top_nav

		$top_nav = $GLOBALS['db']->getAll("select id,name  from ".DB_PREFIX."tour_place_tag where is_recommend = 1  order by sort asc"); //获取标签

		foreach($top_nav as $k=>$v)

		{

			$url_param = $param;

			$url_param['tag'] = $v['name'];

			$top_nav[$k]['url'] = url($route,$url_param);

		}

	

		//获取left_nav

		$left_nav = $GLOBALS['db']->getAll("select id,name  from ".DB_PREFIX."tour_place_tag where is_recommend = 1  order by sort desc"); //获取标签

		foreach($left_nav as $k=>$v)

		{

			$url_param = $param;

			$url_param['tag'] = $v['name'];

			$left_nav[$k]['url'] = url($route,$url_param);

		}

	

	

		$condition = " is_effect = 1 and is_recommend = 1 and (match(place_match) against('".$fullkey1."' IN BOOLEAN MODE)) ";

		

		$condition.="  and (match(city_match) against('".$city_fullkey."' IN BOOLEAN MODE)) ";

		$condition.=" and ".$add_condition;

		$condition .= " and ( (tuan_begin_time < ".NOW_TIME." or tuan_is_pre = 1) and (tuan_end_time > ".NOW_TIME." or tuan_end_time = 0 or is_tuan = 0) ) ";

		

		//获取image_tour,text_tour

		$image_text_tour = $GLOBALS['db']->getAll("select id,name,price,image from ".DB_PREFIX."tourline where $condition order by sort desc limit 10");

		$image_tour=array();

		$text_tour=array();

		foreach($image_text_tour as $k=>$v)

		{

			$image_text_tour[$k]['url'] = url("tours#view",array("id"=>$v['id']));

			$image_text_tour[$k]['price'] = format_price_to_display($v['price']);

			

			if(count($image_tour) <8)

			{

				$image_tour[]=$image_text_tour[$k];//获取image_tour

			}

			else

			{

				$text_tour[]=$image_text_tour[$k];//获取text_tour

			}

		}

	

		$more_url = url($route,$param);

	

	}

	else if($rec_type==9) //标签推荐

	{

		$tour_place_tag = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tour_place_tag where id = ".$rec_id);

		$fullkey1 = str_to_unicode_string($tour_place_tag['name']);

		$city_fullkey = format_fulltext_key($GLOBALS['city']['py']);

			

		

		if($rec_page==0)

		{

			$route = "tourlist"; //首页

			$add_condition = " 1=1 ";

		}

		else if($rec_page==1||$rec_page==2)

		{

			$param['type'] = $rec_page; //国内，出境

			$route = "tourlist";

			$add_condition = " tour_range =  ".$rec_page." ";

		}

		else if($rec_page==3)

		{

			$route = "tourlist#around";

			$add_condition = " tour_range =  ".$rec_page."   and (match(around_city_match) against('".$city_fullkey."' IN BOOLEAN MODE)) ";

		}

		else

		{

			$t_type = $rec_page - 3;

			$route = "tourlist";

			$param['t_type'] = $t_type; //跟团游，自助游，户外游

			$add_condition = " tour_type =  ".$t_type." ";

		}

			

			

		//获取top_nav_more

		$b_cate = $GLOBALS['db']->getAll("select id,name,py from ".DB_PREFIX."tour_area where is_recommend = 1 order by py asc"); //大区域

		if($b_cate)

		{

			foreach($b_cate as $k=>$v)

			{

				$url_param = $param;

				$url_param['a_py'] = $v['py'];

				$b_cate[$k]['url'] = url($route,$url_param);

	

				$fullkey2 = format_fulltext_key($v['py']);

				$s_cate = $GLOBALS['db']->getAll("select id,name,py from ".DB_PREFIX."tour_place where is_recommend = 1 and (match(area_match) against('".$fullkey2."' IN BOOLEAN MODE)) and (match(tag_match) against('".$fullkey1."' IN BOOLEAN MODE)) order by py asc");

				if($s_cate)

				{

					foreach($s_cate as $kk=>$vv)

					{

						$url_param['p_py'] = $vv['py'];

						$s_cate[$kk]['url'] = url($route,$url_param);

					}

					$b_cate[$k]['s_cate'] = $s_cate;

				}

			}

		}

		//$top_nav_more['b_cate'] = $b_cate;

	

	

		//获取top_nav

		$top_nav = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_place where is_recommend = 1 and (match(tag_match) against('".$fullkey1."' IN BOOLEAN MODE)) order by py asc"); //获取大区下的小区

		foreach($top_nav as $k=>$v)

		{

			$url_param = $param;

			if($rec_page==3)

			{

				$url_param['tag'] = $tour_place_tag['name'];

			}

			else

				$url_param['a_py'] = get_first_index_py($v['area_match']);

			

			$url_param['p_py'] = $v['py'];

			$top_nav[$k]['url'] = url($route,$url_param);

		}

	

		//获取left_nav

		$left_nav = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_place where is_recommend = 1 and (match(tag_match) against('".$fullkey1."' IN BOOLEAN MODE)) order by py desc"); //获取大区下的小区

		foreach($left_nav as $k=>$v)

		{

			$url_param = $param;

			if($rec_page==3)

			{

				$url_param['tag'] = $tour_place_tag['name'];

			}

			else

				$url_param['a_py'] = get_first_index_py($v['area_match']);

			$url_param['p_py'] = $v['py'];

			$left_nav[$k]['url'] = url($route,$url_param);

		}

	

	

		$condition = " is_effect = 1 and is_recommend = 1 and (match(tag_match) against('".$fullkey1."' IN BOOLEAN MODE)) ";

		

		$condition.="  and (match(city_match) against('".$city_fullkey."' IN BOOLEAN MODE)) ";

		$condition.=" and ".$add_condition;

		$condition .= " and ( (tuan_begin_time < ".NOW_TIME." or tuan_is_pre = 1) and (tuan_end_time > ".NOW_TIME." or tuan_end_time = 0 or is_tuan = 0) ) ";

		

		//获取image_tour,text_tour

		$image_text_tour = $GLOBALS['db']->getAll("select id,name,price,image from ".DB_PREFIX."tourline where $condition order by sort desc limit 10");

		$image_tour=array();

		$text_tour=array();

		foreach($image_text_tour as $k=>$v)

		{

			$image_text_tour[$k]['url'] = url("tours#view",array("id"=>$v['id']));

			$image_text_tour[$k]['price'] = format_price_to_display($v['price']);

			

			if(count($image_tour) <8)

			{

				$image_tour[]=$image_text_tour[$k];//获取image_tour

			}

			else

			{

				$text_tour[]=$image_text_tour[$k];//获取text_tour

			}

		}

	

		$more_url = url($route,$param);

	

	}

	

	return array(

			"top_nav_more"	=> $top_nav_more,

			"top_nav"	=>	$top_nav,

			"left_nav" =>	$left_nav,

			"image_tour"	=>	$image_tour,

			"text_tour"	=>	$text_tour,

			"more_url" 	=>	$more_url

			);



	

}



//以下弃用

/*

 * 获得线路推荐层

 * 

 * */

function load_tourline_layer($type="area",$id,$color="d_blue",$city_id = 0,$tourline_tour_type=0,$title,$tourline_tour_range=0)

{

	$tourline_tour_type=intval($tourline_tour_type);

	$tourline_tour_range=intval($tourline_tour_range);

	

	$list=array();

	$list['type']=$type;

	$list['color']=$color;

	$list['top_recommend_list']=array();

	$list['all_recommend']=array();

	$list['left_recommend_list']=array();

	$list['big_tourline_list']=array();

	$list['small_tourline_list']=array();

	

	if($type =="tag")/*标签*/

	{

		

	}

	elseif($type =="province")/*省份*/

	{

		

	}

	elseif($type =="domestic")/*国内*/

	{

		$list['title']=$title;

		$area_list = $GLOBALS['db']->getAll("select `id`,`name`,`py` from ".DB_PREFIX."tour_area where type=1 order by is_recommend desc");

		$area_py=array();

		foreach($area_list as $k=>$v)

		{

			$area_py[]=$v['py'];

			

			

			if($k <8)

			{

			  $area_list[$k]['url']=url("tourlist#index",array("type"=>1,"t_type"=>$tourline_tour_type,"a_py"=>$v['py']) );

			  $list['left_recommend_list'][]=$area_list[$k];

			}

		}

		

		$place_list = $GLOBALS['db']->getAll("select `id`,`name`,`py`,`area_match` from ".DB_PREFIX."tour_place where (match(area_match) against('".format_fulltext_key(implode(',',$area_py))."' IN BOOLEAN MODE)) order by is_recommend DESC,py ASC");

		$area_place=array();

		foreach($place_list as $k=>$v)

		{

			$palce_area_py=array();

			$palce_area_py=explode(",",unformat_fulltext_key($v["area_match"]));

			foreach($palce_area_py as $kk=>$vv)

			{

				$area_place[$vv][]=$place_list[$k];

			}

			

			if($k <6)

			{

				$place_list[$k]['url']= url("tourlist#index",array("type"=>1,"t_type"=>$tourline_tour_type,"a_py"=>$palce_area_py[0]['py'],"p_py"=>$v['py']) );

				$list['top_recommend_list']['']=$place_list[$k];

			}

		}

		

		foreach($area_list as $k=>$v)

		{

			$all_recommend=array();

			$all_recommend['name']=$v['name'];

			$all_recommend['url']=url("tourlist#index",array("type"=>1,"t_type"=>$tourline_tour_type,"a_py"=>$v['py']) );

			$all_recommend['all_item'] =$area_place[$v['py']];

			foreach($all_recommend['all_item'] as $kk=>$vv)

			{

				$all_recommend['all_item'][$kk]['url']= url("tourlist#index",array("type"=>1,"t_type"=>$tourline_tour_type,"a_py"=>$v['py'],"p_py"=>$vv['py']) );

			}

			$list['all_recommend'][] = $all_recommend;

		}

		

		/*线路*/

		$tourline_where=" tour_range=1 ";

		if($tourline_tour_type >0)

			$tourline_where .=" and tour_type=".$tourline_tour_type."";

		$tourline=get_layer_number_tourline_list($start_city=0,$areas="",$places="",$belong_citys="",$tags="",$tourline_where,$order="",$limit=8);

		$list['big_tourline_list']=$tourline['big_tourline_list'];

		$list['small_tourline_list']=$tourline['small_tourline_list'];

		

	}

	elseif($type =="outbound")/*出境*/

	{

		

		$list['title']=$title;

		$area_list = $GLOBALS['db']->getAll("select `id`,`name`,`py`,`type` from ".DB_PREFIX."tour_area where type=2");

		$area_py=array();

		foreach($area_list as $k=>$v)

		{

			$area_py[]=$v['py'];

			

			if($k <8)

			{

			  $area_list[$k]['url']=url("tourlist#index",array("type"=>2,"t_type"=>$tourline_tour_type,"a_py"=>$v['py']) );

			  $list['left_recommend_list'][]=$area_list[$k];

			}

		}

		

		$place_list = $GLOBALS['db']->getAll("select `id`,`name`,`py`,`area_match` from ".DB_PREFIX."tour_place where (match(area_match) against('".format_fulltext_key(implode(',',$area_py))."' IN BOOLEAN MODE)) order by is_recommend DESC,py ASC");

		$area_place=array();

		foreach($place_list as $k=>$v)

		{  

			$palce_area_py=array();

			$palce_area_py=explode(",",unformat_fulltext_key($v["area_match"]));

			foreach($palce_area_py as $kk=>$vv)

			{

				$area_place[$vv][]=$place_list[$k];

			}

			

			if($k <6)

			{

				$place_list[$k]['url']= url("tourlist#index",array("type"=>2,"t_type"=>$tourline_tour_type,"a_py"=>$palce_area_py[0]['py'],"p_py"=>$v['py']) );

				$list['top_recommend_list']['']=$place_list[$k];

			}

		}

		foreach($area_list as $k=>$v)

		{

			$all_recommend=array();

			$all_recommend['name']=$v['name'];

			$all_recommend['url']=url("tourlist#index",array("type"=>2,"t_type"=>$tourline_tour_type,"a_py"=>$v['py']) );

			$all_recommend['all_item'] =$area_place[$v['py']];

			foreach($all_recommend['all_item'] as $kk=>$vv)

			{

				$all_recommend['all_item'][$kk]['url']= url("tourlist#index",array("type"=>2,"t_type"=>$tourline_tour_type,"a_py"=>$v['py'],"p_py"=>$vv['py']) );

			}

			$list['all_recommend'][] = $all_recommend;

		}



		/*线路*/

		$tourline_where=" tour_range=2 ";

		if($tourline_tour_type >0)

			$tourline_where .=" and tour_type=".$tourline_tour_type."";

			

		$tourline=get_layer_number_tourline_list($start_city=0,$areas="",$places="",$belong_citys="",$tags="",$tourline_where,$order="",$limit=8);

		$list['big_tourline_list']=$tourline['big_tourline_list'];

		$list['small_tourline_list']=$tourline['small_tourline_list'];

	}

	elseif($type =="around")/*周边*/

	{

		

	}

	else

	{   /*area大区域*/

		$area = $GLOBALS['db']->getRow("select `id`,`name`,`py`,`type` from ".DB_PREFIX."tour_area where py='".$id."'");

		$list['id']=$area['id'];

		$list['title']=$area['name'];

		$list['py']=$area['py'];

		$format_area_py=format_fulltext_key($area['py']);

		$place_list= $GLOBALS['db']->getAll("select `id`,`name`,`py` from ".DB_PREFIX."tour_place where (match(area_match) against('".$format_area_py."' IN BOOLEAN MODE)) order by is_recommend DESC,py ASC");

		foreach((array)$place_list as $kk =>$vv)

		{

			$place_list[$kk]['url']=url("tourlist#index",array("type"=>$area['type'],"t_type"=>$tourline_tour_type,"a_py"=>$area['py'],"p_py"=>$vv['py']) );

		}

		foreach($place_list as $k=>$v)

		{

			if($k <4)

			  $list['top_recommend_list'][]=$v;

			  

			if($k <8)

			  $list['left_recommend_list'][]=$v;

		}

		$list['all_recommend'][0]["name"]=$area['name'];

		$list['all_recommend'][0]["url"]=url("tourlist#index",array("type"=>$area['type'],"t_type"=>$tourline_tour_type,"a_py"=>$area['py']));

		$list['all_recommend'][0]['all_item']=$place_list;

		

		/*线路*/

		$tourline_where=" tour_range= ".$area['type'];

		if($tourline_tour_type >0)

			$tourline_where .=" and tour_type=".$tourline_tour_type."";

		$tourline=get_layer_number_tourline_list($start_city=0,$areas=$area['py'],$places="",$belong_citys="",$tags="",$tourline_where,$order="",$limit=8);

		$list['big_tourline_list']=$tourline['big_tourline_list'];

		$list['small_tourline_list']=$tourline['small_tourline_list'];

	

	}

	

	return $list;

}



/*

 * 获得周边线路推荐层

 * 

 * */

function load_tourline_around_layer($type="tag",$id,$color="d_blue",$city_id = 0,$tourline_tour_type=0,$title)

{

	$tourline_tour_type=intval($tourline_tour_type);

	$city=$GLOBALS['city'];

	$list=array();

	$list['type']=$type;

	$list['color']=$color;

	$list['top_recommend_list']=array();

	$list['all_recommend']=array();

	$list['left_recommend_list']=array();

	$list['big_tourline_list']=array();

	$list['small_tourline_list']=array();

	

	if($type=="tag")

	{

		$list['title']=$id;

		$place_list= $GLOBALS['db']->getAll("select `id`,`name`,`py` from ".DB_PREFIX."tour_place where (match(tag_match) against('".str_to_unicode_string_depart($id)."' IN BOOLEAN MODE)) and  (match(city_match) against('".format_fulltext_key($city['py'])."' IN BOOLEAN MODE)) order by is_recommend DESC,py ASC");

		

		foreach($place_list as $k=>$v)

		{

			$place_list[$k]['url']=url("tourlist#around",array("p_py"=>$v['py'],"tag"=>$id));

			if($k <8)

				$list['left_recommend_list'][]=$place_list[$k];

			if($k>7 && $k <12)

				$list['top_recommend_list'][]=$place_list[$k];

			

		}

		

		$list['all_recommend'][0]["name"]=$id;

		$list['all_recommend'][0]["url"]=url("tourlist#around",array("tag"=>$id));

		$list['all_recommend'][0]['all_item']=$place_list;

		

		/*线路*/

		$tourline_where=" (match(around_city_match) against('".format_fulltext_key($city['py'])."' IN BOOLEAN MODE))";

		if($tourline_tour_type >0)

			$tourline_where .=" and tour_type=".$tourline_tour_type."  and tour_range=3";

		$tourline=get_layer_number_tourline_list($start_city=0,$areas='',$places="",$belong_citys="",$tags=$id,$tourline_where,$order="",$limit=8);

		

		$list['big_tourline_list']=$tourline['big_tourline_list'];

		$list['small_tourline_list']=$tourline['small_tourline_list'];

	

	}

	elseif($type =="around")/*周边*/

	{

		$list['title']=$title;

		$place_list= $GLOBALS['db']->getAll("select `id`,`name`,`py` from ".DB_PREFIX."tour_place where (match(city_match) against('".format_fulltext_key($city['py'])."' IN BOOLEAN MODE)) order by is_recommend DESC,py ASC");

		foreach($place_list as $k=>$v)

		{

			$place_list[$k]['url']=url("tourlist#around",array("p_py"=>$v['py']));

			if($k <8)

				$list['left_recommend_list'][]=$place_list[$k];

			if($k>7 && $k <12)

				$list['top_recommend_list'][]=$place_list[$k];

			

		}

		

		$list['all_recommend'][0]["name"]=$id;

		$list['all_recommend'][0]["url"]=url("tourlist#around");

		$list['all_recommend'][0]['all_item']=$place_list;

		

		/*线路*/

		if($tourline_tour_type >0)

			$tourline_where .=" tour_type=".$tourline_tour_type." and tour_range=3";

		$tourline=get_layer_number_tourline_list($start_city=0,$areas='',$places="",$belong_citys="",'',$tourline_where,$order="",$limit=8);

		

		$list['big_tourline_list']=$tourline['big_tourline_list'];

		$list['small_tourline_list']=$tourline['small_tourline_list'];

		

	}

	

	return $list;

}

//end 弃用



/**

 * 格式化线路时间价格

 * @param array $tourline_item

 * 有永久有效的出游信息放在$tourline_item第一个元素里

 */

function tourline_item_format($tourline_item){

	$tourline_item_array=array();

	if($tourline_item)

	{

		$tourline_item_copy=$tourline_item;

		//is_forever 1表示是永久可以  0表示不是

		if($tourline_item_copy[0]['is_forever'] ==1)//

		{

			$tourline_item_part=array();

			$forever_tourline_item=$tourline_item_copy[0];

			unset($tourline_item_copy[0]);

			$day_tourline_item=array();

			$item_start_time=array();

			foreach($tourline_item_copy as $k=>$v)//获得以出发时间为键值的数组。

			{

				$day_tourline_item["_".$v['start_time']]=$tourline_item_copy[$k];

				$item_start_time[]=$v['start_time'];

			}

			

			for($i=0; $i<62; $i++)//输出两个月永久出游信息

			{

				$one_tourline_item=array();

				$out_time= NOW_TIME + $i*60*60*24;

				$out_time_date=to_date($out_time,'Y-m-d');

				if( in_array($out_time_date,$item_start_time) )

				{

					$one_tourline_item=$day_tourline_item["_".$out_time_date];

					unset($day_tourline_item["_".$out_time_date]);//用完unset掉，剩下就是两个月后的出游信息

				}

				else

				{

					$one_tourline_item=$forever_tourline_item;

					$one_tourline_item['start_time']=$out_time_date;

				}

				

				$tourline_item_part[]=$one_tourline_item;

			}

			

			$day_t_count=count($day_tourline_item);

			if($day_t_count>0)

			{//拼接两个月以后的出游信息

				$keys = range(1, $day_t_count);

				$day_tourline_item_new = array_combine($keys, array_values($day_tourline_item));

				$tourline_item_array=array_merge($tourline_item_part,$day_tourline_item_new);

			}

			else

				$tourline_item_array=$tourline_item_part;

		}

		else

		{

			$tourline_item_array=$tourline_item;

		}

		

		

		foreach($tourline_item_array as $k=>$v)

		{

			$tourline_item_array[$k]['adult_price']=format_price_to_display($v['adult_price']);

			$tourline_item_array[$k]['adult_sale_price']=format_price_to_display($v['adult_sale_price']);

			$tourline_item_array[$k]['child_price']=format_price_to_display($v['child_price']);

			$tourline_item_array[$k]['child_sale_price']=format_price_to_display($v['child_sale_price']);

			

			$time_price="";

			$cur_strtotime=to_timespan($v['start_time']);

			$time_price .=to_date($cur_strtotime,'m-d');//取时间 月日

			$time_price .="(".week_num(to_date($cur_strtotime,'w')).")"; //取时间 是周几

			$time_price .=" ".format_price_to_display($v['adult_price'])."元/人 ".format_price_to_display($v['child_price'])."元/儿童";

			$time_price .="(网上预付:".format_price_to_display($v['adult_sale_price'])."元/人 ".format_price_to_display($v['child_sale_price'])."元/儿童)";

			$tourline_item_array[$k]['time_price']=$time_price;

			

			$tourline_item_array[$k]['id_start_time']=$v['id']."_".$v['start_time'];

		}

	}

	return $tourline_item_array;

}

/*

 * 获取线路时间价格

 * */

function get_tourline_item($tourline_id=0){

	$new_time_date=to_date(get_gmtime(),'Y-m-d');

	

	$sql="select * from ".DB_PREFIX."tourline_item where tourline_id=".intval($tourline_id)." and (start_time >= '".$new_time_date."' or is_forever=1) order by is_forever desc,start_time asc";

	$tourline_item=$GLOBALS['db']->getAll($sql);

	$tourline_item_array=tourline_item_format($tourline_item);

	

	return $tourline_item_array;

}



/**

 * 获取指定线路信息

 * */

function get_tourline($id=0){

	$tourline=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline where id=".intval($id)." ");

	if($tourline)

	{

		$tourline['tour_desc'] = format_html_content_image($tourline['tour_desc'], 760,0);

		$tourline['appoint_desc'] = format_html_content_image($tourline['appoint_desc'], 760,0);

		$tourline['tour_desc_1'] = format_html_content_image($tourline['tour_desc_1'], 760,0);

		$tourline['tour_desc_2'] = format_html_content_image($tourline['tour_desc_2'], 760,0);

		$tourline['tour_desc_3'] = format_html_content_image($tourline['tour_desc_3'], 760,0);

		$tourline['tour_desc_4'] = format_html_content_image($tourline['tour_desc_4'], 760,0);

		

		$tourline['origin_price']=format_price_to_display($tourline['origin_price']);

		$tourline['price']=format_price_to_display($tourline['price']);

		$tourline['return_money']=format_price_to_display($tourline['return_money']);

		$tourline['review_return_money']=format_price_to_display($tourline['review_return_money']);

		$tourline['format_satify']=$tourline['satify']/100;

		$tourline['tourline_item']=get_tourline_item($tourline['id']);

	}

	

	return $tourline;

}



/**

 * 获取指定线路信息

 * */

function get_tourline_supplier($sid=0)

{

	$tourline=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_supplier where id=".intval($sid)." ");

	

	$tourline['tour_desc'] = format_html_content_image($tourline['tour_desc'], 760,0);

	$tourline['appoint_desc'] = format_html_content_image($tourline['appoint_desc'], 760,0);

	$tourline['tour_desc_1'] = format_html_content_image($tourline['tour_desc_1'], 760,0);

	$tourline['tour_desc_2'] = format_html_content_image($tourline['tour_desc_2'], 760,0);

	$tourline['tour_desc_3'] = format_html_content_image($tourline['tour_desc_3'], 760,0);

	$tourline['tour_desc_4'] = format_html_content_image($tourline['tour_desc_4'], 760,0);

	

	$tourline['sid']=$tourline['id'];

	$tourline['id']=0;

	$tourline['origin_price']=format_price_to_display($tourline['origin_price']);

	$tourline['price']=format_price_to_display($tourline['price']);

	$tourline['return_money']=format_price_to_display($tourline['return_money']);

	$tourline['review_return_money']=format_price_to_display($tourline['review_return_money']);

	$tourline['format_satify']=$tourline['satify']/100;

	$tourline_items=unserialize($tourline['tourline_item_cfg']);

	$forever_tourline_item=array();

	$tourline_item_day=array();

	foreach($tourline_items as $k=>$v)

	{

		$tourline_item=unserialize(urldecode($v));

		if($tourline_item['is_forever'] ==1)

		{

			$forever_tourline_item[0]=$tourline_item;

		}

		else

		{

			$tourline_item_day[]=$tourline_item;

		}

	}

	$tourline_item_all=array_merge($forever_tourline_item,$tourline_item_day);

	$tourline['tourline_item']=tourline_item_format($tourline_item_all);

	return $tourline;

}



/*

 * 判断是否隐藏 预付 （是否是预付）

 * $buy_adult_count int 成人购买数

 * $buy_child_count int 儿童购买数

 * $adult_price 成人价

 * $adult_sale_price 成人销售价

 * $child_price 儿童价

 * $child_sale_price 儿童销售价

 * $yufu_hide 0：预付,1:	全款

 * */

function is_yufu_hide($buy_adult_count=0,$buy_child_count=0,$adult_price=0,$adult_sale_price=0,$child_price=0,$child_sale_price=0)

{

	$yufu_hide=0;

	if($buy_adult_count>0 && $buy_child_count ==0)

    {

    	if($adult_price == $adult_sale_price)

    		$yufu_hide=1;

    }

    elseif($buy_adult_count==0 && $buy_child_count >0)

    {

    	if($child_price ==$child_sale_price)

    		$yufu_hide=1;

    }else{

    	if($adult_price == $adult_sale_price && $child_price ==$child_sale_price)

    		$yufu_hide=1;

    }

    

    return $yufu_hide;

}



/**

 * 保存线路订单操作日志

 * @param int $order_id  订单ID

 * @param string $log_info 日志内容

 * @param int $is_supplier 0：会员  1商家操作 2 管理员

 */

function save_tourline_order_log($order_id,$log_info,$is_supplier)

{

	$log_data['order_id'] = $order_id;

	$log_data['log_info'] = $log_info;

	$log_data['is_supplier'] = $is_supplier;

	//$log_data['log_admin'] = intval($adm_session['adm_id']);

	$log_data['log_time'] = NOW_TIME;

	//$log_data['log_ip']	= CLIENT_IP;

	$GLOBALS['db']->autoExecute(DB_PREFIX."tourline_order_log",$log_data);

}



/**

 * 格式化线路订单数据

 * @param array $order

 */

function tourline_order_format(&$order){



	$order['total_price_format'] = format_price(format_price_to_display($order['total_price']));//如果预付\r\nadult_sale_price*adult_count+child_sale_price*child_count\r\n应付总额\r\nadult_sale_price*adult_count+child_sale_price*child_count\r\n+(adult_count+child_count)*insurance_price+visae_price+visa_count*visa_priceP'

	$order['pay_amount_format'] = format_price(format_price_to_display($order['pay_amount']));

	$order['visa_price_format'] = format_price(format_price_to_display($order['visa_price']));//签证单价

	$order['return_money_total_format'] = format_price(format_price_to_display($order['return_money_total']));//购买订单完成后返现金总数

	$order['return_money_format'] = format_price(format_price_to_display($order['return_money']));//购买订单完成后返现金

	$order['refund_amount_format'] = format_price(format_price_to_display($order['refund_amount']));//已退金额(退款包含退人数x单价+人数x保险单价)

	$order['online_pay_format'] = format_price(format_price_to_display($order['online_pay']));//在线支付金额

	$order['voucher_pay_format'] = format_price(format_price_to_display($order['voucher_pay']));//代金券支付(超出部份不显示，如代金券100，应付50，此处为50，但代金券直接失效，超出不退)

	$order['account_pay_format'] = format_price(format_price_to_display($order['account_pay']));//余额支付部份

	$order['pay_amount_format'] = format_price(format_price_to_display($order['pay_amount']));//已付金额(online_pay+voucher_pay+account_pay)

	$order['tourline_total_price_format'] = format_price(format_price_to_display($order['tourline_total_price']));//线路总价：adult_price*adult_count+child_price*child_count+(adult_count+child_count)*insurance_price+visa



	$order['adult_price_format'] = format_price(format_price_to_display($order['adult_price']));

	$order['adult_sale_price_format'] = format_price(format_price_to_display($order['adult_sale_price']));

	$order['child_price_format'] = format_price(format_price_to_display($order['child_price']));

	$order['child_sale_price_format'] = format_price(format_price_to_display($order['child_sale_price']));





	$order['create_time_format'] = to_date($order['create_time']);

	$order['pay_time_format'] = to_date($order['pay_time']);

	$order['verify_time_format'] = to_date($order['verify_time']);

	$order['confirm_time_format'] = to_date($order['confirm_time']);//定单确认时间

	$order['over_time_format'] = to_date($order['over_time']);//全部完成时间

	$order['re_action_time_format'] = to_date($order['re_action_time']);//用户申请退单退票的时间

	$order['supplier_confirm_time_format'] = to_date($order['supplier_confirm_time']);//商家审核时间





	//商家是否通过退款审核:0未确认;1:同意;2:不同意

	if ($order['supplier_confirm'] == 1){

		$order['supplier_confirm_format'] = '同意';

	}else if ($order['supplier_confirm'] == 2){

		$order['supplier_confirm_format'] = '不同意';

	}else{

		$order['supplier_confirm_format'] = '未确认';

	}





	//支付状态

	if ($order['pay_status'] == 1){

		$order['pay_status_format'] = '已支付';

	}else{

		$order['pay_status_format'] = '未支付';

	}



	//订单状态(流程)1.新订单 2.已确认 3.已完成 4.作废\r\n新订单：未确认（包含已付款）的都表示为新订单\r\n已确认：表示为商家或管理员查看，确认手动修改\r\n新订单、已确认均可申请退款，否则不可',

	if ($order['order_status'] == 1){

		$order['order_status_format'] = '新订单';

	}else if ($order['order_status'] == 2){

		$order['order_status_format'] = '确认通过';

	}else if ($order['order_status'] == 3){

		$order['order_status_format'] = '已完成';

	}else if ($order['order_status'] == 4){

		$order['order_status_format'] = '作废';

	}else if ($order['order_status'] == 5){

		$order['order_status_format'] = '确认不通过';

	}else {

		$order['order_status_format'] = '未知';

	}



	//refund_status：0.未申请退款;1:申请退款;2:确认退款;3:拒绝退款;

	if ($order['refund_status'] == 1){

		$order['refund_status_format'] = '申请退款';

	}else if ($order['refund_status'] == 2){

		$order['refund_status_format'] = '已退款';

	}else if ($order['refund_status'] == 3){

		$order['refund_status_format'] = '拒绝退款';

	}else {

		$order['refund_status_format'] = '未申请退款';

	}

	//

	if ($order['verify_time'] == 0){

		$order['is_verify'] = '否';

	}else{

		$order['is_verify'] = '是';

	}



	if ($order['is_verify_code_invalid'] == 0){

		$order['is_invalid'] = '有效';

	}else{

		$order['is_invalid'] = '无效';

	}

		

	//`order_confirm_type` tinyint(1) NOT NULL COMMENT '订单确认方式 1.付款后确认 2.确认后付款,3.自动确认',

	if ($order['order_confirm_type'] == 1){

		$order['order_confirm_type_format'] = '付款后确认';

	}else if ($order['order_confirm_type'] == 2){

		$order['order_confirm_type_format'] = '确认后付款';

	}else if ($order['order_confirm_type'] == 3){

		$order['order_confirm_type_format'] = '自动确认';

	}else {

		$order['order_confirm_type_format'] = '';

	}

}



/**

 * 订单收款处理

 * @param string $order_sn 订单编号

 * @param int $online_pay 在线支付金额(分)

 * @param int $account_pay 余额支付金额(分)

 * @param int $voucher_pay 代金券金额(分)

 * @return 0: 订单未完成全部收款；1：订单已经完成全部收款;

 *

 * 3. 线路下单支付回调流程(需要发邮件，短信以及会员私信通知用户，建立相关邮件短信模板)

 */

function tourline_order_paid($order_sn,$online_pay=0,$account_pay=0,$voucher_pay =0)

{

	$order_info = $GLOBALS['db']->getRow("select id,tourline_id,verify_code,end_time,adult_count,child_count,order_confirm_type from ".DB_PREFIX."tourline_order where sn = '".$order_sn."'");

	$id = intval($order_info['id']);

	$online_pay = intval($online_pay);

	$account_pay = intval($account_pay);

	$voucher_pay = intval($voucher_pay);

	

	$sql = "update ".DB_PREFIX."tourline_order set ".

			"pay_amount = (online_pay + ".$online_pay.") + (account_pay + ".$account_pay.") + (voucher_pay + ".$voucher_pay.")".

			",online_pay = online_pay + ".$online_pay.

			",account_pay = account_pay + ".$account_pay.

			",voucher_pay = voucher_pay + ".$voucher_pay.			

			",pay_time=".NOW_TIME." where  id = ".$id;

	

	$GLOBALS['db']->query($sql,"SILENT");

	$affect_row = $GLOBALS['db']->affected_rows();

	if ($affect_row&&$online_pay > 0){		

		save_tourline_order_log($id,"订单在线支付：".format_price(format_price_to_display($online_pay)),0);

	}



	if ($affect_row&&$account_pay > 0){

		save_tourline_order_log($id,"余额支付：".format_price(format_price_to_display($account_pay)),0);

	}



	if ($affect_row&&$voucher_pay > 0){

		save_tourline_order_log($id,"代金券支付：".format_price(format_price_to_display($voucher_pay)),0);

	}

	

	if($order_info['order_confirm_type'] ==3)

		$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set  pay_status = 1,confirm_time = ".NOW_TIME.",order_status=2 where pay_status = 0 and pay_amount>=total_price and id = ".$id);

	else

		$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set  pay_status = 1 where pay_status = 0 and pay_amount>=total_price and id = ".$id);



	$k = 0;

	if($GLOBALS['db']->affected_rows()>0)

	{		

		$verify_code = trim($order_info['verify_code']);

		if ($verify_code == ''){

			do{

				//在第一次更新pay_status=1时，生成唯一,验证码				

				$verify_code = rand(10000000,99999999);

				$k ++;				

				$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set verify_code = '$verify_code' where id = ".$id,"SILENT");

			}while($GLOBALS['db']->affected_rows()==0 && $k < 100);

		}

		//



		//添加实际销售量

		$adult_count = intval($order_info['adult_count']); 

		$child_count = intval($order_info['child_count']);

		

		$sql = "update ".DB_PREFIX."tourline_item set adult_sale_total = adult_sale_total + $adult_count,child_sale_total = child_sale_total + $child_count where tourline_id = ".$order_info['tourline_id']." and start_time = '".$order_info['end_time']."' limit 1";

		$GLOBALS['db']->query($sql);

		if(!$GLOBALS['db']->affected_rows())

			$GLOBALS['db']->query("update ".DB_PREFIX."tourline_item set adult_sale_total = adult_sale_total + $adult_count,child_sale_total = child_sale_total + $child_count where tourline_id = ".$order_info['tourline_id']." and is_forever =1 limit 1");

			

		

		//团购产品类型1.线路2.门票3.酒店

		$sale_total = intval($GLOBALS['db']->getOne("select sum(adult_sale_total + child_sale_total) from ".DB_PREFIX."tourline_item where tourline_id = ".$order_info['tourline_id']));

		$GLOBALS['db']->query("update ".DB_PREFIX."tourline set sale_total = ".$sale_total." where id = ".$order_info['tourline_id']);

		

		$sale_total = intval($GLOBALS['db']->getOne("select sale_total + sale_virtual_total from ".DB_PREFIX."tourline where id = ".$order_info['tourline_id']));		

		$GLOBALS['db']->query("update ".DB_PREFIX."tuan set sale_total = ".$sale_total." where type = 1 and rel_id = ".$order_info['tourline_id']);

				

				//save_tourline_order_log($id,$sql,1);

		

				$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where sn = '".$order_sn."'");

				//订单支付成功，发短信

				send_order_sms($order_info,1);

				//订单支付成功，发邮件

				send_order_mail($order_info,1);

				

				send_supplier_order_sms($order_info);

					

				send_supplier_order_mail($order_info);

								

		return 1;

	}

	else

	{

		return $order_info['pay_status'];

	}

}



/**

 * 订单确认 操作

 * 订单状态(流程)1.新订单 2.确认通过 3.已完成 4.作废 5.确认不通过

 * @param int $id 订单ID

 *

 * @param int $is_supplier 0：会员；1：商家；2：管理员

 */

function tourline_order_confirm($id,$order_status,$is_supplier)

{

	$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set order_status = ".intval($order_status).",confirm_time = '".NOW_TIME."' where order_status = 1 and id = ".$id." ","SILENT");





	if($GLOBALS['db']->affected_rows()>0 && $order_status == 5){

		save_tourline_order_log($id,'确认订单:不通过',$is_supplier);



		$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where order_status = 5 and refund_status = 0 and is_verify_code_invalid = 0 and verify_time = 0 and id = ".$id);



		if (!empty($order)){



			//订单退款	

			$adult_count = intval($order['adult_count']);

			$child_count = intval($order['child_count']);

			

			$account_pay = intval($order['account_pay']);

			$voucher_pay = intval($order['voucher_pay']);

			$online_pay = intval($order['online_pay']);



			if ($order['pay_status'] == 1){

				//支付成功后,才需要回退销售数量

				$sql = "update ".DB_PREFIX."tourline_item set adult_sale_total = adult_sale_total - $adult_count, child_sale_total = child_sale_total - $child_count where tourline_id = ".$order['tourline_id']." and start_time = '".$order['end_time']."' limit 1";

				$GLOBALS['db']->query($sql);

				save_tourline_order_log($id,"退回销售数量",$is_supplier);

			}



			require_once APP_ROOT_PATH."system/libs/user.php";

			

			//全部退

			if ($account_pay > 0){

				User::modify_account($order['user_id'], 1, $account_pay, "自动处理退款:返回已付余额：".format_price(format_price_to_display($account_pay)).";订单号id:".$id);

				save_tourline_order_log($id,"自动处理退款:返回已付余额：".format_price(format_price_to_display($account_pay)),$is_supplier);

			}

				

			if ($online_pay > 0){

				User::modify_account($order['user_id'], 1, $online_pay, "自动处理退款:返回在线支付金额到用户余额：".format_price(format_price_to_display($online_pay)).";订单号id:".$id);

				save_tourline_order_log($id,"自动处理退款:返回在线支付金额到用户余额：".format_price(format_price_to_display($online_pay)),$is_supplier);

			}

			

			if ($voucher_pay > 0){

				//代金券 fanwe_voucher

				$GLOBALS['db']->query("update ".DB_PREFIX."voucher is_used = 1,use_time = 0 when use_otype = 1 and user_id = ".intval($order['user_id'])." and use_oid = ".$id." limit 1 ","SILENT");

				//User::modify_account($order['user_id'], 1, $online_pay, "订单作废返回在线支付金额到用户余额：".$online_pay);

				save_tourline_order_log($id,"自动处理退款:回返已使用的还代金券",$is_supplier);

			}

			

			if ($account_pay > 0 || $online_pay > 0 || $voucher_pay){

				//有退款时，标识已退款

				$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set is_verify_code_invalid = 1,refund_status = 2,refuse_reason = '订单确认不通过,自动退已经缴的部分' where id = ".$id." ","SILENT");

			}

		}

	}else{

		save_tourline_order_log($id,'确认订单:通过',$is_supplier);

	}



}



/**

 * 订单完成 操作

 * 订单状态(流程)1.新订单 2.已确认 3.已完成 4.作废

 * @param int $id 订单ID

 * @param int $is_supplier  0：会员；1：商家；2：管理员

 * return true 成功; false 失败

 */

function tourline_order_complete($id,$is_supplier)

{

	$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where order_status in (2,5) and id = ".$id);

	if(empty($order))

	{

		//showErr("不是已经确认的订单,不能直接完成",$ajax,admin_url("tourline_order#order",array(id=>$id)))	;

		return false;

	}else{

		//订单完成时，把未使用的验证码设置成：is_verify_code_invalid = 1无效

		//allow_review: verify_time = 0，不允许点评

		//$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set allow_review = case when verify_time = 0 then 0 else 1 end, is_verify_code_invalid = case when verify_time = 0 then 1 else 0 end, order_status = 3,over_time = '".NOW_TIME."' where order_status in (2,5) and id = ".$id." ","SILENT");

		//订单完成时  验证码设置成：is_verify_code_invalid = 1无效

		$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set allow_review = case when verify_time = 0 then 0 else 1 end, is_verify_code_invalid = 1, order_status = 3,over_time = '".NOW_TIME."' where order_status in (2,5) and id = ".$id." ","SILENT");		

		if($GLOBALS['db']->affected_rows()>0){

			//`allow_review` tinyint(1) NOT NULL COMMENT '是否允许点评',

				

			//verify_time = 0，不允许点评

				

			/*

			 `return_money` int(11) NOT NULL COMMENT '购买订单完成后返现金',

			`return_money_total` int(11) NOT NULL COMMENT '购买订单完成后返现金总数',

			`return_score` int(11) NOT NULL COMMENT '返积分',

			`return_score_total` int(11) NOT NULL,

			`return_exp` int(11) NOT NULL,

			`return_exp_total` int(11) NOT NULL,

			`return_voucher_type_id` int(11) NOT NULL COMMENT '购买后返还的代金券',

				

			`refund_child_count` int(11) NOT NULL COMMENT '退单儿童人数',

			`refund_adult_count` int(11) NOT NULL COMMENT '退单成人人数',

			`refund_visa_count` int(11) NOT NULL COMMENT '退签服务人数',



				

			*/

				

			$use_count = intval($order['child_count']) - intval($order['refund_child_count']);

			$use_count = $use_count + intval($order['adult_count']) - intval($order['refund_adult_count']);

			$use_count = $use_count + intval($order['visa_count']) - intval($order['refund_visa_count']);

				

			$return_money_total = intval($order['return_money']) * $use_count;

			$return_score_total = intval($order['return_score']) * $use_count;

			$return_exp_total = intval($order['return_exp']) * $use_count;

				

			$return_voucher_type_id = intval($order['return_voucher_type_id']);

			$user_id = intval($order['user_id']);

			$user_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id);

			//verify_time > 0 and  pay_status = 1  发奖励，返利，允许点评,

			if ($user_data && $order['verify_time'] > 0 && $order['pay_status'] == 1){

				require_once APP_ROOT_PATH."system/libs/user.php";

				if ($return_money_total > 0){

					User::modify_account($user_id, 1, $return_money_total, "购买订单完成后返现金总数：".format_price_to_display($return_money_total));

					save_tourline_order_log($id,"完成订单时返现金总数：".format_price_to_display($return_money_total),$is_supplier);

				}



				if ($return_score_total > 0){

					User::modify_account($user_id, 2, $return_score_total, "购买订单完成后返积分总数：".$return_score_total);

					save_tourline_order_log($id,"完成订单时返积分总数：".$return_score_total,$is_supplier);

				}



				if ($return_exp_total > 0){

					User::modify_account($user_id, 3, $return_exp_total, "购买订单完成后返经验总数：".$return_exp_total);

					save_tourline_order_log($id,"完成订单时返经验总数：".$return_exp_total,$is_supplier);

				}



				//购买后返还的代金券

				if ($return_voucher_type_id > 0){

					require_once APP_ROOT_PATH."system/libs/voucher.php";

					$result = Voucher::gen($return_voucher_type_id, $user_data);

					save_tourline_order_log($id,"完成订单时返代金券：".$result['message'],$is_supplier);

				}



				//推荐人的会员ID，主要用于邀请返利用

				$pid = intval($user_data['pid']);

				$rebate_count = $user_data['rebate_count'];

				$rebate_money = intval(app_conf("REBATE_MONEY"));

				if ($pid > 0 && $rebate_count == 0 && $rebate_money > 0){

					//0:定额;1:按销售价百分比;

					$is_rebate = intval($GLOBALS['db']->getOne("select is_rebate from ".DB_PREFIX."tourline where id = '".$order['tourline_id']."'"));

					$puser_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$pid);

					if($is_rebate==1&&$puser_data)

					{

						if (app_conf("REBATE_TYPE") == 0){

							User::gen_rebate($pid, $user_id, $id, 1, $rebate_money);

							//User::modify_account($pid, 1, $rebate_money, $user_id.":首次购买返利：".format_price_to_display($rebate_money));

							save_tourline_order_log($id,$user_data['user_name'].":首次购买返利给:".$puser_data['user_name'].";".format_price_to_display($rebate_money),$is_supplier);

						}else if (app_conf("REBATE_TYPE") == 1){

							$rebate_money = $rebate_money * $order['total_price'] / 100;

							User::gen_rebate($pid, $user_id, $id, 1, $rebate_money);

							//User::modify_account($pid, 1, $rebate_money, $user_id.":首次购买返利：".format_price_to_display($rebate_money));

							save_tourline_order_log($id,$user_data['user_name'].":首次购买返利给:".$puser_data['user_name'].";".format_price_to_display($rebate_money),$is_supplier);

						}

					}

				}



				//用户成功购买次数加1

				$GLOBALS['db']->query("update ".DB_PREFIX."user set rebate_count = rebate_count + 1 when id = ".$user_id." ","SILENT");

				$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set return_money_total = $return_money_total,return_score_total = $return_score_total,return_exp_total = $return_exp_total where id = ".$id." ","SILENT");



			}else{

				save_tourline_order_log($id,'完成订单时，发现用户id不存',1);

			}

				

			save_tourline_order_log($id,'完成订单',$is_supplier);

				

			return true;

		}

	}

}



/**

 * 订单作废 操作

 * 订单状态(流程)1.新订单 2.已确认 3.已完成 4.作废

 * @param int $id 订单ID

 * @param int $is_supplier  0：会员；1：商家；2：管理员

 * @param int $refund_amount 0:不自动退款   大于0:表示退回用户余额的金额，由外部计算后调用（分）

 * return true 成功; false 失败

 */

function tourline_order_invalid($id,$is_supplier=0,$refund_amount=0)

{

	//订单作废

	$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where order_status <> 4 and id = ".$id);

	if(empty($order))

	{

		//showErr("订单不存在,或已经作废",$ajax,admin_url("tourline_order#order",array(id=>$id)))	;

		return false;

	}else{

		//订单作废 时，把未使用的验证码设置成：is_verify_code_invalid = 1无效

		//$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set order_status = 4,is_verify_code_invalid = case when verify_time = 0 then 1 else 0 end where order_status <>4 and id = ".$id." ","SILENT");

		//订单作废 时，把验证码设置成无效

		$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set order_status = 4,is_verify_code_invalid = 1 where order_status <>4 and id = ".$id." ","SILENT");

		if($GLOBALS['db']->affected_rows()>0){		



			if($refund_amount>0)

			{

				User::modify_account($order['user_id'], 1, $refund_amount, $order['sn']."订单作废，部份付款退回");

				$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set refund_amount = refund_amount + $refund_amount,refund_status = 2 where id = ".$id." ","SILENT");

				save_tourline_order_log($id,"订单作废，自动退款:".format_price(format_price_to_display($refund_amount)),$is_supplier);

			}

			else

			{

				save_tourline_order_log($id,"订单作废",$is_supplier);

			}

			

			/* 作废订单时,不处理自动退款

			//验证码未使用,处理退款;

			// && $order['order_status'] == 1

			$account_pay = intval($order['account_pay']);

			$online_pay = intval($order['online_pay']);

			$voucher_pay = intval($order['voucher_pay']);

			$refund_amount = intval($order['refund_amount']);

			$refund_amount_add = 0;

			$allow_refund = 0;

				

			if ($order['pay_status'] == 0){

				$allow_refund = 1;	//未全额付款时,将部分付款退款回去



				require_once APP_ROOT_PATH."system/libs/user.php";

				if ($account_pay > 0){

					User::modify_account($order['user_id'], 1, $account_pay, "订单作废返回已付余额：".format_price(format_price_to_display($account_pay)));

					save_tourline_order_log($id,"订单作废返回已付余额：".format_price(format_price_to_display($account_pay)),$is_supplier);

				}



				if ($online_pay > 0){

					User::modify_account($order['user_id'], 1, $online_pay, "订单作废返回在线支付金额到用户余额：".format_price(format_price_to_display($online_pay)));

					save_tourline_order_log($id,"订单作废返回在线支付金额到用户余额：".format_price(format_price_to_display($online_pay)),$is_supplier);

				}



				if ($voucher_pay > 0){

					//代金券 fanwe_voucher

					$GLOBALS['db']->query("update ".DB_PREFIX."voucher is_used = 1,use_time = 0 when use_otype = 1 and user_id = ".intval($order['user_id'])." and use_oid = ".$id." limit 1 ","SILENT");



					//User::modify_account($order['user_id'], 1, $online_pay, "订单作废返回在线支付金额到用户余额：".$online_pay);

					save_tourline_order_log($id,"订单作废返回返已使用的还代金券",$is_supplier);

				}



				$refund_amount_add = $account_pay + $online_pay;

			}else if ($order['pay_status'] == 1 && $order['is_expire_refund'] == 1 && ($order['order_status'] == 1 || $order['order_status'] == 2)){

				//is_expire_refund = 1 ，支持过期退，已付款，未使用，订单未完成，未作废

				$allow_refund = 1;



				if ($refund_amount == 0){

					//未有退款金额;

					if ($account_pay > 0){

						User::modify_account($order['user_id'], 1, $account_pay, "订单作废返回已付余额：".format_price(format_price_to_display($account_pay)));

						save_tourline_order_log($id,"订单作废返回已付余额：".format_price(format_price_to_display($account_pay)),$is_supplier);

					}

						

					if ($online_pay > 0){

						User::modify_account($order['user_id'], 1, $online_pay, "订单作废返回在线支付金额到用户余额：".format_price(format_price_to_display($online_pay)));

						save_tourline_order_log($id,"订单作废返回在线支付金额到用户余额：".format_price(format_price_to_display($online_pay)),$is_supplier);

					}



					if ($voucher_pay > 0){

						//代金券 fanwe_voucher

						$GLOBALS['db']->query("update ".DB_PREFIX."voucher is_used = 1,use_time = 0 when use_otype = 1 and user_id = ".intval($order['user_id'])." and use_oid = ".$id." limit 1 ","SILENT");



						//User::modify_account($order['user_id'], 1, $online_pay, "订单作废返回在线支付金额到用户余额：".$online_pay);

						save_tourline_order_log($id,"订单作废返回返已使用的还代金券",$is_supplier);

					}

						

					$refund_amount_add = $account_pay + $online_pay;

				}else{

					//只退，剩下的部分

					$refund_amount_add = $account_pay + $online_pay;

						

				}





			}

				

			if ($refund_amount_add > 0){

				$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set refund_amount = refund_amount + $refund_amount_add where id = ".$id." ","SILENT");

				save_tourline_order_log($id,"订单作废，自动退款:".format_price(format_price_to_display($refund_amount_add)),$is_supplier);

			}else{

				save_tourline_order_log($id,"订单作废",$is_supplier);

			}

			*/

			

			

		}

		return true;

	}

}



/**

 * 订单退款 操作

 * 订单状态(流程)order_status 1.新订单 2.确认通过 3.已完成 4.作废 5.确认不通过

 * 订单退款状态(流程) refund_status：0.未申请退款;1:申请退款;2:确认退款;3:拒绝退款;

 * @param int $id 订单ID

 * @param int $is_supplier 0：会员；1：商家；2：管理员

 * @param int $refund_amount_in 是否退款到用户余额 -1表示自动计算退入余额 0：不退 大于零:退回指定的金额

 * return true 成功; false 失败

 */

function tourline_order_refund($id,$is_supplier,$refund_amount_in=-1)

{

	//订单退款

	$res = array();

	$res['return'] = false;

	$res['message'] = '';

	$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where order_status in (1,2,3,5) and refund_status = 1 and is_verify_code_invalid = 0 and verify_time = 0 and id = ".$id);

	if(empty($order))

	{

		$res['message'] = '订单不存在,或已过期';

	}else{

		//订单退款

		$refund_child_count = intval($order['refund_child_count']);

		$refund_adult_count = intval($order['refund_adult_count']);

		$refund_visa_count = intval($order['refund_visa_count']);



		$adult_count = intval($order['adult_count']);

		$child_count = intval($order['child_count']);

		$visa_count = intval($order['visa_count']);



		$child_sale_price = intval($order['child_sale_price']);

		$adult_sale_price = intval($order['adult_sale_price']);

		$visa_price = intval($order['visa_price']);



		$voucher_pay = intval($order['voucher_pay']);



		$total_price = intval($order['total_price']);



		if ($adult_count >= $refund_adult_count && $child_count >= $refund_child_count && $visa_count >= $refund_visa_count){



			$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set refund_status = 2, order_status = case when order_status = 1 then 2 else order_status end where refund_status = 1 and is_verify_code_invalid = 0 and verify_time = 0 and id = ".$id." ","SILENT");

			if($GLOBALS['db']->affected_rows()>0){

				save_tourline_order_log($id,'订单确认退款',1);

				//退款时，减去购买数量



				$sql = "update ".DB_PREFIX."tourline_item set adult_sale_total = adult_sale_total - $refund_adult_count,child_sale_total = child_sale_total - $refund_child_count where tourline_id = ".$order['tourline_id']." and start_time = '".$order['end_time']."' limit 1";

				$GLOBALS['db']->query($sql);

				

				//团购产品类型1.线路2.门票3.酒店

				$sale_total = intval($GLOBALS['db']->getOne("select sum(adult_sale_total + child_sale_total) from ".DB_PREFIX."tourline_item where tourline_id = ".$order['tourline_id']));

				$GLOBALS['db']->query("update ".DB_PREFIX."tourline set sale_total = ".$sale_total." where id = ".$order['tourline_id']);

				

				$sale_total = intval($GLOBALS['db']->getOne("select sale_total + sale_virtual_total from ".DB_PREFIX."tourline where id = ".$order['tourline_id']));

				$GLOBALS['db']->query("update ".DB_PREFIX."tuan set sale_total = ".$sale_total." where type = 1 and rel_id = ".$order['tourline_id']);



				

				//save_tourline_order_log($id,$sql,1);



				//, order_status = 3,over_time = '".NOW_TIME."',is_verify_code_invalid = 1

				//验证码未使用,处理退款;

				// && $order['order_status'] == 1



					$refund_amount = $refund_child_count * $child_sale_price + $refund_adult_count * $adult_sale_price + $refund_visa_count * $visa_price;

	

					//退款金额，不能超过实际支付的金额(即：需要扣除代金券部分)

					if ($refund_amount > $total_price - $voucher_pay)

						$refund_amount = $total_price - $voucher_pay;

				





				if ($adult_count <= $refund_adult_count && $child_count <= $refund_child_count && $visa_count <= $refund_visa_count){

					//全部退款时，订单直接标识成：作废

					$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set is_verify_code_invalid = 1,order_status = 4, refund_amount = ".$refund_amount." where id = ".$id." ","SILENT");

				}else{

					//非全部退款

					$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set refund_amount = ".$refund_amount." where id = ".$id." ","SILENT");

				}



				if($refund_amount_in==-1)

				{

					if ($refund_amount > 0){

						require_once APP_ROOT_PATH."system/libs/user.php";

						User::modify_account($order['user_id'], 1, $refund_amount, '订单:'.$order['sn']." 退款：".format_price(format_price_to_display($refund_amount)));

					}

				}

				elseif($refund_amount_in>0)

				{

					require_once APP_ROOT_PATH."system/libs/user.php";

					User::modify_account($order['user_id'], 1, $refund_amount_in, '订单:'.$order['sn']." 退款：".format_price(format_price_to_display($refund_amount_in)));

				}

				$res['return'] = true;

				$res['message'] = '退款处理成功';

				

				$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where id = '".$id."'");

				//订单退款成功，发短信

				send_order_refund_sms($order_info);

				//订单退款成功，发邮件

				send_order_refund_mail($order_info);				

			}

		}else{

			$res['message'] = '申请退款人数超出';

		}

	}



	return $res;

}







/**

 * function tourline_order_refund($id,$is_supplier)

{

	//订单退款

	$res = array();

	$res['return'] = false;

	$res['message'] = '';

	$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where order_status in (1,2,3,5) and refund_status = 1 and is_verify_code_invalid = 0 and verify_time = 0 and id = ".$id);

	if(empty($order))

	{

		$res['message'] = '订单不存在,或已过期';

	}else{

		//订单退款

		$refund_child_count = intval($order['refund_child_count']);

		$refund_adult_count = intval($order['refund_adult_count']);

		$refund_visa_count = intval($order['refund_visa_count']);



		$adult_count = intval($order['adult_count']);

		$child_count = intval($order['child_count']);

		$visa_count = intval($order['visa_count']);



		$child_sale_price = intval($order['child_sale_price']);

		$adult_sale_price = intval($order['adult_sale_price']);

		$visa_price = intval($order['visa_price']);



		$voucher_pay = intval($order['voucher_pay']);



		$total_price = intval($order['total_price']);



		if ($adult_count >= $refund_adult_count && $child_count >= $refund_child_count && $visa_count >= $refund_visa_count){



			$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set refund_status = 2, order_status = case when order_status = 1 then 2 else order_status end where refund_status = 1 and is_verify_code_invalid = 0 and verify_time = 0 and id = ".$id." ","SILENT");

			if($GLOBALS['db']->affected_rows()>0){

				save_tourline_order_log($id,'订单确认退款',1);

				//退款时，减去购买数量



				$sql = "update ".DB_PREFIX."tourline_item set adult_sale_total = adult_sale_total - $refund_adult_count,child_sale_total = child_sale_total - $refund_child_count where tourline_id = ".$order['tourline_id']." and start_time = '".$order['end_time']."' limit 1";

				$GLOBALS['db']->query($sql);

				

				//团购产品类型1.线路2.门票3.酒店

				$sale_total = intval($GLOBALS['db']->getOne("select sum(adult_sale_total + child_sale_total) from ".DB_PREFIX."tourline_item where tourline_id = ".$order['tourline_id']));

				$GLOBALS['db']->query("update ".DB_PREFIX."tourline set sale_total = ".$sale_total." where id = ".$order['tourline_id']);

				

				$sale_total = intval($GLOBALS['db']->getOne("select sale_total + sale_virtual_total from ".DB_PREFIX."tourline where id = ".$order['tourline_id']));

				$GLOBALS['db']->query("update ".DB_PREFIX."tuan set sale_total = ".$sale_total." where type = 1 and rel_id = ".$order['tourline_id']);



				

				//save_tourline_order_log($id,$sql,1);



				//, order_status = 3,over_time = '".NOW_TIME."',is_verify_code_invalid = 1

				//验证码未使用,处理退款;

				// && $order['order_status'] == 1

				$refund_amount = $refund_child_count * $child_sale_price + $refund_adult_count * $adult_sale_price + $refund_visa_count * $visa_price;



				//退款金额，不能超过实际支付的金额(即：需要扣除代金券部分)

				if ($refund_amount > $total_price - $voucher_pay)

					$refund_amount = $total_price - $voucher_pay;



				//全部退款时，退回：代金券

				if ($voucher_pay > 0 && $adult_count == $refund_adult_count && $child_count == $refund_child_count && $visa_count == $refund_visa_count){

					//代金券 fanwe_voucher

					$GLOBALS['db']->query("update ".DB_PREFIX."voucher is_used = 1,use_time = 0 when use_otype = 1 and user_id = ".intval($order['user_id'])." and use_oid = ".$id." limit 1 ","SILENT");

						

					//User::modify_account($order['user_id'], 1, $online_pay, "订单作废返回在线支付金额到用户余额：".$online_pay);

					save_tourline_order_log($id,"订单退款返回返已使用的还代金券",$is_supplier);

				}



				if ($adult_count == $refund_adult_count && $child_count == $refund_child_count && $visa_count == $refund_visa_count){

					//全部退款时，订单直接标识成：作废

					$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set is_verify_code_invalid = 1,order_status = 4, refund_amount = ".$refund_amount." where id = ".$id." ","SILENT");

				}else{

					//非全部退款

					$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set refund_amount = ".$refund_amount." where id = ".$id." ","SILENT");

				}



				if ($refund_amount > 0){

					require_once APP_ROOT_PATH."system/libs/user.php";

					User::modify_account($order['user_id'], 1, $refund_amount, '订单:'.$order['sn']." 退款：".format_price(format_price_to_display($refund_amount)));

				}

				$res['return'] = true;

				$res['message'] = '退款处理成功';

				

				$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where id = '".$id."'");

				//订单退款成功，发短信

				send_order_refund_sms($order_info);

				//订单退款成功，发邮件

				send_order_refund_mail($order_info);				

			}

		}else{

			$res['message'] = '申请退款人数超出';

		}

	}



	return $res;

}

 */





/**

 * 验证码标识使用

 * @param int $id 订单ID

 * @param int $verify_code 验证码

 * @param int $is_supplier 是否由商家操作 1是 0否(管理员)

 * @param int $supplier_id 商家ID

 * @return multitype: return:true 验证成功; false:验证失败; message:返回消息内容

 */

function tourline_order_use_verify_code($id,$verify_code,$is_supplier,$supplier_id = 0)

{

	$res = array();

	$res['return'] = false;

	$res['message'] = '';



	if ($supplier_id > 0){

		$sql = "select * from ".DB_PREFIX."tourline_order where order_status <> 4 and id = ".$id." and supplier_id =".$supplier_id;

	}else{

		$sql = "select * from ".DB_PREFIX."tourline_order where order_status <> 4 and id = ".$id;

	}



	$order = $GLOBALS['db']->getRow($sql);

	if(empty($order))

	{

		$res['return'] = false;

		$res['message'] = '订单不存在';

	}else{

		if ($order['verify_code'] <> $verify_code){

			$res['message'] = '验证码错误';

		}else if ($order['is_verify_code_invalid'] == 1){

			$res['message'] = '验证码已失效';

		}else if ($order['verify_time'] > 1){

			$res['message'] = '验证码已被验证';

		}else if ($order['order_status'] == 1){

			$res['message'] = '订单未确认';

		}else if ($order['order_status'] == 3){

			$res['message'] = '订单已完成';

		}else if ($order['order_status'] == 4){

			$res['message'] = '订单已作废';

		}else{

			$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set verify_time = '".NOW_TIME."' where verify_time = 0 and id = ".$id." and verify_code = '".$verify_code."'","SILENT");

			if($GLOBALS['db']->affected_rows()>0){

				$res['return'] = true;

				$res['message'] = '验证成功';

				save_tourline_order_log($id,'验证码验证使用',$is_supplier);

				

				send_use_coupon_sms($order,$verify_code,to_date(NOW_TIME));

				send_use_coupon_mail($order,$verify_code,to_date(NOW_TIME));

			}else{

				$res['message'] = '验证失败';

			}

		}

	}

	return $res;

}





/**

 * 自动退款处理

 * @param unknown_type $order

 * @param unknown_type $is_supplier 0：会员；1：商家；2：管理员

 */

function tourline_auto_refund_amount($order,$is_supplier)

{



	$is_refund = intval($order['is_refund']);//是否支持退，改

	if ($is_refund == 0) return false;//不支持退款;



	$is_expire_refund = intval($order['is_expire_refund']);//是否允许过期退款











}



//获取成交记录

function get_tourline_sale_list($tourline_id,$limit){

	$sql_count = "SELECT count(*) FROM ".DB_PREFIX."tourline_order WHERE tourline_id=".$tourline_id." AND pay_status = 1 and order_status <> 4";

	

	$rs_count = $GLOBALS['db']->getOne($sql_count);

	$list = array();

	if($rs_count > 0){

		$sql = "SELECT a.*,u.user_name FROM ".DB_PREFIX."tourline_order as a " .

			"LEFT JOIN ".DB_PREFIX."user as u ON u.id = a.user_id " .

			"WHERE a.tourline_id=".$tourline_id." AND a.pay_status = 1 and a.order_status <> 4 order by a.id DESC LIMIT ".$limit;

		$list = $GLOBALS['db']->getAll($sql);

		/*

		foreach($list as $k=>$v){	

		}

		*/

	}

	return array("rs_count"=>$rs_count,"list"=>$list);

}





/**

 * 验证订单是否允许退款

 * @param unknown_type $order

 */

function check_allow_refund($order)

{

	$allow_refund = false;//允许退款表单的条件

	if($order['refund_status']<2&&

			(($order['order_status']==1&&$order['pay_status']==1&&$order['total_price']>0) //已付款，有金额的新订单

					||($order['order_status']==2&&$order['is_refund']==1)	//已确认订单，并且支持退款

					||($order['order_status']<=2&&$order['end_time']>0&&(NOW_TIME-to_timespan($order['end_time'],"Y-m-d"))>24*3600&&$order['is_expire_refund']) //过期退

			)

	)

	{

		$allow_refund = true;

	}

	return $allow_refund;

}

?>