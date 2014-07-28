function DataLoader(){
	var self = this;
	
	this.url = '';
	this.status = {};
	this.scheme = {};
	this.data = {};
	
	this.onLoadEvent = function(){}
	
	this.onLoad = function(e){
		self.onLoadEvent = e;
	};
	
	this.onLoadErrorEvent = function(){};
	
	this.onLoadError = function(e){
		self.onLoadErrorEvent = e;
	}
	
	this.load = function(/*url*/){
		if (arguments.length == 1){ 
			self.url = arguments[0];
		}
		if (self.url!=''){
			$.getJSON(self.url,function(data){
				self.onLoadEvent(data);
			});
		}
	}
		
}


function ViewAction(){
	var self=this;
	
	this._label = '';
	this._enabled = false;
	this._selection = 0;
	this._url='#';
	this._list =[];
	this.widget = $('<a class="button disabled" href="#"></a>');
	
	this.options = {
		label: 'action label',
		selection : 0,
		url: '#',
		click : null
	}
	
	if (arguments.length != 0){
		self.options = $.extend(self.options,arguments[0]);
	}
	
	this.onClickEvent = function(){};
	
	this.widget.on('click',function(e){
		if (self._url == '#'){
			self.onClickEvent(self);
			e.preventDefault();
		}
	});
	
	this.list = function(){
		if (arguments.length == 0){
			return self._list;
		} else {
			self._list = arguments[0];
			len = self._list.length;
			if (self.selection() == 0) {
				self.enabled(len==0);
			} else if (self.selection() == 1){
				self.enabled(len==1);
			} else if (self.selection() > 1){
				self.enabled(len>=1);
			}
			if (self._url!='#'){
				self.widget.attr('href',self._url+'?id='+self._list.join(','));
			}
			
		}
		
	}
	
	
	
	this.label = function(){
		if (arguments.length == 0){
			return self._label;
		} else {
			self._label = arguments[0];
			self.widget.text(self._label);
		}
	}
	
	this.enabled = function(){
		if (arguments.length == 0){
			return self._enabled;
		} else {
			_d(arguments[0]);
			self._enabled = arguments[0];
			if (self._enabled == true){
				self.widget.removeClass('disabled');
			} else {
				self.widget.addClass('disabled');
			}
		}
	}
	
	this.url = function(){
		if (arguments.length == 0){
			return self._url;
		} else {
			_d(arguments[0]);
			self._url = arguments[0];
			if (self._enabled == true){
				self.widget.removeClass('disabled');
			} else {
				self.widget.addClass('disabled');
			}
		}
	}
	
	this.selection = function(){
		if (arguments.length == 0){
			return self._selection;
		} else {
			self._selection = arguments[0];
		}
	}
	
	this.appendTo = function(dest){
		$(dest).append(self.widget);
	};
	
	
	
	this.init = function(){
		self.label(self.options.label);
		self.url(self.options.url);
		self.selection(self.options.selection);
		self.onClickEvent = self.options.click;
	}
	this.init();

	
}

function ActionGroup(){
	var self = this;
	this.widget = $('<div class="actionsHolder"><div class="actions"></div></div>');
	this.actions =[];
	this._selection =[];
	
	this.addAction = function(action){
		self.actions.push(action);
		action.appendTo(self.widget.find('.actions'));
		self.selection([]);
	}

	this.appendTo = function(dest){
		$(dest).append(self.widget);
	};

	this.selection = function(){
		if (arguments.length == 0){
			return self._selection;
		} else {
			self._selection = arguments[0];
			var len = self._selection.length;
			for (var i in self.actions){
				self.actions[i].list(self._selection);
			}
			if (self.widget.find('a.button').not('.disabled').length == 0){
				self.widget.find('div.actions').addClass('empty');
			} else {
				self.widget.find('div.actions').removeClass('empty');
			}
		}
	}
	
	this.init = function(){
		
	}
}


