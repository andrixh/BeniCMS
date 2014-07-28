function receiveMessage(event){
	//var target = event.source.frameElement;
	var message = event.data;
	//_d(event.data);
	
	if (message.action == 'insertImage'){
		_d('inserting image');
		var parents = $(window.getSelection().focusNode).parentsUntil('body');
		_d(parents);
		var currentItem = false;
		if (parents.length != 0){
			currentItem = parents[parents.length-1];
		}
		var itemData = message.data;
		var newData = $('<p class="img"><img src="/Images/Resized/'+itemData.physicalName+'_100_100_C_30_t.'+itemData.type+'"/></p>');
		
		if ($('.candidate').length==0){
			$('body').append(newData);
		} else {
			$('.candidate').after(newData);
			$('.candidate').removeClass('candidate');
		}
		$(newData).imageSettings();

	} else if (message.action == 'insertVideo'){
		_d('inserting Video');
		_d(message);


		var parents = $(window.getSelection().focusNode).parentsUntil('body');
		var currentItem = false;
		if (parents.length != 0){
			currentItem = parents[parents.length-1];
		}

		var itemData = message.data;
		var newData = $('<p class="video"><img src="/Images/Resized/'+itemData.thumbnail+'_100_100_C_30_t.'+itemData.thumbnailType+'"/></p>');
		$(newData).data('itemData',itemData);
		if ($('.candidate').length==0){
			$('body').append(newData);
		} else {
			$('.candidate').after(newData);
			$('.candidate').removeClass('candidate');
		}
		$(newData).videoSettings();
	} else if (message.action == 'insertComponent'){
		_d('inserting component');
		var parents = $(window.getSelection().focusNode).parentsUntil('body');
		_d(parents);
		var currentItem = false;
		if (parents.length != 0){
			currentItem = parents[parents.length-1];
		}
		var itemData = message.data;
		var newData = $('<p class="component" contenteditable="false"></p>');
		newData.html(itemData.preview);
		newData.attr('data-component',JSON.stringify(itemData));
		if ($('.candidate').length==0){
			$('body').append(newData);
		} else {
			$('.candidate').after(newData);
			$('.candidate').removeClass('candidate');
		}
	} else if (message.action == 'attemptDrop'){
		if (message.data.resourceType == 'image' || message.data.resourceType == 'video' || message.data.resourceType == 'component'){
			var candidates = $('body>p,body>h1,body>h2,body>h3,body>h4,body>h5,body>h6,body>ul,body>ol,body>table');
			if (candidates.length != 0){
				$(candidates).each(function(){
					scrollY = $(window).scrollTop();
					posY = $(this).position().top;
					height = $(this).height();
					if (message.data.y+scrollY >= posY && message.data.y+scrollY <= posY+height){
						$(this).addClass('candidate'); 
					} else {
						$(this).removeClass('candidate');
					}
				});		
			}	
		}

	} else if (message.action == 'cancelDrop'){
		$('.candidate').removeClass('candidate');

	} else if (message.action == 'insertTable'){
		var newTable = $('<table><tbody><tr><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td></tr></tbody></table>');
		newTable.tableSettings();
		$('body').append(newTable);

	} else if (message.action == 'init'){
		Init();
	}
}

function Init(){
	$('p.img').imageSettings();
	$('p.video').videoSettings();
	$('table').tableSettings();
	$('body>br').remove();
	if ($('body>p.fake').length == 0){
		$('body').prepend('<p class="fake">&nbsp;</p>');
	}
	document.execCommand("enableInlineTableEditing", null, false);
	document.execCommand("enableObjectResizing", null, false);
}
