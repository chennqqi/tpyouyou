<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class reviewModule extends BaseModule {

    function init_review() {
        $review_type = intval($_GET['review_type']);
        $review_rel_id = intval($_GET['review_rel_id']);

        //limit
        $page = $_GET['p'] > 1 ? $_GET['p'] : 1;

        $page_site = 15; //每页数据量

        if ($page > 1) {
            $limit_start = ($page - 1) * $page_site;
        } else {
            $limit_start = 0;
        }
        $param = array();
        $where[] = "review_type=" . $review_type;
        $param['review_type'] = $review_type;

        $where[] = "review_rel_id=" . $review_rel_id;
        $param['review_rel_id'] = $review_rel_id;

        $where[] = 'is_verify = 1';
        if ($_REQUEST['args_star']) {
            $args_star = $_REQUEST['args_star'];
            if ($args_star == 1) {
                $where[] = "point = 1";
            }
            if ($args_star == 2) {
                $where[] = "point in(2,3)";
            }
            if ($args_star == 3) {
                $where[] = "point in(4,5)";
            }
        }
        if ($_REQUEST['args_pic'] > 0) {
            $where[] = "image_count >0";
        }

        $limit = " limit " . $limit_start . "," . $page_site;
        $condition = " WHERE " . implode(" AND ", $where);
        $total_count = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " . DB_PREFIX . "review " . $condition);

        $list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "review" . $condition . " ORDER BY id DESC " . $limit);

        $pager = buildPage("review#init_review", $param, $total_count, $page, $page_site, 1);

        $GLOBALS['tmpl']->assign('pager', $pager);
        $result['pager'] = $GLOBALS['tmpl']->fetch("inc/pages.html");

        foreach ($list as $k => $v) {
            $userids[] = $v['user_id'];
        }
        $userids = array_unique($userids);
        require_once APP_ROOT_PATH . "system/libs/user.php";
        $user_avatars = User::get_user_avatar($userids,66,66);

        foreach ($list as $k => $v) {
            $temp = $v;
            $temp['review_time'] = to_date($v['review_time'], "Y-m-d H:i:s");
            $temp['avatar'] = $user_avatars[$v['user_id']]['img'];
            //点评类型
            $temp['group_point'] = unserialize($v['group_point']);
            $temp['imgs'] = unserialize($v['image_list']);
            $temp['money'] = format_price_to_display($v['money']);
            $temp['voucher_count'] = format_price_to_display($v['voucher_count']);
            $temp['point_satify'] = $v['point'] * 20;
            $f_data[] = $temp;
        }
        $GLOBALS['tmpl']->assign("list", $f_data);
        $result['review_total'] = $total_count;
        $result['html'] = $GLOBALS['tmpl']->fetch("inc/review_item.html");
        $result['status'] = 1;
        ajax_return($result);
    }

    function save_review() {
        global_run();
        $result['status'] = 0;
        if (empty($GLOBALS['user'])) {
            $result['status'] = -1;
            ajax_return($result);
        }
        $review_type = intval($_GET['review_type']);
        $review_rel_id = intval($_GET['review_rel_id']);

        //获取订单信息
        $order = get_review_order($review_type, $review_rel_id, $GLOBALS['user']['id']);

        if (empty($order)) {
            $result['status'] = -2;
            ajax_return($result);
        }

        require_once APP_ROOT_PATH . 'system/libs/contentcheck.php';


        //处理参数
        $ins_data['user_id'] = $GLOBALS['user']['id'];
        $ins_data['nickname'] = $GLOBALS['user']['nickname'];
        $ins_data['review_ip'] = $GLOBALS['user']['login_ip'];

        $content = strim($_REQUEST['content']);
        if (Contentcheck::checkword($content) == 1) {
            $result['status'] = -100; //禁用
            ajax_return($result);
            exit;
        }
        $ins_data['review_content'] = $content;
        $ins_data['review_time'] = NOW_TIME;
        $ins_data['review_type'] = $review_type;
        $ins_data['review_rel_id'] = $review_rel_id;
        $ins_data['point'] = $_REQUEST['dp_point'];
        //点评字段评分
        $dp_point_group = $_REQUEST['dp_point_group'];
        foreach ($dp_point_group as $k => $v) {
            $point_ids[] = $k;
        }
        
        if (count($point_ids) > 0) {
            $group_field = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "review_group_field WHERE id in(" . implode(",", $point_ids) . ")");
            foreach ($group_field as $k => $v) {
                $temp['name'] = $v['name'];
                $temp['point'] = $dp_point_group[$v['id']];
                $f_point_data[] = $temp;
                
            }
        }

        $ins_data['group_point'] = count($f_point_data) > 0 ? serialize($f_point_data) : "";
        $ins_data['image_list'] = count($_REQUEST['img_list']) > 0 ? serialize($_REQUEST['img_list']) : '';
        $ins_data['image_count'] = count($_REQUEST['img_list']) > 0 ? count($_REQUEST['img_list']) : 0;

        //获取订单信息
        $order = get_review_order($ins_data['review_type'], $ins_data['review_rel_id'], $ins_data['user_id']);
        if ($order['review_return_voucher_type_id']) {
            //获取代金券信息
            $voucher_item = $GLOBALS['db']->getRow("SELECT * FROM " . DB_PREFIX . "voucher WHERE id=" . $order['review_return_voucher_type_id']);
            //代金券必须有效且未过期
            if ($voucher_item['is_effect'] && $voucher_item['end_time'] > NOW_TIME) {
                $ins_data['voucher_type_id'] = $order['review_return_voucher_type_id'];
                $ins_data['voucher_name'] = $voucher_item['voucher_name'];
                $ins_data['voucher_count'] = $voucher_item['money'];
            }
        }


        $ins_data['review_rel_name'] = $order['name'];
        $ins_data['money'] = $order['review_return_money'];
        $ins_data['score'] = $order['review_return_score'];
        $ins_data['exp'] = $order['review_return_exp'];


        $GLOBALS['db']->autoExecute(DB_PREFIX . "review", $ins_data, "INSERT");
        $review_id = $GLOBALS['db']->insert_id();
        if ($GLOBALS['db']->affected_rows()) {
            //插入点评分组字段数据
            $ins_group_data = array();
            foreach($dp_point_group as $k=>$v){
                $ins_group_data = array();
                $ins_group_data['review_id'] = $review_id;
                $ins_group_data['review_group_id'] = $k;
                $ins_group_data['point'] = $v;
                $GLOBALS['db']->autoExecute(DB_PREFIX . "review_group", $ins_group_data, "INSERT");
            }
            
            //更新订单状态，不允许点评
            update_set_allow_review($review_type, $order['order_id'], 0);
            $result['status'] = 1;
        }
        ajax_return($result);
    }

    function ajax_del_review(){
        global_run();
        if(!empty($GLOBALS['user'])){
            $uid = $GLOBALS['user']['id'];
            $id = intval($_GET['id']);
        }  else {
            $result['status'] =2;
        }
        //用户只能删除自己未审核的点评
        if ($GLOBALS['db']->getOne("SELECT COUNT(id) FROM ".DB_PREFIX."review WHERE user_id = ".$uid." AND id=".$id." AND is_verify=0")>0){
            require_once APP_ROOT_PATH.'system/libs/review.php';
            if(Review::del_review($id)){
                $result['status'] =1;
            }else{
                $result['status'] =0;
            }
        }else{
            $result['status'] =0;
        }
        ajax_return($result);
    }

}
