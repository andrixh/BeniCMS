(function($) { //ImageSettings
	$.fn.imageSettings = function() {
		var toolbarHtml = '<toolbar contenteditable="false">'
			+ '<input type="text" class="input_width" placeholder="width"/>'
			+ '<a href="#" class="btn_autoWidth" title="Auto Width"></a>'
			+ '<sep></sep>'
			+ '<input type="text" class="input_height" placeholder="width"/>'
			+ '<a href="#" class="btn_autoHeight" title="Auto Height"></a>'
			+ '<sep></sep>'
			+ '<a href="#" class="btn_scaleS" title="Scale Mode: Stretch"></a>'
			+ '<a href="#" class="btn_scaleC" title="Scale Mode: Crop"></a>'
			+ '<a href="#" class="btn_scaleF" title="Scale Mode: Fit"></a>'
			+ '<sep></sep>'
			+ '<a href="#" class="btn_alignL" title="Align: Left"></a>'
			+ '<a href="#" class="btn_alignC" title="Align: Center"></a>'
			+ '<a href="#" class="btn_alignR" title="Align: Right"></a>'
			+ '<sep></sep>'
			+ '<a href="#" class="btn_border" title="Show Border"></a>'
			+ '<a href="#" class="btn_caption" title="Show Caption"></a>'
			+ '<a href="#" class="btn_link" title="Link"></a>'
			//+ '<sep></sep>'
			//+ '<a href="#" class="btn_refresh" title="Refresh"></a>'
			+ '<toolbar>';
		var initData = {
			'physicalName':'',
			'type':'',
			'width':100,
			'height':100,
			'autoWidth':false,
			'autoHeight':false,
			'align':'C',
			'scaleMode':'C',
			'border':false,
			'caption':false,
			'link':''
		};
		var resizeHandleHtml = '<resizeHandle></resizeHandle>';

		$(this).each(function() {
			$(this).find('*').not('img').remove();
			var toolbar = null;
			var handleW = null;
			var handleH = null;
			var handleWH = null;
			var gridArea = null;
			var imgData = {	}
			var self = this;
			var img = $(self).find('img')[0];

			var refreshTimeout = null;

			$(self).attr('contenteditable','false');

			//add data if not existing, or just prepare toolbar if existing
			if (typeof $(self).attr('data-image') !== 'undefined' && $(self).attr('data-image') !== false) {
				imgData = $.parseJSON($(self).attr('data-image'));
				prepareToolbar();
				prepareGrid();
				prepareHandles();
				updateToolbar();
			} else {
				prepareInitialData();
				imgData = $.parseJSON($(self).attr('data-image'));
				prepareToolbar();
				prepareGrid();
				prepareHandles();
				updateToolbar();
			}

			function updateData(){
				$(self).attr('data-image',JSON.stringify(imgData));
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
							refreshTimeout = setTimeout(function(){refreshImage();},1000);
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
							refreshTimeout = setTimeout(function(){refreshImage();},1000);
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
							refreshTimeout = setTimeout(function(){refreshImage();},1000);
						}
					});
				}
				handleH.css('top',($(img).position().top + imgData.height-10)+'px').css('width',(imgData.width-10)+'px');
				handleW.css('left',($(img).position().left + imgData.width-10)+'px').css('height',(imgData.height-10)+'px').css('top',$(img).position().top+'px');
				handleWH.css('top',($(img).position().top + imgData.height-10)+'px').css('left',($(img).position().left + imgData.width-10)+'px');
			}


			function dragResize(){
				clearTimeout(refreshTimeout);
				var snap = true;
				if (arguments.length == 1 && arguments[0] == false){
					snap = false;
				}

				var prevWidth = imgData.width;
				var prevHeight = imgData.height;
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
							imgData.width = snappedWidth;
						} else {
							_d('no snap');
							imgData.width = posX;
						}
						imgData.height = posY;
					} else {
						imgData.width = posX;
						imgData.height = posY;
					}
					imgData.autoHeight=false;
					imgData.autoWidth=false;
					$(img).attr('width',imgData.width).attr('height',imgData.height);
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
				_d(imgData.width);
				gridArea.find('span').removeClass('active');
				gridArea.find('span[size='+imgData.width+']').prevAll().andSelf().addClass('active');
			}

			function prepareInitialData(){
				$(self).attr('data-image',JSON.stringify(initData));
				var data = $.parseJSON($(self).attr('data-image'));
				var src = $(img).attr('src');
				var srcparts = src.split('.');
				var ext = srcparts[srcparts.length-1];
				srcparts = src.split('/');
				var physicalName = srcparts[srcparts.length-1].split('_')[0];
				data.physicalName = physicalName;
				data.type = ext;
				$(self).attr('data-image',JSON.stringify(data));
			}

			function refreshImage(){
				var np = imgData.physicalName;
				var nw = imgData.width;
				var nh = imgData.height;
				var ns = imgData.scaleMode;
				if (imgData.autoWidth) { nw = 0;}
				if (imgData.autoHeight) { nh = 0;}
				var nt = imgData.type;
				var url = '/Images/Resized/'+np+'_'+nw+'_'+nh+'_'+ns+'_30_t.'+nt;
				$(img).attr('src',url);
				updateGrid();
			}

			$(img).load(function(){
				$(this).removeAttr('width').removeAttr('height');
				if (imgData.autoWidth || imgData.autoHeight){
					imgData.width = $(this).width();
					imgData.height = $(this).height();
				}
				updateToolbar();
				updateData();
				prepareHandles();
				updateGrid();
				//toolbar.find('.input_width').focus();
			});

			toolbar.find('a.btn_refresh').click(function() {
				refreshImage();
				return false;
			});

			toolbar.find('a.btn_autoWidth').click(function() {
				imgData.autoWidth = !imgData.autoWidth;
				if (imgData.autoWidth){ imgData.autoHeight = false; imgData.scaleMode = 'S';}
				_d('updateToolbar');
				updateToolbar();
				updateData();
				refreshImage();
				return false;
			});

			toolbar.find('a.btn_autoHeight').click(function() {
				_d('autoHeight');
				imgData.autoHeight = !imgData.autoHeight;
				if (imgData.autoHeight){ imgData.autoWidth = false; imgData.scaleMode = 'S';}
				updateToolbar();
				updateData();
				refreshImage();
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
				}
			}

			toolbar.find('a.btn_scaleS, a.btn_scaleC, a.btn_scaleF, a.btn_alignL, a.btn_alignC, a.btn_alignR, a.btn_border, a.btn_caption').click(function() {
				if ($(this).hasClass('btn_scaleS')){imgData.scaleMode = 'S';}
				if ($(this).hasClass('btn_scaleC')){imgData.scaleMode = 'C';}
				if ($(this).hasClass('btn_scaleF')){imgData.scaleMode = 'F';}
				if ($(this).hasClass('btn_alignL')){imgData.align = 'L';}
				if ($(this).hasClass('btn_alignC')){imgData.align = 'C';}
				if ($(this).hasClass('btn_alignR')){imgData.align = 'R';}
				if ($(this).hasClass('btn_border')){imgData.border = !imgData.border;}
				if ($(this).hasClass('btn_caption')){imgData.caption = !imgData.caption}
				updateData();
				updateToolbar();
				refreshImage();
				return false;
			});

			toolbar.find('a.btn_link').click(function() {
				window.parent.showImgLinkDialog(this,self);
				return false;
				//window.parent.postMessage({'action':'imgLink','source':this},'*');
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
						imgData.width = v;
					} else {
						imgData.height = v;
						$(img).attr('height',v);
					}
					$(img).attr('width',imgData.width).attr('height',imgData.height);

					imgData.autoWidth = false;
					imgData.Height = false;
					imgData.scaleMode = "S";
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
					imgData.width = v;
				} else {
					imgData.height = v;
					$(img).attr('height',v);
				}

				$(img).attr('width',imgData.width).attr('height',imgData.height);
				imgData.autoWidth = false;
				imgData.Height = false;
				imgData.scaleMode = "S";


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
				toolbar.find('.input_width').val(imgData.width);
				toolbar.find('.input_height').val(imgData.height);
				if (imgData.autoWidth) {toolbar.find('.btn_autoWidth').addClass('selected');} else {toolbar.find('.btn_autoWidth').removeClass('selected');}
				if (imgData.autoHeight) {toolbar.find('.btn_autoHeight').addClass('selected');} else {toolbar.find('.btn_autoHeight').removeClass('selected');}
				toolbar.find('.btn_scaleS, .btn_scaleC, .btn_scaleF').removeClass('selected');
				toolbar.find('.btn_scale'+imgData.scaleMode).addClass('selected');
				toolbar.find('.btn_alignL, .btn_alignR, .btn_alignC').removeClass('selected');
				toolbar.find('.btn_align'+imgData.align).addClass('selected');
				if (imgData.border) {toolbar.find('.btn_border').addClass('selected');} else {toolbar.find('.btn_border').removeClass('selected');}
				if (imgData.caption) {toolbar.find('.btn_caption').addClass('selected');} else {toolbar.find('.btn_caption').removeClass('selected');}
				if (imgData.link) {toolbar.find('.btn_link').addClass('selected');} else {toolbar.find('.btn_link').removeClass('selected');}
			}



		});


	}
})(jQuery);