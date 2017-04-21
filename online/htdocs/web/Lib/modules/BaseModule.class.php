<?php 

// +----------------------------------------------------------------------

// | Fanwe 乐程旅游b2b

// +----------------------------------------------------------------------

// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.

// +----------------------------------------------------------------------

// | Author: 同创网络(778251855@qq.com)

// +----------------------------------------------------------------------



class BaseModule{

	public function __construct()

	{

		$adm_session = es_session::get(md5(app_conf("AUTH_KEY")."supplier"));

		$adm_name = $adm_session['user_name'];

		$adm_id = intval($adm_session['id']);
          print_r($adm_id);
	    

		if(intval(app_conf("SITE_STATUS"))==0)

		{

			//输出SEO元素

			$GLOBALS['tmpl']->assign("site_name",app_conf("SITE_NAME"));

			$GLOBALS['tmpl']->assign("site_title",app_conf("SITE_TITLE"));

			$GLOBALS['tmpl']->assign("site_keyword",app_conf("SITE_KEYWORD"));

			$GLOBALS['tmpl']->assign("site_description",app_conf("SITE_DESCRIPTION"));

			$GLOBALS['tmpl']->display("site_close.html");

			exit;

		}

		

		if(app_conf("PUBLIC_DOMAIN_ROOT")!='')

		{

			global $syn_image_ci;

			global $curl_param;

			//global $syn_image_idx;

			$syn_image_idx = 0;

			$syn_image_ci  =  curl_init(app_conf("PUBLIC_DOMAIN_ROOT")."/es_file.php");

			curl_setopt($syn_image_ci, CURLOPT_RETURNTRANSFER, true);

			curl_setopt($syn_image_ci, CURLOPT_SSL_VERIFYPEER, false);

			curl_setopt($syn_image_ci, CURLOPT_SSL_VERIFYHOST, false);

			curl_setopt($syn_image_ci, CURLOPT_NOPROGRESS, true);

			curl_setopt($syn_image_ci, CURLOPT_HEADER, false);

			curl_setopt($syn_image_ci, CURLOPT_POST, TRUE);

			curl_setopt($syn_image_ci, CURLOPT_TIMEOUT, 1);

			curl_setopt($syn_image_ci, CURLOPT_TIMECONDITION, 1);

			$curl_param['username'] = app_conf("IMAGE_USERNAME");

			$curl_param['password'] = app_conf("IMAGE_PASSWORD");

			$curl_param['act'] = 2;

		}

		

		$GLOBALS['tmpl']->assign("MODULE_NAME",MODULE_NAME);

		$GLOBALS['tmpl']->assign("ACTION_NAME",ACTION_NAME);

		

		if(!file_exists(APP_ROOT_PATH."public/runtime/".APP_NAME."/pagecache/"))

			mkdir(APP_ROOT_PATH."public/runtime/".APP_NAME."/pagecache/",0777);

		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/".APP_NAME."/pagecache/page_static_cache/");

		$GLOBALS['dynamic_cache'] = $GLOBALS['fcache']->get("APP_DYNAMIC_CACHE_".MODULE_NAME."_".ACTION_NAME);	



		//开始设置回跳

		if(

				MODULE_NAME=="account"&&ACTION_NAME=="money"||

				MODULE_NAME=="account"&&ACTION_NAME=="deposit"||

				MODULE_NAME=="account"&&ACTION_NAME=="money_log"||

				MODULE_NAME=="account"&&ACTION_NAME=="voucher"||

				MODULE_NAME=="account"&&ACTION_NAME=="myvoucher"||

				MODULE_NAME=="account"&&ACTION_NAME=="voucher_log"||

				MODULE_NAME=="account"&&ACTION_NAME=="score"||

				MODULE_NAME=="around"&&ACTION_NAME=="index"||

				MODULE_NAME=="ask"&&ACTION_NAME=="uc_ask_list"||

				MODULE_NAME=="diy"&&ACTION_NAME=="index"||

				MODULE_NAME=="domestic"&&ACTION_NAME=="index"||

				MODULE_NAME=="drive"&&ACTION_NAME=="index"||

				MODULE_NAME=="guide"&&ACTION_NAME=="index"||

				MODULE_NAME=="guide"&&ACTION_NAME=="writethread"||

				MODULE_NAME=="guide"&&ACTION_NAME=="show"||

				MODULE_NAME=="guide"&&ACTION_NAME=="uc_guide_list"||

				MODULE_NAME=="guide"&&ACTION_NAME=="uc_guide_item"||

				MODULE_NAME=="help"&&ACTION_NAME=="index"||

				MODULE_NAME=="help"&&ACTION_NAME=="show"||

				MODULE_NAME=="index"&&ACTION_NAME=="index"||

				MODULE_NAME=="link"&&ACTION_NAME=="index"||

				MODULE_NAME=="index"&&ACTION_NAME=="index"||

				MODULE_NAME=="news"&&ACTION_NAME=="index"||

				MODULE_NAME=="news"&&ACTION_NAME=="cat"||

				MODULE_NAME=="news"&&ACTION_NAME=="show"||

				MODULE_NAME=="outbound"&&ACTION_NAME=="index"||

				MODULE_NAME=="profile"&&ACTION_NAME=="index"||

				MODULE_NAME=="profile"&&ACTION_NAME=="consignee"||

				MODULE_NAME=="profile"&&ACTION_NAME=="namelist"||

				MODULE_NAME=="profile"&&ACTION_NAME=="namelist_add"||

				MODULE_NAME=="profile"&&ACTION_NAME=="namelist_edit"||

				MODULE_NAME=="spot"&&ACTION_NAME=="index"||

				MODULE_NAME=="spot"&&ACTION_NAME=="cat"||

				MODULE_NAME=="spot"&&ACTION_NAME=="view"||

				MODULE_NAME=="ticket_order"&&ACTION_NAME=="index"||

				MODULE_NAME=="tourline_order"&&ACTION_NAME=="index"||

				MODULE_NAME=="tourlist"&&ACTION_NAME=="index"||

				MODULE_NAME=="tourlist"&&ACTION_NAME=="around"||

				MODULE_NAME=="tours"&&ACTION_NAME=="index"||

				MODULE_NAME=="tours"&&ACTION_NAME=="view"||

				MODULE_NAME=="transaction"&&ACTION_NAME=="pay"||

				MODULE_NAME=="transaction"&&ACTION_NAME=="done"||

				MODULE_NAME=="transaction"&&ACTION_NAME=="order_save_success"||

				MODULE_NAME=="tuan"&&ACTION_NAME=="index"||

				MODULE_NAME=="tuan"&&ACTION_NAME=="history"||

				MODULE_NAME=="tuan"&&ACTION_NAME=="advance"||

				MODULE_NAME=="tuan"&&ACTION_NAME=="search"||

				MODULE_NAME=="tuan"&&ACTION_NAME=="detail"||

				MODULE_NAME=="uc_order"&&ACTION_NAME=="index"||

				MODULE_NAME=="uc_order"&&ACTION_NAME=="tourline_order"||

				MODULE_NAME=="uc_order"&&ACTION_NAME=="tourline"||

				MODULE_NAME=="uc_order"&&ACTION_NAME=="ticket_order"||

				MODULE_NAME=="uc_order"&&ACTION_NAME=="ticket"||

				MODULE_NAME=="uc_order"&&ACTION_NAME=="refundticket"||

				MODULE_NAME=="user"&&ACTION_NAME=="index"

		)

		{

			set_gopreview();

		}

	}



	public function index()

	{

		showErr("invalid access");

	}

	public function __destruct()

	{

		if(isset($GLOBALS['fcache']))

		{

			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/".APP_NAME."/pagecache/page_static_cache/");

			$GLOBALS['fcache']->set("APP_DYNAMIC_CACHE_".MODULE_NAME."_".ACTION_NAME,$GLOBALS['dynamic_cache']);

		}

		

		if(app_conf("PUBLIC_DOMAIN_ROOT")!='')

		{

			if(count($GLOBALS['curl_param']['images'])>0)

			{

				$GLOBALS['curl_param']['images'] =  base64_encode(serialize($GLOBALS['curl_param']['images']));

				curl_setopt($GLOBALS['syn_image_ci'], CURLOPT_POSTFIELDS, $GLOBALS['curl_param']);

				$rss = curl_exec($GLOBALS['syn_image_ci']);

			}

			curl_close($GLOBALS['syn_image_ci']);

		}

		

		unset($this);

	}

}

?>