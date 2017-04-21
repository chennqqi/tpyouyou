<?php

class spot_cateModule extends AuthModule
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
				
		
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."spot_cate where ".$condition);
		if($totalCount > 0){
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."spot_cate where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
			foreach($list as $k=>$v)
			{
				$list[$k]['is_hot_show'] = lang("BLANK_".$v['is_hot']);
			}
		}
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("spot_cate"));
		$GLOBALS['tmpl']->assign("sethoturl",admin_url("spot_cate#set_hot",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("setrecurl",admin_url("spot_cate#set_rec",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("setidxcurl",admin_url("spot_cate#set_idx",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("setsorturl",admin_url("spot_cate#set_sort",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("delurl",admin_url("spot_cate#foreverdelete",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("spot_cate#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("spot_cate#add"));
		$GLOBALS['tmpl']->display("core/spot_cate/index.html");
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
				$del_name = $GLOBALS['db']->getOne("select group_concat(name) from ".DB_PREFIX."spot_cate where id in (".$id.")");			
				$sql = "delete from ".DB_PREFIX."spot_cate where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->affected_rows()>0)
				{					
					save_log(lang("DEL").":".$del_name, 1);
					rm_auto_cache("spot_cate_list");  //自动删除缓存;
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
		$sort = $GLOBALS['db']->getOne("select max(sort) from ".DB_PREFIX."spot_cate")+1;
		
		$GLOBALS['tmpl']->assign("sort",$sort);
		$GLOBALS['tmpl']->assign("formaction",admin_url("spot_cate#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("areatype1",admin_url("tour_area#search_area",array("ajax"=>1,"type"=>1)));
		$GLOBALS['tmpl']->assign("areatype2",admin_url("tour_area#search_area",array("ajax"=>1,"type"=>2)));
		$GLOBALS['tmpl']->display("core/spot_cate/add.html");
	}
	
	
	
	public function insert() {
		$ajax = intval($_REQUEST['ajax']);
		if(!check_empty("name"))
		{
			showErr(lang("SPOT_CATE_NAME_EMPTY"),$ajax);
		}
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['is_hot'] = intval($_REQUEST['is_hot']);
		$data['is_recommend'] = intval($_REQUEST['is_recommend']);
		$data['is_index'] = intval($_REQUEST['is_index']);
		$data['index_adv1'] = format_domain_to_relative(strim($_REQUEST['index_adv1']));
		$data['index_adv1_url'] = strim($_REQUEST['index_adv1_url']);
		$data['index_adv2'] = format_domain_to_relative(strim($_REQUEST['index_adv2']));
		$data['index_adv2_url'] = strim($_REQUEST['index_adv2_url']);
		$data['sort'] = intval($_REQUEST['sort']);

		if($data['is_index'])
		{
			$tour_area1_py_array=array();
			$tour_area2_py_array=array();
			$tour_area1_py=strim($_REQUEST['tour_area1_py']);
			$tour_area2_py=strim($_REQUEST['tour_area2_py']);
			
			if($tour_area1_py != '')
			{
				$tour_area1_py_array=explode(',',$tour_area1_py);
			}
			if($tour_area2_py != '')
			{
				$tour_area2_py_array=explode(',',$tour_area2_py);
			}
			
			$tour_area=implode("','",array_merge($tour_area1_py_array,$tour_area2_py_array));
			if($tour_area !='')
			{
				$area=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_area where py in('".$tour_area."') order by is_recommend desc,py desc");
				$insert_area=array();
				$insert_area['area1']=array();
				$insert_area['area2']=array();
				$i=0;
				$j=0;
				foreach($area as $k=>$v)
				{
					if($v['type']==1)
					{
						$insert_area['area1'][$i]=$v;
						++$i;
					}
					elseif($v['type']==2)
					{	
						$insert_area['area2'][$j]=$v;
						++$j;
					}
				}
				
				$insert_area["area1_py"]=$tour_area1_py;
				$insert_area["area1_name"]=strim($_REQUEST['tour_area1_name']);
				$insert_area["area2_py"]=$tour_area2_py;
				$insert_area["area2_name"]=strim($_REQUEST['tour_area2_name']);
				$data['rec_indexarea']=serialize($insert_area);
			}
		}
		else
		{
			$data['rec_indexarea']='';
		}
		
		// 更新数据
		
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."spot_cate",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log($log_info.lang("INSERT_SUCCESS"),1);
			rm_auto_cache("spot_cate_list");  //自动删除缓存;
			showSuccess(lang("INSERT_SUCCESS"),$ajax,admin_url("spot_cate#index"));
		} else {
			//错误提示
			showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."spot_cate where id = ".$id);
		if($vo['is_index'] ==1)
		{
			$rec_indexarea=unserialize($vo['rec_indexarea']);
			$vo['area1_py']=$rec_indexarea['area1_py'];
			$vo['area1_name']=$rec_indexarea['area1_name'];
			$vo['area2_py']=$rec_indexarea['area2_py'];
			$vo['area2_name']=$rec_indexarea['area2_name'];
		}
		
		$GLOBALS['tmpl']->assign ( 'vo', $vo );
		$GLOBALS['tmpl']->assign("formaction",admin_url("spot_cate#update",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("areatype1",admin_url("tour_area#search_area",array("ajax"=>1,"type"=>1)));
		$GLOBALS['tmpl']->assign("areatype2",admin_url("tour_area#search_area",array("ajax"=>1,"type"=>2)));
		
		$GLOBALS['tmpl']->display("core/spot_cate/edit.html");
	}

	
	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);
		if(!check_empty("name"))
		{
			showErr(lang("SPOT_CATE_NAME_EMPTY"),$ajax);
		}
		$data = array();
		$data['name'] = strim($_REQUEST['name']);
		$data['is_hot'] = intval($_REQUEST['is_hot']);
		$data['is_recommend'] = intval($_REQUEST['is_recommend']);
		$data['is_index'] = intval($_REQUEST['is_index']);
		$data['index_adv1'] = format_domain_to_relative(strim($_REQUEST['index_adv1']));
		$data['index_adv1_url'] = strim($_REQUEST['index_adv1_url']);
		$data['index_adv2'] = format_domain_to_relative(strim($_REQUEST['index_adv2']));
		$data['index_adv2_url'] = strim($_REQUEST['index_adv2_url']);
		$data['sort'] = intval($_REQUEST['sort']);
		if($data['is_index'])
		{
			$tour_area1_py_array=array();
			$tour_area2_py_array=array();
			$tour_area1_py=strim($_REQUEST['tour_area1_py']);
			$tour_area2_py=strim($_REQUEST['tour_area2_py']);
			
			if($tour_area1_py != '')
			{
				$tour_area1_py_array=explode(',',$tour_area1_py);
			}
			if($tour_area2_py != '')
			{
				$tour_area2_py_array=explode(',',$tour_area2_py);
			}
			
			$tour_area=implode("','",array_merge($tour_area1_py_array,$tour_area2_py_array));
			if($tour_area !='')
			{
				$area=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_area where py in('".$tour_area."') order by is_recommend desc,py desc");
				$insert_area=array();
				$insert_area['area1']=array();
				$insert_area['area2']=array();
				$i=0;
				$j=0;
				foreach($area as $k=>$v)
				{
					if($v['type']==1)
					{
						$insert_area['area1'][$i]=$v;
						++$i;
					}
					elseif($v['type']==2)
					{	
						$insert_area['area2'][$j]['id']=$v;
						++$j;
					}
				}
				$insert_area["area1_py"]=$tour_area1_py;
				$insert_area["area1_name"]=strim($_REQUEST['tour_area1_name']);
				$insert_area["area2_py"]=$tour_area2_py;
				$insert_area["area2_name"]=strim($_REQUEST['tour_area2_name']);
				$data['rec_indexarea']=serialize($insert_area);
			}
		}
		else
		{
			$data['rec_indexarea']='';
		}
		
		// 更新数据
		
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."spot_cate",$data,"UPDATE","id=".$id,"SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			save_log($log_info.lang("UPDATE_SUCCESS"),1);
			rm_auto_cache("spot_cate_list");  //自动删除缓存;
			showSuccess(lang("UPDATE_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	public function set_hot()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."spot_cate where id = ".$id);
		$c_is_hot =  $GLOBALS['db']->getOne("select is_hot from ".DB_PREFIX."spot_cate where id = ".$id); //当前状态
		$n_is_hot = $c_is_hot == 0 ? 1 : 0; //需设置的状态
		$GLOBALS['db']->query("update ".DB_PREFIX."spot_cate set is_hot = ".$n_is_hot." where id = ".$id);
		rm_auto_cache("spot_cate_list");  //自动删除缓存;
		save_log($info.lang("SET_EFFECT_".$n_is_hot),1);
		showSuccess(lang("SET_EFFECT_".$n_is_hot),$ajax)	;
	}
	
	public function set_rec()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."spot_cate where id = ".$id);
		$c_is_rec =  $GLOBALS['db']->getOne("select is_recommend from ".DB_PREFIX."spot_cate where id = ".$id); //当前状态
		$n_is_rec = $c_is_rec == 0 ? 1 : 0; //需设置的状态
		$GLOBALS['db']->query("update ".DB_PREFIX."spot_cate set is_recommend = ".$n_is_rec." where id = ".$id);
		rm_auto_cache("spot_cate_list");  //自动删除缓存;
		save_log($info.lang("SET_EFFECT_".$n_is_rec),1);
		showSuccess(lang("SET_EFFECT_".$n_is_rec),$ajax)	;
	}
	
	public function set_idx()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."spot_cate where id = ".$id);
		$c_is_rec =  $GLOBALS['db']->getOne("select is_index from ".DB_PREFIX."spot_cate where id = ".$id); //当前状态
		$n_is_rec = $c_is_rec == 0 ? 1 : 0; //需设置的状态
		$GLOBALS['db']->query("update ".DB_PREFIX."spot_cate set is_index = ".$n_is_rec." where id = ".$id);
		rm_auto_cache("spot_cate_list");  //自动删除缓存;
		save_log($info.lang("SET_EFFECT_".$n_is_rec),1);
		showSuccess(lang("SET_EFFECT_".$n_is_rec),$ajax)	;
	}
	
	
	public function set_sort()
	{
		$ajax = intval($_REQUEST['ajax']);
		$sort = intval($_REQUEST['sort']);
		$id = intval($_REQUEST['id']);
		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."spot_cate where id = ".$id);
		if($data)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."spot_cate set sort = ".$sort." where id = ".$id);
			if($GLOBALS['db']->error()!="")
			{
				showErr($data['sort'],$ajax);
			}
			else
			{
				save_log($data['name'].lang("UPDATE_SUCCESS"),1);
				rm_auto_cache("spot_cate_list");  //自动删除缓存;
				showSuccess($sort,$ajax);
			}
		}
		else
		{
			showErr(0,$ajax);
		}
	}
	
	public function search_cate(){
		//处理保存下来的已选数据
		$this->assign_lookup_fields("cate_name");
	
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
	
	
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."spot_cate where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."spot_cate where ".$condition);
	
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
	
		$GLOBALS['tmpl']->assign("formaction",admin_url("spot_cate#search_cate"));
		$GLOBALS['tmpl']->display("core/spot_cate/search_cate.html");
	}
	
}
?>