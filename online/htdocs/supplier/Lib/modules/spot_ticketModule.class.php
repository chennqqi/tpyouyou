<?php
class spot_ticketModule extends AuthModule{

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
    		$tuan_end_time =0;
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
    	
    	if(intval($_REQUEST['id']) > 0){
    		
    		$old_ticket = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ticket where supplier_id=".$this->supplier_id." and id=".intval($_REQUEST['id']));
    		
    		
    		$_REQUEST['sale_virtual_total'] = $old_ticket['sale_virtual_total'];
    		$_REQUEST['return_money'] = $old_ticket['return_money'];
    		$_REQUEST['return_score'] = $old_ticket['return_score'];
    		$_REQUEST['return_exp'] = $old_ticket['return_exp'];
    		$_REQUEST['show_in_api'] = $old_ticket['show_in_api'];
    		
    		
    		$_REQUEST['voucher'] = $GLOBALS['db']->getOne("SELECT voucher_type_id FROM ".DB_PREFIX."voucher_promote where voucher_promote_type =1 and voucher_promote = 1 and voucher_rel_id=".$old_ticket['id']);
    		$_REQUEST['is_review_return'] = $old_ticket['is_review_return'];
    		$_REQUEST['review_return_money'] = $old_ticket['review_return_money'];
    		$_REQUEST['review_return_score'] = $old_ticket['review_return_score'];
    		$_REQUEST['review_return_exp'] = $old_ticket['review_return_exp'];
    		$_REQUEST['review_voucher'] = $GLOBALS['db']->getOne("SELECT voucher_type_id FROM ".DB_PREFIX."voucher_promote where voucher_promote_type =2 and voucher_promote = 1 and voucher_rel_id=".$old_ticket['id']);
    		$_REQUEST['is_rebate'] = $old_ticket['is_rebate'];
    		$_REQUEST['is_buy_return']  = $old_ticket['is_rebate'];
    		
    		$_REQUEST['tuan_is_pre']  = $old_ticket['tuan_is_pre'];
    		$_REQUEST['sort']  = $old_ticket['tuan_is_pre'];
    	}
    	else{
    		$_REQUEST['sale_virtual_total'] = 0;
    		$_REQUEST['return_money'] = 0;
    		$_REQUEST['return_score'] = 0;
    		$_REQUEST['return_exp'] = 0;
    		
    		$_REQUEST['voucher'] = 0;
    		$_REQUEST['is_review_return'] = 0;
    		$_REQUEST['review_return_money'] = 0;
    		$_REQUEST['review_return_score'] = 0;
    		$_REQUEST['review_return_exp'] = 0;
    		$_REQUEST['review_voucher'] = 0;
    		$_REQUEST['is_rebate'] = 0;
    		$_REQUEST['is_buy_return']  = 0;
    		$_REQUEST['tuan_is_pre']  = 0;
    		$_REQUEST['show_in_api'] = 0;
    		$_REQUEST['sort']  = 0;
    	}
    	
    	$data = base64_encode(serialize($_REQUEST));
    	showSuccess($data,$ajax);
    }
    
}
?>