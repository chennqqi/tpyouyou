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
         if(isNaN(currentv))
         {
             currentv = 0;
         }
         else
         {
             currentv = parseFloat(currentv);
             currentv += 100;
         }
         $(".money_v").val(Math.round(currentv*100)/100);
    });
    
    $(".do_incharge").bind("click",function(){
         $("#incharge_form").submit();
    });
    $("#incharge_form").live("submit",function(){
        var v = $(".money_v").val();
         if(isNaN(v)||v<=0)
         {
             $.showErr("充值金额出错");
             return false;
         }
        var action = $(this).attr("action");
        var query = $(this).serialize();
        if(action.indexOf("?")>=0)
        {
            action+="&"+query;
        }
        else
        {
            action+="?"+query;
        }
        location.href = action;
        return false;
    });
});
