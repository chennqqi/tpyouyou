<?php

// +----------------------------------------------------------------------

// | Fanwe 乐程旅游b2b

// +----------------------------------------------------------------------

// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.

// +----------------------------------------------------------------------

// | Author: 同创网络(778251855@qq.com)

// +----------------------------------------------------------------------





class tourline_rec_configModule extends AuthModule

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

		

		

		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tourline_rec_config  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);

		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tourline_rec_config");

		

		foreach($list as $k=>$v)

		{

			$list[$k]['is_effect_show'] = lang("IS_EFFECT_".$v['is_effect']);

			

			//0首页 1国内游 2出境游 3周边游 4跟团游 5自助游 6自驾游 

			if($v['rec_page'] ==1)

				$list[$k]['rec_page_val']="国内游";

			elseif($v['rec_page'] ==2)

				$list[$k]['rec_page_val']="出境游";

			elseif($v['rec_page'] ==3)

				$list[$k]['rec_page_val']="周边游";

			elseif($v['rec_page'] ==4)

				$list[$k]['rec_page_val']="跟团游";

			elseif($v['rec_page'] ==5)

				$list[$k]['rec_page_val']="自助游";

			elseif($v['rec_page'] ==6)

				$list[$k]['rec_page_val']="自驾游";

			else

				$list[$k]['rec_page_val']="首页";

				

			//1国内游 2出境游 3周边游 4跟团游 5自助游 6自驾游 (1-6只有首页设置可用) 7.大区域 8.小区域 9.标签

			if($v['rec_type'] ==2)

				$list[$k]['rec_type_val']="出境游";

			elseif($v['rec_type'] ==3)

				$list[$k]['rec_type_val']="周边游";

			elseif($v['rec_type'] ==4)

				$list[$k]['rec_type_val']="跟团游";

			elseif($v['rec_type'] ==5)

				$list[$k]['rec_type_val']="自助游";

			elseif($v['rec_type'] ==6)

				$list[$k]['rec_type_val']="自驾游";

			elseif($v['rec_type'] ==7)

				$list[$k]['rec_type_val']="大区域";

			elseif($v['rec_type'] ==8)

				$list[$k]['rec_type_val']="小区域";

			elseif($v['rec_type'] ==9)

				$list[$k]['rec_type_val']="标签";

			else

				$list[$k]['rec_type_val']="国内游";

		}

		

		$GLOBALS['tmpl']->assign('list',$list);

		$GLOBALS['tmpl']->assign('totalCount',$totalCount);

		$GLOBALS['tmpl']->assign('param',$param);

		

		$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_rec_config"));

		$GLOBALS['tmpl']->assign("setsorturl",admin_url("tourline_rec_config#set_sort",array("ajax"=>1)));

		$GLOBALS['tmpl']->assign("delurl",admin_url("tourline_rec_config#foreverdelete",array('ajax'=>1)));		

		$GLOBALS['tmpl']->assign("editurl",admin_url("tourline_rec_config#edit"));

		$GLOBALS['tmpl']->assign("addurl",admin_url("tourline_rec_config#add"));

		$GLOBALS['tmpl']->display("core/tourline_rec_config/index.html");

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

				$del_nav_name = $GLOBALS['db']->getOne("select group_concat(rec_name) from ".DB_PREFIX."tourline_rec_config where id in (".$id.")");

				$sql = "delete from ".DB_PREFIX."tourline_rec_config where id in (".$id.")";

				$GLOBALS['db']->query($sql);				

				if($GLOBALS['db']->affected_rows()>0)

				save_log(lang("DEL").":".$del_nav_name, 1);

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

		$GLOBALS['tmpl']->assign("searchrecidareaurl",admin_url("tour_area#search_area_radio",array("ajax"=>1)));

		$GLOBALS['tmpl']->assign("searchrecidplaceurl",admin_url("tour_place#search_place_radio",array("ajax"=>1)));

		$GLOBALS['tmpl']->assign("searchrecidtagurl",admin_url("tour_place_tag#search_tag_radio",array("ajax"=>1)));

		$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_rec_config#insert",array("ajax"=>1)));

		$GLOBALS['tmpl']->display("core/tourline_rec_config/add.html");

	}

	

	

	public function insert() {

		$ajax = intval($_REQUEST['ajax']);

		

		if(!check_empty("rec_name"))

		{

			showErr(lang("TOURLINE_REC_NAME_EMPTY_TIP"),$ajax);

		}

		if(intval($_REQUEST['rec_type']) <=0)

		{

			showErr(lang("TOURLINE_REC_TYPE_NO_SELECT"),$ajax);

		}

		if(intval($_REQUEST['rec_type']) == 7 && intval($_REQUEST['rec_id_id']) <=0)

		{

			showErr(lang("请选择推荐大区域"),$ajax);

		}

		elseif(intval($_REQUEST['rec_type']) == 8 && intval($_REQUEST['rec_id_id']) <=0)

		{

			showErr(lang("请选择推荐小区域"),$ajax);

		}

		elseif(intval($_REQUEST['rec_type']) == 9 && intval($_REQUEST['rec_id_id']) <=0)

		{

			showErr(lang("请选择推荐标签"),$ajax);

		}

		$data = array();

		$data['rec_name'] = strim($_REQUEST['rec_name']);

		$data['rec_page'] = intval($_REQUEST['rec_page']);

		$data['rec_type'] = intval($_REQUEST['rec_type']);

		$data['rec_id'] = intval($_REQUEST['rec_id_id']);

		$data['rec_adv1'] = strim($_REQUEST['rec_adv1']);

		$data['rec_adv1_url'] = strim($_REQUEST['rec_adv1_url']);

		$data['rec_adv2'] = strim($_REQUEST['rec_adv2']);

		$data['rec_adv2_url'] = strim($_REQUEST['rec_adv2_url']);

                $data['rec_adv3'] = strim($_REQUEST['rec_adv3']);

		$data['rec_adv3_url'] = strim($_REQUEST['rec_adv3_url']);

		$data['rec_color'] = intval($_REQUEST['rec_color']);

		$data['rec_sort'] = intval($_REQUEST['rec_sort']);

		

		$log_info = $data['rec_name'];

		$GLOBALS['db']->autoExecute(DB_PREFIX."tourline_rec_config",$data,"INSERT","","SILENT");

		if ($GLOBALS['db']->error()=="") {

			//成功提示

			save_log($log_info.lang("INSERT_SUCCESS"),1);

			showSuccess(lang("INSERT_SUCCESS"),$ajax,admin_url("tourline_rec_config#add"));

		} else {

			//错误提示

			showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);

		}



	}

	

	

	public function edit() {		

		$id = intval($_REQUEST ['id']);

		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_rec_config where id = ".$id);

		//print_r($vo);

		$rec_data=array();

		if($vo['rec_type'] == 7)

		{

			$rec_data=$GLOBALS['db']->getRow("select id,name from ".DB_PREFIX."tour_area where id = ".$vo['rec_id']);

			$searchrecidurl=admin_url("tour_area#search_area_radio",array("ajax"=>1));

		}

		elseif($vo['rec_type'] == 8)

		{

			$rec_data=$GLOBALS['db']->getRow("select id,name from ".DB_PREFIX."tour_place where id = ".$vo['rec_id']);

			$searchrecidurl=admin_url("tour_place#search_place_radio",array("ajax"=>1));

		}

		elseif($vo['rec_type'] == 9)

		{

			$rec_data=$GLOBALS['db']->getRow("select id,name from ".DB_PREFIX."tour_place_tag where id = ".$vo['rec_id']);

			$searchrecidurl=admin_url("tour_place_tag#search_tag_radio",array("ajax"=>1));

		}

			

		$GLOBALS['tmpl']->assign ( 'rec_data', $rec_data );

		$GLOBALS['tmpl']->assign ( 'searchrecidurl',$searchrecidurl);

		$GLOBALS['tmpl']->assign("searchrecidareaurl",admin_url("tour_area#search_area_radio",array("ajax"=>1)));

		$GLOBALS['tmpl']->assign("searchrecidplaceurl",admin_url("tour_place#search_place_radio",array("ajax"=>1)));

		$GLOBALS['tmpl']->assign("searchrecidtagurl",admin_url("tour_place_tag#search_tag_radio",array("ajax"=>1)));

		$GLOBALS['tmpl']->assign ( 'vo', $vo );

		$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_rec_config#update",array("ajax"=>1)));

		

		$GLOBALS['tmpl']->display("core/tourline_rec_config/edit.html");

	}



	

	public function update() {

		$ajax = intval($_REQUEST['ajax']);

		$id = intval($_REQUEST['id']);


		if(!check_empty("rec_name"))

		{

			showErr(lang("TOURLINE_REC_NAME_EMPTY_TIP"),$ajax);

		}

		if(intval($_REQUEST['rec_type']) <=0)

		{

			showErr(lang("TOURLINE_REC_TYPE_NO_SELECT"),$ajax);

		}

		

		if(intval($_REQUEST['rec_type']) == 7 && intval($_REQUEST['rec_id_id']) <=0)

		{

			showErr(lang("请选择推荐大区域"),$ajax);

		}

		elseif(intval($_REQUEST['rec_type']) == 8 && intval($_REQUEST['rec_id_id']) <=0)

		{

			showErr(lang("请选择推荐小区域"),$ajax);

		}

		elseif(intval($_REQUEST['rec_type']) == 9 && intval($_REQUEST['rec_id_id']) <=0)

		{

			showErr(lang("请选择推荐标签"),$ajax);

		}

			

		$data = array();

		$data['rec_name'] = strim($_REQUEST['rec_name']);

		$data['rec_page'] = intval($_REQUEST['rec_page']);

		$data['rec_type'] = intval($_REQUEST['rec_type']);

		$data['rec_id'] = intval($_REQUEST['rec_id_id']);

		$data['rec_adv1'] = strim($_REQUEST['rec_adv1']);

		$data['rec_adv1_url'] = strim($_REQUEST['rec_adv1_url']);

		$data['rec_adv2'] = strim($_REQUEST['rec_adv2']);

		$data['rec_adv2_url'] = strim($_REQUEST['rec_adv2_url']);

        $data['rec_adv3'] = strim($_REQUEST['rec_adv3']);

		$data['rec_adv3_url'] = strim($_REQUEST['rec_adv3_url']);

		$data['rec_color'] = intval($_REQUEST['rec_color']);

		$data['rec_sort'] = intval($_REQUEST['rec_sort']);
      
		$log_info = $data['rec_name'];

		$dd=$GLOBALS['db']->autoExecute(DB_PREFIX."tourline_rec_config",$data,"UPDATE","id=".$id,"SILENT");
    
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

		$sort = intval($_REQUEST['rec_sort']);

		$id = intval($_REQUEST['id']);

		$list_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline_rec_config where id = ".$id);

		if($list_item)

		{

			$GLOBALS['db']->query("update ".DB_PREFIX."tourline_rec_config set rec_sort = ".$sort." where id = ".$id);

			if($GLOBALS['db']->error()!="")

			{

				showErr($list_item['rec_sort'],$ajax);

			}

			else

			{

				save_log($list_item['rec_name'].lang("UPDATE_SUCCESS"),1);

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