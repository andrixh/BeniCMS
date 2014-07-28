var pages ={};

var descriptionDialog = new Dialog({
    title: 'Edit Page Titles',
    actions: [
        {
            label:'Cancel',
            click:function(parent){
                parent.hide();
            }
        },{
            label:'Save',
            click:function(parent){
                parent.working(true);

                var url = '_pageUtil.php?action=setDescription';
                var postParams = parent.widget.find('form').serialize();
                var pageID = parent.widget.find('form input[name="pageID"]').val();
                $.post(url,postParams,function(data){
                    var mlStrings = JSON.parse(data);
                    $('ul.pageStruct li[pageID="'+pageID+'"] span.pageTitle').text(mlStrings[0]);
                    $('ul.pageStruct li[pageID="'+pageID+'"] span.menuTitle').text(mlStrings[1]);
                    parent.working(false);
                    parent.hide();
                });
            }
        }

    ]
});

$.get('_pageUtil.php?action=getDescriptionForm',function(data){
    descriptionDialog.content(data);
    descriptionDialog.widgets.body.find('.mlstring').mlStringBasic();
});

var linkTitles = {
    'icon':['Normal Page. Double-Click to make this the home page.','Home page'],
    'home':['Normal Page. Double-Click to make this the home page.','Home page'],
    'track':['Not Tracking Visitors','Tracking Visitors'],
    'cache':['Not Caching this page','Caching this page'],
    'menuGroup1':['Not visible in Menu Group 1','Visible in Menu Group 1'],
    'menuGroup2':['Not visible in Menu Group 2','Visible in Menu Group 2'],
    'menuGroup3':['Not visible in Menu Group 3','Visible Menu Group 3'],
    'active':['Page is Inactive','Page is Active'],
    'rep':['No Representative','Has Representative'],
    'link':['No redirect link','Has redirect link']
}

var repCurves =[];
var linkCurves =[];

var linkCanvas;


