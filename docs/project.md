短信模块：
  库system/sms  -- 目前能用的士smsbao_sms.php
  程序中调用通过system/utils/es_sms.php封装好的方法来使用
  web/lib/modules/cornModule.class.php中deal_msg_list()来实现业务队列中的群发

  例子： User::send_getpwd_mobile($user); 就是user.php中直接使用封装好的短信接口的例子,如果为其他功能添加发送短信的功能可以仿照写
  验证码保存在数据表中，要与发送的保持一致

项目目录

htdocs下

  web 放置网站html页面等
     Tpl/fanwe 包含网站所有html页面，css， js文件
     Lib 并包含页面对应的module php控制文件,路由视图等

  system 放置系统控制文件
    Libs 放置程序中常用的类， 封装后可以直接调用,一些公用方法， user表操作等
    utils 放置程序中常用的方法 es_开头， 封装后可以直接调用
  
  vendor composer管理的库
    wechat-php-sdk