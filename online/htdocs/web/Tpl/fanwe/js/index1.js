// JavaScript source code

new tab(document.getElementById("banner"),"but",document.getElementById("banner"),"chooseType");

new tab(document.getElementById("flight"),"flightBut",document.getElementById("flight"),"countryDetail");
new tab(document.getElementById("train"),"trainBut",document.getElementById("train"),"countryDetail");
new tab(document.getElementById("hotel"),"hotelBut",document.getElementById("hotel"),"countryDetail");
//弹出特定城市
var allCityPanel = Global.getByClass(document.getElementById("cityWrap"), "city-panel");

new showSpecialCity(document.getElementById("from1"), 0, allCityPanel);
new showSpecialCity(document.getElementById("to1"), 0, allCityPanel);
new showSpecialCity(document.getElementById("checkInCity"), 0, allCityPanel);

new showSpecialCity(document.getElementById("from2"), 1, allCityPanel);
new showSpecialCity(document.getElementById("to2"), 2, allCityPanel);
new showSpecialCity(document.getElementById("trainInCity"), 1, allCityPanel);
new showSpecialCity(document.getElementById("trainOnCity"), 1, allCityPanel);

new layer(document.getElementById("from1"), document.getElementById("cityWrap"));
new layer(document.getElementById("to1"), document.getElementById("cityWrap"));
new layer(document.getElementById("checkInCity"), document.getElementById("cityWrap"));

new layer(document.getElementById("from2"), document.getElementById("cityWrap"));
new layer(document.getElementById("to2"), document.getElementById("cityWrap"));
new layer(document.getElementById("trainInCity"), document.getElementById("cityWrap"));
new layer(document.getElementById("trainOnCity"), document.getElementById("cityWrap"));
//交换城市
new exchange(document.getElementById("from1"), document.getElementById("to1"), Global.getByClass(document.getElementById("banner"), "exchange")[0]);
new exchange(document.getElementById("from2"), document.getElementById("to2"), Global.getByClass(document.getElementById("banner"), "exchange")[1]);
		
//选择城市选项卡
var cityPannel = Global.getByClass(document.getElementById("cityWrap"), "city-panel")
for (var i = 0; i < cityPannel.length ; i++) {
    new tab(cityPannel[i], "cityIndex", cityPannel[i], "c-item");
}

//初始化日期选择框里面的值
var date = new Date();
if(date.getHours()>=19){
	date.setDate(date.getDate()+1);
	}
var month=date.getMonth()+1;
var day=date.getDate();
$(".go").val(date.getFullYear() + "-" + ((month >= 10) ? month : ("0" + month)) + "-" + (day >= 10 ? day : ("0" + day)));
var returnDate=new Date();
returnDate.setDate(day+3);
month=returnDate.getMonth()+1;
day=returnDate.getDate();
$(".return").val(date.getFullYear() + "-" + ((month >= 10) ? month : ("0" + month)) + "-" + (day >= 10 ? day : ("0" + day)));
//设置提交默认值
function setValue(ele,val){
  if(ele.value==""){
    ele.value=val;
  }
}
document.forms[0].onsubmit=function(){
  setValue(document.getElementById("from1"),"北京");
  setValue(document.getElementById("to1"),"上海");
}
document.forms[1].onsubmit=function(){
  setValue(document.getElementById("from2"),"北京");
  setValue(document.getElementById("to2"),"香港");
}
document.forms[2].onsubmit=function(){
  setValue(document.getElementById("checkInCity"),"北京");
}
//显示时间选择框
$(function () {
    $(".go").datepicker({
        numberOfMonths: 2,
        showButtonPanel: false,
        maxDate: "+7m", minDate: "+0d",
        dateFormat: "yy-mm-dd",
        showMonthAfterYear: true
    });
    $(".return").datepicker({
        numberOfMonths: 2,
        showButtonPanel: false,
        maxDate: "+7m", minDate: "+0d",
        dateFormat: "yy-mm-dd",
        showMonthAfterYear: true
    });
	
	
    try{
    Global.getByClass(document.getElementById("banner"),"but")[1].addEventListener("click",function(){
        Global.getByClass(document.getElementById("undefined").contentDocument.getElementById("hotelOption"), "f_type_ir")[1].click();

})
    }
    catch(e){
    Global.getByClass(document.getElementById("banner"),"but")[1].attachEvent("onclick",function(){
        Global.getByClass(document.getElementById("undefined").contentWindow.document.getElementById("hotelOption"), "f_type_ir")[1].click();
    
  })
    
    }
})
//选择单双程
new chooseWay(Global.getByClass(document, "countryDetail")[0]);
new chooseWay(Global.getByClass(document, "countryDetail")[1]);



