$(document).ready(function() {
  var wxqr = $('#wxQr')
  var timestamp = new Date().getTime().toString()
  var timestamp_str = timestamp.substring(3, 12)
  console.log('time', timestamp_str)
  var status = 0
  var user_id = null
  var wxCount = 180
  
  var interval = setInterval(function () {
    wxCount--
    if (wxCount < 0) {
      clearInterval(interval)
      alert('登录超时!')
    }
    if (status === 1) {
      clearInterval(interval)
      $.ajax({
        url: WX_Login_Url,
        data: {
          user_id: user_id
        },
        dataType: "json",
        type: "POST",
        global: false,
        success: function(obj) {
          console.log('login obj', obj)
          if (obj.status === 1) {
            console.log('登录')
            // window.location.href = obj.jump
            window.location.href = './'
          } else {
            console.log('登录失败， 请刷新页面')
          }
        },
        fail: function(obj) {
          console.log('login obj 2')
          console.log('login obj', obj)
          console.log('登录失败， 请刷新页面')
        }
      });
      return
    }
    longPolling()
  }, 2000)

  $.ajax({
    url: WX_QR_URL,
    data: {
      'scene_id': timestamp_str
    },
    dataType: "json",
    type: "GET",
    global: false,
    success: function(obj) {
      console.log('obj', obj)
      if (obj.status == 1) {
        wxqr.attr('src', obj.pic)
        interval
      }
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

  function longPolling() {
    $.ajax({
        url: WX_Long_Ajax,
        type: 'post',
        data: {
          "time": '10',
          "sceneid": timestamp_str
        },
        dataType: "json",
        timeout: 5000, //5秒超时，可自定义设置  
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          console.log('err data', textStatus)
        },
        success: function(data, textStatus) {
          console.log('data', data)
          console.log('data type', typeof data.status)
          console.log('data conf', data.status === 1)
          if (data.status === 0) {
            console.log('fail still ajax')
          } else if (data.status === 1) {
            status = data.status
            user_id = data.user_id
            console.log('do login')
          } else if (data.status === 2) {
            status = data.status
            console.log('登录失败， 请刷新页面')
          }
          
        }
    });
  }

});

