$(document).ready(function() {
  //从页面收集OpenAPI必要的参数。get_user_info不需要输入参数，因此paras中没有参数
  var paras = {};

  //用JS SDK调用OpenAPI
  QC.api("get_user_info", paras)
    //指定接口访问成功的接收函数，s为成功返回Response对象
    .success(function(s) {
      //成功回调，通过s.data获取OpenAPI的返回数据
      console.log('s data', s.data)
      alert("获取用户信息成功！当前用户昵称为：" + s.data.nickname);
    })
    //指定接口访问失败的接收函数，f为失败返回Response对象
    .error(function(f) {
      //失败回调
      alert("获取用户信息失败！");
    })
    //指定接口完成请求后的接收函数，c为完成请求返回Response对象
    .complete(function(c) {
      //完成请求回调
      alert("获取用户信息完成！");
    });

  console.log('qc login check', QC.Login.check())

  if (QC.Login.check()) {
    QC.Login.getMe(function(openId, accessToken) {
      console.log('openid is', openId)
      console.log('accessToken', accessToken)

      $form = $("#page_login_qq");
      var actionurl = $form.attr("url");
      var query = {
        'openid': openId,
        'accesstoken': accessToken
      }

      $.ajax({
        url: actionurl,
        data: query,
        dataType: "json",
        type: "POST",
        global: false,
        success: function(obj) {
          alert(obj)
        }
      });


    })
  }
  
});

