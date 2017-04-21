<?php
function build_deal_filter_condition($param,$filter_area_match = ""){	
		$tuan_cid = intval($param['cid']);
		$tuan_area = strim($param['area']);
		$tuan_city = strim(format_fulltext_key($GLOBALS['city']['py']));
		$tuan_place = strim($param['place']);
		$condition = "and is_effect=1 and (begin_time<".NOW_TIME." or (is_pre=1 and begin_time>".NOW_TIME.") or begin_time=0) and (end_time>".NOW_TIME." or end_time=0) and (match(city_match) against('".$tuan_city."' IN BOOLEAN MODE))";
		
		if($tuan_cid>0)
		{				
			$condition .= " and cate_id=".$tuan_cid;
		}
				
		if($tuan_area!=""&&$tuan_area!="inall"&&$tuan_area!="outall"){
			$condition .= " and (match(area_match) against('".format_fulltext_key($tuan_area)."' IN BOOLEAN MODE))";
		    if($tuan_place !=""){
			    $condition .= " and (match(place_match) against('".format_fulltext_key($tuan_place)."' IN BOOLEAN MODE))";		
		    }		    
		}elseif($filter_area_match!=""){
			$condition .= " and (match(area_match) against('".format_fulltext_key($filter_area_match)."' IN BOOLEAN MODE))";
		}		
	
	
		return $condition;		
}


?>