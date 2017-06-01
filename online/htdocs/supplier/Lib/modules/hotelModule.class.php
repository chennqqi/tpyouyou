<?php

class hotelModule extends AuthModule{

  function index() {
    $param = array();		
		//条件
		$condition = " supplier_id =  ".$this->supplier_id;
		if(isset($_REQUEST['name']))
			$name_key = strim($_REQUEST['name']);
		else
			$name_key = "";
		$param['name'] = $name_key;
		if($name_key!='')
		{
			$condition.=" and name = '".$name_key."' ";
		}
		
		$has_ticket = 3;
		if(isset($_REQUEST['has_ticket']) && strim($_REQUEST['has_ticket'])!="")
			$has_ticket = intval($_REQUEST['has_ticket']);
		
		
		if(isset($_REQUEST['spot_cate']))
			$spot_cate_key = strim($_REQUEST['spot_cate']);
		else
			$spot_cate_key = '';
		$param['spot_cate'] = $spot_cate_key;
		if($spot_cate_key !='')
		{
			$kw_unicode = str_to_unicode_string($spot_cate_key);
			$condition .=" and (match(cate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
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
		if($totalCount){
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."hotel where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		}
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$spot_cate = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."spot_cate ORDER BY sort DESC,id ASC");
		$GLOBALS['tmpl']->assign('spot_cate',$spot_cate);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("hotel"));
		$GLOBALS['tmpl']->assign("setsorturl",admin_url("hotel#set_sort",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("editurl",admin_url("hotel#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("hotel#add"));
		$GLOBALS['tmpl']->display("core/hotel/index.html");
  }
    
  public function add() {
		$sort = $GLOBALS['db']->getOne("select max(sort) from ".DB_PREFIX."hotel_room")+1;	
		$GLOBALS['tmpl']->assign("sort",$sort);
		$GLOBALS['tmpl']->assign("searchcityurl",admin_url("tour_city#search_city"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchareaurl",admin_url("tour_area#search_area"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchtagurl",admin_url("tour_place_tag#search_tag"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchcateurl",admin_url("spot_cate#search_cate"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchplaceurl",admin_url("tour_place#search_place"),array("ajax"=>1));

		$GLOBALS['tmpl']->assign("addhotelroom",admin_url("hotel_room#add"),array("ajax"=>1));
    	$GLOBALS['tmpl']->assign("edithotelroom",admin_url("hotel_room#edit",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("formaction",admin_url("hotel#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/hotel/add.html");
	}

	public function insert() {
		$ajax = intval($_REQUEST['ajax']);
		if(!check_empty("name"))
		{
			showErr("请输入名称",$ajax);
		}
		if(!check_empty("tel"))
		{
			showErr("请输入酒店联系电话",$ajax);
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
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['supplier_name'] = $this->supplier_name;
		$data['supplier_id'] = $this->supplier_id;
		if(isset($_REQUEST['hotel_img'])){
			$data['image'] = format_domain_to_relative(strim($_REQUEST['hotel_img'][0]));
			$data['image_list'] = serialize($_REQUEST['hotel_img']);
		}
		else{
			$data['image'] ="";
		}
		$data['city_match'] = format_fulltext_key(strim($_REQUEST['tour_city_py']));
		$data['city_match_row'] = strim($_REQUEST['tour_city_name']);

		$data['star_level'] = intval($_REQUEST["star_level"]);

		$data['tel'] = intval($_REQUEST["tel"]);
		
		$data['brief'] = strim($_REQUEST["brief"]);
		$data['description'] = format_domain_to_relative(btrim($_REQUEST['description']));

		$data['address'] = strim($_REQUEST['address']);
		$data['x_point'] = strim($_REQUEST['xpoint']);
		$data['y_point'] = strim($_REQUEST['ypoint']);
		$data['create_time'] = NOW_TIME;
		
		if(isset($_REQUEST['tickets'])){
			foreach($_REQUEST['tickets'] as $k=>$v){
				$v = unserialize(base64_decode($v));
				$tuan_begin_time = 0;
				$tuan_end_time = 0;
				if(intval($v['is_tuan']) == 0){
					$v['tuan_begin_time'] = "";
					$v['tuan_end_time'] = "";
				}
				$_REQUEST['tickets'][$k] = base64_encode(serialize($v));
				
				if($data['price'] == false || $data['price'] > intval($v['sale_price'])){
					$data['price'] = $v['sale_price'];
				}
			}
			$data['room_list'] = serialize($_REQUEST['tickets']);
			$data['price'] = format_price_to_db($data['price']);
		}
		// 更新数据
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."hotel_supplier",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			showSuccess(lang("INSERT_SUCCESS"),$ajax,admin_url("hotel_supplier#index"));
		} else {
			//错误提示
			showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	
	}

	public function edit() {
		$id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."hotel where id = ".$id." AND supplier_id=".$this->supplier_id);
		if(!$vo){
			showErr("不存在的数据",1);
		}
		//商家
		$vo['company_name'] = $GLOBALS['db']->getOne("select `company_name` from ".DB_PREFIX."supplier where id = ".$vo['supplier_id']);
		$vo['city_match'] = unformat_fulltext_key($vo['city_match']);
		$vo['area_match'] = unformat_fulltext_key($vo['area_match']);
		$vo['place_match'] = unformat_fulltext_key($vo['place_match']);
		$GLOBALS['tmpl']->assign ( 'vo', $vo );
		
		//相册
		$timage_list = $GLOBALS['db']->getAll("select `image` from ".DB_PREFIX."spot_image where spot_id = ".$vo['id']." ORDER BY sort ASC");
		if($timage_list){
			foreach($timage_list as $k=>$v){
				$image_list[] = $v['image'];
			}
			$GLOBALS['tmpl']->assign ( 'image_list', $image_list );
		}
		
		//门票
		$tickets = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."hotel_room where hotel_id = ".$vo['id']." ORDER BY sort DESC ");
		$tickets_ids = array();
		foreach($tickets as $k=>$v){
			$tickets[$k]['origin_price'] = format_price_to_display($v['origin_price']);
			$tickets[$k]['current_price'] = format_price_to_display($v['current_price']);
			$tickets[$k]['sale_price'] = format_price_to_display($v['sale_price']);
			if($v['end_time']!=0){
				$tickets[$k]['end_time'] = to_date($v['end_time'],"Y-m-d");
			}
			else{
				$tickets[$k]['end_time'] = "";
			}
			
			if($v['tuan_begin_time']!=0){
				$tickets[$k]['tuan_begin_time'] = to_date($v['tuan_begin_time']);
			}
			else{
				$tickets[$k]['tuan_begin_time'] = "";
			}
			
			if($v['tuan_end_time']!=0){
				$tickets[$k]['tuan_end_time'] = to_date($v['tuan_end_time']);
			}
			else{
				$tickets[$k]['tuan_end_time'] = "";
			}
			$tickets[$k]['return_money'] = format_price_to_display($v['return_money']);
			$tickets[$k]['review_return_money'] = format_price_to_display($v['review_return_money']);
			$tickets_ids[] = $v['id'];
		}
		//获取返券
		if($tickets_ids){
			$tvoucher_promote = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."voucher_promote where voucher_rel_id in(".implode(",",$tickets_ids).") AND voucher_promote = 1 ");
			$voucher_promote = array();
			foreach($tvoucher_promote as $k=>$v){
				if($v['voucher_promote_type'] == 1)
					$voucher_promote['buy'][$v['voucher_rel_id']] = $v['voucher_type_id'];
				if($v['voucher_promote_type'] == 2)
					$voucher_promote['review'][$v['voucher_rel_id']] = $v['voucher_type_id'];
			}
			unset($tvoucher_promote);
		}
		foreach($tickets as $k=>$v){
			$tickets[$k]['voucher'] = $voucher_promote['buy'][$v['id']]; 
			$tickets[$k]['review_voucher'] = $voucher_promote['review'][$v['id']]; 
			$tickets[$k]['ticket_data'] = base64_encode(serialize($tickets[$k]));
		}
		$GLOBALS['tmpl']->assign ( 'tickets', $tickets );
		
		$GLOBALS['tmpl']->assign("searchcityurl",admin_url("tour_city#search_city"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchareaurl",admin_url("tour_area#search_area"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchtagurl",admin_url("tour_place_tag#search_tag"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchcateurl",admin_url("spot_cate#search_cate"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchplaceurl",admin_url("tour_place#search_place"),array("ajax"=>1));
    	$GLOBALS['tmpl']->assign("searchsupplierurl",admin_url("supplier#search_supplier",array("ajax"=>1)));
    	
    	
		$GLOBALS['tmpl']->assign("addtickets",admin_url("spot_ticket#add"),array("ajax"=>1));
    	$GLOBALS['tmpl']->assign("edittickets",admin_url("spot_ticket#edit",array("ajax"=>1)));
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("ticket#update",array("ajax"=>1)));
		
		$GLOBALS['tmpl']->display("core/hotel/edit.html");
	}

	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		if(!check_empty("name"))
		{
			showErr(lang("SPOT_CATE_NAME_EMPTY"),$ajax);
		}
		if(!check_empty("tour_cate_cate_name"))
		{
			showErr("请选择景点分类",$ajax);
		}
		if(!check_empty("tour_city_name"))
		{
			showErr("请选择城市",$ajax);
		}
		if(!check_empty("tour_area_name"))
		{
			showErr("请选择大区域",$ajax);
		}
		if(!check_empty("description"))
		{
			showErr("请输入内容",$ajax);
		}
		if(!check_empty("xpoint") || !check_empty("ypoint") )
		{
			showErr("请定位地图",$ajax);
		}
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['supplier_name'] = $this->supplier_name;
		$data['supplier_id'] = $this->supplier_id;
		if(isset($_POST['spot_img'])){
			$data['image'] = format_domain_to_relative(strim($_POST['spot_img'][0]));
			$data['image_list'] = serialize($_POST['spot_img']);
		}
		else{
			$data['image'] ="";
		}
		
		$data['cate_match'] = str_to_unicode_string_depart(strim($_REQUEST['tour_cate_cate_name']));
		$data['cate_match_row'] = strim($_REQUEST['tour_cate_cate_name']);
		
		$data['city_match'] = format_fulltext_key(strim($_REQUEST['tour_city_py']));
		$data['city_match_row'] = strim($_REQUEST['tour_city_name']);
		
		$data['area_match'] = format_fulltext_key(strim($_REQUEST['tour_area_py']));
		$data['area_match_row'] = strim($_REQUEST['tour_area_name']);
		
		$data['place_match'] = format_fulltext_key(strim($_REQUEST['tour_place_py']));
		$data['place_match_row'] = strim($_REQUEST['tour_place_name']);
		
		$data['tag'] = $data['tag_match'] = str_to_unicode_string_depart(strim($_REQUEST['tour_place_tag_tag_name']));
		$data['tag_match_row'] = strim($_REQUEST['tour_place_tag_tag_name']);
		
		$data['spot_level'] = intval($_REQUEST["spot_level"]);
		
		$data['brief'] = strim($_REQUEST["brief"]);
		$data['appointment_desc'] = format_domain_to_relative(btrim($_REQUEST["appointment_desc"]));
		$data['description'] = format_domain_to_relative(btrim($_REQUEST['description']));
		
		$data['spot_desc_1_name'] = strim($_REQUEST['spot_desc_1_name']);
		$data['spot_desc_2_name'] = strim($_REQUEST['spot_desc_2_name']);
		$data['spot_desc_3_name'] = strim($_REQUEST['spot_desc_3_name']);
		$data['spot_desc_4_name'] = strim($_REQUEST['spot_desc_4_name']);
		
		$data['spot_desc_1'] = format_domain_to_relative(btrim($_REQUEST['spot_desc_1']));
		$data['spot_desc_2'] = format_domain_to_relative(btrim($_REQUEST['spot_desc_2']));
		$data['spot_desc_3'] = format_domain_to_relative(btrim($_REQUEST['spot_desc_3']));
		$data['spot_desc_4'] = format_domain_to_relative(btrim($_REQUEST['spot_desc_4']));
		
		$data['address'] = strim($_REQUEST['address']);
		$data['x_point'] = strim($_REQUEST['xpoint']);
		$data['y_point'] = strim($_REQUEST['ypoint']);
		$data['create_time'] = NOW_TIME;
		
		if(isset($_REQUEST['tickets'])){
			
			$data['has_ticket'] = 1;
			$data['ticket_price'] = false;
			
			foreach($_REQUEST['tickets'] as $k=>$v){
				$v = unserialize(base64_decode($v));
				
				$tuan_begin_time = 0;
				$tuan_end_time = 0;
				if(intval($v['is_tuan']) == 0){
					$v['tuan_begin_time'] = "";
					$v['tuan_end_time'] = "";
				}
				
				$_REQUEST['tickets'][$k] = base64_encode(serialize($v));
				
				if($data['ticket_price'] == false || $data['ticket_price'] > intval($v['sale_price'])){
					
					$data['ticket_price'] = $v['sale_price'];
				}
			}
			
			$data['ticket_list'] = serialize($_REQUEST['tickets']);
			
			$data['ticket_price'] = format_price_to_db($data['ticket_price']);
		}else{
			$data['ticket_list']="";
			$data['has_ticket'] = 0;
		}
		
		// 更新数据
		$data['rel_id'] = intval($_REQUEST["id"]);
		if($data['rel_id'] > 0 && $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."spot_supplier WHERE rel_id=".$data['rel_id']." and supplier_id=".$this->supplier_id) > 0)
			$GLOBALS['db']->autoExecute(DB_PREFIX."spot_supplier",$data,"UPDATE"," supplier_id=".$this->supplier_id ." and rel_id=".$data['rel_id'],"SILENT");
		else
			$GLOBALS['db']->autoExecute(DB_PREFIX."spot_supplier",$data,"INSERT","","SILENT");
			
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			showSuccess("编辑成功，等待审核",$ajax,admin_url("ticket_supplier#index"));
		} else {
			//错误提示
			showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	
	}

	/**
	 * 验证消费券
	 */
	public function verify()
	{
		//处理保存下来的已选数据
		$ajax = intval($_REQUEST['ajax']);
		$verify_code = intval($_REQUEST['verify_code']);
		if ($verify_code > 0){
			//$sql = "select * from ".DB_PREFIX."tourline_order where order_status <> 4 and verify_code = ".$verify_code." and supplier_id =".$this->supplier_id;
			
			$sql = "select t.*,tt.id as tid,tt.verify_time,tt.verify_code,u.user_name,u.mobile,s.user_name as supplier_name  from ".DB_PREFIX."ticket_order t left outer join ".DB_PREFIX."user u on u.id = t.user_id left outer join ".DB_PREFIX."supplier s on s.id = t.supplier_id LEFT JOIN ".DB_PREFIX."ticket_order_item tt ON tt.order_id = t.id where t.order_status <> 4 and tt.verify_code = ".$verify_code." and t.supplier_id =".$this->supplier_id;;
			
			$order = $GLOBALS['db']->getRow($sql);
			if(empty($order))
			{			
				showErr($_REQUEST['verify_code'].':验证码不存在',1);
			}else{
				if ($order['verify_code'] <> $verify_code){					
					showErr($_REQUEST['verify_code'].':验证码错误',1);
				}else if ($order['is_verify_code_invalid'] == 1){					
					showErr($_REQUEST['verify_code'].':验证码已失效',1);
				}else if ($order['verify_time'] > 0){					
					showErr($_REQUEST['verify_code'].':验证码已被验证',1);
				}else if ($order['order_status'] == 1){					
					showErr($_REQUEST['verify_code'].':订单未确认',1);
				}else if ($order['order_status'] == 3){					
					showErr($_REQUEST['verify_code'].':订单已完成',1);
				}else if ($order['order_status'] == 4){					
					showErr($_REQUEST['verify_code'].':订单已作废',1);
				}else{
					require_once APP_ROOT_PATH."system/libs/spot.php";
					
					$id = intval($_REQUEST['id']);
					if ($id == 0){
						ticket_order_format($order);
						$GLOBALS['tmpl']->assign("use_verify_code_url",admin_url("ticket#verify",array('id'=>$order['id'],"ajax"=>1,'verify_code'=>$verify_code)));
						$order['verify_time_format'] = to_date($order['verify_time']);
						$GLOBALS['tmpl']->assign("order",$order);
						$GLOBALS['tmpl']->assign("verify_code",$verify_code);
					}else{
						$res = ticket_order_use_verify_code($id,$order['tid'],$verify_code,1,$this->supplier_id);
						if($res['return']){
							//是否要完成订单
							//ticket_order_complete($id, 1);
							showSuccess('验证成功',1,admin_url("ticket#verify"));
						}else{
							showErr($res['message'],1);
						}
					}					
				}
				//showSuccess('验证成功',0,admin_url("tourline#verify"));
				//exit;
			}
			
		}
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("ticket#verify"));
		$GLOBALS['tmpl']->display("core/ticket/verify.html");
	}
}
?>