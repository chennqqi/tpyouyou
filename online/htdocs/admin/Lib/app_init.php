<?php
require_once 'common.php';
es_session::start();
if(!isset($_REQUEST['ajax']))$_REQUEST['ajax'] = 0 ;
if(!file_exists(APP_ROOT_PATH.'public/runtime/'.APP_NAME.'/'))
{
	mkdir(APP_ROOT_PATH.'public/runtime/'.APP_NAME.'/',0777);
}

//定义模板引擎
require  APP_ROOT_PATH.'system/template/template.php';
if(!file_exists(APP_ROOT_PATH.'public/runtime/'.APP_NAME.'/tpl_caches/'))
	mkdir(APP_ROOT_PATH.'public/runtime/'.APP_NAME.'/tpl_caches/',0777);
if(!file_exists(APP_ROOT_PATH.'public/runtime/'.APP_NAME.'/tpl_compiled/'))
	mkdir(APP_ROOT_PATH.'public/runtime/'.APP_NAME.'/tpl_compiled/',0777);
$tmpl = new AppTemplate;

$GLOBALS['tmpl']->cache_dir      = APP_ROOT_PATH . 'public/runtime/'.APP_NAME.'/tpl_caches';
$GLOBALS['tmpl']->compile_dir    = APP_ROOT_PATH . 'public/runtime/'.APP_NAME.'/tpl_compiled';
$GLOBALS['tmpl']->template_dir   = APP_ROOT_PATH.APP_NAME.'/Tpl';

//输出根路径
$GLOBALS['tmpl']->assign("APP_ROOT",APP_ROOT);
//定义模板路径
$tmpl_path = SITE_DOMAIN.APP_ROOT."/".APP_NAME."/Tpl";
$GLOBALS['tmpl']->assign("TMPL",$tmpl_path);
$GLOBALS['tmpl']->assign("TMPL_REAL",APP_ROOT_PATH.APP_NAME."/Tpl"); 
define("TMPL_REAL",APP_ROOT_PATH.APP_NAME."/Tpl");
//定义模板路径

//后台不用重写地址的解析

//定义前端程序的入口解析
$lang = require APP_ROOT_PATH.APP_NAME.'/Lang/'.app_conf("SITE_LANG").'/lang.php';
require APP_ROOT_PATH.APP_NAME.'/Lib/App.class.php';
require APP_ROOT_PATH.APP_NAME.'/Lib/BaseModule.class.php';
$module = addslashes(strtolower(!empty($_REQUEST['ctl'])?$_REQUEST['ctl']:"index"));
$action = addslashes(strtolower(!empty($_REQUEST['act'])?$_REQUEST['act']:"index"));
if(!file_exists(APP_ROOT_PATH.APP_NAME."/Lib/modules/".$module."Module.class.php"))
showErr("invalid access");
require_once APP_ROOT_PATH.APP_NAME."/Lib/modules/".$module."Module.class.php";				
if(!class_exists($module."Module"))
{
	showErr("invalid access");
}
else
{
	if(!method_exists($module."Module",$action))
	showErr("invalid access");
}
define("MODULE_NAME",$module);
define("ACTION_NAME",$action);
//定义前端程序的入口解析

//定义当前语言包
if(file_exists(APP_ROOT_PATH.APP_NAME.'/Lang/'.app_conf("SITE_LANG")."/".MODULE_NAME."_lang.php"))
{
	$module_lang = require APP_ROOT_PATH.APP_NAME.'/Lang/'.app_conf("SITE_LANG")."/".MODULE_NAME."_lang.php";
	$lang = array_merge($lang,$module_lang);
}
$GLOBALS['tmpl']->assign("LANG",$lang);
//end 定义当前语言包


?>