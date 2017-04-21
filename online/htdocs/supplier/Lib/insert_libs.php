<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------

/*以下为动态载入的函数库*/

function insert_verifyimg($p)
{
	$vid = $p['vid'];
	$w = $p['w'];
	$h = $p['h'];
	$imgurl = APP_ROOT."/verify.php?vid=".$vid."&w=".$w."&h=".$h;
	$html = '<img src="'.$imgurl.'&r='.rand().'" rel="'.$imgurl.'" title="看不清楚？换一张" style="cursor:pointer; margin-right:5px;" onclick="refresh_verify(this);"  />';
	return $html;
}
?>