(function($) { //Form ERROR
  $.fn.formError = function() {
	$(this).each(function() {
		$(this).find('span').delay(500).fadeOut(1000);
		    $(this).mouseenter(function(){
		    	$(this).find('span').clearQueue().stop().delay(300).fadeTo(100,1);
		    });
		    
		    $(this).mouseleave(function(){
		    	$(this).find('span').clearQueue().stop().fadeOut(300);
		    	
		    });
		});
  }
})(jQuery);


$(document).ready(function() {
	$('span.formError').formError();

});  //----- end of document.ready------