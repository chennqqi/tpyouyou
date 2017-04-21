<?php
/* 
 * 评论处理类
 */
class Comment{
    
    /**
     * 加载评论模块
     * @param type $comment_type 评论类型(1:游记评论 2:动态瀑布流评论 3:文章资讯评论 )
     * @param type $comment_rel_id 关联编号
     * @return string
     */
    public static function init_comment($comment_type,$comment_rel_id){
        $comment_total = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."comment WHERE comment_type = ".$comment_type." AND comment_rel_id=".$comment_rel_id);
        $comment_list_ajax_url = url("comment#init_comment",array('comment_type'=>$comment_type,'comment_rel_id'=>$comment_rel_id));
        $comment_save_ajax_url = url("comment#save_comment",array('comment_type'=>$comment_type,'comment_rel_id'=>$comment_rel_id));
        $GLOBALS['tmpl']->assign("comment_type",$comment_type);
        $GLOBALS['tmpl']->assign("comment_rel_id",$comment_rel_id);
        $GLOBALS['tmpl']->assign("comment_total",$comment_total);
        $GLOBALS['tmpl']->assign("comment_list_ajax_url",$comment_list_ajax_url);
        $GLOBALS['tmpl']->assign("comment_save_ajax_url",$comment_save_ajax_url);
        return $GLOBALS['tmpl']->fetch("inc/init_comment.html");
    }
    
    /**
     * 评论数据添加模块
     * @param type $comment_type 评论类型
     * @param type $comment_rel_id关联编号
     * @param type $data 评论数据 
     * array('user_id'=>'1','nickname'=>'jobin','content'=>'看起来还不错');
     *  
     * @return boolean
     */
    public static function insert_comment($comment_type,$comment_rel_id,$data){
        $ins_data = array();
        $ins_data['content'] = $data['content'];
        $ins_data['comment_type'] = $comment_type;
        $ins_data['comment_rel_id'] = $comment_rel_id;
        $ins_data['user_id'] = $data['user_id'];
        $ins_data['nickname'] = $data['nickname'];
        $ins_data['create_time'] = NOW_TIME;
        
        $GLOBALS['db']->autoExecute(DB_PREFIX."comment",$ins_data,'INSERT');
        if($GLOBALS['db']->affected_rows()){
            //发放奖励
            require_once APP_ROOT_PATH."system/libs/user.php";
            User::modify_account(intval($data['user_id']),3,APP_CONF("COMMENT_EXP"),"评论增加经验");
            return true;
        }else{
            return false;
        }
        
    }
}

