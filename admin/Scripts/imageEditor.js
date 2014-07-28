var imgSource;
var imgW;
var imgH;

var zoom = 1;
var panX = 0;
var panY = 0;

var canvasW;
var canvasH;
var centerX = 0;
var centerY = 0;
var startX=0;
var startY=0;

var flipH=false;
var flipV=false;

var angle = 0;
var dragStartAngle = 0;

var cropX, cropY, cropW, cropH;
var dragStartcX; var dragStartcY; var dragStartcW; var dragStartH;

var currentAction = '';
function deg2rad(deg){
	return Math.PI * (deg/180);
}

function rad2deg(rad){
	return rad * (180/Math.PI);
}

$(document).ready(function() {
	imgSource = $('input#srcDir').val()+$('input#physicalName').val()+'.'+$('input#type').val();
	
	var workPlaceTemplate = '<div class="workPlace">';
	workPlaceTemplate += '<div class="scaler">'
	workPlaceTemplate += '<div class="canvas">';
	workPlaceTemplate += '<div class="image"></div>';
	workPlaceTemplate += '</div>';
	workPlaceTemplate += '<div class="cropCover L"></div><div class="cropCover R"></div><div class="cropCover T"></div><div class="cropCover B"></div>';
	workPlaceTemplate += '<div class="cropHandlePoster TL"></div><div class="cropHandlePoster TR"></div><div class="cropHandlePoster BL"></div><div class="cropHandlePoster BR"></div>';
	workPlaceTemplate += '<div class="cropHandle TL"></div><div class="cropHandle TR"></div><div class="cropHandle BL"></div><div class="cropHandle BR"></div><div class="cropArea"></div>';
	workPlaceTemplate += '</div>';
	workPlaceTemplate += '<div class="rotateHandle"></div>';
	workPlaceTemplate += '<div class="panHandle"></div>';
	workPlaceTemplate += '</div>';
	
	var workPlace = $(workPlaceTemplate);
	$('h1').after(workPlace);	
	resizeWorkPlace();
	
	$(window).resize(function(){	
		resizeWorkPlace();
	});
	
	var controlTemplate = '<div class="controls">';
	controlTemplate+= '<a href="#" title="Rotate" id="rotate"></a>';
	controlTemplate+= '<div>';
	controlTemplate+= '<a href="#" title="Rotate 0&deg;" id="r0"></a><a href="#" title="Rotate 90&deg;" id="r90"></a><a href="#" title="Rotate 180&deg;" id="r180"></a><a href="#" title="Rotate 270&deg;" id="r270"></a>';
	controlTemplate+= '<label><input id="r" type="number" value="0"/></label>';
	controlTemplate+= '</div>';
	controlTemplate+= '<a href="#" title="Crop" id="crop"></a>';
	controlTemplate+= '<div>';
	controlTemplate+= '<label><span>X</span><input id="cX" type="number" value="0"/></label>';
	controlTemplate+= '<label><span>Y</span><input id="cY" type="number" value="0"/></label>';
	controlTemplate+= '<label><span>W</span><input id="cW" type="number" value="0"/></label>';
	controlTemplate+= '<label><span>H</span><input id="cH" type="number" value="0"/></label>';
	controlTemplate+= '</div>';	
	controlTemplate+= '<a href="#" title="Flip Vertical" id="flipV"></a>';
	controlTemplate+= '<a href="#" title="Flip Horizontal" id="flipH"></a>';
	controlTemplate+= '<div><labe></label></div>';		
	controlTemplate+= '<a href="#" title="Zoom in" id="zoomIn"></a>';
	controlTemplate+= '<a href="#" title="Zoom out" id="zoomOut"></a>';	
	controlTemplate+= '<a href="#" title="Pan" id="pan"></a>';
	controlTemplate+= '</div>';
	
	 
	var controls = $(controlTemplate);
	$('h1').append(controls);
	var refreshID = parseInt(Math.random()*6400);
	_d(refreshID);
	_d('<img src="'+imgSource+'?ref='+refreshID+'"/>');
	_d('url(\''+imgSource+'?ref='+refreshID+'\')');
	var img=$('<img src="'+imgSource+'?ref='+refreshID+'"/>');
	img.load(function(){
		_d('image loaded');
		imgW = this.width; canvasW=imgW;
		imgH = this.height; canvasH=imgH;
		cropX = 0; cropY = 0;
		cropW = imgW; cropH = imgH;
		parseInt($('input#cX').val(cropX));
		parseInt($('input#cY').val(cropY));
		parseInt($('input#cW').val(cropW));
		parseInt($('input#cH').val(cropH));
		
		$('div.image').css('width',imgW+'px').css('height',imgH+'px').css('background-image','url(\''+imgSource+'?ref='+refreshID+'\')');
		performCrop();
	});
	
	//////////ROTATE/////////
	
	$('a#rotate').click(function() {
		$(this).siblings().removeClass('selected');
		$(this).addClass('selected');
		setCurrentAction('rotate');
	});
	
	$('input#r').keydown(function(e){
		var a = parseFloat($(this).val());
		if (isNaN(a)){a=0;}
		var inc = 0.1;
		if (e.shiftKey){inc = 1;}
		if (e.keyCode == 38){
			a+=inc;
		} else if (e.keyCode==40) {
			a-=inc;
		}
		angle = a;
		$(this).val(a);
		performRotation(true);
	});
	
	$('input#r').change(function(){
		var a = parseFloat($(this).val());
		angle = a;
		performRotation(true);
	});
	
	$('input#r').click(function(){
		var a = parseFloat($(this).val());
		angle = a;
		performRotation(true);
	});
	
	$('a#r0').click(function(){
		$('input#r').val('0');
		angle = 0;
		performRotation(true);
	});
	
	$('a#r90').click(function(){
		$('input#r').val('0');
		angle = 90;
		performRotation(true);
	});
	
	$('a#r180').click(function(){
		$('input#r').val('0');
		angle = 180;
		performRotation(true);
	});
	
	$('a#r270').click(function(){
		$('input#r').val('0');
		angle = 270;
		performRotation(true);
	});
	
	
	$('div.rotateHandle').draggable({
		revert:true,
		revertDuration:0,
  		start: function(event, ui) {
  			_d(event);
  			_d(ui);
			dragStartAngle = angle;
		},
  		drag: function(event, ui) {
			var diff = ui.position.top - ui.originalPosition.top;
			var multiplier = 0.1;
			if (event.shiftKey){
				multiplier = 1;
			}
			var a = dragStartAngle + diff*multiplier;
			if (a<0){
				a = 360+a;
			}
			if (a>360){
				a = a-360;
			}
			angle = a;
			performRotation(true);
		},
	});
	
	////CROP////////
	
	$('a#crop').click(function() {
		$(this).siblings().removeClass('selected');
		$(this).addClass('selected');
		setCurrentAction('crop');
	});
	
	$('input#cX,input#cY,input#cW,input#cH').keydown(function(e){
		var v = parseInt($(this).val());
		if (isNaN(v)){v=0;}
		var inc = 1;
		if (e.shiftKey){inc = 10;}
		if (e.keyCode == 38){
			v+=inc;
		} else if (e.keyCode==40) {
			v-=inc;
		}
		$(this).val(v);
		
		
		var x = parseInt($('input#cX').val());
		var y = parseInt($('input#cY').val());
		var w = parseInt($('input#cW').val());
		var h = parseInt($('input#cH').val());
		
		cropX = x; cropY= y; cropW = w; cropH = h;
		performCrop();
	});
	
	$('div.cropHandle.TL').draggable({
  		start: function(event, ui) {
  			_d(event);
  			_d(ui);
			dragStartcX = cropX;
			dragStartcY = cropY;
			dragStartcW = cropW;
			dragStartcH = cropH;
		},
  		drag: function(event, ui) {
			var Xdiff = (ui.position.left - ui.originalPosition.left) / zoom;
			var Ydiff = (ui.position.top - ui.originalPosition.top) / zoom;
			cropX = dragStartcX + Xdiff;
			cropY = dragStartcY + Ydiff;
			cropW = dragStartcW - Xdiff; 
			cropH = dragStartcH - Ydiff;
			performCrop();
		},
		stop: function(event, ui){
			performCrop();
		}
	});
	
	$('div.cropHandle.TR').draggable({
  		start: function(event, ui) {
  			_d(event);
  			_d(ui);
			dragStartcX = cropX;
			dragStartcY = cropY;
			dragStartcW = cropW;
			dragStartcH = cropH;
		},
  		drag: function(event, ui) {
			var Xdiff = (ui.position.left - ui.originalPosition.left) / zoom;
			var Ydiff = (ui.position.top - ui.originalPosition.top) /zoom;
			//cropX = dragStartcX + Xdiff;
			cropY = dragStartcY + Ydiff;
			cropW = dragStartcW + Xdiff; 
			cropH = dragStartcH - Ydiff;
			performCrop();
		},
		stop: function(event, ui){
			performCrop();
		}
	});
	
	$('div.cropHandle.BL').draggable({
  		start: function(event, ui) {
  			_d(event);
  			_d(ui);
			dragStartcX = cropX;
			dragStartcY = cropY;
			dragStartcW = cropW;
			dragStartcH = cropH;
		},
  		drag: function(event, ui) {
			var Xdiff = (ui.position.left - ui.originalPosition.left) / zoom;
			var Ydiff = (ui.position.top - ui.originalPosition.top) /zoom;
			cropX = dragStartcX + Xdiff;
			//cropY = dragStartcY + Ydiff;
			cropW = dragStartcW - Xdiff; 
			cropH = dragStartcH + Ydiff;
			performCrop();
		},
		stop: function(event, ui){
			performCrop();
		}
	});
	
	$('div.cropHandle.BR').draggable({
  		start: function(event, ui) {
  			_d(event);
  			_d(ui);
			dragStartcX = cropX;
			dragStartcY = cropY;
			dragStartcW = cropW;
			dragStartcH = cropH;
		},
  		drag: function(event, ui) {
			var Xdiff = (ui.position.left - ui.originalPosition.left) / zoom;
			var Ydiff = (ui.position.top - ui.originalPosition.top) /zoom;
			cropW = dragStartcW + Xdiff; 
			cropH = dragStartcH + Ydiff;
			performCrop();
		},
		stop: function(event, ui){
			performCrop();
		}
	});
	
	$('div.cropArea').draggable({
		start: function(event, ui) {
  			_d(event);
  			_d(ui);
			dragStartcX = cropX;
			dragStartcY = cropY;
		},
		drag: function(event, ui) {
			var Xdiff = (ui.position.left - ui.originalPosition.left) / zoom;
			var Ydiff = (ui.position.top - ui.originalPosition.top) /zoom;
			cropX = dragStartcX + Xdiff;
			cropY = dragStartcY + Ydiff;	
			performCrop();
		},
		stop: function(event, ui){
			performCrop();
		}
	});
	
	//Flips
	$('a#flipH').click(function(){
		flipH = !flipH;
		_d(flipH);
		if (flipH) {
			$(this).addClass('active');
		} else {
			$(this).removeClass('active');
		}
		performFlip();
	});
	
	$('a#flipV').click(function(){
		flipV = !flipV;
		_d(flipV);
		if (flipV) {
			$(this).addClass('active');
		} else {
			$(this).removeClass('active');
		}
		performFlip();
	});
	
	///ZOOM
	var zoomTable = [0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1];
	$('a#zoomIn, a#zoomOut').click(function(){
		_d('click');
		_d(zoom);
		var zInc = 1;
		if ($(this).attr('id')=='zoomOut'){	zInc = -1;	}
		var idx = zoomTable.indexOf(zoom);
		_d(idx);
		_d(zoomTable.length);
		if (idx+zInc < 0 || idx+zInc > (zoomTable.length-1)){
			zInc = 0;
		} 
		zoom = zoomTable[idx+zInc];
		panX = 0;
		panY = 0;
		zoomView(zoom);
	});
	
	//PAN
	$('a#pan').click(function(){
		$(this).siblings().removeClass('selected');
		$(this).addClass('selected');
		setCurrentAction('pan');
	});
		
	$('div.panHandle').draggable({
		revert:true,
		revertDuration:0,
  		start: function(event, ui) {
  			_d(event);
  			_d(ui);
			dragStartpanX = panX;
			dragStartpanY = panY;
		},
  		drag: function(event, ui) {
			var Xdiff = (ui.position.left - ui.originalPosition.left) / zoom;
			var Ydiff = (ui.position.top - ui.originalPosition.top) / zoom;
			
			panX = dragStartpanX + Xdiff;
			panY = dragStartpanY + Ydiff;
			zoomView();
		},
	});
	
	$('div.panHandle').dblclick(function() {
		panX = 0;
		panY = 0;
		zoomView();	
	});
	//test

	
}); //End document.ready;

