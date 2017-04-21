
var is_click_scroll = false; //是否为点击后的滚动
$(document).ready(function(){
	
	if($.browser.msie)
	{
		$("#company_description").css("left",$(".upload_btn").position().left-200);
		$("#company_description").css("top",$(".upload_btn").position().top-60);
		
		$(".upload_btn").bind("mouseover",function(e){
			
			$("#company_description").css("left",e.clientX-210);
			$("#company_description").css("top",e.clientY+$(window).scrollTop()-60);
		});
	}
	else
	{
		$("#company_description").hide();
		$(".upload_btn").bind("click",function(){
			$("#company_description").click();
		});
	}
	
	
	


	$(".ajax_loading").find(".bg").css("height",$(document).height());
	$(".ajax_loading").find(".tip").css("left",$(document).width()/2-100);
	
	//绑定提交按钮与重置按钮
	$("#submit_show_btn").bind("click",function(){
		$("#submit_btn").click();
	});
	$("#reset_show_btn").bind("click",function(){
		$("#reset_btn").click();		
		 $.each($("*[holder]") ,function(i, obj){
			$(obj).blur(); 
		 });
	});
	
	//绑定文件域的变化
	$("#company_description").bind("change",function(){
		$("#company_description_show").click();
		$("#company_description_show").val($("#company_description").val());
	});	

	reposition(); //重定位当前滚动位置
	var menuYloc = $(".menu_box").offset().top;
	$(window).scroll(function (){

		var offsetTop = menuYloc + $(window).scrollTop() +"px";
		$(".menu_box").animate({top : offsetTop },{ duration:500 , queue:false }); 
		
		var loading_top = $(window).height()/2 +  $(window).scrollTop() - 180;
		loading_top +="px";
		$(".ajax_loading").find(".tip").css("top",loading_top);
		
		if(!is_click_scroll)
		reposition();
	});
	
	$(".menu_box").find("li").bind("click",function(){
		is_click_scroll = true;
		$(".menu_box").find("li").removeClass("current");
		$(this).addClass("current");
		$(".form_sector").find(".sector_head").removeClass("current");
		
		var rel = $(this).attr("rel");
		$(".form_sector[rel='"+rel+"']").find(".sector_head").addClass("current");
		var top = $(".form_sector[rel='"+rel+"']").offset().top;
		$("html,body").animate({scrollTop:top},"fast","swing",function(){
			is_click_scroll = false;
		});
	});
	
	//绑定表单提交
	$("form[name='join_form']").bind("submit",function(){
		
		if(check_form())
		{
			submit_form();
		}
		return false;
	});

});




function submit_form()
{
	$(".ajax_loading").fadeIn();
	$("form[name='join_form']").ajaxSubmit({
        dataType:'json',
        success:function(data){
        	$(".ajax_loading").fadeOut();
        	if(data.status)
        	{
        		$.showSuccess(data.info,function(){
        			if(data.jump!="")
            		{
            			location.href = data.jump;
            		}
        		});     
        		
        	}
        	else
        	{
        		$.showErr(data.info,function(){
        			if(data.jump!="")
            		{
            			$("input[name='"+data.jump+"']").focus();
            		}  
        		});
        		      		
        	}
        	
        },
        error:function(xhr){	
        	$(".ajax_loading").fadeOut();
        	$.showErr("注册失败");
			
        }
    });
}


function reposition()
{
	var form_sectors = $(".form_sector");
	for(i=0;i<form_sectors.length;i++)
	{
		var scrollTop = $(document).scrollTop() + 90; 
		var current_top = $(form_sectors[i]).offset().top;
		
		
		if(i<form_sectors.length-1)
		var next_top = $(form_sectors[i+1]).offset().top;
		else
		{
			next_top = current_top + 400;
		}
		
		if(scrollTop>current_top&&scrollTop<next_top)
		{
				rel_id = $(form_sectors[i]).attr("rel");
				$(".menu_box").find("li").removeClass("current");
				$(".menu_box").find("li[rel='"+rel_id+"']").addClass("current");
				$(".form_sector").find(".sector_head").removeClass("current");
				$(".form_sector[rel='"+rel_id+"']").find(".sector_head").addClass("current");
				break;
		}
	}
}



function check_form()
{
	
	if($.trim($("input[name='user_name']").val())=="")
	{
		$.showErr("请输入用户名！",function(){
			$("input[name='user_name']").focus();
		});
		
		return false;
	}
	
	if($.trim($("input[name='user_pwd']").val())=="")
	{
		$.showErr("请输入登录密码！",function(){
			$("input[name='user_pwd']").focus();
		});
		return false;
	}
	
	if($.trim($("input[name='user_pwd']").val())!=$.trim($("input[name='cfm_user_pwd']").val()))
	{
		$.showErr("登录密码匹配错误！",function(){
			$("input[name='cfm_user_pwd']").focus();
		});
		return false;
	}
	
	if($.trim($("input[name='contact_name']").val())=="")
	{
		$.showErr("请输入联系人姓名！",function(){
			$("input[name='contact_name']").focus();
		});
		return false;
	}
	
	if($.trim($("input[name='contact_tel']").val())=="")
	{
		$.showErr("请输入联系电话！",function(){
			$("input[name='contact_tel']").focus();
		});

		return false;
	}
	
	if($.trim($("input[name='contact_mobile']").val())=="")
	{
		$.showErr("请输入联系手机号！",function(){
			$("input[name='contact_mobile']").focus();
		});

		return false;
	}
	
	if(!$.checkMobilePhone($.trim($("input[name='contact_mobile']").val())))
	{
		$.showErr("请输入正确的手机号！",function(){
			$("input[name='contact_mobile']").focus();
		});

		return false;
	}
	
	if($.trim($("input[name='contact_email']").val())=="")
	{
		$.showErr("请输入email地址！",function(){
			$("input[name='contact_email']").focus();
		});

		return false;
	}
	
	if(!$.checkEmail($.trim($("input[name='contact_email']").val())))
	{
		$.showErr("请输入正确的email地址！",function(){
			$("input[name='contact_email']").focus();
		});

		return false;
	}
	
	if($.trim($("input[name='company_name']").val())=="")
	{
		$.showErr("请输入公司名称！",function(){
			$("input[name='company_name']").focus();
		});
		return false;
	}
	
	if($.trim($("input[name='company_address']").val())=="")
	{
		$.showErr("请输入公司地址！",function(){
			$("input[name='company_address']").focus();
		});
		return false;
	}
	
	return true;
}

