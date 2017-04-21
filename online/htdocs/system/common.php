<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------

//前后台加载的函数库

/**
 * 获取当前的GMT时间
 */
function get_gmtime()
{
	return (time() - date('Z'));
}

/**
 * 格式化时间
 * @param int $utc_time  UTC时间
 * @param string $format  时间格式
 */
function to_date($utc_time, $format = 'Y-m-d H:i:s') {
	if (empty ( $utc_time ) || $utc_time == 0) {
		return '';
	}
	$timezone = intval(app_conf('TIME_ZONE'));
	$time = $utc_time + $timezone * 3600; 
	return date ($format, $time );
}

/**
 * 将格式化时间转为时间戳
 * @param unknown_type $str 格式化时间字符串
 * @param unknown_type $format 格式
 */
function to_timespan($str, $format = 'Y-m-d H:i:s')
{
	$timezone = intval(app_conf('TIME_ZONE'));
	//$timezone = 8; 
	$time = intval(strtotime($str));
	if($time!=0)
	$time = $time - $timezone * 3600;
    return $time;
}


/**
 * 获取客户端IP
 */
function get_client_ip() {
	if (getenv ( "HTTP_CLIENT_IP" ) && strcasecmp ( getenv ( "HTTP_CLIENT_IP" ), "unknown" ))
		$ip = getenv ( "HTTP_CLIENT_IP" );
	else if (getenv ( "HTTP_X_FORWARDED_FOR" ) && strcasecmp ( getenv ( "HTTP_X_FORWARDED_FOR" ), "unknown" ))
		$ip = getenv ( "HTTP_X_FORWARDED_FOR" );
	else if (getenv ( "REMOTE_ADDR" ) && strcasecmp ( getenv ( "REMOTE_ADDR" ), "unknown" ))
		$ip = getenv ( "REMOTE_ADDR" );
	else if (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], "unknown" ))
		$ip = $_SERVER ['REMOTE_ADDR'];
	else
		$ip = "unknown";
	return ($ip);
}

/**
 * 过滤SQL注入;
 */
function filter_injection(&$request)
{
	$pattern = "/(select[\s])|(insert[\s])|(update[\s])|(delete[\s])|(from[\s])|(where[\s])/i";
	foreach($request as $k=>$v)
	{
				if(preg_match($pattern,$k,$match))
				{
						die("SQL Injection denied!");
				}
		
				if(is_array($v))
				{					
					filter_injection($v);
				}
				else
				{					
					
					if(preg_match($pattern,$v,$match))
					{
						die("SQL Injection denied!");
					}					
				}
	}
	
}


/**
 * 如开启魔法标签，因自动会为请求加上转义需还原请求的转义字符
 * @param  $request 引用传参的请求
 */
function filter_request(&$request)
{
		if(MAGIC_QUOTES_GPC)
		{
			foreach($request as $k=>$v)
			{
				if(is_array($v))
				{
					filter_request($v);
				}
				else
				{
					$request[$k] = stripslashes(trim($v));
				}
			}
		}		
}

/**
 * 为请求加上转义
 * @param  $request 引用传参的请求
 */
function adddeepslashes(&$request)
{

			foreach($request as $k=>$v)
			{
				if(is_array($v))
				{
					adddeepslashes($v);
				}
				else
				{
					$request[$k] = addslashes(trim($v));
				}
			}		
}

/**
 * 去除请求的转义
 * @param  $request 引用传参的请求
 */
function stripdeepslashes(&$request)
{

	if(is_array($request))
	{
			foreach($request as $k=>$v)
			{
				if(is_array($v))
				{
					stripdeepslashes($v);
				}
				else
				{
					$request[$k] = stripslashes(trim($v));
				}
			}
	}
	else
	$request = stripslashes($request);
}

/**
 * request的gbk->utf8转码
 * @param  $req 引用传参的请求
 */
function convert_req(&$req)
{
	foreach($req as $k=>$v)
	{
		if(is_array($v))
		{
			convert_req($req[$k]);
		}
		else
		{
			if(!is_u8($v))
			{
				$req[$k] = iconv("gbk","utf-8",$v);
			}
		}
	}
}

/**
 * 判断是否为utf8字符串
 * @param string $string
 * @return bool 
 */
function is_u8($string)
{
	return preg_match('%^(?:
		 [\x09\x0A\x0D\x20-\x7E]            # ASCII
	   | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
	   |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
	   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
	   |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
	   |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
	   | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
	   |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
   )*$%xs', $string);
}

/**
 * 递归删除目录以及子目录
 * @param string $path
 * @return boolean
 */
function clear_dir_file($path)
{
   if ( $dir = opendir( $path ) )
   {
            while ( $file = readdir( $dir ) )
            {
                $check = is_dir( $path. $file );
                if ( !$check )
                {
                    @unlink( $path . $file );                       
                }
                else 
                {
                 	if($file!='.'&&$file!='..')
                 	{
                 		clear_dir_file($path.$file."/");              			       		
                 	} 
                 }           
            }
            closedir( $dir );
            rmdir($path);
            return true;
   }
}


/**
 * 检测是否已安装系统
 */
function check_install()
{
	if(!file_exists(APP_ROOT_PATH."public/install.lock"))
	{
	    clear_cache();
		header('Location:'.APP_ROOT.'/install');
		exit;
	}
}


/**
 * 发邮件的示例
 */
function send_demo_mail()
{
	if(app_conf("MAIL_ON")==1)
	{		
			//$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
			$msg = "测试邮件";
			$msg_data['dest'] = "demo@demo.com";
			$msg_data['send_type'] = 1;
			$msg_data['title'] = "测试邮件";
			$msg_data['content'] = addslashes($msg);
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = get_gmtime();
			$msg_data['user_id'] = 0;
			$msg_data['is_html'] = 1;
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入

	}
}

/**
 * 发短信的示例
 */
function send_demo_sms()
{
	if(app_conf("SMS_ON")==1)
	{
		
			//$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
			$msg = "测试短信";
			$msg_data['dest'] = "13333333333";
			$msg_data['send_type'] = 0;
			$msg_data['content'] = addslashes($msg);
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = get_gmtime();
			$msg_data['user_id'] = 0;
			$msg_data['is_html'] = 0;
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入

	}
}



/**
 * utf8 字符串截取
 * @param string $str
 * @param int $start
 * @param int $length
 * @param string $charset 默认为utf8
 * @param bool $suffix 是否加上省略
 * @return string 
 */
function msubstr($str, $start=0, $length=15, $charset="utf-8", $suffix=true)
{
	if(function_exists("mb_substr"))
    {
        $slice =  mb_substr($str, $start, $length, $charset);
        if($suffix&$slice!=$str) return $slice."…";
    	return $slice;
    }
    elseif(function_exists('iconv_substr')) {
        return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix&&$slice!=$str) return $slice."…";
    return $slice;
}


/**
 * 兼容的iconv
 */
if(!function_exists("iconv"))
{	
	function iconv($in_charset,$out_charset,$str)
	{
		require 'libs/iconv.php';
		$chinese = new Chinese();
		return $chinese->Convert($in_charset,$out_charset,$str);
	}
}

/**
 * 兼容的json
 */
if(!function_exists("json_encode"))
{	
	function json_encode($data)
	{
		require_once 'libs/json.php';
		$JSON = new JSON();
		return $JSON->encode($data);
	}
}

/**
 * 兼容的json
 */
if(!function_exists("json_decode"))
{	
	function json_decode($data)
	{
		require_once 'libs/json.php';
		$JSON = new JSON();
		return $JSON->decode($data,1);
	}
}

/**
 * 邮件格式验证的函数
 */
function check_email($email)
{
	if(!preg_match("/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/",$email))
	{
		return false;
	}
	else
	return true;
}

/**
 * 验证手机号码
 */
function check_mobile($mobile)
{
	if(!empty($mobile) && !preg_match("/^\d{6,}$/",$mobile))
	{
		return false;
	}
	else
	return true;
}

/**
 * 页面跳转
 */
function app_redirect($url,$time=0,$msg='')
{
    //多行URL地址支持
    $url = str_replace(array("\n", "\r"), '', $url);    
    if (!headers_sent()) {
        // redirect
        if(0===$time&&$msg=="") {
        	if(substr($url,0,1)=="/")
        	{        		
        		header("Location:".SITE_DOMAIN.$url);
        	}
        	else
        	{
        		header("Location:".$url);
        	}
            
        }else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    }else {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if($time!=0)
            $str   .=   $msg;
        exit($str);
    }
}



/**
 * 验证访问IP的有效性
 * @param ip地址 $ip_str
 * @param 访问页面 $module
 * @param 时间间隔 $time_span
 * @param 数据ID $id
 */
