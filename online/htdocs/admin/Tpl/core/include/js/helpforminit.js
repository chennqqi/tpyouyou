$($(navTab.getCurrentPanel())).ready(function(){
        init_is_url();
        $(navTab.getCurrentPanel()).find("select[name='is_url']").bind("change",function(){ init_is_url();});      
    });
  
function init_is_url()
{
    var is_url = $(navTab.getCurrentPanel()).find("select[name='is_url']").val();
    if(is_url==0)
    {
    	$(navTab.getCurrentPanel()).find("input[name='url']").removeClass("url");
        $(navTab.getCurrentPanel()).find("#url_row").hide();
        $(navTab.getCurrentPanel()).find("#content_row").show();        
    }
    else
     {
    	$(navTab.getCurrentPanel()).find("input[name='url']").addClass("url");
        $(navTab.getCurrentPanel()).find("#url_row").show();
        $(navTab.getCurrentPanel()).find("#content_row").hide();        
    }
    
}
