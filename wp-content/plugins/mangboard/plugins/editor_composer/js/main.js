function mb_insertEditorLayout(type){
	var editor_type		= jQuery('#editor_type').val();
	var insert_html		= '	';	
	var inner_html		= '';
	var outer_html		= '';
	var text_style		= '';
	var inner_style		= mb_getEditorBoxStyle("inner");	
	var outer_style		= mb_getEditorBoxStyle("outer");	
	var wrap_style		= mb_getEditorBoxStyle("wrap");
	
	inner_style			+= jQuery("#mbc_inner_style").val();
	outer_style			+= jQuery("#mbc_outer_style").val();
	wrap_style			+= jQuery("#mbc_wrap_style").val();	
	
	var inner_class	= jQuery("#mbc_inner_class").val();
	var outer_class	= jQuery("#mbc_outer_class").val();
	var wrap_class	= jQuery("#mbc_wrap_class").val();	
	
	if(inner_class!='') inner_class	= ' '+inner_class;
	if(outer_class!='') outer_class	= ' '+outer_class;
	if(wrap_class!='') wrap_class	= ' '+wrap_class;

	var line_count		= parseInt(jQuery("#mbc_editor_line_count").val());
	var max_width		= jQuery("#mbc_editor_max_width").val();
	var text_align		= jQuery("#mbc_editor_text_align").val();
	
	if(max_width!='100%'){
		wrap_style		= 'max-width:'+max_width+' !important;'+wrap_style;
	}
	if(text_align!=''){
		text_style		= ' style="text-align:'+text_align+' !important;"';
	}

	inner_html			= '<div class="mb-rb'+inner_class+'" style="'+inner_style+'"><p'+text_style+'><span>&nbsp;</span></p></div>';

	if(type=="L70-30"){		
		outer_html	+= '<div class="col-sm-70 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
		outer_html	+= '<div class="col-sm-30 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
	}else if(type=="L30-70"){		
		outer_html	+= '<div class="col-sm-30 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
		outer_html	+= '<div class="col-sm-70 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
	}else if(type=="L33-67"){		
		outer_html	+= '<div class="col-sm-4 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
		outer_html	+= '<div class="col-sm-8 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
	}else if(type=="L67-33"){
		outer_html	+= '<div class="col-sm-8 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
		outer_html	+= '<div class="col-sm-4 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
	}else if(type=="L16-84"){		
		outer_html	+= '<div class="col-sm-2 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
		outer_html	+= '<div class="col-sm-10 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
	}else if(type=="L84-16"){		
		outer_html	+= '<div class="col-sm-10 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
		outer_html	+= '<div class="col-sm-2 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
	}else if(type=="L25-75"){		
		outer_html	+= '<div class="col-sm-3 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
		outer_html	+= '<div class="col-sm-9 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
	}else if(type=="L75-25"){		
		outer_html	+= '<div class="col-sm-9 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
		outer_html	+= '<div class="col-sm-3 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
	}else if(type=="M331"){		
		outer_html	+= '<div class="col-sm-3-1 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
		outer_html	+= '<div class="col-sm-3-1 col-sm-offset-1-2 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
		outer_html	+= '<div class="col-sm-3-1 col-sm-offset-1-2 mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
	//타입이 "col-" 로 시작하는 반응형 클래스 처리
	}else if(type.indexOf("col-")==0){		
		var count		= parseInt(type.substr(4,1));
		var i				= 0;
		for(i=0;i<count;i++){
			outer_html	+= '<div class="'+type+' mb-rb'+outer_class+'" style="'+outer_style+'">'+inner_html+'</div>';
		}
	}	
	
	if(line_count>1){
		var temp_html		= outer_html;
		for(i=0;i<line_count-1;i++){
			outer_html	+= temp_html;
		}
	}
	insert_html		= '<div class="responsive-list'+wrap_class+'" style="'+wrap_style+'">'+outer_html+'</div>';
	if(editor_type=='S' || editor_type=='HS' || editor_type=='HS2' || editor_type=='HS3'){
		if(typeof(oEditors)!=='undefined'){
			oEditors.getById['se_content'].exec("PASTE_HTML", [insert_html]);
		}
	}else if(editor_type=='C'){
		if(typeof(ckeditor)!=='undefined'){
			ckeditor.insertHtml(insert_html);
		}
	}else if(editor_type=='W'){
		if(typeof(tinyMCE)!=='undefined' && typeof(tinyMCE.activeEditor)!=='undefined'){
			tinyMCE.activeEditor.execCommand('mceInsertRawHTML', false, insert_html);
			tinyMCE.activeEditor.focus();
		}
	}
}
jQuery(document).ready(function() {
	if(jQuery(".mb-editor-composer").length>0){
		jQuery(".mb-editor-composer").closest('td').css('overflow', 'visible');
		jQuery(".mbc-colorpicker>input").colorpicker({ color: "#dddddd", hideButton: true});
		jQuery(".mbc-colorpicker2>input").colorpicker({ color: "#ffffff", hideButton: true});

		jQuery("#mbc_inner_border_width").val(1);
		jQuery("#mbc_inner_padding").val(7);
		jQuery("#mbc_outer_padding").val(7);
		jQuery("#mbc_wrap_padding").val(1);

		jQuery("#mbc_wrap_border_width, #mbc_wrap_padding").change(function(){
			mb_setBorderStyleValue("wrap");
		});
		jQuery("#mbc_wrap_border_radius, #mbc_wrap_border_width, #mbc_wrap_border_color, #mbc_wrap_border_style, #mbc_wrap_background_color").change(function(){
			mb_previewBorderStyle("wrap");	
		});
		jQuery("#mbc_outer_border_width, #mbc_outer_padding").change(function(){
			mb_setBorderStyleValue("outer");
		});
		jQuery("#mbc_outer_border_radius, #mbc_outer_border_width, #mbc_outer_border_color, #mbc_outer_border_style, #mbc_outer_background_color").change(function(){
			mb_previewBorderStyle("outer");	
		});
		jQuery("#mbc_inner_border_width, #mbc_inner_padding").change(function(){
			mb_setBorderStyleValue("inner");
		});
		jQuery("#mbc_inner_border_radius, #mbc_inner_border_width, #mbc_inner_border_color, #mbc_inner_border_style, #mbc_inner_background_color").change(function(){
			mb_previewBorderStyle("inner");	
		});

		mb_previewBorderStyle("wrap");
		mb_previewBorderStyle("outer");
		mb_previewBorderStyle("inner");

		mb_setBorderStyleValue("wrap");
		mb_setBorderStyleValue("outer");
		mb_setBorderStyleValue("inner");
		jQuery('.mb-editor-composer').show();
	}
});
function mb_setEditorTabs(id,idx,name){
	jQuery("#"+id).children('.mp-editor-tabs-selected').removeClass('mp-editor-tabs-selected');
	jQuery("#"+id).children("div:eq("+(idx-1)+")").addClass("mp-editor-tabs-selected");
	jQuery("."+name).hide();
	jQuery("."+name+idx).show();
}

