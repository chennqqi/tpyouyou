<?php
define("USER_SALT", "");
define("USER_PAGE_SIZE",20); //会员中心的记录分页量
/**
 * 
 * @author HC
 *会员事务处理类
 *会员昵称可修改，会员帐号可修改一次（仅限自动创建会员时）,邮箱，手机注册时必须有一个
 */
class User{
	
	/**
	 * 提供任意一手机或邮箱，进行注册
	 * @param unknown_type $mobile
	 * @param unknown_type $email
	 * 用于下订单时的自动注册，is_temp为1
	 * 返回
	 * array(status,data,message);
	 */
	public static function auto_gen($mobile="",$email="")
	{
		
	}
	
	
	/**
	 * 同步所有的会员昵称
	 * @param unknown_type $nickname
	 * @param unknown_type $user_id
	 */
	public static function syn_nickname($nickname,$user_id)
	{
		$GLOBALS['db']->query("update ".DB_PREFIX."user set nickname = '".$nickname."' where id = ".$user_id);
		$GLOBALS['db']->query("update ".DB_PREFIX."comment set nickname = '".$nickname."' where user_id = ".$user_id);
		$GLOBALS['db']->query("update ".DB_PREFIX."review set nickname = '".$nickname."' where user_id = ".$user_id);
		$GLOBALS['db']->query("update ".DB_PREFIX."tour_guide set nickname = '".$nickname."' where user_id = ".$user_id);
		$GLOBALS['db']->query("update ".DB_PREFIX."user_active set nickname = '".$nickname."' where user_id = ".$user_id);
		$GLOBALS['db']->query("update ".DB_PREFIX."user_fans set nickname = '".$nickname."' where fans_id = ".$user_id);
		$GLOBALS['db']->query("update ".DB_PREFIX."user_follow set nickname = '".$nickname."' where follow_id = ".$user_id);
		$GLOBALS['db']->query("update ".DB_PREFIX."ask set nickname = '".$nickname."' where user_id = ".$user_id);
		$GLOBALS['db']->query("update ".DB_PREFIX."user_rebate set nickname = '".$nickname."' where from_uid = ".$user_id);
	}
	
