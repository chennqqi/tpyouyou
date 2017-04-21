$(document).ready(function(){
    $(".money_ipt_box").find(".minus_v").bind("click",function(){
         var currentv = $(".money_v").val();
         if(isNaN(currentv))
         {
             currentv = 0;
         }
         else
         {             
             currentv = parseFloat(currentv);
             currentv -= 100;
             if(currentv<0) currentv = 0;
         }
         $(".money_v").val(Math.round(currentv*100)/100);
    });
    
    $(".money_ipt_box").find(".add_v").bind("click",function(){
         var currentv = $(".money_v").val();
         var max = parseFloat($(".money_v").attr("max"));
         if(isNaN(currentv))
         {
             currentv = 0;
         }
         else
         {
             currentv = parseFloat(currentv);
             currentv += 100;
             if(currentv>max)currentv=max;
         }
         $(".money_v").val(Math.round(currentv*100)/100);
    });
    $(".money_v").val($(".money_v").attr("max"));
    $(".money_v").bind("blur",function(){
    	var currentv = parseFloat($(".money_v").val());
    	if(isNaN(currentv))currentv = 0;
    	if(currentv<0) currentv = 0;
    	var max = parseFloat($(".money_v").attr("max"));
    	if(currentv>max)currentv=max;    	
    	$(".money_v").val(Math.round(currentv*100)/100);
    });
    
    $(".do_incharge").bind("click",function(){
         $("#deposit_form").submit();
    });
    $("#deposit_form").live("submit",function(){
        var v = $(".money_v").val();
         if(isNaN(v)||v<=0)
         {
             $.showErr("充值金额出错");
             return false;
         }
        var action = $(this).attr("action");
        var query = $(this).serialize();

        $.ajax({ 
            url: action,
            dataType: "json", 
            data:query,
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
    
    
    $(".view_deposit").live("click",function(){
    	 var id = $(this).attr("rel");
    	 var more = $("tr[rel='more_"+id+"']").find(".more_td");
    	 if(more.css("display")=="none")
		 {
    		 more.show();
		 }
    	 else
		 {			 
    		 more.hide();
		 }
    });
    
    $(".del_deposit").live("click",function(){
    	var action = $(this).attr("href");
    	$.ajax({ 
            url: action,
            dataType: "json", 
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
