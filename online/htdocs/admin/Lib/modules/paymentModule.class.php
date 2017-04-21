<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class paymentModule extends AuthModule
{
	
	private function read_modules()
	{
		$directory = APP_ROOT_PATH."system/payment/";
		$read_modules = true;
		$dir = @opendir($directory);
	    $modules     = array();
	
	    while (false !== ($file = @readdir($dir)))
	    {
	        if (preg_match("/^.*?\.php$/", $file))
	        {
	            $modules[] = require_once($directory .$file);
	        }
	    }
	    @closedir($dir);
	    unset($read_modules);
	
	    foreach ($modules AS $key => $value)
	    {
	        ksort($modules[$key]);
	    }
	    ksort($modules);
	
	    return $modules;
	}
	
	public function index()
	{		
		
		$modules = $this->read_modules();
		$db_modules = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment");
		foreach($modules as $k=>$v)
		{
			foreach($db_modules as $kk=>$vv)
			{
				if($v['class_name']==$vv['class_name'])
				{
					//已安装
					$modules[$k]['id'] = $vv['id'];
					$modules[$k]['installed'] = 1;
					$modules[$k]['is_effect'] = $vv['is_effect'];
					$modules[$k]['sort'] = $vv['sort'];
					break;
				}
			}
			
			if($modules[$k]['installed'] != 1)
			$modules[$k]['installed'] = 0;
			$modules[$k]['is_effect'] = intval($modules[$k]['is_effect']);			
			$modules[$k]['is_effect_show'] = $modules[$k]['is_effect']==0?lang("IS_EFFECT_0"):lang("IS_EFFECT_1");
			$modules[$k]['sort'] = intval($modules[$k]['sort']);
			
		}
		$GLOBALS['tmpl']->assign("payment_list",$modules);
				
		$GLOBALS['tmpl']->assign("formaction",admin_url("payment"));
		$GLOBALS['tmpl']->assign("setsorturl",admin_url("payment#set_sort",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("seteffecturl",admin_url("payment#set_effect",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("uninstallurl",admin_url("payment#uninstall",array('ajax'=>1)));		
		$GLOBALS['tmpl']->assign("editurl",admin_url("payment#edit",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("installurl",admin_url("payment#install",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("viewlogurl",admin_url("payment#viewlog"));
		$GLOBALS['tmpl']->display("core/payment/index.html");
	}	
	
	
	
	
	public function install()
	{
		$ajax = intval($_REQUEST['ajax']);
		$class_name = strim($_REQUEST['class_name']);
		$directory = APP_ROOT_PATH."system/payment/";
		$read_modules = true;
	
		$file = $directory.$class_name."_payment.php";
		if(file_exists($file))
		{
			$module = require_once($file);
			
			if($module['bank']==1&&$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."payment where bank = 1")>0)
			{
				showErr("已安装过其他的银行直连接口",$ajax);
			}
			
			$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."payment where class_name ='".$class_name."'");
			if($rs > 0)
			{
				showErr("支付接口已安装",$ajax);
			}
		}
		else
		{
			showErr(lang("INVALID_OPERATION"),$ajax);
		}
	
	
		$data['name'] = $module['name'];
		$data['class_name'] = $module['class_name'];
		$data['lang'] = $module['lang'];
		$data['config'] = $module['config'];
		$data['bank'] = $module['bank'];
		$data['sort'] = $GLOBALS['db']->getOne("select max(sort) from ".DB_PREFIX."payment")+1;
		
		foreach($data['config'] as $k=>$v)
		{
			$data['config'][$k]['SHOW_TITLE'] = $data['lang'][$k];
			if(isset($data['config'][$k]['VALUES'])&&is_array($data['config'][$k]['VALUES']))
			{
				foreach($data['config'][$k]['VALUES'] as $kk=>$vv)
				{
					$val = array();
					$val['SHOW_TITLE'] = $data['lang'][$k."_".$vv];
					$val['VALUE'] = $vv;
					$data['config'][$k]['VALUES'][$kk] = $val;
				}
			}
		}
		

	
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->assign("formaction",admin_url("payment#insert",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/payment/install.html");
	
	}
	
	
	public function insert()
	{
		$ajax = intval($_REQUEST['ajax']);			
		
		$data['name'] = strim($_REQUEST['name']);
		$data['class_name'] = strim($_REQUEST['class_name']);
		$data['sort'] = intval($_REQUEST['sort']);
		$data['logo'] = format_domain_to_relative(strim($_REQUEST['logo']));
		$data['is_effect'] = intval($_REQUEST['is_effect']);
		$data['config'] = serialize($_REQUEST['config']);
		$data['bank'] = intval($_REQUEST['bank']);

		
		if($data['bank']==1&&$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."payment where bank = 1")>0)
		{
			showErr("已安装过其他的银行直连接口",$ajax);
		}
		
		$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."payment where class_name ='".$data['class_name']."'");
		if($rs > 0)
		{
			showErr("支付接口已安装",$ajax);
		}
		
		// 更新数据
		$log_info = $data['name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."payment",$data,"INSERT","","SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			clear_auto_cache("payment_list");
			save_log($log_info.lang("INSTALL_SUCCESS"),1);
			showSuccess(lang("INSTALL_SUCCESS"),$ajax,admin_url("payment"));
		} else {
			//错误提示
			showErr(lang("INSTALL_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
	
	
	public function edit() {
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST ['id']);		
		$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$id);
	
		$directory = APP_ROOT_PATH."system/payment/";
		$read_modules = true;
	
		$file = $directory.$vo['class_name']."_payment.php";
		if(file_exists($file))
		{
			$module = require_once($file);
		}
		else
		{
			showErr(lang("INVALID_OPERATION"),$ajax);
		}
		$data = $vo;
		$vo['config'] = unserialize($vo['config']);
	
		$data['lang'] = $module['lang'];
		$data['config'] = $module['config'];
		
		foreach($data['config'] as $k=>$v)
		{
			$data['config'][$k]['SHOW_TITLE'] = $data['lang'][$k];
			$data['config'][$k]['VALUE'] = $vo['config'][$k];
			if(isset($data['config'][$k]['VALUES'])&&is_array($data['config'][$k]['VALUES']))
			{
				foreach($data['config'][$k]['VALUES'] as $kk=>$vv)
				{
					$val = array();
					$val['SHOW_TITLE'] = $data['lang'][$k."_".$vv];
					$val['VALUE'] = $vv;
					$data['config'][$k]['VALUES'][$kk] = $val;
				}
			}
		}
		

	
		$GLOBALS['tmpl']->assign ( 'vo', $vo );
		$GLOBALS['tmpl']->assign ( 'data', $data );
		$GLOBALS['tmpl']->assign("formaction",admin_url("payment#update",array("ajax"=>1)));
		$GLOBALS['tmpl']->display ("core/payment/edit.html");
	}
	
	
	public function update()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$data['name'] = strim($_REQUEST['name']);
		$data['class_name'] = strim($_REQUEST['class_name']);
		$data['sort'] = intval($_REQUEST['sort']);
		$data['logo'] = format_domain_to_relative(strim($_REQUEST['logo']));
		$data['is_effect'] = intval($_REQUEST['is_effect']);
		$data['config'] = serialize($_REQUEST['config']);
		// 更新数据
		$log_info = $data['name'];
	
		$GLOBALS['db']->autoExecute(DB_PREFIX."payment",$data,"UPDATE","id=".$id,"SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			clear_auto_cache("payment_list");
			save_log($log_info.lang("UPDATE_SUCCESS"),1);
			showSuccess(lang("UPDATE_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("UPDATE_SUCCESS")."<br />".$GLOBALS['db']->error(),$ajax);
		}	
	}
	
	
	
	
	public function uninstall()
	{
		$ajax = intval($_REQUEST['ajax']);
		$id = intval($_REQUEST ['id']);
		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$id);
		if($data)
		{
			$info = $data['class_name'];
			$GLOBALS['db']->query("delete from ".DB_PREFIX."payment where id = ".$data['id']);
			if ($GLOBALS['db']->error()=="") {
					clear_auto_cache("payment_list");
					save_log($info.lang("UNINSTALL_SUCCESS"),1);
					showSuccess(lang("UNINSTALL_SUCCESS"),$ajax);
				} else {
					save_log($info.lang("UNINSTALL_FAILED"),0);
					showErr(lang("UNINSTALL_FAILED"),$ajax);
				}
		}
		else
		{
			showErr(lang("INVALID_OPERATION"),$ajax);
		}
	}
	
	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."payment where id = ".$id);
		$c_is_effect =  $GLOBALS['db']->getOne("select is_effect from ".DB_PREFIX."payment where id = ".$id); //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		$GLOBALS['db']->query("update ".DB_PREFIX."payment set is_effect = ".$n_is_effect." where id = ".$id);
		clear_auto_cache("payment_list");
		save_log($info.lang("SET_EFFECT_".$n_is_effect),1);
		showSuccess(lang("SET_EFFECT_".$n_is_effect),$ajax)	;
	}
	

	
	public function set_sort()
	{
		$ajax = intval($_REQUEST['ajax']);
		$sort = intval($_REQUEST['sort']);
		$id = intval($_REQUEST['id']);
		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$id);
		if($data)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."payment set sort = ".$sort." where id = ".$id);
			if($GLOBALS['db']->error()!="")
			{
				clear_auto_cache("payment_list");
				showErr($data['sort'],$ajax);
			}
			else
			{
				save_log($data['name'].lang("UPDATE_SUCCESS"),1);
				showSuccess($sort,$ajax);
			}
		}
		else
		{
			showErr(0,$ajax);
		}
	}
	
	
	public function viewlog()
	{
		$param = array();
		//条件
		$condition = " 1 = 1 ";
		if(isset($_REQUEST['class']))
			$class_key = strim($_REQUEST['class']);
		else
			$class_key = "";
		$param['class'] = $class_key;
		if($class_key!='')
		{
			$condition.=" and payment_class = '".$class_key."' ";
		}
	
		if(isset($_REQUEST['notice_sn']))
			$sn_key = strim($_REQUEST['notice_sn']);
		else
			$sn_key = "";
	
		if($sn_key!='')
		{
			$param['notice_sn'] = $sn_key;
			$condition.=" and notice_sn = '".$sn_key."' ";
		}
		
		if(isset($_REQUEST['outer_notice_sn']))
			$out_sn_key = strim($_REQUEST['outer_notice_sn']);
		else
			$out_sn_key = "";
		
		if($out_sn_key!='')
		{
			$param['outer_notice_sn'] = $out_sn_key;
			$condition.=" and outer_notice_sn = '".$out_sn_key."' ";
		}
		
		if(isset($_REQUEST['is_paid']))
			$is_paid_key = intval($_REQUEST['is_paid']);
		else
			$is_paid_key = -1;
		

		$param['is_paid'] = $is_paid_key;
		if($is_paid_key>=0)
		$condition.=" and is_paid = '".$is_paid_key."' ";
		
	
		
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
	
	
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment_notice where ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit);
		$totalCount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."payment_notice where ".$condition);
		$totalAmount = $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."payment_notice where ".$condition);
		$totalAmount = format_price(format_price_to_display($totalAmount));
		
		foreach($list as $k=>$v)
		{
			$list[$k]['money'] = format_price(format_price_to_display($v['money']));
			$list[$k]['create_time'] = to_date($v['create_time']);
			$list[$k]['pay_time'] = $v['pay_time']==0?"未支付":to_date($v['pay_time']);
			$list[$k]['user_name'] = get_user_name($v['user_id']);
			$list[$k]['order_type'] = lang("ORDER_TYPE_".$v['order_type']);
		}
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign('totalCount',$totalCount);
		$GLOBALS['tmpl']->assign('param',$param);
	
		$GLOBALS['tmpl']->assign("totalAmount",$totalAmount);
		$GLOBALS['tmpl']->assign("formaction",admin_url("payment#viewlog"));
		$GLOBALS['tmpl']->assign("delurl",admin_url("payment_notice#foreverdelete",array('ajax'=>1)));
		$GLOBALS['tmpl']->display("core/payment/viewlog.html");
	}

}
?>