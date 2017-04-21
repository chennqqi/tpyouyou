<?php

class commentModule extends AuthModule{
    /**
     * 评论列表
     */
    public function index(){
        $condition = '';
        if($_REQUEST['nickname']){
            $param['nickname'] = $_REQUEST['nickname'];
            $condition .=" AND nickname='".$param['nickname']."' ";
            
        }
        
        if($condition){
            $condition = " WHERE ".$condition;
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
        
        $count_sql = "SELECT count(*) FROM ".DB_PREFIX."comment ".$condition;
        $sql = "SELECT * FROM ".DB_PREFIX."comment ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit;
        //查询
        $totalCount = $GLOBALS['db']->getOne($count_sql);
        $list = $GLOBALS['db']->getAll($sql);
        
        $GLOBALS['tmpl']->assign('list',$list);
        $GLOBALS['tmpl']->assign('totalCount',$totalCount);
        $GLOBALS['tmpl']->assign('param',$param);
        $GLOBALS['tmpl']->assign("formaction",admin_url("comment"));
        
     
        $GLOBALS['tmpl']->assign("delurl",admin_url("comment#foreverdelete",array('ajax'=>1)));	
        $GLOBALS['tmpl']->display("core/comment/index.html");
    }
    
    /**
     * 永久删除
     */
    public function foreverdelete(){
        $ajax = intval($_REQUEST['ajax']);		
		if (isset ( $_REQUEST ['id'] ))
		{
			$id = strim($_REQUEST ['id']);			
			$id = format_ids_str($id);
			if($id)
			{	

				$del_name = $GLOBALS['db']->getOne("select group_concat(comment_rel_id) from ".DB_PREFIX."comment where id in (".$id.")");			
				$sql = "delete from ".DB_PREFIX."comment where id in (".$id.")";
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
    
}
