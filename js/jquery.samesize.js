/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
(function($){
    $.fn.extend({
        samesize:function(){
            this.each(function(){
                 var tmp_height = $(this).height();
                 if(tmp_height>$.samesize.height)$.samesize.height = tmp_height;
            }); 
            this.each(function(){
                 $(this).height($.samesize.height); 
            });
            return this;
        }
    })
    $.extend({
       samesize:{
           height:0,
           width:0
       } 
    });
})(jQuery);

