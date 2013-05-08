(function($){
    $.fn.extend({
        chcarousel:function(options){
            options=options?options:{};
            var _options = {
                nums:1,   //單一頁的項目
                toLeftId:'',
                toRightId:'',
                recycle:true
            };
            $.extend(_options,options);
            var target = this[0];
            var children = $(target).children();
            var nums = children.size();//子項目
            var sw = children.first().outerWidth(true);//單一項目的寬度            
            var allw = sw*(nums+_options.nums);//總寬度,所有子項目寬度合再加上多餘的顯示項目
            var gow = sw*_options.nums;
            var lid = _options.nums;
            var gid=((nums-(_options.nums+1))<0)?0:nums-(_options.nums+1);
            var p=Math.ceil(nums/_options.nums);
            var curp=1;
            //本身容器設定寬度及加上position:relative
            $(target).width(sw*_options.nums).css({position:'relative',overflow:'hidden'});
            //加上容器
            var container = $("<div>").addClass('chcarousel-container').css({width:allw+'px',height:'100%'});
            container.append(children).appendTo(target);
            function go_left(e){
                if($(this).is('a')){
                    e.preventDefault();
                }
                if(container.data('animating')){
                    return false;   
                }else{
                    container.data('animating',true);
                }
                curp++;
                if(!_options.recycle && curp>=p){
                    $('#'+_options.toLeftId).hide();
                }
                $('#'+_options.toRightId).show();
                container.children().filter(":lt("+lid+")").clone(true).appendTo(container);
                container.animate({ marginLeft:(0-gow) },1000,function(){
                     container.children().filter(":lt("+lid+")").remove();
                     $(this).css({ marginLeft:'' }); 
                     container.data('animating',false);
                });
            }
            function go_right(e){
                if($(this).is('a')){
                    e.preventDefault();
                }
                if(container.data('animating')){
                    return false;   
                }else{
                    container.data('animating',true);
                }           
                curp--;
                if(!_options.recycle && curp<=1){
                    $('#'+_options.toRightId).hide();
                }      
                $('#'+_options.toLeftId).show();
                container.children().filter(":gt("+gid+")").clone(true).prependTo(container);
                container.css({ marginLeft:(0-gow) });
                container.animate({ marginLeft:0 },1000,function(){
                    container.children().filter(":gt("+(nums-1)+")").remove();
                    container.data('animating',false);
                });
            }
            if(_options.toLeftId){
               $('#'+_options.toLeftId).click(go_left);
                if(p<=1 || (!_options.recycle && curp>=p)){
                    $('#'+_options.toLeftId).hide();
                }               
            }
            if(_options.toRightId){
               $('#'+_options.toRightId).click(go_right);
               if(p<=1 || (!_options.recycle && curp<=1)){
                   $('#'+_options.toRightId).hide();
               }                 
            }
            return this;
        } 
    });
})(jQuery);


