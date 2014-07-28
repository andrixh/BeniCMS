(function($) { //MSLTRING TEXTINPUT WIDGET
	$.fn.mlHtml = function() {
	  	var wymIndex = 0;
	  	$(this).each(function() {
	  		
	  		var self = this;
	  		var dropZone = $('<div class="dropZone"></div>');
	  		$(self).prepend(dropZone);
	  		self.wymIndex = wymIndex;
	  		var fieldName = $(self).data('basename');
	  		var element = 'textarea';
	  		var languages = $.parseJSON( $(self).attr('data-languages'));
	  		_d(languages);
	  		//_g('mlString_TextInput: '+fieldName);
	  		
	  		
	  		var languageTabs = $('<div class="languageTabs"></div>');
			for (var langID in languages){
				//_d(languages[langID]);
				var langLink = $('<a href="#" data-language="'+languages[langID].langID+'"><img src="/admin/Gfx/Flags/'+languages[langID].flag+'.png" alt="'+languages[langID].name+'"/></a>');
			    //_d($(self).find(element+'#'+fieldName+'_'+languages[langID].langID).val());
			    if ($(self).find(element+'#'+fieldName+'_'+languages[langID].langID).val() == ''){
			    	_d(languages[langID].langID+' is empty');
			    	langLink.addClass('empty');
			    }
			    if ($(self).find(element+'#'+fieldName+'_'+languages[langID].langID+'[readonly]').length != 0){
			    	langLink.addClass('readonly');
			    }
			    languageTabs.append(langLink);
			}
			languageTabs.find('a').eq(0).addClass('selected');
	  		$(self).append(languageTabs);
	  		
	  		var startingHtml = $(self).find('textarea').eq(0).val();
	  		var editor = $('<textarea class="editor"></textarea>');
	  		editor.val(startingHtml);
	  		$(self).append(editor);
	  		
	  		editor.wymeditor({
				logoHtml: '',
				toolsItems: [
					{'name': 'Bold', 'title': 'Strong', 'css': 'wym_tools_strong'}, 
					{'name': 'Italic', 'title': 'Emphasis', 'css': 'wym_tools_emphasis'},
					{'name': 'InsertUnorderedList', 'title': 'Unordered_List', 'css': 'wym_tools_unordered_list'},
					{'name': 'InsertOrderedList', 'title': 'Ordered_List', 'css': 'wym_tools_ordered_list'},
					{'name': 'Indent', 'title': 'Indent', 'css': 'wym_tools_indent'},
					{'name': 'Outdent', 'title': 'Outdent', 'css': 'wym_tools_outdent'},
					//{'name': 'Undo', 'title': 'Undo', 'css': 'wym_tools_undo'},
					//{'name': 'Redo', 'title': 'Redo', 'css': 'wym_tools_redo'},
					{'name': 'CreateLink', 'title': 'Link', 'css': 'wym_tools_link'},
					{'name': 'Unlink', 'title': 'Unlink', 'css': 'wym_tools_unlink'},
					{'name': 'InsertTable', 'title': 'Table', 'css': 'wym_tools_table'},
					/*{'name': 'InsertImage', 'title': 'Image', 'css': 'wym_tools_image'},*/
					{'name': 'Paste', 'title': 'Paste_From_Word', 'css': 'wym_tools_paste'},
					{'name': 'ToggleHtml', 'title': 'HTML', 'css': 'wym_tools_html'}
				],
				containersItems: [
		        {'name': 'P', 'title': 'Paragraph', 'css': 'wym_containers_p'},
		        {'name': 'H1', 'title': 'Heading_1', 'css': 'wym_containers_h1'},
		        {'name': 'H2', 'title': 'Heading_2', 'css': 'wym_containers_h2'},
		        {'name': 'H3', 'title': 'Heading_3', 'css': 'wym_containers_h3'},
		        {'name': 'H4', 'title': 'Heading_4', 'css': 'wym_containers_h4'},
		        {'name': 'H5', 'title': 'Heading_5', 'css': 'wym_containers_h5'},
		        {'name': 'H6', 'title': 'Heading_6', 'css': 'wym_containers_h6'},
                {'name': 'PRE', 'title': 'Code', 'css': 'wym_containers_pre'}
		    ],
				skin: 'preferred',
				stylesheet: "/admin/Css/wymiframe.css",
				postInit: function() {
			    	self.iframe = $(self).find('iframe')[0];
			    	$(self).find('li.wym_tools_link').replaceWith('<li class="wym_my_link"><a href="#"></a></li>');
			    	$(self).find('li.wym_tools_paste').replaceWith('<li class="wym_my_paste"><a href="#"></a></li>');
			    	$(self).find('li.wym_tools_table').replaceWith('<li class="wym_my_table"><a href="#"></a></li>');
			    	self.iframe.contentWindow.postMessage({'action':'init'},'*');
			    	//_d(self.iframe);
		  		}
																
			}); // init WYMeditors in document
	  	

			function cleaner(xhtml){
				_d('Cleaner');
				_d(xhtml);
				var cleaner = $('<div>'+xhtml+'</div>');
				$(cleaner).children().not('p,h1,h2,h3,h4,h5,h6,ul,ol,table,pre').remove();
				$(cleaner).children('p.img, p.video').each(function(){
					_d(this);
					var img = $(this).find('img');
					$(this).children().remove();
					if (img.length != 0){
						$(this).append(img);
					} else {
						$(this).remove();
					}
				});
				$(cleaner).children('p.fake, br').remove();
				var result = $(cleaner).html();
				_d(result);
				return result;
			}


			/*
			 * Link button
			 */
			$(self).find('li.wym_my_link a').live('click',function() {
				showLinkDialog(self.wymIndex, self.iframe);
				return false;
			});	 	
		
			/*
			 * Paste button
			 */
			$(self).find('li.wym_my_paste a').live('click',function() {
				showPasteDialog(self.wymIndex, self.iframe);
				return false;
			});	 
			
			$(self).find('li.wym_my_table a').live('click',function() {
				self.iframe.contentWindow.postMessage({'action':'insertTable'},'*');
				return false;
			});	 
		
		
			$(languageTabs).find('a').click(function() { //switch language input
				var wymIndex = self.wymIndex;
				_d(wymIndex);
				var lang = $(this).data('language');
				var currLang = $(this).parent().find('a.selected').attr('data-language');
				_d(lang);
				
				var xhtml = cleaner($.wymeditors(wymIndex).xhtml());
				_d(xhtml);

				if (xhtml == ''){
					$(this).parent().find('a[data-language='+currLang+']').addClass('empty');
				} else {
					$(this).parent().find('a[data-language='+currLang+']').removeClass('empty');
				}
				_d(currLang);
				$(self).find(element+'#'+fieldName+'_'+currLang).val(xhtml);
				
				var newXthml = $(self).find(element+'#'+fieldName+'_'+lang).val();
				$.wymeditors(wymIndex).html(newXthml);
				self.iframe.contentWindow.postMessage({'action':'init'},'*');
				//var height = $(self).find(element).not('.hidden').height();
				//$(self).find(element+'.hidden').height(height);
				//$(self).find(element).addClass('hidden');
				$(this).siblings().removeClass('selected');
				//$(self).find(element+'#'+fieldName+'_'+lang).removeClass('hidden').focus();
				
				$(this).addClass('selected');
				return false;
			})
			

			$(dropZone).droppable({ //handle dragging of images;
				drop: function( event, ui ) {
					_g('DROP');
						_d(event);
						_d(ui);
						_d(ui.draggable.data('itemData'));
					_u();
					if (ui.draggable.hasClass('ui-draggable')){
						_d(ui.draggable);						
						var itemData = ui.draggable.data('itemData');
						_d(itemData);
						
						if (itemData.resourceType == 'image'){
							self.iframe.contentWindow.postMessage({'action':'insertImage','data':itemData},'*');
						} else if (itemData.resourceType == 'video'){
							self.iframe.contentWindow.postMessage({'action':'insertVideo','data':itemData},'*');
						} else if (itemData.resourceType == 'file') {
							_d('file dropped');
							_d(itemData);
							var pName = itemData.physicalName;
							var ext = itemData.extension;
							var fileName = itemData.fileName;
							
							var fileLink = '<a class="file '+ext+'" href="'+pName+'" title="'+fileName+'.'+ext+'">'+fileName+'.'+ext+'</a>'; 
							$.wymeditors(self.wymIndex).insert(fileLink);
						} else if (itemData.resourceType == 'component') {
							_d('component Dropped');
							_d(itemData);
							itemData.preview = ui.draggable.html();
							self.iframe.contentWindow.postMessage({'action':'insertComponent','data':itemData},'*');
						}
						dropZone.resourceType = null;
					}
				},
				over: function(event, ui) { 
					var itemData = ui.draggable.data('itemData'); 
					dropZone.resourceType = itemData.resourceType;
				},
				out: function(event, ui) { 
					//var itemData = ui.draggable.data('itemData'); 
					_d('dropout');
					dropZone.resourceType = null;
					self.iframe.contentWindow.postMessage({'action':'cancelDrop'},'*');
				}
			});

			
			$(dropZone).mousemove(function(evt){
				var data = {
					'resourceType':dropZone.resourceType,
					'y':evt.clientY-$(this).offset().top
				};
				if (dropZone.resourceType == 'image' || dropZone.resourceType == 'video' || dropZone.resourceType == 'component'){
					self.iframe.contentWindow.postMessage({'action':'attemptDrop','data':data},'*');
				}				
			});
			
			 $("form").submit(function() { //submit
			 	var wymIndex = self.wymIndex;
				var currLang = $(languageTabs).find('a.selected').attr('data-language');
				var xhtml = $.wymeditors(wymIndex).xhtml();

				$(self).find(element+'#'+fieldName+'_'+currLang).val(cleaner(xhtml));
				
			 });
			
			wymIndex++;
	    });
	    
		
	}
})(jQuery);