function mb_getEditorBoxStyle(box){
	var boxStyle		= '';
	if( box == "inner" || box == "outer" || box == "wrap" ){
		if(jQuery(".mb-"+box+"-box .mb-editor-border-top-width").val()!="0") boxStyle	+='border-top:'+jQuery(".mb-"+box+"-box .mb-editor-border-top-width").val()+'px '+jQuery("#mbc_"+box+"_border_style").val()+' '+jQuery("#mbc_"+box+"_border_color").val()+';';
		if(jQuery(".mb-"+box+"-box .mb-editor-border-right-width").val()!="0") boxStyle	+='border-right:'+jQuery(".mb-"+box+"-box .mb-editor-border-right-width").val()+'px '+jQuery("#mbc_"+box+"_border_style").val()+' '+jQuery("#mbc_"+box+"_border_color").val()+';';
		if(jQuery(".mb-"+box+"-box .mb-editor-border-bottom-width").val()!="0") boxStyle	+='border-bottom:'+jQuery(".mb-"+box+"-box .mb-editor-border-bottom-width").val()+'px '+jQuery("#mbc_"+box+"_border_style").val()+' '+jQuery("#mbc_"+box+"_border_color").val()+';';
		if(jQuery(".mb-"+box+"-box .mb-editor-border-left-width").val()!="0") boxStyle	+='border-left:'+jQuery(".mb-"+box+"-box .mb-editor-border-left-width").val()+'px '+jQuery("#mbc_"+box+"_border_style").val()+' '+jQuery("#mbc_"+box+"_border_color").val()+';';
		if(jQuery("#mbc_"+box+"_border_radius").val()!="0") {
			var border_radius		= jQuery("#mbc_"+box+"_border_radius").val();
			boxStyle	+='border-radius:'+border_radius+'px;-webkit-border-radius:'+border_radius+'px;-moz-border-radius:'+border_radius+'px;-khtml-border-radius:'+border_radius+'px;';
		}
		if(jQuery("#mbc_"+box+"_background_color").val()!=''){
			boxStyle	+='background-color:'+jQuery("#mbc_"+box+"_background_color").val()+';';
		}
		boxStyle	+='padding:'+jQuery(".mb-"+box+"-box .mb-editor-padding-top").val()+'px '+jQuery(".mb-"+box+"-box .mb-editor-padding-right").val()+'px '+jQuery(".mb-"+box+"-box .mb-editor-padding-bottom").val()+'px '+jQuery(".mb-"+box+"-box .mb-editor-padding-left").val()+'px;'
		return boxStyle;
	}
}
function mb_setBorderStyleValue(box){
	if( box == "inner" || box == "outer" || box == "wrap" ){
		jQuery(".mb-"+box+"-box .mb-editor-border>input").val(jQuery("#mbc_"+box+"_border_width").val());
		jQuery(".mb-"+box+"-box .mb-editor-padding>input").val(jQuery("#mbc_"+box+"_padding").val());
	}
}
function mb_previewBorderStyle(box){
	if( box == "inner" || box == "outer" || box == "wrap" ){
		var border_width	= parseInt(jQuery("#mbc_"+box+"_border_width").val());
		if(border_width>10) border_width		= 10;

		var style1			= 'border: '+border_width+'px '+jQuery("#mbc_"+box+"_border_style").val()+' '+jQuery("#mbc_"+box+"_border_color").val()+';border-radius:'+jQuery("#mbc_"+box+"_border_radius").val()+'px;';
		var style2			= 'background-color:'+jQuery("#mbc_"+box+"_background_color").val();
		var style				= '';
		if(border_width==0) style	= style2;
		else style	= style1+style2;
		
		jQuery(".mb-"+box+"-box .mb-editor-padding").attr('style',style);
		
		if(box=="inner"){			
			jQuery(".mb-outer-box .mb-editor-board-style2").attr('style',style);
			jQuery(".mb-wrap-box .mb-editor-board-style1").attr('style',style);
		}else if(box=="outer"){
			jQuery(".mb-inner-box .mb-editor-board-style4").attr('style',style);
			jQuery(".mb-wrap-box .mb-editor-board-style2").attr('style',style);
		}else if(box=="wrap"){
			jQuery(".mb-inner-box .mb-editor-board-style5").attr('style',style);
			jQuery(".mb-outer-box .mb-editor-board-style4").attr('style',style);
		}		
	}
}
function mb_showEditorAttrBox(){
	jQuery('.mb-editor-attr-box').slideDown(300, function() {
		jQuery(".mb-editor-composer").addClass('mb-editor-composer-open');	
		jQuery('.mb-editor-hide-btn').hide();
		jQuery('.mb-editor-show-btn').show();
	});
}
function mb_hideEditorAttrBox(){
	jQuery('.mb-editor-attr-box').slideUp(200);
	jQuery('.mb-editor-hide-btn').show();
	jQuery('.mb-editor-show-btn').hide();
	jQuery(".mb-editor-composer").removeClass('mb-editor-composer-open');	
}