function ItemViewer(){
	var self = this;
	
	this.options = {
		itemType: null,
		loadUrl: '',
		allowedExtensions: '',
		maxFileSize: 10,
		actions : null
	}
	if (arguments.length != 0){
		self.options = $.extend(self.options,arguments[0])
	}
	
	this.itemType = self.options.itemType;
	this.loadUrl = self.options.loadUrl;
	this.allowedExtensions = self.options.allowedExtensions;
	this.maxFileSize = self.options.maxFileSize;
	this.actions = self.options.actions;
	
	this.widget = $('<div class="itemViewer" id="droparea"></div>');
	this.loader = new DataLoader;
	this.items = {};
	this.uploads = {};
	
	this.uploader = '';
	
	this.selection =[];
	this.lastSelection = -1;
	
	this.clearSelection = function(){
		self.selection =[];
		self.lastSelection = -1;
		self.setSelection();
	}
	
	this.widget.on('click', function(e){
		if(e.currentTarget == e.target){
			self.clearSelection();
		}
	});
	
	this.widget.on('click','.item', function(e){
		e.preventDefault();
		e.stopPropagation();
		var selIndex = self.widget.find('.item').index(e.currentTarget);
		//var selIndex = parseInt($(e.currentTarget).attr('id'),10);
		_d('selIndex = '+selIndex);
		if (!e.ctrlKey && !e.shiftKey){
			self.selection = [selIndex];
		} else {
			if (e.ctrlKey && !e.shiftKey){
				if (self.selection.indexOf(selIndex)==-1){
					self.selection.push(selIndex);
				} else {
					self.selection.splice(self.selection.indexOf(selIndex),1);	
				}
			} else if (e.shiftKey){
				_d('shift pressed');
				var minSel = Math.min(self.lastSelection,selIndex);
				var maxSel = Math.max(self.lastSelection,selIndex);
				
				if (minSel == -1){
					minSel = maxSel;
				}
				
				if (!e.ctrlKey){
					self.selection =[];
					
					
				}
				if (self.selection.indexOf(selIndex) == -1){
					for (var i= minSel; i<= maxSel; i++){
						if (self.selection.indexOf(i) == -1) {
							self.selection.push(i);	
						}
					}
				} else {
					for (var i= minSel; i<= maxSel; i++){
						if (self.selection.indexOf(i) != -1){
							self.selection.splice(self.selection.indexOf(i),1);	
						}
					}
				}
			}
		}
		
		_d(self.selection);
		self.lastSelection = selIndex;
		self.setSelection();
		return false;
	});
	
	this.setSelection = function(){
		var idList =[];
		$collection = self.widget.find('.item').each(function(count){
			var id = $(this).attr('id');
			if (self.selection.indexOf(count)!=-1){
				self.items[id].selected(true);
				idList.push(id);
			} else {
				self.items[id].selected(false);
			}
			//self.items[id].selected(self.selection.indexOf(count)!=-1);
		});
		self.actions.selection(idList);
	}
	
	this.removeItem = function(id){
		var deletedItem = self.items[id];
		self.items[id].widget.remove();
		delete self.items[id];
		_d(self.items);
	}
	
	
	this.loadComplete = function(data){
		for (var i in data){
			var item = new self.itemType(data[i]);
			item.parent(self);
			self.items[data[i].ID]=item;
			item.appendTo(self.widget);
			
		}
		//_d(self.items);
	}
	
	this.appendTo = function(dest){
		$(dest).append(self.widget);
		
		self.uploader = new plupload.Uploader({
			runtimes : 'html5',
			drop_element : 'droparea',
			url : self.loadUrl,
			max_file_size : CONFIG.IMAGE_MAX_UPLOAD_SIZE+'mb',
			filters : [
				{title : "Allowed files", extensions:self.allowedExtensions}
			]
		});
		self.uploader.init();
		self.uploader.bind('FilesAdded', function(up, files) {
			for (var i in files){
				var item = new self.itemType({uploading:true});
				item.parent(self);
				self.items[files[i].id]=item;
				self.uploads[files[i].id] = item;
				item.appendTo(self.widget);
			}
			
			
			self.uploader.start();
			_d(files);
		});
		
			
		self.uploader.bind('FileUploaded', function(up, file, extradata) {
			var newFile = JSON.parse(extradata.response).newFile;
			
			self.uploads[file.id].setData(newFile);
			delete self.uploads[file.id];
			self.items[newFile.ID]=self.items[file.id];
			delete self.items[file.id];
			
			_d(Object.keys(self.uploads).length);
        });
		
	};
	
	this.init = function(){
		this.loader.onLoad(self.loadComplete);
		this.loader.load(self.loadUrl+'?action=list');
	};
	this.init();
	
}


$(document).ready(function(){

	$('body').on('drop dragenter dragover dragleave',function(e){
		//alert('drop on body');
		e.stopPropagation();
		e.preventDefault();
		return false;
	});
});
function resizeViewer(viewer){
	_d('resizeViewer');
	var height = $(window).height();
		_d(height);
	$(viewer).prevAll('h1').each(function(){
		_d(this);
		_d($(this).outerHeight());
        if (!$(this).is('#sideMenu')){
            _d('not sidemenu');
            height-=$(this).outerHeight();
        }
	});
	viewer.height(height-60);
}
