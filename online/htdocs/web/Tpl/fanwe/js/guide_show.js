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
			if(topScroll>=$("#J_nbox_0").offset().top-60){
				$("#J_navbar li").removeClass("current");
				$("#J_navbar li:eq(0)").addClass("current");
			}
			if(topScroll>=$("#J_nbox_1").offset().top-60){
				 $("#J_navbar li").removeClass("current");
				 $("#J_navbar li:eq(1)").addClass("current");				 
			}
		
			
		}else{
			$("#J_navbar").removeClass("fixed");
		}
		
      });  
}) ;