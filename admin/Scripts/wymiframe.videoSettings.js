(function($) { //ImageSettings
	$.fn.videoSettings = function() {
		var toolbarHtml = '<toolbar contenteditable="false">'
			+ '<input thumbnailType="text" class="input_width" placeholder="width"/>'

			+ '<sep></sep>'
			+ '<input thumbnailType="text" class="input_height" placeholder="width"/>'
			+ '<a href="#" class="btn_43" title="4:3 Aspect Ratio"></a>'
			+ '<a href="#" class="btn_169" title="16:9 Aspect Ratio"></a>'
			+ '<sep></sep>'
			+ '<a href="#" class="btn_autoPlay" title="Auto Play"></a>'
			+ '<a href="#" class="btn_loop" title="Loop"></a>'
			+ '<a href="#" class="btn_controls" title="Show Player Controls"></a>'
			+ '<a href="#" class="btn_colors" title="Player Color"><span class="swatch"></span><span class="palette"></span></a>'
			+ '<sep></sep>'
			+ '<a href="#" class="btn_alignL" title="Align: Left"></a>'
			+ '<a href="#" class="btn_alignC" title="Align: Center"></a>'
			+ '<a href="#" class="btn_alignR" title="Align: Right"></a>'
			+ '<sep></sep>'
			+ '<a href="#" class="btn_border" title="Show Border"></a>'
			+ '<a href="#" class="btn_caption" title="Show Caption"></a>'
			+ '<toolbar>';
		var initData = {
			'physicalName':'',
			'videoID':'',
			'service':'',
			'thumbnail':'',
			'thumbnailType':'',
			'width':100,
			'height':100,
			'autoPlay':false,
			'loop':false,
			'controls':false,
			'color':'',
			'align':'C',
			'border':false,
			'caption':false,
		};
		var resizeHandleHtml = '<resizeHandle></resizeHandle>';

		$(this).each(function() {
			$(this).find('*').not('img').remove();
			var toolbar = null;
			var handleW = null;
			var handleH = null;
			var handleWH = null;
			var gridArea = null;
			var vidData = {	}
			var self = this;
			var img = $(self).find('img')[0];

			var refreshTimeout = null;

			$(self).attr('contenteditable','false');

			//add data if not existing, or just prepare toolbar if existing
			if (typeof $(self).attr('data-video') !== 'undefined' && $(self).attr('data-video') !== false) {
				vidData = $.parseJSON($(self).attr('data-video'));
				prepareToolbar();
				prepareGrid();
				prepareHandles();
				updateToolbar();
			} else {
				prepareInitialData();
				vidData = $.parseJSON($(self).attr('data-video'));
				prepareToolbar();
				prepareGrid();
				prepareHandles();
				updateToolbar();
			}

			function updateData(){
				$(self).attr('data-video',JSON.stringify(vidData));
			}

			function prepareHandles(){
				_d('preparing handles');
				var init = (handleW==null);
				if (init) { //create handles if not existing;
					_d('creating new handles');
					handleW = $(resizeHandleHtml); handleW.addClass('w');
					handleH = $(resizeHandleHtml); handleH.addClass('h');
					handleWH = $(resizeHandleHtml); handleWH.addClass('wh');
					$(self).append(handleH).append(handleW).append(handleWH);

					handleW.draggable({
						axis:'x',
						containment:  [35,35,620,1200],
						drag:function(event, ui){
							if (event.altKey){
								dragResize(false);
							} else {
								dragResize(true);
							}

						},
						start:function(event, ui){
							clearTimeout(refreshTimeout);
							$(self).find('resizeHandle').not(this).addClass('hide');
						},
						stop:function(event, ui){
							$(self).find('resizeHandle').removeClass('hide');
							updateData();
							prepareHandles();
							refreshTimeout = setTimeout(function(){refreshVideo();},1000);
						}
					});
					handleH.draggable({
						axis:'y',
						containment: [35,35,620,1200],
						drag:function(event, ui){
							dragResize(false);
						},
						start:function(event, ui){
							clearTimeout(refreshTimeout);
							$(self).find('resizeHandle').not(this).addClass('hide');
						},
						stop:function(event, ui){
							$(self).find('resizeHandle').removeClass('hide');
							updateData();
							prepareHandles();
							refreshTimeout = setTimeout(function(){refreshVideo();},1000);
						}
					});
					handleWH.draggable({
						containment: [35,35,620,1200],
						drag:function(event, ui){
							handleH.css('top',$(this).position().top);
							handleW.css('left',$(this).position().left);
							if (event.altKey){
								dragResize(false);
							} else {
								dragResize(true);
							}
						},
						start:function(event, ui){
							clearTimeout(refreshTimeout);
							$(self).find('resizeHandle').not(this).addClass('hide');
						},
						stop:function(event, ui){
							$(self).find('resizeHandle').removeClass('hide');
							updateData();
							prepareHandles();
							refreshTimeout = setTimeout(function(){refreshVideo();},1000);
						}
					});
				}
				handleH.css('top',($(img).position().top + vidData.height-10)+'px').css('width',(vidData.width-10)+'px');
				handleW.css('left',($(img).position().left + vidData.width-10)+'px').css('height',(vidData.height-10)+'px').css('top',$(img).position().top+'px');
				handleWH.css('top',($(img).position().top + vidData.height-10)+'px').css('left',($(img).position().left + vidData.width-10)+'px');
			}


			function dragResize(){
				clearTimeout(refreshTimeout);
				var snap = true;
				if (arguments.length == 1 && arguments[0] == false){
					snap = false;
				}

				var prevWidth = vidData.width;
				var prevHeight = vidData.height;
				var tolerance = 50;

				var posX = $(handleW).position().left-$(img).position().left+10;
				if (posX<20){posX=20};
				var posY = $(handleH).position().top-$(img).position().top+10;
				if (posY<20){posY=20};

				var deltaX = Math.abs(posX-prevWidth);
				var deltaY = Math.abs(posY-prevHeight);
				if (deltaX < tolerance && deltaY < tolerance){

					if (snap){
						_d('snap is on');
						var snapTolerance = 10;
						var snapModule = CONFIG.GRID_COL_WIDTH+CONFIG.GRID_COL_MARGIN;
						if (snapModule-((posX+CONFIG.GRID_COL_MARGIN) % snapModule) < snapTolerance){
							_d('SNAP!');
							var snappedWidth = Math.round((posX+CONFIG.GRID_COL_MARGIN) / snapModule)*snapModule - CONFIG.GRID_COL_MARGIN;
							_d(snappedWidth);
							vidData.width = snappedWidth;
						} else {
							_d('no snap');
							vidData.width = posX;
						}
						vidData.height = posY;
					} else {
						vidData.width = posX;
						vidData.height = posY;
					}
					$(img).attr('width',vidData.width).attr('height',vidData.height);
					updateGrid();
					updateToolbar();

				}
			}

			function prepareGrid(){
				gridArea = $('<span class="gridArea"></span>');
				$(self).prepend(gridArea);
				for (var i = 0; i<=12; i++){
					gridArea.append('<span></span>');
					var css1 = 'width:'+CONFIG.GRID_COL_WIDTH+'px';
					var css2 = 'left:'+(i*(CONFIG.GRID_COL_WIDTH+CONFIG.GRID_COL_MARGIN))+'px';
					gridArea.find('span:last-child').attr('style',css1+';'+css2).attr('size',CONFIG.GRID_COL_WIDTH+(i*(CONFIG.GRID_COL_WIDTH+CONFIG.GRID_COL_MARGIN)));
				}
				updateGrid();
			}

			function updateGrid(){
				gridArea.find('span').removeClass('active');
				gridArea.find('span[size='+vidData.width+']').prevAll().andSelf().addClass('active');
			}

			function prepareInitialData(){
				_d('prepareInitialData');
				var itemData = $(self).data('itemData');
				_d(itemData);
				$(self).attr('data-video',JSON.stringify(initData));
				var data = $.parseJSON($(self).attr('data-video'));
				var src = $(img).attr('src');
				var srcparts = src.split('.');
				var ext = srcparts[srcparts.length-1];
				srcparts = src.split('/');
				var thumbnail = srcparts[srcparts.length-1].split('_')[0];
				data.thumbnail = thumbnail;
				data.thumbnailType = ext;
				data.physicalName = itemData.physicalName;
				data.service = itemData.service;
				data.videoID = itemData.videoID;
				$(self).attr('data-video',JSON.stringify(data));
			}

			function prepareColors(){
				var colors = [
					'FFFFFF',
					'808080',
					'404040',
					'000000',
					'BE1E2D',
					'F7941E',
					'F9ED32',
					'8DC63F',
					'27AAE1',
					'1C75BC',
					'662D91'
				];
				for (var i in colors){
					var span = $('<span class="swatch" color="'+colors[i]+'"></span>');
					span.css('background-color','#'+colors[i]);
					toolbar.find('a.btn_colors span.palette').append(span);

				}
				toolbar.find('a.btn_colors span.palette').append('<span class="swatch empty"></span>');
				toolbar.find('a.btn_colors span.palette').hide();
			}



			function refreshVideo(){
				var np = vidData.thumbnail;
				var nw = vidData.width;
				var nh = vidData.height;
				var ns = 'F';
				if (vidData.autoWidth) { nw = 0;}
				if (vidData.autoHeight) { nh = 0;}
				var nt = vidData.thumbnailType;
				var url = '/Images/Resized/'+np+'_'+nw+'_'+nh+'_'+ns+'_30_t.'+nt;
				$(img).attr('src',url)
			}

			$(img).load(function(){
				$(this).removeAttr('width').removeAttr('height');
				//vidData.width = $(this).width();
				//vidData.height = $(this).height();
				updateToolbar();
				updateData();
				prepareHandles();
			});

			toolbar.on('click','a.btn_colors',function(e){
				toolbar.find('span.palette').show();
				e.preventDefault();
			});

			toolbar.on('click','a.btn_colors span.palette span',function(e){
				e.stopPropagation();
				vidData.color=$(this).attr('color');
				_d(typeof(vidData.color));
				if (typeof (vidData.color) == 'undefined') {
					vidData.color = '';
				}
				updateData();
				updateToolbar();
				toolbar.find('span.palette').hide();
			});

			toolbar.on('mouseleave','a.btn_colors span.palette',function(e){
				e.stopPropagation();
				toolbar.find('span.palette').hide();
			});

			toolbar.find('a.btn_refresh').click(function() {
				refreshVideo();
				return false;
			});

			toolbar.find('a.btn_43').click(function() {
				vidData.height = Math.round(vidData.width/4*3);
				updateToolbar();
				updateData();
				refreshVideo();
				return false;
			});

			toolbar.find('a.btn_169').click(function() {
				vidData.height = Math.round(vidData.width/16*9);
				updateToolbar();
				updateData();
				refreshVideo();
				return false;
			});


			$(self).mousedown(function(evt) {
				if(evt.target == self){
					_d('click on self');
					evt.preventDefault();
					return false;
				}
			});

			function prepareToolbar(){
				if (!toolbar){

					toolbar = $(toolbarHtml);
					$(self).append(toolbar);
					prepareColors();
				}
			}

			toolbar.find('a.btn_autoPlay, a.btn_loop, a.btn_controls, a.btn_alignL, a.btn_alignC, a.btn_alignR, a.btn_border, a.btn_caption').click(function() {
				if ($(this).hasClass('btn_autoPlay')){vidData.autoPlay = !vidData.autoPlay;}
				if ($(this).hasClass('btn_loop')){vidData.loop = !vidData.loop;}
				if ($(this).hasClass('btn_controls')){vidData.controls = !vidData.controls;}
				if ($(this).hasClass('btn_alignL')){vidData.align = 'L';}
				if ($(this).hasClass('btn_alignC')){vidData.align = 'C';}
				if ($(this).hasClass('btn_alignR')){vidData.align = 'R';}
				if ($(this).hasClass('btn_border')){vidData.border = !vidData.border;}
				if ($(this).hasClass('btn_caption')){vidData.caption = !vidData.caption;}
				updateData();
				updateToolbar();
				refreshVideo();
				return false;
			});


			toolbar.find('input').keydown(function(e) {
				if (e.keyCode == 38 || e.keyCode == 40){
					var v = parseInt($(this).val());
					if (isNaN(v)){v=0;}
					var inc = 1;
					if (e.shiftKey){inc = 10;}
					if (e.keyCode == 38){
						v+=inc;
					} else if (e.keyCode==40) {
						v-=inc;
					} else {
						var v = parseInt($(this).val());
						if (isNaN(v)){v=0;}
					}
					if (v<20){v=20;}
					if (v>1200) {v=1200;}
					$(this).val(v);

					if ($(this).hasClass('input_width')){
						vidData.width = v;
					} else {
						vidData.height = v;
						$(img).attr('height',v);
					}
					$(img).attr('width',vidData.width).attr('height',vidData.height);
				}
				updateData();
				updateGrid();
				prepareHandles();
				updateToolbar();
			});

			toolbar.find('input').change(function(e){
				var v = parseInt($(this).val());
				if (isNaN(v)){v=0;}
				if (v<20){v=20;}
				if (v>1200) {v=1200;}
				$(this).val(v);
				if ($(this).hasClass('input_width')){
					vidData.width = v;
				} else {
					vidData.height = v;
					$(img).attr('height',v);
				}

				$(img).attr('width',vidData.width).attr('height',vidData.height);
				updateData();
				updateGrid();
				prepareHandles();
				updateToolbar();
			});

			toolbar.find('input').keyup(function(e) {
				var kc = e.keyCode;
				if (kc <48 || kc >57) {
					e.preventDefault();
					return false;
				}
			});



			function updateToolbar(){
				_d('updateToolbar');
				_d(vidData);
				toolbar.find('.input_width').val(vidData.width);
				toolbar.find('.input_height').val(vidData.height);
				if (vidData.autoPlay) {toolbar.find('.btn_autoPlay').addClass('selected');} else {toolbar.find('.btn_autoPlay').removeClass('selected');}
				if (vidData.loop) {toolbar.find('.btn_loop').addClass('selected');} else {toolbar.find('.btn_loop').removeClass('selected');}
				if (vidData.controls) {toolbar.find('.btn_controls').addClass('selected');} else {toolbar.find('.btn_controls').removeClass('selected');}
				if (vidData.color == '') {toolbar.find('.btn_colors>span.swatch').addClass('empty').removeAttr('style');} else {toolbar.find('.btn_colors>span.swatch').css('background-color','#'+vidData.color).removeClass('empty'); }

				toolbar.find('.btn_alignL, .btn_alignR, .btn_alignC').removeClass('selected');
				toolbar.find('.btn_align'+vidData.align).addClass('selected');
				if (vidData.border) {toolbar.find('.btn_border').addClass('selected');} else {toolbar.find('.btn_border').removeClass('selected');}
				if (vidData.caption) {toolbar.find('.btn_caption').addClass('selected');} else {toolbar.find('.btn_caption').removeClass('selected');}
				if (vidData.link) {toolbar.find('.btn_link').addClass('selected');} else {toolbar.find('.btn_link').removeClass('selected');}
			}



		});


	}
})(jQuery);