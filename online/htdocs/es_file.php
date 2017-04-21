<?php 
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------
define("USERNAME","admin");   //远程同步访问的用户名
define("PASSWORD","admin");	  //远程同步访问的密码
define('APP_ROOT_PATH', str_replace('es_file.php', '', str_replace('\\', '/', __FILE__)));

function mk_dir($dir, $mode = 0777)
{	
  if (is_dir($dir) || @mkdir($dir,$mode)) return true;
  if (!mk_dir(dirname($dir),$mode)) return false;
  return @mkdir($dir,$mode);
}


/**
 * 获取文件扩展名
 * @return string
 */
function fileExt($file_name)
{
	return addslashes(strtolower(substr(strrchr($file_name, '.'), 1, 10)));
}

/**
 * 根据扩展名判断文件是否为图像
 * @param string $ext 扩展名
 * @return bool
 */
function isImageExt($ext)
{
	static $img_ext  = array('jpg', 'jpeg', 'png', 'bmp','gif','giff');
	return in_array($ext, $img_ext) ? 1 : 0;
}

$username = trim(addslashes($_REQUEST['username']));
$password = trim(addslashes($_REQUEST['password']));
$act = intval($_REQUEST['act']);

if($username==USERNAME&&$password==PASSWORD)
{
	if($act==0) //上传
	{
		$file = trim(addslashes($_REQUEST['file']));
		$path = trim(addslashes($_REQUEST['path']));
		$name = trim(addslashes($_REQUEST['name']));		
		if(!isImageExt(fileExt($name)))die("invalid image");
		
		$file_data = @file_get_contents($file);
		$img = @imagecreatefromstring($file_data);
		if($img!==false)
		{
			$save_path = APP_ROOT_PATH."public/".$path;
			if(!is_dir($save_path))
			{
				@mk_dir($save_path);			
			}			
			@file_put_contents($save_path.$name,$file_data);
		}
	}
	else if($act==2) //批量上传
	{
		$_REQUEST['images'] = unserialize(base64_decode($_REQUEST['images']));		
		foreach($_REQUEST['images'] as $k=>$item)
		{
			$file = trim(addslashes($item['file']));
			$path = trim(addslashes($item['path']));
			$name = trim(addslashes($item['name']));
			if(!isImageExt(fileExt($name)))break;

			$file_data = @file_get_contents($file);
			$img = @imagecreatefromstring($file_data);
			if($img!==false)
			{
				$save_path = APP_ROOT_PATH."public/".$path."/";
				if(!is_dir($save_path))
				{
					@mk_dir($save_path);			
				}		
				@file_put_contents($save_path.$name,$file_data);
			}
			
		}
		
	}
	else
	{
		//删除
		$path = trim(addslashes($_REQUEST['path']));

		$save_path = APP_ROOT_PATH.$path;  //删除时直接传入相应的位置与名称
		$file_data = @file_get_contents($save_path);
		$img = @imagecreatefromstring($file_data);
		if($img!==false)
		{
			@unlink($save_path);
		}
	}
}
else
{
	die("invalid access");
}

?>