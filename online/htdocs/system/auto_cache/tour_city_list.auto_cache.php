<?php
//导航的自动缓存
class tour_city_list_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
		$list = $GLOBALS['fcache']->get($key);
		if($list === false)
		{
			$city_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_city where is_effect = 1 order by py_first");
			$city_id_list=array();
			$city_py_list=array();
			foreach($city_list as $k=>$v)
			{
				$city_id_list[$v['id']]=$v;
				$city_py_list[$v['py']]=$v;
			}
			$list['city_id_list']=$city_id_list;
			$list['city_py_list']=$city_py_list;
			
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$list);
		}
		return $list;
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