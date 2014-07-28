function ImageThumb(){
	var self = this;
	
	this._parent = '';
	this._maxWidth = 120;
	this._maxHeight = 100;
	
	
	this._id=0;
	this._physicalName='';
	this._width=120;
	this._height=120;
	this._ext='';
	this._label='';
	this._useCount=0;
	this._description='';
	
	this._selected = false;
	this.uploading = false;
	
	this.widget = $('<figure class="imageItem item"></figure>');
	this.widgets = {
		thumb : $('<div class="thumbArea"></div>'),
		img : $('<img class="thumb" />'),
		figcaption : $('<figcaption></figcaption>'),
		label : $('<span class="label"></span>'),
		labelEditor:$('<input type="text"/>'),
		resolution : $('<span class="resolution"></span>'),
		ext : $('<span class="type"></span>'),
		useCount : $('<span class="useCount"></span>'),
		description : $('<span class="description"></span>')
	};
	
		
	this.parent = function(){ if (arguments.length == 0){ return self._parent; } else { self._parent = arguments[0]; }};
	this.id = function(){ if (arguments.length == 0){ return self._id; } else { self._id = arguments[0]; self.render(); }};
	this.physicalName = function(){ if (arguments.length == 0){ return self._ext; } else { self._ext = arguments[0]; self.render(); }};
	this.width = function(){ if (arguments.length == 0){ return self._ext; } else { self._ext = arguments[0]; self.render(); }};
	this.height = function(){ if (arguments.length == 0){ return self._ext; } else { self._ext = arguments[0]; self.render(); }};
	this.ext = function(){ if (arguments.length == 0){ return self._ext; } else { self._ext = arguments[0]; self.render(); }};
	this.label = function(){ if (arguments.length == 0){ return self._label; } else { self._label = arguments[0]; self.render(); }};
	this.useCount = function(){ if (arguments.length == 0){ return self._useCount; } else { self._useCount = arguments[0]; self.render(); }};
	this.description = function(){ if (arguments.length == 0){ return self._description; } else { self._description = arguments[0]; self.render(); }};
	this.selected = function(){
	
		if (arguments.length == 0){ 
			return self._selected; 
		} else {
			var prev=self._selected;
			self._selected = arguments[0]; 
			if(self._selected != prev) {
				self.render();
			}
		}
	};
	
	
		
	this.onLabelChangeEvent = function(id,label){
		$.get(self.parent().loadUrl+'?action=label&id='+id+'&label='+encodeURIComponent(label));
	}
	
		
	this.render = function(){
		if (self._uploading != true){
			self.widgets.img.attr('src',CONFIG.IMAGE_RESIZED_DIRECTORY + self._physicalName+'_'+self._maxWidth+'_'+self._maxHeight+'_S_30.'+self._ext.toLowerCase());
			var w=0;
			var h=0;
			var rw = self._width;
			var rh = self._height;
			var mw = self._maxWidth;
			var mh = self._maxHeight;
			
			w = mw;	h = (w/rw)*rh;
			if (h>mh){ h = mh; w = (h/rh)*rw;}
	
			self.widgets.img.attr('width',Math.floor(w)).attr('height',Math.floor(h)).css('top',((mh-h)/2)+'px').css('left',((mw-w)/2)+'px')
			
			self.widgets.resolution.text(self._width+' x '+self._height);
			self.widgets.ext.text(self._ext).addClass(self._ext.toLowerCase());
			self.widgets.useCount.text(self._useCount);
			if (self._useCount != 0){
				self.widgets.useCount.addClass('used');
			} else {
				self.widgets.useCount.removeClass('used');
			}
			self.widgets.label.text(self._label);
			if (self._label == ''){
				self.widgets.label.text('(untitled)');
			} 
			self.widgets.labelEditor.val(self._label);
		//	self.widgets.description.attr('title',self._description);
			if (self._description != ''){
				self.widgets.description.removeClass('empty');
			} else {
				self.widgets.description.addClass('empty');
			}
			self.widget.removeClass('uploading');
		} else {
			self.widget.addClass('uploading');
		}
		self.widget.attr('id',self._id);
		if (self._selected == true){
			self.widget.addClass('selected');
		} else {
			self.widget.removeClass('selected');
		}
	};
	
	this.setData = function(data){
		_d(data);
		self._uploading = data.uploading;
		if (self._uploading != true){
			self._id = data.ID;
			self._physicalName = data.physicalName;
			self._width = parseInt(data.width,10);
			self._height = parseInt(data.height,10);
			self._ext = data.type;
			self._label = data.label;
			self._useCount = parseInt(data.useCount);
			self._description = data.description;
		}
		self.render();
		
	};
	if (arguments.length == 1){
		self.setData(arguments[0]);
	}
	
	this.appendTo = function(dest){
		$(dest).append(self.widget);
	};
	
	this.widgets.labelEditor.on('keyup focusout',function(e){
		if (e.type == 'focusout' || (e.type == 'keyup' && e.keyCode == 13)){
			var prevLabel = self.label();
			self.widgets.labelEditor.hide();
			self.label(self.widgets.labelEditor.val());
			if (prevLabel != self.label()){
				self.onLabelChangeEvent(self.id(),self.label());	
			}
		} else if (e.type == 'keyup' && e.keyCode == 27){
			self.widgets.labelEditor.hide();
		}
	});
	
	this.widgets.label.on('click',function(){
		self.widgets.labelEditor.show().focus();
		
		self.widgets.labelEditor[0].setSelectionRange(0,self.widgets.labelEditor.val().length);
	});
	
	this.init = function(){
		self.widget.append(self.widgets.thumb).append(self.widgets.figcaption);
		self.widgets.thumb.append(self.widgets.img);
		self.widgets.figcaption.append(self.widgets.label).append(self.widgets.labelEditor).append(self.widgets.resolution).append(self.widgets.ext).append(self.widgets.useCount).append(self.widgets.description);
		self.widgets.labelEditor.hide();
		
	};
	this.init();
	
	this.widgets.description.on('click',function(e){
		descriptionDialog.title('Edit Image Description');
		descriptionDialog.show();
		descriptionDialog.working(true);
		descriptionDialog.id = self.id();
		$.get(self.parent().loadUrl+'?action=getDescription&id='+self.id(),function(data){
			descriptionDialog.widgets.body.find('.mlstring')[0].mlString.setValues(JSON.parse(data));
			descriptionDialog.working(false);
		});
		
	});
};









