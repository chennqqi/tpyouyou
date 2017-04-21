<?php

/* 
 * 问答
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class askModule  extends AuthModule{
    public function index(){
        $condition = ' 1=1 ';
        
        if($_REQUEST['ask_type_id']){
            $param['ask_type_id'] = $_REQUEST['ask_type_id'];
            $condition .="AND ask_type_id='".$param['ask_type_id']."' ";
        }
        if($_REQUEST['nickname']){
            $param['nickname'] = $_REQUEST['nickname'];
            $condition .="AND nickname='".$param['nickname']."' ";
        }
        if($_REQUEST['is_reply']){
            $param['is_reply'] = $_REQUEST['is_reply'];
            $is_reply = $param['is_reply']=="n"?0:1;
            $condition .=" AND is_reply=".$is_reply;
            
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
        
        $count_sql = "SELECT count(*) FROM ".DB_PREFIX."ask ".$condition;
        $sql = "SELECT * FROM ".DB_PREFIX."ask ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit;
        //查询
        $totalCount = $GLOBALS['db']->getOne($count_sql);
        $list = $GLOBALS['db']->getAll($sql);

        $GLOBALS['tmpl']->assign('ask_type',$GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."ask_type"));
        $GLOBALS['tmpl']->assign('list',$list);
        $GLOBALS['tmpl']->assign('totalCount',$totalCount);
        $GLOBALS['tmpl']->assign('param',$param);
        $GLOBALS['tmpl']->assign("formaction",admin_url("ask"));
        
        $GLOBALS['tmpl']->assign("showurl",admin_url("ask#dialog_ask_item"));	
        $GLOBALS['tmpl']->assign("delurl",admin_url("ask#foreverdelete",array('ajax'=>1)));	
        $GLOBALS['tmpl']->display("core/ask/index.html");
    }
    
    
    function dialog_ask_item(){
        $id = $_REQUEST['id'];
        $ask_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ask WHERE id=".$id);
        if($ask_item){
            
        }
        $GLOBALS['tmpl']->assign("formaction",admin_url("ask#save_ask"));
        $GLOBALS['tmpl']->assign("ask_item",$ask_item);
        $GLOBALS['tmpl']->display("core/ask/ask_item.html");
    }
    /**
     * 问答回复
     */
    function save_ask(){
        $ajax = intval($_REQUEST['ajax']);
        $id = $_REQUEST['id'];
        $updata["reply_content"] = $_REQUEST['reply_content'];
        $updata['reply_time'] = NOW_TIME;
        $updata['is_reply'] = 1;
        $ask_item = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."ask WHERE id=".$id);
        if($ask_item){
            $GLOBALS['db']->autoExecute(DB_PREFIX.'ask',$updata,"UPDATE","id=".$id);
            if($GLOBALS['db']->affected_rows()){
                require APP_ROOT_PATH."system/libs/user.php";
                //通知用户
                User::send_message($ask_item['user_id'],"问答回复","您发布的问答：".$ask_item['title']."已经回复了。");
                //游记奖励发放
                User::modify_account($ask_item['user_id'],1,APP_CONF("ASK_MONEY"),sprintf(lang("ASK_ISSUE_REWARED"),"金钱"));
                User::modify_account($ask_item['user_id'],2,APP_CONF("ASK_SCORE"),sprintf(lang("ASK_ISSUE_REWARED"),"积分"));
                User::modify_account($ask_item['user_id'],3,APP_CONF("ASK_EXP"),sprintf(lang("ASK_ISSUE_REWARED"),"经验"));

                showSuccess(lang("SUCCESS"),$ajax);
            }else{
                showErr(lang("UPDATE_FAILED"),$ajax);
            }
            
        }else{
            showErr(lang("UPDATE_FAILED"),$ajax);
        }
        
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

				$del_name = $GLOBALS['db']->getOne("select group_concat(title) from ".DB_PREFIX."ask where id in (".$id.")");			
				$sql = "delete from ".DB_PREFIX."ask where id in (".$id.")";
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