$(document).ready(function(){
    updatePages();
    _d('pages',pages);

    $('ul.pageStruct').wrap('<div class="holder"></div>');
    $('div.holder').svg();
    linkCanvas = $('div.holder').svg('get');
    $('svg').attr('width','100%').attr('height','100%');
    //arrowStart = svg.marker(linkCanvas, 'arrowStartr', 0, 0, 20, 20);

    prepareCurves();
    drawTree();
    drawConnections();

    drawTree();



    $('ul.pageStruct').on('click', function(evt) {
        if ($(evt.target).is('ul')){
            $('span.page.selected').removeClass('selected');
        }
    });

    setupDraggables();

    $('ul.pageStruct').on('mouseover','.rep',function(e){
        $('.rep').draggable({
            revert: true,
            helper:'clone',
            revertDuration: 200,
            zIndex:20000,
            start: function(event, ui) {
                $('span.page').not($(this).parents()).addClass('expectDrop').droppable({
                    over: function(event, ui) {
                        $(this).addClass('hover');
                    },
                    out: function(event, ui) {
                        $(this).removeClass('hover');
                    },
                    drop: function(event, ui) {
                        _d(ui);
                        repDragEnd();
                        var srcPage = ui.draggable.parent().parent().parent().attr('pageid');

                        var dstPage =$(this).parent().attr('pageid');
                        _d(srcPage);_d(dstPage);

                        reRep(srcPage,dstPage);
                    }
                })
            },
            drag: function(event, ui){
                prepareTempRepLine(ui);
                drawConnections();
            },
            stop: function(event,ui){
                repDragEnd();
            }
        });
    });

    $('ul.pageStruct').on('dblclick','.rep',function(e){
        if (confirm('Do you want to remove the representative of this page?')){
            reRep($(this).parent().parent().parent().attr('pageid'),'');
        }
        e.preventDefault();
    });

    $('ul.pageStruct').on('click','.rep, .link, .home',function(e){
        e.preventDefault();
    });

    $('ul.pageStruct').on('mouseover','.link',function(e){
        $('.link').draggable({
            revert: true,
            helper:'clone',
            revertDuration: 200,
            zIndex:20000,
            start: function(event, ui) {

                $('span.page').not($(this).parents()).addClass('expectDrop').droppable({
                    over: function(event, ui) {
                        $(this).addClass('hover');
                    },
                    out: function(event, ui) {
                        $(this).removeClass('hover');
                    },
                    drop: function(event, ui) {
                        _d(ui);
                        linkDragEnd();
                        var srcPage = ui.draggable.parent().parent().parent().attr('pageid');

                        var dstPage =$(this).parent().attr('pageid');
                        _d(srcPage);_d(dstPage);

                        reLink(srcPage,dstPage);
                    }
                })
            },
            drag: function(event, ui){

                prepareTempLinkLine(ui);
                drawConnections();
            },
            stop: function(event,ui){
                linkDragEnd();
            }
        });
    });

    $('ul.pageStruct').on('dblclick','.link',function(e){
        if (confirm('Do you want to remove the link of this page?')){
            reLink($(this).parent().parent().parent().attr('pageid'),'');
        }
        e.preventDefault();
    });


    $('ul.pageStruct').on('click','.menuGroup1,.menuGroup2,.menuGroup3',function(e){
        var is_on = $(this).hasClass('on');
        var src = $(this).parent().parent().parent().attr('pageid');

        if ($(this).hasClass('menuGroup1')) {
            pages[src].structData.menuGroup1 = is_on?0:1;
        } else if ($(this).hasClass('menuGroup2')) {
            pages[src].structData.menuGroup2 = is_on?0:1;
        } else if ($(this).hasClass('menuGroup3')) {
            pages[src].structData.menuGroup3 = is_on?0:1;
        }

        var mg1 = pages[src].structData.menuGroup1 * 1;
        var mg2 = pages[src].structData.menuGroup2 * 2;
        var mg3 = pages[src].structData.menuGroup3 * 4;

        var mgs = mg1 | mg2 | mg3;

        $.get('_pageUtil.php?action=menuGroups&src='+src+'&val='+mgs);
        updatePages();
        e.preventDefault();
    });

    $('ul.pageStruct').on('click','.active',function(e){
        var is_on = $(this).hasClass('on');
        var src = $(this).parent().parent().parent().attr('pageid');

        pages[src].structData.active = is_on?0:1;

        $.get('_pageUtil.php?action=active&src='+src+'&val='+pages[src].structData.active);
        updatePages();
        e.preventDefault();
    });

    $('ul.pageStruct').on('dblclick','.home',function(e){
        var is_on = $(this).hasClass('on');
        var src = $(this).parent().parent().parent().attr('pageid');

        for(var i in pages){
            pages[i].structData.main = 0;
        }

        pages[src].structData.main = is_on?0:1;

        $.get('_pageUtil.php?action=main&src='+src+'&val='+pages[src].structData.main);
        updatePages();
        e.preventDefault();
    });



    $('ul.pageStruct').on('click','.track',function(e){
        var is_on = $(this).hasClass('on');
        var src = $(this).parent().parent().parent().attr('pageid');

        pages[src].structData.track = is_on?0:1;

        $.get('_pageUtil.php?action=track&src='+src+'&val='+pages[src].structData.active);
        updatePages();
        e.preventDefault();
    });

    $('ul.pageStruct').on('click','.cache',function(e){
        var is_on = $(this).hasClass('on');
        var src = $(this).parent().parent().parent().attr('pageid');

        pages[src].structData.cache = is_on?0:1;

        $.get('_pageUtil.php?action=cache&src='+src+'&val='+pages[src].structData.active);
        updatePages();
        e.preventDefault();
    });

    $('ul.pageStruct').on('click','span.pageID',function(e){
        e.stopPropagation();
        $(this).hide();
        var input = $('<input class="pageID" type="text"/>');
        input.val($(this).parents('li').eq(0).attr('pageid'));

        $(this).after(input);
        input.focus();
    });

    $('ul.pageStruct').on('click','li span.labels', function(){
        if ($(this).parent().hasClass('selected')){
            $(this).parent().removeClass('selected');
        } else {
            $('span.page.selected').removeClass('selected');
            $(this).parent().addClass('selected');
        }
    });

    $('ul.pageStruct').on('click','span.pageTitle, span.menuTitle',function(e){
        e.stopPropagation();

        descriptionDialog.show();
        descriptionDialog.working(true);

        var pageID = $(this).parents('li').attr("pageid");
        $.get('_pageUtil.php?action=getDescription&pageID='+pageID, function(data){
            var mlStrings = JSON.parse(data);
            _d('mlstrings',mlStrings);
            descriptionDialog.widgets.body.find('input[name="pageID"]').val(pageID);
            descriptionDialog.widgets.body.find('.mlstring').eq(0)[0].mlString.setValues(mlStrings[0]);
            descriptionDialog.widgets.body.find('.mlstring').eq(1)[0].mlString.setValues(mlStrings[1]);
            descriptionDialog.working(false);
        });

    });

    $('ul.pageStruct').on('blur keyup','input.pageID', function(e){
        _d('event',e);
        if ((e.type == 'keyup' && e.keyCode == 27) || e.type == 'focusout') {
            $(this).prev().show();
            $(this).remove();
            return;
        } else if ((e.type == 'keyup' && e.keyCode == 13)) {
            var ID= JSON.parse($(this).parents('li').eq(0).attr('data'))['ID'];
            prevID = $(this).parents('li').eq(0).attr('pageid');
            var newID = $(this).val();
            if (newID != prevID) {
                var $this = $(this);
                $.get('_pageUtil.php?', {"action": "setID", "prevID": prevID, "newID": newID}, function (data) {
                    if (data != '') {
                        $this.prev().text(data);
                        $this.parents('li').eq(0).attr('pageid',data);
                        $this.prev().show();
                        $this.remove();
                        return;
                    }
                });
            } else {
                $(this).prev().show();
                $(this).remove();
                return;
            }
        }
    });

});

