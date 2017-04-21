<?php //抓取页面

include_once('ApiAuthUtil.php');


$apiAuthUtil = new ApiAuthUtil("DiyTour", //模块
                               "Query", //分类
                               "GetGroupBuyResources", //方法
                               "d934f05d-bb84-46b2-ac90-d1bb595e4911", //账号
                               "2a8920a5baaeffb2", //密码
                               "2013-09-04 15:15:40.191", //请求时间字符串
                               92320); //联盟ID

$digitalSign = $apiAuthUtil->getDigitalSign();
$authQeruyStringParams = $apiAuthUtil->AuthQeruyStringParams();
$apiURL = $apiAuthUtil->getApiURL("http://localhost/openapi/holiday/DiyTour/Query/GetGroupBuyResources");
var_dump($authQeruyStringParams);
echo "digitalSign=>$digitalSign<br/>";
echo "authQeruyStringParams=>$authQeruyStringParams<br/>";
echo "apiURL=>$apiURL<br/>";