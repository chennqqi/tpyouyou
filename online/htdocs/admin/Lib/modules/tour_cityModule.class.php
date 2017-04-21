<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class tour_cityModule extends AuthModule
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
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_city where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tour_city where ".$condition);
		
		foreach($list as $k=>$v)
		{
			$list[$k]['is_effect_show'] = lang("IS_EFFECT_".$v['is_effect']);
			$list[$k]['is_default_show'] = lang("IS_EFFECT_".$v['is_default']);
		}
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("seteffecturl",admin_url("tour_city#set_effect",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("setdefaulturl",admin_url("tour_city#set_default",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("formaction",admin_url("tour_city"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("tour_city#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("tour_city#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("tour_city#add"));
		$GLOBALS['tmpl']->display("core/tour_city/index.html");
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
				if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tour_city where is_default = 1 and id in (".$id.")")>0)
				{
					showErr("默认城市不可删除",$ajax);
				}
				$del_name = $GLOBALS['db']->getOne("select group_concat(name) from ".DB_PREFIX."tour_city where id in (".$id.")");	
				$del_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_city where id in (".$id.")");		
				$sql = "delete from ".DB_PREFIX."tour_city where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->affected_rows()>0)
				{
					foreach($del_list as $v)
					{
						$GLOBALS['db']->query("delete from ".DB_PREFIX."domain where domain = '".$v['py']."'");
						$param_city['tuan_city'] =$v['py'];
						rm_auto_cache("tuan_list",$param_city);
					}
					save_domain_config();
					clear_auto_cache("dh_city_list");
					clear_auto_cache("tour_city_list");
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

	
	
	public function add()
	{		
		$GLOBALS['tmpl']->assign("formaction",admin_url("tour_city#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/tour_city/add.html");
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
		$data['py'] = strim($_REQUEST['py']);		
		$data['py_first'] =  strtoupper(substr($data['py'], 0,1));
		$data['is_hot'] = intval($_REQUEST['is_hot']);
		$data['is_effect'] = intval($_REQUEST['is_effect']);
		
		/*SEO设置*/
		$data['seo_title'] = strim($_REQUEST['seo_title']);
		$data['seo_keywords'] = strim($_REQUEST['seo_keywords']);
		$data['seo_description'] = strim($_REQUEST['seo_description']);
		
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tour_city")==0)
			$data['is_default'] = 1;

		$py = update_domain_config($data['py']);
		if($py=="")
			showErr(lang("PY_EXIST_TIP"),$ajax);
		
		// 更新数据		
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."tour_city",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			clear_auto_cache("dh_city_list");
			$param_city['tuan_city'] =$data['py'];
			rm_auto_cache("tuan_list",$param_city);	
			rm_auto_cache("tour_city_list");		
			save_log($log_info.lang("INSERT_SUCCESS"),1);
			showSuccess(lang("INSERT_SUCCESS"),$ajax,admin_url("tour_city#add"));
		} else {
			//错误提示
			showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."tour_city where id = ".$id);
		$GLOBALS['tmpl']->assign ( 'vo', $vo );

		
		$GLOBALS['tmpl']->assign("formaction",admin_url("tour_city#update",array("ajax"=>1)));
		
		$GLOBALS['tmpl']->display("core/tour_city/edit.html");
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
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."tour_city where id = ".$id);
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['py'] = strim($_REQUEST['py']);		
		$data['py_first'] =  strtoupper(substr($data['py'], 0,1));
		$data['is_hot'] = intval($_REQUEST['is_hot']);
		$data['is_effect'] = intval($_REQUEST['is_effect']);
		
		/*SEO设置*/
		$data['seo_title'] = strim($_REQUEST['seo_title']);
		$data['seo_keywords'] = strim($_REQUEST['seo_keywords']);
		$data['seo_description'] = strim($_REQUEST['seo_description']);
		
 		$GLOBALS['db']->query("update ".DB_PREFIX."domain set domain = '".$data['py']."' where domain = '".$vo['py']."'","SILENT");
		if($GLOBALS['db']->error()!="")
		{
			showErr(lang("PY_EXIST_TIP"),$ajax);
		}
		save_domain_config();

		// 更新数据
		
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."tour_city",$data,"UPDATE","id=".$id,"SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			clear_auto_cache("dh_city_list");			
			$param_city['tuan_city'] =$data['py'];
			rm_auto_cache("tuan_list",$param_city);
			rm_auto_cache("tour_city_list");
			save_log($log_info.lang("UPDATE_SUCCESS"),1);
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
		$info = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."tour_city where id = ".$id);
		$c_is_effect =  $GLOBALS['db']->getOne("select is_effect from ".DB_PREFIX."tour_city where id = ".$id); //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		if($n_is_effect==0&&$info['is_default']==1)
		{
			showErr("默认城市不可禁用",$ajax);
		}
		$GLOBALS['db']->query("update ".DB_PREFIX."tour_city set is_effect = ".$n_is_effect." where id = ".$id);
		save_log($info.lang("SET_EFFECT_".$n_is_effect),1);
		showSuccess(lang("SET_EFFECT_".$n_is_effect),$ajax)	;
	}
	
	public function set_default()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."tour_city where id = ".$id);		
		$GLOBALS['db']->query("update ".DB_PREFIX."tour_city set is_default = 1 where id = ".$id);
		$GLOBALS['db']->query("update ".DB_PREFIX."tour_city set is_default = 0 where id <> ".$id);
		save_log($info,1);
		showSuccess("设置成功",$ajax)	;
	}
	
	
	public function search_city()
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
	
	
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_city where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tour_city where ".$condition);
	
		foreach($list as $k=>$v)
		{
			$list[$k]['is_effect_show'] = lang("IS_EFFECT_".$v['is_effect']);
		}
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
	
		$GLOBALS['tmpl']->assign("formaction",admin_url("tour_city#search_city"));
		$GLOBALS['tmpl']->display("core/tour_city/search_city.html");
	}
	function search_city_radio(){
    	$selected_data['id'] =  strim($_REQUEST['selected_id']);
		$selected_data['name'] = strim($_REQUEST['selected_name']);
		$GLOBALS['tmpl']->assign("selected_data_str",json_encode($selected_data));
		$GLOBALS['tmpl']->assign("selected_data",$selected_data);
		
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
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_city where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tour_city where ".$condition);
	
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("tour_city#search_city_radio"));	
		
    	$GLOBALS['tmpl']->display("core/tour_city/search_city_radio.html");
    }
}
?>