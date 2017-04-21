$(document).ready(function(){
		$(".remove_ticket").live("click",function(){
			$(this).parent().parent().remove();
		});
		
		$(".edit_ticket").live("click",function(){
			var obj = $(this);
			obj.parent().parent().addClass("is_editor");
			var options = {
					width:800,
					height:480,
					mask:true,
					mixable:true,
					minable:true,
					resizable:true,
					drawable:true,
					fresh:true,
					close:function(args){
						obj.parent().parent().removeClass("is_editor");
						return true;
					},
					data:{
						"tickets":obj.parent().parent().find("input[name='tickets[]']").val()
					}
				};
			$.pdialog.open($("#tickets").attr("editurl"), "edittickets", "编辑门票", options);
　
		});
});
function SpotTicketCallBack(args){
	if(args.statusCode == 300){
		alertMsg.error(args.message)
		return false;
	}
	
	var ticket_data = '<tr>';
		ticket_data += '<td>'+$($.pdialog.getCurrent()).find("input[name='name']").val()+'<input type="hidden" name="tickets[]" value="'+args.message+'" /></td>';
		ticket_data += '<td align="center">'+$($.pdialog.getCurrent()).find("input[name='origin_price']").val()+'</td>';
		ticket_data += '<td align="center">'+$($.pdialog.getCurrent()).find("input[name='current_price']").val()+'</td>';
		ticket_data += '<td align="center">'+$($.pdialog.getCurrent()).find("input[name='sale_price']").val()+'</td>';
		ticket_data += '<td align="center">'+$($.pdialog.getCurrent()).find("input[name='sort']").val()+'</td>';
		
		
		if($($.pdialog.getCurrent()).find("select[name='pager_must']").val() == "1")
			ticket_data += '<td align="center">是</td>';
		else
			ticket_data += '<td align="center">否</td>';
		if($($.pdialog.getCurrent()).find("select[name='is_tuan']").val() == "1")
			ticket_data += '<td align="center">是</td>';
		else
			ticket_data += '<td align="center">否</td>';
		if($($.pdialog.getCurrent()).find("select[name='is_effect']").val() == "1")
			ticket_data += '<td align="center">有效</td>';
		else
			ticket_data += '<td align="center">无效</td>';
			
		ticket_data += '<td align="center">';
		ticket_data += '  <a href="javascript:void(0)" class="edit_ticket">编辑</a>&nbsp;';
		ticket_data += '  <a href="javascript:void(0)" class="remove_ticket">删除</a>';
		ticket_data += '</td>';
		ticket_data += '</tr>';

	$("#tickets .list tbody" ,navTab.getCurrentPanel()).append(ticket_data);     
	$.pdialog.closeCurrent();                  
}

function SpotTicketCallBackEdit(args){
	if(args.statusCode == 300){
		alertMsg.error(args.message)
		return false;
	}
	var ticket_data = '';
		ticket_data += '<td>'+$($.pdialog.getCurrent()).find("input[name='name']").val()+'<input type="hidden" name="tickets[]" value="'+args.message+'" /></td>';
		ticket_data += '<td align="center">'+$($.pdialog.getCurrent()).find("input[name='origin_price']").val()+'</td>';
		ticket_data += '<td align="center">'+$($.pdialog.getCurrent()).find("input[name='current_price']").val()+'</td>';
		ticket_data += '<td align="center">'+$($.pdialog.getCurrent()).find("input[name='sale_price']").val()+'</td>';
		ticket_data += '<td align="center">'+$($.pdialog.getCurrent()).find("input[name='sort']").val()+'</td>';
		if($($.pdialog.getCurrent()).find("select[name='pager_must']").val() == "1")
			ticket_data += '<td align="center">是</td>';
		else
			ticket_data += '<td align="center">否</td>';
		if($($.pdialog.getCurrent()).find("select[name='is_tuan']").val() == "1")
			ticket_data += '<td align="center">是</td>';
		else
			ticket_data += '<td align="center">否</td>';
		if($($.pdialog.getCurrent()).find("select[name='is_effect']").val() == "1")
			ticket_data += '<td align="center">有效</td>';
		else
			ticket_data += '<td align="center">无效</td>';
		ticket_data += '<td align="center">';
		ticket_data += '  <a href="javascript:void(0)" class="edit_ticket">编辑</a>&nbsp;';
		ticket_data += '  <a href="javascript:void(0)" class="remove_ticket">删除</a>';
		ticket_data += '</td>';

	$("#tickets .list tbody tr.is_editor" ,navTab.getCurrentPanel()).html(ticket_data);     
	$.pdialog.closeCurrent(); 
	$("#tickets .list tbody tr.is_editor" ,navTab.getCurrentPanel()).removeClass("is_editor");
}


