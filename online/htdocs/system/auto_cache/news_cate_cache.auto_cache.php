<?php
//文章分类
class news_cate_cache_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
		$cate_list = $GLOBALS['fcache']->get($key);
		if($cate_list === false)
		{
			$all_list = array();
			$index_list = array();
			$cate_list_res = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."news_cate order by sort");
			foreach($cate_list_res as $k=>$v)
			{
				$v['url'] = url("news#cat",array("id"=>$v['id']));
				$all_list[$v['id']] = $v;
				if($v['is_recommend'] == 1)
				{
					$index_list[$v['id']] = $v;
				}
			}
			$cate_list['all_list'] = $all_list;
			$cate_list['index_list'] = $index_list;
			
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$cate_list);
		}
		return $cate_list;
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