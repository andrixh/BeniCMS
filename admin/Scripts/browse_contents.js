var siteContents =[];
var contentsBrowser = {};
var contentTypes =[];
var contentInstances =[];

$(document).ready(function() {
	var contentsBrowserTemplate = '<div class="browser contentsBrowser">';
	contentsBrowserTemplate += '<div class="searchBox"><label><span>Search</span><input type="text" class="search" value=""/></label><a href="#" class="btn_refresh" title="">Refresh</a><div class="filters"></div></div>';
	contentsBrowserTemplate += '<ul id="contentContents"></ul>';
	contentsBrowserTemplate += '<div class="nav">';
	contentsBrowserTemplate += '<div class="pageNav"><p>Page</p><input type="text" class="currentPage" value="1"/><p>of <span class="currentPage">1</span></p><a class="prev" href="#">&lt;</a><a class="next" href="#">&gt;</a></div>';
	contentsBrowserTemplate += '<div class="pageSize"><p>Page size:</p><a class="current" href="25">25</a><a href="50">50</a><a href="100">100</a><a href="200">200</a></div>';
	contentsBrowserTemplate += '</div>';
	contentsBrowserTemplate += '</div>';

	_d('browserBar started');
	if ($('.browserBar').length == 0){ //if browserBar does not exist
		$('body>h1').append('<div class="browserBar"></div>');
	}

	if ($('.browserBar a.contentBrowse').length == 0){
		$('.browserBar').append('<a class="browseButton contentBrowse" href="#">Contents</a>');
	}



	contentsBrowser = $(contentsBrowserTemplate);
	$('body>h1').after(contentsBrowser);
	contentsBrowser.hide();
	contentsBrowser.currentPage = 1;
	contentsBrowser.searchFilter = '';
	contentsBrowser.pageSize = 25;
	resizeContentBrowser();

	function contentsBrowserLoad () {
		_d('starting load');
		$.getJSON('_getBrowserContents.php',function(data) {
			_d('load done');
			_d(data);
			contentTypes = data.contentTypes;
			contentInstances = data.contentInstances;
			contentsBrowser.searchFilter = '';
			contentsBrowser.currentPage = 1;
			contentBrowserInitFilters();
			displayContents();
		});
	}

	contentsBrowserLoad();

	function contentBrowserInitFilters() {
		for (var i in contentTypes){
			if ($(contentsBrowser).find('div.searchBox div.filters a.filter[contentType='+contentTypes[i].typeID+']').length == 0){
				var filterBtn = $('<a href="#" class="filter"></a>');
				filterBtn.attr('contentType',contentTypes[i].typeID).attr('title',contentTypes[i].label).css('background-image','url("/admin/Gfx/PageTypes/16/'+contentTypes[i].icon+'.png")').text(contentTypes[i].label);
				if (contentTypes[i].hidden == 0) {
					filterBtn.addClass('selected');
				}
				$(contentsBrowser).find('div.searchBox div.filters').append(filterBtn);
			}
		}
	}

	$('.browserBar a.contentBrowse').click(function() {
		$(this).siblings().removeClass('selected');
		$('div.browser').hide();
		if ($(this).hasClass('selected')) {
			$(this).removeClass('selected');
			contentsBrowser.slideUp('fast');
		} else {
			$(this).addClass('selected');
			contentsBrowser.slideDown('fast',function(){
				displayContents();
			});
		}
		return false;
	});

	contentsBrowser.find('a.btn_refresh').click(function(e){
		_d('refresh clicked');
		contentsBrowserLoad();
		e.preventDefault();
	});

	contentsBrowser.find('input.search').keyup(function(){
		_d('keyup');
		contentsBrowser.searchFilter = $(this).val();
		displayContents();
	});

	contentsBrowser.find('input.currentPage').keyup(function(evt){
		var val = parseInt($(this).val());
		if (val > 0 && val <=contentsBrowser.totalPages){
			contentsBrowser.currentPage = val;
			displayContents();
		}
	});

	contentsBrowser.find('div.pageNav a.next, div.pageNav a.prev').click(function() {
		if ($(this).hasClass('disabled')==false){
			var inc = 1;
			if ($(this).hasClass('prev')){
				inc = -1;
			}
			var newPage = contentsBrowser.currentPage + inc;
			if (newPage > 0 && newPage <=contentsBrowser.totalPages){
				contentsBrowser.currentPage = newPage;
				displayContents();
			}
		}
		return false;
	});

	contentsBrowser.find('div.searchBox div.filters').on('click','a.filter',function(e){
		var active = ($(this).hasClass('selected'));
		if (active){
			$(this).removeClass('selected');
		} else {
			$(this).addClass('selected');
		}
		displayContents();
		e.preventDefault();
		return false;
	});

	contentsBrowser.find('div.pageSize a').click(function() {
		$(this).siblings().removeClass('current');
		$(this).addClass('current');
		contentsBrowser.pageSize = $(this).attr('href');
		contentsBrowser.currentPage = 1;
		displayContents();
		return false;
	});

	$(window).resize(function(){
		resizeContentBrowser();
	});

	$(window).scroll(function(){
		//_d($(window).scrollTop());
		if ($(window).scrollTop() > 67) {
			contentsBrowser.addClass('static');
			$('.browserBar').addClass('static');
		} else {
			contentsBrowser.removeClass('static');
			$('.browserBar').removeClass('static')
		}
	});
});  //----- end of document ready

