//关于表单的提示
function form_tip(str,field_row)
{
    $(".field_tip").find("*[class!='ok_status']").parent().html("");
    $(field_row).find(".field_tip").html("<span style='color:#090;'>"+str+"</span>");
}
function form_err(str,field_row)
{
    $(".field_tip").find("*[class!='ok_status']").parent().html("");
    $(field_row).find(".field_tip").html("<span class='err_status'>"+str+"</span>");
}
function form_ok(field_row)
{
    $(field_row).find(".field_tip").html("<div class='ok_status'></div>");
}

$(document).ready(function(){
    $("input[name='user_key']").live("blur",function(){
         if($.trim($(this).val())=="")
        {
            form_err("帐号名不能为空",$(this).parent().parent());
        }
        else
        {
            //ajax验证
            var query = new Object();
            query.field = "user_key";
            query.val = $.trim($(this).val());
            var ipt =$(this);
            $.ajax({ 
                url: CHECK_FIELD_URL,
                data:query,
                dataType:"json",
                type:"POST",
                global:false,
                success: function(obj){
                   if(obj.status)
                   {
                       form_ok(ipt.parent().parent());
                   }
                   else
                   {
                       form_err(obj.info,ipt.parent().parent());
                   }
                }
            }); 
        }
    });
    
    $("input[name='user_verify']").live("blur",function(){
         if($.trim($(this).val())=="")
        {
            form_err("请输入验证码",$(this).parent().parent());
        }
        else
        {
            //ajax验证
            var query = new Object();
            query.field = "user_verify";
            query.val = $.trim($(this).val());
            var ipt =$(this);
            $.ajax({ 
                url: CHECK_FIELD_URL,
                data:query,
                dataType:"json",
                type:"POST",
                global:false,
                success: function(obj){
                   if(obj.status)
                   {
                       form_ok(ipt.parent().parent());
                   }
                   else
                   {
                       form_err(obj.info,ipt.parent().parent());
                   }
                }
            }); 
        }
    });
    
    $("#next_step").live("click",function(){
        $("form[name='getpwd_form']").submit();
    });
    
    $("form[name='getpwd_form']").live("submit",function(){
        submit_form();
        return false;
    });
});


function submit_form()
{       
        var form = $("form[name='getpwd_form']");
        var action = $(form).attr("action");
         var query = $(form).serialize();
         $.ajax({ 
                url: action,
                data:query,
                dataType:"json",
                type:"POST",
                global:false,
                success: function(obj){
                   refresh_verify($("#getpwd_verify").find("img"));
                   if(obj.status)
                   {                      
                       IS_RUN_CRON = 1;
                       location.href = obj.jump;
                   }
                   else
                   {                       
                       if(obj.field!="")
                       {
                           form_err( obj.info, form.find("input[name='"+obj.field+"']").parent().parent());
                       }
                       else
                       {
                           $.showErr(obj.info,function(){
                               if(obj.jump!="")
                               location.href = obj.jump;
                           });
                       }
                   }
                }
            }); 
}
