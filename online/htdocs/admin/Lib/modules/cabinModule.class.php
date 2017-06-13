<?php
class cabinModule extends AuthModule{
    
    function add() {
    	$NOW_DATE = to_date(NOW_TIME,"Y-m-d");
        $cabin_cates = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."cabin_cate ORDER BY sort DESC");

        $GLOBALS['tmpl']->assign("NOW_DATE",$NOW_DATE);

    	$GLOBALS['tmpl']->assign("cabin_cates",$cabin_cates);
    	
    	$GLOBALS['tmpl']->assign("formaction",admin_url("cabin#insert",array("ajax"=>1)));
    	$GLOBALS['tmpl']->display("core/cabin/add.html");
    }
    
    function insert(){
    	$ajax = intval($_REQUEST['ajax']);
    	$data = base64_encode(serialize($_REQUEST));
    	showSuccess($data,$ajax);
    }
    
    function edit(){
    	$tickets = unserialize(base64_decode($_POST['tickets']));
    	
    	$NOW_DATE = to_date(NOW_TIME,"Y-m-d");

        $cabin_cates = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."cabin_cate ORDER BY sort DESC");

        $GLOBALS['tmpl']->assign("cabin_cates",$cabin_cates);
    	$GLOBALS['tmpl']->assign("NOW_DATE",$NOW_DATE);
    	$GLOBALS['tmpl']->assign("ticket",$tickets);    	
    	$GLOBALS['tmpl']->assign("formaction",admin_url("cabin#update",array("ajax"=>1)));
    	$GLOBALS['tmpl']->display("core/cabin/edit.html");
    }
    
    function update(){
    	$ajax = intval($_REQUEST['ajax']);

    	$data = base64_encode(serialize($_REQUEST));

        showSuccess($data,$ajax);
    }
}
?>