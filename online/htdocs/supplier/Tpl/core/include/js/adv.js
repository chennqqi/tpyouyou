$(document).ready(function(){
	load_file();
	$("select[name='tmpl']").bind("change",function(){
		load_file();
	});
	
});
function load_file()
{
	var tmpl = $("select[name='tmpl']").val();
	$.ajax({ 
		url: LOAD_FILE_URL+"&tmpl="+tmpl, 
		data: "ajax=1",
		dataType: "json",
		success: function(obj){
			var files = obj;
			var html = "<select name='file' class='combox'>";
			html += "<option value=''>=="+LANG['EMPTY_SELECT']+"==</option>";
			for(i=0;i<files.length;i++)
			{
				html += "<option value='"+files[i]+"'>"+files[i]+"</option>";
			}
			html += "</select>";
			$("#file_box").html(html);
            $("select.combox",$(document)).combox();
            $("select[name='file']").bind("change",function(){
                load_adv_id();
            });
			load_adv_id();
		}
	});
}
function load_adv_id()
{
	var tmpl = $("select[name='tmpl']").val();
	var file = $("select[name='file']").val();
	
	$.ajax({ 
		url: LOAD_ADV_ID_URL+"&tmpl="+tmpl+"&file="+file, 
		data: "ajax=1",
		dataType: "json",
		success: function(obj){
			var adv_ids = obj;
			var html ="<select name='adv_id' class='combox'>";
			html += "<option value=''>=="+LANG['EMPTY_SELECT']+"==</option>";
			for(i=0;i<adv_ids.length;i++)
			{
				html += "<option value='"+adv_ids[i]+"'>"+adv_ids[i]+"</option>";
			}
			html+="</select>";
			$("#adv_id_box").html(html);
			 $("select.combox",$(document)).combox();
		}
	});
}