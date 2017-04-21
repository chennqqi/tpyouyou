<?php
//焦点资讯
class hot_news_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
		$news_list = $GLOBALS['fcache']->get($key);
		if($news_list === false)
		{
			$news_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."news where is_hot = 1 order by create_time desc limit 10");
			
			foreach($news_list as $k=>$v)
			{
				$news_list[$k]['url'] = url("news#show",array("id"=>$v['id']));
			}
			
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$news_list);
		}
		return $news_list;
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