#!/usr/local/bin/python
#-*- coding:utf-8 -*-
# Author: jacky
# Time: 14-2-22 下午11:48
# Desc: 短信http接口的python代码调用示例
import httplib
import urllib
import json
#服务地址
host = "vsms.253.com"

#端口号
port = 80

#版本号
version = "v1.1"

#查账户信息的URI
balance_get_uri = "/msg/balance/json"

#智能匹配模版短信接口的URI
sms_send_uri = "/msg/send/json"

#创蓝账号
account  = "N9994784"

#创蓝密码
password = "Jiubugaosuni10"

def get_user_balance():
    """
    取账户余额
    """
    params = {'account': account, 'password' : password}
    params=json.dumps(params)
   
    headers = {"Content-type": "application/json"}
    conn = httplib.HTTPConnection(host, port=port)
    conn.request('POST', balance_get_uri, params, headers)
    response = conn.getresponse()
    response_str = response.read()
    conn.close()
    return response_str

def send_sms(text, phone):
    """
    能用接口发短信
    """
 
    params = {'account': account, 'password' : password, 'msg': urllib.quote(text), 'phone':phone, 'report' : 'false'}
    params=json.dumps(params)
   
    headers = {"Content-type": "application/json"}
    conn = httplib.HTTPConnection(host, port=port, timeout=30)
    conn.request("POST", sms_send_uri, params, headers)
    response = conn.getresponse()
    response_str = response.read()
    conn.close()
    return response_str 

if __name__ == '__main__':

    phone = "18721755342"
    text = "【253云通讯】您的验证码是1234"

    #查账户余额
    print(get_user_balance())

    #调用智能匹配模版接口发短信
    print(send_sms(text, phone))
