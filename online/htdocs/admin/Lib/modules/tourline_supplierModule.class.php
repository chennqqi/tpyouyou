<?php



class tourline_supplierModule extends AuthModule

{

    function index() {

    	$param = array();		

		//条件

		$condition = " 1=1 ";

		if(isset($_REQUEST['name']))

			$name_key = strim($_REQUEST['name']);

		else

			$name_key = "";

		$param['name'] = $name_key;

		if($name_key!='')

		{

			$condition.=" and name like '%".$name_key."%' ";

		}

		

    	if(isset($_REQUEST['start_city_city_id']))

		{

			$start_city_city_id = intval($_REQUEST['start_city_city_id']);

			$start_city_name = strim($_REQUEST['start_city_name']);

		}

		else

		{

			$start_city_city_id = 0;

			$start_city_name='';

		}

		$param['start_city_city_id'] = $start_city_city_id;

		$param['start_city_name'] = $start_city_name;

   	     if($start_city_city_id >0)

		{

			$condition.=" and city_id = ".$start_city_city_id." ";

		}

		

    	if(isset($_REQUEST['tour_type']))

			$tour_type = strim($_REQUEST['tour_type']);

		else

			$tour_type = 0;

		$param['tour_type'] = $tour_type;

		if($tour_type >0)

		{

			$condition.=" and tour_type = ".$tour_type." ";

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

		

		$totalCount = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."tourline_supplier where ".$condition);

		if($totalCount)

		{

			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tourline_supplier where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);

			foreach($list as $k=>$v)

			{

				$list[$k]['supplier_company']=$GLOBALS['db']->getOne("select company_name from ".DB_PREFIX."supplier where id=".$v['supplier_id']);

				$list[$k]['city_name']=$GLOBALS['db']->getOne("select name from ".DB_PREFIX."tour_city where id=".$v['city_id']);

				if($v['tour_type'] ==3)

					$list[$k]['type_value']='自驾游';

				elseif($v['tour_type'] ==2)

					$list[$k]['type_value']='自助游';

				else

					$list[$k]['type_value']='跟团游';

					

				$list[$k]['preview_url'] = url("tours#view",array("sid"=>$v['id'],"preview"=>1));

				

			}

		}

		$GLOBALS['tmpl']->assign('list',$list);

		$GLOBALS['tmpl']->assign('totalCount',$totalCount);

		$GLOBALS['tmpl']->assign('param',$param);

		

		

		$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_supplier"));

		$GLOBALS['tmpl']->assign("setsorturl",admin_url("tourline_supplier#set_sort",array("ajax"=>1)));

		$GLOBALS['tmpl']->assign("delurl",admin_url("tourline_supplier#foreverdelete",array('ajax'=>1)));

		$GLOBALS['tmpl']->assign("searchstartcityurl",admin_url("tour_city#search_city_radio"),array("ajax"=>1));

    	$GLOBALS['tmpl']->assign("searchsupplierurl",admin_url("supplier#search_supplier",array("ajax"=>1)));

		$GLOBALS['tmpl']->assign("editurl",admin_url("tourline_supplier#edit"));

		$GLOBALS['tmpl']->assign("addurl",admin_url("tourline#add"));

