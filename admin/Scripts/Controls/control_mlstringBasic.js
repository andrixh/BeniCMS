function mlStringControl(){
	var self = this;
	
	this.options = {
		widget : null,
		selector : null
	};
	
	if (arguments.length != 0){
		self.options = $.extend(self.options,arguments[0]);
	};
	
	this.widget = null;
	this.widgets = {
		tabHolder:$('<div class="languageTabs"></div>'),
		tabs:[]
	}
	this.elements =[];

	

	this.init = function(){
		var languages = CONFIG.LANGUAGES;
		
		self.widget = $(self.options.widget); 
		
		
		
		$(self.widget).find(self.options.selector).each(function(){
			self.elements.push ($(this)); 

		});
		var count = 0;
		for (var langID in languages){
			var langLink = $('<a href="#" data-language="'+languages[langID].langID+'"><img src="/admin/Gfx/Flags/'+languages[langID].flag+'.png" alt="'+languages[langID].name+'"/></a>');
			langLink[0].linkedElement = self.elements[count];
			if (self.elements[count].val() == ''){
				langLink.addClass('empty');
			}
			self.widgets.tabs.push(langLink);
			count++;
		}
		for (var i in self.widgets.tabs){
			 self.widgets.tabHolder.append(self.widgets.tabs[i]);
		}
		self.widget.append(self.widgets.tabHolder);
		
		for (var i in self.widgets.tabs){
			if (i==0){
				self.elements[i].removeClass('hidden');
				if (self.elements[i].data('widget')!==undefined){
					self.elements[i].data('widget').show();
				}
				self.widgets.tabs[i].addClass('selected');
			} else {
				self.elements[i].addClass('hidden');
				if (self.elements[i].data('widget')!==undefined){
					self.elements[i].data('widget').hide();
				}
				self.widgets.tabs[i].removeClass('selected');
			}
		}
		;
		//_d(self.widgets.tabs[0]);
		//
	}
	
	this.init();
	
	this.widgets.tabHolder.on('click','a',function(e){
		$(e.currentTarget).siblings().removeClass('selected');
		$(e.currentTarget).addClass('selected');
		$(e.currentTarget.linkedElement).siblings(self.options.selector).each(function(){
			$(this).addClass('hidden');
			if ($(this).data('widget')!==undefined){
				$(this).data('widget').hide();
			}
		})
		$(e.currentTarget.linkedElement).removeClass('hidden');
		if ($(e.currentTarget.linkedElement).data('widget')!==undefined){
			$(e.currentTarget.linkedElement).data('widget').show();
		}
		e.preventDefault();
	});

	
	this.widget.on('change',self.options.selector,function(e){
		var index = $(this).parent().find(self.options.selector).index(this);
		if ($(this).val()==''){
			self.widgets.tabs[index].addClass('empty');
		} else {
			self.widgets.tabs[index].removeClass('empty');
		}
	});
	
	this.setValues = function(values){
		var count = 0;
		for(var i in values){
			self.elements[count].val(values[i]);
			self.elements[count].trigger('change');
			count++;
		}
	}
	
	this.clear = function(){
		for(var i in self.elements){
			self.elements[i].val('');
		}
	}
	
	
}

(function($) { //MSLTRING TEXTINPUT WIDGET
	$.fn.mlStringBasic = function() {
	  	$(this).each(function() {
	  		var self = this;
	  		var fieldName = $(self).data('basename');
	  		var element = $(self).data('element');
	  		
	  		this.mlString = new mlStringControl({
	  			widget:self,
	  			selector:element
	  		});
	    });
	    
		
	}
})(jQuery);


$(document).ready(function() {

	$('fieldset.mlstring.basic, fieldset.mlGalleryField').mlStringBasic();
	//_d($('fieldset.mlstring.textinput input').attr('type'));
});  //----- end of document.ready------

