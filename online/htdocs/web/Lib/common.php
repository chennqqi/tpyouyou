<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------

//web项目用到的函数库

/**
 * 格式化导航菜单
 */
function format_nav_list($nav_list)
{
		foreach($nav_list as $k=>$v)
		{
			if($v['url']!='')
			{
				if(substr($v['url'],0,7)!="http://")
				{		
					//开始分析url
					$nav_list[$k]['url'] = APP_ROOT."/".$v['url'];
				}
			}
		}
		return $nav_list;
}

/**
 * 获取导航菜单数据，从模块缓存中获取;
 * @return array
 */
function get_nav_list()
{
	return load_auto_cache("cache_nav_list");
}

/**
 * 初始化导航菜单数据状态
 * @param array $nav_list
 * @return array
 */
function init_nav_list($nav_list)
{
	$u_param = "";
	foreach($_GET as $k=>$v)
	{
		if(strtolower($k)!="ctl"&&strtolower($k)!="act")
		{
			$u_param.=$k."=".$v."&";
		}
	}
	if(substr($u_param,-1,1)=='&')
	$u_param = substr($u_param,0,-1);

	foreach($nav_list as $k=>$v)
	{			
		if($v['url']=='')
		{
				if($v['u_module']=="")$v['u_module']="index";
				if($v['u_action']=="")$v['u_action']="index";
				$route = $v['u_module'];
				if($v['u_action']!='')$route.="#".$v['u_action'];								
				$str = "u:".$route."|".$v['u_param'];					
				$nav_list[$k]['url'] =  parse_url_tag($str);		
				if(ACTION_NAME==$v['u_action']&&MODULE_NAME==$v['u_module']&&$v['u_param']==$u_param)
				{					
					$nav_list[$k]['current'] = 1;										
				}	
		}
	}	
	return $nav_list;
}


/**
 * 获取所有子集的类
 * @author HC
 *
 */
class ChildIds
{
	public function __construct($tb_name)
	{
		$this->tb_name = $tb_name;	
	}
	private $tb_name;
	private $childIds;
	private function _getChildIds($pid = '0', $pk_str='id' , $pid_str ='pid')
	{
		$childItem_arr = $GLOBALS['db']->getAll("select id from ".DB_PREFIX.$this->tb_name." where ".$pid_str."=".intval($pid));
		if($childItem_arr)
		{
			foreach($childItem_arr as $childItem)
			{
				$this->childIds[] = $childItem[$pk_str];
				$this->_getChildIds($childItem[$pk_str],$pk_str,$pid_str);
			}
		}
	}
	public function getChildIds($pid = '0', $pk_str='id' , $pid_str ='pid')
	{
		$this->childIds = array();
		$this->_getChildIds($pid,$pk_str,$pid_str);
		return $this->childIds;
	}
}

/**
 * 获取相应规格的图片地址
 * gen=0:保持比例缩放，不剪裁,如高为0，则保证宽度按比例缩放  gen=1：保证长宽，剪裁
 */
function get_spec_image($img_path,$width=0,$height=0,$gen=0,$is_preview=true)
{
	if($width==0)
		$new_path = $img_path;
	else
	{
		$img_name = substr($img_path,0,-4);
		$img_ext = substr($img_path,-3);	
		if($is_preview)
		$new_path = $img_name."_".$width."x".$height.".jpg";	
		else
		$new_path = $img_name."o_".$width."x".$height.".jpg";	
		if(!file_exists(APP_ROOT_PATH.$new_path))
		{
			require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
			$imagec = new es_imagecls();
			$thumb = $imagec->thumb(APP_ROOT_PATH.$img_path,$width,$height,$gen,true,"",$is_preview);
			
        	
        	if(app_conf("PUBLIC_DOMAIN_ROOT")!='')
        	{
        		$paths = pathinfo($new_path);
        		$path = str_replace("./","",$paths['dirname']);
        		$filename = $paths['basename'];
        		$pathwithoupublic = str_replace("public/","",$path);
        	
        		$file_array['path'] = $pathwithoupublic;
        		$file_array['file'] = SITE_DOMAIN.APP_ROOT."/".$path."/".$filename;
        		$file_array['name'] = $filename;
        		$GLOBALS['curl_param']['images'][] = $file_array;
        	}
			
		}
	}
	return $new_path;
}

