var is_submit_lock = false;
$(function(){
	$("#consignee_list tr").hover(function(){
		$(this).addClass("cur");
	},function(){
		$(this).removeClass("cur");
	});
	$(".j_set_default").live("click",function(){
		if (is_submit_lock) {
			alert("处理中请稍后");
			return false;
		}
		is_submit_lock = true;
		var id = $(this).attr("dataid");
		$.ajax({
			url : SET_DEFAULT_URL,
			data:"&id="+id,
			type:"post",
			dataType:"json",
			success:function(ajaxobj){
				is_submit_lock = false;
				if(ajaxobj.status==1){
					$(".J_setDefaultBX").each(function(){
						var tid = $(this).attr("dataid");
						if(tid == id){
							$(this).parent().addClass("def");
							$(this).html('<span style="color:blue">默认地址</span>');
						}
						else{
							$(this).parent().removeClass("def");
							$(this).html('<a href="javascript:void(0);" class="j_set_default" dataid="'+tid+'">设为默认</a>');
						}
					});
					
				}
				else{
					$.showErr(ajaxobj.info);
				}
			},
			error:function(){
				is_submit_lock = false;
				$.showErr("请求操作失败");
			}
		});
		
	});
	
	$(".j_del_namelist").live("click",function(){
		if (is_submit_lock) {
			alert("处理中请稍后");
			return false;
		}
		is_submit_lock = true;
		
		var obj = $(this);
		var id = obj.attr("dataid");
		$.ajax({
			url : DEL_NAMELIST_URL,
			data:"&id="+id,
			type:"post",
			dataType:"json",
			success:function(ajaxobj){
				is_submit_lock = false;
				if(ajaxobj.status==1){
					obj.parent().parent().remove();
				}
				else{
					$.showErr(ajaxobj.info);
				}
			},
			error:function(){
				is_submit_lock = false;
				$.showErr("请求操作失败");
			}
		});
	});
	
	$("#namelistName").focus(function(){
		$("#namelistNameError").addClass("hide");
	});
	
	$("#paperSn").focus(function(){
		$("#paperSnError").addClass("hide");
	});

	$("#nameListTel").focus(function(){
		$("#nameListTelError").addClass("hide");
	});
	
	$(".j_edit_consignee,.j_noedit").live("click",function(){
		if (is_submit_lock) {
			alert("处理中请稍后");
			return false;
		}
		is_submit_lock = true;
		
		var obj = $(this);
		var id = obj.attr("dataid");
		$.ajax({
			url : EDIT_CONSIGNEE_URL,
			data:"&id="+id,
			type:"post",
			dataType:"json",
			success:function(ajaxobj){
				is_submit_lock = false;
				if(ajaxobj.status==1){
					$("#J_consigneeBX").html(ajaxobj.info);
					
				}
				else{
					$.showErr(ajaxobj.info);
				}
			},
			error:function(){
				is_submit_lock = false;
				$.showErr("请求操作失败");
			}
		});
	});
	
	$(".J_do_namelist").live("click",function(){
		var is_err = false;
		if($.trim($("#namelistName").val())=="")
		{
			$("#namelistNameError .error_notice i").html("请填写姓名");
			$("#namelistNameError").removeClass("hide");
			is_err = true;
		}
		else{
			$("#namelistNameError").addClass("hide");
		}
		
		
		if($.trim($("#paperSn").val())=="")
		{
			$("#paperSnError .error_notice i").html("请填写证件号码");
			$("#paperSnError").removeClass("hide");
			is_err = true;
		}
		else{
			$("#paperSnError").addClass("hide");
		}
		
		if($.trim($("#nameListTel").val())=="")
		{
			$("#nameListTelError .error_notice i").html("请填写手机号码");
			$("#nameListTelError").removeClass("hide");
			is_err = true;
		}
		else{
			if ($.checkMobilePhone($("#namelistTel").val()) == false) {
				$("#nameListTelError .error_notice i").html("手机号码格式错误");
				$("#nameListTelError").removeClass("hide");
				is_err = true;
			}
			else {
				$("#nameListTelError").addClass("hide");
			}
		}
		
		if(is_err==true){
			return false;
		}
		else{
			$("#namelist_form").submit()
		}
	});
});


