<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class tourline_insuranceModule extends AuthModule
{
	public function index()
	{		
		$param = array();
		//条件
		$condition = " 1 = 1 ";
		if(isset($_REQUEST['name']))
		$name = strim($_REQUEST['name']);
		else
		$name = "";
		$param['name'] = $name;
		if($name!='')
		{
			$condition.=" and name like '%".$name."%' ";
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
		
		
		$list = $GLOBALS['db']->getAll("select id,name,price,supplier_id from ".DB_PREFIX."insurance where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."insurance where ".$condition);
		
		foreach($list as $k=>$v)
		{
			if($v['supplier_id'] >0)
			{
				$list[$k]['supplier_company']=$GLOBALS['db']->getOne("select company_name from ".DB_PREFIX."supplier where id=".$v['supplier_id']."");
			}
			$list[$k]['price']=format_price(format_price_to_display($v['price']));		
		}

		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_insurance"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("tourline_insurance#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("tourline_insurance#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("tourline_insurance#add"));
		$GLOBALS['tmpl']->display("core/tourline_insurance/index.html");
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
				$dinsuranc_names = $GLOBALS['db']->getOne("select group_concat(name) from ".DB_PREFIX."insurance where id in (".$id.")");				
				$sql = "delete from ".DB_PREFIX."insurance where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->affected_rows()>0)
				save_log(lang("DEL").":".$dinsuranc_names, 1);
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
		$role_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."role");
		$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_insurance#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("searchsupplierurl",admin_url("supplier#search_supplier",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/tourline_insurance/add.html");
	}
	
	
	public function insert() {
		$ajax = intval($_REQUEST['ajax']);
		if(!check_empty("name"))
		{
			showErr(lang("INSURANCE_NAME_EMPTY_TIP"),$ajax);
		}
		if(!check_empty("price"))
		{
			showErr(lang("INSURANCE_PRICE_EMPTY_TIP"),$ajax);
		}
		// 更新数据
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['price'] = format_price_to_db(strim($_REQUEST['price']));
		$data['supplier_id'] = intval($_REQUEST['supplier_id']);
		$data['insurance_brief'] = strim($_REQUEST['insurance_brief']);
		$data['insurance_info'] = strim($_REQUEST['insurance_info']);
		$data['insurance_file']	=	format_domain_to_relative(btrim($_REQUEST['insurance_file']));
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."insurance",$data,"INSERT","","SILENT");		
		if ($GLOBALS['db']->error()=="") {		
			//成功提示
			save_log($log_info.lang("INSERT_SUCCESS"),1);
			showSuccess(lang("INSERT_SUCCESS"),$ajax,admin_url("tourline_insurance#add"));
		} else {
			//错误提示
			showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}
	}
	
	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."insurance where id = ".$id);
		
		//商家
		$vo['company_name'] = $GLOBALS['db']->getOne("select `company_name` from ".DB_PREFIX."supplier where id = ".$vo['supplier_id']);
		$GLOBALS['tmpl']->assign ( 'vo', $vo );
		
		$supplier_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id=".$vo['supplier_id']);
		$GLOBALS['tmpl']->assign("supplier_info",$supplier_info);
		
		$GLOBALS['tmpl']->assign("searchsupplierurl",admin_url("supplier#search_supplier",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_insurance#update",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/tourline_insurance/edit.html");
	}

	
	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		if(!check_empty("name"))
		{
			showErr(lang("INSURANCE_NAME_EMPTY_TIP"),$ajax);
		}
		if(!check_empty("price"))
		{
			showErr(lang("INSURANCE_PRICE_EMPTY_TIP"),$ajax);
		}
		$id = intval($_REQUEST['id']);
		// 更新数据
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['price'] = format_price_to_db(strim($_REQUEST['price']));
		$data['supplier_id'] = intval($_REQUEST['supplier_id']);
		$data['insurance_brief'] = strim($_REQUEST['insurance_brief']);
		$data['insurance_info'] = strim($_REQUEST['insurance_info']);
		$data['insurance_file']	=	format_domain_to_relative(btrim($_REQUEST['insurance_file']));
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."insurance",$data,"UPDATE","id=".$id,"SILENT");
		
		if ($GLOBALS['db']->error()=="") {			
			save_log($log_info.lang("UPDATE_SUCCESS"),1);
			showSuccess(lang("UPDATE_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}
	}
	
	public function search_insurance()
	{
		//处理保存下来的已选数据
		$this->assign_lookup_fields("id");
	
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
	
	
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."insurance where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."insurance where ".$condition);
	
		foreach($list as $k=>$v)
		{
			if($v['supplier_id']>0)
				$list[$k]['supplier_company']=$GLOBALS['db']->getOne("select company_name from ".DB_PREFIX."supplier where id=".$v['supplier_id']);
			else
				$list[$k]['supplier_company']="系统";
		}
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
	
		$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_insurance#search_insurance"));
		$GLOBALS['tmpl']->display("core/tourline_insurance/search_insurance.html");
	}

}
?>