///////////////////////////////////////
					
var linkDialog=null;
var imgLinkDialog = null;
var pasteDialog=null;
var overlay = null;


$(document).ready(function() {
	$('fieldset.mlHtml').mlHtml();
	initOverlay();
	initPasteDialog();
	initLinkDialog();
	initImgLinkDialog();
});  //----- end of document.ready------

function initImgLinkDialog(){
	var imgLinkDialogHtml = '<div id="imgLinkDialog" class="dialog">'
						+'<h2>Add or Edit Image Link</h2>'
						+'<form>'
						+'<fieldset class="col1">'
						+'<label for="imgLinkDialog_url"><span>Link address</span>'
						+'<input type="text" id="imgLinkDialog_url"/></label>'
						+'</fieldset>'
						+'<fieldset class="col1 submit">'
						+'<label><span></span><a class="formButton" id="imgLinkDialog_submit" href="#">Add Link</a></label>'
						+'<label><span></span><a class="formButton" id="imgLinkDialog_cancel" href="#">Cancel</a></label>'
						+'</fieldset>'
						+'</form>'
						+'</div>';
	imgLinkDialog = $(imgLinkDialogHtml);
	imgLinkDialog.hide();
	imgLinkDialog.find('a#imgLinkDialog_submit').click(function() {
		var url = imgLinkDialog.find('#imgLinkDialog_url').val();
		var imgData = $.parseJSON($(imgLinkDialog.imgHolder).attr('data-image'));
		if (url==''){
			imgData.link = false;
			$(imgLinkDialog.linkButton).removeClass('selected');
		} else {
			imgData.link = url;
			$(imgLinkDialog.linkButton).addClass('selected');
		}
		$(imgLinkDialog.imgHolder).attr('data-image',JSON.stringify(imgData));
		hideImgLinkDialog();
		return false;
	});
	imgLinkDialog.find('a#imgLinkDialog_cancel').click(function() {
		hideImgLinkDialog();
		return false;
	});
	$('body').append(imgLinkDialog);
}

