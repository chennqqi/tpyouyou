<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class advModule extends AuthModule
{
	
	public function index()
	{						
		$param = array();		
		
		//条件
		$condition = " 1 = 1 ";
		if(isset($_REQUEST['adv_id']))
			$adv_id_key = strim($_REQUEST['adv_id']);
		else
			$adv_id_key = "";
		$param['adv_id'] = $adv_id_key;
		if($adv_id_key!='')
		{
			$condition.=" and adv_id = '".$adv_id_key."' ";
		}
		
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
			$param['orderField'] = "adv_id";
		
		if(isset($_REQUEST['orderDirection']))
			$param['orderDirection'] = strim($_REQUEST['orderDirection'])=="asc"?"asc":"desc";
		else
			$param['orderDirection'] = "desc";
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."adv  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."adv");
		
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("adv"));
		$GLOBALS['tmpl']->assign("seteffecturl",admin_url("adv#set_effect",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("delurl",admin_url("adv#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("adv#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("adv#add"));
		$GLOBALS['tmpl']->display("core/adv/index.html");
	}	
	
	
	public function foreverdelete()
	 {
		
		$ajax = intval($_REQUEST['ajax']);		
		if (isset ( $_REQUEST ['id'] ))
		{
			$id = strim($_REQUEST ['id']);	
			$id_key = format_ids_str_key($id);		
			if($id)
			{	
				$del_nav_name = $GLOBALS['db']->getOne("select group_concat(adv_id) from ".DB_PREFIX."adv where id in (".$id_key.")");
				$sql = "delete from ".DB_PREFIX."adv where id in (".$id_key.")";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->affected_rows()>0)
				save_log(lang("DEL").":".$del_nav_name, 1);
				$ids_arr = explode(",", $id);
				foreach($ids_arr as $id_item)
				clear_auto_cache("adv_cache");
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
		$GLOBALS['tmpl']->assign("searchcityurl",admin_url("tour_city#search_city"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("formaction",admin_url("adv#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/adv/add.html");
	}
	
	
	public function insert() {
		$ajax = intval($_REQUEST['ajax']);
		if(!check_empty("adv_id"))
		{
			showErr("广告位ID不能为空",$ajax);
		}
		if(!check_empty("name"))
		{
			showErr("广告位名称不能为空",$ajax);
		}
		
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['adv_id'] = strim($_REQUEST['adv_id']);
		
		if(intval($_REQUEST['show_all_city']) == 1){
			$city_info = $GLOBALS['db']->getRow("SELECT GROUP_CONCAT(`name`) AS tour_city_name,GROUP_CONCAT(`py`) AS tour_city_py FROM ".DB_PREFIX."tour_city ORDER BY py_first ASC");
			$data['city_match'] = format_fulltext_key($city_info['tour_city_py']);
			$data['city_match_row'] = $city_info['tour_city_name'];
		}
		else{
			$data['city_match'] = format_fulltext_key(strim($_REQUEST['tour_city_py']));
			$data['city_match_row'] = strim($_REQUEST['tour_city_name']);
		}
		
		$data['type'] = intval($_REQUEST['type']);
		if($data['type']==0)
		$data['code'] = format_domain_to_relative(strim($_REQUEST['code_img']));
		elseif($data['type']==1)
		$data['code'] = format_domain_to_relative(strim($_REQUEST['code_flash']));
		elseif($data['type']==2)
		$data['code'] = format_domain_to_relative(strim($_REQUEST['code_video']));
		
		$data['title'] = strim($_REQUEST['title']);
		$data['url'] = strim($_REQUEST['url']);
		$data['width'] = intval($_REQUEST['width']);
		$data['height'] = intval($_REQUEST['height']);
		$data['auto_play'] = intval($_REQUEST['auto_play']);
		
		preg_match("/[^A-Za-z0-9_]+/", $data['adv_id'],$matches);
		if($matches)
		{
			showErr("广告位ID只能是英文字母",$ajax);
		}
		
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."adv where adv_id ='".$data['adv_id']."' and match(`city_match`)  against ('".$data['city_match']."'  IN BOOLEAN MODE)")>0)
		{
			showErr("广告位已经存在",$ajax);
		}
		
		// 更新数据		
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."adv",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log($log_info.lang("INSERT_SUCCESS"),1);
			showSuccess(lang("INSERT_SUCCESS"),$ajax,admin_url("adv#add"));
		} else {
			//错误提示
			showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	
	
	public function edit() {		
		$id = strim($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."adv where id = '".$id."' ");
		
		if($vo['type']==0)
			$vo['code_img'] = $vo['code'];
		elseif($vo['type']==1)
			$vo['code_flash'] = $vo['code'];
		elseif($vo['type']==2)
			$vo['code_video'] = $vo['code'];
		
		$vo['city_match'] = unformat_fulltext_key($vo['city_match']);
		$GLOBALS['tmpl']->assign ( 'vo', $vo );
		$GLOBALS['tmpl']->assign("searchcityurl",admin_url("tour_city#search_city"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("formaction",admin_url("adv#update",array("ajax"=>1)));		
		$GLOBALS['tmpl']->display("core/adv/edit.html");
	}

	
	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);

		if(!check_empty("adv_id"))
		{
			showErr("广告位ID不能为空",$ajax);
		}
		if(!check_empty("name"))
		{
			showErr("广告位名称不能为空",$ajax);
		}
		
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		
		if(intval($_REQUEST['show_all_city']) == 1){
			$city_info = $GLOBALS['db']->getRow("SELECT GROUP_CONCAT(`name`) AS tour_city_name,GROUP_CONCAT(`py`) AS tour_city_py FROM ".DB_PREFIX."tour_city ORDER BY py_first ASC");
			$data['city_match'] = format_fulltext_key($city_info['tour_city_py']);
			$data['city_match_row'] = $city_info['tour_city_name'];
		}
		else{
			$data['city_match'] = format_fulltext_key(strim($_REQUEST['tour_city_py']));
			$data['city_match_row'] = strim($_REQUEST['tour_city_name']);
		}
		
		$data['type'] = intval($_REQUEST['type']);
		if($data['type']==0)
		$data['code'] = format_domain_to_relative(strim($_REQUEST['code_img']));
		elseif($data['type']==1)
		$data['code'] = format_domain_to_relative(strim($_REQUEST['code_flash']));
		elseif($data['type']==2)
		$data['code'] = format_domain_to_relative(strim($_REQUEST['code_video']));
		
		$data['title'] = strim($_REQUEST['title']);
		$data['url'] = strim($_REQUEST['url']);
		$data['width'] = intval($_REQUEST['width']);
		$data['height'] = intval($_REQUEST['height']);
		$data['auto_play'] = intval($_REQUEST['auto_play']);
		$data['adv_id'] = strim($_REQUEST['adv_id']);
		
		preg_match("/[^A-Za-z0-9_]+/", $data['adv_id'],$matches);
		if($matches)
		{
			showErr("广告位ID只能是英文字母",$ajax);
		}
		
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."adv where id <> $id and adv_id ='".$data['adv_id']."' and match(`city_match`)  against ('".$data['city_match']."'  IN BOOLEAN MODE)")>0)
		{
			showErr("广告位已经存在",$ajax);
		}
		
		// 更新数据		
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."adv",$data,"UPDATE","id='".$id."'","SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			clear_auto_cache("adv_cache");
			save_log($log_info.lang("UPDATE_SUCCESS"),1);
			showSuccess(lang("UPDATE_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	

}
?>