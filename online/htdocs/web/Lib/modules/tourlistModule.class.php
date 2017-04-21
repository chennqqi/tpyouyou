<?php
require APP_ROOT_PATH . "system/libs/tourline.php";
class tourlistModule extends BaseModule{

    function index() {
    	global_run();
		$param=array();
		$param['type']=intval($_REQUEST['type']);/*线路区域范围*/
		$param['a_py']=strim($_REQUEST['a_py']);
		$param['p_py']=strim($_REQUEST['p_py']);
		$param['t_type']=intval($_REQUEST['t_type']);/*线路类型*/
		$param['t_day']=strim($_REQUEST['t_day']);
		$param['tag']=strim($_REQUEST['tag']);
		$param['is_hot'] = intval($_REQUEST['is_hot']);
    	$param['is_recommend'] = intval($_REQUEST['is_recommend']);
    	$param['status'] = $status = intval($_REQUEST['status']);
    	$param['order'] = $order = intval($_REQUEST['order']); //0 DESC  1 ASC
    	$param['keyword'] = strim($_REQUEST['keyword']);


    	$param['keyid'] = intval($_REQUEST['keyid']);  ///id

    	if($param['keyword'] != '')
		{    
           
           // $sql = "select py  from ".DB_PREFIX."tour_area where name = ".$param['xianlu'];
            $sql = "select py  from fanwe_tour_area where name ='".$param['keyword']."' ";
		    //echo $sql;exit;
			$listxl = $GLOBALS['db']->getAll($sql);	
			if(!empty($listxl)){
                $param['xianlu']=$listxl[0]['py'];
                $param['keyword']=''; 
			}
            
		}



    	//$param['xianlu'] = strim($_REQUEST['xianlu']);//线路

    	
    	$GLOBALS['tmpl']->assign("price_range_url",url("tourlist#index",$param));//价格区间url
		$min_price = intval($_REQUEST['min_price']);
    	$max_price = intval($_REQUEST['max_price']);
    	if($min_price > $max_price && $max_price > 0)
    	{
    		$min_price = intval($_REQUEST['max_price']);
    		$max_price = intval($_REQUEST['min_price']);
    	}
    	$param['min_price'] = $min_price;
    	$param['max_price'] = $max_price;
    	$db_min_price=format_price_to_db($min_price);
    	$db_max_price=format_price_to_db($max_price);
    	
    	$GLOBALS['tmpl']->assign("param",$param);
    	if($min_price >0)
			$GLOBALS['tmpl']->assign("min_price",$min_price);
		if($max_price >0)
			$GLOBALS['tmpl']->assign("max_price",$max_price);
    
    	/*把所选天数 放在数组里*/
    	if($param['t_day'] !=''){
	    	$t_day_array=array();
			$t_day_array=explode('_',$param['t_day']);
			foreach($t_day_array as $k =>$v)
			{
				$t_day_array[$k]=intval($v);
			}
    	}
    	
    	/*把所选景点 放在数组里*/
    	if($param['p_py'] !=''){
	    	$p_py_array=array();
			$p_py_array=explode('_',$param['p_py']);
			foreach($p_py_array as $k =>$v)
			{
				$p_py_array[$k]=strim($v);
			}
    	}
    	
    	$hot_param=$param;
    	if($param['is_hot'] == 1)
    	{
    		$hot_param['is_hot']=0;
    		
    	}else
    	{
    		$hot_param['is_hot']=1;
    	}
    	$hot_url=url("tourlist#index",$hot_param);
    	
    	$recommend_param=$param;
    	if($param['is_recommend'] == 1)
    	{
    		$recommend_param['is_recommend']=0;
    	}else
    	{
    		$recommend_param['is_recommend']=1;
    	}
    	$recommend_url=url("tourlist#index",$recommend_param);
    	
    	$GLOBALS['tmpl']->assign("hot_url",$hot_url);
    	$GLOBALS['tmpl']->assign("recommend_url",$recommend_url);
    		
    	/*左测筛选列表*/
		$nav_return=load_auto_cache("tourline_tourlist_nav",array("type"=>$param['type'],"a_py"=>$param['a_py']));
		$filter_list=$nav_return['list'];
	
		foreach($filter_list[$param['a_py']]['sub_list'] as $k=>$v)
		{
			if(in_array($v['py'],$p_py_array))
				$filter_list[$param['a_py']]['sub_list'][$k]['act']=1;
			else
				$filter_list[$param['a_py']]['sub_list'][$k]['act']=0;
		}
		$GLOBALS['tmpl']->assign("filter_list",$filter_list);
		/*当前大区域 综合情况(综合满意度)*/
		if($param['a_py'] !='')
		{
			$situation=$filter_list[$param['a_py']]['situation'];
		}
		else
		{
			$cur_city=$GLOBALS['city'];
			if($param['type'] !='')
				$t_where =" and tour_range =".$param['type']."";
			$situation=$GLOBALS['db']->getRow("select count(*) as count,sum(review_total) as review_total_sum,sum(sale_total+sale_virtual_total) as sale_sum,avg(satify) as satify_avg from ".DB_PREFIX."tourline where is_effect=1 {$t_where} and ( city_id=".intval($cur_city['id'])." or (match(city_match) against( '".format_fulltext_key($cur_city['py'])."' IN BOOLEAN MODE)) ) ");
			$satify_avg=$GLOBALS['db']->getOne("select avg(satify) as satify_avg from ".DB_PREFIX."tourline where is_effect=1 and satify >0 {$t_where} and ( city_id=".$cur_city['id']." or (match(city_match) against( '".format_fulltext_key($cur_city['py'])."' IN BOOLEAN MODE)) ) ");
			if($satify_avg <=0)
					$satify_avg=10000;
				$situation['satify_avg'] =round($satify_avg/100,2);
		}
		
		$GLOBALS['tmpl']->assign("situation",$situation);
	
		/*产品类型*/
		$tourline_type=array(
					   array("t_type" =>1,"name"=>"跟团游"),
					   array("t_type" =>2,"name"=>"自助游"),
					   array("t_type" =>3,"name"=>"自驾游")
					   );
		foreach($tourline_type as $k=> $v)
		{
			$t_param=$param;
			$t_param['t_type']=$v['t_type'];
			$tourline_type[$k]['url']=url("tourlist#index",$t_param);
		}
		$GLOBALS['tmpl']->assign("tourline_type",$tourline_type);
		
		/*包含天数*/
		$tourline_day=array();
		$d_param=$param;
		for($i=0;$i<12;$i++)
		{
			$name=intval($i+1);
			$d_param['t_day']=$i+1;
			$tourline_day[$i]["name"]=$i+1;
			$tourline_day[$i]['url']=url("tourlist#index",$d_param);
			if(in_array($name,$t_day_array))
				$tourline_day[$i]['act']=1;
			else
				$tourline_day[$i]['act']=0;
		}
		$d_param['t_day']=0;
		$all_day_url=url("tourlist#index",$d_param);
		$GLOBALS['tmpl']->assign("all_day_url",$all_day_url);
		$GLOBALS['tmpl']->assign("tourline_day",$tourline_day);
		unset($d_param['t_day']);
		$multi_day_url=url("tourlist#index",$d_param);
		$GLOBALS['tmpl']->assign("multi_day_url",$multi_day_url);
		
		/*包含景点*/
		$tourline_jdian=array();
		$tourline_jdian=$filter_list[$param['a_py']]['sub_list'];
		$jd_param=$param;
		foreach($tourline_jdian as $k=>$v)
		{
			$jd_param['p_py']=$v['py'];
			$tourline_jdian[$k]['url']=url("tourlist#index",$jd_param);
			if(in_array($v['py'],$p_py_array))
				$tourline_jdian[$k]['act']=1;
			else
				$tourline_jdian[$k]['act']=0;
		}
		$jd_param['p_py']='';
		$GLOBALS['tmpl']->assign("jd_quanbu_url",url("tourlist#index",$jd_param));
		$GLOBALS['tmpl']->assign("tourline_jdian",$tourline_jdian);
		unset($jd_param['p_py']);
		$multi_jd_url=url("tourlist#index",$jd_param);
		$GLOBALS['tmpl']->assign("multi_jd_url",$multi_jd_url);
		
		
		/*组装排序开始*/
		$sfilter_param = $param;
		
		$sfilter_param['status'] = 0;
		$sfilter_param['order'] =  0;
		$GLOBALS['tmpl']->assign("status_0",0);
		
		$status_url[0] = url("tourlist#index",$sfilter_param);
		$GLOBALS['tmpl']->assign("status_0",$sfilter_param['order'] == 0 ? 1 : 0);
		
		$sfilter_param['status'] = 1;
		if($status == $sfilter_param['status']){
			$sfilter_param['order'] =  $order == 1 ? 0 : 1;
			$GLOBALS['tmpl']->assign("status_1",$order);
		}
		else{
			$sfilter_param['order'] =  0;
			$GLOBALS['tmpl']->assign("status_1",0);
		}
		$status_url[1] = url("tourlist#index",$sfilter_param);
		
		
		$sfilter_param['status'] = 2;
		if($status == $sfilter_param['status']){
			$sfilter_param['order'] =  $order == 1 ? 0 : 1;
			$GLOBALS['tmpl']->assign("status_2",$order);
		}
		else{
			$sfilter_param['order'] =  1;
			$GLOBALS['tmpl']->assign("status_2",1);
		}
		$status_url[2] = url("tourlist#index",$sfilter_param);
		
		$sfilter_param['status'] = 3;
		if($status == $sfilter_param['status']){
			$sfilter_param['order'] =  $order == 1 ? 0 : 1;
			$GLOBALS['tmpl']->assign("status_3",$order);
		}
		else{
			$sfilter_param['order'] =  0;
			$GLOBALS['tmpl']->assign("status_3",0);
		}
		$status_url[3] = url("tourlist#index",$sfilter_param);
		
		$GLOBALS['tmpl']->assign("status",$param['status']);
		$GLOBALS['tmpl']->assign("status_url",$status_url);
		/*组装排序结束*/
		
    	$order_by = " sort DESC, id DESC ";
		$DESC_ASC = $order== 0 ? "DESC" : "ASC";
		switch($status){
			//销量
			case "1":
				$order_by = " sale_total $DESC_ASC, sort DESC, id DESC ";
				break;
			//价格
			case "2":
				$order_by = " price $DESC_ASC, sort DESC, id DESC ";
				break;
			//评价
			case "2":
				$order_by = " satify $DESC_ASC sort ,DESC, id DESC ";
				break;
		}
		
		/* 获取线路列表  */
		
		$page=intval($_REQUEST['p']);
    	if($page==0)
    		$page=1;
    	
    	$pagesize = 20;
		$limit  = (($page - 1) *$pagesize) .",$pagesize";
		
		$city=$GLOBALS['city'];
		$conditions=" 1=1 ";
		
		if($param['type'] >0)
		{
			$conditions .=" and tour_range = ".$param['type']." ";
		}
		
		if($param['t_type'] >0)
		{
			$conditions .=" and tour_type = ".$param['t_type']." ";
		}
		
   	 	if($t_day_array)
		{
			if(in_array(12,$t_day_array))
			{
				$conditions .=" and (tour_total_day in(".implode(',',$t_day_array).") or tour_total_day >12)";
			}
			else
			{
				$conditions .=" and tour_total_day in(".implode(',',$t_day_array).") ";
			}
			
		}
		
    	if( $param['t_day'] ==12)
		{
			$conditions .=" and tour_total_day >= ".$param['t_day']." ";
		}
		
    	if($param['is_hot'] ==1)
		{
			$conditions .=" and is_hot = ".$param['is_hot']." ";
		}
		
    	if($param['is_recommend'] ==1)
		{
			$conditions .=" and is_recommend = ".$param['is_recommend']." ";
		}
		
    	if($db_min_price !=0 || $db_max_price !=0){
			if($db_min_price >0 && $db_max_price > 0){
				$conditions .=" and price >= $db_min_price and price <= $db_max_price ";
			}
			elseif($db_min_price == 0 && $db_max_price > 0){
				$conditions .=" and price <= $db_max_price ";
			}
			elseif($db_min_price > 0 && $db_max_price == 0){
				$conditions .=" and price >= $db_min_price ";
			}
		}
		
		if($param['keyword'] != '')
		{
			$conditions .=" and name like '%".$param['keyword']."%' ";
		}

		if($param['keyid'] != '')
		{
			$conditions .=" and id like '%".$param['keyid']."%' ";
		}
        

        if($param['xianlu'] != '')
		{    
            $conditions .=" and area_match like '%".$param['xianlu']."%' ";
          /*  $sql = "select py  from fanwe_tour_area where name ='".$param['xianlu']."' ";
		    //echo $sql;exit;
			$listxl = $GLOBALS['db']->getAll($sql);	
            $conxl=$listxl[0]['py'];
			$conditions .=" and area_match like '%".$conxl."%' ";*/
		}
		$result=get_tourline_list($start_city=$city['id'],$areas=$param['a_py'],$places=implode(',',$p_py_array),$belong_citys=$city['py'],$param['tag'],$conditions,$order_by,$limit);
		$tourline_list=$result['list'];
		$cache_city_list=load_auto_cache("tour_city_list");
		
		$city_id_list=$cache_city_list['city_id_list'];
		foreach($tourline_list as $k=>$v)
		{
			$tourline_list[$k]['start_city_name']=$city_id_list[$v['city_id']]['name'];
		}
		
		$GLOBALS['tmpl']->assign("tourline_list",$tourline_list);//线路列表
		require APP_ROOT_PATH.APP_NAME.'/Lib/page.php';
		$page = new Page($result['rs_count'],$pagesize);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		/*推荐线路*/
		$rand_tourline="";
    	if($param['type'] >0)
		{
			$recommend_conditions .=" tour_range = ".$param['type']." ";
		}
		$recommend_result=get_number_tourline_list($start_city=$city['id'],$areas=$param['a_py'],$places='',$belong_citys=$city['py'],'',$recommend_conditions,'',3);
		$GLOBALS['tmpl']->assign('recommend_tourline',$recommend_result['list']);
		
		/*销量排行*/
		$topsale_conditions="";
    	if($param['type'] >0)
		{
			$topsale_conditions .=" tour_range = ".$param['type']." ";
		}
		$topsale_result=get_number_tourline_list($start_city=$city['id'],$areas='',$places='',$belong_citys=$city['py'],'',$topsale_conditions,'sale_total desc',10);
		$GLOBALS['tmpl']->assign('topsale_list',$topsale_result['list']);
		
		init_app_page();
		//输出SEO元素
		$GLOBALS['tmpl']->assign("site_name","国内游 - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword","国内游,".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description","国内游,".app_conf("SITE_DESCRIPTION"));
		
		//注册当前地址
		$ur_here[] = array("name"=>"线路列表","url"=>url("tourlist#index"));
		
		if($param['type'] ==1)
		{
			$ur_here[] = array("name"=>"国内旅游","url"=>url("tourlist#index",array("type"=>1)));
		}
		elseif($param['type'] ==2)
		{
		 	$ur_here[] = array("name"=>"出境旅游","url"=>url("tourlist#index",array("type"=>2)));
		}
		if($param['a_py'] !='')
		{
		 	$ur_here[] = array("name"=>$filter_list[$param['a_py']]['name'],"url"=>$filter_list[$param['a_py']]['url']);
		}
		$GLOBALS['tmpl']->assign("ur_here",$ur_here);
		
		$GLOBALS['tmpl']->assign("current_name",$filter_list[$param['a_py']]['name']);
		
		
    	$GLOBALS['tmpl']->display("tourline_tourlist.html");
    }
    
  
    function around(){
    	global_run();
    	$city=$GLOBALS['city'];
    	$param=array();
		$param['type']=3;
		$param['tag']=strim($_REQUEST['tag']);
		$param['a_py']=strim($_REQUEST['a_py']);
		$param['p_py']=strim($_REQUEST['p_py']);
		$param['t_type']=strim($_REQUEST['t_type']);
		$param['t_day']=strim($_REQUEST['t_day']);
		$param['is_hot'] = intval($_REQUEST['is_hot']);
    	$param['is_recommend'] = intval($_REQUEST['is_recommend']);
    	$param['status'] = $status = intval($_REQUEST['status']);
		$param['order'] = $order = intval($_REQUEST['order']); //0 DESC  1 ASC
    	$param['keyword'] = strim($_REQUEST['keyword']);
    	$param['is_hot'] = intval($_REQUEST['is_hot']);
    	$param['is_recommend'] = intval($_REQUEST['is_recommend']);
    	
    	$GLOBALS['tmpl']->assign("price_range_url",url("tourlist#around",$param));//价格区间url
		$min_price = intval($_REQUEST['min_price']);
    	$max_price = intval($_REQUEST['max_price']);
    	if($min_price > $max_price && $max_price > 0)
    	{
    		$min_price = intval($_REQUEST['max_price']);
    		$max_price = intval($_REQUEST['min_price']);
    	}
    	$param['min_price'] = $min_price;
    	$param['max_price'] = $max_price;
    	$db_min_price=format_price_to_db($min_price);
    	$db_max_price=format_price_to_db($max_price);
    	
    	$GLOBALS['tmpl']->assign("param",$param);
    	
    	if($min_price > 0)
			$GLOBALS['tmpl']->assign("min_price",$min_price);
		if($max_price > 0)
			$GLOBALS['tmpl']->assign("max_price",$max_price);
    
    	/*把所选天数 放在数组里*/
    	if($param['t_day'] !=''){
	    	$t_day_array=array();
			$t_day_array=explode('_',$param['t_day']);
			foreach($t_day_array as $k =>$v)
			{
				$t_day_array[$k]=intval($v);
			}
    	}
    	
    	/*把所选景点 放在数组里*/
    	if($param['p_py'] !=''){
	    	$p_py_array=array();
			$p_py_array=explode('_',$param['p_py']);
			foreach($p_py_array as $k =>$v)
			{
				$p_py_array[$k]=strim($v);
			}
    	}
    	
    	$hot_param=$param;
    	if($param['is_hot'] == 1)
    	{
    		$hot_param['is_hot']=0;
    		
    	}else
    	{
    		$hot_param['is_hot']=1;
    	}
    	$hot_url=url("tourlist#around",$hot_param);
    	
    	$recommend_param=$param;
    	if($param['is_recommend'] == 1)
    	{
    		$recommend_param['is_recommend']=0;
    		
    	}else
    	{
    		$recommend_param['is_recommend']=1;
    	}
    	$recommend_url=url("tourlist#around",$recommend_param);
    	
    	$GLOBALS['tmpl']->assign("hot_url",$hot_url);
    	$GLOBALS['tmpl']->assign("recommend_url",$recommend_url);
    	
    	/*左测筛选列表*/
    	$nav_return=load_auto_cache("tourline_tourlist_around_nav",array("city_id"=>$city['id']));
		$filter_list=$nav_return['list'];
    	foreach($filter_list[$param['tag']]['sub_list'] as $k=>$v)
		{
			if(in_array($v['py'],$p_py_array))
				$filter_list[$param['tag']]['sub_list'][$k]['act']=1;
			else
				$filter_list[$param['tag']]['sub_list'][$k]['act']=0;
		}
		$GLOBALS['tmpl']->assign("filter_list",$filter_list);
		
		/*当前大区域 综合情况*/
		$situation=$filter_list[$param['tag']]['situation'];
		
    	if($param['tag'] !='')
		{
			$situation=$filter_list[$param['tag']]['situation'];
		}
		else
		{	
			$cur_city=$GLOBALS['city'];
			if($param['type'] !='')
				$t_where =" and tour_range =".$param['type']."";
			$situation=$GLOBALS['db']->getRow("select count(*) as count,sum(review_total) as review_total_sum,sum(sale_total+sale_virtual_total) as sale_sum from ".DB_PREFIX."tourline where is_effect=1 {$t_where} and ( city_id=".$cur_city['id']." or (match(city_match) against( '".format_fulltext_key($cur_city['py'])."' IN BOOLEAN MODE)) ) ");
			$satify_avg=$GLOBALS['db']->getOne("select avg(satify) as satify_avg from ".DB_PREFIX."tourline where is_effect=1 and satify>0 {$t_where} and ( city_id=".intval($cur_city['id'])." or (match(city_match) against( '".format_fulltext_key($cur_city['py'])."' IN BOOLEAN MODE)) ) ");
			if($satify_avg <=0)
					$satify_avg=10000;
				$situation['satify_avg'] =round($satify_avg/100,2);
		}
		$GLOBALS['tmpl']->assign("situation",$situation);
		/*产品类型*/
		$tourline_type=array(
					   array("t_type" =>0,"name"=>"全部"),
					   array("t_type" =>1,"name"=>"跟团游"),
					   array("t_type" =>2,"name"=>"自助游"),
					   array("t_type" =>3,"name"=>"自驾游")
					   );
		foreach($tourline_type as $k=> $v)
		{
			$t_param=$param;
			$t_param['t_type']=$v['t_type'];
			$tourline_type[$k]['url']=url("tourlist#around",$t_param);
		}
		$GLOBALS['tmpl']->assign("tourline_type",$tourline_type);
		
		/*包含天数*/
		$tourline_day=array();
		$d_param=$param;
		for($i=0;$i<12;$i++)
		{
			$name=intval($i+1);
			$d_param['t_day']=$i+1;
			$tourline_day[$i]["name"]=$i+1;
			$tourline_day[$i]['url']=url("tourlist#around",$d_param);
			if(in_array($name,$t_day_array))
				$tourline_day[$i]['act']=1;
			else
				$tourline_day[$i]['act']=0;
		}
		$d_param['t_day']=0;
		$all_day_url=url("tourlist#around",$d_param);
		$GLOBALS['tmpl']->assign("all_day_url",$all_day_url);
		$GLOBALS['tmpl']->assign("tourline_day",$tourline_day);
		unset($d_param['t_day']);
		$multi_day_url=url("tourlist#around",$d_param);
		$GLOBALS['tmpl']->assign("multi_day_url",$multi_day_url);
		
		/*包含景点*/
		$tourline_jdian=array();
		$tourline_jdian=$filter_list[$param['tag']]['sub_list'];
		$jd_param=$param;
		foreach($tourline_jdian as $k=>$v)
		{
			$jd_param['p_py']=$v['py'];
			$tourline_jdian[$k]['url']=url("tourlist#around",$jd_param);
			if(in_array($v['py'],$p_py_array))
				$tourline_jdian[$k]['act']=1;
			else
				$tourline_jdian[$k]['act']=0;
		}
		$jd_param['p_py']='';
		$GLOBALS['tmpl']->assign("jd_quanbu_url",url("tourlist#around",$jd_param));
		$GLOBALS['tmpl']->assign("tourline_jdian",$tourline_jdian);
		unset($jd_param['p_py']);
		$multi_jd_url=url("tourlist#around",$jd_param);
		$GLOBALS['tmpl']->assign("multi_jd_url",$multi_jd_url);
		
		/*组装排序开始*/
		$sfilter_param = $param;
		
		$sfilter_param['status'] = 0;
		$sfilter_param['order'] =  0;
		$GLOBALS['tmpl']->assign("status_0",0);
		
		$status_url[0] = url("tourlist#around",$sfilter_param);
		$GLOBALS['tmpl']->assign("status_0",$sfilter_param['order'] == 0 ? 1 : 0);
		
		$sfilter_param['status'] = 1;
		if($status == $sfilter_param['status']){
			$sfilter_param['order'] =  $order == 1 ? 0 : 1;
			$GLOBALS['tmpl']->assign("status_1",$order);
		}
		else{
			$sfilter_param['order'] =  0;
			$GLOBALS['tmpl']->assign("status_1",0);
		}
		$status_url[1] = url("tourlist#around",$sfilter_param);
		
		
		$sfilter_param['status'] = 2;
		if($status == $sfilter_param['status']){
			$sfilter_param['order'] =  $order == 1 ? 0 : 1;
			$GLOBALS['tmpl']->assign("status_2",$order);
		}
		else{
			$sfilter_param['order'] =  1;
			$GLOBALS['tmpl']->assign("status_2",1);
		}
		$status_url[2] = url("tourlist#around",$sfilter_param);
		
		$sfilter_param['status'] = 3;
		if($status == $sfilter_param['status']){
			$sfilter_param['order'] =  $order == 1 ? 0 : 1;
			$GLOBALS['tmpl']->assign("status_3",$order);
		}
		else{
			$sfilter_param['order'] =  0;
			$GLOBALS['tmpl']->assign("status_3",0);
		}
		$status_url[3] = url("tourlist#around",$sfilter_param);
		
		$GLOBALS['tmpl']->assign("status",$param['status']);
		$GLOBALS['tmpl']->assign("status_url",$status_url);
		/*组装排序结束*/
		
    	$order_by = " sort DESC, id DESC ";
		$DESC_ASC = $order== 0 ? "DESC" : "ASC";
		switch($status){
			//销量
			case "1":
				$order_by = " sale_total $DESC_ASC, sort DESC, id DESC ";
				break;
			//价格
			case "2":
				$order_by = " price $DESC_ASC, sort DESC, id DESC ";
				break;
			//评价
			case "2":
				$order_by = " satify $DESC_ASC sort ,DESC, id DESC ";
				break;
		}
		
		/* 获取线路列表  */
		
		$page=intval($_REQUEST['p']);
    	if($page==0)
    		$page=1;
    	
    	$pagesize = 20;
		$limit  = (($page - 1) *$pagesize) .",$pagesize";
		
		$city=$GLOBALS['city'];
		$conditions=" (match(around_city_match) against('".format_fulltext_key($city['py'])."' IN BOOLEAN MODE)) ";
		
		if($param['tag']!="")
		$conditions.="  and (match(tag_match) against('".str_to_unicode_string_depart($param['tag'])."' IN BOOLEAN MODE)) ";
		
		if($param['type'] >0)
		{
			$conditions .=" and tour_range = ".$param['type']." ";
		}
		
    	if($param['t_type'] >0)
		{
			$conditions .=" and tour_type = ".$param['t_type']." ";
		}
		
   	 	if($t_day_array)
		{
			if(in_array(12,$t_day_array))
			{
				$conditions .=" and (tour_total_day in(".implode(',',$t_day_array).") or tour_total_day >12)";
			}
			else
			{
				$conditions .=" and tour_total_day in(".implode(',',$t_day_array).") ";
			}
			
		}
		
    	if( $param['t_day'] ==12)
		{
			$conditions .=" and tour_total_day >= ".$param['t_day']." ";
		}
		
    	if($param['is_hot'] ==1)
		{
			$conditions .=" and is_hot = ".$param['is_hot']." ";
		}
		
    	if($param['is_recommend'] ==1)
		{
			$conditions .=" and is_recommend = ".$param['is_recommend']." ";
		}
		
    	if($db_min_price !=0 || $db_max_price !=0){
			if($db_min_price >0 && $db_max_price > 0){
				$conditions .=" and price >= $db_min_price and price <= $db_max_price ";
			}
			elseif($db_min_price == 0 && $db_max_price > 0){
				$conditions .=" and price <= $db_max_price ";
			}
			elseif($db_min_price > 0 && $db_max_price == 0){
				$conditions .=" and price >= $db_min_price ";
			}
		}
		
    	if($param['keyword'] != '')
		{
			$conditions .=" and name like '%".$param['keyword']."%' ";
		}
		
		$result=get_tourline_list($start_city=0,$areas=$param['a_py'],$places=implode(',',$p_py_array),$belong_citys='',$param['tag'],$conditions,$order_by,$limit);
		$tourline_list=$result['list'];
		$cache_city_list=load_auto_cache("tour_city_list");
	
		$city_id_list=$cache_city_list['city_id_list'];
		foreach($tourline_list as $k=>$v)
		{
			$tourline_list[$k]['start_city_name']=$city_id_list[$v['city_id']]['name'];
		}
		
		$GLOBALS['tmpl']->assign("tourline_list",$tourline_list);
		require APP_ROOT_PATH.APP_NAME.'/Lib/page.php';
		$page = new Page($result['rs_count'],$pagesize);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		/*推荐线路*/
		$recommend_conditions="";
    	if($param['type'] >0)
		{
			$recommend_conditions .="tour_range = ".$param['type']." ";
		}
		$recommend_result=get_number_tourline_list($start_city=$city['id'],$areas=$param['a_py'],$places='',$belong_citys=$city['py'],$param['tag'],$recommend_conditions,'',3);
		$GLOBALS['tmpl']->assign('recommend_tourline',$recommend_result['list']);
		
		/*销量排行*/
		$topsale_conditions="";
    	if($param['type'] >0)
		{
			$topsale_conditions .=" tour_range = ".$param['type']." ";
		}
		$topsale_result=get_number_tourline_list($start_city=$city['id'],$areas='',$places='',$belong_citys=$city['py'],'',$topsale_conditions,'sale_total desc',10);
		$GLOBALS['tmpl']->assign('topsale_list',$topsale_result['list']);
		
    	init_app_page();
		//输出SEO元素
		$GLOBALS['tmpl']->assign("site_name","国内游 - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword","国内游,".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description","国内游,".app_conf("SITE_DESCRIPTION"));
		
		//注册当前地址
		$ur_here[] = array("name"=>"周边旅游","url"=>url("tourlist#around"));
    	if($param['tag'] !='')
		{
		 	$ur_here[] = array("name"=>$filter_list[$param['tag']]['name'],"url"=>$filter_list[$param['tag']]['url']);
		}
		
		$GLOBALS['tmpl']->assign("ur_here",$ur_here);
		
		
		$GLOBALS['tmpl']->assign("current_name",$filter_list[$param['tag']]['name']);
	
    	$GLOBALS['tmpl']->display("tourline_tourlist_around.html");
    }
}
?>