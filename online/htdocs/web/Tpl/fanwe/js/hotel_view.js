//初始化日期选择框里面的值
var date = new Date();
if (date.getHours() >= 19) {
  date.setDate(date.getDate() + 1);
}
var month = date.getMonth() + 1;
var day = date.getDate();
$(".go").val(date.getFullYear() + "-" + ((month >= 10) ? month : ("0" + month)) + "-" + (day >= 10 ? day : ("0" + day)));
var returnDate = new Date();
returnDate.setDate(day + 3);
month = returnDate.getMonth() + 1;
day = returnDate.getDate();
$(".return").val(date.getFullYear() + "-" + ((month >= 10) ? month : ("0" + month)) + "-" + (day >= 10 ? day : ("0" + day)));

//jq加载时间层
$(function() {
    $(".go").datepicker()
    $(".return").datepicker();
});
