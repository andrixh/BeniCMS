var siteVideos =[];
var videoBrowser = {};
var videoBrowserSearchTimeout;

var videoBrowserVideoViewTimeout;
var videoBrowserVideoView;

$(document).ready(function() {
	var imgBrowserTemplate = '<div class="browser videoBrowser">';
	imgBrowserTemplate += '<div class="searchBox"><label><span>Search</span><input type="text" class="search" value=""/></label><a href="#" class="btn_refresh" title="">Refresh</a></div>';
	imgBrowserTemplate += '<ul id="videoContents"></ul>';
	imgBrowserTemplate += '<div class="nav">';
	imgBrowserTemplate += '<div class="pageNav"><p>Page</p><input type="text" class="currentPage" value="1"/><p>of <span class="currentPage">1</span></p><a class="prev" href="#">&lt;</a><a class="next" href="#">&gt;</a></div>';
	imgBrowserTemplate += '<div class="pageSize"><p>Page size:</p><a class="current" href="25">25</a><a href="50">50</a><a href="100">100</a><a href="200">200</a></div>';
	imgBrowserTemplate += '</div>';
	imgBrowserTemplate += '</div>';

	_d('browserBar started');
	if ($('.browserBar').length == 0){ //if browserBar does not exist
		$('body>h1').append('<div class="browserBar"></div>');
	}

	if ($('.browserBar a.videoBrowse').length == 0){
		$('.browserBar').append('<a class="browseButton videoBrowse" href="#">Videos</a>');
	}

	videoBrowser = $(imgBrowserTemplate);
	$('body>h1').after(videoBrowser);
	videoBrowser.hide();
	videoBrowser.currentPage = 1;
	videoBrowser.searchFilter = '';
	videoBrowser.pageSize = 25;
	resizeVideoBrowser();

	function videoBrowserLoad(){
		$.getJSON('_getBrowserVideos.php',function(data) {
			_d(data);
			siteVideos = data.contents;
			videoMetaData = data.metadata;
			videoBrowser.searchFilter = '';
			videoBrowser.currentPage = 1;
			displayVideos();
		});
	}
	videoBrowserLoad();

	$('.browserBar a.videoBrowse').click(function() {
		$(this).siblings().removeClass('selected');
		$('div.browser').hide();

		if ($(this).hasClass('selected')) {
			$(this).removeClass('selected');
			videoBrowser.slideUp('fast');
		} else {
			$(this).addClass('selected');
			videoBrowser.slideDown('fast');
		}
		return false;
	});

	videoBrowser.find('a.btn_refresh').click(function(e){
		_d('refresh clicked');
		videoBrowserLoad();
		e.preventDefault();
	});

	videoBrowser.find('input.search').keyup(function(){
		_d('keyup');
		videoBrowser.searchFilter = $(this).val();
		displayVideos();
	});



	videoBrowser.find('input.currentPage').keyup(function(evt){
		var val = parseInt($(this).val());
		if (val > 0 && val <=videoBrowser.totalPages){
			videoBrowser.currentPage = val;
			displayVideos();
		}
	});

	videoBrowser.find('div.pageNav a.next, div.pageNav a.prev').click(function() {
		if ($(this).hasClass('disabled')==false){
			var inc = 1;
			if ($(this).hasClass('prev')){
				inc = -1;
			}
			var newPage = videoBrowser.currentPage + inc;
			if (newPage > 0 && newPage <=videoBrowser.totalPages){
				videoBrowser.currentPage = newPage;
				displayVideos();
			}
		}
		return false;
	});

	videoBrowser.find('div.pageSize a').click(function() {
		$(this).siblings().removeClass('current');
		$(this).addClass('current');
		videoBrowser.pageSize = $(this).attr('href');
		videoBrowser.currentPage = 1;
		displayVideos();
		return false;
	});

	$(window).resize(function(){
		resizeVideoBrowser();
	});

	$(window).scroll(function(){
		//_d($(window).scrollTop());
		if ($(window).scrollTop() > 67) {
			videoBrowser.addClass('static');
			$('.browserBar').addClass('static');
		} else {
			videoBrowser.removeClass('static');
			$('.browserBar').removeClass('static')
		}
	});

	$('ul#videoContents img').live('mouseover',function(){
		if ($('.ui-draggable-dragging').length == 0){
			videoBrowserVideoView = $(this);
			videoBrowserVideoViewTimeout = setTimeout("previewVideoBrowserVideo(videoBrowserVideoView)",300);
		}
	});

	$('ul#videoContents img').live('mouseout',function(){
		clearTimeout(videoBrowserVideoViewTimeout);
		$('div.videoView').remove();
	});

	$('ul#videoContents img').live('mousedown',function(){
		clearTimeout(videoBrowserVideoViewTimeout);
		$('div.videoView').remove();
	});

});  //----- end of document ready

