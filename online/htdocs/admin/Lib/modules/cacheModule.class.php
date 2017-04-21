<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class cacheModule extends AuthModule
{
	public function index()
	{		
		$GLOBALS['tmpl']->assign("clear_dataallurl",admin_url("cache#clear_data",array("ajax"=>1,"is_all"=>1)));
		$GLOBALS['tmpl']->assign("clear_dataurl",admin_url("cache#clear_data",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("clear_imageurl",admin_url("cache#clear_image",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("clear_parse_fileurl",admin_url("cache#clear_parse_file",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("clear_adminurl",admin_url("cache#clear_admin",array("ajax"=>1)));
		
		$GLOBALS['tmpl']->display("core/cache/index.html");
	}	
	
	
	public function clear_data()
	{
		$ajax = intval($_REQUEST['ajax']);
		set_time_limit(0);
		es_session::close();
		if(intval($_REQUEST['is_all'])==0)
		{
			//数据缓存
			clear_dir_file(APP_ROOT_PATH."public/runtime/web/cache/");
			clear_dir_file(APP_ROOT_PATH."public/runtime/web/pagecache/");
			clear_dir_file(APP_ROOT_PATH."public/runtime/web/tpl_caches/");
			clear_dir_file(APP_ROOT_PATH."public/runtime/web/tpl_compiled/");
		}
		else
		{	
			clear_dir_file(APP_ROOT_PATH."public/runtime/admin/");
			clear_dir_file(APP_ROOT_PATH."public/runtime/autocache/");
			clear_dir_file(APP_ROOT_PATH."public/runtime/db_caches/");
			clear_dir_file(APP_ROOT_PATH."public/runtime/web/");	
			clear_dir_file(APP_ROOT_PATH."public/runtime/supplier/");
			$GLOBALS['db']->query("truncate table ".DB_PREFIX."auto_cache");
		}
		
		showSuccess(lang("CLEAR_SUCCESS"),$ajax);
		
	}
	
	
	
	public function clear_image()
	{
		$ajax = intval($_REQUEST['ajax']);
		set_time_limit(0);
		es_session::close();
		$path  = APP_ROOT_PATH."public/images/";
		$this->clear_image_file($path);
	
	
		clear_dir_file(APP_ROOT_PATH."public/runtime/web/tpl_caches/");
		clear_dir_file(APP_ROOT_PATH."public/runtime/web/tpl_compiled/");
		

		showSuccess(lang("CLEAR_SUCCESS"),$ajax);
	}
	
	private function clear_qrcode($path)
	{
	
		if ( $dir = opendir( $path ) )
		{
			while ( $file = readdir( $dir ) )
			{
				$check = is_dir( $path. $file );
				if ( !$check )
				{
					@unlink ( $path . $file);
				}
				else
				{
					if($file!='.'&&$file!='..')
					{
						$this->clear_qrcode($path.$file."/");
					}
				}
			}
			closedir( $dir );
			return true;
		}
	}
	
	private function clear_image_file($path)
	{
		if ( $dir = opendir( $path ) )
		{
			while ( $file = readdir( $dir ) )
			{
				$check = is_dir( $path. $file );
				if ( !$check )
				{
					if(preg_match("/_(\d+)x(\d+)/i",$file,$matches))
						@unlink ( $path . $file);
				}
				else
				{
					if($file!='.'&&$file!='..')
					{
						$this->clear_image_file($path.$file."/");
					}
				}
			}
			closedir( $dir );
			return true;
		}
	}
	
	
	public function clear_parse_file()
	{
		$ajax = intval($_REQUEST['ajax']);
		set_time_limit(0);
		es_session::close();
		clear_dir_file(APP_ROOT_PATH."public/runtime/web/statics/");
	
		clear_dir_file(APP_ROOT_PATH."public/runtime/web/tpl_caches/");
		clear_dir_file(APP_ROOT_PATH."public/runtime/web/tpl_compiled/");
		
		showSuccess(lang("CLEAR_SUCCESS"),$ajax);
	}
	
	public function clear_admin()
	{
		$ajax = intval($_REQUEST['ajax']);
		set_time_limit(0);
		es_session::close();
		clear_dir_file(APP_ROOT_PATH."public/runtime/admin/");
		clear_dir_file(APP_ROOT_PATH."public/runtime/supplier/");
	
		showSuccess(lang("CLEAR_SUCCESS"),$ajax);
	}
	
}
?>