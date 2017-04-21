function initEnv() {
	$("body").append(DWZ.frag["dwzFrag"]);

	if ( $.browser.msie && /6.0/.test(navigator.userAgent) ) {
		try {
			document.execCommand("BackgroundImageCache", false, true);
		}catch(e){}
	}
	//清理浏览器内存,只对IE起效
	if ($.browser.msie) {
		window.setInterval("CollectGarbage();", 10000);
	}

	$(window).resize(function(){
		initLayout();
		$(this).trigger(DWZ.eventType.resizeGrid);
	});

	var ajaxbg = $("#background,#progressBar");
	ajaxbg.hide();
	$(document).ajaxStart(function(){
		ajaxbg.show();
	}).ajaxStop(function(){
		ajaxbg.hide();
	});
	
	$("#leftside").jBar({minW:150, maxW:700});
	
	if ($.taskBar) $.taskBar.init();
	navTab.init();
	if ($.fn.switchEnv) $("#switchEnvBox").switchEnv();
	if ($.fn.navMenu) $("#navMenu").navMenu();
		
	setTimeout(function(){
		initLayout();
		initUI();
		
		// navTab styles
		var jTabsPH = $("div.tabsPageHeader");
		jTabsPH.find(".tabsLeft").hoverClass("tabsLeftHover");
		jTabsPH.find(".tabsRight").hoverClass("tabsRightHover");
		jTabsPH.find(".tabsMore").hoverClass("tabsMoreHover");
	
	}, 10);

}
function initLayout(){
	var iContentW = $(window).width() - (DWZ.ui.sbar ? $("#sidebar").width() + 10 : 34) - 5;
	var iContentH = $(window).height() - $("#header").height() - 34;

	$("#container").width(iContentW);
	$("#container .tabsPageContent").height(iContentH - 34).find("[layoutH]").layoutH();
	$("#sidebar, #sidebar_s .collapse, #splitBar, #splitBarProxy").height(iContentH - 5);
	$("#taskbar").css({top: iContentH + $("#header").height() + 5, width:$(window).width()});
}

