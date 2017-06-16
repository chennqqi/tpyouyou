<?php
require APP_ROOT_PATH . "system/libs/tourline.php";
class toursModule extends BaseModule{

    function index() {
    	global_run();
		$GLOBALS['tmpl']->caching = true;
		$GLOBALS['tmpl']->cache_lifetime = 600;  //首页缓存10分钟
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['city']['py']);
		
		if (!$GLOBALS['tmpl']->is_cached('tourline_tours_index.html', $cache_id))

		{	
			//输出推荐线路

			$recommend_tourline_layer = array();

			require_once APP_ROOT_PATH."system/libs/tourline.php";

			

			$recommend_tourlist = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tourline_rec_config where rec_page = 4 order by rec_sort asc");

				

			foreach($recommend_tourlist as $k=>$v)

			{

				$rec_result = load_tourline_rec(4, $v['rec_type'], $v['rec_id']);

				$rec_result['name'] = $v['rec_name'];

				$rec_result['color'] = $v['rec_color'];

				$rec_result['adv1'] = $v['rec_adv1'];

				$rec_result['adv1_url'] = $v['rec_adv1_url'];

				$rec_result['adv2'] = $v['rec_adv2'];

				$rec_result['adv2_url'] = $v['rec_adv2_url'];

				$GLOBALS['tmpl']->assign("rec_result",$rec_result);

				$recommend_tourline_layer[] = $GLOBALS['tmpl']->fetch("inc/recommend_channel_tourline.html");

			}

				

			$GLOBALS['tmpl']->assign("recommend_tourline_layer",$recommend_tourline_layer);
		
			init_app_page();
			//输出SEO元素
			$GLOBALS['tmpl']->assign("site_name","跟团游 - ".app_conf("SITE_NAME"));
			$GLOBALS['tmpl']->assign("site_keyword","跟团游,".app_conf("SITE_KEYWORD"));
			$GLOBALS['tmpl']->assign("site_description","跟团游,".app_conf("SITE_DESCRIPTION"));
		}
    	$GLOBALS['tmpl']->display("tourline_tours_index.html",$cache_id);
    }
    
    function view(){
    	set_gopreview();
    	global_run();
    	init_app_page();
    	$id = intval($_REQUEST['id']);
    	$preview=intval($_REQUEST['preview']);
    	$sid=intval($_REQUEST['sid']);
    	
    	if($id == 0 && $sid==0){
    		showErr("请选择线路",0,url("index"));
    	}
    	
    	/*线路*/
    	if($sid >0)
    	{
    		$tourline = get_tourline_supplier($sid);
            $is_cruise=$GLOBALS['db']->getOne("select is_cruise from ".DB_PREFIX."tourline_supplier where id=".intval($sid)." ");
    		$tourline_id = intval($tourline['tourline_id']);
    	}
    	else
    	{
    		$tourline=get_tourline($id);
            $is_cruise=$GLOBALS['db']->getOne("select is_cruise from ".DB_PREFIX."tourline where id=".intval($id)." ");
    		$tourline_id=intval($tourline['id']);
    	}


        $GLOBALS['tmpl']->assign("is_cruise",$is_cruise);
    		
    	if(!$tourline)
    		showErr("线路不存在",0,url("index"));
    		
    	if(!$preview)
    	{	
	    	if($tourline['is_effect'] ==0)
	    		showErr("线路已下架",0,url("index"));
    	}

        // 如果是邮轮信息，则将邮轮中的舱位信息引入
        if ($is_cruise == 1) {
            # code...
            $cabin_info = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."cabin where cruise_id=".$tourline_id." order by id");
            $GLOBALS['tmpl']->assign("cabin_info",$cabin_info);
        }
    	
    	if($tourline['is_tuan'])
    	{
    		
    		if($sid >0)
    		{
    			app_redirect(url("tuan#detail",array("sid"=>$sid,"preview"=>1,"type"=>1)));
    		}
    		else
    		{
    			$tuan_id = $GLOBALS['db']->getOne("select id FROM ".DB_PREFIX."tuan where rel_id=".$tourline['id']." and type=1 ");
    			if($preview ==1)
    		    	app_redirect(url("tuan#detail",array("did"=>$tuan_id)));
    		    else
    		    	app_redirect(url("tuan#detail",array("did"=>$tuan_id,"preview"=>1)));
    		}
    	}
    		
    	$city_list=load_auto_cache("tour_city_list");
    	$tourline['start_city_name']=$city_list['city_id_list'][$tourline['city_id']]['name'];
    	
    	//返利显示
    	$return_conf=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."return_conf");
    	if($return_conf['REBATE_MONEY'] >0)
    	{
    		if($return_conf['REBATE_TYPE']==1)
    			$return_conf['REBATE_MONEY_VAL']=$return_conf['REBATE_MONEY']."%";
    		else
    			$return_conf['REBATE_MONEY_VAL']=format_price_to_display($return_conf['REBATE_MONEY']);
    	}
    	$GLOBALS['tmpl']->assign("return_conf",$return_conf);
    	if($tourline['is_buy_return'] ==1)
    	{
    		if($tourline['return_money'] >0)
    		{
    			$tourline['return_money_val']=$tourline['return_money'];
    		}
	    	else
	    	{
	    		if($return_conf['BUY_RETURN_MONEY_TYPE']==1)
    				$tourline['return_money_val']=$return_conf['BUY_RETURN_MONEY']."%";
	    		else
	    			$tourline['return_money_val']=format_price_to_display($return_conf['BUY_RETURN_MONEY']);
	    		
	    	}
	    	
	    	if($tourline['return_score'] >0)
	    	{
	    		$tourline['return_score_val']=$tourline['return_score'];
	    	}
	    	else
	    	{
	    		if($return_conf['BUY_RETURN_SCORE_TYPE']==1)
    				$tourline['return_score_val']=$return_conf['BUY_RETURN_SCORE']."%";
	    		else
	    			$tourline['return_score_val']=$return_conf['BUY_RETURN_SCORE'];
	    	}
    		
    	}
    	
    	if($tourline['is_review_return'] == 1)
    	{
    		$tourline['review_return_money'] = $tourline['review_return_money'] > 0 ? $tourline['review_return_money'] : format_price_to_display(app_conf("REVIEW_MONEY"));
    	}
        $GLOBALS['tmpl']->assign("tourline",$tourline);
    	
    	/*右边区域筛选列表*/
    	$nav_return=load_auto_cache("tourline_tourlist_nav",array("type"=>$tourline['type_range']));
    	$filter_list=$nav_return['list'];
    	$GLOBALS['tmpl']->assign("filter_list",$filter_list);
    	
		/*获取商家信息*/
    	if($tourline['supplier_id'] > 0){
    		require APP_ROOT_PATH.'system/libs/supplier.php';
    		$supplier = get_supplier($tourline['supplier_id']);
    		$GLOBALS['tmpl']->assign("supplier",$supplier);
    	}
        
    	/*select 人数*/
    	$dmin_i=0;
    	$dmax_i=50;
    	$select_adult_people =array();
    	for($i=$dmin_i; $i<=$dmax_i; $i++)
    	{
    		$select_adult_people[$i]['value']=$i;
    	}
    	
    	$cmin_i=0;
    	$cmax_i=50;
    	$select_child_people =array();
    	for($i=$cmin_i; $i<=$cmax_i; $i++)
    	{
    		$select_child_people[$i]['value']=$i;
    	}
    	
    	$GLOBALS['tmpl']->assign("select_adult_people",$select_adult_people);
    	$GLOBALS['tmpl']->assign("select_child_people",$select_child_people);

		
		/*日历json数据*/
    	$json_item=array();
		foreach($tourline['tourline_item'] as $k=>$v)
		{
			$title= "位置> 9";
			if($v['adult_limit'] >0 || $v['child_limit']>0){
    			$position_num=($v['adult_limit']+$v['child_limit'])-($v['child_sale_total']+$v['adult_sale_total']);
    			$position_num=$position_num<0?0:$position_num;
    		    if($position_num <=9)
    		    	$title= "位置 ".$position_num;
    		}else{
    			$position_num=9;
    		}
			
				
			if($position_num>0){
			    $json_item[]=array(
					'id' => $v['id_start_time'],
					'title' => "".$v['adult_price']."元",
					'start' => $v['start_time'],
					'textColor' => "red",
				);
			    $json_item[]=array(
					'id' => $v['id_start_time'],
					'title' => $title,
					'start' => $v['start_time'],
					'textColor' => "#999",
					'content'=> $v['brief']					
			    );    					
	    	}
		}
		
		$GLOBALS['tmpl']->assign("json_item",json_encode($json_item));
		$GLOBALS['tmpl']->assign("tourline_item_array",json_encode($tourline['tourline_item']));
		//订单提交页链接
        if ($is_cruise == 1) {
		  $GLOBALS['tmpl']->assign('tourline_order_url',url("cruise_order#index"));
        } else {
          $GLOBALS['tmpl']->assign('tourline_order_url',url("tourline_order#index"));
        }
		
    	//成交记录
    	if($tourline['show_sale_list'] && $id >0){
    		$sale_result = toursModule::ajax_sale_list();
    		$GLOBALS['tmpl']->assign("sale_result",$sale_result);
    		unset($sale_result);
    	}
    	
    	//载入点评模块
    	if($id >0)
    	{
	        require_once APP_ROOT_PATH.'system/libs/review.php';
	        $review_html = Review::init_review(1,$id);
	        $GLOBALS['tmpl']->assign('review_html',$review_html);
    	}


        /* 商家其他线路*/

       //print_r($supplier);
       $condition = " supplier_id =  ".$supplier['id']." and is_effect=1 ";
       $suplier_otherlist = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tourline where ".$condition."  order by id");
        foreach($suplier_otherlist as $k=>$v)
            {   $suplier_otherlist[$k]['view_url'] = url("tours#view",array("id"=>$v['id']));  
                $suplier_otherlist[$k]['price'] = ($v['price']/100);      

            }
       $GLOBALS['tmpl']->assign('suplier_otherlist',$suplier_otherlist);
    
        /*销量排行*/
        $city=$GLOBALS['city'];
		$topsale_conditions .=" tour_range = ".$tourline['tour_range']." ";
		$topsale_result=get_number_tourline_list($start_city=$city['id'],$areas='',$places='',$belong_citys=$city['py'],'',$topsale_conditions,'sale_total desc',10);
		$GLOBALS['tmpl']->assign('topsale_list',$topsale_result['list']);
		
		/*猜你喜欢*/
		$recommend_conditions .=" tour_range = ".$tourline['tour_range']." ";
		$recommend_result=get_number_tourline_list($start_city=$city['id'],$areas=$param['a_py'],$places='',$belong_citys=$city['py'],'',$recommend_conditions,'',10);
		$GLOBALS['tmpl']->assign('rand_tourline',$recommend_result['list']);
		
		//注册当前地址
		if($tourline['tour_range'] ==3)
		{
			$ur_here[] = array("name"=>"周边旅游","url"=>url("tourlist#around"));
			$ur_here[] = array("name"=>$tourline['short_name']);
		}
		else
		{
			$ur_here[] = array("name"=>"线路列表","url"=>url("tourlist#index"));
			$ur_here[] = array("name"=>$tourline['short_name']);
		}
		$GLOBALS['tmpl']->assign('ur_here',$ur_here);
		/*输出SEO元素*/
		if($tourline['seo_title'] !='')
			$site_name=$tourline['seo_title'];
		else
			$site_name=$tourline['name'];

		if($tourline['seo_keywords'] !='')
			$seo_keywords=$tourline['seo_keywords'];
		else
			$seo_keywords=$tourline['city_match_row'].",".$tourline['area_match_row'].",".$tourline['place_match_row'].",".$tourline['tag_match_row'].",线路详情";
			
    	if($tourline['seo_description'] !='')
    		$seo_description=$tourline['seo_description'];
		else
			$seo_description=$tourline['brief'];
			
		$GLOBALS['tmpl']->assign("site_name",$site_name."-线路详情 - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword",$seo_keywords.",".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description",$seo_description.",".app_conf("SITE_DESCRIPTION"));
		
    	$GLOBALS['tmpl']->display("tourline_view.html");
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
		$tourline_id = intval($_REQUEST['id']);
		if($tourline_id == 0)
			exit();
		$limit  = (($page - 1) *$pagesize) .",$pagesize";
		
    	$rs = get_tourline_sale_list($tourline_id,$limit);
		
		$pager = buildPage("tours#ajax_sale_list",array('id'=>$tourline_id,"is_ajax"=>1),$rs['rs_count'],$page,$pagesize,1);
      
        $GLOBALS['tmpl']->assign('pager',$pager);
        $result['pager'] = $GLOBALS['tmpl']->fetch("inc/pages.html");
		
		$GLOBALS['tmpl']->assign("is_ajax",$is_ajax);
		
		$GLOBALS['tmpl']->assign("sale_list",$rs['list']);
		
		$result['html'] = $GLOBALS['tmpl']->fetch("inc/tourline/sale_list.html");
    	if($is_ajax == 1){
    		ajax_return($result);
    	}
    	else{
    		return $result;
    	}
    	
    }
     
    /*
    下载
    ***/

    function word_trour(){

         $tourline_id = intval($_REQUEST['id']);
         $tourline=get_tourline($tourline_id);
         $tour_desc=$tourline['tour_desc'];
         $filename = $_REQUEST['name'];
         /* header('content-disposition:attachment;filename='.basename($filename));  //通过附件  文件名
         header('content-length:'.filesize($filenamte)); //大小
         readfile($tour_desc);  //读取文件内容*/
        // echo substr($tour_desc,0,3000);
         //echo mb_substr($tour_desc,0,4000,'utf-8');
         //echo mb_substr($tour_desc,2000,2000,'utf-8');
         echo $tour_desc;
    	 ob_start(); //打开缓冲区 
		 header("Cache-Control: public"); 
		 Header("Content-type: application/octet-stream"); 
		 Header("Accept-Ranges: bytes"); 
		 if (strpos($_SERVER["HTTP_USER_AGENT"],'MSIE')) { 
		 header("Content-Disposition: attachment; filename=test.doc"); 
		 }else if (strpos($_SERVER["HTTP_USER_AGENT"],'Firefox')) { 
		 Header("Content-Disposition: attachment; filename=test.doc"); 
		 } else { 
		 header("Content-Disposition: attachment; filename=test.doc"); 
		 } 
		 header("Pragma:no-cache"); 
		 header("Expires:0"); 
		 ob_end_flush();//输出全部内容到浏览器  
    }
    /**打印**/
    function print_trour(){
         $tourline_id = intval($_REQUEST['id']);
         $filename = $_REQUEST['name'];
         $tourline=get_tourline($tourline_id);
         $tour_desc=$tourline['tour_desc'];

         $GLOBALS['tmpl']->assign('tour_desc',$tour_desc);
         $GLOBALS['tmpl']->assign('filename',$filename);
         $GLOBALS['tmpl']->display("print_trour.html");

    }



}
?>