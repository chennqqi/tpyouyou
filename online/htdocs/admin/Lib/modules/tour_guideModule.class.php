<?php
/**
 * 旅游游记管理
 * @author Jobin.lin <jobin.lin@gmail.com>
 */
class tour_guideModule extends AuthModule{
    
    /**
     * 游记列表
     */
    public function index(){
        $condition = '';
        
        if($_REQUEST['title']){
            $param['title'] = $_REQUEST['title'];
            $condition .=" title='".$param['title']."' ";
        }
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
        
        $count_sql = "SELECT count(*) FROM ".DB_PREFIX."tour_guide ".$condition;
        $sql = "SELECT * FROM ".DB_PREFIX."tour_guide ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit;
        //查询
        $totalCount = $GLOBALS['db']->getOne($count_sql);
        $list = $GLOBALS['db']->getAll($sql);
        
        $GLOBALS['tmpl']->assign('list',$list);
        $GLOBALS['tmpl']->assign('totalCount',$totalCount);
        $GLOBALS['tmpl']->assign('param',$param);
        $GLOBALS['tmpl']->assign("formaction",admin_url("tour_guide"));
        
        $GLOBALS['tmpl']->assign("is_hot_url",admin_url("tour_guide#is_hot",array('ajax'=>1)));
        $GLOBALS['tmpl']->assign("is_recommend_url",admin_url("tour_guide#is_recommend",array('ajax'=>1)));
        $GLOBALS['tmpl']->assign("delurl",admin_url("tour_guide#foreverdelete",array('ajax'=>1)));	
        $GLOBALS['tmpl']->assign("is_index_url",admin_url("tour_guide#is_index",array('ajax'=>1)));	
        $GLOBALS['tmpl']->display("core/tour_guide/index.html");
    }
    
    /**
     * 游记审核列表
     */
    public function check_list(){
        $condition = ' is_public = 1 ';
        
        if($_REQUEST['title']){
            $param['title'] = $_REQUEST['title'];
            $condition .="AND title='".$param['title']."' ";
        }
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
       
        $count_sql = "SELECT count(*) FROM ".DB_PREFIX."tour_guide_temp ".$condition;
        $sql = "SELECT * FROM ".DB_PREFIX."tour_guide_temp ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit;
        //查询
        $totalCount = $GLOBALS['db']->getOne($count_sql);
        $list = $GLOBALS['db']->getAll($sql);

        $GLOBALS['tmpl']->assign('list',$list);
        $GLOBALS['tmpl']->assign('totalCount',$totalCount);
        $GLOBALS['tmpl']->assign('param',$param);
        $GLOBALS['tmpl']->assign("formaction",admin_url("tour_guide#check_list"));
        
        $GLOBALS['tmpl']->assign("checkurl",admin_url("tour_guide#check",array('ajax'=>1)));
        $GLOBALS['tmpl']->assign("delurl",admin_url("tour_guide#foreverdelete_2",array('ajax'=>1)));
        $GLOBALS['tmpl']->display("core/tour_guide/check_list.html");
    }
    
