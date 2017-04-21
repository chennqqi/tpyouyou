$(document).ready(function(){
    init_getform();
    init_holder();
	$("img").one("error",function(){
		$(this).attr("src",ERROR_IMG);
	});
	$.each($("img"),function(i,n){
		if($(n).attr("src")=='')
			$(n).attr("src",ERROR_IMG);
	});
	$(".lazy,.lazy img").lazyload({ 
		placeholder : LOADER_IMG,
		threshold : 0,
		event:"scroll",
		effect: "fadeIn",
		failurelimit : 10
	});
	
	//回顶部
	init_gotop();
	
	$("#J_categorys").hover(function(){
		$("#J_ALLSORT").show();
	},function(){
		$("#J_ALLSORT").hide();
	});
	
	 $("#J_ALLSORT .item").hover(function(){
        $(this).find(".border_hide_span").show();
        $(this).find(".i-mc").show();
        $(this).find(".left_title").addClass("title_border");
    }, function(){
        $(this).find(".border_hide_span").hide();
        $(this).find(".i-mc").hide();
        $(this).find(".left_title").removeClass("title_border");
    });
	
	//加载主导航的焦点取消
	$(".main_nav").find("a").bind("focus",function(){
		$(this).blur();
	});
	//城市列表切换
	$(".head_start_city").hover(
		function(){
			$(this).addClass("change_tab");
		},
		function(){
			$(this).removeClass("change_tab");
		}
	);
	//搜索类型切换
	$(".change_type_box").hover(
		function(){
			$(".tn_search_bar").show();
		},
		function(){
			$(".tn_search_bar").hide();
		}
	);
	$(".tn_search_bar").find("div").live("click",function(){
		var search_type = $(this).attr("rel");
		var search_show = $(this).html();
		$("#search_type").val(search_type);
		$("#select_search_type").html(search_show);
		$(".tn_search_bar").hide();
	});
	
	$("#J_side_areas .view_btn").click(function(){
		if($(this).hasClass("v")){
			$(this).removeClass("v");
			$(this).parent().parent().find(".bx").slideUp(100);
		}
		else{
			$(this).addClass("v");
			$(this).parent().parent().find(".bx").slideDown(100);
		}
	});
	
	$("#weixin_button").toggle(
	  function () {
	  	$("#weixin_button a").css({"border-left":"1px solid #b5b5b5","border-right":"1px solid #b5b5b5",top:"1px"});
		$(".qr_code_img").show();
	  },
	  function () {
	     $("#weixin_button a").css({"border-left":"1px solid #fafafa","border-right":"1px solid #fafafa",top:"0px"});
		 $(".qr_code_img").hide();
	  }
	);
});


//回顶部
function init_gotop()
{
	
	$(window).scroll(function(){
		
		var s_top = $(document).scrollTop()+$(window).height()-70;
		if($.browser.msie && $.browser.version =="6.0")
		{
			$("#gotop").css("top",s_top);
			if($(document).scrollTop()>0)
			{				
				$("#gotop").css("visibility","visible");	
			}
			else
			{
				$("#gotop").css("visibility","hidden");	
			}
		}	
		else
		{
			if($(document).scrollTop()>0)
			{
				if($("#gotop").css("display")=="none")
				$("#gotop").fadeIn();	
			}
			else
			{
				if($("#gotop").css("display")!="none")
				$("#gotop").fadeOut();
			}
		}
		
		
	});		
	
	$("#gotop").bind("click",function(){		
		$("html,body").animate({scrollTop:0},"fast","swing",function(){});		
	});
	var top = $(document).scrollTop()+$(window).height()-70;
	if($.browser.msie && $.browser.version =="6.0")
	{
		$("#gotop").css("top",top);
		if($(document).scrollTop()>0)
		{	
			$("#gotop").css("visibility","visible");
		}
		else
		{
			$("#gotop").css("visibility","hidden");
		}
	}
	else
	{
		if($(document).scrollTop()>0)
		{	
			if($("#gotop").css("display")=="none")
			$("#gotop").show();	
		}
		else
		{
			if($("#gotop").css("display")!="none")
			$("#gotop").hide();
		}
	}
	

}

function selectTag(box,obj){
	$("#tagContent .tagContent").removeClass("selectTag");
	$("#"+box).addClass("selectTag");
	$("#tags li").removeClass("selectTag");
	$(obj).parent().addClass("selectTag");
}



