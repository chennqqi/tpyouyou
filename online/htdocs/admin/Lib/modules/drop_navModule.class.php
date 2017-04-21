<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class drop_navModule extends AuthModule
{
	
	private $navs;
	
	public function __construct()
	{
		parent::__construct();
		$this->navs = require APP_ROOT_PATH."system/webnav_cfg.php";
	}
	
	public function index()
	{				
		$param = array();		
		
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
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."drop_nav  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."drop_nav");
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("drop_nav"));
		$GLOBALS['tmpl']->assign("setsorturl",admin_url("drop_nav#set_sort",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("delurl",admin_url("drop_nav#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("drop_nav#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("drop_nav#add"));
		$GLOBALS['tmpl']->display("core/drop_nav/index.html");
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
				$del_nav_name = $GLOBALS['db']->getOne("select group_concat(name) from ".DB_PREFIX."drop_nav where id in (".$id.")");
				$sql = "delete from ".DB_PREFIX."drop_nav where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->affected_rows()>0)
				save_log(lang("DEL").":".$del_nav_name, 1);
				rm_auto_cache("cache_drop_nav_list");  //自动删除缓存;
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
		$GLOBALS['tmpl']->assign("navs",$this->navs);
		$GLOBALS['tmpl']->assign("formaction",admin_url("drop_nav#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("loadmoduleurl",admin_url("drop_nav#load_module",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/drop_nav/add.html");
	}
	
	
	public function insert() {
		$ajax = intval($_REQUEST['ajax']);
		if(!check_empty("name"))
		{
			showErr(lang("NAV_NAME_EMPTY_TIP"),$ajax);
		}
		if(!check_empty("url")&&trim($_REQUEST['u_module'])=='')
		{
			showErr(lang("NAV_URL_EMPTY_TIP"),$ajax);
		}
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['url'] = strim($_REQUEST['url']);
		$data['sort'] = intval($_REQUEST['sort']);
		$data['u_module'] = strim($_REQUEST['u_module']);
		$data['u_action'] = strim($_REQUEST['u_action']);
		$data['u_param'] = btrim($_REQUEST['u_param']);
		$data['u_id'] = intval($_REQUEST['u_id']);
				
		if(trim($_REQUEST['u_module'])!='')
		{
			$data['url'] = '';
		}
		if($data['url']!='')
		{
			$data['u_module'] = '';
			$data['u_action'] = '';
			$data['u_id'] = '';
			$data['u_param'] = '';
		}
		if(!isset($_REQUEST['u_action']))
			$data['u_action'] = '';
		// 更新数据
		
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."drop_nav",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log($log_info.lang("INSERT_SUCCESS"),1);
			rm_auto_cache("cache_drop_nav_list");  //自动删除缓存;
			showSuccess(lang("INSERT_SUCCESS"),$ajax,admin_url("drop_nav#add"));
		} else {
			//错误提示
			showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."drop_nav where id = ".$id);
		$GLOBALS['tmpl']->assign ( 'vo', $vo );
		
		$GLOBALS['tmpl']->assign("navs",$this->navs);
		$GLOBALS['tmpl']->assign("formaction",admin_url("drop_nav#update",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("loadmoduleurl",admin_url("drop_nav#load_module",array("ajax"=>1,"id"=>$id)));
		
		$GLOBALS['tmpl']->display("core/drop_nav/edit.html");
	}

	
	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);
		if(!check_empty("name"))
		{
			showErr(lang("NAV_NAME_EMPTY_TIP"),$ajax);
		}
		if(!check_empty("url")&&trim($_REQUEST['u_module'])=='')
		{
			showErr(lang("NAV_URL_EMPTY_TIP"),$ajax);
		}
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['url'] = strim($_REQUEST['url']);
		$data['sort'] = intval($_REQUEST['sort']);
		$data['u_module'] = strim($_REQUEST['u_module']);
		$data['u_action'] = strim($_REQUEST['u_action']);
		$data['u_param'] = btrim($_REQUEST['u_param']);
		$data['u_id'] = intval($_REQUEST['u_id']);
				
		if(trim($_REQUEST['u_module'])!='')
		{
			$data['url'] = '';
		}
		if($data['url']!='')
		{
			$data['u_module'] = '';
			$data['u_action'] = '';
			$data['u_id'] = '';
			$data['u_param'] = '';
		}
		if(!isset($_REQUEST['u_action']))
			$data['u_action'] = '';
		// 更新数据
		
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."drop_nav",$data,"UPDATE","id=".$id,"SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log($log_info.lang("UPDATE_SUCCESS"),1);
			rm_auto_cache("cache_drop_nav_list");  //自动删除缓存;
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
		$nav = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."drop_nav where id = ".$id);
		if($nav)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."drop_nav set sort = ".$sort." where id = ".$id);
			if($GLOBALS['db']->error()!="")
			{
				showErr($nav['sort'],$ajax);
			}
			else
			{
				save_log($nav['name'].lang("UPDATE_SUCCESS"),1);
				rm_auto_cache("cache_drop_nav_list");  //自动删除缓存;
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