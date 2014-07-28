function Dialog(){
	var self=this;
	
	this.options = {
		title : "Dialog",
		actions : [],
		postInit : null
	}
	
	if (arguments.length != 0){
		self.options = $.extend(self.options,arguments[0])
	}

	this.actions =[];

	this.widget = $('<div class="overlay hidden"><form class="dialog"></form></div>'	);
	this.widgets = {
		title:$('<h2></h2>'),
		body:$('<fieldset class="col1"></fieldset>'),
		actions:$('<fieldset class="submit"></fieldset>'),
		working:$('<div class="working hidden"></div>')
	};
	
	this.working = function(working){
		if (working == true)
			this.widgets.working.removeClass('hidden');
		else {
			this.widgets.working.addClass('hidden');
		}
	};
	
	this.addAction = function(action){
		self.actions.push(action);
		self.widgets.actions.append('<label></label>');
		action.appendTo(self.widgets.actions.find('label:last-child'));
		action.dialog(self);	
	};
	
	this.content = function(content){
		self.widgets.body.html(content);
	};
	
	this.show = function(){
		_d('show dialog');
		self.working(false);
		self.widget.removeClass('hidden');
		self.widget.fadeIn('fast');
		var w = self.widget.find('.dialog').outerWidth();var h = self.widget.find('.dialog').outerHeight();
		var W = self.widget.outerWidth(); var H = self.widget.outerHeight();
		var x = (W-w)/2;var y = (H-h)/2;
		if (x<0){x=0;};if (y<0){y=0;}
		self.widget.find('.dialog').css('top',y+'px').css('left',x+'px');
		
	};
	
	this.hide = function(){
		self.widget.fadeOut('fast',function(){
			self.widget.addClass('hidden');
		});
	};
	
	this.title = function(){
		if (arguments.length != 0){
			return self._title;
		} else {
			self._title = arguments[0];
			self.widgets.title.text(self._title);
			return self;
		}
	};
	
	this.init = function(){
		self._title = self.options.title;
		for (var i in self.options.actions){
			var newAction = new DialogAction(self.options.actions[i]);
			newAction.parent = self;
			self.addAction(newAction);
		}
		
		if (self.options.postInit != null){
			self.options.postInit();
		}

		self.widget.find('.dialog').append(self.widgets.title).append(self.widgets.body).append(self.widgets.actions).append(self.widgets.working);
		self.widgets.title.text(self._title);

		$('body').append(self.widget);
		self.widget.find('.dialog').draggable({
			handle:'h2',
			cursor:'move'
		});
		//self.widget.hide();
	};
	this.init();
}


function DialogAction(){
	var self=this;
	this.parent = null;
	
	this.options = {
		label : "Action",
		click : function(){}
	};
	
	if (arguments.length != 0){
		self.options = $.extend(self.options,arguments[0])
	}
	
	this.widget = $('<a class="formButton" href="#"></a>');
	
	this.widget.on('click',function(){self.onClickEvent(self.parent);});
	
	this._dialog = null;
	
	this.onClickEvent = function(parent){
		
	};
	
	this.appendTo = function(dest){
		$(dest).append(self.widget);
	};
	
	this.dialog=function(){
		if (arguments.length == 0){
			return self._dialog; 
		} else {
			self._dialog = arguments[0];
		}
	};
	
	this.init = function(){
		self.widget.text(self.options.label);
		self.onClickEvent = self.options.click;
	};
	this.init();
}


