(function($){
    $.fn.extend({
        chcarousel:function(options){
            options=options?options:{};
            var _options = {
                nums:1,   //單一頁的項目
                toLeftId:'',
                toRightId:'',
                pageId:'',
            };
            $.extend(_options,options);
            var target = this[0];
            var children = $(target).children();
            var nums = children.size();//子項目
            var sw = children.first().outerWidth(true);//單一項目的寬度            
            var allw = sw*nums;//總寬度,所有子項目寬度合
            var gow = sw*_options.nums;//單一頁寬度
            var lid = _options.nums;
            var gid=((nums-(_options.nums+1))<0)?0:nums-(_options.nums+1);
            var p=Math.ceil(nums/_options.nums);
            var curp=1;
            //本身容器設定寬度及加上position:relative
            $(target).width(sw*_options.nums).css({position:'relative',overflow:'hidden'});
            //加上容器
            var container = $("<div>").addClass('chcarousel-container').css({width:allw+'px',height:'100%'});
            container.append(children).appendTo(target);
            function movingTo(pid){
                if(curp!=pid){
                    if(container.data('animating')){
                        return false;   
                    }       
                    if(curp<pid){
                        curp++;
                        if( curp>=p && _options.toLeftId){
                            $('#'+_options.toLeftId).hide();
                        }
                        container.data('animating',true);
                        if(_options.toRightId)$('#'+_options.toRightId).show();
                        container.animate({ marginLeft:"-="+gow },1000,function(){
                             container.data('animating',false);
                        });    
                    }else if(curp>pid){
                        curp--;
                        container.data('animating',true);
                        if( curp<=1 && _options.toRightId){
                            $('#'+_options.toRightId).hide();
                        }      
                        if(_options.toLeftId)$('#'+_options.toLeftId).show();
                        container.animate({ marginLeft:"+="+gow },1000,function(){
                            container.data('animating',false);
                        });  
                    }
                }
            }
            function go_left(e){
                if($(this).is('a')){
                    e.preventDefault();
                }
                movingTo(curp+1);
            }
            function go_right(e){
                if($(this).is('a')){
                    e.preventDefault();
                }
                movingTo(curp-1);
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
            if(_options.pageId){
                var ul = $("<ul></ul>");
                for(var pi=1;pi<=nums;pi++){
                    var link = $("<a href='#'></a>").attr('rel',pi).text(pi).click(function(evt){
                        evt.preventDefault();
                        var pid = $(this).attr('rel');
                        movingTo(pid);
                        $(this).parents("ul").find("a").removeClass("current");
                        $(this).addClass("current");
                    });
                    if(curp==pi){
                        link.addClass("current");
                    }
                    var li = $("<li></li>");
                    li.append(link);
                    ul.append(li);
                }               
                $("#"+_options.pageId).append(ul);
            }
            return this;
        } 
    });
})(jQuery);