function zoomView(){
	$('div.scaler').transform({
	  scale: [zoom, zoom],
	  translate: [panX+'px', panY+'px']
	}, {
		preserve:false
	});
}


function performFlip(){
	var x = 1;
	if (flipV) {x = -1;}
	var y =1; 
	if (flipH) {y=-1}
	$('div.image').transform({
		scaleX : x,
	 	scaleY : y
	}, {
		preserve: false
	});
	performRotation(false);
}

function resizeWorkPlace(){
	var bh = window.innerHeight;
	var mh = $('img.logo').height(); 
	var hh = $('h1').height();
	var fh = $('fieldset.submit').height(); _d(fh); 
	var h = bh - mh - hh - fh-10;
	
	if (h<0) {
		h = 0;
	}
	$('div.workPlace').height(h);
}

function setCurrentAction(action){
	currentAction=action;
	if (action=='rotate'){
		_d('action is rotate');
		$('div.workPlace').addClass('rotate');
	} else {
		$('div.workPlace').removeClass('rotate');
	}
	if (action=='crop'){
		_d('action is crop');
		$('div.workPlace').addClass('crop');
	} else {
		$('div.workPlace').removeClass('crop');
	}
	if (action=='pan'){
		_d('action is pan');
		$('div.workPlace').addClass('pan');
	} else {
		$('div.workPlace').removeClass('pan');
	}
}


