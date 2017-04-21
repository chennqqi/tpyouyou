$(document).ready(function(){ 

	$(document.body).click(function(e) {
		if($(e.target).attr("class")!='fc-event-title')
    	{
    		$(".mid-buy_box").hide();
    	}
    });
	
	$(".place_choose .place_tit").click(function(){
	
		var a = $(".place_choose .place_con")
		for (var i = 0; i <=11; i++) {
	             if($(a[i]).css("display")=="block") $(a[i]).css('display','none'); 
		}
		
		$(this).next().show();	
	})
	
	$(".child_norm").hover(
		 function () {
		    $(this).find(".norm_a").addClass("hover_a");
			$(this).find(".norm_value").show();
		  },
		  function () {
			$(this).find(".norm_a").removeClass("hover_a");
			$(this).find(".norm_value").hide();
		  }

	);
	
	$(".to_pay_button").click(function(){
		var parent_box=$(this).parent().parent();
		tourline_id=$(parent_box).find("input[name='tourline_id']").val();
		if($(parent_box).attr("name") =="form_l")
			tourline_item_id=$(parent_box).find("select[name='tourline_item_id']").val();
		else
			tourline_item_id=$(parent_box).find("input[name='tourline_item_id']").val();
		adult_count=$(parent_box).find("select[name='adult_count']").val();
		child_count=$(parent_box).find("select[name='child_count']").val();

		if( tourline_item_id <= 0)
		{
			$.showErr("请选择成人日期!");
			return false;
		}
		
		if( adult_count <= 0 && child_count<=0)
		{
			$.showErr("请选择人数!");
			return false;
		}
		
		var query = new Object();
		query.tourline_id=tourline_id;
		query.tourline_item_id=tourline_item_id;
		query.adult_count=adult_count;
		query.child_count=child_count;
		query.ajax=1;
		$.ajax({
			url:tourline_order_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(ajaxobj){
				if(ajaxobj.status==2){
					ajax_login();
					return false;
				}
				else if(ajaxobj.status==0){
					$.showErr(ajaxobj.info);
					return false;
				}
				else if(ajaxobj.status==1){
					$(parent_box).submit();
				}
			}
		});
		
	});
});