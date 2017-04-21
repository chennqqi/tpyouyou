<?php
/**
 * 游记配置
 * @author Jobin.lin <jobin.lin@gmail.com>
 */
class tour_guide_confModule extends AuthModule{
    /**
     * 游记配置表单
     */
    public function index(){
        $conf = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."tour_guide_conf");
        if($conf){
            $conf['GUIDE_MONEY'] = format_price_to_display($conf['GUIDE_MONEY']);
            $GLOBALS['tmpl']->assign("conf",$conf);
        }
        
        $GLOBALS['tmpl']->assign("formaction",admin_url("tour_guide_conf#save",array("ajax"=>1)));
        $GLOBALS['tmpl']->display("core/tour_guide/guide_conf.html");
    }
   
    public function save(){
        $ajax = intval($_REQUEST['ajax']);
        $save_data = array();
        $save_data['GUIDE_MONEY'] = format_price_to_db(floatval($_REQUEST['GUIDE_MONEY']));
        $save_data['GUIDE_SCORE'] = intval($_REQUEST['GUIDE_SCORE']);
        $save_data['GUIDE_EXP'] = intval($_REQUEST['GUIDE_EXP']);
        $save_data['GUIDE_PAGE_LOAD_GOUNT'] = intval($_REQUEST['GUIDE_PAGE_LOAD_GOUNT']);
        $save_data['GUIDE_PAGE_ITEM_COUNT'] = intval($_REQUEST['GUIDE_PAGE_ITEM_COUNT']);
        $save_data['GUIDE_SEO_TITLE'] = $_REQUEST['GUIDE_SEO_TITLE'];
        $save_data['GUIDE_SEO_KEYWORD'] = $_REQUEST['GUIDE_SEO_KEYWORD'];
        $save_data['GUIDE_SEO_DESCRIPTION'] = $_REQUEST['GUIDE_SEO_DESCRIPTION'];
        $save_data['GUIDE_IS_AGAIN'] = intval($_REQUEST['GUIDE_IS_AGAIN']);
        $save_data['GUIDE_HOT_SPOT'] = strim($_REQUEST['GUIDE_HOT_SPOT']);
        $GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."tour_guide_conf");
        $GLOBALS['db']->autoExecute(DB_PREFIX."tour_guide_conf",$save_data,"INSERT","","SILENT");
       
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
