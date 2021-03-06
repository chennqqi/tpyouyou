<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class cityModule extends AuthModule
{	
	public function index()
	{				
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
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."city where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."city where ".$condition);
		
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("city"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("city#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("city#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("city#add"));
		$GLOBALS['tmpl']->display("core/city/index.html");
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
				$del_name = $GLOBALS['db']->getOne("select group_concat(name) from ".DB_PREFIX."city where id in (".$id.")");			
				$sql = "delete from ".DB_PREFIX."city where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->affected_rows()>0)
				{
					$GLOBALS['db']->query("delete from ".DB_PREFIX."supplier_freight where city_id in (".$id.")");
					save_log(lang("DEL").":".$del_name, 1);
				}
				clear_auto_cache("city_list");
				make_region_city_js();
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
		$province_list = load_auto_cache("province_list");
		$GLOBALS['tmpl']->assign("province_list",$province_list);
		$GLOBALS['tmpl']->assign("formaction",admin_url("city#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/city/add.html");
	}
	
	
	public function insert() {
		$ajax = intval($_REQUEST['ajax']);
		if(!check_empty("name"))
		{
			showErr(lang("CITY_NAME_EMPTY_TIP"),$ajax);
		}
		if(!check_empty("py"))
		{
			showErr(lang("PY_NAME_EMPTY_TIP"),$ajax);
		}
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['pid'] = intval($_REQUEST['pid']);
		$data['py'] = strim($_REQUEST['py']);		
		$data['py_first'] =  strtoupper(substr($data['py'], 0,1));
				
		// 更新数据
		
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."city",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log($log_info.lang("INSERT_SUCCESS"),1);
			clear_auto_cache("city_list");
			make_region_city_js();
			showSuccess(lang("INSERT_SUCCESS"),$ajax,admin_url("city#add"));
		} else {
			//错误提示
			showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."city where id = ".$id);
		$GLOBALS['tmpl']->assign ( 'vo', $vo );
		
		$province_list = load_auto_cache("province_list");
		$GLOBALS['tmpl']->assign("province_list",$province_list);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("city#update",array("ajax"=>1)));
		
		$GLOBALS['tmpl']->display("core/city/edit.html");
	}

	
	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);
		if(!check_empty("name"))
		{
			showErr(lang("CITY_NAME_EMPTY_TIP"),$ajax);
		}
		if(!check_empty("py"))
		{
			showErr(lang("PY_NAME_EMPTY_TIP"),$ajax);
		}
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['pid'] = intval($_REQUEST['pid']);
		$data['py'] = strim($_REQUEST['py']);		
		$data['py_first'] =  strtoupper(substr($data['py'], 0,1));

		// 更新数据
		
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."city",$data,"UPDATE","id=".$id,"SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log($log_info.lang("UPDATE_SUCCESS"),1);
			clear_auto_cache("city_list");
			make_region_city_js();
			showSuccess(lang("UPDATE_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	public function load_city()
	{
		$city_all = load_auto_cache("city_list");
		$province_id = intval($_REQUEST['province_id']);
		$city_list = $city_all[$province_id];
		if($city_list)
		{
			$result['status'] = true;
			$result['list'] = $city_list;
		}
		else
		{
			$result['status'] = false;
		}
		ajax_return($result);
	}
	
	
	
}
?>