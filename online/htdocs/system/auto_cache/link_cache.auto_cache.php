<?php
//友情链接
class link_cache_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
		$link_list = $GLOBALS['fcache']->get($key);
		if($link_list === false)
		{
			$link_list_all = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."link where is_effect = 1 order by sort");
			
			$index_image_link = array();
			$index_text_link = array();
			$all_image_link = array();
			$all_text_link = array();
			
			foreach($link_list_all as $k=>$v)
			{
				if($v['is_image']==1)
				{
					$all_image_link[] = $v;
					if($v['is_recommend']==1)
					{
						$index_image_link[] = $v;
					}
				}
				else
				{
					$all_text_link[] = $v;
					if($v['is_recommend']==1)
					{
						$index_text_link[] = $v;
					}
				}
			}
			$link_list['index_image_link'] = $index_image_link;
			$link_list['all_image_link'] = $all_image_link;
			
			$link_list['index_text_link'] = $index_text_link;
			$link_list['all_text_link'] = $all_text_link;
			
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$link_list);
		}
		return $link_list;
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