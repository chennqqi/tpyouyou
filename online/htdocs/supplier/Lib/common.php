<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------

/**
 * 初始化页面变量
 */
function init_app_page()
{
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
}

//显示错误
function showErr($msg,$ajax=0,$jump='')
{
	
	if($ajax==1)
	{
		$result['statusCode'] = 300;
		$result['message'] = $msg;
		if($jump!="")
		{
			$result['callbackType']	= 'forward';
			$result['forwardUrl'] = $jump;
		}
		header("Content-Type:text/html; charset=utf-8");
        echo(json_encode($result));exit;
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
		$jump = APP_ROOT."/".ADMINFILE;
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->display("core/include/error.html");
		exit;
	}
}

//显示成功
function showSuccess($msg,$ajax=0,$jump='')
{
	
	if($ajax==1)
	{
		$result['statusCode'] = 200;
		$result['message'] = $msg;
		if($jump!="")
		{
			$result['callbackType']	= 'forward';
			$result['forwardUrl'] = $jump;
		}
		header("Content-Type:text/html; charset=utf-8");
        echo(json_encode($result));exit;
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
		$jump = APP_ROOT."/".ADMINFILE;
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->display("core/include/success.html");
		exit;
	}
}

function get_gopreview()
{
		$gopreview = es_session::get("admin_gopreview");
		if($gopreview==get_current_url())
		{
			$gopreview = url("index");
		}
		return $gopreview;
}

function get_current_url()
{
	$url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?");   
    $parse = parse_url($url);
    if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            $url   =  $parse['path'].'?'.http_build_query($params);
    }
    return $url;
}

function set_gopreview()
{
	$url =  get_current_url();
	es_session::set("admin_gopreview",$url); 
}	
function app_redirect_preview()
{
	app_redirect(get_gopreview());
}	

//显示语言
// lang($key,p1,p2......) 用于格式化 sprintf %s
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
 * url 函数，用于admin项目
 * @param string $route 如: deal#cate
 * @param array $param 参数 如:array("id"=>1,"name"=>"名称");
 * @return string
 */
function admin_url($route="index",$param=array())
{
		$route_array = explode("#",$route);
	
		if(isset($param)&&$param!=''&&!is_array($param))
		{
			$param['id'] = $param;
		}
	
		$module = isset($route_array[0])?strtolower(trim($route_array[0])):"";
		$action = isset($route_array[1])?strtolower(trim($route_array[1])):"";
	
		if(!$module||$module=='index')$module="";
		if(!$action||$action=='index')$action="";


		//原始模式
		$url = APP_ROOT."/".ADMINFILE;
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
		return $url;

}


/**
 * 验证并返回有效的in条件用的ID字符串
 * @param string $idstr
 */
function format_ids_str($idstr)
{
	$ids = explode(',', $idstr);
	if(count($ids)>0)
	{
		foreach ($ids as $k=>$v)
		{
			$ids[$k] = intval($v);
		}
		$ids = implode(',', $ids);
		return $ids;
	}
	else
	return false;

}


function load_keimg($name,$value)
{
	$html = "<span>
				 <a href='%s' target='_blank' id='keimg_a_%s' class='%s'><img src='%s' id='keimg_m_%s' width=50 height=50 style='float:left; border:#ccc solid 1px; margin-right:5px;' /></a>   
				 <div style='float:left; height:50px; padding-top:12px;'>
					<input type='hidden' value='%s' name='%s' id='keimg_h_%s' />
					<div class='buttonActive' style='margin-right:5px;'>
						<div class='buttonContent'>
							<button type='button' class='keimg' rel='%s'>选择图片</button>
						</div>
					</div>
					<div class='buttonActive'>
						<div class='buttonContent'>
							<button type='button' class='keimg_d' rel='%s'>删除图片</button>
						</div>
					</div>
				</div>
				</span>";

	$cls = "";
	if(empty($value))$cls="hide";
	$html = sprintf($html,$value,$name,$cls,$value,$name,$value,$name,$name,$name,$name);
	return $html;
}

