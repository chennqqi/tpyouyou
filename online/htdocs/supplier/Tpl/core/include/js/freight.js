$($(navTab.getCurrentPanel())).ready(function(){
	
	$(navTab.getCurrentPanel()).find(".province_id").bind("change",function(){
		load_city($(this));
	});
	$(navTab.getCurrentPanel()).find(".formtable tr a").bind("click",function(){
		$(navTab.getCurrentPanel()).find(".formtable tr[rel='"+$(this).attr("rel")+"']").remove();
	});
	
	$(navTab.getCurrentPanel()).find("#add_freight").bind("click",function(){
		var province_dom = $(PROVINCE_LIST);
		var rowdom = $("<tr><td class='item_title'></td><td class='item_input'></td></tr>");		
		rowdom.find(".item_input").append(province_dom);
		rowdom.find(".item_input").append($("<input type='text' name='price[]' class='textInput' /> <div style='margin-right:10px; margin-left:10px; height:22px; line-height:22px; display:inline-block; float:left;'>元</div> <a class='button' href='javascript:void(0);'><span>删除</span></a>"));
		$(navTab.getCurrentPanel()).find(".formtable tr").last().after(rowdom);
		load_city($(province_dom));
		rowdom.find("a").bind("click",function(){
			rowdom.remove();
		});
		$(province_dom).bind("change",function(){
			 load_city($(this));
		})
	});

 });
 function load_city(dom)
 {
     var province_id = $(dom).val();
     $.ajax({ 
                    url: LOAD_CITY_URL+"&province_id="+province_id, 
                    data: "ajax=1",
                    dataType: "json",
                    success: function(obj){
                        var html="<select name='city_id[]' class='city_id'><option value='0'>全省统一</option>";
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
                        var city_dom = $(html);
                        $(dom).parent().find(".city_id").remove();
                        $(dom).after(city_dom);
                    }
            });
 }
