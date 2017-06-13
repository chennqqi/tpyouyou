<?php

class tourline_supplierModule extends AuthModule

{

  function index() {

    $param = array();		

		//条件

		$condition = " supplier_id =  ".$this->supplier_id." and is_cruise != 1";

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

    	

		$GLOBALS['tmpl']->assign("additem",admin_url("tourline_item#add",array("ajax"=>1,"tourline_id"=>$vo['rel_id'])));

    	$GLOBALS['tmpl']->assign("edititem",admin_url("tourline_item#edit",array("ajax"=>1,"tourline_id"=>$vo['rel_id'])));

		

		$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_supplier#update",array("ajax"=>1)));

		

		$GLOBALS['tmpl']->display("core/tourline_supplier/edit.html");

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

		$id = intval($_REQUEST['id']);

		/*基本配置*/

		$data['name'] = strim($_REQUEST['name']);

		$data['short_name'] = strim($_REQUEST['short_name']);

		$data['supplier_id'] = $this->supplier_id;

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

		

		$insurance_ids = strim($_REQUEST["insurance_id"]);

		if($insurance_ids !='')

		{

			$insurances=array();

			$insurances['insurance_ids']=$insurance_ids;

			$insurances['insurance_names']=strim($_REQUEST["insurance_name"]);	

			$data['insurance_cfg']=serialize($insurances);

		}

		if(isset($_POST['new_insurances'])){

			$data['other_insurance_cfg'] = serialize($_POST['new_insurances']);

		}

		if(isset($_POST['tourline_items'])){

			$data['tourline_item_cfg'] = serialize($_POST['tourline_items']);

		}

		

		// 更新数据

		

		$GLOBALS['db']->autoExecute(DB_PREFIX."tourline_supplier",$data,"UPDATE"," supplier_id=".$this->supplier_id ." and id=".$id,"SILENT");

		if ($GLOBALS['db']->error()=="") {		

			//成功提示

			showSuccess("编辑成功，等待审核",$ajax);

		} else {

			//错误提示

			showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);

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

				$sql = "delete from ".DB_PREFIX."tourline_supplier where id in (".$id.") and supplier_id=".$this->supplier_id;

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