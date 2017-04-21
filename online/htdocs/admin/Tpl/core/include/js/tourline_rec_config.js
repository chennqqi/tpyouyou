$($(navTab.getCurrentPanel())).ready(function(){
        $(navTab.getCurrentPanel()).find("select[name='rec_page']").bind("change",function(){ load_type_html(); });
		$(navTab.getCurrentPanel()).find("select[name='rec_type']").bind("change",function(){ load_rec_id(); });   
    });
    function load_type_html()
    {
        var type_html='';
		if($(navTab.getCurrentPanel()).find("select[name='rec_page']").val()==0)
        {
		   type_html +='<option value="0">请选择类型</option>';
		   type_html +='<option value="1">国内游</option>';
		   type_html +='<option value="2">出境游</option>';
		   type_html +='<option value="3">周边游</option>';
		   type_html +='<option value="4">跟团游</option>';
		   type_html +='<option value="5">自助游</option>';
		   type_html +='<option value="6">自驾游</option>';
		   type_html +='<option value="7">大区域</option>';
		   type_html +='<option value="8">小区域</option>';
		   type_html +='<option value="9">标签</option>';
        }
		else{
		   type_html +='<option value="0">请选择类型</option>';
		   type_html +='<option value="7">大区域</option>';
		   type_html +='<option value="8">小区域</option>';
		   type_html +='<option value="9">标签</option>';
		}
        $(navTab.getCurrentPanel()).find("select[name='rec_type']").html(type_html);
    }
	
	function load_rec_id()
    {
		var rec_type_val=$(navTab.getCurrentPanel()).find("select[name='rec_type']").val();
		var area_url=$(navTab.getCurrentPanel()).find("input[name='area_url']").val();
		var place_url=$(navTab.getCurrentPanel()).find("input[name='place_url']").val();
		var tag_url=$(navTab.getCurrentPanel()).find("input[name='tag_url']").val();
		var dlg_rec_id=$(navTab.getCurrentPanel()).find("a[rel='dlg_rec_id']");
		var item_title=$(navTab.getCurrentPanel()).find("#rec_id_c .item_title");
		
		if( rec_type_val==7 || rec_type_val==8 || rec_type_val==9 )
		{
			$(navTab.getCurrentPanel()).find("#rec_id_c").show();
			$(navTab.getCurrentPanel()).find("input[name='rec_id.id']").val("0");
			$(navTab.getCurrentPanel()).find("input[name='rec_id.name']").val("");
			$(navTab.getCurrentPanel()).find("input[name='rec_id.name']").addClass("required");
			if(rec_type_val ==8)
			{
				$(dlg_rec_id).attr("href",place_url);
				$(dlg_rec_id).html("选择小区域");
				$(item_title).html("推荐小区域:");
				
			}
			else if(rec_type_val ==9)
			{
				$(dlg_rec_id).attr("href",tag_url);
				$(dlg_rec_id).html("选择标签");
				$(item_title).html("推荐标签:");
			}	
			else
			{
				$(dlg_rec_id).attr("href",area_url);
				$(dlg_rec_id).html("选择大区域");
				$(item_title).html("推荐大区域:");
			}
		}
		else
		{
			$(navTab.getCurrentPanel()).find("input[name='rec_id.name']").removeClass("required");
			$(navTab.getCurrentPanel()).find("#rec_id_c").hide();
		}
    }