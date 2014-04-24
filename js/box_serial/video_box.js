// JavaScript Document

/* 使用方法

$(".class / #id").video_box({
	VIDEO_W : "640", // 影片寬度
	VIDEO_H : "480", // 影片寬度
	CODE : "", // YouTube 辨識碼
	AFTER :  function() {  }, // 動作後執行擴充			
});

*/

(function($){
	$.fn.video_box = function(OPTION){
		var VIDEO = jQuery.extend({
			VIDEO_W : "640", // 影片寬度
			VIDEO_H : "480", // 影片寬度
			CODE : "", // YouTube 辨識碼
			AFTER :  function() {  }, // 動作後執行擴充			
		}, OPTION);
		
		var THIS = this;
		
		//return this.each(function(){
			THIS.click(function(E){
				E.preventDefault();
				
				$("body").append('<div id="video_box"></div>');								
				$("#video_box").html("");
				
				if(VIDEO.CODE != ""){
					var CODE = VIDEO.CODE;
				}else{
					var CODE = $(this).attr("rel");
				}
				
				if(CODE != ""){
					$("body").append(
						'<iframe id="video_iframe" width="'+ VIDEO.VIDEO_W +'" height="'+ VIDEO.VIDEO_H +'" src="http://www.youtube.com/embed/'+ CODE +'?rel=0" frameborder="0" allowfullscreen></iframe>'+
						'<div id="video_close">Close</div>'
					);
					
					$("#video_box").css({
						"width":"100%",
						"height":"100%",
						"display":"none",
						"position":"fixed",
						"z-index":"998",
						"background":"#000",
						"top":"0",
						"left":"0",
						"opacity":"0.6",
						"filter":"alpha(opacity=60)",
					});
					
					$("#video_iframe").css({
						"position":"fixed",
						"z-index":"999",
						"top":"50%",
						"left":"50%",
						"margin":"-"+ VIDEO.VIDEO_H / 2 +"px 0 0 -"+ VIDEO.VIDEO_W / 2 +"px"
					});
					
					$("#video_close").css({
						"display":"none",
						"position":"fixed",
						"width":"100px",
						"height":"25px",
						"z-index":"999",
						"top":"50%",
						"left":"50%",
						"background":"#000",
						"color":"#DDD",
						"margin":VIDEO.VIDEO_H / 2 +"px 0 0 "+ (VIDEO.VIDEO_W / 2 - 100) +"px",
						"text-align":"center",
						"letter-spacing":"0.1em",
						"line-height":"25px",
						"cursor":"pointer",
					});
					
					$("#video_close").hover(function(){
						$(this).css({
							"background":"#FFF",
							"color":"#900",
						});
					}, function(){
						$(this).css({
							"background":"#000",
							"color":"#DDD",
						});
					});
					
					$("#video_box,#video_close").fadeIn("slow");
				}
				
				VIDEO.AFTER();
			});
			
			$("#video_box,#video_close").live("click",function(){
				$("#video_iframe").hide();
				$("#video_box,#video_close").fadeOut("slow",function(){
					$("#video_box,#video_iframe,#video_close").remove();
				});
			});
		//});
	};
})(jQuery);
