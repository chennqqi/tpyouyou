var current_id = "email_reg";
$(document).ready(function(){    
    $(".regist_tab").find("a").live("click",function(){
        var id = $(this).attr("id");        
        if(id==current_id)return;
        var adom = $(this);
         $.ajax({ 
                url: LOAD_REG_FORM_URL,
                data:"type="+id,
                type:"POST",
                global:false,
                success: function(html){
                    $(".regist_left").html(html);
                    $(".regist_tab").find("li").removeClass("current");
                    adom.parent().addClass("current");
                    current_id = id;
                    init_holder();
                }
            }); 
    });
    
    $(".regist_left").find(".regist_btn").live("click",function(){
        $(".regist_left").find("form").submit();
    });
    $(".regist_left").find("form").live("submit",function(){
        if($(this).find("input[name='agree']:checked").val()!=1)
        {
            $.showErr("请先阅读服务条款，并勾选同意");
            return false;
        }
         var action = $(this).attr("action");
         var query = $(this).serialize();
         var form = $(this);
           $.ajax({ 
                url: action,
                data:query,
                dataType:"json",
                type:"POST",
                global:false,
                success: function(obj){
                    refresh_verify($("#reg_verify_img").find("img"));
                   if(obj.status)
                   {
                      
                       $.showSuccess(obj.info,function(){
                           if(obj.jump!="")
                           location.href = obj.jump;
                       });
                   }
                   else
                   {
                       
                       if(obj.field!="")
                       {
                           form_err( obj.info, form.find("input[name='"+obj.field+"']").parent().parent());
                       }
                       else if(obj.jump!="")
                       {
                           //需要验证会员身份
                           location.href = obj.jump; //跳转到验证页
                       }
                       else
                       {
                           $.showErr(obj.info);
                       }
                   }
                }
            }); 
         return false;
    });
    
    
    //以下绑定表单blur事件
    $(".regist_left").find("input[name='user_name']").live("blur",function(){
        if($.trim($(this).val())=="")
        {
            form_err("用户名不能为空",$(this).parent().parent());
        }
        else
        {
            //ajax验证
            var query = new Object();
            query.field = "user_name";
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
    
    $(".regist_left").find("input[name='email']").live("blur",function(){
        if($.trim($(this).val())=="")
        {
            form_err("邮箱不能为空",$(this).parent().parent());
        }
        else
        {
            //ajax验证
            var query = new Object();
            query.field = "email";
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
    
    $(".regist_left").find("input[name='mobile']").live("blur",function(){
        if($.trim($(this).val())=="")
        {
            form_err("手机号码不能为空",$(this).parent().parent());
        }
        else
        {
            //ajax验证
            var query = new Object();
            query.field = "mobile";
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
    
    $(".regist_left").find("input[name='user_pwd']").live("blur",function(){
        if($.trim($(this).val())=="")
        {
            form_err("登录密码不能为空",$(this).parent().parent());
        }
        else
        {
            form_ok($(this).parent().parent());
        }
    });
    
    $(".regist_left").find("input[name='cfm_user_pwd']").live("blur",function(){
        if($.trim($(this).val())!=$.trim($(".regist_left").find("input[name='user_pwd']").val()))
        {
            form_err("密码确认失败",$(this).parent().parent());
        }
        else
        {
            form_ok($(this).parent().parent());
        }
    });
    
    
    $(".regist_left").find("input[name='user_verify']").live("blur",function(){
        if($.trim($(this).val())=="")
        {
            form_err("验证码不能为空",$(this).parent().parent());
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
    
});


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