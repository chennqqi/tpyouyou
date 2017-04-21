<?php

class uc_reviewModule extends BaseModule{
    public function index(){
        //判断用户登录
        global_run();
        
        if(empty($GLOBALS['user'])) //验证是否登录
        {
                app_redirect(url("user#login"));
        }		
        $user = $GLOBALS['user'];
         //分页类
        require_once APP_ROOT_PATH.APP_NAME."/Lib/page.php";
	
        $page = intval($_REQUEST['p']);
        if($page==0)
                $page = 1;
        $limit = (($page-1)*USER_PAGE_SIZE).",".USER_PAGE_SIZE;

        $condition = " WHERE user_id =".$user['id']; 
        $total = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX."review ".$condition);
        if($total>0)
                $list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."review".$condition." ORDER BY id DESC limit ".$limit);

        
        
        $page = new Page($total,USER_PAGE_SIZE);   //初始化分页对象
        $p  =  $page->show();
        $GLOBALS['tmpl']->assign('pages',$p);
        

        foreach($list as $k=>$v){
            $userids[] = $v['user_id'];
        }
        $userids = array_unique($userids);
        require_once APP_ROOT_PATH."system/libs/user.php";
        $user_avatars = User::get_user_avatar($userids);
        
        foreach($list as $k=>$v){
            $temp = $v;
            $temp['review_time'] = to_date($v['review_time'],"Y-m-d H:i:s");
            $temp['avatar'] = $user_avatars[$v['user_id']]['img'];
            //点评类型
            $temp['group_point'] = unserialize($v['group_point']);
            $temp['imgs'] = unserialize($v['image_list']);
            $temp['money'] = format_price_to_display($v['money']);
            $temp['voucher_count'] = format_price_to_display($v['voucher_count']);
            $temp['point_satify'] = $v['point']*20;
            $f_data[] = $temp;
        }
        $GLOBALS['tmpl']->assign("list",$f_data);
        $review_html = $GLOBALS['tmpl']->fetch("inc/review_item.html");
        $GLOBALS['tmpl']->assign("review_html",$review_html);
        init_app_page();
        $GLOBALS['tmpl']->display("uc_review.html");
    }
}