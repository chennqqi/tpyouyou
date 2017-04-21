<?php
//团购首页数据库缓存
class tuan_list_auto_cache extends auto_cache{
	
	public function load($param){
		$key = $this->build_key(__CLASS__,$param);
		
		$end_time = NOW_TIME- 3600*24;	
		$result = unserialize($GLOBALS['db']->getOne("select cache_data from ".DB_PREFIX."auto_cache where cache_type = '".__CLASS__."' and cache_key = '".$key."' and cache_time > ".$end_time));
		
		if($result===false||IS_DEBUG)
		{
			$tuan_cid = intval($param['cid']); //分类
			$tuan_area = strim($param['area']);	//大区
			$tuan_place = strim($param['place']);//小区
			$url_param = $param;				
			
			//团购分类	
			$temptuan_cate = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."tuan_cate ORDER BY `sort` DESC ,id ASC");
			$tuan_cate= array();
			
			$tem_url_param=$url_param;
			unset($tem_url_param['cid']);
			$tuan_cate[0]['name'] = "所有分类";
			$tuan_cate[0]['cid'] = "0";
			if($tuan_cid==0) $tuan_cate[0]['current'] = 1;
			unset($tem_url_param['area']);
			unset($tem_url_param['place']);
			$durl = url("tuan",$tem_url_param);
			$tuan_cate[0]['url']=$durl;		
			$condtion = build_deal_filter_condition($tem_url_param);			
			$tuan_cate[0]['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tuan where 1=1 ".$condtion);
			
			foreach($temptuan_cate as $k=>$v)
			{
				$tuan_cate[$k+1] = $v;
				$tuan_cate[$k+1]['name'] = $v['name'];
				if($tuan_cid==$v['id'])	$tuan_cate[$k+1]['current'] = 1;					
				$tem_url_param=$url_param;
				$tem_url_param['cid']=$v['id'];
				unset($tem_url_param['area']);
				unset($tem_url_param['place']);
				$durl = url("tuan",$tem_url_param);				
				$tuan_cate[$k+1]['url']=$durl;		
				$condtion = build_deal_filter_condition($tem_url_param);				
				$tuan_cate[$k+1]['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tuan where 1=1 ".$condtion);								
			}			
			
			$result['tuan_cate'] = $tuan_cate;			
			
			//大区				
			$area_list = load_auto_cache("tour_area_list");
			//print_r($area_list);
			$area_list_in = array();
			$area_list_out = array();

			$i=1;$o=1;
			$filter_in_area_match= '';
			$filter_out_area_match= '';
			foreach($area_list as $k=>$v)
			{
				$tmp_url = $url_param;				
				if($tuan_cid>0){
					$tem_url_param['cid']=$tuan_cid;					
				}else{
					unset($tem_url_param['cid']);
				} 
				$tem_url_param['area'] = $v['py'];
				if($v['type'] == 1)
					$filter_in_area_match .= $v['py'].",";
				else{
					$filter_out_area_match .= $v['py'].",";
				}				
					
				//国内大区
				if($v['type']==1){
					$area_list_in[$i]['name'] = $v['name'];					
					unset($tem_url_param['place']);					
					if($tuan_area==$v['py']) $area_list_in[$i]['current'] = 1;					
					$durl = url("tuan",$tem_url_param);					
					$area_list_in[$i]['url']=$durl;		
					$condtion = build_deal_filter_condition($tem_url_param);			
					$area_list_in[$i]['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tuan where 1=1 ".$condtion);											
					$i++;
				}
				//国外大区
				if($v['type']==2){
					$area_list_out[$o]['name'] = $v['name'];					
					unset($tem_url_param['place']);					
					if($tuan_area==$v['py']) $area_list_out[$o]['current'] = 1;
					$durl = url("tuan",$tem_url_param);					
					$area_list_out[$o]['url']=$durl;		
					$condtion = build_deal_filter_condition($tem_url_param);			
					$area_list_out[$o]['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tuan where 1=1 ".$condtion);											
					$o ++;
				}					
				
			}
			if($filter_in_area_match!=""){
				$filter_in_area_match = substr($filter_in_area_match,0,-1);
			}
			if($filter_out_area_match!=""){
				$filter_out_area_match = substr($filter_out_area_match,0,-1);
			}
			
			//国内全部
			$tem_url_param=$url_param;
			$area_list_in[0]['name'] = "国内全部";
			if($tuan_area=="inall"||$tuan_area=="") $area_list_in[0]['current'] = 1;
			unset($tem_url_param['place']);
			$tem_url_param['area']="inall";
			$durl = url("tuan",$tem_url_param);
			$area_list_in[0]['url']=$durl;		
			$condtion = build_deal_filter_condition($tem_url_param,$filter_in_area_match);			
			$area_list_in[0]['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tuan where 1=1 ".$condtion);
			
			//国外全部
			$area_list_out[0]['name'] = "出境全部";
			if($tuan_area=="outall"||$tuan_area=="") $area_list_out[0]['current'] = 1;
			unset($tem_url_param['place']);
			$tem_url_param['area']="outall";
			$durl = url("tuan",$tem_url_param);
			$area_list_out[0]['url']=$durl;		
			$condtion = build_deal_filter_condition($tem_url_param,$filter_out_area_match);			
			$area_list_out[0]['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tuan where 1=1 ".$condtion);
			
			ksort($area_list_in);
			ksort($area_list_out);
			
			$result['area_list_in'] = $area_list_in;
			$result['area_list_out'] = $area_list_out;			
			//小区域
			if($tuan_area!=""&&$tuan_area!="inall"&&$tuan_area!="outall"){
				$place_list = load_auto_cache("tour_place_list");
				$place_list = $place_list['areas'][$tuan_area]['place'];								
				if($place_list!=""){
					$place = array();				
					$tem_url_param=$url_param;
					$place[0]['name'] = "全部区域";				
					if($tuan_place=="") $place[0]['current'] = 1;
					unset($tem_url_param['place']);
					$durl = url("tuan",$tem_url_param);				
					$place[0]['url']=$durl;		
					$condtion = build_deal_filter_condition($tem_url_param);							
					$place[0]['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tuan where 1=1 ".$condtion);			 
					$j=1;	 
					foreach($place_list as $k=>$v){					
						$tem_url_param=$url_param;				
						$place[$j]['name'] = $v['name'];
						$tem_url_param['place'] = $place[$j]['py'] = $v['py'];					
						if($tuan_place==$v['py']) $place[$j]['current'] = 1;
						$durl = url("tuan",$tem_url_param);					
						$place[$j]['url']=$durl;		
						$condtion = build_deal_filter_condition($tem_url_param);								
						$place[$j]['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."tuan where 1=1 ".$condtion);
						$j++;
					}
				}


				$result['place'] = $place;
			}		
			

		
			$db_data['cache_key'] = $key;
			$db_data['cache_type'] = __CLASS__;
			$db_data['cache_time'] = NOW_TIME;
			$db_data['cache_data'] = serialize($result);
			$db_data['tuan_cate_id'] = $tuan_cid;
			$db_data['tuan_area'] = $tuan_area;
			$db_data['tuan_place'] = $tuan_place;
			$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."auto_cache where cache_key='".$key."'");
			$GLOBALS['db']->autoExecute(DB_PREFIX."auto_cache",$db_data);		
		}
		
		
		return $result;		
	}	
	
	
	public function rm($param)
	{
		$condition = "";
		if(intval($param['cate_id']) > 0){
			$condition.=" AND tuan_cate_id = ".intval($param['cate_id']);
		}
		if(strim($param['tuan_area']) !=""){
			$condition.=" AND tuan_area='".strim($param['tuan_area'])."'";
		}
		if(strim($param['tuan_province']) !=""){
			$condition.=" AND tuan_province='".strim($param['tuan_province'])."'";
		}
		if(strim($param['tuan_city']) !=""){
			$condition.=" AND tuan_city='".strim($param['tuan_city'])."'";
		}
		if(strim($param['tuan_place']) !=""){
			$condition.=" AND tuan_place='".strim($param['tuan_place'])."'";
		}
		
		$GLOBALS['db']->query("delete from ".DB_PREFIX."auto_cache where cache_type = '".__CLASS__."' ".$condition);
	}
	public function clear_all()
	{
		$GLOBALS['db']->query("delete from ".DB_PREFIX."auto_cache where cache_type = '".__CLASS__."'");		
	}
	
}
?>
