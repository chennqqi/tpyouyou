<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class commentModule extends BaseModule{
    function init_comment(){
        $comment_type = intval($_GET['comment_type']);
        $comment_rel_id = intval($_GET['comment_rel_id']);
        
        //limit
        $page = $_GET['p']>1?$_GET['p']:1;

        $page_site = 2; //每页数据量

        if($page>1){
            $limit_start = ($page-1)*$page_site;
        }else{
            $limit_start =0;
        }
        
        $limit = " limit ".$limit_start.",".$page_site;
        $condition = " WHERE comment_type=".$comment_type." AND comment_rel_id=".$comment_rel_id ; 
        $total_count = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX."comment ".$condition);

        $list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."comment".$condition." ORDER BY id DESC ".$limit);
        
        $pager = buildPage("comment#init_comment",array('comment_type'=>$comment_type,'comment_rel_id'=>$comment_rel_id),$total_count,$page,$page_site,1);
        $GLOBALS['tmpl']->assign('pager',$pager);
        $result['pager'] = $GLOBALS['tmpl']->fetch("inc/pages.html");

        foreach($list as $k=>$v){
            $userids[] = $v['user_id'];
        }
        $userids = array_unique($userids);
        require_once APP_ROOT_PATH."system/libs/user.php";
        $user_avatars = User::get_user_avatar($userids);
        
        foreach($list as $k=>$v){
            $temp['nickname'] = $v['nickname'];
            $temp['user_id'] = $v['user_id'];
            $temp['content'] = $v['content'];
            $temp['create_time'] = to_date($v['create_time'],'Y-m-d H:i:s');
            $temp['avatar'] = $user_avatars[$v['user_id']]['img'];
            $f_data[] = $temp;
        }
        $GLOBALS['tmpl']->assign("list",$f_data);
        $result['comment_total'] = $total_count;
        $result['html'] = $GLOBALS['tmpl']->fetch("inc/comment_item.html");
        ajax_return($result);
    }
    function save_comment(){
        global_run();
        if(empty($GLOBALS['user'])) //验证是否登录
        {
               $result['status'] = -1;
               ajax_return($result);
        }		
        $comment_type = intval($_GET['comment_type']); 
        $comment_rel_id = intval($_GET['comment_rel_id']);
        
        require_once APP_ROOT_PATH.'system/libs/contentcheck.php';
        $comment_content = strim($_REQUEST['comment_content']);
        if(Contentcheck::checkword($comment_content)==1){
            $result['status'] = -100; //禁用
            ajax_return($result);
            exit;
        }
        $ins_data = array();
        $ins_data['user_id'] = intval($GLOBALS['user']['id']); 
        $ins_data['comment_type'] = $comment_type; 
        $ins_data['comment_rel_id'] = $comment_rel_id; 
        $ins_data['content'] = $comment_content; 
        $ins_data['nickname'] = $GLOBALS['user']['nickname'];
        $ins_data['create_time'] = NOW_TIME;
        
        require_once APP_ROOT_PATH."system/libs/comment.php";
        if(Comment::insert_comment($comment_type,$comment_rel_id,$ins_data)){
            //更新游记评论数量
            $GLOBALS['db']->query("UPDATE ".DB_PREFIX."tour_guide SET comment_count=comment_count+1 WHERE id=".$comment_rel_id);
            
            $avatar = get_spec_image($GLOBALS['user']['avatar']);
            //对图片路径的修复
            $domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?SITE_DOMAIN.APP_ROOT:app_conf("PUBLIC_DOMAIN_ROOT");
            $avatar = str_replace(APP_ROOT."./public/images/",$domain."/public/images/",$avatar);	
            $avatar = str_replace("./public/images/",$domain."/public/images/",$avatar);
            $result['status'] = 1;
            $result['html'] = '<div class="comment_content">
			<div class="comment_info">
				<div class="floor_info">
					<p class="floor_info_p">
						<a href="#">'.$ins_data['nickname'].'</a>
					</p>
					<div class="floor_content">
						<div class="inner_floor_content">
							<blockquote>
								<p>'.$ins_data['content'].'</p>
							</blockquote>
							<div class="quote_info">
								<span class="quote_time">发表于 '.to_date($ins_data['create_time'],'Y-m-d H:i:s').'</span>
							</div>
					    </div>
						
					</div>
                                        <div class="blank15"></div>
				</div>
                        </div>
			<div class="comment_portait">
				<a href="#"><img src="'.$avatar.'"></a>
		    </div>
		</div>';
        }else{
            $result['status'] = 0;
            $result['html'] = '';
        }
        ajax_return($result);
    }
}