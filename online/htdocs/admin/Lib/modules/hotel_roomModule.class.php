<?php
class hotel_roomModule extends AuthModule{
    
    function add() {
    	$NOW_DATE = to_date(NOW_TIME,"Y-m-d");
    	$GLOBALS['tmpl']->assign("NOW_DATE",$NOW_DATE);
    	
    	// $tuan_cates = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."tuan_cate ORDER BY sort DESC");
    	// $GLOBALS['tmpl']->assign("tuan_cates",$tuan_cates);
    	
    	$GLOBALS['tmpl']->assign("formaction",admin_url("hotel_room#insert",array("ajax"=>1)));
    	$GLOBALS['tmpl']->display("core/hotel_room/add.html");
    }
    
    function insert(){
    	$ajax = intval($_REQUEST['ajax']);
    	if(intval($_REQUEST['is_end_time'])==1 && intval($_REQUEST['end_time_day']) <=0){
    		showErr("过期时间天数必须大于0",$ajax);
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
    	
    	$GLOBALS['tmpl']->assign("formaction",admin_url("hotel_room#update",array("ajax"=>1)));
    	$GLOBALS['tmpl']->display("core/hotel_room/edit.html");
    }
    
    function update(){
    	$ajax = intval($_REQUEST['ajax']);
    	if(intval($_REQUEST['is_end_time'])==1 && intval($_REQUEST['end_time_day']) <=0){
    		showErr("过期时间天数必须大于0",$ajax);
    	}
    	$data = base64_encode(serialize($_REQUEST));
    	// showSuccess($_REQUEST,$ajax);
        showSuccess($data,$ajax);
    }
    
}
?>