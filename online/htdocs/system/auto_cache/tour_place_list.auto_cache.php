<?php
class tour_place_list_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
		$place = $GLOBALS['fcache']->get($key);
		if($place === false)
		{
			$place['list'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_place order by is_recommend DESC,py");
			foreach($place['list'] as $k=>$v){
				$v['py'] = unformat_fulltext_key($v['py']);
				if($v['area_match']){
					$tmp_name_areas = explode(",",$v['area_match_row']);
					$tmp_py_areas = explode(",",$v['area_match']);
					foreach($tmp_name_areas as $kk=>$vv){
						$place['areas'][unformat_fulltext_key($tmp_py_areas[$kk])]["name"] = $vv;
						$place['areas'][unformat_fulltext_key($tmp_py_areas[$kk])]["py"] = $tmp_py_areas[$kk];
						$place['areas'][unformat_fulltext_key($tmp_py_areas[$kk])]["place"][$v['py']] = $v;
					}
				}
				
				if($v['city_match']){
					$tmp_name_citys = explode(",",$v['city_match_row']);
					$tmp_py_citys = explode(",",$v['city_match']);
					foreach($tmp_name_citys as $kk=>$vv){
						$place['citys'][unformat_fulltext_key($tmp_py_citys[$kk])]['place'][$v['py']] = $v;
					}
				}
				
				if($v['tag_match_row']){
					$tmp_name_tags = explode(",",$v['tag_match_row']);
					foreach($tmp_name_tags as $kk=>$vv){
						$place['tags'][$vv]['place'][$v['py']] = $v;
						$place['place_tags'][$v['py']][$vv]['name'] = $vv;
					}
				}
				
				$place['pys'][$v['py']] = $v;
			}	
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$place);
		}
		return $place;
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
