$($(navTab.getCurrentPanel())).ready(function(){
	load_define();
	var panel = $($(navTab.getCurrentPanel()));
	 $(panel).find("select[name='send_type']").bind("change",function(){
		 load_define(); 
	 });
});

function load_define()
{
	var panel = $($(navTab.getCurrentPanel()));
	var send_type = $(panel).find("select[name='send_type']").val();
	$(panel).find("#user_group").hide();
	$(panel).find("#user_level").hide();
	$(panel).find("#user_define").hide();
	if(send_type==0)
		$(panel).find("#user_group").show();
	else if(send_type==1)
		$(panel).find("#user_level").show();
	else if(send_type==2)
		$(panel).find("#user_define").show();
	
}