<?php
class tourline_new_insuranceModule {

    function add() {
    	
    	$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_new_insurance#insert",array("ajax"=>1)));
    	$GLOBALS['tmpl']->display("core/tourline_new_insurance/add.html");
    }
    
    function insert(){
    	$ajax = intval($_REQUEST['ajax']);
    	if(strim($_REQUEST['name'])==''){
    		showErr("请输入名称",$ajax);
    	}
    	if($_REQUEST['price'] <=0){
    		showErr("请输入价格",$ajax);
    	}

    	$data = serialize($_REQUEST);
    	showSuccess($data,$ajax);
    }
    
    function edit(){
    	$new_insurances = unserialize(urldecode($_POST['new_insurances']));
    	$GLOBALS['tmpl']->assign("new_insurances",$new_insurances);
    	
    	$GLOBALS['tmpl']->assign("formaction",admin_url("tourline_new_insurance#update",array("ajax"=>1)));
    	$GLOBALS['tmpl']->display("core/tourline_new_insurance/edit.html");
    }
    
    function update(){
    	$ajax = intval($_REQUEST['ajax']);
    	if(strim($_REQUEST['name'])==''){
    		showErr("请输入名称",$ajax);
    	}
    	if($_REQUEST['price'] <=0){
    		showErr("请输入价格",$ajax);
    	}
    
    	$data = serialize($_REQUEST);
    	showSuccess($data,$ajax);
    }
    
}
?>