//用于未来扩展的提示正确错误的JS
$.showErr = function(str,func)
{
	$.weeboxs.open(str, {boxid:'fanwe_error_box',contentType:'text',showButton:true, showCancel:false, showOk:true,title:'错误',width:250,type:'wee',onclose:func});
};

$.showSuccess = function(str,func)
{
	$.weeboxs.open(str, {boxid:'fanwe_success_box',contentType:'text',showButton:true, showCancel:false, showOk:true,title:'提示',width:250,type:'wee',onclose:func});
};

$.showCfm = function(str,funok,funcls)
{
	$.weeboxs.open(str, {boxid:'fanwe_msg_box',contentType:'text',showButton:true, showCancel:true, showOk:true,title:'确认',width:250,type:'wee',onok:function(){
		$.weeboxs.close("fanwe_msg_box");
		if(funok!=null){
			funok.call(this);
		}
	},onclose:funcls});
};

/*验证*/
$.minLength = function(value, length , isByte) {
	var strLength = $.trim(value).length;
	if(isByte)
		strLength = $.getStringLength(value);
		
	return strLength >= length;
};

$.maxLength = function(value, length , isByte) {
	var strLength = $.trim(value).length;
	if(isByte)
		strLength = $.getStringLength(value);
		
	return strLength <= length;
};
$.getStringLength=function(str)
{
	str = $.trim(str);
	
	if(str=="")
		return 0; 
		
	var length=0; 
	for(var i=0;i <str.length;i++) 
	{ 
		if(str.charCodeAt(i)>255)
			length+=2; 
		else
			length++; 
	}
	
	return length;
};

$.checkMobilePhone = function(value){
	if($.trim(value)!='')
		return /^\d{6,}$/i.test($.trim(value));
	else
		return true;
};
$.checkEmail = function(val){
	var reg = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/; 
	return reg.test(val);
};


function close_pop()
{
	$(".dialog-close").click();
}


function init_ui_textbox()
{
    $(".ui-textbox,.ui-textarea").bind("focus",function(){
            $(this).removeClass("hover");
            $(this).removeClass("normal");
            $(this).addClass("hover");
    });
    $(".ui-textbox,.ui-textarea").bind("blur",function(){
            $(this).removeClass("hover");
            $(this).removeClass("normal");
            $(this).addClass("normal");
    });
}


function init_holder()
{
    init_ui_textbox();
     $.each($("*[holder]") ,function(i, obj){
        
        
           if('placeholder' in document.createElement('input'))
           {
                $(obj).attr("placeholder",$(obj).attr("holder"));
           }
           else
           
           {
                var holder = $(obj).prev();
                if($(holder).attr("rel")!="holder")
                holder = $("<span style='position:absolute; color:#666;' rel='holder'>"+$(obj).attr("holder")+"</span>");
                $(holder).css({"font-size":$(obj).css("font-size"),"padding-left":$(obj).css("padding-left"),"padding-right":$(obj).css("padding-right"),"padding-top":$(obj).css("padding-top"),"padding-bottom":$(obj).css("padding-bottom")});
                $(holder).css("left",$(obj).position().left);
                $(holder).css("top",$(obj).position().top);
                $(holder).css("width",$(obj).width());
                $(obj).before(holder);  

                if($.trim($(obj).val())!="")
                {
                    $(holder).css("display","none");
                }
                $(holder).click(function(){
                    $(obj).focus();
                });     
                $(obj).focus(function(){
                    $(holder).css("display","none");
                });
                $(obj).blur(function(){
                    if($.trim($(obj).val())=="")
                    $(holder).css("display","");
                });
           }            
    });
}

function view_map(xpoint,ypoint){
	var def = "?";
	if(MAP_URL.indexOf(".php")!=-1)
		def = "&";
	$.weeboxs.open("<iframe src='"+MAP_URL+def+"xpoint="+xpoint+"&ypoint="+ypoint+"' height='100%' width='100%' frameborder=0></iframe>",{boxid:'fanwe_map_box',contentType:'text',showButton:false,type:'wee',title:'地图',width:700,height:500});
}


//刷新验证码
function refresh_verify(dom)
{
    $(dom).attr("src",$(dom).attr("rel")+"?"+Math.random());
}

/**
 * 
 */
function ajax_login()
{   
    $.weeboxs.open(ajax_login_url, {contentType:'jsonp',boxid:'ajax_login_box',showButton:false,title:"会员登录",width:600,type:'wee', onopen:function(){ 
            init_holder();
            $("#ajax_login_form").live("submit",function(){            	
                doajaxlogin();
                return false;
            });

            $("#ajax_login_btn").unbind("click");
            $("#ajax_login_btn").bind("click",function(){   
                $("#ajax_login_form").submit();
            });
        },onclose:function(){
            
        }}); 
    
}

