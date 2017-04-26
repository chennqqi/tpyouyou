package com.chuanglan.demo;

import java.io.UnsupportedEncodingException;

import com.alibaba.fastjson.JSON;
import com.chuanglan.sms.request.SmsVarableRequest;
import com.chuanglan.sms.response.SmsVarableResponse;
import com.chuanglan.sms.util.ChuangLanSmsUtil;
/**
 * 
 * @author tianyh 
 * @date 2017年4月15日 下午3:26:25
 * @Title: ChuangLanSmsDemo
 * @ClassName: ChuangLanSmsDemo
 * @Description:变量短信发送
 */
public class SmsVarableDemo {

	public static final String charset = "utf-8";
	// 用户平台API账号(非登录账号,示例:N1234567)
	public static String account = "";
	// 用户平台API密码(非登录密码)
	public static String pswd = "";

	public static void main(String[] args) throws UnsupportedEncodingException {
		
		
		//变量短信发送
		String smsVarableRequestUrl = "http://vsms.253.com/msg/variable/json";
		//短信内容
		String msg = "【253云通讯】尊敬的{$var},您好,您的验证码是{$var},{$var}分钟内有效";
		//参数组																
		String params = "187********,王先生,123456,3;130********,李先生,654321,5";
		
		SmsVarableRequest smsVarableRequest=new SmsVarableRequest(account, pswd, msg, params);
		
        String requestJson = JSON.toJSONString(smsVarableRequest);
		
		System.out.println("before request string is: " + requestJson);
		
		String response = ChuangLanSmsUtil.sendSmsByPost(smsVarableRequestUrl, requestJson);
		
		System.out.println("response after request result is : " + response);
		
		SmsVarableResponse smsVarableResponse = JSON.parseObject(response, SmsVarableResponse.class);
		
		System.out.println("response  toString is : " + smsVarableResponse);
		
		
	
	}


}
