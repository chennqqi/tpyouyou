<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class voucherModule extends AuthModule
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
		
		if(isset($_REQUEST['user_id']))
			$user_id = intval($_REQUEST['user_id']);
		$param['user_id'] = $user_id;
		if($user_id>0)
		{
			$condition.=" and user_id = '".$user_id."' ";
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
		$GLOBALS['tmpl']->assign("formaction",admin_url("voucher"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("voucher#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->display("core/voucher/index.html");
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
				$del_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."voucher where id in (".$id.")");						
				$sql = "delete from ".DB_PREFIX."voucher where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->affected_rows()>0)
				{
					foreach($del_list as $k=>$v)
					{
						$del_name = "删除了".get_user_name($v['user_id'])."的".$v['voucher_name'];
						$GLOBALS['db']->query("update ".DB_PREFIX."voucher_type set deliver_count = deliver_count - 1 where id = ".$v['voucher_type_id']);
						save_log(lang("DEL").":".$del_name, 1);
					}					
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
	
	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."voucher where id = ".$id);
		$info = get_user_name($data['user_id'])."的".$data["voucher_name"];
		$c_is_effect =  $GLOBALS['db']->getOne("select is_effect from ".DB_PREFIX."voucher where id = ".$id); //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		$GLOBALS['db']->query("update ".DB_PREFIX."voucher set is_effect = ".$n_is_effect." where id = ".$id);
		save_log($info.lang("SET_EFFECT_".$n_is_effect),1);
		showSuccess(lang("SET_EFFECT_".$n_is_effect),$ajax)	;
	}

}
?>