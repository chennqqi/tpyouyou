<?php
require_once 'common.php';
filter_injection($_REQUEST);  //过滤SQL注入的条件

//创建当前项目所需的缓存目录
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
$GLOBALS['tmpl']->template_dir   = APP_ROOT_PATH.APP_NAME.'/Tpl/' . app_conf("TEMPLATE");

//输出根路径
$GLOBALS['tmpl']->assign("APP_ROOT",APP_ROOT);
//定义模板路径
$tmpl_path = SITE_DOMAIN.APP_ROOT."/".APP_NAME."/Tpl/";
$GLOBALS['tmpl']->assign("TMPL",$tmpl_path.app_conf("TEMPLATE"));
$GLOBALS['tmpl']->assign("TMPL_REAL",APP_ROOT_PATH.APP_NAME."/Tpl/".app_conf("TEMPLATE")); 
define("TMPL_REAL",APP_ROOT_PATH.APP_NAME."/Tpl/".app_conf("TEMPLATE"));
//定义模板路径


if(app_conf("URL_MODEL")==1)
{	
	$domain = get_host();
	if(strpos($domain,".".app_conf("DOMAIN_ROOT")))
	{
		$sub_domain = str_replace(".".app_conf("DOMAIN_ROOT"),"",$domain);
		if($sub_domain!='')
		{
			if($GLOBALS['domain_cfg'][$sub_domain]==1)
			{
				$city_py = $sub_domain;
			}
			elseif ($GLOBALS['domain_cfg'][$sub_domain]==2)
			{
				$module_domain = $sub_domain;
			}
		}
	}
	//重写模式
	$current_url = APP_ROOT;
	$_GET['ctl'] = $module_domain;
	if(isset($_REQUEST['rewrite_param']))
		$rewrite_param = $_REQUEST['rewrite_param'];
	else
		$rewrite_param = "";
	$rewrite_param = explode("/",$rewrite_param);
	$rewrite_param_array = array();
	foreach($rewrite_param as $k=>$param_item)
	{
		if($param_item!='')
			$rewrite_param_array[] = $param_item;
	}
	$ma = array();
	if($module_domain)$ma[] = $module_domain;
	foreach ($rewrite_param_array as $k=>$v)
	{
		$param_array = explode("-", $v);
		if(!is_array($param_array)||count($param_array)==1)
		{
			//解析ctl或act
			if(empty($module_domain))
			{
				if($k==0)
				{
					$_GET['ctl'] = strim($v);						
					$current_url.="/".$v;
					$ma[] = strim($v);
				}
				if($k==1)
				{
					$_GET['act'] = strim($v);
					$current_url.="/".$v;
					$ma[] = strim($v);
				}
				
			}
			else
			{					
				
				if($k==0)
				{
					$_GET['act'] = strim($v);
					$current_url.="/".$v;
					$ma[] = strim($v);
				}
			}	
		}
	}
	$current_url.="/";
	foreach ($rewrite_param_array as $k=>$v)
	{
			$param_array = explode("-", $v);
			if(is_array($param_array)&&count($param_array)>1)
			{
				//扩展参数
				$ext_param = explode("-",$v);
				foreach($ext_param as $kk=>$vv)
				{
					if($kk%2==0)
					{
						if(preg_match("/(\w+)\[(\w+)\]/",$vv,$matches))
						{
							$_GET[$matches[1]][$matches[2]] = $ext_param[$kk+1];
						}
						else
							$_GET[$ext_param[$kk]] = $ext_param[$kk+1];
							
						if($ext_param[$kk]!="p"&&!in_array($ext_param[$kk],$ma))
						{
							$current_url.=$ext_param[$kk]."-";
							$current_url.=$ext_param[$kk+1]."-";
						}
					}
				}
			}
				
		
	}//foreach
	$current_url = substr($current_url,-1)=="-"?substr($current_url,0,-1):$current_url;

	
}
unset($_REQUEST['rewrite_param']);
unset($_GET['rewrite_param']);

$_REQUEST = array_merge($_GET,$_POST);

//定义前端程序的入口解析
require APP_ROOT_PATH.APP_NAME.'/Lib/App.class.php';
require APP_ROOT_PATH.APP_NAME.'/Lib/BaseModule.class.php';
$module = addslashes(strtolower(!empty($_GET['ctl'])?$_GET['ctl']:"index"));
$action = addslashes(strtolower(!empty($_GET['act'])?$_GET['act']:"index"));
if(!file_exists(APP_ROOT_PATH.APP_NAME."/Lib/modules/".$module."Module.class.php"))
$module = "index";
require_once APP_ROOT_PATH.APP_NAME."/Lib/modules/".$module."Module.class.php";				
if(!class_exists($module."Module"))
{
	$module = "index";
	require_once APP_ROOT_PATH.APP_NAME."/Lib/modules/".$module."Module.class.php";	
}
if(!method_exists($module."Module",$action))
$action = "index";
define("MODULE_NAME",$module);
define("ACTION_NAME",$action);
//定义前端程序的入口解析

//定义当前语言包
$lang = require APP_ROOT_PATH.APP_NAME.'/Lang/'.app_conf("SITE_LANG").'/lang.php';
if(file_exists(APP_ROOT_PATH.APP_NAME.'/Lang/'.app_conf("SITE_LANG")."/".MODULE_NAME."_lang.php"))
{
	$module_lang = require APP_ROOT_PATH.APP_NAME.'/Lang/'.app_conf("SITE_LANG")."/".MODULE_NAME."_lang.php";
	$lang = array_merge($lang,$module_lang);
}
$GLOBALS['tmpl']->assign("LANG",$lang);
//end 定义当前语言包


//引入用户类库
require_once APP_ROOT_PATH."system/libs/user.php";
$user = null;

//引入城市定位
require_once APP_ROOT_PATH."system/libs/city.php";
$city = null;

//定义来路与推荐人
//保存返利的cookie
if($_REQUEST['ref_pid'])
{
	$ref_pid = intval(base64_decode($_REQUEST['ref_pid']));
	$ref_pid = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where id = ".intval($ref_pid)));
	es_cookie::set("REFERRAL_USER",intval($ref_pid));
}
else
{
	//获取存在的推荐人ID
	if(intval(es_cookie::get("REFERRAL_USER"))>0)
	$ref_pid = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where id = ".intval(es_cookie::get("REFERRAL_USER"))));
}


//保存来路
if(!es_cookie::get("referer_url"))
{	
	if(!preg_match("/".urlencode(SITE_DOMAIN.APP_ROOT)."/",urlencode($_SERVER["HTTP_REFERER"])))
	es_cookie::set("referer_url",$_SERVER["HTTP_REFERER"]);
}
$ref = strim(es_cookie::get("referer_url"));

$GLOBALS['tmpl']->assign("ajax_login_url",url("user#ajax_login"));

$GLOBALS['tmpl']->assign("user_tip_url",url("user#user_tip"));

$GLOBALS['tmpl']->assign("check_user_url",url("user#check_login"));
$GLOBALS['tmpl']->assign("user_follow_url",url("user#user_follow"));
$GLOBALS['tmpl']->assign("user_follows_url",url("user#user_follows"));
$GLOBALS['tmpl']->assign("remove_fans_url",url("user#remove_fans"));



?>