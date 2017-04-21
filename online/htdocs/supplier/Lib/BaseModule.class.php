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
		$GLOBALS['tmpl']->assign("MODULE_NAME",MODULE_NAME);
		$GLOBALS['tmpl']->assign("ACTION_NAME",ACTION_NAME);				
	}

	public function index()
	{
		showErr("invalid access");
	}
	public function __destruct()
	{
		unset($this);
	}
	/**
	 * 定义带回调查看，自动保存的字段
	 */
	protected function assign_lookup_fields($pk)
	{		
		$fields = array();
		foreach($_REQUEST as $k=>$v)
		{
			if(preg_match("/selected_(\w+)/", $k,$matches))
			{
				$fields[] = $matches[1];
			}
		}
		foreach($fields as $field)
		{
			$selected_data[$field] =  strim($_REQUEST['selected_'.$field.'']);
		}
		$GLOBALS['tmpl']->assign("fields",$fields);
		$GLOBALS['tmpl']->assign("fields_str",json_encode($fields));
		$GLOBALS['tmpl']->assign("pk",$pk);
		$GLOBALS['tmpl']->assign("selected_data_str",json_encode($selected_data));
		$GLOBALS['tmpl']->assign("selected_data",$selected_data);
	}
}

class AuthModule extends BaseModule{
		public $supplier_id;
		public $supplier_name;
		public function __construct(){
			parent::__construct();
			$this->check_auth();
		}
		
		
		/**
		 * 验证检限
		 * 已登录时验证用户权限, Index模块下的所有函数无需权限验证
		 * 未登录时跳转登录
		 */
		private function check_auth()
		{
			if(intval(app_conf("EXPIRED_TIME"))>0&&es_session::is_expired())
			{
				es_session::delete(md5(app_conf("AUTH_KEY")."supplier"));
				es_session::delete("expire");
			}
		
			//管理员的SESSION
			$adm_session = es_session::get(md5(app_conf("AUTH_KEY")."supplier"));
			$adm_name = $adm_session['user_name'];
			$adm_id = intval($adm_session['id']);
			$ajax = intval($_REQUEST['ajax']);
			$is_auth = 0;
			if(isset($_REQUEST['_']))
				$is_navClick = intval($_REQUEST['_']);
			else
				$is_navClick = 0;
		
			$this->supplier_id = $adm_id;
			$this->supplier_name = $adm_session['company_name'];
		
			if($adm_id == 0&&$is_auth==0)
			{
				if($ajax == 0&&$is_navClick==0)
					app_redirect(admin_url("login"));
				else
				{
					$result['statusCode'] = 301;
					$result['message'] = lang("LOGIN_TIMEOUT");
					ajax_return($result);
				}
			}						
		}
}
?>