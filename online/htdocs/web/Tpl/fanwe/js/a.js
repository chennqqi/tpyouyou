 //选项卡
        function tab(butPar,butClassN, content,contentClassN) {
            var _this = this;
            this.but = Global.getByClass(butPar, butClassN);
            this.div = Global.getByClass(content, contentClassN);
            this.radio = [];
            for (var i = 0; i < this.but.length; i++) {
                this.but[i].index = i;
                this.but[i].onclick = function () {
                    _this.change(this);
                }
            }
        }
        tab.prototype.change = function (ele) {
            for (var i = 0; i < this.but.length; i++) {
                Global.removeClass(this.but[i], "active");
                this.div[i].style.display = "none";
            }
            Global.addClass(ele,"active")
            this.div[ele.index].style.display = "block";
        }
        //选择单双程
        function chooseWay(ele) {
                var _this = this;
                this.input = ele.getElementsByTagName("input");
                radio = [];
                this.goBox = Global.getByClass(ele, "go")[0];
                this.backBox = Global.getByClass(ele, "return")[0];
                for (var i = 0, radioLength = this.input.length; i < radioLength; i++) {
                    if (this.input[i].getAttribute("type") == "radio") {
                        radio.push(this.input[i]);
                    }
                }
                this.temp = "";
                this.radio1 = radio[0];
                this.radio2 = radio[1];
                this.radio1.onclick = function () {
                    _this.backBox.disabled = "disabled";
                    _this.temp = _this.backBox.value;
                    _this.backBox.value = "";
                    Global.addClass(_this.backBox.parentNode, "disabled");
                }
                this.radio2.onclick = function () {
                    _this.backBox.removeAttribute("disabled");
                    Global.removeClass(_this.backBox.parentNode, "disabled");
                    _this.backBox.value = _this.temp || _this.backBox.value;
                    if(_this.backBox.value==""){
                        _this.goTime=_this.goBox.value.split("-");
                        _this.value=new Date(_this.goTime[0],_this.goTime[1],_this.goTime[2]-0+3);
                        _this.backBox.value=_this.value.getFullYear()+"-"+(_this.value.getMonth()<10?"0"+_this.value.getMonth():_this.value.getMonth())+"-"+(_this.value.getDate()<10?"0"+_this.value.getDate():_this.value.getDate());
                    }
                }
            }
        //弹出层
        var focusEle = null;
        function layer(clickEle, layerEle) {
            var _this = this;
            this.clickEle = clickEle;
            this.layerEle = layerEle;
            this.clickEle.onfocus = function () {
                _this.focusFn(this);
            }
            this.layerEleCell = this.layerEle.getElementsByTagName("a");
            for (var i = 0; i < this.layerEleCell.length; i++) {
                this.layerEleCell[i].onclick = function () {
                    focusEle.value = this.innerHTML;
                    _this.hideLayer();
                }
            }
            document.onclick = function () {
                _this.hideLayer();
            }
            this.clickEle.onclick = function (e) {
                var oevent = e || window.event;
                oevent.cancelBubble = true;
                if(oevent.stopPropagation){
                oevent.stopPropagation();}
                return false;
            }
            this.close = Global.getByClass(this.layerEle,"close")[0];
            this.close.onclick = function () {
                _this.hideLayer();
            }
            this.layerEle.onclick=function(e) {
                var oevent = e || window.event;
                oevent.cancelBubble = true;
                if(oevent.stopPropagation){
                oevent.stopPropagation();}
                return false;
            }
        }
        layer.prototype.focusFn = function (ele) {
            focusEle = ele;
            this.layerEle.style.top = Global.getY(ele) + ele.offsetHeight + 3 + "px";
            this.layerEle.style.left = Global.getX(ele) + "px";
            Global.getByClass(this.layerEle, "cityIndex")[0].click();
        }
        layer.prototype.chooseValue = function (ele) {
            this.clickEle.value = ele.innerHTML;
        }
        layer.prototype.hideLayer = function () {
            this.layerEle.style.display = "none";
        }
        //显示特定城市
        function showSpecialCity(ele, index,all) {
            var _this = this;
            this.ele = ele;
            this.index = index;
            this.all =all;
            try {this.ele.addEventListener("click", function () { _this.show() });}
            catch(e){
                this.ele.attachEvent("onclick", function () { _this.show() });
            }
            try {this.ele.addEventListener("blur", function () {_this.check(this) });}
            catch(e){
                this.ele.attachEvent("onblur", function () { _this.check(_this.ele);});
            }
        }
        showSpecialCity.prototype.show = function () {
            //for (var i = 0; i > this.all.length; i++) {
               // this.all[i].style.display = "none";
            //}
            this.all[this.index].style.display = "block";
            document.getElementById("cityWrap").style.display = "block";
        }
        showSpecialCity.prototype.check=function(ele){
            var reg=/[\!\@\#\$\%\^\&\*\,\.\;\'\:\?\"\<\>\(\)\_\+\\\/\`\~\-\=\{\}\s￥《》？、！，；：。“”‘’0-9]/g;
              if(ele.value){
                ele.value=ele.value.replace(reg,"");
              }
        }
        
        //交换出发城市和到达城市
        function exchange(text1,text2,button) {
            var _this = this;
            this.text1 = text1;
            this.text2 = text2;
            button.onclick = function () {
                _this.change();
                return false;
            }
        }
        exchange.prototype.change = function () {
            var temp = this.text1.value;
            this.text1.value = this.text2.value;
            this.text2.value = temp;
        }
        
