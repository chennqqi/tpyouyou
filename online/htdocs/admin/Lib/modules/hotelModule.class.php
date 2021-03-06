<?php

class hotelModule extends AuthModule
{
  function index() {
    $param = array();		
		//条件
		$condition = " 1 = 1 ";
		if(isset($_REQUEST['name']))
			$name_key = strim($_REQUEST['name']);
		else
			$name_key = "";
		$param['name'] = $name_key;
		if($name_key!='')
		{
			$condition.=" and name = '".$name_key."' ";
		}

		//分页
		if(isset($_REQUEST['numPerPage']))
		{			
			$param['pageSize'] = intval($_REQUEST['numPerPage']);
			if($param['pageSize'] <=0||$param['pageSize'] >200)
				$param['pageSize'] = ADMIN_PAGE_SIZE;
		}
		else
			$param['pageSize'] = ADMIN_PAGE_SIZE;
			
		if(isset($_REQUEST['pageNum']))
			$page = intval($_REQUEST['pageNum']);
		else
			$page = 0;
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$param['pageSize']).",".$param['pageSize'];
		$param['pageNum'] = $page;
		
		//排序
		if(isset($_REQUEST['orderField']))
			$param['orderField'] = strim($_REQUEST['orderField']);
		else
			$param['orderField'] = "id";
		
		if(isset($_REQUEST['orderDirection']))
			$param['orderDirection'] = strim($_REQUEST['orderDirection'])=="asc"?"asc":"desc";
		else
			$param['orderDirection'] = "desc";
		
