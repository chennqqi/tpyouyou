$(document).ready(function(){
    $(".getvoucher").bind("click",function(){
    	var action = $(this).attr("href");
    	$.ajax({ 
            url: action,
            dataType: "json", 
            type:"POST",
            global:false,
            success: function(obj){                
                if(obj.status==1)
                $.showSuccess(obj.info,function(){                      
                    location.reload();
                });
                else        
                {                           
                    $.showErr(obj.info,function(){                        
                         if(obj.jump!="")
                        location.href = obj.jump;
                    });
                }
            }
        }); 
    	return false;
    });
});
