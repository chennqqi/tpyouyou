<?php
// +----------------------------------------------------------------------
// | Author: 张子锐(2467254599@qq.com)
// +----------------------------------------------------------------------

class wxModule extends BaseModule
{
	public function index()
	{
		set_gopreview();
		global_run();
		init_app_page();		
		$GLOBALS['tmpl']->display("user_index.html");
	}
	
	public function main()
	{		
		global_run();
   	include APP_ROOT_PATH.'system/libs/wx.php';
		$options = array(
				'token'=>'test', //填写你设定的key
				'appid'=>'wxd62a2af068f0693a', //填写高级调用功能的app id, 请在微信开发模式后台查询
				'appsecret'=>'c40eb5ab81add3be6502f6b7f7d1d5dd' //填写高级调用功能的密钥
		);
		$weObj = new WX($options);
		$weObj->valid();//明文或兼容模式可以在接口验证通过后注释此句，但加密模式一定不能注释，否则会验证失败
		$type = $weObj->getRev()->getRevType();
		//设置菜单
		$newmenu =  array(
				"button"=>
					array(
						array('type'=>'view','name'=>'我要搜索','url'=>'http://www.uu-club.com')
					)
			);
		$result = $weObj->createMenu($newmenu);
		switch($type) {
			case Wechat::MSGTYPE_TEXT:
					$weObj->text("hello, I'm wechat")->reply();
					exit;
					break;
			case Wechat::MSGTYPE_EVENT:
					break;
			case Wechat::MSGTYPE_IMAGE:
					break;
			default:
					$weObj->text("help info")->reply();
   	}
  }

}
?>