		$GLOBALS['tmpl']->display("core/tourline_supplier/index.html");

    }

    

	public function edit() {		

		$id = intval($_REQUEST ['id']);

		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_supplier where id = ".$id);

		

		//city_name

		$vo['city_name'] = $GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."tour_city where id = ".$vo['city_id']);

	

		$vo['city_match'] = unformat_fulltext_key($vo['city_match']);

		$vo['province_match'] = unformat_fulltext_key($vo['province_match']);

		$vo['area_match'] = unformat_fulltext_key($vo['area_match']);

		$vo['place_match'] = unformat_fulltext_key($vo['place_match']);

		$vo['around_city_match'] = unformat_fulltext_key($vo['around_city_match']);

		

		

		$insurance_cfg=unserialize($vo['insurance_cfg']);

		if($insurance_cfg)

		{

			$vo['insurance_ids']=$insurance_cfg['insurance_ids'];

			$vo['insurance_names']=$insurance_cfg['insurance_names'];

		}

		else

		{

			$vo['insurance_ids']='';

			$vo['insurance_names']='';

		}

		

		

		//商家

		if($vo['supplier_id'] >0)

			$vo['company_name'] = $GLOBALS['db']->getOne("select `company_name` from ".DB_PREFIX."supplier where id = ".$vo['supplier_id']);



		$GLOBALS['tmpl']->assign ( 'vo', $vo );

		

		/*新增保险*/

		if($vo['other_insurance_cfg'])

		{

			$other_insurance_cfg=unserialize($vo['other_insurance_cfg']);

			foreach($other_insurance_cfg as $k=>$v)

			{

				$other_insurance=unserialize(urldecode($v));

				$other_insurance['new_insurance_data']=$v;

				$other_insurance_cfg[$k]=$other_insurance;

			}

		}

		else

			$other_insurance_cfg=array();

		

		$GLOBALS['tmpl']->assign ( 'other_insurance_cfg', $other_insurance_cfg );

		

		//时间价格

		if($vo['tourline_item_cfg'])

		{

			$tourline_items=unserialize($vo['tourline_item_cfg']);

			foreach($tourline_items as $k=>$v)

			{

				$tourline_item=unserialize(urldecode($v));

				$tourline_item['tourline_item_data']=$v;

				$tourline_items[$k]=$tourline_item;

			}

		}

		else

			$tourline_items=array();

		$GLOBALS['tmpl']->assign ( 'tourline_items', $tourline_items );

		

		$GLOBALS['tmpl']->assign("searchstartcityurl",admin_url("tour_city#search_city_radio"),array("ajax"=>1));

		$GLOBALS['tmpl']->assign("searchcityurl",admin_url("tour_city#search_city"),array("ajax"=>1));

		$GLOBALS['tmpl']->assign("searchareaurl",admin_url("tour_area#search_area"),array("ajax"=>1));

		$GLOBALS['tmpl']->assign("searchplaceurl",admin_url("tour_place#search_place"),array("ajax"=>1));

    	$GLOBALS['tmpl']->assign("searchinsurance",admin_url("tourline_insurance#search_insurance",array("ajax"=>1)));

    	$GLOBALS['tmpl']->assign("edit_new_insurance",admin_url("tourline_new_insurance#edit",array("ajax"=>1)));

    	$GLOBALS['tmpl']->assign("add_new_insurance",admin_url("tourline_new_insurance#add",array("ajax"=>1)));

    	$GLOBALS['tmpl']->assign("searchtagurl",admin_url("tour_place_tag#search_tag"),array("ajax"=>1));

    	$GLOBALS['tmpl']->assign("searchprovinceurl",admin_url("tour_province#search_province"),array("ajax"=>1));

    	

    	$tuan_cates = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."tuan_cate ORDER BY sort DESC");

    	$GLOBALS['tmpl']->assign("tuan_cates",$tuan_cates);

    	

		$GLOBALS['tmpl']->assign("additem",admin_url("tourline_item#add",array("ajax"=>1,"tourline_id"=>$vo['rel_id'],"is_supplier_submit"=>1)));

    	$GLOBALS['tmpl']->assign("edititem",admin_url("tourline_item#edit",array("ajax"=>1,"tourline_id"=>$vo['rel_id'],"is_supplier_submit"=>1)));

		

		$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_supplier#update",array("ajax"=>1)));

		

		$GLOBALS['tmpl']->display("core/tourline_supplier/edit.html");

	}



	

	public function update() {

		$ajax = intval($_REQUEST['ajax']);

		if(intval($_REQUEST['id']) == 0){

			showErr("未找到审核数据！",$ajax);

		}

		if(!check_empty("name"))

		{

			showErr(lang("TOURLINE_NAME_EMPTY"),$ajax);

		}

		
		

		if(!check_empty("start_city_city_id") || intval($_REQUEST['start_city_city_id']) == 0 )

		{

			showErr("请选择出发城市",$ajax);

		}

		

		if(!check_empty("tour_city_name") && intval($_REQUEST['show_all_city']) ==0)

		{

			showErr("请选择允许显示的城市",$ajax);

		}

		

		if(!check_empty("tour_area_name"))

		{

			showErr("请选择大区域",$ajax);

		}

		

		if(isset($_REQUEST['tourline_items']))

		{

			$is_forever_count=0;

			$item_start_time_array=array();

			foreach($_REQUEST['tourline_items'] as $k=>$v)

			{

				$tourline_item=unserialize(urldecode($v));

				

				if( strim($tourline_item['start_time']) =='' && intval($tourline_item['is_forever']) !=1){

	    			showErr("增加非永久有效的时间价格，出游时间不能为空",$ajax);

	    		}

				

				if(strim($tourline_item['start_time'])=='1970-01-01' && intval($tourline_item['is_forever']) !=1 )

		    	{

		    		showErr("非永久有效出游信息，出发时间不能是：1970-01-01",$ajax);

		    	}

	    		

				if(intval($tourline_item['is_forever']) ==1)

				{

					$is_forever_count +=1;

					if($is_forever_count >1)

						showErr(lang("IS_FOREVER_NOTICE_ONE"),$ajax);

				}

				

				if(in_array($tourline_item['start_time'],$item_start_time_array))

					showErr("有相同的出发时间:".$tourline_item['start_time'],$ajax);

				

				$item_start_time_array[]=$tourline_item['start_time'];	

				

			}

		}

		

		$id = intval($_REQUEST['id']);

		$tourline_supplier=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_supplier where id=".$id);

		if(!$tourline_supplier)

		{

			showErr("未找到审核数据！",$ajax);

		}

		$data = array();

		/*基本配置*/

		$data['supplier_id'] = $tourline_supplier['supplier_id'];

		$data['name'] = strim($_REQUEST['name']);

		$data['short_name'] = strim($_REQUEST['short_name']);

		$data['tour_type'] = intval($_REQUEST['tour_type']);

		$data['tour_range'] = intval($_REQUEST['tour_range']);

		$data['is_namelist'] = intval($_REQUEST['is_namelist']);

		$data['order_confirm_type'] = intval($_REQUEST['order_confirm_type']);

		$data['tour_total_day'] = intval($_REQUEST['tour_total_day']);

		if(isset($_REQUEST['image'])){

			$data['image'] = format_domain_to_relative(strim($_REQUEST['image']));

		}

		else{

			$data['image'] ="";

		}

		$data['city_id'] =intval($_REQUEST['start_city_city_id']);

		

		if(intval($_REQUEST['show_all_city']) == 1){

			$city_info = $GLOBALS['db']->getRow("SELECT GROUP_CONCAT(`name`) AS tour_city_name,GROUP_CONCAT(`py`) AS tour_city_py FROM ".DB_PREFIX."tour_city ORDER BY py_first ASC");

			$data['city_match'] = format_fulltext_key($city_info['tour_city_py']);

			$data['city_match_row'] = $city_info['tour_city_name'];

		}

		else{

			$data['city_match'] = format_fulltext_key(strim($_REQUEST['tour_city_py']));

			$data['city_match_row'] = strim($_REQUEST['tour_city_name']);

		}

		

		$data['around_city_match'] = format_fulltext_key(strim($_REQUEST['around_city_py']));

		$data['around_city_match_row'] = strim($_REQUEST['around_city_name']);

		

		$data['province_match'] = format_fulltext_key(strim($_REQUEST['tour_province_py']));

		$data['province_match_row'] = strim($_REQUEST['tour_province_name']);

		

		$data['area_match'] = format_fulltext_key(strim($_REQUEST['tour_area_py']));

		$data['area_match_row'] = strim($_REQUEST['tour_area_name']);

		

		$data['place_match'] = format_fulltext_key(strim($_REQUEST['tour_place_py']));

		$data['place_match_row'] = strim($_REQUEST['tour_place_name']);

		

		$data['tag_match'] = str_to_unicode_string_depart(strim($_REQUEST['tour_place_tag_tag_name']));

		$data['tag_match_row'] = strim($_REQUEST['tour_place_tag_tag_name']);

		

		$data['brief'] = strim($_REQUEST["brief"]);

		$data['tour_desc'] = format_domain_to_relative(btrim($_REQUEST["tour_desc"]));

		$data['appoint_desc'] = format_domain_to_relative(btrim($_REQUEST['appoint_desc']));

		

		/*相关内容*/

		$data['tour_desc_1_name'] = strim($_REQUEST['tour_desc_1_name']);

		$data['tour_desc_2_name'] = strim($_REQUEST['tour_desc_2_name']);

		$data['tour_desc_3_name'] = strim($_REQUEST['tour_desc_3_name']);

		$data['tour_desc_4_name'] = strim($_REQUEST['tour_desc_4_name']);

		$data['tour_desc_1'] = format_domain_to_relative(btrim($_REQUEST['tour_desc_1']));

		$data['tour_desc_2'] = format_domain_to_relative(btrim($_REQUEST['tour_desc_2']));

		$data['tour_desc_3'] = format_domain_to_relative(btrim($_REQUEST['tour_desc_3']));

		$data['tour_desc_4'] = format_domain_to_relative(btrim($_REQUEST['tour_desc_4']));

		

		/*时间价格数量*/

		$data['origin_price']= format_price_to_db($_REQUEST['origin_price']);

		$data['price']= format_price_to_db($_REQUEST['price']);

		$data['price_explain'] = strim($_REQUEST["price_explain"]);

		$data['child_norm'] = strim($_REQUEST["child_norm"]);

		$data['advance_day'] = intval($_REQUEST["advance_day"]);

		

		/*退改配置*/

		$data['is_refund'] = intval($_REQUEST['is_refund']);

		$data['refund_desc'] = strim($_REQUEST["refund_desc"]);

		$data['is_expire_refund]'] = intval($_REQUEST["is_expire_refund]"]);

		

		/*团购配置*/

		$data['is_tuan'] = intval($_REQUEST['is_tuan']);

		if($data['is_tuan'] ==1)

		{

			$data['tuan_cate'] = intval($_REQUEST['tuan_cate']);

			if(strim($_REQUEST['tuan_begin_time'])!="")

				$data['tuan_begin_time']=to_timespan(strim($_REQUEST['tuan_begin_time']));

			else

				$data['tuan_begin_time'] = 0;

			if(strim($_REQUEST['tuan_end_time'])!="")

				$data['tuan_end_time']=to_timespan(strim($_REQUEST['tuan_end_time']));

			else

				$data['tuan_end_time'] = 0;

			$data['tuan_success_count']=intval($_REQUEST['tuan_success_count']);

			$data['tuan_is_pre']=intval($_REQUEST['tuan_is_pre']);

		}else

		{

			$data['tuan_cate'] = 0;

			$data['tuan_begin_time'] =0;

			$data['tuan_end_time'] = 0;

			$data['tuan_success_count'] =0;

			$data['tuan_is_pre']=0;

		}

		

		/*签证配置*/

		$data['is_visa'] = intval($_REQUEST['is_visa']);

		$data['visa_name'] = strim($_REQUEST["visa_name"]);

		$data['visa_price'] = format_price_to_db($_REQUEST["visa_price"]);

		$data['visa_brief'] = format_domain_to_relative($_REQUEST["visa_brief"]);

		

		

		$log_info = $data['name'];

		if($tourline_supplier['rel_id'] >0 )

		{

			$GLOBALS['db']->autoExecute(DB_PREFIX."tourline",$data,"UPDATE"," id=".$tourline_supplier['rel_id'],"SILENT");

			if ($GLOBALS['db']->error()=="")

			{

				$tourline_id = $tourline_supplier['rel_id'];

				

				//删除无关时间价格

				$tourline_item_ids=array();

				if(isset($_REQUEST['tourline_items'])){

					foreach($_REQUEST['tourline_items'] as $k=>$v){

						$tourline_item = unserialize(urldecode($v));

						if(intval($tourline_item["id"]) > 0){

							$tourline_item_ids[] = $tourline_item["id"];

						}

					}

				}

	

				if($tourline_item_ids)

					$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."tourline_item WHERE tourline_id=".$tourline_id." AND id not in(".implode(",",$tourline_item_ids).")");

				else

					$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."tourline_item WHERE tourline_id=".$tourline_id." ");

				

				//添加时间价格

				if(isset($_REQUEST['tourline_items']))

				{

					foreach($_REQUEST['tourline_items'] as $k=>$v)

					{

						$tourline_item=unserialize(urldecode($v));

						if($tourline_item['start_time'] !="" && intval($tourline_item['id']) ==0)

						{

							$tourline_item_data['is_forever'] =intval($tourline_item['is_forever']);

							if($tourline_item_data['is_forever'] == 1)

								$tourline_item_data['start_time']=to_date(to_timespan('1970-01-01'),"Y-m-d");

							else	

							$tourline_item_data['start_time'] =to_date(to_timespan(trim($tourline_item['start_time'])),"Y-m-d");

							

							$tourline_item_data['adult_price'] =format_price_to_db($tourline_item['adult_price']);

							$tourline_item_data['child_price'] =format_price_to_db($tourline_item['child_price']);

							$tourline_item_data['adult_sale_price']= format_price_to_db($tourline_item['adult_sale_price']);

							$tourline_item_data['child_sale_price']= format_price_to_db($tourline_item['child_sale_price']);

							$tourline_item_data['adult_limit'] =intval($tourline_item['adult_limit']);

							$tourline_item_data['adult_buy_min'] =intval($tourline_item['adult_buy_min']);

							$tourline_item_data['adult_buy_max'] =intval($tourline_item['adult_buy_max']);

							$tourline_item_data['child_limit'] =intval($tourline_item['child_limit']);

							$tourline_item_data['child_buy_min'] =intval($tourline_item['child_buy_min']);

							$tourline_item_data['child_buy_max'] =intval($tourline_item['child_buy_max']);

							$tourline_item_data['brief'] =strim($tourline_item['brief']);

							

							$tourline_item_data['tourline_id']=$tourline_id;

				

							$GLOBALS['db']->autoExecute(DB_PREFIX."tourline_item",$tourline_item_data,"INSERT","");

							if($GLOBALS['db']->error()!="")

							{

								showErr("更新失败，已有相同日期的线路",1);

							}

						}

						

					}

				}

				

				//同步团购信息

				$t_ids = $GLOBALS['db']->getOne("select Group_concat(id) FROM ".DB_PREFIX."tuan where rel_id=".$tourline_id." and type=1 ");

				if($data['is_tuan'] ==1){

					$tt_data=array();

					if($t_ids)

					{

						$t_data['type'] = 1;

						$t_data['rel_id'] = $tourline_id;

						$t_data['name'] = $data['name'];

						$t_data['origin_price'] =  $data['origin_price'];

						$t_data['current_price'] =  $data['price'];

						$t_data['sale_price'] =  $data['price'];

						$t_data['sale_total'] =  $data['sale_virtual_total'];

						$t_data['image'] = $data['image'];

						$t_data['brief'] =  $data['brief'];

						$t_data['discount'] =  $data['current_price']/$data['origin_price'] * 100;

						$t_data['begin_time'] =  $data['tuan_begin_time'];

						$t_data['end_time'] =  $data['tuan_end_time'];

						$t_data['is_pre'] =  $data['tuan_is_pre'];

						$t_data['success_count'] =  $data['tuan_success_count'];

						$t_data['cate_id'] =$data['tuan_cate'];

						$t_data['area_match'] =  $data['area_match'];

						$t_data['place_match'] =  $data['place_match'];

						$t_data['city_match'] =  $data['city_match'];

						$GLOBALS['db']->autoExecute(DB_PREFIX."tuan",$tt_data,"UPDATE","type=1 and id in(".$t_ids.")","SILENT");

					}

					else

					{

						$t_data['type'] = 1;

						$t_data['rel_id'] = $tourline_id;

						$t_data['name'] = $data['name'];

						$t_data['origin_price'] =  $data['origin_price'];

						$t_data['current_price'] =  $data['price'];

						$t_data['sale_price'] =  $data['price'];

						$t_data['sale_total'] =  $data['sale_virtual_total'];

						$t_data['image'] = $data['image'];

						$t_data['brief'] =  $data['brief'];

						$t_data['discount'] =  $data['current_price']/$data['origin_price'] * 100;

						$t_data['begin_time'] =  $data['tuan_begin_time'];

						$t_data['end_time'] =  $data['tuan_end_time'];

						$t_data['is_pre'] =  $data['tuan_is_pre'];

						$t_data['success_count'] =  $data['tuan_success_count'];

						$t_data['cate_id'] =$data['tuan_cate'];

						$t_data['area_match'] =  $data['area_match'];

						$t_data['place_match'] =  $data['place_match'];

						$t_data['city_match'] =  $data['city_match'];

						$t_data['create_time'] =  NOW_TIME;

						$GLOBALS['db']->autoExecute(DB_PREFIX."tuan",$t_data,"INSERT","","SILENT");

						if($GLOBALS['db']->error()==""){

							save_log($log_info."，团购：".$data['name'].lang("INSERT_SUCCESS"),1);

						}

					}

				}

				else

				{

					if($t_ids)

					{

						$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."tuan WHERE type=1 and rel_id=".$tourline_id." AND id not in(".$t_ids.")");

					}

				}

				//删除线路关联保险

				$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."tourline_insurance_link WHERE tourline_id=".$tourline_id." ");

				/*商家新增保险*/

					if(isset($_POST['new_insurances'])){

						$new_insurance_ids=array();

						foreach($_POST['new_insurances'] as $k=>$v)

						{

							$new_insurance=unserialize(urldecode($v));

							$new_insurance_data['name']=strim($new_insurance['name']);

							$new_insurance_data['price']=format_price_to_db($new_insurance['price']);

							$new_insurance_data['insurance_file']=format_domain_to_relative(strim($new_insurance['insurance_file']));

							$new_insurance_data['insurance_brief']=strim($new_insurance['insurance_brief']);

							$new_insurance_data['insurance_info']=format_domain_to_relative(btrim($new_insurance['insurance_info']));

							$GLOBALS['db']->autoExecute(DB_PREFIX."insurance",$new_insurance_data,"INSERT","","SILENT");

							if($GLOBALS['db']->error()==""){

								$i_data=array();

								$i_data['insurance_id']=$GLOBALS['db']->insert_id();

								$i_data['tourline_id']=$tourline_id;

								$GLOBALS['db']->autoExecute(DB_PREFIX."tourline_insurance_link",$i_data,"INSERT","","SILENT");

							}

						}

						

					}

				

				//增加关联保险

				$insurance_ids=strim($_REQUEST['insurance_id']);

				if($insurance_ids !='')

				{

					$insurances= $GLOBALS['db']->getAll("select id from ".DB_PREFIX."insurance where id in(".$insurance_ids.")");

					foreach($insurances as $k=>$v)

					{

						$i_data=array();

						$i_data['insurance_id']=$v['id'];

						$i_data['tourline_id']=$tourline_id;

						$GLOBALS['db']->autoExecute(DB_PREFIX."tourline_insurance_link",$i_data,"INSERT","","SILENT");

					}

				}

				

				//删除商户提交的景点门票

				$sql = "delete from ".DB_PREFIX."tourline_supplier where id =".$id." ";

				$GLOBALS['db']->query($sql);			

				//成功提示

				save_log($log_info.lang("UPDATE_SUCCESS"),1);

				showSuccess("审核成功",$ajax,admin_url("tourline#edit",array('id'=>$tourline_id)));

			}else{

				showSuccess("审核失败",$ajax);

			}																					

		}

		else

		{

			$data['is_effect'] = 1;

			$GLOBALS['db']->autoExecute(DB_PREFIX."tourline",$data,"INSERT","","SILENT");

			if ($GLOBALS['db']->error()=="") {	

				$tourline_id = $GLOBALS['db']->insert_id();

				

				//时间价格

				if(isset($_REQUEST['tourline_items']))

				{

					foreach($_REQUEST['tourline_items'] as $k=>$v)

					{

						$tourline_item=unserialize(urldecode($v));

						if($tourline_item['start_time'] !="")

						{

							$tourline_item_data['start_time'] =to_date(to_timespan(trim($tourline_item['start_time'])),"Y-m-d");

							$tourline_item_data['adult_price'] =format_price_to_db($tourline_item['adult_price']);

							$tourline_item_data['child_price'] =format_price_to_db($tourline_item['child_price']);

							$tourline_item_data['adult_sale_price']= format_price_to_db($tourline_item['adult_sale_price']);

							$tourline_item_data['child_sale_price']= format_price_to_db($tourline_item['child_sale_price']);

							$tourline_item_data['adult_limit'] =intval($tourline_item['adult_limit']);

							$tourline_item_data['adult_buy_min'] =intval($tourline_item['adult_buy_min']);

							$tourline_item_data['adult_buy_max'] =intval($tourline_item['adult_buy_max']);

							$tourline_item_data['child_limit'] =intval($tourline_item['child_limit']);

							$tourline_item_data['child_buy_min'] =intval($tourline_item['child_buy_min']);

							$tourline_item_data['child_buy_max'] =intval($tourline_item['child_buy_max']);

							$tourline_item_data['brief'] =strim($tourline_item['brief']);

							$tourline_item_data['is_forever'] =intval($tourline_item['is_forever']);

							

							$tourline_item_data['tourline_id']=$tourline_id;

							$GLOBALS['db']->autoExecute(DB_PREFIX."tourline_item",$tourline_item_data,"INSERT","");

						}

					}

				}

				

				//如果是团购线路

				if($data['is_tuan'] ==1)

				{

					$t_data['type'] = 1;

					$t_data['rel_id'] = $tourline_id;

					$t_data['name'] = $data['name'];

					$t_data['origin_price'] =  $data['origin_price'];

					$t_data['current_price'] =  $data['price'];

					$t_data['sale_price'] =  $data['price'];

					$t_data['sale_total'] =  $data['sale_virtual_total'];

					$t_data['image'] = $data['image'];

					$t_data['brief'] =  $data['brief'];

					$t_data['discount'] =  $data['current_price']/$data['origin_price'] * 100;

					$t_data['begin_time'] =  $data['tuan_begin_time'];

					$t_data['end_time'] =  $data['tuan_end_time'];

					$t_data['is_pre'] =  $data['tuan_is_pre'];

					$t_data['success_count'] =  $data['tuan_success_count'];

					$t_data['cate_id'] =$data['tuan_cate'];

					$t_data['area_match'] =  $data['area_match'];

					$t_data['place_match'] =  $data['place_match'];

					$t_data['city_match'] =  $data['city_match'];

					$t_data['create_time'] =  NOW_TIME;

					$GLOBALS['db']->autoExecute(DB_PREFIX."tuan",$t_data,"INSERT","","SILENT");

					if($GLOBALS['db']->error()==""){

						save_log($log_info."，团购：".$data['name'].lang("INSERT_SUCCESS"),1);

					}

				}

			

				/*商家新增保险*/

				if(isset($_POST['new_insurances'])){

					$new_insurance_ids=array();

					foreach($_POST['new_insurances'] as $k=>$v)

					{

						$new_insurance=unserialize(urldecode($v));

						$new_insurance_data['name']=strim($new_insurance['name']);

						$new_insurance_data['price']=format_price_to_db($new_insurance['price']);

						$new_insurance_data['insurance_file']=format_domain_to_relative(strim($new_insurance['insurance_file']));

						$new_insurance_data['insurance_brief']=strim($new_insurance['insurance_brief']);

						$new_insurance_data['insurance_info']=format_domain_to_relative(btrim($new_insurance['insurance_info']));

						$GLOBALS['db']->autoExecute(DB_PREFIX."insurance",$new_insurance_data,"INSERT","","SILENT");

						if($GLOBALS['db']->error()==""){

							$i_data=array();

							$i_data['insurance_id']=$GLOBALS['db']->insert_id();

							$i_data['tourline_id']=$tourline_id;

							$GLOBALS['db']->autoExecute(DB_PREFIX."tourline_insurance_link",$i_data,"INSERT","","SILENT");

						}

					}

					

				}

			

				//保险关联

				$insurance_ids=strim($_REQUEST['insurance_id']);

				if($insurance_ids !='')

				{

					$insurances= $GLOBALS['db']->getAll("select id from ".DB_PREFIX."insurance where id in(".$insurance_ids.")");

					foreach($insurances as $k=>$v)

					{

						$i_data=array();

						$i_data['insurance_id']=$v['id'];

						$i_data['tourline_id']=$tourline_id;

						$GLOBALS['db']->autoExecute(DB_PREFIX."tourline_insurance_link",$i_data,"INSERT","","SILENT");

					}

				}

				

				//删除商户提交的景点门票

				$sql = "delete from ".DB_PREFIX."tourline_supplier where id =".$id." ";

				$GLOBALS['db']->query($sql);			

				//成功提示

				save_log($log_info.lang("INSERT_SUCCESS"),1);

				showSuccess("审核成功",$ajax,admin_url("tourline#edit",array('id'=>$tourline_id)));

			}else{

				showSuccess("审核失败",$ajax);

			}	

			

		}

		

	}

	

	public function search_insurance()

	{

		//处理保存下来的已选数据

		$this->assign_lookup_fields("id");

	

		$param = array();

		//条件

		$condition = " supplier_id=0 or supplier_id=".$this->supplier_id." ";

		if(isset($_REQUEST['name']))

			$name_key = strim($_REQUEST['name']);

		else

			$name_key = "";

		$param['name'] = $name_key;

		if($name_key!='')

		{

			$condition.=" and name like '%".$name_key."%' ";

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

	

	

		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."insurance where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);

		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."insurance where ".$condition);

	

		foreach($list as $k=>$v)

		{

			

		}

		$GLOBALS['tmpl']->assign('list',$list);

		$GLOBALS['tmpl']->assign('totalCount',$totalCount);

		$GLOBALS['tmpl']->assign('param',$param);

	

		$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_insurance#search_insurance"));

		$GLOBALS['tmpl']->display("core/tourline_supplier/search_insurance.html");

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

				$sql = "delete from ".DB_PREFIX."tourline_supplier where id in (".$id.") ";

				$GLOBALS['db']->query($sql);				

				

				showSuccess(lang("FOREVER_DELETE_SUCCESS"),$ajax);				

			}

			else

			{

				showErr(lang("INVALID_OPERATION"),$ajax);

			}			

		}

		else

		{

			showErr(lang("INVALID_OPERATION"),$ajax);

		}



	}

	

	public function set_sort()

	{

		$ajax = intval($_REQUEST['ajax']);

		$sort = intval($_REQUEST['sort']);

		$id = intval($_REQUEST['id']);

		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline where id = ".$id);

		if($data)

		{

			$GLOBALS['db']->query("update ".DB_PREFIX."tourline set sort = ".$sort." where id = ".$id);

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

	

}

?>