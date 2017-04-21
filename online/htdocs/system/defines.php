<?php
//定义系统的一些常量

define("NOW_TIME",get_gmtime());
define("CLIENT_IP",get_client_ip());
define("SITE_DOMAIN",get_domain());
define("MAX_DYNAMIC_CACHE_SIZE",1000);  //动态缓存最数量
define("MAPTYPE","baidumap"); // baidumap[百度]   map[谷歌]
define("FULLTEXT_PREFIX","fanwe_");
define("IS_DEBUG",0);
define("SHOW_DEBUG",0);
?>