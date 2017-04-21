<?php
class spot_ticketModule {

    function add() {
    	$NOW_DATE = to_date(NOW_TIME,"Y-m-d");
    	$GLOBALS['tmpl']->assign("NOW_DATE",$NOW_DATE);
    	
    	$vouchers = $GLOBALS['db']->getAll("select id,voucher_name from ".DB_PREFIX."voucher_type where deliver_type=3 and is_effect=1 ORDER BY sort DESC");
    	$GLOBALS['tmpl']->assign("vouchers",$vouchers);
    	
    	$tuan_cates = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."tuan_cate ORDER BY sort DESC");
    	$GLOBALS['tmpl']->assign("tuan_cates",$tuan_cates);
    	
    	$GLOBALS['tmpl']->assign("formaction",admin_url("spot_ticket#insert",array("ajax"=>1)));
    	$GLOBALS['tmpl']->display("core/spot_ticket/add.html");
    }
    
    function insert(){
    	$ajax = intval($_REQUEST['ajax']);
    	if(intval($_REQUEST['is_end_time'])==1 && intval($_REQUEST['end_time_day']) <=0){
    		showErr("过期时间天数必须大于0",$ajax);
    	}
    	if(intval($_REQUEST['is_tuan'])==1){
    		if(intval($_REQUEST['tuan_cate']) <=0)
    			showErr("请选择团购分类",$ajax);
    		
    		
    		$tuan_begin_time=0; 
    		$tuan_end_time=0;
    		if(strim($_REQUEST['tuan_begin_time'])!=""){
				$tuan_begin_time=to_timespan($_REQUEST['tuan_begin_time']);
			}
						
			if(strim($_REQUEST['tuan_end_time'])!=""){
				$tuan_end_time=to_timespan($_REQUEST['tuan_end_time']);							
				if($tuan_end_time<$tuan_begin_time){
					showErr("团购结束时间必须晚于开始时间",$ajax);								
				}
			}		
    	}
    	$data = base64_encode(serialize($_REQUEST));
    	showSuccess($data,$ajax);
    }
    
    function edit(){
    	$tickets = unserialize(base64_decode($_POST['tickets']));
    	
    	$NOW_DATE = to_date(NOW_TIME,"Y-m-d");
    	$GLOBALS['tmpl']->assign("NOW_DATE",$NOW_DATE);
    	
    	$vouchers = $GLOBALS['db']->getAll("select id,voucher_name from ".DB_PREFIX."voucher_type where deliver_type=3 and is_effect=1 ORDER BY sort DESC");
    	$GLOBALS['tmpl']->assign("vouchers",$vouchers);
    	
    	$tuan_cates = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."tuan_cate ORDER BY sort DESC");
    	$GLOBALS['tmpl']->assign("tuan_cates",$tuan_cates);
    	
    	$GLOBALS['tmpl']->assign("ticket",$tickets);
    	
    	$GLOBALS['tmpl']->assign("formaction",admin_url("spot_ticket#update",array("ajax"=>1)));
    	$GLOBALS['tmpl']->display("core/spot_ticket/edit.html");
    }
    
    function update(){
    	$ajax = intval($_REQUEST['ajax']);
    	if(intval($_REQUEST['is_end_time'])==1 && intval($_REQUEST['end_time_day']) <=0){
    		showErr("过期时间天数必须大于0",$ajax);
    	}
    	if(intval($_REQUEST['is_tuan'])==1){
    		if(intval($_REQUEST['tuan_cate']) <=0)
    			showErr("请选择团购分类",$ajax);
    		
    		$tuan_begin_time=0; 
    		$tuan_end_time=0;
    		if(strim($_REQUEST['tuan_begin_time'])!=""){
				$tuan_begin_time=to_timespan($_REQUEST['tuan_begin_time']);
			}
						
			if(strim($_REQUEST['tuan_end_time'])!=""){
				$tuan_end_time=to_timespan($_REQUEST['tuan_end_time']);							
				if($tuan_end_time<$tuan_begin_time){
					showErr("团购结束时间必须晚于开始时间",$ajax);								
				}
			}		  		
    	}
    	//更新数据库操作
    	if(intval($_REQUEST['id']) > 0){
    		$ticket = $_POST;
    		
			$ticket_data['name'] = strim($ticket['name']);
			$ticket_data['short_name'] = strim($ticket['short_name']);
			$ticket_data['name_brief'] = strim($ticket['name_brief']);
			$ticket_data['is_appoint_time'] = intval($ticket['is_appoint_time']);
			$ticket_data['is_end_time'] = intval($ticket['is_end_time']);
			if($ticket_data['is_end_time'] == 0){
				if($ticket['is_end_time']!="")
					$ticket_data['end_time'] = to_timespan($ticket['end_time']);
				else
					$ticket_data['end_time'] = 0;
				
				$ticket_data['end_time_day'] = 0;
			}
			else{
				$ticket_data['end_time'] = 0;
				$ticket_data['end_time_day'] = intval($ticket['end_time_day']);
			}
			
			$ticket_data['is_delivery'] = intval($ticket['is_delivery']);
			$ticket_data['is_history'] = intval($ticket['is_history']);
			$ticket_data['paper_must'] = intval($ticket['paper_must']);
			$ticket_data['show_in_api'] = intval($ticket['show_in_api']);
			$ticket_data['is_effect'] = intval($ticket['is_effect']);
			$ticket_data['sort'] = intval($ticket['sort']);
			$ticket_data['is_divide']= intval($ticket['is_divide']);
			$ticket_data['pay_type']= intval($ticket['pay_type']);
			$ticket_data['order_status']= intval($ticket['order_status']);
			$ticket_data['origin_price']= format_price_to_db($ticket['origin_price']);
			$ticket_data['current_price']= format_price_to_db($ticket['current_price']);
			if($ticket_data['pay_type'] == 1)
				$ticket_data['sale_price']=$ticket_data['current_price'];
			elseif($ticket_data['pay_type'] == 2)
				$ticket_data['sale_price']= format_price_to_db($ticket['sale_price']);
			elseif($ticket_data['pay_type'] == 3)
				$ticket_data['sale_price'] = 0;
			$ticket_data['sale_virtual_total']= intval($ticket['sale_virtual_total']);
			$ticket_data['min_buy']= intval($ticket['min_buy']);
			$ticket_data['max_buy']= intval($ticket['max_buy']);
			$ticket_data['sale_max']= intval($ticket['sale_max']);
			$ticket_data['return_money']= format_price_to_db($ticket['return_money']);
			$ticket_data['return_score']= intval($ticket['return_score']);
			$ticket_data['return_exp']= intval($ticket['return_exp']);
			$ticket_data['voucher']= intval($ticket['voucher']);
			$ticket_data['is_review_return']= intval($ticket['is_review_return']);
			$ticket_data['review_return_money']= format_price_to_db($ticket['review_return_money']);
			$ticket_data['review_return_score']= intval($ticket['review_return_score']);
			$ticket_data['review_return_exp']= intval($ticket['review_return_exp']);
			$ticket_data['review_voucher']= intval($ticket['review_voucher']);
			$ticket_data['is_buy_return']= intval($ticket['is_buy_return']);
			$ticket_data['is_refund']= intval($ticket['is_refund']);
			$ticket_data['refund_desc']= strim($ticket['refund_desc']);
			$ticket_data['is_expire_refund']= intval($ticket['is_expire_refund']);
			$ticket_data['tuan_is_pre']=intval($ticket['tuan_is_pre']);			
			$ticket_data['is_tuan']=intval($ticket['is_tuan']);
			$ticket_data['tuan_cate']=intval($ticket['tuan_cate']);
			
			if(strim($ticket['tuan_begin_time'])!=""){
				$ticket_data['tuan_begin_time']=to_timespan($ticket['tuan_begin_time']);
			    
			}else{
				$ticket_data['tuan_begin_time'] = 0;
			}				
				
			if(strim($ticket['tuan_end_time'])!=""){
				$ticket_data['tuan_end_time']=to_timespan($ticket['tuan_end_time']);							
				if($ticket_data['tuan_end_time']<$ticket_data['tuan_begin_time']){
					showErr("团购结束时间必须晚于开始时间",$ajax);								
				}
			}							
			else{
				$ticket_data['tuan_end_time'] = 0;
			}
			
			$ticket_data['tuan_success_count']=intval($ticket['tuan_success_count']);

			
			$GLOBALS['db']->autoExecute(DB_PREFIX."ticket",$ticket_data,"UPDATE","id=".$ticket['id'],"SILENT");
    		
    		//如果是团购门票
			if($ticket_data['is_tuan']==1&&$ticket_data['is_effect']==1){
				
				$t_data['type'] = 2;
				$t_data['rel_id'] = $ticket['id'];
				$t_data['name'] = $ticket_data['name'];
				$t_data['brief'] = $ticket_data['name_brief'];
				$t_data['origin_price'] =  $ticket_data['origin_price'];
				$t_data['current_price'] =  $ticket_data['current_price'];
				$t_data['sale_price'] =  $ticket_data['sale_price'];
				$t_data['sale_total'] =  $ticket_data['sale_virtual_total'];
				
				$t_data['discount'] =  $t_data['current_price']/$t_data['origin_price'] * 100;
				$t_data['begin_time'] =  $ticket_data['tuan_begin_time'];
				$t_data['end_time'] =  $ticket_data['tuan_end_time'];
				$t_data['is_pre'] =  $ticket_data['tuan_is_pre'];
				$t_data['is_effect'] =  $ticket_data['is_effect'];
				$t_data['is_history'] =  $ticket_data['is_history'];
				$t_data['success_count'] =  $ticket_data['tuan_success_count'];
				$t_data['cate_id'] =  $ticket_data['tuan_cate'];
				
				$t_data['create_time'] =  NOW_TIME;
				//判断是否已经推送到团这个表
				
				if($tuan_id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."tuan where type=2 and rel_id=".$ticket['id']))
				{
					$GLOBALS['db']->query("UPDATE ".DB_PREFIX."ticket set tuan_id=$tuan_id WHERE id=".$ticket['id']);

					$GLOBALS['db']->autoExecute(DB_PREFIX."tuan",$t_data,"UPDATE","type=2 and rel_id=".$ticket['id'],"SILENT");
				}
				else{
					$GLOBALS['db']->autoExecute(DB_PREFIX."tuan",$t_data,"INSERT","","SILENT");
					$tuan_id = $GLOBALS['db']->insert_id();
					$GLOBALS['db']->query("UPDATE ".DB_PREFIX."ticket set tuan_id=$tuan_id WHERE id=".$ticket['id']);
				}
					
				
			}
			else{
				$GLOBALS['db']->getOne("DELETE  FROM ".DB_PREFIX."tuan where type=2 and rel_id=".$ticket['id']);
			}
			
			//删除返券
			$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."voucher_promote WHERE voucher_promote = 1 and voucher_rel_id=".intval($_REQUEST['id']));
			//购物返券
			if(intval($ticket_data['voucher']) > 0){
				$review_voucher['voucher_type_id'] = $ticket_data['voucher'];
				$review_voucher['voucher_promote'] = 1;
				$review_voucher['voucher_rel_id'] = intval($_REQUEST['id']);
				$review_voucher['voucher_promote_type'] = 1;
				$GLOBALS['db']->autoExecute(DB_PREFIX."voucher_promote",$review_voucher,"INSERT","","SILENT");
			}
			//评论返券
			if(intval($ticket_data['review_voucher']) > 0){
				$review_voucher['voucher_type_id'] = $ticket_data['review_voucher'];
				$review_voucher['voucher_promote'] = 1;
				$review_voucher['voucher_rel_id'] = intval($_REQUEST['id']);
				$review_voucher['voucher_promote_type'] = 2;
				$GLOBALS['db']->autoExecute(DB_PREFIX."voucher_promote",$review_voucher,"INSERT","","SILENT");
			}
    	}
    	$data = base64_encode(serialize($_REQUEST));
    	showSuccess($data,$ajax);
    }
    
}
?>