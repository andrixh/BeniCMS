(function($) { //IMAGE SELECT WIDGET
	$.fn.imageSelect = function() {
	  	$(this).each(function() {
			$this = this;
			$($this).hide();
			var values =[];
			$($this).find('option').each(function(){
				values[$(this).val()]=$(this).text();
			});
            _d('control imageSelect');
            _d(values);
			var control = $('<ul class="imageSelect clearfix"></ul>');
			for (var key in values){
				$(control).append('<li><img src="'+values[key]+'" title="'+key+'"/></li>');
			}
			$(control).find('li img[title="'+$($this).find('option[selected]').attr('value')+'"]').parent().addClass('selected');
			$(control).find('li').click(function(){
				$index = $(this).find('img').attr('title');
				$(this).siblings().removeClass('selected');
				$(this).addClass('selected');
				$($this).val($index);
			});
			$($this).after(control);
			if ($(control).find('.selected').length > 0){
				_d($(control).find('.selected').position().top);
				setTimeout(function(){_d($(control).find('.selected').position().top);$(control).scrollTop($(control).find('.selected').position().top);},1000);
			}
	    });
	}
})(jQuery);


$(document).ready(function() {
	$('select.imageSelect').imageSelect();
});  //----- end of document.ready------