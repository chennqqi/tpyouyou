$(document).ready(function(){ 
	$(".place_choose .place_tit").click(function(){
	
	var a = $(".place_choose .place_con")
	for (var i = 0; i <=11; i++) {
             if($(a[i]).css("display")=="block") $(a[i]).css('display','none'); 
	}
	
	$(this).next().show()	
	})
	
	$("#J_price input[name='min_price'],input[name='max_price']").focus(function(){
		$("#J_price").addClass("price_cur");
	});
	
	$("#J_price input[name='min_price'],input[name='max_price']").blur(function(){
		if($.trim($("input[name='min_price']").val()) =="" && $.trim($("input[name='max_price']").val()) =="")
			$("#J_price").removeClass("price_cur");
		if(parseInt($.trim($("input[name='min_price']").val())) < 0)
			$("input[name='min_price']").val("");
		if(parseInt($.trim($("input[name='max_price']").val())) < 0)
			$("input[name='max_price']").val("");
	});
	
	$("#J_price #ClearPrice").click(function(){
		$("input[name='min_price'],input[name='max_price']").val("");
		$("#J_price").removeClass("price_cur");
		$("#J_price form").submit();
	});
	
	$("#J_price_form").submit(function(){
		
		if(parseInt($.trim($("input[name='min_price']").val())) < 0){
			$("input[name='min_price']").val("");
		}
		if(parseInt($.trim($("input[name='max_price']").val())) < 0)
			$("input[name='max_price']").val("");
	});
	
	//包含天数
	$(".day_mone").click(function(){
		var parent_d=$(this).parent();
		 
		$(parent_d).addClass("multi_bgd");
		$(".day_rad").addClass("d_none");
		$(".day_mul").addClass("d_block");
		$(".day_mone").addClass("d_none");
	});
	
	$(".day_close").click(function(){
		$(".day_choose").removeClass("multi_bgd");
		$(".day_rad").removeClass("d_none");
		$(".day_mul").removeClass("d_block");
		$(".day_mone").removeClass("d_none");
	});
	
	$("#day_do").click(function(){
		dayBox = $(".day_input:checked");
		dayArray = new Array();
		$.each( dayBox, function(i, n){ 
			dayArray.push($(n).val());
		});
		day = dayArray.join("_");
		
		$("#multi_day_form").submit(function(){
			$("input[name='t_day']").val(day);
		});
	});
	
	//包含景点
	$(".jd_mone").click(function(){
		var parent_d=$(this).parent();
		 
		$(parent_d).addClass("multi_bgd");
		$(".jd_rad").addClass("d_none");
		$(".jd_mul").addClass("d_block");
		$(".jd_mone").addClass("d_none");
	});
	$(".jd_close").click(function(){
		$(".jd_choose").removeClass("multi_bgd");
		$(".jd_rad").removeClass("d_none");
		$(".jd_mul").removeClass("d_block");
		$(".jd_mone").removeClass("d_none");
	});
	$("#jd_do").click(function(){
		jdBox = $(".multi_input:checked");
		jdArray = new Array();
		$.each( jdBox, function(i, n){ 
			jdArray.push($(n).val());
		});
		jd = jdArray.join("_");
		
		$("#multi_jd_form").submit(function(){
			$("input[name='p_py']").val(jd);
		});
	});
	
});