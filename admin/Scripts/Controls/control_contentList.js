(function($) { //Content List Widget
	$.fn.contentList = function() {
	  	var controlTemplate = '<ul class="contentList"></ul>';
	  	var itemTemplate = '<li></li>';

	  	$(this).each(function() {
			var self = this;
			var contents = $.parseJSON($(self).val());
			var control = $(controlTemplate);

			var classes = $(this).attr('class').split(' ');
			var acceptedTypes =[];
			for (var cl in classes) {
				if (classes[cl].substring(0,'accept_content_'.length) == 'accept_content_'){
					acceptedTypes.push(classes[cl]);
				}
			}

			for (var at in acceptedTypes){
				control.addClass(acceptedTypes[at]);
			}

			control.addClass('accept_content');
			if ($(self).hasClass('single')) {control.addClass('single');}

			_g('contentList Contents')
			for (i in contents){

				var itemData = contents[i];
				var liItem = $(itemTemplate);
				var itemHtml = itemData.listTemplate;

				for (var prop in itemData) {
					itemHtml = itemHtml.split('{'+prop+'}').join(itemData[prop]);
					for (var itemProp in itemData[prop]){
						itemHtml = itemHtml.split('{'+prop+'.'+itemProp+'}').join(itemData[prop][itemProp]);
					}
				}

				liItem.html(itemHtml);
				liItem.data('itemData',itemData);
				liItem.append('<a href="#" class="close">x</a>');
				control.append(liItem);
			}
			_u();
			control.on('click','li a.close', function(e) {
				e.preventDefault();
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
						_g('Dropped ItemData');
						_d(itemData);
						_u();
                        if (control.hasClass('accept_content_'+itemData.typeID)) {
                            var liItem = $(itemTemplate);

                            var itemHtml = itemData.listTemplate;


                            for (var prop in itemData) {
                                itemHtml = itemHtml.split('{' + prop + '}').join(itemData[prop]);
                                for (var itemProp in itemData[prop]) {
                                    itemHtml = itemHtml.split('{' + prop + '.' + itemProp + '}').join(itemData[prop][itemProp]);
                                }
                            }
                            liItem.html(itemHtml);
                            liItem.data('itemData', itemData);
                            liItem.append('<a href="#" class="close">x</a>');
                            control.append(liItem);
                            updateField();
                        }
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
	$('input.contentList').contentList();
	
});  //----- end of document.ready------