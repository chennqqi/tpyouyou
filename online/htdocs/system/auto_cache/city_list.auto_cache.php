<?php
//配送用的城市列表缓存，按省分ID归类
class city_list_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
		$city_list = $GLOBALS['fcache']->get($key);
		if($city_list === false)
		{
			//$province_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."province order by py_first");
			$city_all = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."city order by py_first");
			$city_list = array();
			
			while(count($city_all)>0)
			{
				$city = array_shift($city_all);
				$city_list[$city['pid']][] = $city;
			}
			
			
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$city_list);
		}
		return $city_list;
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