<?php

class supplierModule extends AuthModule{

    function index() {
    	
    	$vo =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".$this->supplier_id);
		$GLOBALS['tmpl']->assign ( 'vo', $vo );
		
		$GLOBALS['tmpl']->assign("formaction",admin_url("supplier#update",array("ajax"=>1)));
		
		$GLOBALS['tmpl']->display("core/supplier/index.html");
    }
    
   public function update() {
		$ajax = intval($_REQUEST['ajax']);
		$id= $this->supplier_id;
		$data = array();
		//$data['contact_name'] = strim($_REQUEST['contact_name']);
		//$data['contact_sex'] = intval($_REQUEST['contact_sex']);
		$data['contact_mobile'] = strim($_REQUEST['contact_mobile']);
		//$data['contact_tel'] = strim($_REQUEST['contact_tel']);
		//$data['contact_fax'] = strim($_REQUEST['contact_fax']);
		//$data['contact_qq'] = strim($_REQUEST['contact_qq']);
		$data['contact_email'] = strim($_REQUEST['contact_email']);
		//$data['company_address'] = strim($_REQUEST['company_address']);
		//$data['company_zip'] = strim($_REQUEST['company_zip']);
		
		//if(isset($_REQUEST['logo']))
		//$data['logo'] = format_domain_to_relative(strim($_REQUEST['logo']));
	
		// 更新数据
		
		$log_info = $data['user_name'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier",$data,"UPDATE","id=".$id,"SILENT");
		if ($GLOBALS['db']->error()=="") {
			//成功提示
			showSuccess(lang("UPDATE_SUCCESS"),$ajax);
		} else {
			//错误提示
			showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
		}	

	}
}
?>