function performRotation(auto){
	
	if (angle<0){
		angle = 360+angle;
	}
	if (angle>360){
		angle = angle-360;
	}
	var padW;
	var padH;
	var W;
	var H;
	var a;
	if (angle%180 <=90) {
		a = deg2rad(angle%180);
		W = imgH * Math.sin(a) + imgW * Math.cos(a);
		H = imgH * Math.cos(a) + imgW * Math.sin(a);
	} else {
		a = deg2rad((angle%180)-90);
		W = imgW * Math.sin(a) + imgH * Math.cos(a);
		H = imgW * Math.cos(a) + imgH * Math.sin(a);
	}
	canvasW = W;
	canvasH = H;
	padW = parseInt(W-imgW)/2;
	padH = parseInt(H-imgH)/2;
	_d('padW:'+padW+  'padH:'+padH);
	$('div.image').css('margin',padH+'px '+padW+'px');
	$('input#r').val(Math.round(angle*100)/100);
	if (auto) {autoCrop();}
	performCrop();
	if (flipH == flipV){
		$('div.image').css('rotate', angle+'deg');
	} else {
		$('div.image').css('rotate', -angle+'deg');
	}
	updateForm ();
}

function performCrop(){
	var p = 11;
	fixCrops();
	
	$('div.cropCover.L').css('top',p+'px').css('left',p+'px').css('width',cropX+'px').css('height',canvasH+'px');
	$('div.cropCover.R').css('top',p+'px').css('left',p+cropX+cropW+'px').css('width',canvasW-cropX-cropW+'px').css('height',canvasH+'px');
	$('div.cropCover.T').css('top',p+'px').css('left',p+cropX+'px').css('width',cropW+'px').css('height',cropY+'px');
	$('div.cropCover.B').css('top',p+cropH+cropY+'px').css('left',p+cropX+'px').css('width',cropW+'px').css('height',canvasH-cropH-cropY+'px');
	
	$('div.cropHandle.TL').css('left',cropX+'px').css('top',cropY+'px');
	$('div.cropHandle.TR').css('left',cropX+cropW+'px').css('top',cropY+'px');
	$('div.cropHandle.BL').css('left',cropX+'px').css('top',cropY+cropH+'px');
	$('div.cropHandle.BR').css('left',cropX+cropW+'px').css('top',cropY+cropH+'px');
	
	$('div.cropHandlePoster.TL').css('left',cropX+'px').css('top',cropY+'px');
	$('div.cropHandlePoster.TR').css('left',cropX+cropW+'px').css('top',cropY+'px');
	$('div.cropHandlePoster.BL').css('left',cropX+'px').css('top',cropY+cropH+'px');
	$('div.cropHandlePoster.BR').css('left',cropX+cropW+'px').css('top',cropY+cropH+'px');
	
	$('div.cropArea').css('left',p+cropX+'px').css('top',p+cropY+'px').css('width',cropW+'px').css('height',cropH+'px');
	updateForm ();
}



