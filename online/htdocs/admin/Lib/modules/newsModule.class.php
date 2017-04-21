<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class newsModule extends AuthModule
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
		
		if(isset($_REQUEST['cate_id']))
			$cate_id_key = intval($_REQUEST['cate_id']);
		else
			$cate_id_key = "";
		$param['cate_id'] = $cate_id_key;
		if($cate_id_key>0)
		{
			$condition.=" and cate_id = '".$cate_id_key."' ";
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
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."news where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."news where ".$condition);
		
		$cate_list_res = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."news_cate order by sort");
		$cate_list = array();
		foreach($cate_list_res as $k=>$v)
		{
			$cate_list[$v['id']] = $v;
		}
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);
		
		foreach($list as $k=>$v)
		{
			$list[$k]['is_recommend_show'] = $v['is_recommend']==1?lang("YES"):lang("NO");
			$list[$k]['create_time'] = to_date($v['create_time']);
			$list[$k]['cate_name'] = $cate_list[$v['cate_id']]['name'];
			$list[$k]['is_hot'] = $v['is_hot']==1?lang("YES"):lang("NO");
			$list[$k]['is_loop'] = $v['is_loop']==1?lang("YES"):lang("NO");
		}
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("news"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("news#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("news#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("news#add"));
		$GLOBALS['tmpl']->assign("setrecommendurl",admin_url("news#set_recommend",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("setsorturl",admin_url("news#set_sort",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/news/index.html");
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
				$del_name = $GLOBALS['db']->getOne("select group_concat(name) from ".DB_PREFIX."news where id in (".$id.")");			
				$sql = "delete from ".DB_PREFIX."news where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->affected_rows()>0)
				{
					save_log(lang("DEL").":".$del_name, 1);
				}
				clear_auto_cache("hot_news");
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
		$sort = $GLOBALS['db']->getOne("select max(sort) from ".DB_PREFIX."news")+1;		
		$GLOBALS['tmpl']->assign("sort",$sort);
		$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."news_cate order by sort");
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);
		$GLOBALS['tmpl']->assign("formaction",admin_url("news#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/news/add.html");
	}
	
	
	public function insert() {
		$ajax = intval($_REQUEST['ajax']);
		if(!check_empty("name"))
		{
			showErr("名称不能为空",$ajax);
		}
		if(!check_empty("content"))
		{
			showErr("内容不能为空",$ajax);
		}
		if(!check_empty("cate_id"))
		{
			showErr("请选择分类",$ajax);
		}
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['brief'] = strim($_REQUEST['brief']);
		$data['image'] = format_domain_to_relative(strim($_REQUEST['image']));
		$data['content'] = format_domain_to_relative(btrim($_REQUEST['content']));
		$data['cate_id'] = intval($_REQUEST['cate_id']);
		$data['create_time'] = NOW_TIME;
		$data['source']	=	strim($_REQUEST['source']);
		$data['author']	=	strim($_REQUEST['author']);
		
		$data['is_recommend'] = intval($_REQUEST['is_recommend']);
		$data['sort'] = intval($_REQUEST['sort']);
		$data['is_loop'] = intval($_REQUEST['is_loop']);
		$data['is_hot'] = intval($_REQUEST['is_hot']);
		if($data['image']!="")
			$data['is_image'] = 1;
		else
			$data['is_image'] = 0;
	
				
		// 更新数据
		
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."news",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			clear_auto_cache("hot_news");
			save_log($log_info.lang("INSERT_SUCCESS"),1);
			showSuccess(lang("INSERT_SUCCESS"),$ajax,admin_url("news#add"));
		} else {
			//错误提示
			showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."news where id = ".$id);
		$GLOBALS['tmpl']->assign ( 'vo', $vo );
		$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."news_cate order by sort");
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);
		$GLOBALS['tmpl']->assign("formaction",admin_url("news#update",array("ajax"=>1)));
		
		$GLOBALS['tmpl']->display("core/news/edit.html");
	}

	
	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);
		if(!check_empty("name"))
		{
			showErr("名称不能为空",$ajax);
		}
		if(!check_empty("content"))
		{
			showErr("内容不能为空",$ajax);
		}
		if(!check_empty("cate_id"))
		{
			showErr("请选择分类",$ajax);
		}
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['brief'] = strim($_REQUEST['brief']);
		$data['image'] = format_domain_to_relative(strim($_REQUEST['image']));
		$data['content'] = format_domain_to_relative(btrim($_REQUEST['content']));
		$data['cate_id'] = intval($_REQUEST['cate_id']);
		$data['create_time'] = NOW_TIME;
		$data['source']	=	strim($_REQUEST['source']);
		$data['author']	=	strim($_REQUEST['author']);
		
		$data['is_recommend'] = intval($_REQUEST['is_recommend']);
		$data['sort'] = intval($_REQUEST['sort']);
		$data['is_loop'] = intval($_REQUEST['is_loop']);
		$data['is_hot'] = intval($_REQUEST['is_hot']);
		if($data['image']!="")
			$data['is_image'] = 1;
		else
			$data['is_image'] = 0;

		// 更新数据
		
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."news",$data,"UPDATE","id=".$id,"SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			clear_auto_cache("hot_news");
			save_log($log_info.lang("UPDATE_SUCCESS"),1);
			showSuccess(lang("UPDATE_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	public function set_recommend()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."news where id = ".$id);
		$c_is_effect =  $GLOBALS['db']->getOne("select is_recommend from ".DB_PREFIX."news where id = ".$id); //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		$GLOBALS['db']->query("update ".DB_PREFIX."news set is_recommend = ".$n_is_effect." where id = ".$id);
		save_log($info.lang("SET_EFFECT_".$n_is_effect),1);
		showSuccess(lang("SET_EFFECT_".$n_is_effect),$ajax)	;
	}
	
	
	public function set_sort()
	{
		$ajax = intval($_REQUEST['ajax']);
		$sort = intval($_REQUEST['sort']);
		$id = intval($_REQUEST['id']);
		$nav = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."news where id = ".$id);
		if($nav)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."news set sort = ".$sort." where id = ".$id);
			if($GLOBALS['db']->error()!="")
			{
				clear_auto_cache("hot_news");
				showErr($nav['sort'],$ajax);
			}
			else
			{
				save_log($nav['name'].lang("UPDATE_SUCCESS"),1);
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