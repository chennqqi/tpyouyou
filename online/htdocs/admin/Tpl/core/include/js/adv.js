$($(navTab.getCurrentPanel())).ready(function(){

	$(navTab.getCurrentPanel()).find("select[name='type']").bind("change",function(){
	    init_type_row();
	});
	init_type_row();
});

function init_type_row()
{
    var type = $(navTab.getCurrentPanel()).find("select[name='type']").val();
    $(navTab.getCurrentPanel()).find(".type_row").hide();
    $(navTab.getCurrentPanel()).find("*[rel='type_"+type+"']").show();
}