	/**
	 * 后台添加修改保存,$dataset表示为提交的数据集
	 * 返回
	 * result(message,id)
	 * id:用户id message:相关信息
	 * 
	 */
	public static function admin_save($dataset)
	{
		$data['id'] = intval($dataset['id']);
		$data['user_name'] = $dataset['user_name'];
		//if(!empty($dataset['nickname']))
		$data['nickname'] = $data['user_name'];
		if(!empty($dataset['user_pwd']))
		$data['user_pwd'] = $dataset['user_pwd'];	
		if(!empty($dataset['email']))
		$data['email'] = $dataset['email'];
		if(!empty($dataset['mobile']))
		$data['mobile'] = $dataset['mobile'];
		$data['is_verify'] = $dataset['is_verify'];
		$data['is_effect'] =$dataset['is_effect'];
		$data['is_temp'] = $dataset['is_temp'];
		$data['is_modify_nickname'] = $dataset['is_modify_nickname'];
		$data['group_id'] = $dataset['group_id'];
		$data['truename'] = $dataset['truename'];
		$data['sex'] = $dataset['sex'];
		$data['birthday'] = $dataset['birthday'];
		$data['paper_type'] = $dataset['paper_type'];
		$data['paper_sn'] = $dataset['paper_sn'];
		$data['province_id'] = $dataset['province_id'];
		$data['city_id'] = $dataset['city_id'];
		$data['address'] = $dataset['address'];
		$data['zip_code'] = $dataset['zip_code'] ;
		$data['avatar'] = $dataset['avatar'];
	
		if(!empty($data['user_pwd'])&&$data['user_pwd']!=$dataset['cfm_user_pwd'])
		{
			$result['id'] = 0;
			$result['message'] = "密码输入不配匹";
			return $result;
		}
		if($data['user_name']=="")
		{
			$result['id'] = 0;
			$result['message'] = "用户名不能为空";
			return $result;
		}
		if($data['email']!=""&&!check_email($data['email']))
		{
			$result['id'] = 0;
			$result['message'] = "邮箱格式错误";
			return $result;
		}
		if($data['mobile']!=""&&!check_mobile($data['mobile']))
		{
			$result['id'] = 0;
			$result['message'] = "手机号错误";
			return $result;
		}
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where user_name = '".$data['user_name']."' and id <> ".$data['id'])>0)
		{
			$result['id'] = 0;
			$result['message'] = "用户名已存在";
			return $result;
		}
		if($data['email']!=""&&$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where email = '".$data['email']."' and id <> ".$data['id'])>0)
		{
			$result['id'] = 0;
			$result['message'] = "邮箱已存在";
			return $result;
		}
		if($data['mobile']!=""&&$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where mobile = '".$data['mobile']."' and id <> ".$data['id'])>0)
		{
			$result['id'] = 0;
			$result['message'] = "手机号已存在";
			return $result;
		}
		
		if(empty($data['id']))
		{
			//添加
			if($data['user_pwd']=="")
			{
				$result['id'] = 0;
				$result['message'] = "密码不能为空";
				return $result;
			}
			
			
			//后台添加会员时通知会员整合添加
			$integrate  = $GLOBALS['db']->getRow("select class_name from ".DB_PREFIX."integrate");
			if($integrate)
			{
				$directory = APP_ROOT_PATH."system/integrate/";
				$file = $directory.$integrate['class_name']."_integrate.php";
				if(file_exists($file))
				{
					require_once($file);
					$integrate_class = $integrate['class_name']."_integrate";
					$integrate_item = new $integrate_class;
					$ck = $integrate_item->add_user($data['user_name'],$data['user_pwd'],$data['email']);
					if($ck['status']==0)
					{
						$result['id'] = 0;
						$result['message'] = $ck['info'];
						return $result;
						//ajax_return(array("status"=>0,"info"=>$ck['info'],"field"=>$ck['field']));
					}
				}
			}
			
			$data['create_time'] = NOW_TIME;
			$data['update_time'] = NOW_TIME;
			$data['salt']	 = USER_SALT;
			$data['user_pwd'] = md5($data['user_pwd'].$data['salt']);	
			$data['source'] = "admin";
			$GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"INSERT","","SILENT");
			if($GLOBALS['db']->error()!="")
			{
				$result['id'] = 0;
				print_r($GLOBALS['db']);
				$result['message'] = $GLOBALS['db']->error()."aaa";
				return $result;
			}
			else
			{
				$id = $GLOBALS['db']->insert_id();
				if($id>0)
				{
					$result['id'] = $id;
					$result['message']  = "添加成功";
					return $result;
				}
				else
				{
					$result['id'] = 0;
					$result['message']  = "添加失败";
					return $result;
				}
			}	
		}
		else
		{
			//保存
			$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$data['id']);
			if(empty($user))
			{
				$result['id'] = 0;
				$result['message'] = "非法的操作";
				return $result;
			}
			
			if(!empty($data['user_pwd']))
			{		
				$data['user_pwd'] = md5($data['user_pwd'].$user['salt']);
			}
			$data['update_time'] = NOW_TIME;
			$GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$data['id'],"SILENT");
			if($GLOBALS['db']->error()!="")
			{
				$result['id'] = 0;
				$result['message'] = $GLOBALS['db']->error();
				return $result;
			}
			else
			{				
					User::syn_nickname($data['nickname'], $data['id']);
					$result['id'] = $data['id'];
					$result['message']  = "修改成功";
					return $result;
			}
		}	

	}
	
	
	/**
	 * 检查用户相关字段有效性
	 * 返回
	 * result(status,info)
	 */
	public static function checkfield($fieldname,$fieldvalue,$uid=0)
	{
		if($fieldname=="user_key")
		{
			$user  = $GLOBALS['db']->getRow("select  *  from ".DB_PREFIX."user where (user_name = '".$fieldvalue."' or email='".$fieldvalue."' or mobile='".$fieldvalue."') and id <> ".$uid);
			if(empty($user))
			{
				return array("status"=>0,"info"=>"帐号不存在");
			}
			elseif($user['is_effect']==0)
			{
				return array("status"=>0,"info"=>"帐号被禁用");
			}
			else
			{
				return array("status"=>1);
			}
		}
		
		if($fieldname=='user_name')
		{
			//preg_match("/^[\x{4e00}-\x{9fa5}_\-]*[0-9a-zA-Z_\-]*[\x{201c}\x{201d}\x{3001}\x{uff1a}\x{300a}\x{300b\x{ff0c}\x{ff1b}\x{3002}_\-]*$/u",$fieldvalue,$matches);
			if($matches)
			{
				return array("status"=>0,"info"=>"用户名不能有非法字符");
			}
			$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where user_name = '".$fieldvalue."' and id <> ".$uid);
			if($rs>0)
			{
				return array("status"=>0,"info"=>"用户名已存在");
			}
			else
			{
				return array("status"=>1);
			}
		}
		
		
		if($fieldname=="email")
		{
			if(!check_email($fieldvalue))
			{
				return array("status"=>0,"info"=>"邮箱格式不正确");
			}
			$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where email = '".$fieldvalue."' and id <> ".$uid);
			if($rs>0)
			{
				return array("status"=>0,"info"=>"邮箱地址已存在");
			}
			else
			{
				return array("status"=>1);
			}
		}
		
		if($fieldname=="mobile")
		{
			if(!check_mobile($fieldvalue))
			{
				return array("status"=>0,"info"=>"手机格式不正确");
			}
			$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where mobile = '".$fieldvalue."' and id <> ".$uid);
			if($rs>0)
			{
				return array("status"=>0,"info"=>"手机号码已存在");
			}
			else
			{
				return array("status"=>1);
			}
		}
	}
	
		
	/**
	 * 会员登录
	 * @param string $user_name_email_mobile
	 * @param string $user_pwd
	 * 返回 result(status,message,extra)extra表示为同步登录的一些信息
	 * status:0不存在 1未通过验证 2会员被禁用 3密码不对 4成功
	 */
	public static function do_login($user_name_email_mobile,$user_pwd)
	{
		$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where (user_name = '".$user_name_email_mobile."' or mobile = '".$user_name_email_mobile."' or email = '".$user_name_email_mobile."')  limit 1");
		$user_name = $user['user_name']?$user['user_name']:$user_name_email_mobile;
		$integrate  = $GLOBALS['db']->getRow("select class_name from ".DB_PREFIX."integrate");
		if($integrate)
		{
			$directory = APP_ROOT_PATH."system/integrate/";
			$file = $directory.$integrate['class_name']."_integrate.php";
			if(file_exists($file))
			{
				require_once($file);
				$integrate_class = $integrate['class_name']."_integrate";
				$integrate_item = new $integrate_class;
				$integrate_res = $integrate_item->login($user_name,$user_pwd);
			}
		}
		$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where (user_name = '".$user_name_email_mobile."' or mobile = '".$user_name_email_mobile."' or email = '".$user_name_email_mobile."')  limit 1");
		if($user)
		{
			if($user['is_verify']==0)
			{
				$result['status'] = 1;
				$result['message'] = "会员未通过验证";
				$result['user'] = $user;
			}
			elseif($user['is_effect'] == 0)
			{
				$result['status'] = 2;
				$result['message'] = "会员被管理员禁用";
				$result['user'] = $user;
			}
			elseif($user['user_pwd'] != md5($user_pwd.$user['salt']))
			{
				$result['status'] = 3;
				$result['message'] = "密码不匹配";
				$result['user'] = $user;
			}
			else
			{				
				$result['script'] = $integrate_res['msg'];
				$result['status'] = 4;
				$result['message'] = "登录成功";
				es_session::start();			
				es_session::set('login_type', 'normal');
				es_session::close();	
				User::do_login_save($user);
				$result['user'] = $user;
			}
		}
		else
		{
			$result['status'] = 0;
			$result['message'] = "会员不存在";
			$result['user'] = $user;
		}
		return $result;
	}
	
	/**
	 * 通过cookie中存放的识别码自动登录
	 */
	public static function auto_do_login()
	{
		$cookie_key = es_cookie::get("fanwetour_user_cookie");
		if($cookie_key)
		{
			$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where cookie_key ='".$cookie_key."'");
			if($user)
			{
				if($user['is_effect']==1&&$user['is_verify']==1&&$user['cookie_expire']>NOW_TIME)
				{				
					User::do_login_save($user);
				}
			}
		}
	}
	
	/**
	 * 会员登录成功后保存登录信息，同步
	 */
	public static function do_login_save($user)
	{
		$GLOBALS['db']->query("update ".DB_PREFIX."user set is_daily_login = 1 where id = ".$user['id']." and (FROM_UNIXTIME(login_time,'%Y %D %M')<>FROM_UNIXTIME(".NOW_TIME.",'%Y %D %M'))");		
		$GLOBALS['db']->query("update ".DB_PREFIX."user set login_ip = '".CLIENT_IP."',login_time= ".NOW_TIME." where id = ".$user['id']);		
		User::user_level_locate($user['id']);	
		es_session::start();
		$GLOBALS['user'] = $user;
		es_session::set("fanwetour_user_".app_conf("AUTH_KEY"), $GLOBALS['user']);
		es_session::close();
	}
	
	/**
	 * 会员登录
	 */
	public static function do_logout()
	{
		es_session::start();
		es_session::delete("fanwetour_user_".app_conf("AUTH_KEY"));
		es_session::close();
		es_cookie::delete("fanwetour_user_cookie");		
	}
	
	/**
	 *  更改会员帐号，钱，经验，积分，代金，并生成日志
	 * @param unknown_type $user_id
	 * @param unknown_type $type 1.金钱(分) 2.积分 3.经验 4.代金券(只生成日志)
	 * @param unknown_type $val
	 * @param unknown_type $info
	 * 返回status,message
	 */
	public static function modify_account($user_id,$type,$val,$info)
	{
		$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id);
		if(empty($user))
		{
			return array(
					"status"=>false,
					"message"	=>	'会员不存在'
					);
		}
		if($val==0)
		{
			return array(
					"status"=>false,
					"message"	=>	'更新值不能为零'
			);
		}
		if($type==1)
		{
			
			$GLOBALS['db']->query("update ".DB_PREFIX."user set money = money + ".$val." where money + ".$val.">=0 and id = ".$user_id);
			if($GLOBALS['db']->affected_rows()==0)
			{
				return array(
						"status"=>false,
						"message"	=>	'余额不足'
				);
			}
			$log['money'] = $val;
		}
		elseif($type==2)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."user set score = score + ".$val." where score + ".$val.">=0 and id = ".$user_id);
			if($GLOBALS['db']->affected_rows()==0)
			{
				return array(
						"status"=>false,
						"message"	=>	'积分余额不足'
				);
			}
			$log['score'] = $val;
		}
		elseif($type==3)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."user set exp = exp + ".$val." where   id = ".$user_id);			
			//会员经验自动升级
			User::user_level_locate($user_id);
			$log['exp'] = $val;
		}
		else
		{
			$log['voucher_money'] = $val;
		}
		
		$log['user_id'] = $user_id;
		$log['log_time'] = NOW_TIME;
		$log['log_ip'] = CLIENT_IP;
		$log['log_type'] = $type;
		$log['log_info'] = $info;
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."user_log",$log,"INSERT","","SILENT");		
		return array(
				"status"=>true,
				"message"	=>	'更新成功'
		);
	}
	
	
	/**
	 * 会员升级降级
	 * @param unknown_type $user_id
	 */
	public static function user_level_locate($user_id)
	{
		$user_exp = $GLOBALS['db']->getOne("select exp from ".DB_PREFIX."user where id = ".$user_id);
		$user_level = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_level where exp <= ".$user_exp." order by exp desc");
		$GLOBALS['db']->query("update ".DB_PREFIX."user set level_id = ".intval($user_level['id'])." where id = ".$user_id." and level_id <> ".intval($user_level['id']));
		if($GLOBALS['db']->affected_rows()>0)
		{
			User::send_message($user_id, "会员升级通知", "您的会员等级改变了，你当前等级是[".$user_level['name']."]");
		}
		if($GLOBALS['user']&&$GLOBALS['user']['id']==$user_id)
		$GLOBALS['user']['level_id'] = intval($user_level['id']);
	}
	
	/**
	 * 前台获取已登录的用户数据
	 * 返回user数据集
	 */
	public static function load_user()
	{
		$user = $GLOBALS['user'];
		if(empty($user))
		{
			es_session::start();
			$user = es_session::get("fanwetour_user_".app_conf("AUTH_KEY"));
			es_session::close();
		}
		return $user;
	}
	
	/**
	 * 登录后用于刷新会员最新数据，一般用于会员信息产生变更的时候刷新，重新保存session
	 */
	public static function reload_user()
	{
		$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($GLOBALS['user']['id']));
		if($user)
		{
			User::user_level_locate($user['id']);
			$GLOBALS['user'] = $user;
			es_session::start();
			es_session::set("fanwetour_user_".app_conf("AUTH_KEY"), $GLOBALS['user']);
			es_session::close();
		}
		else
		{
			es_session::start();
			es_session::delete("fanwetour_user_".app_conf("AUTH_KEY"));
			es_session::close();
		}
	}
	

	/**
	 * 生成会员动态
	 * array:image_list=array(
                                array("title"=>xxx , "src"=>xxxx), //src:./public/....
                                array("title"=>xxx , "src"=>xxxx),
                                array("title"=>xxx , "src"=>xxxx)
                            );
	 * from_type 动态来源(1.购物分享 2.游记发表 3.点评发表... 后续待扩展)
	 * $review_id 点评id
	 * 返回 会员动态的id
	 */
	public static function gen_active($user_data,$content,$image_list,$from_type,$from_id,$tag_match,$tag_match_row,$review_id)
	{
		$active = array();
		$active['user_id'] = intval($user_data['id']);
		$active['nickname'] = $user_data['nickname'];
		$active['content'] = $content;
		$active['image_list'] = serialize($image_list);
		$active['from_type'] = $from_type;
		$active['from_id'] = $from_id;
		$active['tag_match'] = $tag_match;
		$active['tag_match_row'] = $tag_match_row;
		$active['review_id'] = intval($review_id);
		$GLOBALS['db']->autoExecute(DB_PREFIX."user_active",$active,"INSERT","","SILENT");
                $id = $GLOBALS['db']->insert_id();
                self::syn_user_active_count($active['user_id']); //同步用户动态统计数据
		return $id;
		
	}
        
        /**
         * 删除用户动态
         * user_id
         * from_type 动态来源(1.购物分享 2.游记发表 3.线路点评 4.门票点评... 后续待扩展)
         * rel_id 关联编号
	 * 同步减少会员动态数
	 * 返回 更新状态
	 */
	public static function del_active($uid,$from_type,$rel_id)
	{
            $GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."user_active WHERE user_id=".$uid." AND from_type=".$from_type." AND from_id=".$rel_id);
            if ($GLOBALS['db']->error()=="") {
                self::syn_user_active_count($uid);
                return true;
            } else {
                return false;
            }

	}
        /**
         * 批量删除用户动态
         * @param type $from_type 动态类型(1.购物分享 2.游记发表 [3.线路点评 4.门票点评]... 后续待扩展)
         *                         如果是点评：任意传一个3或者4
         * @param array $del_active_data 
         *               array(
         *                      [0]=>array('uid'=>"1","ids"=>"5,3,31,52"),
         *                      [1]=>array('uid'=>"2","ids"=>"683,431,98"),
         *                  );
         * 转换代码参考示例
         *  //游记数据
         *  $guide_data = $GLOBALS['db']->getAll("SELECT user_id,group_concat(CAST(id as char)) AS ids FROM ".DB_PREFIX."tour_guide where id in (".$id.") GROUP BY user_id");
         *  //设置删除动态格式
         *  foreach($guide_data as $k=>$v){
         *      $temp['uid'] = $v['user_id'];
         *      $temp['ids'] =  $v['ids'];
         *      $del_user_active[] = $temp;
         *  }
         * 
         * @return boolean
         */
        public static function batch_del_active($from_type,$del_active_data)
	{
            if(!empty($del_active_data) && is_array($del_active_data)){
                
                if($from_type == 3 || $from_type ==4){
                    $condition = " WHERE (from_type=3 or from_type=4) ";
                }else{
                    $condition = " WHERE from_type = ".$from_type;
                }
                foreach ($del_active_data as $k=>$v){
                    $condition .=" AND user_id=".$v['uid'];
                    $condition .=" AND from_id in (".$v['ids'].") ";
                    $GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."user_active ".$condition);
                    if ($GLOBALS['db']->error()=="") {
                        self::syn_user_active_count($v['uid']);
                    }
                }
            }
            return FALSE;
	}
        /**
         * 同步用户动态统计数据
         * @param type $uid
         */
        public static function syn_user_active_count($uid){
            $active_count = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX."user_active WHERE user_id = ".intval($uid));
            $GLOBALS['db']->query("UPDATE ".DB_PREFIX."user SET active_count=".$active_count." WHERE id=".intval($uid));
        }
	
	/**
	 * 发送到微博
	 * @param unknown_type $user_id
	 * @param unknown_type $url
	 * @param unknown_type $content
         * @param unknown_type $image_list
	 */
	public static function send_weibo($user_data,$content,$image_list,$url)
	{
		$weibo = array();
		if($image_list)
		{
        	$weibo['img'] = APP_ROOT_PATH.$image_list[0];
        	//$domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?SITE_DOMAIN.APP_ROOT:app_conf("PUBLIC_DOMAIN_ROOT");
        	//$weibo['img'] = str_replace("./public/images/",$domain."/public/images/",$weibo['img']);
		}
    $weibo['content'] = $content." ".$url;
    
    $api_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."api_login");
    foreach($api_list as $k=>$v)
    {
    	$c_name = $v['class_name']."_api";
    	$file = APP_ROOT_PATH."system/api_login/".$c_name.".php";
    	require_once $file;
    	$c_object = new $c_name($v);
    	$c_object->send_message($weibo);
    }
	}
	
	
	/**
	 * 产生返利记录
	 *	user_id获取返利的用户
	 * from_uid产生订单购买的用户
	 * from_otype 返利来源的订单类型(1:旅游产品 2.景点门票 ...后续可扩展)
	 * money 返利的钱：分
	 */
	public static function gen_rebate($user_id,$from_uid,$from_oid,$from_otype,$money)
	{
		$rebate['user_id'] = $user_id;
		$rebate['from_uid'] = $from_uid;
		$rebate['nickname'] = $GLOBALS['db']->getOne("select nickname from ".DB_PREFIX."user where id = ".intval($from_uid));
		$rebate['from_otype'] = $from_otype;
		$rebate['from_oid'] = $from_oid;
		$rebate['money'] = $money;
		$rebate['create_time'] = NOW_TIME;
		$GLOBALS['db']->autoExecute(DB_PREFIX."user_rebate",$rebate,"INSERT","","SILENT");
		
		$rebate_id = $GLOBALS['db']->insert_id();
		if($rebate_id>0)
		{
			$rebate['id'] = $rebate_id;
			User::pay_rebate($rebate);
		}
	}
	
	/**
	 * 发放返利
	 * @param arr $rebate 返利数据
	 */
	public static function pay_rebate($rebate)
	{
		$GLOBALS['db']->query("update ".DB_PREFIX."user_rebate set pay_time = ".NOW_TIME." where pay_time = 0 and id = ".$rebate['id']);
		if($GLOBALS['db']->affected_rows()>0)
		{
			User::modify_account($rebate['user_id'], 1, $rebate['money'], "邀请返利");
		}
	}
	
	
	/**
	 * 为会员发放站内信
	 * @param int $user_id
	 * @param string $title
	 * @param string $message
	 */
	public static function send_message($user_id,$title,$message,$system_msg_id = 0)
	{
		$msg['user_id'] = $user_id;
		$msg['msg_title'] = $title;
		$msg['msg_content'] = $message;
		$msg['msg_time'] = NOW_TIME;
		
		if($system_msg_id>0)
			$msg['system_msg_id'] = $system_msg_id;
		$GLOBALS['db']->autoExecute(DB_PREFIX."user_msg",$msg,"INSERT","","SILENT");		
		if($GLOBALS['db']->insert_id()>0)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."user set msg_count=msg_count+1,new_msg_count=new_msg_count+1 where id = ".$user_id);
			$GLOBALS['user']['msg_count']+=1;
			$GLOBALS['user']['new_msg_count']+=1;
			if($system_msg_id>0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."system_msg set send_count = send_count + 1 where id = ".$system_msg_id);
			}
		}
	}
	
	/**
	 * 发送会员验证邮件
	 * @param unknown_type $user
	 */
	public static function send_verify_email($user)
	{
		$code = rand(100000, 999999);
		$code_time = NOW_TIME + 2*3600;
		$GLOBALS['db']->query("update ".DB_PREFIX."user set email_code = '".$code."',email_code_time = ".$code_time." where id = ".$user['id']);
		
		$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_USER_VERIFY'");
		$verify['user_name'] = $user['user_name'];
		$verify['verify_url'] = url("user#doverify",array("t"=>"email","c"=>$code,"un"=>$user['user_name']));
		$GLOBALS['tmpl']->assign("user",$verify);
		$email_content = $GLOBALS['tmpl']->fetch("str:".$tmpl['content']);
			
		$msg_data['dest'] =  $user['email'];
		$msg_data['send_type'] = 1;
		$msg_data['title'] = app_conf("SITE_NAME")."会员身份验证";
		$msg_data['content'] = $email_content;
		$msg_data['send_time'] = 0;
		$msg_data['is_send'] = 0;
		$msg_data['create_time'] = NOW_TIME;
		$msg_data['user_id'] = $user['id'];
		$msg_data['is_html'] = $tmpl['is_html'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	}
	
	
	/**
	 * 发送会员邮箱变更验证邮件
	 * @param unknown_type $user
	 */
	public static function send_modify_email($user)
	{
		$code = rand(100000, 999999);
		$code_time = NOW_TIME + 2*3600;
		$GLOBALS['db']->query("update ".DB_PREFIX."user set email_code = '".$code."',email_code_time = ".$code_time." where id = ".$user['id']);
	
		$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_MODIFY'");
		$verify['user_name'] = $user['user_name'];
		$verify['email'] = $user['tmp_email'];
		$verify['verify_url'] = url("profile#dosave_email",array("c"=>$code,"un"=>$user['user_name']));
		$GLOBALS['tmpl']->assign("user",$verify);
		$email_content = $GLOBALS['tmpl']->fetch("str:".$tmpl['content']);
			
		$msg_data['dest'] =  $user['tmp_email'];
		$msg_data['send_type'] = 1;
		$msg_data['title'] = app_conf("SITE_NAME")."邮箱变更验证";
		$msg_data['content'] = $email_content;
		$msg_data['send_time'] = 0;
		$msg_data['is_send'] = 0;
		$msg_data['create_time'] = NOW_TIME;
		$msg_data['user_id'] = $user['id'];
		$msg_data['is_html'] = $tmpl['is_html'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	}
	
	
	/**
	 * 发送会员验证短信
	 * @param unknown_type $user
	 */
	public static function send_verify_mobile($user)
	{
		$code = rand(100000, 999999);
		$code_time = NOW_TIME + 2*3600;
		$GLOBALS['db']->query("update ".DB_PREFIX."user set mobile_code = '".$code."',mobile_code_time = ".$code_time." where id = ".$user['id']);
			
		$tmpl = $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_USER_VERIFY'");
		$verify['user_name'] = $user['user_name'];
		$verify['mobile'] = $user['mobile'];
		$verify['code'] = $code;
		$GLOBALS['tmpl']->assign("user",$verify);
		$sms_content = $GLOBALS['tmpl']->fetch("str:".$tmpl);
		$msg_data['dest'] = $user['mobile'];
		$msg_data['content'] = $sms_content;
		$msg_data['create_time'] = NOW_TIME;
		$msg_data['user_id'] = $user['id'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	}
	
	
	/**
	 * 发送会员手机变更验证短信
	 * @param unknown_type $user
	 */
	public static function send_modify_mobile($user)
	{
		$code = rand(100000, 999999);
		$code_time = NOW_TIME + 2*3600;
		$GLOBALS['db']->query("update ".DB_PREFIX."user set mobile_code = '".$code."',mobile_code_time = ".$code_time." where id = ".$user['id']);
	
		$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_MODIFY'");
		$verify['user_name'] = $user['user_name'];
		$verify['mobile'] = $user['tmp_mobile'];
		$verify['code'] = $code;
		$GLOBALS['tmpl']->assign("user",$verify);
		$sms_content = $GLOBALS['tmpl']->fetch("str:".$tmpl['content']);
			
		$msg_data['dest'] =  $user['tmp_mobile'];
		$msg_data['content'] = $sms_content;
		$msg_data['create_time'] = NOW_TIME;
		$msg_data['user_id'] = $user['id'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	}
	
	/**
	 * 发送会员重置密码邮件
	 * @param unknown_type $user
	 */
	public static function send_getpwd_email($user)
	{
		$code = rand(100000, 999999);
		$code_time = NOW_TIME + 2*3600;
		$GLOBALS['db']->query("update ".DB_PREFIX."user set pwd_code = '".$code."',pwd_code_time = ".$code_time." where id = ".$user['id']);
	
		$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_USER_GETPWD'");
		$verify['user_name'] = $user['user_name'];
		$verify['verify_url'] = url("user#getpwd_verifycode",array("t"=>"email","c"=>$code,"un"=>$user['user_name']));
		$GLOBALS['tmpl']->assign("user",$verify);
		$email_content = $GLOBALS['tmpl']->fetch("str:".$tmpl['content']);
			
		$msg_data['dest'] =  $user['email'];
		$msg_data['send_type'] = 1;
		$msg_data['title'] = app_conf("SITE_NAME")."会员密码重置";
		$msg_data['content'] = $email_content;
		$msg_data['send_time'] = 0;
		$msg_data['is_send'] = 0;
		$msg_data['create_time'] = NOW_TIME;
		$msg_data['user_id'] = $user['id'];
		$msg_data['is_html'] = $tmpl['is_html'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	}
	
	/**
	 * 发送会员取回密码的验证短信
	 * @param unknown_type $user
	 */
	public static function send_getpwd_mobile($user)
	{
		$code = rand(100000, 999999);
		$code_time = NOW_TIME + 2*3600;
		$GLOBALS['db']->query("update ".DB_PREFIX."user set pwd_code = '".$code."',pwd_code_time = ".$code_time." where id = ".$user['id']);
			
		$tmpl = $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_USER_GETPWD'");
		$verify['user_name'] = $user['user_name'];
		$verify['mobile'] = $user['mobile'];
		$verify['code'] = $code;
		$GLOBALS['tmpl']->assign("user",$verify);
		$sms_content = $GLOBALS['tmpl']->fetch("str:".$tmpl);
		$msg_data['dest'] = $user['mobile'];
		$msg_data['content'] = $sms_content;
		$msg_data['create_time'] = NOW_TIME;
		$msg_data['user_id'] = $user['id'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	}
	
	
	/**
	 * 修改会员密码
	 * @param unknown_type $user
	 * @param unknown_type $new_pwd
	 * @return multitype:number string
	 */
	public static function modify_pwd($user,$new_pwd)
	{
		$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id  =".$user['id'] );
		if($user)
		{
			$new_pwd = md5($new_pwd.$user['salt']);
			if($new_pwd==$user['user_pwd'])
			{
				return array("status"=>0,"info"=>"新密码与原密码相同");
			}
			else
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."user set user_pwd = '".$new_pwd."',update_time = ".NOW_TIME." where id = ".$user['id'],"SILENT");
				if($GLOBALS['db']->error()!="")
				{
					return array("status"=>0,"info"=>"系统繁忙，请重试");
				}
				else
				{
					return array("status"=>1,"info"=>"密码修改成功");
				}
			}
		}
		else
		{
			return array("status"=>0,"info"=>"会员不存在");
		}
	}
	
	/**
	 * 充值回调成功时，为用户充值
	 * @param array $payment_notice 充值的支付单
	 */
	public static function doincharge($payment_notice)
	{
		$GLOBALS['db']->query("update ".DB_PREFIX."user_incharge set pay_time = ".NOW_TIME.",is_paid = 1,pay_money = pay_money + ".$payment_notice['money']." where user_id = ".$payment_notice['user_id']." and order_sn = '".$payment_notice['order_sn']."'");	
		$result = User::modify_account($payment_notice['user_id'], 1, $payment_notice['money'], "会员充值成功,充值订单号：".$payment_notice['order_sn'].",支付单号：".$payment_notice['notice_sn']);		
		User::send_message($payment_notice['user_id'], "充值成功", "您已充值成功,充值订单号：".$payment_notice['order_sn'].",支付单号：".$payment_notice['notice_sn']);
		return $result;
	}
	
	/**
	 * 获取配送地址
	 */
	public static function get_consignee($id)
	{
		$condition = " user_id = ".$GLOBALS['user']['id']." and id=".$id;
		return $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_consignee where ".$condition);
	}
        
  /**
   * 获取用户id为索引的头像数组
   * @param int or array $user_ids 用户编号可以单个ID 或者 ID 数组
   * @param int $img_w 所需要的头像宽度
   * @param int $img_h 所需要的头像高度
   * @return array(
   *                  '1'=>array(
   *                                  'avatar'=>'xxxxx50_50.jpg.',
   *                                  'img'=>'<img src="http://xxxx.50_50.jpg"/>',
   *                          ),
   *              );  二维数组
   */
  public static function get_user_avatar($user_ids,$img_w=50,$img_h=50)
  {
      $avatar_list = array();
      $condition = ' WHERE ';
      if(empty($user_ids)){
          return ;
      }
      if(is_numeric($user_ids)){
          $condition.=" id =".$user_ids;
      }else{
          $condition.=" id in(".implode(",",$user_ids).")";
      }
      $user_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."user ".$condition);
      $f_user_data = array();
      foreach($user_list as $k=>$v){
          $img_temp = get_spec_image($v['avatar'],$img_w,$img_h,1);
          $f_user_data[$v['id']]['avatar'] = get_spec_image($v['avatar'],$img_w,$img_h,1);
          $f_user_data[$v['id']]['img'] = '<img class="GUID" uid="'.$v['id'].'" src="'.$img_temp.'" style="width:'.$img_w.';height:'.$img_h.';"/>';
      }
      return $f_user_data;
  }
  /**
   * 获取用户信息，可以为一个用户或者多个用户
   * @param type $user_ids 数字或id数组
   * @return array 
   */
  public static function get_user_info($user_ids)
  {
      $condition = ' WHERE ';
      if(is_numeric($user_ids)){
          $condition.=" id =".$user_ids;
          $data = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user ".$condition);
          $result = $data;
      }else{
          $condition.=" id in(".implode(",",$user_ids).")";
          $data = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."user ".$condition);
          $f_user_data = array();
          foreach($data as $k=>$v){
              $f_user_data[$v['id']] = $v;
          }
          $result = $f_user_data;
      }

      return $result;
  }

  /**
   * 查询用户 qq信息
   * @param string $user_name_email_mobile
   * @param string $user_pwd
   * 返回 result(status,message,extra)extra表示为同步登录的一些信息
   * status: 0 第一次使用qq登录本平台未关联帐号 1 会员已存在, 但是没有关联user
   * 2.会员已存在, 并且已经关联user
   */
  public static function checkqq($user_openid,$user_nickname,$user_figureurl,$user_accesstoken)
  {
  	// status 1 已经与以前账号关联，0 第一次使用qq登录本平台，询问其是否关联已有账号 如果没有已有账号则需要注册
  	$qq = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_qq where (openid = '".$user_openid."')  limit 1");
  	$result = array();
  	if($qq && $qq['user_id'])
  	{
			$result['status'] = 2;
			$result['message'] = "会员已存在, 并且已经关联user";
			$result['user'] = $qq;
  	}
  	elseif ($qq && !$qq['user_id'])
  	{
  		$result['status'] = 1;
  		$result['message'] = "会员已存在, 但是没有关联user";
  		$result['user'] = $qq;
  	}
  	else
  	{
  		$user_qq = array();
  		$user_qq['openid'] = $user_openid;
  		$user_qq['accesstoken'] = $user_accesstoken;
  		$user_qq['nickname'] = $user_nickname;
  		$user_qq['figureurl'] = $user_figureurl;
  		// 插入数据
  		$user_qq_new = $GLOBALS['db']->autoExecute(DB_PREFIX."user_qq",$user_qq,"INSERT","","SILENT");
  		$result['status'] = 0;
  		$result['message'] = "会员新创建";
  		$result['user'] = $user_qq_new;
  	}
  	return $result;
  }

  /**
   * 发送会员取回使用qq登录时的验证短信
   * @param unknown_type $openid
   */
  public static function send_getqq_mobile($openid)
  {
  	$code = rand(100000, 999999);
  	$code_time = NOW_TIME + 2*3600;
  	$GLOBALS['db']->query("update ".DB_PREFIX."user_qq set rel_phone_code = '".$code."',rel_phone_code_time = ".$code_time." where openid = ".$openid);
  		
  	$tmpl = $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_USER_GETPWD'");
  	$verify['user_name'] = $user['user_name'];
  	$verify['mobile'] = $user['mobile'];
  	$verify['code'] = $code;
  	$GLOBALS['tmpl']->assign("user",$verify);
  	$sms_content = $GLOBALS['tmpl']->fetch("str:".$tmpl);
  	$msg_data['dest'] = $user['mobile'];
  	$msg_data['content'] = $sms_content;
  	$msg_data['create_time'] = NOW_TIME;
  	$msg_data['user_id'] = $user['id'];
  	$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
  }


  /**
   * 会员qq登录后关联帐号
   * @param string $user_name_email_mobile
   * @param string $user_pwd
   * 返回 result(status,message,extra)extra表示为同步登录的一些信息
   * status:0不存在 1未通过验证 2会员被禁用 3密码不对 4成功
   */
  public static function do_rel($user_name_email_mobile,$user_pwd,$user_openid)
  {
  	$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where (user_name = '".$user_name_email_mobile."' or mobile = '".$user_name_email_mobile."' or email = '".$user_name_email_mobile."')  limit 1");
  	$user_name = $user['user_name']?$user['user_name']:$user_name_email_mobile;
  	$integrate  = $GLOBALS['db']->getRow("select class_name from ".DB_PREFIX."integrate");
  	if($integrate)
  	{
  		$directory = APP_ROOT_PATH."system/integrate/";
  		$file = $directory.$integrate['class_name']."_integrate.php";
  		if(file_exists($file))
  		{
  			require_once($file);
  			$integrate_class = $integrate['class_name']."_integrate";
  			$integrate_item = new $integrate_class;
  			$integrate_res = $integrate_item->login($user_name,$user_pwd);
  		}
  	}
  	$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where (user_name = '".$user_name_email_mobile."' or mobile = '".$user_name_email_mobile."' or email = '".$user_name_email_mobile."')  limit 1");
  	if($user)
  	{
  		// if($user['is_verify']==0)
  		// {
  		// 	$result['status'] = 1;
  		// 	$result['message'] = "会员未通过验证";
  		// 	$result['user'] = $user;
  		// }
  		if($user['is_effect'] == 0)
  		{
  			$result['status'] = 2;
  			$result['message'] = "会员被管理员禁用";
  			$result['user'] = $user;
  		}
  		else
  		{				
  			$result['script'] = $integrate_res['msg'];
  			$result['status'] = 4;
  			$result['message'] = "登录成功";
  			$user_id = $user['id'];	
  			User::do_rel_save($user_id, $user_openid);
  			User::do_login_save($user);
  			$result['user'] = $user;
  		}
  	}
  	else
  	{
  		$result['status'] = 0;
  		$result['message'] = "会员不存在";
  		$result['user'] = $user;
  	}
  	return $result;
  }
  /**
     * 保存user_id到user_qq表里
     * @param string $user_id
     * @param string $user_openid
     * 返回 result(status,message,extra)extra表示为同步登录的一些信息
     * status: 0 失败没有这个user_qq表 1 已经有user_id，不能绑定多个 2 这个帐号已经被别的qq关联 3.关联成功,直接跳转
     */
    public static function do_rel_save($user_id,$user_openid)
    {
    	$qq = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_qq where (openid = '".$user_openid."')  limit 1");
    	$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_qq where (user_id = '".$user_id."')  limit 1");
    	if (!$qq)
    	{
    		$result['status'] = 0;
    		$result['message'] = "失败,没有这个user_qq表";
    		$result['userqq'] = '';
    	}
    	elseif($qq && $qq->user_id)
    	{
  			$result['status'] = 1;
  			$result['message'] = "已经关联user";
  			$result['userqq'] = $user_qq;
    	}
    	elseif($user)
    	{
    		$result['status'] = 2;
    		$result['message'] = "这个帐号已经被别的qq关联";
    		$result['userqq'] = $user_qq;
    	}
    	else
    	{
    	  $data = array();
    		$data['user_id'] = $user_id;
    		// 更新数据
    		$GLOBALS['db']->autoExecute(DB_PREFIX."user_qq",$data,"UPDATE","openid='".$user_openid."'","SILENT");
    		$result['status'] = 3;
    		$result['message'] = "关联成功,直接跳转";
    		$result['userqq'] = $qq;
    	}
    	return $result;
    }
    
    /**
     * 通过userid直接保存信息登录
     * @param string $user_name_email_mobile
     * @param string $user_pwd
     * 返回 result(status,message,extra)extra表示为同步登录的一些信息
     * status:0不存在 1未通过验证 2会员被禁用 3密码不对 4成功
     */
    public static function loginByUserId($user_id)
    {
    	$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where (id = '".$user_id."')  limit 1");
    	$user_name = $user['user_name']?$user['user_name']:'';
    	$integrate  = $GLOBALS['db']->getRow("select class_name from ".DB_PREFIX."integrate");
    	if($integrate)
    	{
    		$directory = APP_ROOT_PATH."system/integrate/";
    		$file = $directory.$integrate['class_name']."_integrate.php";
    		if(file_exists($file))
    		{
    			require_once($file);
    			$integrate_class = $integrate['class_name']."_integrate";
    			$integrate_item = new $integrate_class;
    			$integrate_res = $integrate_item->login($user_name,$user_pwd);
    		}
    	}

    	if($user)
    	{
    		// if($user['is_verify']==0)
    		// {
    		// 	$result['status'] = 1;
    		// 	$result['message'] = "会员未通过验证";
    		// 	$result['user'] = $user;
    		// }
    		if($user['is_effect'] == 0)
    		{
    			$result['status'] = 2;
    			$result['message'] = "会员被管理员禁用";
    			$result['user'] = $user;
    		}
    		else
    		{				
    			$result['script'] = $integrate_res['msg'];
    			$result['status'] = 4;
    			$result['message'] = "登录成功";						
    			User::do_login_save($user);
    			$result['user'] = $user;
    		}
    	}
    	else
    	{
    		$result['status'] = 0;
    		$result['message'] = "会员不存在";
    		$result['user'] = $user;
    	}
    	return $result;
    }

  /**
   * 第三方登录后自动创建user信息，并将userid保存在对应的user_qq、user_wx .etc中
   * @param string $openid 第三方登录的openid
   * @param string $type 第三方登录的类型 qq, wx, weibo
   * 返回 @param array (status,info,jump,user)
   * array("status"=>1,"info"=>"恭喜您！注册成功","jump"=>get_gopreview(), 'user'=>$user_data);
   * status: 0 失败 1 创建成功
   */
  public static function login_auto_user($openid, $type)
  {
  	$user_name = $type.'_'.$openid;
  	$user_pwd  = '123';

  	$ck = User::checkfield("user_name", $user_name);		
  	if($ck['status']==0)
  	{
  		$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where user_name = '".$user_name."'");
  		$user_id = $user['id'];
  		$relres = User::login_auto_rel($user_id, $openid, $type);
  		if ($relres) {
	  		return array(
	  			"status"=> 1,
	  			"info"=> $ck['info'],
	  			"user"=> $user);
  		}else{
  			return array(
	  			"status"=> 0,
	  			"info"=> $ck['info'],
	  			"field"=>"user_name"
	  		);
  		}
  	}

  	//会员注册时通知uc添加用户
  	$integrate  = $GLOBALS['db']->getRow("select class_name from ".DB_PREFIX."integrate");
  	if($integrate)
  	{
  		$directory = APP_ROOT_PATH."system/integrate/";
  		$file = $directory.$integrate['class_name']."_integrate.php";
  		if(file_exists($file))
  		{
  			require_once($file);
  			$integrate_class = $integrate['class_name']."_integrate";
  			$integrate_item = new $integrate_class;
  			$ck = $integrate_item->add_user($user_name,$user_pwd,$email);
  			if($ck['status']==0)
  			{
  				return array("status"=>0,"info"=>$ck['info'],"field"=>$ck['field']);
  			}
  		}
  	}
  	
  	$user_data = array();
  	$user_data['user_name'] = $user_name;
  	$user_data['salt'] = "";
  	$user_data['user_pwd'] = md5($user_pwd.$user_data['salt']);
  	$user_data['is_effect'] = 1;
  	$user_data['create_time'] = NOW_TIME;
  	$user_data['integrate_id'] = intval($ck['data']);
  	$user_data['source'] = empty($GLOBALS['ref'])?"native":$GLOBALS['ref'];  //来路
  	$user_data['pid'] = 0; //推荐人
  	$user_data['nickname'] = $user_data['user_name'];
  	$ip = CLIENT_IP ? CLIENT_IP : '0.0.0.0';
  	$user_data['regist_ip'] = $ip;
  	// require_once APP_ROOT_PATH."system/libs/city.php";
  	$user_data['regist_city'] = '上海市';
  	$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_data,"INSERT","","SILENT");
  	if($GLOBALS['db']->error()=="")
  	{
  		$user_id = $GLOBALS['db']->insert_id();
  		$user_data['id'] = $user_id;
  		//发放注册奖劢
  		if(app_conf("USER_REG_MONEY")>0)
  		{
  			USER::modify_account($user_id, 1, app_conf("USER_REG_MONEY"), "注册获赠现金");
  		}
  		if(app_conf("USER_REG_SCORE")>0)
  		{
  			USER::modify_account($user_id, 2, app_conf("USER_REG_SCORE"), "注册获赠积分");
  		}
  		if(app_conf("USER_REG_EXP")>0)
  		{
  			USER::modify_account($user_id, 3, app_conf("USER_REG_EXP"), "注册获赠经验");
  		}
  		if(app_conf("USER_REG_VOUCHER")>0)
  		{
  			require_once APP_ROOT_PATH."system/libs/voucher.php";
  			$voucher_data = Voucher::gen(app_conf("USER_REG_VOUCHER"), $user_data);
  			if($voucher_data['status'])
  			USER::modify_account($user_id, 4, $voucher_data['data']['money'], "注册获赠代金券");
  		}
  		User::user_level_locate($user_id);
  		$update = array('user_id'=> $user_id);
  		User::login_auto_rel($user_id, $openid, $type);
  		if($GLOBALS['db']->error() != '') {
  			return array('status'=>0, 'info'=>'服务器繁忙，请重试');
  		}
  		return array('status'=>1, 'info'=>'恭喜您！注册成功', 'user'=>$user_data);
  	}
  	else
  	{
  		return array("status"=>0,"info"=>"服务器繁忙，请重试","field"=>"");
  	}
  }

  /**
   * 将userid保存在对应的user_qq、user_wx .etc中
   * @param string $openid 第三方登录的openid
   * @param string $type 第三方登录的类型 qq, wx, weibo
   * 返回 @param Boolean (false, true)
   */
  public static function login_auto_rel($user_id, $openid, $type)
  {
		$update = array('user_id'=> $user_id);
		$GLOBALS['db']->autoExecute(DB_PREFIX.'user_'.$type, $update, 'UPDATE', 'openid='.$openid, 'SILENT');
		if($GLOBALS['db']->error() != '') {
			return false;
		}
		return true;
  	
  }
}
?>