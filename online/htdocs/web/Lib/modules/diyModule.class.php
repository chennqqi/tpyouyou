<?php
require APP_ROOT_PATH . "system/libs/tourline.php";
class diyModule extends BaseModule{

    function index() {
    	global_run();
		$GLOBALS['tmpl']->caching = true;
		$GLOBALS['tmpl']->cache_lifetime = 600;  //首页缓存10分钟
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['city']['py']);
		
		if (!$GLOBALS['tmpl']->is_cached('tourline_diy_index.html', $cache_id))
		{	
			//输出推荐线路
			$recommend_tourline_layer = array();
			require_once APP_ROOT_PATH."system/libs/tourline.php";
			
			$recommend_tourlist = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tourline_rec_config where rec_page = 5 order by rec_sort asc");
				
			foreach($recommend_tourlist as $k=>$v)
			{
				$rec_result = load_tourline_rec(5, $v['rec_type'], $v['rec_id']);
				$rec_result['name'] = $v['rec_name'];
				$rec_result['color'] = $v['rec_color'];
				$rec_result['adv1'] = $v['rec_adv1'];
				$rec_result['adv1_url'] = $v['rec_adv1_url'];
				$rec_result['adv2'] = $v['rec_adv2'];
				$rec_result['adv2_url'] = $v['rec_adv2_url'];
				$GLOBALS['tmpl']->assign("rec_result",$rec_result);
				$recommend_tourline_layer[] = $GLOBALS['tmpl']->fetch("inc/recommend_channel_tourline.html");
                                
			}
				
			$GLOBALS['tmpl']->assign("recommend_tourline_layer",$recommend_tourline_layer);
			
		
			init_app_page();
			//输出SEO元素
			$GLOBALS['tmpl']->assign("site_name","自助游 - ".app_conf("SITE_NAME"));
			$GLOBALS['tmpl']->assign("site_keyword","自助游,".app_conf("SITE_KEYWORD"));
			$GLOBALS['tmpl']->assign("site_description","自助游,".app_conf("SITE_DESCRIPTION"));
		}
    	$GLOBALS['tmpl']->display("tourline_diy_index.html",$cache_id);
    }

}
?>