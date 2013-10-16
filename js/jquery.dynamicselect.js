jQuery(function($){
   $.fn.extend({
        dynamicSelect:function(options){
            var _options = {
                method: 'get',    //request的method
                source: '',      //request來源(網址)
                paramName: '',   //操作物件值欲代表的變數名
                params: {},      //其他欲一併送出查詢的變數
                dataType:'',     //回傳的資料型態
                relatedElement:null, //連動的select物件
            };
            $.extend(_options,options);
            if(_options.paramName==""){
                _options.paramName = this[0].name;
            }
            if(_options.relatedElement == null || _options.relatedElement.size()==0){
                alert("請指定欲連動的select物件");
                return false;
            }
            var paramName = _options.paramName?_options.paramName:this[0].name;
            var myData = {};
            $(this[0]).change(function(evt){
                var target = _options.relatedElement;
                myData[paramName] = $(this).val();
                $.extend(myData,_options.params);
                $.ajax(_options.source,{ 
                    type:_options.method,
                    data: myData,
                    dataType:_options.dataType,
                    error:function(req){
                       alert(req);
                    },
                    success:function(req){
                        target.find('option:gt(0)').remove();
                        target.append(req);
                    }        
                });
            });
        }
   });
});
