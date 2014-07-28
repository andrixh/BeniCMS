function FileIcon(){
	var self = this;
	
	this._parent = '';
	this._maxWidth = 120;
	this._maxHeight = 100;
	
	
	this._id=0;
	this._physicalName='';
	this._size=0;
	this._ext='';
	this._label='';
	this._useCount=0;
	
	this._selected = false;
	this.uploading = false;
	
	this.widget = $('<div class="fileItem item"></div>');
	this.widgets = {
		label : $('<span class="label"></span>'),
		labelEditor:$('<input type="text"/>'),
		size : $('<span class="size"></span>'),
		ext : $('<span class="type"></span>'),
		useCount : $('<span class="useCount"></span>'),
	};
	
		
	this.parent = function(){ if (arguments.length == 0){ return self._parent; } else { self._parent = arguments[0]; }};
	this.id = function(){ if (arguments.length == 0){ return self._id; } else { self._id = arguments[0]; self.render(); }};
	this.physicalName = function(){ if (arguments.length == 0){ return self._ext; } else { self._ext = arguments[0]; self.render(); }};
	this.size = function(){ if (arguments.length == 0){ return self._ext; } else { self._ext = arguments[0]; self.render(); }};
	this.ext = function(){ if (arguments.length == 0){ return self._ext; } else { self._ext = arguments[0]; self.render(); }};
	this.label = function(){ if (arguments.length == 0){ return self._label; } else { self._label = arguments[0]; self.render(); }};
	this.useCount = function(){ if (arguments.length == 0){ return self._useCount; } else { self._useCount = arguments[0]; self.render(); }};
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
			self.widgets.size.text(Math.ceil(self._size/1024)+' kB');
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
			self._size = parseInt(data.size,10);
			self._ext = data.extension;
			self._label = data.fileName;
			self._useCount = parseInt(data.useCount);
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
		self.widget.append(self.widgets.label).append(self.widgets.labelEditor).append(self.widgets.size).append(self.widgets.ext).append(self.widgets.useCount);
		self.widgets.labelEditor.hide();
		
	};
	this.init();

};




$(document).ready(function(){
	var dataUrl = '_filesUtil.php';
	var actions = new ActionGroup();

	var viewer = new ItemViewer({
		itemType: FileIcon,
		loadUrl: dataUrl,
		allowedExtensions: CONFIG.FILE_ALLOWED_EXTENSIONS,
		maxFileSize: CONFIG.FILE_MAX_UPLOAD_SIZE,
		actions: actions
	});
	viewer.appendTo('body');
	actions.appendTo('body');
	
	var deleteAction = new ViewAction({
		label: 'Delete',
		selection : 2,
		click : function(action){
			_d(viewer);
			var contentStr = '';
			if (viewer.selection.length == 1){
				contentStr = '<p>Are you sure you want to delete this file?</p>';
			} else {
				contentStr = '<p>Are you sure you want to delete these '+viewer.selection.length+' files?</p>';
			}
			deleteDialog.content(contentStr);
			deleteDialog.show();
		}
	});

	actions.addAction(deleteAction);
	
	var deleteDialog = new Dialog({
		title:'Delete Files',
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
		
	//$('.plupload').attr('style','');
	resizeViewer(viewer.widget);
	$(window).on('resize',function(){resizeViewer(viewer.widget);});
	
});
