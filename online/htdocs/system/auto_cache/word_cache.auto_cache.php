<?php
class word_cache_auto_cache extends auto_cache{
	public function load($param)
	{		
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");		
		$list = $GLOBALS['fcache']->get($key);

		if($list === false)
		{			
			$list = array();

                        $res = $GLOBALS['db']->getAll("SELECT w.* FROM ".DB_PREFIX."word AS w 
                                LEFT JOIN ".DB_PREFIX."word_type AS wt ON wt.id = w.cid 
                                WHERE w.status = 1 AND (w.cid = 0 OR wt.status = 1)");
                        
                        foreach ($res as $k=>$v){
                            $data = $v;
                            if(preg_match('/^\/(.+?)\/$/', $data['word'], $a)) {
                                        switch($data['type'])
                                        {
                                                case 1:
                                                        $list['banned'][] = $data['word'];
                                                        break;
                                                default:
                                                        $list['filter']['find'][] = $data['word'];
                                                        $list['filter']['replace'][] = preg_replace("/\((\d+)\)/", "\\\\1", $data['replacement']);
                                                        break;
                                        }
                                } else {
                                        $data['word'] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($data['word'], '/'));
                                        switch($data['type'])
                                        {
                                                case 1:
                                                        $banned[] = $data['word'];
                                                        $bannednum ++;
                                                        if($bannednum == 1000) {
                                                                $list['banned'][] = '/('.implode('|', $banned).')/i';
                                                                $banned = array();
                                                                $bannednum = 0;
                                                        }
                                                        break;
                                                default:
                                                        $list['filter']['find'][] = '/'.$data['word'].'/i';
                                                        $list['filter']['replace'][] = $data['replacement'];
                                                        break;
                                        }
                                }
                        }
                      

                        if($banned)
                                $list['banned'][] = '/('.implode('|', $banned).')/i';

			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/autocache/".__CLASS__."/");
			$GLOBALS['fcache']->set($key,$list);
		}
		return $list;
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