function previewVideoBrowserVideo(videoView){
	_d(videoView);
	var service = $(videoView).attr('service');
	var videoID = $(videoView).attr('videoID');
	var label = $(videoView).attr('alt');
	$('body').append('<div class="videoView"></div>');

	$('div.videoView').html('<iframe width="200px" height="140px" id="ytplayer" type="text/html" frameborder="0"></iframe><p>'+label+'</p>');
	var src = '';
	if (service == 'youtube'){
		src = 'http://www.youtube.com/embed/'+videoID+'?autoplay=1&controls=0&modestbranding=1&rel=0&showinfo=0';
	} else if (service == 'vimeo'){
		src = 'http://player.vimeo.com/video/'+videoID+'?autoplay=1&title=0&byline=0&portrait=0';
	} else if (service == 'dailymotion'){
		src = 'http://www.dailymotion.com/embed/video/'+videoID+'?autoplay=1&related=0;network=cellular&logo=0';
	}
	$('div.videoView iframe').attr('src',src);
	$('div.videoView').show();
	$('div.videoView').position({
		my: "right top",
		at: "left bottom",
		of: videoView
	});

}

function resizeVideoBrowser(){
	var bh = window.innerHeight;
	var t = parseInt($('div.videoBrowser').css('top'));
	var h = bh - t - 5;
	var ih = h - 31-33;
	videoBrowser.css('height',h+'px');
	videoBrowser.find('ul#videoContents').css('height',ih+'px');
	_d(t);
}

function displayVideos(){
	var filteredVideos =[];
	if (videoBrowser.searchFilter == ''){
		filteredVideos = siteVideos;
	} else {
		_g('displayVideos iteration');
		for (var img in siteVideos){

			var matches = videoBrowser.searchFilter.split(' ');
			var hasMatch = false;
			var matchCount = 0;
			for (match in matches){
				if (siteVideos[img].label.toLowerCase().indexOf(matches[match].toLowerCase()) != -1){
					hasMatch = true;
					matchCount++;
				}
			}
			/*if (hasMatch) {
			 filteredVideos.push(siteVideos[img]);
			 }*/
			if (matchCount == matches.length) {
				filteredVideos.push(siteVideos[img]);
			}
			//_d(siteVideos[img]);
		}
		_u();
	}
	var totalPages = Math.ceil(filteredVideos.length / videoBrowser.pageSize);

	videoBrowser.totalPages = totalPages;
	_d(totalPages);
	videoBrowser.find('div.pageNav span.currentPage').text(totalPages);
	videoBrowser.find('input.currentPage').val(videoBrowser.currentPage);
	if (videoBrowser.currentPage == 1){
		videoBrowser.find('div.pageNav a.prev').addClass('disabled');
	} else {
		videoBrowser.find('div.pageNav a.prev').removeClass('disabled');
	}
	if (videoBrowser.currentPage == videoBrowser.totalPages){
		videoBrowser.find('div.pageNav a.next').addClass('disabled');
	} else {
		videoBrowser.find('div.pageNav a.next').removeClass('disabled');
	}
	var startIndex = (videoBrowser.currentPage-1) * videoBrowser.pageSize;
	var endIndex = startIndex + videoBrowser.pageSize;
	if (endIndex > filteredVideos.length) {endIndex = filteredVideos.length;}
	videoBrowser.find('ul#videoContents').children().remove();
	var imgItemTemplate = '<li><img src="" width="50" height="50" /></li>';

	for (var i = startIndex; i<endIndex; i++) {
		var imgItem = $(imgItemTemplate);
		imgItem.data('itemData',{
			resourceType:'video',
			physicalName:filteredVideos[i].physicalName,
			service:filteredVideos[i].service,
			videoID:filteredVideos[i].videoID,
			thumbnail:filteredVideos[i].thumbnail,
			thumbnailType:filteredVideos[i].thumbnailType
		}).addClass(filteredVideos[i].service);
		imgItem.find('img')
			.attr('src',videoMetaData.imagePath+filteredVideos[i].thumbnail+'_50_50_C_30.'+filteredVideos[i].thumbnailType)
			.attr('alt',filteredVideos[i].label)
			.attr('service',filteredVideos[i].service)
			.attr('videoID',filteredVideos[i].videoID);


		videoBrowser.find('ul#videoContents').append(imgItem);
	}

	$("ul#videoContents li").disableSelection();

	$("ul#videoContents li").draggable({
		helper: 'clone',
		stack: 'ul#videoContents li',
		iframeFix: true,
		cursorAt: { left: -15 , top:0 },
		cursor: 'pointer',
		start: function(event, ui) { $('.accept_video').addClass('accepting');},
		stop: function(event, ui) {$('.accept_video').removeClass('accepting'); }

	});

	_d(filteredVideos.length);

}