/**
 * 按宽度格式化html内容中的图片
 * @param unknown_type $content
 * @param unknown_type $width
 * @param unknown_type $height
 */
function format_html_content_image($content,$width,$height=0)
{
	$res = preg_match_all("/<img.*?src=[\"|\']([^\"|\']*)[\"|\'][^>]*>/i", $content, $matches);
	if($res)
	{
		foreach($matches[0] as $k=>$match)
		{
			$old_path = $matches[1][$k];
			if(preg_match("/\.\/public\//i", $old_path))
			{			
				$new_path = get_spec_image($matches[1][$k],$width,$height,0);
				$content = str_replace($match, "<img src='".$new_path."' />", $content);	
			}	
		}
	}

	return $content;
}

/**
 * 获取指定规格大小的gif动态画片
 * 存在BUG，部份GIF动画缩放后会出现失真
 */
function get_spec_gif_anmation($url,$width,$height)
{
	require_once APP_ROOT_PATH."system/utils/gif_encoder.php";
	require_once APP_ROOT_PATH."system/utils/gif_reader.php";
	require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
	$gif = new GIFReader();
	$gif->load($url);
	$imagec = new es_imagecls();
	foreach($gif->IMGS['frames'] as $k=>$img)
	{
		$im = imagecreatefromstring($gif->getgif($k));		
		$im = $imagec->make_thumb($im,$img['FrameWidth'],$img['FrameHeight'],"gif",$width,$height,$gen=1);
		ob_start();
		imagegif($im);
		$content = ob_get_contents();
        ob_end_clean();
		$frames [ ] = $content;
   		$framed [ ] = $img['frameDelay'];
	}
		
	$gif_maker = new GIFEncoder (
	       $frames,
	       $framed,
	       0,
	       2,
	       0, 0, 0,
	       "bin"   //bin为二进制   url为地址
	  );
	return $gif_maker->GetAnimation ( );
}


/**
 * 用于解析URL的标签
 * @param string $str = u:acate#index|id=10&name=abc
 * @return string url地址
 */
function parse_url_tag($str)
{
	$key = md5("URL_TAG_".$str);
	if(isset($GLOBALS[$key]))
	{
		return $GLOBALS[$key];
	}
	
	$url = load_dynamic_cache($key);
	if($url!==false)
	{
		$GLOBALS[$key] = $url;
		return $url;
	}
	$str = substr($str,2);
	$str_array = explode("|",$str);
	$route = $str_array[0];
	$param_tmp = explode("&",$str_array[1]);
	$param = array();
	foreach($param_tmp as $item)
	{
		if($item!='')
		$item_arr = explode("=",$item);
		if(isset($item_arr[0])&&isset($item_arr[1]))
		$param[$item_arr[0]] = $item_arr[1];
	}
	$GLOBALS[$key]= url($route,$param);
	set_dynamic_cache($key,$GLOBALS[$key]);
	return $GLOBALS[$key];
}

/**
 * 编译生成css文件
 * @param array $urls
 * @return string 合并后的css url
 */
function parse_css($urls)
{
	
	$url = md5(implode(',',$urls));
	$css_url = 'public/runtime/'.APP_NAME.'/statics/'.$url.'.css';
	$url_path = APP_ROOT_PATH.$css_url;
	if(!file_exists($url_path)||IS_DEBUG)
	{
		if(!file_exists(APP_ROOT_PATH.'public/runtime/'.APP_NAME.'/statics/'))
		mkdir(APP_ROOT_PATH.'public/runtime/'.APP_NAME.'/statics/',0777);
		$tmpl_path = $GLOBALS['tmpl']->_var['TMPL'];	
	
		$css_content = '';
		$urlss = array();
		foreach($urls as $url)
		{
			$urlss[$url] = $url;
		}		
		foreach($urlss as $url)
		{
			$css_content .= @file_get_contents($url);
		}
		$css_content = preg_replace("/[\r\n]/",'',$css_content);
		$css_content = str_replace("../images/",$tmpl_path."/images/",$css_content);
		@file_put_contents($url_path, $css_content);
	}
	return SITE_DOMAIN.APP_ROOT."/".$css_url;
}

