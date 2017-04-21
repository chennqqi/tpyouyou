$(document).ready(function(){
    $("#do_verify").live("click",function(){        
        $("form[name='verify_form']").submit();
    });
    $("form[name='verify_form']").bind("submit",function(){
         doverify();
         return false;
    });
     $("#do_resend").live("click",function(){        
        doresend();
        return false;
    });
});

function doresend()
{
    var action = $("#do_resend").attr("href");
    var query = new Object();
    query.ajax = 1;
    $.ajax({ 
                url: action,
                data:query,
                dataType:"json",
                type:"POST",
                global:false,
                success: function(obj){
                   if(obj.status)
                   {
                       IS_RUN_CRON = 1;
                       $.showSuccess(obj.info,function(){
                           if(obj.jump!="")
                           location.href = obj.jump;
                       });
                   }
                   else
                   {
                        $.showErr(obj.info,function(){
                           if(obj.jump!="")
                           location.href = obj.jump;
                       });
                   }
                }
            }); 
}

function doverify()
{
    if($.trim($("form[name='verify_form']").find("input[name='c']").val())=="")
    {
        $.showErr("请输入验证码");
        return;
    }

    var action = $("form[name='verify_form']").attr("action");
    var query = $("form[name='verify_form']").serialize();
    $.ajax({ 
                url: action,
                data:query,
                dataType:"json",
                type:"POST",
                global:false,
                success: function(obj){
                   if(obj.status)
                   {
                       $.showSuccess(obj.info,function(){
                           if(obj.jump!="")
                           location.href = obj.jump;
                       });
                   }
                   else
                   {
                        $.showErr(obj.info,function(){
                           if(obj.jump!="")
                           location.href = obj.jump;
                       });
                   }
                }
            }); 
}
