var siteComponents =[];
var componentsBrowser = {};
var componentTypes =[];
var componentInstances =[];

$(document).ready(function() {
	var componentsBrowserTemplate = '<div class="browser componentsBrowser">';
	componentsBrowserTemplate += '<div class="searchBox"><label><span>Search</span><input type="text" class="search" value=""/></label><a href="#" class="btn_refresh" title="">Refresh</a><div class="filters"></div></div>';
	componentsBrowserTemplate += '<ul id="componentContents"></ul>';
	componentsBrowserTemplate += '<div class="nav">';
	componentsBrowserTemplate += '<div class="pageNav"><p>Page</p><input type="text" class="currentPage" value="1"/><p>of <span class="currentPage">1</span></p><a class="prev" href="#">&lt;</a><a class="next" href="#">&gt;</a></div>';
	componentsBrowserTemplate += '<div class="pageSize"><p>Page size:</p><a class="current" href="25">25</a><a href="50">50</a><a href="100">100</a><a href="200">200</a></div>';
	componentsBrowserTemplate += '</div>';
	componentsBrowserTemplate += '</div>';

	_d('browserBar started');
	if ($('.browserBar').length == 0){ //if browserBar does not exist
		$('body>h1').append('<div class="browserBar"></div>');
	}

	if ($('.browserBar a.componentBrowse').length == 0){
		$('.browserBar').append('<a class="browseButton componentBrowse" href="#">Components</a>');
	}



	componentsBrowser = $(componentsBrowserTemplate);
	$('body>h1').after(componentsBrowser);
	componentsBrowser.hide();
	componentsBrowser.currentPage = 1;
	componentsBrowser.searchFilter = '';
	componentsBrowser.pageSize = 25;
	resizeComponentBrowser();

	function componentsBrowserLoad () {
		_d('starting load');
		$.getJSON('_getBrowserComponents.php',function(data) {
			_d('load done');
			_d(data);
			componentTypes = data.componentTypes;
			componentInstances = data.componentInstances;
			componentsBrowser.searchFilter = '';
			componentsBrowser.currentPage = 1;
			componentBrowserInitFilters();
			displayComponents();
		});
	}

	componentsBrowserLoad();

	function componentBrowserInitFilters() {
		for (var i in componentTypes){
			if ($(componentsBrowser).find('div.searchBox div.filters a.filter[componentType='+componentTypes[i].typeID+']').length == 0){
				var filterBtn = $('<a href="#" class="filter"></a>');
				filterBtn.attr('componentType',componentTypes[i].typeID).attr('title',componentTypes[i].label).css('background-image','url("/admin/Gfx/PageTypes/16/'+componentTypes[i].icon+'.png")').text(componentTypes[i].label);
				if (componentTypes[i].hidden == 0) {
					filterBtn.addClass('selected');
				}
				$(componentsBrowser).find('div.searchBox div.filters').append(filterBtn);
			}
		}
	}

	$('.browserBar a.componentBrowse').click(function() {
		$(this).siblings().removeClass('selected');
		$('div.browser').hide();
		if ($(this).hasClass('selected')) {
			$(this).removeClass('selected');
			componentsBrowser.slideUp('fast');
		} else {
			$(this).addClass('selected');
			componentsBrowser.slideDown('fast',function(){
				displayComponents();
			});
		}
		return false;
	});

	componentsBrowser.find('a.btn_refresh').click(function(e){
		_d('refresh clicked');
		componentsBrowserLoad();
		e.preventDefault();
	});

	componentsBrowser.find('input.search').keyup(function(){
		_d('keyup');
		componentsBrowser.searchFilter = $(this).val();
		displayComponents();
	});

	componentsBrowser.find('input.currentPage').keyup(function(evt){
		var val = parseInt($(this).val());
		if (val > 0 && val <=componentsBrowser.totalPages){
			componentsBrowser.currentPage = val;
			displayComponents();
		}
	});

	componentsBrowser.find('div.pageNav a.next, div.pageNav a.prev').click(function() {
		if ($(this).hasClass('disabled')==false){
			var inc = 1;
			if ($(this).hasClass('prev')){
				inc = -1;
			}
			var newPage = componentsBrowser.currentPage + inc;
			if (newPage > 0 && newPage <=componentsBrowser.totalPages){
				componentsBrowser.currentPage = newPage;
				displayComponents();
			}
		}
		return false;
	});

	componentsBrowser.find('div.searchBox div.filters').on('click','a.filter',function(e){
		var active = ($(this).hasClass('selected'));
		if (active){
			$(this).removeClass('selected');
		} else {
			$(this).addClass('selected');
		}

		displayComponents();
		e.preventDefault();
		return false;
	});

	componentsBrowser.find('div.pageSize a').click(function() {
		$(this).siblings().removeClass('current');
		$(this).addClass('current');
		componentsBrowser.pageSize = $(this).attr('href');
		componentsBrowser.currentPage = 1;
		displayComponents();
		return false;
	});

	$(window).resize(function(){
		resizeComponentBrowser();
	});

	$(window).scroll(function(){
		//_d($(window).scrollTop());
		if ($(window).scrollTop() > 67) {
			componentsBrowser.addClass('static');
			$('.browserBar').addClass('static');
		} else {
			componentsBrowser.removeClass('static');
			$('.browserBar').removeClass('static')
		}
	});
});  //----- end of document ready

