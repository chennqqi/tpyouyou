$($(navTab.getCurrentPanel())).ready(function(){
    $(navTab.getCurrentPanel()).find("select[name='deliver_type']").bind("change",function(){
        initDeliverType();
    });
    initDeliverType();
});

function initDeliverType()
{
    var deliver_type = parseInt($(navTab.getCurrentPanel()).find("select[name='deliver_type']").val());
    if(deliver_type==3)
    {
        $(navTab.getCurrentPanel()).find("#rel_id").hide();
        $(navTab.getCurrentPanel()).find("#deliver_end_time").hide();
        $(navTab.getCurrentPanel()).find("#deliver_limit").hide();
    }
    else
    {
         $(navTab.getCurrentPanel()).find("#rel_id").show();
         $(navTab.getCurrentPanel()).find("#deliver_end_time").show();
         $(navTab.getCurrentPanel()).find("#deliver_limit").show();
         if(deliver_type==2)
         {
             $(navTab.getCurrentPanel()).find("#rel_id_title").html(LANG['USER_GROUP']);
             $.ajax({ 
                url: LOAD_USER_GROUP_URL,
                dataType: "json",
                global:false,
                success: function(obj){
                    var html = "<select name='deliver_rel_id' class='combox'><option value='0'>全部</option>";
                    for(i=0;i<obj.length;i++)
                    {
                        if(obj[i].select)
                        html+="<option value='"+obj[i].id+"' selected='selected'>"+obj[i].name+"</option>";
                        else
                        html+="<option value='"+obj[i].id+"'>"+obj[i].name+"</option>";
                    }
                    html+="</select>";
                    $(navTab.getCurrentPanel()).find("#rel_id_box").html(html);
                   $("select.combox",$(document)).combox();
                }
            }); 
         }
         else if(deliver_type==1)
         {
             $(navTab.getCurrentPanel()).find("#rel_id_title").html(LANG['USER_LEVEL']);
              $.ajax({ 
                url: LOAD_USER_LEVEL_URL,
                dataType: "json",
                global:false,
                success: function(obj){
                    var html = "<select name='deliver_rel_id' class='combox'><option value='0'>全部</option>";
                    for(i=0;i<obj.length;i++)
                    {
                        if(obj[i].select)
                        html+="<option value='"+obj[i].id+"' selected='selected'>"+obj[i].name+"</option>";
                        else
                        html+="<option value='"+obj[i].id+"'>"+obj[i].name+"</option>";
                    }
                    html+="</select>";
                    $(navTab.getCurrentPanel()).find("#rel_id_box").html(html);
                   $("select.combox",$(document)).combox();
                }
            }); 
         }
    }
}