function load_mukeimg($name,$value)
{
	$html = "<div>
				<div class='buttonActive' style='margin-right:5px;'>
					<div class='buttonContent'>
						<button type='button' class='kemukeimg' rel='$name'>批量上传图片</button>
					</div>
				</div>
				<div class='buttonActive' style='margin-right:5px;'>
					<div class='buttonContent'>
						<button type='button' class='kemufimg' rel='$name'>服务器图片</button>
					</div>
				</div>
				<div style='clear:both;height:1px;overflow:hidden;overflow:visible'></div>";
				 if($value){
					foreach($value as $k=>$v){
						$html .="<div style='float:left; height:80px; padding:10px 5px;'>
									<input type='hidden' value='".$v."' name='".$name."[]' />
									<a href='".$v."' target='_blank'><img src='".$v."' width=60 height=60 style='float:left; border:#ccc solid 1px;' /></a>
									<div style='clear:both;height:5px;overflow:hidden;overflow:visible'></div>
									<div class='buttonActive'>
										<div class='buttonContent'>
											<button type='button' class='kemuimg_d'>删除图片</button>
										</div>
									</div>
								</div>"; 
				 	}
				}
		   $html .="</div><div style='clear:both;height:10px;overflow:hidden;overflow:visible'></div>";
	
	return $html;
}


function load_kemap($lat='',$lng='',$w='300',$h='300',$type=MAPTYPE){
	if($w=="")
		$w = "200";
	if($h=="")
		$h="200";
	$html = "<div>
				<div class='buttonActive' style='margin-right:5px;'>
					<div class='buttonContent'>
						<button type='button' class='kemapbtn' map='".$type."' w='".$w."' h='".$h."'>地图定位</button>
					</div>
				</div>
				<div style='clear:both;height:1px;overflow:hidden;overflow:visible'></div>
				<input type='hidden' value='".$lat."' name='xpoint' id='xpoint' />
				<input type='hidden' value='".$lng."' name='ypoint' id='ypoint' />
				<div class='mapimg' id='mapimg' style='margin-left:0;width:".$w."px;height:".$h."px;'>";
			if($lat!=""&&$lng!=""){
				if($type=="map")
					$html.="<img src='http://maps.googleapis.com/maps/api/staticmap?center=".$lat."%2C".$lng."&zoom=11&size=".$w."x".$h."&maptype=roadmap&markers=".$lat."%2C".$lng."&language=zh_CN&sensor=false' />";
				else
					$html.="<img src='http://api.map.baidu.com/staticimage?center=".$lng."%2C".$lat."&zoom=13&width=300&height=300&markers=".$lng."%2C".$lat."&markerStyles=l%2CA' />";
			}	
	$html.= "	</div>		
			 </div>";
				
	return $html;			
}


function load_kefile($name,$value)
{
	$html = "<div class=\"upload\">
					<input class=\"ke-input-text filebox \" type=\"text\" id=\"".$name."url\" value=\"".$value."\" name=\"".$name."\" readonly=\"readonly\" />
					<input type=\"button\"   class='uploadfilebtn' value=\"".lang("UPLOAD")."\" />";
	if($value!="")
	{
		$html.="<span class=\"ke-button-common viewbox \"  ><a target='_blank'  href=\"".$value."\"    class=\"ke-button-common ke-button view \">".lang("VIEW")."</a></span>";
		$html.="<span class=\"ke-button-common delbox \"  ><a  href='javascript:void(0);' onclick='delfile(this)' class=\"ke-button-common ke-button\">".lang("DEL")."</a></span>";
	}
	else
	{
		$html.="<span class=\"ke-button-common viewbox \" style='display:none;'  ><a target='_blank'  href=\"".$value."\" class=\"ke-button-common ke-button view \">".lang("VIEW")."</a></span>";
		$html.="<span class=\"ke-button-common delbox \" style='display:none;'  ><a  href='javascript:void(0);' onclick='delfile(this)' class=\"ke-button-common ke-button\">".lang("DEL")."</a></span>";
	}
	$html.="</div>";
	return $html;

}
/**
 * 格式化编辑器提交 ,还原为./public/
 */
function format_domain_to_relative($content)
{
	//对图片路径的修复
	$domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?SITE_DOMAIN.APP_ROOT."/":app_conf("PUBLIC_DOMAIN_ROOT")."/";
	$content = str_replace($domain,"./",$content);	
	$content =  str_replace(SITE_DOMAIN.APP_ROOT."/","./",$content);
	return $content;
}



function check_empty($key)
{
	if(!isset($_REQUEST[$key]))return false;
	if(empty($_REQUEST[$key]))return false;
	if(trim($_REQUEST[$key])=="")return false;
	return true;
}

function get_status($k)
{
	if($k==0)
		return lang("NO");
	else 
		return lang("YES");
}

function get_all_files( $path )
{
	$list = array();
	$dir = @opendir($path);
	while (false !== ($file = @readdir($dir)))
	{
		if($file!='.'&&$file!='..')
			if( is_dir( $path.$file."/" ) ){
			$list = array_merge( $list , get_all_files( $path.$file."/" ) );
		}
		else
		{
			$list[] = $path.$file;
		}
	}
	@closedir($dir);
	return $list;
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
?>