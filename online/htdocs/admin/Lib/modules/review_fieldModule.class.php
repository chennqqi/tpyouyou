<?php
class review_fieldModule extends AuthModule{
    
    public function index() {
        //分页
        if(isset($_REQUEST['numPerPage']))
        {			
                $param['pageSize'] = intval($_REQUEST['numPerPage']);
                if($param['pageSize'] <=0||$param['pageSize'] >200)
                        $param['pageSize'] = ADMIN_PAGE_SIZE;
        }
        else
                $param['pageSize'] = ADMIN_PAGE_SIZE;

        if(isset($_REQUEST['pageNum']))
                $page = intval($_REQUEST['pageNum']);
        else
                $page = 0;
        if($page==0)
                $page = 1;
        $limit = (($page-1)*$param['pageSize']).",".$param['pageSize'];
        $param['pageNum'] = $page;
        
         //排序
		if(isset($_REQUEST['orderField']))
                $param['orderField'] = strim($_REQUEST['orderField']);
        else
                $param['orderField'] = "id";

        if(isset($_REQUEST['orderDirection']))
                $param['orderDirection'] = strim($_REQUEST['orderDirection'])=="asc"?"asc":"desc";
        else
                $param['orderDirection'] = "desc";
        
	$count_sql = "SELECT count(*) FROM ".DB_PREFIX."review_group_field ".$condition;
        $sql = "SELECT * FROM ".DB_PREFIX."review_group_field ".$condition."  order by ".$param['orderField']." ".$param["orderDirection"]." limit ".$limit;
        //查询
        $totalCount = $GLOBALS['db']->getOne($count_sql);
        $list = $GLOBALS['db']->getAll($sql);	
        
        //链接
        $GLOBALS['tmpl']->assign("addurl",admin_url("review_field#add"));
        $GLOBALS['tmpl']->assign("editurl",admin_url("review_field#edit"));

	$GLOBALS['tmpl']->assign('list',$list);
        $GLOBALS['tmpl']->assign('totalCount',$totalCount);
        $GLOBALS['tmpl']->assign('param',$param);
        $GLOBALS['tmpl']->display("core/review/field_index.html");
    }
    
    public function add(){
        $GLOBALS['tmpl']->assign('formaction',admin_url("review_field#insert",array("ajax"=>1)));
        $GLOBALS['tmpl']->display("core/review/add.html");
    }

    public function insert(){
        $ajax = intval($_REQUEST['ajax']);
        $ins_data = array();
        $ins_data['review_type'] = intval($_REQUEST['review_type']);
        $ins_data['name'] = $_REQUEST['name'];
        if($ins_data){
            $GLOBALS['db']->autoExecute(DB_PREFIX."review_group_field",$ins_data);
            showSuccess(lang("SUCCESS_PAGE_TITLE"),$ajax,admin_url("review_field#add"));	
        }else{
            showErr(lang("INVALID_OPERATION"),$ajax);
        }
            
    }

    public function edit(){
        $id = intval($_REQUEST['id']);
        $item = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."review_group_field WHERE id=".$id);
        $GLOBALS['tmpl']->assign("item",$item);
        $GLOBALS['tmpl']->assign('formaction',admin_url("review_field#update",array("ajax"=>1)));
        $GLOBALS['tmpl']->display("core/review/edit.html");
    }
    
    public function update(){
        $ajax = intval($_REQUEST['ajax']);
        $id = intval($_REQUEST['id']);
        $ins_data = array();
        $ins_data['review_type'] = intval($_REQUEST['review_type']);
        $ins_data['name'] = $_REQUEST['name'];
        if($ins_data){
            $GLOBALS['db']->autoExecute(DB_PREFIX."review_group_field",$ins_data,'update','id='.$id);
            showSuccess(lang("SUCCESS_PAGE_TITLE"),$ajax);	
        }else{
            showErr(lang("INVALID_OPERATION"),$ajax);
        }
    }
}