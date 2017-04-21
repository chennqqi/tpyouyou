<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------

class App{		
	private $module_obj;
	//网站项目构造
	public function __construct(){		
		$module_name = $GLOBALS['module']."Module";
		$this->module_obj = new $module_name;
		$this->module_obj->$GLOBALS['action']();
	}
	
	public function __destruct()
	{
		unset($this);
	}
}
?>