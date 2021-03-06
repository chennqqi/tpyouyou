// JavaScript source code

var Global = {};
//ͨ??class??ȡԪ??        
Global.$ = function (ele) {
ele = typeof (ele) == "string" ? document.getElementById(ele) : ele;
return ele;
}
Global.getByClass = function (ele, classN, tag) {
    ele = typeof (ele) == "string" ? document.getElementById(ele) : ele;
    var target = new RegExp("\\b" + classN + "\\b", "g");
    var result = [];
    var tag = tag || "*";
    var all = ele.getElementsByTagName(tag);
    for (var i = 0; i < all.length; i++) {
        if (all[i].className.match(target)) {
            result.push(all[i])
        }
    }
    return result;
}
//????class
Global.addClass = function (ele, classN) {
    var classExp = new RegExp("\\b" + classN + "\\b", "g");
    if (ele.className.match(classExp)) {
        return;
    }
    else {
        ele.className += ((ele.className.length ? " " : "") + classN);
    };
}
//ɾ??class
Global.removeClass = function (ele, classN) {
    var classExp = new RegExp("\\b" + classN + "\\b", "g");
    if (ele.className.match(classExp)) {
        ele.className = ele.className.replace(classExp, "")
    }
    else {
        return;
    };
}
//??ȡ??ʽ????
Global.getStyle = function (ele, styleName) {
    return parseFloat(ele.currentStyle ? ele.currentStyle[styleName] : getComputedStyle(ele, null)[styleName]);
}
Global.getX = function (ele) {
    var x = 0;
    while (ele) {
        x += ele.offsetLeft;
        ele = ele.offsetParent;
    }
    return (x)
}
Global.getY = function (ele) {
    var y = 0;
    while (ele) {
        y += ele.offsetTop;
        ele = ele.offsetParent;
    }
    return (y)
}

//ajax
Global.ajax = function (url, fn) {
    if (window.XMLHttpRequest) {
        var XMLHttp = new XMLHttpRequest();
    }
    else {
        var XMLHttp = new ActiveXObject("Microsoft,XMLHTTP");
    }
    XMLHttp.open("GET", url, true);
    XMLHttp.send();
    XMLHttp.onreadystatechange = function () {
        if (XMLHttp.readyState == 4 && XMLHttp.status == 200) {
            fn(XMLHttp.responseText);
        }
    }
}
//cookie
Global.cookie = {};
//????cookie
Global.cookie.set = function (name, val, time) {
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + time);
    document.cookie = name + "=" + encodeURIComponent(val) + (time ? "" : ";expires=" + exdate.toString);
}
//??ȡcookie
Global.cookie.get = function (name) {
    var cell = decodeURIComponent(document.cookie).split(";");
    for (var i = 0; i < cell.length; i++) {
        var cookie = cell[i].split("=");
        if (name == cookie[0]) {
            return cookie[1];
        }
    }
}
//?Ƴ?cookie
Global.cookie.remove = function (name) {
    Global.cookie.set(name, null, -1);
}
//?˶?????
Global.move = function(ele, json, time, type, callFn) {
    clearTimeout(ele.timer);
    for (var att in json) {
        var frames = time / 20;
        var curFrame = 1;
        var start = Global.getStyle(ele, att);
        function run(curFrame, frames, start) {
            if (curFrame < frames) {
                curFrame += 1;
                if (att == "opacity") {
                    ele.style.opacity = start + type(curFrame, frames, (json[att] - start));
                    ele.style.filter = "(opacity=" + (start + type(curFrame, frames, (json[att] - start))) * 100 + ")";
                }
                else {
                    ele.style[att] = start + type(curFrame, frames, (json[att] - start)) + "px";
                }
                ele.timer = setTimeout(function () {
                    run(curFrame, frames, start);
                }, 20);
            }
            else {
                if (att == "opacity") {
                    ele.style.opacity = json[att] + "px";
                    ele.style.filter = "(opacity=" + json[att] * 100 + ")";
                }
                else {
                    ele.style[att] = json[att] + "px";
                    if (callFn) {
                        callFn();
                    }
                }
            }
        }
        run(curFrame, frames, start);
    }
}//?˶?????
//?????˶?
Global.move.linear=function(curFrame, frames, target) {
    return curFrame * target / frames;
}
//???η?
Global.move.twice= function(curFrame, frames, target) {
    return (curFrame /= frames) * curFrame * target;
}
//???η?
Global.move.three= function(curFrame, frames, target) {
    return (curFrame /= frames) * curFrame * curFrame * target;
}
//sin
Global.move.sin1= function(curFrame, frames, target) {
    return Math.sin(curFrame / frames * Math.PI * 3 / 4) * target * Math.sqrt(2);
}
Global.move.sin2= function(curFrame, frames, target) {
    return (1 - Math.cos(curFrame / frames * Math.PI / 2)) * target;
}
//????
Global.move.bounce= function(curFrame, frames, target) {
    {
        if ((curFrame /= frames) < (1 / 2.75)) {
            return target * (7.5625 * curFrame * curFrame);
        } else if (curFrame < (2 / 2.75)) {
            return target * (7.5625 * (curFrame -= (1.5 / 2.75)) * curFrame + 0.75);
        } else if (curFrame < (2.5 / 2.75)) {
            return target * (7.5625 * (curFrame -= (2.25 / 2.75)) * curFrame + 0.9375);
        } else {
            return target * (7.5625 * (curFrame -= (2.625 / 2.75)) * curFrame + 0.984375);
        }
    }
}
Global.addEvent=function(ele,eventType,handle){
	if(ele.addEventListener){
	ele.addEventListener(eventType,handle);
	}
	else{
		ele.attachEvent("on"+eventType,handle)
		}
	}
