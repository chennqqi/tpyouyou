(function($){
	
	var Slider_item = function(){
		var self = this;
		this.dom = null;   //当前元素的文档对象
		this.title = null; //显示的标题
		this.idx = null;  //标记顺序
		this.ctl = null;  //控制点dom
		
		this.show = function(){
			self.dom.show();
			self.dom.css("opacity",0);
			self.dom.animate({
				opacity: 1
			}, { duration: 500,queue:false });
			//self.dom.fadeIn();	
			title_box.html(self.title);
		}; //控制显示
		this.hide = function(){
			//self.dom.fadeOut();
			self.dom.animate({
				opacity: 0
			}, { duration: 500,queue:false,complete:function(){
				self.dom.hide();
			} });
		}; //控制隐藏
		
		this.init = function(dom,ctl,idx){
			self.dom = $(dom);	
			self.title = $(dom).attr("title");		
			self.idx = idx;
			self.ctl = ctl;
		};
	};
	
	var Slider_ctl = function(){
		var self = this;
		var slider_obj = null; //对应的切换元素
		var dom = null; //自身的圆点
		var idx = null; //顺序
		this.init = function(slider_obj,dom,idx){
			self.slider_obj = slider_obj;
			self.dom = dom;
			self.idx = idx;
		};
		
		this.click = function(){
			slider_items[current_idx].hide();
			slider_ctls[current_idx].dom.removeClass("current");
			slider_items[self.idx].show();
			slider_ctls[self.idx].dom.addClass("current");
			current_idx = self.idx;
			//current_dom.hide();
			//self.slider_obj.show();			
		};
	};
	
	var title_box = null;
	var slider_items = new Array();
	var slider_ctls = new Array();
	var current_idx = null;
	
	
	$.fn.image_slider = function(timespan){

		var slider_outer = $(this);
		var doms = slider_outer.find("a");
		var ctl_box = $("<div class='ctl_box'></div>");
		slider_outer.append(ctl_box); //添加控制点的外框		
		
		title_box = $("<span class='title_box'></span>");
		slider_outer.append(title_box); 
		
//		slider_outer.append($("<span class='title_box_bg'></span>")); 
		
		for(i=0;i<doms.length;i++)
		{
			var dom = $(doms[i]); //当前元素			
			var dot = $("<span class='dot' rel='"+i+"'>"+(i+1)+"</span>");
			ctl_box.append(dot);
			
			var slider_item = new Slider_item();
			slider_item.init(dom, dot,i);
			if(i==0)
			{
				title_box.html(dom.attr("title"));
				dom.show();
				dot.addClass("current");
				current_idx = i;
			}
			else
			{
				dom.hide();
			}
			slider_items.push(slider_item);
			
			var slider_ctl = new Slider_ctl();
			slider_ctl.init(slider_item, dot, i);
			slider_ctls.push(slider_ctl);

		}
		
		$(slider_outer).find(".ctl_box .dot").live("click",function(){
			slider_ctls[$(this).attr("rel")].click();
		});
		
		//定时任务
		var slider_timer = null;
		
		//定义定时器委拖
		var timeHandler = function(){
			var nxt_idx = (current_idx+1)<doms.length?(current_idx+1):0;
			
			slider_ctls[nxt_idx].click();
		};
		
		if(slider_timer==null)
		slider_timer = setInterval(timeHandler,timespan);
		
		slider_outer.hover(function(){
			clearInterval(slider_timer);
			slider_timer = null;			
		},function(){
			if(slider_timer==null)
			slider_timer = setInterval(timeHandler,timespan);
		});
		
	};
})(jQuery);