function prepareNewPageDroppables(){
    _d('prepare new page droppable');
    $('ul.pageStruct span.dropArea').droppable({
        tolerance: 'pointer',
        greedy: 'true',
        over: function (event, ui) {
            _d('droppable over');
            $(this).addClass('hover');
        },
        out: function (event, ui) {
            _d('droppable out');
            $(this).removeClass('hover');
        },
        drop: function (event, ui) {
            _d('droppable drop');
            _d('UI', ui);
            _d('$this', $(this));

            var droppable = $(this);
            var draggable = $(ui.draggable);

            _d('type',draggable.attr('type'));
            $.get('_pageUtil.php?action=create&type='+draggable.attr('type'),function(data){
                var newPageData = JSON.parse(data);
                var newPage = $('<li></li>');
                newPage.attr('pageid',newPageData['pageID']).attr('data',data);
                var hostLi = droppable.parents('li').eq(0);
                var newParent;
                if (droppable.hasClass('middle')){
                    if ($(hostLi).find('>ul').length == 0){
                        $(hostLi).append('<ul></ul>');
                    }
                    hostLi.find('>ul').append(newPage);
                    newParent = $(hostLi).attr('pageid');
                } else {
                    if (droppable.hasClass('top')) {
                        hostLi.before(newPage);
                    } else if (droppable.hasClass('bottom')) {
                        hostLi.after(newPage);
                    }
                    if (hostLi.parents('ul').eq(0).hasClass('pageStruct')){
                        newParent = ''
                    } else {
                        newParent =hostLi.parents('ul').eq(0).parent().attr('pageID');
                    }
                }
                updatePages();
                reParent(newPageData['pageID'],newParent);
                setupDraggables();
                setTimeout(reRank, 50);
                setTimeout(prepareCurves, 300);
                setTimeout(drawConnections, 300);
                setTimeout(drawTree, 300);
                $('ul.pageStruct span.dropArea').droppable("destroy");
            });
        }
    });
}

