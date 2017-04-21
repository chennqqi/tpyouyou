<?php
class tourline_itemModule {

    function add() {
    	$NOW_DATE = to_date(NOW_TIME,"Y-m-d");
    	$TOMORROW_DATE = to_date(to_timespan("+1 day"),"Y-m-d");
    	$GLOBALS['tmpl']->assign("NOW_DATE",$NOW_DATE);
    	$GLOBALS['tmpl']->assign("TOMORROW_DATE",$TOMORROW_DATE);
    	
    	$tourline_id=intval($_REQUEST['tourline_id']);//线路id
    	$GLOBALS['tmpl']->assign("tourline_id",$tourline_id);
    	
    	$is_supplier_submit=intval($_REQUEST['is_supplier_submit']);//１：商家提交线路审核，０：不是，是网站后台线路操作;
    	$GLOBALS['tmpl']->assign("is_supplier_submit",$is_supplier_submit);
    	
    	$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_item#insert",array("ajax"=>1)));
    	$GLOBALS['tmpl']->display("core/tourline_item/add.html");
    }
    
    function insert(){
    	$ajax = intval($_REQUEST['ajax']);
    	$is_supplier_submit = intval($_REQUEST['is_supplier_submit']);//１：商家提交线路审核，０：不是，是网站后台线路操作;
    	if(strim($_REQUEST['start_time'])=='' && intval($_REQUEST['is_forever']) !=1){
    		showErr("请输入时间",$ajax);
    	}
		
    	$tourline_id=intval($_REQUEST['tourline_id']);
    	$is_forever=intval($_REQUEST['is_forever']);
    	$start_time=strim($_REQUEST['start_time']);
    	$end_time=strim($_REQUEST['end_time']);
    	$start_timespan=to_timespan($start_time);
    	$end_timespan=to_timespan($end_time);
    	if($end_timespan >0)
    	{
    		if($is_forever == 1)
	    	 	showErr(lang("BATCH_ADD_DAY_NOTIICE"),$ajax);//批量不能增加永久的发团信息
	    	 	
    		if($start_timespan > $end_timespan)
	    	 	showErr(lang("TOURLINE_ITEM_END_NOTICE"),$ajax);//开始时间不能大于结束时间
			
	    	$difftime =$end_timespan-$start_timespan;
	    	$day_num=$difftime/(60*60*24)+1;
	    	if( $day_num >63)
	    		showErr(lang("DAY_NUM_NOTIICE"),$ajax);//批量增加两个月
    	}
    	else
    	{
    		$day_num=1;
    	}
    	
    	$adult_price=floatval($_REQUEST['adult_price']);
    	$adult_sale_price=floatval($_REQUEST['adult_sale_price']);
    	$child_price=floatval($_REQUEST['child_price']);
    	$child_sale_price=floatval($_REQUEST['child_sale_price']);
    	$adult_limit=intval($_REQUEST['adult_limit']);
    	$adult_buy_min=intval($_REQUEST['adult_buy_min']);
    	$adult_buy_max=intval($_REQUEST['adult_buy_max']);
    	$child_limit=intval($_REQUEST['child_limit']);
    	$child_buy_min=intval($_REQUEST['child_buy_min']);
    	$child_buy_max=intval($_REQUEST['child_buy_max']);
    	$brief=strim($_REQUEST['brief']);
    	
    	if($is_forever == 1 )
    	{
    		if($tourline_id >0 && !$is_supplier_submit)
    		{
    			$tourline_item_forever=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tourline_item where tourline_id=".$tourline_id." and is_forever =1");
    			if($tourline_item_forever >0)
    				showErr(lang("IS_FOREVER_NOTICE_ONE"),$ajax);//永久只能增加一条
    		}
    		$start_timespan=to_timespan("1970-01-01");
    	}
    	if($tourline_id >0 && !$is_supplier_submit)
    	{
    		$tourline_item_start_time=$GLOBALS['db']->getOne("select Group_concat(start_time) from ".DB_PREFIX."tourline_item where tourline_id=".$tourline_id."");
    		$tourline_item_start_time_array=explode(',',$tourline_item_start_time);
    	}
    	$data=array();
    	for($i=0;$i<$day_num;$i++)
    	{
    		$for_start_timespan=$start_timespan + 60*60*24*$i;//当不是批理增加时，for循环一次，60*60*24*$i＝0
    		$for_start_time=to_date($for_start_timespan,"Y-m-d");
    		
    		if(in_array($for_start_time,$tourline_item_start_time_array) && !$is_supplier_submit)
    			showErr("有相同的出发时间:".$for_start_time,$ajax);
    		
    		$data[$i]['start_time']=$for_start_time;
    		$data[$i]['is_forever']=$is_forever;
    		$data[$i]['adult_price']=$adult_price;
    		$data[$i]['adult_sale_price']=$adult_sale_price;
    		$data[$i]['child_price']=$child_price;
    		$data[$i]['child_sale_price']=$child_sale_price;
    		$data[$i]['adult_limit']=$adult_limit;
    		$data[$i]['adult_buy_min']=$adult_buy_min;
    		$data[$i]['adult_buy_max']=$adult_buy_max;
    		$data[$i]['child_limit']=$child_limit;
    		$data[$i]['adult_buy_min']=$adult_buy_min;
    		$data[$i]['child_buy_max']=$child_buy_max;
    		$data[$i]['brief']=$brief;
    		$data[$i]['ser']=serialize($data[$i]);
    	}
    	
    	showSuccess($data,$ajax);
    }
    
