/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//    var n=$('.prods-list').size();//數量
//    var sw=$(".prods-list").outerWidth(true);//單一項目的寬度
//    var allw=sw*(n+3);//總寬度
//    var gow=sw*3;//移動寬度
//    var lid=3;
//    var gid=((n-4)<0)?0:n-4;
//    var container = $("<div>").addClass("prods_container").width(allw);
//    container.append($(".prods-list")).appendTo("#prods-box");
//    if(n>3){
//        $(".arrow_l,.arrow_r").css({ visibility: 'visible'});
//        //設定事件
//        $(".arrow_l a").click(function(e){
//            e.preventDefault();
//            if($(".prods_container").data('animating')){
//                return false;   
//            }else{
//                $(".prods_container").data('animating',true);
//            }
//            $(".prods-list:lt("+lid+")").clone().appendTo('.prods_container');
//            $(".prods_container").animate({ marginLeft:(0-gow) },1000,function(){
////                                         $(this).append($(".prods-list:lt("+lid+")"));
//                 $(".prods-list:lt("+lid+")").remove();
//                 $(this).css({ marginLeft:'' }); 
//                 $(".prods_container").data('animating',false);
//            });
//        });
//        $(".arrow_r a").click(function(e){
//            e.preventDefault();
//            if($(".prods_container").data('animating')){
//                return false;   
//            }else{
//                $(".prods_container").data('animating',true);
//            }                                    
//            $(".prods-list:gt("+gid+")").clone().prependTo('.prods_container');
//            $('.prods_container').css({ marginLeft:(0-gow) });
//            $(".prods_container").animate({ marginLeft:0 },1000,function(){
//                $(".prods-list:gt("+(n-1)+")").remove();
//                $(".prods_container").data('animating',false);
//            });
//        });
//    }
(function($){
    $.fn.extend({
        chcarousel:function(options){
            options=options?options:{};
            var _options = {
                nums:1   //單一頁的項目
            };
            $.extend(_options,options);
            var target = this[0];
            var children = $(target).children();
            var nums = children.size();//子項目
            var sw = children.first().outerWidth(true);//單一項目的寬度            
            var allw = sw*(nums+_options.nums);//總寬度,所有子項目寬度合再加上多餘的顯示項目
            //本身容器設定寬度及加上position:relative
            $(target).width(sw*_options.nums).css({position:'relative',overflow:'hidden'});
            //加上容器
            var container = $("<div>").addClass('chcarousel-container').width(allw);
            container.append(children).appendTo(target);
            function go_left(){
                
            }
            function go_right(){
                
            }
            return this;
        } 
    });
})(jQuery);


