<?php
/**
 * 评论配置
 * @author Jobin.lin <jobin.lin@gmail.com>
 */
class comment_confModule extends AuthModule{
    /**
     * 评论配置表单
     */
    public function index(){
        $conf = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."comment_conf");
        if($conf){
            $GLOBALS['tmpl']->assign("conf",$conf);
        }

        $GLOBALS['tmpl']->assign("formaction",admin_url("comment_conf#save",array("ajax"=>1)));
        $GLOBALS['tmpl']->display("core/comment/comment_conf.html");
    }
   
    public function save(){
        $ajax = intval($_REQUEST['ajax']);
        $save_data = array();
        $save_data['COMMENT_EXP'] = intval($_REQUEST['COMMENT_EXP']);
        
        $GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."comment_conf");
        $GLOBALS['db']->autoExecute(DB_PREFIX."comment_conf",$save_data,"INSERT","","SILENT");
       
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
