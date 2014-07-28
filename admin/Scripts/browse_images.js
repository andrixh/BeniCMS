var siteImages =[];
var imageBrowser = {};
var imageBrowserSearchTimeout;

var imageBrowserImageViewTimeout;
var imageBrowserImageView;

$(document).ready(function() {
	var imgBrowserTemplate = '<div class="browser imageBrowser">';
	imgBrowserTemplate += '<div class="searchBox"><label><span>Search</span><input type="text" class="search" value=""/></label><a href="#" class="btn_refresh" title="">Refresh</a></div>';
	imgBrowserTemplate += '<ul id="imageContents"></ul>';
	imgBrowserTemplate += '<div class="nav">';
	imgBrowserTemplate += '<div class="pageNav"><p>Page</p><input type="text" class="currentPage" value="1"/><p>of <span class="currentPage">1</span></p><a class="prev" href="#">&lt;</a><a class="next" href="#">&gt;</a></div>';
	imgBrowserTemplate += '<div class="pageSize"><p>Page size:</p><a class="current" href="25">25</a><a href="50">50</a><a href="100">100</a><a href="200">200</a></div>';
	imgBrowserTemplate += '</div>';
	imgBrowserTemplate += '</div>';
	
	_d('browserBar started');
	if ($('.browserBar').length == 0){ //if browserBar does not exist
		$('body>h1').append('<div class="browserBar"></div>');
	}
	
	if ($('.browserBar a.imageBrowse').length == 0){
	 	$('.browserBar').append('<a class="browseButton imageBrowse" href="#">Images</a>');
	}



	
	imageBrowser = $(imgBrowserTemplate);
	$('body>h1').after(imageBrowser);
	imageBrowser.hide();
	imageBrowser.currentPage = 1;
	imageBrowser.searchFilter = '';
	imageBrowser.pageSize = 25;
	resizeImageBrowser();

	function imageBrowserLoad() {
		_d('starting load');
		$.getJSON('_getBrowserImages.php',function(data) {
			_d(data);
			siteImages = data.contents;
			imageMetaData = data.metadata;
			//_d(siteImages);
			imageBrowser.searchFilter = '';
			imageBrowser.currentPage = 1;
			displayImages();
		});
	}
	imageBrowserLoad();

	$('.browserBar a.imageBrowse').click(function() {
		$(this).siblings().removeClass('selected');
		$('div.browser').hide();
			
		if ($(this).hasClass('selected')) {
			$(this).removeClass('selected');
			imageBrowser.slideUp('fast');
		} else {
			$(this).addClass('selected');
			imageBrowser.slideDown('fast');
		}
		return false;
	});

	imageBrowser.find('a.btn_refresh').click(function(e){
		_d('refresh clicked');
		imageBrowserLoad();
		e.preventDefault();
	});

	imageBrowser.find('input.search').keyup(function(){
		_d('keyup');
		imageBrowser.searchFilter = $(this).val();
		displayImages();
	});
	
	imageBrowser.find('input.currentPage').keyup(function(evt){
		var val = parseInt($(this).val()); 
		if (val > 0 && val <=imageBrowser.totalPages){
			imageBrowser.currentPage = val;
			displayImages();
		}
	});
	
	imageBrowser.find('div.pageNav a.next, div.pageNav a.prev').click(function() {
		if ($(this).hasClass('disabled')==false){
			var inc = 1;
			if ($(this).hasClass('prev')){
				inc = -1;
			}
			var newPage = imageBrowser.currentPage + inc;
			if (newPage > 0 && newPage <=imageBrowser.totalPages){
				imageBrowser.currentPage = newPage;
				displayImages();
			}
		}
		return false;
	});
	
	imageBrowser.find('div.pageSize a').click(function() {
		$(this).siblings().removeClass('current');
		$(this).addClass('current');
		imageBrowser.pageSize = $(this).attr('href');
		imageBrowser.currentPage = 1;
		displayImages();
		return false;
	});
	
	$(window).resize(function(){
		resizeImageBrowser();
	});
	
	$(window).scroll(function(){
		//_d($(window).scrollTop());
		if ($(window).scrollTop() > 67) { 
			imageBrowser.addClass('static');
			$('.browserBar').addClass('static');
		} else {
			imageBrowser.removeClass('static');
			$('.browserBar').removeClass('static')
		}
	});
	
	$('ul#imageContents img').live('mouseover',function(){
		if ($('.ui-draggable-dragging').length == 0){
			imageBrowserImageView = $(this);
			imageBrowserImageViewTimeout = setTimeout("previewImageBrowserImage(imageBrowserImageView)",300);
		}
	});
												
	$('ul#imageContents img').live('mouseout',function(){
		clearTimeout(imageBrowserImageViewTimeout);	
		$('div.imageView').remove();	
	});
	
	$('ul#imageContents img').live('mousedown',function(){
		clearTimeout(imageBrowserImageViewTimeout);	
		$('div.imageView').remove();	
	});
	
});  //----- end of document ready

