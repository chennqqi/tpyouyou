<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class uploadModule extends BaseModule
{
	public function avatar()
	{
		global_run();
		if(empty($GLOBALS['user']))
		{
			showErr("请先登录",1,url("user#login"));
		}
		$msg = $this->upload("avatar",false);
		if($msg['error']==0)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."user set avatar = '".$msg['url']."' where id = ".$GLOBALS['user']['id']);
			$data['status'] = 1;
			$data['url'] = APP_ROOT."/".get_spec_image($msg['url'],100,100,1);
			
			ajax_return($data);
		}
		else
		{
			showErr($msg['message'],1);
		}
		
	}
	public function guide()
	{		
		global_run();
		$msg = $this->upload("guide",true);
                
                $msg['web_url'] = APP_ROOT."/".get_spec_image($msg['url'],160,88,1);
 
		ajax_return($msg);
	}
        
        public function review()
	{	
		global_run();
		
		if(empty($GLOBALS['user']))
		{
			$msg['error'] = -1;
                        ajax_return($msg);
                        exit;
		}
                //return;
		
		$msg = $this->upload("review",true);
		ajax_return($msg);
	}
	
	
	/**
	 * 开放单文件上传控件上传的脚本
	 */
	function upload($dir,$allow_water = true)
	{
		//上传处理
		logger::write($dir,logger::ERR,logger::FILE,"upload");
		$dir = "images/web/".$dir;
		if (!is_dir(APP_ROOT_PATH."public/".$dir."/")) {
			@mkdir(APP_ROOT_PATH."public/".$dir."/");
			@chmod(APP_ROOT_PATH."public/".$dir."/", 0777);
		}
	
		$dir = $dir."/".to_date(NOW_TIME,"Ym");
		if (!is_dir(APP_ROOT_PATH."public/".$dir."/")) {
			@mkdir(APP_ROOT_PATH."public/".$dir."/");
			@chmod(APP_ROOT_PATH."public/".$dir."/", 0777);
		}
	
		$dir = $dir."/".to_date(NOW_TIME,"d");
		if (!is_dir(APP_ROOT_PATH."public/".$dir."/")) {
			@mkdir(APP_ROOT_PATH."public/".$dir."/");
			@chmod(APP_ROOT_PATH."public/".$dir."/", 0777);
		}
	
		$dir = $dir."/".to_date(NOW_TIME,"H");
		if (!is_dir(APP_ROOT_PATH."public/".$dir."/")) {
			@mkdir(APP_ROOT_PATH."public/".$dir."/");
			@chmod(APP_ROOT_PATH."public/".$dir."/", 0777);
		}
	
		//创建原始文件目录
		if (!is_dir(APP_ROOT_PATH."public/".$dir."/origin/")) {
			@mkdir(APP_ROOT_PATH."public/".$dir."/origin/");
			@chmod(APP_ROOT_PATH."public/".$dir."/origin/", 0777);
		}
	
		//保存图片
		$res = $this->saveimage($dir,$allow_water);

		if($res['error']==1)
		{
			$msg = $res;
		}
		else
		{
			$msg['error'] = 0;
			$msg['web_url'] =str_replace("./public/",SITE_DOMAIN.APP_ROOT."/public/",$res['url']); //恢复成绝对路径，在提交时处理成./public/
			$msg['url'] = $res['url']; //恢复成绝对路径，在提交时处理成./public/
                        $msg['width'] = $res['width'];
                        $msg['height'] = $res['height'];
		}
		return $msg;
	}
	
	function saveimage($dir,$allow_water=true)
	{
		//image object
		require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
		$image = new es_imagecls();
		$image->max_size = intval(app_conf("MAX_IMAGE_SIZE"));
	
		$img_item;
		$image->init($_FILES['Filedata'],$dir);

		if($image->save()){
			$img_item['url'] = $image->file['target'];
			$img_item['path'] = $image->file['local_target'];
			$img_item['name'] = $image->file['prefix'];
                        $img_item['width'] = $image->file['width'];
                        $img_item['height'] = $image->file['height'];
		}else{
			if($image->error_code==-105)
			{
				return array('error'=>1,'message'=>'上传的图片太大');
			}
			elseif($image->error_code==-104||$image->error_code==-103||$image->error_code==-102||$image->error_code==-101)
			{
				return array('error'=>1,'message'=>'非法图像');
			}
			exit;
		}
	
		if($allow_water&&intval(app_conf("IS_WATER_MARK")))
			$is_water = intval(app_conf("IS_WATER_MARK"));
	
		$water_image = APP_ROOT_PATH.app_conf("WATER_MARK");
		$alpha = intval(app_conf("WATER_ALPHA"));
		$place = intval(app_conf("WATER_POSITION"));
	
		if($is_water)
		{
			$dirs = pathinfo($img_item['url']);
			$dir = $dirs['dirname'];
			$dir = $dir."/origin/";
			$paths = pathinfo($img_item['path']);
			$path = $paths['dirname'];
			$path = $path."/origin/";
			if (!is_dir($path)) {
				@mkdir($path);
				@chmod($path, 0777);
			}
			
			$filename = $paths['basename'];
			@file_put_contents($path.$filename,@file_get_contents($img_item['path']));
			$image->water($img_item['path'],$water_image,$alpha, $place);
			if(app_conf("PUBLIC_DOMAIN_ROOT")!='')
			{
				syn_to_remote_image_server($img_item['url']); //同步水印图
				syn_to_remote_image_server($dir.$filename); //同步原图
			}
		}
		else
		{
			if(app_conf("PUBLIC_DOMAIN_ROOT")!='')
			{
				syn_to_remote_image_server($img_item['url']);
			}
		}
	
		return $img_item;
	}

	
	
}
?>