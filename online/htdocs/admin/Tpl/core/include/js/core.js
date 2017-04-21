function delfile(obj)
{
    $(obj).parent().parent().find(".viewbox").hide();
    $(obj).parent().parent().find(".delbox").hide();
    $(obj).parent().parent().find(".view").attr("href","#");
    $(obj).parent().parent().find(".filebox").val("");
}

//以下关于多选可保存数据的回调用的js
function check_all(obj,pk,fields)
{
    if($(obj).attr("checked"))
    {
        $.each($($.pdialog.getCurrent()).find("input[name='"+pk+"']"), function(i, n){
            $(n).attr("checked",true);
        });
    }
    else
    {
        $.each($($.pdialog.getCurrent()).find("input[name='"+pk+"']"), function(i, n){
            $(n).attr("checked",false);
        });
    }
    
    reload_selected_data(pk,fields);
}

function reload_selected_data(pk,fields)
{
    $.each($($.pdialog.getCurrent()).find("input[name='"+pk+"']"), function(i, n){
        var current_data = DWZ.jsonEval($(n).val());
        if($(n).attr("checked"))
        {
            for(var i=0;i<fields.length;i++)
            {
                var field = fields[i];
                var tmp_select_data_field = selected_data[field].split(",");          
                
                if($.inArray(current_data[field],tmp_select_data_field)<0)
                {
                    tmp_select_data_field.push(current_data[field]);
                    var new_select_data_field = new Array();
                    for(k=0;k<tmp_select_data_field.length;k++)
                    {
                        if($.trim(tmp_select_data_field[k])!="")
                        {
                            new_select_data_field.push(tmp_select_data_field[k]);
                        }
                    }
                    selected_data[field] = new_select_data_field.join(",");
                }
                
            }
            
        }
        else
        {
            for(var i=0;i<fields.length;i++)
            {
                var field = fields[i];
                var tmp_select_data_field = selected_data[field].split(",");  
                
                 if($.inArray(current_data[field],tmp_select_data_field)>=0)
                {
                    //已存保
                    var new_select_data_field = new Array();
                    for(k=0;k<tmp_select_data_field.length;k++)
                    {
                        if(tmp_select_data_field[k]!=current_data[field]&&$.trim(tmp_select_data_field[k])!="")
                        {
                            new_select_data_field.push(tmp_select_data_field[k]);
                        }
                    }
                    selected_data[field] = new_select_data_field.join(",");
                }
            }           
        }
    });
    for(var i=0;i<fields.length;i++)
    {
        var field = fields[i];
        $($.pdialog.getCurrent()).find("input[name='selected_"+field+"']").val(selected_data[field]);
    }
}
function clear_select(pk,fields)
{
    $.each($($.pdialog.getCurrent()).find("input[name='"+pk+"']"), function(i, n){
        $(n).attr("checked",false);
    });
    for(var i=0;i<fields.length;i++)
    {
        var field = fields[i];
        selected_data[field] = "";
        $($.pdialog.getCurrent()).find("input[name='selected_"+field+"']").val(selected_data[field]);
    }
}