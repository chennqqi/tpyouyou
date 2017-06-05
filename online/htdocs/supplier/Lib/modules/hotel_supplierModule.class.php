<?php

class hotel_supplierModule extends AuthModule{

  function index() {
    $param = array();		
		//条件
		$condition = " supplier_id = ".$this->supplier_id;
		if(isset($_REQUEST['name']))
			$name_key = strim($_REQUEST['name']);
		else
			$name_key = "";
		$param['name'] = $name_key;
		if($name_key!='')
		{
			$condition.=" and name = '".$name_key."' ";
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
			
			foreach ($list as $k=>$v){
				if($v['spot_level'] >0)
					$list[$k]['level_format'] = lang("SPOT_LEVEl_".$v['spot_level']);
				else
					$list[$k]['level_format'] = "无";
				
				$list[$k]['ticket_price_format'] = format_price(format_price_to_display($v['ticket_price']));
				
				$list[$k]['create_time_format'] = to_date($v['create_time']);
				$list[$k]['preview_url'] = url("hotel#view",array("sid"=>$v['id']));
				$supplier_ids[] = $v['supplier_id'];
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
		$GLOBALS['tmpl']->assign("addurl",admin_url("hotel#add"));
		$GLOBALS['tmpl']->display("core/hotel_supplier/index.html");
  }
    
  public function edit(){
    $id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."hotel_supplier where id = ".$id." AND supplier_id=".$this->supplier_id);
		if(!$vo){
			showErr("不存在的数据",1);
		}
		$vo['city_match'] = unformat_fulltext_key($vo['city_match']);
		$vo['area_match'] = unformat_fulltext_key($vo['area_match']);
		$vo['place_match'] = unformat_fulltext_key($vo['place_match']);
		
		$GLOBALS['tmpl']->assign ( 'vo', $vo );		
		//相册
		if($vo['image_list']!=""){
			$image_list = unserialize($vo['image_list']);
			$GLOBALS['tmpl']->assign ( 'image_list', $image_list );
		}
		//门票
		if($vo['room_list']!=""){
			$ttickets = unserialize($vo['room_list']);
			foreach($ttickets as $k=>$v){
				$tickets[$k] = unserialize(base64_decode($v));
				$tickets[$k]['ticket_data'] = $v;
			}
			$GLOBALS['tmpl']->assign ( 'tickets', $tickets );
		}
		
		$GLOBALS['tmpl']->assign("addhotelroom",admin_url("hotel_room#add"),array("ajax"=>1));
    $GLOBALS['tmpl']->assign("edithotelroom",admin_url("hotel_room#edit",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("formaction",admin_url("hotel_supplier#update",array("ajax"=>1)));
		
    $GLOBALS['tmpl']->display("core/hotel_supplier/edit.html");
  }
    
  public function update() {
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST ['id']);
		if(!check_empty("name"))
		{
			showErr(lang("SPOT_CATE_NAME_EMPTY"),$ajax);
		}
		if(!check_empty("tel"))
		{
			showErr("请输入电话号码",$ajax);
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
		
		$data['tag'] = $data['tag_match'] = str_to_unicode_string_depart(strim($_REQUEST['tour_place_tag_tag_name']));
		$data['tag_match_row'] = strim($_REQUEST['tour_place_tag_tag_name']);
		
		$data['star_level'] = intval($_REQUEST["star_level"]);
		$data['tel'] = intval($_REQUEST["tel"]);
		
		$data['brief'] = strim($_REQUEST["brief"]);
		$data['appointment_desc'] = format_domain_to_relative(btrim($_REQUEST["appointment_desc"]));
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
		$GLOBALS['db']->autoExecute(DB_PREFIX."hotel_supplier",$data,"UPDATE"," supplier_id=".$this->supplier_id ." and id=".$id,"SILENT");
		
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			showSuccess("审核酒店编辑成功，等待审核",$ajax);
		} else {
			//错误提示
			showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	
	}
    
  public function foreverdelete() {		
		$ajax = intval($_REQUEST['ajax']);		
		if (isset ( $_REQUEST ['id'] ))
		{
			$id = strim($_REQUEST ['id']);			
			$id = format_ids_str($id);
			if($id)
			{					
				$sql = "delete from ".DB_PREFIX."hotel_supplier where id in (".$id.") and supplier_id=".$this->supplier_id;
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
}
?>