<?php
class spot_supplierModule extends AuthModule {

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
		
		
		$totalCount = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."spot_supplier where ".$condition);
		$supplier_ids = array();
		if($totalCount){
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."spot_supplier where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
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
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("spot_supplier"));
		$GLOBALS['tmpl']->assign("setsorturl",admin_url("spot_supplier#set_sort",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("delurl",admin_url("spot_supplier#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("spot_supplier#edit"));
		$GLOBALS['tmpl']->display("core/spot_supplier/index.html");
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
				$del_name = $GLOBALS['db']->getOne("select group_concat(name) from ".DB_PREFIX."spot_supplier where id in (".$id.")");			
				$sql = "delete from ".DB_PREFIX."spot_supplier where id in (".$id.")";
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
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."spot_supplier where id = ".$id);
		
		$vo['city_match'] = unformat_fulltext_key($vo['city_match']);
		$vo['area_match'] = unformat_fulltext_key($vo['area_match']);
		$vo['place_match'] = unformat_fulltext_key($vo['place_match']);
		
		//商家
		$vo['company_name'] = $vo['supplier_name'];
		
		
		if($vo['rel_id'] > 0){
			$old_vo = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."spot WHERE id=".$vo['rel_id']);
			$vo['show_sale_list'] = $old_vo['show_sale_list'];
			$vo['tour_guide_key'] = $old_vo['tour_guide_key'];
			
			$vo['seo_title'] = $old_vo['seo_title'];
			$vo['seo_keywords'] = $old_vo['seo_keywords'];
			$vo['seo_description'] = $old_vo['seo_description'];
			
			$vo['adv1_name'] = $old_vo['adv1_name'];
			$vo['adv1_image'] = $old_vo['adv1_image'];
			$vo['adv1_url'] = $old_vo['adv1_url'];
			
			$vo['adv2_name'] = $old_vo['adv2_name'];
			$vo['adv2_image'] = $old_vo['adv2_image'];
			$vo['adv2_url'] = $old_vo['adv2_url'];
			
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
		//门票
		if($vo['ticket_list']){
			$ttickets = unserialize($vo['ticket_list']);
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
    	
    	
		$GLOBALS['tmpl']->assign("addtickets",admin_url("spot_ticket#add"),array("ajax"=>1));
    	$GLOBALS['tmpl']->assign("edittickets",admin_url("spot_ticket#edit",array("ajax"=>1)));
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("spot_supplier#update",array("ajax"=>1)));
		
		$GLOBALS['tmpl']->display("core/spot_supplier/edit.html");
	}
	
	
	public function update(){
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);
		$spot_id = intval($_REQUEST['rel_id']);
		if($id == 0){
			showErr(lang("PUBLISH_FAILED")."<br />不存在的商户提交的景点门票",$ajax);
		}
		if(!check_empty("name"))
		{
			showErr(lang("SPOT_CATE_NAME_EMPTY"),$ajax);
		}
		if(!check_empty("supplier_id") || intval($_REQUEST['supplier_id']) == 0 )
		{
			showErr("请选择商家",$ajax);
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
		$data['supplier_id'] = intval($_REQUEST['supplier_id']);
		if(isset($_REQUEST['spot_img'])){
			$data['image'] = format_domain_to_relative(strim($_REQUEST['spot_img'][0]));
		}
		else{
			$data['image'] ="";
		}
		
		$data['cate_match'] = str_to_unicode_string_depart(strim($_REQUEST['tour_cate_cate_name']));
		$data['cate_match_row'] = strim($_REQUEST['tour_cate_cate_name']);
		
		if(intval($_REQUEST['show_all_city']) == 1){
			$city_info = $GLOBALS['db']->getRow("SELECT GROUP_CONCAT(`name`) AS tour_city_name,GROUP_CONCAT(`py`) AS tour_city_py FROM ".DB_PREFIX."tour_city ORDER BY py_first ASC");
			$data['city_match'] = format_fulltext_key($city_info['tour_city_py']);
			$data['city_match_row'] = $city_info['tour_city_name'];
		}
		else{
			$data['city_match'] = format_fulltext_key(strim($_REQUEST['tour_city_py']));
			$data['city_match_row'] = strim($_REQUEST['tour_city_name']);
		}
		
		$data['area_match'] = format_fulltext_key(strim($_REQUEST['tour_area_py']));
		$data['area_match_row'] = strim($_REQUEST['tour_area_name']);
		
		$data['place_match'] = format_fulltext_key(strim($_REQUEST['tour_place_py']));
		$data['place_match_row'] = strim($_REQUEST['tour_place_name']);
		
		$data['tag'] = $data['tag_match'] = str_to_unicode_string_depart(strim($_REQUEST['tour_place_tag_tag_name']));
		$data['tag_match_row'] = strim($_REQUEST['tour_place_tag_tag_name']);
		
		$data['spot_level'] = intval($_REQUEST["spot_level"]);
		$data['show_sale_list'] = intval($_REQUEST["show_sale_list"]);
		
		$data['brief'] = strim($_REQUEST["brief"]);
		$data['appointment_desc'] = strim($_REQUEST["appointment_desc"]);
		$data['description'] = format_domain_to_relative(strim($_REQUEST['description']));
		
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
		$data['sort'] = intval($_REQUEST['sort']);
		
		$data['seo_title'] = strim($_REQUEST['seo_title']);
		$data['seo_keywords'] = strim($_REQUEST['seo_keywords']);
		$data['seo_description'] = strim($_REQUEST['seo_description']);
		
		$data['adv1_name'] = strim($_REQUEST['adv1_name']);
		$data['adv1_image'] = format_domain_to_relative(strim($_REQUEST['adv1_image']));
		$data['adv1_url'] = strim($_REQUEST['adv1_url']);
		
		$data['adv2_name'] = strim($_REQUEST['adv2_name']);
		$data['adv2_image'] = format_domain_to_relative(strim($_REQUEST['adv2_image']));
		$data['adv2_url'] = strim($_REQUEST['adv2_url']);
		
		
		
		// 更新数据
		
		$log_info = $data['name'];
		$mode= "";
		if($spot_id > 0){
			$mode = "update";
			$GLOBALS['db']->autoExecute(DB_PREFIX."spot",$data,"UPDATE","id=".$spot_id,"SILENT");
		}
		else{
			$mode = "insert";
			$GLOBALS['db']->autoExecute(DB_PREFIX."spot",$data,"INSERT","","SILENT");
		}
			
		if ($GLOBALS['db']->error()=="") {
			if($mode=="insert")
				$spot_id = $GLOBALS['db']->insert_id();
			//图册
			//删除旧图库
			$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."spot_image WHERE spot_id=".$spot_id);
			if(isset($_REQUEST['spot_img'])){
				foreach($_REQUEST['spot_img'] as $k=>$v){
					if($v!=''){
						$spot_image_data = array();
						$spot_image_data['image'] = format_domain_to_relative(strim($v));
						$spot_image_data['spot_id'] = $spot_id;
						$spot_image_data['sort'] = $k;
						$GLOBALS['db']->autoExecute(DB_PREFIX."spot_image",$spot_image_data,"INSERT","","SILENT");
					}
				}
			}
			
			//门票
			//删除无关门票
			$ticket_ids=array();
			$dt_ids = array();
			if(isset($_REQUEST['tickets'])){
				foreach($_REQUEST['tickets'] as $k=>$v){
					$ticket = unserialize(base64_decode($v));
					if(intval($ticket["id"]) > 0){
						$ticket_ids[] = $ticket["id"];
						if($ticket['is_effect']==0)
						{
							$dt_ids[] = $ticket["id"];
						}
					}
				}
			}
			
			if(count($ticket_ids) > 0){
				
				$temp_ids =  $GLOBALS['db']->getAll("SELECT id FROM ".DB_PREFIX."ticket WHERE spot_id=".$spot_id." AND id not in(".implode(",",$ticket_ids).")");
				$t_ids = array();
				foreach($temp_ids as $k=>$v){
					$t_ids[] = $v["id"];
				}
				$dt_ids = array_merge($t_ids,$dt_ids);
				if($t_ids){
					//删除代金券
					$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."voucher_promote WHERE voucher_promote =1 and voucher_rel_id in(".implode(",",$t_ids).")");
				}
				$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."ticket WHERE spot_id=".$spot_id." AND id not in(".implode(",",$ticket_ids).")");
			}
			
			if($dt_ids)
			{
				//删除团购
				$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."tuan where type=2 and rel_id in (".implode(",",$dt_ids).")");
			}
			
			
			
			
			if(isset($_REQUEST['tickets'])){
				foreach($_REQUEST['tickets'] as $k=>$v){
					$ticket = unserialize(base64_decode($v));
					if($ticket['name']!=""){
						$ticket_data = array();
						$t_data = array();
						$ticket_data['spot_id'] = $spot_id;
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
						
						$ticket_data['is_tuan']=intval($ticket['is_tuan']);
						
						$ticket_data['tuan_cate']=intval($ticket['tuan_cate']);
						if(strim($ticket['tuan_begin_time'])!="")
							$ticket_data['tuan_begin_time']=to_timespan($ticket['tuan_begin_time']);
						else
							$ticket_data['tuan_begin_time'] = 0;
							
						if(strim($ticket['tuan_end_time'])!="")
							$ticket_data['tuan_end_time']=to_timespan($ticket['tuan_end_time']);
						else
							$ticket_data['tuan_end_time'] = 0;
							
						$ticket_data['tuan_success_count']=intval($ticket['tuan_success_count']);
						$ticket_data['tuan_is_pre']=intval($ticket['tuan_is_pre']);
						
						if(intval($ticket['id']) >0)
							$GLOBALS['db']->autoExecute(DB_PREFIX."ticket",$ticket_data,"UPDATE","id=".$ticket['id'],"SILENT");
						else
							$GLOBALS['db']->autoExecute(DB_PREFIX."ticket",$ticket_data,"INSERT","","SILENT");
						
						if($GLOBALS['db']->error()==""){
							
							if(intval($ticket['id']) >0)
								$ticket_id = intval($ticket['id']);
							else
								$ticket_id  = $GLOBALS['db']->insert_id();
								
							
							//如果是团购门票
							if($ticket_data['is_tuan']==1&&$ticket_data['is_effect']==1){
								$t_data['type'] = 2;
								$t_data['rel_id'] = $ticket_id;
								$t_data['name'] = $ticket_data['name'];
								$t_data['brief'] = $ticket_data['name_brief'];
								$t_data['origin_price'] =  $ticket_data['origin_price'];
								$t_data['current_price'] =  $ticket_data['current_price'];
								$t_data['sale_price'] =  $ticket_data['sale_price'];
								$t_data['sale_total'] =  $ticket_data['sale_virtual_total'];
								$t_data['image'] = $data['image'];
								$t_data['discount'] =  $t_data['current_price']/$t_data['origin_price'] * 100;
								$t_data['begin_time'] =  $ticket_data['tuan_begin_time'];
								$t_data['end_time'] =  $ticket_data['tuan_end_time'];
								$t_data['is_pre'] =  $ticket_data['tuan_is_pre'];
								$t_data['success_count'] =  $ticket_data['tuan_success_count'];
								$t_data['cate_id'] =  $ticket_data['tuan_cate'];
								$t_data['area_match'] =  $data['area_match'];
								$t_data['place_match'] =  $data['place_match'];
								$t_data['city_match'] =  $data['city_match'];
								$t_data['create_time'] =  NOW_TIME;
								//判断是否已经推送到团这个表
								if($tuan_id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."tuan where type=2 and rel_id=".$ticket_id)){
									$GLOBALS['db']->autoExecute(DB_PREFIX."tuan",$t_data,"UPDATE","type=2 and rel_id=".$ticket_id,"SILENT");
									if($GLOBALS['db']->error()==""){
										$GLOBALS['db']->query("UPDATE ".DB_PREFIX."ticket set tuan_id=$tuan_id WHERE id=$ticket_id");
										save_log($log_info."，团购：".$ticket_data['name'].lang("UPDATE_SUCCESS"),1);
									}
								}
								else{
									$GLOBALS['db']->autoExecute(DB_PREFIX."tuan",$t_data,"INSERT","","SILENT");
									$tuan_id = $GLOBALS['db']->insert_id();
									if($GLOBALS['db']->error()==""){
										$GLOBALS['db']->query("UPDATE ".DB_PREFIX."ticket set tuan_id=$tuan_id WHERE id=$ticket_id");
										save_log($log_info."，团购：".$ticket_data['name'].lang("INSERT_SUCCESS"),1);
									}
								}
							}
							if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."tuan where type=2 and rel_id=".$ticket_id)){
								//删除门票
								$GLOBALS['db']->query("DELETE  FROM ".DB_PREFIX."tuan where type=2 and rel_id=".$ticket_id);
								if($GLOBALS['db']->error()=="")
									save_log($log_info."，团购：".$ticket_data['name'].lang("FOREVER_DELETE_SUCCESS"),1);
							}
							
							//删除返券
							$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."voucher_promote WHERE voucher_promote =1 and voucher_rel_id=".$ticket_id);
							//购物返券
							if(intval($ticket_data['voucher']) > 0){
								$review_voucher['voucher_type_id'] = $ticket_data['voucher'];
								$review_voucher['voucher_promote'] = 1;
								$review_voucher['voucher_rel_id'] = $ticket_id;
								$review_voucher['voucher_promote_type'] = 1;
								$GLOBALS['db']->autoExecute(DB_PREFIX."voucher_promote",$review_voucher,"INSERT","","SILENT");
							}
							//评论返券
							if(intval($ticket_data['review_voucher']) > 0){
								$review_voucher['voucher_type_id'] = $ticket_data['review_voucher'];
								$review_voucher['voucher_promote'] = 1;
								$review_voucher['voucher_rel_id'] = $ticket_id;
								$review_voucher['voucher_promote_type'] = 2;
								$GLOBALS['db']->autoExecute(DB_PREFIX."voucher_promote",$review_voucher,"INSERT","","SILENT");
							}
							save_log($log_info."，门票：".$ticket_data['name'].lang("INSERT_SUCCESS"),1);
						}
					}
				}
			}
			require APP_ROOT_PATH."system/libs/spot.php";
			//更新门票冗余信息
			update_spot_ticket($spot_id);
			//同步团购信息
			$t_ids = $GLOBALS['db']->getAll("SELECT t.id FROM ".DB_PREFIX."tuan t LEFT JOIN ".DB_PREFIX."ticket tt ON t.rel_id = tt.id  where t.type=2 and tt.spot_id=".$spot_id);
			if($t_ids){
				foreach($t_ids as $k=>$v){
					$tt_ids[] =$v['id'];
				}
				$tt_data['image'] = $data['image'];
				$tt_data['area_match'] =  $data['area_match'];
				$tt_data['place_match'] =  $data['place_match'];
				$tt_data['city_match'] =  $data['city_match'];
				
				$GLOBALS['db']->autoExecute(DB_PREFIX."tuan",$tt_data,"UPDATE","type=2 and id in(".implode(",",$tt_ids).")","SILENT");
				
			}
			
			//删除商户提交的景点门票
			$sql = "delete from ".DB_PREFIX."spot_supplier where id =".$id." ";
			$GLOBALS['db']->query($sql);			
			//成功提示
			save_log($log_info.lang("PUBLISH_SUCCESS"),1);
			showSuccess(lang("PUBLISH_SUCCESS"),$ajax,admin_url("spot#index"));
		} else {
			//错误提示
			showErr(lang("PUBLISH_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	
	}
    
}
?>