


$(function(){
    $(".more_choose").click(function(){
	
		if($(this).prev().hasClass("city_list_extend")){
	       $(this).prev().removeClass("city_list_extend");
		   $(this).prev().addClass("city_list");
		   $(this).text("更多");
		}else{
		   $(this).prev().removeClass("city_list");
		   $(this).prev().addClass("city_list_extend");
		   $(this).text("收起");
		}	
	
	}
  );    

});