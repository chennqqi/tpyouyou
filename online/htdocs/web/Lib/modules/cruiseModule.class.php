<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------
class cruiseModule extends BaseModule
{
  public function index(){
    global_run();
  	$GLOBALS['tmpl']->display("cruise_index.html");
  }

  function cabin () {
  	global_run();
  	
  	$ajax=intval($_REQUEST['ajax']);
  	$tourline_id=intval($_REQUEST['tourline_id']);
  	$adult_count=intval($_REQUEST['adult_count']);
  	$child_count=intval($_REQUEST['child_count']);

  	    	if(!$GLOBALS['user']){
  	    		if($ajax ==1)
  	    		{
  	    			$return['status'] = 2;
  					$return['info'] = "请先登录";
  					ajax_return($return);
  	    		}
  	    		else
  	    		{
  	    			app_redirect(url("user#login"));
  	    		}
  	    	}
  			
  	    	if(!$tourline_id){
  		    		showErr("请选择旅游线路！",$ajax);
  	    	}
  	    	
  	    	if(!$tourline_item_id){
  		    		showErr("请选择出游时间！",$ajax);
  	    	}
  	    	
  	    	if($adult_count <0 && $child_count<0){
  		    		showErr("请选择人数！",$ajax);
  	    	}
  	    	$GLOBALS['tmpl']->assign("tourline_id",$tourline_id);
  	    	$GLOBALS['tmpl']->assign("adult_count",$adult_count);
  	    	$GLOBALS['tmpl']->assign("child_count",$child_count);


  	    	
  	    	
  	    if($ajax == 1)
  	    	showSuccess("成功", $ajax);

  	    	
  			init_app_page();
  			//输出SEO元素
  			$GLOBALS['tmpl']->assign("site_name","旅游线路 - ".app_conf("SITE_NAME"));
  			$GLOBALS['tmpl']->assign("site_keyword","旅游线路,".app_conf("SITE_KEYWORD"));
  			$GLOBALS['tmpl']->assign("site_description","旅游线路,".app_conf("SITE_DESCRIPTION"));
  			
  	    $GLOBALS['tmpl']->display("tourline_order.html");

  }
}
?>