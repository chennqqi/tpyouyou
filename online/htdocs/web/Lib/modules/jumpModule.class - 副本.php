<?php
class jumpModule extends BaseModule{
	public function index(){
	
			$keyword = strim($_REQUEST['keyword']);
			$search_type = intval($_REQUEST['search_type']);
			/**
				<div class="type_s" rel="1">跟团游</div>
				<div class="type_s" rel="2">自助游</div>
				<div class="type_s" rel="3">自驾游</div>
				<div class="type_s" rel="4">国内游</div>
				<div class="type_s" rel="5">出境游</div>
				<div class="type_s" rel="6">周边游</div>
				<div class="type_s" rel="7">门票</div>
				<div class="type_s" rel="8">团购</div>
				<div class="type_s" rel="9">游记</div>
			*/
		
			if($search_type==1)
			{
				$url = url("tourlist",array("t_type"=>1,"keyword"=>$keyword));
			}
			elseif($search_type==2)
			{
				$url = url("tourlist",array("t_type"=>2,"keyword"=>$keyword));
			}
			elseif($search_type==3)
			{
				$url = url("tourlist",array("t_type"=>3,"keyword"=>$keyword));
			}
			elseif($search_type==4)
			{
				$url = url("tourlist",array("type"=>1,"keyword"=>$keyword));
			}
			elseif($search_type==5)
			{
				$url = url("tourlist",array("type"=>2,"keyword"=>$keyword));
			}
			elseif($search_type==6)
			{
				$url = url("tourlist#around",array("keyword"=>$keyword));
			}
			elseif($search_type==7)
			{
				$url = url("spot#cat",array("keyword"=>$keyword));
			}
			elseif($search_type==8)
			{
				$url = url("tuan",array("keyword"=>$keyword));
			}
			elseif($search_type==9)
			{
				$url = url("guide",array("keyword"=>$keyword));
			}
			else
			{
				$url = url("index");
			}
			app_redirect($url);
	
	}
}
?>