function close_ajax_login()
{
    $.weeboxs.close("ajax_login_box");
}

function user_tip()
{    
    $.ajax({ 
                url: user_tip_url,
                dataType: "jsonp",
                jsonp: 'callback',  
                type:"GET",
                global:false,
                success: function(obj){
                   $("#header_user_tip").html(obj.html);
                }
            }); 
}


function doajaxlogin()
{
    $form = $("#ajax_login_form");
    var action = $form.attr("action");
    var query = new Object();
    query.user_key = $.trim($form.find("input[name='user_key']").val());
    query.user_pwd = $.trim($form.find("input[name='user_pwd']").val());
    query.user_verify = $.trim($form.find("input[name='user_verify']").val());
    query.save_user = $form.find("input[name='save_user']:checked").val();
    query.ajax = 2;
    if(query.user_key == "")
    {
        $.showErr("用户名/手机/邮箱必需填写");
    }
    else if(query.user_pwd == "")
    {
        $.showErr("登录密码不能为空");
    }
   else  if(query.user_verify == "")
    {
        $.showErr("验证码不能为空");
    }
    else
    {
          $.ajax({ 
                url: action,
                dataType: "jsonp",
                jsonp: 'callback',  
                data:query,
                type:"GET",
                global:false,
                success: function(obj){                         
                    if(obj.status==1)
                    {
                        user_tip();
                        close_ajax_login();  
                        if(obj.script!="")
                        {
                        	$("body").append(obj.script);
                        }
                        $.showSuccess(obj.info,function(){ });
                    }
                    else        
                    {       
                        refresh_verify($("#ajax_login_form").find("img"));                    
                        $.showErr(obj.info,function(){                            
                            if(obj.jump!="")
                            location.href = obj.jump;
                        });
                    }
                }
            }); 
    }
}

/**
 * 获取滚动条距离顶部的距离
 */
function getScroll(){  
         var bodyTop = 0;    
         if (typeof window.pageYOffset != 'undefined') {    
             bodyTop = window.pageYOffset;    
         } else if (typeof document.compatMode != 'undefined' && document.compatMode != 'BackCompat') {    
             bodyTop = document.documentElement.scrollTop;    
         }    
         else if (typeof document.body != 'undefined') {    
             bodyTop = document.body.scrollTop;    
         }    
         return bodyTop  
    } 
    

function init_getform()
{ 
    $(".getform").live("submit",function(){
        var action = $(this).attr("action");
        var query = $(this).serialize();
        if(action.indexOf("?")>=0)
        {
            action+="&"+query;
        }
        else
        {
            action+="?"+query;
        }
        location.href = action;
        return false;
    });
}

function setcity(pobj,cobj,city_id,callBack){
	load_region_city(pobj,cobj,city_id,callBack);
	if (callBack != null) {
		callBack.call(this);
	}
}

function load_region_city(pobj,cobj,city_id,callBack){
	var pid = $(pobj).val();
	var html = '<option value="0">请选择</option>';
	
	if (parseInt(pid) > 0 && region_city[pid] != undefined) {
		$.each(region_city[pid], function(i, data){
			if (data.id == city_id) 
				html += '<option value="' + data.id + '" selected="selected">' + data.py_first + ' ' + data.name + '</option>';
			else 
				html += '<option value="' + data.id + '">' + data.py_first + ' ' + data.name + '</option>';
		});
	}
	
	$(cobj).html(html);
	
	if(callBack!=null){
		callBack.call(this);
	}
}


