$(document).ready(function(){
		$(".remove_tourline_item").live("click",function(){
			$(this).parent().parent().remove();
		});
		
		$(".edit_tourline_item").live("click",function(){
			var obj = $(this);
			obj.parent().parent().addClass("is_editor");
			var options = {
					width:850,
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
						"tourline_items":obj.parent().parent().find("input[name='tourline_items[]']").val()
					}
				};
			$.pdialog.open($(navTab.getCurrentPanel()).find("#tourline_items").attr("editurl"), "edit_tourline_items", "编辑时间价格", options);
　
		});
		$(".edit_new_insurance").live("click",function(){
			var obj = $(this);
			obj.parent().parent().addClass("is_editor");
			var options = {
					width:800,
					height:600,
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
						"new_insurances":obj.parent().parent().find("input[name='new_insurances[]']").val()
					}
				};
			$.pdialog.open($("#new_insurances").attr("editurl"), "edit_new_insurance", "编辑新保险", options);
		});
		
		
	
});
function SpotTitemCallBack(args){
	if(args.statusCode == 300){
		alertMsg.error(args.message)
		return false;
	}

	var arr=new Array();
	data=args.message;
	var tourline_item_data ='';
		for(i=0; i< data.length;i++)
		{
			if(data[i]['is_forever']==1)
				data[i]['start_time']="永久";
			tourline_item_data += '<tr>';
			tourline_item_data += '<td>'+data[i]['start_time']+'<input type="hidden" name="tourline_items[]" value="'+encodeURI(data[i]['ser'])+'" /></td>';
			tourline_item_data += '<td align="center">'+data[i]['adult_price']+'</td>';
			tourline_item_data += '<td align="center">'+data[i]['adult_sale_price']+'</td>';
			tourline_item_data += '<td align="center">'+data[i]['child_price']+'</td>';
			tourline_item_data += '<td align="center">'+data[i]['child_sale_price']+'</td>';
			tourline_item_data += '<td align="center">'+data[i]['adult_limit']+'</td>';
			tourline_item_data += '<td align="center">'+data[i]['child_limit']+'</td>';
			tourline_item_data += '<td align="center">';
			tourline_item_data += '  <a href="javascript:void(0)" class="edit_tourline_item">编辑</a>&nbsp;';
			tourline_item_data += '  <a href="javascript:void(0)" class="remove_tourline_item">删除</a>';
			tourline_item_data += '</td>';
			tourline_item_data += '</tr>';
		}

	$("#tourline_items .list tbody" ,navTab.getCurrentPanel()).append(tourline_item_data);     
	$.pdialog.closeCurrent();                 
}

function SpotTitemCallBackEdit(args){
	if(args.statusCode == 300){
		alertMsg.error(args.message)
		return false;
	}
	start_time=$($.pdialog.getCurrent()).find("input[name='start_time']").val();
	var is_forever=$($.pdialog.getCurrent()).find("select[name='is_forever']").val();
	if(is_forever ==1)
		start_time="永久";
		
	var tourline_item_data = '';
		tourline_item_data += '<td>'+start_time+'<input type="hidden" name="tourline_items[]" value="'+encodeURI(args.message)+'" /></td>';
		tourline_item_data += '<td align="center">'+$($.pdialog.getCurrent()).find("input[name='adult_price']").val()+'</td>';
		tourline_item_data += '<td align="center">'+$($.pdialog.getCurrent()).find("input[name='adult_sale_price']").val()+'</td>';
		tourline_item_data += '<td align="center">'+$($.pdialog.getCurrent()).find("input[name='child_price']").val()+'</td>';
		tourline_item_data += '<td align="center">'+$($.pdialog.getCurrent()).find("input[name='child_sale_price']").val()+'</td>';
		tourline_item_data += '<td align="center">'+$($.pdialog.getCurrent()).find("input[name='adult_limit']").val()+'</td>';
		tourline_item_data += '<td align="center">'+$($.pdialog.getCurrent()).find("input[name='child_limit']").val()+'</td>';
		tourline_item_data += '<td align="center">';
		tourline_item_data += '  <a href="javascript:void(0)" class="edit_tourline_item">编辑</a>&nbsp;';
		tourline_item_data += '  <a href="javascript:void(0)" class="remove_tourline_item">删除</a>';
		tourline_item_data += '</td>';
		tourline_item_data += '</tr>';

	$("#tourline_items .list tbody tr.is_editor" ,navTab.getCurrentPanel()).html(tourline_item_data);     
	$.pdialog.closeCurrent(); 
	$("#tourline_items .list tbody tr.is_editor" ,navTab.getCurrentPanel()).removeClass("is_editor");
}

function SpotNewInsuranceCallBack(args){
	if(args.statusCode == 300){
		alertMsg.error(args.message)
		return false;
	}
	var new_insurance_data = '<tr>';
		new_insurance_data += '<td>'+$($.pdialog.getCurrent()).find("input[name='name']").val()+'<input type="hidden" name="new_insurances[]" value="'+encodeURI(args.message)+'" /></td>';
		new_insurance_data += '<td align="center">'+$($.pdialog.getCurrent()).find("input[name='price']").val()+'</td>';
		new_insurance_data += '<td align="center">';
		new_insurance_data += '  <a href="javascript:void(0)" class="edit_new_insurance">编辑</a>&nbsp;';
		new_insurance_data += '  <a href="javascript:void(0)" class="remove_new_insurance">删除</a>';
		new_insurance_data += '</td>';
		new_insurance_data += '</tr>';

	$("#new_insurances .list tbody" ,navTab.getCurrentPanel()).append(new_insurance_data);     
	$.pdialog.closeCurrent();                  
}

function SpotNewInsuranceCallBackEdit(args){
	if(args.statusCode == 300){
		alertMsg.error(args.message)
		return false;
	}
	var new_insurance_data = '';
		new_insurance_data += '<td>'+$($.pdialog.getCurrent()).find("input[name='name']").val()+'<input type="hidden" name="new_insurances[]" value="'+encodeURI(args.message)+'" /></td>';
		new_insurance_data += '<td align="center">'+$($.pdialog.getCurrent()).find("input[name='price']").val()+'</td>';
		new_insurance_data += '<td align="center">';
		new_insurance_data += '  <a href="javascript:void(0)" class="edit_new_insurance">编辑</a>&nbsp;';
		new_insurance_data += '  <a href="javascript:void(0)" class="remove_new_insurance">删除</a>';
		new_insurance_data += '</td>';
		new_insurance_data += '</tr>';

	$("#new_insurances .list tbody tr.is_editor" ,navTab.getCurrentPanel()).html(new_insurance_data);     
	$.pdialog.closeCurrent(); 
	$("#new_insurances .list tbody tr.is_editor" ,navTab.getCurrentPanel()).removeClass("is_editor");
}
