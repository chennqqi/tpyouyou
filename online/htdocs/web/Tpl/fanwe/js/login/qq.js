$(document).ready(function() {
  //从页面收集OpenAPI必要的参数。get_user_info不需要输入参数，因此paras中没有参数
  var paras = {};

  var box_main = $('.box_main');
  var rel_h3 = $('.rel_h3');
  var go_index = $('.go_index');

  box_main.hide();
  rel_h3.hide();

  var user_info;

  //用JS SDK调用OpenAPI
  QC.api("get_user_info", paras)
    //指定接口访问成功的接收函数，s为成功返回Response对象
    .success(function(s) {
      //成功回调，通过s.data获取OpenAPI的返回数据
      console.log('s data', s.data)
      user_info = s.data;
      // alert("获取用户信息成功！当前用户昵称为：" + s.data.nickname);
      console.log('qc login check', QC.Login.check())
      if (QC.Login.check()) {
        QC.Login.getMe(function(openId, accessToken) {
          console.log('openid is', openId)
          console.log('accessToken', accessToken)

          $form = $("#page_login_qq");
          var actionurl = $form.attr("url");
          console.log('page login qq', actionurl)
          var query = {
            'openid': openId,
            'accesstoken': accessToken,
            'nickname': user_info.nickname,
            'figureurl': user_info.figureurl_qq_2 || user_info.figureurl_qq_1
          }

          $.ajax({
            url: actionurl,
            data: query,
            dataType: "json",
            type: "POST",
            global: false,
            success: function(obj) {
              console.log('obj 1')
              console.log('obj', obj)
              if (obj.status == 2) {
                // console.log('status 2')
                // window.history.go(-2)
                window.location.href = './'
              }
              go_index.hide();
              box_main.show();
              rel_h3.show();
            },
            fail: function(obj) {
              console.log('obj 2')
              console.log('obj', obj)
            },
            complete: function(obj) {
              console.log('obj 3')
              console.log('obj', obj)
            }
          });


        })
      }
    })
    //指定接口访问失败的接收函数，f为失败返回Response对象
    .error(function(f) {
      //失败回调
      alert("获取用户信息失败！");
    });

  
});

