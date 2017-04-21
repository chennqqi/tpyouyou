$($(navTab.getCurrentPanel())).ready(function(){
        $(navTab.getCurrentPanel()).find("select[name='province_id']").bind("change",function(){
            load_city();
        });
 });
 function load_city()
 {
     var province_id = $(navTab.getCurrentPanel()).find("select[name='province_id']").val();
     $.ajax({ 
                    url: LOAD_CITY_URL+"&province_id="+province_id, 
                    data: "ajax=1",
                    dataType: "json",
                    success: function(obj){
                        var html="<select name='city_id' class='combox'><option value='0'>未选择</option>";
                        if(obj.status)
                        {                            
                            for(i=0;i<obj.list.length;i++)
                            {
                                city_data = obj.list[i];
                                html+="<option value='"+city_data.id+"' ";
                                html+=" >"+city_data.py_first+" "+city_data.name+"</option>";
                            }
                        }
                        
                        html+="</select>";
                        $(navTab.getCurrentPanel()).find("#city_list").html(html);
                        $(navTab.getCurrentPanel()).find("select[name='city_id']").combox();
                    }
            });
 }
