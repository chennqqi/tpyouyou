var timer;
var c_idx = 1;
var total = 0;
var is_has_show = false;
$(document).ready(function(){
	if (!is_has_show) {
		$("#spot_index_adv .adv li").each(function(){
			if ($.trim($(this).html()) != "") {
				total +=1;
			}
			else {
				var rel = $(this).attr("rel");
				$("#spot_index_adv .ctl li[rel='" + rel + "']").remove();
				$(this).remove();
			}
		});
		is_has_show= true
	}
	
	if (total >= 1) {
		init_main_adv();
	}
	else if (total == 0) {
		$("#spot_index_adv").remove();
	}
});

function init_main_adv()
{
	$("#spot_index_adv .adv li[rel='1']").show();
	$("#spot_index_adv .ctl li[rel='1']").addClass("cur");
	
	timer = window.setInterval("auto_play()", 5000);
	$("#spot_index_adv .ctl li").hover(function(){
		show_current_adv($(this).attr("rel"));		
	});
	
	$("#spot_index_adv").hover(function(){
		clearInterval(timer);
	},function(){
		timer = window.setInterval("auto_play()", 5000);
	});
}

function auto_play()
{	
	if(c_idx == total)
	{
		c_idx = 1;
	}
	else
	{
		c_idx++;
	}
	show_current_adv(c_idx);
}

function show_current_adv(idx)
{	
	$("#spot_index_adv .adv li[rel!='"+idx+"'] ").hide();
	$("#spot_index_adv .ctl li").removeClass("cur");
	if($("#spot_index_adv .adv li[rel='"+idx+"']").css("display")=='none')
		$("#spot_index_adv .adv li[rel='"+idx+"']").fadeIn();
	$("#spot_index_adv .ctl li[rel='"+idx+"']").addClass("cur");
	c_idx = idx;
	
	
}
