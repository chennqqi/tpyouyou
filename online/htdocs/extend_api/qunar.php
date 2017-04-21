<?php
	require_once "extend_api.php";
	header('Content-type: text/xml; charset=utf-8');
	$tourline = $GLOBALS['db']->getAll("SELECT a.*,b.name as city_name FROM ".DB_PREFIX."tourline as a "
	." left join ".DB_PREFIX."tour_city as b on b.id = a.city_id"
	." WHERE  a.is_effect=1 and ( (a.is_tuan=1 and a.tuan_end_time < ".NOW_TIME." and a.tuan_end_time >0) <> true) and ((a.is_tuan=1 and a.tuan_is_pre=0 and a.tuan_begin_time >".NOW_TIME.") <> true)  ORDER BY a.is_recommend DESC,a.sort DESC,a.id DESC ");
	
	$xml="<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
	$xml.="<routes>\r\n";
	
	foreach($tourline as $k=>$v)
	{
		$tourline_item=get_tourline_item($v['id']);
		$item_count=count($tourline_item);
		if( !$item_count )//没有出游时间跳出本次循环
			continue;
		$item_end_key=$item_count-1;
		$date_of_departure=$tourline_item['0']['start_time'];
		$date_of_expire=$tourline_item[$item_end_key]['start_time'];
		
		$price=round(format_price_to_display($v['price']),2);
		
		$url=url("tours#view",array("id"=>$v['id']));
		
		if($v['tour_type'] ==1)
			$function="跟团游";
		else
			$function="自由行";
		
		if($v['tour_range'] == 2)
			$type="出境游";
		else
			$type="国内游";
		
		$place_match_row=explode(',',$v['place_match_row']);
		if(!$place_match_row)
			$place_match_row[0]='';
			
		$image=get_spec_image($v['image'],481,321,1);//线路图片信息 分辨率应大于480*320 画面清晰无logo 
		
		$name_array=explode('。',$v['name']);
		
		
		$tour_desc=addslashes(convertUrl(emptyTag($v['tour_desc'])));
		$tour_desc_array=split_day($tour_desc);
		
		$xml.="<route>\r\n";
		$xml.="<title>".addslashes(convertUrl(emptyTag($v['name'])))."</title>\r\n";
		$xml.="<url>".convertUrl($url)."</url>\r\n";
		$xml.="<price>".$price."</price>\r\n";
		$xml.="<price_desc>".$v['price_explain']."</price_desc>\r\n";
		$xml.="<child_price></child_price>\r\n";
		$xml.="<price_diff></price_diff>\r\n";
		$xml.="<function>".$function."</function>\r\n";
		$xml.="<departure>".$v['city_name']."</departure>\r\n";
		$xml.="<type>".$type."</type>\r\n";
		$xml.="<subject></subject>\r\n";
		$xml.="<date_of_departure>".$date_of_departure."</date_of_departure>\r\n";
		$xml.="<date_of_expire>".$date_of_expire."</date_of_expire>\r\n";
		$xml.="<advance_day>".$v['advance_day']."</advance_day>\r\n";
		$xml.="<day_num>".$v['tour_total_day']."</day_num>\r\n";
		$xml.="<hotel_night>0</hotel_night>\r\n";
		$xml.="<to_traffic></to_traffic>\r\n";
		$xml.="<back_traffic></back_traffic>\r\n";
		$xml.="<promotion></promotion>\r\n";
		
		/*
		//途经城市
		$xml.="<cities>\r\n";
		$xml.="<city>\r\n";
		$xml.="</city>\r\n";
		$xml.="</cities>\r\n";
		
		//途经国家
		$xml.="<countries>\r\n";
		$xml.="<country>\r\n";
		$xml.="</country>\r\n";
		$xml.="</countries>\r\n";
		*/
		
		//途经景点
		$xml.="<sights>\r\n";
		$xml.="<sight>\r\n";
		foreach($place_match_row as $place)
		{
			$xml.="<sight_alias>".$place."</sight_alias>\r\n";
			$xml.="<sight_image></sight_image>\r\n";
		}
		$xml.="</sight>\r\n";
		$xml.="</sights>\r\n";
		
		//图片
		$xml.="<images>\r\n";
		$xml.="<image>".formatImageUrl($image)."</image>\r\n";
		$xml.="</images>\r\n";
		
		//线路特色,每句话一个节点,必填 
		$xml.="<features>\r\n";
		foreach($name_array as $name_item)
		{
			$xml.="<feature>".$name_item."</feature>\r\n";
		}
		$xml.="</features>\r\n";
		
		//费用包含信息
		$xml.="<fee_includes>\r\n";
		$xml.="<fee_include>收费最低起步价:".$price."元</fee_include>\r\n";
		$xml.="</fee_includes>\r\n";
		

	    //每日行程描述 每天的信息为一个子节点
		$xml.="<daily_trips>\r\n";
		$xml.="<daily_trip>\r\n";
		if($tour_desc_array)
		{	
			$day=1;
			foreach($tour_desc_array as $desc)
			{
				$xml.="<day>".$day."</day>\r\n";
				$xml.="<desc>".$desc."</desc>\r\n";
				$day++;
			}
		}
		else
		{
			$xml.="<day>1</day>\r\n";
			$xml.="<desc>".$tour_desc."</desc>\r\n";
			
		}
		
		
		$xml.="</daily_trip>\r\n";
		$xml.="</daily_trips>\r\n";
			
		//价格日历信息 每个出发日期一个子节点 
		$xml.="<route_dates>\r\n";
		foreach($tourline_item as $kk => $vv)
		{
			$xml.="<route_date>\r\n";
			$xml.="<date>".$vv['start_time']."</date>\r\n";
			$xml.="<price>".round($vv['adult_price'],2)."</price>\r\n";
			$xml.="<child_price>".round($vv['child_price'],2)."</child_price>\r\n";
			$xml.="<stock></stock>\r\n";
			$xml.="<price_desc></price_desc>\r\n";
			$xml.="</route_date>\r\n";
		}
		$xml.="</route_dates>\r\n";
		
		$xml.="</route>\r\n";
	}
	
	$xml.="</routes>\r\n";
	echo $xml;
?>