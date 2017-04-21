<?php
class company_tourModule extends BaseModule {
	public function index() {
				
		global_run();   	
    	init_app_page();    	    	
		$GLOBALS['tmpl']->display("company_tour.html");
	}
	
	public function sub() {		
		global_run();   	
    	init_app_page();
    	$ajax=intval($_REQUEST['ajax']);
		$data = array();
		$data['start_city']=strim($_POST['start_city']);
		$data['tour_dest']=strim($_POST['destination']);
		$data['tour_date']=strim($_POST['start_date']);
		$data['tour_days']=intval($_POST['date_num']);
		$data['people_num_min']=intval($_POST['people_min']);
		$data['people_num_max']=intval($_POST['people_max']);
		$data['budget_min']=intval($_POST['budget_min']);
		$data['budget_max']=intval($_POST['budget_max']);
		$data['other_require']=strim($_POST['other_require']);
		$data['company_name']=strim($_POST['company_name']);
		$data['company_contact']=strim($_POST['contacts']);
		$data['company_mobile']=strim($_POST['mobilephone']);
		$data['area_code']=strim($_POST['area_code']);
		$data['company_tel']=strim($_POST['telephone']);
		$data['company_qq']=strim($_POST['qq']);
		$data['company_address']=strim($_POST['address']);
		$data['create_time']=NOW_TIME;
		$data['user_id']=intval($GLOBALS['user']['id']);
		$yzm=strim($_POST['yzm']);
	    es_session::start();
	    $verify = es_session::get("verify");
	    es_session::close();
	    if($verify!=md5($yzm))
		{
			showErr("验证码错误",$ajax);
		}
		if($data['start_city']==""){
			showErr("出发地不能为空",$ajax);
		}elseif($this->checkLength($data['start_city'],255,"max")){
			showErr("出发地字数太长",$ajax);
		}
		if($data['tour_dest']==""||$data['tour_dest']=='可输入多个出游目的地，以逗号隔开'){
			showErr("目的地不能为空",$ajax);
		}elseif($this->checkLength($data['tour_dest'],255,"max")){
			showErr("目的地字数太长",$ajax);
		}		
 		if($data['tour_date']==""){
			showErr("出发时间不能为空",$ajax);
		}elseif($this->checkLength($data['tour_date'],255,"max")){
			showErr("出发时间字数太长",$ajax);
		}   	
		if($data['tour_days']==""){
			showErr("出游天数不能为空",$ajax);
		}elseif($this->checkLength($data['tour_days'],6,"max")){
			showErr("出游天数字数太长",$ajax);
		}elseif(!is_numeric($data['tour_days'])){
			showErr("出游天数必须是数字",$ajax);
		}    	
		if($data['people_num_min']==""){
			showErr("出行人数最小值不能为空",$ajax);
		}elseif($this->checkLength($data['people_num_min'],6,"max")){
			showErr("出行人数字数太长",$ajax);
		}elseif(!is_numeric($data['people_num_min'])){
			showErr("出行人数必须是数字",$ajax);
		}   		
		if($data['people_num_max']==""){
			showErr("出行人数最大值不能为空",$ajax);
		}elseif($this->checkLength($data['people_num_max'],6,"max")){
			showErr("出行人数字数太长",$ajax);
		}elseif(!is_numeric($data['people_num_max'])){
			showErr("出行人数必须是数字",$ajax);
		}   		
		if($data['budget_min']!=""){
			if($this->checkLength($data['budget_min'],6,"max")){
				showErr("预算字数太长",$ajax);
			}elseif(!is_numeric($data['budget_min'])){
			    showErr("预算必须是数字",$ajax);
		    }  	
		}	
		if($data['budget_max']!=""){
			if($this->checkLength($data['budget_max'],6,"max")){
				showErr("预算字数太长",$ajax);
			}elseif(!is_numeric($data['budget_max'])){
			    showErr("预算必须是数字",$ajax);
		    }  	
		}
		if($data['other_require']!=""){			
			if($this->checkLength($data['other_require'],5000,"max")){
				showErr("其他要求字数太长",$ajax);
			}		
		}
		if($data['company_name']==""){
			showErr("公司名称不能为空",$ajax);
		}elseif($this->checkLength($data['company_name'],255,"max")){
			showErr("公司名称字数太长",$ajax);
		}		
		if($data['company_contact']==""){
			showErr("联系人不能为空",$ajax);
		}elseif($this->checkLength($data['company_contact'],50,"max")){
			showErr("联系人字数太长",$ajax);
		}		
		if($data['company_mobile']==""){
			showErr("手机不能为空",$ajax);
		}elseif(!preg_match("/^\d{11}$/",$data['company_mobile'])){
			showErr("手机号码必须是11位纯数字",$ajax);
		}		
		if($data['area_code']!=""&&$data['area_code']!="区号"){			
			if(!preg_match("/^\d{3,4}$/",$data['area_code'])){
				showErr("请填写正确的区号",$ajax);
			}		
		}elseif($data['area_code']=="区号")	{
			$data['area_code']="";
		}		
		if($data['company_tel']!=""&&$data['company_tel']!="电话号码"){			
			if(!preg_match("/^\d{3,10}$/",$data['company_tel'])){
				showErr("请填写正确的电话号码",$ajax);
			}		
		}elseif($data['company_tel']=="电话号码")	{
			$data['company_tel']="";
		}		
		if($data['company_qq']==""){
			showErr("qq不能为空",$ajax);
		}elseif(!preg_match("/^[1-9]\d{4,9}$/",$data['company_qq'])){
			showErr("请填写正确的qq",$ajax);
		}		
		if($data['company_address']!=""&&$data['company_address']!="个人组织可不填写"){			
			if($this->checkLength($data['company_address'],255,"max")){
				showErr("地址字数太长",$ajax);
			}		
		}elseif($data['company_address']=="个人组织可不填写")	{
			$data['company_address']="";
		}	
		
		es_session::start();
		es_session::delete("verify");
		es_session::close();
		$GLOBALS['db']->autoExecute(DB_PREFIX."company_tour",$data,"INSERT","","SILENT");
		
		if($GLOBALS['db']->error()==""){			
			if($ajax == 1){
				$jump_url=url("company_tour");
				showSuccess("提交成功",$ajax,$jump_url);
			}
		}else{
			showErr("提交失败",$ajax);
		}
		

		
		
	}
	
	
	public function checkLength($_data, $_length, $_flag) {
		if ($_flag == 'min') {
			if (mb_strlen(trim($_data),'utf-8') < $_length) return true;
			return false;
		} elseif ($_flag == 'max') {
			if (mb_strlen(trim($_data),'utf-8') > $_length) return true;
			return false;
		} elseif ($_flag == 'equals') {
			if (mb_strlen(trim($_data),'utf-8') != $_length) return true;
			return false;
		}
	}
	
	
	
	
}
?>
