(function($) { //tableSettings
	$.fn.tableSettings = function() {


		$(this).each(function() {
			if ($(this).data('table-initialized') != true){
				$(this).data('table-initialized',true);

				var self = $(this);

				var rowUp = $('<rowup contenteditable="false"></rowup>');
				var rowDown = $('<rowdown contenteditable="false"></rowdown>');
				var rowAdd = $('<rowadd contenteditable="false"></rowadd>');
				var rowRemove = $('<rowremove contenteditable="false"></rowremove>');
				var colLeft = $('<colleft contenteditable="false"></colleft>');
				var colRight = $('<colright contenteditable="false"></colright>');
				var colAdd = $('<coladd contenteditable="false"></coladd>');
				var colRemove = $('<colremove contenteditable="false"></colremove>');

				$(self).append(rowUp,rowDown,rowAdd,rowRemove,colLeft,colRight,colAdd,colRemove);


				$(self).on('mouseenter','td', function(event) {
					var rowY = 0;

					var trIdx = $(self).find('tr').index($(this).parent());
					var trCount =0;
					$(self).find('tr').each(function(){
						if (trCount < trIdx){
							rowY += $(this).outerHeight();
							trCount++;
						}
					});

					var colX = 0;
					var tdIdx = $(this).parent().find('td').index($(this));
					var tdCount = 0;
					$(this).parent().find('td').each(function(){
						if (tdCount < tdIdx){
							colX += $(this).outerWidth();
							tdCount++;
						}
					});


					$(self).find('rowup').css('top',(rowY+14)+'px').css('left','2px').attr('index',trIdx);
					$(self).find('rowdown').css('top',(rowY+14+12)+'px').css('left','2px').attr('index',trIdx);
					$(self).find('rowadd').css('top',(rowY+14)+'px').css('right','20px').attr('index',trIdx);
					$(self).find('rowremove').css('top',(rowY+14+12)+'px').css('right','20px').attr('index',trIdx);

					$(self).find('colleft').css('top','2px').css('left',(colX+14)+'px').attr('index',tdIdx);
					$(self).find('colright').css('top','2px').css('left',(colX+14+12)+'px').attr('index',tdIdx);
					$(self).find('coladd').css('top','2px').css('left',(colX+$(this).outerWidth()-12+2)+'px').attr('index',tdIdx);
					$(self).find('colremove').css('top','2px').css('left',(colX+$(this).outerWidth()+2)+'px').attr('index',tdIdx);

				});




				$(self).on('click','rowup',function(e){
					_d('rowUp');
					e.preventDefault();
					var idx = parseInt($(this).attr('index'),10);
					if (idx > 0){
						$(this).parent().find('tr').eq(idx).after($(this).parent().find('tr').eq(idx-1));
					}
				});

				$(self).on('click','rowdown',function(e){
					_d('rowDown');
					e.preventDefault();
					var idx = parseInt($(this).attr('index'),10);
					if (idx < $(this).parent().find('tr').length){
						$(this).parent().find('tr').eq(idx).before($(this).parent().find('tr').eq(idx+1));
					}
				});

				$(self).on('click','rowadd',function(e){
					e.preventDefault();
					var idx = parseInt($(this).attr('index'),10);
					var numTr = $(this).parent().find('tr').eq(0).find('td').length;
					var newTr = '<tr>';
					for (var i=0; i<numTr; i++){
						newTr+='<td></td>';
					}
					newTr += '</tr>';
					$(this).parent().find('tr').eq(idx).after(newTr);
				});

				$(self).on('click','rowremove',function(e){
					e.preventDefault();
					var idx = parseInt($(this).attr('index'),10);
					$(this).parent().find('tr').eq(idx).remove();
				});

				$(self).on('click','colleft',function(e){
					e.preventDefault();
					var idx = parseInt($(this).attr('index'),10);
					if (idx > 0){
						$(this).parent().find('tr').each(function(){
							var oldHtml = $(this).find('td').eq(idx-1).html();
							$(this).find('td').eq(idx-1).html($(this).find('td').eq(idx).html());
							$(this).find('td').eq(idx).html(oldHtml);
						});
					}
				});

				$(self).on('click','colright',function(e){
					e.preventDefault();
					var idx = parseInt($(this).attr('index'),10);
					if (idx < $(this).parent().find('tr').eq(0).find('td').length -1 ){
						$(this).parent().find('tr').each(function(){
							var oldHtml = $(this).find('td').eq(idx+1).html();
							$(this).find('td').eq(idx+1).html($(this).find('td').eq(idx).html());
							$(this).find('td').eq(idx).html(oldHtml);
						});
					}
				});


				$(self).on('click','coladd',function(e){
					e.preventDefault();
					var idx = parseInt($(this).attr('index'),10);

					$(this).parent().find('tr').each(function(){
						var oldHtml = $(this).find('td').eq(idx).after('<td></td>');
					});
				});


				$(self).on('click','colremove',function(e){
					e.preventDefault();
					var idx = parseInt($(this).attr('index'),10);

					$(this).parent().find('tr').each(function(){
						var oldHtml = $(this).find('td').eq(idx).remove();
					});
				});
			}
		});
	}
})(jQuery);