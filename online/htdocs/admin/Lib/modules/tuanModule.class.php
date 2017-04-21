<?php
class tuanModule extends AuthModule
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
		
		$cate_id = 0;
		if(isset($_REQUEST['cate_id']))
			$cate_id = intval($_REQUEST['cate_id']);
		$param['cate_id'] = $cate_id;
		if($param['cate_id'] > 0)
		{
			$condition .=" and cate_id = ".$cate_id;
		}
		
		$type = 0;
		if(isset($_REQUEST['type']))
			$type = intval($_REQUEST['type']);	
		$param['type'] = $type;
		if($param['type'] > 0)
		{
			$condition .=" and type = ".$type;
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
		
		$tuan_cate = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."tuan_cate ORDER BY sort DESC,id ASC");
		$GLOBALS['tmpl']->assign('tuan_cate',$tuan_cate);
		$ttuan_cate = array();
		foreach($tuan_cate as $k=>$v){
			$ttuan_cate[$v['id']] = $v;
		}
		
		$totalCount = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."tuan where ".$condition);
		if($totalCount){
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tuan where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
			foreach($list as $k=>$v){
				$list[$k]['type_name'] = lang("TYPE_".$v['type']);
				$list[$k]['cate_name'] = $ttuan_cate[$v['cate_id']]['name'];
				$list[$k]['begin_time_format'] = to_date($v['begin_time']);
				$list[$k]['end_time_format'] = to_date($v['end_time']);
			}
		}
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("tuan"));
		$GLOBALS['tmpl']->assign("setsorturl",admin_url("tuan#set_sort",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("delurl",admin_url("tuan#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("tuan#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("tuan#add"));
		$GLOBALS['tmpl']->display("core/tuan/index.html");
    }
    
    public function add(){
    	$tuan_cates = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."tuan_cate ORDER BY sort DESC,id ASC");
		$GLOBALS['tmpl']->assign('tuan_cates',$tuan_cates);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("tuan#insert",array("ajax"=>1)));
		
		$GLOBALS['tmpl']->assign("searchtuanurl_1",admin_url("tuan#search_tuan",array("type"=>1)));
		$GLOBALS['tmpl']->assign("searchtuanurl_2",admin_url("tuan#search_tuan",array("type"=>2)));
		$GLOBALS['tmpl']->assign("searchtuanurl_3",admin_url("tuan#search_tuan",array("type"=>3)));
		
    	$GLOBALS['tmpl']->display("core/tuan/add.html");
    }
    
    public function insert(){
    	$ajax = intval($_REQUEST['ajax']);
    	
    	if(intval($_REQUEST['cate_id']) <=0){
    		showErr("请选择团购分类",$ajax);
    	}
    	
    	if(intval($_REQUEST['type'])==0){
    		showErr("请选择团购类型",$ajax);
    	}
    	
    	if(intval($_REQUEST['tuan_rel_id'])==0){
    		showErr("请选择团购",$ajax);
    	}
    	
    	if(strim($_REQUEST['image'])==""){
    		showErr("请上传团购图片",$ajax);
    	}
    	
		if(strim($_POST['tuan_begin_time'])!=""){
			$tuan_begin_time=to_timespan($_POST['tuan_begin_time']);
		}else{
			$tuan_begin_time = 0;
		}				
			
		if(strim($_POST['tuan_end_time'])!=""){
			$tuan_end_time=to_timespan($_POST['tuan_end_time']);							
			if($tuan_end_time < $tuan_begin_time){
				showErr("团购结束时间必须晚于开始时间",$ajax);								
			}
		}							
		else{
			$tuan_end_time = 0;
		}
			
		//判断是否已经操作此团购
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."tuan WHERE rel_id=".intval($_REQUEST['tuan_rel_id'])." AND type=".intval($_REQUEST['type'])) > 0)
		{
			showErr("该团购已存在",$ajax);
		}
		
    	switch(intval($_REQUEST['type'])){
			case 1:
				$data = $GLOBALS['db']->getRow("select name,city_match,area_match,place_match FROM ".DB_PREFIX."tourline where id=".intval($_REQUEST['tuan_rel_id']));
			    break;
			case 2:
				$data =  $GLOBALS['db']->getRow("SELECT s.name,s.area_match,s.place_match,s.city_match FROM ".DB_PREFIX."spot s LEFT JOIN ".DB_PREFIX."ticket t ON t.spot_id=s.id WHERE t.id=".intval($_REQUEST['tuan_rel_id']));
				break; 
			case 3:
				$data = $GLOBALS['db']->getRow("select name,city_match,area_match,place_match FROM ".DB_PREFIX."hotel where id=".intval($_REQUEST['tuan_rel_id']));
				break;
		}
		$t_data['type'] = intval($_REQUEST['type']);
		$t_data['rel_id'] = intval($_REQUEST['tuan_rel_id']);
		$t_data['name'] = strim($_REQUEST['tuan_name']);
		$t_data['origin_price'] =  format_price_to_db($_REQUEST['tuan_origin_price']);
		$t_data['current_price'] =  format_price_to_db($_REQUEST['tuan_current_price']);
		$t_data['sale_price'] =  format_price_to_db($_REQUEST['tuan_sale_price']);
		$t_data['image'] = format_domain_to_relative(strim($_REQUEST['image']));
		$t_data['brief'] =  strim($_REQUEST['brief']);
		$t_data['discount'] =  $t_data['current_price']/$t_data['origin_price'] * 100;
		$t_data['begin_time'] =  $tuan_begin_time;
		$t_data['end_time'] =  $tuan_end_time;
		$t_data['is_pre'] =  intval($_REQUEST['is_pre']);
		$t_data['is_history'] =  intval($_REQUEST['is_history']);
		$t_data['success_count'] =  intval($_REQUEST['tuan_success_count']);
		$t_data['cate_id'] =  intval($_REQUEST['cate_id']);
		$t_data['area_match'] =  $data['area_match'];
		$t_data['place_match'] =  $data['place_match'];
		$t_data['city_match'] =  $data['city_match'];
		$t_data['create_time'] =  NOW_TIME;
		//if($t_data['begin_time']>$t_data['end_time'])showErr("结束时间必须晚于开始时间",$ajax);
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."tuan",$t_data,"INSERT","","SILENT");
		$tuan_id = $GLOBALS['db']->insert_id();
		if($GLOBALS['db']->error()==""){
			//同步相应的表
			switch($t_data['type']){
				case 1:
					$table = "tourline";
				break;
				case 2:
					$table = "ticket";
				break; 
				case 3:
					$table = "hotel_room";
				break;
			}
			
			$tmp_data['is_tuan'] = 1;
			$tmp_data['tuan_begin_time'] = $t_data['begin_time'];
			$tmp_data['tuan_end_time'] = $t_data['end_time'];
			$tmp_data['tuan_is_pre'] = $t_data['is_pre'];
			$tmp_data['tuan_cate'] = $t_data['cate_id'];
			$tmp_data['is_history'] = $t_data['is_history'];
			$tmp_data['tuan_success_count'] = $t_data['success_count'];
			$tmp_data['tuan_id'] = $tuan_id;
			if($table=="ticket")
			{
				$tmp_data['name_brief'] = $t_data['brief'];
			}
			
			
			$GLOBALS['db']->autoExecute(DB_PREFIX.$table,$tmp_data,"UPDATE","id=".$t_data['rel_id'],"SILENT");
			
			if($t_data['type']  == 2){
				require APP_ROOT_PATH."system/libs/spot.php";
				//更新门票冗余信息
				update_spot_ticket(0,$t_data['rel_id']);
			}
			
			save_log($data['name']."，门票：".$t_data['name'].lang("INSERT_SUCCESS"),1);
			
			showSuccess(lang("INSERT_SUCCESS"),$ajax);
		}
		else {
			//错误提示
			showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	
    }
    
   	public function edit(){
   		$id = intval($_REQUEST['id']);
   		$tuan_cates = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."tuan_cate ORDER BY sort DESC,id ASC");
		$GLOBALS['tmpl']->assign('tuan_cates',$tuan_cates);
		
		$vo=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."tuan WHERE id=".$id);
		$vo['origin_price_format'] = format_price_to_display($vo['origin_price']);
		$vo['current_price_format'] = format_price_to_display($vo['current_price']);
		$vo['sale_price_format'] = format_price_to_display($vo['sale_price']);
		
		if(intval($vo['begin_time']) > 0)
			$vo['begin_time_format'] = to_date($vo['begin_time']);
		else
			$vo['begin_time_format'] = "";
		if(intval($vo['end_time']) > 0)
			$vo['end_time_format'] = to_date($vo['end_time']);
		else
			$vo['end_time_format'] = "";
		
		$GLOBALS['tmpl']->assign('vo',$vo);
		
		$GLOBALS['tmpl']->assign("searchtuanurl_1",admin_url("tuan#search_tuan",array("type"=>1)));
		$GLOBALS['tmpl']->assign("searchtuanurl_2",admin_url("tuan#search_tuan",array("type"=>2)));
		$GLOBALS['tmpl']->assign("searchtuanurl_3",admin_url("tuan#search_tuan",array("type"=>3)));
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("tuan#update",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/tuan/edit.html");
   	}
   	
   	public function update(){
   		$ajax = intval($_REQUEST['ajax']);
   		
   		$id = intval($_REQUEST['id']);
		if($id == 0){
			showErr("数据错误",$ajax);
		}
    	
    	if(intval($_REQUEST['cate_id']) <=0){
    		showErr("请选择团购分类",$ajax);
    	}
    	
    	if(intval($_REQUEST['type'])==0){
    		showErr("请选择团购类型",$ajax);
    	}
    	
    	if(intval($_REQUEST['tuan_rel_id'])==0){
    		showErr("请选择团购",$ajax);
    	}
    	
    	if(strim($_REQUEST['image'])==""){
    		showErr("请上传团购图片",$ajax);
    	}
    	
		if(strim($_POST['tuan_begin_time'])!=""){
			$tuan_begin_time=to_timespan($_POST['tuan_begin_time']);
		}else{
			$tuan_begin_time = 0;
		}				
			
		if(strim($_POST['tuan_end_time'])!=""){
			$tuan_end_time=to_timespan($_POST['tuan_end_time']);							
			if($tuan_end_time < $tuan_begin_time){
				showErr("团购结束时间必须晚于开始时间",$ajax);								
			}
		}							
		else{
			$tuan_end_time = 0;
		}
		
		
   		switch(intval($_REQUEST['type'])){
			case 1:
				$data = $GLOBALS['db']->getRow("select name,city_match,area_match,place_match FROM ".DB_PREFIX."tourline where id=".intval($_REQUEST['tuan_rel_id']));
			    break;
			case 2:
				$data =  $GLOBALS['db']->getRow("SELECT s.name,s.area_match,s.place_match,s.city_match FROM ".DB_PREFIX."spot s LEFT JOIN ".DB_PREFIX."ticket t ON t.spot_id=s.id WHERE t.id=".intval($_REQUEST['tuan_rel_id']));
				break; 
			case 3:
				$data = $GLOBALS['db']->getRow("select name,city_match,area_match,place_match FROM ".DB_PREFIX."hotel where id=".intval($_REQUEST['tuan_rel_id']));
				break;
		}
		
		$t_data['type'] = intval($_REQUEST['type']);
		$t_data['rel_id'] = intval($_REQUEST['tuan_rel_id']);
		$t_data['name'] = strim($_REQUEST['tuan_name']);
		$t_data['origin_price'] =  format_price_to_db($_REQUEST['tuan_origin_price']);
		$t_data['current_price'] =  format_price_to_db($_REQUEST['tuan_current_price']);
		$t_data['sale_price'] =  format_price_to_db($_REQUEST['tuan_sale_price']);
		$t_data['image'] = format_domain_to_relative(strim($_REQUEST['image']));
		$t_data['brief'] =  strim($_REQUEST['brief']);
		$t_data['discount'] =  $t_data['current_price']/$t_data['origin_price'] * 100;
		$t_data['begin_time'] =  $tuan_begin_time;
		$t_data['end_time'] =  $tuan_end_time;
		$t_data['is_pre'] =  intval($_REQUEST['is_pre']);
		$t_data['success_count'] =  intval($_REQUEST['tuan_success_count']);
		$t_data['cate_id'] =  intval($_REQUEST['cate_id']);
		$t_data['is_history'] =  intval($_REQUEST['is_history']);
		$t_data['area_match'] =  $data['area_match'];
		$t_data['place_match'] =  $data['place_match'];
		$t_data['city_match'] =  $data['city_match'];
		$t_data['create_time'] =  NOW_TIME;
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."tuan",$t_data,"UPDATE","id=".$id,"SILENT");
		
		if($GLOBALS['db']->error()==""){
			//同步相应的表
			switch($t_data['type']){
				case 1:
					$table = "tourline";
				break;
				case 2:
					$table = "ticket";
				break; 
				case 3:
					$table = "hotel_room";
				break;
			}
			
			$tmp_data['is_tuan'] = 1;
			$tmp_data['tuan_begin_time'] = $t_data['begin_time'];
			$tmp_data['tuan_end_time'] = $t_data['end_time'];
			$tmp_data['tuan_is_pre'] = $t_data['is_pre'];
			$tmp_data['tuan_cate'] = $t_data['cate_id'];
			$tmp_data['tuan_success_count'] = $t_data['success_count'];
			$tmp_data['is_history'] = $t_data['is_history'];
			$tmp_data['tuan_id'] = $id;
			if($table=="ticket")
			{
				$tmp_data['name_brief'] = $t_data['brief'];
			}
			//$tmp_data['is_effect'] = $t_data['is_effect'];
			
			$GLOBALS['db']->autoExecute(DB_PREFIX.$table,$tmp_data,"UPDATE","id=".$t_data['rel_id'],"SILENT");
			
			
			if($t_data['type']  == 2){
				require APP_ROOT_PATH."system/libs/spot.php";
				//更新门票冗余信息
				update_spot_ticket(0,$t_data['rel_id']);
			}
			
			save_log($data['name']."，门票：".$t_data['name'].lang("UPDATE_SUCCESS"),1);
			
			showSuccess(lang("UPDATE_SUCCESS"),$ajax);
		}
		else {
			//错误提示
			showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	
   	}
   	
   	public function search_tuan(){
   		
		$type = intval($_REQUEST['type']);
		if($type == 0){
			showErr("发生错误");
		}
		
		$param = array();
		$param['type'] = $type;
		//条件
		$condition = " is_effect = 1 ";
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
		
		switch($type){
			case 1:
				$table = "tourline";
			break;
			case 2:
				$table = "ticket";
			break;
			case 3:
				$table = "hotel_room";
			break;
		}
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX.$table." where ".$condition);
		if($totalCount > 0){
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX.$table." where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
			foreach($list as $k=>$v){
				$list[$k]['origin_price'] = format_price_to_display($v['origin_price']);
				$list[$k]['current_price'] = format_price_to_display($v['current_price']);
				$list[$k]['sale_price'] = format_price_to_display($v['sale_price']);
				if(intval($v['tuan_begin_time']) > 0)
					$list[$k]['tuan_begin_time_format'] = to_date($v['tuan_begin_time']);
				else
					$list[$k]['tuan_begin_time_format'] = to_date(get_gmtime());
				if(intval($v['tuan_end_time']) > 0)
					$list[$k]['tuan_end_time_format'] = to_date($v['tuan_end_time']);
				else
					$list[$k]['tuan_end_time_format'] = to_date(get_gmtime() + 3600 * 24 *7);
			}
		}
		
	
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		$GLOBALS['tmpl']->assign('table',$table);
	
		$GLOBALS['tmpl']->assign("formaction",admin_url("tuan#search_tuan",array("type"=>$type)));
		
   		$GLOBALS['tmpl']->display("core/tuan/search_tuan.html");
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
				$del_name = $GLOBALS['db']->getOne("select group_concat(name) from ".DB_PREFIX."tuan where id in (".$id.")");
				$rel_ids = 	$GLOBALS['db']->getAll("select rel_id,type from ".DB_PREFIX."tuan where id in (".$id.")");
				$tourlines =  array();
				$tickets =  array();
				$hotelrooms = array();
				foreach($rel_ids as $k=>$v){
					if($v['type']==1)
						$tourlines[] = $v['rel_id'];
					if($v['type']==2)
						$tickets[] = $v['rel_id'];
					if($v['type']==3)
						$hotelrooms[] = $v['rel_id'];
				}
				
				$sql = "delete from ".DB_PREFIX."tuan where id in (".$id.") ";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->affected_rows()>0)
				{	
					//更新线路
					if(count($tourlines) > 0){
						$GLOBALS['db']->query("UPDATE ".DB_PREFIX."tourline SET is_tuan=0 WHERE id in (".implode(",",$tourlines).")");
					}
					//更新门票
					if(count($tickets) > 0){
						$GLOBALS['db']->query("UPDATE ".DB_PREFIX."ticket SET is_tuan=0 WHERE id in (".implode(",",$tickets).")");
					}
					//更新酒店
					if(count($hotelrooms) > 0){
						$GLOBALS['db']->query("UPDATE ".DB_PREFIX."hotel_room SET is_tuan=0 WHERE id in (".implode(",",$hotelrooms).")");
					}
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
}
?>