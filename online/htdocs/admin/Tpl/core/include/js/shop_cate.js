$(document).ready(function(){
		$(navTab.getCurrentPanel()).find("select[name='is_index']").change(function(){
			var is_index=Number($("select[name='is_index']").val());
	
			if(is_index ==1)
			{
				$(".area1").show();
				$(".area2").show();
			}else
			{
				$(".area1").hide();
				$(".area2").hide();
			}
			
		});
});