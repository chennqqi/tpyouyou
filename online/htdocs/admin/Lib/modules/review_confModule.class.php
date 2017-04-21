<?php
class review_confModule extends AuthModule{
    
    public function index(){
        $review_conf = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."review_conf");
        if($review_conf){
            $review_conf['REVIEW_MONEY'] = format_price_to_display($review_conf['REVIEW_MONEY']);
            $GLOBALS['tmpl']->assign("review_conf",$review_conf);
        }
        $vouchers = $GLOBALS['db']->getAll("select id,voucher_name from ".DB_PREFIX."voucher_type where deliver_type=3 and is_effect=1 ORDER BY sort DESC");
    	$GLOBALS['tmpl']->assign("vouchers",$vouchers);
        $GLOBALS['tmpl']->assign('formaction',admin_url("review_conf#save",array("ajax"=>1)));
        $GLOBALS['tmpl']->display("core/review/review_conf.html");
    }
    public function save(){
        $ajax = intval($_REQUEST['ajax']);
        $save_data = array();
        
        $save_data['REVIEW_MONEY'] = format_price_to_db($_REQUEST['REVIEW_MONEY']);
        $save_data['REVIEW_VOUCHER'] = intval($_REQUEST['REVIEW_VOUCHER']);
        $save_data['REVIEW_SCORE'] = intval($_REQUEST['REVIEW_SCORE']);
        $save_data['REVIEW_EXP'] = intval($_REQUEST['REVIEW_EXP']);
        

         $GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."review_conf");
        $GLOBALS['db']->autoExecute(DB_PREFIX."review_conf",$save_data,"INSERT","","SILENT");
       
        if ($GLOBALS['db']->error()=="") {
                //成功提示
                save_sys_config();
                save_log(lang("UPDATE_SUCCESS"),1);
                showSuccess(lang("UPDATE_SUCCESS"),$ajax);
        } else {
                //错误提示
                showErr(lang("UPDATE_FAILED")."<br />".$GLOBALS['db']->error(),$ajax);
        }
    }
}