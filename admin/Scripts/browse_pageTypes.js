var sitePageTypes = [];
var pageTypeBrowser = {};

$(document).ready(function () {
    var pageTypeBrowserTemplate = '<div class="browser pageTypeBrowser">';
    pageTypeBrowserTemplate += '<div class="searchBox"><label><span>Search</span><input type="text" class="search" value=""/></label><a href="#" class="btn_refresh" title="">Refresh</a></div>';
    pageTypeBrowserTemplate += '<ul id="pageTypeContents"></ul>';
    pageTypeBrowserTemplate += '<div class="nav">';
    pageTypeBrowserTemplate += '<div class="pageNav"><p>Page</p><input type="text" class="currentPage" value="1"/><p>of <span class="currentPage">1</span></p><a class="prev" href="#">&lt;</a><a class="next" href="#">&gt;</a></div>';
    pageTypeBrowserTemplate += '<div class="pageSize"><p>Page size:</p><a class="current" href="25">25</a><a href="50">50</a><a href="100">100</a><a href="200">200</a></div>';
    pageTypeBrowserTemplate += '</div>';
    pageTypeBrowserTemplate += '</div>';

    _d('browserBar started');
    if ($('.browserBar').length == 0) { //if browserBar does not exist
        $('body>h1').append('<div class="browserBar"></div>');
    }

    if ($('.browserBar a.pageTypeBrowse').length == 0) {
        $('.browserBar').append('<a class="browseButton pageTypeBrowse" href="#">PageTypes</a>');
    }


    pageTypeBrowser = $(pageTypeBrowserTemplate);
    $('body>h1').after(pageTypeBrowser);
    pageTypeBrowser.hide();
    pageTypeBrowser.currentPage = 1;
    pageTypeBrowser.searchFilter = '';
    pageTypeBrowser.pageSize = 25;
    resizePageTypeBrowser();

    function pageTypeBrowserLoad() {
        _d('starting load');
        $.getJSON('_getBrowserPageTypes.php', function (data) {
            _d('load done');
            _d(data);
            sitePageTypes = data;
            pageTypesMetaData = data.metadata;
            pageTypeBrowser.searchFilter = '';
            pageTypeBrowser.currentPage = 1;

            displayPageTypes();
        });
    }

    pageTypeBrowserLoad();

    $('.browserBar a.pageTypeBrowse').click(function () {
        $(this).siblings().removeClass('selected');
        $('div.browser').hide();
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
            pageTypeBrowser.slideUp('fast');
        } else {
            $(this).addClass('selected');
            pageTypeBrowser.slideDown('fast');
        }
        return false;
    });

    pageTypeBrowser.find('a.btn_refresh').click(function (e) {
        _d('refresh clicked');
        pageTypeBrowserLoad();
        e.preventDefault();
    });

    pageTypeBrowser.find('input.search').keyup(function () {
        _d('keyup');
        pageTypeBrowser.searchFilter = $(this).val();
        displayPageTypes();
    });

    pageTypeBrowser.find('input.currentPage').keyup(function (evt) {
        var val = parseInt($(this).val());
        if (val > 0 && val <= pageTypeBrowser.totalPages) {
            pageTypeBrowser.currentPage = val;
            displayPageTypes();
        }
    });

    pageTypeBrowser.find('div.pageNav a.next, div.pageNav a.prev').click(function () {
        if ($(this).hasClass('disabled') == false) {
            var inc = 1;
            if ($(this).hasClass('prev')) {
                inc = -1;
            }
            var newPage = pageTypeBrowser.currentPage + inc;
            if (newPage > 0 && newPage <= pageTypeBrowser.totalPages) {
                pageTypeBrowser.currentPage = newPage;
                displayPageTypes();
            }
        }
        return false;
    });

    pageTypeBrowser.find('div.pageSize a').click(function () {
        $(this).siblings().removeClass('current');
        $(this).addClass('current');
        pageTypeBrowser.pageSize = $(this).attr('href');
        pageTypeBrowser.currentPage = 1;
        displayPageTypes();
        return false;
    });

    $(window).resize(function () {
        resizePageTypeBrowser();
    });

    $(window).scroll(function () {
        //_d($(window).scrollTop());
        if ($(window).scrollTop() > 67) {
            pageTypeBrowser.addClass('static');
            $('.browserBar').addClass('static');
        } else {
            pageTypeBrowser.removeClass('static');
            $('.browserBar').removeClass('static')
        }
    });
});  //----- end of document ready