function previewImageBrowserImage(imageView){
	_d(imageView);
	$('body').append('<div class="imageView"></div>');
	var chunks = $(imageView).attr('src').split('.');
	var ext = chunks[chunks.length-1];
	var chunks = $(imageView).attr('src').split('/');
	var label = $(imageView).attr('alt');
	var pname = chunks[chunks.length-1].split('_')[0];
	$('div.imageView').html('<img src="/Images/Resized/'+pname+'_200_200_B_30_t.'+ext+'"/><p>'+label+'</p>');
	$('div.imageView img').load(function(){
		$('div.imageView').show();
		$('div.imageView').position({
			my: "right top",
			at: "left bottom",
			of: imageView
		});	
	});
}

function resizeImageBrowser(){
	var bh = window.innerHeight;
	var t = parseInt($('div.imageBrowser').css('top'));
	var h = bh - t - 5;
	var ih = h - 31-33;
	imageBrowser.css('height',h+'px');
	imageBrowser.find('ul#imageContents').css('height',ih+'px');
	_d(t);
}

function displayImages(){
	var filteredImages =[];
	if (imageBrowser.searchFilter == ''){
		filteredImages = siteImages;
	} else {
		_g('displayImages iteration');
		for (var img in siteImages){
			
			var matches = imageBrowser.searchFilter.split(' ');
			var hasMatch = false;
			var matchCount = 0;
			for (match in matches){
				if (siteImages[img].label.toLowerCase().indexOf(matches[match].toLowerCase()) != -1){
					hasMatch = true;
					matchCount++;
				}
			}
			/*if (hasMatch) {
				filteredImages.push(siteImages[img]);
			}*/
			if (matchCount == matches.length) {
				filteredImages.push(siteImages[img]);
			}
			//_d(siteImages[img]);
		}	
		_u();
	}
	var totalPages = Math.ceil(filteredImages.length / imageBrowser.pageSize);
	
	imageBrowser.totalPages = totalPages;
	_d(totalPages);
	imageBrowser.find('div.pageNav span.currentPage').text(totalPages);
	imageBrowser.find('input.currentPage').val(imageBrowser.currentPage);
	if (imageBrowser.currentPage == 1){
		imageBrowser.find('div.pageNav a.prev').addClass('disabled');
	} else {
		imageBrowser.find('div.pageNav a.prev').removeClass('disabled');
	}
	if (imageBrowser.currentPage == imageBrowser.totalPages){
		imageBrowser.find('div.pageNav a.next').addClass('disabled');
	} else {
		imageBrowser.find('div.pageNav a.next').removeClass('disabled');
	}
	var startIndex = (imageBrowser.currentPage-1) * imageBrowser.pageSize;
	var endIndex = startIndex + imageBrowser.pageSize;
	if (endIndex > filteredImages.length) {endIndex = filteredImages.length;}
	imageBrowser.find('ul#imageContents').children().remove();
	var imgItemTemplate = '<li><img src="" width="50" height="50" /></li>'; 
	
	for (var i = startIndex; i<endIndex; i++) {
		var imgItem = $(imgItemTemplate);
		
		imgItem.data('itemData',{
			resourceType:'image',
			physicalName:filteredImages[i].physicalName,
			type:filteredImages[i].type
		});
		imgItem.find('img').attr('src',imageMetaData.imagePath+filteredImages[i].physicalName+'_50_50_C_30.'+filteredImages[i].type);
		imgItem.find('img').attr('alt',filteredImages[i].label);
		imageBrowser.find('ul#imageContents').append(imgItem);
	}
	
	$("ul#imageContents li").disableSelection();
	
	$("ul#imageContents li").draggable({
		helper: 'clone',
		stack: 'ul#imageContents li',
		iframeFix: true,
		cursorAt: { left: -15 , top:0 },
		cursor: 'pointer',
		start: function(event, ui) { $('.accept_image').addClass('accepting');},
		stop: function(event, ui) {$('.accept_image').removeClass('accepting'); } 
	
	});
	
	_d(filteredImages.length);
	
}