function check_ipop_limit($ip_str,$module,$time_span=0,$id=0)
{
		$op = es_session::get($module."_".$id."_ip");
    	if(empty($op))
    	{
    		$check['ip']	=	 get_client_ip();
    		$check['time']	=	get_gmtime();
    		es_session::set($module."_".$id."_ip",$check);    		
    		return true;  //不存在session时验证通过
    	}
    	else 
    	{   
    		$check['ip']	=	 get_client_ip();
    		$check['time']	=	get_gmtime();    
    		$origin	=	es_session::get($module."_".$id."_ip");
    		
    		if($check['ip']==$origin['ip'])
    		{
    			if($check['time'] - $origin['time'] < $time_span)
    			{
    				return false;
    			}
    			else 
    			{
    				es_session::set($module."_".$id."_ip",$check);
    				return true;  //不存在session时验证通过    				
    			}
    		}
    		else 
    		{
    			es_session::set($module."_".$id."_ip",$check);
    			return true;  //不存在session时验证通过
    		}
    	}
    }

function gzip_out($content)
{
	header("Content-type: text/html; charset=utf-8");
    header("Cache-control: private");  //支持页面回跳
	$gzip = app_conf("GZIP_ON");
	if( intval($gzip)==1 && !defined("JSONP") )
	{
		if(!headers_sent()&&extension_loaded("zlib")&&preg_match("/gzip/i",$_SERVER["HTTP_ACCEPT_ENCODING"]))
		{
			$content = gzencode($content,9);	
			header("Content-Encoding: gzip");
			header("Content-Length: ".strlen($content));
			echo $content;
		}
		else
		echo $content;
	}else{
		echo $content;
	}
	
}


/**
	 * 保存图片
	 * @param array $upd_file  即上传的$_FILES数组
	 * @param array $key $_FILES 中的键名 为空则保存 $_FILES 中的所有图片
	 * @param string $dir 保存到的目录
	 * @param array $whs
	 	可生成多个缩略图
		数组 参数1 为宽度，
			 参数2为高度，
			 参数3为处理方式:0(缩放,默认)，1(剪裁)，
			 参数4为是否水印 默认为 0(不生成水印)
	 	array(
			'thumb1'=>array(300,300,0,0),
			'thumb2'=>array(100,100,0,0),
			'origin'=>array(0,0,0,0),  宽与高为0为直接上传
			...
		)，
	 * @param array $is_water 原图是否水印
	 * @return array
	 	array(
			'key'=>array(
				'name'=>图片名称，
				'url'=>原图web路径，
				'path'=>原图物理路径，
				有略图时
				'thumb'=>array(
					'thumb1'=>array('url'=>web路径,'path'=>物理路径),
					'thumb2'=>array('url'=>web路径,'path'=>物理路径),
					...
				)
			)
			....
		)
	 */
//$img = save_image_upload($_FILES,'avatar','temp',array('avatar'=>array(300,300,1,1)),1);
function save_image_upload($upd_file, $key='',$dir='temp', $whs=array(),$is_water=false,$need_return = false)
{
		require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
		$image = new es_imagecls();
		$image->max_size = intval(app_conf("MAX_IMAGE_SIZE"));
		
		$list = array();

		if(empty($key))
		{
			foreach($upd_file as $fkey=>$file)
			{
				$list[$fkey] = false;
				$image->init($file,$dir);
				if($image->save())
				{
					$list[$fkey] = array();
					$list[$fkey]['url'] = $image->file['target'];
					$list[$fkey]['path'] = $image->file['local_target'];
					$list[$fkey]['name'] = $image->file['prefix'];
				}
				else
				{
					if($image->error_code==-105)
					{
						if($need_return)
						{
							return array('error'=>1,'message'=>'上传的图片太大');
						}
						else
						echo "上传的图片太大";
					}
					elseif($image->error_code==-104||$image->error_code==-103||$image->error_code==-102||$image->error_code==-101)
					{
						if($need_return)
						{
							return array('error'=>1,'message'=>'非法图像');
						}
						else
						echo "非法图像";
					}
					exit;
				}
			}
		}
		else
		{
			$list[$key] = false;
			$image->init($upd_file[$key],$dir);
			if($image->save())
			{
				$list[$key] = array();
				$list[$key]['url'] = $image->file['target'];
				$list[$key]['path'] = $image->file['local_target'];
				$list[$key]['name'] = $image->file['prefix'];
			}
			else
				{
					if($image->error_code==-105)
					{
						if($need_return)
						{
							return array('error'=>1,'message'=>'上传的图片太大');
						}
						else
						echo "上传的图片太大";
					}
					elseif($image->error_code==-104||$image->error_code==-103||$image->error_code==-102||$image->error_code==-101)
					{
						if($need_return)
						{
							return array('error'=>1,'message'=>'非法图像');
						}
						else
						echo "非法图像";
					}
					exit;
				}
		}

		$water_image = APP_ROOT_PATH.app_conf("WATER_MARK");
		$alpha = app_conf("WATER_ALPHA");
		$place = app_conf("WATER_POSITION");
		
		foreach($list as $lkey=>$item)
		{
				//循环生成规格图
				foreach($whs as $tkey=>$wh)
				{
					$list[$lkey]['thumb'][$tkey]['url'] = false;
					$list[$lkey]['thumb'][$tkey]['path'] = false;
					if($wh[0] > 0 || $wh[1] > 0)  //有宽高度
					{
						$thumb_type = isset($wh[2]) ? intval($wh[2]) : 0;  //剪裁还是缩放， 0缩放 1剪裁
						if($thumb = $image->thumb($item['path'],$wh[0],$wh[1],$thumb_type))
						{
							$list[$lkey]['thumb'][$tkey]['url'] = $thumb['url'];
							$list[$lkey]['thumb'][$tkey]['path'] = $thumb['path'];
							if(isset($wh[3]) && intval($wh[3]) > 0)//需要水印
							{
								$paths = pathinfo($list[$lkey]['thumb'][$tkey]['path']);
								$path = $paths['dirname'];
				        		$path = $path."/origin/";
				        		if (!is_dir($path)) { 
						             @mkdir($path);
						             @chmod($path, 0777);
					   			}   	    
				        		$filename = $paths['basename'];
								@file_put_contents($path.$filename,@file_get_contents($list[$lkey]['thumb'][$tkey]['path']));      
								$image->water($list[$lkey]['thumb'][$tkey]['path'],$water_image,$alpha, $place);
							}
						}
					}
				}
			if($is_water)
			{
				$paths = pathinfo($item['path']);
				$path = $paths['dirname'];
        		$path = $path."/origin/";
        		if (!is_dir($path)) { 
		             @mkdir($path);
		             @chmod($path, 0777);
	   			}   	    
        		$filename = $paths['basename'];
				@file_put_contents($path.$filename,@file_get_contents($item['path']));        		
				$image->water($item['path'],$water_image,$alpha, $place);
			}
		}			
		return $list;
}



/**
 * utf8字符转Unicode字符
 * @param string $char 要转换的单字符
 * @return void
 */
function utf8_to_unicode($char)
{
	switch(strlen($char))
	{
		case 1:
			return ord($char);
		case 2:
			$n = (ord($char[0]) & 0x3f) << 6;
			$n += ord($char[1]) & 0x3f;
			return $n;
		case 3:
			$n = (ord($char[0]) & 0x1f) << 12;
			$n += (ord($char[1]) & 0x3f) << 6;
			$n += ord($char[2]) & 0x3f;
			return $n;
		case 4:
			$n = (ord($char[0]) & 0x0f) << 18;
			$n += (ord($char[1]) & 0x3f) << 12;
			$n += (ord($char[2]) & 0x3f) << 6;
			$n += ord($char[3]) & 0x3f;
			return $n;
	}
}

/**
 * utf8字符串分隔为unicode字符串
 * @param string $str 要转换的字符串
 * @param string $depart 分隔,默认为空格为单字
 * @return string
 */
function str_to_unicode_word($str,$depart=' ')
{
	$arr = array();
	$str_len = mb_strlen($str,'utf-8');
	for($i = 0;$i < $str_len;$i++)
	{
		$s = mb_substr($str,$i,1,'utf-8');
		if($s != ' ' && $s != '　')
		{
			$arr[] = 'ux'.utf8_to_unicode($s);
		}
	}
	return implode($depart,$arr);
}


/**
 * utf8字符串分隔为unicode字符串
 * @param string $str 要转换的字符串
 * @return string
 */
function str_to_unicode_string($str)
{
	$string = str_to_unicode_word($str,'');
	return $string;
}

function str_to_unicode_string_depart($str,$depart=",")
{
	$str_arr = explode($depart,$str);
	$str_arr_n = array();
	foreach($str_arr as $k=>$v)
	{
		if(trim($v)!="")
		$str_arr_n[] = str_to_unicode_word(trim($v),"");
	}
	$string = implode(",", $str_arr_n);
	return $string;
}

/**
 * 分词函数
 * @param string $str
 * @return array 包含原字符串
 */
function div_str($str)
{
	require_once APP_ROOT_PATH."system/libs/words.php";
	$words = words::segment($str);
	$words[] = $str;	
	return $words;
}



/**
 * 
 * @param $tag  //要插入的关键词
 * @param $table  //表名
 * @param $id  //数据ID
 * @param $field		// tag_match/name_match/cate_match/locate_match
 */
