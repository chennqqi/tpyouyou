<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class fileModule extends AuthModule
{
	/**
	 * 开放给kindeditor的单文件上传控件上传的脚本，$_FILES的key为imgFile
	 */
	public function upload()
	{		
		//上传处理
		//创建comment目录
		$dir = "images/upload";
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
		$is_water = intval($_REQUEST['is_water']);
		$res = $this->saveimage($dir,$is_water);		 
		if($res['error']==1)
		{
			$msg = $res;
		}
		else
		{
			$msg['error'] = 0;
			$msg['url'] =str_replace("./public/",SITE_DOMAIN.APP_ROOT."/public/",$res['imgFile']['url']); //恢复成绝对路径，在提交时处理成./public/
		}
		ajax_return($msg);		
	}	
	
	public function uploadfile()
	{
		//上传处理
		//创建comment目录
		$dir = "attachments/upload";
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
		
		
		$res = $this->savefile($dir);		 

		ajax_return($res);		
	}
	
	
	public function uploadflash()
	{
		//上传处理
		//创建comment目录
		$dir = "adv/flash";
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
	
	
		$res = $this->saveswf($dir);
	
		ajax_return($res);
	}
	
	public function uploadvideo()
	{
		//上传处理
		//创建comment目录
		$dir = "adv/video";
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
	
	
		$res = $this->saveflv($dir);
	
		ajax_return($res);
	}
	
	public function uploadimg()
	{
		//上传处理
		//创建comment目录
		$dir = "adv/img";
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
	
	
		$res = $this->saveimg($dir);
	
		ajax_return($res);
	}
	
	
	public function manage()
	{	
		//根目录路径，可以指定绝对路径，比如 /var/www/attached/
		$root_path = APP_ROOT_PATH . 'public/images/upload';

		//根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
		$root_url = SITE_DOMAIN.APP_ROOT.'/public/images/upload/';
		
		//图片扩展名
		$cfg_ext = strim(app_conf("ALLOW_IMAGE_EXT"));
		$arr = explode(",", $cfg_ext);
		if(count($arr)>0)
			$ext_arr = $arr;
		else
			$ext_arr  = array('jpg', 'jpeg', 'png','gif');
		
		//目录名
		$dir_name = empty($_GET['dir']) ? '' : trim($_GET['dir']);
		if ($dir_name !== ''&&$dir_name!="image") {
			$root_path .= $dir_name . "/";
			$root_url .= $dir_name . "/";
			if (!file_exists($root_path)) {
				@mkdir($root_path);
			}
		}
		
		//根据path参数，设置各路径和URL
		if (empty($_GET['path'])) {

			$current_path = $root_path. '/';
			$current_url = $root_url;
			$current_dir_path = '';
			$moveup_dir_path = '';
		} else {
			$current_path = $root_path . '/' . $_GET['path'];
			$current_url = $root_url . $_GET['path'];
			$current_dir_path = $_GET['path'];
			$moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
		}
		
		//echo realpath($root_path);
		//排序形式，name or size or type
		$order = empty($_GET['order']) ? 'name' : strtolower($_GET['order']);
		
		//不允许使用..移动到上一级目录
		if (preg_match('/\.\./', $current_path)) {
			echo 'Access is not allowed.';
			exit;
		}
		//最后一个字符不是/
		if (!preg_match('/\/$/', $current_path)) {
			echo 'Parameter is not valid.';
			exit;
		}
		//目录不存在或不是目录
		if (!file_exists($current_path) || !is_dir($current_path)) {
			echo 'Directory does not exist.';
			exit;
		}
		
		//遍历目录取得文件信息
		$file_list = array();
		
		if ($handle = opendir($current_path)) {
			$i = 0;
			while (false !== ($filename = readdir($handle))) {
				if ($filename{0} == '.') continue;				
				$file = $current_path . $filename;
				if (is_dir($file)) {
					$file_list[$i]['is_dir'] = true; //是否文件夹
					$file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
					$file_list[$i]['filesize'] = 0; //文件大小
					$file_list[$i]['is_photo'] = false; //是否图片
					$file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
				} else {
					preg_match("/_[\d]+[x][\d]+/i", $file,$match_res);
					if(count($match_res)>0)continue;					
					$file_list[$i]['is_dir'] = false;
					$file_list[$i]['has_file'] = false;
					$file_list[$i]['filesize'] = filesize($file);
					$file_list[$i]['dir_path'] = '';
					$file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
					$file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
					$file_list[$i]['filetype'] = $file_ext;
				}
				$file_list[$i]['filename'] = $filename; //文件名，包含扩展名
				$file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
				$i++;
			}
			closedir($handle);
		}
		
		
		@usort($file_list, 'cmp_func');
		
		$result = array();
		//相对于根目录的上一级目录
		$result['moveup_dir_path'] = $moveup_dir_path;
		//相对于根目录的当前目录
		$result['current_dir_path'] = $current_dir_path;
		//当前目录的URL
		$result['current_url'] = $current_url;
		//文件数
		$result['total_count'] = count($file_list);
		//文件列表数组
		$result['file_list'] = $file_list;
		
		ajax_return($result);
	}
	
	//排序
	private function cmp_func($a, $b) {
		global $order;
		if ($a['is_dir'] && !$b['is_dir']) {
			return -1;
		} else if (!$a['is_dir'] && $b['is_dir']) {
			return 1;
		} else {
			if ($order == 'size') {
				if ($a['filesize'] > $b['filesize']) {
					return 1;
				} else if ($a['filesize'] < $b['filesize']) {
					return -1;
				} else {
					return 0;
				}
			} else if ($order == 'type') {
				return strcmp($a['filetype'], $b['filetype']);
			} else {
				return strcmp($a['filename'], $b['filename']);
			}
		}
	}
	

	/**
	 * 
	 * @param  $dir 不包含public以及结尾的/，如images/qrcode
	 * @return multitype:number string |Ambigous <boolean, multitype:multitype: boolean , unknown, mixed>
	 */
	private function saveimage($dir,$is_water)
	{
		require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
		$image = new es_imagecls();
		$image->max_size = intval(app_conf("MAX_IMAGE_SIZE"));
		
		
		$list = array();
		
		foreach($_FILES as $fkey=>$file)
		{
			$list[$fkey] = false;
			$image->init($file,$dir);
			if($image->save())
			{
				$list[$fkey] = array();
				$list[$fkey]['url'] = $image->file['target'];
				$list[$fkey]['path'] = $image->file['local_target'];
				$list[$fkey]['name'] = $image->file['prefix'];
			}
			else
			{
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
		}
		if($is_water==1)
		$is_water = intval(app_conf("IS_WATER_MARK"));
		$water_image = APP_ROOT_PATH.app_conf("WATER_MARK");
		$alpha = intval(app_conf("WATER_ALPHA"));
		$place = intval(app_conf("WATER_POSITION"));

		foreach($list as $lkey=>$item)
		{			
			if($is_water)
			{
				$dirs = pathinfo($item['url']);
				$dir = $dirs['dirname'];
				$dir = $dir."/origin/";
				$paths = pathinfo($item['path']);
				$path = $paths['dirname'];
				$path = $path."/origin/";				
				if (!is_dir($path)) {
					@mkdir($path);
					@chmod($path, 0777);
				}
				$filename = $paths['basename'];
				@file_put_contents($path.$filename,@file_get_contents($item['path']));
				$image->water($item['path'],$water_image,$alpha, $place);
				if(app_conf("PUBLIC_DOMAIN_ROOT")!='')
				{
					syn_to_remote_image_server($item['url']); //同步水印图
					syn_to_remote_image_server($dir.$filename); //同步原图
				}
			}
			else
			{
				if(app_conf("PUBLIC_DOMAIN_ROOT")!='')
				{
					syn_to_remote_image_server($item['url']);					
				}
			}
		}
		
		return $list;
	}
	
	private function savefile($dir)
	{
		//定义允许上传的文件扩展名
		$ext_arr = array('zip','rar','doc','docx','pdf','jpg','jpeg','gif','png');
		//最大文件大小 2MB
		$max_size = 2000000;
		
		$save_path = APP_ROOT_PATH."public/".$dir;
		$key = "";
		foreach($_FILES as $fkey=>$file)
		{
			$key = $fkey;
		}
	
		//PHP上传失败
		if (!empty($_FILES[$key]['error'])) {
			switch($_FILES[$key]['error']){
				case '1':
					$error = '超过php.ini允许的大小。';
					break;
				case '2':
					$error = '超过表单允许的大小。';
					break;
				case '3':
					$error = '文件只有部分被上传。';
					break;
				case '4':
					$error = '请选择图片。';
					break;
				case '6':
					$error = '找不到临时目录。';
					break;
				case '7':
					$error = '写文件到硬盘出错。';
					break;
				case '8':
					$error = 'File upload stopped by extension。';
					break;
				case '999':
				default:
					$error = '未知错误。';
			}
			return array("error"=>1,"message"=>$error);
		}
		
		//有上传文件时
		if (empty($_FILES) === false) {
			//原文件名
			$file_name = $_FILES[$key]['name'];
			//服务器上临时文件名
			$tmp_name = $_FILES[$key]['tmp_name'];
			//文件大小
			$file_size = $_FILES[$key]['size'];
			//检查文件名
			if (!$file_name) {
				return array("error"=>1,"message"=>"请选择文件");
			}
			//检查目录
			if (@is_dir($save_path) === false) {
				return array("error"=>1,"message"=>"上传目录不存在");
			}
			//检查目录写权限
			if (@is_writable($save_path) === false) {
				return array("error"=>1,"message"=>"上传目录没有写入权限");
			}
			//检查是否已上传
			if (@is_uploaded_file($tmp_name) === false) {
				return array("error"=>1,"message"=>"上传失败");
			}
			//检查文件大小
			if ($file_size > $max_size) {
				return array("error"=>1,"message"=>"上传文件不能超过2MB");
			}
			//检查目录名
			$dir_name = $dir;

			//获得文件扩展名
			$temp_arr = explode(".", $file_name);
			$file_ext = array_pop($temp_arr);
			$file_ext = trim($file_ext);
			$file_ext = strtolower($file_ext);
			//检查扩展名
			if (in_array($file_ext, $ext_arr) === false) {				
				return array("error"=>1,"message"=>"上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr) . "格式。");
			}
			$new_file_name = to_date(NOW_TIME,"YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
			//移动文件
			$file_path= $save_path."/".$new_file_name;
			$file_url = "./public/".$dir."/".$new_file_name;
			if (move_uploaded_file($tmp_name, $file_path) === false) {
				return array("error"=>1,"message"=>"上传失败");
			}
			return array("error"=>0,"url"=>$file_url);
		}
		else
		{
			return array("error"=>1,"message"=>"请选择文件");
		}
	}
	
	
private function saveswf($dir)
	{
		//定义允许上传的文件扩展名
		$ext_arr = array('swf');
	
		$save_path = APP_ROOT_PATH."public/".$dir;
		$key = "";
		foreach($_FILES as $fkey=>$file)
		{
			$key = $fkey;
		}
	
		//PHP上传失败
		if (!empty($_FILES[$key]['error'])) {
			switch($_FILES[$key]['error']){
				case '1':
					$error = '超过php.ini允许的大小。';
					break;
				case '2':
					$error = '超过表单允许的大小。';
					break;
				case '3':
					$error = '文件只有部分被上传。';
					break;
				case '4':
					$error = '请选择图片。';
					break;
				case '6':
					$error = '找不到临时目录。';
					break;
				case '7':
					$error = '写文件到硬盘出错。';
					break;
				case '8':
					$error = 'File upload stopped by extension。';
					break;
				case '999':
				default:
					$error = '未知错误。';
			}
			return array("error"=>1,"message"=>$error);
		}
	
		//有上传文件时
		if (empty($_FILES) === false) {
			//原文件名
			$file_name = $_FILES[$key]['name'];
			//服务器上临时文件名
			$tmp_name = $_FILES[$key]['tmp_name'];
			//文件大小
			$file_size = $_FILES[$key]['size'];
			//检查文件名
			if (!$file_name) {
				return array("error"=>1,"message"=>"请选择文件");
			}
			//检查目录
			if (@is_dir($save_path) === false) {
				return array("error"=>1,"message"=>"上传目录不存在");
			}
			//检查目录写权限
			if (@is_writable($save_path) === false) {
				return array("error"=>1,"message"=>"上传目录没有写入权限");
			}
			//检查是否已上传
			if (@is_uploaded_file($tmp_name) === false) {
				return array("error"=>1,"message"=>"上传失败");
			}
			//检查文件大小
			//检查目录名
			$dir_name = $dir;
	
			//获得文件扩展名
			$temp_arr = explode(".", $file_name);
			$file_ext = array_pop($temp_arr);
			$file_ext = trim($file_ext);
			$file_ext = strtolower($file_ext);
			//检查扩展名
			if (in_array($file_ext, $ext_arr) === false) {
				return array("error"=>1,"message"=>"上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr) . "格式。");
			}
			$new_file_name = to_date(NOW_TIME,"YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
			//移动文件
			$file_path= $save_path."/".$new_file_name;
			$file_url = "./public/".$dir."/".$new_file_name;
			if (move_uploaded_file($tmp_name, $file_path) === false) {
				return array("error"=>1,"message"=>"上传失败");
			}
			return array("error"=>0,"url"=>$file_url);
		}
		else
		{
			return array("error"=>1,"message"=>"请选择文件");
		}
	}
	
private function saveflv($dir)
	{
		//定义允许上传的文件扩展名
		$ext_arr = array('flv');

		$save_path = APP_ROOT_PATH."public/".$dir;
		$key = "";
		foreach($_FILES as $fkey=>$file)
		{
			$key = $fkey;
		}
	
		//PHP上传失败
		if (!empty($_FILES[$key]['error'])) {
			switch($_FILES[$key]['error']){
				case '1':
					$error = '超过php.ini允许的大小。';
					break;
				case '2':
					$error = '超过表单允许的大小。';
					break;
				case '3':
					$error = '文件只有部分被上传。';
					break;
				case '4':
					$error = '请选择图片。';
					break;
				case '6':
					$error = '找不到临时目录。';
					break;
				case '7':
					$error = '写文件到硬盘出错。';
					break;
				case '8':
					$error = 'File upload stopped by extension。';
					break;
				case '999':
				default:
					$error = '未知错误。';
			}
			return array("error"=>1,"message"=>$error);
		}
	
		//有上传文件时
		if (empty($_FILES) === false) {
			//原文件名
			$file_name = $_FILES[$key]['name'];
			//服务器上临时文件名
			$tmp_name = $_FILES[$key]['tmp_name'];
			//文件大小
			$file_size = $_FILES[$key]['size'];
			//检查文件名
			if (!$file_name) {
				return array("error"=>1,"message"=>"请选择文件");
			}
			//检查目录
			if (@is_dir($save_path) === false) {
				return array("error"=>1,"message"=>"上传目录不存在");
			}
			//检查目录写权限
			if (@is_writable($save_path) === false) {
				return array("error"=>1,"message"=>"上传目录没有写入权限");
			}
			//检查是否已上传
			if (@is_uploaded_file($tmp_name) === false) {
				return array("error"=>1,"message"=>"上传失败");
			}
			//检查文件大小
			//检查目录名
			$dir_name = $dir;
	
			//获得文件扩展名
			$temp_arr = explode(".", $file_name);
			$file_ext = array_pop($temp_arr);
			$file_ext = trim($file_ext);
			$file_ext = strtolower($file_ext);
			//检查扩展名
			if (in_array($file_ext, $ext_arr) === false) {
				return array("error"=>1,"message"=>"上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr) . "格式。");
			}
			$new_file_name = to_date(NOW_TIME,"YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
			//移动文件
			$file_path= $save_path."/".$new_file_name;
			$file_url = "./public/".$dir."/".$new_file_name;
			if (move_uploaded_file($tmp_name, $file_path) === false) {
				return array("error"=>1,"message"=>"上传失败");
			}
			return array("error"=>0,"url"=>$file_url);
		}
		else
		{
			return array("error"=>1,"message"=>"请选择文件");
		}
	}
	
	
	private function saveimg($dir)
	{
		//定义允许上传的文件扩展名
		$ext_arr = array('jpg','jpeg','gif','png');
		$max_size =  intval(app_conf("MAX_IMAGE_SIZE"));
		$save_path = APP_ROOT_PATH."public/".$dir;
		$key = "";
		foreach($_FILES as $fkey=>$file)
		{
			$key = $fkey;
		}
	
		//PHP上传失败
		if (!empty($_FILES[$key]['error'])) {
			switch($_FILES[$key]['error']){
				case '1':
					$error = '超过php.ini允许的大小。';
					break;
				case '2':
					$error = '超过表单允许的大小。';
					break;
				case '3':
					$error = '文件只有部分被上传。';
					break;
				case '4':
					$error = '请选择图片。';
					break;
				case '6':
					$error = '找不到临时目录。';
					break;
				case '7':
					$error = '写文件到硬盘出错。';
					break;
				case '8':
					$error = 'File upload stopped by extension。';
					break;
				case '999':
				default:
					$error = '未知错误。';
			}
			return array("error"=>1,"message"=>$error);
		}
	
		//有上传文件时
		if (empty($_FILES) === false) {
			//原文件名
			$file_name = $_FILES[$key]['name'];
			//服务器上临时文件名
			$tmp_name = $_FILES[$key]['tmp_name'];
			//文件大小
			$file_size = $_FILES[$key]['size'];
			//检查文件名
			if (!$file_name) {
				return array("error"=>1,"message"=>"请选择文件");
			}
			//检查目录
			if (@is_dir($save_path) === false) {
				return array("error"=>1,"message"=>"上传目录不存在");
			}
			//检查目录写权限
			if (@is_writable($save_path) === false) {
				return array("error"=>1,"message"=>"上传目录没有写入权限");
			}
			//检查是否已上传
			if (@is_uploaded_file($tmp_name) === false) {
				return array("error"=>1,"message"=>"上传失败");
			}
			//检查文件大小
			if ($file_size > $max_size) {
				return array("error"=>1,"message"=>"上传图片大太，不能超过".ceil($max_size/1000000)."MB");
			}
			//检查目录名
			$dir_name = $dir;
	
			//获得文件扩展名
			$temp_arr = explode(".", $file_name);
			$file_ext = array_pop($temp_arr);
			$file_ext = trim($file_ext);
			$file_ext = strtolower($file_ext);
			//检查扩展名
			if (in_array($file_ext, $ext_arr) === false) {
				return array("error"=>1,"message"=>"上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr) . "格式。");
			}
			$new_file_name = to_date(NOW_TIME,"YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
			//移动文件
			$file_path= $save_path."/".$new_file_name;
			$file_url = "./public/".$dir."/".$new_file_name;
			if (move_uploaded_file($tmp_name, $file_path) === false) {
				return array("error"=>1,"message"=>"上传失败");
			}
			return array("error"=>0,"url"=>$file_url);
		}
		else
		{
			return array("error"=>1,"message"=>"请选择文件");
		}
	}
}
?>