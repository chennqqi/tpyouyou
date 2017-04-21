<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class api_callbackModule extends BaseModule
{
	public function index()
	{
		$class = strim($_REQUEST['act']);
		$c = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."api_login where class_name = '".$class."'");
		$c_name = $class."_api";
		$file = APP_ROOT_PATH."system/api_login/".$c_name.".php";
		require_once $file;
		$c_object = new $c_name($c);
		$c_object->callback();
	}
}
?>