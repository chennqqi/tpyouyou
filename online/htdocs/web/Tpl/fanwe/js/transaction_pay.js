$(document).ready(function(){
    $(".payment_tabs").find("li").live("click",function(){
        $(".payment_tabs").find("li").removeClass("current");
        $(this).addClass("current");
        rel = $(this).attr("rel");
        $(".payment_box").find(".box_item").removeClass("current");
        $(".payment_box").find(".box_item[rel='"+rel+"']").addClass("current");
    });
    
    $(".payment_box").find(".pay_item").live("click",function(){
    	$(".payment_box").find(".pay_item").removeClass("current");
    	$(".payment_box").find(".pay_item").find("input[type='radio']").attr("checked",false);
    	$(this).find("input[type='radio']").attr("checked",true);
    	$(this).addClass("current");
    	var c = $(this);
    	$("input[name='bank_type']").val(c.attr("rel"));
    });
    
    $(".payment_box").find(".pay_item:first").click();
    
    $(".submit_form").bind("click",function(){
    	var tip_html = "<span class='qes_span'>是否已经成功付款？</span>";
    	tip_html+="<div class='blank'></div><div class='paid_funcbtn'>";
    	tip_html+="<a href='javascript:$.weeboxs.close(\"pay_result\");' class='paid'>已经付款</a> <a href='javascript:$.weeboxs.close(\"pay_result\");' class='repaid'>重新支付</a> ";
    	tip_html+"<div class='blank'></div></div>";

    	$.weeboxs.open(tip_html, {boxid:'pay_result',contentType:'text',showButton:false, showCancel:false, showOk:false,title:'等待支付结果',width:260,type:'wee',onclose:function(){
    		location.reload();
    	}});

    	$(".pay_form").submit();
    });
});