var descriptionDialog;


$(document).ready(function(){
	
	var dataUrl = '_imagesUtil.php';

	var actions = new ActionGroup();

	var viewer = new ItemViewer({
		itemType: ImageThumb,
		loadUrl: dataUrl,
		allowedExtensions: CONFIG.IMAGE_ALLOWED_EXTENSIONS,
		maxFileSize: CONFIG.IMAGE_MAX_UPLOAD_SIZE,
		actions: actions
	});
	viewer.appendTo('body');
	actions.appendTo('body');


	var descriptionAction = new ViewAction({
		label: 'Edit Description',
		selection : 2,
		click : function(action){
			descriptionDialog.show();
			descriptionDialog.working(true);
			descriptionDialog.title('Set Multiple Descriptions');
			descriptionDialog.working(false);
			descriptionDialog.id = action.list().join(',');
			$.get(viewer.loadUrl+'?action=getDescription&id='+action.list()[0],function(data){
				descriptionDialog.widgets.body.find('.mlstring')[0].mlString.setValues(JSON.parse(data));
				descriptionDialog.working(false);
			});
			
			
			//descriptionDialog.widgets.body.find('.mlstring')[0].mlString.clear();
		}
	});
	
	var deleteAction = new ViewAction({
		label: 'Delete',
		selection : 2,
		click : function(action){
			_d(viewer);
			var contentStr = '';
			if (viewer.selection.length == 1){
				contentStr = '<p>Are you sure you want to delete this image?</p>';
			} else {
				contentStr = '<p>Are you sure you want to delete these '+viewer.selection.length+' images?</p>';
			}
			deleteDialog.content(contentStr);
			deleteDialog.show();
		}
	});
	
	var editImageAction = new ViewAction({
		label: 'Edit Image',
		selection : 1,
		url: 'imagesEdit.php'
	});
	
	actions.addAction(descriptionAction);
	actions.addAction(editImageAction);
	actions.addAction(deleteAction);
	
	var deleteDialog = new Dialog({
		title:'Delete Images',
		actions : [
			{
				label:'Cancel',
				click:function(parent){
					parent.hide();
				}	
			},
			{
				label:'Delete',
				click:function(parent){
					var url = viewer.loadUrl;
					var ids =[];
					$(viewer.widget.find('.item.selected')).each(function(){
						ids.push($(this).attr('id'));
					});
					parent.working(true);
					$.get(url+'?action=delete&ids='+ids.join(','),function(data){
						parent.hide();
						var deletedIds = JSON.parse(data);
						
						for (i in deletedIds){
							viewer.removeItem(deletedIds[i]);
						}
						viewer.clearSelection();
					});
					_d(ids);
				}
			}
		
		
		]
		
	});

    descriptionDialog = new Dialog({
		title: 'Edit Description',
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
					
					_d(parent.id);
					var url = viewer.loadUrl+'?action=setDescription&ids='+parent.id;
					var postParams = parent.widget.find('form').serialize();
					
					$.post(url,postParams,function(data){
						_d(data);
						var changes = JSON.parse(data);
						_d(changes);
						for (var i in changes){
							viewer.items[i].description(changes[i]);
						}
						parent.working(false);
						parent.hide();
					});
				}
			}
		
		] 
	});
	
	$.get(dataUrl+'?action=getDescriptionForm',function(data){
		descriptionDialog.content(data);
		descriptionDialog.widgets.body.find('.mlstring').mlStringBasic();
	});
	
	//$('.plupload').attr('style','');
	resizeViewer(viewer.widget);
	$(window).on('resize',function(){resizeViewer(viewer.widget);});
	
});


