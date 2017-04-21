<?php
/**
 * 游记
 */
class guideModule extends BaseModule{
    function index() {
        global_run();
        $condition = '';
        $where[] = " is_public = 2 ";
        $keyword = strim($_GET['keyword']);
        if($keyword){
            $where[] = " match(area_match) against('".str_to_unicode_string($keyword)."') ";
            $GLOBALS['tmpl']->assign('keyword',$keyword);
        }
        $GUIDE_PAGE_LOAD_GOUNT = APP_CONF("GUIDE_PAGE_LOAD_GOUNT");
        $GUIDE_PAGE_ITEM_COUNT = APP_CONF("GUIDE_PAGE_ITEM_COUNT");
        $GLOBALS['tmpl']->assign('GUIDE_PAGE_LOAD_GOUNT',$GUIDE_PAGE_LOAD_GOUNT);
        $GLOBALS['tmpl']->assign('GUIDE_PAGE_ITEM_COUNT',$GUIDE_PAGE_ITEM_COUNT);
        
        if(count($where)>0){
            $condition = " WHERE ".implode(" AND ",$where);
        }

        $total_count = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX."tour_guide ".$condition);
        
        //获取热门景点
        $hot_spot_str = app_conf("GUIDE_HOT_SPOT");
        $spot_list = explode(",", $hot_spot_str);
        //获取景点
        //$spot_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."tour_guide_spot GROUP BY area_name ORDER BY spot_id DESC limit 16");
        $guide_count['total'] = $total_count;
        $guide_count['spot_count'] = $GLOBALS['db']->getOne("SELECT count(distinct(area_name)) FROM ".DB_PREFIX."tour_guide_spot");

        $GLOBALS['tmpl']->assign("total_count",$total_count);
        $GLOBALS['tmpl']->assign('spot_list',$spot_list);
        $GLOBALS['tmpl']->assign('guide_count',$guide_count);
        init_app_page();
        
        
        $seo_title = app_conf("GUIDE_SEO_TITLE")?app_conf("GUIDE_SEO_TITLE"):"游记攻略 - ".app_conf("SITE_NAME");
        $seo_keywords = app_conf("GUIDE_SEO_KEYWORD")?app_conf("GUIDE_SEO_KEYWORD"):"游记攻略 - ".app_conf("SITE_NAME");
        $seo_description = app_conf("GUIDE_SEO_DESCRIPTION")?app_conf("GUIDE_SEO_DESCRIPTION"):"游记攻略 - ".app_conf("SITE_NAME");
        
