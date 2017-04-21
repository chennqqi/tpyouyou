$(document).ready(function(){
	$(".del_ask").bind("click",function(){
		var action = $(this).attr("href");
		$.ajax({ 
            url: action,
            dataType: "json", 
            type:"POST",
            global:false,
            success: function(obj){
            	if(obj.status)
            	{
            		$.showSuccess(obj.info,function(){
            			if(obj.jump!="")
            			{
            				location.href = obj.jump;
            			}
            			else
            			{
            				location.reload();
            			}
            		});
            	}
            	else
            	{
            		$.showErr(obj.info,function(){
            			if(obj.jump!="")
            			{
            				location.href = obj.jump;
            			}
            		});
            	}
               
            }
        }); 
		return false;
	});
	$(".open_ask").bind("click",function(){
		var url = $(this).attr("url");
		var rel = $(this).attr("rel");
		var cnt = $(".content[rel='content_"+rel+"']");
		var cnt_box = cnt.find(".cnt");
		if($.trim(cnt_box.html())=="")
		{
			var query = new Object();
			query.id = rel;
			$.ajax({ 
                url: url,
                dataType: "json", 
                data:query,
                type:"POST",
                global:false,
                success: function(obj){
                	if(obj.status)
                	{
                		user_tip();
                		cnt_box.html(obj.content);
                	}
                	else
                	{
                		$.showErr(obj.info,function(){
                			if(obj.jump!="")
                			{
                				location.href = obj.jump;
                			}
                		});
                	}
                   
                }
            }); 
		}
		if(cnt.css("display")=="none")
		{
			cnt.show();
		}
		else
		{
			cnt.hide();
			
		}
	});
        
        $(".do_ask").bind("click",function(){
		var url = $(this).attr("url");
		var rel = $(this).attr("rel");
		var cnt = $(".content[rel='content_"+rel+"']");
		var cnt_box = cnt.find(".cnt");
		if($.trim(cnt_box.html())=="")
		{
			var query = new Object();
			query.id = rel;
			$.ajax({ 
                url: url,
                dataType: "json", 
                data:query,
                type:"POST",
                global:false,
                success: function(obj){
                	if(obj.status)
                	{
                		user_tip();
                		cnt_box.html(obj.content);
                	}
                	else
                	{
                		$.showErr(obj.info,function(){
                			if(obj.jump!="")
                			{
                				location.href = obj.jump;
                			}
                		});
                	}
                   
                }
            }); 
		}
		if(cnt.css("display")=="none")
		{
			cnt.show();
		}
		else
		{
			cnt.hide();
			
		}
	});
        $(".do_ask").bind("click",function(){
            $("#incharge_form").submit();
        });
});