function resizeComponentBrowser(){
	var bh = window.innerHeight;
	var xh = parseInt($('div.componentsBrowser div.searchBox').height());
	var t = parseInt($('div.componentsBrowser').css('top'));
	var h = bh - t - 10;
	var ih = h - 35- xh;
	componentsBrowser.css('height',h+'px');
	componentsBrowser.find('ul#componentContents').css('height',ih+'px');
}

function displayComponents(){
	var shownComponents =[];
	var shownCategories =[];
	componentsBrowser.find('div.searchBox div.filters a.filter.selected').each(function(){
		shownCategories.push($(this).attr('componenttype'));
	});
	_g('FILTERING BY COMPONENT TYPE');
	_d(shownCategories);
	for (var i in shownCategories){
		_d(componentInstances[shownCategories[i]]);
		if (componentInstances[shownCategories[i]] !== null){
			for (var j in componentInstances[shownCategories[i]]) {
				var newInstance = $.extend({},componentInstances[shownCategories[i]][j]);
				newInstance.resourceType = 'component';
				newInstance.typeID = componentTypes[shownCategories[i]].typeID;
				newInstance.label = componentTypes[shownCategories[i]].label;
				newInstance.icon = componentTypes[shownCategories[i]].icon;
				shownComponents.push(newInstance);
			}
		}
	}
	_u();
	_g('SHOWN COMPONENTS');
	_d(shownComponents);
	_u();
	var filteredComponents =[];
	if (componentsBrowser.searchFilter == ''){
		filteredComponents = shownComponents;
	} else {
		_g('displayComponents iteration');
		for (var components in shownComponents){

			var matches = componentsBrowser.searchFilter.split(' ');
			var hasMatch = false;
			for (match in matches){
				if (shownComponents[components].label.toLowerCase().indexOf(matches[match].toLowerCase()) != -1){
					hasMatch = true;
				}
				if (shownComponents[components].typeID.toLowerCase().indexOf(matches[match].toLowerCase()) != -1){
					hasMatch = true;
				}
				if (shownComponents[components].componentID.toLowerCase().indexOf(matches[match].toLowerCase()) != -1){
					hasMatch = true;
				}
			}
			if (hasMatch) {
				filteredComponents.push(shownComponents[components]);
			}

			_d(siteComponents[components]);
		}
		_u();
	}
	var totalPages = 1;
	if (filteredComponents){
		var totalPages = Math.ceil(filteredComponents.length / componentsBrowser.pageSize);
	}
	componentsBrowser.totalPages = totalPages;
	_d(totalPages);
	componentsBrowser.find('div.pageNav span.currentPage').text(totalPages);
	componentsBrowser.find('input.currentPage').val(componentsBrowser.currentPage);
	if (componentsBrowser.currentPage == 1){
		componentsBrowser.find('div.pageNav a.prev').addClass('disabled');
	} else {
		componentsBrowser.find('div.pageNav a.prev').removeClass('disabled');
	}
	if (componentsBrowser.currentPage == componentsBrowser.totalPages){
		componentsBrowser.find('div.pageNav a.next').addClass('disabled');
	} else {
		componentsBrowser.find('div.pageNav a.next').removeClass('disabled');
	}
	var startIndex = (componentsBrowser.currentPage-1) * componentsBrowser.pageSize;
	var endIndex = startIndex + componentsBrowser.pageSize;
	if (filteredComponents){
		if (endIndex > filteredComponents.length) {endIndex = filteredComponents.length;}
	} else {
		endIndex = 0;
	}
	componentsBrowser.find('ul#componentContents').children().remove();



	for (var i = startIndex; i<endIndex; i++) {
		var itemHtml = componentTypes[filteredComponents[i].typeID].listTemplate;
		for(var prop in filteredComponents[i]) {
			itemHtml = itemHtml.split('{'+prop+'}').join(filteredComponents[i][prop]);
			for (var itemProp in filteredComponents[i][prop]){
				itemHtml = itemHtml.split('{'+prop+'.'+itemProp+'}').join(filteredComponents[i][prop][itemProp]);
			}
		}
		var componentItem = $('<li>'+itemHtml+'</li>');
		componentItem.data('itemData', $.extend({},filteredComponents[i]));
		componentsBrowser.find('ul#componentContents').append(componentItem);
	}

	$("ul#componentContents li").disableSelection();

	$("ul#componentContents li").draggable({
		helper: 'clone',
		stack: 'ul#componentContents li',
		iframeFix: true,
		cursorAt: { left: -15 , top:0 },
		cursor: 'pointer',
		start: function(event, ui) { $('.accept_component').addClass('accepting');},
		stop: function(event, ui) {$('.accept_component').removeClass('accepting'); }

	});
	resizeComponentBrowser();
}
