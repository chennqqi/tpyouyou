<?php
//帮助缓存
class help_cache_auto_cache extends auto_cache{
	public function load($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
		$help_list = $GLOBALS['fcache']->get($key);
		if($help_list === false)
		{
			$help_cate = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."help_cate order by sort asc");
			foreach($help_cate as $k=>$v)
			{
				$help_list[$v['id']] = $v;
				$help_list[$v['id']]['url'] = url("help",array("id"=>$v['id']));
				$cate_list_res = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."help where cate_id = ".$v['id']." and is_effect = 1 and is_footer=1 order by sort asc");
				$cate_list = array();
				foreach($cate_list_res as $kk=>$vv)
				{
					$cate_list[$vv['id']] = $vv;
					$url = url("help#show",array("cid"=>$v['id'],"id"=>$vv['id']));
					if($vv['is_url']==1)
					{
						
						if($vv['url']=='')
						{
							if($vv['u_module']=="")$vv['u_module']="index";
							if($vv['u_action']=="")$vv['u_action']="index";
							$route = $vv['u_module'];
							if($vv['u_action']!='')$route.="#".$vv['u_action'];
							$str = "u:".$route."|".$vv['u_param'];
							$url =  parse_url_tag($str);
						}
						else
						{
		
							if(substr($vv['url'],0,7)!="http://")
							{
								//开始分析url
								$url = APP_ROOT."/".$v['url'];
							}
							else
							$url = $vv['url'];
						}			
						
					}
					$cate_list[$vv['id']]['url'] = $url;
				}
				$help_list[$v['id']]['list'] = $cate_list;
			}
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$help_list);
		}
		return $help_list;
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