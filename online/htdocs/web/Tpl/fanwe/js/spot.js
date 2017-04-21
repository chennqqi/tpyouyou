var current_ajax = null;
jQuery(function(){
	$("#J_idx_spot_cate li").hover(function(){
		$(this).addClass("cur");
	},function(){
		$(this).removeClass("cur");
	});
	
	$("#J_spots_box .province_city span.pro").click(function(){
		$("#J_spots_box .province_city .pro ").removeClass("current");
		$("#J_spots_box .spots .citys span").removeClass("current");
		$("#J_spots_box .select_provice .md_b").html($(this).html());
		$("#J_spots_box .select_provice").attr("py",$(this).attr("py"));
		$(this).addClass("current")
		get_py_spots();
	});
	
	$("#J_spots_box .spots .citys span").live("click",function(){
		$("#J_spots_box .spots .citys span").removeClass("current");
		$(this).addClass("current");
		get_py_spots();
	});
	
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
	
	$("#J_spotv_tickets .tit a").click(function(){
		var j = $(this).parent().parent().find(".j");
		var des = $(this).parent().parent().parent().find(".des");
		if(des.css("display")=="block"){
			j.html("▼");
			des.hide();
		}
		else{
			j.html("▲");
			des.show();
		}
	});
	
});

function get_py_spots(){
	if(current_ajax)
		current_ajax.abort();
	var tag = "";
	if($("#J_spots_box .province_city span.current").parent().find("span.tit").attr("rel")!=undefined)
		tag = $("#J_spots_box .province_city span.current").parent().find("span.tit").attr("rel");
	var ppy=$("#J_spots_box .select_provice").attr("py");
	var cpy=""
	if($("#J_spots_box .spots .citys span.current").length > 0)
		cpy = $("#J_spots_box .spots .citys span.current").attr("py");
	current_ajax = $.ajax({
		url : province_tag_spots_url,
		data:"&tag="+tag+"&ppy="+ppy+"&cpy="+cpy,
		type:"post",
		dataType:"text",
		success:function(ajaxobj){
			$("#J_spots_box .spots").html(ajaxobj);
		}
	});
}
