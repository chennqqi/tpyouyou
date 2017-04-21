<?php

class askModule extends BaseModule{
    public function uc_ask_list(){
        //判断用户登录
        global_run();
        
        if(empty($GLOBALS['user'])) //验证是否登录
        {
                app_redirect(url("user#login"));
        }		
        $user = $GLOBALS['user'];
        
        $where = array();
        $where[] = 'user_id='.$user['id'];
        if($_GET['is_reply'] == 'n' || $_GET['is_reply'] == 'y'){
            $is_reply = $_GET['is_reply'];
            $where[] = " is_reply=".($is_reply=="n"?0:1);
            $GLOBALS['tmpl']->assign("is_reply",$is_reply);
        }
        if(count($where)){
            $condition = " WHERE ".implode(" AND ",$where);
        }else{
            $condition = "";
        }

        //分页类
        require_once APP_ROOT_PATH.APP_NAME."/Lib/page.php";
	
        $page = intval($_REQUEST['p']);
        if($page==0)
                $page = 1;
        $limit = (($page-1)*USER_PAGE_SIZE).",".USER_PAGE_SIZE;

        $total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ask ".$condition);
        if($total>0)
                $list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."ask  ".$condition." order by id desc limit ".$limit);

        
        
        $page = new Page($total,USER_PAGE_SIZE);   //初始化分页对象
        $p  =  $page->show();
        $GLOBALS['tmpl']->assign('pages',$p);
        
        
        $GLOBALS['tmpl']->assign("ask_type",$GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."ask_type"));
        $GLOBALS['tmpl']->assign('list',$list);
        init_app_page();
        $GLOBALS['tmpl']->display("uc_ask_list.html");
    }
    
    //提交问题
    public function do_ask(){
        global_run();
        
        if(empty($GLOBALS['user'])) //验证是否登录
        {
                app_redirect(url("user#login"));
        }		
        $user = $GLOBALS['user'];
        $title = strim($_REQUEST['title']);
        $content = strim($_REQUEST['content']);
        require_once APP_ROOT_PATH.'system/libs/contentcheck.php';

        if(Contentcheck::checkword($title)==1){
            showErr("请勿提交非法内容");
            exit;
        }
        if(Contentcheck::checkword($content)==1){
            showErr("请勿提交非法内容");
            exit;
        }
        
        $data = array();
        $data['ask_type_id'] = $_REQUEST['ask_type_id'];
        $data['title'] = $title;
        $data['content'] = $content;
        $data['user_id'] = $user['id'];
        $data['nickname'] = $user['nickname'];
        $data['create_time'] = NOW_TIME;
        
        $GLOBALS['db']->autoExecute(DB_PREFIX."ask",$data);
        if($GLOBALS['db']->affected_rows()){
            app_redirect(url("ask#uc_ask_list"));
        }else{
            showErr("发布失败，请稍后再试");
        }
    }
    
    public function ajax_load_ask(){
        global_run();
        if(empty($GLOBALS['user'])) //验证是否登录
        {
                $data['status'] = false;
                $data['info'] = "请选登录";
                $data['jump'] = url("user#login");
                ajax_return($data);
        }
        $id = intval($_REQUEST['id']);
        $ask = $GLOBALS['db']->getRow("SELECT reply_content FROM ".DB_PREFIX."ask WHERE user_id = ".$GLOBALS['user']['id']." AND id=".$id);
        if($ask){
            $data['status'] = true;
            $data['content'] = $ask['reply_content'];		
            ajax_return($data);
        }else
        {
                $data['status'] = false;
                $data['info'] = "暂无回复";
                $data['jump'] = "";
                ajax_return($data);
        }
    }
    
    public function ajax_del_ask(){
        global_run();
        if(empty($GLOBALS['user'])) //验证是否登录
        {
                showErr("登录超时",1,url("user#login"));
        }
        $id = intval($_REQUEST['id']);
        $ask = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ask where user_id = ".$GLOBALS['user']['id']." and id= ".$id);
        $GLOBALS['db']->query("delete from ".DB_PREFIX."ask where user_id = ".$GLOBALS['user']['id']." and id = ".$id);
        if($GLOBALS['db']->affected_rows()>0)
        {
                $GLOBALS['db']->query("update ".DB_PREFIX."user set ask_count = ask_count -1 where id = ".$GLOBALS['user']['id']);
        }
        showSuccess("删除成功",1);
    }
    

}
