
var siteFiles =[];
var fileBrowser = {};

$(document).ready(function() {
	var fileBrowserTemplate = '<div class="browser fileBrowser">';
	fileBrowserTemplate += '<div class="searchBox"><label><span>Search</span><input type="text" class="search" value=""/></label><a href="#" class="btn_refresh" title="">Refresh</a></div>';
	fileBrowserTemplate += '<ul id="fileContents"></ul>';
	fileBrowserTemplate += '<div class="nav">';
	fileBrowserTemplate += '<div class="pageNav"><p>Page</p><input type="text" class="currentPage" value="1"/><p>of <span class="currentPage">1</span></p><a class="prev" href="#">&lt;</a><a class="next" href="#">&gt;</a></div>';
	fileBrowserTemplate += '<div class="pageSize"><p>Page size:</p><a class="current" href="25">25</a><a href="50">50</a><a href="100">100</a><a href="200">200</a></div>';
	fileBrowserTemplate += '</div>';
	fileBrowserTemplate += '</div>';
	
	_d('browserBar started');
	if ($('.browserBar').length == 0){ //if browserBar does not exist
		$('body>h1').append('<div class="browserBar"></div>');
	}
	
	if ($('.browserBar a.fileBrowse').length == 0){
	 	$('.browserBar').append('<a class="browseButton fileBrowse" href="#">Files</a>');
	}



	fileBrowser = $(fileBrowserTemplate);
	$('body>h1').after(fileBrowser);
	fileBrowser.hide();
	fileBrowser.currentPage = 1;
	fileBrowser.searchFilter = '';
	fileBrowser.pageSize = 25;
	resizeFileBrowser();

	function fileBrowserLoad () {
		_d('starting load');
		$.getJSON('_getBrowserFiles.php',function(data) {
			_d('load done');
			_d(data);
			siteFiles = data.contents;
			filesMetaData = data.metadata;
			fileBrowser.searchFilter = '';
			fileBrowser.currentPage = 1;

			displayFiles();
		});
	}

	fileBrowserLoad();

	$('.browserBar a.fileBrowse').click(function() {
		$(this).siblings().removeClass('selected');
		$('div.browser').hide();
		if ($(this).hasClass('selected')) {
			$(this).removeClass('selected');
			fileBrowser.slideUp('fast');
		} else {
			$(this).addClass('selected');
			fileBrowser.slideDown('fast');
		}
		return false;
	});

	fileBrowser.find('a.btn_refresh').click(function(e){
		_d('refresh clicked');
		fileBrowserLoad();
		e.preventDefault();
	});

	fileBrowser.find('input.search').keyup(function(){
		_d('keyup');
		fileBrowser.searchFilter = $(this).val();
		displayFiles();
	});

	fileBrowser.find('input.currentPage').keyup(function(evt){
		var val = parseInt($(this).val()); 
		if (val > 0 && val <=fileBrowser.totalPages){
			fileBrowser.currentPage = val;
			displayFiles();
		}
	});
	
	fileBrowser.find('div.pageNav a.next, div.pageNav a.prev').click(function() {
		if ($(this).hasClass('disabled')==false){
			var inc = 1;
			if ($(this).hasClass('prev')){
				inc = -1;
			}
			var newPage = fileBrowser.currentPage + inc;
			if (newPage > 0 && newPage <=fileBrowser.totalPages){
				fileBrowser.currentPage = newPage;
				displayFiles();
			}
		}
		return false;
	});
	
	fileBrowser.find('div.pageSize a').click(function() {
		$(this).siblings().removeClass('current');
		$(this).addClass('current');
		fileBrowser.pageSize = $(this).attr('href');
		fileBrowser.currentPage = 1;
		displayFiles();
		return false;
	});
	
	$(window).resize(function(){
		resizeFileBrowser();
	});
	
	$(window).scroll(function(){
		//_d($(window).scrollTop());
		if ($(window).scrollTop() > 67) { 
			fileBrowser.addClass('static');
			$('.browserBar').addClass('static');
		} else {
			fileBrowser.removeClass('static');
			$('.browserBar').removeClass('static')
		}
	});
});  //----- end of document ready

function resizeFileBrowser(){
	var bh = window.innerHeight;
	var t = parseInt($('div.fileBrowser').css('top'));
	var h = bh - t - 5;
	var ih = h - 31-33;
	fileBrowser.css('height',h+'px');
	fileBrowser.find('ul#fileContents').css('height',ih+'px');
	_d(t);
}

function displayFiles(){
	var filteredFiles =[];
	if (fileBrowser.searchFilter == ''){
		filteredFiles = siteFiles;
	} else {
		_g('displayFiles iteration');
		for (var files in siteFiles){
			
			var matches = fileBrowser.searchFilter.split(' ');
			var hasMatch = false;
			for (match in matches){
				if (siteFiles[files].fileName.toLowerCase().indexOf(matches[match].toLowerCase()) != -1){
					hasMatch = true;
				}
			}
			if (hasMatch) {
				filteredFiles.push(siteFiles[files]);
			}
			
			_d(siteFiles[files]);
		}	
		_u();
	}
	var totalPages = 1;
	if (filteredFiles){
		var totalPages = Math.ceil(filteredFiles.length / fileBrowser.pageSize);
	}
	fileBrowser.totalPages = totalPages;
	_d(totalPages);
	fileBrowser.find('div.pageNav span.currentPage').text(totalPages);
	fileBrowser.find('input.currentPage').val(fileBrowser.currentPage);
	if (fileBrowser.currentPage == 1){
		fileBrowser.find('div.pageNav a.prev').addClass('disabled');
	} else {
		fileBrowser.find('div.pageNav a.prev').removeClass('disabled');
	}
	if (fileBrowser.currentPage == fileBrowser.totalPages){
		fileBrowser.find('div.pageNav a.next').addClass('disabled');
	} else {
		fileBrowser.find('div.pageNav a.next').removeClass('disabled');
	}
	var startIndex = (fileBrowser.currentPage-1) * fileBrowser.pageSize;
	var endIndex = startIndex + fileBrowser.pageSize;
	if (filteredFiles){
		if (endIndex > filteredFiles.length) {endIndex = filteredFiles.length;}
	} else {
		endIndex = 0;
	}
	fileBrowser.find('ul#fileContents').children().remove();
	
	var fileItemTemplate = '<li><img class="extension" width="16" height="16" src=""/><span class="fileName"></span></li>'; 
	
	for (var i = startIndex; i<endIndex; i++) {
		var fileItem = $(fileItemTemplate);
		fileItem.data('itemData',{
			resourceType:'file',
			physicalName:filteredFiles[i].physicalName,
			fileName:filteredFiles[i].fileName,
			extension:filteredFiles[i].extension
		});
		fileItem.find('img').attr('src','Gfx/Extensions/'+filteredFiles[i].extension+'.png');
		fileItem.find('span.fileName').text(filteredFiles[i].fileName+'.'+filteredFiles[i].extension);
		fileBrowser.find('ul#fileContents').append(fileItem);
	}
	
	$("ul#fileContents li").disableSelection();
	
	$("ul#fileContents li").draggable({
		helper: 'clone',
		stack: 'ul#fileContents li',
		iframeFix: true,
		cursorAt: { left: -15 , top:0 },
		cursor: 'pointer',
		start: function(event, ui) { $('.accept_file').addClass('accepting');},
		stop: function(event, ui) {$('.accept_file').removeClass('accepting'); } 
		
	});
}