    function edit(){
    	$tourline_items = unserialize(urldecode($_POST['tourline_items']));
    	
    	$NOW_DATE = to_date(NOW_TIME,"Y-m-d");
    	$GLOBALS['tmpl']->assign("NOW_DATE",$NOW_DATE);
    	
    	$tourline_id=intval($_REQUEST['tourline_id']);//线路id;
    	$GLOBALS['tmpl']->assign("tourline_id",$tourline_id);
    	
    	$is_supplier_submit=intval($_REQUEST['is_supplier_submit']);//１：商家提交线路审核，０：不是，是网站后台线路操作;
    	$GLOBALS['tmpl']->assign("is_supplier_submit",$is_supplier_submit);
    	
    	$GLOBALS['tmpl']->assign("tourline_items",$tourline_items);
    	
    	$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_item#update",array("ajax"=>1)));
    	$GLOBALS['tmpl']->display("core/tourline_item/edit.html");
    }
    
    function update(){
    	$ajax = intval($_REQUEST['ajax']);
    	$is_supplier_submit = intval($_REQUEST['is_supplier_submit']);//１：商家提交线路审核，０：不是，是网站后台线路操作;
    	if(strim($_REQUEST['start_time'])=='' && intval($_REQUEST['is_forever']) !=1){
    		showErr("请输入时间",$ajax);
    	}
    	
    	if(strim($_REQUEST['start_time'])=='1970-01-01' && intval($_REQUEST['is_forever']) !=1 )
    	{
    		showErr("非永久有效出游信息，出发时间不能是：1970-01-01",$ajax);
    	}
    	
		$start_time_1970=to_date(to_timespan('1970-01-01'),"Y-m-d");
    	if(intval($_REQUEST['id']) > 0 && !$is_supplier_submit){
    		$tourline_item = $_POST;
    		$tourline_id=intval($tourline_item['tourline_id']);
    		$tourline_item_forever=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tourline_item where tourline_id=".$tourline_id." and is_forever =1 and id <> ".intval($tourline_item['id'])."");
    		
    		if($tourline_item_forever >0 && intval($_REQUEST['is_forever']) ==1)
    				showErr(lang("IS_FOREVER_NOTICE_ONE"),$ajax);
    		
    		$data['is_forever']=intval($tourline_item['is_forever']);
			if($data['is_forever'] == 1)
				$data['start_time']=$start_time_1970;
			else	
				$data['start_time'] =to_date(to_timespan(trim($tourline_item['start_time'])),"Y-m-d");
    		
			$data['adult_price']= format_price_to_db($tourline_item['adult_price']);
			$data['adult_sale_price']= format_price_to_db($tourline_item['adult_sale_price']);
			$data['child_price']= format_price_to_db($tourline_item['child_price']);
			$data['child_sale_price']= format_price_to_db($tourline_item['child_sale_price']);
			$data['adult_limit'] = intval($tourline_item['adult_limit']);
			$data['adult_buy_min'] = intval($tourline_item['adult_buy_min']);
			$data['adult_buy_max'] = intval($tourline_item['adult_buy_max']);
			$data['child_limit'] = intval($tourline_item['child_limit']);
			$data['child_buy_max'] = intval($tourline_item['child_buy_max']);
			$data['child_buy_min'] = intval($tourline_item['child_buy_min']);
			$data['brief'] = trim($tourline_item['brief']);
			
			$GLOBALS['db']->autoExecute(DB_PREFIX."tourline_item",$data,"UPDATE","id=".intval($tourline_item['id']),"SILENT");
			if($GLOBALS['db']->error()!="")
			{
				showErr("更新失败，已有相同出发时间",1);
			}

    	}
		if(intval($_REQUEST['is_forever']) ==1)
			$_REQUEST['start_time']=$start_time_1970;
			
    	$data = serialize($_REQUEST);
    	showSuccess($data,$ajax);
    }
    
}
?>