/*setTimeout(function(){
    var detail=Global.getByClass(document.getElementById("wrap"),"detail");
    detail[0].innerHTML='<iframe height="144" width="930" border="0" frameborder="0" scrolling="no"  allowTransparency="no" style="border:none" src="http://u.ctrip.com/union/Code/VacationListBox.aspx?SearchId=218&SearchValue=32&StartCityID=2&SearchType=D&width=930&counts=1&AllianceID=7480&sid=172916&ouid=&app=0105C02" id="preview"></iframe><iframe height="144" width="930" border="0" frameborder="0" scrolling="no"  allowTransparency="no" style="border:none" src="http://u.ctrip.com/union/Code/VacationListBox.aspx?SearchId=195&SearchValue=25&StartCityID=2&SearchType=D&width=928&counts=1&AllianceID=7480&sid=172916&ouid=&app=0105C02" id="preview"></iframe> <iframe height="144" width="930" border="0" frameborder="0" scrolling="no"  allowTransparency="no" style="border:none" src="http://u.ctrip.com/union/Code/VacationListBox.aspx?SearchId=467&SearchValue=23&StartCityID=2&SearchType=D&width=928&counts=1&AllianceID=7480&sid=172916&ouid=&app=0105C02" id="preview"></iframe>';
    detail[1].innerHTML='<iframe height="144" width="930" border="0" frameborder="0" scrolling="no"  allowTransparency="no" style="border:none" src="http://u.ctrip.com/union/Code/VacationListBox.aspx?SearchId=139&SearchValue=42&StartCityID=2&SearchType=C&width=928&counts=1&AllianceID=7480&sid=172916&ouid=&app=0105C02" id="preview"></iframe><iframe height="144" width="930" border="0" frameborder="0" scrolling="no"  allowTransparency="no" style="border:none" src="http://u.ctrip.com/union/Code/VacationListBox.aspx?SearchId=288&SearchValue=96%2C30%2C31%2C34%2C106%2C80%2C109%2C97%2C25%2C27%2C36%2C94%2C35%2C99%2C247%2C226%2C95%2C77%2C14%2C28%2C258%2C76%2C21%2C22%2C23%2C12%2C43&StartCityID=2&SearchType=C&width=928&counts=1&AllianceID=7480&sid=172916&ouid=&app=0105C02" id="preview"></iframe><iframe height="144" width="930" border="0" frameborder="0" scrolling="no"  allowTransparency="no" style="border:none" src="http://u.ctrip.com/union/Code/VacationListBox.aspx?SearchId=313&SearchValue=78&StartCityID=2&SearchType=C&width=928&counts=1&AllianceID=7480&sid=172916&ouid=&app=0105C02" id="preview"></iframe> ';
    detail[2].innerHTML='<iframe height="144" width="930" border="0" frameborder="0" scrolling="no"  allowTransparency="no" style="border:none" src="http://u.ctrip.com/union/Code/VacationListBox.aspx?SearchId=&SearchValue=4&StartCityID=2&SearchType=pp&width=928&counts=1&AllianceID=7480&sid=172916&ouid=&app=0105C02" id="preview"></iframe><iframe height="144" width="930" border="0" frameborder="0" scrolling="no"  allowTransparency="no" style="border:none" src="http://u.ctrip.com/union/Code/VacationListBox.aspx?SearchId=&SearchValue=8&StartCityID=2&SearchType=pp&width=928&counts=1&AllianceID=7480&sid=172916&ouid=&app=0105C02" id="preview"></iframe> ';
},8000);
*/
/*try{
	window.addEventListener("load",function(){loadFrame()})
	}
catch(e){
	window.attachEvent("onload",function(){loadFrame()})
	}
function loadFrame(){
window.frames[0].onload=function(){
    var detail=Global.getByClass(document.getElementById("wrap"),"detail");
    detail[0].innerHTML='<iframe height="144" width="930" border="0" frameborder="0" scrolling="no"  allowTransparency="no" style="border:none" src="http://u.ctrip.com/union/Code/VacationListBox.aspx?SearchId=218&SearchValue=32&StartCityID=2&SearchType=D&width=930&counts=1&AllianceID=7480&sid=172916&ouid=&app=0105C02" id="preview"></iframe><iframe height="144" width="930" border="0" frameborder="0" scrolling="no"  allowTransparency="no" style="border:none" src="http://u.ctrip.com/union/Code/VacationListBox.aspx?SearchId=195&SearchValue=25&StartCityID=2&SearchType=D&width=928&counts=1&AllianceID=7480&sid=172916&ouid=&app=0105C02" id="preview"></iframe> <iframe height="144" width="930" border="0" frameborder="0" scrolling="no"  allowTransparency="no" style="border:none" src="http://u.ctrip.com/union/Code/VacationListBox.aspx?SearchId=467&SearchValue=23&StartCityID=2&SearchType=D&width=928&counts=1&AllianceID=7480&sid=172916&ouid=&app=0105C02" id="preview"></iframe>';
    detail[1].innerHTML='<iframe height="144" width="930" border="0" frameborder="0" scrolling="no"  allowTransparency="no" style="border:none" src="http://u.ctrip.com/union/Code/VacationListBox.aspx?SearchId=139&SearchValue=42&StartCityID=2&SearchType=C&width=928&counts=1&AllianceID=7480&sid=172916&ouid=&app=0105C02" id="preview"></iframe><iframe height="144" width="930" border="0" frameborder="0" scrolling="no"  allowTransparency="no" style="border:none" src="http://u.ctrip.com/union/Code/VacationListBox.aspx?SearchId=288&SearchValue=96%2C30%2C31%2C34%2C106%2C80%2C109%2C97%2C25%2C27%2C36%2C94%2C35%2C99%2C247%2C226%2C95%2C77%2C14%2C28%2C258%2C76%2C21%2C22%2C23%2C12%2C43&StartCityID=2&SearchType=C&width=928&counts=1&AllianceID=7480&sid=172916&ouid=&app=0105C02" id="preview"></iframe><iframe height="144" width="930" border="0" frameborder="0" scrolling="no"  allowTransparency="no" style="border:none" src="http://u.ctrip.com/union/Code/VacationListBox.aspx?SearchId=313&SearchValue=78&StartCityID=2&SearchType=C&width=928&counts=1&AllianceID=7480&sid=172916&ouid=&app=0105C02" id="preview"></iframe> ';
    detail[2].innerHTML='<iframe height="144" width="930" border="0" frameborder="0" scrolling="no"  allowTransparency="no" style="border:none" src="http://u.ctrip.com/union/Code/VacationListBox.aspx?SearchId=&SearchValue=4&StartCityID=2&SearchType=pp&width=928&counts=1&AllianceID=7480&sid=172916&ouid=&app=0105C02" id="preview"></iframe><iframe height="144" width="930" border="0" frameborder="0" scrolling="no"  allowTransparency="no" style="border:none" src="http://u.ctrip.com/union/Code/VacationListBox.aspx?SearchId=&SearchValue=8&StartCityID=2&SearchType=pp&width=928&counts=1&AllianceID=7480&sid=172916&ouid=&app=0105C02" id="preview"></iframe> ';
	}
}*/