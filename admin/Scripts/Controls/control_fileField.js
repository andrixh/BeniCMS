(function($) { //IMAGE SELECT WIDGET
	$.fn.fileField = function() {
	  	var controlTemplate = '<ul class="fileField"></ul>';
	  	var itemTemplate = '<li><img width="16" height="16"/><span class="fileName"></span></li>';
	  	$(this).each(function() {
			var self = this;
			var files = $.parseJSON($(self).val());
			var control = $(controlTemplate);
			control.addClass('accept_file');
			if ($(self).hasClass('single')) {control.addClass('single');}
			
			for (i in files){
				var liItem = $(itemTemplate);
				liItem.data('itemData',files[i]);
				liItem.find('img').attr('src','Gfx/Extensions/'+files[i].extension+'.png');
				liItem.find('span.fileName').text(files[i].fileName+'.'+files[i].extension)
				liItem.append('<a href="#" class="close">x</a>');
				control.append(liItem);
			}
			control.find('li a.close').click(function() {
				$(this).parent().remove();
				updateField();
				return false;
			});
			
			
			control.droppable({
				tolerance:'pointer',
				drop: function( event, ui ) {
					if (ui.draggable.hasClass('ui-draggable')){
						if (control.hasClass('single')){
							control.children().remove();
						}
						var itemData = ui.draggable.data('itemData');
						var liItem = $(itemTemplate);
						liItem.data('itemData',itemData);
						liItem.find('img').attr('src','Gfx/Extensions/'+itemData.extension+'.png');
						liItem.find('span.fileName').text(itemData.fileName+'.'+itemData.extension)
						liItem.append('<a href="#" class="close">x</a>');
						liItem.find('a.close').click(function(){
							liItem.remove();
							updateField();
							return false;
						});
						control.append(liItem);
						updateField();
					}
				}
			});
			
			control.sortable({
				cancel: 'a.close',
				stop: function(){
					updateField();
				}	
			});
			
			function updateField(){
				$(self).val('');
				var data =[];
				$(control).find('li').each(function() {
				  data.push($(this).data('itemData'));
				});
				$(self).val(JSON.stringify(data));
				_d(JSON.stringify(data));
			}
			$(self).data('widget',control);
			
			$(self).hide();
			$(self).after(control);
	    });
	}
})(jQuery);


$(document).ready(function() {
	$('input.fileField').fileField();
	
});  //----- end of document.ready------