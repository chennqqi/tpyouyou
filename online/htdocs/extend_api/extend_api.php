<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(88522820@qq.com)
// +----------------------------------------------------------------------
//用于处理 扩展商品api 如（去哪儿api）
require '../system/system_init.php';
require '../web/Lib/common.php';
require '../system/libs/tourline.php';

function convertUrl($url)
{
	$url = str_replace("&","&amp;",$url);
	return $url;
}

function formatImageUrl($out)
{
	$domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?SITE_DOMAIN.APP_ROOT:app_conf("PUBLIC_DOMAIN_ROOT");
	        $out = str_replace(APP_ROOT."./public/images/",$domain."/public/images/",$out);	
	        $out = str_replace("./public/images/",$domain."/public/images/",$out);	
	return $out;
}
function emptyTag($string)
{
		if(empty($string))
			return "";
			
		$string = strip_tags(trim($string));
		$string = preg_replace("|&.+?;|",'',$string);
		
		return $string;
}
function split_day($content)
{
	$content = strip_tags($content);
	$content = preg_replace("/第\S+天/", "-~!@#\$0", $content);
	$content = preg_replace("/\s+/", "", $content);
	$content = explode("-~!@#",$content);
	
	$res = array();
	foreach($content as $k=>$v)
	{
		if($k>0)
			$res[] = $v;
	}
	return $res;
}
?>