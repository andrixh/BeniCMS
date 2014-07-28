(function($) { 
	$.fn.customList = function() { //custom options list
	  	$(this).each(function() {
			var self = this;
			
			var controlFieldset = $('<fieldset class="customList"></fieldset>');
			var selectCustom = $('<select class="customSelect no_include" size="6"/>');
			var inputValue = $('<input type="text" placeholder="value" class="customValue no_include"/>');
			var inputLabel = $('<input type="text" placeholder="label" class="customLabel no_include"/>');
			var btnAdd = $('<a href="#" class="btn add">+</a>');
			var btnRemove = $('<a href="#" class="btn remove">-</a>');
			
			controlFieldset.append(selectCustom).append(inputValue).append(inputLabel).append(btnAdd).append(btnRemove);
			
			$(self).after($(controlFieldset));
			
			var values = $.parseJSON($(self).val());
			for (var val in values){
				selectCustom.append('<option value="'+val+'">'+values[val]+'</option>');
			} 
			
			$(btnAdd).click(function() {
				var value = inputValue.val();
				var label = inputLabel.val()
				if (value !='' && label!=''){
					if ($(selectCustom).find('option[value="'+value+'"]').length == 0){
						$(selectCustom).append('<option value="'+inputValue.val()+'">'+inputLabel.val()+'</option>');	
					} else {
						$(selectCustom).find('option[value="'+value+'"]').text(label);
					}
				}
				$(selectCustom).trigger('change');
				return false;
			});
			
			$(btnRemove).click(function() {
				var value = $(inputValue).val();
				_d(value);
				$(selectCustom).find('option[value="'+value+'"]').remove();
				$(selectCustom).trigger('change');
			});
			
			$(selectCustom).change(function() {
				$(inputValue).val($(this).find('option:selected').val());
				$(inputLabel).val($(this).find('option:selected').text());
				var result = {}
				$(selectCustom).find('option').each(function() {
					result[$(this).val()]=$(this).text();
					$(self).val(JSON.stringify(result));
				});
				return false;
			});
			
			
			$(self).change(function(){
				if ($(this).val != ''){
					vals = $.parseJSON($(this).val());	
					$(selectCustom).children().remove();
					for (val in vals){
						$(selectCustom).append('<option value="'+val+'">'+vals[val]+'</option>');	
					}
				}
				
			});
			
		});	
	}
})(jQuery);

$(document).ready(function(){
	$('input.customList').customList();
});
