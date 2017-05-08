<?php

// +----------------------------------------------------------------------

// | Fanwe 乐程旅游b2b

// +----------------------------------------------------------------------

// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.

// +----------------------------------------------------------------------

// | Author: 同创网络(778251855@qq.com)

// +----------------------------------------------------------------------

class indexModule extends BaseModule

{

	public function index()

	{		

		global_run();
      /*   //管理员的SESSION

		$adm_session = es_session::get(md5(app_conf("AUTH_KEY")."supplier"));

		$adm_name = $adm_session['user_name'];

		$adm_id = intval($adm_session['id']);

	    

		if($adm_id == 0)

		{
          header("Location: http://b2b.uu-club.com/s.php?ctl=login"); 
         //确保重定向后，后续代码不会被执行 
        exit;
			//app_redirect(admin_url("login"));
			//app_redirect(admin_url("login#login"));

		}*/

		//print_r($GLOBALS);



		/*if(empty($GLOBALS['user'])) //验证是否登录

		{

			app_redirect(url("user#login"));

		}	*/


		$GLOBALS['tmpl']->caching = true;

		$GLOBALS['tmpl']->cache_lifetime = 600;  //首页缓存10分钟

		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['city']['py']);	

    load_auto_cache("word_cache");

		if (!$GLOBALS['tmpl']->is_cached('index.html', $cache_id))

		{ 		

			init_app_page();

//                        $indexuser=load_auto_cache("load_index_newsx");

//                        var_dump($indexuser);

			$GLOBALS['tmpl']->assign("index_news",load_auto_cache("load_index_news"));

			$GLOBALS['tmpl']->assign("index_newsx",load_auto_cache("load_index_newsx"));

			

			//输出推荐线路

			$recommend_tourline_layer = array();

			require_once APP_ROOT_PATH."system/libs/tourline.php";

			

			$recommend_tourlist = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."tourline_rec_config where rec_page = 0 order by rec_sort asc");

                        //print_r($recommend_tourlist);

			foreach($recommend_tourlist as $k=>$v)

			{  

				$rec_result = load_tourline_rec(0, $v['rec_type'], 0);
			
				$rec_result['name'] = $v['rec_name'];
				
				$rec_result['key'] = $k;//

				$rec_result['color'] = $v['rec_color'];

				$rec_result['adv1'] = $v['rec_adv1'];

				$rec_result['adv1_url'] = $v['rec_adv1_url'];

				$rec_result['adv2'] = $v['rec_adv2'];

				$rec_result['adv2_url'] = $v['rec_adv2_url'];

                                $rec_result['adv3'] = $v['rec_adv3'];

				$rec_result['adv3_url'] = $v['rec_adv3_url'];

				$GLOBALS['tmpl']->assign("rec_result",$rec_result);

				$recommend_tourline_layer[] = $GLOBALS['tmpl']->fetch("inc/recommend_index_tourline.html");

                                //var_dump($rec_result['image_tour']);

			}

			

			$GLOBALS['tmpl']->assign("recommend_tourline_layer",$recommend_tourline_layer);

			

			/*路线满意度*/

			$situation = $GLOBALS['db']->getRow("select Group_concat(id) as tourline_ids,avg(satify) as satify_avg,sum(review_total) as review_total_sum,sum(sale_total+sale_virtual_total) as sale_sum FROM ".DB_PREFIX."tourline where is_effect=1 and (city_id=".intval($GLOBALS['city']['id'])." or (match(city_match) against('".format_fulltext_key($GLOBALS['city']['py'])."' IN BOOLEAN MODE)))");

			if($situation)

			{

				//线满意度

				$satify_avg = $GLOBALS['db']->getOne("select avg(satify) as satify_avg FROM ".DB_PREFIX."tourline where is_effect=1 and satify >0 and (city_id=".intval($GLOBALS['city']['id'])." or (match(city_match) against('".format_fulltext_key($GLOBALS['city']['py'])."' IN BOOLEAN MODE)) and satify >0)");

				if($satify_avg <=0)

					$satify_avg=10000;

				$situation['satify_avg'] =$satify_avg;

				

				if(empty($situation['tourline_ids']))$situation['tourline_ids'] = 0;

				$order_tourline = $GLOBALS['db']->getAll("select t.id as tourline_id,t.name,t.is_tuan,t.tuan_is_pre,t.tuan_begin_time,t.tuan_end_time,us.user_name,tord.create_time as o_create_time "

				."FROM ".DB_PREFIX."tourline_order as tord "

				." left join ".DB_PREFIX."tourline as t on t.id = tord.tourline_id "

				." left join ".DB_PREFIX."user as us on us.id = tord.user_id "

				." where tord.tourline_id in(".$situation['tourline_ids'].") and tord.order_status < 4 and tord.pay_status =1 and t.is_effect=1 and ((t.tuan_begin_time=0 or t.tuan_begin_time < ".NOW_TIME." ) && (t.tuan_end_time =0 or t.tuan_end_time > ".NOW_TIME.") ) order by tord.id desc limit 10");

//                        var_dump($order_tourline);die;

				foreach($order_tourline as $k=>$v)

				{

					$order_tourline[$k]['url']=url("tours#view",array("id"=>$v['tourline_id']));

					$order_tourline[$k]['user_name_formate']=substr($v['user_name'],0,3)."***";

					$order_tourline[$k]['o_create_time_formate']=pass_date($v['o_create_time']);

				}

				$situation['order_tourline']=$order_tourline;

			}

			else{

				$situation['order_tourline']=array();

			}

			

			$situation['satify_avg']=round($situation['satify_avg']/100);

			$GLOBALS['tmpl']->assign("situation",$situation);

			/*路线满意度end*/

			

			//首页门票

			require APP_ROOT_PATH."system/libs/spot.php";

			$index_spot_tickets = get_index_spots();

	

			$GLOBALS['tmpl']->assign("index_spot_tickets",$index_spot_tickets);

		}		

		

            $GLOBALS['tmpl']->display("index.html",$cache_id);

	}

	

	

}

?>