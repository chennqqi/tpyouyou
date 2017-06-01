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
}
?>