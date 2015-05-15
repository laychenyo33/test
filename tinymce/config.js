// JavaScript Document

	function tiny_load(ROOT_PATH,SELECT){
		
		if(typeof(SELECT) == "undefined" || SELECT == ""){
			SELECT = "#elm1,#elm2,#elm3,#elm4,#elm5,#elm6,.mceEditor";
		}
		
		tinymce.init({
                        forced_root_block : "",
			selector: SELECT,
			theme: "modern",
			width: 760,
			height: 200,
			language : 'zh_TW',
			
			image_advtab: true,
			external_filemanager_path: ROOT_PATH +"filemanager/",
			filemanager_title:"檔案管理" ,
			external_plugins: { "filemanager" : ROOT_PATH +"filemanager/plugin.min.js"},
			
			/*plugins: [
	                "advlist autolink autosave link image lists charmap print preview hr anchor pagebreak spellchecker",
	                "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
	                "table contextmenu directionality emoticons template textcolor paste fullpage textcolor colorpicker textpattern save"
	        ],
	
	        toolbar1: "save newdocument fullpage | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect",
	        toolbar2: "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image media code | insertdatetime preview | forecolor backcolor",
	        toolbar3: "table | hr removeformat | subscript superscript | charmap emoticons | print fullscreen | ltr rtl | spellchecker | visualchars visualblocks nonbreaking template pagebreak restoredraft",
	
	        menubar: false,
	        toolbar_items_size: 'small',
			*/
			
			plugins: [
			     "image responsivefilemanager advlist autolink link lists charmap print preview hr anchor pagebreak spellchecker",
			     "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
			     "save table contextmenu directionality emoticons template paste textcolor colorpicker youTube "
			],
			  
			toolbar1: "insertfile undo redo | styleselect | fontselect | fontsizeselect | bold italic underline | colorpicker forecolor backcolor | youTube",
			toolbar2: "alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link unlink anchor | responsivefilemanager image media |  preview emoticons print code ",
			 			
			//toolbar1: "insertfile undo redo | styleselect formatselect fontselect fontsizeselect | cut copy paste pastetext | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link unlink anchor | image responsivefilemanager media |  preview emoticons print code ",
			//toolbar1 : "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect | insertdatetime | table",
			//toolbar2 : "insertdate inserttime | fontselect | fontsizeselect | colorpicker forecolor backcolor | insertfile | image | cleanup | media | link unlink anchor insertfile | print preview code | ",
			//toolbar3 : "strikethrough charmap iespell subscript superscript | hr removeformat | ltr rtl | visualchars nonbreaking pagebreak restoredraft visualblocks",
			//fullscreen  spellchecker blockquote newdocument save | cut copy paste pastetext pasteword insertdate inserttime | cut copy paste pastetext pasteword 
			menubar: true,
			image_advtab: true,
	        //toolbar_items_size: 'small',
			
			
            convert_urls: false,
            paste_auto_cleanup_on_paste : true,
            paste_postprocess : function(pl, o) {
                // remove &nbsp
                o.node.innerHTML = o.node.innerHTML.replace(/&nbsp;/ig, " ");
             },
            extended_valid_elements : "iframe[src|width|height|name|align],span[style|id|nam|class|lang]",
            paste_retain_style_properties : "margin padding width height font-size font-weight font-family color text-align ul ol li text-decoration border background float display background-color",
            paste_word_valid_elements: "b,strong,i,em,h1,h2,table,tr,th,td,ul,ol,li,style,img",
            paste_data_images: true

		});
	}
	
	function open_popup(url){
		var w = 880;
		var h = 570;
		var l = Math.floor((screen.width-w)/2);
		var t = Math.floor((screen.height-h)/2);
		var win = window.open(url, 'ResponsiveFilemanager', "scrollbars=1,width=" + w + ",height=" + h + ",top=" + t + ",left=" + l);
	}
