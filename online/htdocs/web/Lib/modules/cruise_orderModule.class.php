<?php
require APP_ROOT_PATH . "system/libs/tourline.php";
class cruise_orderModule extends BaseModule{
    function index() {
    	global_run();
    	
    	$ajax=intval($_REQUEST['ajax']);
    	$tourline_id=intval($_REQUEST['tourline_id']);
    	
    	$id_start_time=strim($_REQUEST['tourline_item_id']);
    	$array_id_start_time=explode('_',$id_start_time);
 		$tourline_item_id=intval($array_id_start_time['0']);
 		$tourline_item_start_time=$array_id_start_time['1'];
 		$GLOBALS['tmpl']->assign("tourline_item_start_time",$tourline_item_start_time);

    	if(!$GLOBALS['user']){
    		if($ajax ==1)
    		{
    			$return['status'] = 2;
				$return['info'] = "请先登录";
				ajax_return($return);
    		}
    		else
    		{
    			app_redirect(url("user#login"));
    		}
    	}
		
    	if(!$tourline_id){
	    		showErr("请选择旅游线路！",$ajax);
    	}
    	
    	if(!$tourline_item_id){
	    	showErr("请选择出游时间！",$ajax);
    	}
    	
    	$GLOBALS['tmpl']->assign("tourline_id",$tourline_id);
    	
    	$tourline_info=$GLOBALS['db']->getRow(
    	" select t.is_history,t.id,t.name,t.city_id,t.tour_total_day,t.is_visa,t.visa_name,t.visa_price,t.visa_brief,t.is_namelist,t.appoint_desc,t.order_confirm_type,t.is_tuan,t.tuan_is_pre,t.tuan_cate,t.tuan_begin_time,t.tuan_end_time,t.tuan_success_count,"
    	."ti.id as tourline_item_id,ti.start_time,ti.adult_price,ti.adult_sale_price,ti.child_price,ti.child_sale_price,ti.adult_limit,ti.adult_buy_max,ti.child_limit,ti.child_buy_min,ti.child_buy_max,ti.adult_sale_total,ti.child_sale_total,ti.is_forever"
    	." from ".DB_PREFIX."tourline as t "
    	." left join ".DB_PREFIX."tourline_item as ti on ti.tourline_id=t.id"
    	." where t.id=".$tourline_id." and ti.id=".$tourline_item_id." and t.is_effect=1");
    	 
    	if(!$tourline_info)
    		showErr("没有找到该旅游线路或已下架",$ajax);
    	
    	if($tourline_info['is_history']==1)
    		showErr("旅游产品已关闭购买",$ajax);

    	if($tourline_info['is_tuan'] ==1)
    	{
	    	if($tourline_info['tuan_begin_time'] > NOW_TIME && $tourline_info['tuan_begin_time'] >0)
	    		showErr("团购未开始",$ajax);
	    	
	    	if($tourline_info['tuan_end_time'] < NOW_TIME && $tourline_info['tuan_end_time'] >0)
	    		showErr("团购已结束",$ajax);
    	}
    	
    	if($ajax == 1)
    		showSuccess("成功",$ajax);

        $cabin_info = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."cabin where cruise_id=".$tourline_id." order by id");
    	
        $GLOBALS['tmpl']->assign("tourline_info",$tourline_info);

    	$GLOBALS['tmpl']->assign("cabin_info",$cabin_info);
    	
        $GLOBALS['tmpl']->assign("json_list",json_encode($tourline_info));

    	$GLOBALS['tmpl']->assign("userinfo",$GLOBALS['user']);
    	
