$($(navTab.getCurrentPanel())).ready(function(){
        load_u_define();
        $(navTab.getCurrentPanel()).find("select[name='u_module']").bind("change",function(){ load_u_define();});      
    });
    function load_u_define()
    {
        if($(navTab.getCurrentPanel()).find("select[name='u_module']").val()=='')
        {
            $(navTab.getCurrentPanel()).find("#u_config").hide();
            $(navTab.getCurrentPanel()).find("#u_act").hide();
            $(navTab.getCurrentPanel()).find("#u_define").show();
            $(navTab.getCurrentPanel()).find("input[name='url']").addClass("url");
        }
        else
        {
            var module = $(navTab.getCurrentPanel()).find("select[name='u_module']").val();
            $.ajax({ 
                    url: LOAD_MODULE_URL+"&module="+module, 
                    data: "ajax=1",
                    dataType: "json",
                    success: function(obj){
                        if(obj.data)
                        {
                            var html="<select name='u_action' class='combox'>";
                            for(name in obj.data)
                            {
                                html+="<option value='"+name+"' ";
                                if(obj.info==name)
                                {
                                    html+=" selected='selected' ";
                                }
                                html+=" >"+obj.data[name]+"</option>";
                            }
                            html+="</select>";
                            $(navTab.getCurrentPanel()).find("#u_act").html(html);
                            $(navTab.getCurrentPanel()).find("select.combox").combox();
                        }
                        else
                        {
                            $(navTab.getCurrentPanel()).find("#u_act").html("");
                        }
                    }
            });
            $(navTab.getCurrentPanel()).find("#u_act").show();
            $(navTab.getCurrentPanel()).find("#u_define").hide();
            $(navTab.getCurrentPanel()).find("#u_config").show();
            $(navTab.getCurrentPanel()).find("input[name='url']").removeClass("url");
        }
    }