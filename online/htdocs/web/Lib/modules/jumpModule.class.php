<?php
class jumpModule extends BaseModule{
	public function index(){

			$keyword = strim($_REQUEST['keyword']);
			$search_type = intval($_REQUEST['search_type']);
	
			if($search_type==1)
			{
				$url = url("tourlist",array("keyword"=>$keyword));
			}
			elseif($search_type==2)
			{
				$url = url("tourlist",array("keyid"=>$keyword));
			}
			else
			{
				$url = url("index");
			}
			app_redirect($url);
	
	
	}
}
?>