$(function(){
/* user_tip JS*/
var GUID_DEFAULT_HTML = null;
var GUID_TIME_OUT = null;
var User_Tip_Ajax = null;
$(".GUID").live('mouseover',function(){
		if(GUID_DEFAULT_HTML == null)
			GUID_DEFAULT_HTML = $("#USER_INFO_TIP").html();
		clearTimeout(GUID_TIME_OUT);
		ClearUserTipAjax();
		var uid = parseInt(this.getAttribute('uid'));
		if(uid < 1)
			return;
                
                if($(".user_tip_info_"+uid).html()!=null){
                    UserTipShow(this,$(".user_tip_info_"+uid).html());
                    return;
                }

		UserTipShow(this,GUID_DEFAULT_HTML);
		var query = new Object();
		query.uid = uid;
		
		var thisobj = this;
		var ajax_url = $(".get_user_info_tip_ajaxurl").val();
		User_Tip_Ajax = $.ajax({
			url: ajax_url,
			type: "POST",
			data:query,
			cache:false,
			dataType: "html",
			success:function(html){
				if(html != '')
				{
                                    UserTipShow(thisobj,html);
                                    $(".user_info_tip_cache").append('<div class="user_tip_info_'+uid+'">'+html+'</div>');
				}
				else
					$("#USER_INFO_TIP").hide();
				ClearUserTipAjax();
			},
			error:function(){
				$("#USER_INFO_TIP").hide();
				ClearUserTipAjax();
			}
		});
	}).live('mouseout',function(){
		var fun = function(){
			$("#USER_INFO_TIP").hide();
		};
		GUID_TIME_OUT = setTimeout(fun,500);
		ClearUserTipAjax();
	});
	
	$("#USER_INFO_TIP").hover(function(){
		clearTimeout(GUID_TIME_OUT);
		$("#USER_INFO_TIP").show();
	},function(){
		$("#USER_INFO_TIP").hide();
	});
	
	
function UserTipShow(obj,html)
{
	$("#USER_INFO_TIP").html(html);
	$("#USER_INFO_TIP").show();
	
	var w = 302;
	var offset = $(obj).offset();
	var left = offset.left;
	var top = offset.top - $("#USER_INFO_TIP").height();
	var width = $(document).width() - 30;
	
	if(left + w > width)
		left = left - w + $(obj).width();
	else if(left < 30)
		left = 30;
	var c = offset.left - left + $(obj).width() / 2 - 8;
	
	$("#USER_INFO_TIP").css({"top":top,"left":left});
	$("#USER_INFO_TIP .tip_arrow").css({"margin-left":c});
}

function ClearUserTipAjax()
{
	if(User_Tip_Ajax != null)
	{
		User_Tip_Ajax.abort();
		User_Tip_Ajax = null;
	}
}

function UserTipFollowHandler(obj,result)
{
	var parent = $(obj).parent();
	if(result.status == 1)
	{
		parent.html('<span class="fl icrad_add">已关注</span><a class="follow_del" href="javascript:;" onclick="$.User_Follow('+ result.uid +',this,\'UserTipFollowHandler\');">取消</a>');
	}
	else
	{
            parent.html('<div class="blank3"></div><a class="follow_button" onclick="$.User_Follow('+ result.uid +',this,\'UserTipFollowHandler\');" href="javascript:;">+加关注</a><div class="blank3"></div>');
	}
}
$.Check_Login = function(){
    $.ajax({
                url: check_user_url,
                type: "POST",
                dataType: "json",
                success: function(result){
                     if(result.status ==0){
                         ajax_login();
			 return false;
                     }else{
                         alert("uid:"+result.uid);
                         return result.uid;
                     }
                }
        });
};
//关注会员，uid 要关注的会员编号，ojb 点击对像，fun 处理函数
$.User_Follow=function(uid,obj,fun)
{
    $(".user_tip_info_"+uid).remove();
    fun= eval(fun);
        var query = new Object();
        query.uid = uid;
        $.ajax({
                url: user_follow_url,
                type: "POST",
                data:query,
                dataType: "json",
                success: function(result){
                        if(result.status == 2){
                               ajax_login();
                               return false;
                        }
                        if(result.html != null && fun == null)
                                $(obj).html(result.html);

                        if(fun != null)
                        {
                                result.uid = uid;
                                fun.call(this,obj,result);
                        }
                }
        });
};

//关注会员，uid 要关注的会员编号，ojb 点击对像，fun 处理函数
$.User_Follows=function(uids,fun)
{
        var query = new Object();
        query.uids = uids;

        $.ajax({
                url: user_follows_url,
                type: "POST",
                data:query,
                dataType: "json",
                success: function(result){
                    if(result.status == 2){
                               ajax_login();
                               return false;
                        }else{
                            fun.call(this,result);
                        }
                        
                }
        });
};

//删除粉丝，uid 要删除的会员编号，fun 处理函数
$.Remove_Fans=function(uid,obj,fun)
{
        var query = new Object();
        query.uid = uid;

        $.ajax({
                url: remove_fans_url,
                type: "POST",
                data:query,
                dataType: "json",
                success: function(result){
                     if(result.status == 2){
                               ajax_login();
                               return false;
                        }else{
                           if(fun != null)
                                fun.call(this,obj,result);
                        }
                        
                }
        });
};

});