<?php
require APP_ROOT_PATH . "system/libs/tourline.php";
class tourline_orderModule extends BaseModule{
    function index() {
    	global_run();
    	
    	$ajax=intval($_REQUEST['ajax']);
    	$tourline_id=intval($_REQUEST['tourline_id']);
    	$adult_count=intval($_REQUEST['adult_count']);
    	$child_count=intval($_REQUEST['child_count']);
    	
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
    function submit_cart(){
    	//print_r($_POST);
    	
    	$return = array("status"=>0,"info"=>"","jump");
    	$GLOBALS['user'] = User::load_user();
		if(empty($GLOBALS['user']))User::auto_do_login();
		$session_id = es_session::id();
		if(empty($GLOBALS['user'])){
			$return['status'] = 2;
			$return['info'] = "请先登录";
			ajax_return($return);
		}

		$tourline_id=intval($_POST['tourline_id']);/*线路id*/
		$tourline_item_id=intval($_POST['tourline_item_id']);
		$tourline_item_start_time=strim($_POST['tourline_item_start_time']);
	    $buy_adult_count=intval($_POST['buy_adult_count']);
	    $buy_child_count=intval($_POST['buy_child_count']);
    	$appoint_name = strim($_POST['appoint_name']);/*预订人姓名*/
    	$appoint_mobile = strim($_POST['appoint_mobile']);/*预订人手机*/
        session_start();

        $_SESSION['mobile']= $appoint_mobile;/**/
        //echo $_SESSION['mobile'];

    	$appoint_email = strim($_POST['appoint_email']);/*预订人邮箱*/
    	
    	$name_array = $_POST['name'];/*游客名字数组*/
    	$paper_type_array = $_POST['paper_type'];/*游客证件类型数组*/
    	$paper_sn_array = $_POST['paper_sn'];/*游客证件号数组*/
    	$mobile_array = $_POST['mobile'];/*游客手机号码数组*/
    	
    	$insurance_ids = $_POST['insurance_ids'];/*保险id数组*/
    	$voucher = $_POST['voucher'];/*所用优惠券id数组*/
    	$visa_count = intval($_POST['number_visa']);/*购买签证数量*/
    	$account_pay = format_price_to_db(floatval($_POST['account_pay']));/*余额*/
    	$share_order = intval($_POST['share_order']);/*是 否分享*/
    	
    	$order_memo = strim($_POST['order_memo']);/*订单备注*/
    	
    	$buy_count=$buy_adult_count+$buy_child_count;/*购买总数*/
    	
    	if(!$tourline_id){
	    		showErr("请选择旅游线路！",1);
    	}
    	
    	if(!$tourline_item_id){
	    		showErr("请选择出游时间！",1);
    	}
    	
    	if($buy_adult_count <=0 && $buy_child_count<=0){
		    showErr("请选择人数！",1);
	    }
    	if(!$tourline_id){
    		showErr("选择旅游线路",1);
    	}
    	if(!$tourline_item_id){
    		showErr("选择旅游日期",1);
    	}
    	if($appoint_name==""){
    		showErr("预订人姓名不能为空",1);
    	}
    	if($appoint_mobile==""){
    		showErr("预订人手机不能为空",1);
    	}
    	elseif(!check_mobile($appoint_mobile)){
    		showErr("预订人手机格式错误",1);
    	}
    	
    	$tourline_info=$GLOBALS['db']->getRow(
    	 " select t.*,"
    	." ti.id as tourline_item_id,ti.start_time,ti.adult_price,ti.adult_sale_price,ti.child_price,ti.child_sale_price,ti.adult_limit,ti.adult_buy_max,ti.child_limit,ti.child_buy_min,ti.child_buy_max,ti.adult_sale_total,ti.child_sale_total,ti.is_forever"
    	." from ".DB_PREFIX."tourline as t "
    	." left join ".DB_PREFIX."tourline_item as ti on ti.tourline_id=t.id"
    	." where t.id=".$tourline_id." and ti.id=".$tourline_item_id."");
    
    	if(!$tourline_info)
    		showErr("线路不存在！",1);
    	
    	if($tourline_info['adult_buy_max']>0){
	    	if($buy_adult_count > $tourline_info['adult_buy_max'])
	    		showErr("本线路成人最多可购买".$tourline_info['adult_buy_max']."人",1);
    	}
    	
    	if($tourline_info['adult_buy_min'] >0)
    	{
    		if($tourline_info['adult_buy_min'] > $buy_adult_count || $buy_adult_count > $tourline_info['adult_buy_max'])
	    		showErr("本线路成人至少购买".$tourline_info['adult_buy_min']."人",1);
    	}
    	
    	if($tourline_info['adult_limit']>0)
    	{
    		$adult_yushu=$tourline_info['adult_limit']-$tourline_info['adult_sale_total'];
    		$adult_yushu=$adult_yushu <0?0:$adult_yushu;
	    	if($adult_yushu < $buy_adult_count)
	    		showErr("本线路成人只剩下".$adult_yushu."人",1);
    	}
    	
    	if($tourline_info['child_buy_max'] >1)
    	{
    		if($child_count > $tourline_info['child_buy_max'])
    			showErr("本线路儿童最多能购买".$tourline_info['child_buy_max']."人",1);
    	}
    	
        if($tourline_info['child_buy_min'] > $buy_child_count)
    	{
    		showErr("本线路儿童至少购买".$tourline_info['child_buy_min']."人",1);
    	}
    	
    	if($tourline_info['child_limit']>1)
    	{
	    	$child_yushu=$tourline_info['child_limit']-$tourline_info['child_sale_total'];
	    	$child_yushu=$child_yushu <0?0:$child_yushu;
	    	if($child_yushu < $buy_child_count)
	    		showErr("本线路儿童只剩下".$child_yushu."人",1);
    	}
    	
    	if($tourline_info['is_tuan'] ==1)
    	{
	    	if($tourline_info['tuan_begin_time'] > NOW_TIME && $tourline_info['tuan_begin_time'] >0)
	    		showErr("团购未开始",1);
	    	
	    	if($tourline_info['tuan_end_time'] < NOW_TIME && $tourline_info['tuan_end_time'] >0)
	    		showErr("团购已结束",1);
    	}
    	$order_data['user_id'] = $GLOBALS['user']['id'];
    	//1.新订单 2.已确认 3.已完成 4.作废
    	$order_data['order_status'] = 1;
    	
    	//0.未支付(代金券或余额支付仍算未支付) 1.已支付(pay_amount==total_price)
    	$order_data['pay_status'] = 0;
    	
    	$order_data['tourline_total_price'] = 0;//线路总额
    	
    	$order_data['total_price'] = 0;//订单支付总额
    	
    	//已付金额
    	$order_data['pay_amount'] =0;
    	//余额支付部份
    	$order_data['account_pay'] = 0;
    	//代金券支付部份
    	$order_data['voucher_pay'] = 0;
    	//在线支付总额
    	$order_data['online_pay'] = 0;
    	
    	//成人价*成人个数+儿童价*儿童个数
    	$order_data['tourline_total_price']+=$tourline_info['adult_price']*$buy_adult_count+$tourline_info['child_price']*$buy_child_count;
    	
    	//签证总价=签证价*签证数
    	$order_data['tourline_total_price']+=$tourline_info['visa_price']*$visa_count;
    	
    	//保险总价
    	$insurance_total=0;
    	if($insurance_ids)
    	{
    		$insurance_list = $GLOBALS['db']->getAll("select a.* from ".DB_PREFIX."insurance as a left join ".DB_PREFIX."tourline_insurance_link as b on a.id=b.insurance_id where b.tourline_id=".$tourline_info['id']." and b.insurance_id in(".implode(',',$insurance_ids).")");
    		foreach($insurance_list as $k=>$v)
    		{
    			$insurance_total+=$buy_count*$v['price'];
    		}
    	}
    	$order_data['tourline_total_price']+=$insurance_total;
    	
    	/*判断是否是预付0：预付，1：付全款*/
    	$yufu_hide=is_yufu_hide($buy_adult_count,$buy_child_count,$tourline_info['adult_price'],$tourline_info['adult_sale_price'],$tourline_info['child_price'],$tourline_info['child_sale_price']);
    	
    	if( $yufu_hide ==1)
    		$order_data['total_price']+=$order_data['tourline_total_price'];
    	else
    		$order_data['total_price']+=$tourline_info['adult_sale_price']*$buy_adult_count+$tourline_info['child_sale_price']*$buy_child_count;
    
        if($order_data['order_confirm_type'] !=2)
        {
	    	//验证代金券
	    	if($voucher)
	    	{
		    	$voucher_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."voucher where id in(".implode(",",$voucher).") ");
		    	$tmp_voucher = array();
		    	foreach($voucher_list as $k=>$v){
		    		if($v['is_used'] == 1){
		    			showErr("代金券".$v['voucher_name']." 已使用。",1);
		    		}
		    		if($v['end_time'] <= NOW_TIME && $v['end_time'] >0){
		    			showErr("代金券".$v['voucher_name']." 已过期。",1);
		    		}
		    		if($v['user_id'] != $GLOBALS['user']['id']){
		    			showErr("代金券".$v['voucher_name']." 不属于您的。",1);
		    		}
		    		$tmp_voucher[$v['id']] = $v;
		    	}
		    	
		    	foreach($voucher as $k=>$v){
		    		if(!isset($tmp_voucher[$v])){
		    			showErr("id为 ".$v." 的代金券不存在。",1);
		    		}
		    		$tmp_voucher_price +=$tmp_voucher[$v]['money'];
		    		$tmp_voucher_name[]=$tmp_voucher[$v]['voucher_name'];/*名字数组*/
		    	}
		    	
		    	//代金券抵用金额
		    	if($tmp_voucher_price > 0){
		    		//假如代金卷总额小于商品总的必须支付的金额那么 代金券抵用金额  = 实际代金卷总额， 否则 代金券抵用金额  = 应付总额
		    		/*if($order_data['total_price'] - $tmp_voucher_price > 0)
		    			$order_data['pay_amount'] += $order_data['voucher_pay'] = $tmp_voucher_price;
		    		else
		    			$order_data['pay_amount'] += $order_data['voucher_pay'] = $order_data['total_price'];*/
		    			
		    		if($order_data['total_price'] - $tmp_voucher_price < 0)
    					$tmp_voucher_price = $order_data['total_price'];
		    	}
		    	/*else
		    		$order_data['voucher_pay'] = 0;*/
	    	}
	    	if($account_pay > 0){
	    		//是否大于账户余额
	    		if($account_pay > $GLOBALS['user']['money']){
	    			$account_pay  =  $GLOBALS['user']['money'];
	    		}
	    		/*
		    	if($order_data['total_price'] - $tmp_voucher_price - $account_pay > 0){
		    		$order_data['pay_amount'] += $order_data['account_pay'] = $account_pay;
		    	}
		    	else{
		    		$order_data['pay_amount'] += $order_data['account_pay'] = $order_data['total_price'] - $tmp_voucher_price;
		    	}*/
		    	
		    	if($order_data['total_price'] - $tmp_voucher_price - $account_pay < 0)
	    			$account_pay = $order_data['total_price'] - $tmp_voucher_price;
	    	}
        }
        
    	if($tourline_info['is_buy_return'] == 1)
    	{
    		$order_data['return_money'] = $tourline_info['return_money'] > 0 ? $tourline_info['return_money'] : (intval(app_conf("BUY_RETURN_MONEY_TYPE")) ==0 ? app_conf("BUY_RETURN_MONEY") : $order_data['total_price'] * (app_conf("BUY_RETURN_MONEY") / 100)/$buy_count);
    		$order_data['return_score'] = $tourline_info['return_score'] > 0 ? $tourline_info['return_score'] : (intval(app_conf("BUY_RETURN_SCORE_TYPE")) ==0 ? app_conf("BUY_RETURN_SCORE") : $order_data['total_price'] * (app_conf("BUY_RETURN_SCORE") / 100)/$buy_count);
		    $order_data['return_exp'] = $tourline_info['return_exp'] > 0 ? $tourline_info['return_exp'] : (intval(app_conf("BUY_RETURN_EXP_TYPE")) ==0 ? app_conf("BUY_RETURN_EXP") : $order_data['total_price'] * (app_conf("BUY_RETURN_EXP") / 100)/$buy_count);
    		$order_data['return_money_total']=$order_data['return_money']*$buy_count;
	    	$order_data['return_score_total']=$order_data['return_score']*$buy_count;
	    	$order_data['return_exp_total']=$order_data['return_exp']*$buy_count;
    	}
    	
    	if($tourline_info['is_review_return'] == 1)
    	{
    		$order_data['review_return_money'] = $tourline_info['review_return_money'] > 0 ? $tourline_info['review_return_money'] : app_conf("REVIEW_MONEY");
    		$order_data['review_return_score'] = $tourline_info['review_return_score']> 0 ? $tourline_info['review_return_score'] : app_conf("REVIEW_SCORE");
    		$order_data['review_return_exp'] = $tourline_info['review_return_exp']> 0 ? $tourline_info['review_return_exp'] : app_conf("REVIEW_EXP");
    	}
    	
    	//购买、点评  后 要发的代金券
    	if($tourline_info['is_buy_return'] == 1 || $tourline_info['is_review_return'] == 1)
    	{
	    	$b_voucher_type =  $GLOBALS['db']->getAll("select a.* from ".DB_PREFIX."voucher_promote as a left join ".DB_PREFIX."voucher_type as b on b.id = a.voucher_type_id where a.voucher_rel_id =".$tourline_info['id']." and b.deliver_type=3 and b.is_effect=1");
	    	foreach($b_voucher_type as $k=>$v)
	    	{
	    		//1.订单完成后返还 2.点评审核后返还
	    		if($v['voucher_promote_type'] ==1 && $tourline_info['is_buy_return'] == 1)
	    			$order_data['return_voucher_type_id']=$v['voucher_type_id'];
	    		elseif($v['voucher_promote_type'] ==2 && $tourline_info['is_review_return'] == 1)
	    			$order_data['review_return_voucher_type_id']=$v['voucher_type_id'];
	    	}
	    	if( !$order_data['return_voucher_type_id'] && $tourline_info['is_buy_return'] == 1)
	    		$order_data['return_voucher_type_id']=app_conf("REVIEW_VOUCHER");//获取点评设置表里的代金券
	    		
	    	if( !$order_data['review_return_voucher_type_id'] && $tourline_info['is_review_return'] == 1)
	    		$order_data['review_return_voucher_type_id']=app_conf("REVIEW_VOUCHER");
	    	
        }
    	$order_data['visa_count']=$visa_count;
    	$order_data['visa_price']=$tourline_info['visa_price'];
    	$order_data['tourline_name']=$tourline_info['name'];
    	$order_data['short_name']=$tourline_info['short_name'];
    	$order_data['tourline_id']=$tourline_info['id'];
    	$order_data['adult_price']=$tourline_info['adult_price'];
    	$order_data['adult_sale_price']=$tourline_info['adult_sale_price'];
    	$order_data['child_price']=$tourline_info['child_price'];
    	$order_data['child_sale_price']=$tourline_info['child_sale_price'];
    	$order_data['adult_count']=$buy_adult_count;
    	$order_data['child_count']=$buy_child_count;
    	$order_data['appoint_name']=$appoint_name;
    	$order_data['appoint_mobile']=$appoint_mobile;
    	$order_data['appoint_email']=$appoint_email;
    	$order_data['supplier_id']=$tourline_info['supplier_id'];
    	$order_data['is_refund']=$tourline_info['is_refund']; 
    	$order_data['is_expire_refund']=$tourline_info['is_expire_refund'];
    	$order_data['order_confirm_type']=$tourline_info['order_confirm_type'];
    	
    	$order_data['order_memo']=$order_memo;
    	
    	$order_data['create_time']=NOW_TIME;
    	
    	//判断 是不是永久有效的出游信息，1 ：是，0：不是
    	if($tourline_info['is_forever'] ==1)
    	  	$order_data['end_time'] = $tourline_item_start_time;
    	else
    		$order_data['end_time']=$tourline_info['start_time'];
    	
    	$order_id = 0;
    	do{
    		$order_data['sn']= "L_".to_date(NOW_TIME,"Ymdhis").rand(10,99);
    		$GLOBALS['db']->autoExecute(DB_PREFIX."tourline_order",$order_data,"INSERT","","SILENT");
    		$order_id = $GLOBALS['db']->insert_id();
    	}while($order_id==0);
    	
    	if($order_id == 0){
    		showErr("下单失败，请联系网站客服",1);
    	}
    	
    	if($order_id>0)
    	{
	    	 //插入保险
	    	 if($insurance_list)
	    	 {
	    	 	$order_insurance=array();
	    	 	$order_insurance['order_id']=$order_id;
	    	 	foreach($insurance_list as $k=>$v)
	    	 	{
	    	 		$order_insurance['insurance_id']=$v['id'];
	    	 		$order_insurance['insurance_name']=$v['name'];
	    	 		$order_insurance['insurance_price']=$v['price'];
	    	 		$GLOBALS['db']->autoExecute(DB_PREFIX."tourline_insurance_order",$order_insurance,"INSERT");
	    	 	}
	    	 }
	    	 
	    	//插入线路订单游客信息关联表 与  会员游客信息表
	    	if($name_array)
	    	{   
	    		$have_username=$GLOBALS['db']->getOne("select Group_concat(name) from ".DB_PREFIX."user_namelist where user_id =".intval($GLOBALS['user']['id'])."");
	    		$have_usernamearray=explode(',',$have_username);
	    		
	    		$user_namelist=array();
	    		$user_namelist['user_id']=$GLOBALS['user']['id'];
	    		$user_namelist['is_default']=0;
	    		
	    		$ord_namelist=array();
	    		$ord_namelist['tourline_order_id']=$order_id;
	    		$ord_namelist['status']=1;
	    		foreach($name_array as $k=>$v)
	    		{
	    			$user_namelist['name']=$ord_namelist['name']=$v;
	    			$user_namelist['paper_type']=$ord_namelist['paper_type']=$paper_type_array[$k];
	    			$user_namelist['paper_sn']=$ord_namelist['paper_sn']=$paper_sn_array[$k];
	    			$user_namelist['mobile']=$ord_namelist['mobile']=$mobile_array[$k];
	    			if(in_array($user_namelist['name'],$have_usernamearray))
	    				$GLOBALS['db']->autoExecute(DB_PREFIX."user_namelist",$user_namelist,"UPDATE"," name= '".$user_namelist['name']."' ","SILENT");
	    			else
	    				$GLOBALS['db']->autoExecute(DB_PREFIX."user_namelist",$user_namelist,"INSERT","","SILENT");
	    			$GLOBALS['db']->autoExecute(DB_PREFIX."tourline_order_namelist",$ord_namelist,"INSERT","","SILENT");
	    		}
	    		
	    	}
    		
    		if($order_data['order_confirm_type'] !=2)
    		{
	    		//更改代金券状态
	    		if($voucher)
	    		{
		    		$voucher_data['is_used'] = 1;
		    		$voucher_data['use_otype'] = 1;
		    		$voucher_data['use_oid'] = $order_id;
		    		$voucher_data['use_time'] = NOW_TIME;
		    		$GLOBALS['db']->autoExecute(DB_PREFIX."voucher",$voucher_data,"UPDATE"," id in (".implode(",",$voucher).") and user_id ='".$GLOBALS['user']['id']."' ");
		    		User::modify_account($GLOBALS['user']['id'],4,-$tmp_voucher_price,"订单 ".$order_data['sn']." 使用代金券(".implode(',',$tmp_voucher_name).")支付".format_price_to_display($tmp_voucher_price)."元。");
	    		}
		    	//扣除账户余额
		    	if($account_pay > 0)
		    		User::modify_account($GLOBALS['user']['id'],1,-$account_pay,"订单 ".$order_data['sn']." 使用余额支付".format_price_to_display($account_pay)."元。");
    		}

	    	//更新订单状态
	    	tourline_order_paid($order_data['sn'],0,$account_pay,$tmp_voucher_price);
			
	    	
	    	
	    	//发微博
	    	if(intval($_POST['share_order']) == 1){
	    		$image_list[] = $tourline_info['image'];
	    		User::send_weibo($GLOBALS['user']['id'],$tourline_info['name'],$image_list,url("tours#view",array("id"=>$tourline_info['id'],"ref_pid"=>base64_encode($GLOBALS['user']['id']))));
	    	}
	    	
	    	$jump_url=url("transaction#pay",array("ot"=>1,"sn"=>$order_data['sn']));
	    	showSuccess("提交成功",1,$jump_url);
    	}
    	
    	
    }
    
  
}
?>