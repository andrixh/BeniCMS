$(document).ready(function() {

	$('body').on('mouseenter','p,h1,h2,h3,h4,h5,h6,ul,ol,table,component,pre',function(){
		$(this).prepend('<handle contenteditable="false"></handle>');
		$(this).prepend('<close contenteditable="false"></close>');
		$(this).find('close').disableSelection();
	});

	$('body').on('mouseleave','p,h1,h2,h3,h4,h5,h6,ul,ol,table,component,pre',function(){
		$(this).find('handle').remove();
		$(this).find('close').remove();
	});

	$('body').on('click','close',function(){
		$(this).parent().remove();
	});

	$('body').sortable({
		handle: 'handle',
		tolerance: 'pointer',
		axis: 'y'
	});

	$('body').on('paste',function(e){
		_d('paste Detected');
		_d(e);

		var pasteTimeout = setTimeout(function(){
			Init();
		},100);
	});

	$('html').on('click',function(e){
		if ($(e.target).is('html')){
			if ($('body').children().last()[0].hasAttribute('contenteditable') || $('body').children().last().hasClass('fake')){
				$('body').append('<p><br/></p>');
				var lastparagraph = $('body p:last-child')[0];
				var sel = window.getSelection();
				sel.removeAllRanges();
				var range = document.createRange();
				range.setStart(lastparagraph, 0);
				range.setEnd(lastparagraph,0);
				sel.addRange(range);
				e.preventDefault();
			}
		}
	});

	window.addEventListener("message", receiveMessage, false);

}); //End document.ready