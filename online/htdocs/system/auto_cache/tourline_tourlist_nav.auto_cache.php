<?php
//出境线路区域列表缓存
class tourline_tourlist_nav_auto_cache extends auto_cache{
	public function load($param)
	{
		$cur_city=$GLOBALS['city'];
		$param['city_id']=$cur_city['id'];
		$key = $this->build_key(__CLASS__,$param);/* city_id,type,a_py*/
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
		$return = $GLOBALS['fcache']->get($key);
		if($return === false)
		{
			$where =" 1=1";
			if($param['type'] >0)
			{
				$where .=" and type=".$param['type']."";
				$t_where =" and tour_range =".$param['type']."";
			}
			$area_list = $GLOBALS['db']->getAll("select `id`,`name`,`py`,`type` from ".DB_PREFIX."tour_area where ".$where."");
			$area_py=array();
			foreach($area_list as $k=>$v)
			{
				$area_py[]=$v['py'];
			}
			$place_list = $GLOBALS['db']->getAll("select `id`,`name`,`py`,`area_match` from ".DB_PREFIX."tour_place where (match(area_match) against('".format_fulltext_key(implode(',',$area_py))."' IN BOOLEAN MODE)) order by is_recommend DESC,py ASC");
			$area_place=array();
			foreach($place_list as $k=>$v)
			{	
				$palce_area_py=array();
				$palce_area_py=explode(",",unformat_fulltext_key($v["area_match"]));
				foreach($palce_area_py as $kk=>$vv)
				{
					$area_place[$vv][$v['py']]=$place_list[$k];
				}
			}
			
			
			foreach($area_list as $k=>$v)
			{
				$area_list[$k]['url']=url("tourlist#index",array("type"=>$param['type'],"a_py"=>$v['py']));
				$situation=$GLOBALS['db']->getRow("select count(*) as count,sum(review_total) as review_total_sum,sum(sale_total+sale_virtual_total) as sale_sum,avg(satify) as satify_avg from ".DB_PREFIX."tourline where is_effect=1 {$t_where} and (match(area_match) against('".format_fulltext_key($v['py'])."' IN BOOLEAN MODE)) and ( city_id=".$cur_city['id']." or (match(city_match) against( '".format_fulltext_key($cur_city['py'])."' IN BOOLEAN MODE)) ) ");
				$satify_avg=$GLOBALS['db']->getOne("select avg(satify) as satify_avg from ".DB_PREFIX."tourline where is_effect=1 and satify > 0 {$t_where} and (match(area_match) against('".format_fulltext_key($v['py'])."' IN BOOLEAN MODE)) and ( city_id=".$cur_city['id']." or (match(city_match) against( '".format_fulltext_key($cur_city['py'])."' IN BOOLEAN MODE)) ) ");
				if($satify_avg <=0)
					$satify_avg=10000;
				$situation['satify_avg'] =$satify_avg;
				
				$area_list[$k]['count']=$situation['count'];
				$situation['satify_avg']=round($situation['satify_avg']/100,2);
				$area_list[$k]['situation']= $situation;
				$area_list[$k]['sub_list'] =$area_place[$v['py']];
				foreach($area_list[$k]['sub_list'] as $kk=>$vv)
				{
					$area_list[$k]['sub_list'][$kk]['url']=url("tourlist#index",array("type"=>$param['type'],"a_py"=>$v['py'],"p_py"=>$vv[py]));
					$area_list[$k]['sub_list'][$kk]['count']=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tourline where is_effect=1 {$t_where} and (match(area_match) against('".format_fulltext_key($v['py'])."' IN BOOLEAN MODE)) and (match(place_match) against('".format_fulltext_key($vv['py'])."' IN BOOLEAN MODE)) and ( city_id=".$cur_city['id']." or (match(city_match) against( '".format_fulltext_key($cur_city['py'])."' IN BOOLEAN MODE)) ) ");
				}
				$list[$v['py']]=$area_list[$k];
			}

			$return['list']=$list;
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