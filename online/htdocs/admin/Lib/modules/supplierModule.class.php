<?php

// +----------------------------------------------------------------------

// | Fanwe 乐程旅游b2b

// +----------------------------------------------------------------------

// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.

// +----------------------------------------------------------------------

// | Author: 同创网络(778251855@qq.com)

// +----------------------------------------------------------------------





class supplierModule extends AuthModule

{

	public function index()

	{

		$param = array();

		//条件

		$condition = " 1 = 1 ";

		if(isset($_REQUEST['user_name']))

			$user_name_key = strim($_REQUEST['user_name']);

		else

			$user_name_key = "";

		$param['user_name'] = $user_name_key;

		if($user_name_key!='')

		{

			$condition.=" and user_name = '".$user_name_key."' ";

		}



		if(isset($_REQUEST['company_name']))

			$company_name_key = strim($_REQUEST['company_name']);

		else

			$company_name_key = "";

		$param['company_name'] = $company_name_key;

		if($company_name_key!='')

		{

			$condition.=" and company_name like '%".$company_name_key."%' ";

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





		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);

		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier where ".$condition);



		foreach($list as $k=>$v)

		{

			$list[$k]['is_verify'] = $v['is_verify']==1?lang("YES"):lang("NO");

			$list[$k]['create_time'] = to_date($v['create_time']);

		}



		$GLOBALS['tmpl']->assign('list',$list);

		$GLOBALS['tmpl']->assign('totalCount',$totalCount);

		$GLOBALS['tmpl']->assign('param',$param);



		$GLOBALS['tmpl']->assign("formaction",admin_url("supplier"));

		$GLOBALS['tmpl']->assign("setsorturl",admin_url("supplier#set_sort",array("ajax"=>1)));

		$GLOBALS['tmpl']->assign("delurl",admin_url("supplier#foreverdelete",array('ajax'=>1)));

		$GLOBALS['tmpl']->assign("editurl",admin_url("supplier#edit"));

		$GLOBALS['tmpl']->assign("addurl",admin_url("supplier#add"));

		$GLOBALS['tmpl']->display("core/supplier/index.html");

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

				$del_name = $GLOBALS['db']->getOne("select group_concat(user_name) from ".DB_PREFIX."supplier where id in (".$id.")");

				$sql = "delete from ".DB_PREFIX."supplier where id in (".$id.")";

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







	public function add()

	{

		$sort = $GLOBALS['db']->getOne("select max(sort) from ".DB_PREFIX."supplier")+1;

		$GLOBALS['tmpl']->assign("sort",$sort);

		$GLOBALS['tmpl']->assign("formaction",admin_url("supplier#insert",array("ajax"=>1)));

		$GLOBALS['tmpl']->display("core/supplier/add.html");

	}





	public function insert() {

		$ajax = intval($_REQUEST['ajax']);

		if(!check_empty("user_name"))

		{

			showErr(lang("SUPPLIER_USER_NAME_EMPTY"),$ajax);

		}

		if(!check_empty("user_pwd"))

		{

			showErr(lang("SUPPLIER_USER_PWD_EMPTY"),$ajax);

		}

		$data = array();

		$data['user_name'] = strim($_REQUEST['user_name']);

		$data['user_pwd'] = md5(strim($_REQUEST['user_pwd']));

		$data['sort'] = intval($_REQUEST['sort']);

		$data['contact_name'] = strim($_REQUEST['contact_name']);

		$data['contact_sex'] = intval($_REQUEST['contact_sex']);

		$data['contact_mobile'] = strim($_REQUEST['contact_mobile']);

		$data['contact_tel'] = strim($_REQUEST['contact_tel']);

		$data['contact_fax'] = strim($_REQUEST['contact_fax']);

		$data['contact_qq'] = strim($_REQUEST['contact_qq']);

		$data['contact_email'] = strim($_REQUEST['contact_email']);

		$data['company_name'] = strim($_REQUEST['company_name']);

		$data['company_address'] = strim($_REQUEST['company_address']);

		$data['company_zip'] = strim($_REQUEST['company_zip']);

		$data['company_person'] = intval($_REQUEST['company_person']);

		$data['company_regist'] = intval($_REQUEST['company_regist']);

		$data['company_description']	=	format_domain_to_relative(strim($_REQUEST['company_description']));

		$data['company_electronco']	=	format_domain_to_relative(strim($_REQUEST['company_electronco']));

		$data['is_verify'] = intval($_REQUEST['is_verify']);

		$data['create_time'] = NOW_TIME;

		$data['logo'] = format_domain_to_relative(strim($_REQUEST['logo']));

		$data['is_cooperation'] = intval($_REQUEST['is_cooperation']);



		// 更新数据



		$log_info = $data['user_name'];

		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier",$data,"INSERT","","SILENT");

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

		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".$id);

		$GLOBALS['tmpl']->assign ( 'vo', $vo );





		$GLOBALS['tmpl']->assign("formaction",admin_url("supplier#update",array("ajax"=>1)));



		$GLOBALS['tmpl']->display("core/supplier/edit.html");

	}





	public function update() {

		$ajax = intval($_REQUEST['ajax']);

		$id = intval($_REQUEST['id']);

		if(!check_empty("user_name"))

		{

			showErr(lang("SUPPLIER_USER_NAME_EMPTY"),$ajax);

		}

		$data = array();

		$data['user_name'] = strim($_REQUEST['user_name']);

		if(trim($_REQUEST['user_pwd'])!="")

			$data['user_pwd'] = md5(strim($_REQUEST['user_pwd']));

		$data['sort'] = intval($_REQUEST['sort']);

		$data['contact_name'] = strim($_REQUEST['contact_name']);

		$data['contact_sex'] = intval($_REQUEST['contact_sex']);

		$data['contact_mobile'] = strim($_REQUEST['contact_mobile']);

		$data['contact_tel'] = strim($_REQUEST['contact_tel']);

		$data['contact_fax'] = strim($_REQUEST['contact_fax']);

		$data['contact_qq'] = strim($_REQUEST['contact_qq']);

		$data['contact_email'] = strim($_REQUEST['contact_email']);

		$data['company_name'] = strim($_REQUEST['company_name']);

		$data['company_address'] = strim($_REQUEST['company_address']);

		$data['company_zip'] = strim($_REQUEST['company_zip']);

		$data['company_person'] = intval($_REQUEST['company_person']);

		$data['company_regist'] = intval($_REQUEST['company_regist']);

		$data['company_description']	=	format_domain_to_relative(strim($_REQUEST['company_description']));

		$data['company_electronco']	=	format_domain_to_relative(strim($_REQUEST['company_electronco']));

		$data['is_verify'] = intval($_REQUEST['is_verify']);

		$data['create_time'] = NOW_TIME;

		$data['logo'] = format_domain_to_relative(strim($_REQUEST['logo']));

		$data['is_cooperation'] = intval($_REQUEST['is_cooperation']);



		// 更新数据



		$log_info = $data['user_name'];

		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier",$data,"UPDATE","id=".$id,"SILENT");

		if ($GLOBALS['db']->error()=="") {

			//成功提示

			save_log($log_info.lang("UPDATE_SUCCESS"),1);

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

		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".$id);

		if($data)

		{

			$GLOBALS['db']->query("update ".DB_PREFIX."supplier set sort = ".$sort." where id = ".$id);

			if($GLOBALS['db']->error()!="")

			{

				showErr($data['sort'],$ajax);

			}

			else

			{

				save_log($data['user_name'].lang("UPDATE_SUCCESS"),1);

				showSuccess($sort,$ajax);

			}

		}

		else

		{

			showErr(0,$ajax);

		}

	}



	function search_supplier(){

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

			$condition.=" and company_name like '%".$name_key."%' ";

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





		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);

		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier where ".$condition);



		foreach($list as $k=>$v)

		{

			$list[$k]['is_verify_show'] = lang("BLANK_".$v['is_verify']);

		}

		$GLOBALS['tmpl']->assign('list',$list);

		$GLOBALS['tmpl']->assign('totalCount',$totalCount);

		$GLOBALS['tmpl']->assign('param',$param);



		$GLOBALS['tmpl']->assign("formaction",admin_url("supplier#search_supplier"));



		$GLOBALS['tmpl']->display("core/supplier/search_supplier.html");

	}

}

?>