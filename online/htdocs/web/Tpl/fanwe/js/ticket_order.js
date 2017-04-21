var ticketorder_timer = null
jQuery(function(){
	$("#J_ORDER_BOX .remove_item").click(function(){
		var id = $(this).attr("data-id");
		$.ajax({
			url : AJAX_DEL_CART_ITEM_URL,
			data:"&id="+id,
			type:"post",
			dataType:"json",
			success:function(ajaxobj){
				if(ajaxobj.status==1)
				{
					$("#J_ORDER_BOX .item[data-id='"+id+"']").remove();
					$("#summaryInfo #item_"+id).remove();
					doneCart();
					if($("#J_ORDER_BOX .remove_item").length == 0){
						window.location.reload();
					}
				}
				else{
					$.showErr(ajaxobj.info);
				}
			},
			error:function(){
				$.showErr("数据请求错误");
			}
		});
	});
	
	$("#J_ORDER_BOX .item").hover(function(){
		$(this).find(".remove_item").show();
	},function(){
		$(this).find(".remove_item").hide();
	});
	
	$("#J_ORDER_BOX .change_num .min,#J_ORDER_BOX .change_num .plus").click(function(){
		var obj = $(this);
		var number = obj.parent().find(".trav_num").val();
		number= parseInt(number);
		var limit = obj.parent().find(".trav_num").attr("limit").split(",");
		obj.parent().find(".min,.plus").addClass("actived");
		
		if(obj.hasClass("min")){
			number = number - 1 >= parseInt(limit[0]) ? number - 1 : limit[0];
			if(number == parseInt(limit[0])){
				obj.removeClass("actived");
			}
		}
		
		if(obj.hasClass("plus")){
			number = number + 1 <= parseInt(limit[1]) ? number +1 : parseInt(limit[1]);
			if(number == parseInt(limit[1])){
				obj.removeClass("actived");
			}
		}
		
		obj.parent().find(".trav_num").val(number);
		var id = obj.parent().find(".trav_num").attr("dataid");
		
		doneCart();
	});
	
	$("#J_ORDER_BOX .storePickRadio").click(function(){
		doneCart();
	});
	
	$("#J_ORDER_BOX .change_num .trav_num").blur(function(){
		var obj = $(this);
		var number = obj.val();
		number= parseInt(number);
		var limit = obj.attr("limit").split(",");
		
		obj.parent().find(".min,.plus").addClass("actived");
		
		if(number < limit[0]){
			obj.val(limit[0]);
			number = limit[0];
		}
		
		if(number > limit[1]){
			obj.val(limit[1]);
			number = limit[1];
		}
		
		
		if(number == parseInt(limit[0])){
			obj.parent().find(".min").removeClass("actived");
		}
		
		if (number == parseInt(limit[1])) {
			obj.parent().find(".plus").removeClass("actived");
		}
		
		doneCart();
	});
	
	$("#J_ORDER_BOX .deliverInfoRadio").click(function(){
		var is_deliver = false;
		
		$("#J_ORDER_BOX .deliverInfoRadio:checked").each(function(){
			if(parseInt($(this).val())==1){
				is_deliver = true;
			}
		});
		
		if(is_deliver)
			$("#J_ORDER_BOX #deliver_part").show();
		else
			$("#J_ORDER_BOX #deliver_part").hide();
		
	});
	
	$("#ydxz .rule_name .t").click(function(){
		var id=$(this).attr("rid");
		var tmp = $("#desc_box_"+id).attr("class");
		$("#ydxz .desc_box").addClass("hide");
		$("#desc_box_"+id).attr("class",tmp);
		if($("#desc_box_"+id).hasClass("hide")){
			$("#desc_box_"+id).removeClass("hide");
		}
		else{
			$("#desc_box_"+id).addClass("hide");
		}
	});
	
	$("#deliveryHistory dd").click(function(){
		$("#deliveryHistory dd").removeClass("cur");
		$("#deliveryHistory dd input[name='user_consignee']").attr("checked",false);
		$("#J_add_delivery").attr("checked",false);
		$("#newAddressDetail").addClass("hide");
		$(this).addClass("cur");
		$(this).find("input[name='user_consignee']").attr("checked",true);
		doneCart();
	});
	
	$("#J_add_delivery").click(function(){
		$("#deliveryHistory dd").removeClass("cur");
		$("#deliveryHistory dd input[name='user_consignee']").attr("checked",false);
		$("#newAddressDetail").removeClass("hide");
		doneCart();
	});
	
	if ($("#summaryInfo").length > 0) {
		var default_tp = $("#summaryInfo").offset().top;
		$(window).scroll(function(){
			clearInterval(ticketorder_timer);
			var topScroll = getScroll();
			if (topScroll > default_tp) {
				$("#summaryInfo").addClass("fixed");
				if ($.browser.msie && $.browser.version == "6.0") {
					var topDiv = 0;
					var top = topScroll + parseInt(topDiv);
					ticketorder_timer = setInterval(function(){
						$("#summaryInfo").animate({
							"top": top + "px"
						}, 50);
					}, 0);
				}
			}
			else {
				$("#summaryInfo").removeClass("fixed");
			}
			
		});
	}
	
	
	
	$("#summaryInfo #subButton,#J_ORDER_BOX #subButton").click(function(){
		if(checkCart()){
			summitCart();
		}
		else{
			return false;
		}
	});
	
	$("#J_ORDER_BOX input.J_date").focus(function(){
		$(this).parent().parent().parent().find("#dateError").addClass("hide");
	});
	
	
	$("#appointName").focus(function(){
		$("#appointNameError").addClass("hide");
	});
	
	$("#appointMobile").focus(function(){
		$("#appointMobileError").addClass("hide");
	});
	
	$("#appointEmail").focus(function(){
		$("#appointEmailError").addClass("hide");
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
	
	$("#check_book_notice").click(function(){
		if($("#check_book_notice").attr("checked")==false || $("#check_book_notice").attr("checked")==undefined){
			$("#book_notice_tip").removeClass("hide");
		}
		else{
			$("#book_notice_tip").addClass("hide");
		}
	});
	
	$("#lvyouquan").click(function(){
		if($("#lvYouQuanTip").hasClass("hide")){
			$(".J_travCouponList").removeClass("hide");
			$("#lvYouQuanTip").removeClass("hide");
			$(this).find("span").html("▲");
		}
		else{
			$(".J_travCouponList").addClass("hide");
			$("#lvYouQuanTip").addClass("hide");
			$(this).find("span").html("▼");
		}
	});
	
	$("#travCouponAreaBox").click(function(){
		if($(this).attr("checked")=="checked"){
			$(".J_travCouponList input[name='voucher[]']").attr("checked","checked");
		}
		else{
			$(".J_travCouponList input[name='voucher[]']").attr("checked",false);
		}
		var voucher_total_price = 0;
		$(".J_travCouponList input[name='voucher[]']").each(function(){
			if($(this).attr("checked")=="checked"){
				voucher_total_price += parseFloat($(this).parent().parent().find("#haveTravelValue").attr("value"));
			}
		});
		$("#travCouponArea #haveTravelValue").attr("value",voucher_total_price);
		$("#travCouponArea #haveTravelValue").html("¥" + voucher_total_price);
		doneCart();
	});
	
	$(".J_travCouponList input[name='voucher[]']").click(function(){
		var voucher_total_price = 0;
		$(".J_travCouponList input[name='voucher[]']").each(function(){
			if($(this).attr("checked")=="checked"){
				voucher_total_price += parseFloat($(this).parent().parent().find("#haveTravelValue").attr("value"));
			}
		});
		$("#travCouponArea #haveTravelValue").attr("value",voucher_total_price);
		$("#travCouponArea #haveTravelValue").html("¥" + voucher_total_price);
		doneCart();
	});
	
	$("#account_part input[name='account_pay']").click(function(){
		doneCart();
	});
	
});

/**
 * 检查购物车
 */
function checkCart(){
	var is_err = false;
	$("#J_ORDER_BOX input.J_date").each(function(){
		if($.trim($(this).val()) ==""){
			$(this).parent().parent().parent().find("#dateError").removeClass("hide");
			is_err = true;
		}
		else{
			$(this).parent().parent().parent().find("#dateError").addClass("hide");
		}
	});
	
	if($.trim($("#appointName").val())=="")
	{
		$("#appointNameError .error_notice i").html("请填写预订人姓名");
		$("#appointNameError").removeClass("hide");
		is_err = true;
	}
	else{
		$("#appointNameError").addClass("hide");
	}
	
	if($.trim($("#appointMobile").val())=="")
	{
		$("#appointMobileError .error_notice i").html("请填写预订人手机");
		$("#appointMobileError").removeClass("hide");
		is_err = true;
	}
	else{
		if ($.checkMobilePhone($("#appointMobile").val()) == false) {
			$("#appointMobileError .error_notice i").html("手机号码格式错误");
			$("#appointMobileError").removeClass("hide");
			is_err = true;
		}
		else {
			$("#appointMobileError").addClass("hide");
		}
	}
	
	if($.trim($("#appointEmail").val())!="")
	{
		if ($.checkEmail($("#appointEmail").val())==false) {
			$("#appointEmailError .error_notice i").html("预订人邮箱错误");
			$("#appointEmailError").removeClass("hide");
			is_err = true;
		}
		else{
			$("#appointEmailError").addClass("hide");
		}
	}
	
	if($("#paperType").length > 0){
		if(parseInt($.trim($("#paperType").val()))==0){
			$("#paperTypeError").removeClass("hide");
			is_err = true;
		}
		else{
			$("#paperTypeError").addClass("hide");
		}
		
		if($.trim($("#paperSn").val())==""){
			$("#paperSnError").removeClass("hide");
			is_err = true;
		}
		else{
			$("#paperSnError").addClass("hide");
		}
		
	}
	
	var is_deliver =false;
	$("#J_ORDER_BOX .deliverInfoRadio:checked").each(function(){
		if(parseInt($(this).val())==1){
			is_deliver = true;
		}
	});
	
	if(is_deliver == true && $("#deliveryHistory dd.cur").length == 0){
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
		
	}
	
	if($.trim($("#order_memo_part textarea[name='order_memo']").val())!=""){
		if($.getStringLength($("#order_memo_part textarea[name='order_memo']").val()) > 100 || $.getStringLength($("#order_memo_part textarea[name='order_memo']").val()) < 20){
			$("#orderMemoError").removeClass("hide");
		}
		else{
			$("#orderMemoError").addClass("hide");
		}
	}
	
	
	if($("#check_book_notice").attr("checked")==false || $("#check_book_notice").attr("checked")==undefined){
		is_err = true;
		$("#book_notice_tip").removeClass("hide");
	}
	else{
		$("#book_notice_tip").addClass("hide");
	}
	
	if(is_err){
		return false;
	}
	else
		return true;
}

/**
 * 购车信息组装
 */
function doneCart(){
	var total_price = 0; //总价
	var pick_total_price = 0; //配送运费
	var supplier_deliver = Array();
	var voucher_total_price = 0;
	voucher_total_price += parseFloat($("#travCouponArea #haveTravelValue").attr("value"));
	
	
	$("#J_ORDER_BOX .item").each(function(){
		var id = $(this).attr("data-id");
		if($.trim($(this).find("input[name='end_time["+id+"]']").val())==""){
			$("#summaryInfo #item_"+id+" .pn .tip").addClass("org");
			$("#summaryInfo #item_"+id+" .pn .tip").html("请选择出游日期");
			$("#summaryInfo #item_"+id+" .pn .price").html("0元");
		}
		else{
			var number = $(this).find("input[name='sale_count["+id+"]']").val();
			var sale_price = json_data[id].sale_price;
			$("#summaryInfo #item_"+id+" .pn .tip").removeClass("org");
			$("#summaryInfo #item_"+id+" .pn .tip").html(number + " x " + sale_price + "元");
			var item_total_price = parseInt(number)*sale_price;
			total_price += item_total_price;
			$("#summaryInfo #item_"+id+" .pn .price").html(item_total_price+"元");
			
			/*如果有选择配送门票*/
			var picker = $(this).find("input[name='pickRadio["+id+"]']:checked").val();
			if(picker > 0){
				var province_id =0;
				var city_id = 0;
				if($("#deliveryHistory dd.cur").length == 0){
					province_id= $("#J_region_province").val();
					city_id= $("#J_region_city").val();
				}
				else{
					province_id =$("#deliveryHistory dd.cur").attr("province");
					city_id =$("#deliveryHistory dd.cur").attr("city");
				}
				/*一个商户只计算一次配送价格*/
				if(supplier_deliver[json_data[id].supplier_id] == undefined && deliver_json_data!=null){
					/*如果有配送到城市*/
					if (deliver_json_data[json_data[id].supplier_id] != undefined) {
						if (deliver_json_data[json_data[id].supplier_id]['city'] != undefined && deliver_json_data[json_data[id].supplier_id]['city'][province_id] != undefined && deliver_json_data[json_data[id].supplier_id]['city'][province_id][city_id] != undefined) {
							pick_total_price += parseFloat(deliver_json_data[json_data[id].supplier_id]['city'][province_id][city_id].price);
							supplier_deliver[json_data[id].supplier_id] = 1;
						}
						/*如果有配送到省份*/
						else 
							if (deliver_json_data[json_data[id].supplier_id]['province'] != undefined && deliver_json_data[json_data[id].supplier_id]['province'][province_id] != undefined) {
								pick_total_price += parseFloat(deliver_json_data[json_data[id].supplier_id]['province'][province_id].price);
								supplier_deliver[json_data[id].supplier_id] = 1;
							}
							/*如果有默认配送的价格*/
							else 
								if (deliver_json_data[json_data[id].supplier_id]['default'] != undefined) {
									pick_total_price += parseFloat(deliver_json_data[json_data[id].supplier_id]['default'].price);
									supplier_deliver[json_data[id].supplier_id] = 1;
								}
					}
				}
			}
		}
	});
	if (pick_total_price != 0) {
		$("#summaryInfo #totalPickNet").parent().removeClass("hide");
	}
	else{
		$("#summaryInfo #totalPickNet").parent().addClass("hide");
	}
		
	$("#summaryInfo #totalPickNet").html(pick_total_price + "元");
	
	if(voucher_total_price > 0){
		$("#summaryInfo #totalvoucherNet").parent().removeClass("hide");
	}
	else{
		$("#summaryInfo #totalvoucherNet").parent().addClass("hide");
	}
	
	$("#summaryInfo #totalvoucherNet").html("- " + voucher_total_price + "元");
	
	total_price += pick_total_price;
	
	if(total_price - voucher_total_price >= 0){
		
		total_price -=voucher_total_price;
	}
	else{
		total_price = 0;
	}
	
	account_money = parseFloat($("#account_part input[name='account_pay']:checked").val());
	if(total_price > 0 && account_money >0){
		$("#summaryInfo #totalaccountNet").parent().removeClass("hide");
		if(total_price - account_money > 0){
			total_price -= account_money;
			$("#summaryInfo #totalaccountNet").html("- " + account_money + "元");
		}
		else{
			$("#summaryInfo #totalaccountNet").html("- " + total_price + "元");
			total_price = 0; 
		}
	}
	else{
		$("#summaryInfo #totalaccountNet").parent().addClass("hide");
	}
	
	$("#summaryInfo #totalPayNet").html(total_price +"元");
	
}
/**
 * 提交购物车
 */
function summitCart(){
	$(".J_subButton").addClass("hide");
	$(".J_subButton").parent().find(".J_doing").removeClass("hide");
	var query =  $("#ticket_order_form").serialize();
	$.ajax({
		url:AJAX_SUBMIT_CART_URL,
		data:query,
		type:"post",
		dataType:"json",
		success:function(ajaxobj){
			$(".J_subButton").removeClass("hide");
			$(".J_subButton").parent().find(".J_doing").addClass("hide");
			if(ajaxobj.stauts==2){
				ajax_login();
				return false;
			}
			if(ajaxobj.status==1){
				window.location.href = ajaxobj.jump;
			}
			else{
				$.showErr(ajaxobj.info);
			}
		},
		error:function(){
			$(".J_subButton").removeClass("hide");
			$(".J_subButton").parent().find(".J_doing").addClass("hide");
			$.showErr("请求出错");
		}
	});
}


