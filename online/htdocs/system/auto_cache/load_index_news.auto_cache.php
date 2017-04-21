<?php
//加载首页新闻模块
class load_index_news_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
		$index_news = $GLOBALS['fcache']->get($key);
		if($index_news === false)
		{
			$index_news = $GLOBALS['db']->getAll("select `name`,`id` from ".DB_PREFIX."news_cate where is_index = 1 order by sort desc limit 2");
			foreach($index_news as $k=>$v){
				$contents=$GLOBALS['db']->getAll("select `name`,`id`,`brief`,`image` from ".DB_PREFIX."news where cate_id=".$v['id']." and is_recommend = 1 order by is_image desc,create_time desc limit 10");
				foreach($contents as $kk=>$vv){
					$contents[$kk]['name']=msubstr($vv['name'],0,18,'utf-8');
					$contents[$kk]['brief']=msubstr($vv['brief'],0,23,'utf-8');
					$contents[$kk]['url']=url("news#show",array("id"=>$vv['id']));
				}
				$index_news[$k]['contents']=$contents;
				
				
			}
			
			
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$index_news);
		}
		return $index_news;
	}
	public function rm($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
		$GLOBALS['fcache']->rm($key);
	}
	public function clear_all()
	{
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
		$GLOBALS['fcache']->clear();
	}
}
?>