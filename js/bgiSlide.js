(function($){
    $.fn.bgiSlide = function(settings) {
        var _settings = {
           width:'100%',
           height:'100%'
        };
        $.extend(_settings,settings);
        var banner_index = 0;
        var pre_index = -1;   
        var tarobj  = $(this[0]);
        var imgLength = tarobj.find('div[rel]').size();
        var init = function(){
            tarobj.find("div[rel]").each(function(){
                var img_src = $(this).attr("rel");
                $(this).css({
                    "background":"url("+img_src+") top center no-repeat ",
                     position: 'absolute',
                     top:'0',
                     left:'0',
                     width:'100%',
                     height:_settings['height']
                });                
            });        
        }   
        
        init();
        
        
        var banner_slide = function(){
            tarobj.find('div[rel]:eq('+banner_index+')').css('z-index',10).fadeIn(1000);
            if(pre_index>-1){
                tarobj.find('div[rel]:eq('+pre_index+')').fadeOut("fast",function(){
                    $(this).css("z-index",1);
                });
            }
            pre_index = banner_index;
            banner_index = (banner_index==(imgLength-1))?0:banner_index+1;
            setTimeout(banner_slide,5000);            
        }
        
        banner_slide();  
        return this;
    };
})(jQuery);

