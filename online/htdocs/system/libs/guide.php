<?php

class Guide{
    
    /**
     * 删除游记
     * @param int $ID 游记编号
     * @return array("id"=>1,"user_id"=>1,"title"=>"我的第一次旅行") 被删除的游记数据
     */
    public static function del_guide($id){
        $guide_item = $GLOBALS['db']->getRow("SELECT id,user_id FROM ".DB_PREFIX."tour_guide WHERE id=".$id);
        if($guide_item){//已审核
            $GLOBALS['db']->query("DELETE from ".DB_PREFIX."tour_guide WHERE id=".$id);
        }else{ //未审核
            $guide_item = $GLOBALS['db']->getRow("SELECT id,user_id FROM ".DB_PREFIX."tour_guide_temp WHERE id=".$id);
            $GLOBALS['db']->query("DELETE from ".DB_PREFIX."tour_guide_temp WHERE id=".$id);
        }
        if($GLOBALS['db']->error()==""){//删除游记相关数据
            //删除图片表
            $GLOBALS['db']->query("delete from ".DB_PREFIX."tour_guide_gallery where guide_id=".$id);
            //删除景点表
            $GLOBALS['db']->query("delete from ".DB_PREFIX."tour_guide_spot where guide_id=".$id);
            //删除每日记录表
            $GLOBALS['db']->query("delete from ".DB_PREFIX."tour_guide_route where guide_id=".$id);
            //删除用户动态
            require_once APP_ROOT_PATH.'system/libs/user.php';
            User::del_active($guide_item['user_id'],2,$id); 
            
            return TRUE;
        }else{
            return FALSE;
        }
        
    }
    
}
