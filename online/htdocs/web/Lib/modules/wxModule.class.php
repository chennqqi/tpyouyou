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
			'token'=>'62vLSgm6gjE3zMEKNQ3J', //填写你设定的key
			'appid'=>'wx3d25a1f51ac18d48', //填写高级调用功能的app id, 请在微信开发模式后台查询
			'appsecret'=>'1577fa485c19c1b4eb78ad3143a257f6' //填写高级调用功能的密钥
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
    $weObj = $this->wx;
		$weObj->valid();//明文或兼容模式可以在接口验证通过后注释此句，但加密模式一定不能注释，否则会验证失败
		//设置菜单
		$newmenu =  array(
				"button"=>
					array(
            array('type'=>'view','name'=>'微官网','url'=>'http://uuclub.kuaizhan.com')
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
		$login = false;
		switch($event['event']) {
			case Wechat::EVENT_SUBSCRIBE:
			    $login = true;
          $sub = "您好，感谢你关注颢栊假期。\n
我们会在每周五为您推送优质的出境旅游路线，为您的旅游出行提供多样化的选择。/::)\n
查看更多旅游线路请登录我们的官网：www.uu-club.com\n
或者手机端输入：\nuuclub.kuaizhan.com查看。";
					$weObj->text($sub)->reply();
					break;
			case Wechat::EVENT_SCAN:
			    $login = true;
			    $weObj->text("正在为您登录悠游旅游网,请稍等")->reply();
					break;
			default:
          break;
   	}
   	if ($login) {
   		$result = $this->login($openid, $userinfo, $sceneId);
   		return $result;
   	}
  }

  public function loginqr()
  {
    $weObj = $this->wx;
    $scene_id = strim($_GET['scene_id']);
    $show = strim($_GET['show']);
    // if (intval($scene_id) < intval(NOW_TIME)) {
    // 	ajax_return(array('status'=> 0,'err'=> 'no past time'));
    // 	exit;
    // }
    $qr = $weObj->getQRCode($scene_id, 0, 1800);
    $pic = $weObj->getQRUrl($qr['ticket']);
    // $pic1 = preg_replace('/([^:])[\/\\\\]{2,}/','$1/',$pic);
    if ($show == "1") {
      echo "<img src='".$pic."'></img>";
      exit;
    }
    ajax_return(array('pic'=>$pic, 'status'=>1, 'sceneid'=>$scene_id, 'qr'=>$qr));
    exit;
  }
  /**
   * 微信登录page
   * 返回 result(user_id)
   */
  public function qr() 
  {
		init_app_page();
		$GLOBALS['tmpl']->assign("site_name","微信登录 - ".app_conf("SITE_NAME"));
		$GLOBALS['tmpl']->assign("site_keyword","微信登录,".app_conf("SITE_KEYWORD"));
		$GLOBALS['tmpl']->assign("site_description","微信登录,".app_conf("SITE_DESCRIPTION"));
  	
  	$GLOBALS['tmpl']->display("wx_qr.html");
  }

  /**
   * 将用户扫码或者关注后登录网站,如果是微信第一次登录， 会创建相应的信息
   * @param string $openid
   * @param array $userinfo
   * 返回 result(user_id)
   */
  public function login($openid, $userinfo, $sceneid)
  {
		global_run();
		// $openid = 'oz-PQwkRBA8qkZzXLXnJsc_BM9iY';
		// $userinfo = array(
		// 	'openid'=>'oz-PQwkRBA8qkZzXLXnJsc_BM9iY',
		// 	'nickname'=>'mikng'
		// );
		if (!$openid) {
			exit;
		}
		$user_wx = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_wx where (openid  = '".$openid."') limit 1");
		$result = array();
		if (!$user_wx) {
			$user_wx = $this->saveinfo($userinfo);
		}
		$user_id = $user_wx['user_id'];
		if (!$user_id) {			
			$login_data = User::login_auto_user($openid, 'wx');
		}
		if (!$login_data) {
			$result['status'] = 0;
			$result['message'] = 'fail';
		} elseif ($login_data && $login_data['user']) {
      $user_id = $login_data['user']->id;
      $result['status'] = 0;
      $result['message'] = 'success';
      $result['user_id'] = $user_id;
		}
		$update = $this->updateLogin($openid, $sceneid);
		return $update;
  }

  /**
   * 将用户wx信息保存在user_wx中
   * @param array $userinfo
   * 返回 result(user)
   */
  public function saveinfo($userinfo)
  {
  	$openid = $userinfo['openid'];
  	$user_wx = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_wx where (openid  = '".$openid."') limit 1");
  	if (!$user_wx) {
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
	  	$user_wx = $GLOBALS['db']->autoExecute(DB_PREFIX.'user_wx',$userdata,'INSERT','','SILENT');
	  	$user_wx['user_id'] = $user_wx['id'];
  	}
  	return $user_wx;
  }

  /**
   * 将用户wx信息保存在user_wx中
   * @param array $userinfo
   * 返回 result(user)
   */
  public function updateLogin($openid, $sceneid)
  {
  	$sceneid = intval($sceneid);
    $uniq = $GLOBALS['db']->getOne("select login_time from ".DB_PREFIX."user_wx where (login_time  = ".$sceneid.") limit 1");
    if ($uniq) {
    	return false;
    }
    $update = array('login_time'=>$sceneid);
    $GLOBALS['db']->autoExecute(DB_PREFIX.'user_wx',$update,'UPDATE','openid='."'".$openid."'",'SILENT');
    // var_dump($GLOBALS['db']->affected_rows());
    if ($GLOBALS['db']->affected_rows() == 0) {
    	return false;
    }
  	return true;
  }

  /**
   * 通过ajax长轮询返回userid
   * @param array $userinfo
   * 返回 result(user)
   */
  public function longajax()
  {
  	$time = $_POST['time'];
	  $sceneid = $_POST['sceneid'];
	  // header('Access-Control-Allow-Origin:*');
  	if (empty($_POST['time']) || empty($sceneid)) {
  		echo 'fail'.$time."asd".$sceneid;
  	  exit();
  	}
	  set_time_limit(0);// 无限请求超时时间
	  usleep($_POST['time']);// 等待时间
	  $result = array();

    $user_wx = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_wx where (login_time  = ".$sceneid.") limit 1");
    if($user_wx && $user_wx['user_id']){
    	$result['status'] = 1;
    	$result['msg'] = 'success';
    	$result['user'] = $user_wx;
    	$result['user_id'] = $user_wx['user_id'];
      echo json_encode($result);
      exit();
    } elseif ($user_wx && !$user_wx['user_id']) {
  		$result['status'] = 2;
  		$result['msg'] = 'no user id';
  		$result['user'] = $user_wx;
  	  echo json_encode($result);
  	  exit();
    } else {
  		$result['status'] = 0;
  		$result['msg'] = 'no user wx';
  		$result['count'] = $count;
  	  echo json_encode($result);
      sleep(3);
      exit;
    }
  }

  /**
   * 通过user_id实现微信登录
   * 返回 result(user)
   */
  public function userlogin()
  {
  	$user_id = strim($_POST['user_id']);
  	$user_wx = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_wx where (user_id  = ".$user_id.") limit 1");
  	es_session::start();
  	es_session::set("wx_name", $user_wx['nickname']);
  	es_session::set("wx_img", $user_wx['headimgurl']);
  	es_session::set("login_type","wx");
  	es_session::close();
		$result = User::loginByUserId($user_id);
	  ajax_return(array("status"=>1,"info"=>"恭喜您！登录成功","jump"=>get_gopreview(),"result"=>$result));
  }

}
?>