    /**
     * 审核
     */
    public function check(){
        $ajax = intval($_REQUEST['ajax']);
        $id = $_REQUEST ['id'] ;
		if (isset ( $id ))
		{
                    if($GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX."tour_guide_temp WHERE id=".$id)){
                        $GLOBALS['db']->autoExecute(DB_PREFIX."tour_guide_temp",array("is_public"=>2),"UPDATE","id=".$id);
                        if($GLOBALS['db']->affected_rows()){
                            //将游记从临时文件转为正式的
                            $guide_item = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."tour_guide_temp WHERE id=".$id);
                     
                            if($guide_item){
                                $GLOBALS['db']->autoExecute(DB_PREFIX."tour_guide",$guide_item);
                            }
                            $image_list = unserialize($guide_item['image_list']);


                            foreach($image_list as $k=>$v){
                                $temp[] = array('title'=>$v['area_name'],'src'=>$v['image']);
                            }
                            $image_list = $temp;
                            
                            if($GLOBALS['db']->error()==""){
                                //删除临时表数据
                                $GLOBALS['db']->query("DELETE from ".DB_PREFIX."tour_guide_temp WHERE id=".$id);
                                
                                //同步到用户动态
                                require APP_ROOT_PATH."system/libs/user.php";
                                $user_item = User::get_user_info($guide_item['user_id']);
                                User::gen_active($user_item,$guide_item['title'],$image_list,2,$guide_item['id'],$guide_item['area_match'],$guide_item['area_match_row']);
                                
                                //通知用户
                                User::send_message($guide_item['user_id'],"游记审核通过","您发布的游记：".$guide_item['title']."审核通过。");
     
                                //审核后将游记同步到微博
                                User::send_weibo($user_item,$guide_item['title'],$image_list,url("guide#show",array("id"=>$id)));
                                //游记奖励发放
                                
                                User::modify_account($guide_item['user_id'],1,APP_CONF("GUIDE_MONEY"),sprintf(lang("GUIDE_ISSUE_REWARED"),"金钱"));
                                User::modify_account($guide_item['user_id'],2,APP_CONF("GUIDE_SCORE"),sprintf(lang("GUIDE_ISSUE_REWARED"),"积分"));
                                User::modify_account($guide_item['user_id'],3,APP_CONF("GUIDE_EXP"),sprintf(lang("GUIDE_ISSUE_REWARED"),"经验"));
                                showSuccess(lang("SUCCESS"),$ajax);
                            }else{
                                showErr(lang("UPDATE_FAILED"),$ajax);
                            }
                           
                        }else{
                            showErr(lang("UPDATE_FAILED"),$ajax);
                        }
                    }
                }
		else
		{
			showErr(lang("UPDATE_FAILED"),$ajax);
		}
    }
    
    public function is_hot(){
        $ajax = intval($_REQUEST['ajax']);
        $id = $_REQUEST['id'];
        if($this->update_field($id,"is_hot")){
            showSuccess(lang("UPDATE_SUCCESS"),$ajax);	
        }else{
            showErr(lang("UPDATE_FAILED"),$ajax);
        }
    }
    public function is_recommend(){
        $ajax = intval($_REQUEST['ajax']);
        $id = $_REQUEST['id'];
        if($this->update_field($id,"is_recommend")){
            showSuccess(lang("UPDATE_SUCCESS"),$ajax);	
        }else{
            showErr(lang("UPDATE_FAILED"),$ajax);
        }
    }
    
    public function is_index(){
        $ajax = intval($_REQUEST['ajax']);
        $id = $_REQUEST['id'];
        if($this->update_field($id,"is_index")){
            showSuccess(lang("UPDATE_SUCCESS"),$ajax);	
        }else{
            showErr(lang("UPDATE_FAILED"),$ajax);
        }
    }
    
    public function update_field($id,$field){

        if($GLOBALS['db']->getOne("SELECT ".$field." FROM ".DB_PREFIX."tour_guide WHERE id=".$id)){
            $GLOBALS['db']->autoExecute(DB_PREFIX."tour_guide",array($field=>0),"UPDATE","id=".$id);
        }else{
            $GLOBALS['db']->autoExecute(DB_PREFIX."tour_guide",array($field=>1),"UPDATE","id=".$id);
        }
        if($GLOBALS['db']->affected_rows()){
            return true;
        }else{
            return false;
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
                                //游记数据
                                $guide_data = $GLOBALS['db']->getAll("SELECT user_id,group_concat(CAST(id as char)) AS ids FROM ".DB_PREFIX."tour_guide where id in (".$id.") GROUP BY user_id");
                                //设置删除动态格式
                                foreach($guide_data as $k=>$v){
                                    $temp['uid'] = $v['user_id'];
                                    $temp['ids'] =  $v['ids'];
                                    $del_user_active[] = $temp;
                                }
                                
				$del_name = $GLOBALS['db']->getOne("select group_concat(title) from ".DB_PREFIX."tour_guide where id in (".$id.")");			
				$sql = "delete from ".DB_PREFIX."tour_guide where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->error=="")
				{
                                    //删除图片表
                                    $GLOBALS['db']->query("delete from ".DB_PREFIX."tour_guide_gallery where guide_id in (".$id.")");
                                    //删除景点表
                                    $GLOBALS['db']->query("delete from ".DB_PREFIX."tour_guide_spot where guide_id in (".$id.")");
                                    //删除每日记录表
                                    $GLOBALS['db']->query("delete from ".DB_PREFIX."tour_guide_route where guide_id in (".$id.")");
                                    //删除用户动态
                                    require_once APP_ROOT_PATH.'system/libs/user.php';
                                    User::batch_del_active(2,$del_user_active);
                                    
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
    
    /**
     * 永久删除 未审核游记
     */
    public function foreverdelete_2(){
        $ajax = intval($_REQUEST['ajax']);		
		if (isset ( $_REQUEST ['id'] ))
		{
			$id = strim($_REQUEST ['id']);			
			$id = format_ids_str($id);
			if($id)
			{	
				$del_name = $GLOBALS['db']->getOne("select group_concat(title) from ".DB_PREFIX."tour_guide_temp where id in (".$id.")");			
				$sql = "delete from ".DB_PREFIX."tour_guide_temp where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->error=="")
				{
                                    //删除图片表
                                    $GLOBALS['db']->query("delete from ".DB_PREFIX."tour_guide_gallery where guide_id in (".$id.")");
                                    //删除景点表
                                    $GLOBALS['db']->query("delete from ".DB_PREFIX."tour_guide_spot where guide_id in (".$id.")");
                                    //删除每日记录表
                                    $GLOBALS['db']->query("delete from ".DB_PREFIX."tour_guide_route where guide_id in (".$id.")");
                                 
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
function to_url($id){
    //{url r="guide#uc_guide_item" v="id=$item.id&type=admin"}
    echo url("guide#uc_guide_item",array("id"=>$id,"type"=>"admin"));
}