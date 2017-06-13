var ticketorder_timer = null
jQuery(function(){
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
	
	$("#check_book_notice").click(function(){
		if($("#check_book_notice").attr("checked")==false || $("#check_book_notice").attr("checked")==undefined){
			$("#book_notice_tip").removeClass("hide");
		}
		else{
			$("#book_notice_tip").addClass("hide");
		}
	});
	
	$(".rule_name .t").click(function(){
		var id=$(this).attr("rid");
		if($(".desc_box").hasClass("hide")){
			$(".desc_box").removeClass("hide");
		}
		else{
			$(".desc_box").addClass("hide");
		}
	});
	
	/* 优惠券*/
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
		doneCart();
	});
	/* 优惠券*/
	
	/* 现金券*/
	$("#account_part input[name='account_pay']").click(function(){
		doneCart();
	});
	
	/*保险 */
	$("#insurance_part .J_travInsuranceList input[name='insurance_ids[]']").click(function(){
		var insurance_total_p = 0;
		var insurance_html="<hr class='hr_dotted' />";
		insurance_html+="<div class='part'>";
		insurance_html+="<p class='part_name'>保险费用</p>";
		$(".J_travInsuranceList input[name='insurance_ids[]']").each(function(){
			if($(this).attr("checked")=="checked"){
				insurance_id=$(this).attr("value");
				insurance_info=json_insurance[insurance_id];
				one_insurance_total_p=buy_count*insurance_info['price'];
				insurance_total_p += parseFloat(one_insurance_total_p);
				
				
				insurance_html+="<p>"+insurance_info['name']+"</p>";
				insurance_html+="<p class='clearfix pn'>";
				insurance_html+="<span class='pn_1 f_l'>"+buy_count+"3份 × ¥"+insurance_info['price']+"</span>";
				insurance_html+="<span class='pn_2 f_r'>¥"+one_insurance_total_p+"</span>";
				insurance_html+="</p>";
			}
		});
		insurance_html+="</div>";
		$("#insurance_part #insurance_total").attr("value",insurance_total_p);
		
		$("#summaryInfo #insurance_summary").show();
		$("#summaryInfo #insurance_summary").html(insurance_html);
		if(insurance_total_p <=0)
			$("#summaryInfo #insurance_summary").hide();
			
		doneCart();
	});
	
	/* 签证*/
	$("#visa_part select[name='number_visa']").change(function(){
		var visa_html="";
		var number_visa=$(this).val();
		var visa_total_price_input=$(this).parent().find("input[name='visa_total_price']");
		var visa_price=parseFloat($(visa_total_price_input).attr("price"));
		visa_total_p=visa_price * number_visa;
		$(visa_total_price_input).attr("value",visa_total_p);
		
		visa_html +="<span class='pn_1 f_l'>"+number_visa+"份 × ¥"+visa_price+"</span>";
		visa_html +="<span class='pn_2 f_r'>¥"+visa_total_p+"</span>";
		
		$("#summaryInfo #visa_summary").show();
		$("#summaryInfo #visa_summary .j_visa").html(visa_html);
		if(visa_total_p <=0)
			$("#summaryInfo #visa_summary").hide();
			
		doneCart();
	});
	
	/*提交订单*/
	$("#summaryInfo .J_subButton,#J_ORDER_BOX .J_subButton").click(function(){
		if(checkCart()){
			summitCart();
		}
		else{
			return false;
		}
	});
	
	//修改游客信息
	$("select[name='s_namelist']").change(function(){
		name_id=parseInt($(this).val());
		namelist_item=json_namelist_idlist[name_id];
		parent_table=$(this).parent().parent().parent().parent();
		$(parent_table).find("input[name='name[]']").val(namelist_item['name']);
		$(parent_table).find("select[name='paper_type[]']").val(namelist_item['paper_type']);
		$(parent_table).find("input[name='paper_sn[]']").val(namelist_item['paper_sn']);
		$(parent_table).find("input[name='mobile[]']").val(namelist_item['mobile']);
	});
	
});