function resizePageTypeBrowser() {
    var bh = window.innerHeight;
    var t = parseInt($('div.pageTypeBrowser').css('top'));
    var h = bh - t - 5;
    var ih = h - 31 - 33;
    pageTypeBrowser.css('height', h + 'px');
    pageTypeBrowser.find('ul#pageTypeContents').css('height', ih + 'px');
    _d(t);
}

function displayPageTypes() {
    var filteredPageTypes = [];
    if (pageTypeBrowser.searchFilter == '') {
        filteredPageTypes = sitePageTypes;
    } else {
        _g('displayPageTypes iteration');
        for (var pageTypes in sitePageTypes) {

            var matches = pageTypeBrowser.searchFilter.split(' ');
            var hasMatch = false;
            for (match in matches) {
                if (sitePageTypes[pageTypes].pageTypeName.toLowerCase().indexOf(matches[match].toLowerCase()) != -1) {
                    hasMatch = true;
                }
            }
            if (hasMatch) {
                filteredPageTypes.push(sitePageTypes[pageTypes]);
            }

            _d(sitePageTypes[pageTypes]);
        }
        _u();
    }
    var totalPages = 1;
    if (filteredPageTypes) {
        var totalPages = Math.ceil(filteredPageTypes.length / pageTypeBrowser.pageSize);
    }
    pageTypeBrowser.totalPages = totalPages;
    _d(totalPages);
    pageTypeBrowser.find('div.pageNav span.currentPage').text(totalPages);
    pageTypeBrowser.find('input.currentPage').val(pageTypeBrowser.currentPage);
    if (pageTypeBrowser.currentPage == 1) {
        pageTypeBrowser.find('div.pageNav a.prev').addClass('disabled');
    } else {
        pageTypeBrowser.find('div.pageNav a.prev').removeClass('disabled');
    }
    if (pageTypeBrowser.currentPage == pageTypeBrowser.totalPages) {
        pageTypeBrowser.find('div.pageNav a.next').addClass('disabled');
    } else {
        pageTypeBrowser.find('div.pageNav a.next').removeClass('disabled');
    }
    var startIndex = (pageTypeBrowser.currentPage - 1) * pageTypeBrowser.pageSize;
    var endIndex = startIndex + pageTypeBrowser.pageSize;
    if (filteredPageTypes) {
        if (endIndex > filteredPageTypes.length) {
            endIndex = filteredPageTypes.length;
        }
    } else {
        endIndex = 0;
    }
    pageTypeBrowser.find('ul#pageTypeContents').children().remove();

    var pageTypeItemTemplate = '<li><img class="icon" width="48" height="48" src=""/><span class="pageTypeName"></span><span class="pageTypeComment"></span></li>';

    for (var i = startIndex; i < endIndex; i++) {
        var pageTypeItem = $(pageTypeItemTemplate);

        pageTypeItem.data('itemData', {
            resourceType: 'pageType',
            type: filteredPageTypes[i].typeID,
            label: filteredPageTypes[i].label,
            comment: filteredPageTypes[i].comment
        });
        pageTypeItem.attr('type',filteredPageTypes[i].typeID);
        pageTypeItem.find('img').attr('src', 'Gfx/PageTypes/' + filteredPageTypes[i].icon + '.png');
        pageTypeItem.find('span.pageTypeName').text(filteredPageTypes[i].label);
        pageTypeItem.find('span.pageTypeComment').text(filteredPageTypes[i].comment);
        pageTypeBrowser.find('ul#pageTypeContents').append(pageTypeItem);
    }

    $("ul#pageTypeContents li").disableSelection();

    $("ul#pageTypeContents li").draggable({
        helper: 'clone',
        stack: 'ul#pageTypeContents li',
        iframeFix: true,
        cursorAt: {left: -15, top: 0},
        cursor: 'pointer',
        start: function (event, ui) {
            prepareNewPageDroppables();
        },
        stop: function (event, ui) {
            $('ul.pageStruct span.dropArea').removeClass("hover");
        }

    });
}