function initUI(_box){
	var $p = $(_box || document);

	$("div.panel", $p).jPanel();

	//tables
	$("table.table", $p).jTable();
	
	// css tables
	$('table.list', $p).cssTable();

	//auto bind tabs
	$("div.tabs", $p).each(function(){
		var $this = $(this);
		var options = {};
		options.currentIndex = $this.attr("currentIndex") || 0;
		options.eventType = $this.attr("eventType") || "click";
		$this.tabs(options);
	});

	$("ul.tree", $p).jTree();
	$('div.accordion', $p).each(function(){
		var $this = $(this);
		$this.accordion({fillSpace:$this.attr("fillSpace"),alwaysOpen:true,active:0});
	});

	$(":button.checkboxCtrl, :checkbox.checkboxCtrl", $p).checkboxCtrl($p);
	
	if ($.fn.combox) $("select.combox",$p).combox();
	
	if ($.fn.xheditor) {
		$("textarea.editor", $p).each(function(){
			var $this = $(this);
			var op = {html5Upload:false, skin: 'vista',tools: $this.attr("tools") || 'full'};
			var upAttrs = [
				["upLinkUrl","upLinkExt","zip,rar,txt"],
				["upImgUrl","upImgExt","jpg,jpeg,gif,png"],
				["upFlashUrl","upFlashExt","swf"],
				["upMediaUrl","upMediaExt","avi"]
			];
			
			$(upAttrs).each(function(i){
				var urlAttr = upAttrs[i][0];
				var extAttr = upAttrs[i][1];
				
				if ($this.attr(urlAttr)) {
					op[urlAttr] = $this.attr(urlAttr);
					op[extAttr] = $this.attr(extAttr) || upAttrs[i][2];
				}
			});
			
			$this.xheditor(op);
		});
	}
	
	if ($.fn.uploadify) {
		$(":file[uploaderOption]", $p).each(function(){
			var $this = $(this);
			var options = {
				fileObjName: $this.attr("name") || "file",
				auto: true,
				multi: true,
				onUploadError: uploadifyError
			};
			
			var uploaderOption = DWZ.jsonEval($this.attr("uploaderOption"));
			$.extend(options, uploaderOption);

			DWZ.debug("uploaderOption: "+DWZ.obj2str(uploaderOption));
			
			$this.uploadify(options);
		});
	}
	
	// init styles
	$("input[type=text], input[type=password], textarea", $p).addClass("textInput").focusClass("focus");

	$("input[readonly], textarea[readonly]", $p).addClass("readonly");
	$("input[disabled=true], textarea[disabled=true]", $p).addClass("disabled");

	$("input[type=text]", $p).not("div.tabs input[type=text]", $p).filter("[alt]").inputAlert();

	//Grid ToolBar
	$("div.panelBar li, div.panelBar", $p).hoverClass("hover");

	//Button
	$("div.button", $p).hoverClass("buttonHover");
	$("div.buttonActive", $p).hoverClass("buttonActiveHover");
	
	//tabsPageHeader
	$("div.tabsHeader li, div.tabsPageHeader li, div.accordionHeader, div.accordion", $p).hoverClass("hover");

	//validate form
	$("form.required-validate", $p).each(function(){
		var $form = $(this);
		$form.validate({
			onsubmit: false,
			focusInvalid: false,
			focusCleanup: true,
			errorElement: "span",
			ignore:".ignore",
			invalidHandler: function(form, validator) {
				var errors = validator.numberOfInvalids();
				if (errors) {
					var message = DWZ.msg("validateFormError",[errors]);
					alertMsg.error(message);
				} 
			}
		});
		
		$form.find('input[customvalid]').each(function(){
			var $input = $(this);
			$input.rules("add", {
				customvalid: $input.attr("customvalid")
			})
		});
	});

	if ($.fn.datepicker){
		$('input.date', $p).each(function(){
			var $this = $(this);
			var opts = {};
			if ($this.attr("dateFmt")) opts.pattern = $this.attr("dateFmt");
			if ($this.attr("minDate")) opts.minDate = $this.attr("minDate");
			if ($this.attr("maxDate")) opts.maxDate = $this.attr("maxDate");
			if ($this.attr("mmStep")) opts.mmStep = $this.attr("mmStep");
			if ($this.attr("ssStep")) opts.ssStep = $this.attr("ssStep");
			$this.datepicker(opts);
		});
	}

	// navTab
	$("a[target=navTab]", $p).each(function(){
		$(this).click(function(event){
			var $this = $(this);
			var title = $this.attr("title") || $this.text();
			var tabid = $this.attr("rel") || "_blank";
			var fresh = eval($this.attr("fresh") || "true");
			var external = eval($this.attr("external") || "false");
			var url = unescape($this.attr("href")).replaceTmById($(event.target).parents(".unitBox:first"));
			DWZ.debug(url);
			if (!url.isFinishedTm()) {
				alertMsg.error($this.attr("warn") || DWZ.msg("alertSelectMsg"));
				return false;
			}
			navTab.openTab(tabid, url,{title:title, fresh:fresh, external:external});

			event.preventDefault();
		});
	});
	
	//dialogs
	$("a[target=dialog]", $p).each(function(){
		$(this).click(function(event){
			var $this = $(this);
			var title = $this.attr("title") || $this.text();
			var rel = $this.attr("rel") || "_blank";
			var options = {};
			var w = $this.attr("width");
			var h = $this.attr("height");
			if (w) options.width = w;
			if (h) options.height = h;
			options.max = eval($this.attr("max") || "false");
			options.mask = eval($this.attr("mask") || "false");
			options.maxable = eval($this.attr("maxable") || "true");
			options.minable = eval($this.attr("minable") || "true");
			options.fresh = eval($this.attr("fresh") || "true");
			options.resizable = eval($this.attr("resizable") || "true");
			options.drawable = eval($this.attr("drawable") || "true");
			options.close = eval($this.attr("close") || "");
			options.param = $this.attr("param") || "";

			var url = unescape($this.attr("href")).replaceTmById($(event.target).parents(".unitBox:first"));
			DWZ.debug(url);
			if (!url.isFinishedTm()) {
				alertMsg.error($this.attr("warn") || DWZ.msg("alertSelectMsg"));
				return false;
			}
			$.pdialog.open(url, rel, title, options);
			
			return false;
		});
	});
	$("a[target=ajax]", $p).each(function(){
		$(this).click(function(event){
			var $this = $(this);
			var rel = $this.attr("rel");
			if (rel) {
				var $rel = $("#"+rel);
				$rel.loadUrl($this.attr("href"), {}, function(){
					$rel.find("[layoutH]").layoutH();
				});
			}

			event.preventDefault();
		});
	});
	
	$("div.pagination", $p).each(function(){
		var $this = $(this);
		$this.pagination({
			targetType:$this.attr("targetType"),
			rel:$this.attr("rel"),
			totalCount:$this.attr("totalCount"),
			numPerPage:$this.attr("numPerPage"),
			pageNumShown:$this.attr("pageNumShown"),
			currentPage:$this.attr("currentPage")
		});
	});

	if ($.fn.sortDrag) $("div.sortDrag", $p).sortDrag();

	// dwz.ajax.js
	if ($.fn.ajaxTodo) $("a[target=ajaxTodo]", $p).ajaxTodo();
	if ($.fn.dwzExport) $("a[target=dwzExport]", $p).dwzExport();

    if ($.fn.lookupselect) $("a[lookupGroupSelect]", $p).lookupselect();  //增加 by hc ，关于带已选项的弹出查询
	if ($.fn.lookup) $("a[lookupGroup]", $p).lookup();
	if ($.fn.multLookup) $("[multLookup]:button", $p).multLookup();
	if ($.fn.multLookupSelected) $("[multLookupSelected]:button", $p).multLookupSelected(); //增加 by hc 
	if ($.fn.suggest) $("input[suggestFields]", $p).suggest();
	if ($.fn.itemDetail) $("table.itemDetail", $p).itemDetail();
	if ($.fn.selectedTodo) $("a[target=selectedTodo]", $p).selectedTodo();
	if ($.fn.pagerForm) $("form[rel=pagerForm]", $p).pagerForm({parentBox:$p});

	// 这里放其他第三方jQuery插件...
	
	//关于ke编辑器
   // KindEditor.ready(function(K) {
       var K = KindEditor;
        var editor = K.create('textarea.ketext', {
            allowFileManager : true,
            uploadJson : FILE_UPLOAD_URL,
            fileManagerJson : FILE_MANAGE_URL,
            emoticonsPath:EMOT_URL,
            afterBlur: function(){this.sync();}, //兼容jq的提交，失去焦点时同步表单值
            items : [
                                'source', '|', 'undo', 'redo', '|', 'preview', 'print', 'template', 'cut', 'copy', 'paste',
                                'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
                                'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
                                'superscript', 'clearhtml', 'quickformat', 'selectall', '|', 'fullscreen', '/',
                                'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
                                'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'image','multiimage','flash',
                                'table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
                                'anchor', 'link', 'unlink'
                            ]
        });         
    //});
    
    //关于ke编辑器的图片控件
    //KindEditor.ready(function(K) {
           var ieditor = K.editor({
               allowFileManager : true,
                uploadJson : FILE_UPLOAD_URL,
               fileManagerJson : FILE_MANAGE_URL ,
               imageSizeLimit:MAX_FILE_SIZE               
            });
			K('.keimg').unbind("click");
            K('.keimg').click(function() {
                var node = K(this);
                var dom =$(node).parent().parent().parent().parent();
                ieditor.loadPlugin('image', function() {
                       ieditor.plugin.imageDialog({
                       // imageUrl : K("#keimg_h_"+$(this).attr("rel")).val(),
                        imageUrl:dom.find("#keimg_h_"+node.attr("rel")).val(),
                        clickFn : function(url, title, width, height, border, align) {                            
                            dom.find("#keimg_a_"+node.attr("rel")).removeClass("hide");
                            dom.find("#keimg_a_"+node.attr("rel")).attr("href",url),
                            dom.find("#keimg_m_"+node.attr("rel")).attr("src",url),
                            dom.find("#keimg_h_"+node.attr("rel")).val(url),
                            ieditor.hideDialog();
                        }
                    });
                });
            });
			/**
			 * 批量上传图片
			 */
			K('.kemukeimg').unbind("click");
			K('.kemukeimg').click(function() {
                var node = K(this);
                ieditor.loadPlugin('multiimage', function() {                                         
                        ieditor.plugin.multiImageDialog({
						clickFn : function(urlList) {
							K('.kemuimg_d').unbind("click");
							var rel = node.attr("rel");
							K.each(urlList, function(i, data) {
								var html ="<div style='float:left; height:80px; padding:10px 5px;'>" ;
								html +='	<input type="hidden" value="'+data.url+'" name="'+rel+'[]" />';
								html +='	<a href="'+data.url+'" target="_blank"><img src="'+data.url+'" width=60 height=60 style="float:left; border:#ccc solid 1px" /></a><br>';
								html +='	<div style="clear:both;height:5px;overflow:hidden;overflow:visible"></div>';
								html +='	<div class="buttonActive">';
								html +='		<div class="buttonContent">';
								html +='			<button type="button" class="kemuimg_d">删除图片</button>';
								html +='		</div>';
								html +='	</div>';
								html +='</div>';
								
	                            node.parent().parent().parent().append(html);
							});
						
							K('.kemuimg_d').click(function() {
				                 K(this).parent().parent().parent().remove();
				            });
							
                            ieditor.hideDialog();
                        }
                    });
                });
            });
			/**
			 * 服务器选择图片
			 */
			K('.kemufimg').unbind("click");
			K(".kemufimg").click(function() {
                var node = K(this);
                ieditor.loadPlugin('filemanager', function() {
                    ieditor.plugin.filemanagerDialog({
						viewType : 'VIEW',
						dirName : 'image',
						clickFn : function(url, title) {
							K('.kemuimg_d').unbind("click");
							var rel = node.attr("rel");
							var html ="<div style='float:left; height:80px; padding:10px 5px;'>" ;
								html +='	<input type="hidden" value="'+url+'" name="'+rel+'[]" />';
								html +='	<a href="'+url+'" target="_blank"><img src="'+url+'" width=60 height=60 style="float:left; border:#ccc solid 1px" /></a><br>';
								html +='	<div style="clear:both;height:5px;overflow:hidden;overflow:visible"></div>';
								html +='	<div class="buttonActive">';
								html +='		<div class="buttonContent">';
								html +='			<button type="button" class="kemuimg_d">删除图片</button>';
								html +='		</div>';
								html +='	</div>';
								html +='</div>';
								
	                        node.parent().parent().parent().append(html);
							
							K('.kemuimg_d').click(function() {
				                 K(this).parent().parent().parent().remove();
				            });
							
							ieditor.hideDialog();
						}
					});
				});
			});
			
			/**
			 * 删除单图
			 */
            K('.keimg_d').click(function() {
                var node = K(this);
                var dom =$(node).parent().parent().parent().parent();
                dom.find("#keimg_a_"+node.attr("rel")).attr("href","");
                dom.find("#keimg_m_"+node.attr("rel")).attr("src","");
                dom.find("#keimg_h_"+node.attr("rel")).val("");
                dom.find("#keimg_a_"+node.attr("rel")).addClass("hide");
            });
			
			/**
			 * 删除多图中的一张
			 */
			K('.kemuimg_d').click(function() {
                 K(this).parent().parent().parent().remove();
            });
			
			/**
			 * 地图插件
			 */
			K('.kemapbtn').unbind("click");
			 K('.kemapbtn').click(function() {
                var node = $(this);
				 ieditor.loadPlugin(node.attr("map"), function() {
				 	var self = this, name = node.attr("map"), lang = self.lang(name + '.');
					var xpoint = node.parent().parent().parent().find("#xpoint").val(), ypoint = node.parent().parent().parent().find("#ypoint").val();
					var mapWidth = K.undef(self.mapWidth, 558);
					var mapHeight = K.undef(self.mapHeight, 360);
						var html ="";
						if(node.attr("map") == "map"){
							html = ['<div style="padding:10px 20px;">',
								'<div class="ke-dialog-row">',
								lang.address + ' <input id="kindeditor_plugin_map_address" name="address" class="ke-input-text" value="" style="width:200px;" /> ',
								'<span class="ke-button-common ke-button-outer">',
								'<input type="button" name="searchBtn" class="ke-button-common ke-button" value="' + lang.search + '" />',
								'</span>',
								'</div>',
								'<div class="ke-map" style="width:558px;height:360px;"></div>',
								'<input type="hidden" value="'+xpoint+'" id="kindeditor_plugin_map_xpoint" />',
								'<input type="hidden" value="'+ypoint+'" id="kindeditor_plugin_map_ypoint" />',
								'</div>'].join('');
						}
						else{
							
							html = ['<div style="padding:10px 20px;">',
								'<div class="ke-header">',
								// left start
								'<div class="ke-left">',
								lang.address + ' <input id="kindeditor_plugin_map_address" name="address" class="ke-input-text" value="" style="width:200px;" /> ',
								'<span class="ke-button-common ke-button-outer">',
								'<input type="button" name="searchBtn" class="ke-button-common ke-button" value="' + lang.search + '" />',
								'</span>',
								'</div>',
								'<div class="ke-clearfix"></div>',
								'</div>',
								'<div class="ke-map" style="width:' + mapWidth + 'px;height:' + mapHeight + 'px;"></div>',
								'<input type="hidden" value="'+xpoint+'" id="kindeditor_plugin_map_xpoint" />',
								'<input type="hidden" value="'+ypoint+'" id="kindeditor_plugin_map_ypoint" />',
								'</div>'].join('');
						}
						var dialog = self.createDialog({
							name : name,
							width : mapWidth + 42,
							title : self.lang(name),
							body : html,
							yesBtn : {
								name : self.lang('yes'),
								click : function(e) {
									var map = win.map;
									if(node.attr("map") == "baidumap"){
										var centerObj = map.getCenter();
										var center = centerObj.lng + ',' + centerObj.lat;
										node.parent().parent().parent().find("#xpoint").val(centerObj.lat);
										node.parent().parent().parent().find("#ypoint").val(centerObj.lng);
										
										var zoom = map.getZoom();
										var url ="";
										 url = 'http://api.map.baidu.com/staticimage';
											url += '?center=' + encodeURIComponent(center);
											url += '&zoom=' + encodeURIComponent(zoom);
											url += '&width=' + node.attr("w");
											url += '&height=' + node.attr("h");
											url += '&markers=' + encodeURIComponent(center);
											url += '&markerStyles=' + encodeURIComponent('l,A');
											
											node.parent().parent().parent().find("#mapimg").html("<img src='"+url+"' width='"+node.attr("w")+"' height='"+node.attr("h")+"' />");
									}
									else{
										center = map.getCenter().lat() + ',' + map.getCenter().lng(),
										zoom = map.getZoom(),
										maptype = map.getMapTypeId(),
										url = 'http://maps.googleapis.com/maps/api/staticmap';
										url += '?center=' + encodeURIComponent(center);
										url += '&zoom=' + encodeURIComponent(zoom);
										url += '&size='+node.attr("w")+'x'+node.attr("h");
										url += '&maptype=' + encodeURIComponent(maptype);
										url += '&markers=' + encodeURIComponent(center);
										url += '&language=' + self.langType;
										url += '&sensor=false';
										node.parent().parent().parent().find("#mapimg").html("<img src='"+url+"' width='"+node.attr("w")+"' height='"+node.attr("h")+"' />");
									
										node.parent().parent().parent().find("#xpoint").val(map.getCenter().lat());
										node.parent().parent().parent().find("#ypoint").val(map.getCenter().lng());
									}
									self.hideDialog().focus();
								}
							}
						});
						var div = dialog.div,
							addressBox = K('[name="address"]', div),
							searchBtn = K('[name="searchBtn"]', div),
							win;
						var iframe = K('<iframe class="ke-textarea" frameborder="0" src="' + self.pluginsPath + node.attr("map") + '/map.html" style="width:' + mapWidth + 'px;height:' + mapHeight + 'px;"></iframe>');
						function ready() {
							win = iframe[0].contentWindow;
						}
						iframe.bind('load', function() {
							iframe.unbind('load');
							if (K.IE) {
								ready();
							} else {
								setTimeout(ready, 0);
							}
						});
						K('.ke-map', div).replaceWith(iframe);
						// search map
						searchBtn.click(function() {
							win.search(addressBox.val());
						});
				});
			});
            
			
            
       // });
    
   $.each($("input.uploadfilebtn[is_bind!=true]"),function(i,n){
       var btn = $(n);
       btn.attr("is_bind",true);
        var origin_tip_text = "";
        var uploadbutton = KindEditor.uploadbutton({
            button : btn,
            width:"54px",
            fieldName : 'UPLOAD_FILE',
            url : ATTACHMENT_UPLOAD_URL,
            afterUpload : function(data) {
                if (data.error === 0) {
                    $('#progressBar').html(origin_tip_text);
                    $('#background,#progressBar').hide();
                    alertMsg.correct('上传成功');
                    $(btn).parent().find(".filebox").val(data.url);
                    $(btn).parent().find(".viewbox").show();
                    $(btn).parent().find(".delbox").show();
                    $(btn).parent().find(".view").attr('href',data.url);
                } else {
                    $('#progressBar').html(origin_tip_text);
                    $('#background,#progressBar').hide();
                    alertMsg.error(data.message);
                }
            },
            afterError : function(str) {
                $('#progressBar').html(origin_tip_text);
                $('#background,#progressBar').hide();
                alertMsg.error('自定义错误信息: ' + str);
   
            }
        });
        uploadbutton.fileBox.change(function(e) {
            uploadbutton.submit();
            origin_tip_text = $('#progressBar').html();
            $('#progressBar').html('正在上传...');
            $('#background,#progressBar').show();
        });
   });
   
   $.each($("input.uploadflashbtn[is_bind!=true]"),function(i,n){
       var btn = $(n);
       btn.attr("is_bind",true);
        var origin_tip_text = "";
        var uploadbutton = KindEditor.uploadbutton({
            button : btn,
            width:"54px",
            fieldName : 'UPLOAD_FLASH',
            url : FLASH_UPLOAD_URL,
            afterUpload : function(data) {
                if (data.error === 0) {
                    $('#progressBar').html(origin_tip_text);
                    $('#background,#progressBar').hide();
                    alertMsg.correct('上传成功');
                    $(btn).parent().find(".filebox").val(data.url);
                    $(btn).parent().find(".viewbox").show();
                    $(btn).parent().find(".delbox").show();
                    $(btn).parent().find(".view").attr('href',data.url);
                } else {
                    $('#progressBar').html(origin_tip_text);
                    $('#background,#progressBar').hide();
                    alertMsg.error(data.message);
                }
            },
            afterError : function(str) {
                $('#progressBar').html(origin_tip_text);
                $('#background,#progressBar').hide();
                alertMsg.error('自定义错误信息: ' + str);
   
            }
        });
        uploadbutton.fileBox.change(function(e) {
            uploadbutton.submit();
            origin_tip_text = $('#progressBar').html();
            $('#progressBar').html('正在上传...');
            $('#background,#progressBar').show();
        });
   });
   
    $.each($("input.uploadvideobtn[is_bind!=true]"),function(i,n){
       var btn = $(n);
       btn.attr("is_bind",true);
        var origin_tip_text = "";
        var uploadbutton = KindEditor.uploadbutton({
            button : btn,
            width:"54px",
            fieldName : 'UPLOAD_VIDEO',
            url : VIDEO_UPLOAD_URL,
            afterUpload : function(data) {
                if (data.error === 0) {
                    $('#progressBar').html(origin_tip_text);
                    $('#background,#progressBar').hide();
                    alertMsg.correct('上传成功');
                    $(btn).parent().find(".filebox").val(data.url);
                    $(btn).parent().find(".viewbox").show();
                    $(btn).parent().find(".delbox").show();
                    $(btn).parent().find(".view").attr('href',data.url);
                } else {
                    $('#progressBar').html(origin_tip_text);
                    $('#background,#progressBar').hide();
                    alertMsg.error(data.message);
                }
            },
            afterError : function(str) {
                $('#progressBar').html(origin_tip_text);
                $('#background,#progressBar').hide();
                alertMsg.error('自定义错误信息: ' + str);
   
            }
        });
        uploadbutton.fileBox.change(function(e) {
            uploadbutton.submit();
            origin_tip_text = $('#progressBar').html();
            $('#progressBar').html('正在上传...');
            $('#background,#progressBar').show();
        });
   });
   
   
   $.each($("input.uploadimgbtn[is_bind!=true]"),function(i,n){
       var btn = $(n);
       btn.attr("is_bind",true);
        var origin_tip_text = "";
        var uploadbutton = KindEditor.uploadbutton({
            button : btn,
            width:"54px",
            fieldName : 'UPLOAD_IMG',
            url : IMG_UPLOAD_URL,
            afterUpload : function(data) {
                if (data.error === 0) {
                    $('#progressBar').html(origin_tip_text);
                    $('#background,#progressBar').hide();
                    alertMsg.correct('上传成功');
                    $(btn).parent().find(".filebox").val(data.url);
                    $(btn).parent().find(".viewbox").show();
                    $(btn).parent().find(".delbox").show();
                    $(btn).parent().find(".view").attr('href',data.url);
                } else {
                    $('#progressBar').html(origin_tip_text);
                    $('#background,#progressBar').hide();
                    alertMsg.error(data.message);
                }
            },
            afterError : function(str) {
                $('#progressBar').html(origin_tip_text);
                $('#background,#progressBar').hide();
                alertMsg.error('自定义错误信息: ' + str);
   
            }
        });
        uploadbutton.fileBox.change(function(e) {
            uploadbutton.submit();
            origin_tip_text = $('#progressBar').html();
            $('#progressBar').html('正在上传...');
            $('#background,#progressBar').show();
        });
   });
    
}


