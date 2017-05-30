<?php
/**
 * 获取首页推荐景点
 */
function get_index_spots(){
	$result_html = "";
	$spot_cate_list = load_auto_cache("spot_cate_list");
	
	if(!$spot_cate_list['index']){
		return '';
	}
	$area_list = load_auto_cache("tour_area_list");
	$class_array = array(
		0=>"tour_box_blue",
		1=>"tour_box_pink",
		2=>"tour_box_purple",
	);
	$sk = 0;
	foreach($spot_cate_list['index'] as $k=>$v){
		
		$spot_cate_list['index'][$k]['url'] = url("spot#cat",array("cate"=>$v['id']));
		
		//获取分类下的景点
		$spots_list = get_spots_list($v['name'],"","","","","","","0,20");
		
		$spot_cate_list['index'][$k]['spots_list'] = $spots_list['list'];
		
		$tags = array();
		//获取分类景点下的标签
		foreach($spots_list['list'] as $kk=>$vv){
			if($vv['tag_match_row']!=""){
				$temp_tags = explode(",",$vv['tag_match_row']);
				$tags = array_merge($tags,$temp_tags);
			}
		}
		
		$tags = array_unique($tags);
		
		$spot_cate_list['index'][$k]['tags'] = $tags;
		
		//大区域
		$tt_areas = array();
		
		$rec_indexarea=unserialize($v['rec_indexarea']);
		$spot_cate_list['index'][$k]['in_site_list']=$rec_indexarea['area1'];
		$spot_cate_list['index'][$k]['out_site_list']=$rec_indexarea['area1'];
		
		if($sk >2){
			$sk = 0;
		}
		
		$spot_cate_list['index'][$k]['class_name'] = $class_array[$sk];
		$sk ++;
	}
	unset($area_list);
	$GLOBALS['tmpl']->assign("spot_cate_list",$spot_cate_list['index']);
	$result_html = $GLOBALS['tmpl']->fetch("inc/index_spot_ticket.html");
	return $result_html;
}

/**
 * 获取景点列表
 * $cates 分类名称 多个,隔开
 * $citys 城市拼音 多个,隔开
 * $areas 大区拼音 多个,隔开
 * $places 小区拼音 多个,隔开
 * $tags 标签 多个,隔开
 */
