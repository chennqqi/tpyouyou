<?php



class Review{

    

    /**

     * 初始化点评模块

     * @param int $review_type 点评分类(1.线路 2.景点)

     * @param int $review_rel_id 关联编号

     * @return string 模块HTML

     */

    public static function init_review($review_type,$review_rel_id){

        es_session::start();

        $es_session_id = es_session::id();

        es_session::close();

        $GLOBALS['tmpl']->assign("es_session_id",$es_session_id);

        if($review_type ==1){

            $table = "tourline";

        }elseif($review_type == 2){

            $table = "spot";

        }

        else

        {

        	return "";

        }

        

        $GLOBALS['tmpl']->assign("allow_review",0);

        //满意度百分比，点评总数，点评一星~五星人数

        $count_data = $GLOBALS['db']->getRow("SELECT satify,review_total,star_1_count,star_2_count,star_3_count,star_4_count,star_5_count FROM ".DB_PREFIX.$table." WHERE id='".$review_rel_id."'");

        $satis_count = self::format_star2satis_count($count_data);

        //满意度百分比

       //  $count_data['satify'] = round($count_data['satify']/100);

        $count_data['satify'] = 90;

        $review_list_ajax_url = url("review#init_review",array('review_type'=>$review_type,'review_rel_id'=>$review_rel_id));

        $review_save_ajax_url = url("review#save_review",array('review_type'=>$review_type,'review_rel_id'=>$review_rel_id));

        $GLOBALS['tmpl']->assign("review_list_ajax_url",$review_list_ajax_url);

        $GLOBALS['tmpl']->assign("review_save_ajax_url",$review_save_ajax_url);

        $GLOBALS['tmpl']->assign('count_data',$count_data);



        $GLOBALS['tmpl']->assign('satis_count',$satis_count);

        

        

        $group_point = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."review_group_field WHERE review_type=".$review_type);

        $GLOBALS['tmpl']->assign("group_point",$group_point);

        return $GLOBALS['tmpl']->fetch("inc/init_review.html");

    }

    public static function insert_review($review_type,$review_rel_id,$data){

        $ins_data = array();

        $ins_data['content'] = $data['content'];

        $ins_data['review_type'] = $review_type;

        $ins_data['review_rel_id'] = $review_rel_id;

        $ins_data['user_id'] = $data['user_id'];

        $ins_data['nickname'] = $data['nickname'];

        $ins_data['create_time'] = NOW_TIME;

        

        $GLOBALS['db']->autoExecute(DB_PREFIX."review",$ins_data,'INSERT');

        if($GLOBALS['db']->affected_rows()){

            //发放奖励

            require_once APP_ROOT_PATH."system/libs/user.php";

            User::modify_account(intval($data['user_id']),3,APP_CONF("COMMENT_EXP"),"评论增加经验");

            return true;

        }else{

            return false;

        }

    }

    

    public static function get_review_limit($num=5,$orderby="DESC"){

        $list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."review ORDER BY id ".$orderby." LIMIT 0,".$num);

        $result = array();

        if($list){

            foreach($list as $k=>$v){

                $userids[] = $v['user_id'];

            }

            $userids = array_unique($userids);

            require_once APP_ROOT_PATH."system/libs/user.php";

            $user_avatars = User::get_user_avatar($userids);



            foreach($list as $k=>$v){

                $temp = $v;

                $temp['review_time'] = to_date($v['review_time'],"Y-m-d H:i");

                $temp['avatar'] = $user_avatars[$v['user_id']]['img'];

                //点评类型

                $temp['group_point'] = unserialize($v['group_point']);

                $temp['imgs'] = unserialize($v['image_list']);

                $temp['money'] = format_price_to_display($v['money']);

                $temp['voucher_count'] = format_price_to_display($v['voucher_count']);

                $temp['point_satify'] = $v['point']*20;

                if($v['review_type']==1)

                    $show_url = url("tours#view",array('id'=>$v['review_rel_id']));

                elseif($v['review_type']==2)

                    $show_url = url("spot#view",array('id'=>$v['review_rel_id']));

                

                $temp["url"] =  $show_url;

                

                $f_data[] = $temp;

            }

            $result = $f_data;

        }

        return $result;

    }

    

    

    /**

     * 转换用户点评分数数组为点评满意度

     * @param type $data array('star_1_count'=>11,'star_2_count'=>313,'star_3_count'=>231,....) 评分数量数组

     * @return array('level_someone_1'=>421,'level_someone_2'=>132,'level_someone_3'=>1) 满意 一般 不满意

     */

    public static function format_star2satis_count($data){

        

        $star_1_count = $data['star_1_count'];

        $star_2_count = $data['star_2_count'];

        $star_3_count = $data['star_3_count'];

        $star_4_count = $data['star_4_count'];

        $star_5_count = $data['star_5_count'];

        $star_total = $star_1_count+$star_2_count+$star_3_count+$star_4_count+$star_5_count;

        

        $satis_type_1 = $star_1_count;  //不满意

        $satis_type_2 = $star_2_count+$star_3_count;    //一般

        $satis_type_3 = $star_4_count+$star_5_count;    //满意

        

        //满意~不满意的 百分比及 点评的人数

        $retult = array();        

        $retult['star_type_1'] = array('satis_percent'=>round($satis_type_1/$star_total,2)*100,'count'=>$satis_type_1); 

        $retult['star_type_2'] = array('satis_percent'=>round($satis_type_2/$star_total,2)*100,'count'=>$satis_type_2);

        $retult['star_type_3'] = array('satis_percent'=>round($satis_type_3/$star_total,2)*100,'count'=>$satis_type_3);

        return $retult;

    }

    

    public static function del_review($id){

        $sql = "delete from ".DB_PREFIX."review where id =".$id;

        $GLOBALS['db']->query($sql);				

        if($GLOBALS['db']->error=="")

        {

            //删除点评对应表数据

            $GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."review_group WHERE review_id =".$id);

            $GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."review_image WHERE review_id =".$id);

            return true;

        }  else {

            return false;

        }

    }

    

}

