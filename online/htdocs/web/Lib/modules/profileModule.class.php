<?php

class profileModule extends BaseModule{
	
	public function index()
	{
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			app_redirect(url("user#login"));
		}
		init_app_page();
		
		$GLOBALS['user']['email'] = preg_replace("/(\S{3})(\S*)(\S{3})/", "\${1}*****\$3", $GLOBALS['user']['email']);
		$GLOBALS['user']['mobile'] = preg_replace("/(\d{3})(\d*)(\d{3})/", "\${1}*****\$3", $GLOBALS['user']['mobile']);
		$GLOBALS['user']['pid_user'] = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$GLOBALS['user']['pid']);
		
		
		$GLOBALS['tmpl']->assign("user",$GLOBALS['user']);
		
		$province_list = load_auto_cache("province_list");
		$GLOBALS['tmpl']->assign("province_list",$province_list);
		$GLOBALS['tmpl']->display("profile_index.html");
	}
	
	public function saveuser()
	{
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			showErr("登录超时",1,url("user#login"));
		}
		else
		{
			$user_pwd = strim($_REQUEST['user_pwd']);
			$new_pwd = strim($_REQUEST['new_pwd']);
			$cfm_new_pwd = strim($_REQUEST['cfm_new_pwd']);
			$truename = strim($_REQUEST['truename']);
			$sex = intval($_REQUEST['sex']);
			$birthday = strim($_REQUEST['birthday']);
			$paper_type = intval($_REQUEST['paper_type']);
			$paper_sn = strim($_REQUEST['paper_sn']);
			$province_id = intval($_REQUEST['province_id']);
			$city_id = intval($_REQUEST['city_id']);
			$address = strim($_REQUEST['address']);
			$zip_code = strim($_REQUEST['zip_code']);
			
			if($new_pwd!="")
			{
				//验证原始密码的有效性
				if($user_pwd=="")
				{
					showErr("请输入原密码",1);
				}
				elseif(md5($user_pwd.$GLOBALS['user']['salt'])!=$GLOBALS['user']['user_pwd'])
				{
					showErr("原密码不正确",1);
				}
				elseif($new_pwd!=$cfm_new_pwd)
				{
					showErr("新密码确认失败");
				}
				$user_data['user_pwd'] = md5($user_pwd.$GLOBALS['user']['salt']);
			}
			$user_data['truename'] = $truename;
			$user_data['sex'] = $sex;
			$user_data['birthday'] = $birthday;
			$user_data['paper_type'] = $paper_type;
			$user_data['paper_sn'] = $paper_sn;
			$user_data['province_id'] = $province_id;
			$user_data['city_id'] = $city_id;
			$user_data['address'] = $address;
			$user_data['zip_code'] = $zip_code;
			
			$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_data,"UPDATE","id=".$GLOBALS['user']['id'],"SILENT");
			showSuccess("更新成功",1);
		}
	}
	
	public function modify_username()
	{
		$GLOBALS['tmpl']->display("profile_modify_username.html");
	}
	public function modify_email()
	{
		$GLOBALS['tmpl']->display("profile_modify_email.html");
	}
	public function modify_mobile()
	{
		$GLOBALS['tmpl']->display("profile_modify_mobile.html");
	}
	
	//保存用户名
	public function save_username()
	{
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			showErr("登录超时",1,url("user#login"));
		}
		else
		{
			$username = strim($_REQUEST['user_name']);
			if($username=="")
			{
				showErr("用户名不能为空",1);
			}
			else
			{
				$rs = User::checkfield("user_name", $username);
				if(!$rs['status'])
				{
					showErr($rs['info'],1);
				}
				if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where id <> ".$GLOBALS['user']['id']." and user_name = '".$username."'")>0)
				{
					showErr("用户名已存在",1);
				}
				else
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."user set user_name = '".$username."',is_temp = 0 where id = ".$GLOBALS['user']['id'],"SILENT");
					if($GLOBALS['db']->error()=="")
					{
						User::syn_nickname($username, $GLOBALS['user']['id']);
						showSuccess("修改成功",1);
					}
					else
					{
						showErr("用户名已存在",1);
					}
				}
			}
		}
	}
	
	public function save_email()
	{
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			showErr("登录超时",1,url("user#login"));
		}
		else
		{
			
			es_session::start();
			$verify = es_session::get("verify");
			es_session::close();
			
			$user_verify = strim($_REQUEST['verify_code']);
			if(md5($user_verify)!=$verify)
			{
				showErr("验证码不正确",1);
			}
			
			
			$email = strim($_REQUEST['email']);
			if($email==$GLOBALS['user']['email'])
			{
				showErr("新邮箱旧邮箱相同",1);
			}
			if($email=="")
			{
				showErr("邮箱不能为空",1);
			}
			elseif(!check_email($email))
			{
				showErr("邮箱格式不正确",1);
			}
			else
			{
				if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where id <> ".$GLOBALS['user']['id']." and email = '".$email."'")>0)
				{
					showErr("邮箱已存在",1);
				}
				else
				{
					
					if(app_conf("MAIL_ON")==1)
					{
						//发邮件验证
						$GLOBALS['db']->query("update ".DB_PREFIX."user set tmp_email = '".$email."' where id = ".$GLOBALS['user']['id'],"SILENT");
						$GLOBALS['user']['tmp_email'] = $email;
						User::send_modify_email($GLOBALS['user']);
						showSuccess("已经发送一封验证邮件到您的邮箱 ".$email."，请查收并验证",1);
					}
					else
					{					
						$GLOBALS['db']->query("update ".DB_PREFIX."user set email = '".$email."' where id = ".$GLOBALS['user']['id'],"SILENT");
						if($GLOBALS['db']->error()=="")
						{
							showSuccess("修改成功",1);
						}
						else
						{
							showErr("邮箱已存在",1);
						}
					}
				}
			}
		}
	}
	
	public function dosave_email()
	{
		$username = strim($_REQUEST['un']);
		$code = strim($_REQUEST['c']);
		$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where user_name ='".$username."'");
		if(empty($user)||$user['is_effect']==0)
		{
			app_redirect(url("index")); //无会员或者被禁用的会员跳过
		}
		else
		{
			//邮箱验证
			if(empty($code))
			{
				showErr("验证码错误");
			}
			else
			{
				if($user['email_code']==$code&&$user['email_code_time']>NOW_TIME)
				{
					//验证成功
					$GLOBALS['db']->query("update ".DB_PREFIX."user set email = tmp_email,tmp_email = '',email_code = '',email_code_time = 0 where id = ".$user['id']);
					if($GLOBALS['db']->error()=="")
					{
						showSuccess("邮箱更改成功，新邮箱地址为：".$user['tmp_email']);
					}
					else
					{
						showErr("新邮箱已被占用");
					}
					
				}
				else
				{
					if($user['email_code']!=$code)
					{
						showErr("验证码错误");
					}
					else
					{
						$GLOBALS['db']->query("update ".DB_PREFIX."user set email_code = '',email_code_time = 0,tmp_email = '' where id = ".$user['id']);
						showErr("验证码已过期");
					}
				}
			}
		}
	}
	
	
	
	public function save_mobile()
	{
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			showErr("登录超时",1,url("user#login"));
		}
		else
		{
				
			es_session::start();
			$verify = es_session::get("verify");
			es_session::close();
				
			$user_verify = strim($_REQUEST['verify_code']);
			if(md5($user_verify)!=$verify)
			{
				showErr("验证码不正确",1);
			}
				
				
			$mobile = strim($_REQUEST['mobile']);
			if($mobile==$GLOBALS['user']['mobile'])
			{
				showErr("新手机号与旧手机号相同",1);
			}
			if($mobile=="")
			{
				showErr("手机号码不能为空",1);
			}
			elseif(!check_mobile($mobile))
			{
				showErr("手机格式不正确",1);
			}
			else
			{
				if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where id <> ".$GLOBALS['user']['id']." and mobile = '".$mobile."'")>0)
				{
					showErr("手机号码已被占用",1);
				}
				else
				{
						
					if(app_conf("SMS_ON")==1)
					{
						//需校验码
						$user = $GLOBALS['user'];
						$code = strim($_REQUEST['verify']);
						
						if(empty($code))
						{
							showErr("校验码不能为空",1);
						}
						else
						{
							if($user['mobile_code']==$code&&$user['mobile_code_time']>NOW_TIME)
							{
								//验证成功
								$GLOBALS['db']->query("update ".DB_PREFIX."user set mobile = tmp_mobile,tmp_mobile = '',mobile_code = '',mobile_code_time = 0 where id = ".$user['id']);
								if($GLOBALS['db']->error()=="")
								{
									showSuccess("手机号更改成功，新手机号为：".$user['tmp_mobile'],1);
								}
								else
								{
									showErr("手机号已被占用",1);
								}									
							}
							else
							{
								if($user['mobile_code']!=$code)
								{
									showErr("校验码错误",1);
								}
								else
								{
									$GLOBALS['db']->query("update ".DB_PREFIX."user set mobile_code = '',mobile_code_time = 0,tmp_mobile = '' where id = ".$user['id']);
									showErr("校验码已过期",1);
								}
							}
						}
						//校验码end
						
					}
					else
					{
						$GLOBALS['db']->query("update ".DB_PREFIX."user set mobile = '".$mobile."' where id = ".$GLOBALS['user']['id'],"SILENT");
						if($GLOBALS['db']->error()=="")
						{
							showSuccess("修改成功",1);
						}
						else
						{
							showErr("手机号码已存在",1);
						}
					}
				}
			}
		}
	}
	
	
	public function get_modify_mobile_code()
	{
		if(app_conf("SMS_ON")==0)
		{
			showErr("短信功能未开启，无法发送校验码",1);
		}
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			showErr("登录超时",1,url("user#login"));
		}
		else
		{
			$user_verify = strim($_REQUEST['verify_code']);
			$mobile = strim($_REQUEST['mobile']);
			
			if($mobile==$GLOBALS['user']['mobile'])
			{
				showErr("新手机号与旧手机号相同",1);
			}
			
			es_session::start();
			$verify = es_session::get("verify");
			es_session::close();
			
			if(md5($user_verify)!=$verify)
			{
				showErr("验证码不正确",1);
			}
			
			
			if($GLOBALS['user']['mobile_code_time']>0&&NOW_TIME - ($GLOBALS['user']['mobile_code_time']-2*3600)<30)
			{
				showErr("发送太频繁，每次发送间隔为30秒",1);
			}
			
			if($mobile=="")
			{
				showErr("手机号码不能为空",1);
			}
			elseif(!check_mobile($mobile))
			{
				showErr("手机格式不正确",1);
			}
			else
			{
				if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where id <> ".$GLOBALS['user']['id']." and mobile = '".$mobile."'")>0)
				{
					showErr("手机号码已被占用",1);
				}
				else
				{
					//发校验码
					$GLOBALS['db']->query("update ".DB_PREFIX."user set tmp_mobile = '".$mobile."' where id = ".$GLOBALS['user']['id'],"SILENT");
					$GLOBALS['user']['tmp_mobile'] = $mobile;
					User::send_modify_mobile($GLOBALS['user']);
					showSuccess("已经成功发送校验码到您的手机 ".$mobile."，请查收并验证",1);
					
				}
			}
			
			
			
		}
	}
	
	
	/**
	 * 配送地址
	 */
    function consignee() {
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			app_redirect(url("user#login"));
		}		
		init_app_page();
		
		$consignee_list = $GLOBALS["db"]->getAll("SELECT uc.*,p.name as province_name,c.name as city_name FROM ".DB_PREFIX."user_consignee uc LEFT JOIN ".DB_PREFIX."province p ON p.id = uc.province_id LEFT JOIN ".DB_PREFIX."city c ON c.id = uc.city_id  WHERE uc.user_id=".$GLOBALS['user']['id']);
		$GLOBALS['tmpl']->assign("consignee_list",$consignee_list);
		
		$province_list = load_auto_cache("province_list");
    	$GLOBALS['tmpl']->assign("province_list",$province_list);
    	$GLOBALS['tmpl']->assign("userinfo",$GLOBALS['user']);
		
		$GLOBALS['tmpl']->display("profile_consignee.html");
    }
    
    /**
     * 设置为默认配送地址
     */
    function set_consignee_default(){
    	global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			showErr("登录过期",1);
		}
		
		$id = intval($_POST['id']);
		
		if($id == 0)
		{
			showErr("参数错误",1);
		}
		
		$GLOBALS["db"]->query("UPDATE ".DB_PREFIX."user_consignee set is_default = 1 WHERE user_id=".$GLOBALS['user']['id']." AND id=".$id);
		if($GLOBALS["db"]->affected_rows()){
			$GLOBALS["db"]->query("UPDATE ".DB_PREFIX."user_consignee set is_default = 0 WHERE user_id=".$GLOBALS['user']['id']." AND id<>".$id);
			showSuccess("设置成功",1);
		}
		else{
			showErr("设置失败",1);
		}
    }
    
    /**
     * 设置为默认配送地址
     */
    function del_consignee(){
    	global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			showErr("登录过期",1);
		}
		
		$id = intval($_POST['id']);
		
		if($id == 0)
		{
			showErr("参数错误",1);
		}
		
		$GLOBALS["db"]->query("DELETE FROM ".DB_PREFIX."user_consignee WHERE user_id=".$GLOBALS['user']['id']." AND id=".$id);
		if($GLOBALS["db"]->affected_rows()){
			showSuccess("删除成功",1);
		}
		else{
			showErr("删除失败",1);
		}
    }
    
    /**
     * 编辑地址
     */
    function edit_consignee(){
    	global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			showErr("登录过期",1);
		}
		
		$id = intval($_POST['id']);
		
		if($id > 0)
		{
			$do_consignee = $GLOBALS["db"]->getRow("SELECT * FROM ".DB_PREFIX."user_consignee WHERE user_id=".$GLOBALS['user']['id']." and id=".$id);
		}
		
		if($do_consignee){
			$GLOBALS['tmpl']->assign("do_consignee",$do_consignee);
		}
		
		$GLOBALS['tmpl']->assign("userinfo",$GLOBALS['user']);
		$province_list = load_auto_cache("province_list");
		$GLOBALS['tmpl']->assign("province_list",$province_list);
		
		$return['status'] = 1;
		$return['city_id'] = $do_consignee['city_id'];
		
		$return['info'] = $GLOBALS['tmpl']->fetch("inc/do_consignee.html");
		ajax_return($return);
    }
    
    /**
     * 添加编辑
     */
    function do_consignee(){
    	global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			showErr("登录过期",1);
		}
		
		$data['id'] = intval($_POST['id']);
		$data['delivery_name'] = strim($_POST['delivery_name']);
		if($data['delivery_name'] ==""){
			showErr("请填写收件人姓名",1);
		}
		$data['province_id'] = intval($_POST['province_id']);
		$data['city_id'] = intval($_POST['city_id']);
		if($data['province_id'] == 0 || $data['city_id'] ==0){
			showErr("请选择城市",1);
		}
		$data['address'] = strim($_POST['address']);
		if($data['address'] == ""){
			showErr("请填写详细地址",1);
		}
		$data['zip'] = strim($_POST['zip']);
		if($data['zip'] == ""){
			showErr("请填写邮政编码",1);
		}
		$data['delivery_mobile'] = strim($_POST['delivery_mobile']);
		if($data['delivery_mobile'] == ""){
			showErr("请填写手机号码",1);
		}
		if(!check_mobile($data['delivery_mobile'])){
			showErr("手机号码格式错误",1);
		}
		
		$data['is_default'] = intval($_POST['is_default']);
		$data['user_id'] = $GLOBALS['user']['id'];
		
		$mode = "INSERT";
		$where =" ";
		if($data['id'] > 0){
			$mode = "UPDATE";
			$where = "id=".$data['id'];
		}
		$GLOBALS["db"]->autoExecute(DB_PREFIX."user_consignee",$data,$mode,$where);
		if($GLOBALS["db"]->affected_rows()>0){
			if($data['id'] > 0){
				if($data['is_default'] > 0){
					$GLOBALS["db"]->query("UPDATE ".DB_PREFIX."user_consignee set is_default = 0 WHERE user_id=".$GLOBALS['user']['id']." AND id<>".$data['id']);
				}
				showSuccess("编辑成功",1);
			}
			else{
				if($data['is_default'] > 0){
					$GLOBALS["db"]->query("UPDATE ".DB_PREFIX."user_consignee set is_default = 0 WHERE user_id=".$GLOBALS['user']['id']." AND id<>".$GLOBALS["db"]->insert_id());
				}
				showSuccess("添加成功",1);
			}
			
		}
		else{
			if($data['id'] > 0){
				showErr("编辑失败",1);
			}
			else{
				showErr("编辑失败",1);
			}
		}
    }
    
    function namelist(){
		global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			app_redirect(url("user#login"));
		}		
		init_app_page();
		
		$namelist = $GLOBALS["db"]->getAll("SELECT * FROM ".DB_PREFIX."user_namelist WHERE user_id=".$GLOBALS['user']['id']);
		foreach($namelist as $k=>$v){
			$namelist[$k]['paper_type_val'] =get_paper_type_name($v['paper_type']);
			$namelist[$k]['edit_url']=url("profile#namelist_edit",array('id'=>$v['id']));	
		}
    	$GLOBALS['tmpl']->assign("namelist",$namelist);
    	$GLOBALS['tmpl']->assign("userinfo",$GLOBALS['user']);
		
		$GLOBALS['tmpl']->display("profile_namelist.html");
    }
    
 	function namelist_add()
    {
    	global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			app_redirect(url("user#login"));
		}		
		init_app_page();
    	$GLOBALS['tmpl']->display("profile_namelist_add.html");
    }
    
    function namelist_edit()
    {
    	global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			app_redirect(url("user#login"));
		}
		init_app_page();
		
		$id=intval($_REQUEST['id']);
		$namelist = $GLOBALS["db"]->getRow("SELECT * FROM ".DB_PREFIX."user_namelist WHERE user_id=".$GLOBALS['user']['id']." and id=".$id);		
		
    	$GLOBALS['tmpl']->assign("namelist",$namelist);
    	$GLOBALS['tmpl']->display("profile_namelist_add.html");
    }
    
    /**
     * 添加编辑
     */
    function namelist_do(){
    	global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			app_redirect(url("user#login"));
		}
		
		$data['id'] = intval($_POST['id']);
		$data['name'] = strim($_POST['name']);
		
		$return_url=url("profile#namelist_add");
		if($data['id'] > 0)
			$return_url=url("profile#namelist_edit",array("id"=>$data['id']));
			
		if($data['name'] ==""){
			showErr("请填写姓名",0,$return_url);
		}
		
		if($data['id'] >0)
		{
			if($GLOBALS["db"]->getOne("select count(*) from ".DB_PREFIX."user_namelist where name='".$data['name']."' and id <> ".$data['id']."") >0)
			{
				
				showErr("已有相同名称的游客信息",0,$return_url);
			}
		}
		else
		{
			if($GLOBALS["db"]->getOne("select count(*) from ".DB_PREFIX."user_namelist where name='".$data['name']."'") >0)
			{
				
				showErr("已有相同名称的游客信息",0,$return_url);
			}
		}
		
		$data['paper_sn'] = strim($_POST['paper_sn']);
		if($data['paper_sn'] == ""){
			showErr("请填写证件号",0,$return_url);
		}
		$data['mobile'] = strim($_POST['mobile']);
		if($data['mobile'] == ""){
			showErr("请填写手机号码",0,$return_url);
		}
		if(!check_mobile($data['mobile'])){
			showErr("手机号码格式错误",0,$return_url);
		}
		
		$data['paper_type'] = intval($_POST['paper_type']);
		$data['is_default'] = intval($_POST['is_default']);
		$data['user_id'] = $GLOBALS['user']['id'];
		$data['sort'] = intval($_POST['sort']);
		$mode = "INSERT";
		$where =" ";
		if($data['id'] > 0){
			$mode = "UPDATE";
			$where = "id=".$data['id'];
		}
		$GLOBALS["db"]->autoExecute(DB_PREFIX."user_namelist",$data,$mode,$where);
		if($GLOBALS["db"]->affected_rows()>0){
			if($data['id'] > 0){
				if($data['is_default'] > 0){
					$GLOBALS["db"]->query("UPDATE ".DB_PREFIX."user_namelist set is_default = 0 WHERE user_id=".$GLOBALS['user']['id']." AND id<>".$data['id']);
				}
				showSuccess("编辑成功");
			}
			else{
				if($data['is_default'] > 0){
					$GLOBALS["db"]->query("UPDATE ".DB_PREFIX."user_namelist set is_default = 0 WHERE user_id=".$GLOBALS['user']['id']." AND id<>".$GLOBALS["db"]->insert_id());
				}
				showSuccess("添加成功");
			}
			
		}
		else{
			if($data['id'] > 0){
				showErr("编辑失败");
			}
			else{
				showErr("编辑失败");
			}
		}
    }
    
 	/**
     * 设置为默认游客地址
     */
    function namelist_del(){
    	global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			showErr("登录过期",1);
		}
		
		$id = intval($_POST['id']);
		
		if($id == 0)
		{
			showErr("参数错误",1);
		}
		
		$GLOBALS["db"]->query("DELETE FROM ".DB_PREFIX."user_namelist WHERE user_id=".$GLOBALS['user']['id']." AND id=".$id);
		if($GLOBALS["db"]->affected_rows()){
			showSuccess("删除成功",1);
		}
		else{
			showErr("删除失败",1);
		}
    }
    
 	/**
     * 设置为默认配送地址
     */
    function set_namelist_default(){
    	global_run();
		if(empty($GLOBALS['user'])) //验证是否登录
		{
			showErr("登录过期",1);
		}
		
		$id = intval($_POST['id']);
		
		if($id == 0)
		{
			showErr("参数错误",1);
		}
		
		$GLOBALS["db"]->query("UPDATE ".DB_PREFIX."user_namelist set is_default = 1 WHERE user_id=".$GLOBALS['user']['id']." AND id=".$id);
		if($GLOBALS["db"]->affected_rows()){
			$GLOBALS["db"]->query("UPDATE ".DB_PREFIX."user_namelist set is_default = 0 WHERE user_id=".$GLOBALS['user']['id']." AND id<>".$id);
			showSuccess("设置成功",1);
		}
		else{
			showErr("设置失败",1);
		}
    }
}
?>