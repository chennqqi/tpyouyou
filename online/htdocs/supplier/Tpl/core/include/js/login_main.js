$(document).ready(function(){
	init_holder();
	var document_height = $(document).height();
	var window_height = $(window).height();
	var height = window_height<document_height?document_height:window_height;
	$("#main_bg").css("height",height);
	$(window).resize(function(){
		var document_height = $(document).height();
		var window_height = $(window).height();
		var height = window_height<document_height?document_height:window_height;
		$("#main_bg").css("height",height);
	});
	
	
	
	
	//绑定提交按钮
	$("input[name='adm_name']").focus();
	$(".submit").bind("click",function(){ do_login();});	
	$("form").bind("submit",function(){
		do_login();
		return false;
	});
	
	//绑定提交结束
	
});




function init_ui_textbox()
{
    $(".ui-textbox,.ui-textarea").bind("focus",function(){
            $(this).removeClass("hover");
            $(this).removeClass("normal");
            $(this).addClass("hover");
    });
    $(".ui-textbox,.ui-textarea").bind("blur",function(){
            $(this).removeClass("hover");
            $(this).removeClass("normal");
            $(this).addClass("normal");
    });
}


function init_holder()
{
    init_ui_textbox();
     $.each($("*[holder]") ,function(i, obj){
        
        
           if('placeholder' in document.createElement('input'))
           {
                $(obj).attr("placeholder",$(obj).attr("holder"));
           }
           else
           
           {
                var holder = $(obj).prev();
                if($(holder).attr("rel")!="holder")
                holder = $("<span style='position:absolute; color:#666;' rel='holder'>"+$(obj).attr("holder")+"</span>");
                $(holder).css({"font-size":$(obj).css("font-size"),"padding-left":$(obj).css("padding-left"),"padding-right":$(obj).css("padding-right"),"padding-top":$(obj).css("padding-top"),"padding-bottom":$(obj).css("padding-bottom")});
                $(holder).css("left",$(obj).position().left);
                $(holder).css("top",$(obj).position().top);
                $(holder).css("width",$(obj).width());
                $(obj).before(holder);  

                if($.trim($(obj).val())!="")
                {
                    $(holder).css("display","none");
                }
                $(holder).click(function(){
                    $(obj).focus();
                });     
                $(obj).focus(function(){
                    $(holder).css("display","none");
                });
                $(obj).blur(function(){
                    if($.trim($(obj).val())=="")
                    $(holder).css("display","");
                });
           }            
    });
}



//刷新验证码
function refresh_verify(dom)
{
    $(dom).attr("src",$(dom).attr("rel")+"?"+Math.random());
}


var is_logining = false;
function do_login(){

	if(is_logining)return;
	
	//验证帐号
	if($.trim($(".adm_name").val())=='')
	{
		$(".adm_name").val("");
		$(".adm_name").focus();
		$("#login_msg").html(ADM_NAME_EMPTY);
		$("#login_msg").oneTime(2000, function() {
		    $(this).html("");		    
		    
		 });
		return;
	}	
	//验证密码
	if($.trim($(".adm_password").val())=='')
	{
		$(".adm_password").val("");
		$(".adm_password").focus();
		$("#login_msg").html(ADM_PASSWORD_EMPTY);
		$("#login_msg").oneTime(2000, function() {
		    $(this).html("");		    
		 });
		return;
	}	
	
	//验证密码
	if($.trim($(".adm_verify").val())=='')
	{
		$(".adm_verify").val("");
		$(".adm_verify").focus();
		$("#login_msg").html(ADM_VERIFY_EMPTY);
		$("#login_msg").oneTime(2000, function() {
		    $(this).html("");		    
		 });
		return;
	}	
	
	//表单参数
	param = $("form").serialize();
	url = $("form").attr("action");
	$(".adm_name").attr("disabled",true);
	$(".adm_password").attr("disabled",true);
	$(".adm_verify").attr("disabled",true);
	is_logining = true;
	$.ajax({ 
		url: url, 
		data: param+"&ajax=1",
		dataType: "json",
		success: function(obj){
			is_logining = false;
			if(obj.statusCode==200)
			{
				$("#login_msg").html(obj.message);
				$("#login_msg").oneTime(2000, function() {
				    $(this).html("");
				    location.reload();
				 });
				
			}
			else
			{
				$("#login_msg").html(obj.message);
				$("#login_msg").oneTime(1000, function() {
				    $(this).html("");
				    $(".submit").attr("disabled",false);
				    $(".adm_name").attr("disabled",false);
					$(".adm_password").attr("disabled",false);
					$(".adm_verify").attr("disabled",false);
					refresh_verify($("#page_login_verify").find("img"));
				 });
			}
	}});
}