		$totalCount = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."spot where ".$condition);
		$supplier_ids =  array();

		$totalCount = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."spot where ".$condition);
		if($totalCount){
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."hotel where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
			foreach($list as $k=>$v){
				$list[$k]['company_name'] = $GLOBALS['db']->getOne("select company_name from ".DB_PREFIX."supplier where id = ".$v['supplier_id']);
			}
		}
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$spot_cate = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."hotel_cate ORDER BY sort DESC,id ASC");
		$GLOBALS['tmpl']->assign('spot_cate',$spot_cate);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("hotel"));
		$GLOBALS['tmpl']->assign("setsorturl",admin_url("hotel#set_sort",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("delurl",admin_url("hotel#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("hotel#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("hotel#add"));
		$GLOBALS['tmpl']->assign("previewurl",url("hotel#view"));
		
		$GLOBALS['tmpl']->assign("verifycodelisturl",admin_url("hotel#verify_code_list"));
		$GLOBALS['tmpl']->assign("statisticsurl",admin_url("hotel#statistics"));
		
		$GLOBALS['tmpl']->display("core/hotel/index.html");
    }
    
   public function foreverdelete()
	 {		
			$ajax = intval($_REQUEST['ajax']);		
			if (isset ( $_REQUEST ['id'] ))
			{
				$id = strim($_REQUEST ['id']);			
				$id = format_ids_str($id);
				if($id)
				{					
					$del_name = $GLOBALS['db']->getOne("select group_concat(name) from ".DB_PREFIX."hotel where id in (".$id.")");			
					$sql = "delete from ".DB_PREFIX."hotel where id in (".$id.")";
					$GLOBALS['db']->query($sql);				
					if($GLOBALS['db']->affected_rows()>0)
					{	
						//删除门票
						$sql = "delete from ".DB_PREFIX."hotel_room where hotel_id in (".$id.")";
						$GLOBALS['db']->query($sql);
						//删除图像
						$sql = "delete from ".DB_PREFIX."hotel_image where hotel_id in (".$id.")";
						$GLOBALS['db']->query($sql);
						
						save_log(lang("DEL").":".$del_name, 1);
					}
					showSuccess(lang("FOREVER_DELETE_SUCCESS"),$ajax);				
				}
				else
				{
					save_log(lang("DEL")."ID:".strim($_REQUEST ['id']), 0);
					showErr(lang("INVALID_OPERATION"),$ajax);
				}			
			}
			else
			{
				showErr(lang("INVALID_OPERATION"),$ajax);
			}
	 }
	
	public function add()
	{	
		$sort = $GLOBALS['db']->getOne("select max(sort) from ".DB_PREFIX."spot")+1;	
		$GLOBALS['tmpl']->assign("sort",$sort);
		$GLOBALS['tmpl']->assign("searchcityurl",admin_url("tour_city#search_city"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchareaurl",admin_url("tour_area#search_area"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchtagurl",admin_url("tour_place_tag#search_tag"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchcateurl",admin_url("hotel_cate#search_cate"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchplaceurl",admin_url("tour_place#search_place"),array("ajax"=>1));
    	$GLOBALS['tmpl']->assign("searchsupplierurl",admin_url("supplier#search_supplier",array("ajax"=>1)));
    	
		$GLOBALS['tmpl']->assign("addtickets",admin_url("hotel_room#add"),array("ajax"=>1));
    	$GLOBALS['tmpl']->assign("edittickets",admin_url("hotel_room#edit",array("ajax"=>1)));
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("hotel#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/hotel/add.html");
	}
	
	public function insert() {
		$ajax = intval($_REQUEST['ajax']);
		if(!check_empty("name"))
		{
			showErr(lang("SPOT_CATE_NAME_EMPTY"),$ajax);
		}
		if(!check_empty("supplier_id") || intval($_REQUEST['supplier_id']) == 0 )
		{
			showErr("请选择商家",$ajax);
		}
		if(!check_empty("tour_city_name"))
		{
			showErr("请选择城市",$ajax);
		}
		if(!check_empty("description"))
		{
			showErr("请输入内容",$ajax);
		}
		if(!check_empty("tour_cate_cate_name"))
		{
			showErr("请选择分类",$ajax);
		}
		if(!check_empty("xpoint") || !check_empty("ypoint") )
		{
			showErr("请定位地图",$ajax);
		}
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['supplier_id'] = intval($_REQUEST['supplier_id']);
		if(isset($_REQUEST['spot_img'])){
			$data['image'] = format_domain_to_relative(strim($_REQUEST['spot_img'][0]));
		}
		else{
			$data['image'] ="";
		}
		
		$data['city_match'] = format_fulltext_key(strim($_REQUEST['tour_city_py']));
		$data['city_match_row'] = strim($_REQUEST['tour_city_name']);

		$data['cate_match'] = str_to_unicode_string_depart(strim($_REQUEST['tour_cate_cate_name']));
		$data['cate_match_row'] = strim($_REQUEST['tour_cate_cate_name']);
	
		$data['star_level'] = intval($_REQUEST["star_level"]);
		
		$data['brief'] = strim($_REQUEST["brief"]);
		$data['appointment_desc'] = format_domain_to_relative(btrim($_REQUEST["appointment_desc"]));
		$data['description'] = format_domain_to_relative(btrim($_REQUEST['description']));
		
		$data['address'] = strim($_REQUEST['address']);
		$data['x_point'] = strim($_REQUEST['xpoint']);
		$data['y_point'] = strim($_REQUEST['ypoint']);
		$data['tel'] = strim($_REQUEST['tel']);
		$data['sort'] = intval($_REQUEST['sort']);
		
		// 更新数据

		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."hotel",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			$hotel_id = $GLOBALS['db']->insert_id();
			//图册
			if(isset($_REQUEST['spot_img'])){
				foreach($_REQUEST['spot_img'] as $k=>$v){
					if($v!=''){
						$spot_image_data = array();
						$spot_image_data['image'] = format_domain_to_relative(strim($v));
						$spot_image_data['hotel_id'] = $hotel_id;
						$spot_image_data['sort'] = $k;
						$GLOBALS['db']->autoExecute(DB_PREFIX."hotel_image",$spot_image_data,"INSERT","","SILENT");
					}
				}
			}
			//门票
			if(isset($_REQUEST['tickets'])){
				foreach($_REQUEST['tickets'] as $k=>$v){
					$ticket = unserialize(base64_decode($v));
					if($ticket['name']!=""){
						$ticket_data = array();
						$t_data = array();
						$ticket_data['hotel_id'] = $hotel_id;
						$ticket_data['name'] = strim($ticket['name']);
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
						$ticket_data['supplier_id']= $data['supplier_id'];
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
						
						$ticket_data['is_tuan']=intval($ticket['is_tuan']);
						
						$ticket_data['tuan_cate']=intval($ticket['tuan_cate']);
							
						$ticket_data['tuan_success_count']=intval($ticket['tuan_success_count']);
						$ticket_data['tuan_is_pre']=intval($ticket['tuan_is_pre']);
						
						if(intval($ticket['id']) >0)
							$GLOBALS['db']->autoExecute(DB_PREFIX."hotel_room",$ticket_data,"UPDATE","id=".$ticket['id'],"SILENT");
						else
							$GLOBALS['db']->autoExecute(DB_PREFIX."hotel_room",$ticket_data,"INSERT","","SILENT");
						
						if($GLOBALS['db']->error()==""){
							
							if(intval($ticket['id']) >0)
								$ticket_id = intval($ticket['id']);
							else
								$ticket_id  = $GLOBALS['db']->insert_id();
															
							save_log($log_info."，酒店房型：".$ticket_data['name'].lang("INSERT_SUCCESS"),1);
						}
					}
				}
			}
			
			require APP_ROOT_PATH."system/libs/hotel.php";
			//更新门票冗余信息
			update_spot_ticket($hotel_id);
			
			//成功提示
			save_log($log_info.lang("INSERT_SUCCESS"),1);
			showSuccess(lang("INSERT_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	
	}
		
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."hotel where id = ".$id);
		
		$vo['city_match'] = unformat_fulltext_key($vo['city_match']);
		
		//商家
		$vo['company_name'] = $GLOBALS['db']->getOne("select `company_name` from ".DB_PREFIX."supplier where id = ".$vo['supplier_id']);
		
		$GLOBALS['tmpl']->assign ( 'vo', $vo );
		
		//相册
		$timage_list = $GLOBALS['db']->getAll("select `image` from ".DB_PREFIX."hotel_image where hotel_id = ".$vo['id']." ORDER BY sort ASC");
		foreach($timage_list as $k=>$v){
			$image_list[] = $v['image'];
		}
		$GLOBALS['tmpl']->assign ( 'image_list', $image_list );
		//门票
		$tickets = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."hotel_room where hotel_id = ".$vo['id']." ORDER BY sort DESC ");
		$tickets_ids = array();
		foreach($tickets as $k=>$v){
			$tickets[$k]['origin_price'] = format_price_to_display($v['origin_price']);
			$tickets[$k]['current_price'] = format_price_to_display($v['current_price']);
			$tickets[$k]['sale_price'] = format_price_to_display($v['sale_price']);
			$tickets[$k]['is_breakfast'] = intval($v['is_breakfast']);
			if($v['end_time']!=0){
				$tickets[$k]['end_time'] = to_date($v['end_time'],"Y-m-d");
			}
			else{
				$tickets[$k]['end_time'] = "";
			}
			
			$tickets[$k]['return_money'] = format_price_to_display($v['return_money']);
			$tickets[$k]['review_return_money'] = format_price_to_display($v['review_return_money']);
			
			$tickets_ids[] = $v['id'];
		}

		foreach($tickets as $k=>$v){
			$tickets[$k]['ticket_data'] = base64_encode(serialize($tickets[$k]));
		}

		$GLOBALS['tmpl']->assign ( 'tickets', $tickets );
		
		$GLOBALS['tmpl']->assign("searchcityurl",admin_url("tour_city#search_city"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchareaurl",admin_url("tour_area#search_area"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchtagurl",admin_url("tour_place_tag#search_tag"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchcateurl",admin_url("hotel_cate#search_cate"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchplaceurl",admin_url("tour_place#search_place"),array("ajax"=>1));
    $GLOBALS['tmpl']->assign("searchsupplierurl",admin_url("supplier#search_supplier",array("ajax"=>1)));
    	
		$GLOBALS['tmpl']->assign("addtickets",admin_url("hotel_room#add"),array("ajax"=>1));
    	$GLOBALS['tmpl']->assign("edittickets",admin_url("hotel_room#edit",array("ajax"=>1)));
    	$GLOBALS['tmpl']->assign("previewurl",url("hotel#view",array("id"=>$id)));
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("hotel#update",array("ajax"=>1)));
		
		$GLOBALS['tmpl']->display("core/hotel/edit.html");
	}

	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		if(intval($_REQUEST['id']) == 0){
			showErr("编辑数据出错",$ajax);
		}
		if(!check_empty("name"))
		{
			showErr(lang("SPOT_CATE_NAME_EMPTY"),$ajax);
		}
		if(!check_empty("supplier_id") || intval($_REQUEST['supplier_id']) == 0 )
		{
			showErr("请选择商家",$ajax);
		}
		if(!check_empty("tour_city_name"))
		{
			showErr("请选择城市",$ajax);
		}
		if(!check_empty("description"))
		{
			showErr("请输入内容",$ajax);
		}
		if(!check_empty("xpoint") || !check_empty("ypoint") )
		{
			showErr("请定位地图",$ajax);
		}
		if(!check_empty("tour_cate_cate_name"))
		{
			showErr("请选择分类",$ajax);
		}
	
		$data = array();
		$hotel_id = intval($_REQUEST['id']);
		$data['name'] = strim($_REQUEST['name']);
		$data['supplier_id'] = intval($_REQUEST['supplier_id']);
		if(isset($_REQUEST['spot_img'])){
			$data['image'] = format_domain_to_relative(strim($_REQUEST['spot_img'][0]));
		}
		else{
			$data['image'] ="";
		}
		
		$data['star_level'] = intval($_REQUEST["star_level"]);
	
		$data['brief'] = strim($_REQUEST["brief"]);

		$data['cate_match'] = str_to_unicode_string_depart(strim($_REQUEST['tour_cate_cate_name']));
		$data['cate_match_row'] = strim($_REQUEST['tour_cate_cate_name']);

		$data['tel'] = strim($_REQUEST["tel"]);
		$data['appointment_desc'] = format_domain_to_relative(btrim($_REQUEST["appointment_desc"]));
		$data['description'] = format_domain_to_relative(btrim($_REQUEST['description']));
		
		$data['address'] = strim($_REQUEST['address']);
		$data['x_point'] = strim($_REQUEST['xpoint']);
		$data['y_point'] = strim($_REQUEST['ypoint']);
		$data['sort'] = intval($_REQUEST['sort']);
				
		// 更新数据
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."hotel",$data,"UPDATE","id=".$hotel_id,"SILENT");
		if ($GLOBALS['db']->error()=="") {
			//删除旧图库
			$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."hotel_image WHERE hotel_id=".$hotel_id);
			//图册
			if(isset($_REQUEST['spot_img'])){
				foreach($_REQUEST['spot_img'] as $k=>$v){
					if($v!=''){
						$spot_image_data = array();
						$spot_image_data['image'] = format_domain_to_relative(strim($v));
						$spot_image_data['hotel_id'] = $hotel_id;
						$spot_image_data['sort'] = $k;
						$GLOBALS['db']->autoExecute(DB_PREFIX."hotel_image",$spot_image_data,"INSERT","","SILENT");
					}
				}
			}
			
			// //删除无关房型
			$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."hotel_room WHERE hotel_id=".$hotel_id);
			
			//添加门票
			$love = 1;
			if(isset($_REQUEST['tickets'])){	
				foreach($_REQUEST['tickets'] as $k=>$v){
					$ticket = unserialize(base64_decode($v));
					$love = intval($ticket['id']);
					if($ticket['name']!=""){
						$ticket_data = array();
						$t_data = array();
						
						$ticket_data['hotel_id'] = $hotel_id;
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
						
						$ticket_data['is_effect'] = intval($ticket['is_effect']);
						$ticket_data['is_history'] = intval($ticket['is_history']);
						$ticket_data['sort'] = intval($ticket['sort']);
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
						$ticket_data['supplier_id']= $data['supplier_id'];
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
						$ticket_data['is_breakfast'] = intval($ticket['is_breakfast']);
							
						$ticket_data['tuan_success_count']=intval($ticket['tuan_success_count']);

						$GLOBALS['db']->autoExecute(DB_PREFIX."hotel_room",$ticket_data,"INSERT","","SILENT");

						// if (intval($ticket['id']) ==0) {
						// 	$GLOBALS['db']->autoExecute(DB_PREFIX."hotel_room",$ticket_data,"INSERT","","SILENT");
						// } else {
						//   $GLOBALS['db']->autoExecute(DB_PREFIX."hotel_room",$ticket_data,"UPDATE","id=".intval($ticket['id']),"SILENT");
						// }
						
						if($GLOBALS['db']->error()==""){
							$ticket_id  = $GLOBALS['db']->insert_id();
						}
					}
				}
			}
			
			require APP_ROOT_PATH."system/libs/hotel.php";
			//更新门票冗余信息
			update_spot_ticket($hotel_id);
					
			//成功提示
			save_log($log_info.lang("UPDATE_SUCCESS"),1);
			showSuccess(lang("UPDATE_SUCCESS"),$ajax,admin_url("hotel#edit",array("id"=>$hotel_id)));
			// showSuccess(unserialize(base64_decode($_REQUEST['tickets']['0'])),$ajax,admin_url("hotel#edit",array("id"=>$hotel_id)));
			// showSuccess($love,$ajax,admin_url("hotel#edit",array("id"=>$hotel_id)));
		} else {
			//错误提示
			showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}
	}
	
	public function set_sort()
	{
		$ajax = intval($_REQUEST['ajax']);
		$sort = intval($_REQUEST['sort']);
		$id = intval($_REQUEST['id']);
		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."spot where id = ".$id);
		if($data)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."spot set sort = ".$sort." where id = ".$id);
			if($GLOBALS['db']->error()!="")
			{
				showErr($data['sort'],$ajax);
			}
			else
			{
				save_log($data['name'].lang("UPDATE_SUCCESS"),1);
				showSuccess($sort,$ajax);
			}
		}
		else
		{
			showErr(0,$ajax);
		}
	}
	
	function verify_code_list() {
		$param = array();
		//条件
		$condition = " t.pay_status = 1 ";
	
		//订单号
		if(isset($_REQUEST['sn']))
			$sn = strim($_REQUEST['sn']);
		else
			$sn = "";
		$param['sn'] = $sn;
		if($sn!='')
		{
			$condition.=" and t.sn = '".$sn."' ";
		}
	
	
	
		//景点ID
		if(isset($_REQUEST['spot_id']))
			$spot_id = strim($_REQUEST['spot_id']);
		else
			$spot_id = "";
		$param['spot_id'] = $spot_id;
		if($spot_id!='' && intval($spot_id) > 0)
		{
			$condition.=" and t.spot_id = ".intval($spot_id)." ";
		}
	
	
	
		//预定人姓名
		if(isset($_REQUEST['appoint_name']))
			$appoint_name = strim($_REQUEST['appoint_name']);
		else
			$appoint_name = "";
		$param['appoint_name'] = $appoint_name;
		if($appoint_name!='')
		{
			$condition.=" and t.appoint_name = '".$appoint_name."' ";
		}
	
		//预定人手机
		if(isset($_REQUEST['appoint_mobile']))
			$appoint_mobile = strim($_REQUEST['appoint_mobile']);
		else
			$appoint_mobile = "";
		$param['appoint_mobile'] = $appoint_mobile;
		if($appoint_mobile!='')
		{
			$condition.=" and t.appoint_mobile = '".$appoint_mobile."' ";
		}
	
		//验证码
		if(isset($_REQUEST['verify_code']))
			$verify_code = strim($_REQUEST['verify_code']);
		else
			$verify_code = "";
		$param['verify_code'] = $verify_code;
		if($verify_code!='')
		{
			$condition.=" and t.verify_code = '".$verify_code."' ";
		}
	
		//退款状态
		$refund_status = -1;
		if(isset($_REQUEST['refund_status']) && strim($_REQUEST['refund_status'])!="")
			$refund_status = intval($_REQUEST['refund_status']);
	
		$param['refund_status'] = $refund_status;
		if($refund_status !=-1)
		{
			$condition .=" and t.refund_status=$refund_status ";
		}
	
		//订单状态
		$order_status = 0;
		if(isset($_REQUEST['order_status']) && strim($_REQUEST['order_status'])!="")
			$order_status = intval($_REQUEST['order_status']);
	
		$param['order_status'] = $order_status;
		if($order_status !=0)
		{
			$condition .=" and t.order_status=$order_status ";
		}
	
		//是否验证
		$is_verify = intval($_REQUEST['is_verify']);
		if ($is_verify == 1){
			$condition .=" and a.verify_time=0";
		}else if ($is_verify == 2){
			$condition .=" and a.verify_time>0";
		}
		$param['is_verify'] = $is_verify;
	
		//是否有效
		$is_verify_code_invalid = -1;
		if(isset($_REQUEST['is_verify_code_invalid']) && strim($_REQUEST['is_verify_code_invalid'])!="")
			$is_verify_code_invalid = intval($_REQUEST['is_verify_code_invalid']);
	
		$param['is_verify_code_invalid'] = $is_verify_code_invalid;
		if($is_verify_code_invalid !=-1)
		{
			$condition .=" and a.is_verify_code_invalid=$is_verify_code_invalid ";
		}
	
		//print_r($_REQUEST);
		//echo $is_verify_code_invalid;
	
		//会员ID
		if(isset($_REQUEST['user_id']))
			$user_id = strim($_REQUEST['user_id']);
		else
			$user_id = "";
		$param['user_id'] = $user_id;
		if($user_id!='' && intval($user_id) > 0)
		{
			$condition.=" and t.user_id = ".intval($user_id)." ";
		}
	
		//下单时间
		$create_time_begin  = strim($_REQUEST['create_time_begin']);
		$param['create_time_begin'] = $create_time_begin;
	
		$create_time_end  = strim($_REQUEST['create_time_end']);
		$param['create_time_end'] = $create_time_end;
	
		if(!empty($create_time_begin) && !empty($create_time_end))
		{
			$condition.=" and t.create_time >= '".to_timespan($create_time_begin)."' and t.create_time <='". (to_timespan($create_time_end) + 3600 * 24 - 1)."' ";
		}
	
		//预约时间
		$appoint_time_begin  = strim($_REQUEST['appoint_time_begin']);
		$param['appoint_time_begin'] = $appoint_time_begin;
	
		$appoint_time_end  = strim($_REQUEST['appoint_time_end']);
		$param['appoint_time_end'] = $appoint_time_end;
	
		if(!empty($appoint_time_begin) && !empty($appoint_time_end))
		{
			$condition.=" and a.appoint_time >= '".$appoint_time_begin."' and a.appoint_time <='". $appoint_time_end."' ";
		}
	
		//分页
		if(isset($_REQUEST['numPerPage']))
		{
			$param['pageSize'] = intval($_REQUEST['numPerPage']);
			if($param['pageSize'] <=0||$param['pageSize'] >200)
				$param['pageSize'] = ADMIN_PAGE_SIZE;
		}
		else
			$param['pageSize'] = ADMIN_PAGE_SIZE;
			
		if(isset($_REQUEST['pageNum']))
			$page = intval($_REQUEST['pageNum']);
		else
			$page = 0;
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$param['pageSize']).",".$param['pageSize'];
		$param['pageNum'] = $page;
	
	
		//排序
		if(isset($_REQUEST['orderField']))
			$param['orderField'] = strim($_REQUEST['orderField']);
		else
			$param['orderField'] = "t.id";
	
		if(isset($_REQUEST['orderDirection']))
			$param['orderDirection'] = strim($_REQUEST['orderDirection'])=="asc"?"asc":"desc";
		else
			$param['orderDirection'] = "desc";
	
	
		$sql = "
				select count(a.id) from ".DB_PREFIX."ticket_order_item a
