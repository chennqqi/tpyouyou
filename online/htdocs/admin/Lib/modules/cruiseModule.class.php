<?php



class cruiseModule extends AuthModule

{

  function index() {

    $param = array();	
		//条件

		$condition = " 1 = 1 and is_cruise = 1 ";

		if(isset($_REQUEST['name']))

			$name_key = strim($_REQUEST['name']);

		else

			$name_key = "";

		$param['name'] = $name_key;

		if($name_key!='')

		{

			$condition.=" and name like '%".$name_key."%' ";

		}
		

		if(isset($_REQUEST['supplier_id']))

		{

			$supplier_id = intval($_REQUEST['supplier_id']);

			$supplier_user_name = strim($_REQUEST['supplier_user_name']);

		}

		else

		{

			$supplier_id = 0;

			$supplier_user_nam='';

		}

		$param['supplier_id'] = $supplier_id;

		$param['supplier_user_name'] = $supplier_user_name;

   	     if($supplier_id >0)

		{

			$condition.=" and supplier_id = ".$supplier_id." ";

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

		

		//商家

		//$vo['company_name'] = $GLOBALS['db']->getOne("select `company_name` from ".DB_PREFIX."supplier where id = ".$vo['supplier_id']);

		

		//city_name

		//$vo['city_name'] = $GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."tour_city where id = ".$vo['city_id']);

		

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

		

		

		$totalCount = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."tourline where ".$condition);

		if($totalCount)

		{

			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tourline where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);

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

					$list[$k]['preview_url'] = url("tours#view",array("id"=>$v['id'],"preview"=>1));

				

			}

		}

		$GLOBALS['tmpl']->assign('list',$list);

		$GLOBALS['tmpl']->assign('totalCount',$totalCount);

		$GLOBALS['tmpl']->assign('param',$param);

		

		$GLOBALS['tmpl']->assign("verifycodelisturl",admin_url("tourline#verify_code_list"));

		$GLOBALS['tmpl']->assign("statisticsurl",admin_url("tourline#statistics"));

		

		$GLOBALS['tmpl']->assign("formaction",admin_url("cruise"));

		$GLOBALS['tmpl']->assign("setsorturl",admin_url("cruise#set_sort",array("ajax"=>1)));

		$GLOBALS['tmpl']->assign("delurl",admin_url("cruise#foreverdelete",array('ajax'=>1)));

		$GLOBALS['tmpl']->assign("seteffecturl",admin_url("cruise#set_effect",array("ajax"=>1)));

		$GLOBALS['tmpl']->assign("searchstartcityurl",admin_url("tour_city#search_city_radio"),array("ajax"=>1));

    	$GLOBALS['tmpl']->assign("searchsupplierurl",admin_url("supplier#search_supplier",array("ajax"=>1)));	

		$GLOBALS['tmpl']->assign("editurl",admin_url("cruise#edit"));

		$GLOBALS['tmpl']->assign("addurl",admin_url("cruise#add"));

		$GLOBALS['tmpl']->display("core/tourline/index.html");

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

				$del_name = $GLOBALS['db']->getOne("select group_concat(name) from ".DB_PREFIX."tourline where id in (".$id.")");			

				$sql = "delete from ".DB_PREFIX."tourline where id in (".$id.")";

				$GLOBALS['db']->query($sql);	

				if($GLOBALS['db']->affected_rows()>0)

				{	

					//删除团购

					$sql = "delete from ".DB_PREFIX."tuan where type=1 and rel_id in (".$id.")";

					$GLOBALS['db']->query($sql);

					//删除时间价格

					$sql = "delete from ".DB_PREFIX."tourline_item where tourline_id in (".$id.")";

					$GLOBALS['db']->query($sql);

					

					//删除代金关联表

					$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."voucher_promote WHERE voucher_rel_id in (".$id.") AND voucher_promote=3");

					//删除保险关联表

					$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."tourline_insurance_link WHERE tourline_id in (".$id.") ");

					

					save_log(lang("DEL").":".$del_name, 1);

				}

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

	public function add()

	{	

		$sort = $GLOBALS['db']->getOne("select max(sort) from ".DB_PREFIX."tourline")+1;	

		$GLOBALS['tmpl']->assign("sort",$sort);

		$GLOBALS['tmpl']->assign("searchstartcityurl",admin_url("tour_city#search_city_radio"),array("ajax"=>1));

		$GLOBALS['tmpl']->assign("searchcityurl",admin_url("tour_city#search_city"),array("ajax"=>1));

		$GLOBALS['tmpl']->assign("searchareaurl",admin_url("tour_area#search_area"),array("ajax"=>1));

		$GLOBALS['tmpl']->assign("searchplaceurl",admin_url("tour_place#search_place"),array("ajax"=>1));

    	$GLOBALS['tmpl']->assign("searchsupplierurl",admin_url("supplier#search_supplier",array("ajax"=>1)));

    	$GLOBALS['tmpl']->assign("searchinsurance",admin_url("tourline_insurance#search_insurance",array("ajax"=>1)));

    	$GLOBALS['tmpl']->assign("searchtagurl",admin_url("tour_place_tag#search_tag"),array("ajax"=>1));

    	$GLOBALS['tmpl']->assign("searchprovinceurl",admin_url("tour_province#search_province"),array("ajax"=>1));
    	

    	$tuan_cates = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."tuan_cate ORDER BY sort DESC");

    	$GLOBALS['tmpl']->assign("tuan_cates",$tuan_cates);

    	

    	$voucher_type = $GLOBALS['db']->getAll("select id,voucher_name from ".DB_PREFIX."voucher_type where deliver_type=3 and is_effect=1 ORDER BY sort DESC");

    	$GLOBALS['tmpl']->assign("voucher_type",$voucher_type);

    	

		$GLOBALS['tmpl']->assign("additem",admin_url("tourline_item#add"),array("ajax"=>1));

    	$GLOBALS['tmpl']->assign("edititem",admin_url("tourline_item#edit",array("ajax"=>1)));


		$GLOBALS['tmpl']->assign("formaction",admin_url("cruise#insert",array("ajax"=>1)));

		$GLOBALS['tmpl']->display("core/tourline/add.html");

	}
	

	public function insert() {

		

		$ajax = intval($_REQUEST['ajax']);

		//print_r($_REQUEST); exit;

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

		$data = array();

		/*基本配置*/

		$data['name'] = strim($_REQUEST['name']);

		$data['short_name'] = strim($_REQUEST['short_name']);

		$data['supplier_id'] = intval($_REQUEST['supplier_id']);

		$data['tour_type'] = intval($_REQUEST['tour_type']);

		$data['tour_range'] = intval($_REQUEST['tour_range']);

		$data['is_namelist'] = intval($_REQUEST['is_namelist']);

		$data['order_confirm_type'] = intval($_REQUEST['order_confirm_type']);

		$data['tour_total_day'] = intval($_REQUEST['tour_total_day']);

		$data['show_in_api'] = intval($_REQUEST['show_in_api']);

		$data['tour_guide_key'] = strim($_REQUEST["tour_guide_key"]);
		$data['is_cruise'] = 1;

		if(isset($_REQUEST['image'])){

			$data['image'] = format_domain_to_relative(strim($_REQUEST['image']));

		}

		else{

			$data['image'] ="";

		}
       // print_r($data);exit;
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

		

		$data['is_history'] = intval($_REQUEST['is_history']);

		$data['is_effect'] = intval($_REQUEST['is_effect']);

		$data['is_hot'] = intval($_REQUEST['is_hot']);

		$data['is_recommend'] = intval($_REQUEST['is_recommend']);

		$data['sort'] = intval($_REQUEST['sort']);

		

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

		$data['sale_virtual_total']= intval($_REQUEST['sale_virtual_total']);

		$data['show_sale_list']= intval($_REQUEST['show_sale_list']);

		$data['price_explain'] = strim($_REQUEST["price_explain"]);

		$data['child_norm'] = strim($_REQUEST["child_norm"]);

		$data['advance_day'] = intval($_REQUEST["advance_day"]);

/*套餐类型*/
		$data['package_1']= intval($_REQUEST['package_1']);
		$data['package_2']= intval($_REQUEST['package_2']);
		$data['package_3']= intval($_REQUEST['package_3']);
		$data['package_4']= intval($_REQUEST['package_4']);
		$data['package_5']= intval($_REQUEST['package_5']);

		

		/*返利设置*/

		$data['return_money'] = format_price_to_db($_REQUEST['return_money']);

		$data['return_score'] = intval($_REQUEST['return_score']);

		$data['return_exp'] = intval($_REQUEST['return_exp']);

		$data['is_review_return'] = intval($_REQUEST['is_review_return']);

		$data['review_return_money'] = format_price_to_db($_REQUEST['review_return_money']);

		$data['review_return_score'] = intval($_REQUEST['review_return_score']);

		$data['review_return_exp'] = intval($_REQUEST['review_return_exp']);

		$data['is_rebate'] = intval($_REQUEST['is_rebate']);

		$data['is_buy_return'] = intval($_REQUEST['is_buy_return']);

		

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

		}

		

		/*签证配置*/

		$data['is_visa'] = intval($_REQUEST['is_visa']);

		$data['visa_name'] = strim($_REQUEST["visa_name"]);

		$data['visa_price'] = format_price_to_db($_REQUEST["visa_price"]);

		$data['visa_brief'] = format_domain_to_relative($_REQUEST["visa_brief"]);

		

		/*SEO设置*/

		$data['seo_title'] = strim($_REQUEST['seo_title']);

		$data['seo_keywords'] = strim($_REQUEST['seo_keywords']);

		$data['seo_description'] = strim($_REQUEST['seo_description']);

		

		/*广告设置*/

		$data['adv1_name'] = strim($_REQUEST['adv1_name']);

		$data['adv1_image'] = format_domain_to_relative(strim($_REQUEST['adv1_image']));

		$data['adv1_url'] = strim($_REQUEST['adv1_url']);

		$data['adv2_name'] = strim($_REQUEST['adv2_name']);

		$data['adv2_image'] = format_domain_to_relative(strim($_REQUEST['adv2_image']));

		$data['adv2_url'] = strim($_REQUEST['adv2_url']);

		//print_r($data);exit;

		// 更新数据

		$log_info = $data['name'];

		$GLOBALS['db']->autoExecute(DB_PREFIX."tourline",$data,"INSERT","","SILENT");

		if ($GLOBALS['db']->error()=="") {

			$tourline_id = $GLOBALS['db']->insert_id();

		

			//时间价格

			if(isset($_REQUEST['tourline_items']))

			{

				foreach($_REQUEST['tourline_items'] as $k=>$v)

				{

					$tourline_item=unserialize(urldecode($v));

					if(intval($tourline_item['is_forever']) ==1)

					

					if($tourline_item['start_time'] !="")

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

					}

				}

			}

			

			//购买返代金券

			$buy_voucher_type=intval($_REQUEST['buy_voucher_type']);

			if($buy_voucher_type >0)

			{

				$b_voucher_type = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."voucher_type where deliver_type=3 and is_effect=1 and id=".$buy_voucher_type." ORDER BY sort DESC");

				if($b_voucher_type){

					$voucher_promote=array();

					$voucher_promote['voucher_type_id']=$b_voucher_type['id'];

					$voucher_promote['voucher_promote']=3;

					$voucher_promote['voucher_rel_id']=$tourline_id;

					$voucher_promote['voucher_promote_type']=1;

					$GLOBALS['db']->autoExecute(DB_PREFIX."voucher_promote",$voucher_promote,"INSERT","","SILENT");

				}

			}

			

			//评论返代金券

			$review_voucher_type=intval($_REQUEST['review_voucher_type']);

			if($review_voucher_type >0)

			{

				$r_voucher_type= $GLOBALS['db']->getRow("select id from ".DB_PREFIX."voucher_type where deliver_type=3 and is_effect=1 and id=".$review_voucher_type." ORDER BY sort DESC");

				if($r_voucher_type){

					$voucher_promote=array();

					$voucher_promote['voucher_type_id']=$r_voucher_type['id'];

					$voucher_promote['voucher_promote']=3;

					$voucher_promote['voucher_rel_id']=$tourline_id;

					$voucher_promote['voucher_promote_type']=2;

					$GLOBALS['db']->autoExecute(DB_PREFIX."voucher_promote",$voucher_promote,"INSERT","","SILENT");

				}

			}

			

			

			//如果是团购线路

			if($data['is_tuan'] ==1)

			{

				$t_data['type'] = 1;

				$t_data['is_effect'] =  $data['is_effect'];

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

				$t_data['is_history'] =  $data['is_history'];

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

			

			//增加保险

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

			

			//成功提示

			save_log($log_info.lang("INSERT_SUCCESS"),1);

			showSuccess(lang("INSERT_SUCCESS"),$ajax,admin_url("cruise#add"));

		} else {

			//错误提示

			showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);

		}



	}

	

	public function edit() {

		$id = intval($_REQUEST ['id']);

		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline where id = ".$id);

		

		$vo['city_match'] = unformat_fulltext_key($vo['city_match']);

		$vo['province_match'] = unformat_fulltext_key($vo['province_match']);

		$vo['area_match'] = unformat_fulltext_key($vo['area_match']);

		$vo['place_match'] = unformat_fulltext_key($vo['place_match']);

		$vo['around_city_match'] = unformat_fulltext_key($vo['around_city_match']);

		

		//商家

		$vo['company_name'] = $GLOBALS['db']->getOne("select `company_name` from ".DB_PREFIX."supplier where id = ".$vo['supplier_id']);

		

		//city_name

		$vo['city_name'] = $GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."tour_city where id = ".$vo['city_id']);

	

		//保险

    	$insurances=$GLOBALS['db']->getAll("select a.id,a.name from ".DB_PREFIX."insurance as a left join ".DB_PREFIX."tourline_insurance_link as b on b.insurance_id=a.id where b.tourline_id=".$vo['id']."");

    	if($insurances)

		{

	    	foreach($insurances as $k=>$v){

				$insurance_ids[]=$v['id'];

				$insurance_names[]=$v['name'];

			}

			$vo['insurance_ids']=implode(',',$insurance_ids);

			$vo['insurance_names']=implode(',',$insurance_names);

		}else

		{

			$vo['insurance_ids']='';

			$vo['insurance_names']='';

		}

		$GLOBALS['tmpl']->assign ( 'vo', $vo );

		

		//时间价格

		$tourline_items = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tourline_item where tourline_id = ".$vo['id']." ORDER BY is_forever DESC,start_time DESC ");

		foreach($tourline_items as $k=>$v){

			$tourline_items[$k]['adult_price'] = format_price_to_display($v['adult_price']);

			$tourline_items[$k]['child_price'] = format_price_to_display($v['child_price']);

			$tourline_items[$k]['adult_sale_price'] = format_price_to_display($v['adult_sale_price']);

			$tourline_items[$k]['child_sale_price'] = format_price_to_display($v['child_sale_price']);

			$tourline_items[$k]['tourline_item_data'] = urlencode(serialize($tourline_items[$k]));

		}

		$GLOBALS['tmpl']->assign ( 'tourline_items', $tourline_items );

		

		//代金券

		$voucher_type = $GLOBALS['db']->getAll("select id,voucher_name from ".DB_PREFIX."voucher_type where deliver_type=3 and is_effect=1 ORDER BY sort DESC");

    	$GLOBALS['tmpl']->assign("voucher_type",$voucher_type);

    	

    	$buy_voucher_type_id=$GLOBALS['db']->getOne("select a.id from ".DB_PREFIX."voucher_type as a left join ".DB_PREFIX."voucher_promote as b on b.voucher_type_id=a.id where b.voucher_promote=3 and b.voucher_rel_id=".$vo['id']." and b.voucher_promote_type=1");

    	$review_voucher_type_id=$GLOBALS['db']->getOne("select a.id from ".DB_PREFIX."voucher_type as a left join ".DB_PREFIX."voucher_promote as b on b.voucher_type_id=a.id where b.voucher_promote=3 and b.voucher_rel_id=".$vo['id']." and b.voucher_promote_type=2");

    	$GLOBALS['tmpl']->assign("buy_voucher_type_id",$buy_voucher_type_id);

    	$GLOBALS['tmpl']->assign("review_voucher_type_id",$review_voucher_type_id);

    	

		$GLOBALS['tmpl']->assign("searchstartcityurl",admin_url("tour_city#search_city_radio"),array("ajax"=>1));

		$GLOBALS['tmpl']->assign("searchcityurl",admin_url("tour_city#search_city"),array("ajax"=>1));

		$GLOBALS['tmpl']->assign("searchareaurl",admin_url("tour_area#search_area"),array("ajax"=>1));

		$GLOBALS['tmpl']->assign("searchplaceurl",admin_url("tour_place#search_place"),array("ajax"=>1));

    	$GLOBALS['tmpl']->assign("searchsupplierurl",admin_url("supplier#search_supplier",array("ajax"=>1)));

    	$GLOBALS['tmpl']->assign("searchinsurance",admin_url("tourline_insurance#search_insurance",array("ajax"=>1)));

    	$GLOBALS['tmpl']->assign("searchtagurl",admin_url("tour_place_tag#search_tag"),array("ajax"=>1));

    	$GLOBALS['tmpl']->assign("searchprovinceurl",admin_url("tour_province#search_province"),array("ajax"=>1));

    	

    	$tuan_cates = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."tuan_cate ORDER BY sort DESC");

    	$GLOBALS['tmpl']->assign("tuan_cates",$tuan_cates);

    	

		$GLOBALS['tmpl']->assign("additem",admin_url("tourline_item#add",array("ajax"=>1,"tourline_id"=>$vo['id'])));

    	$GLOBALS['tmpl']->assign("edititem",admin_url("tourline_item#edit",array("ajax"=>1,"tourline_id"=>$vo['id'])));

    	$GLOBALS['tmpl']->assign("previewurl",url("tours#view",array("id"=>$id,"preview"=>1)));

		

		$GLOBALS['tmpl']->assign("formaction",admin_url("tourline#update",array("ajax"=>1)));

		

		$GLOBALS['tmpl']->display("core/tourline/edit.html");

	}



	

	public function update() {

		$ajax = intval($_REQUEST['ajax']);

		if(intval($_REQUEST['id']) == 0){

			showErr("编辑数据出错",$ajax);

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

		

		$data = array();

		$tourline_id = intval($_REQUEST['id']);

		/*基本配置*/

		$data['name'] = strim($_REQUEST['name']);

		$data['short_name'] = strim($_REQUEST['short_name']);

		$data['supplier_id'] = intval($_REQUEST['supplier_id']);

		$data['tour_type'] = intval($_REQUEST['tour_type']);

		$data['tour_range'] = intval($_REQUEST['tour_range']);

		$data['is_namelist'] = intval($_REQUEST['is_namelist']);

		$data['order_confirm_type'] = intval($_REQUEST['order_confirm_type']);

		$data['tour_total_day'] = intval($_REQUEST['tour_total_day']);

		$data['show_in_api'] = intval($_REQUEST['show_in_api']);

		$data['tour_guide_key'] = strim($_REQUEST["tour_guide_key"]);

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

		

		$data['is_history'] = intval($_REQUEST['is_history']);

		$data['is_effect'] = intval($_REQUEST['is_effect']);

		$data['is_hot'] = intval($_REQUEST['is_hot']);

		$data['is_recommend'] = intval($_REQUEST['is_recommend']);

		$data['sort'] = intval($_REQUEST['sort']);

		

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

		$data['sale_virtual_total']= intval($_REQUEST['sale_virtual_total']);

		$data['show_sale_list']= intval($_REQUEST['show_sale_list']);

		$data['price_explain'] = strim($_REQUEST["price_explain"]);

		$data['child_norm'] = strim($_REQUEST["child_norm"]);

		$data['advance_day'] = intval($_REQUEST["advance_day"]);

		/*套餐类型*/
		$data['package_1']= intval($_REQUEST['package_1']);
		$data['package_2']= intval($_REQUEST['package_2']);
		$data['package_3']= intval($_REQUEST['package_3']);
		$data['package_4']= intval($_REQUEST['package_4']);
		$data['package_5']= intval($_REQUEST['package_5']);

		

		

		/*返利设置*/

		$data['return_money'] = format_price_to_db($_REQUEST['return_money']);

		$data['return_score'] = intval($_REQUEST['return_score']);

		$data['return_exp'] = intval($_REQUEST['return_exp']);

		$data['is_review_return'] = intval($_REQUEST['is_review_return']);

		$data['review_return_money'] = format_price_to_db($_REQUEST['review_return_money']);

		$data['review_return_score'] = intval($_REQUEST['review_return_score']);

		$data['review_return_exp'] = intval($_REQUEST['review_return_exp']);

		$data['is_rebate'] = intval($_REQUEST['is_rebate']);

		$data['is_buy_return'] = intval($_REQUEST['is_buy_return']);

		

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

		

		/*SEO设置*/

		$data['seo_title'] = strim($_REQUEST['seo_title']);

		$data['seo_keywords'] = strim($_REQUEST['seo_keywords']);

		$data['seo_description'] = strim($_REQUEST['seo_description']);

		

		/*广告设置*/

		$data['adv1_name'] = strim($_REQUEST['adv1_name']);

		$data['adv1_image'] = format_domain_to_relative(strim($_REQUEST['adv1_image']));

		$data['adv1_url'] = strim($_REQUEST['adv1_url']);

		$data['adv2_name'] = strim($_REQUEST['adv2_name']);

		$data['adv2_image'] = format_domain_to_relative(strim($_REQUEST['adv2_image']));

		$data['adv2_url'] = strim($_REQUEST['adv2_url']);

		

		

		// 更新数据

		$log_info = $data['name'];

		$GLOBALS['db']->autoExecute(DB_PREFIX."tourline",$data,"UPDATE","id=".$tourline_id,"SILENT");

		if ($GLOBALS['db']->error()=="") {

			

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

						

						$GLOBALS['db']->autoExecute(DB_PREFIX."tourline_item",$tourline_item_data,"INSERT","","SILENT");

						

						if($GLOBALS['db']->error()!="")

						{

							showErr("更新失败，已有相同日期的线路",1);

						}

					}

				}

			}

			

			//删除线路代金券关联

			$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."voucher_promote WHERE voucher_rel_id=".$tourline_id." AND voucher_promote=3");

			//购买返代金券

			$buy_voucher_type=intval($_REQUEST['buy_voucher_type']);

			if($buy_voucher_type >0)

			{

				$b_voucher_type = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."voucher_type where deliver_type=3 and is_effect=1 and id=".$buy_voucher_type." ORDER BY sort DESC");

				if($b_voucher_type){

					$voucher_promote=array();

					$voucher_promote['voucher_type_id']=$b_voucher_type['id'];

					$voucher_promote['voucher_promote']=3;

					$voucher_promote['voucher_rel_id']=$tourline_id;

					$voucher_promote['voucher_promote_type']=1;

					$GLOBALS['db']->autoExecute(DB_PREFIX."voucher_promote",$voucher_promote,"INSERT","","SILENT");

				}

			}

			

			//评论返代金券

			$review_voucher_type=intval($_REQUEST['review_voucher_type']);

			if($review_voucher_type >0)

			{

				$r_voucher_type= $GLOBALS['db']->getRow("select id from ".DB_PREFIX."voucher_type where deliver_type=3 and is_effect=1 and id=".$review_voucher_type." ORDER BY sort DESC");

				if($r_voucher_type){

					$voucher_promote=array();

					$voucher_promote['voucher_type_id']=$r_voucher_type['id'];

					$voucher_promote['voucher_promote']=3;

					$voucher_promote['voucher_rel_id']=$tourline_id;

					$voucher_promote['voucher_promote_type']=2;

					$GLOBALS['db']->autoExecute(DB_PREFIX."voucher_promote",$voucher_promote,"INSERT","","SILENT");

				}

			}

			

			//同步团购信息

			$t_ids = $GLOBALS['db']->getOne("select Group_concat(id) FROM ".DB_PREFIX."tuan where rel_id=".$tourline_id." and type=1 ");

			

			if($data['is_tuan'] ==1){

				$t_data=array();

				$t_data['type'] = 1;

				$t_data['is_effect'] =  $data['is_effect'];

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

				$t_data['is_history'] =  $data['is_history'];

				$t_data['success_count'] =  $data['tuan_success_count'];

				$t_data['cate_id'] =$data['tuan_cate'];

				$t_data['area_match'] =  $data['area_match'];

				$t_data['place_match'] =  $data['place_match'];

				$t_data['city_match'] =  $data['city_match'];

				if($t_ids)

				{

					$GLOBALS['db']->autoExecute(DB_PREFIX."tuan",$t_data,"UPDATE","type=1 and id in(".$t_ids.")","SILENT");

				}

				else

				{

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

					$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."tuan WHERE type=1 and rel_id=".$tourline_id." AND id in(".$t_ids.")");

				}

			}

			

			//删除线路关联保险

			$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."tourline_insurance_link WHERE tourline_id=".$tourline_id." ");

			//增加保险

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

			

			//成功提示

			save_log($log_info.lang("UPDATE_SUCCESS"),1);

			showSuccess(lang("UPDATE_SUCCESS"),$ajax);

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

	

	public function set_effect()

	{

		$id = intval($_REQUEST['id']);

		$ajax = intval($_REQUEST['ajax']);		

		$info = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."tourline where id = ".$id);

		$c_is_effect =  $GLOBALS['db']->getOne("select is_effect from ".DB_PREFIX."tourline where id = ".$id); //当前状态

		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态

		$GLOBALS['db']->query("update ".DB_PREFIX."tourline set is_effect = ".$n_is_effect." where id = ".$id);

		$GLOBALS['db']->query("update ".DB_PREFIX."tuan set is_effect = ".$n_is_effect." where rel_id = ".$id." and type=1");

		save_log($info.lang("SET_EFFECT_".$n_is_effect),1);

		showSuccess(lang("SET_EFFECT_".$n_is_effect),$ajax);

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

	

	

	

		//线路ID

		if(isset($_REQUEST['tourline_id']))

			$tourline_id = strim($_REQUEST['tourline_id']);

		else

			$tourline_id = "";

		$param['tourline_id'] = $tourline_id;

		if($tourline_id!='' && intval($tourline_id) > 0)

		{

			$condition.=" and t.tourline_id = ".intval($tourline_id)." ";

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

			$condition .=" and t.verify_time=0";

		}else if ($is_verify == 2){

			$condition .=" and t.verify_time>0";

		}

		$param['is_verify'] = $is_verify;

	

		//是否有效

		$is_verify_code_invalid = -1;

		if(isset($_REQUEST['is_verify_code_invalid']) && strim($_REQUEST['is_verify_code_invalid'])!="")

			$is_verify_code_invalid = intval($_REQUEST['is_verify_code_invalid']);

	

		$param['is_verify_code_invalid'] = $is_verify_code_invalid;

		if($is_verify_code_invalid !=-1)

		{

			$condition .=" and t.is_verify_code_invalid=$is_verify_code_invalid ";

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

	

		//出发时间

		$end_time_begin  = strim($_REQUEST['end_time_begin']);

		$param['end_time_begin'] = $end_time_begin;

	

		$end_time_end  = strim($_REQUEST['end_time_end']);

		$param['end_time_end'] = $end_time_end;

	

		if(!empty($end_time_begin) && !empty($end_time_end))

		{

			$condition.=" and t.end_time >= '".$end_time_begin."' and t.end_time <='". $end_time_end."' ";

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

	

		$totalCount = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."tourline_order t where ".$condition);

		if($totalCount > 0){

			$sql = "select t.*,u.user_name,u.mobile,s.user_name as supplier_name  from ".DB_PREFIX."tourline_order t left outer join ".DB_PREFIX."user u on u.id = t.user_id left outer join ".DB_PREFIX."supplier s on s.id = t.supplier_id where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit;

			//echo $sql;

			//die();

			$list = $GLOBALS['db']->getAll($sql);

	

			require_once APP_ROOT_PATH."system/libs/tourline.php";

	

			foreach($list as $k=>$v)

			{

				tourline_order_format($list[$k]);

				//print_r($v);

	

				//print_r($list[$k]);

				/*

				 $list[$k]['create_time_format'] = to_date($v['create_time']);

				$list[$k]['total_price_format'] = format_price($v['total_price']);

				$list[$k]['pay_amount_format'] = format_price($v['pay_amount']);

	

				//支付状态

				if ($v['pay_status'] == 1){

				$list[$k]['pay_status_format'] = '已支付';

				}else{

				$list[$k]['pay_status_format'] = '未支付';

				}

	

				//订单状态(流程)1.新订单 2.已确认 3.已完成 4.作废\r\n新订单：未确认（包含已付款）的都表示为新订单\r\n已确认：表示为商家或管理员查看，确认手动修改\r\n新订单、已确认均可申请退款，否则不可',

				if ($v['order_status'] == 1){

				$list[$k]['order_status_format'] = '新订单';

				}else if ($v['order_status'] == 2){

				$list[$k]['order_status_format'] = '已确认';

				}else if ($v['order_status'] == 3){

				$list[$k]['order_status_format'] = '作废';

				}else {

				$list[$k]['order_status_format'] = '未知';

				}*/

	

			}

		}

		/*

		 线路名称:tourline_name

		订单号:sn

		购买会员:user_name

		下单时间:create_time

		订单状态:order_status

		支付状态:pay_status

		订单金额：total_price

		已付金额：pay_amount

		已退金额：refund_amount

		*/

	

		$GLOBALS['tmpl']->assign('list',$list);

		$GLOBALS['tmpl']->assign('totalCount',$totalCount);

		$GLOBALS['tmpl']->assign('param',$param);

	

		$GLOBALS['tmpl']->assign("formaction",admin_url("tourline#verify_code_list"));

		$GLOBALS['tmpl']->assign("editurl",admin_url("tourline_order#order"));

		$GLOBALS['tmpl']->assign("exporturl",admin_url("tourline#export_csv"));

		$GLOBALS['tmpl']->assign("seteffecturl",admin_url("tourline#set_invalid",array("ajax"=>1)));

		

		$GLOBALS['tmpl']->assign("sendurl",admin_url("tourline#send_sms_mail",array("ajax"=>1)));

		

		$GLOBALS['tmpl']->display("core/tourline/code_list.html");

	}

	

	public function export_csv($page = 1)

	{

		$param = array();

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

	

	

	

		//线路ID

		if(isset($_REQUEST['tourline_id']))

			$tourline_id = strim($_REQUEST['tourline_id']);

		else

			$tourline_id = "";

		$param['tourline_id'] = $tourline_id;

		if($tourline_id!='' && intval($tourline_id) > 0)

		{

			$condition.=" and t.tourline_id = ".intval($tourline_id)." ";

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

			$condition .=" and t.verify_time=0";

		}else if ($is_verify == 2){

			$condition .=" and t.verify_time>0";

		}

		$param['is_verify'] = $is_verify;

	

		//是否有效

		$is_verify_code_invalid = -1;

		if(isset($_REQUEST['is_verify_code_invalid']) && strim($_REQUEST['is_verify_code_invalid'])!="")

			$is_verify_code_invalid = intval($_REQUEST['is_verify_code_invalid']);

	

		$param['is_verify_code_invalid'] = $is_verify_code_invalid;

		if($is_verify_code_invalid !=-1)

		{

			$condition .=" and t.is_verify_code_invalid=$is_verify_code_invalid ";

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

	

		//出发时间

		$end_time_begin  = strim($_REQUEST['end_time_begin']);

		$param['end_time_begin'] = $end_time_begin;

	

		$end_time_end  = strim($_REQUEST['end_time_end']);

		$param['end_time_end'] = $end_time_end;

	

		if(!empty($end_time_begin) && !empty($end_time_end))

		{

			$condition.=" and t.end_time >= '".$end_time_begin."' and t.end_time <='". $end_time_end."' ";

		}

	

		$param['pageSize'] = 100;

		//分页

		$limit = (($page-1)*$param['pageSize']).",".$param['pageSize'];

	

		$totalCount = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."tourline_order t where ".$condition);

		if($totalCount > 0){

			$sql = "select t.*,u.user_name,u.mobile,s.user_name as supplier_name  from ".DB_PREFIX."tourline_order t left outer join ".DB_PREFIX."user u on u.id = t.user_id left outer join ".DB_PREFIX."supplier s on s.id = t.supplier_id where ".$condition." limit ".$limit;

			//echo $sql;

			//die();

			$list = $GLOBALS['db']->getAll($sql);

	

			require_once APP_ROOT_PATH."system/libs/tourline.php";

	

			foreach($list as $k=>$v)

			{

				tourline_order_format($list[$k]);

			}

	

			if($page == 1)

			{

				$content = iconv("utf-8","gbk","订单ID,验证码,购买会员,预定人姓名,预定人手机,出发日期,下单时间,订单金额,付款时间,已付金额,是否验证,是否有效,订单状态,退款状态,订单备注");

				$content = $content . "\n";

			}

	

			if($list)

			{

				register_shutdown_function(array(&$this, 'export_csv'), $page+1);

				foreach($list as $k=>$v)

				{

	

					$order_value = array();

					$order_value['id'] = '"' . $v['id'] . '"';

					$order_value['verify_code'] = '"' . $v['verify_code'] . '"';

					$order_value['user_name'] = '"' .iconv('utf-8','gbk',$v['user_name']) . '"';

					$order_value['appoint_name'] = '"' . iconv('utf-8','gbk',$v['appoint_name']) . '"';

					$order_value['appoint_mobile'] = '"' . $v['appoint_mobile'] . '"';

	

					$order_value['end_time'] = '"' . $v['end_time'] . '"';

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

		header("Content-Disposition: attachment; filename=tourline_code.csv");

		echo $content;

	

	

	}

	

	public function set_invalid()

	{

		$id = intval($_REQUEST['id']);

		$ajax = intval($_REQUEST['ajax']);

		$info = $GLOBALS['db']->getOne("select verify_code from ".DB_PREFIX."tourline_order where id = ".$id);

		$c_is_effect =  $GLOBALS['db']->getOne("select is_verify_code_invalid from ".DB_PREFIX."tourline_order where id = ".$id); //当前状态

		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态

	

		$GLOBALS['db']->query("update ".DB_PREFIX."tourline_order set is_verify_code_invalid = ".$n_is_effect." where id = ".$id);

		save_log($info.lang("SET_EFFECT_".$n_is_effect),1);

		showSuccess(lang("SET_EFFECT_".$n_is_effect),$ajax);

	}	

	

	//统计

	public function statistics()

	{

		$id = intval($_REQUEST['id']);

		//订单总数，实收总额，余额支付总额，代金券支付总额，在线支付总额，退款总额，返利现金总额，返积分总额，返经验总额，已点评数，点评每个星级的统计数

		$param['order_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tourline_order where pay_status = 1 and tourline_id = ".$id);

		$param['order_count'] = intval($param['order_count']);

		

		//实收总额

		$param['pay_amount'] = $GLOBALS['db']->getOne("select sum(pay_amount) from ".DB_PREFIX."tourline_order where pay_status = 1 and tourline_id = ".$id);

		$param['pay_amount'] = format_price(format_price_to_display($param['pay_amount']));

	

		//余额支付总额

		$param['account_pay'] = $GLOBALS['db']->getOne("select sum(account_pay) from ".DB_PREFIX."tourline_order where pay_status = 1 and tourline_id = ".$id);

		$param['account_pay'] = format_price(format_price_to_display($param['account_pay']));

	

		//代金券支付总额

		$param['voucher_pay'] = $GLOBALS['db']->getOne("select sum(voucher_pay) from ".DB_PREFIX."tourline_order where pay_status = 1 and tourline_id = ".$id);

		$param['voucher_pay'] = format_price(format_price_to_display($param['voucher_pay']));

	

		//在线支付总额

		$param['online_pay'] = $GLOBALS['db']->getOne("select sum(online_pay) from ".DB_PREFIX."tourline_order where pay_status = 1 and tourline_id = ".$id);

		$param['online_pay'] = format_price(format_price_to_display($param['online_pay']));

	

		//退款总额

		$param['refund_amount'] = $GLOBALS['db']->getOne("select sum(refund_amount) from ".DB_PREFIX."tourline_order where pay_status = 1 and tourline_id = ".$id);

		$param['refund_amount'] = format_price(format_price_to_display($param['refund_amount']));

	

		//返利现金总额

		$param['return_money_total'] = $GLOBALS['db']->getOne("select sum(return_money_total) from ".DB_PREFIX."tourline_order where pay_status = 1 and tourline_id = ".$id);

		$param['return_money_total'] = format_price(format_price_to_display($param['return_money_total']));

	

		//返积分总额

		$param['return_score_total'] = $GLOBALS['db']->getOne("select sum(return_score_total) from ".DB_PREFIX."tourline_order where pay_status = 1 and tourline_id = ".$id);

		$param['return_score_total'] = intval($param['return_score_total']);

		//返经验总额

		$param['return_exp_total'] = $GLOBALS['db']->getOne("select sum(return_exp_total) from ".DB_PREFIX."tourline_order where pay_status = 1 and tourline_id = ".$id);

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

		$buy_dp_group = $GLOBALS['db']->getAll("select point,count(*) as num from ".DB_PREFIX."review where review_type = 1 and review_rel_id = ".$id." group by point");

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

		$GLOBALS['tmpl']->display("core/tourline/statistics.html");

	}	

	

	public function send_sms_mail()

	{

		$id = intval($_REQUEST['id']);

		$ajax = intval($_REQUEST['ajax']);

		

		//1:短信;2:邮件

		$send_type = intval($_REQUEST['send_type']);

		

		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_order where id = '".$id."'");

		//订单支付成功，发短信

		if ($send_type == 1){

			if (send_order_sms($order_info,1) == 1){

				showSuccess("已将发送内容,添加到队列",$ajax);

			}else{

				showErr("添加发送队列失败",$ajax);

			}

		}

		

		//订单支付成功，发邮件		

		if ($send_type == 2){

			if (send_order_mail($order_info,1) == 1){

				showSuccess("已将发送内容,添加到队列",$ajax);

			}else{

				showErr("添加发送队列失败",$ajax);

			}

		}		

	}	

}

?>