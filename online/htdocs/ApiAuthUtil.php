<?php //抓取页面

class ApiAuthUtil 
{ 
  
    
	var  $module; 
	var  $category; 
	var  $methodName; 
    var  $accountId; 
    var  $password; 
    var  $requestTime; 
	var  $allianceId; 
    
    function __construct($module,$category,$methodName,$accountId,$password,$requestTime,$allianceId){  
         $this->module = $module; 
         $this->category = $category; 
         $this->methodName = $methodName; 
         $this->accountId = $accountId; 
         $this->password = $password;
         $this->requestTime = $requestTime;
         $this->allianceId = $allianceId;
    }
    
    public function getDigitalSign() { 
        $fullActionName = $this->module.'.'. $this->category.'.'. $this->methodName;
        $accountId = $this->accountId;
        $password = $this->password;
        $requestTime = $this->requestTime;
        $token = "$fullActionName&$accountId&$password&$requestTime" ;
        $token =  strtolower($token);
    	return  sha1($token);
    } 

    public function AuthQeruyStringParams() { 
        $allianceId = $this->allianceId;
        $digitalSign = $this->getDigitalSign();
        $reqTime = $this->requestTime;
    	return "allianceId=$allianceId&digitalSign=$digitalSign&reqTime=$reqTime";
    }
    
    public function getApiURL($apiUrl) { 
        $authQeruyStringParams = $this->AuthQeruyStringParams();
    	return "$apiUrl?$authQeruyStringParams";
    } 
} 