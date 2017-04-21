<?php

/* 
 * 敏感词管理
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class wordModule  extends AuthModule{
    public function index(){
        $condition = ' 1=1 ';
        
        if($_REQUEST['cid']){
            $param['cid'] = $_REQUEST['cid'];
            $condition .="AND cid='".$param['cid']."' ";
        }
        if($_REQUEST['word']){
            $param['word'] = $_REQUEST['word'];
            $condition .="AND word='".$param['word']."' ";
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
        
        $count_sql = "SELECT count(*) FROM ".DB_PREFIX."word ".$condition;
        $sql = "SELECT * FROM ".DB_PREFIX."word ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit;
        //查询
        $totalCount = $GLOBALS['db']->getOne($count_sql);
        $list = $GLOBALS['db']->getAll($sql);
        
        $word_cate = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."word_type");
        foreach($word_cate as $k=>$v){
            $f_word_cate[$v['id']] =  $v['name'];
        }
        foreach($list as $k=>$v){
            $v['word_type'] = $v['type']==1?"禁用":"替换";
            $v['type'] = $f_word_cate[$v['cid']];
            $list[$k] = $v;
        }

        $GLOBALS['tmpl']->assign('word_type',$GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."word_type"));
        $GLOBALS['tmpl']->assign('list',$list);
        $GLOBALS['tmpl']->assign('totalCount',$totalCount);
        $GLOBALS['tmpl']->assign('param',$param);
        $GLOBALS['tmpl']->assign("formaction",admin_url("word"));
        
        $GLOBALS['tmpl']->assign("statusurl",admin_url("word#set_status",array('ajax'=>1)));
        $GLOBALS['tmpl']->assign("addurl",admin_url("word#add"));	
        $GLOBALS['tmpl']->assign("delurl",admin_url("word#foreverdelete",array('ajax'=>1)));	
        $GLOBALS['tmpl']->display("core/word/index.html");
    }
    function add(){
        $word_cate = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."word_type");
        $GLOBALS['tmpl']->assign("word_cate",$word_cate);
        $GLOBALS['tmpl']->assign("formaction",admin_url("word#save_word"));
        $GLOBALS['tmpl']->display("core/word/add.html");
    }

    function save_word(){
        $ajax = intval($_REQUEST['ajax']);
        $words = trim($_REQUEST['words']);
		$words = explode("\n",$words);
		$obj = array();
		$obj['cid'] = intval($_REQUEST['cid']);
		$obj['type'] = intval($_REQUEST['type']);
		if($obj['type'] == 2)
			$obj['replacement'] = trim($_REQUEST['replacement']);
			
                
		foreach($words as $word)
		{
			$word = trim($word);
			if(!empty($word))
			{
				$obj['word'] = $word;
                                $oldid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."word WHERE word='".$word."'");

				if($oldid>0)
                                    $GLOBALS['db']->autoExecute(DB_PREFIX."word",$obj,"UPDATE","id=".$oldid);
                                else{
                                    $GLOBALS['db']->autoExecute(DB_PREFIX."word",$obj);
                                }
                                    
			}
		}
		
		if ($GLOBALS['db']->error()=="") {
                    //成功提示
                    showSuccess(lang("INSERT_SUCCESS"),1);
            } else {
                    //错误提示
                    showErr(lang("INSERT_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
            }	
        
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
                         
                           $del_name = $GLOBALS['db']->getOne("select group_concat(name) from ".DB_PREFIX."word_type where id in (".$id.")");			
                           $sql = "delete from ".DB_PREFIX."word_type where id in (".$id.")";
                           $GLOBALS['db']->query($sql);				
                           if($GLOBALS['db']->affected_rows()>0)
                           {					
                                   save_log(lang("DEL").":".$del_name, 1);
                                   clear_auto_cache("word_cache");
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
   
    public function set_status(){
        $ajax = intval($_REQUEST['ajax']);
        $id = $_REQUEST['id'];
        if($this->update_field($id,"status")){
            showSuccess(lang("UPDATE_SUCCESS"),$ajax);	
        }else{
            showErr(lang("UPDATE_FAILED"),$ajax);
        }
    }
    
    public function update_field($id,$field){

        if($GLOBALS['db']->getOne("SELECT ".$field." FROM ".DB_PREFIX."word WHERE id=".$id)){
            $GLOBALS['db']->autoExecute(DB_PREFIX."word",array($field=>0),"UPDATE","id=".$id);
        }else{
            $GLOBALS['db']->autoExecute(DB_PREFIX."word",array($field=>1),"UPDATE","id=".$id);
        }
        if($GLOBALS['db']->affected_rows()){
            return true;
        }else{
            return false;
        }
    }
}
