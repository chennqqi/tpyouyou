$(document).ready(function(){ 
	
	$("#cabin_next").click(function(){
		var parent_box=$(this).parent().parent();
		tourline_id=$(parent_box).find("input[name='tourline_id']").val();
		tourline_item_id=$(parent_box).find("input[name='tourline_item_id']").val();

		var cabins = []
		var uuCabinLis = $('.uu-cabin-li')

		var money = 0
		var adult_price_total_val = 0
		var child_price_total_val = 0
		var adult_count_total_val = 0
		var child_count_total_val = 0

		let item = $('ul.cabin-list').find('.cabins-input')
		let item_money = $('ul.cabin-list').find("input[name='money']")
		let adult_price_total = $('ul.cabin-list').find("input[name='adult_price_total']")
		let adult_count_total = $('ul.cabin-list').find("input[name='adult_count_total']")
		let child_count_total = $('ul.cabin-list').find("input[name='child_count_total']")
		let child_price_total = $('ul.cabin-list').find("input[name='child_price_total']")
		for (var i = 0; i < uuCabinLis.length; i++) {
			let uuCabinLi = uuCabinLis.eq(i)
			let data = {}
			let adult_count = uuCabinLi.find("select[name='adult_count']").val();
			let child_count = uuCabinLi.find("select[name='child_count']").val();
			let cabin_room_count = uuCabinLi.find("input[name='room_count']").val()
			if (Number(data.adult_count) !== 0 && Number(cabin_room_count) !== 0) {
			  let cabin_id = uuCabinLi.find("input[name='cabin_id']").val()
			  let cabin_people_num = uuCabinLi.find("input[name='cabin_people_num']").val()
			  let cabin_adult_price = uuCabinLi.find("input[name='cabin_adult_price']").val()
			  let cabin_child_price = uuCabinLi.find("input[name='cabin_child_price']").val()
			  if (Number(cabin_room_count) <= 0) {
			  	alert('房间数不能小于0')
			  	return
			  }
			  if (Number(cabin_people_num) !== (Number(adult_count) + Number(child_count))) {
			  	alert('每间舱房必须入住' + Number(cabin_people_num) + '人,请修改')
			  	return
			  } else {
					data.id = cabin_id.id
					data.cabin_people_num = cabin_people_num
					data.child_price = cabin_child_price
					data.adult_price = cabin_adult_price
					data.room_count = cabin_room_count
					data.adult_count = adult_count
					data.child_count = child_count
					money += (Number(adult_count)*Number(cabin_adult_price) + Number(child_count)*Number(cabin_child_price)) * Number(cabin_room_count)
					adult_count_total_val += Number(adult_count)
					child_count_total_val += Number(child_count)
					adult_price_total_val += Number(adult_count)*Number(cabin_adult_price) * Number(cabin_room_count)
					child_price_total_val += Number(child_count)*Number(cabin_child_price) * Number(cabin_room_count)
					cabins.push(data)
			  }
			}
		}

		item.val(JSON.stringify(cabins))
		adult_price_total.val(adult_price_total_val)
		child_price_total.val(child_price_total_val)
		adult_count_total.val(adult_count_total_val)
		child_count_total.val(child_count_total_val)

		var spanmoney = $('span.span-money')
		spanmoney.text(money)

		if (cabins.length == 0) {
			return
		}
		
		var query = new Object();
		query.tourline_id=tourline_id;
		query.tourline_item_id=tourline_item_id;
		query.ajax=1;
		$.ajax({
			url:cruise_order_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(ajaxobj){
				if(ajaxobj.status==2){
					ajax_login();
					return false;
				}
				else if(ajaxobj.status==0){
					$.showErr(ajaxobj.info);
					return false;
				}
				else if(ajaxobj.status==1){
					$(parent_box).submit();
				}
			}
		});
		
	});
});