/**
 * 
 * @param $urls 载入的脚本
 * @param $encode_url 需加密的脚本
 */
function parse_script($urls,$encode_url=array())
{	
	$url = md5(implode(',',$urls));
	$js_url = 'public/runtime/'.APP_NAME.'/statics/'.$url.'.js';
	$url_path = APP_ROOT_PATH.$js_url;
	if(!file_exists($url_path)||IS_DEBUG)
	{
		if(!file_exists(APP_ROOT_PATH.'public/runtime/'.APP_NAME.'/statics/'))
		mkdir(APP_ROOT_PATH.'public/runtime/'.APP_NAME.'/statics/',0777);
	
		if(count($encode_url)>0)
		{
			require_once APP_ROOT_PATH."system/libs/javascriptpacker.php";
		}
		
		$js_content = '';
		$urlss = array();
		foreach($urls as $url)
		{
			$urlss[$url] = $url;
		}
		foreach($urlss as $url)
		{
			$append_content = @file_get_contents($url)."\r\n";
			if(@in_array($url,$encode_url))
			{
				$packer = new JavaScriptPacker($append_content);
				$append_content = $packer->pack();
			}			
			$js_content .= $append_content;
		}		
		@file_put_contents($url_path,$js_content);
	}
	return SITE_DOMAIN.APP_ROOT."/".$js_url;
}

/**
 * 生成页面兼容ie6的png透明图地址
 * @param string $img png图片
 * @return string
 */
function load_page_png($img)
{
	return load_auto_cache("page_image",array("img"=>$img));
}


/**
 * 通用的数据错误提示，可用于页面方式与ajax方式
 * @param string $msg 消息
 * @param int $ajax 0:页面方式 1:ajax方面
 * @param string $jump 跳转地址
 * @param int $stay 停留时间
 */
function showErr($msg,$ajax=0,$jump='',$stay=0)
{
	
	if($ajax==1)
	{
		$result['status'] = 0;
		$result['info'] = $msg;
		$result['jump'] = $jump;
		header("Content-Type:text/html; charset=utf-8");
        echo(json_encode($result));exit;
	}
	else if($ajax==2)
	{
		$result['status'] = 1;
		$result['info'] = $msg;
		$result['jump'] = $jump;
		$json = json_encode($result);
		header("Content-Type:text/html; charset=utf-8");
		echo $_GET['callback']."(".$json.")";exit;
	}
	else
	{		
		init_app_page();
		$GLOBALS['tmpl']->assign('page_title',lang("ERROR_PAGE_TITLE"));
		$GLOBALS['tmpl']->assign('msg',$msg);
		if($jump=='')
		{
			$jump = get_gopreview();
		}
		if(!$jump&&$jump=='')
		$jump = APP_ROOT."/";
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->assign("stay",$stay);
		$GLOBALS['tmpl']->display("error.html");
		exit;
	}
}

/**
 * 通用的数据成功提示，可用于页面方式与ajax方式
 * @param string $msg 消息
 * @param int $ajax 0:页面方式 1:ajax方面
 * @param string $jump 跳转地址
 * @param int $stay 停留时间
 */
function showSuccess($msg,$ajax=0,$jump='',$stay=0,$script="")
{
	
	if($ajax==1)
	{
		$result['status'] = 1;
		$result['info'] = $msg;
		$result['jump'] = $jump;
		$result['script'] = $script;
		header("Content-Type:text/html; charset=utf-8");
        echo(json_encode($result));exit;
	}
	else if($ajax==2)
	{
			$result['status'] = 1;
			$result['info'] = $msg;
			$result['jump'] = $jump;
			$result['script'] = $script;
			$json = json_encode($result);
			 header("Content-Type:text/html; charset=utf-8");
			echo $_GET['callback']."(".$json.")";exit;
	}
	else
	{
		init_app_page();
		$GLOBALS['tmpl']->assign('page_title',lang("SUCCESS_PAGE_TITLE"));
		$GLOBALS['tmpl']->assign('msg',$msg);
		if($jump=='')
		{
			$jump = get_gopreview();
		}
		if(!$jump&&$jump=='')
		$jump = APP_ROOT."/";
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->assign("stay",$stay);
		$GLOBALS['tmpl']->display("success.html");
		exit;
	}
}

