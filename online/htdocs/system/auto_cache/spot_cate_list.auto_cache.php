<?php
//导航的自动缓存
class spot_cate_list_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
		$spot_cate = $GLOBALS['fcache']->get($key);
		if($spot_cate === false)
		{
			$tempspot = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."spot_cate order by is_hot DESC,is_recommend DESC, sort desc");
			foreach($tempspot as $k=>$v){
				$spot_cate['list'][$v['id']] = $v;
				if($v['is_recommend']==1)
					$spot_cate['recommend'][] =  $v;
				if($v['is_index']==1)
					$spot_cate['index'][] =  $v;
			}
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$spot_cate);
		}
		return $spot_cate;
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