function hideImgLinkDialog(){
	hideOverlay();
	imgLinkDialog.fadeOut('fast');	
}

function showImgLinkDialog(linkButton, imgHolder){
	var imgData = $.parseJSON($(imgHolder).attr('data-image'));
	var url = imgData.link;
	if (url == false || url == ''){
		imgLinkDialog.find('#imgLinkDialog_url').val('');
		imgLinkDialog.find('h2').text('Add Link to Image');
		imgLinkDialog.find('#imgLinkDialog_submit').text('Add Link');
	} else {
		imgLinkDialog.find('#imgLinkDialog_url').val(url);
		imgLinkDialog.find('h2').text('Add Link to Image');
		imgLinkDialog.find('#imgLinkDialog_submit').text('Modify Link');
	}
	imgLinkDialog.linkButton = linkButton;
	imgLinkDialog.imgHolder = imgHolder;
	
	showOverlay();
	imgLinkDialog.fadeIn('normal');
	var bh = window.innerHeight;
	var bw = window.innerWidth;
	var h = imgLinkDialog.height();
	var w = imgLinkDialog.width();
	var posx = (bw-w)/2;
	var posy = (bh-h)/2;
	imgLinkDialog.css('top',posy+'px').css('left',posx+'px');
	
	imgLinkDialog.draggable({
		handle:'h2',
		cursor:'move'
	});
}


