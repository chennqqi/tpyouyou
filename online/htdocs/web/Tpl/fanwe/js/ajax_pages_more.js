
(function($){
    
    $.fn.click_page = function(pager,htmlbox,query,fun_item){
        var self = this;
        var url = $(this).attr("url");     

        if(url !="undefined"){
            $.ajax(
                 {
                    type: "POST",
                    url: url,
                    data:query,
                    dataType:"JSON",
                    success:function(result){
                        $(pager).html(result.pager);
                        $(htmlbox).html(result.html);
                        if(typeof fun_item === 'function' ){
                            fun_item();
                        }
                    }
                  }
            );
        }else{
            return false;
        }

    };
    
    
    
    $.fn.init_page = function(htmlbox,query,fun){
        var pager =  $(this);     
        var pager_item = pager.find("a");  
        var fun_item = fun;
        pager_item.live("click",function(){
             $(this).click_page(pager,htmlbox,query,fun_item);
        });
    };
})(jQuery);