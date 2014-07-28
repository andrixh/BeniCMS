(function($) { //Gallery Field
	$.fn.galleryField = function() {
	  	var controlTemplate = '<ul class="galleryField"></ul>';
	  	var itemTemplate = '<li><img/></li>';
	  	$(this).each(function() {
			var self = this;
			var images = $.parseJSON($(self).val());
			var imgPath = $(self).attr('data-imagePath');
			var control = $(controlTemplate);
			if ($(self).hasClass('accept_image')) {control.addClass('accept_image');}
			if ($(self).hasClass('accept_video')) {control.addClass('accept_video');}
			if ($(self).hasClass('single')) {control.addClass('single');}
			
			for (i in images){
				var liItem = $(itemTemplate);
				liItem.data('itemData',images[i]);
				if (images[i].resourceType == 'image'){
					liItem.find('img').attr('src',imgPath+images[i].physicalName+'_80_80_C_35.'+images[i].type);
				}  else if (images[i].resourceType == 'video'){
					liItem.find('img').attr('src',imgPath+images[i].thumbnail+'_80_80_C_35.'+images[i].thumbnailType);
					liItem.addClass('video');
				}
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
						if (itemData.resourceType == 'image'){
							liItem.find('img').attr('src',imgPath+itemData.physicalName+'_80_80_C_35.'+itemData.type);
						} else if (itemData.resourceType == 'video'){
							liItem.find('img').attr('src',imgPath+itemData.thumbnail+'_80_80_C_35.'+itemData.thumbnailType);
							liItem.addClass('video');
						}

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
	_d('gallery fields');
	$('input.galleryField').galleryField();
	//$('fieldset.mlGalleryField').galleryField();
});  //----- end of document.ready------