function mive_notice(o){
	var parent_d=$(o).parent();
	$(parent_d).find(".err_structure").addClass("hide");
}

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
		$("#appointNameError .error_notice i").html("请填写联系人姓名");
		$("#appointNameError").removeClass("hide");
		is_err = true;
	}
	else{
		$("#appointNameError").addClass("hide");
	}
	
	if($.trim($("#appointMobile").val())=="")
	{
		$("#appointMobileError .error_notice i").html("请填写手机号");
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

	if(youke_number >0 && is_namelist ==1)
	{
		for(i=1;i<=youke_number;i++)
		{
			if($.trim($("#name_"+i).val())=="")
			{
				$("#name"+i+"Error .error_notice i").html("请填写游客姓名");
				$("#name"+i+"Error").removeClass("hide");
				is_err = true;
			}
			else{
				$("#name"+i+"Error").addClass("hide");
			}
			
			if($.trim($("#paper_sn_"+i).val())=="")
			{
				$("#paperSn"+i+"Error .error_notice i").html("请填写证件号码");
				$("#paperSn"+i+"Error").removeClass("hide");
				is_err = true;
			}
			else{
				$("#paperSn"+i+"Error").addClass("hide");
			}
			
			if($.trim($("#mobile_"+i).val())=="")
			{
				$("#mobile"+i+"Error .error_notice i").html("请填写手机号");
				$("#mobile"+i+"Error").removeClass("hide");
				is_err = true;
			}
			else{
				if ($.checkMobilePhone($("#mobile_"+i).val()) == false) {
					$("#mobile"+i+"Error .error_notice i").html("手机号码格式错误");
					$("#mobile"+i+"Error").removeClass("hide");
					is_err = true;
				}
				else {
					$("#mobile"+i+"Error").addClass("hide");
				}
			}
		}
		
	}
	
	
	if($.trim($("#order_memo_part textarea[name='order_memo']").val())!=""){
		if($.getStringLength($("#order_memo_part textarea[name='order_memo']").val()) > 100 || $.getStringLength($("#order_memo_part textarea[name='order_memo']").val()) < 20){
			$("#orderMemoError").removeClass("hide");
			is_err = true;
		}
		else{
			$("#orderMemoError").addClass("hide");
		}
	}

	if($("#check_book_notice").attr("checked")==false || $("#check_book_notice").attr("checked")==undefined){
		$("#book_notice_tip").removeClass("hide");
		is_err = true;
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


function doneCart(){
	var total_price = 0; //网付总价
	var tourline_total_price = 0; //线路总价
	var visa_total_price = 0; //
	var insurance_total_price = 0; //
	var voucher_total_price = 0;

	tourline_total_price +=parseFloat(json_data['adult_price_total'] + json_data['child_price_total']);
	
	insurance_total_price=parseFloat($("#insurance_part #insurance_total").attr("value"));/*保险费用*/
	if(insurance_total_price >0)
		tourline_total_price += insurance_total_price;
	
	visa_total_price=parseFloat($("#visa_part input[name='visa_total_price']").attr("value"));/*签证费用*/
	if(visa_total_price>0)
		tourline_total_price += visa_total_price;
	
	tourline_total_price=Math.round(tourline_total_price*100)/100;
	$("#summaryInfo #totalTourlinePay").html(tourline_total_price +"元");
	
	if(json_data['yufu_hide'] ==1)/*全额付款*/
	{
		total_price=tourline_total_price;
	}
	else
	{
		total_price +=json_data['adult_sale_price_total'] + json_data['child_sale_price_total'];
	}
	
	voucher_total_price += parseFloat($("#travCouponArea #haveTravelValue").attr("value"));
	if(voucher_total_price > 0){
		$("#summaryInfo #totalvoucherNet").parent().removeClass("hide");
		total_price -=voucher_total_price;
	}
	else{
		$("#summaryInfo #totalvoucherNet").parent().addClass("hide");
	}
	$("#summaryInfo #totalvoucherNet").html("- " + voucher_total_price + "元");
	
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
	total_price=Math.round(total_price*100)/100;
	if(total_price <0)
		total_price=0;
		
	$("#summaryInfo #totalPayNet").html(total_price +"元");
}

/**
 * 提交购物车
 */
function summitCart(){
	$(".J_subButton").addClass("hide");
	$(".J_subButton").parent().find(".J_doing").removeClass("hide");
	var query =  $("#tourline_order_form").serialize();
	$.ajax({
		url:AJAX_SUBMIT_CART_URL,
		data:query,
		type:"post",
		dataType:"json",
		success:function(ajaxobj){
			$(".J_subButton").removeClass("hide");
			$(".J_subButton").parent().find(".J_doing").addClass("hide");
			if(ajaxobj.status==2){
				ajax_login();
				return false;
			}
			else if(ajaxobj.status==1){
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