
function deal_sender_fun()
{    
    if(IS_RUN_CRON==1)
    {
        window.clearInterval(deal_sender);
        $.ajax({
            url: deal_msg_list_url,
            global:false,
            dataType: "jsonp",
            jsonp: 'callback',  
            type:"GET",
            success:function(obj)
            {
            	if(obj.count!='0')
                {                       
                    $("#send_deal_msg_tip").show();          
                }
                else
                {
                    $("#send_deal_msg_tip").hide();
                }
                deal_sender = window.setInterval("deal_sender_fun()",send_span);
            }
        });
    }
    else
    {
    	$("#send_deal_msg_tip").hide();
    }
}

function promote_sender_fun()
{    
    if(IS_RUN_CRON==1)
    {
        window.clearInterval(promote_sender);
        $.ajax({
            url: promote_msg_list_url,
            dataType: "jsonp",
            jsonp: 'callback',  
            global:false,
            type:"GET",
            success:function(obj)
            {
            	if(obj.count!='0')
                {                       
                    $("#send_promote_msg_tip").show();          
                }
                else
                {
                    $("#send_promote_msg_tip").hide();
                }
                promote_sender = window.setInterval("promote_sender_fun()",send_span);
            }
        });
    }
    else
    {
    	$("#promote_deal_msg_tip").hide();
    }
}

var deal_sender = window.setInterval("deal_sender_fun()",send_span);    
var promote_sender = window.setInterval("promote_sender_fun()",send_span);    