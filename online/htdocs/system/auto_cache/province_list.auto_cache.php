<?php
//配送用的省份列表缓存
class province_list_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
		$province_list = $GLOBALS['fcache']->get($key);
		if($province_list === false)
		{
			$province_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."province order by py_first");
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$province_list);
		}
		return $province_list;
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