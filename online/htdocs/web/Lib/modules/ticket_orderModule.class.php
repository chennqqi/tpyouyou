<?php

class ticket_orderModule extends BaseModule{

    function index() {
    	
    	global_run();
    	
    	$ajax = intval($_REQUEST['ajax']);
    	
    	if(!$GLOBALS['user']){
    		if($ajax == 0){
	    		set_gopreview();
	    		app_redirect(url("user#login"));
    		}
    		else{
    			$result['status'] = 2;
    			ajax_return($result);
    		}
    	}
    	
    	$id = intval($_REQUEST['id']);
    	if($id > 0){
    		$this->do_cart();
    		if($ajax == 0){
    			app_redirect(url("ticket_order#index"));
    		}
    	}
    	
    	//获取购物车里面的数据 
    	$result = $this->get_cart();
    	$list = array();
    	$appointment = array();
    	$is_deliver = 0;
    	$paper_must = 0;
    	foreach($result['list'] as $k=>$v){
    		$appointment[$v['spot_id']]['name'] = $v['name'];
    		$appointment[$v['spot_id']]['appointment_desc'] = $v['appointment_desc'];
    		$list[$v['id']] = $v;
    		if($v['is_appoint_time'] == 0){//免预约
    			if($v['is_end_time'] == 0){
    				if($v['end_time'] == 0)
    					$list[$v['id']]['end_time'] = "永不过期";
    				else
    					$list[$v['id']]['end_time'] = to_date($v['end_time'],"Y-m-d");
    			}
    			else
	    			$list[$v['id']]['end_time'] = to_date(NOW_TIME + $v['end_time_day']*3600*24 - 1,"Y-m-d");
    		}
    		else{//需预约
    			$list[$v['id']]['min_date'] = to_date(NOW_TIME,"Y-m-d");
    			if($v['end_time'] > 0)
	    			$list[$v['id']]['max_date'] = to_date($v['end_time'],"Y-m-d");
	    		else{
	    			$list[$v['id']]['max_date'] = to_date(NOW_TIME + 365*24*3600,"Y-m-d");
	    		}
    		}
    		
    		if($v['is_delivery'] == 1){
    			$is_deliver = 1;
    		}
    		
    		if($v['paper_must'] == 1){
    			$paper_must = 1;
    		}
    	}
    	
    	if(!$list){
    		showErr("很抱歉，您查看的门票不存在，可能已过期或者被转移，暂时不能购买。");
    	}
    	
    	$GLOBALS['tmpl']->assign("paper_must",$paper_must);
    	$GLOBALS['tmpl']->assign("list",$list);
    	//购物车JSON数据
    	$GLOBALS['tmpl']->assign("json_list",json_encode($list));
    	$GLOBALS['tmpl']->assign("userinfo",$GLOBALS['user']);
    	$GLOBALS['tmpl']->assign("MIN_DATE",to_date(NOW_TIME,"Y-m-d"));
    	
    	//预订协议
    	$GLOBALS['tmpl']->assign("appointment",$appointment);
    	
    	$voucher_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."voucher where is_effect = 1 and user_id =".intval($GLOBALS['user']['id'])." and is_used =0 and ((end_time > 0 AND end_time >".NOW_TIME.") or end_time=0)  order by create_time desc ");
    	$GLOBALS['tmpl']->assign("voucher_list",$voucher_list);
    	
    	//假如需要配送的话
    	if($is_deliver==1){
    		$province_list = load_auto_cache("province_list");
	    	$GLOBALS['tmpl']->assign("province_list",$province_list);
	    	if($result['deliver'])
	    		$GLOBALS['tmpl']->assign("deliver_json_data",json_encode($result['deliver']));
	    	//会员的配送地址
	    	$consignee_list = $GLOBALS["db"]->getAll("SELECT uc.*,p.name as province_name,c.name as city_name FROM ".DB_PREFIX."user_consignee uc LEFT JOIN ".DB_PREFIX."province p ON p.id = uc.province_id LEFT JOIN ".DB_PREFIX."city c ON c.id = uc.city_id  WHERE uc.user_id=".$GLOBALS['user']['id']." ORDER BY is_default DESC");
			$GLOBALS['tmpl']->assign("consignee_list",$consignee_list);
			$GLOBALS['tmpl']->assign("is_deliver",$is_deliver);
    	}
    	
