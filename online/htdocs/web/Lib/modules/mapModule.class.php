<?php
class mapModule extends BaseModule{

    function index() {
    	$xpoint = strim($_REQUEST['xpoint']);
    	$ypoint = strim($_REQUEST['ypoint']);
    	$GLOBALS['tmpl']->assign("xpoint",$xpoint);
    	$GLOBALS['tmpl']->assign("ypoint",$ypoint);
    	$GLOBALS['tmpl']->display("map/".MAPTYPE.".html");
    }
}
?>