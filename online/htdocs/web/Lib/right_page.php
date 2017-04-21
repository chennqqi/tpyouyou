<?php
	//分页类
	require_once APP_ROOT_PATH."web/Lib/page.php";
	class RightPage extends Page {		
		//分页信息
		public function show() {
			if(0 == $this->totalRows) return '';
        $p = 'p';
        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
        
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
        
        if(app_conf("URL_MODEL")==1)
        {
        	$url = $GLOBALS['current_url'];        	
        }
        //上下翻页字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            $upPage="<a class='sort_last' href='".$this->get_page_link($url,$upRow)."'></a>";
        }else{
            $upPage="<em class='sort_last'></em>";
        }

        if ($downRow <= $this->totalPages){
            $downPage="<a class='sort_next' href='".$this->get_page_link($url,$downRow)."'>".$this->config['next']."</a>";
        }else{
            $downPage="<em class='sort_next'>".$this->config['next']."</em>";
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst = "";
            $prePage = "";
        }else{
            $preRow =  $this->nowPage-$this->rollPage;
            $prePage = "<a href='".$this->get_page_link($url,$preRow)."' >上".$this->rollPage."页</a>";
            $theFirst = "<a href='".$this->get_page_link($url,1)."' >".$this->config['first']."</a>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage = "";
            $theEnd="";
        }else{
            $nextRow = $this->nowPage+$this->rollPage;
            if($nextRow>$this->totalPages)$nextRow = $this->totalPages;
            $theEndRow = $this->totalPages;
            $nextPage = "<a href='".$this->get_page_link($url,$nextRow)."' >下".$this->rollPage."页</a>";
            $theEnd = "<a href='".$this->get_page_link($url,$theEndRow)."' >".$this->config['last']."</a>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page=($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "&nbsp;<a href='".$this->get_page_link($url,$page)."'>&nbsp;".$page."&nbsp;</a>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "&nbsp;<span class='current'>".$page."</span>";
                }
            }
        }
        

        $pageStr ='<em>'.$this->nowPage.'/'.$this->totalPages.'</em>'.$upPage.$downPage;
        	
        return $pageStr;
		}
	}
?>