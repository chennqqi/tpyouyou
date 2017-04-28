<?php
// +----------------------------------------------------------------------
// | Author: 张子锐(2467254599@qq.com)
// +----------------------------------------------------------------------

include APP_ROOT_PATH.'system/libs/wx.php';

class wxModule extends BaseModule
{
	private $wx;
	private $options;
	//网站项目构造
	public function __construct($options = '', $wx = '')
	{
		$options = array(
			'token'=>'test', //填写你设定的key
			'appid'=>'wxd62a2af068f0693a', //填写高级调用功能的app id, 请在微信开发模式后台查询
			'appsecret'=>'c40eb5ab81add3be6502f6b7f7d1d5dd' //填写高级调用功能的密钥
    );
    $this->options = $options;
    $this->wx = new WX($this->options);
	}

	public function __destruct()
	{
		unset($this);
	}

	public function index()
	{
		set_gopreview();
		global_run();
	}
	
	public function main()
	{		
		global_run();
    $weObj = $this->wx;
		$weObj->valid();//明文或兼容模式可以在接口验证通过后注释此句，但加密模式一定不能注释，否则会验证失败
		//设置菜单
		$newmenu =  array(
				"button"=>
					array(
						array('type'=>'view','name'=>'我要搜索','url'=>'http://www.uu-club.com')
					)
		);
		$result = $weObj->createMenu($newmenu);
		$type = $weObj->getRev()->getRevType();
		$openid = $weObj->getRev()->getRevFrom();
		$userinfo =$weObj->getUserInfo($openid);
		switch($type) {
			case Wechat::MSGTYPE_TEXT:
					$weObj->text("may the force with you, ".$userinfo['nickname'])->reply();
					exit;
					break;
			case Wechat::MSGTYPE_EVENT:
					break;
			case Wechat::MSGTYPE_IMAGE:
					break;
			default:
					$weObj->text("help info msg")->reply();
   	}
		$event = $weObj->getRev()->getRevEvent();
		$sceneId = $weObj->getRev()->getRevSceneId();
		$sceneId = $sceneId ? $sceneId : '';
		switch($event['event']) {
			case Wechat::EVENT_SUBSCRIBE:
					$weObj->text("欢迎关注!".$sceneId)->reply();
					exit;
					break;
			case Wechat::EVENT_SCAN:
			    $weObj->text("正在登录,请稍等".$sceneId)->reply();
			    $this->login($openid, $userinfo);
			    exit;
					break;
			default:
					$weObj->text("1".$event['event']."2")->reply();
   	}
  }

  public function loginqr()
  {
		global_run();
    $weObj = $this->wx;
    $qr = $weObj->getQRCode(NOW_TIME,$type=0,$expire=1800);
    $pic = $weObj->getQRUrl($qr['ticket']);
    $pic1 = preg_replace('/([^:])[\/\\\\]{2,}/','$1/',$pic);
    // ajax_return(array('qr'=>$pic1));
    echo "<img src='".$pic."'></img>";
  }

  public function login($openid, $userinfo)
  {
		global_run();
		$user_wx = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_wx where (openid  = '".$openid."') limit 1");
		if (!$user_wx) {
			$this->regist($openid, $userinfo);
		}
  }

  /**
   * 将用户wx信息保存在user_wx中， 并创建相关联的user
   * @param string $openid
   * @param array $userinfo
   * 返回 result(status,info)
   * status: 0 此openid已经被占用 1 创建成功
   */
  public function regist($openid, $userinfo)
  {
  	global_run();
  	$user_wx = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_wx where (openid  = '".$openid."') limit 1");
  	$result = array();
  	if ($user_wx) {
  		$result['status'] = 0;
  		$result['info'] = '此微信用户已经注册';
  	}
  	$userdata = array();
  	$userdata['openid'] = $userinfo['openid'];
  	$userdata['nickname'] = $userinfo['nickname'];
  	$userdata['sex'] = $userinfo['sex'];
  	$userdata['language'] = $userinfo['language'];
  	$userdata['city'] = $userinfo['city'];
  	$userdata['province'] = $userinfo['province'];
  	$userdata['country'] = $userinfo['country'];
  	$userdata['headimgurl'] = $userinfo['headimgurl'];
  	$userdata['subscribe_time'] = $userinfo['subscribe_time'];
  	$user_wx_new = $GLOBALS['db']->autoExecute(DB_PREFIX.'user_wx',$userdata,'INSERT','','SILENT');
  	User::login_auto_user($openid, 'wx')
  }

}
?>