    	$api_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."api_login");
    	$GLOBALS['tmpl']->assign("api_list",$api_list);
    	
    	//会员信息
    	$user=array();
    	if($GLOBALS['user']['truename'] !='')
    		$user['name']= $GLOBALS['user']['truename'];
    	else
    		$user['name']= $GLOBALS['user']['user_name'];
    		
    	$user['mobile'] = $GLOBALS['user']['mobile'];
    	$user['email'] = $GLOBALS['user']['email'];
    	$user['paper_type'] = $GLOBALS['user']['paper_type'];
    	$user['paper_sn'] = $GLOBALS['user']['paper_sn'];
    	
    	$GLOBALS['tmpl']->assign("user",$user);
    	
    	init_app_page();
    	//输出SEO元素
    	
    	$GLOBALS['tmpl']->assign("site_name","景点门票预订 - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword","景点门票预订,".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description","景点门票预订,".app_conf("SITE_DESCRIPTION"));
    	
    	
    	$GLOBALS['tmpl']->display("ticket_order.html");
    }
    
    //添加到购物车
    function do_cart(){
    	$id = intval($_REQUEST['id']);
    	$ajax = intval($_REQUEST['ajax']);
    	if($id > 0){
    		require APP_ROOT_PATH . "system/libs/spot.php";
    		$ticket = get_ticket($id);
    		
    		if($ticket){
    			if($ticket['is_history'] == 1){
    				showErr("门票已下架。",$ajax);
    			}
    			//默认购买个数
    			if($ticket["min_buy"] > 0)
    				$sale_count = $ticket["min_buy"];
    			else
    				$sale_count = 1;
    			if($ticket['sale_max'] >0  && $ticket['sale_total'] + $sale_count > $ticket['sale_max'] ){
    				showErr("门票库存不足。",$ajax);
    			}
    			
    			if($ticket['is_delivery']==1 && $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."ticket_cart WHERE ticket_id<>".$id." and is_delivery=1 and user_id= ".$GLOBALS['user']['id'])>0){
    				showErr("购物车内只允许一种配送门票。",$ajax);
    			}
    				
    			$data['ticket_id'] =$id;
    			$data['ticket_name'] = $ticket['name'];
    			$data['session_id'] = es_session::id();
    			$data['user_id'] = $GLOBALS['user']['id'];
    			$data['create_time'] = NOW_TIME;
    			$data['sale_price'] = $ticket['sale_price'];
    			$data['sale_count'] = $sale_count;
    			$data['return_money'] = $ticket['return_money'];
    			$data['return_score'] = $ticket['return_score'];
    			$data['return_exp'] = $ticket['return_exp'];
    			$data['return_voucher_type_id'] = $ticket['voucher'];
    			$data['is_refund'] = $ticket['is_refund'];
    			$data['is_delivery'] = $ticket['is_delivery'];
    			$data['is_expire_refund'] = $ticket['is_expire_refund'];
    			$data['supplier_id'] = $ticket['supplier_id'];
    			$data['is_appoint_time'] = $ticket['is_appoint_time'];
    			$data['is_end_time'] = $ticket['is_end_time'];
    			
    			if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."ticket_cart WHERE ticket_id=".$id." and user_id= ".$GLOBALS['user']['id'])==0){
    				$GLOBALS['db']->autoExecute(DB_PREFIX."ticket_cart",$data);
    				if($GLOBALS['db']->insert_id() == 0)
    					showErr("放入购物车失败。",$ajax);
    			}
    		}
    		else{
    			showErr("门票不存在或已失效。",$ajax);
    		}
    		
    		$result['status'] = 1;
    		if($ajax==1)
    			ajax_return($result);
    		else
    			return true;
    	}
    }
    
    //获取购物车
    public static function get_cart($act = ""){
    	$GLOBALS['user'] = User::load_user();
		if(empty($GLOBALS['user']))User::auto_do_login();
    	
    	$session_id = es_session::id();
    	$user_id = $GLOBALS['user']['id'];
    	
    	$result = array();
    	
    	$sql = "SELECT tc.*,t.short_name,t.name_brief,t.min_buy,t.max_buy,t.is_divide," .
    			"t.spot_id,t.end_time_day,t.end_time,t.paper_must, " .
    			"t.is_buy_return,t.is_review_return,t.review_return_money,t.review_return_score,t.review_return_exp,t.order_status," .
    			"s.image,s.name,s.appointment_desc,s.address,s.brief,t.pay_type FROM ".DB_PREFIX."ticket_cart tc " .
    			"LEFT JOIN ".DB_PREFIX."ticket t ON t.id = tc.ticket_id  " .
    			"LEFT JOIN ".DB_PREFIX."spot s ON s.id = t.spot_id " .
    			"WHERE (tc.user_id=".$user_id." OR tc.session_id='".$session_id."') ";
    	
    	$list = $GLOBALS['db']->getAll($sql);
    	
    	if($list){
	    	//根据购物车的商品获取商家
	    	$supplier_ids = array();
	    	$ticket_ids = array();
	    	foreach($list as $k=>$v){
	    		unset($list[$k]['session_id']);
	    		unset($list[$k]['user_id']);
	    		$supplier_ids[$v['supplier_id']] = $v['supplier_id'];
	    		$list[$k]['sale_price'] = format_price_to_display($v['sale_price']);
	    		if($v['is_buy_return'] == 1){
		    		$list[$k]['return_money'] = $v['return_money'] > 0 ? $v['return_money'] : (intval(app_conf("BUY_RETURN_MONEY_TYPE")) ==0 ? app_conf("BUY_RETURN_MONEY") : $v['sale_price'] * app_conf("BUY_RETURN_MONEY") / 100);
		    		$list[$k]['return_score'] = $v['return_score'] > 0 ? $v['return_score'] : (intval(app_conf("BUY_RETURN_SCORE_TYPE")) ==0 ? app_conf("BUY_RETURN_SCORE") : $v['sale_price'] * app_conf("BUY_RETURN_SCORE") / 100);
		    		$list[$k]['return_exp'] = $v['return_exp'] > 0 ? $v['return_exp'] : (intval(app_conf("BUY_RETURN_EXP_TYPE")) ==0 ? app_conf("BUY_RETURN_EXP") : $v['sale_price'] * app_conf("BUY_RETURN_EXP") / 100);
	    		}
	    		if($v['is_review_return'] == 1){
	    			$list[$k]['review_return_money'] = $v['review_return_money'] > 0 ? $v['review_return_money'] : app_conf("REVIEW_MONEY");
	    			$list[$k]['review_return_score'] = $v['review_return_score']> 0 ? $v['review_return_score'] : app_conf("REVIEW_SCORE");
	    			$list[$k]['review_return_exp'] = $v['review_return_exp']> 0 ? $v['review_return_exp'] : app_conf("REVIEW_EXP");
	    		}
	    		$ticket_ids[] = $v['ticket_id'];
	    	}
	    	if($act == "submit" && count($ticket_ids)>0){
	    		$tvoucher_promote = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."voucher_promote where voucher_rel_id in(".implode(",",$ticket_ids).") AND voucher_promote = 1 ");
				$voucher_promote = array();
				foreach($tvoucher_promote as $k=>$v){
					if($v['voucher_promote_type'] == 1)
						$voucher_promote['buy'][$v['voucher_rel_id']] = $v['voucher_type_id'];
					if($v['voucher_promote_type'] == 2)
						$voucher_promote['review'][$v['voucher_rel_id']] = $v['voucher_type_id'];
				}
				unset($tvoucher_promote);
				foreach($list as $k=>$v){
					$list[$k]['voucher'] = isset($voucher_promote['buy'][$v['ticket_id']]) ? intval($voucher_promote['buy'][$v['ticket_id']]) : 0; 
					$list[$k]['review_voucher'] = intval($voucher_promote['review'][$v['ticket_id']]) ? isset($voucher_promote['review'][$v['ticket_id']]) : app_conf("REVIEW_VOUCHER"); 
				}
				
	    	}
	    	$result["list"] = $list;
	    	unset($list);
	    	//工具商家获取配送
	    	if($supplier_ids){
	    		$tdeliver = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."supplier_freight WHERE supplier_id in (".implode(",",$supplier_ids).") ORDER BY is_default ASC ");
	    		$deliver = array();
	    		if($tdeliver){
	    			foreach($tdeliver as $k=>$v){
	    				$v['price'] = format_price_to_display($v['price']);
	    				if($v['province_id'] > 0 && $v['city_id'] >0)
	    					$deliver[$v['supplier_id']]['city'][$v['province_id']][$v['city_id']] = $v;
	    				if($v['province_id'] > 0 && $v['city_id'] == 0)
	    					$deliver[$v['supplier_id']]['province'][$v['province_id']] = $v;
	    				if($v['is_default']==1)
	    					$deliver[$v['supplier_id']]['default'] = $v;
	    			}
	    		}
	    		unset($tdeliver);
	    		$result['deliver'] = $deliver;
	    	}
	    	
    	}
    	
    	$result['supplier'] = $supplier_ids;
    	
    	return $result;
    }
    
    /**
     * 删除购物资料
     */
    function del(){
    	$id = intval($_REQUEST['id']);
    	if($id==0){
    		showErr("删除失败",1);
    	}
    	
    	$GLOBALS['user'] = User::load_user();
		if(empty($GLOBALS['user']))User::auto_do_login();
		
		$session_id= es_session::id();
		
		$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."ticket_cart WHERE (user_id=".$GLOBALS['user']['id']." OR session_id='".$session_id."') and id=".$id." ");
		
		if($GLOBALS["db"]->affected_rows() > 0){
			showSuccess("删除成功",1);
		}
		else{
			showErr("删除失败",1);
		}
    }
    
    /**
     * 提交购物车
     */
    function submit_cart(){
    	
    	$return = array("status"=>0,"info"=>"","jump");
    	$GLOBALS['user'] = User::load_user();
		if(empty($GLOBALS['user']))User::auto_do_login();
		$session_id = es_session::id();
		if(empty($GLOBALS['user'])){
			$return['status'] = 2;
			$return['info'] = "请先登录";
			ajax_return($return);
		}
		
    	//获取购物车里面的数据 
    	$result = $this->get_cart("submit");
    	if(!$result['list']){
    		showErr("购物车内无数据",1);
    	}
    	    	
    	//出游日期-过期日期数组
    	$end_time = $_POST['end_time'];
    	//购买数量数组
    	$sale_count = $_POST['sale_count'];
    	//配送方式数组
    	$pickRadio = $_POST['pickRadio'];
    	//代金券数组
    	$voucher = $_POST['voucher'];
    	//余额支付
    	$account_pay = format_price_to_db(strim($_POST['account_pay']));
    	if($account_pay > 0){
    		$left_account_pay = $GLOBALS['user']['money'];
    	}
    	
    	//预订人姓名
    	$appoint_name = strim($_POST['appoint_name']);
    	//预订人手机
    	$appoint_mobile = strim($_POST['appoint_mobile']);

        session_start();

        $_SESSION['mobile']= $appoint_mobile;

    	//预订人邮箱
    	$appoint_email = strim($_POST['appoint_email']);
    	
    	$paper_type = intval($_POST['paper_type']);
    	$paper_sn = strim($_POST['paper_sn']);
    	
    	
    	//配送地址 
    	$user_consignee  = intval($_POST['user_consignee']);
    	
    	$delivery_name = strim($_POST['delivery_name']);
    	$province_id = intval($_POST['province_id']);
    	$city_id = intval($_POST['city_id']);
    	$address = strim($_POST['address']);
    	$zip = strim($_POST['zip']);
    	$delivery_mobile = strim($_POST['delivery_mobile']);
    	$order_memo = strim($_POST['order_memo']);
    	
    	
    	//是否有有配送
    	$all_has_delivery = false;
    	//是否需要输入证件号
    	$has_paper = false;
    	$has_supplier_deliver=array();
    	
    	if($appoint_name==""){
    		showErr("预订人姓名不能为空",1);
    	}
    	if($appoint_mobile==""){
    		showErr("预订人手机不能为空",1);
    	}
    	elseif(!check_mobile($appoint_mobile)){
    		showErr("预订人手机格式错误",1);
    	}
    	if($appoint_email!="" && !check_email($appoint_email)){
    		showErr("预订人邮箱不能为空",1);
    	}
    	
    	
    	//验证产品
    	foreach($result['list'] as $k=>$v){
    		//判断最小购买数量
    		if($v['min_buy'] > 0){
    			if($sale_count[$v['id']] < $v['min_buy']){
    				showErr($v['ticket_name']." 至少购买 ".$v['min_buy']." 张",1);
    			}
    		}
    		//判断最大购买数量
    		if($v['max_buy'] > 0){
    			if($sale_count[$v['id']] > $v['max_buy']){
    				showErr($v['ticket_name']." 至多购买  ".$v['max_buy']." 张",1);
    			}
    		}
    		//判断是否销售完毕
    		if($v['sale_max'] >0  && $v['sale_total'] + $sale_count > $v['sale_max'] ){
				showErr($v['ticket_name']." 库存不足。",1);
			}
			
			//是否允许配送
			if($pickRadio[$v['id']] == 1 && $v['is_delivery'] == 0){
				showErr($v['ticket_name']." 不允许快递配送。",1);
			}
			
			if($pickRadio[$v['id']] == 1 && $v['is_delivery'] == 1){
				$all_has_delivery = true;
			}
			
			if($v['paper_must'] == 1){
				$has_paper = true;
			}
			
    	}
    	
    	//验证代金券
    	$left_voucher_price = 0;
    	if($voucher){
	    	$voucher_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."voucher where  id in(".implode(",",$voucher).") ");
	    	$tmp_voucher = array();
	    	foreach($voucher_list as $k=>$v){
	    		if($v['is_used'] == 1){
	    			showErr("代金券".$v['voucher_name']." 已使用。",1);
	    		}
	    		if($v['end_time'] <> 0 && $v['end_time'] <= NOW_TIME){
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
	    		$left_voucher_price +=$tmp_voucher[$v]['money'];
	    	}
    	}
    	
    	//如果有需要输入证件的
    	if($has_paper){
    		if($paper_type == 0){
    			showErr("请选择证件类型",1);
    		}
    		if($paper_sn == ""){
    			showErr("请输入证件号",1);
    		}
    	}
    	
    	//如果有配送
    	if($all_has_delivery){
    		if($user_consignee == 0){
		    	$delivery_mobile = strim($_POST['delivery_mobile']);
    			if($delivery_name==""){
    				showErr("请填写收件人姓名",1);
    			}
    			if($province_id==0){
    				showErr("请选择省份",1);
    			}
    			if($city_id==0){
    				showErr("请选择城市",1);
    			}
    			if($address==""){
    				showErr("请填写详细地址",1);
    			}
    			if($zip==""){
    				showErr("请填写邮政编码",1);
    			}
    			if($delivery_mobile==""){
    				showErr("请填写配送手机号码",1);
    			}
    			if(!check_mobile($appoint_mobile)){
		    		showErr("配送手机号码格式错误",1);
		    	}
    		}
    		else{
    			$consignee = User::get_consignee($user_consignee);
    			
    			$delivery_name = $consignee['delivery_name'];
		    	$province_id = intval($consignee['province_id']);
		    	$city_id = intval($consignee['city_id']);
		    	$address = $consignee['address'];
		    	$zip = $consignee['zip'];
		    	$delivery_mobile = $consignee['delivery_mobile'];
    		}
    		
    		$province_name = $GLOBALS['db']->getOne("SELECT name FROM ".DB_PREFIX."province WHERE id=".$province_id);
    		$city_name = $GLOBALS['db']->getOne("SELECT name FROM ".DB_PREFIX."city WHERE id=".$city_id);
    	}
    	else{
    		$delivery_name = "";
	    	$province_id = 0;
	    	$city_id = 0;
	    	$address = "";
	    	$zip = "";
	    	$delivery_mobile = "";
    	}
    	
    	$is_error = false;
    	$temp_tickets=array();
    	foreach($result['list'] as $k=>$v){
    		$order_data = array();
    		$order_data['ticket_name'] = $v['ticket_name'];
    		$order_data['short_name'] = $v['short_name'];
    		$order_data['ticket_id'] = $v['ticket_id'];
    		$order_data['order_confirm_type'] = $v['order_status'];
    		$order_data['user_id'] = $GLOBALS['user']['id'];
	    	//1.新订单 2.已确认 3.已完成 4.作废
	    	$order_data['order_status'] = 0;
	    	
	    	//0.未支付(代金券或余额支付仍算未支付) 1.已支付(pay_a mount==total_price)
	    	$order_data['pay_status'] = 0;
	    	//已付金额
	    	$order_data['pay_amount'] =0;
	    	//商品本身金额
	    	$order_data['item_price'] = 0;
	    	//运费
	    	$order_data['delivery_fee'] = 0;
	    	//余额支付部份
	    	$order_data['account_pay'] = 0;
	    	
	    	//销售数量
	    	$order_data['sale_count'] = $sale_count[$v['id']];
	    	
			//获取门票本身价格
    		$order_data['item_price'] = format_price_to_db($v['sale_price']);
    		
    		//获取运费
    		if(!isset($has_supplier_deliver[$v['supplier_id']])){
	    		if(isset($result['deliver'][$v['supplier_id']]['city'][$province_id][$city_id])){
	    			$order_data['delivery_fee'] = format_price_to_db($result['deliver'][$v['supplier_id']]['city'][$province_id][$city_id]['price']);
	    			$has_supplier_deliver[$v['supplier_id']] = 1;
	    		}
	    		elseif(isset($result['deliver'][$v['supplier_id']]['province'][$province_id])){
	    			$order_data['delivery_fee'] = format_price_to_db($result['deliver'][$v['supplier_id']]['province'][$province_id]['price']);
	    			$has_supplier_deliver[$v['supplier_id']] = 1;
	    		}
	    		elseif(isset($result['deliver'][$v['supplier_id']]['default'])){
	    			$order_data['delivery_fee'] = format_price_to_db($result['deliver'][$v['supplier_id']]['default']['price']);
	    			$has_supplier_deliver[$v['supplier_id']] = 1;
	    		}
    		}
    		
    		//是否需要配送 
    		if($pickRadio[$v['id']] == 1 && $v['is_delivery'] == 1){
				$has_delivery = true;
				$order_data['is_delivery'] = 1;
				$order_data['delivery_status'] = 0;
				if($province_id){
			    	$order_data['province_id'] = $province_id;
			    	$order_data['province_name'] = $province_name;
		    	}
		    	if($city_id > 0){
			    	$order_data['city_id'] = $city_id;
			    	$order_data['city_name'] = $city_name;
		    	}
		    	$order_data['address'] = $address;
		    	$order_data['zip'] = $zip;
		    	
	    		$order_data['delivery_name'] = $delivery_name;
		    	$order_data['delivery_mobile'] = $delivery_mobile;
			}
			else{
				$order_data['delivery_status'] = -1;
			}
	    	
	    	//应付金额
    		$order_data['total_price'] = $order_data['item_price']*intval($order_data['sale_count']) + $order_data['delivery_fee'];
    		
	    	$order_data['create_time'] = NOW_TIME;
	    	if($v['is_appoint_time'] == 0){//免预约
    			$order_data['appoint_time'] = 0;
    			
    			if($v['is_end_time'] == 0)//如果是按固定日期过期的话
	    		{
	    			if($v["end_time"]==0)//无限时间
	    				$order_data['end_time'] = 0;
	    			else
	    				$order_data['end_time'] = $v["end_time"] + 24*3600 - 1;	
	    			
	    		}
				else//如果是按购买之日起固定天数
				{
					$order_data['end_time'] = to_timespan(to_date(NOW_TIME,"Y-m-d"),"Y-m-d") + $v['end_time_day']*24*3600  + 24*3600 - 1;	
				}
				
    		}
    		else{//需要预约
    			$order_data['appoint_time'] =  $order_data['end_time'] = to_timespan($end_time[$v['id']],"Y-m-d") + 24*3600 - 1;
    			
    		}
	    	
	    	$order_data['appoint_name'] = $appoint_name;
	    	$order_data['appoint_mobile'] = $appoint_mobile;
	    	$order_data['appoint_email'] = $appoint_email;
	    	if($v['paper_must'] == 1){
	    		$order_data['paper_type'] = $paper_type;
	    		$order_data['paper_sn'] = $paper_sn;
	    	}
	    	
	    	$order_data['order_status'] = 1;
	    	
	    	
	    	//代金券抵用金额
	    	if($left_voucher_price > 0){
	    		//假如代金券金额超出订单总额
	    		if($order_data['total_price'] - $left_voucher_price < 0){
	    			$temp_tickets[$k]['voucher_pay'] = $order_data['total_price'];
	    			//留到下次使用
	    			$left_voucher_price = $left_voucher_price - $temp_tickets[$k]['voucher_pay'];
	    		}
	    		else{
	    			$temp_tickets[$k]['voucher_pay'] = $left_voucher_price;
	    			$left_voucher_price = 0;
	    		}	
	    	}
	    	
	    	//余额支付
	    	if($left_account_pay > 0){
	    		//假如余额  超出订单总额-代金券支付金额
		    	if($order_data['total_price'] - $temp_tickets[$k]['voucher_pay'] - $left_account_pay < 0){
		    		$temp_tickets[$k]['account_pay'] = $order_data['total_price'] - $temp_tickets[$k]['voucher_pay'];
		    		$left_account_pay = $left_account_pay - $temp_tickets[$k]['account_pay'];
		    	}
	    		else{
	    			$temp_tickets[$k]['account_pay'] = $left_account_pay;
	    			$left_account_pay = 0;
	    		}
	    	}
	    	
	    	//购买返还
	    	$order_data['return_money'] = $v['return_money'];
    		$order_data['return_score'] = $v['return_score'];
    		$order_data['return_exp'] = $v['return_exp'];
    		$order_data['return_voucher_type_id'] = $v['voucher'];
    		//评论返还
    		$order_data['review_return_money'] = $v['review_return_money'];
    		$order_data['review_return_score'] = $v['review_return_score'];
    		$order_data['review_return_exp'] = $v['review_return_score'];
    		$order_data['review_return_voucher_type_id'] = $v['review_voucher'];
    		$order_data['is_refund'] = $v['is_refund'];
    		$order_data['is_expire_refund'] = $v['is_expire_refund'];
    		
    		$order_data['supplier_id'] = $v['supplier_id'];
    		
    		
	    	$order_data['is_divide'] = $v['is_divide'];
	    	$order_data['order_memo'] = $order_memo;
	    	
	    	$order_data['spot_id'] = $v['spot_id'];//景点id
	    	
	    	$temp_tickets[$k]['order_id'] = 0;
	    	
	    	$kk=0;
	    	do{
	    		$temp_tickets[$k]['sn'] = $order_data['sn']= "T_".to_date(NOW_TIME,"Ymdhis").rand(10,99);
	    		$GLOBALS['db']->autoExecute(DB_PREFIX."ticket_order",$order_data,"INSERT","","SILENT");
	    		$temp_tickets[$k]['order_id'] = $GLOBALS['db']->insert_id();
	    		$kk ++ ;
	    	}while($temp_tickets[$k]['order_id']==0 && $k < 100);
	    	
	    	if($temp_tickets[$k]['order_id'] == 0){
	    		$is_error = true;
	    	}
	    	else{
	    		//======================下单成功将门票插入数据库 start====================
	    		$order_item_data = array();
	    		
	    		$order_item_data['ticket_id'] = $v['ticket_id'];
	    		$order_item_data['ticket_name'] = $v['ticket_name'];
	    		$order_item_data['order_id'] = $temp_tickets[$k]['order_id'];
	    		$order_item_data['user_id'] = $GLOBALS['user']['id'];
	    		$order_item_data['supplier_id'] = $v['supplier_id'];
	    		$order_item_data['is_appoint_time'] = $v['is_appoint_time'];
	    		
	    		if($v['is_appoint_time'] == 0){//免预约
	    			$order_item_data['begin_time'] = NOW_TIME;
	    			$order_item_data['appoint_time'] = 0;
	    			
	    			if($v['is_end_time'] == 0)//如果是按固定日期过期的话
		    		{	
		    			if($v["end_time"]==0)//无限时间
		    				$order_item_data['end_time'] = 0;
		    			else
		    				$order_item_data['end_time'] = $v["end_time"] + 24*3600 - 1;	
		    		}
					else//如果是按购买之日起固定天数
					{
						$order_item_data['end_time'] = to_timespan(to_date(NOW_TIME,"Y-m-d"),"Y-m-d") + $v['end_time_day']*24*3600 -1;	
					}
					
	    		}
	    		else{//需要预约
	    			$order_item_data['begin_time'] = to_timespan($end_time[$v['id']],"Y-m-d");
	    			$order_item_data['appoint_time'] = $order_item_data['end_time'] = to_timespan($end_time[$v['id']],"Y-m-d") + 24*3600 - 1;
	    			
	    		}
	    		
	    		$order_item_data['supplier_id'] = $pickRadio[$v['supplier_id']];
	    		$order_item_data['is_divide'] = $v['is_divide'];
	    		
	    		//0团体票  或者实体票只生成一张 
	    		if($v['is_divide'] == 0 || $v['is_delivery'] == 1){
	    			$insert_id = 0;
	    			$kk=0;
	    			do{
	    				//非实体票生成序列号
	    				if($order_data['is_delivery']==0)
	    					$order_item_data['verify_code'] = rand(10000000,99999999);
	    				$GLOBALS['db']->autoExecute(DB_PREFIX."ticket_order_item",$order_item_data,"INSERT","","SILENT");
	    				$insert_id =$GLOBALS['db']->insert_id();
	    				$kk++;
	    			}while($insert_id == 0&&$kk<100);
	    			
	    			//下单失败
	    			if($insert_id==0){
	    				$is_error = true;
	    			}
	    			
	    		}
	    		else{
	    			for($i=0;$i<$sale_count[$v['id']];$i++){
	    				$insert_id = 0;
	    				$kk=0;
	    				do{
	    					//非实体票生成序列号
	    					if($order_data['is_delivery']==0)
		    					$order_item_data['verify_code'] = rand(10000000,99999999);
		    				$GLOBALS['db']->autoExecute(DB_PREFIX."ticket_order_item",$order_item_data,"INSERT","","SILENT");
		    				$insert_id =$GLOBALS['db']->insert_id();
		    			$kk++;
	    				}while($insert_id == 0&&$kk<100);
	    				
	    				//下单失败
		    			if($insert_id==0){
		    				$is_error = true;
		    			}
	    			}
	    		}
		    	
		    	//======================下单成功将门票插入数据库 end====================
	    		
	    	}
	    	
	    	
    	}
    	
    	
    	if($is_error == false){
    		require APP_ROOT_PATH . "system/libs/spot.php";
    		$order_ids = array();
    		$pay_order_sn = array();
    		$no_pay_order_sn = array();
    		foreach($temp_tickets as $kk=>$vv){
    			$order_ids[] = $vv['order_id'];
    			$pay_status = ticket_order_paid($vv['sn'],0,intval($vv['account_pay']),intval($vv['voucher_pay']));
    			
    			if($pay_status == 1){//支付成功
    				$pay_order_sn[] = $vv['sn'];
    			}
    			else{//未支付
    				$no_pay_order_sn[] =  $vv['sn'];
    			}
    			
    			if(intval($vv['account_pay']) > 0){
    				User::modify_account($GLOBALS['user']['id'],1,-intval($vv['account_pay']),"订单 ".$vv['sn']." 使用余额支付".format_price_to_display($vv['account_pay'])."元。");
    			}
    		}
    		
    		//更改代金券状态
    		if(count($voucher) > 0){
	    		$voucher_data['is_used'] = 1;
	    		$voucher_data['use_otype'] = 2;
	    		$voucher_data['use_oid'] = $order_ids[0];
	    		$voucher_data['use_time'] = NOW_TIME;
	    		$GLOBALS['db']->autoExecute(DB_PREFIX."voucher",$voucher_data,"UPDATE"," id in (".implode(",",$voucher).") and user_id ='".$GLOBALS['user']['id']."' ");
    		}
    		
    		//更新订单状态
    		$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."ticket_cart WHERE (session_id= '".$session_id."' or user_id='".$GLOBALS['user']['id']."') ");
    		
    		//保存地址
    		if($all_has_delivery && intval($_POST['user_consignee'])==0 && intval($_POST['save_delivery']) == 1){
    			$consignee_data['delivery_name'] = $delivery_name;
    			$consignee_data['province_id'] = $province_id;
    			$consignee_data['city_id'] = $city_id;
    			$consignee_data['address'] = $address;
    			$consignee_data['zip'] = $zip;
    			$consignee_data['delivery_mobile'] = $delivery_mobile;
    			$GLOBALS['db']->autoExecute(DB_PREFIX."user_consignee",$consignee_data,"INSERT","","SILENT");
    		}
    		
    		
    		//发微博
    		if(intval($_POST['share_order']) == 1){
    			$has_send_weibo = array();
	    		foreach($result['list'] as $k=>$v){
	    			if(!isset($has_send_weibo[$v['spot_id']])){
	    				$image_list[] = $v['image'];
	    				User::send_weibo($GLOBALS['user'],$v['ticket_name'],$image_list,url("spot#view",array("id"=>$v['spot_id'],"ref_pid"=>base64_encode($GLOBALS['user']['id']))));
	    				$has_send_weibo[$v['spot_id']] = 1;
	    			}
	    		}
	    		
    		}
    		
    		if(count($pay_order_sn) > 0)
    			showSuccess("提交成功",1,url("transaction#done",array("ot"=>2,"sn"=>implode(",",$pay_order_sn))));
    		else
    			showSuccess("提交成功",1,url("transaction#pay",array("ot"=>2,"sn"=>implode(",",$no_pay_order_sn))));
    	}
    	else{
    		$order_ids = array();
    		foreach($temp_tickets as $kk=>$vv){
    			$order_ids[] = $vv['order_id'];
    		}
    		if(count($order_ids) > 0){
	    		$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."ticket_order_item  WHERE order_id in (".implode(",",$order_ids).")");
				$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."ticket_order  WHERE id in (".implode(",",$order_ids).")");
    		}
    		showErr("下单失败",1);
    	}
    	
    }
}
?>