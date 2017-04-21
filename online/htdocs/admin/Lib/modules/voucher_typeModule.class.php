<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class voucher_typeModule extends AuthModule
{	
	public function index()
	{				
		$param = array();		
		//条件
		$condition = " 1 = 1 ";
		if(isset($_REQUEST['voucher_name']))
			$name_key = strim($_REQUEST['voucher_name']);
		else
			$name_key = "";
		$param['voucher_name'] = $name_key;
		if($name_key!='')
		{
			$condition.=" and voucher_name like '%".$name_key."%' ";
		}
		
		if(isset($_REQUEST['voucher_money']))
			$money_key = format_price_to_db(floatval($_REQUEST['voucher_money']));
		else
			$money_key = "";
		
		if($money_key!='')
		{
			$param['voucher_money'] = format_price_to_display($money_key);
			$condition.=" and money = ".$money_key." ";
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
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."voucher_type where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."voucher_type where ".$condition);
		
		foreach($list as $k=>$v)
		{
			$list[$k]['deliver_end_time'] = $v['deliver_end_time']==0?"不限期":to_date($v['deliver_end_time'],"Y-m-d");
			$list[$k]['money'] = format_price(format_price_to_display($v['money']));
			$list[$k]['deliver_type'] = lang("DELIVER_TYPE_".$v['deliver_type']);
			$list[$k]['is_effect_show'] = lang("IS_EFFECT_".$v['is_effect']);
		}
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("setsorturl",admin_url("voucher_type#set_sort",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("seteffecturl",admin_url("voucher_type#set_effect",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("formaction",admin_url("voucher_type"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("voucher_type#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("voucher_type#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("voucher_type#add"));
		$GLOBALS['tmpl']->assign("viewlogurl",admin_url("voucher_type#viewlog"));
		$GLOBALS['tmpl']->display("core/voucher_type/index.html");
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
				$del_name = $GLOBALS['db']->getOne("select group_concat(voucher_name) from ".DB_PREFIX."voucher_type where id in (".$id.")");	
				$sql = "delete from ".DB_PREFIX."voucher_type where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->affected_rows()>0)
				{
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
	
	public function load_user_level()
	{
		$deliver_rel_id = intval($_REQUEST['deliver_rel_id']);
		$user_level = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."user_level");
		foreach($user_level as $k=>$v)
		{
			if($v['id']==$deliver_rel_id)
			$user_level[$k]['select'] = true;
		}
		ajax_return($user_level);
	}
	
	public function load_user_group()
	{		
		$deliver_rel_id = intval($_REQUEST['deliver_rel_id']);
		$user_group = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."user_group");
		foreach($user_group as $k=>$v)
		{
			if($v['id']==$deliver_rel_id)
				$user_group[$k]['select'] = true;
		}
		ajax_return($user_group);
	}

	
	
	public function add()
	{		
		$sort = $GLOBALS['db']->getOne("select max(sort) from ".DB_PREFIX."voucher_type")+1;
		$GLOBALS['tmpl']->assign("sort",$sort);
		$GLOBALS['tmpl']->assign("formaction",admin_url("voucher_type#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("loaduserlevel",admin_url("voucher_type#load_user_level",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("loadusergroup",admin_url("voucher_type#load_user_group",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/voucher_type/add.html");
	}
	
	
	public function insert() {
		$ajax = intval($_REQUEST['ajax']);
		if(!check_empty("voucher_name"))
		{
			showErr(lang("VOUCHER_NAME_EMPTY_TIP"),$ajax);
		}
		if(floatval($_REQUEST['money'])<=0)
		{
			showErr(lang("MONEY_INVALID_TIP"),$ajax);
		}
		$data = array();
		$data['voucher_name'] = strim($_REQUEST['voucher_name']);
		$data['sort'] = intval($_REQUEST['sort']);
		$data['money'] = format_price_to_db(floatval($_REQUEST['money']));
		$data['deliver_limit'] = intval($_REQUEST['deliver_limit']);
		$data['deliver_type'] = intval($_REQUEST['deliver_type']);
		if($data['deliver_type']==3)
		{
			$data['is_promote'] = 1;
			$data['deliver_rel_id'] = 0;
			$data['deliver_end_time'] = 0;
			$data['deliver_limit'] = 0;
		}
		else
		{
			$data['deliver_rel_id'] = intval($_REQUEST['deliver_rel_id']);
		}
		$data['deliver_end_time'] = !check_empty("deliver_end_time")?0:to_timespan(strim($_REQUEST['deliver_end_time']))+3600*24-1;
		$data['voucher_end_time'] =!check_empty("voucher_end_time")?0: to_timespan(strim($_REQUEST['voucher_end_time']))+3600*24-1;
		$data['is_effect'] = intval($_REQUEST['is_effect']);

		
		// 更新数据		
		$log_info = $data['voucher_name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."voucher_type",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log($log_info.lang("INSERT_SUCCESS"),1);
			showSuccess(lang("INSERT_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."voucher_type where id = ".$id);
		$vo['money'] = format_price_to_display($vo['money']);
		$vo['deliver_end_time'] = to_date($vo['deliver_end_time'],"Y-m-d");
		$vo['voucher_end_time'] = to_date($vo['voucher_end_time'],"Y-m-d");
		$GLOBALS['tmpl']->assign ( 'vo', $vo );

		$GLOBALS['tmpl']->assign("loaduserlevel",admin_url("voucher_type#load_user_level",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("loadusergroup",admin_url("voucher_type#load_user_group",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("formaction",admin_url("voucher_type#update",array("ajax"=>1)));		
		$GLOBALS['tmpl']->display("core/voucher_type/edit.html");
	}

	
	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);
		if(!check_empty("voucher_name"))
		{
			showErr(lang("VOUCHER_NAME_EMPTY_TIP"),$ajax);
		}
		if(floatval($_REQUEST['money'])<=0)
		{
			showErr(lang("MONEY_INVALID_TIP"),$ajax);
		}
		$data = array();
		$data['voucher_name'] = strim($_REQUEST['voucher_name']);
		$data['sort'] = intval($_REQUEST['sort']);
		$data['money'] = format_price_to_db(floatval($_REQUEST['money']));
		$data['deliver_limit'] = intval($_REQUEST['deliver_limit']);
		$data['deliver_type'] = intval($_REQUEST['deliver_type']);
		if($data['deliver_type']==3)
		{
			$data['deliver_rel_id'] = 0;
			$data['is_promote'] = 1;
			$data['deliver_end_time'] = 0;
			$data['deliver_limit'] = 0;
		}
		else
		{
			$data['deliver_rel_id'] = intval($_REQUEST['deliver_rel_id']);
		}
		$data['deliver_end_time'] = !check_empty("deliver_end_time")?0:to_timespan(strim($_REQUEST['deliver_end_time']))+3600*24-1;
		$data['voucher_end_time'] =!check_empty("voucher_end_time")?0: to_timespan(strim($_REQUEST['voucher_end_time']))+3600*24-1;
		$data['is_effect'] = intval($_REQUEST['is_effect']);
		
		// 更新数据
		
		$log_info = $data['voucher_name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."voucher_type",$data,"UPDATE","id=".$id,"SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
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
		$info = $GLOBALS['db']->getOne("select voucher_name from ".DB_PREFIX."voucher_type where id = ".$id);
		$c_is_effect =  $GLOBALS['db']->getOne("select is_effect from ".DB_PREFIX."voucher_type where id = ".$id); //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		$GLOBALS['db']->query("update ".DB_PREFIX."voucher_type set is_effect = ".$n_is_effect." where id = ".$id);
		save_log($info.lang("SET_EFFECT_".$n_is_effect),1);
		showSuccess(lang("SET_EFFECT_".$n_is_effect),$ajax)	;
	}
	
	public function set_sort()
	{
		$ajax = intval($_REQUEST['ajax']);
		$sort = intval($_REQUEST['sort']);
		$id = intval($_REQUEST['id']);
		$nav = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."voucher_type where id = ".$id);
		if($nav)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."voucher_type set sort = ".$sort." where id = ".$id);
			if($GLOBALS['db']->error()!="")
			{
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
	
	public function viewlog()
	{
		$param = array();
		//条件
		$condition = " 1 = 1 ";
		if(isset($_REQUEST['voucher_name']))
			$name_key = strim($_REQUEST['voucher_name']);
		else
			$name_key = "";
		$param['voucher_name'] = $name_key;
		if($name_key!='')
		{
			$condition.=" and voucher_name like '%".$name_key."%' ";
		}
		
		if(isset($_REQUEST['voucher_money']))
			$money_key = format_price_to_db(floatval($_REQUEST['voucher_money']));
		else
			$money_key = "";
		
		if($money_key!='')
		{
			$param['voucher_money'] = format_price_to_display($money_key);
			$condition.=" and money = ".$money_key." ";
		}
		
		if(isset($_REQUEST['type_id']))
			$type_id_key = intval($_REQUEST['type_id']);
		else
			$type_id_key = 0;
		
		if($type_id_key>0)
		{
			$param['type_id'] = $type_id_key;
			$condition.=" and voucher_type_id = ".$type_id_key." ";
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
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."voucher where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."voucher where ".$condition);
		
		foreach($list as $k=>$v)
		{
			$list[$k]['money'] = format_price(format_price_to_display($v['money']));
			$list[$k]['create_time'] = to_date($v['create_time']);
			$list[$k]['end_time'] = $v['end_time']==0?"不限期":to_date($v['end_time'],"Y-m-d");
			$list[$k]['user_name'] = get_user_name($v['user_id']);
			$list[$k]['is_use'] = $v['is_use'] == 0?"未使用":"已使用";
			$list[$k]['use_time'] = $v['use_time'] == 0?"未使用":to_date($v['use_time'],"Y-m-d");
			$list[$k]['is_effect_show'] = lang("IS_EFFECT_".$v['is_effect']);
		}
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("seteffecturl",admin_url("voucher#set_effect",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("formaction",admin_url("voucher_type#viewlog"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("voucher#foreverdelete",array('ajax'=>1)));
		$GLOBALS['tmpl']->display("core/voucher_type/viewlog.html");
	}
}
?>