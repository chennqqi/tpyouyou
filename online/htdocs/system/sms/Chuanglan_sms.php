<?php
/* *
 * 类名：ChuanglanSmsApi
 * 功能：创蓝接口请求类
 * 详细：构造创蓝短信接口请求，获取远程HTTP数据
 * 版本：1.3
 * 日期：2014-07-16
 * 说明：
 * 以下代码只是为了方便客户测试而提供的样例代码，客户可以根据自己网站的需要，按照技术文档自行编写,并非一定要使用该代码。
 * 该代码仅供学习和研究创蓝接口使用，只是提供一个参考。
 */
require_once APP_ROOT_PATH."system/libs/sms.php";  //引入接口
require_once APP_ROOT_PATH."system/sms/Chuanglan/transport.php";
require_once APP_ROOT_PATH."system/sms/Chuanglan/chuanglan_config.php";

class Chuanglan_sms implements sms{
	public $sms;
	public $message = "";
  public function __construct($smsInfo = '')
  { 	    	
		if(!empty($smsInfo))
		{			
			$this->sms = $smsInfo;
		}
  }
		
	
	/**
	 * 发送短信
	 *
	 * @param string $mobile 手机号码
	 * @param string $msg 短信内容
	 * @param string $needstatus 是否需要状态报告
	 * @param string $extno   扩展码，可选
	 */
	public function sendSMS($mobile_number,$content)
	{
		global $chuanglan_config;
		if(is_array($mobile_number))
		{
			$mobile_number = implode(",",$mobile_number);
		}
		$sms = new transport();
				
				//创蓝接口参数
				$params = array (
		      'account' => $chuanglan_config['api_account'],
		      'pswd' => $chuanglan_config['api_password'],
		      'msg' => $content,
		      'mobile' => $mobile_number,
		      'needstatus' => 'false',
		      'extno' => '19723'
         );
				
				$result = $sms->request($this->sms['server_url'],$params);
				$code = $result['body'];
				
				if($code=='0')
				{
							$result['status'] = 1;
				}
				else
				{
							$result['status'] = 0;
							$result['msg'] = $this->statusStr[$code];
				}
		return $result;
	}

	public function getSmsInfo()
	{		
			return "创蓝短信平台";
	}
		
	public function check_fee()
	{
		$sms = new transport();
			

		global $chuanglan_config;
		//查询参数
		$params = array ( 
        'account' => $chuanglan_config['api_account'],
        'pswd' => $chuanglan_config['api_password'],
		);
		$result = $sms->request($chuanglan_config['api_balance_query_url'],$params);
		return $result;
	
	}
	
	//魔术获取
	public function __get($name){
		return $this->$name;
	}
	
	//魔术设置
	public function __set($name,$value){
		$this->$name=$value;
	}
}
?>