function get_spots_list($cates="",$citys="",$areas="",$places="",$tags="",$condtions="",$order="",$limit="0,10"){
	$condition = " 1 = 1 ";
	if($cates!=""){
		$kw_unicode = str_to_unicode_string_depart($cates);
		$condition .=" and (match(cate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
	}
	
	if($citys!=""){
		$condition .=" and (match(city_match) against('".format_fulltext_key($citys)."' IN BOOLEAN MODE))";
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
	
	if($condtions!="")
		$condition .= " and ".$condtions;
	
	if($order == ""){
		$order = " sort DESC,id DESC";
	}
	
	if($limit=="")
		$limit = "0,10";
	
	$result["rs_count"] = $GLOBALS['db']->getOne('SELECT count(*) FROM '.DB_PREFIX.'spot WHERE '.$condition);
	if($result["rs_count"] > 0){
		$result["list"] = $GLOBALS['db']->getAll('SELECT * FROM '.DB_PREFIX.'spot WHERE '.$condition.' ORDER BY '.$order.' LIMIT '.$limit);
		foreach($result["list"]  as $k=>$v){
			
			$result["list"][$k] = format_spot($v);
		}
	}
	return $result;
}

/**
 * 获取某个景点
 */
function get_spot($id){
	$spot = $GLOBALS['db']->getRow('SELECT * FROM '.DB_PREFIX.'spot WHERE id='.intval($id));
	
	if($spot){
		$spot = format_spot($spot);
	}
	return $spot;
}

/**
 * 获取商家的待发布的景点
 */
function get_supplier_spot($sid){
	$spot = $GLOBALS['db']->getRow('SELECT * FROM '.DB_PREFIX.'spot_supplier WHERE id='.intval($sid));
	if($spot){
		$spot['description'] = format_html_content_image($spot['description'], 760,0);
		$spot['appoint_desc'] = format_html_content_image($spot['appoint_desc'], 760,0);
		$spot['tour_desc_1'] = format_html_content_image($spot['tour_desc_1'], 760,0);
		$spot['tour_desc_2'] = format_html_content_image($spot['tour_desc_2'], 760,0);
		$spot['tour_desc_3'] = format_html_content_image($spot['tour_desc_3'], 760,0);
		$spot['tour_desc_4'] = format_html_content_image($spot['tour_desc_4'], 760,0);
		
		$spot['ticket_list'] = unserialize($spot['ticket_list']);
		$spot['tickets'] = array();
		foreach($spot['ticket_list'] as $k=>$v){
			$spot['tickets'][$k] = unserialize(base64_decode($v));
			$spot['tickets'][$k]['origin_price'] = format_price_to_db($spot['tickets'][$k]['origin_price']);
			$spot['tickets'][$k]['current_price'] = format_price_to_db($spot['tickets'][$k]['current_price']);
			$spot['tickets'][$k]['sale_price'] = format_price_to_db($spot['tickets'][$k]['sale_price']);
		}
		$spot['sale_total'] = 0 ;
		$spot['review_total'] = 0 ;
		$spot['satify'] = 0 ;
	}
	return $spot;
}

//获取成交记录
function get_sale_list($spotid,$limit){
	$sql_count = "SELECT count(*) FROM ".DB_PREFIX."ticket_order tto " .
			"LEFT JOIN ".DB_PREFIX."ticket t ON t.id=tto.ticket_id " .
			"WHERE t.spot_id=$spotid AND tto.pay_status = 1";
	
	$rs_count = $GLOBALS['db']->getOne($sql_count);
	$list = array();
	if($rs_count > 0){
		$sql = "SELECT tto.*,t.name_brief,u.user_name FROM ".DB_PREFIX."ticket_order tto " .
			"LEFT JOIN ".DB_PREFIX."ticket t ON t.id=tto.ticket_id " .
			"LEFT JOIN ".DB_PREFIX."user u ON u.id = tto.user_id " .
			"WHERE t.spot_id=$spotid AND tto.pay_status = 1 order by tto.id DESC LIMIT ".$limit;
		$list = $GLOBALS['db']->getAll($sql);
		foreach($list as $k=>$v){
			ticket_order_item_format($v);
			$list[$k] = $v;
		}
	}
	return array("rs_count"=>$rs_count,"list"=>$list);
}

/**
 * 猜你喜欢的
 */
 function get_rand_spot($count,$noid=0){
 	$extw="";
 	if($noid > 0)
 		$extw = "where id<>$noid ";
 	$temp_list = $GLOBALS['db']->getAll('SELECT * FROM '.DB_PREFIX.'spot '.$extw.' ORDER BY sort DESC,id DESC LIMIT 200');
 	foreach($temp_list  as $k=>$v){
		$temp_list[$k] = format_spot($v);;
	}
	$max_count = count($temp_list);
	if($max_count > $count)
		$list_key = $count;
	else
 		$list_key = $max_count;
 	
 	$list = array();
 	$i = 0;
        if($max_count>0){
            do{
                    $kk = array_rand($temp_list);
                    if(!isset($list[$kk])){
                            $list[$kk] = $temp_list[$kk];
                            $i++;
                    }
            }while($i<$list_key);
        }
 	unset($temp_list);
 	return $list;
 	
 }

/**
 * 获取销量排行
 * $cates 分类名称 多个,隔开
 * $citys 城市拼音 多个,隔开
 * $areas 大区拼音 多个,隔开
 * $places 小区拼音 多个,隔开
 * $tags 标签 多个,隔开
 */
function get_spots_top($cates="",$citys="",$areas="",$places="",$tags="",$condtions="",$limit="0,10"){
	$condition = " 1 = 1 ";
	if($cates!=""){
		$kw_unicode = str_to_unicode_string_depart($cates);
		$condition .=" and (match(cate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
	}
	
	if($citys!=""){
		$condition .=" and (match(city_match) against('".format_fulltext_key($citys)."' IN BOOLEAN MODE))";
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
	
	if($condtions!="")
		$condition .= " and ".$condtions;
	
	
	$order = " sale_total DESC ,sort DESC";
	
	
	if($limit=="")
		$limit = "0,10";
	
	$list = $GLOBALS['db']->getAll('SELECT * FROM '.DB_PREFIX.'spot WHERE '.$condition.' ORDER BY '.$order.' LIMIT '.$limit);
	foreach($list  as $k=>$v){
		$list[$k] = format_spot($v);
	}
	return $list; 
}

/**
 * 获取门票信息
 */
function get_ticket($id){
	if($id >0)
		return $GLOBALS['db']->getRow('SELECT * FROM '.DB_PREFIX.'ticket WHERE is_effect=1 and id= '.intval($id));
	else
		return false;
}


function update_spot_ticket($spot_id,$tuan_id){
	if($spot_id==0 && $tuan_id == 0)
		return ;
	if($spot_id==0 && $tuan_id >0){
		
		$spot_id = $GLOBALS['db']->getOne("SELECT spot_id FROM ".DB_PREFIX."ticket WHERE id = ".$tuan_id);
	}
	if($spot_id==0)
		return ;
	//更新门票冗余信息
	$spot_tickets = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."ticket WHERE spot_id = ".$spot_id." and is_effect =1  ORDER BY sort DESC  ");
	
	if($spot_tickets){
		$spot_tickets_data['ticket_list'] = serialize($spot_tickets);
		$spot_tickets_data['has_ticket'] = 1;
		$spot_tickets_data['ticket_price'] = $GLOBALS['db']->getOne("SELECT min(current_price) FROM ".DB_PREFIX."ticket WHERE spot_id = ".$spot_id." and is_effect = 1 ORDER BY sort DESC ");
		
		$spot_sale_total = $GLOBALS['db']->getRow("SELECT sum(sale_total) as sale_total_sum,sum(sale_virtual_total) as sale_virtual_total_sum FROM ".DB_PREFIX."ticket WHERE spot_id = ".$spot_id." ");
		$spot_tickets_data['sale_total']=$spot_sale_total['sale_total_sum']+$spot_sale_total['sale_virtual_total_sum'];
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."spot",$spot_tickets_data,"UPDATE","id=".$spot_id,"SILENT");
	}
	else
	{
		$spot_tickets_data['ticket_list'] = '';
		$spot_tickets_data['has_ticket'] = 0;
		$GLOBALS['db']->autoExecute(DB_PREFIX."spot",$spot_tickets_data,"UPDATE","id=".$spot_id,"SILENT");
	}
}

/**
 * 格式化景点信息
 */
function format_spot($spot){
	
	if($spot['ticket_list'])
		$spot['tickets'] = unserialize($spot['ticket_list']);
	$spot['area_match'] = unformat_fulltext_key($spot['area_match']);
	$spot['city_match'] = unformat_fulltext_key($spot['city_match']);
	$spot['place_match'] = unformat_fulltext_key($spot['place_match']);
	
	$spot['url'] = bulid_spot_url($spot['id']);
	$spot['review_return_money'] = 0;
	foreach($spot['tickets'] as $kk=>$vv){
		$spot['tickets'][$kk]['return_money'] = $vv['return_money'] + $vv['review_return_money'];
		$spot['tickets'][$kk]['save_pirce'] = $vv['origin_price'] - $vv['sale_price'] + $spot['tickets'][$kk]['return_money'];
		if($spot['review_return_money'] == 0 || $spot['review_return_money'] < $vv['review_return_money']){
			$spot['review_return_money'] = $vv['review_return_money'];
		}
	}
	$spot['ticket_price_format'] = format_price(format_price_to_display($spot['ticket_price']));
	$spot['save_price'] = $spot['tickets'][0]['save_pirce'];
	/*$spot['description'] = format_html_content_image($spot['description'], 760,0);
	$spot['appoint_desc'] = format_html_content_image($spot['appoint_desc'], 760,0);
	$spot['tour_desc_1'] = format_html_content_image($spot['tour_desc_1'], 760,0);
	$spot['tour_desc_2'] = format_html_content_image($spot['tour_desc_2'], 760,0);
	$spot['tour_desc_3'] = format_html_content_image($spot['tour_desc_3'], 760,0);
	$spot['tour_desc_4'] = format_html_content_image($spot['tour_desc_4'], 760,0);*/
	
	
	return $spot;
}

