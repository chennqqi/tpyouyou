<?php
//旅游城市的管理类
class City{
	
	//定位城市
	public static function locate_city()
	{
		$city_py =$GLOBALS['city_py'];
		if(empty($city_py))
		{
			$city_py = strim($_REQUEST['city_py']);
		}
		
		if($city_py)
		{
			$current_city_data = es_cookie::get("current_city");
			if($current_city_data)
				$current_city = unserialize(base64_decode($current_city_data));
			
			//强行定位
			if($current_city['py']!=$city_py)
			$current_city = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tour_city where py = '".$city_py."' and is_effect = 1");
		}
		
		if(empty($current_city))
		{
			//无城市，由cookie中获取
			$current_city_data = es_cookie::get("current_city");
			if($current_city_data)
				$current_city = unserialize(base64_decode($current_city_data));
		}
		
		if(empty($current_city))
		{
			$city_list_res =  load_auto_cache("dh_city_list");
			$city_list = $city_list_res['all_citys'];
			//自动定位
			require_once APP_ROOT_PATH."system/extend/ip.php";
			$ip =  CLIENT_IP;
			$iplocation = new iplocate();
			$address=$iplocation->getaddress($ip);
			foreach ($city_list as $city)
			{
				if(strpos($address['area1'],$city['name']))
				{
					$current_city = $city;
					break;
				}
			}
		}
		
		if(empty($current_city))
		$current_city = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tour_city where is_default = 1 and is_effect = 1");
		
		$current_city_cookie  = base64_encode(serialize($current_city));
		es_cookie::set("current_city", $current_city_cookie);
		return $current_city;
		
	}
	
	
	/**
	 * 根据IP定位城市名称
	 * @param unknown_type $ip
	 */
	public static function locate_city_name($ip)
	{
		require APP_ROOT_PATH."system/extend/ip.php";
			$iplocation = new iplocate();
		$address=$iplocation->getaddress($ip);
		return $address['area1'];
	}
	
	

}
?>