<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class tour_provinceModule extends AuthModule
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
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_province where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tour_province where ".$condition);
		
		foreach($list as $k=>$v)
		{
			$list[$k]['is_effect_show'] = lang("IS_EFFECT_".$v['is_effect']);
		}
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("seteffecturl",admin_url("tour_province#set_effect",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("formaction",admin_url("tour_province"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("tour_province#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("tour_province#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("tour_province#add"));
		$GLOBALS['tmpl']->display("core/tour_province/index.html");
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
				$del_name = $GLOBALS['db']->getOne("select group_concat(name) from ".DB_PREFIX."tour_province where id in (".$id.")");	
				$del_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_province where id in (".$id.")");		
				$sql = "delete from ".DB_PREFIX."tour_province where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->affected_rows()>0)
				{
					foreach($del_list as $v)
					{
						$GLOBALS['db']->query("delete from ".DB_PREFIX."domain where domain = '".$v['py']."'");
					}
					save_domain_config();
					save_log(lang("DEL").":".$del_name, 1);
					rm_auto_cache("tour_province_list");  //自动删除缓存;
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

	
	
	public function add()
	{		
		$GLOBALS['tmpl']->assign("searchcityurl",admin_url("tour_city#search_city"),array("ajax"=>1));
		$GLOBALS['tmpl']->assign("formaction",admin_url("tour_province#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/tour_province/add.html");
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
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tour_province where py = '".strim($_REQUEST['py'])."'") > 0)
		{
			showErr(lang("PY_EXIST_TIP"),$ajax);
		}
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['py'] = strim($_REQUEST['py']);		
		$data['py_first'] =  strtoupper(substr($data['py'], 0,1));
		$data['city_match'] = format_fulltext_key(strim($_REQUEST['tour_city_py']));
		$data['city_match_row'] = strim($_REQUEST['tour_city_name']);
		
		// 更新数据		
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."tour_province",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log($log_info.lang("INSERT_SUCCESS"),1,admin_url("tour_province#add"));
			rm_auto_cache("tour_province_list");  //自动删除缓存;
			showSuccess(lang("INSERT_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."tour_province where id = ".$id);
		$vo['city_match'] = unformat_fulltext_key($vo['city_match']);
		$GLOBALS['tmpl']->assign ( 'vo', $vo );

		
		$GLOBALS['tmpl']->assign("formaction",admin_url("tour_province#update",array("ajax"=>1)));
		
		$GLOBALS['tmpl']->assign("searchcityurl",admin_url("tour_city#search_city"),array("ajax"=>1));
		$GLOBALS['tmpl']->display("core/tour_province/edit.html");
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
		
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tour_province where py = '".strim($_REQUEST['py'])."' and id>$id ") > 0)
		{
			showErr(lang("PY_EXIST_TIP"),$ajax);
		}
		
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."tour_province where id = ".$id);
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['py'] = strim($_REQUEST['py']);		
		$data['py_first'] =  strtoupper(substr($data['py'], 0,1));
		$data['city_match'] = format_fulltext_key(strim($_REQUEST['tour_city_py']));
		$data['city_match_row'] = strim($_REQUEST['tour_city_name']);

		// 更新数据
		
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."tour_province",$data,"UPDATE","id=".$id,"SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log($log_info.lang("UPDATE_SUCCESS"),1);
			rm_auto_cache("tour_province_list");  //自动删除缓存;
			showSuccess(lang("UPDATE_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."tour_province where id = ".$id);
		$c_is_effect =  $GLOBALS['db']->getOne("select is_effect from ".DB_PREFIX."tour_province where id = ".$id); //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		$GLOBALS['db']->query("update ".DB_PREFIX."tour_province set is_effect = ".$n_is_effect." where id = ".$id);
		save_log($info.lang("SET_EFFECT_".$n_is_effect),1);
		rm_auto_cache("tour_province_list");  //自动删除缓存;
		showSuccess(lang("SET_EFFECT_".$n_is_effect),$ajax)	;
	}
	
	
	public function search_province()
	{
		//处理保存下来的已选数据
		$this->assign_lookup_fields("py");
	
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
	
	
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_province where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tour_province where ".$condition);
	
		foreach($list as $k=>$v)
		{
			$list[$k]['is_effect_show'] = lang("IS_EFFECT_".$v['is_effect']);
		}
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
	
		$GLOBALS['tmpl']->assign("formaction",admin_url("tour_province#search_province"));
		$GLOBALS['tmpl']->display("core/tour_province/search_province.html");
	}
	
}
?>