function insert_match_item($tag,$table,$id,$field)
{
	if($tag=='')
	return;
	
	$unicode_tag = str_to_unicode_string($tag);
	$sql = "select count(*) from ".DB_PREFIX.$table." where match(".$field.") against ('".$unicode_tag."' IN BOOLEAN MODE) and id = ".$id;
	$rs = $GLOBALS['db']->getOne($sql);
	if(intval($rs) == 0)
	{
		$match_row = $GLOBALS['db']->getRow("select * from ".DB_PREFIX.$table." where id = ".$id);
		if($match_row[$field]=="")
		{
				$match_row[$field] = $unicode_tag;
				$match_row[$field."_row"] = $tag;
		}
		else
		{
				$match_row[$field] = $match_row[$field].",".$unicode_tag;
				$match_row[$field."_row"] = $match_row[$field."_row"].",".$tag;
		}
		$GLOBALS['db']->autoExecute(DB_PREFIX.$table, $match_row, $mode = 'UPDATE', "id=".$id, $querymode = 'SILENT');	
		
	}	
}

/**同步索引的示例
function syn_supplier_match($supplier_id)
{
	$supplier = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".$supplier_id);
	if($supplier)
	{
		$supplier['name_match'] = "";
		$supplier['name_match_row'] = "";
		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier", $supplier, $mode = 'UPDATE', "id=".$supplier_id, $querymode = 'SILENT');	
		
		
		//同步名称
		$name_arr = div_str(trim($supplier['name'])); 
		foreach($name_arr as $name_item)
		{
			insert_match_item($name_item,"supplier",$supplier_id,"name_match");
		}
		
	}
}
*/

/**
 * url 函数，用于web项目，如多个项目另行封装;
 * @param string $route 如: deal#cate
 * @param array $param 参数 如:array("id"=>1,"name"=>"名称");
 * @return string
 */
function url($route="index",$param=array())
{
	$key = md5("URL_KEY_".$route.serialize($param));
	if(isset($GLOBALS[$key]))
	{
		$url = $GLOBALS[$key];
		return $url;
	}
	
	$url = load_dynamic_cache($key);
	if($url!==false)
	{
		$GLOBALS[$key] = $url;
		return $url;
	}
	
	$route_array = explode("#",$route);
	
	if(isset($param)&&$param!=''&&!is_array($param))
	{
		$param['id'] = $param;
	}

	$module = isset($route_array[0])?strtolower(trim($route_array[0])):"";
	$action = isset($route_array[1])?strtolower(trim($route_array[1])):"";

	if(!$module||$module=='index')$module="";
	if(!$action||$action=='index')$action="";
	
	if(app_conf("URL_MODEL")==0)
	{
	//原始模式
		$url = SITE_DOMAIN.APP_ROOT."/index.php";
		if($module!=''||$action!=''||count($param)>0) //有后缀参数
		{
			$url.="?";
		}
	
		if($module&&$module!='')
		$url .= "ctl=".$module."&";
		if($action&&$action!='')
		$url .= "act=".$action."&";
		if(count($param)>0)
		{
			foreach($param as $k=>$v)
			{
				if($k&&$v)
				$url =$url.$k."=".urlencode($v)."&";
			}
		}
		if(substr($url,-1,1)=='&'||substr($url,-1,1)=='?') $url = substr($url,0,-1);
		$GLOBALS[$key] = $url;
		set_dynamic_cache($key,$url);
		return $url;
	}
	else
	{
		//重写的默认
		$url = APP_ROOT;

		
		if(app_conf("DOMAIN_ROOT")!="")
		{
			if($GLOBALS['domain_cfg'][$module]==2)
			{
				$url = "http://".$module.".".app_conf("DOMAIN_ROOT").$url;
			}
			else
			{
				$url = "http://www.".app_conf("DOMAIN_ROOT").$url;
				if($module&&module!="")
					$url .= "/".$module;
			}
		}elseif($module&&$module!='')
		{
			$url .= "/".$module;
		}
		if($action&&$action!='')
		$url .= "/".$action;
		
		if(count($param)>0)
		{
			$url.="/";
			foreach($param as $k=>$v)
			{
				$url =$url.$k."-".urlencode($v)."-";
			}
		}
		
		$route = $module."#".$action;
		switch ($route)
		{
				case "xxx":
					break;
				default:
					break;
		}
				
		if(substr($url,-1,1)=='/'||substr($url,-1,1)=='-') $url = substr($url,0,-1);		
		
		if($url=='')$url="/";
		$GLOBALS[$key] = $url;
		set_dynamic_cache($key,$url);
		return $url;
	}	
}


function unicode_encode($name) {//to Unicode
    $name = iconv('UTF-8', 'UCS-2', $name);
    $len = strlen($name);
    $str = '';
    for($i = 0; $i < $len - 1; $i = $i + 2) {
        $c = $name[$i];
        $c2 = $name[$i + 1];
        if (ord($c) > 0) {// 两个字节的字
            $cn_word = '\\'.base_convert(ord($c), 10, 16).base_convert(ord($c2), 10, 16);
            $str .= strtoupper($cn_word);
        } else {
            $str .= $c2;
        }
    }
    return $str;
}

function unicode_decode($name) {//Unicode to
    $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
    preg_match_all($pattern, $name, $matches);
    if (!empty($matches)) {
        $name = '';
        for ($j = 0; $j < count($matches[0]); $j++) {
            $str = $matches[0][$j];
            if (strpos($str, '\\u') === 0) {
                $code = base_convert(substr($str, 2, 2), 16, 10);
                $code2 = base_convert(substr($str, 4), 16, 10);
                $c = chr($code).chr($code2);
                $c = iconv('UCS-2', 'UTF-8', $c);
                $name .= $c;
            } else {
                $name .= $str;
            }
        }
    }
    return $name;
}


/**
 * 载入页面动态缓存，即全局使用的变量，可以缓存相应项目中，在BaseModule的析构函数中处理
 * @param string $name
 * @return 数据
 */
function load_dynamic_cache($name)
{
	if(isset($GLOBALS['dynamic_cache'][$name]))
	{
		return $GLOBALS['dynamic_cache'][$name];
	}
	else
	{
		return false;
	}
}

/**
 * 缓存页面动态缓存，即全局使用的变量，可以缓存相应项目中，在BaseModule的析构函数中处理
 * @param string $name
 * @param $value 数据
 */
function set_dynamic_cache($name,$value)
{
	if(!isset($GLOBALS['dynamic_cache'][$name]))
	{
		if(isset($GLOBALS['dynamic_cache'])&&count($GLOBALS['dynamic_cache'])>MAX_DYNAMIC_CACHE_SIZE)
		{
			array_shift($GLOBALS['dynamic_cache']);
		}
		$GLOBALS['dynamic_cache'][$name] = $value;		
	}
}

/**
 * 加载模块化缓存，如无数据将自动缓存，主要调用auto_cache中的对应模块
 * @param string $key 模块名称
 * @param array $param  模块参数
 * @return 数据或者false
 */
function load_auto_cache($key,$param=array())
{
	require_once APP_ROOT_PATH."system/libs/auto_cache.php";
	$file =  APP_ROOT_PATH."system/auto_cache/".$key.".auto_cache.php";
	if(file_exists($file))
	{
		require_once $file;
		$class = $key."_auto_cache";
		$obj = new $class;
		$result = $obj->load($param);
	}
        else{
	$result = false;}
	return $result;
}

/**
 * 删除指定参数的模块化缓存
 * @param string $key 模块名称
 * @param array $param  模块参数
 */
function rm_auto_cache($key,$param=array())
{
	require_once APP_ROOT_PATH."system/libs/auto_cache.php";
	$file =  APP_ROOT_PATH."system/auto_cache/".$key.".auto_cache.php";
	if(file_exists($file))
	{
		require_once $file;
		$class = $key."_auto_cache";
		$obj = new $class;
		$obj->rm($param);
	}
}

/**
 * 删除模块化缓存
 * @param string $key 模块名称
 */
function clear_auto_cache($key)
{
	require_once APP_ROOT_PATH."system/libs/auto_cache.php";
	$file =  APP_ROOT_PATH."system/auto_cache/".$key.".auto_cache.php";
	if(file_exists($file))
	{
		require_once $file;
		$class = $key."_auto_cache";
		$obj = new $class;
		$obj->clear_all();
	}
}


/**
 * 返回json的ajax数据
 * @param string $data
 */
function ajax_return($data,$jsonp=false)
{
	if($jsonp)
	{
			$json = json_encode($data);
			header("Content-Type:text/html; charset=utf-8");
			echo $_GET['callback']."(".$json.")";exit;
			

	}
	else
	{
		header("Content-Type:text/html; charset=utf-8");
        echo(json_encode($data));
        exit;	
	}
}


/**
 * 判断是否为gif动画
 * @param string $filename
 * @return bool
 */