function setupDraggables(){
    $('ul.pageStruct li').draggable("destroy");
    $('ul.pageStruct li').draggable({
        handle: "img",
        revert: 'invalid',
        revertDuration: 250,
        start:function(event,ui){
            _d('draggable start');
            _d('UI',ui);
            //$('ul.pageStruct li')
            var $selected = $('ul.pageStruct li').not($(ui.helper).find('li')).find('span.dropArea');
            _d('selected',$selected);

            $('ul.pageStruct li span.dropArea').removeClass('hover');
            $selected.droppable({
                tolerance:'pointer',
                greedy: 'true',
                over: function(event, ui) {
                    _d('droppable over');
                    $(this).addClass('hover');
                },
                out: function(event, ui) {
                    _d('droppable out');
                    $(this).removeClass('hover');
                },
                drop: function(event, ui) {
                    _d('droppable drop');
                    _d('UI',ui);
                    _d('$this',$(this));

                    var droppable = $(this);
                    var draggable = $(ui.draggable);

                    if (droppable.hasClass('middle')) { //reparent
                        var parent = $(this).parents('li').eq(0);
                        var child = draggable.attr('pageid');
                        var newParent =  $(parent).attr('pageid');
                        reParent(child,newParent);
                    } else if (droppable.hasClass('top') || droppable.hasClass('bottom')) {
                        var prevParent = '';
                        if (!draggable.parents('ul').eq(0).hasClass('pageStruct')) {
                            prevParent = draggable.parents('ul').eq(0).parents('li').eq(0).attr('pageid');
                        }
                        _d('prevParent',prevParent);
                        var targetItem = droppable.parents('li').eq(0);
                        if ($(this).hasClass('top')) {
                            $(targetItem).before(draggable);
                        } else if ($(this).hasClass('bottom')) {
                            $(targetItem).after(draggable);
                        }

                        var newParent = '';
                        if (!draggable.parents('ul').eq(0).hasClass('pageStruct')) {
                            newParent = draggable.parents('ul').eq(0).parents('li').eq(0).attr('pageid');
                        }
                        _d('newParent',newParent);
                        if (prevParent != newParent) {
                            var child = draggable.attr('pageid');
                            reParent(child,newParent);
                        }
                    }
                    $('ul.pageStruct ul').each(function(){
                        if ($(this).find('li').length == 0){
                            $(this).remove();
                        }
                    });
                    $('ul.pageStruct li').css({top:0,left:0});

                    $('ul.pageStruct span.dropArea').droppable("destroy");
                    setTimeout(reRank, 50);
                    setTimeout(prepareCurves, 300);
                    setTimeout(drawConnections, 300);
                    setTimeout(drawTree, 300);
                    setTimeout(function(){$('ul.pageStruct span.dropArea').removeClass("hover");},100);
                }
            });
        },
        stop: function (event,ui) {
            _d('draggable stop');
            _d('dropareas',$('ul.pageStruct span.dropArea'));
            $('ul.pageStruct span.dropArea').removeClass('hover');
            $('ul.pageStruct span.dropArea').droppable('destroy');
        }


    });
}


function reRep(src,dst){
    pages[src].structData.rep = dst;
    $.get('_pageUtil.php?action=represent&src='+src+'&dst='+dst);
    updatePages();
    setTimeout(prepareCurves,50);
    setTimeout(drawConnections,100);
}

function reLink(src,dst){
    _d('relinking');
    pages[src].structData.link = dst;
    $.get('_pageUtil.php?action=link&src='+src+'&dst='+dst);
    updatePages();
    setTimeout(prepareCurves,50);
    setTimeout(drawConnections,100);
}


function reParent(child, newParent){
    _d('trying to reparent '+child+' to '+newParent);
    if (pages[child].structData.parent != newParent){
        pages[child].structData.parent = newParent;
        if (newParent != ''){
            if (pages[newParent].parent().find('>ul').length == 0){
                pages[newParent].parent().append('<ul><div class="replaceMe"></div></ul>');
            } else {
                pages[newParent].parent().find('>ul').append('<div class="replaceMe"></div>');
            }
        } else {
            $('ul.pageStruct').append('<div class="replaceMe"></div>');
        }
        $.get('_pageUtil.php?action=parent&child='+child+'&parent='+newParent);
        //_d('siblings:');


        $('div.replaceMe').replaceWith($(pages[child]).parent());
        //$('ul.pageStruct ul:empty').remove();
        reRank();
        setTimeout(prepareCurves,50);
        setTimeout(drawConnections,100);
        setTimeout(drawTree,300);
    }
}


function reRank(){
    var pageIDs =[];
    var ranks =[];
    $('ul.pageStruct, ul.pageStruct ul').each(function(){
        $(this).find('>li').each(function(index){
            var newRank = index * 10;
            var pageID = $(this).attr('pageid');
            _d(pageID);
            _d(pages[pageID].structData.rank);
            _d(newRank);
            pages[pageID].structData.rank = newRank;
            pageIDs.push(pageID);
            ranks.push(newRank);
        });
    });
    $.get('_pageUtil.php?action=rerank&ids='+pageIDs.join('|')+'&ranks='+ranks.join('|'));
    updatePages();
}



