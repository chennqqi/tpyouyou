<?php
class reviewModule extends AuthModule{
    public function index(){
        $condition ='';
        $where = array();
        if($_REQUEST['review_type']){
            $where[] = "review_type = ".$_REQUEST['review_type'];
            $param['review_type'] = $_REQUEST['review_type'];
        }
        if(isset($_REQUEST['is_verify'])&& $_REQUEST['is_verify'] !==''){
            $is_verify = $_REQUEST['is_verify']=="n"?0:1;
            $where[] = "is_verify = ".$is_verify;
            $param['is_verify'] = $_REQUEST['is_verify'];
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
        if($where){
            $condition = " WHERE ".implode(" AND ",$where);
        }
        
	//查询
        $sql_count = "SELECT COUNT(*) FROM ".DB_PREFIX."review ".$condition; 
        $sql = "SELECT * FROM ".DB_PREFIX."review ".$condition." order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit;
        $totalCount = $GLOBALS['db']->getOne($sql_count);
        $list = $GLOBALS['db']->getAll($sql);

	$GLOBALS['tmpl']->assign('list',$list);
        $GLOBALS['tmpl']->assign('totalCount',$totalCount);
        $GLOBALS['tmpl']->assign('param',$param);
        
        $GLOBALS['tmpl']->assign("formaction",admin_url("review#index"));
        $GLOBALS['tmpl']->assign("showurl",admin_url("review#dialog_review_item"));
        $GLOBALS['tmpl']->assign("delurl",admin_url("review#foreverdelete",array('ajax'=>1)));	
        
        $GLOBALS['tmpl']->display("core/review/index.html");
    }
    
    public function dialog_review_item(){
        $id = $_REQUEST['id'];
        $row = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."review WHERE id=".$id);
        $row['review_time'] = to_date($row['review_time'],"Y-m-d H:i:s");
        //点评类型
        $row['group_point'] = unserialize($row['group_point']);
        $row['imgs'] = unserialize($row['image_list']);

        $row['money'] = format_price_to_display($row['money']);
        $row['voucher_count'] = format_price_to_display($row['voucher_count']);

        $GLOBALS['tmpl']->assign("css_img",SITE_DOMAIN."/web/Tpl/fanwe/images/");
        $GLOBALS['tmpl']->assign("row",$row);
        $GLOBALS['tmpl']->assign("review_url",admin_url("review#index"));
        $GLOBALS['tmpl']->assign("formaction",admin_url("review#check_save",array('ajax'=>1)));
        $GLOBALS['tmpl']->display("core/review/review_item.html");
    }
    
    
    /**
     * 审核点评
     */
    public function check_save(){
        $ajax = intval($_REQUEST['ajax']);
        if (isset ( $_REQUEST ['id'] ))
        {
            //点评数据
            $review_item = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."review WHERE id=".$_REQUEST ['id']);
            //点评关联数据
            $rel_item = get_review_rel_item($review_item['review_type'],$review_item['review_rel_id']);
            $count_star = $rel_item['star_1_count']+$rel_item['star_2_count']+$rel_item['star_3_count']+$rel_item['star_4_count']+$rel_item['star_5_count'];
            $count_star = $count_star+1; //当前审核的这条
            /*
            $star_total = $count_star*5;
            $star_cur_total = $rel_item['star_1_count']*1+$rel_item['star_2_count']*2+$rel_item['star_3_count']*3+$rel_item['star_4_count']*4+$rel_item['star_5_count']*5;
            $star_cur_total = $star_cur_total+$review_item['point']; //当前审核的这条的星星数
            
            //满意度百分比 冗余
            $updata['satify'] = $star_cur_total/$star_total*10000;//所有点评总得分/（点评人数*5）*10000
            */
            
            if($review_item['point'] <=2)
            	$star_2_count=$rel_item['star_2_count']+1;
            else
            	$star_2_count=$rel_item['star_2_count'];
           	
            //满意度百分比 冗余
            if($star_2_count >0)
            	$updata['satify'] = ($rel_item['sale_total']-$star_2_count)/$rel_item['sale_total']*10000; // (出游总人数-评论两分人数)/评论总人数*10000
            else
            	$updata['satify'] = 1*10000;
             
            //对应星星数字段更新
            $updata['star_'.$review_item['point'].'_count'] = $rel_item['star_'.$review_item['point'].'_count']+1;
            //点评总数
            $updata['review_total'] = $rel_item['review_total']+1; 
            
            //更新点评表
            $updata_review = array(
                'is_verify'=>1,
                'review_reply'=>$_REQUEST['review_reply'],
                'review_reply_time'=>NOW_TIME,
            );
            $GLOBALS['db']->autoExecute(DB_PREFIX."review",$updata_review,'UPDATE',"id=".$review_item['id']);
            
            if(update_review_rel_item($review_item['review_type'],$review_item['review_rel_id'],$updata)){
                require APP_ROOT_PATH."system/libs/user.php";
                $images = unserialize($review_item['image_list']);
                    foreach($images as $k=>$v){
                        $temp['title'] = '';
                        $temp['src'] = $v;
                        $image_list[] = $temp;
                    }

                    if($review_item['review_type']==1){
                        $active_type=3; //线路
                    }elseif($review_item['review_type']==2){
                        $active_type=4;//门票
                    }
                    //同步用户动态
                    User::gen_active(User::get_user_info($review_item['user_id']),$review_item['review_content'],$image_list,$active_type,$review_item['review_rel_id'],'','',$review_item['id']);
                	
                if($rel_item['is_review_return']){ //是否有返利操作
                    //通知用户
                    User::send_message($review_item['user_id'],"点评审核通过","您对[".$review_item['review_rel_name']."]发布的点评已经审核通过。");
                    
                    //游记奖励发放
                    User::modify_account($review_item['user_id'],1,$review_item['money'],"点评返金钱");
                    User::modify_account($review_item['user_id'],2,$review_item['score'],"点评返积分");
                    User::modify_account($review_item['user_id'],3,$review_item['exp'],"点评返经验");
                    User::modify_account($review_item['user_id'],4,$review_item['voucher_count'],"点评返经验");
                }
                showSuccess(lang("REVIEW_CHECK_SUCCESS"),$ajax);	
            }else{
                showErr(lang("REVIEW_CHECK_ERROR"),$ajax);
            }
            
        }
        else
        {
                showErr(lang("REVIEW_CHECK_ERROR"),$ajax);
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
                                //删除用户动态组织数据
                                $review_data = $GLOBALS['db']->getAll("SELECT user_id,group_concat(CAST(id as char)) AS ids FROM ".DB_PREFIX."review WHERE id in (".$id.") GROUP BY user_id");
                                foreach($review_data as $k=>$v){
                                    $temp['uid'] = $v['user_id'];
                                    $temp['ids'] =  $v['ids'];
                                    $del_active_data[] = $temp;
                                }
                                
				$del_name = $GLOBALS['db']->getOne("select group_concat(id) from ".DB_PREFIX."review where id in (".$id.")");			
				$sql = "delete from ".DB_PREFIX."review where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->error=="")
				{
                                    //删除点评对应表数据
                                    $GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."review_group WHERE review_id in (".$id.")");
                                    $GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."review_image WHERE review_id in (".$id.")");
                                    //删除用户动态
                                    require_once APP_ROOT_PATH.'system/libs/user.php';
                                    User::batch_del_active(3, $del_active_data);
                                    
                                    save_log(lang("DEL").":点评 ".$del_name, 1);
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