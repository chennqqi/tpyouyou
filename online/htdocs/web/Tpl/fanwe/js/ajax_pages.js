//ajax 分页处理
var AJAX_PAGE_FUN ;
var AJAX_URL = '';

$(".pages .page_btn").live("click",function(){
    AJAX_URL = $(this).attr("url");
    $.AjaxPage($(this),AJAX_PAGE_FUN);
});
$.AjaxPage = function(obj,fun){
    fun.call(this,obj);
};
