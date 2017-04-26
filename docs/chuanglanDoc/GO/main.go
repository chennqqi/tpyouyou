package main
import (
    "net/http"
    "net/url"
    "encoding/json"
    "fmt"
    "bytes"
    "io/ioutil"
    "unsafe"
)
 
type JsonPostSample struct {
 
}
 
func  main() {
    params := make(map[string]interface{})
    params["account"] = ""
    params["password"] = "a.123456"
    params["phone"] = "18721755342"
    params["msg"] =url.QueryEscape("【253云通讯】您好，您的验证码是999999") 
    params["report"] = "false"
    bytesData, err := json.Marshal(params)
    if err != nil {
        fmt.Println(err.Error() )
        return
    }
    reader := bytes.NewReader(bytesData)
    url := "http://vsms.253.com/msg/send/json"
    request, err := http.NewRequest("POST", url, reader)
    if err != nil {
        fmt.Println(err.Error())
        return
    }
    request.Header.Set("Content-Type", "application/json;charset=UTF-8")
    client := http.Client{}
    resp, err := client.Do(request)
    if err != nil {
        fmt.Println(err.Error())
        return
    }
    respBytes, err := ioutil.ReadAll(resp.Body)
    if err != nil {
        fmt.Println(err.Error())
        return
    }
   
    str := (*string)(unsafe.Pointer(&respBytes))
    fmt.Println(*str)
}