        $GLOBALS['tmpl']->assign("site_name",$seo_title);
        $GLOBALS['tmpl']->assign("site_keyword",$seo_keywords);
        $GLOBALS['tmpl']->assign("site_description",$seo_description);
        $GLOBALS['tmpl']->display("guide/index.html");
    }
    /**
     * 写游记
     */
    function writethread(){
        global_run();
        
        if(empty($GLOBALS['user'])) //验证是否登录
        {
                app_redirect(url("user#login"));
        }		
        $user = $GLOBALS['user'];
        
        $id = intval($_GET['id']);
        
        $is_new_guide = false;
        if($id){ //存在查询出已有的草稿数据
            $guide_item = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."tour_guide_temp WHERE id =".$id." AND is_public=0");
            if($guide_item){//存在游记
                    $GLOBALS['tmpl']->assign("guide_item",$guide_item);
                    //获取游记每日信息
                    $route_count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."tour_guide_route WHERE guide_id=".$id);
                    if(!$route_count){//判断是否为第一次发布
                             //默认取出第一天HTML
                                $day_html = $this->getGuideDayItem($id);
                                $GLOBALS['tmpl']->assign("day_html",$day_html);
                    }else{//已经有发布过的数据
                            //获取每天的信息
                            $route_data_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."tour_guide_route WHERE guide_id=".$id." ORDER BY route_time ASC");
                            //获取每天的景点
                            $spot_data_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."tour_guide_spot WHERE guide_id=".$id);
                            //获取每天的图片
                            $gallery_data_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."tour_guide_gallery WHERE guide_id=".$id);

                            //处理数据排序
                            //景点==》图片
                            $f_gallery_data_list = array();
                            foreach($gallery_data_list as $g_k=>$g_v){
                               // $g_v['image'] = SITE_DOMAIN."/".$g_v['image'];
                                $f_gallery_data_list[$g_v['spot_id']][] = $g_v;
                            }

                            //天===》景点格式
                            $f_spot_data_list = array();
                            foreach($spot_data_list as $s_k=>$s_v){
                                $s_v['gallery'] = $f_gallery_data_list[$s_v['spot_id']];
                                $s_v['pic_count'] = count($f_gallery_data_list[$s_v['spot_id']]);
                                $f_spot_data_list[$s_v['route_id']][] = $s_v;
                            }

                            //整合 每天 景点 图片数据
                            $data_list = array();
                            foreach($route_data_list as $r_k=>$r_v){
                                $temp = array();
                                if($r_v['route_time'] == "0000-00-00"){
                                    $r_v['day_date'] = "请选择日期";
                                }else{
                                    $r_v['day_date'] = $r_v['route_time'];
                                }
                                $r_v['day_num'] = $r_k+1;
                                $temp = $r_v;
                                $temp['spot'] = $f_spot_data_list[$r_v['route_id']];
                                $data_list[] = $temp;
                            }

                            $day_html = $this->getGuideDayList($data_list);
                            $GLOBALS['tmpl']->assign("day_html",$day_html);
                    }

            }else{
                $is_new_guide = true;
            }


            $GLOBALS['tmpl']->assign("id",$id);
            init_app_page();
            $GLOBALS['tmpl']->display("guide/writethread.html");

        }else{ //不存在草稿新建草稿
            $is_new_guide = true;
        }
        
        //新发布游记
        if($is_new_guide){
            //查询是否有未发布没标题的游记编号
            $item = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."tour_guide_temp WHERE user_id =".$user['id']."  AND is_public=0 ");
            //如果存在进行跳转
            if($item){
                app_redirect(url("guide#writethread",array("id"=>$item['id'])));
            }else{
                if($user){
                    $ins_data = array(
                        'user_id' => $user['id'],
                        'nickname' => $user['user_name'],
                        'is_public'=>'0',
                        'create_time'=> NOW_TIME,
                    );
                    $GLOBALS['db']->autoExecute(DB_PREFIX.'tour_guide_temp',$ins_data);
                    $id = $GLOBALS['db']->insert_id();
                    app_redirect(url("guide#writethread",array("id"=>$id)));
                }
            }
        }
    }
    /**
     * 再次编辑已经审核的游记
     */
    function again_writethread(){
        global_run();
        if(empty($GLOBALS['user'])) //验证是否登录
        {
                app_redirect(url("user#login"));
        }		
        $user = $GLOBALS['user'];
        
        $id = intval($_GET['id']);
        if(!app_conf("GUIDE_IS_AGAIN")){ //必须开启 允许编辑
            app_redirect(url("guide#index"));
            exit;
        }
        if($id){ //存在查询出已有的草稿数据
            $guide_item = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."tour_guide WHERE id =".$id." AND is_public=2");
            if($guide_item){//存在游记
                    $GLOBALS['tmpl']->assign("guide_item",$guide_item);
                    //获取游记每日信息
                    $route_count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."tour_guide_route WHERE guide_id=".$id);
                    if($route_count){//至少有一天数据
                            //获取每天的信息
                            $route_data_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."tour_guide_route WHERE guide_id=".$id." ORDER BY route_time ASC");
                            //获取每天的景点
                            $spot_data_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."tour_guide_spot WHERE guide_id=".$id);
                            //获取每天的图片
                            $gallery_data_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."tour_guide_gallery WHERE guide_id=".$id);

                            //处理数据排序
                            //景点==》图片
                            $f_gallery_data_list = array();
                            foreach($gallery_data_list as $g_k=>$g_v){
                               // $g_v['image'] = SITE_DOMAIN."/".$g_v['image'];
                                $f_gallery_data_list[$g_v['spot_id']][] = $g_v;
                            }

                            //天===》景点格式
                            $f_spot_data_list = array();
                            foreach($spot_data_list as $s_k=>$s_v){
                                $s_v['gallery'] = $f_gallery_data_list[$s_v['spot_id']];
                                $s_v['pic_count'] = count($f_gallery_data_list[$s_v['spot_id']]);
                                $f_spot_data_list[$s_v['route_id']][] = $s_v;
                            }

                            //整合 每天 景点 图片数据
                            $data_list = array();
                            foreach($route_data_list as $r_k=>$r_v){
                                $temp = array();
                                if($r_v['route_time'] == "0000-00-00"){
                                    $r_v['day_date'] = "请选择日期";
                                }else{
                                    $r_v['day_date'] = $r_v['route_time'];
                                }
                                $r_v['day_num'] = $r_k+1;
                                $temp = $r_v;
                                $temp['spot'] = $f_spot_data_list[$r_v['route_id']];
                                $data_list[] = $temp;
                            }

                            $day_html = $this->getGuideDayList($data_list);
                            $GLOBALS['tmpl']->assign("day_html",$day_html);
                    }

            }else{
                $is_new_guide = true;
            }


            $GLOBALS['tmpl']->assign("id",$id);
            init_app_page();
            $GLOBALS['tmpl']->display("guide/again_writethread.html");

        }else{ //不存在草稿新建草稿
            app_redirect(url("guide#index"));
        }
    }
    /**
     * 查看游记
     */
    function show(){
    	global_run();
        $guide_id = intval($_GET['id']);
        if($guide_id){
          $guide_item = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."tour_guide WHERE id=".$guide_id);
        }
        if($guide_item){
            //游记浏览次数增加
            $guide_item['browse_count'] = $guide_item['browse_count']+1; 
            $GLOBALS['db']->autoExecute(DB_PREFIX."tour_guide",array("browse_count"=>$guide_item['browse_count']),"UPDATE","id=".$guide_id);
            
            $gallery_list = unserialize($guide_item['image_list']);
            //格式化图片key为景点
            foreach($gallery_list as $k=>$v){
                $f_gallery_list[$v['spot_id']][] = $v;
            }
            
            $route_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."tour_guide_route WHERE guide_id = ".$guide_id." ORDER BY route_time ASC");
            $f_route = $route_list[0];
            $l_route = $route_list[(count($route_list)-1)];
            if(count($route_list)){
                $route_ids = array();
                foreach($route_list as $k=>$v){
                    $route_ids[] = $v['route_id'];
                }
                //获取景点信息
                $spot_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."tour_guide_spot WHERE route_id in(".  implode(",", $route_ids).")");
                foreach($spot_list as $k=>$v){
                    $v["gallery_list"] = $f_gallery_list[$v['spot_id']];
                    $f_spot_list[$v['route_id']][] = $v;
                }
            
                //整合数据
                $route_data = array();
                foreach($route_list as $r_k=>$r_v){
                    $r_v['day_num'] = $r_k+1;
                    $r_v['spot_list'] = $f_spot_list[$r_v['route_id']];
                    $route_data[] = $r_v;
                }
            }
        }
        
        $start_spot = $f_spot_list[$f_route['route_id']][0]['area_name'];
        $end_spot = $f_spot_list[$l_route['route_id']][(count($f_spot_list[$l_route['route_id']])-1)]['area_name'];
        $guide_item['start_date'] = $f_route['route_time'];
        $guide_item['start_end_address'] = $start_spot."到".$end_spot;
        
        $user = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user WHERE id=".intval($guide_item['user_id']));
        
        //载入评论模块
        require APP_ROOT_PATH."system/libs/comment.php";

        $comment_html = Comment::init_comment(1,$guide_id);

        $GLOBALS['tmpl']->assign("comment_html",$comment_html);
        $GLOBALS['tmpl']->assign("user",$user);
        $GLOBALS['tmpl']->assign("guide_item",$guide_item);
        $GLOBALS['tmpl']->assign("route_data",$route_data);
        init_app_page();
        
        
        $seo_title = $guide_item['title'];

        foreach($spot_list as $k=>$v){
            $spot_name[] = $v['area_name'];
        }
        $seo_keywords = "景区,".implode(",", $spot_name);
        $seo_description = app_conf("GUIDE_SEO_DESCRIPTION");
        
        $GLOBALS['tmpl']->assign("site_name",$seo_title);
        $GLOBALS['tmpl']->assign("site_keyword",$seo_keywords);
        $GLOBALS['tmpl']->assign("site_description",$seo_description);
        
        $GLOBALS['tmpl']->display("guide/show.html");
    }

    /**
     * 获取没发布过游记的第一天游记
     * @param type $id
     * @return type
     */
    function getGuideDayItem($id){
       //插入一条游记每天行程记录
       $GLOBALS['db']->autoExecute(DB_PREFIX.'tour_guide_route',array("guide_id"=>$id));
       $route_id = $GLOBALS['db']->insert_id();

        $vo = array(
            'route_id'=>$route_id,
            'day_num'=>1,
            'day_date'=>"请选择日期"
        );

        $GLOBALS['tmpl']->assign("vo",$vo);

        return  $GLOBALS['tmpl']->fetch("guide/guide_day_item.html");


    }
    
    function getGuideDayList($data_list){
        $GLOBALS['tmpl']->assign("data_list",$data_list);
        return  $GLOBALS['tmpl']->fetch("guide/guide_day_list.html");
    }
    
    
    /*************************************************************************
     *                          用户中心调用的方法                            *
     *************************************************************************/
    
  
    /**
     * 用户中心游记管理列表
     */
    function uc_guide_list(){
        global_run();
        if(empty($GLOBALS['user'])) //验证是否登录
        {
                app_redirect(url("user#login"));
        }		
        $user = $GLOBALS['user'];
        $is_public  = intval($_GET['is_public'])<2?1:2;
        $GLOBALS['tmpl']->assign('is_public',$is_public);
        
        

        //条件
        if($is_public <2){
            $condition = " is_public<2";
        }else{
            $condition = " is_public=".$is_public;
        }

        $condition .= " AND user_id=".$user['id'];
        if($condition)
            $condition = " WHERE ".$condition;
        
        
        require_once APP_ROOT_PATH.APP_NAME."/Lib/page.php";
	
        $page = intval($_REQUEST['p']);
        if($page==0)
                $page = 1;
        $limit = (($page-1)*USER_PAGE_SIZE).",".USER_PAGE_SIZE;

        if($is_public==2){ 
            $select_table = "tour_guide";
        }else{
            $select_table = "tour_guide_temp";
        }

        $total_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX.$select_table.$condition);
        if($total_count>0)
                $list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX.$select_table.$condition." order by id desc limit ".$limit);

        $page = new Page($total_count,USER_PAGE_SIZE);   //初始化分页对象
        $p  =  $page->show();
        $GLOBALS['tmpl']->assign('pages',$p);
        $GLOBALS['tmpl']->assign("total_count",$total_count);
        $GLOBALS['tmpl']->assign('list',$list);
        $GLOBALS['tmpl']->assign('is_again',app_conf("GUIDE_IS_AGAIN"));
        
        $GLOBALS['tmpl']->assign('ajax_del_guide_url',url("guide#ajax_del_guide"));
        init_app_page();
        $GLOBALS['tmpl']->display("waterfall/uc_guide_list.html");
    }
    
    /**
     * 用户中心单个游记预览页面
     */
    function uc_guide_item(){
        $guide_id = intval($_GET['id']);
        $check_type = $_GET['type'];
        if(empty($guide_id) || empty($check_type)){
            app_redirect(url("guide#index"));
            exit;
        }
        //只有管理员和用户本身可以查看
        if($check_type == 'user'){
            global_run();
            if(empty($GLOBALS['user'])) //验证是否登录
            {
                    app_redirect(url("user#login"));
            }		
            $user = $GLOBALS['user'];
            $guide_item = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."tour_guide_temp WHERE id=".$guide_id." AND user_id =".$user['id']);
        }elseif($check_type=="admin"){
            $admin = es_session::get(md5(app_conf("AUTH_KEY")));
            
            if($admin['adm_id']>0){
                $guide_item = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."tour_guide_temp WHERE id=".$guide_id);
                if($guide_item){
                    $user = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user WHERE id=".$guide_item['user_id']);
                }else{
                    app_redirect(url("guide#index"));
                }
            }else{
                app_redirect(url("guide#index"));
            }
        }
        
        
        if($guide_item){
            $gallery_list = unserialize($guide_item['image_list']);
            //格式化图片key为景点
            foreach($gallery_list as $k=>$v){
                $f_gallery_list[$v['spot_id']][] = $v;
            }
            
            $route_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."tour_guide_route WHERE guide_id = ".$guide_id." ORDER BY route_time ASC");
            if(count($route_list)){
                $route_ids = array();
                foreach($route_list as $k=>$v){
                    $route_ids[] = $v['route_id'];
                }
                //获取景点信息
                $spot_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."tour_guide_spot WHERE route_id in(".  implode(",", $route_ids).")");
                foreach($spot_list as $k=>$v){
                    $v["gallery_list"] = $f_gallery_list[$v['spot_id']];
                    $f_spot_list[$v['route_id']][] = $v;
                }
            
                //整合数据
                $route_data = array();
                foreach($route_list as $r_k=>$r_v){
                    $r_v['day_num'] = $r_k+1;
                    $r_v['spot_list'] = $f_spot_list[$r_v['route_id']];
                    $route_data[] = $r_v;
                }
            }
        }  else {
            app_redirect(url("guide#index"));
        }
        
        $GLOBALS['tmpl']->assign("user",$user);
        $GLOBALS['tmpl']->assign("check_type",$check_type);
        $GLOBALS['tmpl']->assign("guide_item",$guide_item);
        $GLOBALS['tmpl']->assign("spot_list",$spot_list);
        $GLOBALS['tmpl']->assign("route_data",$route_data);
        $GLOBALS['tmpl']->assign("ajax_del_guide_url",url("guide#ajax_del_guide"));
        
        init_app_page();
        $GLOBALS['tmpl']->display("waterfall/uc_guide_item.html");
    }
    
   





    /*************************************************************************
     *                          ajax方法                                     *
     *************************************************************************/
    
    
    /**
     * 保存景点
     */
    function ajax_save_spot(){
        $is_again = intval($_REQUEST['is_again']);
        $table_name = "tour_guide_temp";
        if($is_again){
            $table_name = "tour_guide";
        }
        
        $area_list = $_REQUEST['area_list'];
        $route_id = intval($_REQUEST['route_id']);
        $guide_id = intval($_REQUEST['guide_id']);
        $route_time = $_REQUEST['route_time'];
        global_run();
        if(empty($GLOBALS['user'])){
            $result['status'] = 2;
        }else{
            $uid = $GLOBALS['user']['id'];
            if($GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX.$table_name." WHERE id=".$guide_id." AND user_id=".$uid)){
                $area_list = explode(",",$area_list);
                $result = array();
                $result['html_1'] = '';//景点的HTML
                $result['html_2'] = '';//图片的HTML
                require_once APP_ROOT_PATH.'system/libs/contentcheck.php';
		$temp_arr = array();
		foreach($area_list  as $k=>$v){
			if(empty($v))
				continue;
                        
                        if(Contentcheck::checkword($v)==1){
                            continue;
                        }
                        
			$v= trim($v);
			$spot_ins = array();
			$spot_ins['guide_id'] = $guide_id;
			$spot_ins['route_id'] = $route_id;
			$spot_ins['area_name'] = $v;
			$spot_ins['route_time'] = $route_time;
			$GLOBALS['db']->autoExecute(DB_PREFIX.'tour_guide_spot',$spot_ins);
			$spot_id = $GLOBALS['db']->insert_id();

                        if($spot_id){             
                        
                            $html_1.='<div class="poi_dot default_poi poi_dot_spot_'.$spot_id.' poi_dot_route_'.$route_id.'" data_route_id="'.$route_id.'" data_spot_id="'.$spot_id.'">
                                                                                    <p class="spot_area_name">'.$v.'</p>
                                                                                    <p class="spot_action">
                                                                                            <a class="edit_spot" href="javascript:void(0);" data_route_id="'.$route_id.'"  data_spot_id="'.$spot_id.'"></a>
                                                                                            <a class="del_spot" href="javascript:void(0);"  data_route_id="'.$route_id.'"  data_spot_id="'.$spot_id.'"></a>
                                                                                    </p>
                                                                                    <p class="spot_gallery_num"><span>0</span>张</p>
                                                                            <div class="arr_r">········</div></div>';


                            $html_2.='<div class="photo_content photo_content_route_'.$route_id.' photo_content_spot_'.$spot_id.' clearfix ui-sortable ">

                                        <!--行程内没有照片时，允许添加照片-->
                                        <div class="photo_null f_l" style="">
                                                <i></i>
                                                <span>这一天没有照片，请<a class="add_pic_btn" href="javascript:void(0);" onclick=" $.Add_Pic('.$route_id.','.$spot_id.')">添加照片</a></span>

                                        </div>
                                </div>';
                        }
		}
                $result['html_1'] = $html_1;
                $result['html_2'] = $html_2;
            }
                
        }	
        ajax_return($result);
    }
    /**
     * 编辑景点
     */
    function ajax_edit_spot(){
         //判断是不是 二次编辑的
        $is_again = intval($_REQUEST['is_again']);
        $spot_id = intval($_REQUEST['spot_id']);
        
        $table_name = "tour_guide_temp";
        if($is_again){
            $table_name = "tour_guide";
        }
        global_run();
        if(empty($GLOBALS['user'])){
            $result['status'] = 2;
        }else{
            //敏感词过滤
            $area_name = trim($_REQUEST['area_name']);
            require_once APP_ROOT_PATH.'system/libs/contentcheck.php';
            if(Contentcheck::checkword($area_name)==1){
                $result['status'] = -100; //禁用
                ajax_return($result);
                exit;
            }
            
            $uid = $GLOBALS['user']['id'];
            $guide_id = $GLOBALS['db']->getOne("SELECT guide_id FROM ".DB_PREFIX."tour_guide_spot WHERE spot_id=".$spot_id);
            if($GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX.$table_name." WHERE id=".$guide_id." AND user_id=".$uid)){
                //更新景点主表
                $GLOBALS['db']->autoExecute(DB_PREFIX.'tour_guide_spot',array('area_name'=>$area_name),'UPDATE',"spot_id=".$spot_id);

                if($GLOBALS['db']->affected_rows()){
                        //更新景点图片关联表
                        if($GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX."tour_guide_gallery WHERE spot_id=".$spot_id)>0){
                                $GLOBALS['db']->autoExecute(DB_PREFIX.'tour_guide_gallery',array('area_name'=>$area_name),'UPDATE',"spot_id=".$spot_id);
                        }
                        $result['status'] = 1;
                }else{
                        $result['status'] = 0;
                }
            }
        }
    	
        ajax_return($result);
    }
    /**
     * 删除景点
     */
    function ajax_del_spot(){
        //判断是不是 二次编辑的
        $is_again = intval($_REQUEST['is_again']);
        $spot_id = intval($_REQUEST['spot_id']);
        
        $table_name = "tour_guide_temp";
        if($is_again){
            $table_name = "tour_guide";
        }
        global_run();
        if(empty($GLOBALS['user'])){
            $result['status'] = 2;
        }else{
            $uid = $GLOBALS['user']['id'];
            $guide_id = $GLOBALS['db']->getOne("SELECT guide_id FROM ".DB_PREFIX."tour_guide_spot WHERE spot_id=".$spot_id);
            if($GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX.$table_name." WHERE id=".$guide_id." AND user_id=".$uid)){
                $GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."tour_guide_spot WHERE spot_id=".$spot_id);
                $GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."tour_guide_gallery WHERE spot_id=".$spot_id);
            }

            if($GLOBALS['db']->error()==''){
                    $result['status']=1;
            }else{
                    $result['status'] = 0;
            }
        }
    	
    	ajax_return($result);
    }
    /**
     * 保存图片
     */
    function ajax_save_img(){
        //判断是不是 二次编辑的
        $is_again = intval($_REQUEST['is_again']);
        $table_name = "tour_guide_temp";
        if($is_again){
            $table_name = "tour_guide";
        }
        global_run();
        if(empty($GLOBALS['user'])){
            $result['status'] = 2;
        }else{
            $guide_id = intval($_REQUEST['guide_id']);
            $uid = $GLOBALS['user']['id'];
            if($GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX.$table_name." WHERE id=".$guide_id." AND user_id=".$uid)){
                $ins_data = array();
                $ins_data['guide_id'] = $guide_id;
                $ins_data['spot_id'] = intval($_REQUEST['spot_id']);
                $ins_data['route_id'] = intval($_REQUEST['route_id']);
                $ins_data['area_name'] = strim($_REQUEST['area_name']);
                $ins_data['image'] = strim($_REQUEST['image']);
                $ins_data['route_time'] = strim($_REQUEST['route_time']);
                $ins_data['width'] = intval($_REQUEST['width']);
                $ins_data['height'] = intval($_REQUEST['height']);
                if($ins_data['spot_id'] && $ins_data['image']){
                    $GLOBALS['db']->autoExecute(DB_PREFIX."tour_guide_gallery",$ins_data);
                    $result['status'] = 1;
                    $result['gallery_id'] = $GLOBALS['db']->insert_id();
                }
            }
        }
        
        ajax_return($result);
        
    }
    /**
     * 删除单张图片
     */
    function ajax_del_img(){
        //判断是不是 二次编辑的
        $is_again = intval($_REQUEST['is_again']);
        $gallery_id = intval($_REQUEST['gallery_id']);
        $table_name = "tour_guide_temp";
        if($is_again){
            $table_name = "tour_guide";
        }
        global_run();
        if(empty($GLOBALS['user'])){
            $result['status'] = 2;
        }else{
            $uid = $GLOBALS['user']['id'];
            $guide_id = $GLOBALS['db']->getOne("SELECT guide_id FROM ".DB_PREFIX."tour_guide_gallery WHERE gallery_id = ".$gallery_id);
            if($GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX.$table_name." WHERE id = ".$guide_id." AND user_id=".$uid)){
                $GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."tour_guide_gallery WHERE gallery_id = ".$gallery_id);
                if($GLOBALS['db']->error()==""){
                    $result['status'] =1;
                }
            }
            
        }
        
        ajax_return($result);
    }
    
    /**
     * 添加一天
     */
    function ajax_add_day(){
        $result = array();
    	$result['status'];
        $guide_id = intval($_REQUEST['guide_id']);
        $day_count = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX."tour_guide_route WHERE guide_id = ".$guide_id);
        if($day_count>0){
            $curr_num_day = $day_count+1;

            $GLOBALS['db']->autoExecute(DB_PREFIX.'tour_guide_route',array("guide_id"=>$guide_id));
            $route_id = $GLOBALS['db']->insert_id();
            if($route_id){
                $result['status'] =1;
                $vo = array(
                    'route_id'=>$route_id,
                    'day_num'=>$curr_num_day,
                    'day_date'=>"请选择日期"
                );

                $GLOBALS['tmpl']->assign("vo",$vo);

                $result['html'] = $GLOBALS['tmpl']->fetch("inc/guide/guide_day_item.html");
            }
        }
        
        
        ajax_return($result);
    }
    /**
     * 删除景点
     */
    function ajax_del_route(){
        
        $is_again = intval($_REQUEST['is_again']);
        $route_id = intval($_REQUEST['route_id']);
        $table_name = "tour_guide_temp";
        if($is_again){
            $table_name = "tour_guide";
        }
        global_run();
        if(empty($GLOBALS['user'])){
            $result['status'] = 2;
        }else{
            //保证用户只能删除自己的数据
            $uid = $GLOBALS['user']['id'];
            $guide_id = $GLOBALS['db']->getOne("SELECT guide_id FROM ".DB_PREFIX."tour_guide_route WHERE route_id=".$route_id);
            if($GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX.$table_name." WHERE id=".$guide_id." AND user_id=".$uid)){
                if($GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX."tour_guide_spot WHERE route_id=".$route_id)>0){//删除关联图片表数据
                    $GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."tour_guide_spot WHERE route_id=".$route_id);
                    $GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."tour_guide_gallery WHERE route_id=".$route_id);
                }
                $GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."tour_guide_route WHERE route_id=".$route_id);

                if($GLOBALS['db']->error()=="")
                {
                        $result['status'] = 1;
                }
                else
                {
                        $result['status'] = 0;
                }
            }
            
        }

    	
    	ajax_return($result);
    }
    
    /**
     * 保存输入框数据
     */
    function ajax_save_input_data(){
        $is_again = intval($_REQUEST['is_again']);
        $table_name = "tour_guide_temp";
        if($is_again){
            $table_name = "tour_guide";
        }
        global_run();
        if(empty($GLOBALS['user'])){
            $result['status'] = 2;
        }else{
            $result = array();
            $result['status'] = 0;
            $guide_id = intval($_REQUEST['guide_id']);
            $route_id = intval($_REQUEST['route_id']);
            $input_data = strim($_REQUEST['input_data']);
            $data_type = strim($_REQUEST['data_type']);
            //保证用户只能删除自己的数据
            $uid = $GLOBALS['user']['id'];
            require_once APP_ROOT_PATH.'system/libs/contentcheck.php';
            if(Contentcheck::checkword($input_data)==1){
                $result['status'] = -100; //禁用
                ajax_return($result);
                exit;
            }
            if($GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX.$table_name." WHERE id=".$guide_id." AND user_id=".$uid)){
                switch ($data_type){
                    case "guide_title":
                        $GLOBALS['db']->autoExecute(DB_PREFIX.$table_name,array("title"=>$input_data),"UPDATE","id=".$guide_id);
                        if($GLOBALS['db']->affected_rows()){
                                 $result['status'] = 1;
                            }
                        break;
                    case "route_title":
                    case "route_content":
                        //每日的标题和内容
                        if($GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX."tour_guide_route WHERE route_id=".$route_id)>0 && !empty($input_data)){
                            if($data_type == "route_title"){
                                $GLOBALS['db']->autoExecute(DB_PREFIX."tour_guide_route",array("title"=>$input_data),"UPDATE","route_id=".$route_id);
                            }elseif($data_type == "route_content"){
                                $GLOBALS['db']->autoExecute(DB_PREFIX."tour_guide_route",array("content"=>$input_data),"UPDATE","route_id=".$route_id);
                            }
                            if($GLOBALS['db']->affected_rows()){
                                 $result['status'] = 1;
                            }
                        }
                        break;
                }
            }
        }
        
        ajax_return($result);
            
    }
    
    /*
     * 更新时间
     */
    function ajax_save_route_time(){
        $result = array();
        $result['status'] = 0;
        $is_again = intval($_REQUEST['is_again']);
        $table_name = "tour_guide_temp";
        if($is_again){
            $table_name = "tour_guide";
        }
        $route_id = intval($_REQUEST['route_id']);
        $route_time = strim($_REQUEST['route_time']);
        global_run();

        if(empty($GLOBALS['user'])){
            $result['status'] = 2;
        }else{
             //保证用户只能删除自己的数据
            $uid = $GLOBALS['user']['id'];
            $guide_id = $GLOBALS['db']->getOne("SELECT guide_id FROM ".DB_PREFIX."tour_guide_route WHERE route_id=".$route_id);
            if($GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX.$table_name." WHERE id=".$guide_id." AND user_id=".$uid)){
                if($route_id>0 && !empty($route_time)){
                    $GLOBALS['db']->autoExecute(DB_PREFIX."tour_guide_route",array("route_time"=>$route_time),"UPDATE","route_id=".$route_id);
                    //同步更新，图片表和景区表
                    //更新景区表
                    $GLOBALS['db']->autoExecute(DB_PREFIX."tour_guide_spot",array("route_time"=>$route_time),"UPDATE","route_id=".$route_id);
                    //更新图片表
                    $GLOBALS['db']->autoExecute(DB_PREFIX."tour_guide_gallery",array("route_time"=>$route_time),"UPDATE","route_id=".$route_id);
                }
                if($GLOBALS['db']->affected_rows()){
                    $result['status'] = 1;
                }
            }
            
        }
        
        ajax_return($result);
    }


    /**
     * 保存草稿
     */
    function ajax_save_draft(){
         global_run();
        //模拟会员信息
        if(empty($GLOBALS['user'])){
            $result['status'] = 2;
        }else{
            $result = array();
            $result['status'] = 0;
            $uid = $GLOBALS['user']['id'];
            $guide_id = intval($_REQUEST['guide_id']);

            if($this->syn_guide_temp($guide_id,$uid)){
                $result['status'] = 1;
            }
        }
        
        ajax_return($result);
    }
    
    /**
     * 发表游记进入审核流程
     */
    function ajax_send_guide(){
        $result = array();
        $result['status'] = 0;
        $guide_id = intval($_REQUEST['guide_id']);
        global_run();
        //模拟会员信息
        if(empty($GLOBALS['user'])){
            $result['status'] = 2;
        }else{
            $uid = $GLOBALS['user']['id'];
            if($this->syn_guide_temp($guide_id,$uid)){
                $GLOBALS['db']->autoExecute(DB_PREFIX."tour_guide_temp",array("is_public"=>1),"UPDATE","id=".$guide_id);
                if($GLOBALS['db']->affected_rows()){
                    $result['status'] =1;
                }
            }
        }
        ajax_return($result);
    }
    /**
     * 保存二次编辑审核后的游记
     */
    function ajax_again_guide(){
        $result = array();
        $result['status'] = 0;
        global_run();
        //模拟会员信息
        if(empty($GLOBALS['user'])){
            $result['status'] = 2;
        }else{
            $guide_id = intval($_REQUEST['guide_id']);
            $uid = $GLOBALS['user']['id'];
            if($this->syn_guide($guide_id,$uid)){
                $result['status'] =1;
            }
        }
        
        ajax_return($result);
    }
    function waterfull_init_data(){
        global_run();
        $load_index = intval($_REQUEST['load_index']);
        $order_by_type = strim($_REQUEST['order_by_type']);
        $area_name = strim($_REQUEST['area_name']);
        $tour_days = strim($_REQUEST['tour_days']);
        $insert_param = strim($_REQUEST['insert_param']);
        
        //搜索条件
        $param = array();
        $condition = '';
        $where[] = " is_public = 2 ";
        $keyword = strim($_REQUEST['keyword']);
        if($keyword){
            $where[] = " (title like '%".$keyword."%') OR (match(area_match) against('".str_to_unicode_string($keyword)."'))";
        }
        if($order_by_type){
            $param['order_by_type'] = $order_by_type;
            $where[] = $param['order_by_type'].' = 1';
        }
        if($area_name){
            $param['area_name'] = $area_name;
            $where[] = " (title like '%".$param['area_name']."%') OR (match(area_match) against('".str_to_unicode_string($param['area_name'])."' IN BOOLEAN MODE)) ";
        }
        if($tour_days){
            $param['tour_days'] = $tour_days;
            $where[] = " tour_days =".$param['tour_days'];
        }

        if($insert_param){
            $insert_param = $insert_param;
            $ins_p_arr = explode("|",$insert_param);
            foreach($ins_p_arr as $k=>$v){
                $f_ins_p = explode("-",$insert_param);
                $where[] = " ".$f_ins_p[0]." = ".$f_ins_p[1];
            }
        }
        
        $GLOBALS['tmpl']->assign("param",$param);
        //print_r($param);exit;
        $condition = " WHERE ".implode(" AND ",$where);
        
        //limit
        $page = $_GET['p']>1?$_GET['p']:1;

        $GUIDE_PAGE_LOAD_GOUNT = APP_CONF("GUIDE_PAGE_LOAD_GOUNT");
        $GUIDE_PAGE_ITEM_COUNT = APP_CONF("GUIDE_PAGE_ITEM_COUNT");
        $waterfull_count =  $GUIDE_PAGE_LOAD_GOUNT*$GUIDE_PAGE_ITEM_COUNT;
        if($load_index>1){
            $limit_start = ($load_index-1)*$GUIDE_PAGE_ITEM_COUNT;
        }else{
            $limit_start = 0;
        }
        if($page>1){
            $limit_start = ($page-1)*$waterfull_count+$limit_start;
        }
        
        $limit = " limit ".$limit_start.",".intval($GUIDE_PAGE_ITEM_COUNT);
        
        $page_site = $GUIDE_PAGE_LOAD_GOUNT*$GUIDE_PAGE_ITEM_COUNT;

        $total_count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."tour_guide ".$condition);

        $pager = buildPage("guide#waterfull_init_data",$param,$total_count,$page,$page_site,1);

        $GLOBALS['tmpl']->assign("pager",$pager);

        $result['pager'] = $GLOBALS['tmpl']->fetch("inc/pages.html");
        //排序
        $orderby = ' ORDER BY id DESC ';
        $sql = "SELECT * FROM ".DB_PREFIX."tour_guide ".$condition.$orderby.$limit;
        $guide_list = $GLOBALS['db']->getAll($sql);
        //获取用户编号集合
        foreach($guide_list as $k=>$v){
            $userids[] = $v['user_id'];
        }
        $userids = array_unique($userids);

        require_once APP_ROOT_PATH."system/libs/user.php";
        $user_avatars = User::get_user_avatar($userids);

        //转换页面格式数组
        foreach($guide_list as $k=>$v){
            $temp = array();
            $temp['id'] = $v['id'];
            $temp['user_id'] = $v['user_id'];
            $temp['nickname'] = $v['nickname'];
            $temp['comment_count'] = $v['comment_count'];
            $temp['browse_count'] = $v['browse_count'];
            $temp['recommend_count'] = $v['recommend_count'];
            
            $images = unserialize($v['image_list']);
            $temp['image'] = $images[0]['image'];
            
            $temp['avatar'] =$user_avatars[$v['user_id']]['avatar'] ;
            
            $route = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."tour_guide_route WHERE guide_id = ".$v['id']." order by route_id asc");
            $temp['title'] = $v['title'];
            $temp['content'] = $route['content'];
            $list[] = $temp;
        }
        
        $result['total_count'] = $total_count;
        $GLOBALS['tmpl']->assign("list",$list);
        $result['html'] = $GLOBALS['tmpl']->fetch("guide/guide_item.html");
        $result['status'] = 1;
        ajax_return($result);
    }

    function view_waterfull_init_data(){
        global_run();
        
        //搜索条件
        $param = array();
        $condition = '';
        $where[] = " is_public = 2 ";

        $GUIDE_PAGE_ITEM_COUNT = intval(app_conf("GUIDE_PAGE_ITEM_COUNT"))*intval(app_conf("GUIDE_PAGE_LOAD_GOUNT"));

        if(strim($_REQUEST['insert_param'])){
            $insert_param = strim($_REQUEST['insert_param']);
            $ins_p_arr = explode("|",$insert_param);
            foreach($ins_p_arr as $k=>$v){
                $f_ins_p = explode("-",$insert_param);
                if($f_ins_p[0] == 'tags'){
                    $search_keyword = str_to_unicode_string_depart($f_ins_p[1]);
                    $where[] = " MATCH(area_match)AGAINST('".$search_keyword."') ";
                }else{
                    $where[] = " ".$f_ins_p[0]." = ".$f_ins_p[1];
                }
                
            }
        }
        
        $GLOBALS['tmpl']->assign("param",$param);
        $condition = " WHERE ".implode(" AND ",$where);
        
        //limit
        $page = $_GET['p']>1?$_GET['p']:1;

        if($page>1){
            $limit_start = ($page-1)*$GUIDE_PAGE_ITEM_COUNT;
        }else{
            $limit_start = 0;
        }
        
        $limit = " limit ".$limit_start.",".$GUIDE_PAGE_ITEM_COUNT;
        
        $page_site = $GUIDE_PAGE_ITEM_COUNT;

        $total_count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."tour_guide ".$condition);

        $pager = buildPage("guide#view_waterfull_init_data",$param,$total_count,$page,$page_site,1);

        $GLOBALS['tmpl']->assign("pager",$pager);

        $result['pager'] = $GLOBALS['tmpl']->fetch("inc/pages.html");
        //排序
        $orderby = ' ORDER BY id DESC ';
        $sql = "SELECT * FROM ".DB_PREFIX."tour_guide ".$condition.$orderby.$limit;
        $guide_list = $GLOBALS['db']->getAll($sql);
        //获取用户编号集合
        foreach($guide_list as $k=>$v){
            $userids[] = $v['user_id'];
        }
        $userids = array_unique($userids);

        require_once APP_ROOT_PATH."system/libs/user.php";
        $user_avatars = User::get_user_avatar($userids);

        //转换页面格式数组
        foreach($guide_list as $k=>$v){
            $temp = array();
            $temp['id'] = $v['id'];
            $temp['user_id'] = $v['user_id'];
            $temp['nickname'] = $v['nickname'];
            $temp['comment_count'] = $v['comment_count'];
            $temp['browse_count'] = $v['browse_count'];
            $temp['recommend_count'] = $v['recommend_count'];
            
            $images = unserialize($v['image_list']);
            $temp['image'] = $images[0]['image'];
            
            $temp['avatar'] =$user_avatars[$v['user_id']]['avatar'] ;
            
            $route = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."tour_guide_route WHERE guide_id = ".$v['id']." order by route_id asc");
            $temp['title'] = $v['title'];
            $temp['content'] = $route['content'];
            $list[] = $temp;
        }

        $GLOBALS['tmpl']->assign("list",$list);

        $result['html'] = $GLOBALS['tmpl']->fetch("waterfall/view_guide_item.html");
        $result['status'] = 1;
        ajax_return($result);
    }
    
    function ajax_save_tour_type(){
        $is_again = intval($_REQUEST['is_again']);
        $table_name = "tour_guide_temp";
        if($is_again){
            $table_name = "tour_guide";
        }
        $result = array();
        $result['status'] = 0;
        $guide_id = intval($_REQUEST['guide_id']);
        $tour_range = intval($_REQUEST['tour_range']);
        $tour_type = intval($_REQUEST['tour_type']);
        global_run();
        if(empty($GLOBALS['user'])){
            $result['status'] = 2;
        }else{
            $uid = $GLOBALS['user']['id'];
            if($GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX.$table_name." WHERE id=".$guide_id." AND user_id=".$uid)){
                $GLOBALS['db']->autoExecute(DB_PREFIX.$table_name,array("tour_range"=>$tour_range,"tour_type"=>$tour_type),"UPDATE","id=".$guide_id);
        
                if($GLOBALS['db']->affected_rows()){
                    $result['status'] = 1;
                }
            }
        }
        
        
        ajax_return($result);
    }
    
    
    function ajax_del_guide(){
        es_session::start();
        es_session::close();
        $guide_id = intval($_REQUEST['id']);
        $check_type = strim($_REQUEST['type']);
        $result = array();
        $result['jump'] = url("guide#index");
        if(empty($guide_id) || empty($check_type)){
            $result['status'] =3;//请求地址参数错误
        }
        $is_del_auth = 0; //是否有删除权限
        //只有管理员和用户本身可以查看
        if($check_type == 'user'){
            global_run();
            if(empty($GLOBALS['user'])) //验证是否登录
            {
               $result['status'] = 2;//用户未登录
            }else{
                //用户自己只能删除未审核的数据
                if(
                        ($GLOBALS['db']->getOne("SELECT COUNT(id) FROM ".DB_PREFIX."tour_guide_temp WHERE user_id = ".$GLOBALS['user']['id']." AND id=".$guide_id))||
                        ($GLOBALS['db']->getOne("SELECT COUNT(id) FROM ".DB_PREFIX."tour_guide WHERE user_id = ".$GLOBALS['user']['id']." AND id=".$guide_id)))
                    $is_del_auth = 1;
            }		
        }elseif($check_type=="admin"){
            $admin = es_session::get(md5(app_conf("AUTH_KEY")));
            if($admin['adm_id']>0){
                $is_del_auth = 1;
            }else{
                $result['status'] = 2;//用户不是管理员
            }
        }
        if($is_del_auth){
            require_once APP_ROOT_PATH.'system/libs/guide.php';
            if(Guide::del_guide($guide_id))
            {

                    $result['status'] = 1;
            }
            else
            {
                    $result['status'] = 0;
            }
        }
        return ajax_return($result);
    }
    /*************************************************************************
     *                          通用函数                                     *
     *************************************************************************/
    
    
    /**
     * 同步编辑的数据到 临时表
     * @param type $guide_id
     * @return boolean
     */
    function syn_guide_temp($guide_id,$uid){
        if($GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX."tour_guide_temp WHERE id=".$guide_id." AND user_id = ".$uid)){
            
            //查询每天的编号
            $route_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."tour_guide_route WHERE guide_id = ".$guide_id);

            //游记每日信息
            foreach($route_list as $k=>$v){
                $route_ids[] = $v['route_id'];
            }
            if($route_ids){
                //景点信息
                $spot_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."tour_guide_spot WHERE route_id in( ".  implode(",", $route_ids).")");

                foreach($spot_list as $k=>$v){
                    $spot_ids[] = $v['spot_id'];
                    $spot_area_names[] = $v['area_name'];
                }
                //景点
                $spot_area_names = implode(",", $spot_area_names);
                
                
            }
            
            if($spot_ids){
                //图片信息
                $gallery_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."tour_guide_gallery WHERE spot_id in( ".  implode(",", $spot_ids).")");
            }

            
            $guide_data  = array();
            
            $guide_data['area_match'] = $spot_area_names?str_to_unicode_string_depart($spot_area_names):'';
            $guide_data['area_match_row'] = $spot_area_names;
            $guide_data['tour_days'] = count($route_list);
            $guide_data['image_list'] = $gallery_list?serialize($gallery_list):'';
            $guide_data['image_count'] = count($gallery_list);
            $guide_data['is_public'] = 0;
            $guide_data['update_time'] = NOW_TIME;
            //同步到草稿表
            $GLOBALS['db']->autoExecute(DB_PREFIX."tour_guide_temp",$guide_data,"UPDATE","id=".$guide_id);
            if($GLOBALS['db']->error()==''){
                return true;
            }
        }else{
                return false;
        }
        
    }
    
    /**
     * 同步已审核的游记编辑的数据到 游记主表
     * @param type $guide_id
     * @return boolean
     */
    function syn_guide($guide_id,$uid){
        if($GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX."tour_guide WHERE id=".$guide_id." AND is_public=2 AND user_id =".$uid)){
            
            //查询每天的编号
            $route_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."tour_guide_route WHERE guide_id = ".$guide_id);

            //游记每日信息
            foreach($route_list as $k=>$v){
                $route_ids[] = $v['route_id'];
            }
            if($route_ids){
                //景点信息
                $spot_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."tour_guide_spot WHERE route_id in( ".  implode(",", $route_ids).")");

                foreach($spot_list as $k=>$v){
                    $spot_ids[] = $v['spot_id'];
                    $spot_area_names[] = $v['area_name'];
                }
                //景点
                $spot_area_names = implode(",", $spot_area_names);
            }
            
            if($spot_ids){
                //图片信息
                $gallery_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."tour_guide_gallery WHERE spot_id in( ".  implode(",", $spot_ids).")");
            }
            
            $guide_data  = array();
            
            $guide_data['area_match'] = $spot_area_names?str_to_unicode_string_depart($spot_area_names):'';
            $guide_data['area_match_row'] = $spot_area_names;
            $guide_data['tour_days'] = count($route_list);
            $guide_data['image_list'] = $gallery_list?serialize($gallery_list):'';
            $guide_data['image_count'] = count($gallery_list);
            $guide_data['update_time'] = NOW_TIME;
            //同步到草稿表
            $GLOBALS['db']->autoExecute(DB_PREFIX."tour_guide",$guide_data,"UPDATE","id=".$guide_id);
            if($GLOBALS['db']->error()==''){
                return true;
            }
        }else{
                return false;
        }
        
    }
    
    
}