function bulid_spot_url($spot_id){
	return url("spot#view",array("id"=>$spot_id));
}



/**
 * 保存线路订单操作日志
 * @param int $order_id  订单ID
 * @param string $log_info 日志内容
 * @param int $is_supplier  0:会员；1：商家；2：管理员
 */
function save_ticket_order_log($order_id,$log_info,$is_supplier)
{
	$log_data['order_id'] = $order_id;
	$log_data['log_info'] = $log_info;
	$log_data['is_supplier'] = $is_supplier;
	$log_data['log_time'] = NOW_TIME;
	$GLOBALS['db']->autoExecute(DB_PREFIX."ticket_order_log",$log_data);
}

/**
 * 订单信息
 */
function ticket_order_info($order_sn){
	if($order_sn == "")
		return false;
	$order_sn_e = explode(",",$order_sn);
	if(count($order_sn_e)==1){
		$order_data = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."ticket_order WHERE sn = '".$order_sn."' ");
		if($order_data){
			$order_data['money'] = $order_data['total_price'] - $order_data['pay_amount'];
		}
	}
	else{
		$order_data = $GLOBALS['db']->getRow("SELECT sum(total_price) as a_total_price,sum(pay_amount) as a_pay_amount,min(end_time) as end_time  FROM ".DB_PREFIX."ticket_order WHERE sn in ('".implode("','",$order_sn_e)."') ");
		if($order_data){
			$order_data['sn'] = $order_sn_e[0];
			$order_data['money'] = $order_data['a_total_price'] - $order_data['a_pay_amount'];
			if($order_data['money'] == 0){
				$order_data['pay_status'] = 1;
			}
			
			$order_data['order_list'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."ticket_order WHERE sn in ('".implode("','",$order_sn_e)."') ");
		}
	}
	
	return $order_data;
}

function ticket_order_item_sn($verify_code){
	$item = $GLOBALS['db']->getRow("SELECT *  FROM ".DB_PREFIX."ticket_order_item WHERE verify_code = '".$verify_code."' ");
	return $item;
}

/**
 * 订单信息
 */
function ticket_order_info_byid($id){
	if($id == "")
		return false;
	$order_data = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."ticket_order WHERE id = '".$id."' ");
	
	return $order_data;
}

/**
 * 格式化订单门票列表
 * @param unknown_type $item
 */
function ticket_order_item_format(&$item){


	//re_appoint_status：0:未申请改期;1:申请改期中;2:确认改期;3:拒改期;
	if ($item['re_appoint_status'] == 1){
		$item['re_appoint_status_format'] = '申请改期中';
	}else if ($item['re_appoint_status'] == 2){
		$item['re_appoint_status_format'] = '确认改期';
	}else if ($item['re_appoint_status'] == 3){
		$item['re_appoint_status_format'] = '拒改期';
	}else {
		$item['re_appoint_status_format'] = '未申请改期';
	}
	
	$item['re_action_time_format'] = to_date($item['re_action_time'],'Y-m-d');//申请退改的时间，只有预约的门票才能申请改签，同步到end_time和begin_time，当天
	
	$item['re_appoint_time_format'] = to_date($item['re_appoint_time'],'Y-m-d');//改期申请的日期
	$item['appoint_time_format'] = to_date($item['appoint_time'],'Y-m-d');//预约时间
	
	$item['verify_time_format'] = to_date($item['verify_time']);//验证时间
	
	$item['begin_time_format'] = to_date($item['begin_time']);//开始时间
	if($item['end_time'] > 0)
		$item['end_time_format'] = to_date($item['end_time']);//过期时间
	else
		$item['end_time_format'] = "永不过期";
	
	if ($item['verify_time'] == 0){
		$item['is_verify'] = '否';
	}else{
		$item['is_verify'] = '是';
	}	
	
	if ($item['is_verify_code_invalid'] == 0){
		$item['is_invalid'] = '有效';
	}else{
		$item['is_invalid'] = '无效';
	}	
}

/**
 * 格式化线路订单数据
 * @param array $order
 */
