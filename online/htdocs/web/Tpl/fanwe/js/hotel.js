//切换国内，国际选项卡
new tab(Global.getByClass(document, "chooseCountry")[0],"but", Global.getByClass(document, "chooseCountry")[0],"countryDetail");


//弹出城市选择框
        new layer(document.getElementById("checkInCity"), document.getElementById("cityWrap"));
//选择特定城市
var allCityPanel = Global.getByClass(document.getElementById("cityWrap"), "city-panel");
        new showSpecialCity(document.getElementById("checkInCity"), 0, allCityPanel);
//选择城市选项卡
var cityPannel = Global.getByClass(document.getElementById("cityWrap"), "city-panel");
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

function setValue(ele,val){
  if(ele.value==""){
    ele.value=val;
  }
}

document.forms[0].onsubmit=function(){
  setValue(document.getElementById("checkInCity"),"北京");
}

//jq加载时间层
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
    Global.getByClass(document.getElementById("undefined").contentDocument.getElementById("hotelOption"),"f_type_ir")[1].click();
    document.getElementById("undefined").contentDocument.getElementById("citynameInter").style.width="200px";
    document.getElementById("undefined").contentDocument.getElementById("starttime").style.width="200px";
    document.getElementById("undefined").contentDocument.getElementById("deptime").style.width="200px";
    document.getElementById("undefined").contentDocument.getElementById("hotelName").style.width="200px";
    for(var i=0;i<document.getElementById("undefined").contentDocument.getElementsByTagName("li").length;i++){
        document.getElementById("undefined").contentDocument.getElementsByTagName("li")[i].style.width="100%"
    }

})
    }
    catch(e){
    Global.getByClass(document.getElementById("banner"),"but")[1].attachEvent("onclick",function(){
    Global.getByClass(document.getElementById("undefined").contentWindow.document.getElementById("hotelOption"),"f_type_ir")[1].click();
    document.getElementById("undefined").contentWindow.document.getElementById("citynameInter").style.width="200px";
    document.getElementById("undefined").contentWindow.document.getElementById("starttime").style.width="200px";
    document.getElementById("undefined").contentWindow.document.getElementById("deptime").style.width="200px";
    document.getElementById("undefined").contentWindow.document.getElementById("hotelName").style.width="200px";
    for(var i=0;i<document.getElementById("undefined").contentWindow.document.getElementsByTagName("li").length;i++){
        document.getElementById("undefined").contentWindow.document.getElementsByTagName("li")[i].style.width="100%"
    }
    
  })
    
    }


    
});