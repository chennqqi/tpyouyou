$(document).ready(function(){
	$(navTab.getCurrentPanel()).find("select[name='type']").bind("change",function(){
		$(navTab.getCurrentPanel()).find(".btnLook[lookupgroupselect='tuan']").show();
		var ajax_url = $(this).find("option:selected").attr("rel");
		$(navTab.getCurrentPanel()).find("input[name='tuan.rel_id']").val("");
		$(navTab.getCurrentPanel()).find("input[name='tuan.name']").val("");
		switch($(this).val()){
			case "1":
			case "2":
			case "3":
				$(navTab.getCurrentPanel()).find(".btnLook[lookupgroupselect='tuan']").attr("href",ajax_url);
				break;
			default:
				$(navTab.getCurrentPanel()).find(".btnLook[lookupgroupselect='tuan']").hide();
				break;
		}
	});
});