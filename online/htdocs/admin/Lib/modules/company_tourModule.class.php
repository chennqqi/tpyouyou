<?php

class company_tourModule extends AuthModule{

    function index() {
    	$param = array();
		//条件
		$condition = " 1 = 1 ";    	
    	
        //提交时间
		$create_time_begin  = strim($_REQUEST['create_time_begin']);
		$param['create_time_begin'] = $create_time_begin;
		
		$create_time_end  = strim($_REQUEST['create_time_end']);
		$param['create_time_end'] = $create_time_end;
		
		if(!empty($create_time_begin) && !empty($create_time_end))
		{
			$condition.=" and create_time >= '".to_timespan($create_time_begin)."' and create_time <='". (to_timespan($create_time_end) + 3600 * 24 - 1)."' ";
		
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
		
		
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."company_tour where ".$condition);		
		if($totalCount > 0){
			$sql = "select id,company_name,company_contact,company_mobile,create_time,status from ".DB_PREFIX."company_tour  where ".$condition."  order by create_time desc limit ".$limit;

			$list = $GLOBALS['db']->getAll($sql);
			
			foreach($list as $k=>$v){
				$list[$k]['create_time']=to_date($v['create_time']);
				$list[$k]['sort']=$k+1;
				if($v['status']==0){
					$list[$k]['status']="未读";
				}elseif($v['status']==1){
					$list[$k]['status']="已读";
				}				
			}
		}		
		$GLOBALS['tmpl']->assign("formaction",admin_url("company_tour"));
		$GLOBALS['tmpl']->assign("editurl",admin_url("company_tour#show"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("company_tour#foreverdelete"));	
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
    	$GLOBALS['tmpl']->display("core/company_tour/index.html");
    }




	function show(){
		$id = intval($_REQUEST['id']);
		$sql="select * from ".DB_PREFIX."company_tour where id=".$id;
		$result = $GLOBALS['db']->getRow($sql);
		if($result!=""){
			$result['create_time']=to_date($result['create_time']);
			$tmp_data['status']=1;
			$GLOBALS['db']->autoExecute(DB_PREFIX."company_tour",$tmp_data,"UPDATE","id=".$result['id'],"SILENT");
		}else{
			showErr("消息不存在",1);
		}
		
		$GLOBALS['tmpl']->assign('result',$result);
		$GLOBALS['tmpl']->display("core/company_tour/show.html");
	}

	function foreverdelete(){
		$id = intval($_REQUEST['id']);		
		if($id>0){								
			$del_name = $GLOBALS['db']->getOne("select company_name from ".DB_PREFIX."company_tour where id=".$id);			
			
			$sql = "delete from ".DB_PREFIX."company_tour where id=".$id;
			$GLOBALS['db']->query($sql);				
			if($GLOBALS['db']->affected_rows()>0){					
				save_log(lang("DEL").":".$del_name, 1);
				showSuccess(lang("FOREVER_DELETE_SUCCESS"),1);
			}else{
				showErr("删除失败",1);
			}						
		}
		else{
			save_log(lang("DEL")."ID:".strim($_REQUEST ['id']), 0);
			showErr(lang("INVALID_OPERATION"),1);
		}
		
	}












}
?>