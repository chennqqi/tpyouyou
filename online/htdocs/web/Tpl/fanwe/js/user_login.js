$(document).ready(function(){
    $("#page_login_btn").bind("click",function(){
        dopagelogin();
    });
    $("#page_login_form").bind("submit",function(){
        dopagelogin();
        return false;
    });

});
function dopagelogin()
{
    $form = $("#page_login_form");
    var action = $form.attr("action");
    var query = new Object();
    query.user_key = $.trim($form.find("input[name='user_key']").val());
    query.user_pwd = $.trim($form.find("input[name='user_pwd']").val());
    query.user_verify = $.trim($form.find("input[name='user_verify']").val());
    query.save_user = $form.find("input[name='save_user']:checked").val();
    query.ajax = 2;
    if(query.user_key == "")
    {
        $.showErr("用户名/手机/邮箱必需填写");
    }
    else if(query.user_pwd == "")
    {
        $.showErr("登录密码不能为空");
    }
   else  if(query.user_verify == "")
    {
        $.showErr("验证码不能为空");
    }
    else
    {
          $.ajax({ 
                url: action,
                dataType: "jsonp",
                jsonp: 'callback',  
                data:query,
                type:"GET",
                global:false,
                success: function(obj){
                    refresh_verify($("#page_login_verify").find("img"));
                    if(obj.status==1)
                    {
                    	if(obj.script!="")
                        {
                        	$("body").append(obj.script);
                        }
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
}
