$(function(){
    $.Del_guide = function(id,type){
        if(confirm("确定要删除游记吗?")){
            var query = new Object();
            query.id = id;
            query.type = type;
            $.ajax(
                    {
                            type: "POST",
                            url: AJAX_DEL_GUIDE_URL,
                            data: query,
                            dataType:"JSON",
                            success: function(result){
                                if(result.status ==1){
                                     $.showSuccess("删除成功！");
                                     location.reload();
                                }else{
                                    location.href = result.jump;
                                }
                            }
                    }
            );
        }
    };
    
})