// link dialog
function initLinkDialog(){
	var linkDialogHtml = '<div id="linkDialog" class="dialog">'
					+'<h2>Add or Edit Link</h2>'
					+'<form>'
					+'<fieldset class="col1">'
					+'<label for="linkDialog_url"><span>Link address</span>'
					+'<input type="text" id="linkDialog_url"/></label>'
					+'<label for="linkDialog_title"><span>Link Title</span>'
					+'<input type="text" id="linkDialog_title"/></label>'
					+'<label for="linkDialog_target"><span>Link Target</span>'
					+'<input type="text" id="linkDialog_target"/></label>'
					+'</fieldset>'
					+'<fieldset class="col1 submit">'
					+'<label><span></span><a class="formButton" id="linkDialog_submit" href="#">Add Link</a></label>'
					+'<label><span></span><a class="formButton" id="linkDialog_cancel" href="#">Cancel</a></label>'
					+'</fieldset>'
					+'</form>'
					+'</div>';
	linkDialog = $(linkDialogHtml);
	linkDialog.hide();

	linkDialog.find('a#linkDialog_submit').click(function() {
		var wymIndex = linkDialog.wymIndex;
		var sel = $($.wymeditors(wymIndex).selected());
		var url = linkDialog.find('#linkDialog_url').val();
		var title = linkDialog.find('#linkDialog_title').val();
		var target = linkDialog.find('#linkDialog_target').val();
		_d(url);
		_d(title);
		_d(target);
		var linkText = url;
		var targetText = '';
		if (title != ''){
			linkText = title;
		}
		if (target!=''){
			targetText = ' target="'+target+'"';
		}
		
		if (url != '') {
			if (sel.is('body')){
				$.wymeditors(wymIndex).insert('<p><a href="'+url+'" title="'+title+'"'+targetText+'>'+linkText+'</a></p>');
			} else if (sel.is('a')){
				sel.attr('href',url).attr('title',title).attr('target',target);
			} else {
				var seltext = linkDialog.iframe.contentWindow.getSelection().toString();
				if (seltext != ''){
					$.wymeditors(wymIndex)._exec('createLink','XXX'); //replace with new link type thingy
					linkDialog.iframe.contentWindow.$('a[href=XXX]').replaceWith('<a href="'+url+'" title="'+title+'"'+targetText+'>'+seltext+'</a>')
				} else {
					$.wymeditors(wymIndex).insert('<a href="'+url+'" title="'+title+'"'+targetText+'>'+linkText+'</a>');
				}
				
			}
		}
		hideLinkDialog();
		return false;
	});
	linkDialog.find('a#linkDialog_cancel').click(function() {
		hideLinkDialog();
		return false;
	});
	
	
	$('body').append(linkDialog);
}

