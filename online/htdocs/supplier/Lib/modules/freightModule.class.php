<?php

class freightModule extends AuthModule{

    function index() {
    	
    	$supplier_id  = $this->supplier_id;
    	$province_list = load_auto_cache("province_list");
    	$GLOBALS['tmpl']->assign("province_list",$province_list);
    	
    	$default_freight = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_freight where supplier_id = ".$supplier_id." and is_default = 1");
    	if($default_freight)
    	$default_freight['price'] = format_price_to_display($default_freight['price']);
    	$GLOBALS['tmpl']->assign("default_freight",$default_freight);
    	
    	
    	$freight_list_res = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_freight where supplier_id = ".$supplier_id." and is_default = 0");
    	if($freight_list_res)
    	{
    		while(count($freight_list_res)>0)
    		{
    			$f = array_pop($freight_list_res);
    			$f['price'] = format_price_to_display($f['price']);
    			$freight_list[] = $f;
    		}

    		$GLOBALS['tmpl']->assign("freight_list",$freight_list);
    		$city_list = load_auto_cache("city_list");
    		$GLOBALS['tmpl']->assign("city_list",$city_list);
    	}
		
    	
    	$GLOBALS['tmpl']->assign("formaction",admin_url("freight#update",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("loadcityurl",admin_url("freight#load_city"));
		$GLOBALS['tmpl']->display("core/freight/index.html");
    }
    
    
    public function load_city()
    {
    	$city_all = load_auto_cache("city_list");
    	$province_id = intval($_REQUEST['province_id']);
    	$city_list = $city_all[$province_id];
    	if($city_list)
    	{
    		$result['status'] = true;
    		$result['list'] = $city_list;
    	}
    	else
    	{
    		$result['status'] = false;
    	}
    	ajax_return($result);
    }
    
   public function update() 
   {
   		$ajax = intval($_REQUEST['ajax']);
   		$id = $this->supplier_id;
   		$GLOBALS['db']->query("delete from ".DB_PREFIX."supplier_freight where supplier_id = ".$id);
   		
   		$is_default = intval($_REQUEST['is_default']);
   		$default_price = format_price_to_db(floatval($_REQUEST['default_price']));
   		if($is_default==1)
   		{
   			$default_data=array();
   			$default_data['supplier_id'] = $id;
   			$default_data['price'] = $default_price;
   			$default_data['is_default'] = 1;
   			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_freight",$default_data,"INSERT","","SILENT");
   		}
   		
   		$province_ids = $_REQUEST['province_id'];
   		$city_ids = $_REQUEST['city_id'];
   		$prices = $_REQUEST['price'];
   		
   		$freight_data = array();
   		foreach($province_ids as $k=>$province_id)
   		{
   			$province_id = intval($province_id);
   			$city_id = intval($city_ids[$k]);
   			$price =  format_price_to_db(floatval($prices[$k]));
   			$freight_data[$province_id."_".$city_ids[$k]] = array(
   				"province_id"=> $province_id,
   				"city_id"	=> 	$city_id,
   				"price"	=>	$price   					
   			);
   		}
   		
   		
   		
   		foreach($freight_data as $v)
   		{
   			$data=array();
   			$data['supplier_id'] = $id;
   			$data['price'] = $v['price'];
   			$data['province_id'] = $v['province_id'];
   			$data['city_id'] = $v['city_id'];
   			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_freight",$data,"INSERT","","SILENT");
   		}
   		
		showSuccess(lang("UPDATE_SUCCESS"),1);

	}
}
?>