function updatePages(){
    _g('updatePages()');
    var pageTemplate= '	<span class="page">'
        + '     <span class="main">'
        + '			<img />'
        + '		</span>'
        + '		<span class="labels">'
        + '			<span class="pageID"></span>'
        + '			<span class="pageTitle"></span>'
        + '			<span class="menuTitle"></span>'
        + '		</span>'
        + '     <span class="behaviour">'
        + '			<a href="#" class="home" title="Home Page - Double Click to toggle"></a>'
        + '			<a href="#" class="cache" title="Cache This page - Double Click to toggle"></a>'
        + '			<a href="#" class="track" title="Track this page - Double Click to toggle"></a>'
        + '		</span>'
        + '		<span class="visibility">'
        + '			<a href="#" class="menuGroup1" title="Menu Group 1"></a>'
        + '			<a href="#" class="menuGroup2" title="Menu Group 2"></a>'
        + '			<a href="#" class="menuGroup3" title="Menu Group 3"></a>'
        + '		</span>'
        + '		<span class="connections">'
        + '			<a href="#" class="active"></a>'
        + '			<a href="#" class="rep"></a>'
        + '			<a href="#" class="link"></a>'
        + '		</span>'
        + '		<span class="actions"><a href="#" class="action_edit button">Edit Page</a><a href="#" class="action_delete button" confirm="The page and all its information will be deleted forever! Continue?">Delete Page</a></span>'
        + '		<span class="dropArea top"></span><span class="dropArea middle"></span><span class="dropArea bottom"></span>'
        + '	</span>';

    $('ul.pageStruct li').each(function(){
        _g('item');
        _d($(this));
        var data;
        var page;

        if ($(this).find('>span.page').length != 0){
            page = pages[$(this).attr('pageid')];
            data = page.structData;
        } else {
            data = $.parseJSON($(this).attr('data'));
            page = $(pageTemplate);
        }

        page.structData = data;
        page.find('.main img').attr('src','Gfx/PageTypes/'+data.icon+'.png').attr('title',data.menuTitle);
        page.find('.pageID').text(data.pageID);
        page.find('.pageTitle').text(data.title);
        page.find('.menuTitle').text(data.menuTitle);



        if (data.menuGroup1 == 1) {
            page.find('.menuGroup1').addClass('on');
        } else {
            page.find('.menuGroup1').removeClass('on');
        }
        page.find('.menuGroup2').attr('title',linkTitles['menuGroup1'][data.menuGroup1]);
        if (data.menuGroup2 == 1) {
            page.find('.menuGroup2').addClass('on');
        } else {
            page.find('.menuGroup2').removeClass('on');
        }
        page.find('.menuGroup2').attr('title',linkTitles['menuGroup2'][data.menuGroup2]);
        if (data.menuGroup3 == 1) {
            page.find('.menuGroup3').addClass('on');
        } else {
            page.find('.menuGroup3').removeClass('on');
        }
        page.find('.menuGroup3').attr('title',linkTitles['menuGroup3'][data.menuGroup3]);
        if (data.active == 1) {
            page.find('.active').addClass('on');
        } else {
            page.find('.active').removeClass('on');
        }
        page.find('.active').attr('title',linkTitles['active'][data.active]);
        if (data.track == 1) {
            page.find('.track').addClass('on');
        } else {
            page.find('.track').removeClass('on');
        }
        page.find('.track').attr('title',linkTitles['track'][data.track]);
        if (data.cache == 1) {
            page.find('.cache').addClass('on');
        } else {
            page.find('.cache').removeClass('on');
        }
        page.find('.cache').attr('title',linkTitles['cache'][data.cache]);
        if (data.rep!='') {
            page.find('.rep').addClass('on').attr('title',linkTitles['rep'][1]);
        } else {
            page.find('.rep').removeClass('on').attr('title',linkTitles['rep'][0]);
        }
        if (data.link!='') {
            page.find('.link').addClass('on').attr('title',linkTitles['link'][1]);
            if ($('ul.pageStruct li[pageid="'+data.link+'"]').length==0){//link is external
                page.find('.link').html('<span class="extLink">'+data.link+'</span>');
            }
        } else {
            page.find('.link').removeClass('on').attr('title',linkTitles['link'][0]);

        }
        if (data.main == 1) {
            page.find('.home').addClass('on').attr('title',linkTitles['home'][1]);
        } else {
            page.find('.home').removeClass('on').attr('title',linkTitles['home'][0]);
        }
        page.find('.rank').text(data.rank);

        page.find('.action_edit').attr('href','pagesEdit.php?id='+data.ID);
        page.find('.action_delete').attr('href','pagesDelete.php?id='+data.ID).on('click',function(e){
            if (!window.confirm($(this).attr('confirm'))) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });

        //page.disableSelection();
        pages[data.pageID]=page;
        if ($(this).find('>span.page').length == 0){
            $(this).prepend(page);
        }
        _u();
        _d('pages',pages);
    });
    _u();
}

