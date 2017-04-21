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
	
	$(".j_del_consignee").live("click",function(){
		if (is_submit_lock) {
			alert("处理中请稍后");
			return false;
		}
		is_submit_lock = true;
		
		var obj = $(this);
		var id = obj.attr("dataid");
		$.ajax({
			url : DEL_CONSIGNEE_URL,
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
	
	$("#deliveryName").focus(function(){
		$("#deliveryNameError").addClass("hide");
	});
	
	$("#deliveryName").focus(function(){
		$("#deliveryNameError").addClass("hide");
	});
	
	$("#J_region_province,#J_region_city").focus(function(){
		$("#deliveryProvinceError").addClass("hide");
	});
	
	$("#deliveryAddress").focus(function(){
		$("#deliveryAddrError").addClass("hide");
	});
	
	$("#deliveryCode").focus(function(){
		$("#deliveryCodeError").addClass("hide");
	});
	
	$("#deliveryTel").focus(function(){
		$("#deliveryTelError").addClass("hide");
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
	
	$(".J_do_consignee").live("click",function(){
		var is_err = false;
		if($.trim($("#deliveryName").val())=="")
		{
			$("#deliveryNameError .error_notice i").html("请填写收件人姓名");
			$("#deliveryNameError").removeClass("hide");
			is_err = true;
		}
		else{
			$("#deliveryNameError").addClass("hide");
		}
		
		if(parseInt($("#J_region_province").val())==0 || parseInt($("#J_region_city").val())==0){
			$("#deliveryProvinceError .error_notice i").html("请选择城市");
			$("#deliveryProvinceError").removeClass("hide");
			is_err = true;
		}
		else{
			$("#deliveryProvinceError").addClass("hide");
		}
		
		if($.trim($("#deliveryAddress").val())=="")
		{
			$("#deliveryAddrError .error_notice i").html("请填写详细地址");
			$("#deliveryAddrError").removeClass("hide");
			is_err = true;
		}
		else{
			$("#deliveryAddrError").addClass("hide");
		}
		
		
		if($.trim($("#deliveryCode").val())=="")
		{
			$("#deliveryCodeError .error_notice i").html("请填写邮政编码");
			$("#deliveryCodeError").removeClass("hide");
			is_err = true;
		}
		else{
			$("#deliveryCodeError").addClass("hide");
		}
		
		if($.trim($("#deliveryTel").val())=="")
		{
			$("#deliveryTelError .error_notice i").html("请填写手机号码");
			$("#deliveryTelError").removeClass("hide");
			is_err = true;
		}
		else{
			if ($.checkMobilePhone($("#deliveryTel").val()) == false) {
				$("#deliveryTelError .error_notice i").html("手机号码格式错误");
				$("#deliveryTelError").removeClass("hide");
				is_err = true;
			}
			else {
				$("#deliveryTelError").addClass("hide");
			}
		}
		
		if(is_err==true){
			return false;
		}
		else{
			if (is_submit_lock) {
				alert("处理中请稍后");
				return false;
			}
			is_submit_lock = true;
			var query =  $("#J_consigneeBX form").serialize();
			$.ajax({
				url:DO_CONSIGNEE_URL,
				data:query,
				type:"post",
				dataType:"json",
				success:function(ajaxobj){
					is_submit_lock = false;
					if(ajaxobj.status==1){
						$.showSuccess(ajaxobj.info,function(){
							window.location.reload();
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
		}
	});
});


