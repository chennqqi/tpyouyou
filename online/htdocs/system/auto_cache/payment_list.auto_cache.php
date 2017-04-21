<?php
//支付接口列表
class payment_list_auto_cache extends auto_cache{
	public function load($param)
	{				
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");		
		$payment_list = $GLOBALS['fcache']->get($key);
		if($payment_list === false)
		{			
			$bank_payment = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where bank=1 and is_effect = 1");
			$common_payment  = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment where bank = 0 and is_effect = 1 order by sort desc");
			
			if($bank_payment)
			{
				require_once APP_ROOT_PATH."system/payment/".$bank_payment['class_name']."_payment.php";
				$cn = $bank_payment['class_name']."_payment";
				$payment_obj = new $cn();
				$payment_list['bank_payment'] = $payment_obj->get_display_code();
			}
			
			if($common_payment)
			{
				foreach($common_payment as $k=>$v)
				{
					require_once APP_ROOT_PATH."system/payment/".$v['class_name']."_payment.php";
					$cn = $v['class_name']."_payment";
					$payment_obj = new $cn();
					$payment_list['common_payment'][$k] = $payment_obj->get_display_code();
				}
			}			
			
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$payment_list);
		}
		return $payment_list;
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