function is_animated_gif($filename){
 $fp=fopen($filename, 'rb');
 $filecontent=fread($fp, filesize($filename));
 fclose($fp);
 return strpos($filecontent,chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0')===FALSE?0:1;
}


/**
 * 更新数据库中的config
 */
function update_sys_config()
{
	$filename = APP_ROOT_PATH."public/sys_config.php";
	if(!file_exists($filename))
	{
		//定义DB
		require APP_ROOT_PATH.'system/db/db.php';
		$dbcfg = require APP_ROOT_PATH."public/db_config.php";
		define('DB_PREFIX', $dbcfg['DB_PREFIX']); 
		if(!file_exists(APP_ROOT_PATH.'public/runtime/app/db_caches/'))
			mkdir(APP_ROOT_PATH.'public/runtime/app/db_caches/',0777);
		$pconnect = false;
		$db = new mysql_db($dbcfg['DB_HOST'].":".$dbcfg['DB_PORT'], $dbcfg['DB_USER'],$dbcfg['DB_PWD'],$dbcfg['DB_NAME'],'utf8',$pconnect);
		//end 定义DB

		$sys_configs = $db->getAll("select * from ".DB_PREFIX."conf");
		$config_str = "<?php\n";
		$config_str .= "return array(\n";
		foreach($sys_configs as $k=>$v)
		{
			$config_str.="'".$v['name']."'=>'".addslashes($v['value'])."',\n";
		}
		$config_str.=");\n ?>";	
		file_put_contents($filename,$config_str);
		$url = APP_ROOT."/";
		app_redirect($url);
	}
}

/**
 * 生成并获取二维码图片
 * @param string $str 用于生成的字符串
 * @param int $size 图片大小
 * @return string 二维码图片地址
 */
function gen_qrcode($str,$size = 5)
{

	require_once APP_ROOT_PATH."system/phpqrcode/qrlib.php";

	$root_dir = APP_ROOT_PATH."public/images/qrcode/";
 	if (!is_dir($root_dir)) {
            @mkdir($root_dir);               
            @chmod($root_dir, 0777);
     }
     
     $filename = md5($str."|".$size);
     $hash_dir = $root_dir. '/c' . substr(md5($filename), 0, 1)."/";
     if (!is_dir($hash_dir))
     {
        @mkdir($hash_dir);
        @chmod($hash_dir, 0777);
     }   
	
	$filesave = $hash_dir.$filename.'.png';

	if(!file_exists($filesave))
	{
		QRcode::png($str, $filesave, 'Q', $size, 2); 
	}	
	return APP_ROOT."/public/images/qrcode/c". substr(md5($filename), 0, 1)."/".$filename.".png";       
}

/**
 * 获取服务端传输协议
 * @return string
 */
function get_http()
{
	return (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';
}

/**
 * 动态获取域名或ip地址，包含端口
 * @return string
 */
function get_domain()
{
	/* 协议 */
	$protocol = get_http();

	/* 域名或IP地址 */
	if (isset($_SERVER['HTTP_X_FORWARDED_HOST']))
	{
		$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
	}
	elseif (isset($_SERVER['HTTP_HOST']))
	{
		$host = $_SERVER['HTTP_HOST'];
	}
	else
	{
		/* 端口 */
		if (isset($_SERVER['SERVER_PORT']))
		{
			$port = ':' . $_SERVER['SERVER_PORT'];

			if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol))
			{
				$port = '';
			}
		}
		else
		{
			$port = '';
		}

		if (isset($_SERVER['SERVER_NAME']))
		{
			$host = $_SERVER['SERVER_NAME'] . $port;
		}
		elseif (isset($_SERVER['SERVER_ADDR']))
		{
			$host = $_SERVER['SERVER_ADDR'] . $port;
		}
	}

	return $protocol . $host;
}

/**
 * 获取主机IP或地址，不含协议与端口
 * @return string
 */
function get_host()
{
	/* 域名或IP地址 */
	if (isset($_SERVER['HTTP_X_FORWARDED_HOST']))
	{
		$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
	}
	elseif (isset($_SERVER['HTTP_HOST']))
	{
		$host = $_SERVER['HTTP_HOST'];
	}
	else
	{
		if (isset($_SERVER['SERVER_NAME']))
		{
			$host = $_SERVER['SERVER_NAME'];
		}
		elseif (isset($_SERVER['SERVER_ADDR']))
		{
			$host = $_SERVER['SERVER_ADDR'];
		}
	}
	return $host;
}

/**
 * 转义html编码去空格
 */
function strim($str)
{
	return quotes(htmlspecialchars(trim($str)));
}

/**
 * 转义去空格
 */
function btrim($str)
{
	return quotes(trim($str));
}

function quotes($content)
{
	//if $content is an array
	if (is_array($content))
	{
		foreach ($content as $key=>$value)
		{
			//$content[$key] = mysql_real_escape_string($value);
			$content[$key] = addslashes($value);
		}
	} else
	{
		//if $content is not an array
		$content = addslashes($content);
		//mysql_real_escape_string($content);
	}
	return $content;
}


/**
 * 将单个图片同步到远程的图片服务器
 * @param string $url 本地的图片地址，"./public/......"
 */
function syn_to_remote_image_server($url)
{
	$pathinfo = pathinfo($url);
	$file = $pathinfo['basename'];
	$dir = $pathinfo['dirname'];
	$dir = str_replace("./public/", "", $dir);
	$filefull = SITE_DOMAIN.APP_ROOT."/public/".$dir."/".$file;
	$syn_url = app_conf("PUBLIC_DOMAIN_ROOT")."/es_file.php?username=".app_conf("IMAGE_USERNAME")."&password=".app_conf("IMAGE_PASSWORD")."&file=".
						$filefull."&path=".$dir."/&name=".$file."&act=0";
	@file_get_contents($syn_url);
}

/**
 * 将填写的金额(元)转成整型(分)
 */
function format_price_to_db($money)
{
	return round($money*100);
}
/**
 * 将整型(分)转换为显示的金额（元）
 */
function format_price_to_display($money)
{
	return round($money/100,2);
}

/**
 * 将价格展示成显示格式
 * @param float $money
 */
function format_price($money)
{
	return number_format($money,2)." 元";
}

function format_score($score)
{
	return $score;
}

function format_exp($exp)
{
	return $exp;
}

function format_fulltext_key($key)
{
	if(empty($key))
		return "";
	$keys = preg_split("/[, ]/", $key);
	foreach ($keys as $k=>$v)
	{
		$keys[$k] = FULLTEXT_PREFIX.$v;
	}
	$key = implode(",", $keys);
	return $key;
}

function unformat_fulltext_key($key)
{
	if(empty($key))
		return "";
	$keys = preg_split("/[, ]/", $key);
	foreach ($keys as $k=>$v)
	{
		$keys[$k] = preg_replace("/".FULLTEXT_PREFIX."/", "", $v,1);
	}
	$key = implode(",", $keys);
	return $key;
}

/**
 * 生成配送城市
 */
function make_region_city_js(){
	$tmplist = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."city ORDER BY py_first ASC ");
	$list = array();
	foreach($tmplist as $k=>$v){
		$list[$v['pid']][$v['id']] = $v;
	}
	
	unset($tmplist);
	
	$str = "var region_city = " .json_encode($list).";";
	
	@file_put_contents(APP_ROOT_PATH."/public/region_city.js",$str);
}

/*
 * 获得星期串
 * */
function week_num($num,$prifix="周"){
	
	$week_val='';
	if($num==1){
		$week_val=$prifix.'一';
	}elseif($num==2){
		$week_val=$prifix.'二';
	}elseif($num==3){
		$week_val=$prifix.'三';
	}elseif($num==4){
		$week_val=$prifix.'四';
	}elseif($num==5){
		$week_val=$prifix.'五';
	}elseif($num==6){
		$week_val=$prifix.'六';
	}else{
		$week_val=$prifix.'日';
	}
	return $week_val;
}

/**
 * 
 * @param int $type: 1:线路; 2:门票
 * @param int $id,线路或门票id
 * @param int $user_id 用户ID
 * @return 
 *  		name： 门票/线路名
			 order_id:订单ID
			 allow_review: 0:不允许点评;1:允许点评;2:已点评;
			`review_return_money` '点评审核后返现',
			`review_return_score` '点评后返积分',
			`review_return_exp` '点评后增加的经验',
			`review_return_voucher_type_id` '点评后返还的代金券ID',
 */