function repDragEnd(){
    $('.expectDrop').draggable('destroy').removeClass('expectDrop').removeClass('hover');
    $('svg').find('.tempRepCurve').parent().remove();
    delete(repCurves['_tempRepLine']);
}

function linkDragEnd(){
    $('.expectDrop').draggable('destroy').removeClass('expectDrop').removeClass('hover');
    $('svg').find('.tempLinkCurve').parent().remove();
    delete(repCurves['_tempLinkLine']);
}


function prepareTempRepLine(ui){
    var posBase = $('ul.pageStruct').offset();
    var posEnd = $(ui.helper).offset();

    var x = posEnd.left - posBase.left + 10;
    var y = posEnd.top - posBase.top + 8;


    if (typeof(repCurves['_tempRepLine'])=='undefined') {
        repCurves['_tempRepLine']={
            'svg':linkCanvas.group(),
            'className':'tempRepCurve',
            'x1':x,
            'y1':y
        };
    }

    repCurves['_tempRepLine'].x2=x;
    repCurves['_tempRepLine'].y2=y;
}

function prepareTempLinkLine(ui){
    var posBase = $('ul.pageStruct').offset();
    var posEnd = $(ui.helper).offset();

    var x = posEnd.left - posBase.left + 10;
    var y = posEnd.top - posBase.top + 8;


    if (typeof(repCurves['_tempLinkLine'])=='undefined') {
        repCurves['_tempLinkLine']={
            'svg':linkCanvas.group(),
            'className':'tempLinkCurve',
            'x1':x,
            'y1':y
        };
    }

    repCurves['_tempLinkLine'].x2=x;
    repCurves['_tempLinkLine'].y2=y;
}



function prepareCurves (){
    $('.repCurve, .linkCurve').parent().remove();
    for(i in pages){
        if (pages[i].structData.rep!=''){
            var posBase = $('ul.pageStruct').offset();
            var posStart = pages[i].find('.rep').offset();
            var posEnd = pages[pages[i].structData.rep].find('.active').offset();
            var x1 = posStart.left - posBase.left + 20;
            var y1 = posStart.top - posBase.top + 8;
            var x2 = posEnd.left - posBase.left + 20;
            var y2 = posEnd.top - posBase.top + 8;
            repCurves[pages[i].structData.pageID]={
                'target':pages[i].structData.rep,
                'className':'repCurve',
                'svg':linkCanvas.group(),
                'x1':x1,
                'y1':y1,
                'x2':x2,
                'y2':y2
            }
        }

        if ((pages[i].structData.link!='') && ($('ul.pageStruct li[pageid="'+pages[i].structData.link+'"]').length!=0)){
            var posBase = $('ul.pageStruct').offset();
            var posStart = pages[i].find('.link').offset();
            var posEnd = pages[pages[i].structData.link].find('.active').offset();
            var x1 = posStart.left - posBase.left + 20;
            var y1 = posStart.top - posBase.top + 8;
            var x2 = posEnd.left - posBase.left + 20;
            var y2 = posEnd.top - posBase.top + 8;
            linkCurves[pages[i].structData.pageID]={
                'target':pages[i].structData.rep,
                'className':'linkCurve',
                'svg':linkCanvas.group(),
                'x1':x1,
                'y1':y1,
                'x2':x2,
                'y2':y2
            }
        }


    }


}

