(function($){
    $(document).ready(function(){
        $(".btn a[rel]").each(function(e){
           var offset = $(this).offset();
           var menuid = $(this).attr('rel');
           var obj_height = $(this).height();
           var parent = this;
           //設定起始位置
           $("#"+menuid).css({ 'top':(offset.top+obj_height)+'px','left':offset.left+'px' });
           //設定handler
           $("#"+menuid).mouseleave(function(e){
              if(e.relatedTarget !== parent){
                  $(this).trigger('close'); 
              }                      
           });
           $("#"+menuid).bind('close',function(e){
              $(this).slideUp(); 
           });
           $("#"+menuid).bind('open',function(){
              $(this).slideDown(); 
           });                   
           $(this).mouseenter(function(){
               $("#"+menuid).trigger('open');
           });
           $(this).mouseleave(function(e){
               var sw=true;
               if($("#"+menuid)[0]===e.relatedTarget){
                   sw=false;
               }else{
                   $(e.relatedTarget).parents().each(function(){
                       if(this===$("#"+menuid)[0]){
                           sw=false;
                           return false;
                       }
                   });
               }
               if(sw)$("#"+menuid).trigger('close');
           })                   
        });
    });
})(jQuery);