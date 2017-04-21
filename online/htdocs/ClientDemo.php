<?php
/*************************************************************
 * 注意：此处需设定默认时区为北京时间
 *************************************************************/
date_default_timezone_set('PRC');

function concat_string($array) {
	$arg  = "";
	while (list ($key, $val) = each ($array)) {
		$arg.=$key."=".$val."&";
	}
	$arg = substr($arg,0,count($arg)-2); //去掉最后一个&字符
	return $arg;
}

/**
 * 对数组排序
 * $array 排序前的数组
 * return 排序后的数组
 */
function arg_sort($array) {
	ksort($array);
	reset($array);
	return $array;
}

/**
 * 实现多种字符编码方式
 * $input 需要编码的字符串
 * $_output_charset 输出的编码格式
 * $_input_charset 输入的编码格式
 * return 编码后的字符串
 */
function charset_encode($input,$_output_charset ,$_input_charset) {
	$output = "";
	if(!isset($_output_charset))$_output_charset = $_input_charset;
	if($_input_charset == $_output_charset || $input ==null ) {
		$output = $input;
	} elseif (function_exists("mb_convert_encoding")) {
		$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
	} elseif(function_exists("iconv")) {
		$output = iconv($_input_charset,$_output_charset,$input);
	} else die("sorry, you have no libs support for charset change.");
	return $output;
}

/**
 * 实现多种字符解码方式
 * $input 需要解码的字符串
 * $_output_charset 输出的解码格式
 * $_input_charset 输入的解码格式
 * return 解码后的字符串
 */
function charset_decode($input,$_input_charset ,$_output_charset) {
	$output = "";
	if(!isset($_input_charset) )$_input_charset = $_input_charset ;
	if($_input_charset == $_output_charset || $input ==null ) {
		$output = $input;
	} elseif (function_exists("mb_convert_encoding")) {
		$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
	} elseif(function_exists("iconv")) {
		$output = iconv($_input_charset,$_output_charset,$input);
	} else die("sorry, you have no libs support for charset changes.");
	return $output;
}

/********************************************************************************/

$server_url = 'http://tcopenapi.17usoft.com/handlers/scenery/queryhandler.ashx';	//接口地址调用正式的url

$version = '20111128102912';							//接口协议版本号，详见接口协议文档
$accountID = 'd934f05d-bb84-46b2-ac90-d1bb595e4911';	//API帐户ID(小写)，待申请审批通过后发
$accountKey = '2a8920a5baaeffb2';		//API帐户密钥，待申请审批通过后发放
$serviceName = 'GetSceneryList';							//调用接口的方法名称
$currentMS =  (int)(microtime()*1000); 					//当前时间的毫秒
$reqTime = date("Y-m-d H:i:s").".".$currentMS;			//当前时间到毫秒
$arr = array('Version'=>$version,
        'AccountID' => $accountID,      
        'ServiceName' => $serviceName,
        'ReqTime' => $reqTime
);
$sort_array  = arg_sort($arr);
$arg = concat_string($sort_array);
$digitalSign = md5($arg.$accountKey); //数字签名

//body中的请求参数
$clientIp = "127.0.0.1";
$cityId = 321;

//将$xml_data字符串中的param1节点拿去，即可看到少传参数返回的错误信息显示
$xml_data = '<?xml version="1.0" encoding="utf-8"?>
<request>
  <header>
    <version>'.$version.'</version>
    <accountID>'.$accountID.'</accountID>   
    <serviceName>'.$serviceName.'</serviceName>
    <digitalSign>'.$digitalSign.'</digitalSign>
    <reqTime>'.$reqTime.'</reqTime>
  </header>
  <body>
    <clientIp>'.$clientIp.'</clientIp>
    <cityId>'.$cityId.'</cityId>
  </body>
</request>';

/*************************************************************
 * 下一行代码视运行环境的字符集设置，决定是否启用
 *************************************************************/
//$xml_data = charset_encode($xml_data,'GBK','UTF-8');

$header = array();
$header[] = "Content-type: text/xml";	//定义content-type为xml
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $server_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
$response = curl_exec($ch);
if(curl_errno($ch))
{
	print curl_error($ch);
}
curl_close($ch);

//print_r($response);

/*************************************************************
 * 下一行代码视运行环境的字符集设置，决定是否启用
 *************************************************************/
//$response = charset_decode($response,'UTF-8','GBK');

header("Content-type: text/xml");
echo $response;
?>
