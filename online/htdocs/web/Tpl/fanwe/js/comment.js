$(function(){
    var COMMENT_TYPE = '';
    var COMMENT_REL_ID = '';
    $(window).load(function() {
        //ajax 分页用常量
        AJAX_PAGE_FUN = init_comment;
        AJAX_URL = $(".comment_list_ajax_url").val();

        init_comment();
    });
    
    
    /**
     * 载入评论数据
     * @returns {ajax}
     */
    function init_comment(){
        var query = new Object();
        
         $.ajax(
                    {
                            type: "POST",
                            url: AJAX_URL,
                            data: query,
                            dataType:"JSON",
                            success: function(result){
                                $(".comment_list").html(result.html);
                                $(".pager_box").html(result.pager);
                                $(".comment_total").html(result.comment_total);
                                $(".comment_count_num").html(result.comment_total);
                            }
                    }
            );
    }
    /**
     * 保存评论
     * @returns {ajax}
     */
    function save_comment(){
        var comment_content = $(".comment_form_box textarea[name=comment_content]").val();
        var ajax_url = $(".comment_form_box input[name=ajax_url]").val();
    
            comment_content = $.trim(comment_content);
            if(comment_content.length>=10){
                //发布评论
                var query = new Object();

                query.comment_type = COMMENT_TYPE;
                query.comment_rel_id = COMMENT_REL_ID;
                query.comment_content = comment_content;
                $.ajax({
                    type: "POST",
                    url: ajax_url,
                    data: query,
                    dataType:"JSON",
                    success: function(result){
                        if(result.status == -100){
                            $.showErr("请勿提交非法内容");
                            return false;
                         }
                                
                        if(result.status == -1)
                        {
                            ajax_login();
                        }
                        else if(result.status){
                             AJAX_URL = $(".comment_list_ajax_url").val();
                             init_comment();
                            
                            $(".comment_form_box textarea[name=comment_content]").val("");
                        }else{
                            $.showErr("发布失败请稍后再试");
                        }
                    }
                });
            }else{
                $.showErr("不能少于10个字");
            }
        
    }
    $(".comment_form_box .reply_submit").live("click",function(){
        save_comment();
    });
});