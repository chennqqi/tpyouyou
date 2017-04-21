<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class confModule extends AuthModule
{
	public function index()
	{		
		$conf_res = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."conf where is_conf = 1 order by group_id asc,sort asc");
	
		foreach($conf_res as $k=>$v)
		{
			$v['value'] = htmlspecialchars($v['value']);
			if($v['name']=='TEMPLATE')
			{
		
				//输出现有模板文件夹
				$directory = APP_ROOT_PATH."web/Tpl/";
				$dir = @opendir($directory);
				$tmpls     = array();
					
				while (false !== ($file = @readdir($dir)))
				{
					if($file!='.'&&$file!='..')
						$tmpls[] = $file;
				}
				@closedir($dir);
				//end
		
				$v['input_type'] = 1;
				$v['value_scope'] = $tmpls;
			}
			elseif($v['name']=='SHOP_LANG')
			{
				//输出现有语言包文件夹
				$directory = APP_ROOT_PATH."web/Lang/";
				$dir = @opendir($directory);
				$tmpls     = array();
					
				while (false !== ($file = @readdir($dir)))
				{
					if($file!='.'&&$file!='..')
						$tmpls[] = $file;
				}
				@closedir($dir);
				//end
		
				$v['input_type'] = 1;
				$v['value_scope'] = $tmpls;
			}
			else
				$v['value_scope'] = explode(",",$v['value_scope']);
			
			$v['title'] = lang("CONF_".$v['name']);
			
			
			foreach($v['value_scope'] as $kkk=>$vvv)
			{
				$scope = array();
				$scope['val'] = $vvv;
				if($v['name']!="TEMPLATE"&&$v['name']!="SITE_LANG")
					$scope['show_val'] = lang("CONF_".$v['name']."_".$vvv);
				else
					$scope['show_val'] = $vvv;
				
 				$v['value_scope'][$kkk] =$scope;

			}

			
			$conf[$v['group_id']][] = $v;
		}
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("conf#update",array('ajax'=>1)));
		$GLOBALS['tmpl']->assign("conf",$conf);		
		$GLOBALS['tmpl']->display("core/conf/index.html");
	
	}	
	
	
	public function update()
	{
		$ajax = intval($_REQUEST['ajax']);
		$conf_res = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."conf where  is_conf = 1");
		foreach($conf_res as $k=>$v)
		{
			if ($v['input_type']==0||$v['input_type']==1||$v['input_type']==4)
				$val = strim($_REQUEST[$v['name']]);
			else if($v['input_type']==5)
				$val = btrim($_REQUEST[$v['name']]);
			else if($v['input_type']==2)
				$val = format_domain_to_relative(strim($_REQUEST[$v['name']]));
			else if($v['input_type']==3)
				$val = format_domain_to_relative(btrim($_REQUEST[$v['name']]));
			$GLOBALS['db']->query("update ".DB_PREFIX."conf set value = '".$val."' where name = '".$v['name']."'");
		}		
		
		//开始写入配置文件
		$result = save_sys_config();
		
	
		if(!$result['status']){
			showErr($result['info'],$ajax);
		}
			
		save_log(lang("CONF_UPDATED"),1);
		$ADMIN_MSG_SENDER_OPEN = $GLOBALS['db']->getOne("select value from ".DB_PREFIX."conf where name = 'ADMIN_MSG_SENDER_OPEN'");		
		$IS_WATER = $GLOBALS['db']->getOne("select value from ".DB_PREFIX."conf where name = 'IS_WATER_MARK'");		
		$data['statusCode'] = 200;
		$data['message'] = lang("UPDATE_SUCCESS");
		$data['sender_open'] = $ADMIN_MSG_SENDER_OPEN;
		$data['is_water'] = $IS_WATER;
		ajax_return($data);
	}

}
?>