/**
 * 获取前次停留的页面地址
 * @return string url
 */
function get_gopreview()
{
		es_session::start();
		$gopreview = es_session::get("gopreview");
		es_session::close();
		if($gopreview==get_current_url())
		{
			$gopreview = url("index");
		}
		if(empty($gopreview))
			$gopreview = url("index");
		return $gopreview;
}

/**
 * 获取当前的url地址，包含分页
 * @return string
 */
function get_current_url()
{
	$url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?");   
    $parse = parse_url($url);
    if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            $url   =  $parse['path'].'?'.http_build_query($params);
    }
    if(app_conf("URL_MODEL")==1)
    {	
    	$url = $GLOBALS['current_url'];
    	if(isset($_REQUEST['p'])&&intval($_REQUEST['p'])>0)
    	{
    		$req = $_REQUEST;
    		unset($req['ctl']);
    		unset($req['act']);
    		unset($req['p']);
    		if(count($req)>0)
    		{
    			$url.="-p-".intval($_REQUEST['p']);
    		}
    		else
    		{
    			$url.="/p-".intval($_REQUEST['p']);
    		}
    	}
    }
    return $url;
}

/**
 * 将当前页设为回跳的上一页地址
 */
function set_gopreview()
{
	es_session::start();
	$url =  get_current_url();
	es_session::set("gopreview",$url); 
	es_session::close();
}	

/**
 * 跳转回上一页
 */
function app_redirect_preview()
{
	app_redirect(get_gopreview());
}	

/**
 * 显示语言
 * lang($key,p1,p2......) 用于格式化 sprintf %s
 */
function lang($key)
{
	$args = func_get_args();//取得所有传入参数的数组
	$key = strtoupper($key);
	if(isset($GLOBALS['lang'][$key]))
	{
		if(count($args)==1)
		return $GLOBALS['lang'][$key];
		else
		{
			$result = $key;
			$cmd = '$result'." = sprintf('".$GLOBALS['lang'][$key]."'";
			for ($i=1;$i<count($args);$i++)
			{
				$cmd .= ",'".$args[$i]."'";
			}
			$cmd.=");";
			eval($cmd);
			return $result;
		}
	}
	else
	return $key;
}


/**
 * 获得查询次数以及查询时间
 *
 * @access  public
 * @return  string
 */
function run_info()
{
	
	if(!SHOW_DEBUG)return "";

    $query_time = number_format($GLOBALS['db']->queryTime,6);
    
    if($GLOBALS['begin_run_time']==''||$GLOBALS['begin_run_time']==0)
    {
    	$run_time = 0;
    }
    else
    {
    	if (PHP_VERSION >= '5.0.0')
        {
            $run_time = number_format(microtime(true) - $GLOBALS['begin_run_time'], 6);
        }
        else
        {
            list($now_usec, $now_sec)     = explode(' ', microtime());
            list($start_usec, $start_sec) = explode(' ', $GLOBALS['begin_run_time']);
            $run_time = number_format(($now_sec - $start_sec) + ($now_usec - $start_usec), 6);
        }
    }

    /* 内存占用情况 */
    if (function_exists('memory_get_usage'))
    {
    	$unit=array('B','KB','MB','GB'); 
    	$size = memory_get_usage();
		$used = @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i]; 
        $memory_usage = lang("MEMORY_USED",$used);        
    }
    else
    {
        $memory_usage = '';
    }

    /* 是否启用了 gzip */
    $enabled_gzip = (app_conf("GZIP_ON") && function_exists('ob_gzhandler'));
    $gzip_enabled = $enabled_gzip ? lang("GZIP_ON") : lang("GZIP_OFF");

    $str = lang("QUERY_INFO_STR",$GLOBALS['db']->queryCount, $query_time,$gzip_enabled,$memory_usage,$run_time);

    foreach($GLOBALS['db']->queryLog as $K=>$sql)
	{
		if($K==0)$str.="<br />SQL语句列表：";
		$str.="<br />行".($K+1).":".$sql;
	}

	return "<div style='width:940px; padding:10px; line-height:22px; border:1px solid #ccc; text-align:left; margin:30px auto; font-size:14px; color:#999; height:150px; overflow-y:auto;'>".$str."</div>";
}

