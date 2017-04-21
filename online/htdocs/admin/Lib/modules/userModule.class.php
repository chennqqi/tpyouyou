<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class userModule extends AuthModule
{	
	public function index()
	{				
		$param = array();		
		//条件
		$condition = " 1 = 1 ";
		if(isset($_REQUEST['user_name']))
			$name_key = strim($_REQUEST['user_name']);
		else
			$name_key = "";
		$param['user_name'] = $name_key;
		if($name_key!='')
		{
			$condition.=" and user_name =  '".$name_key."' ";
		}
		
		if(isset($_REQUEST['email']))
			$email_key = strim($_REQUEST['email']);
		else
			$email_key = "";
		$param['email'] = $email_key;
		if($email_key!='')
		{
			$condition.=" and email =  '".$email_key."' ";
		}
		
		if(isset($_REQUEST['mobile']))
			$mobile_key = strim($_REQUEST['mobile']);
		else
			$mobile_key = "";
		$param['mobile'] = $mobile_key;
		if($mobile_key!='')
		{
			$condition.=" and mobile =  '".$mobile_key."' ";
		}
		
		if(isset($_REQUEST['group_id']))
			$group_key = intval($_REQUEST['group_id']);
		else
			$group_key = 0;
		$param['group_id'] = $group_key;
		if($group_key>0)
		{
			$condition.=" and group_id =  '".$group_key."' ";
		}
		
		if(isset($_REQUEST['level_id']))
			$level_key = intval($_REQUEST['level_id']);
		else
			$level_key = 0;
		$param['level_id'] = $level_key;
		if($level_key>0)
		{
			$condition.=" and level_id =  '".$level_key."' ";
		}
		
		//分页
		if(isset($_REQUEST['numPerPage']))
		{			
			$param['pageSize'] = intval($_REQUEST['numPerPage']);
			if($param['pageSize'] <=0||$param['pageSize'] >200)
				$param['pageSize'] = ADMIN_PAGE_SIZE;
		}
		else
			$param['pageSize'] = ADMIN_PAGE_SIZE;
			
		if(isset($_REQUEST['pageNum']))
			$page = intval($_REQUEST['pageNum']);
		else
			$page = 0;
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$param['pageSize']).",".$param['pageSize'];
		$param['pageNum'] = $page;
		
		
		//排序
		if(isset($_REQUEST['orderField']))
			$param['orderField'] = strim($_REQUEST['orderField']);
		else
			$param['orderField'] = "id";
		
		if(isset($_REQUEST['orderDirection']))
			$param['orderDirection'] = strim($_REQUEST['orderDirection'])=="asc"?"asc":"desc";
		else
			$param['orderDirection'] = "desc";
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where ".$condition);
		$user_level = load_auto_cache("user_level");
		$GLOBALS['tmpl']->assign('user_level',$user_level);
		$user_group = load_auto_cache("user_group");
		$GLOBALS['tmpl']->assign('user_group',$user_group);
		foreach($list as $k=>$v)
		{
			$list[$k]['create_time'] = to_date($v['create_time']);
			$list[$k]['login_time'] = to_date($v['login_time']);
			$list[$k]['is_effect_show'] = lang("IS_EFFECT_".$v['is_effect']);
			$list[$k]['level_name'] = $user_level[$v['level_id']]?$user_level[$v['level_id']]['name']:"未知";
			$list[$k]['group_name'] = $user_group[$v['group_id']]?$user_group[$v['group_id']]['name']:"未知";
			$list[$k]['format_money'] =format_price(format_price_to_display($list[$k]['money'])); 
		}

		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("user"));
		$GLOBALS['tmpl']->assign("accounturl",admin_url("user#account",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("seteffecturl",admin_url("user#set_effect",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("delurl",admin_url("user#foreverdelete",array('ajax'=>1)));	
		$GLOBALS['tmpl']->assign("exporturl",admin_url("user#export_csv"));
		$GLOBALS['tmpl']->assign("editurl",admin_url("user#edit"));
		$GLOBALS['tmpl']->assign("addurl",admin_url("user#add"));
		$GLOBALS['tmpl']->display("core/user/index.html");
	}	
	
	public function export_csv($page=1)
	{
		$param = array();
		//条件
		$condition = " 1 = 1 ";
		if(isset($_REQUEST['user_name']))
			$name_key = strim($_REQUEST['user_name']);
		else
			$name_key = "";
		$param['user_name'] = $name_key;
		if($name_key!='')
		{
			$condition.=" and user_name =  '".$name_key."' ";
		}
		
		if(isset($_REQUEST['email']))
			$email_key = strim($_REQUEST['email']);
		else
			$email_key = "";
		$param['email'] = $email_key;
		if($email_key!='')
		{
			$condition.=" and email =  '".$email_key."' ";
		}
		
		if(isset($_REQUEST['mobile']))
			$mobile_key = strim($_REQUEST['mobile']);
		else
			$mobile_key = "";
		$param['mobile'] = $mobile_key;
		if($mobile_key!='')
		{
			$condition.=" and mobile =  '".$mobile_key."' ";
		}
		
		if(isset($_REQUEST['group_id']))
			$group_key = intval($_REQUEST['group_id']);
		else
			$group_key = 0;
		$param['group_id'] = $group_key;
		if($group_key>0)
		{
			$condition.=" and group_id =  '".$group_key."' ";
		}
		
		if(isset($_REQUEST['level_id']))
			$level_key = intval($_REQUEST['level_id']);
		else
			$level_key = 0;
		$param['level_id'] = $level_key;
		if($level_key>0)
		{
			$condition.=" and level_id =  '".$level_key."' ";
		}
		
		//分页
		if(isset($_REQUEST['numPerPage']))
		{
			$param['pageSize'] = intval($_REQUEST['numPerPage']);
			if($param['pageSize'] <=0||$param['pageSize'] >200)
				$param['pageSize'] = ADMIN_PAGE_SIZE;
		}
		else
			$param['pageSize'] = ADMIN_PAGE_SIZE;
		
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$param['pageSize']).",".$param['pageSize'];
		$param['pageNum'] = $page;
		
		
		//排序
		if(isset($_REQUEST['orderField']))
			$param['orderField'] = strim($_REQUEST['orderField']);
		else
			$param['orderField'] = "id";
		
		if(isset($_REQUEST['orderDirection']))
			$param['orderDirection'] = strim($_REQUEST['orderDirection'])=="asc"?"asc":"desc";
		else
			$param['orderDirection'] = "desc";
		
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$user_level = load_auto_cache("user_level");
		$user_group = load_auto_cache("user_group");
		
		
		$user_form = array(
				'user_name'=>'""',
				'email'=>'""',
				'mobile'=>'""',
				'group_name'=>'""',
				'level_name'=>'""',
				'create_time'=>'""',
				'regist_city'=>'""',
				'sex'=>'""',
				'birthday'=>'""',
				'paper_type'=>'""',
				'paper_sn'=>'""',
				'province'=>'""',
				'city'=>'""',
				'address'=>'""',
				'zip_code'=>'""');
		if($page == 1)
		{
			$content = iconv("utf-8","gbk","会员名,邮箱地址,手机号码,会员组,会员等级,注册时间,注册地,性别,生日,证件类型,证件号,所在省份,所在地市,地址,邮编");
			$content = $content . "\n";
		}
		
		if($list)
		{
			register_shutdown_function(array(&$this, 'export_csv'), $page+1);
			foreach($list as $k=>$v)
			{
				$user_list = array();
				$user_list['user_name'] = '"' . iconv('utf-8','gbk',$v['user_name']) . '"';
				$user_list['email'] = '"' . iconv('utf-8','gbk',$v['email']) . '"';
				$user_list['mobile'] = '"' . iconv('utf-8','gbk',$v['mobile']) . '"';
				$group_name =  $user_group[$v['group_id']]?$user_group[$v['group_id']]['name']:"未知";
				$user_list['group_name'] = '"' . iconv('utf-8','gbk',$group_name) . '"';
				$level_name =  $user_level[$v['level_id']]?$user_level[$v['level_id']]['name']:"未知";
				$user_list['level_name'] = '"' . iconv('utf-8','gbk',$level_name) . '"';
				$user_list['create_time'] = '"' . iconv('utf-8','gbk',to_date($v['create_time'])) . '"';
				$user_list['regist_city'] = '"' . iconv('utf-8','gbk',$v['regist_city']) . '"';
				$sex = "未知";
				if($v['sex']==1)$sex="男";
				if($v['sex']==0)$sex="女";
				$user_list['sex'] = '"' . iconv('utf-8','gbk',$sex) . '"';
				$user_list['birthday'] = '"' . iconv('utf-8','gbk',$v['birthday']) . '"';
				$paper_type = "其他";
				if($v['paper_type']==1)$paper_type="身份证";
				if($v['paper_type']==2)$paper_type="护照";
				if($v['paper_type']==3)$paper_type="军官证";
				if($v['paper_type']==4)$paper_type="港澳通行证";
				if($v['paper_type']==5)$paper_type="台胞证";
				$user_list['paper_type'] = '"' . iconv('utf-8','gbk',$paper_type) . '"';
				$user_list['paper_sn'] = '"' . iconv('utf-8','gbk',$v['paper_sn']) . '"';
				$province = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."province where id = ".$v['province_id']);
				$user_list['province'] = '"' . iconv('utf-8','gbk',$province) . '"';
				$city = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."city where id = ".$v['city_id']);
				$user_list['city'] = '"' . iconv('utf-8','gbk',$city) . '"';
				$user_list['address'] = '"' . iconv('utf-8','gbk',$v['address']) . '"';
				$user_list['zip_code'] = '"' . iconv('utf-8','gbk',$v['zip_code']) . '"';
		
				$content .= implode(",", $user_list) . "\n";
		
			}
		}
		
		header("Content-Disposition: attachment; filename=user_list.csv");
		echo $content;
	}
	
	public function foreverdelete()
	 {
		
		$ajax = intval($_REQUEST['ajax']);		
		if (isset ( $_REQUEST ['id'] ))
		{
			$id = strim($_REQUEST ['id']);			
			$id = format_ids_str($id);
			if($id)
			{	
				$del_name = $GLOBALS['db']->getOne("select group_concat(user_name) from ".DB_PREFIX."user where id in (".$id.")");		
				$sql = "delete from ".DB_PREFIX."user where id in (".$id.")";
				$GLOBALS['db']->query($sql);				
				if($GLOBALS['db']->affected_rows()>0)
				{
					//$GLOBALS['db']->query("delete from ".DB_PREFIX."comment where user_id in (".$id.") ");
					$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_msg_list where user_id in (".$id.") ");
					$GLOBALS['db']->query("delete from ".DB_PREFIX."hotel_room_cart where user_id in (".$id.") ");
					//$GLOBALS['db']->query("delete from ".DB_PREFIX."hotel_room_order where user_id in (".$id.") ");
					//$GLOBALS['db']->query("delete from ".DB_PREFIX."hotel_room_order_item where user_id in (".$id.") ");
					//$GLOBALS['db']->query("delete from ".DB_PREFIX."payment_notice where user_id in (".$id.") ");
					//$GLOBALS['db']->query("delete from ".DB_PREFIX."review where user_id in (".$id.") ");
					$GLOBALS['db']->query("delete from ".DB_PREFIX."ticket_cart where user_id in (".$id.") ");
					//$GLOBALS['db']->query("delete from ".DB_PREFIX."ticket_order where user_id in (".$id.") ");
					//$GLOBALS['db']->query("delete from ".DB_PREFIX."ticket_order_item where user_id in (".$id.") ");
					//$GLOBALS['db']->query("delete from ".DB_PREFIX."tour_guide where user_id in (".$id.") ");
					//$GLOBALS['db']->query("delete from ".DB_PREFIX."tourline_order where user_id in (".$id.") ");
					//$GLOBALS['db']->query("delete from ".DB_PREFIX."user_active where user_id in (".$id.") ");
					$GLOBALS['db']->query("delete from ".DB_PREFIX."user_deposit where user_id in (".$id.") ");
					$GLOBALS['db']->query("delete from ".DB_PREFIX."user_fans where user_id in (".$id.") ");
					//$GLOBALS['db']->query("delete from ".DB_PREFIX."user_fans where fans_id in (".$id.") ");
					$GLOBALS['db']->query("delete from ".DB_PREFIX."user_follow where user_id in (".$id.") ");
					//$GLOBALS['db']->query("delete from ".DB_PREFIX."user_follow where follow_id in (".$id.") ");
					//$GLOBALS['db']->query("delete from ".DB_PREFIX."user_incharge where user_id in (".$id.") ");
				    $GLOBALS['db']->query("delete from ".DB_PREFIX."user_log where user_id in (".$id.") ");
					$GLOBALS['db']->query("delete from ".DB_PREFIX."user_msg where user_id in (".$id.") ");
					//$GLOBALS['db']->query("delete from ".DB_PREFIX."user_rebate where user_id in (".$id.") ");
					//$GLOBALS['db']->query("delete from ".DB_PREFIX."voucher where user_id in (".$id.") ");
					$GLOBALS['db']->query("delete from ".DB_PREFIX."ask where user_id in (".$id.") ");
					save_log(lang("DEL").":".$del_name, 1);
				}
				showSuccess(lang("FOREVER_DELETE_SUCCESS"),$ajax);				
			}
			else
			{
				save_log(lang("DEL")."ID:".strim($_REQUEST ['id']), 0);
				showErr(lang("INVALID_OPERATION"),$ajax);
			}			
		}
		else
		{
			showErr(lang("INVALID_OPERATION"),$ajax);
		}

	}
	
	

	
	
	public function add()
	{		
		$group_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_group");
		$GLOBALS['tmpl']->assign("group_list",$group_list);
		$province_list = load_auto_cache("province_list");
		$GLOBALS['tmpl']->assign("province_list",$province_list);
		$GLOBALS['tmpl']->assign("formaction",admin_url("user#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("loadcityurl",admin_url("city#load_city",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/user/add.html");
	}
	
	
	public function insert() {
		$ajax = intval($_REQUEST['ajax']);
		
		$data['user_name'] = strim($_REQUEST['user_name']);
		$data['nickname'] = strim($_REQUEST['nickname']);
		$data['user_pwd'] = strim($_REQUEST['user_pwd']);
		$data['cfm_user_pwd'] = strim($_REQUEST['cfm_user_pwd']);
		if(strim($_REQUEST['email'])!="")
		$data['email'] = strim($_REQUEST['email']);
		if(strim($_REQUEST['mobile'])!="")
		$data['mobile'] = strim($_REQUEST['mobile']);
		$data['is_verify'] = intval($_REQUEST['is_verify']);
		$data['is_effect'] = intval($_REQUEST['is_effect']);
		$data['is_temp'] = intval($_REQUEST['is_temp']);
		$data['is_modify_nickname'] = intval($_REQUEST['is_modify_nickname']);
		$data['group_id'] = intval($_REQUEST['group_id']);
		$data['truename'] = strim($_REQUEST['truename']);
		$data['sex'] = intval($_REQUEST['sex']);
		$data['birthday'] = strim($_REQUEST['birthday']);
		$data['paper_type'] = intval($_REQUEST['paper_type']);
		$data['paper_sn'] = strim($_REQUEST['paper_sn']);
		$data['province_id'] = intval($_REQUEST['province_id']);
		$data['city_id'] = intval($_REQUEST['city_id']);
		$data['address'] = strim($_REQUEST['address']);
		$data['zip_code'] = strim($_REQUEST['zip_code']);
		$data['avatar'] = format_domain_to_relative(strim($_REQUEST['avatar']));
		
		require_once APP_ROOT_PATH."system/libs/user.php";

		$result = User::admin_save($data);

				
		// 更新数据		
		$log_info = $data['user_name'];
		if ($result['id']>0) {
			//成功提示
			save_log($log_info.lang("INSERT_SUCCESS"),1);
			showSuccess(lang("INSERT_SUCCESS"),$ajax,admin_url("user#add"));
		} else {
			//错误提示
			showErr(lang("INSERT_FAILED")."<br />".$result['message'],$ajax);
		}	

	}
	
	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$id);
		if($vo['birthday']==0)$vo['birthday']  = "";
		$GLOBALS['tmpl']->assign ( 'vo', $vo );
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("user#update",array("ajax"=>1)));
		
		$group_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_group");
		$GLOBALS['tmpl']->assign("group_list",$group_list);
		$province_list = load_auto_cache("province_list");
		$GLOBALS['tmpl']->assign("province_list",$province_list);
		
		$city_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."city where pid = '".$vo['province_id']."' order by py_first");
		$GLOBALS['tmpl']->assign("city_list",$city_list);
		$GLOBALS['tmpl']->assign("loadcityurl",admin_url("city#load_city",array("ajax"=>1)));
		
		$GLOBALS['tmpl']->display("core/user/edit.html");
	}

	
	public function update() {
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);
		
		$data['id'] = $id;
		$data['user_name'] = strim($_REQUEST['user_name']);
		$data['nickname'] = strim($_REQUEST['nickname']);
		$data['user_pwd'] = strim($_REQUEST['user_pwd']);
		$data['cfm_user_pwd'] = strim($_REQUEST['cfm_user_pwd']);
		if(strim($_REQUEST['email'])!="")
		$data['email'] = strim($_REQUEST['email']);
		if(strim($_REQUEST['mobile'])!="")
		$data['mobile'] = strim($_REQUEST['mobile']);
		$data['is_verify'] = intval($_REQUEST['is_verify']);
		$data['is_effect'] = intval($_REQUEST['is_effect']);
		$data['is_temp'] = intval($_REQUEST['is_temp']);
		$data['is_modify_nickname'] = intval($_REQUEST['is_modify_nickname']);
		$data['group_id'] = intval($_REQUEST['group_id']);
		$data['truename'] = strim($_REQUEST['truename']);
		$data['sex'] = intval($_REQUEST['sex']);
		$data['birthday'] = strim($_REQUEST['birthday']);
		$data['paper_type'] = intval($_REQUEST['paper_type']);
		$data['paper_sn'] = strim($_REQUEST['paper_sn']);
		$data['province_id'] = intval($_REQUEST['province_id']);
		$data['city_id'] = intval($_REQUEST['city_id']);
		$data['address'] = strim($_REQUEST['address']);
		$data['zip_code'] = strim($_REQUEST['zip_code']);
		$data['avatar'] = format_domain_to_relative(strim($_REQUEST['avatar']));
		
		require_once APP_ROOT_PATH."system/libs/user.php";

		$result = User::admin_save($data);

				
		// 更新数据		
		$log_info = $data['user_name'];
		if ($result['id']>0) {
			//成功提示
			save_log($log_info.lang("UPDATE_SUCCESS"),1);
			showSuccess(lang("UPDATE_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("UPDATE_FAILED")."<br />".$result['message'],$ajax);
		}

	}
	
	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$id);
		$c_is_effect =  $GLOBALS['db']->getOne("select is_effect from ".DB_PREFIX."user where id = ".$id); //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		$GLOBALS['db']->query("update ".DB_PREFIX."user set is_effect = ".$n_is_effect." where id = ".$id);
		save_log($info.lang("SET_EFFECT_".$n_is_effect),1);
		showSuccess(lang("SET_EFFECT_".$n_is_effect),$ajax)	;
	}
	
	
	public function account()
	{
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST['id']);
		$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$id);
		if(empty($user))
		{
			showErr("会员不存在",$ajax)	;
		}

		$user_level = load_auto_cache("user_level");
		$user_group = load_auto_cache("user_group");
		
		$user['pid_user'] = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$user['pid']);
		$user['pid_user']=$user['pid_user']?$user['pid_user']:"无";
		$user['money'] = format_price(format_price_to_display($user['money']));
		$user['level_name'] = $user_level[$user['level_id']]['name'];
		$user['group_name'] = $user_group[$user['group_id']]['name'];
		$user['province'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."province where id = ".$user['province_id']);
		$user['city'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."city where id = ".$user['city_id']);
		$user['voucher_total'] = format_price(format_price_to_display(intval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."voucher where user_id = ".$id))));
		
		$GLOBALS['tmpl']->assign("user",$user);
		
	
		$GLOBALS['tmpl']->assign("userlogurl",admin_url("user#account_log",array("ajax"=>1,id=>$id)));
		$GLOBALS['tmpl']->assign("usertourlineorderurl",admin_url("user#tourline_order",array("ajax"=>1,id=>$id)));
		$GLOBALS['tmpl']->assign("userticketorderurl",admin_url("user#ticket_order",array("ajax"=>1,id=>$id)));
		$GLOBALS['tmpl']->assign("userguideurl",admin_url("user#user_guide",array("ajax"=>1,id=>$id)));
		$GLOBALS['tmpl']->assign("userreviewurl",admin_url("user#user_review",array("ajax"=>1,id=>$id)));

		$GLOBALS['tmpl']->assign("voucher_url",admin_url("voucher",array("user_id"=>$user['id'])));
		$GLOBALS['tmpl']->assign("editurl",admin_url("user#edit",array("ajax"=>1,id=>$id)));
		$GLOBALS['tmpl']->assign("opmoneyurl",admin_url("user#op_account",array("ajax"=>1,"type"=>"money",id=>$id)));
		$GLOBALS['tmpl']->assign("opscoreurl",admin_url("user#op_account",array("ajax"=>1,"type"=>"score",id=>$id)));
		$GLOBALS['tmpl']->assign("opexpurl",admin_url("user#op_account",array("ajax"=>1,"type"=>"exp",id=>$id)));
		
		$GLOBALS['tmpl']->display("core/user/account.html");
	}
	
	public function op_account()
	{
		$type = strim($_REQUEST['type']);
		if($type!="money"&&$type!="score"&&$type!="exp")$type="money";
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$id);
		if(empty($user))
		{
			showErr("会员不存在",$ajax)	;
		}
		$user['money'] = format_price(format_price_to_display($user['money']));
		$GLOBALS['tmpl']->assign("user",$user);
		$GLOBALS['tmpl']->assign("type",$type);
		$GLOBALS['tmpl']->assign("formaction",admin_url("user#do_op_account",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("accounturl",admin_url("user#account",array("ajax"=>1,id=>$id)));
		$GLOBALS['tmpl']->display("core/user/op_account.html");
		
	}
	
	public function do_op_account()
	{
		require_once APP_ROOT_PATH."system/libs/user.php";
		$type = strim($_REQUEST['type']);
		$id  = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$id);
		if(empty($user))
		{
			showErr("会员不存在",$ajax)	;
		}
		$log = strim($_REQUEST['op_log']);
		if($type=="money")
		{
			$money = format_price_to_db(floatval($_REQUEST['op_value']));
			if($money>0)
				$res = User::modify_account($id, 1, $money, "管理员充值：".$log);
			else
				$res = User::modify_account($id, 1, $money, "管理员扣款：".$log);
			if(!$res['status'])
			{
				showErr($res['message'],$ajax)	;
			}
		}
		else if($type=="score")
		{
			$score = intval($_REQUEST['op_value']);
			if($score>0)
				$res = User::modify_account($id, 2, $score, "管理员增加积分：".$log);
			else
				$res = User::modify_account($id, 2, $score, "管理员扣除积分：".$log);
			if(!$res['status'])
			{
				showErr($res['message'],$ajax)	;
			}
		}
		else if($type=="exp")
		{
			$exp = intval($_REQUEST['op_value']);
			if($exp>0)
				$res = User::modify_account($id, 3, $exp, "管理员增加经验：".$log);
			else
				$res = User::modify_account($id, 3, $exp, "管理员扣除经验：".$log);
			if(!$res['status'])
			{
				showErr($res['message'],$ajax)	;
			}
		}
		
		showSuccess("更新成功",$ajax)	;
	}
	
	
	public function account_log()
	{
		$param = array();
		$id = intval($_REQUEST['id']);
		//条件
		$condition = " user_id =  ".$id;
		if(isset($_REQUEST['begin_time']))
			$begin_time_key = strim($_REQUEST['begin_time']);
		else
			$begin_time_key = "";
		$param['begin_time'] = $begin_time_key;
		if($begin_time_key!='')
		{
			$begin_time = to_timespan($begin_time_key);
			$condition.=" and log_time >=  '".$begin_time."' ";
		}
	
		if(isset($_REQUEST['end_time']))
			$end_time_key = strim($_REQUEST['end_time']);
		else
			$end_time_key = "";
		$param['end_time'] = $end_time_key;
		if($end_time_key!='')
		{
			$end_time = to_timespan($end_time_key);
			$condition.=" and log_time <=  '".$end_time."' ";
		}
		
	
		//分页
		if(isset($_REQUEST['numPerPage']))
		{
			$param['pageSize'] = intval($_REQUEST['numPerPage']);
			if($param['pageSize'] <=0||$param['pageSize'] >200)
				$param['pageSize'] = ADMIN_PAGE_SIZE;
		}
		else
			$param['pageSize'] = ADMIN_PAGE_SIZE;
			
		if(isset($_REQUEST['pageNum']))
			$page = intval($_REQUEST['pageNum']);
		else
			$page = 0;
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$param['pageSize']).",".$param['pageSize'];
		$param['pageNum'] = $page;
	
	
		//排序
		if(isset($_REQUEST['orderField']))
			$param['orderField'] = strim($_REQUEST['orderField']);
		else
			$param['orderField'] = "id";
	
		if(isset($_REQUEST['orderDirection']))
			$param['orderDirection'] = strim($_REQUEST['orderDirection'])=="asc"?"asc":"desc";
		else
			$param['orderDirection'] = "desc";
	
	
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_log where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_log where ".$condition);
		foreach($list as $k=>$v)
		{
			$list[$k]['log_time'] = to_date($v['log_time']);
			$list[$k]['money'] = format_price(format_price_to_display($v['money']));
			$list[$k]['voucher_money'] = format_price(format_price_to_display($v['voucher_money']));
		}
	
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
	
		$GLOBALS['tmpl']->assign("formaction",admin_url("user#account_log",array("id"=>$id,"ajax"=>1)));
		$GLOBALS['tmpl']->assign("delurl",admin_url("user#del_account_log",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/user/account_log.html");
	}
	
	public function del_account_log()
	{
		$ajax = intval($_REQUEST['ajax']);
		if (isset ( $_REQUEST ['log_id'] ))
		{
			$id = strim($_REQUEST ['log_id']);
			$id = format_ids_str($id);
			if($id)
			{
				$sql = "delete from ".DB_PREFIX."user_log where id in (".$id.")";
				$GLOBALS['db']->query($sql);
				if($GLOBALS['db']->affected_rows()>0)
				{
					save_log(lang("DEL"), 1);
				}
				showSuccess(lang("FOREVER_DELETE_SUCCESS"),$ajax);
			}
			else
			{
				save_log(lang("DEL")."ID:".strim($_REQUEST ['id']), 0);
				showErr(lang("INVALID_OPERATION"),$ajax);
			}
		}
		else
		{
			showErr(lang("INVALID_OPERATION"),$ajax);
		}
	}
	
	/**
     * 会员线路订单列表
     */
	public function tourline_order(){
		$ajax=intval($_REQUEST['ajax']);
		$id=intval($_REQUEST['id']);
		
		//条件
		$condition = " user_id =  ".$id;
		
		//订单号
		if(isset($_REQUEST['sn']))
			$sn = strim($_REQUEST['sn']);
		else
			$sn = "";
		$param['sn'] = $sn;
		if($sn!='')
		{
			$condition.=" and t.sn = '".$sn."' ";
		}
		

				
		//线路ID
		if(isset($_REQUEST['tourline_id']))
			$tourline_id = strim($_REQUEST['tourline_id']);
		else
			$tourline_id = "";
		$param['tourline_id'] = $tourline_id;
		if($tourline_id!='' && intval($tourline_id) > 0)
		{
			$condition.=" and t.tourline_id = ".intval($tourline_id)." ";
		}		
		
		
		//预定人姓名
		if(isset($_REQUEST['appoint_name']))
			$appoint_name = strim($_REQUEST['appoint_name']);
		else
			$appoint_name = "";
		$param['appoint_name'] = $appoint_name;
		if($appoint_name!='')
		{
			$condition.=" and t.appoint_name = '".$appoint_name."' ";
		}		
		
		//预定人手机
		if(isset($_REQUEST['appoint_mobile']))
			$appoint_mobile = strim($_REQUEST['appoint_mobile']);
		else
			$appoint_mobile = "";
		$param['appoint_mobile'] = $appoint_mobile;
		if($appoint_mobile!='')
		{
			$condition.=" and t.appoint_mobile = '".$appoint_mobile."' ";
		}
				
		//验证码
		if(isset($_REQUEST['verify_code']))
			$verify_code = strim($_REQUEST['verify_code']);
		else
			$verify_code = "";
		$param['verify_code'] = $verify_code;
		if($verify_code!='')
		{
			$condition.=" and t.verify_code = '".$verify_code."' ";
		}
				
		
		//支付状态
		$pay_status = -1;
		if(isset($_REQUEST['pay_status']) && strim($_REQUEST['pay_status'])!="")
			$pay_status = intval($_REQUEST['pay_status']);
		
		$param['pay_status'] = $pay_status;
		if($pay_status !=-1)
		{
			$condition .=" and t.pay_status=$pay_status ";
		}
		
		
		//退款状态
		$refund_status = -1;
		if(isset($_REQUEST['refund_status']) && strim($_REQUEST['refund_status'])!="")
			$refund_status = intval($_REQUEST['refund_status']);
		
		$param['refund_status'] = $refund_status;
		if($refund_status !=-1)
		{
			$condition .=" and t.refund_status=$refund_status ";
		}

		//订单状态
		$order_status = 0;
		if(isset($_REQUEST['order_status']) && strim($_REQUEST['order_status'])!="")
			$order_status = intval($_REQUEST['order_status']);
		
		$param['order_status'] = $order_status;
		if($order_status !=0)
		{
			$condition .=" and t.order_status=$order_status ";
		}	
		
		//是否验证
		$is_verify = intval($_REQUEST['is_verify']);
		if ($is_verify == 1){
			$condition .=" and t.verify_time=0";
		}else if ($is_verify == 2){
			$condition .=" and t.verify_time>0";
		} 
		$param['is_verify'] = $is_verify;
		
		//下单时间
		$create_time_begin  = strim($_REQUEST['create_time_begin']);
		$param['create_time_begin'] = $create_time_begin;
		
		$create_time_end  = strim($_REQUEST['create_time_end']);
		$param['create_time_end'] = $create_time_end;
		
		if(!empty($create_time_begin) && !empty($create_time_end))
		{
			$condition.=" and t.create_time >= '".to_timespan($create_time_begin)."' and t.create_time <='". (to_timespan($create_time_end) + 3600 * 24 - 1)."' ";
		
		}elseif(!empty($create_time_begin) && empty($create_time_end))
		{
			$condition.=" and t.create_time >= '".to_timespan($create_time_begin)."'";
		}
		elseif(empty($create_time_begin) && !empty($create_time_end))
		{
			$condition.=" and t.create_time <='". (to_timespan($create_time_end) + 3600 * 24 - 1)."' ";
		}
		
		//出发时间
		$end_time_begin  = strim($_REQUEST['end_time_begin']);
		$param['end_time_begin'] = $end_time_begin;
		
		$end_time_end  = strim($_REQUEST['end_time_end']);
		$param['end_time_end'] = $end_time_end;
		
		if(!empty($end_time_begin) && !empty($end_time_end))
		{
			$condition.=" and t.end_time >= '".$end_time_begin."' and t.end_time <='". $end_time_end."' ";
		}
		elseif(!empty($end_time_begin) && empty($end_time_end))
		{
			$condition.=" and t.end_time >= '".$end_time_begin."' ";
		}
		elseif(!empty($end_time_begin) && empty($end_time_end))
		{
			$condition.=" and t.end_time >= '".$end_time_begin."' ";
		}
		
		//分页
		if(isset($_REQUEST['numPerPage']))
		{
			$param['pageSize'] = intval($_REQUEST['numPerPage']);
			if($param['pageSize'] <=0||$param['pageSize'] >200)
				$param['pageSize'] = ADMIN_PAGE_SIZE;
		}
		else
			$param['pageSize'] = ADMIN_PAGE_SIZE;
			
		if(isset($_REQUEST['pageNum']))
			$page = intval($_REQUEST['pageNum']);
		else
			$page = 0;
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$param['pageSize']).",".$param['pageSize'];
		$param['pageNum'] = $page;
		
		//排序
		if(isset($_REQUEST['orderField']))
			$param['orderField'] = strim($_REQUEST['orderField']);
		else
			$param['orderField'] = "id";
	
		if(isset($_REQUEST['orderDirection']))
			$param['orderDirection'] = strim($_REQUEST['orderDirection'])=="asc"?"asc":"desc";
		else
			$param['orderDirection'] = "desc";
		
		$totalCount = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."tourline_order t where ".$condition);
		if($totalCount > 0){
			$sql = "select t.*,u.user_name,u.mobile,s.user_name as supplier_name  from ".DB_PREFIX."tourline_order t left outer join ".DB_PREFIX."user u on u.id = t.user_id left outer join ".DB_PREFIX."supplier s on s.id = t.supplier_id where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit;
			//echo $sql;
			//die();
			$list = $GLOBALS['db']->getAll($sql);	

			require_once APP_ROOT_PATH."system/libs/tourline.php";
			
			foreach($list as $k=>$v)
			{
				tourline_order_format($list[$k]);
				//print_r($v);
				//print_r($list[$k]);
			}
		}
		/*
		线路名称:tourline_name
		订单号:sn
		购买会员:user_name
		下单时间:create_time
		订单状态:order_status
		支付状态:pay_status
		订单金额：total_price
		已付金额：pay_amount
		已退金额：refund_amount
		*/
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("user#tourline_order",array("id"=>$id)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("tourline_order#order"));
		
		$GLOBALS['tmpl']->display("core/user/tourline_order.html");
		
	}
	
	/**
     * 会员门票订单列表
     */
	public function ticket_order(){
		$ajax=intval($_REQUEST['ajax']);
		$id=intval($_REQUEST['id']);
		$param = array();
		
		//条件
		$condition = " t.user_id =  ".$id;
		
		//订单号
		if(isset($_REQUEST['sn']))
			$sn = strim($_REQUEST['sn']);
		else
			$sn = "";
		$param['sn'] = $sn;
		if($sn!='')
		{
			$condition.=" and t.sn = '".$sn."' ";
		}
	
		//商家ID
		if(isset($_REQUEST['supplier_id']))
			$supplier_id = strim($_REQUEST['supplier_id']);
		else
			$supplier_id = "";
		$param['supplier_id'] = $supplier_id;
		if($supplier_id!='' && intval($supplier_id) > 0)
		{
			$condition.=" and t.supplier_id = ".intval($supplier_id)." ";
		}
	
		//门票ID
		if(isset($_REQUEST['ticket_id']))
			$ticket_id = strim($_REQUEST['ticket_id']);
		else
			$ticket_id = "";
		$param['ticket_id'] = $ticket_id;
		if($ticket_id!='' && intval($ticket_id) > 0)
		{
			$condition.=" and t.ticket_id = ".intval($ticket_id)." ";
		}		
		
	
		//预定人姓名
		if(isset($_REQUEST['appoint_name']))
			$appoint_name = strim($_REQUEST['appoint_name']);
		else
			$appoint_name = "";
		$param['appoint_name'] = $appoint_name;
		if($appoint_name!='')
		{
			$condition.=" and t.appoint_name = '".$appoint_name."' ";
		}
	
		//预定人手机
		if(isset($_REQUEST['appoint_mobile']))
			$appoint_mobile = strim($_REQUEST['appoint_mobile']);
		else
			$appoint_mobile = "";
		$param['appoint_mobile'] = $appoint_mobile;
		if($appoint_mobile!='')
		{
			$condition.=" and t.appoint_mobile = '".$appoint_mobile."' ";
		}
	
		//发货状态
		$delivery_status = -2;
		if(isset($_REQUEST['delivery_status']) && strim($_REQUEST['delivery_status'])!="")
			$delivery_status = intval($_REQUEST['delivery_status']);
		
		$param['delivery_status'] = $delivery_status;
		if($delivery_status !=-2)
		{
			$condition .=" and t.delivery_status=$delivery_status ";
		}
	
	
		//支付状态
		$pay_status = -1;
		if(isset($_REQUEST['pay_status']) && strim($_REQUEST['pay_status'])!="")
			$pay_status = intval($_REQUEST['pay_status']);
	
		$param['pay_status'] = $pay_status;
		if($pay_status !=-1)
		{
			$condition .=" and t.pay_status=$pay_status ";
		}
	
	
		//改签申请
		$re_appoint_status = -1;
		if(isset($_REQUEST['re_appoint_status']) && strim($_REQUEST['re_appoint_status'])!="")
			$re_appoint_status = intval($_REQUEST['re_appoint_status']);
		
		$param['re_appoint_status'] = $re_appoint_status;
		if($re_appoint_status !=-1)
		{
			$condition .=" and t.re_appoint_status=$re_appoint_status ";
		}
				
		//退款状态
		$refund_status = -1;
		if(isset($_REQUEST['refund_status']) && strim($_REQUEST['refund_status'])!="")
			$refund_status = intval($_REQUEST['refund_status']);
	
		$param['refund_status'] = $refund_status;
		if($refund_status !=-1)
		{
			$condition .=" and t.refund_status=$refund_status ";
		}
	
		//订单状态
		$order_status = 0;
		if(isset($_REQUEST['order_status']) && strim($_REQUEST['order_status'])!="")
			$order_status = intval($_REQUEST['order_status']);
	
		$param['order_status'] = $order_status;
		if($order_status !=0)
		{
			$condition .=" and t.order_status=$order_status ";
		}
	
		//是否验证
		$is_verify = intval($_REQUEST['is_verify']);
		if ($is_verify == 1){
			$condition .=" and t.verify_time=0";
		}else if ($is_verify == 2){
			$condition .=" and t.verify_time>0";
		}
		$param['is_verify'] = $is_verify;
	
		//下单时间
		$create_time_begin  = strim($_REQUEST['create_time_begin']);
		$param['create_time_begin'] = $create_time_begin;
		
		$create_time_end  = strim($_REQUEST['create_time_end']);
		$param['create_time_end'] = $create_time_end;
		
		if(!empty($create_time_begin) && !empty($create_time_end))
		{
			$condition.=" and t.create_time >= '".to_timespan($create_time_begin)."' and t.create_time <='". (to_timespan($create_time_end) + 3600 * 24 - 1)."' ";
		
		}
		elseif(!empty($create_time_begin) && empty($create_time_end))
		{
			$condition.=" and t.create_time >= '".to_timespan($create_time_begin)."' ";
		
		}
		elseif(empty($create_time_begin) && !empty($create_time_end))
		{
			$condition.=" and t.create_time <='". (to_timespan($create_time_end) + 3600 * 24 - 1)."' ";
		
		}
		
		//支付时间
		$pay_time_begin  = strim($_REQUEST['pay_time_begin']);
		$param['pay_time_begin'] = $pay_time_begin;
		
		$pay_time_end  = strim($_REQUEST['pay_time_end']);
		$param['pay_time_end'] = $pay_time_end;
		
		if(!empty($pay_time_begin) && !empty($pay_time_end))
		{
			$condition.=" and t.pay_time >= '".to_timespan($pay_time_begin)."' and t.pay_time <='". (to_timespan($pay_time_end) + 3600 * 24 - 1)."' ";
		}
		elseif(!empty($pay_time_begin) && empty($pay_time_end))
		{
			$condition.=" and t.pay_time >= '".to_timespan($pay_time_begin)."'";
		}
		elseif(empty($pay_time_begin) && !empty($pay_time_end))
		{
			$condition.=" and t.pay_time <='". (to_timespan($pay_time_end) + 3600 * 24 - 1)."' ";
		}
				
		//分页
		if(isset($_REQUEST['numPerPage']))
		{
			$param['pageSize'] = intval($_REQUEST['numPerPage']);
			if($param['pageSize'] <=0||$param['pageSize'] >200)
				$param['pageSize'] = ADMIN_PAGE_SIZE;
		}
		else
			$param['pageSize'] = ADMIN_PAGE_SIZE;
			
		if(isset($_REQUEST['pageNum']))
			$page = intval($_REQUEST['pageNum']);
		else
			$page = 0;
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$param['pageSize']).",".$param['pageSize'];
		$param['pageNum'] = $page;
	
	
		//排序
		if(isset($_REQUEST['orderField']))
			$param['orderField'] = strim($_REQUEST['orderField']);
		else
			$param['orderField'] = "t.id";
	
		if(isset($_REQUEST['orderDirection']))
			$param['orderDirection'] = strim($_REQUEST['orderDirection'])=="asc"?"asc":"desc";
		else
			$param['orderDirection'] = "desc";
	
		$totalCount = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."ticket_order t where ".$condition);
		if($totalCount){
			$sql = "select t.*,u.user_name,u.mobile,s.user_name as supplier_name  from ".DB_PREFIX."ticket_order t left outer join ".DB_PREFIX."user u on u.id = t.user_id left outer join ".DB_PREFIX."supplier s on s.id = t.supplier_id where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit;
			//echo $sql;
			//die();
			$list = $GLOBALS['db']->getAll($sql);
	
			require APP_ROOT_PATH . "system/libs/spot.php";
				
			foreach($list as $k=>$v)
			{
				ticket_order_format($list[$k]);
			}
		}
		/*
			线路名称:tourline_name
		订单号:sn
		购买会员:user_name
		下单时间:create_time
		订单状态:order_status
		支付状态:pay_status
		订单金额：total_price
		已付金额：pay_amount
		已退金额：refund_amount
		*/
	
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
	
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("user#ticket_order",array("id"=>$id)));
		$GLOBALS['tmpl']->assign("editurl",admin_url("spot_order#order"));
	
		$GLOBALS['tmpl']->display("core/user/ticket_order.html");
	}
	
	 /**
     * 会员游记列表
     */
 	public function user_guide(){
 		$ajax=intval($_REQUEST['ajax']);
		$id=intval($_REQUEST['id']);
		
        //条件
		$condition = " user_id =  ".$id."" ;
        
        if($_REQUEST['title']){
            $param['title'] = $_REQUEST['title'];
            $condition .=" and title='".$param['title']."' ";
        }
        if($_REQUEST['nickname']){
            $param['nickname'] = $_REQUEST['nickname'];
            $condition .=" AND nickname='".$param['nickname']."' ";
            
        }
        
        if($condition){
            $condition = " WHERE ".$condition;
        }
        
        //分页
        if(isset($_REQUEST['numPerPage']))
        {			
                $param['pageSize'] = intval($_REQUEST['numPerPage']);
                if($param['pageSize'] <=0||$param['pageSize'] >200)
                        $param['pageSize'] = ADMIN_PAGE_SIZE;
        }
        else
                $param['pageSize'] = ADMIN_PAGE_SIZE;

        if(isset($_REQUEST['pageNum']))
                $page = intval($_REQUEST['pageNum']);
        else
                $page = 0;
        if($page==0)
                $page = 1;
        $limit = (($page-1)*$param['pageSize']).",".$param['pageSize'];
        $param['pageNum'] = $page;
        
        //排序
        if(isset($_REQUEST['orderField']))
                $param['orderField'] = strim($_REQUEST['orderField']);
        else
                $param['orderField'] = "id";

        if(isset($_REQUEST['orderDirection']))
                $param['orderDirection'] = strim($_REQUEST['orderDirection'])=="asc"?"asc":"desc";
        else
                $param['orderDirection'] = "desc";
        
        $count_sql = "SELECT count(*) FROM ".DB_PREFIX."tour_guide ".$condition;
        $sql = "SELECT * FROM ".DB_PREFIX."tour_guide ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit;
        //查询
        $totalCount = $GLOBALS['db']->getOne($count_sql);
        $list = $GLOBALS['db']->getAll($sql);
        
        $GLOBALS['tmpl']->assign('list',$list);
        $GLOBALS['tmpl']->assign('totalCount',$totalCount);
        $GLOBALS['tmpl']->assign('param',$param);
        $GLOBALS['tmpl']->assign("formaction",admin_url("user#user_guide",array("id"=>$id)));
        
        $GLOBALS['tmpl']->display("core/user/guide.html");
    }
   
    public function user_review(){
		$ajax=intval($_REQUEST['ajax']);
		$id=intval($_REQUEST['id']);
		
        //条件
		$condition = " user_id =  ".$id." " ;
        $where = array();
        if($_REQUEST['review_type']){
             $condition .= " and review_type = ".intval($_REQUEST['review_type'])." ";
            $param['review_type'] = $_REQUEST['review_type'];
        }
        if(isset($_REQUEST['is_verify'])&& $_REQUEST['is_verify'] !==''){
            $is_verify = $_REQUEST['is_verify']=="n"?0:1;
            $condition .=" and is_verify = ".$is_verify." ";
            $param['is_verify'] = $_REQUEST['is_verify'];
        }
        //分页
         if(isset($_REQUEST['numPerPage']))
        {			
                $param['pageSize'] = intval($_REQUEST['numPerPage']);
                if($param['pageSize'] <=0||$param['pageSize'] >200)
                        $param['pageSize'] = ADMIN_PAGE_SIZE;
        }
        else
                $param['pageSize'] = ADMIN_PAGE_SIZE;

        if(isset($_REQUEST['pageNum']))
                $page = intval($_REQUEST['pageNum']);
        else
                $page = 0;
        if($page==0)
                $page = 1;
        
        $limit = (($page-1)*$param['pageSize']).",".$param['pageSize'];
        $param['pageNum'] = $page;
        
        //排序
        if(isset($_REQUEST['orderField']))
                $param['orderField'] = strim($_REQUEST['orderField']);
        else
                $param['orderField'] = "id";

        if(isset($_REQUEST['orderDirection']))
                $param['orderDirection'] = strim($_REQUEST['orderDirection'])=="asc"?"asc":"desc";
        else
                $param['orderDirection'] = "desc";
      
		//查询
        $sql_count = "SELECT COUNT(*) FROM ".DB_PREFIX."review where ".$condition; 
        $sql = "SELECT * FROM ".DB_PREFIX."review where ".$condition." order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit;
        $totalCount = $GLOBALS['db']->getOne($sql_count);
        $list = $GLOBALS['db']->getAll($sql);

		$GLOBALS['tmpl']->assign('list',$list);
        $GLOBALS['tmpl']->assign('totalCount',$totalCount);
        $GLOBALS['tmpl']->assign('param',$param);
        
        $GLOBALS['tmpl']->assign("formaction",admin_url("user#user_review"));
       
        $GLOBALS['tmpl']->display("core/user/review.html");
	}
	 /**
     * 会员游记列表
     */
}

	function to_url($id){
	    //{url r="guide#uc_guide_item" v="id=$item.id&type=admin"}
	    echo url("guide#uc_guide_item",array("id"=>$id,"type"=>"admin"));
	}
?>