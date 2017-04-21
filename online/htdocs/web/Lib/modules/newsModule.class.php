<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class newsModule extends BaseModule
{
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
		$GLOBALS['tmpl']->display("news_index.html",$cache_id);
	}
	public function cat()
	{		
		require_once APP_ROOT_PATH.APP_NAME.'/Lib/page.php';
		global_run();
		init_app_page();
		$cate_list = load_auto_cache("news_cate_cache");
		$id = intval($_REQUEST['id']); //分类ID;
		if(empty($cate_list['all_list'][$id]))
		{
			app_redirect(url("index"));
		}
		
		$cate_data = $cate_list['all_list'][$id];
		$GLOBALS['tmpl']->assign("cate_data",$cate_data);
		
		$condition = " cate_id =  ".$id." ";
		
		$news_total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."news where ".$condition);
		
		if($news_total>0)
		{		
			
			$page=intval($_REQUEST['p']);
			if($page==0)
				$page=1;
			 
			$pagesize = 20;
			$limit  = (($page - 1) *$pagesize) .",$pagesize";
			
			$page = new Page($news_total,$pagesize);   //初始化分页对象
			$p  =  $page->show();
			$GLOBALS['tmpl']->assign('pages',$p);
			
			$news_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."news where ".$condition." order by create_time desc limit ".$limit);
			
			foreach($news_list as $k=>$v)
			{
				$news_list[$k]['url'] = url("news#show",array("id"=>$v['id']));
				$news_list[$k]['create_time'] = to_date($v['create_time'],"Y-m-d");
			}
			
			$GLOBALS['tmpl']->assign("news_list",$news_list);
		}
		
		$GLOBALS['tmpl']->assign("site_name",$cate_data['name']." - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword",$cate_data['name'].",".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description",$cate_data['name'].",".app_conf("SITE_DESCRIPTION"));
				
		$hot_news = load_auto_cache("hot_news");
		$GLOBALS['tmpl']->assign("hot_news",$hot_news);
		$GLOBALS['tmpl']->display("news_cat.html");
	}
	
	
	public function show()
	{
		global_run();
		init_app_page();
		$id = intval($_REQUEST['id']); //文章ID;
		$news = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."news where id = ".$id);
		$news['content'] = format_html_content_image($news['content'],610,0);
		if(empty($news))
		{
			app_redirect(url("index"));
		}
		
		$GLOBALS['db']->query("update ".DB_PREFIX."news set count = count+1 where id = ".$id);
		$news['count'] = $news['count'] + 1;
		$cate_list = load_auto_cache("news_cate_cache");
		$cate_data = $cate_list['all_list'][$news['cate_id']];
		$GLOBALS['tmpl']->assign("cate_data",$cate_data);
		$GLOBALS['tmpl']->assign("news",$news);
		
		$GLOBALS['tmpl']->assign("site_name",$news['name']." - ".$cate_data['name']." - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword",$news['name'].",".$cate_data['name'].",".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description",$news['name'].",".$cate_data['name'].",".app_conf("SITE_DESCRIPTION"));
		
		$hot_news = load_auto_cache("hot_news");
		$GLOBALS['tmpl']->assign("hot_news",$hot_news);
		
		require_once APP_ROOT_PATH."system/libs/comment.php";
		$comment_html = Comment::init_comment(3, $id);
		
		$GLOBALS['tmpl']->assign("comment_html",$comment_html);
		$GLOBALS['tmpl']->display("news_show.html");
		
	}

	
}
?>