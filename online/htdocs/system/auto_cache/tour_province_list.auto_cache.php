<?php
//导航的自动缓存
class tour_province_list_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
		$province_list = $GLOBALS['fcache']->get($key);
		if($province_list === false)
		{
			$tmpprovince_list = $GLOBALS['db']->getAll("select `id`,`name`,`py`,`city_match`,`city_match_row` from ".DB_PREFIX."tour_province order by py");
			foreach($tmpprovince_list as $k=>$v){
				$v['py'] = unformat_fulltext_key($v['py']);
				$province_list[$v['py']] =  $v;
				
				if($v['city_match']){
					$tmp_name_citys = explode(",",$v['city_match_row']);
					$tmp_py_citys = explode(",",$v['city_match']);
					foreach($tmp_name_citys as $kk=>$vv){
						$province_list[$v['py']]['citys'][$kk]['name'] = $vv;
						$province_list[$v['py']]['citys'][$kk]['py'] = unformat_fulltext_key($tmp_py_citys[$kk]);
					}
				}
				
				
			}	
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