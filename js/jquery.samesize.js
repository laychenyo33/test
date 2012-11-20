/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
(function($){
    $.fn.extend({
        samesize:function(){
            function sameheight(caller){
                this.height=0;
                var obj = this;
                caller.each(function(){
                     var tmp_height = $(this).height();
                     if(tmp_height>obj.height)obj.height = tmp_height;
                }); 
                caller.each(function(){
                     $(this).height(obj.height); 
                });
            }
            var ss = new sameheight(this);
            return this;
        }
    });
})(jQuery);

