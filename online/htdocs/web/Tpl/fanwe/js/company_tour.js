function chooseTag(box,obj){
	$("#tagContents .tagContent").removeClass("selectTag");
	$("#"+box).addClass("selectTag");
	$("#tags li").removeClass("selectTag");
	$(obj).parent().addClass("selectTag");
}

function check_start_city(){
    var start_city = $("input[name='start_city']").val();
    if(!start_city){
        $('.start_city_notice').html('出发城市不能为空');
        return false;
    }
    return true;
}

function check_destination(){
    var destination = $('.destinations').val();
    if(destination == '可输入多个出游目的地，以逗号隔开' || !destination){
        $('.destination_notice').html('目的城市不能为空');
        $('.destinations').val('可输入多个出游目的地，以逗号隔开');
        return false;
    }else{
        $('#destination_notice').html('');
    }
    return true;
}

function check_start_date(){
    var start_date = $("input[name='start_date']").val();
    if(!start_date){
        $('.start_date_notice').html('出游时间不能为空');
        return false;
    }
    return true;
}

function check_date_num(){
    var date_num = $("input[name='date_num']").val();
    if(!date_num){
        $('.date_num_notice').html('出游天数不能为空');
        return false;
    }
	if(date_num && isNaN(date_num)){
		$('.date_num_notice').html('出游天数必须是数字');
		return false;
	}
    return true;
}

function check_people_num(){
    var people_min = $("input[name='people_min']").val();
    var people_max = $("input[name='people_max']").val();

    if(!people_min || !people_max){
        $('.people_num_notice').html('请输入出游人数');
        return false;
    }
    if((people_min && isNaN(people_min))|| (people_max && isNaN(people_max))){
        $('.people_num_notice').html('出游人数必须是数字');
        return false;
    }   
    return true;
}

function check_budget_num(){
    var budget_min = $("input[name='budget_min']").val();
    var budget_max = $("input[name='budget_max']").val();	
    if((budget_min && isNaN(budget_min))|| (budget_max && isNaN(budget_max))){
        $('.budget_notice').html('预算必须是数字');
        return false;
    }   
    return true;
}

function check_company_name(){
    var company_name = $("input[name='company_name']").val();
    if(!company_name){
        $('.company_name_notice').html('公司名称不能为空');
        return false;
    }
    return true;
}

function check_contacts(){
    var contacts = $("input[name='contacts']").val();
    if(!contacts){
        $('.contacts_notice').html('联系人不能为空');
        return false;
    }
    return true;
}

function check_mobilephone(){
    var mobilephone = $("input[name='mobilephone']").val();
    	
    if(!mobilephone){
        $('.mobilephone_notice').html('手机不能为空');
        return false;
    }
    if(mobilephone.search(/^\d{11}$/)==-1){
        $('.mobilephone_notice').html('手机号必须是11位数字');
        return false;
    }  	   
    return true;
}

function check_telephone(){
    var area_code = $("input[name='area_code']").val();
	var telephone = $("input[name='telephone']").val();
    	
    if(area_code&&area_code!="区号"&&area_code.search(/^\d{3,4}$/)==-1){
        $('.telephone_notice').html('区号格式不正确');
        return false;
    }
    if(telephone&&telephone!="电话号码"&&telephone.search(/^\d{3,10}$/)==-1){
        $('.telephone_notice').html('电话号码格式不正确');
        return false;
    }	   
    return true;
}

function check_qq(){
    var qq = $("input[name='qq']").val();
    if(!qq){
        $('.qq_notice').html('请输入出qq');
        return false;
    }
    if(qq.search(/^[1-9]\d{4,9}$/)==-1){
        $('.qq_notice').html('qq必须是数字,5到10位，开头不能为0');
        return false;
    }   
    return true;
}
function check_yzm(){
    var yzm = $("input[name='yzm']").val();
    if(!yzm){
        $('.yzm_notice').html('请输入出验证码');
        return false;
    } 
    return true;
}

function check_form(){	
    if(!check_start_city()){        
        return false;
    }
    if(!check_destination()){        
        return false;
    }
    if(!check_start_date()){        
        return false;
    }
    if(!check_date_num()){        
        return false;
    }
    if(!check_people_num()){        
        return false;
    }
    if(!check_budget_num()){        ;
        return false;
    }
    if(!check_company_name()){        
        return false;
    }
    if(!check_contacts()){        
        return false;
    }
    if(!check_mobilephone()){        
        return false;
    }
    if(!check_telephone()){       
        return false;
    }
    if(!check_qq()){       
        return false;
    }
    if(!check_yzm()){       
        return false;
    }
    return true;

}


