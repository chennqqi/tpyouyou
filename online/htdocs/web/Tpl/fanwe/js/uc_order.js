jQuery(function(){
	$(".J_cancelOrders").live("click",function(){
		var ajaxurl = $(this).attr("href");
		$.showCfm("确定取消订单吗？",function(){
			$.ajax({
				url:ajaxurl,
				dataType:"json",
				success:function(ajaxobj){
					if(ajaxobj.status==1){
						window.location.reload();
					}
					else{
						$.showSuccess(ajaxobj.info);
					}
				},
				error:function(){
					$.showErr("请求错误");
				}
			});
		});
		return false;
	});
	
	$(".j_refund,.j_appoint").live("click",function(){
		var op_title = $(this).html();
		var url=$(this).attr("href");
		$.ajax({
			url:url,
			type:"post",
			dataType:"json",
			success:function(ajaxobj){
				if(ajaxobj.status == 1){
					$.weeboxs.open(ajaxobj.info, {contentType:'html',boxid:'ajax_op_ticket_box',showButton:false,title:op_title+"操作",width:570,type:'wee', focus:"#refund_do_txt",onclose:function(){
			            
			        }});
					
					
				}
				else{
					$.showErr(ajaxobj.info);
				}
			}
			,error:function(){
				$.showErr("请求出错");
			}
		});
		return false;
	});
	
	$("#J_do_refund").live("click",function(){
		var ajaxurl = $(this).parent().attr("action");
		var query = $(this).parent().serialize();
		$.ajax({
			url:ajaxurl,
			data:query,
			type:"post",
			dataType:"json",
			success:function(ajaxobj){
				if(ajaxobj.status == 1){
					window.location.reload();
				}
				else{
					$.showErr(ajaxobj.info);
				}
			}
			,error:function(){
				$.showErr("请求出错");
			}
		});
		return false;
	});
	
	$("#J_cancel_refund").live("click",function(){
		$.weeboxs.close("ajax_op_ticket_box");
	});
});