function drawConnections() {
    var svg=linkCanvas;
    for(i in repCurves){
        var g = repCurves[i].svg;
        var x1 = repCurves[i].x1;
        var y1 = repCurves[i].y1;
        var x2 = repCurves[i].x2;
        var y2 = repCurves[i].y2;
        $(g).children().remove();

        var xDiff1;
        var xDiff2;
        var yDiff = Math.abs(y1-y2)/2;
        if (x1 > x2){
            xDiff1 = yDiff - (x1-x2);
            xDiff2 = yDiff;
        } else if ((x1 < x2)){
            xDiff1 = yDiff - (x1-x2);
            xDiff2 = yDiff;
        } else {
            xDiff1 = xDiff2 = yDiff;
        }

        var path = svg.createPath();
        svg.path(g, path.move(x1, y1).curveC(x1+xDiff1, y1, x2+xDiff2, y2, x2, y2),{'class':repCurves[i].className});
        svg.circle(g, x1, y1, 3, {'class':repCurves[i].className+'_mark'});
        svg.polygon(g, [[x2,y2],[x2+6,y2-4],[x2+6,y2+4]], {'class':repCurves[i].className+'_mark'});

    }

    for(i in linkCurves){
        var g = linkCurves[i].svg;
        var x1 = linkCurves[i].x1;
        var y1 = linkCurves[i].y1;
        var x2 = linkCurves[i].x2;
        var y2 = linkCurves[i].y2;
        $(g).children().remove();

        var xDiff1;
        var xDiff2;
        var yDiff = Math.abs(y1-y2)/2;
        if (x1 > x2){
            xDiff1 = yDiff - (x1-x2);
            xDiff2 = yDiff;
        } else if ((x1 < x2)){
            xDiff1 = yDiff - (x1-x2);
            xDiff2 = yDiff;
        } else {
            xDiff1 = xDiff2 = yDiff;
        }

        var path = svg.createPath();
        svg.path(g, path.move(x1, y1).curveC(x1+xDiff1, y1, x2+xDiff2, y2, x2, y2),{'class':linkCurves[i].className});
        svg.circle(g, x1, y1, 3, {'class':linkCurves[i].className+'_mark'});
        svg.polygon(g, [[x2,y2],[x2+6,y2-4],[x2+6,y2+4]], {'class':linkCurves[i].className+'_mark'});

    }

}

function drawTree(){
    var svg=linkCanvas;
    var treeGroup;
    $('#treeGroup').remove();

    treeGroup = svg.group('treeGroup');


    var posBase = $('ul.pageStruct').offset();

    $('ul.pageStruct li').each(function() {
        if ($(this).find('>ul').length != 0){

            $(this).find('>ul').each(function(){
                if (!$(this).is(':empty')){
                    var uPos = $(this).offset();
                    var lPos = $(this).find('>li:last-child').offset();
                    _d($(this).find('>li:last-child'));
                    var x1 = uPos.left-posBase.left;
                    var x2 = uPos.left-posBase.left;
                    var y1 = uPos.top-posBase.top;
                    var y2 = lPos.top-posBase.top;
                    svg.line(treeGroup,x1,y1-10,x2,y2+20);
                    $(this).find('>li').each(function(){
                        var uPos = $(this).offset();
                        var x1 = uPos.left-posBase.left;
                        var x2 = uPos.left-posBase.left-15;
                        var y1 = uPos.top-posBase.top+20;
                        var y2 = y1;
                        svg.line(treeGroup,x1,y1,x2,y2);
                    });
                }else {
                    $(this).remove();
                }
            });
        }

        var uPos = $(this).find('span.page').offset();
        var x = uPos.left-posBase.left;
        var y = uPos.top-posBase.top;
        var w = $(this).find('span.page').width();
        var h = $(this).find('span.page').height();

        svg.rect(treeGroup, x+2, y+2, w-4, h-4, 3, 3, {'class':'pageRect'});
    });
}

function getState(){
    _g('state');
    var state =[];
    for (var i in pages){
        state[pages[i].structData.pageID] = pages[i].structData;
    }
    _d(state);
    _u();
    return (state);
}


