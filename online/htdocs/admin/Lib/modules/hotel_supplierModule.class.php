<?php
class hotel_supplierModule extends AuthModule {

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
		
		if(isset($_REQUEST['supplier_name']))
			$supplier_name_key = strim($_REQUEST['supplier_name']);
		else
			$name_key = "";
		$param['supplier_name'] = $supplier_name_key;
		if($supplier_name_key!='')
		{
			$condition.=" and supplier_name = '".$supplier_name_key."' ";
		}
		
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
		
		
		$totalCount = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."hotel_supplier where ".$condition);
		$supplier_ids = array();
		if($totalCount){
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."hotel_supplier where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
			foreach($list as $k=>$v){
				$list[$k]['create_time_format'] = to_date($v['create_time']);
				if($v['spot_level'] >0)
					$list[$k]['level_format'] = lang("SPOT_LEVEl_".$v['spot_level']);
				else
					$list[$k]['level_format'] = "无";
				
				$list[$k]['ticket_price_format'] = format_price(format_price_to_display($v['ticket_price']));
				
				$list[$k]['preview_url'] = url("spot#view",array("sid"=>$v['id']));
			}
		}
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$spot_cate = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."spot_cate ORDER BY sort DESC,id ASC");
		$GLOBALS['tmpl']->assign('spot_cate',$spot_cate);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("hotel_supplier"));
		$GLOBALS['tmpl']->assign("setsorturl",admin_url("hotel_supplier#set_sort",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("delurl",admin_url("hotel_supplier#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("hotel_supplier#edit"));
		$GLOBALS['tmpl']->display("core/hotel_supplier/index.html");
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
				$del_name = $GLOBALS['db']->getOne("select group_concat(name) from ".DB_PREFIX."hotel_supplier where id in (".$id.")");			
				$sql = "delete from ".DB_PREFIX."hotel_supplier where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->affected_rows()>0)
				{	
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
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."hotel_supplier where id = ".$id);
				
		$vo['city_match'] = unformat_fulltext_key($vo['city_match']);
		$vo['area_match'] = unformat_fulltext_key($vo['area_match']);
		$vo['place_match'] = unformat_fulltext_key($vo['place_match']);
		
		//商家
		$vo['company_name'] = $vo['supplier_name'];
		
		if($vo['rel_id'] > 0){
			$old_vo = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."hotel WHERE id=".$vo['rel_id']);
			$vo['show_sale_list'] = $old_vo['show_sale_list'];
			$vo['tour_guide_key'] = $old_vo['tour_guide_key'];

			$vo['sort'] = $old_vo['sort'];
		}
		else{
			
			$vo['sort'] = $GLOBALS['db']->getOne("select max(sort) from ".DB_PREFIX."spot")+1;	
			$vo['show_sale_list'] = 1;
		}
		
		$GLOBALS['tmpl']->assign ( 'vo', $vo );
		
		//相册
		if($vo['image_list']){
			$image_list = unserialize($vo['image_list']);
			$GLOBALS['tmpl']->assign ( 'image_list', $image_list );
		}
		// 房型
		if($vo['room_list']){
			$ttickets = unserialize($vo['room_list']);
			foreach($ttickets as $k=>$v){
				$tickets[$k] = unserialize(base64_decode($v));
				$tickets[$k]['ticket_data'] = $v;
			}
			$GLOBALS['tmpl']->assign ( 'tickets', $tickets );
		}
		
		
		$GLOBALS['tmpl']->assign("searchcityurl",admin_url("tour_city#search_city"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchareaurl",admin_url("tour_area#search_area"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchtagurl",admin_url("tour_place_tag#search_tag"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchcateurl",admin_url("spot_cate#search_cate"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("searchplaceurl",admin_url("tour_place#search_place"),array("ajax"=>1));
    $GLOBALS['tmpl']->assign("searchsupplierurl",admin_url("supplier#search_supplier",array("ajax"=>1)));
    	
		$GLOBALS['tmpl']->assign("addtickets",admin_url("hotel_room#add"),array("ajax"=>1));
  	$GLOBALS['tmpl']->assign("edittickets",admin_url("hotel_room#edit",array("ajax"=>1)));
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("hotel_supplier#update",array("ajax"=>1)));
		
		$GLOBALS['tmpl']->display("core/hotel_supplier/edit.html");
	}
	
	public function update(){
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);
		$hotel_id = intval($_REQUEST['rel_id']);
		if($id == 0){
			showErr(lang("PUBLISH_FAILED")."<br />不存在的商户提交的酒店",$ajax);
		}
		if(!check_empty("name"))
		{
			showErr("请输入酒店名称",$ajax);
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
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['supplier_id'] = intval($_REQUEST['supplier_id']);
		if(isset($_REQUEST['spot_img'])){
			$data['image'] = format_domain_to_relative(strim($_REQUEST['spot_img'][0]));
			$data['image_list'] = serialize($_POST['spot_img']);
		}
		else{
			$data['image'] ="";
		}
		
		$data['city_match'] = format_fulltext_key(strim($_REQUEST['tour_city_py']));
		$data['city_match_row'] = strim($_REQUEST['tour_city_name']);		
		
		$data['star_level'] = intval($_REQUEST["star_level"]);
		
		$data['brief'] = strim($_REQUEST["brief"]);
		$data['appointment_desc'] = strim($_REQUEST["appointment_desc"]);
		$data['description'] = format_domain_to_relative(strim($_REQUEST['description']));
		
		$data['address'] = strim($_REQUEST['address']);
		$data['x_point'] = strim($_REQUEST['xpoint']);
		$data['y_point'] = strim($_REQUEST['ypoint']);
		$data['sort'] = intval($_REQUEST['sort']);

		// 更新数据
		$log_info = $data['name'];
		$mode= "";
		if($hotel_id > 0){
			$mode = "update";
			$GLOBALS['db']->autoExecute(DB_PREFIX."hotel",$data,"UPDATE","id=".$spot_id,"SILENT");
		}
		else{
			$mode = "insert";
			$GLOBALS['db']->autoExecute(DB_PREFIX."hotel",$data,"INSERT","","SILENT");
		}
		
		if ($GLOBALS['db']->error()=="") {
			if($mode=="insert")
				$hotel_id = $GLOBALS['db']->insert_id();
			//图册
			//删除旧图库
			$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."hotel_image WHERE hotel_id=".$hotel_id);
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
			
			//删除商户提交的景点门票
			$sql = "delete from ".DB_PREFIX."hotel_supplier where id =".$id." ";
			$GLOBALS['db']->query($sql);			
			//成功提示
			save_log($log_info.lang("PUBLISH_SUCCESS"),1);
			showSuccess(lang("PUBLISH_SUCCESS"),$ajax,admin_url("hotel#index"));
		} else {
			//错误提示
			showErr(lang("PUBLISH_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	
	}
}
?>