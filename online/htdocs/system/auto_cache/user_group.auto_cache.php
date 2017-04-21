<?php

class user_group_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
		$user_group = $GLOBALS['fcache']->get($key);
		if($user_group === false)
		{
			$user_group_res = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_group");
			foreach($user_group_res as $k=>$v)
			{
				$user_group[$v['id']] = $v;
			}
			if(empty($user_group))$user_group = array();
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");			
			$GLOBALS['fcache']->set($key,$user_group);
		}
		return $user_group;
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