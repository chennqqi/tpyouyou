<?php
//广告位
class adv_cache_auto_cache extends auto_cache{
	public function load($param)
	{		
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");		
		$code = $GLOBALS['fcache']->get($key);
		if($code === false)
		{			
			$adv_id = $param['adv_id'];
			$city_py = $param['city_py'];
			
			$code = "";
			if($city_py!="")
			{	
				$city_py = format_fulltext_key($city_py);
				$adv_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."adv where adv_id = '".$adv_id."' and match(`city_match`) against ('".$city_py."' IN BOOLEAN MODE) ");
			}
			else
			{
				$adv_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."adv where adv_id = '".$adv_id."'");
			}
			if($adv_data)
			{
				if($adv_data['type']==0)
				{
					//图片
					$image = "<img src='".$adv_data['code']."' ";
					if($adv_data['width']>0)
						$image.=" width=".$adv_data['width'];
					if($adv_data['height']>0)
						$image.=" height=".$adv_data['height'];
					if($adv_data['title']!="")
						$image.=" alt=".$adv_data['title'];
					$image.=" />";
					if($adv_data['url']!="")
					{
						$code = "<a href='".$adv_data['url']."' target='_blank' ";
						if($adv_data['title']!="")
							$code.=" title=".$adv_data['title'];
						$code.=">".$image."</a>";
					}
					else
					{
						$code = "<a href='#' ";
						if($adv_data['title']!="")
							$code.=" title=".$adv_data['title'];
						$code.=">".$image."</a>";
					}
				}
				elseif($adv_data['type']==1)
				{
					//flash
					$code = '<embed src="'.$adv_data['code'].'" type="application/x-shockwave-flash"  ';
					if($adv_data['width']>0)
						$code.=' width='.$adv_data['width'];
					if($adv_data['height']>0)
						$code.=' height='.$adv_data['height'];
					$code.= ' quality="high" allowScriptAccess="always" allowFullScreen="true" mode="transparent" />';
				}
				elseif ($adv_data['type']==2)
				{
					//flv
					$auto_play = $adv_data['auto_play']==1?"true":"false";
					$code = '<object ';
					if($adv_data['width']>0)
						$code.=' width='.$adv_data['width'];
					if($adv_data['height']>0)
						$code.=' height='.$adv_data['height'];
					$code.=' data="'.APP_ROOT.'/vcastr3.swf" type="application/x-shockwave-flash">';
					$code.='<param value="'.APP_ROOT.'/vcastr3.swf" name="movie"> ';
					$code.='<param value="true" name="allowFullScreen">';
					$code.='<param value="xml={vcastr}{channel}{item}{source}'.$adv_data['code'].'{/source}{duration}{/duration}{title}{/title}{/item}{/channel}{config}{isAutoPlay}'.$auto_play.'{/isAutoPlay}{/config}{/vcastr}" name="FlashVars"></object>';
				}
			}			
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$code);
		}
		return $code;
	}
	public function rm($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
		$GLOBALS['fcache']->rm($key);
	}
	public function clear_all()
	{
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
		$GLOBALS['fcache']->clear();
	}
}
?>