    	$api_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."api_login");
    	$GLOBALS['tmpl']->assign("api_list",$api_list);
    	
		init_app_page();
		//输出SEO元素
		$GLOBALS['tmpl']->assign("site_name","邮轮线路 - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword","邮轮线路,".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description","邮轮线路,".app_conf("SITE_DESCRIPTION"));
		
    	$GLOBALS['tmpl']->display("cruise_cabin.html");
    }

    function addform() {
        global_run();
        
        $ajax=intval($_REQUEST['ajax']);
        $tourline_id=intval($_REQUEST['tourline_id']);
        $adult_count=intval($_REQUEST['adult_count']);
        $child_count=intval($_REQUEST['child_count']);
        $adult_price_total = intval($_REQUEST['adult_price_total']);
        $child_price_total = intval($_REQUEST['child_price_total']);
        
        $id_start_time=strim($_REQUEST['tourline_item_id']);
        $array_id_start_time=explode('_',$id_start_time);
        $tourline_item_id=intval($array_id_start_time['0']);
        $tourline_item_start_time=$array_id_start_time['1'];
        $GLOBALS['tmpl']->assign("tourline_item_start_time",$tourline_item_start_time);

        if(!$GLOBALS['user']){
            if($ajax ==1)
            {
                $return['status'] = 2;
                $return['info'] = "请先登录";
                ajax_return($return);
            }
            else
            {
                app_redirect(url("user#login"));
            }
        }
        
        if(!$tourline_id){
                showErr("请选择旅游线路！",$ajax);
        }
        
        if(!$tourline_item_id){
                showErr("请选择出游时间！",$ajax);
        }
        
        if($adult_count <0 && $child_count<0){
                showErr("请选择人数！",$ajax);
        }
        $GLOBALS['tmpl']->assign("tourline_id",$tourline_id);
        $GLOBALS['tmpl']->assign("adult_count",$adult_count);
        $GLOBALS['tmpl']->assign("child_count",$child_count);
        
        $tourline_info=$GLOBALS['db']->getRow(
        " select t.is_history,t.id,t.name,t.city_id,t.tour_total_day,t.is_visa,t.visa_name,t.visa_price,t.visa_brief,t.is_namelist,t.appoint_desc,t.order_confirm_type,t.is_tuan,t.tuan_is_pre,t.tuan_cate,t.tuan_begin_time,t.tuan_end_time,t.tuan_success_count,"
        ."ti.id as tourline_item_id,ti.start_time,ti.adult_price,ti.adult_sale_price,ti.child_price,ti.child_sale_price,ti.adult_limit,ti.adult_buy_max,ti.child_limit,ti.child_buy_min,ti.child_buy_max,ti.adult_sale_total,ti.child_sale_total,ti.is_forever"
        ." from ".DB_PREFIX."tourline as t "
        ." left join ".DB_PREFIX."tourline_item as ti on ti.tourline_id=t.id"
        ." where t.id=".$tourline_id." and ti.id=".$tourline_item_id." and t.is_effect=1");
         
        if(!$tourline_info)
            showErr("没有找到该旅游线路或已下架",$ajax);
        
        if($tourline_info['is_history']==1)
            showErr("旅游产品已关闭购买",$ajax);
        
        if($tourline_info['adult_buy_max']>0){
            if($adult_count > $tourline_info['adult_buy_max'])
                showErr("本线路成人最多可购买".$tourline_info['adult_buy_max']."人",$ajax);
        }
        
        if($tourline_info['adult_buy_min'] >0)
        {
            if($tourline_info['adult_buy_min'] > $adult_count || $adult_count > $tourline_info['adult_buy_max'])
                showErr("本线路成人至少购买".$tourline_info['adult_buy_min']."人",$ajax);
        }
        
        if($tourline_info['adult_limit']>0)
        {
            $adult_yushu=$tourline_info['adult_limit']-$tourline_info['adult_sale_total'];
            $adult_yushu=$adult_yushu <0?0:$adult_yushu;
            if($adult_yushu < $adult_count)
                showErr("本线路成人只剩下".$adult_yushu."人",$ajax);
        }
        
        if($tourline_info['child_buy_max'] >0)
        {
            if($child_count > $tourline_info['child_buy_max'])
                showErr("本线路儿童最多能购买".$tourline_info['child_buy_max']."人",$ajax);
        }
        
        if($tourline_info['child_buy_min'] > $child_count)
        {
            showErr("本线路儿童至少购买".$tourline_info['child_buy_min']."人",$ajax);
        }
        
        if($tourline_info['child_limit']>0)
        {
            $child_yushu=$tourline_info['child_limit']-$tourline_info['child_sale_total'];
            $child_yushu=$child_yushu <0?0:$child_yushu;
            if($child_yushu < $child_count)
                showErr("本线路儿童只剩下".$child_yushu."人",$ajax);
        }
        
        if($tourline_info['is_tuan'] ==1)
        {
            if($tourline_info['tuan_begin_time'] > NOW_TIME && $tourline_info['tuan_begin_time'] >0)
                showErr("团购未开始",$ajax);
            
            if($tourline_info['tuan_end_time'] < NOW_TIME && $tourline_info['tuan_end_time'] >0)
                showErr("团购已结束",$ajax);
        }
        
        
        if($ajax == 1)
            showSuccess("成功",$ajax);
            
        //判断 是不是永久有效的出游信息，1 ：是，0：不是
        if($tourline_info['is_forever'] ==1)
           $tourline_info['start_time'] = $tourline_item_start_time;
        
        /*线路信息处理*/
        $tour_city_cache=load_auto_cache("tour_city_list");
        $city_id_list=$tour_city_cache['city_id_list'];
        $tourline_info['city_name']=$city_id_list[$tourline_info['city_id']]['name'];
        /*是否隐藏预付（判断是否是预付）0：预付，1：付全款*/
        $tourline_info['yufu_hide']=is_yufu_hide($adult_count,$child_count,$tourline_info['adult_price'],$tourline_info['adult_sale_price'],$tourline_info['child_price'],$tourline_info['child_sale_price']);

        $tourline_info['visa_price']=format_price_to_display($tourline_info['visa_price']);
        $tourline_info['adult_price']=format_price_to_display($tourline_info['adult_price']);
        $tourline_info['adult_sale_price']=format_price_to_display($tourline_info['adult_sale_price']);
        $tourline_info['child_price']=format_price_to_display($tourline_info['child_price']);
        $tourline_info['child_sale_price']=format_price_to_display($tourline_info['child_sale_price']);
        
        $tourline_info['buy_adult_count']=$adult_count;
        $tourline_info['buy_child_count']=$child_count;
        $tourline_info['adult_price_total']=$tourline_info['adult_price']*$adult_count;
        $tourline_info['child_price_total']=$tourline_info['child_price']*$child_count;
        $tourline_info['adult_sale_price_total']=$tourline_info['adult_sale_price']*$adult_count;
        $tourline_info['child_sale_price_total']=$tourline_info['child_sale_price']*$child_count;
        
        $GLOBALS['tmpl']->assign("tourline_info",$tourline_info);
        $GLOBALS['tmpl']->assign("json_list",json_encode($tourline_info));
        $GLOBALS['tmpl']->assign("buy_count",intval($adult_count+$child_count));
        //print_r($tourline_info);
        /*游客数量*/
        $youke_all_num=$adult_count+$child_count;
        $user_namelist=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_namelist where user_id =".intval($GLOBALS['user']['id'])."  order by is_default desc,sort desc");
        
        $youke_all_array=array();       
        for($i=0; $i<$youke_all_num; $i++)
        {
            $youke_all_array[$i]['num']=$i+1;
            if($user_namelist[$i])
            {
                $youke_all_array[$i]['name']=$user_namelist[$i]['name'];
                $youke_all_array[$i]['paper_type']=$user_namelist[$i]['paper_type'];
                $youke_all_array[$i]['paper_sn']=$user_namelist[$i]['paper_sn'];
                $youke_all_array[$i]['mobile']=$user_namelist[$i]['mobile'];

            }
            else
            {
                $youke_all_array[$i]['name']='';
                $youke_all_array[$i]['paper_type']=1;
                $youke_all_array[$i]['paper_sn']='';
                $youke_all_array[$i]['mobile']='';
            }
        }
        
        
        $user_namelist_idlist=array();
        foreach($user_namelist as $k=>$v)
        {
            $user_namelist_idlist[$v['id']]=$v;
        }

        $GLOBALS['tmpl']->assign("user_namelist_idlist",$user_namelist_idlist);
        $GLOBALS['tmpl']->assign("json_namelist_idlist",json_encode($user_namelist_idlist));
        $GLOBALS['tmpl']->assign("namelist_count",count($user_namelist_idlist));
        
        $GLOBALS['tmpl']->assign("user_namelist_one",$user_namelist['0']);
        $GLOBALS['tmpl']->assign("youke_all_array",$youke_all_array);
        $GLOBALS['tmpl']->assign("youke_number",count($youke_all_array));
        /*代金券*/
        $voucher_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."voucher where is_effect = 1 and user_id =".intval($GLOBALS['user']['id'])." and is_used =0 and ((create_time < ".NOW_TIME." or create_time=0) and (end_time > ".NOW_TIME." or end_time=0)) order by create_time desc ");
        $voucher_useable_money=0;
        foreach($voucher_list as $k=>$v)
        {
            $voucher_useable_money +=$v['money'];
        }
   
        $voucher_useable_money=format_price_to_display($voucher_useable_money);
        
        $GLOBALS['tmpl']->assign("voucher_useable_money",$voucher_useable_money);
        $GLOBALS['tmpl']->assign("voucher_list",$voucher_list);
        
        /*保险*/
        $insurance_list = $GLOBALS['db']->getAll("select a.* from ".DB_PREFIX."insurance as a left join ".DB_PREFIX."tourline_insurance_link as b on a.id=b.insurance_id where b.tourline_id=".$tourline_info['id']." ");
        $json_insurance=array();
        foreach($insurance_list as $k=>$v)
        {
            $insurance_list[$k]['price']=format_price_to_display($v['price']);
            $json_insurance[$v['id']]['id']=$v['id'];
            $json_insurance[$v['id']]['name']=$v['name'];
            $json_insurance[$v['id']]['price']=$insurance_list[$k]['price'];
        }
        $GLOBALS['tmpl']->assign("insurance_list",$insurance_list);
        $GLOBALS['tmpl']->assign("json_insurance",json_encode($json_insurance));
        $GLOBALS['tmpl']->assign("userinfo",$GLOBALS['user']);
        
        $api_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."api_login");
        $GLOBALS['tmpl']->assign("api_list",$api_list);
        
        init_app_page();
        //输出SEO元素
        $GLOBALS['tmpl']->assign("site_name","旅游线路 - ".app_conf("SITE_NAME"));
        $GLOBALS['tmpl']->assign("site_keyword","旅游线路,".app_conf("SITE_KEYWORD"));
        $GLOBALS['tmpl']->assign("site_description","旅游线路,".app_conf("SITE_DESCRIPTION"));
        
        $GLOBALS['tmpl']->display("tourline_order.html");
    }
}
?>