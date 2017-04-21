$(document).ready(function(){
	$("#profile_form").bind("submit",function(){		
		var action = $(this).attr("action");
		var query = $(this).serialize();
		$.ajax({ 
            url: action,
            data:query,
            dataType:"json",
            type:"POST",
            global:false,
            success: function(obj){
               if(obj.status)
               {
                   $.showSuccess(obj.info);
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
	$("#save_profile").bind("click",function(){
		$("#profile_form").submit();
	});
	
	$(".modify").bind("click",function(){
		var title = $(this).attr("rel");
		var url = $(this).attr("href");
		$.weeboxs.open(url, {boxid:'modify_box',contentType:'ajax',showButton:false, showCancel:false, showOk:false,title:title,width:450,type:'wee',onclose:function(){location.reload();},onopen:function(){
			init_holder();
		}});

		return false;
	});
	
	$("#modify_form").live("submit",function(){
		var action = $(this).attr("action");
		var query = $(this).serialize();
		$.ajax({ 
            url: action,
            data:query,
            dataType:"json",
            type:"POST",
            global:false,
            success: function(obj){
               if(obj.status)
               {
                   $.showSuccess(obj.info,function(){
                	   $.weeboxs.close("modify_box");
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
	$("#save_modify").live("click",function(){
		$("#modify_form").submit();
	});
		
});