function resizeContentBrowser(){
	var bh = window.innerHeight;
	var xh = parseInt($('div.contentsBrowser div.searchBox').height());
	var t = parseInt($('div.contentsBrowser').css('top'));
	var h = bh - t - 10;
	var ih = h - 35- xh;
	contentsBrowser.css('height',h+'px');
	contentsBrowser.find('ul#contentContents').css('height',ih+'px');
}

function displayContents(){
	var shownContents =[];
	var shownCategories =[];
	contentsBrowser.find('div.searchBox div.filters a.filter.selected').each(function(){
		shownCategories.push($(this).attr('contenttype'));
	});
	_g('FILTERING BY COMPONENT TYPE');
	_d(shownCategories);
	for (var i in shownCategories){
		_d(contentInstances[shownCategories[i]]);
		if (contentInstances[shownCategories[i]] !== null){
			for (var j in contentInstances[shownCategories[i]]) {
				var newInstance = $.extend({},contentInstances[shownCategories[i]][j]);
				newInstance.resourceType = 'content';
				newInstance.typeID = contentTypes[shownCategories[i]].typeID;
				newInstance.listTemplate = contentTypes[shownCategories[i]].listTemplate;
				newInstance.label = contentTypes[shownCategories[i]].label;
				newInstance.icon = contentTypes[shownCategories[i]].icon;
				shownContents.push(newInstance);
			}
		}
	}
	_u();
	_g('SHOWN COMPONENTS');
	_d(shownContents);
	_u();
	var filteredContents =[];
	if (contentsBrowser.searchFilter == ''){
		filteredContents = shownContents;
	} else {
		_g('displayContents iteration');
		for (var contents in shownContents){

			var matches = contentsBrowser.searchFilter.split(' ');
			var hasMatch = false;
			for (match in matches){
				if (shownContents[contents].label.toLowerCase().indexOf(matches[match].toLowerCase()) != -1){
					hasMatch = true;
				}
				if (shownContents[contents].typeID.toLowerCase().indexOf(matches[match].toLowerCase()) != -1){
					hasMatch = true;
				}
				if (shownContents[contents].contentID.toLowerCase().indexOf(matches[match].toLowerCase()) != -1){
					hasMatch = true;
				}
			}
			if (hasMatch) {
				filteredContents.push(shownContents[contents]);
			}

			_d(siteContents[contents]);
		}
		_u();
	}
	var totalPages = 1;
	if (filteredContents){
		var totalPages = Math.ceil(filteredContents.length / contentsBrowser.pageSize);
	}
	contentsBrowser.totalPages = totalPages;
	_d(totalPages);
	contentsBrowser.find('div.pageNav span.currentPage').text(totalPages);
	contentsBrowser.find('input.currentPage').val(contentsBrowser.currentPage);
	if (contentsBrowser.currentPage == 1){
		contentsBrowser.find('div.pageNav a.prev').addClass('disabled');
	} else {
		contentsBrowser.find('div.pageNav a.prev').removeClass('disabled');
	}
	if (contentsBrowser.currentPage == contentsBrowser.totalPages){
		contentsBrowser.find('div.pageNav a.next').addClass('disabled');
	} else {
		contentsBrowser.find('div.pageNav a.next').removeClass('disabled');
	}
	var startIndex = (contentsBrowser.currentPage-1) * contentsBrowser.pageSize;
	var endIndex = startIndex + contentsBrowser.pageSize;
	if (filteredContents){
		if (endIndex > filteredContents.length) {endIndex = filteredContents.length;}
	} else {
		endIndex = 0;
	}
	contentsBrowser.find('ul#contentContents').children().remove();



	for (var i = startIndex; i<endIndex; i++) {
		var itemHtml = contentTypes[filteredContents[i].typeID].listTemplate;



		for (var prop in filteredContents[i]) {
			itemHtml = itemHtml.split('{'+prop+'}').join(filteredContents[i][prop]);
			for (var itemProp in filteredContents[i][prop]){
				itemHtml = itemHtml.split('{'+prop+'.'+itemProp+'}').join(filteredContents[i][prop][itemProp]);
			}
		}
		var contentItem = $('<li>'+itemHtml+'</li>');
		contentItem.data('itemData', $.extend({},filteredContents[i]));
		contentsBrowser.find('ul#contentContents').append(contentItem);
	}

	$("ul#contentContents li").disableSelection();

	$("ul#contentContents li").draggable({
		helper: 'clone',
		stack: 'ul#contentContents li',
		iframeFix: true,
		cursorAt: { left: -15 , top:0 },
		cursor: 'pointer',
		start: function(event, ui) {_d(this);_d(ui.helper); $('.accept_content_'+$(this).data('itemData').typeID).addClass('accepting');},
		stop: function(event, ui) {$('.accept_content').removeClass('accepting'); }

	});
	resizeContentBrowser();
}