function get_review_order($type,$id,$user_id){
	if(intval($type)==0) $type = 1;
	$order = array();
	switch ( $type ) {
		case 1: //线路
			$order = $GLOBALS['db']->getRow("select id as order_id,tourline_name as name,allow_review,review_return_money,review_return_score,review_return_exp,review_return_voucher_type_id from ".DB_PREFIX."tourline_order where allow_review = 1 and tourline_id = ".$id." and user_id = ".$user_id." limit 1");
			return $order;			
			break;
		case 2: //门票
			
			//$order = $GLOBALS['db']->getRow("select tt.id as order_id ,tt.ticket_id ,tt.ticket_name as name,tt.allow_review,tt.review_return_money,tt.review_return_score,tt.review_return_exp,tt.review_return_voucher_type_id from ".DB_PREFIX."ticket_order tt LEFT JOIN ".DB_PREFIX."ticket t ON t.id = tt.ticket_id where tt.allow_review = 1 and t.spot_id = ".$id." and tt.user_id = ".$user_id." limit 1");
			$order = $GLOBALS['db']->getRow("select id as order_id ,ticket_id ,ticket_name as name,allow_review,review_return_money,review_return_score,review_return_exp,review_return_voucher_type_id from ".DB_PREFIX."ticket_order  where allow_review = 1 and spot_id = ".$id." and user_id = ".$user_id." limit 1");
			return $order;
			break;
	}
	return $order;
}
/**
 * 获取对应景点或者门票 信息
 * @param int $type
 * @param type $id
 * @return type
 */
function get_review_rel_item($type,$id){
    if(intval($type)==0) $type = 1;
	$item = array();
	switch ( $type ) {
		case 1: //线路
			$item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."tourline where id = ".$id);
			return $item;			
			break;
		case 2: //门票
			
			$item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ticket  where id = ".$id);
			return $item;
			break;
	}
	return $item;
}

/**
 * 更新点评关联表统计数据
 * @param int $type
 * @param type $id
 * @param type $data
 * @return type
 */
function update_review_rel_item($type,$id,$data){
    if(intval($type)==0) $type = 1;
	switch ( $type ) {
		case 1: //线路
			$GLOBALS['db']->autoExecute(DB_PREFIX."tourline",$data,"UPDATE","id=".$id);		
			break;
		case 2: //门票
			$GLOBALS['db']->autoExecute(DB_PREFIX."spot",$data,"UPDATE","id=".$id);
			break;
	}
	return $GLOBALS['db']->affected_rows();
}

/**
 * 更新点评权限状态
 * @param int $review_type
 * @param int $order_id
 * @param int $allow_status 0为不允许 1为允许
 * @return type
 */