/**
 * 初始化页面变量
 */
function init_app_page()
{
	
	//输出导航菜单
	$nav_list = get_nav_list();
	$nav_list= init_nav_list($nav_list);
	$GLOBALS['tmpl']->assign("nav_list",$nav_list);

	
	//输出SEO元素
	if(!$GLOBALS['city'])
		$GLOBALS['city'] = City::locate_city();
	
	$site_title = app_conf("SITE_NAME")."-".$GLOBALS['city']['name']."站";
	$site_keyword = app_conf("SITE_KEYWORD");
	$site_description = app_conf("SITE_DESCRIPTION");
	if($GLOBALS['city']['seo_title'] !='')
		$site_title .="-".$GLOBALS['city']['seo_title'];
	if($GLOBALS['city']['seo_keywords'] !='')
		$site_keyword .="-".$GLOBALS['city']['seo_keywords'];
	if($GLOBALS['city']['seo_description'] !='')
		$site_description .="-".$GLOBALS['city']['seo_description'];
		
	$GLOBALS['tmpl']->assign("site_name",app_conf("SITE_NAME"));
	$GLOBALS['tmpl']->assign("site_title",$site_title);
	$GLOBALS['tmpl']->assign("site_keyword",$site_keyword);
	$GLOBALS['tmpl']->assign("site_description",$site_description);
	
	$lang = require APP_ROOT_PATH.APP_NAME.'/Lang/'.app_conf("SITE_LANG").'/lang.php';
	//将标准包中的语言输出为js
	if(!file_exists(APP_ROOT_PATH."public/runtime/".APP_NAME."/lang.js")||IS_DEBUG)
	{			
			$str = "var LANG = {";
			foreach($lang as $k=>$lang_row)
			{
				$str .= "\"".$k."\":\"".str_replace("nbr","\\n",addslashes($lang_row))."\",";
			}
			$str = substr($str,0,-1);
			$str .="};";
			@file_put_contents(APP_ROOT_PATH."public/runtime/".APP_NAME."/lang.js",$str);
	}
	global $help_list;
	$help_list = load_auto_cache("help_cache");
	$GLOBALS['tmpl']->assign("help_list",$help_list);
	
	if(MODULE_NAME=="index"||MODULE_NAME=="link")
	{
		global $link_list;
		$link_list = load_auto_cache("link_cache");
		$GLOBALS['tmpl']->assign("link_list",$link_list);
	}
	
	if(MODULE_NAME=="tourlist"&&ACTION_NAME=="index"&&$_REQUEST['t_type']==1)$search_type = 1;
	if(MODULE_NAME=="tourlist"&&ACTION_NAME=="index"&&$_REQUEST['t_type']==2)$search_type = 2;
	if(MODULE_NAME=="tourlist"&&ACTION_NAME=="index"&&$_REQUEST['t_type']==3)$search_type = 3;
	if(MODULE_NAME=="tourlist"&&ACTION_NAME=="index"&&$_REQUEST['type']==1)$search_type = 4;
	if(MODULE_NAME=="tourlist"&&ACTION_NAME=="index"&&$_REQUEST['type']==2)$search_type = 5;
	if(MODULE_NAME=="tourlist"&&ACTION_NAME=="around")$search_type = 6;
	if(MODULE_NAME=="spot"&&ACTION_NAME=="cat")$search_type = 7;
	if(MODULE_NAME=="tuan"&&ACTION_NAME=="index")$search_type = 8;
	if(MODULE_NAME=="guide"&&ACTION_NAME=="index")$search_type = 9;
	
	if($search_type>0)$keyword = strim($_REQUEST['keyword']);
	
	$GLOBALS['tmpl']->assign("keyword",$keyword);
	$GLOBALS['tmpl']->assign("search_type",$search_type);
}