$(document).ready(function(){
	$(".start_city_dd").hover(
		function(){
			$(this).addClass("change_tab");
		},
		function(){
			$(this).removeClass("change_tab");
		}
	);	
	$(".city_name").click(function(){
		$('form').find("input[name='start_city']").val($(this).html());		
	})
	
	
	$(".main").find(".sub").live("click",function(){
        if(check_form()){
			var query = $(".main").find("form").serialize()+"&ajax=1";						
		    var action = $(".main").find("form").attr("action");			
			$.ajax({ 
                url: action,
                data:query,
                dataType:"json",
                type:"POST",
                global:false,
                success: function(obj){
                    refresh_verify($(".yamdd").find("img"));
               		if(obj.status==0){
						$.showErr(obj.info);
				    }				
					else if(obj.status==1){
						$.showSuccess(obj.info,function(){
                           if(obj.jump!="")
                           location.href = obj.jump;
                        });
					}
                }
            }); 
			
			
			
			return false;
		}else{
			return false;
		}		
    });
	
	
//	$(".main").find("form").live("submit",function(){	
//		
//	});
	
	$("input[name='start_city']").live("blur",function(){
        check_start_city();
    });
	$("input[name='start_city']").live("focus",function(){
       $('.start_city_notice').html('');
    });
	$(".destinations").live("blur",function(){
        check_destination();
    });
	$(".destinations").live("focus",function(){		
        $('.destination_notice').html('');
        var destination = $('.destinations').val();
        if(destination == '可输入多个出游目的地，以逗号隔开'){
            $('.destinations').val('');
        }
    });
	$("input[name='start_date']").live("blur",function(){
        check_start_date();
    });
	$("input[name='start_date']").live("focus",function(){
       $('.start_date_notice').html('');
    });
	$("input[name='date_num']").live("blur",function(){
        check_date_num();
    });
	$("input[name='date_num']").live("focus",function(){
         $('.date_num_notice').html('');
    });		
	$("input[name='people_min'],input[name='people_max']").live("blur",function(){
        check_people_num();		
    });	
	$("input[name='people_min'],input[name='people_max']").live("focus",function(){
        $('.people_num_notice').html('');		
    });	
	$("input[name='budget_min'],input[name='budget_max']").live("blur",function(){
        check_budget_num();
    });	
	$("input[name='budget_min'],input[name='budget_max']").live("focus",function(){
        $('.budget_notice').html('');
    });		
	$("input[name='company_name']").live("blur",function(){
        check_company_name();
    });
	$("input[name='company_name']").live("focus",function(){
         $('.company_name_notice').html('');
    });		
	$("input[name='contacts']").live("blur",function(){
        check_contacts();
    });
	$("input[name='contacts']").live("focus",function(){
         $('.contacts_notice').html('');
    });		
	$("input[name='mobilephone']").live("blur",function(){
        check_mobilephone();
    });
	$("input[name='mobilephone']").live("focus",function(){
         $('.mobilephone_notice').html('');
    });	
	$("input[name='area_code'],input[name='telephone']").live("blur",function(){
        check_telephone();
    });
	$("input[name='area_code'],input[name='telephone']").live("focus",function(){
        $('.telephone_notice').html('');
		var area_codes = $("input[name='area_code']").val();
		var telephones = $("input[name='telephone']").val();
        if(area_codes == '区号'){
            $("input[name='area_code']").val('');
        }
        if(telephones == '电话号码'){
            $("input[name='telephone']").val('');
        }		
    });
	$("input[name='qq']").live("blur",function(){
        check_qq();
    });
	$("input[name='qq']").live("focus",function(){
         $('.qq_notice').html('');
    });			
	$("input[name='address']").live("focus",function(){
         var addresss = $("input[name='address']").val();
		 if(addresss == '个人组织可不填写'){
		 	$("input[name='address']").val('');
		 }		 
    });			
	$("input[name='yzm']").live("blur",function(){
        check_yzm();
    });
	$("input[name='yzm']").live("focus",function(){
         $('.yzm_notice').html('');
    });		
	
	
	
	
	
	
});