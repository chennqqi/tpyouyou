<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class huocheModule extends BaseModule
{
//	public function index()
//	{
//		$cache_id=1;
//		$GLOBALS['tmpl']->display("huoche_index.html",$cache_id);
//	}
	public function index()
	{
		global_run();
		$GLOBALS['tmpl']->caching = true;
		$GLOBALS['tmpl']->cache_lifetime = 600;  //首页缓存10分钟
		$cache_id  = md5(MODULE_NAME.ACTION_NAME);
		if (!$GLOBALS['tmpl']->is_cached('news_index.html', $cache_id))
		{
			init_app_page();
			
			//输出轮播新闻
			$loop_news = $GLOBALS['db']->getAll("select id,name,brief,image from ".DB_PREFIX."news where is_loop = 1 order by sort limit 10");
			foreach($loop_news as $k=>$v)
			{
				$loop_news[$k]['url'] = url("news#show",array("id"=>$v['id']));
			}
			$GLOBALS['tmpl']->assign("loop_news",$loop_news);
			
			//第一个推荐分类
			$rec_top = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."news_cate where is_focus = 1 order by sort desc limit 1");
			if($rec_top)
			{
				$rec_top['url'] = url("news#cat",array("id"=>$rec_top['id']));
				$rec_list = $GLOBALS['db']->getAll("select id,name,brief,image from ".DB_PREFIX."news where is_recommend = 1 and cate_id = ".$rec_top['id']." order by sort desc limit 7");
				if($rec_list)
				{
					foreach($rec_list as $k=>$v)
					{
						$rec_list[$k]['url'] = url("news#show",array("id"=>$v['id']));
					}
					$rec_top['list'] = $rec_list;
				}
			}
//                        var_dump($rec_top);die;
			$GLOBALS['tmpl']->assign("rec_top",$rec_top);
			
			//输出焦点资讯
			$hot_news = $GLOBALS['db']->getAll("select id,name,brief,image,cate_id from ".DB_PREFIX."news where is_hot = 1 order by sort desc");
			if($hot_news)
			{
				foreach($hot_news as $k=>$v)
				{
					$hot_news[$k]['url'] = url("news#show",array("id"=>$v['id']));
					$cate = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."news_cate where id = ".$v['cate_id']);
					if($cate)
					{
						$cate['url'] = url("news#cat",array("id"=>$cate['id']));
						$hot_news[$k]['cate'] = $cate;
					}
				}
			}
			$GLOBALS['tmpl']->assign("hot_news",$hot_news);
			
			//输出推荐分类
			$rec_cate =  $GLOBALS['db']->getAll("select * from ".DB_PREFIX."news_cate where is_recommend = 1 order by sort desc");
			if($rec_cate)
			{
				foreach($rec_cate as $k=>$v)
				{
					$rec_cate[$k]['url'] = url("news#cat",array("id"=>$v['id']));
					$rec_list = $GLOBALS['db']->getAll("select id,name,brief,image,create_time from ".DB_PREFIX."news where is_recommend = 1 and cate_id = ".$v['id']." order by sort desc limit 6");
					if($rec_list)
					{
						foreach($rec_list as $kk=>$vv)
						{
							$rec_list[$kk]['url'] = url("news#show",array("id"=>$vv['id']));
							$rec_list[$kk]['create_time'] = to_date($vv['create_time'],"Y-m-d");
							if($kk==0)
							$rec_cate[$k]['top'] = $rec_list[$kk];
						}
						$rec_cate[$k]['list'] = $rec_list;
					}
				}
			}
			$GLOBALS['tmpl']->assign("rec_cate",$rec_cate);
		}
//                var_dump($cache_id);
		$GLOBALS['tmpl']->display("huoche_index.html",$cache_id);
	}
        public function  gotrain(){
//         array(5) { ["ctl"]=> string(6) "huoche" ["act"]=> string(7) "gotrain" ["cityname"]=> string(6) "上海" ["citynames"]=> string(6) "北京" ["checkindate"]=> string(10) "2015-04-25" }
        //var_dump($_REQUEST);die;
            $cityname=$_REQUEST['cityname'];
            $citynames=$_REQUEST['citynames'];
            $checkindate=$_REQUEST['checkindate'];
             $GLOBALS['tmpl']->assign('cityname',$cityname);
               $GLOBALS['tmpl']->assign('citynames',$citynames);
               $GLOBALS['tmpl']->assign('checkindate',$checkindate);
               
               
       
        $GLOBALS['tmpl']->display("gotrain.html");
//            var_dump($_REQUEST);
        }
	
}
?>