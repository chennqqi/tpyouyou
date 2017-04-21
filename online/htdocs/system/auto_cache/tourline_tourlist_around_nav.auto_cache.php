<?php
//出境线路区域列表缓存
class tourline_tourlist_around_nav_auto_cache extends auto_cache{
	public function load($param)
	{
		$city=$GLOBALS['city'];
		$param['city']=$city['id'];
		$key = $this->build_key(__CLASS__,$param);/* city_id,type,a_py*/
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
		$return = $GLOBALS['fcache']->get($key);
		if($return === false)
		{
			$place_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_place where (match(city_match) against('".format_fulltext_key($city['py'])."' IN BOOLEAN MODE)) and tag_match_row !='' order by is_recommend DESC,py ASC");
			$tag_array=array();
			foreach($place_list as $k=>$v)
			{
				$tag_match_row=explode(',',$v['tag_match_row']);
				foreach($tag_match_row as $kk=>$vv)
				{
					$situation=$GLOBALS['db']->getRow("select count(*) as count,sum(review_total) as review_total_sum,sum(sale_total+sale_virtual_total) as sale_sum from ".DB_PREFIX."tourline where is_effect=1  and tour_range=3 and (match(tag_match) against('".str_to_unicode_string_depart($vv)."' IN BOOLEAN MODE)) and (match(around_city_match) against( '".format_fulltext_key($city['py'])."' IN BOOLEAN MODE))  ");
					$satify_avg=$GLOBALS['db']->getOne("select avg(satify) as satify_avg from ".DB_PREFIX."tourline where is_effect=1  and tour_range=3 and satify > 0 and (match(tag_match) against('".str_to_unicode_string_depart($vv)."' IN BOOLEAN MODE)) and (match(around_city_match) against( '".format_fulltext_key($city['py'])."' IN BOOLEAN MODE))  ");
					if($satify_avg <=0)
						$satify_avg=10000;
					$situation['satify_avg'] =$satify_avg;
					
					$situation['satify_avg']=round($situation['satify_avg']/100,2);
					$tag_array[$vv]['count']=$situation['count'];
					$tag_array[$vv]['situation']=$situation;
					$tag_array[$vv]['name']=$vv;
					$tag_array[$vv]['url']=url("tourlist#around",array("tag"=>$vv));
					
					$place_list[$k]['url']=url("tourlist#around",array("tag"=>$vv,"p_py"=>$v['py']));
					$place_list[$k]['count']=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tourline where is_effect=1 and tour_range=3 and (match(place_match) against('".format_fulltext_key($v['py'])."' IN BOOLEAN MODE)) and (match(tag_match) against('".str_to_unicode_string_depart($vv)."' IN BOOLEAN MODE)) and (match(around_city_match) against( '".format_fulltext_key($city['py'])."' IN BOOLEAN MODE))  ");
					$tag_array[$vv]['sub_list'][]=$place_list[$k];
				}
			}
			$return['list']=$tag_array;
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$return);
		}
			return $return;
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