function hideLinkDialog(){
	hideOverlay();
	linkDialog.fadeOut('fast');	
}

function showLinkDialog(wymIndex, iframe){
	linkDialog.wymIndex = wymIndex;
	linkDialog.iframe = iframe;
	var sel = $($.wymeditors(wymIndex).selected());
	if (sel.is('a')){
		linkDialog.find('#linkDialog_url').val(sel.attr('href'));
		linkDialog.find('#linkDialog_title').val(sel.attr('title'));
		linkDialog.find('#linkDialog_target').val(sel.attr('target'));
		linkDialog.find('h2').text('Modify Link');
		linkDialog.find('#linkDialog_submit').text('Modify Link');
	} else {
		linkDialog.find('h2').text('Add Link');
		linkDialog.find('#linkDialog_submit').text('Add Link');
		linkDialog.find('input').val('');
	}
		
	showOverlay();
	linkDialog.fadeIn('normal');
	var bh = window.innerHeight;
	var bw = window.innerWidth;
	var h = linkDialog.height();
	var w = linkDialog.width();
	var posx = (bw-w)/2;
	var posy = (bh-h)/2;
	linkDialog.css('top',posy+'px').css('left',posx+'px');
	
	linkDialog.draggable({
		handle:'h2',
		cursor:'move'
	});
}

//paste from word;
function initPasteDialog(){
	var pasteDialogHtml = '<div id="pasteDialog" class="dialog">'
					+'<h2>Paste text from Word</h2>'
					+'<form>'
					+'<fieldset class="col1">'
					+'<label for="pasteDialog_text"><span>Paste text here</span>'
					+'<textarea id="pasteDialog_text" rows="12"/></textarea></label>'
					+'</fieldset>'
					+'<fieldset class="col1 submit">'
					+'<label><span></span><a class="formButton" id="pasteDialog_submit" href="#">Paste</a></label>'
					+'<label><span></span><a class="formButton" id="pasteDialog_cancel" href="#">Cancel</a></label>'
					+'</fieldset>'
					+'</form>'
					+'</div>';
	pasteDialog = $(pasteDialogHtml);
	pasteDialog.hide();
	pasteDialog.find('a#pasteDialog_submit').click(function() {
		if (pasteDialog.find('textarea').val()!=''){
			$.wymeditors(pasteDialog.wymIndex).paste(pasteDialog.find('textarea').val());
		}
		hidePasteDialog();
		return false;
	});
	pasteDialog.find('a#pasteDialog_cancel').click(function() {
		hidePasteDialog();
		return false;
	});
	
	
	$('body').append(pasteDialog);
}

function hidePasteDialog(){
	hideOverlay();
	pasteDialog.fadeOut('fast');	
}


function showPasteDialog(wymIndex, iframe){
	pasteDialog.wymIndex = wymIndex;
	pasteDialog.iframe = iframe;
	pasteDialog.find('textarea').val('');
	showOverlay();
	pasteDialog.fadeIn('normal');
	var bh = window.innerHeight;
	var bw = window.innerWidth;
	var h = pasteDialog.height();
	var w = pasteDialog.width();
	var posx = (bw-w)/2;
	var posy = (bh-h)/2;
	pasteDialog.css('top',posy+'px').css('left',posx+'px');
	pasteDialog.draggable({
		handle:'h2',
		cursor:'move'
	});
}


//overlay functions;
function initOverlay(){
	overlay=$('<div class="overlay"></div>');
	overlay.hide();
	$('body').append(overlay);
}
function showOverlay(){
	overlay.fadeIn('fast');
}
function hideOverlay(){
	overlay.fadeOut('fast');
}