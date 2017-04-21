<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------
function delUpload($file)
{
	@unlink(APP_ROOT_PATH.$file);
}

class joinModule extends BaseModule
{
	public function index()
	{		
		$GLOBALS['tmpl']->assign("formaction",url("join#save"));		
		$GLOBALS['tmpl']->display("join_index.html");
	}	
	
	
	public function save()
	{
		//处理文件上传
		if(!empty($_FILES['company_description']))
		{
			$res = $this->uploadfile();
			if($res['error']==0)
			{			
				$data['company_description'] = $res['url'];
			}
			else
			{
				showErr($res['message'],1,"company_description_show");
			}
		}
		
		$data['user_name'] = strim($_REQUEST['user_name']);
		$data['user_pwd'] = strim($_REQUEST['user_pwd']);
		$cfm_user_pwd = strim($_REQUEST['cfm_user_pwd']);
		$data['contact_name'] = strim($_REQUEST['contact_name']);
		$data['contact_sex'] = intval($_REQUEST['contact_sex']);
		$data['contact_tel'] = strim($_REQUEST['contact_tel']);
		$data['contact_mobile'] = strim($_REQUEST['contact_mobile']);
		$data['contact_fax'] = strim($_REQUEST['contact_fax']);
		$data['contact_qq'] = strim($_REQUEST['contact_qq']);
		$data['contact_email'] = strim($_REQUEST['contact_email']);
		$data['company_name'] = strim($_REQUEST['company_name']);
		$data['company_address'] = strim($_REQUEST['company_address']);
		$data['company_zip'] = strim($_REQUEST['company_zip']);
		$data['company_person'] = intval($_REQUEST['company_person']);
		$data['company_regist'] = intval($_REQUEST['company_regist']);
		
		if($data['user_name']=="")
		{
			delUpload($data['company_description']);
			showErr("请输入用户名！",1,"user_name");
		}
		if($data['user_pwd']=="")
		{
			delUpload($data['company_description']);
			showErr("请输入登录密码！",1,"user_pwd");
		}
		if($data['user_pwd']!=$cfm_user_pwd)
		{
			delUpload($data['company_description']);
			showErr("登录密码匹配错误！",1,"cfm_user_pwd");
		}
		if($data['contact_name']=="")
		{
			delUpload($data['company_description']);
			showErr("请输入联系人姓名！",1,"contact_name");
		}
		if($data['contact_tel']=="")
		{
			delUpload($data['company_description']);
			showErr("请输入联系电话！",1,"contact_tel");
		}
		if($data['contact_mobile']=="")
		{
			delUpload($data['company_description']);
			showErr("请输入联系手机号！",1,"contact_mobile");
		}
		if(!check_mobile($data['contact_mobile']))
		{
			delUpload($data['company_description']);
			showErr("请输入正确的手机号！",1,"contact_mobile");
		}
		if($data['contact_email']=="")
		{
			delUpload($data['company_description']);
			showErr("请输入email地址！",1,"contact_email");
		}
		if(!check_email($data['contact_email']))
		{
			delUpload($data['company_description']);
			showErr("请输入正确的email地址！",1,"contact_email");
		}
		if($data['company_name']=="")
		{
			delUpload($data['company_description']);
			showErr("请输入联公司名称！",1,"company_name");
		}
		if($data['company_address']=="")
		{
			delUpload($data['company_description']);
			showErr("请输入联公司地址！",1,"company_address");
		}
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier where user_name = '".$data['user_name']."'")>0)
		{
			delUpload($data['company_description']);
			showErr("用户名已存在，请更换用户名！",1,"user_name");
		}
		$data['user_pwd'] = md5($data['user_pwd']);
		$data['create_time'] = NOW_TIME;
		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier",$data,"INSERT","","SILENT");
		$id = $GLOBALS['db']->insert_id();
		if($id>0)
		{
			showSuccess("注册成功，等待管理员审核",1,url("join"));
		}
		else
		{
			delUpload($data['company_description']);
			showErr("用户名已存在，请更换用户名！",1,"user_name");
		}
		

		exit;
	}
	
	
	
	
	//关于文件上传的处理
	private function uploadfile()
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
	
		return $res;
	}
	
	
	private function savefile($dir)
	{
		//定义允许上传的文件扩展名
		$ext_arr = array('zip','rar','doc','docx','pdf','jpg','jpeg','png','gif');
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
	

}
?>