function ticket_order_format(&$order){


	$order['supplier_confirm_time_format'] = to_date($order['supplier_confirm_time']);//商家对退款审核时间
	$order['delivery_time_format'] = to_date($order['delivery_time']);//快递发出时间
	$order['delivery_time_format'] = to_date($order['delivery_time']);//快递发出时间
	

	$order['delivery_time_format'] = to_date($order['delivery_time']);//快递发出时间
	$order['create_time_format'] = to_date($order['create_time']);//下单时间
	$order['pay_time_format'] = to_date($order['pay_time']);//支付时间
	$order['confirm_time_format'] = to_date($order['confirm_time']);//定单确认时间
	$order['over_time_format'] = to_date($order['over_time']);//全部完成时间	
	$order['create_time_short'] = to_date($order['create_time'],"Y-m-d");
	$order['appoint_time_format'] = to_date($order['appoint_time'],"Y-m-d");
	$order['end_time_format'] = to_date($order['end_time'],"Y-m-d");
	

	$order['total_price_format'] = format_price(format_price_to_display($order['total_price']));//应付总额(item_price+delivery_fee)
	$order['pay_amount_format'] = format_price(format_price_to_display($order['pay_amount']));//已付金额(online_pay+voucher_pay+account_pay)
	$order['delivery_fee_format'] = format_price(format_price_to_display($order['delivery_fee']));//运费
	$order['item_price_format'] = format_price(format_price_to_display($order['item_price']));//商品本身的价格
	$order['online_pay_format'] = format_price(format_price_to_display($order['online_pay']));//在线支付金额
	$order['voucher_pay_format'] = format_price(format_price_to_display($order['voucher_pay']));//代金券支付(超出部份不显示，如代金券100，应付50，此处为50，但代金券直接失效，超出不退)
	$order['account_pay_format'] = format_price(format_price_to_display($order['account_pay']));//余额支付部份
	$order['refund_amount_format'] = format_price(format_price_to_display($order['refund_amount']));//已退金额
	$order['return_money_format'] = format_price(format_price_to_display($order['return_money']));//返现的金额单价

	$order['return_money_total_format'] = format_price(format_price_to_display($order['return_money_total']));//已返现总额
	$order['review_return_money_format'] = format_price(format_price_to_display($order['review_return_money']));//点评返现金额
	
	//是否为团体票 1:个人票 0团体票
	if ($order['is_divide'] == 1){
		$order['is_divide_format'] = '个人票';
	}else{
		$order['is_divide_format'] = '团体票';	
	}

	
	//refund_status：发货状态：0未发货 1已发货 2已收货 -1无需发货;
	if ($order['delivery_status'] == 0){
		$order['delivery_status_format'] = '未发货';
	}else if ($order['delivery_status'] == 1){
		$order['delivery_status_format'] = '已发货';
	}else if ($order['delivery_status'] == 2){
		$order['delivery_status_format'] = '已收货';
	}else if ($order['delivery_status'] == -1){
		$order['delivery_status_format'] = '无需发货';
	}else {
		$order['delivery_status_format'] = '未知';
	}
		
	//支付状态
	if ($order['pay_status'] == 1){
		$order['pay_status_format'] = '已支付';
	}else{
		$order['pay_status_format'] = '未支付';
		
	}
	
	$order['left_time_format']= "";
	if($order['end_time'] > NOW_TIME){
		$left_time = $order['end_time'] - NOW_TIME;
		$left_time_day = intval($left_time/24/3600);
		$left_time_hour  = intval(($left_time%(24*3600))/3600);
		$left_time_min  = intval(($left_time%3600) /60);
		if($left_time_day > 0)
			$order['left_time_format'] .= $left_time_day.'天';
		if($left_time_hour >0)
			$order['left_time_format'] .= $left_time_hour.'小时';
		if($left_time_min >0)
			$order['left_time_format'] .= $left_time_min.'分';
	}
	
			
	
	//订单状态(流程)1.新订单 2.已确认 3.已完成 4.作废\r\n新订单：未确认（包含已付款）的都表示为新订单\r\n已确认：表示为商家或管理员查看，确认手动修改\r\n新订单、已确认均可申请退款，否则不可',
	if ($order['order_status'] == 1){
		$order['order_status_format'] = '新订单';
		$order['uc_order_status_format'] = '出票中';
	}else if ($order['order_status'] == 2){
		$order['order_status_format'] = '确认通过';
		$order['uc_order_status_format'] = '出票成功';
	}else if ($order['order_status'] == 3){
		$order['order_status_format'] = '已完成';
		$order['uc_order_status_format'] = '已完成';
	}else if ($order['order_status'] == 4){
		$order['order_status_format'] = '作废';
		$order['uc_order_status_format'] = '订单作废';
	}else if ($order['order_status'] == 5){
		$order['order_status_format'] = '确认不通过';
		$order['uc_order_status_format'] = '出票失败';
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
	
	//re_appoint_status：是否有改期申请
	if ($order['re_appoint_status'] == 0){
		$order['re_appoint_status_format'] = '无';
	}else{
		$order['re_appoint_status_format'] = '有';
	}	
	
	if ($order['is_verify_code_invalid'] == 0){
		$order['is_invalid'] = '有效';
	}else{
		$order['is_invalid'] = '无效';
	}
			
	//0：未确认；1：确认退票；2：拒绝退票；
	if ($order['supplier_confirm'] == 1){
		$order['supplier_confirm_format'] = '确认退票';
	}else if ($order['supplier_confirm'] == 2){
		$order['supplier_confirm_format'] = '拒绝退票';
	}else {
		$order['supplier_confirm_format'] = '未确认';
	}
	
	//证件类型 
	
	if ($order['paper_type'] == 1){
		$order['paper_type_name'] = '身份证';
	}else if ($order['paper_type'] == 2){
		$order['paper_type_name'] = '护照';
	}else if ($order['paper_type'] == 3){
		$order['paper_type_name'] = '军官证';
	}else if ($order['paper_type'] == 4){
		$order['paper_type_name'] = '港澳通行证';
	}else if ($order['paper_type'] == 5){
		$order['paper_type_name'] = '台胞证';
	}else if ($order['paper_type'] == 6){
		$order['paper_type_name'] = '其他';
	}
	
	$order['url'] = url("uc_order#ticket",array("id"=>$order['id']));
}

/**
 * 
 * @param int $order_sns 订单编号,以","分割的多个订单号
 * @param int $online_pay
 * @return
 * 
 * $res = array();
 * $res['order_sn'] = $order_sn;
 * $res['pay_status'] = -1;//-1:未参与支付; 0: 订单未完成全部收款；1：订单已经完成全部收款
 * $res['pay_money'] = 0;//订单分配到的金额
 * 
 * $resarr[] = $res;
 * return $resarr;		
 */
function ticket_order_online_pay($order_sns,$online_pay){
	$arr =explode(",",$order_sns);
	
	$pay_status = 0;
	$resarr = array();
		
	foreach($arr as $key=>$order_sn){		
		$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ticket_order where sn = '".$order_sn."'");
		
		$res = array();
		$res['order_sn'] = $order_sn;
		$res['pay_status'] = -1;//-1:未参与支付; 0: 订单未完成全部收款；1：订单已经完成全部收款
		$res['pay_money'] = 0;//订单分配到的金额
				
		if(!empty($order)&&$online_pay>0)
		{
			//还需要支付金额;
			$pay_money = intval($order['total_price']) - intval($order['pay_amount']);

			if ($pay_money > 0){
				if($online_pay>=$pay_money)
				{
					$online_pay = $online_pay - $pay_money;						
				}
				else
				{
					$pay_money = $online_pay;
				}
				$res['pay_money'] = $pay_money;
				$res['pay_status'] = ticket_order_paid($order_sn,$pay_money);
			}			
		}
		$resarr[] = $res;
	}
	
	return $resarr;
}

/**
 * 订单收款处理
 * @param string $order_sn 订单编号,以","分割的多个订单号
 * @param int $online_pay 在线支付金额(分)
 * @param int $account_pay 余额支付金额(分)
 * @param int $voucher_pay 代金券金额(分)
 * @return 0: 订单未完成全部收款；1：订单已经完成全部收款
 * 
 * 3. 门票下单支付回调流程(需要发邮件，短信以及会员私信通知用户，建立相关邮件短信模板)
 */
function ticket_order_paid($order_sn,$online_pay=0,$account_pay=0,$voucher_pay =0){
	

		$order_info = $GLOBALS['db']->getRow("select id,ticket_id,sale_count,order_confirm_type from ".DB_PREFIX."ticket_order where sn = '".$order_sn."'");
		$id = $order_info['id'];
		
		$online_pay = intval($online_pay);
		$account_pay = intval($account_pay);
		$voucher_pay = intval($voucher_pay);
		
		$sql = "update ".DB_PREFIX."ticket_order set ".
		"pay_amount = (online_pay + ".$online_pay.") + (account_pay + ".$account_pay.") + (voucher_pay + ".$voucher_pay.")".
		",online_pay = online_pay + ".$online_pay.
		",account_pay = account_pay + ".$account_pay.
		",voucher_pay = voucher_pay + ".$voucher_pay.
		
		",pay_time=".NOW_TIME." where  id = ".$id;
		
		$GLOBALS['db']->query($sql,"SILENT");
		$affect_row = $GLOBALS['db']->affected_rows();
		
		if ($affect_row&&$online_pay > 0){				
			save_ticket_order_log($id,"订单在线支付：".format_price(format_price_to_display($online_pay)),0);
		}
		
		if ($affect_row&&$account_pay > 0){
			save_ticket_order_log($id,"余额支付：".format_price(format_price_to_display($account_pay)),0);
		}
			
		if ($affect_row&&$voucher_pay > 0){
			save_ticket_order_log($id,"代金券支付：".format_price(format_price_to_display($voucher_pay)),0);
		}
	
		//account_pay,voucher_pay,online_pay
		if($order_info['order_confirm_type'] ==2)
			$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order set pay_status = 1,order_status = 2,confirm_time = '".NOW_TIME."' where pay_status = 0 and pay_amount>=total_price and id = ".$id);
		else
			$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order set pay_status = 1 where pay_status = 0 and pay_amount>=total_price and id = ".$id);
			
		if($GLOBALS['db']->affected_rows()>0)
		{
			//支付成功 添加实际销售量
			$ticket_id = intval($order_info['ticket_id']);
			$sale_count = intval($order_info['sale_count']);
			$GLOBALS['db']->query("update ".DB_PREFIX."ticket set sale_total = sale_total + ".$sale_count." where id = ".$ticket_id);
			
			//团购产品类型1.线路2.门票3.酒店
			$spot_id =  intval($GLOBALS['db']->getOne("select spot_id from ".DB_PREFIX."ticket where id = ".$ticket_id));
			
			//$sale_total = intval($GLOBALS['db']->getOne("select sum(sale_total) from ".DB_PREFIX."ticket where spot_id = ".$spot_id));
			
						
			$sale_total = intval($GLOBALS['db']->getOne("select sale_total + sale_virtual_total from ".DB_PREFIX."ticket where id = ".$ticket_id));
			$GLOBALS['db']->query("update ".DB_PREFIX."tuan set sale_total = ".$sale_total." where type = 2 and rel_id = ".$ticket_id);
			$GLOBALS['db']->query("update ".DB_PREFIX."spot set sale_total = ".$sale_total." where id = ".$spot_id);
			
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ticket_order where sn = '".$order_sn."'");
			//订单支付成功，发短信
			send_order_sms($order_info,2);
			//订单支付成功，发邮件
			send_order_mail($order_info,2);
			
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
 * 订单退款 操作
 * 订单状态(流程)order_status 1.新订单 2.确认通过 3.已完成 4.作废 5.确认不通过
 * 订单退款状态(流程) refund_status：0.未申请退款;1:申请退款;2:确认退款;3:拒绝退款;
 * @param int $id 订单ID
 * @param int $is_supplier  0:会员;1:商家;2:管理员;
 * @param int $refund_amount_in 是否退款到用户余额 -1表示自动计算退入余额 0：不退 大于零:退回指定的金额
 * return true 成功; false 失败
 */
function ticket_order_refund($id,$is_supplier,$refuse_reason='',$refund_amount_in = -1)
{
	//订单退款
	$res = array();
	$res['return'] = false;
	$res['message'] = '';
	$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ticket_order where order_status in (1,2,3,5) and refund_status = 1 and id = ".$id);
	if(empty($order))
	{
		$res['message'] = '订单不存在,或已过期';
	}else{

		//订单退款
		$delivery_fee = intval($order['delivery_fee']);
		$voucher_pay = intval($order['voucher_pay']);
		$total_price = intval($order['total_price']);


		$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order set refuse_reason='".$refuse_reason."',refund_status = 2, order_status = case when order_status = 1 then 2 else order_status end where refund_status = 1 and id = ".$id." ","SILENT");
		if($GLOBALS['db']->affected_rows()>0){
			save_ticket_order_log($id,'订单确认退款',1);

			$refund_amount = 0;
			$new_refund_count = 0;
				
			if ($order['is_divide'] == 1){
				//个人，多张门票;
				//门票列表;
				$ticketlist = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."ticket_order_item where refund_status = 1 and order_id = ".intval($id));
				foreach($ticketlist as $k=>$v)
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order_item set refund_status = 2, is_verify_code_invalid = 1 where id = ".$v['id']." ","SILENT");

					$refund_amount = $refund_amount + $order['item_price'];
						
					$new_refund_count = $new_refund_count + 1;
				}
			}else{
				//团队
				$new_refund_count = $order['ref_refund_count'];

				$refund_amount = $order['item_price'] * $order['re_fund_count'];

				//$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order_item set refund_status = 2, is_verify_code_invalid = 1 where order_id = ".$id." ","SILENT");

			}
				
			//判断是否全部退款
			if ($order['sale_count'] <= intval($order['refund_count']) + $new_refund_count){
				$refund_all = true;
			}else{
				$refund_all = false;
			}
				
				
			//退款金额，不能超过实际支付的金额(即：需要扣除代金券部分)
			if ($refund_amount > $total_price - $voucher_pay)
				$refund_amount = $total_price - $voucher_pay;

			
			if ($refund_all){
				//全部退款时，订单直接标识成：作废
				$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order set ref_refund_count = 0, refund_count = refund_count + ".$new_refund_count.", order_status = 4, refund_amount = refund_amount + ".$refund_amount." where id = ".$id." ","SILENT");
			}else{
				//非全部退款
				$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order set ref_refund_count = 0, refund_count = refund_count + ".$new_refund_count.",refund_amount = refund_amount + ".$refund_amount." where id = ".$id." ","SILENT");
			}

			//退款成功 减少实际销售量
			$ticket_id = intval($order['ticket_id']);			
			$GLOBALS['db']->query("update ".DB_PREFIX."ticket set sale_total = sale_total - ".$new_refund_count." where id = ".$ticket_id);

			//团购产品类型1.线路2.门票3.酒店
			$spot_id =  intval($GLOBALS['db']->getOne("select spot_id from ".DB_PREFIX."ticket where id = ".$ticket_id));
			
			$sale_total = intval($GLOBALS['db']->getOne("select sale_total + sale_virtual_total from ".DB_PREFIX."ticket where id = ".$ticket_id));
			$GLOBALS['db']->query("update ".DB_PREFIX."tuan set sale_total = ".$sale_total." where type = 2 and rel_id = ".$ticket_id);
			$GLOBALS['db']->query("update ".DB_PREFIX."spot set sale_total = ".$sale_total." where id = ".$spot_id);
							
			
			if ($refund_amount_in ==-1){
				if($refund_amount>0)
				{
				require_once APP_ROOT_PATH."system/libs/user.php";
				User::modify_account($order['user_id'], 1, $refund_amount, '订单:'.$order['sn']." 退款：".format_price_to_display($refund_amount));
				}
			}
			elseif($refund_amount_in> 0)
			{
				require_once APP_ROOT_PATH."system/libs/user.php";
				User::modify_account($order['user_id'], 1, $refund_amount_in, '订单:'.$order['sn']." 退款：".format_price_to_display($refund_amount_in));
			}

			$res['return'] = true;
			$res['message'] = '退款处理成功';
			
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ticket_order where id = '".$id."'");
			//订单退款成功，发短信
			send_order_refund_sms($order_info);
			//订单退款成功，发邮件
			send_order_refund_mail($order_info);			
		}

	}

	return $res;
}

/**
 * 订单作废 操作
 * 订单状态(流程)1.新订单 2.确认通过 3.已完成 4.作废
 * @param int $id 订单ID
 * @param int $is_supplier  0:会员;1:商家;2:管理员;
 * @param int $refund_amount 0:不自动退款   大于0:表示退回用户余额的金额，由外部计算后调用（分）
 * return true 成功; false 失败
 */
function ticket_order_invalid($id,$is_supplier=0,$refund_amount=0)
{
	
	//订单作废
	$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ticket_order where order_status <> 4 and id = ".$id);
	
	if(empty($order))
	{
		//showErr("订单不存在,或已经作废",$ajax,admin_url("ticket_order#order",array(id=>$id)))	;
		return false;
	}else{
		
		$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order set order_status = 4 where order_status <>4 and id = ".$id." ","SILENT");
		
		if($GLOBALS['db']->affected_rows()>0){
			//订单作废 时，把验证码设置成：is_verify_code_invalid = 1无效
			
			$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order_item set is_verify_code_invalid = 1 where order_id = ".$id." ","SILENT");
			
			if($refund_amount>0)
			{
				User::modify_account($order['user_id'], 1, $refund_amount, $order['sn']."订单作废，部份付款退回");
				$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order set refund_amount = refund_amount + $refund_amount,refund_status = 2 where id = ".$id." ","SILENT");
				save_ticket_order_log($id,"订单作废，自动退款:".format_price(format_price_to_display($refund_amount)),$is_supplier);
			}
			else
			{
				save_ticket_order_log($id,"订单作废",$is_supplier);
			}
			
			
		}
		return true;
	}
}

/**
 * 自动退款处理
 * @param unknown_type $order
 * @param unknown_type $is_supplier 0：会员；1：商家；2：管理员
 */
function ticket_auto_refund_amount($order,$is_supplier)
{

	$is_refund = intval($order['is_refund']);//是否支持退，改
	if ($is_refund == 0) return false;//不支持退款;
	
	$is_expire_refund = intval($order['is_expire_refund']);//是否允许过期退款
		
	$id = $order['id'];
	$refund_amount = intval($order['refund_amount']);
	$sale_count = intval($order['sale_count']);
	$refund_count = intval($order['refund_count']);//实际退的数量，由后台更新（申请退的数量在另一个字段中)
	
	$account_pay = intval($order['account_pay']);
	$online_pay = intval($order['online_pay']);
	$voucher_pay = intval($order['voucher_pay']);
	
	$pay_amount = intval($order['pay_amount']);
	$total_price = intval($order['total_price']);
	$delivery_fee = intval($order['delivery_fee']);
	$item_price = intval($order['item_price']);
	

	
	$is_divide = intval($order['is_divide']);//是否为团体票 1:个人票 0团体票
	$is_delivery = intval($order['is_delivery']);//是否允为实体票
	
	$refund_all = false;//true:全额退; false:只退还未使用的门票部分;
	if ($is_delivery == 0){
		if ($is_expire_refund == 1){
			//支持过期退，则不判断时间
			//查询：已使用 或 退款 的门票数量;
			$ticket_count = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ticket_order_item where (verify_time > 0 or refund_status = 2) and order_id = ".$id));
		}else{
			//查询：已使用 或 退款 或 过期 的门票数量;
			$ticket_count = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ticket_order_item where (verify_time > 0 or refund_status = 2 or end_time > ".get_gmtime().") and order_id = ".$id));
		}
		
		//从未使用 或 退款过 以及未过期;(适合 个人，团体 票判断)
		if ($ticket_count == 0 && $refund_count == 0 && $refund_amount == 0 && $pay_amount <= $total_price) $refund_all = true;
		/*
		if ($is_divide == 1){			
			if ($ticket_count == 0) $refund_all = true;
		}else{
			//团体票，只能使用一次
			//从未使用, 也未有退款;
			
		}
		*/
		
		$auto_refund_amount = 0;
		$auto_refund_count = 0;
		if ($refund_all){
			//全部退
			if ($account_pay > 0){
				User::modify_account($order['user_id'], 1, $account_pay, "自动处理退款:返回已付余额：".format_price(format_price_to_display($account_pay)).";订单号:".$order['sn']);
				save_ticket_order_log($id,"自动处理退款:返回已付余额：".format_price(format_price_to_display($account_pay)),$is_supplier);
			}
			
			if ($online_pay > 0){
				User::modify_account($order['user_id'], 1, $online_pay, "自动处理退款:返回在线支付金额到用户余额：".format_price(format_price_to_display($online_pay)).";订单号:".$order['sn']);
				save_ticket_order_log($id,"自动处理退款:返回在线支付金额到用户余额：".format_price(format_price_to_display($online_pay)),$is_supplier);
			}

			if ($voucher_pay > 0){
				//代金券 fanwe_voucher
				$GLOBALS['db']->query("update ".DB_PREFIX."voucher is_used = 1,use_time = 0 when use_otype = 1 and user_id = ".intval($order['user_id'])." and use_oid = ".$id." limit 1 ","SILENT");					
				//User::modify_account($order['user_id'], 1, $online_pay, "订单作废返回在线支付金额到用户余额：".$online_pay);
				save_ticket_order_log($id,"自动处理退款:回返已使用的还代金券",$is_supplier);
			}			
			
			$auto_refund_count = $refund_count;
			$auto_refund_amount = $pay_amount;//$account_pay + $online_pay + $voucher_pay;
		}else{
			//只退还未使用的门票，代金券部分不退
			if ($is_divide == 1){
				//查询出：未使用，未退款的门票列表;
				if ($is_expire_refund == 1){
					$ticketlist = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."ticket_order_item where verify_time = 0 and refund_status <> 2 and order_id = ".$id);
				}else{
					//查询出：未使用，未退款，未过期 的门票列表;
					$ticketlist = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."ticket_order_item where verify_time = 0 and refund_status <> 2 and end_time <= ".get_gmtime()." and order_id = ".$id);
				}
								
				foreach($ticketlist as $k=>$v)
				{					
					//将门票设置成:失效,已退款
					$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order_item set refund_status = 2,is_verify_code_invalid = 1 where verify_time = 0 and refund_status <> 2 and id = ".$v['id']." ","SILENT");
					
					if($GLOBALS['db']->affected_rows()>0){
						$auto_refund_amount = $auto_refund_amount + $item_price;
						$auto_refund_count = $auto_refund_count + 1;
					}
				}
			
			}else{
				//团体票，只能使用一次,判断有没有被使用过
				//查询出：未使用，未退款的门票数量;
				if ($is_expire_refund == 1){
					$ticket_count = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ticket_order_item where verify_time = 0 and refund_status <> 2 and order_id = ".$id));
				}else{
					//查询出：未使用，未退款，未过期 的门票数量;
					$ticket_count = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ticket_order_item where verify_time = 0 and refund_status <> 2 and end_time <= ".get_gmtime()." and order_id = ".$id));
				}
				
				if ($ticket_count == 0){
					//已经被使用或过期
					$auto_refund_amount = 0;
					$auto_refund_count = 0;
				}else{
					$auto_refund_count = $sale_count - $refund_count;//购买数量-已退款数量（一次使用的，所以不存在已经使用了几个）
					$auto_refund_amount = $auto_refund_count * $item_price;
				}				
			}
			
			//退款金额，不能超过实际支付的金额(即：需要扣除代金券部分)
			if ($auto_refund_amount > $total_price - $voucher_pay)
				$auto_refund_amount = $total_price - $voucher_pay;
								
			if ($auto_refund_amount > 0){
				User::modify_account($order['user_id'], 1, $auto_refund_amount, "自动处理退款：".format_price(format_price_to_display($auto_refund_amount)).";订单号:".$order['sn']);
				save_ticket_order_log($id,"自动退款:".format_price(format_price_to_display($auto_refund_amount)),$is_supplier);
			}
		}	

		if ($auto_refund_amount > 0){
			$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order set refund_amount = refund_amount + $auto_refund_amount,refund_count = refund_count + $auto_refund_count where id = ".$id." ","SILENT");		
		}	
	}else{
		//有配送的退款方式
		//`delivery_status` tinyint(1) NOT NULL default '-1' COMMENT '发货状态：0未发货 1已发货 2已收货 -1无需发货',
		$delivery_status = intval($order['delivery_status']);
		if ($delivery_status == 0){
			//实体票未发货时，自动退款?
			
		}
	}
	
}

