<?php
//缓存城市导航数据
class dh_city_list_auto_cache extends auto_cache{
	public function load($param)
	{
		static $city_list = null;
		if($city_list!==null)return $city_list;
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");		
		$city_list = $GLOBALS['fcache']->get($key);
		if($city_list === false)
		{			
			$hot_city_list_res = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_city where is_hot = 1 and is_effect = 1 order by py_first");
			$city_list_res = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_city where is_effect = 1 order by py_first");
			
			foreach($hot_city_list_res as $k=>$v)
			{
				if(app_conf("URL_MODEL")==0)
				$v['url'] = url("index",array("city_py"=>$v['py']));	
				else
				$v['url'] = "http://".$v['py'].".".app_conf("DOMAIN_ROOT").APP_ROOT;
				$hot_city_list[] = $v;
			}
			
			foreach($city_list_res as $k=>$v)
			{
				if($v['py_first']>="A"&&$v['py_first']<"H")$g="ABCDEFG";
				if($v['py_first']>="H"&&$v['py_first']<"P")$g="HIJKLMNO";
				if($v['py_first']>="P"&&$v['py_first']<"W")$g="PQRSTUV";
				if($v['py_first']>="W"&&$v['py_first']<="Z")$g="WXYZ";
				if(app_conf("URL_MODEL")==0)
				$v['url'] = url("index",array("city_py"=>$v['py']));
				else
				$v['url'] = "http://".$v['py'].".".app_conf("DOMAIN_ROOT").APP_ROOT;
				$city_list[$g][$v['py_first']][] = $v;
			}

			$city_list  = array("hot_city_list"=>$hot_city_list,"city_list"=>$city_list,"all_citys"=>$city_list_res);

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