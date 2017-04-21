<?php

class tuan_cateModule extends AuthModule
{
    function index() {
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
			$condition.=" and name = '".$name_key."' ";
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
		
		
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tuan_cate where ".$condition);
		if($totalCount > 0)
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tuan_cate where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		foreach($list as $k=>$v)
		{
			$list[$k]['type_name'] = lang("TYPE_".$v['type']);
		}
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("tuan_cate"));
		$GLOBALS['tmpl']->assign("setsorturl",admin_url("tuan_cate#set_sort",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("delurl",admin_url("tuan_cate#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("tuan_cate#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("tuan_cate#add"));
		$GLOBALS['tmpl']->display("core/tuan_cate/index.html");
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
				$del_name = $GLOBALS['db']->getOne("select group_concat(name) from ".DB_PREFIX."tuan_cate where id in (".$id.")");			
				$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tuan where cate_id in (".$id.")");
				if($count > 0)
				{
					showErr("所选分类下有团购，无法删除",$ajax);	
				}
				$sql = "delete from ".DB_PREFIX."tuan_cate where id in (".$id.") and count=0 ";
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
		$sort = $GLOBALS['db']->getOne("select max(sort) from ".DB_PREFIX."tuan_cate")+1;	
		$GLOBALS['tmpl']->assign("sort",$sort);
		$GLOBALS['tmpl']->assign("formaction",admin_url("tuan_cate#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/tuan_cate/add.html");
	}
	
	
	
	public function insert() {
		$ajax = intval($_REQUEST['ajax']);
		if(!check_empty("name"))
		{
			showErr(lang("TUAN_CATE_NAME_EMPTY"),$ajax);
		}
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['type'] = intval($_REQUEST['type']);
		if($data['type']==0){
			showErr(lang("TUAN_CATE_TYPE_EMPTY"),$ajax);
		}
		$data['sort'] = intval($_REQUEST['sort']);
		$data['head_file']	=	format_domain_to_relative(btrim($_REQUEST['head_file']));
		
		// 更新数据
		
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."tuan_cate",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log($log_info.lang("INSERT_SUCCESS"),1);
			//rm_auto_cache("tuan_list",$param['cate_id'] =$GLOBALS['db']->insert_id() );
			showSuccess(lang("INSERT_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."tuan_cate where id = ".$id);
		$GLOBALS['tmpl']->assign ( 'vo', $vo );
		
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("tuan_cate#update",array("ajax"=>1)));
		
		$GLOBALS['tmpl']->display("core/tuan_cate/edit.html");
	}

	
	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);
		if(!check_empty("name"))
		{
			showErr(lang("TUAN_CATE_NAME_EMPTY"),$ajax);
		}
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['type'] = intval($_REQUEST['type']);
		if($data['type']==0){
			showErr(lang("TUAN_CATE_TYPE_EMPTY"),$ajax);
		}
		$data['sort'] = intval($_REQUEST['sort']);
		
		$data['count'] =  $GLOBALS['db']->getOne("SELECT COUNT(id) FROM ".DB_PREFIX."tuan WHERE cate_id=".$id );
		$data['head_file']	=	format_domain_to_relative(btrim($_REQUEST['head_file']));
		// 更新数据
		
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."tuan_cate",$data,"UPDATE","id=".$id,"SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log($log_info.lang("UPDATE_SUCCESS"),1);
			//rm_auto_cache("tuan_list",$param['cate_id'] =$id );
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
		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tuan_cate where id = ".$id);
		if($data)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."tuan_cate set sort = ".$sort." where id = ".$id);
			if($GLOBALS['db']->error()!="")
			{
				showErr($data['sort'],$ajax);
			}
			else
			{
				save_log($data['name'].lang("UPDATE_SUCCESS"),1);
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