/**
 * 订单确认 操作
 * 订单状态(流程)1.新订单 2.确认通过 3.已完成 4.作废 5.确认不通过
 * @param int $id 订单ID
 *
 * @param int $is_supplier  0：会员；1：商家；2：管理员
 */
function ticket_order_confirm($id,$order_status,$is_supplier)
{
	$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order set order_status = ".intval($order_status).",confirm_time = '".NOW_TIME."' where order_status = 1 and id = ".$id." ","SILENT");
	
	if($GLOBALS['db']->affected_rows()>0 && $order_status == 5){
		$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ticket_order where id = ".$id);
		//自动计算需要退款的部分，返回到用户帐户
		ticket_auto_refund_amount($order,$is_supplier);
	}
	
	save_ticket_order_log($id,'确认订单',$is_supplier);
}

/**
 * 订单完成 操作
 * 订单状态(流程)1.新订单 2.已确认 3.已完成 4.作废
 * @param int $id 订单ID
 * @param int $is_supplier  0：会员；1：商家；2：管理员
 * return true 成功; false 失败
 */
function ticket_order_complete($id,$is_supplier)
{
	$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ticket_order where order_status in (2,5) and id = ".$id);
	if(empty($order))
	{
		//showErr("不是已经确认的订单,不能直接完成",$ajax,admin_url("ticket_order#order",array(id=>$id)))	;
		return false;
	}else{
		//订单完成时，把未使用的验证码设置成：is_verify_code_invalid = 1无效
		//allow_review: verify_time = 0，不允许点评
		$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order set order_status = 3,over_time = '".NOW_TIME."' where order_status in (2,5) and id = ".$id." ","SILENT");
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
			*/
			//订单作废 时，把验证码设置成：is_verify_code_invalid = 1无效
			$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order_item set is_verify_code_invalid = 1 where order_id = ".$id." ","SILENT");							
			
			$is_divide = intval($order['is_divide']);//是否为团体票 1:个人票 0团体票
			$is_delivery = intval($order['is_delivery']);//是否允为实体票
			
			$use_count = 0;
			if ($is_divide == 0 || $is_delivery == 1){
				//团体票 或 实体票：购买数量 - 退票数量
				$use_count = intval($order['sale_count']) - intval($order['refund_count']);
			}else{
				//获得已使用数量
				//查询：已使用  的门票数量;
				$use_count = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ticket_order_item where verify_time > 0 and order_id = ".$id));				
			}
			
			
			$return_money_total = intval($order['return_money']) * $use_count;
			$return_score_total = intval($order['return_score']) * $use_count;
			$return_exp_total = intval($order['return_exp']) * $use_count;
			$return_voucher_type_id = intval($order['return_voucher_type_id']);
			$user_id = intval($order['user_id']);
			$user_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id);
			//verify_time > 0 and  pay_status = 1  发奖励，返利，允许点评,
			if ($user_data && $order['pay_status'] == 1){
				require_once APP_ROOT_PATH."system/libs/user.php";
				if ($return_money_total > 0){
					User::modify_account($user_id, 1, $return_money_total, "购买订单完成后返现金总数：".format_price_to_display($return_money_total));
					save_ticket_order_log($id,"完成订单时返现金总数：".format_price_to_display($return_money_total),$is_supplier);
				}
					
				if ($return_score_total > 0){
					User::modify_account($user_id, 2, $return_score_total, "购买订单完成后返积分总数：".$return_score_total);
					save_ticket_order_log($id,"完成订单时返积分总数：".$return_score_total,$is_supplier);
				}
					
				if ($return_exp_total > 0){
					User::modify_account($user_id, 3, $return_exp_total, "购买订单完成后返经验总数：".$return_exp_total);
					save_ticket_order_log($id,"完成订单时返经验总数：".$return_exp_total,$is_supplier);
				}
	
				//购买后返还的代金券
				if ($return_voucher_type_id > 0){
					$result = Voucher::gen($return_voucher_type_id, $user_data);
					save_ticket_order_log($id,"完成订单时返代金券：".$result['message'],$is_supplier);
				}
	
				//推荐人的会员ID，主要用于邀请返利用
				$pid = intval($user_data['pid']);
				$rebate_count = $user_data['rebate_count'];
				$rebate_money = intval(app_conf("REBATE_MONEY"));
				if ($pid > 0 && $rebate_count == 0 && $rebate_money > 0){
					//0:定额;1:按销售价百分比;
					$is_rebate = intval($GLOBALS['db']->getOne("select is_rebate from ".DB_PREFIX."ticket where id = '".$order['ticket_id']."'"));
					$puser_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$pid);
					if($is_rebate==1&&$puser_data)
					{
						if (app_conf("REBATE_TYPE") == 0){
							User::gen_rebate($pid, $user_id, $id, 2, $rebate_money);
							//User::modify_account($pid, 1, $rebate_money, $user_id.":首次购买返利：".format_price_to_display($rebate_money));
							save_ticket_order_log($id,$user_data['user_name'].":首次购买返利给:".$puser_data['user_name'].";".format_price_to_display($rebate_money),$is_supplier);
						}else if (app_conf("REBATE_TYPE") == 1){
							$rebate_money = $rebate_money * $order['total_price'] / 100;
							User::gen_rebate($pid, $user_id, $id, 2, $rebate_money);
							//User::modify_account($pid, 1, $rebate_money, $user_id.":首次购买返利：".format_price_to_display($rebate_money));
							save_ticket_order_log($id,$user_data['user_name'].":首次购买返利给:".$puser_data['user_name'].";".format_price_to_display($rebate_money),$is_supplier);
						}
					}
				}
					
				//用户成功购买次数加1
				$GLOBALS['db']->query("update ".DB_PREFIX."user set rebate_count = rebate_count + 1 when id = ".$user_id." ","SILENT");
					
				
				$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order set allow_review = 1, return_money_total = $return_money_total,return_score_total = $return_score_total,return_exp_total = $return_exp_total where id = ".$id." ","SILENT");
				
			}else{
				save_ticket_order_log($id,'完成订单时，发现用户id不存',1);
			}
			save_ticket_order_log($id,'完成订单',$is_supplier);
	
			return true;
		}
	}
	
}

