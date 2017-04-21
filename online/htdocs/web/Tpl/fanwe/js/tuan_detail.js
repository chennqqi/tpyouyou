var left_time_act = null;

function left_time_clock(){
	clearTimeout(left_time_act);
	var left_time = endtime - systime;
	
	if (left_time > 0) {
		var day = parseInt(left_time / (3600 * 24));
		var hour = parseInt(left_time % (3600 * 24) / 3600);
		var min = parseInt((left_time % 3600) / 60);
		var send = parseInt(left_time % 60);
		
		$(".remain_time .dd").html(day);
		$(".remain_time .hh").html(hour);
		$(".remain_time .mm").html(min);
		$(".remain_time .ss").html(send);
		systime ++;
		left_time_act = setTimeout(left_time_clock,1000);
	}
	else{
		//$(".remain_time .bx").html("团购已结束");
	}
}

function AddFavorite(sURL, sTitle){
   try{
      window.external.addFavorite(sURL, sTitle);
   }catch (e){ 
      $.showErr("请按  ctrl+D 加入收藏");
   } 
}

$(document).ready(function(){ 
	$(".other_product dl").mouseover(function(){
       var a = $(".other_product dl dt a")
	   for (var i = 0; i <=11; i++) {	   	
            if($(a[i]).css("display")=="block") $(a[i]).css('display','none'); 
	   		$(this).children("dt").children("a").css('display','block');	   
	   }	
	})	
	
	$("a,input,button").focus(function(){this.blur()});
	
	$(document.body).click(function(e) {		
		if($(e.target).attr("class")!='fc-event-title' && e.target.nodeName!='SELECT'&& e.target.nodeName!='A')
    	{
    		$(".mid-buy_box").hide();
    	}
    });

	
	$(".child_norm,.children_norm").hover(
		 function () {		 			 	
		    $(this).find(".norm_a").addClass("hover_a");
			$(this).find(".norm_value").show();
		  },
		  function () {
			$(this).find(".norm_a").removeClass("hover_a");
			$(this).find(".norm_value").hide();
		  }
	);
	

	
	$(".to_pay_button,.pay_button").click(function(){
		var parent_box=$(this).parent().parent().parent();
		tourline_id=$(parent_box).find("input[name='tourline_id']").val();
		tourline_item_id=$(parent_box).find("[name='tourline_item_id']").val();
		adult_count=$(parent_box).find("select[name='adult_count']").val();
		child_count=$(parent_box).find("select[name='child_count']").val();
		if( tourline_item_id <= 0)
		{
		    $.showErr("请选择出发日期!");
			return false;
		}
		if( adult_count <= 0)
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
			url:sub_order_url,
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
		return false;			

	});
	
	
	$(".ticket_sub").click(function(){		
		var query = new Object();
		query.id=ticket_id;				
		$.ajax({
			url:sub_order_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(ajaxobj){
				if(ajaxobj.status==2){
                    window.location.href = ajaxobj.jump;					
				}else if(ajaxobj.status==0){
					$.showErr(ajaxobj.info);
				}
				else if(ajaxobj.status==1){					
					window.location.href = ajaxobj.jump;
				}
			}
		});
		return false;
		
	})
	
	
	
			
});



var timer;  
$(function(){
	  var default_tp = $("#J_navbar").parent().offset().top;
  
	  $("#J_navbar a").bind("click",function(){
	  	var rel = $(this).attr("rel");
		var box = $("#J_nbox_"+rel);
		var top = $(box).offset().top-60;
		$("html,body").animate({scrollTop:top},"fast","swing");
	  });
	
      $(window).scroll(function(){  
        clearInterval(timer);  
        var topScroll=getScroll(); 
		if(topScroll > default_tp){
			$("#J_navbar").addClass("fixed");
            if ($.browser.msie && $.browser.version == "6.0"){
               var topDiv=0;  
               var top=topScroll+parseInt(topDiv);
               timer=setInterval(function(){  
                    $("#J_navbar").animate({"top":top+"px"},50);  
               },0);
            }
			if ($("#J_nbox_0").length > 0) {
				if (topScroll >= $("#J_nbox_0").offset().top - 60) {
					$("#J_navbar li").removeClass("cur");
					$("#J_navbar li a[rel='0']").parent().addClass("cur");
				}
			}
			if ($("#J_nbox_1").length > 0) {
				if (topScroll >= $("#J_nbox_1").offset().top - 60) {
					$("#J_navbar li").removeClass("cur");
					$("#J_navbar li a[rel='1']").parent().addClass("cur");
				}
			}
			if ($("#J_nbox_2").length > 0) {
				if (topScroll >= $("#J_nbox_2").offset().top - 60) {
					$("#J_navbar li").removeClass("cur");
					$("#J_navbar li a[rel='2']").parent().addClass("cur");
				}
			}
			if ($("#J_nbox_3").length > 0) {
				if (topScroll >= $("#J_nbox_3").offset().top - 60) {
					$("#J_navbar li").removeClass("cur");
					$("#J_navbar li a[rel='3']").parent().addClass("cur");
				}
			}
			if ($("#J_nbox_4").length > 0) {
				if (topScroll >= $("#J_nbox_4").offset().top - 60) {
					$("#J_navbar li").removeClass("cur");
					$("#J_navbar li a[rel='4']").parent().addClass("cur");
				}
			}
			if ($("#J_nbox_5").length > 0) {
				if (topScroll >= $("#J_nbox_5").offset().top - 60) {
					$("#J_navbar li").removeClass("cur");
					$("#J_navbar li a[rel='5']").parent().addClass("cur");
				}
			}
			if ($("#J_nbox_6").length > 0) {
				if (topScroll >= $("#J_nbox_6").offset().top - 60) {
					$("#J_navbar li").removeClass("cur");
					$("#J_navbar li a[rel='6']").parent().addClass("cur");
				}
			}
			if ($("#J_nbox_7").length > 0) {
				if (topScroll >= $("#J_nbox_7").offset().top - 60) {
					$("#J_navbar li").removeClass("cur");
					$("#J_navbar li a[rel='7']").parent().addClass("cur");
				}
			}
			if ($("#J_nbox_8").length > 0) {
				if (topScroll >= $("#J_nbox_8").offset().top - 60) {
					$("#J_navbar li").removeClass("cur");
					$("#J_navbar li a[rel='8']").parent().addClass("cur");
				}
			}			
			
		}else{
			$("#J_navbar").removeClass("fixed");
		}
		
      });  
}) ;























