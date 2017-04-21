<?php
class cj2Module extends BaseModule {
    
    
    public function index() {
        
        global_run();       
        init_app_page();
        if(isset($_GET['keyword'])){$this->search();exit();}
        $tuan_cate_id = intval($_GET['cid']);
        $tuan_area = strim($_GET['area']);
        $tuan_place = strim($_GET['place']);
        $url_param = array();
        if($tuan_cate_id > 0)$url_param['cid'] = $tuan_cate_id;
        if($tuan_area != "")$url_param['area'] = $tuan_area;
        if($tuan_place != "") $url_param['place'] = $tuan_place;
        
        require APP_ROOT_PATH."system/libs/tuan.php";       
        $filter_nav_data = load_auto_cache("tuan_list",$url_param);     
        
        //print_r($filter_nav_data);    
        
        $filter_area_match= "";
        if($tuan_area=="inall"||$tuan_area=="outall"){
            //大区                
            $area_list = load_auto_cache("tour_area_list");
            foreach($area_list as $k=>$v)
            {
                if($v['type']==1){
                    $filter_in_area_match .= $v['py'].",";
                }elseif($v['type']==2){
                    $filter_out_area_match .= $v['py'].",";
                }
            }
            if($tuan_area=="inall"){
                $filter_area_match = substr($filter_in_area_match,0,-1);
            }elseif($tuan_area=="outall"){
                $filter_area_match = substr($filter_out_area_match,0,-1);
            }
        }
        
        $conidtion  = build_deal_filter_condition($url_param,$filter_area_match);
        
        
        

        //获取当前页面的团购列表
        $p = intval($_GET['p']);        
        $page_size= 9;
        //统计团购个数
        $rs_count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."tuan where 1=1 ".$conidtion);
        $total_count=$GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."tuan where is_effect=1 and (begin_time<".NOW_TIME." or (is_pre=1 and begin_time>".NOW_TIME.") or begin_time=0) and (end_time>".NOW_TIME." or end_time=0) and (match(city_match) against('".format_fulltext_key($GLOBALS['city']['py'])."' IN BOOLEAN MODE))");
        
        if($rs_count > 0){
            require APP_ROOT_PATH.'web/Lib/right_page.php';
            
            $page = new Page($rs_count,$page_size);   //初始化分页对象         
            $pages  =  $page->show();
            $right_page = new RightPage($rs_count,$page_size);
            $right_pages  =  $right_page->show(); 
            //print_r($pages);exit;         
            //排序
            $sort_url_param = $url_param;
            $sort_url_param['s']=1;
            $sort['s1'] = url("tuan",$sort_url_param);
            $sort_url_param['s']=2;
            $sort['s2'] = url("tuan",$sort_url_param);
            $sort_url_param['s']=3;
            $sort['s3'] = url("tuan",$sort_url_param);
            $sort_url_param['s']=4;
            $sort['s4'] = url("tuan",$sort_url_param);
            $sort_url_param['s']=5;
            $sort['s5'] = url("tuan",$sort_url_param);
            $sort_url_param['s']=6;
            $sort['s6'] = url("tuan",$sort_url_param);
            if($p<=0)$p = 1;
            $order="sale_total desc";
            $current=array();
            $current['s6']="on";
            if($_GET['s']==1) {$order="discount asc";$current['s1']="on";$current['s6']="";}
            if($_GET['s']==2) {$order="sale_total desc";$current['s2']="on";$current['s6']="";}
            if($_GET['s']==3) {$order="create_time desc";$current['s3']="on";$current['s6']="";}
            $price_up_on=3;
            if($_GET['s']==4) {$order="sale_price asc";$price_up_on=2;$current['s6']="";}
            if($_GET['s']==5) {$order="sale_price desc";$price_up_on=1;$current['s6']="";}  
            if($_GET['s']==6) {$order="sale_total desc";$current['s6']="on";}
            $limit = (($p-1)*$page_size).",".$page_size;
            $sql="SELECT id,type,name,origin_price,sale_price,image,brief,sale_total,end_time,begin_time FROM ".DB_PREFIX."tuan where 1=1 ".$conidtion." order by ".$order." LIMIT $limit";
            
            $list = $GLOBALS['db']->getAll($sql);
            
            foreach($list as $k=>$v){
                $list[$k]['remain_time'] = intval(($v['end_time']-NOW_TIME)/86400);
                if($list[$k]['remain_time']<0)$list[$k]['remain_time']=0;
                $list[$k]['remain_time']="剩余".$list[$k]['remain_time']."天";
                if($list[$k]['end_time']==0)$list[$k]['remain_time']="抢购进行中";
                if($list[$k]['begin_time']>NOW_TIME){$list[$k]['remain_time']="抢购即将开始";$list[$k]['sale_total']=0;}
                $list[$k]['brief']=msubstr($list[$k]['brief'],0,35,'utf-8');
                $list[$k]['sale_price']=format_price_to_display($list[$k]['sale_price']);
                $list[$k]['origin_price']=format_price_to_display($list[$k]['origin_price']);
                if($list[$k]['type']==1)$list[$k]['type']='bg_1';
                if($list[$k]['type']==2)$list[$k]['type']='bg_2';
                if($list[$k]['type']==3)$list[$k]['type']='bg_3';
                $list[$k]['url']=url("tuan#detail",array("did"=>$v['id']));             
            }           
                    
         }else{
            $is_empty=1;
         }
         



         $arraytuan =  $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."tuan_cate WHERE id=".$tuan_cate_id );
         //print_r($arraytuan);


        $GLOBALS['tmpl']->assign('is_empty',$is_empty); 
        $GLOBALS['tmpl']->assign('tuan_index',1);
        $GLOBALS['tmpl']->assign('price_up_on',$price_up_on);
        $GLOBALS['tmpl']->assign('current',$current);
        $GLOBALS['tmpl']->assign('sort',$sort);
        $GLOBALS['tmpl']->assign("site_name","团购首页 - ".app_conf("SITE_NAME"));
        $GLOBALS['tmpl']->assign('list',$list);
        $GLOBALS['tmpl']->assign('page',$pages);
        $GLOBALS['tmpl']->assign('right_page',$right_pages);
        $GLOBALS['tmpl']->assign('total_count',$total_count);
        $GLOBALS['tmpl']->assign('filter_nav_data',$filter_nav_data);   

        $GLOBALS['tmpl']->assign('arraytuan',$arraytuan);
        $GLOBALS['tmpl']->display("cj2.html"); 
    }
    
    
    
    
 
 
   
}
?>