/**
 * 验证码标识使用
 * @param int $id 订单ID
 * @param int $tid 门票ID
 * @param int $verify_code 验证码
 * @param int $is_supplier 0：会员；1：商家；2：管理员
 * @param int $supplier_id 商家ID
 * @return multitype: return:true 验证成功; false:验证失败; message:返回消息内容
 */
function ticket_order_use_verify_code($id,$tid,$verify_code,$is_supplier,$supplier_id = 0)
{	
	$res = array();
	$res['return'] = false;
	$res['message'] = '';
	
	if ($supplier_id > 0){
		$sql = "select * from ".DB_PREFIX."ticket_order where order_status <> 4 and id = ".$id." and supplier_id =".$supplier_id;
	}else{
		$sql = "select * from ".DB_PREFIX."ticket_order where order_status <> 4 and id = ".$id;
	}
	
	$order = $GLOBALS['db']->getRow($sql);
	if(empty($order))
	{
		$res['return'] = false;
		$res['message'] = '订单不存在';
	}else{
		
		$ticket  =  ticket_order_item_sn($verify_code);
		
		if ($ticket['verify_code'] <> $verify_code){
			$res['message'] = '验证码错误';
		}else if ($ticket['is_verify_code_invalid'] == 1){
			$res['message'] = '验证码已失效';
		}else if ($ticket['verify_time'] > 1){
			$res['message'] = '验证码已被验证';
		}else if ($ticket['order_status'] == 1){
			$res['message'] = '订单未确认';
		}else if ($order['order_status'] == 3){
			$res['message'] = '订单已完成';
		}else if ($order['order_status'] == 4){
			$res['message'] = '订单已作废';
		}else{
			$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order_item set verify_time = '".NOW_TIME."' where verify_time = 0 and id = ".$tid." and verify_code = '".$verify_code."'","SILENT");
			if($GLOBALS['db']->affected_rows()>0){
				$res['return'] = true;
				$res['message'] = '验证成功';
				save_ticket_order_log($id,'验证码:'.$verify_code.'验证使用',$is_supplier);
				
				send_use_coupon_sms($order,$verify_code,to_date(NOW_TIME));
				send_use_coupon_mail($order,$verify_code,to_date(NOW_TIME));
				
			}else{
				$res['message'] = '验证失败';
			}
		} 
	}	
	return $res;
}
?>