LEFT JOIN ".DB_PREFIX."ticket_order t on t.id = a.order_id
LEFT JOIN ".DB_PREFIX."user u on u.id = a.user_id
where ".$condition;
	
	
		$totalCount = $GLOBALS['db']->getOne($sql);
		if($totalCount > 0){
			//$sql = "select t.*,u.user_name,u.mobile,s.user_name as supplier_name  from ".DB_PREFIX."tourline_order t left outer join ".DB_PREFIX."user u on u.id = t.user_id left outer join ".DB_PREFIX."supplier s on s.id = t.supplier_id where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit;
			$sql = "
					select a.*,u.user_name, t.create_time, t.appoint_name,t.appoint_mobile,t.sale_count, t.total_price, t.pay_amount,t.pay_status,t.pay_time,t.order_status,t.order_memo from ".DB_PREFIX."ticket_order_item a
					LEFT JOIN ".DB_PREFIX."ticket_order t on t.id = a.order_id
					LEFT JOIN ".DB_PREFIX."user u on u.id = a.user_id
					where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit;
			//echo $sql;
			//die();
			$list = $GLOBALS['db']->getAll($sql);
	
			require_once APP_ROOT_PATH."system/libs/spot.php";
	
			foreach($list as $k=>$v)
			{
				//tourline_order_format($list[$k]);
				ticket_order_item_format($list[$k]);
	
				//是否为团体票 1:个人票 0团体票
				if ($list[$k]['is_divide'] == 1){
					$list[$k]['is_divide_format'] = '个人票';
				}else{
					$list[$k]['is_divide_format'] = '团体票['.$list[$k]['sale_count'].']';
				}
	
				$list[$k]['create_time_format'] = to_date($list[$k]['create_time']);//下单时间
	
				//订单状态(流程)1.新订单 2.已确认 3.已完成 4.作废\r\n新订单：未确认（包含已付款）的都表示为新订单\r\n已确认：表示为商家或管理员查看，确认手动修改\r\n新订单、已确认均可申请退款，否则不可',
				if ($list[$k]['order_status'] == 1){
					$list[$k]['order_status_format'] = '新订单';
				}else if ($list[$k]['order_status'] == 2){
					$list[$k]['order_status_format'] = '确认通过';
				}else if ($list[$k]['order_status'] == 3){
					$list[$k]['order_status_format'] = '已完成';
				}else if ($list[$k]['order_status'] == 4){
					$list[$k]['order_status_format'] = '作废';
				}else if ($list[$k]['order_status'] == 5){
					$list[$k]['order_status_format'] = '确认不通过';
				}else {
					$list[$k]['order_status_format'] = '未知';
				}
	
				//refund_status：0.未申请退款;1:申请退款;2:确认退款;3:拒绝退款;
				if ($list[$k]['refund_status'] == 1){
					$list[$k]['refund_status_format'] = '申请退款';
				}else if ($list[$k]['refund_status'] == 2){
					$list[$k]['refund_status_format'] = '已退款';
				}else if ($list[$k]['refund_status'] == 3){
					$list[$k]['refund_status_format'] = '拒绝退款';
				}else {
					$list[$k]['refund_status_format'] = '未申请退款';
				}
			}
		}
	
	
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
	
		$GLOBALS['tmpl']->assign("formaction",admin_url("spot#verify_code_list"));
		//$GLOBALS['tmpl']->assign("editurl",admin_url("tourline_order#order"));
		$GLOBALS['tmpl']->assign("exporturl",admin_url("spot#export_csv"));
		$GLOBALS['tmpl']->assign("seteffecturl",admin_url("spot#set_invalid",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("sendurl",admin_url("spot#send_sms_mail",array("ajax"=>1)));
		
		$GLOBALS['tmpl']->display("core/spot/code_list.html");
	}
	
	public function export_csv($page = 1)
	{
	
		$param = array();
		//条件
		$condition = " t.pay_status = 1 ";
	
		//订单号
		if(isset($_REQUEST['sn']))
			$sn = strim($_REQUEST['sn']);
		else
			$sn = "";
		$param['sn'] = $sn;
		if($sn!='')
		{
			$condition.=" and t.sn = '".$sn."' ";
		}
	
	
	
		//景点ID
		if(isset($_REQUEST['spot_id']))
			$spot_id = strim($_REQUEST['spot_id']);
		else
			$spot_id = "";
		$param['spot_id'] = $spot_id;
		if($spot_id!='' && intval($spot_id) > 0)
		{
			$condition.=" and t.spot_id = ".intval($spot_id)." ";
		}
	
	
	
		//预定人姓名
		if(isset($_REQUEST['appoint_name']))
			$appoint_name = strim($_REQUEST['appoint_name']);
		else
			$appoint_name = "";
		$param['appoint_name'] = $appoint_name;
		if($appoint_name!='')
		{
			$condition.=" and t.appoint_name = '".$appoint_name."' ";
		}
	
		//预定人手机
		if(isset($_REQUEST['appoint_mobile']))
			$appoint_mobile = strim($_REQUEST['appoint_mobile']);
		else
			$appoint_mobile = "";
		$param['appoint_mobile'] = $appoint_mobile;
		if($appoint_mobile!='')
		{
			$condition.=" and t.appoint_mobile = '".$appoint_mobile."' ";
		}
	
		//验证码
		if(isset($_REQUEST['verify_code']))
			$verify_code = strim($_REQUEST['verify_code']);
		else
			$verify_code = "";
		$param['verify_code'] = $verify_code;
		if($verify_code!='')
		{
			$condition.=" and t.verify_code = '".$verify_code."' ";
		}
	
		//退款状态
		$refund_status = -1;
		if(isset($_REQUEST['refund_status']) && strim($_REQUEST['refund_status'])!="")
			$refund_status = intval($_REQUEST['refund_status']);
	
		$param['refund_status'] = $refund_status;
		if($refund_status !=-1)
		{
			$condition .=" and t.refund_status=$refund_status ";
		}
	
		//订单状态
		$order_status = 0;
		if(isset($_REQUEST['order_status']) && strim($_REQUEST['order_status'])!="")
			$order_status = intval($_REQUEST['order_status']);
	
		$param['order_status'] = $order_status;
		if($order_status !=0)
		{
			$condition .=" and t.order_status=$order_status ";
		}
	
		//是否验证
		$is_verify = intval($_REQUEST['is_verify']);
		if ($is_verify == 1){
			$condition .=" and a.verify_time=0";
		}else if ($is_verify == 2){
			$condition .=" and a.verify_time>0";
		}
		$param['is_verify'] = $is_verify;
	
		//是否有效
		$is_verify_code_invalid = -1;
		if(isset($_REQUEST['is_verify_code_invalid']) && strim($_REQUEST['is_verify_code_invalid'])!="")
			$is_verify_code_invalid = intval($_REQUEST['is_verify_code_invalid']);
	
		$param['is_verify_code_invalid'] = $is_verify_code_invalid;
		if($is_verify_code_invalid !=-1)
		{
			$condition .=" and a.is_verify_code_invalid=$is_verify_code_invalid ";
		}
	
		//print_r($_REQUEST);
		//echo $is_verify_code_invalid;
	
		//会员ID
		if(isset($_REQUEST['user_id']))
			$user_id = strim($_REQUEST['user_id']);
		else
			$user_id = "";
		$param['user_id'] = $user_id;
		if($user_id!='' && intval($user_id) > 0)
		{
			$condition.=" and t.user_id = ".intval($user_id)." ";
		}
	
		//下单时间
		$create_time_begin  = strim($_REQUEST['create_time_begin']);
		$param['create_time_begin'] = $create_time_begin;
	
		$create_time_end  = strim($_REQUEST['create_time_end']);
		$param['create_time_end'] = $create_time_end;
	
		if(!empty($create_time_begin) && !empty($create_time_end))
		{
			$condition.=" and t.create_time >= '".to_timespan($create_time_begin)."' and t.create_time <='". (to_timespan($create_time_end) + 3600 * 24 - 1)."' ";
		}
	
		//预约时间
		$appoint_time_begin  = strim($_REQUEST['appoint_time_begin']);
		$param['appoint_time_begin'] = $appoint_time_begin;
	
		$appoint_time_end  = strim($_REQUEST['appoint_time_end']);
		$param['appoint_time_end'] = $appoint_time_end;
	
		if(!empty($appoint_time_begin) && !empty($appoint_time_end))
		{
			$condition.=" and a.appoint_time >= '".$appoint_time_begin."' and a.appoint_time <='". $appoint_time_end."' ";
		}
	
		$param['pageSize'] = 100;
		//分页
		$limit = (($page-1)*$param['pageSize']).",".$param['pageSize'];
	
		$sql = "select count(a.id) from ".DB_PREFIX."ticket_order_item a
					LEFT JOIN ".DB_PREFIX."ticket_order t on t.id = a.order_id
					LEFT JOIN ".DB_PREFIX."user u on u.id = a.user_id
					where ".$condition;
			
		$totalCount = $GLOBALS['db']->getOne($sql);
		if($totalCount > 0){
			$sql = "
					select a.*,u.user_name, t.create_time, t.appoint_name,t.appoint_mobile,t.sale_count, t.total_price, t.pay_amount,t.pay_status,t.pay_time,t.order_status,t.order_memo from ".DB_PREFIX."ticket_order_item a
					LEFT JOIN ".DB_PREFIX."ticket_order t on t.id = a.order_id
					LEFT JOIN ".DB_PREFIX."user u on u.id = a.user_id
					where ".$condition." limit ".$limit;
	
			//$sql = "select t.*,u.user_name,u.mobile,s.user_name as supplier_name  from ".DB_PREFIX."tourline_order t left outer join ".DB_PREFIX."user u on u.id = t.user_id left outer join ".DB_PREFIX."supplier s on s.id = t.supplier_id where ".$condition." limit ".$limit;
			//echo $sql;
			//die();
			$list = $GLOBALS['db']->getAll($sql);
	
			require_once APP_ROOT_PATH."system/libs/spot.php";
	
			foreach($list as $k=>$v)
			{
			//tourline_order_format($list[$k]);
				ticket_order_item_format($list[$k]);
	
				//是否为团体票 1:个人票 0团体票
				if ($list[$k]['is_divide'] == 1){
					$list[$k]['is_divide_format'] = '个人票';
				}else{
					$list[$k]['is_divide_format'] = '团体票['.$list[$k]['sale_count'].']';
				}
	
				$list[$k]['create_time_format'] = to_date($list[$k]['create_time']);//下单时间
	
				//订单状态(流程)1.新订单 2.已确认 3.已完成 4.作废\r\n新订单：未确认（包含已付款）的都表示为新订单\r\n已确认：表示为商家或管理员查看，确认手动修改\r\n新订单、已确认均可申请退款，否则不可',
				if ($list[$k]['order_status'] == 1){
					$list[$k]['order_status_format'] = '新订单';
				}else if ($list[$k]['order_status'] == 2){
					$list[$k]['order_status_format'] = '确认通过';
				}else if ($list[$k]['order_status'] == 3){
					$list[$k]['order_status_format'] = '已完成';
				}else if ($list[$k]['order_status'] == 4){
					$list[$k]['order_status_format'] = '作废';
				}else if ($list[$k]['order_status'] == 5){
					$list[$k]['order_status_format'] = '确认不通过';
				}else {
					$list[$k]['order_status_format'] = '未知';
				}
	
				//refund_status：0.未申请退款;1:申请退款;2:确认退款;3:拒绝退款;
				if ($list[$k]['refund_status'] == 1){
					$list[$k]['refund_status_format'] = '申请退款';
				}else if ($list[$k]['refund_status'] == 2){
					$list[$k]['refund_status_format'] = '已退款';
				}else if ($list[$k]['refund_status'] == 3){
					$list[$k]['refund_status_format'] = '拒绝退款';
				}else {
					$list[$k]['refund_status_format'] = '未申请退款';
				}
			}
	
			if($page == 1)
			{
				$content = iconv("utf-8","gbk","订单ID,门票,验证码,购买会员,预定人姓名,预定人手机,预定日期,下单时间,订单金额,付款时间,已付金额,是否验证,是否有效,订单状态,退款状态,订单备注");
				$content = $content . "\n";
			}
	
			if($list)
			{
				register_shutdown_function(array(&$this, 'export_csv'), $page+1);
				foreach($list as $k=>$v)
				{
	
					$order_value = array();
					$order_value['id'] = '"' . $v['id'] . '"';
					$order_value['ticket_name'] = '"' .iconv('utf-8','gbk',$v['ticket_name']) . '"';
					$order_value['verify_code'] = '"' . $v['verify_code'] . '"';
					$order_value['user_name'] = '"' .iconv('utf-8','gbk',$v['user_name']) . '"';
					$order_value['appoint_name'] = '"' . iconv('utf-8','gbk',$v['appoint_name']) . '"';
					$order_value['appoint_mobile'] = '"' . $v['appoint_mobile'] . '"';
	
					$order_value['appoint_time_format'] = '"' . $v['appoint_time_format'] . '"';
					$order_value['create_time_format'] = '"' . $v['create_time_format'] . '"';
					$order_value['total_price_format'] = '"' . $v['total_price_format'] . '"';
					$order_value['pay_time_format'] = '"' . $v['pay_time_format'] . '"';
					$order_value['pay_amount_format'] = '"' . $v['pay_amount_format'] . '"';
	
					$order_value['is_verify'] = '"' . iconv('utf-8','gbk',$v['is_verify']) . '"';
					$order_value['is_invalid'] = '"' . iconv('utf-8','gbk',$v['is_invalid']) . '"';
					$order_value['order_status_format'] = '"' . iconv('utf-8','gbk',$v['order_status_format']) . '"';
					$order_value['refund_status_format'] = '"' . iconv('utf-8','gbk',$v['refund_status_format']) . '"';
	
	
	
					$order_value['order_memo'] = '"' . iconv('utf-8','gbk',$v['order_memo']) . '"';
	
					$content .= implode(",", $order_value) . "\n";
				}
			}
		}
	
	
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=spot_code.csv");
		echo $content;
	
	
	}
	
	public function set_invalid()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = $GLOBALS['db']->getOne("select verify_code from ".DB_PREFIX."ticket_order_item where id = ".$id);
		$c_is_effect =  $GLOBALS['db']->getOne("select is_verify_code_invalid from ".DB_PREFIX."ticket_order_item where id = ".$id); //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
	
		$GLOBALS['db']->query("update ".DB_PREFIX."ticket_order_item set is_verify_code_invalid = ".$n_is_effect." where id = ".$id);
		save_log($info.lang("SET_EFFECT_".$n_is_effect),1);
		showSuccess(lang("SET_EFFECT_".$n_is_effect),$ajax);
	}	
	
	//统计
	public function statistics()
	{
		$id = intval($_REQUEST['id']);
		//订单总数，实收总额，余额支付总额，代金券支付总额，在线支付总额，退款总额，返利现金总额，返积分总额，返经验总额，已点评数，点评每个星级的统计数
		$param['order_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ticket_order where pay_status = 1 and spot_id = ".$id);
		$param['order_count'] = intval($param['order_count']);
		//实收总额
		$param['pay_amount'] = $GLOBALS['db']->getOne("select sum(pay_amount) from ".DB_PREFIX."ticket_order where pay_status = 1 and spot_id = ".$id);
		$param['pay_amount'] = format_price(format_price_to_display($param['pay_amount']));
		
		//余额支付总额
		$param['account_pay'] = $GLOBALS['db']->getOne("select sum(account_pay) from ".DB_PREFIX."ticket_order where pay_status = 1 and spot_id = ".$id);
		$param['account_pay'] = format_price(format_price_to_display($param['account_pay']));
		
		//代金券支付总额
		$param['voucher_pay'] = $GLOBALS['db']->getOne("select sum(voucher_pay) from ".DB_PREFIX."ticket_order where pay_status = 1 and spot_id = ".$id);
		$param['voucher_pay'] = format_price(format_price_to_display($param['voucher_pay']));
		
		//在线支付总额
		$param['online_pay'] = $GLOBALS['db']->getOne("select sum(online_pay) from ".DB_PREFIX."ticket_order where pay_status = 1 and spot_id = ".$id);
		$param['online_pay'] = format_price(format_price_to_display($param['online_pay']));
		
		//退款总额
		$param['refund_amount'] = $GLOBALS['db']->getOne("select sum(refund_amount) from ".DB_PREFIX."ticket_order where pay_status = 1 and spot_id = ".$id);
		$param['refund_amount'] = format_price(format_price_to_display($param['refund_amount']));
		
		//返利现金总额
		$param['return_money_total'] = $GLOBALS['db']->getOne("select sum(return_money_total) from ".DB_PREFIX."ticket_order where pay_status = 1 and spot_id = ".$id);
		$param['return_money_total'] = format_price(format_price_to_display($param['return_money_total']));
		
		//返积分总额
		$param['return_score_total'] = $GLOBALS['db']->getOne("select sum(return_score_total) from ".DB_PREFIX."ticket_order where pay_status = 1 and spot_id = ".$id);
		$param['return_score_total'] = intval($param['return_score_total']);
		//返经验总额
		$param['return_exp_total'] = $GLOBALS['db']->getOne("select sum(return_exp_total) from ".DB_PREFIX."ticket_order where pay_status = 1 and spot_id = ".$id);
		$param['return_exp_total'] = intval($param['return_exp_total']);
		//fanwe_review
		
		//星级点评数
		$param['star_1'] = 0;
		$param['star_2'] = 0;
		$param['star_3'] = 0;
		$param['star_4'] = 0;
		$param['star_5'] = 0;
		
		$buy_dp_sum = 0.0;
		//已点评数，点评每个星级的统计数   review_type 点评类型(1.线路 2.门票 3.酒店)
		$buy_dp_group = $GLOBALS['db']->getAll("select point,count(*) as num from ".DB_PREFIX."review where review_type = 2 and review_rel_id = ".$id." group by point");
		foreach($buy_dp_group as $dp_k=>$dp_v)
		{
			$star = intval($dp_v['point']);
			if ($star >= 1 && $star <= 5){
				$param['star_'.$star] = $dp_v['num'];
				$buy_dp_sum += $star * $dp_v['num'];
			}
		}
		//已点评数
		$param['star_sum'] = $param['star_1'] + $param['star_2'] + $param['star_3'] + $param['star_4'] + $param['star_5'];
		//点评平均分
		$param['star_avg'] = round($buy_dp_sum / $param['star_sum'],1);
		
		$GLOBALS['tmpl']->assign("param",$param);
		$GLOBALS['tmpl']->display("core/spot/statistics.html");
	}
	
	public function send_sms_mail()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
	
		//1:短信;2:邮件
		$send_type = intval($_REQUEST['send_type']);
	
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ticket_order where id = '".$id."'");
		//订单支付成功，发短信
		if ($send_type == 1){
			if (send_order_sms($order_info,2) == 1){
				showSuccess("已将发送内容,添加到队列",$ajax);
			}else{
				showErr("添加发送队列失败",$ajax);
			}
		}
	
		//订单支付成功，发邮件
		if ($send_type == 2){
			if (send_order_mail($order_info,2) == 1){
				showSuccess("已将发送内容,添加到队列",$ajax);
			}else{
				showErr("添加发送队列失败",$ajax);
			}
		}
	}	
}
?>