function update_set_allow_review($review_type,$order_id,$allow_status){
    if(intval($type)==0) $type = 1;
	$order = array();
	switch ( $review_type ) {
		case 1: //线路 fanwe_tourline_order
			$GLOBALS['db']->autoExecute(DB_PREFIX."tourline_order",array("allow_review"=>$allow_status),"UPDATE","id=".$order_id);
			break;
		case 2: //门票 fanwe_ticket_order_item
			$GLOBALS['db']->autoExecute(DB_PREFIX."ticket_order",array("allow_review"=>$allow_status),"UPDATE","id=".$order_id);
			break;
	}
	return $GLOBALS['db']->affected_rows();
}
    /**
    * 分页处理
    * @param string $type 所在页面
    * @param array  $args 参数
    * @param int $total_count 总数
    * @param int $page 当前页
    * @param int $page_size 分页大小
    * @param int $is_ajax 是否生成AJAX使用分页
    * @param string $url 自定义路径
    * @param int $offset 偏移量
    * @return array
    */
   function buildPage($type,$args,$total_count,$page = 1,$page_size = 0,$is_ajax=0,$url='',$offset = 5)
   {
            
           $pager['total_count'] = intval($total_count);
           $pager['page'] = $page;
           $pager['is_ajax'] = $is_ajax;
           $pager['page_size'] = ($page_size == 0) ? 20 : $page_size;
           /* page 总数 */
           $pager['page_count'] = ($pager['total_count'] > 0) ? ceil($pager['total_count'] / $pager['page_size']) : 1;

           /* 边界处理 */
           if ($pager['page'] > $pager['page_count'])
                   $pager['page'] = $pager['page_count'];

           $pager['limit'] = ($pager['page'] - 1) * $pager['page_size'] . "," . $pager['page_size'];

           $page_prev  = ($pager['page'] > 1) ? $pager['page'] - 1 : 1;
           $page_next  = ($pager['page'] < $pager['page_count']) ? $pager['page'] + 1 : $pager['page_count'];
           $pager['prev_page'] = $page_prev;
           $pager['next_page'] = $page_next;

           if (!empty($url))
           {
                   $pager['page_first'] = $url . 1;
                   $pager['page_prev']  = $url . $page_prev;
                   $pager['page_next']  = $url . $page_next;
                   $pager['page_last']  = $url . $pager['page_count'];
           }
           else
           {
                   $args['p'] = '_page_';
                   if(!empty($type))
                           $page_url = url($type,$args);
                   else
                           $page_url = 'javascript:;';

                   $pager['page_first'] = str_replace('_page_',1,$page_url);
                   $pager['page_prev']  = str_replace('_page_',$page_prev,$page_url);
                   $pager['page_next']  = str_replace('_page_',$page_next,$page_url);
                   $pager['page_last']  = str_replace('_page_',$pager['page_count'],$page_url);
           }

           $pager['page_nums'] = array();

           if($pager['page_count'] <= $offset * 2)
           {
                   for ($i=1; $i <= $pager['page_count']; $i++)
                   {
                           $pager['page_nums'][] = array('name' => $i,'url' => empty($url) ? str_replace('_page_',$i,$page_url) : $url . $i);
                   }
           }
           else
           {
                   if($pager['page'] - $offset < 2)
                   {
                           $temp = $offset * 2;

                           for ($i=1; $i<=$temp; $i++)
                           {
                                   $pager['page_nums'][] = array('name' => $i,'url' => empty($url) ? str_replace('_page_',$i,$page_url) : $url . $i);
                           }

                           $pager['page_nums'][] = array('name'=>'...');
                           $pager['page_nums'][] = array('name' => $pager['page_count'],'url' => empty($url) ? str_replace('_page_',$pager['page_count'],$page_url) : $url . $pager['page_count']);
                   }
                   else
                   {
                           $pager['page_nums'][] = array('name' => 1,'url' => empty($url) ? str_replace('_page_',1,$page_url) : $url . 1);
                           $pager['page_nums'][] = array('name'=>'...');
                           $start = $pager['page'] - $offset + 1;
                           $end = $pager['page'] + $offset - 1;

                           if($pager['page_count'] - $end > 1)
                           {
                                   for ($i=$start;$i<=$end;$i++)
                                   {
                                           $pager['page_nums'][] = array('name' => $i,'url' => empty($url) ? str_replace('_page_',$i,$page_url) : $url . $i);
                                   }

                                   $pager['page_nums'][] = array('name'=>'...');
                                   $pager['page_nums'][] = array('name' => $pager['page_count'],'url' => empty($url) ? str_replace('_page_',$pager['page_count'],$page_url) : $url . $pager['page_count']);
                           }
                           else
                           {
                                   $start = $pager['page_count'] - $offset * 2 + 1;
                                   $end = $pager['page_count'];
                                   for ($i=$start;$i<=$end;$i++)
                                   {
                                           $pager['page_nums'][] = array('name' => $i,'url' => empty($url) ? str_replace('_page_',$i,$page_url) : $url . $i);
                                   }
                           }
                   }
           }

           return $pager;
   }
   
   
   /**
    * 
    * @param string $cnt
    * 返回过滤过非法词言后的文字内容
    */
   function filter_content($cnt)
   {
   	return $cnt;
   }
   
   /*
    * 获得游客证件名称
    * */
   function get_paper_type_name($paper_type=1){
   		
	   	if($paper_type == 2)
			$paper_type_val="护照";
		elseif($paper_type == 3)
			$paper_type_val="军官证";
		elseif($paper_type == 4)
			$paper_type_val="港澳通行证";
		elseif($paper_type == 5)
			$paper_type_val="台胎证";
		elseif($paper_type == 6)
			$paper_type_val="其他";
		else
			$paper_type_val="身份证";
			
		return $paper_type_val;
   }
   
   

   /**
    * 加载下拉的导航菜单
    * 返回数据
    * array(
    * 	"name"	=>	"xxx",  名称
    * 	"url"	=>	"xxx", 地址
    *  "sub_nav" => array(array("name"=>"xxx","url"=>"xxx")); 子菜单
    *  "pop_nav"	=>	array(array("name"=>"xx","url"=>"xx","s_cate"=>array(array("name"=>"xx","url"=>"xx")) )//分组)
    * )
    */
   function load_drop_navs()
   {
   	//有弹出显示: moudule:around(周边游) domestic(国内游) outbound(出境游) tours#index(跟团游) diy(自助游) drive(自驾游)
   	$drop_navs_res = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."drop_nav order by sort asc");
   	$drop_navs = array();
   	foreach($drop_navs_res as $k=>$v)
   	{
   		$drop_navs[$k]['name'] = $v['name'];
   		if($v['u_module']=="around")
   		{
   			$drop_navs[$k]['name']=$GLOBALS['city']['name'].$v['name'];
   		}
   		if($v['url']==="")
   		{
   			if($v['u_module']=="")$v['u_module']="index";
   			if($v['u_action']=="")$v['u_action']="index";
   			$route = $v['u_module'];
   			if($v['u_action']!='')$route.="#".$v['u_action'];
   			$str = "u:".$route."|".$v['u_param'];
   			$drop_navs[$k]['url'] =  parse_url_tag($str);
   		}
   		else
   		{
   			$drop_navs[$k]['url'] = $v['url'];
   		}
   		//设置sub_nav
   		if($v['u_module']=="around"||$v['u_module']=="domestic"||$v['u_module']=="outbound"||$v['u_module']=="diy"||$v['u_module']=="drive"||($v['u_module']=="tours"&&$v['u_action']=="index")||($v['u_module']=="spot"&&$v['u_action']=="index"))
   		{
   			$sub_nav = array();
   			if($v['u_module']=="around")
   			{
   				//查询当前城市的周边景点
   				$city_fullkey = format_fulltext_key($GLOBALS['city']['py']);
   				$sub_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_place where (match(city_match) against('".$city_fullkey."' IN BOOLEAN MODE)) and is_recommend = 1 limit 3 ");
   				foreach($sub_list as $kk=>$vv)
   				{
   					$sub_nav[$kk]['name'] = $vv['name'];
   					$tag = get_first_index($vv['tag_match_row']);
   					$sub_nav[$kk]['url'] = url("tourlist#around",array("tag"=>$tag,"p_py"=>$vv['py']));
   				}
   			}
   			elseif($v['u_module']=="domestic")
   			{
   				//查询国内的大区
   				$sub_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_area where type=1 and is_recommend = 1 limit 3 ");
   				foreach($sub_list as $kk=>$vv)
   				{
   					$sub_nav[$kk]['name'] = $vv['name'];
   					$sub_nav[$kk]['url'] = url("tourlist",array("type"=>1,"a_py"=>$vv['py']));
   				}
   			}
   			elseif($v['u_module']=="outbound")
   			{
   				//查询国外的大区
   				$sub_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_area where type=2 and is_recommend = 1 limit 3 ");
   				foreach($sub_list as $kk=>$vv)
   				{
   					$sub_nav[$kk]['name'] = $vv['name'];
   					$sub_nav[$kk]['url'] = url("tourlist",array("type"=>2,"a_py"=>$vv['py']));
   				}
   			}
   			elseif($v['u_module']=="tours"&&$v['u_action']=="index")
   			{
   				//跟团游查询大区域
   				$sub_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_area where  is_recommend = 1 order by rand() limit 3  ");
   				foreach($sub_list as $kk=>$vv)
   				{
   					$sub_nav[$kk]['name'] = $vv['name'];
   					$sub_nav[$kk]['url'] = url("tourlist",array("t_type"=>1,"a_py"=>$vv['py']));
   				}
   			}
   			elseif($v['u_module']=="diy")
   			{
   				//自助游查询大区域
   				$sub_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_area where  is_recommend = 1 order by rand() limit 3  ");
   				foreach($sub_list as $kk=>$vv)
   				{
   					$sub_nav[$kk]['name'] = $vv['name'];
   					$sub_nav[$kk]['url'] = url("tourlist",array("t_type"=>2,"a_py"=>$vv['py']));
   				}
   			}
   			elseif($v['u_module']=="drive")
   			{
   				//自助游查询大区域
   				$sub_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_area where  is_recommend = 1 order by rand() limit 3  ");
   				foreach($sub_list as $kk=>$vv)
   				{
   					$sub_nav[$kk]['name'] = $vv['name'];
   					$sub_nav[$kk]['url'] = url("tourlist",array("t_type"=>3,"a_py"=>$vv['py']));
   				}
   			}
   			elseif($v['u_module']=="spot"&&$v['u_action']=="index")
   			{
   				//景点查询分类
   				$sub_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."spot_cate where  is_recommend = 1 order by sort limit 3  ");
   				foreach($sub_list as $kk=>$vv)
   				{
   					$sub_nav[$kk]['name'] = $vv['name'];
   					$sub_nav[$kk]['url'] = url("spot#cat",array("cate"=>$vv['id']));
   				}
   			}
   				
   			$drop_navs[$k]['sub_nav'] = $sub_nav;
   		}
   
   		//设置pop_nav
   		if($v['u_module']=="around"||$v['u_module']=="domestic"||$v['u_module']=="outbound"||$v['u_module']=="diy"||$v['u_module']=="drive"||($v['u_module']=="tours"&&$v['u_action']=="index"))
   		{
   			$b_cate = array();
   			if($v['u_module']=="around")
   			{
   				/*
   				$b_cate = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."tour_place_tag where is_recommend = 1 order by sort asc"); //获取标签列表
   				if($b_cate)
   				{
   					foreach($b_cate as $kk=>$vv)
   					{
   						$b_cate[$kk]['url'] = url("tourlist#around",array("tag"=>$vv['name']));
   						$fullkey = str_to_unicode_string($vv['name']);
   						$city_fullkey = format_fulltext_key($GLOBALS['city']['py']);
   						$s_cate = $GLOBALS['db']->getAll("select id,name,py from ".DB_PREFIX."tour_place where is_recommend = 1 and (match(tag_match) against('".$fullkey."' IN BOOLEAN MODE)) and (match(city_match) against('".$city_fullkey."' IN BOOLEAN MODE)) order by py asc");
   						if($s_cate)
   						{
   							foreach($s_cate as $kkk=>$vvv)
   							{
   								$s_cate[$kkk]['url'] = url("tourlist#around",array("tag"=>$vv['name'],"p_py"=>$vvv['py']));
   							}
   							$b_cate[$kk]['s_cate'] = $s_cate;
   						}
   					}
   				}*/
   				$city_fullkey = format_fulltext_key($GLOBALS['city']['py']);
   				$place_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tour_place where (match(city_match) against('".$city_fullkey."' IN BOOLEAN MODE)) and tag_match_row !='' order by is_recommend DESC,py ASC");
	   			foreach($place_list as $kk=>$vv)
				{
					$tag_match_row=explode(',',$vv['tag_match_row']);
					foreach($tag_match_row as $kkk=>$vvv)
					{
						$b_cate[$vvv]['name']=$vvv;
						$b_cate[$vvv]['url']=url("tourlist#around",array("tag"=>$vvv));						
						$place_list[$kk]['url']=url("tourlist#around",array("tag"=>$vvv,"p_py"=>$vv['py']));
						$b_cate[$vvv]['s_cate'][]=$place_list[$kk];
					}
				}
   			}
   			elseif($v['u_module']=="domestic"||$v['u_module']=="outbound")
   			{
   				$rec_type = $v['u_module']=="domestic"?1:2;
   				$b_cate = $GLOBALS['db']->getAll("select id,name,py from ".DB_PREFIX."tour_area where type = $rec_type and is_recommend = 1 order by py asc"); //获取国内或境外大区域
   				if($b_cate)
   				{
   					foreach($b_cate as $kk=>$vv)
   					{
   						$b_cate[$kk]['url'] = url("tourlist",array("type"=>$rec_type,"a_py"=>$vv['py']));
   						$fullkey = format_fulltext_key($vv['py']);
   						$s_cate = $GLOBALS['db']->getAll("select id,name,py from ".DB_PREFIX."tour_place where is_recommend = 1 and (match(area_match) against('".$fullkey."' IN BOOLEAN MODE)) order by py asc");
   						if($s_cate)
   						{
   							foreach($s_cate as $kkk=>$vvv)
   							{
   								$s_cate[$kkk]['url'] = url("tourlist",array("type"=>$rec_type,"a_py"=>$vv['py'],"p_py"=>$vvv['py']));
   							}
   							$b_cate[$kk]['s_cate'] = $s_cate;
   						}
   					}
   				}
   			}
   			elseif($v['u_module']=="diy"||$v['u_module']=="drive"||($v['u_module']=="tours"&&$v['u_action']=="index"))
   			{
   				if($v['u_module']=="tours"&&$v['u_action']=="index")
   					$t_type = 1;
   				elseif($v['u_module']=="diy")
   				$t_type = 2;
   				else
   					$t_type = 3;
   				$b_cate = $GLOBALS['db']->getAll("select id,name,py from ".DB_PREFIX."tour_area where is_recommend = 1 order by py asc"); //获取全部大区域
   				if($b_cate)
   				{
   					foreach($b_cate as $kk=>$vv)
   					{
   						$b_cate[$kk]['url'] = url("tourlist",array("t_type"=>$t_type,"a_py"=>$vv['py']));
   						$fullkey = format_fulltext_key($vv['py']);
   						$s_cate = $GLOBALS['db']->getAll("select id,name,py from ".DB_PREFIX."tour_place where is_recommend = 1 and (match(area_match) against('".$fullkey."' IN BOOLEAN MODE)) order by py asc");
   						if($s_cate)
   						{
   							foreach($s_cate as $kkk=>$vvv)
   							{
   								$s_cate[$kkk]['url'] = url("tourlist",array("t_type"=>$t_type,"a_py"=>$vv['py'],"p_py"=>$vvv['py']));
   							}
   							$b_cate[$kk]['s_cate'] = $s_cate;
   						}
   					}
   				}
   			}
   				
   			$drop_navs[$k]['pop_nav'] = $b_cate;
   		}
   	}
   	return $drop_navs;
   
   }
   
   //获取已过时间
	function pass_date($time)
	{
		$time_span = get_gmtime() - $time;
		if($time_span>3600*24*365)
		{
			//一年以前
			$time_span_lang = to_date($time,"Y-m-d");
		}
		elseif($time_span>3600*24*30)
		{
			//一月
			$time_span_lang = to_date($time,"Y-m-d");
		}
		elseif($time_span>3600*24)
		{
			//一天
			//$time_span_lang = to_date($time,"Y-m-d");
			$time_span_lang = round($time_span/(3600*24))."天前";
		}
		elseif($time_span>3600)
		{
			//一小时
			$time_span_lang = round($time_span/(3600))."小时前";
		}
	    elseif($time_span>60)
		{
			//一分
			$time_span_lang = round($time_span/(60))."分钟前";
		}
		else
		{
			//一秒
			$time_span_lang = $time_span."秒前";
		}
		return $time_span_lang;
	}
	
	
	/**
	 * 订单支付成功，发短信
	 * @param unknown_type $order_info
	 * @param int $order_type 订单类型; 类型(1.线路 2.门票 3.酒店)
	 */	
	function send_order_sms($order_info,$order_type)
	{
		if(app_conf("SMS_ON")==1)
		{
			//$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
			//{$order.user_name}你好,你所下订单{$order.order_sn},金额{$order.pay_amount_format}于{$order.pay_time_format}支付成功,验证码为:{$order.verify_code}
			if($order_info)
			{
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);
				$mobile = $order_info['appoint_mobile'];
				if (empty($mobile)){
					$mobile = $user_info['mobile'];
				}
					
				if(!empty($mobile))
				{
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_ORDER'");
					$tmpl_content = $tmpl['content'];
					$notice_data['user_name'] = $user_info['user_name'];
					$notice_data['order_sn'] = $order_info['sn'];
					$notice_data['pay_time_format'] = to_date($order_info['pay_time']);
					$notice_data['pay_amount_format'] = format_price(format_price_to_display($order_info['pay_amount']));
					
					//类型(1.线路 2.门票 3.酒店)
					$verify_code = '';
					if ($order_type == 1){
						$verify_code = $order_info['verify_code'];
					}else if ($order_type == 2){
						$verify_code = $GLOBALS['db']->getOne("select group_concat(verify_code) from ".DB_PREFIX."ticket_order_item where order_id = ".intval($order_info['id']));
					}
					$notice_data['verify_code'] = $verify_code;
					
					if($order_info['supplier_id'] >0)
					{
						$supplier_phone= $GLOBALS['db']->getRow("select contact_mobile,contact_tel from ".DB_PREFIX."supplier where id = ".intval($order_info['supplier_id']));
						if($supplier_phone['contact_mobile'] !='')
							$notice_data['supplier_phone'] = $supplier_phone['contact_mobile'];
						else
							$notice_data['supplier_phone'] = $supplier_phone['contact_tel'];
					}else
						$notice_data['supplier_phone'] = '';
					
					$GLOBALS['tmpl']->assign("order",$notice_data);
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
	
					$msg_data['dest'] = $mobile;
	
					$msg_data['send_type'] = 0;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
					
					return 1;
				}
			}
		}else{
			return 0;
		}
	}
	
	/**
	 * 订单支付成功，发短信给商家
	 * @param unknown_type $order_info
	 */
	function send_supplier_order_sms($order_info)
	{
		if(app_conf("SMS_ON")==1&&app_conf("SUPPLIER_SMS_ON")==1)
		{
			if($order_info)
			{
				$supplier_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".$order_info['supplier_id']);
				$mobile = $supplier_info['contact_mobile'];

					
				if(!empty($mobile))
				{
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_SUPPLIER_ORDER'");
					$tmpl_content = $tmpl['content'];
					
					$GLOBALS['tmpl']->assign("supplier_name",$supplier_info['contact_name']);
					$GLOBALS['tmpl']->assign("product_name",$order_info['short_name']);
					$GLOBALS['tmpl']->assign("order_sn",$order_info['sn']);
					
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
		
					$msg_data['dest'] = $mobile;
		
					$msg_data['send_type'] = 0;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = 0;
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
						
					return 1;
				}
			}
		}else{
			return 0;
		}
	}
	
		
	/**
	 * 订单支付成功，发邮件
	 * @param unknown_type $order_info
	 * @param int $order_type 订单类型; 类型(1.线路 2.门票 3.酒店)
	 */
	function send_order_mail($order_info,$order_type)
	{
		if(app_conf("MAIL_ON")==1)
		{
			//$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
			if($order_info)
			{
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);
				$email = $order_info['appoint_email'];
				if (empty($email)){
					$email = $user_info['email'];
				}
	
				if(!empty($email))
				{
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_ORDER'");
					$tmpl_content = $tmpl['content'];
					$notice_data['user_name'] = $user_info['user_name'];
					$notice_data['order_sn'] = $order_info['sn'];
					$notice_data['pay_time_format'] = to_date($order_info['pay_time']);
					$notice_data['pay_amount_format'] = format_price(format_price_to_display($order_info['pay_amount']));
					
					//类型(1.线路 2.门票 3.酒店)
					$verify_code = '';
					if ($order_type == 1){
						$verify_code = $order_info['verify_code'];
					}else if ($order_type == 2){
						
						$verify_code = $GLOBALS['db']->getOne("select group_concat(verify_code) from ".DB_PREFIX."ticket_order_item where order_id = ".intval($order_info['id']));
					}
					
					$notice_data['verify_code'] = $verify_code;
					
					$GLOBALS['tmpl']->assign("order",$notice_data);
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
	
					$msg_data['dest'] = $email;
	
					$msg_data['send_type'] = 1;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
					
					return 1;
				}
			}
		}else{
			return 0;
		}
	}
	
	/**
	 * 订单支付成功，发邮件给商家
	 * @param unknown_type $order_info
	 */
	function send_supplier_order_mail($order_info)
	{
		if(app_conf("MAIL_ON")==1&&app_conf("SUPPLIER_MAIL_ON")==1)
		{
			if($order_info)
			{
				$supplier_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".$order_info['supplier_id']);
				$email = $supplier_info['contact_email'];
	
					
				if(!empty($email))
				{
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_SUPPLIER_ORDER'");
					$tmpl_content = $tmpl['content'];
						
					$GLOBALS['tmpl']->assign("supplier_name",$supplier_info['contact_name']);
					$GLOBALS['tmpl']->assign("product_name",$order_info['short_name']);
					$GLOBALS['tmpl']->assign("order_sn",$order_info['sn']);
						
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
	
					$msg_data['dest'] = $email;
	
					$msg_data['send_type'] = 1;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = 0;
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	
					return 1;
				}
			}
		}else{
			return 0;
		}
	}
	
	
	//订单退款成功，发短信
	function send_order_refund_sms($order_info)
	{
		if(app_conf("SMS_ON")==1)
		{
			//$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
			if($order_info)
			{
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);
				$mobile = $order_info['appoint_mobile'];
				if (empty($mobile)){
					$mobile = $user_info['mobile'];
				}
					
				if(!empty($mobile))
				{
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_ORDER_REFUND'");
					$tmpl_content = $tmpl['content'];
					$notice_data['user_name'] = $user_info['user_name'];
					$notice_data['order_sn'] = $order_info['sn'];

	
					$GLOBALS['tmpl']->assign("order",$notice_data);
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
	
					$msg_data['dest'] = $mobile;
	
					$msg_data['send_type'] = 0;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	
				}
			}
		}
	}

	//订单拒绝退款，发短信
	function send_order_reject_refund_sms($order_info)
	{
		if(app_conf("SMS_ON")==1)
		{
			//$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
			if($order_info)
			{
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);
				$mobile = $order_info['appoint_mobile'];
				if (empty($mobile)){
					$mobile = $user_info['mobile'];
				}
					
				if(!empty($mobile))
				{
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_ORDER_REJECT_REFUND'");
					$tmpl_content = $tmpl['content'];
					$notice_data['user_name'] = $user_info['user_name'];
					$notice_data['order_sn'] = $order_info['sn'];
					$notice_data['refuse_reason'] = $order_info['refuse_reason'];
	
					$GLOBALS['tmpl']->assign("order",$notice_data);
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
	
					$msg_data['dest'] = $mobile;
	
					$msg_data['send_type'] = 0;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	
				}
			}
		}
	}	
	
	//订单退款成功，发邮件
	function send_order_refund_mail($order_info)
	{
		if(app_conf("MAIL_ON")==1)
		{
			//$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
			if($order_info)
			{
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);
				$email = $order_info['appoint_email'];
				if (empty($email)){
					$email = $user_info['email'];
				}
	
				if(!empty($email))
				{
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_ORDER_REFUND'");
					$tmpl_content = $tmpl['content'];
					$notice_data['user_name'] = $user_info['user_name'];
					$notice_data['order_sn'] = $order_info['sn'];
					//$notice_data['refuse_reason'] = $order_info['refuse_reason'];
	
					$GLOBALS['tmpl']->assign("order",$notice_data);
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
	
					$msg_data['dest'] = $email;
	
					$msg_data['send_type'] = 1;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				}
			}
		}
	}

	//订单拒绝退款，发邮件
	function send_order_reject_refund_mail($order_info)
	{
		if(app_conf("MAIL_ON")==1)
		{
			//$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
			if($order_info)
			{
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);
				$email = $order_info['appoint_email'];
				if (empty($email)){
					$email = $user_info['email'];
				}
	
				if(!empty($email))
				{
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_ORDER_REJECT_REFUND'");
					$tmpl_content = $tmpl['content'];
					$notice_data['user_name'] = $user_info['user_name'];
					$notice_data['order_sn'] = $order_info['sn'];
					$notice_data['refuse_reason'] = $order_info['refuse_reason'];
	
					$GLOBALS['tmpl']->assign("order",$notice_data);
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
	
					$msg_data['dest'] = $email;
	
					$msg_data['send_type'] = 1;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				}
			}
		}
	}

	//验证码使用成功，发短信
	function send_use_coupon_sms($order_info,$verify_code,$verify_time_format)
	{
		if(app_conf("SMS_ON")==1)
		{
			//$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
			if($order_info)
			{
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);
				$mobile = $order_info['appoint_mobile'];
				if (empty($mobile)){
					$mobile = $user_info['mobile'];
				}
					
				if(!empty($mobile))
				{
					//{$user_name}你好! 验证码{$verify_code}，已于{$verify_time_format}使用
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_USE_COUPON'");
					$tmpl_content = $tmpl['content'];
					
					$GLOBALS['tmpl']->assign("user_name",$user_info['user_name']);
					$GLOBALS['tmpl']->assign("verify_code",$verify_code);
					$GLOBALS['tmpl']->assign("verify_time_format",$verify_time_format);
					
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
	
					$msg_data['dest'] = $mobile;
	
					$msg_data['send_type'] = 0;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	
				}
			}
		}
	}
	
	//验证码使用成功，发邮件
	function send_use_coupon_mail($order_info,$verify_code,$verify_time_format)
	{
		if(app_conf("MAIL_ON")==1)
		{
			//$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
			if($order_info)
			{
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);
				$email = $order_info['appoint_email'];
				if (empty($email)){
					$email = $user_info['email'];
				}
	
				if(!empty($email))
				{
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_USE_COUPON'");
					$tmpl_content = $tmpl['content'];
					
					$GLOBALS['tmpl']->assign("user_name",$user_info['user_name']);
					$GLOBALS['tmpl']->assign("verify_code",$verify_code);
					$GLOBALS['tmpl']->assign("verify_time_format",$verify_time_format);
					
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
	
					$msg_data['dest'] = $email;
	
					$msg_data['send_type'] = 1;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				}
			}
		}
	}	
	
	
	//订单发货 短信通知
	function send_order_delivery_sms($order_info)
	{
		if(app_conf("SMS_ON")==1)
		{
			//$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
			if($order_info)
			{
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);
				$mobile = $order_info['appoint_mobile'];
				if (empty($mobile)){
					$mobile = $user_info['mobile'];
				}
					
				if(!empty($mobile))
				{
					//{$order.user_name}你好,你所下订单{$order.order_sn},已{$order.delivery_time_format}发货,单号为:{$order.delivery_sn},请注意查收
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_ORDER_DELIVERY'");
					$tmpl_content = $tmpl['content'];

					$order_info['user_name'] = $user_info['user_name'];
					$order_info['order_sn'] = $order_info['sn'];
					$order_info['delivery_time_format'] = to_date($order_info['delivery_time'],'Y-m-d');
					
					$GLOBALS['tmpl']->assign("order",$order_info);
						
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
	
					$msg_data['dest'] = $mobile;
	
					$msg_data['send_type'] = 0;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	
				}
			}
		}
	}
	
	//订单发货 发邮件 通知
	function send_order_delivery_mail($order_info)
	{
		if(app_conf("MAIL_ON")==1)
		{
			//$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
			if($order_info)
			{
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);
				$email = $order_info['appoint_email'];
				if (empty($email)){
					$email = $user_info['email'];
				}
	
				if(!empty($email))
				{
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_ORDER_DELIVERY'");
					$tmpl_content = $tmpl['content'];
					
					$order_info['user_name'] = $user_info['user_name'];
					$order_info['order_sn'] = $order_info['sn'];
					$order_info['delivery_time_format'] = to_date($order_info['delivery_time'],'Y-m-d');
						
					$GLOBALS['tmpl']->assign("order",$order_info);
						
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
	
					$msg_data['dest'] = $email;
	
					$msg_data['send_type'] = 1;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				}
			}
		}
	}	
	
	
	//订单改签通过  短信通知
	function send_order_re_appoint_sms($order_info,$appoint_time_format)
	{
		if(app_conf("SMS_ON")==1)
		{
			//$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
			if($order_info)
			{
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);
				$mobile = $order_info['appoint_mobile'];
				if (empty($mobile)){
					$mobile = $user_info['mobile'];
				}

				//echo 'mobile:'.$mobile;
				
				if(!empty($mobile))
				{
					
					//echo 'mobile:'.$mobile;
					
					//{$order.user_name}你好,你所下订单{$order.order_sn},改签成功,改签日期为:{$order.appoint_time_format}
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_ORDER_RE_APPOINT'");
					$tmpl_content = $tmpl['content'];
	
					$order_info['user_name'] = $user_info['user_name'];
					$order_info['order_sn'] = $order_info['sn'];
					$order_info['appoint_time_format'] = $appoint_time_format;
						
					$GLOBALS['tmpl']->assign("order",$order_info);
	
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
	
					$msg_data['dest'] = $mobile;
	
					$msg_data['send_type'] = 0;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	
				}
			}
		}
	}
	
	//订单改签通过  发邮件 通知
	function send_order_re_appoint_mail($order_info,$appoint_time_format)
	{
		if(app_conf("MAIL_ON")==1)
		{
			//$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
			if($order_info)
			{
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);
				$email = $order_info['appoint_email'];
				if (empty($email)){
					$email = $user_info['email'];
				}
	
				if(!empty($email))
				{
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_ORDER_RE_APPOINT'");
					$tmpl_content = $tmpl['content'];
						
					$order_info['user_name'] = $user_info['user_name'];
					$order_info['order_sn'] = $order_info['sn'];
					$order_info['appoint_time_format'] = $appoint_time_format;
	
					$GLOBALS['tmpl']->assign("order",$order_info);
	
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
	
					$msg_data['dest'] = $email;
	
					$msg_data['send_type'] = 1;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				}
			}
		}
	}	
	
	//订单拒绝改签  短信通知
	function send_order_reject_re_appoint_sms($order_info,$re_appoint_refuse_reason)
	{
		if(app_conf("SMS_ON")==1)
		{
			//$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
			if($order_info)
			{
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);
				$mobile = $order_info['appoint_mobile'];
				if (empty($mobile)){
					$mobile = $user_info['mobile'];
				}
					
				if(!empty($mobile))
				{
					//{$order.user_name}你好,你所下订单{$order.order_sn},拒绝改签,原因:{$order.re_appoint_refuse_reason}
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_ORDER_REJECT_RE_APPOINT'");
					$tmpl_content = $tmpl['content'];
	
					$order_info['user_name'] = $user_info['user_name'];
					$order_info['order_sn'] = $order_info['sn'];
					$order_info['re_appoint_refuse_reason'] = $re_appoint_refuse_reason;
	
					$GLOBALS['tmpl']->assign("order",$order_info);
	
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
	
					$msg_data['dest'] = $mobile;
	
					$msg_data['send_type'] = 0;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	
				}
			}
		}
	}
	
	//订单拒绝改签  发邮件 通知
	function send_order_reject_re_appoint_mail($order_info,$re_appoint_refuse_reason)
	{
		if(app_conf("MAIL_ON")==1)
		{
			//$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
			if($order_info)
			{
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);
				$email = $order_info['appoint_email'];
				if (empty($email)){
					$email = $user_info['email'];
				}
	
				if(!empty($email))
				{
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_ORDER_REJECT_RE_APPOINT'");
					$tmpl_content = $tmpl['content'];
	
					$order_info['user_name'] = $user_info['user_name'];
					$order_info['order_sn'] = $order_info['sn'];
					$order_info['re_appoint_refuse_reason'] = $re_appoint_refuse_reason;
	
					$GLOBALS['tmpl']->assign("order",$order_info);
	
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
	
					$msg_data['dest'] = $email;
	
					$msg_data['send_type'] = 1;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = get_gmtime();
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				}
			}
		}
	}
	
	//过滤
	function filter_ma_request($str){
		$search = array("../","\n","\r","\t","\r\n","'","<",">","\"");
			
		return str_replace($search,"",$str);
	}
?>