/**
 * 前台显示面的全局任务处理，如会员自动登录，购物车更新等等。
 */
function global_run()
{
	$cron_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_msg_list where is_send = 0");
	$GLOBALS['tmpl']->assign("CRON_COUNT",intval($cron_count));	
	
	$GLOBALS['user'] = User::load_user();
	if(empty($GLOBALS['user']))
		User::auto_do_login();
	if(!empty($GLOBALS['user']))
	{
		User::reload_user();
		//加载用户的消息
		$uname = str_to_unicode_string($GLOBALS['user']['user_name']);
		$msg_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."system_msg where (group_id = ".$GLOBALS['user']['group_id']." or level_id = ".$GLOBALS['user']['level_id']." or match(`username_match`) against ('".$uname."' IN BOOLEAN MODE)) and send_time <= ".NOW_TIME." and end_time > ".NOW_TIME);
		foreach($msg_list as $k=>$v)
		{
			$msg_item = array();
			User::send_message($GLOBALS['user']['id'], $v['msg_title'], $v['msg_content'],$v['id']);
		}
		
		
	}	
	
	//删除购物车内超过60分钟的商品
	//$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."ticket_cart WHERE create_time <= ".(NOW_TIME - 3600)." ");	
	
	$GLOBALS['city'] = City::locate_city();
	$city_list =  load_auto_cache("dh_city_list");
	$GLOBALS['tmpl']->assign("dh_hot_city_list",$city_list['hot_city_list']);
	$GLOBALS['tmpl']->assign("dh_city_list",$city_list['city_list']);
	
}

//获取索引列表中的第一个的拼音原形
function get_first_index_py($idx)
{
	$idx = explode(",",$idx);
	$idx = $idx[0];
	if($idx)
	{
		$idx = unformat_fulltext_key($idx);
	}
	return $idx;
}

//获取索引列表中的第一个索引
function get_first_index($idx)
{

	$idx = explode( ",",$idx);

	$idx = $idx[0];

	return $idx;
}


function format_wee_err($msg){
	return '<div class="wee_error_tip">'.$msg.'</div>';
}
  function GetIp(){
  $realip = '';
  $unknown = 'unknown';
  if (isset($_SERVER)){
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)){
      $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
      foreach($arr as $ip){
        $ip = trim($ip);
        if ($ip != 'unknown'){
          $realip = $ip;
          break;
        }
      }
    }else if(isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], $unknown)){
      $realip = $_SERVER['HTTP_CLIENT_IP'];
    }else if(isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)){
      $realip = $_SERVER['REMOTE_ADDR'];
    }else{
      $realip = $unknown;
    }
  }else{
    if(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), $unknown)){
      $realip = getenv("HTTP_X_FORWARDED_FOR");
    }else if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), $unknown)){
      $realip = getenv("HTTP_CLIENT_IP");
    }else if(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), $unknown)){
      $realip = getenv("REMOTE_ADDR");
    }else{
      $realip = $unknown;
    }
  }
  $realip = preg_match("/[\d\.]{7,15}/", $realip, $matches) ? $matches[0] : $unknown;
  return $realip;
}

 function GetIpLookup($ip = ''){
  if(empty($ip)){
    $ip = GetIp();
  }
  $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);
  if(empty($res)){ return false; }
  $jsonMatches = array();
  preg_match('#\{.+?\}#', $res, $jsonMatches);
  if(!isset($jsonMatches[0])){ return false; }
  $json = json_decode($jsonMatches[0], true);
  if(isset($json['ret']) && $json['ret'] == 1){
    $json['ip'] = $ip;
    unset($json['ret']);
  }else{
    return false;
  }
  return $json;
}

?>