function fixCrops(){
	var cw = canvasW; var ch = canvasH;
	var x = cropX; var y = cropY;
	var w = cropW; var h = cropH;
	
	if (x<0) {x = 0;}
	if (y<0) {y = 0;}
	
	if (w>cw) {w = cw;}
	if (h>ch) {h = ch;}
	
	if (x>=cw) {x=cw-1; w = 1;}
	if (y>=ch) {x=ch-1; h = 1;}

	if (x+w>cw) {w=cw-x}
	if (y+h>ch) {h=ch-y}

	if (h<30) {h = 30;}
	if (w<30) {w = 30;}
		
	cropX = x; cropY = y; cropW = w; cropH = h;
	
	parseInt($('input#cX').val(cropX));
	parseInt($('input#cY').val(cropY));
	parseInt($('input#cW').val(cropW));
	parseInt($('input#cH').val(cropH));
}


function autoCrop(){
	if (imgW<=imgH){
		if (angle%180 <= 90){
			var W=canvasW;
			var H=canvasH;
			var w=imgW;
			var h=imgH;

			var alpha = deg2rad(angle%180);
			var beta = deg2rad(90-angle%180);
			var gamma = Math.atan(W/H);
			var delta = deg2rad(180-(rad2deg(alpha)+rad2deg(gamma)));

			var d = h*Math.cos(alpha);
			var a = d * Math.sin(alpha)/Math.sin(delta);

			var y = a*Math.cos(gamma);
			var x = y*Math.tan(gamma);
			
			var cw = W-2*x;
			var ch = H-2*y;
			
			cropX=parseInt(Math.abs(x));
			cropY=parseInt(Math.abs(y));
			cropW=parseInt(cw);
			cropH=parseInt(ch);
		} else {
			var W=canvasW;
			var H=canvasH;
			var w=imgW;
			var h=imgH;

			var beta = deg2rad(angle%180);
			var alpha = deg2rad(angle%180-90);
			var gamma = Math.atan(W/H);
			var delta = deg2rad(180-(rad2deg(alpha)+rad2deg(gamma)));

			var d = h*Math.cos(alpha);
			var a = d * Math.sin(alpha)/Math.sin(delta);

			var x = a*Math.cos(gamma);
			var y = x*Math.tan(gamma);
			
			var cw = W-2*x;
			var ch = H-2*y;
			
			cropX=parseInt(Math.abs(x));
			cropY=parseInt(Math.abs(y));
			cropW=parseInt(cw);
			cropH=parseInt(ch);			
		}
	} else if (imgW>imgH){
		if (angle%180 <= 90){
			var W=canvasW;
			var H=canvasH;
			var w=imgW;
			var h=imgH;

			var beta = deg2rad(angle%180);
			var alpha = deg2rad(90-angle%180);
			var gamma = Math.atan(W/H);
			var delta = deg2rad(180-(rad2deg(alpha)+rad2deg(gamma)));

			var d = w* Math.cos(alpha);
			var a = d * Math.sin(alpha)/Math.sin(delta);

			var y = a*Math.cos(gamma);
			var x = y*Math.tan(gamma);
			
			var cw = W-2*x;
			var ch = H-2*y;
			
			cropX=parseInt(Math.abs(x));
			cropY=parseInt(Math.abs(y));
			cropW=parseInt(cw);
			cropH=parseInt(ch);
		} else {
			var W=canvasW;
			var H=canvasH;
			var w=imgW;
			var h=imgH;

			var alpha = deg2rad(180-angle%180);
			var beta = deg2rad(rad2deg(alpha) -90) ;
			var gamma = Math.atan(W/H);
			var delta = deg2rad(180-(rad2deg(alpha)+rad2deg(gamma)));

			var d = w* Math.cos(alpha);
			var a = d * Math.sin(alpha)/Math.sin(delta);

			var x = a*Math.cos(gamma);
			var y = x*Math.tan(gamma);
			
			var cw = W-2*x;
			var ch = H-2*y;
			
			cropX=parseInt(Math.abs(x));
			cropY=parseInt(Math.abs(y));
			cropW=parseInt(cw);
			cropH=parseInt(ch);	
		}
	}
}

function updateForm (){
	$('input#angle').val(angle);
	$('input#cropX').val(cropX);
	$('input#cropY').val(cropY);
	$('input#cropW').val(cropW);
	$('input#cropH').val(cropH);
	$('input#flipH').val(flipH);
	$('input#flipV').val(flipV);
}
