<?php 
// +----------------------------------------------------------------------
// | Fanwe 系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------

define("APP_NAME", "supplier");
if(!defined("ADMIN_ROOT"))
{
	die("Invalid access");
}
require './system/system_init.php';

$url = _PHP_FILE_;
$filename= substr( $url , strrpos($url , '/')+1 );
define("ADMINFILE",$filename);//当前文件名

require './supplier/Lib/app_init.php';
define("ADMIN_PAGE_SIZE",20); //每页显示的条数;
define("ADMIN_PAGE_SIZE1",20); //每页显示的条数;
define("ADMIN_PAGE_SIZE2",40); //每页显示的条数;
define("ADMIN_PAGE_SIZE3",100); //每页显示的条数;
define("ADMIN_PAGE_SIZE4",200); //每页显示的条数;
//实例化一个网站应用实例
$AppWeb = new App(); 

?>