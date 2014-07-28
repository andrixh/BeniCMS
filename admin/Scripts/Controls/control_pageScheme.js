var SchemeData = '';

(function($) { //page scheme
	$.fn.pageScheme = function(method) {
		if (method != 'calculate'){	
		  	var fieldTypes = {
				'boolean':'Checkbox',
				'string':'Text Field',
				'number':'Numeric Value',
				'date':'Date Value',
				'select':'Select List',
                'page':'Page',
				'mlstring':'Multilingual String',
				'mlhtml':'Multilingual HTML',
				'gallery':'Gallery',
				'mlgallery':'Multilingual Gallery',
				'files':'Files',
				'mlfiles':'Multilingual Files',
				'content':'Content List',
                'contentselect':'Content Select'//,
				//'component':'Component List'
			};
		  	
		  	$(this).each(function() {
				var self = this;
				
				self.selectProviders = $(self).attr('data-providers');
				
				var fieldTemplate = '<li><a class="close" href="#">x</a><a class="dragHandle" href="#"></a>';
				fieldTemplate+= '<fieldset class="first">';
				fieldTemplate+= '<label><span>Field Name</span><input class="fieldName" type="text"/></label>';
                fieldTemplate+= '<label><span>Type</span><select class="fieldType"></select></label>';
				fieldTemplate+= '<label><span>Label</span><input class="fieldLabel" type="text"/></label>';
				fieldTemplate+= '<label><span>Description</span><input class="fieldDescription" type="text"/></label>';
				fieldTemplate+= '<a class="tool visibility on" href="#" title="Visible in Tables"></a>';
				fieldTemplate+= '<a class="tool lock_admin" href="#" title="Lock to Administators"></a>';
				fieldTemplate+= '<a class="tool lock_user" href="#" title="Lock to Users"></a>';
				fieldTemplate+= '</fieldset></li>'; 
				
				var field = $(fieldTemplate);
				$(field).find('select').append('<option value="">(select)</option>');
				for (var fld in fieldTypes){
					$(field).find('select').append('<option value="'+fld+'">'+fieldTypes[fld]+'</option>');
				}



				var list = $('<ul class="fieldList"/></ul>');
				list.sortable({
					delay:200//,
					//handle:'a.dragHandle'
				});
				
				var addLink = $('<a class="addField" href="#">Add Field</a>');
				
				var control = $('<div></div>');
				control.append(list);
				control.append(addLink);

				_d($(self).val());
				if ($(self).val()!=''){
					var values = $.parseJSON($(self).val());
					for (value in values){
						addField(values[value]);
					}
				}
				
	
				addLink.click(function() {
					addField('');
					return false;
				});		
				
				function addField(data){
					var newField = $(field).clone();
					if (data!=''){
						$(newField).attr('data-value',JSON.stringify(data));
					}
					$(newField).attr('data-providers',self.selectProviders);
					control.find('ul').append(newField);
					newField.schemeField();
				}
				
				$(self).after(control);
				$(self).hide(); 
		    });
		} else {
			var result =[];
			var self = this;
			self.parent().find('li').each(function() {
				result.push($.parseJSON($(this).attr('data-value')));
			});
			self.val(JSON.stringify(result));
		}	
	}
})(jQuery);



(function($) { // scheme field
	$.fn.schemeField = function() {
	  	$(this).each(function() {
			var self = this;
			var fieldType = $(self).find('select.fieldType');
			var fieldName = $(self).find('.fieldName');
            var fieldLabel = $(self).find('.fieldLabel');
			var tool_visible = $(self).find('a.tool.visibility');
			var tool_lock_admin = $(self).find('a.tool.lock_admin');
			var tool_lock_user = $(self).find('a.tool.lock_user');


			var i = 0;
			var providers = $.parseJSON($(self).attr('data-providers'));
		  	_d(providers);
		  	var providerOptions = '<option value="">(select one)</option>';
		  	for (var provider in providers){
		  		providerOptions += '<option value="'+provider+'">'+providers[provider]+'</option>';
		  	}



			var contentOptions = '';
			for (i in SchemeData.contents){
				_d(SchemeData.contents[i].typeID);
				_d(SchemeData.contents[i].label);
				contentOptions += '<option value="'+SchemeData.contents[i].typeID+'">'+SchemeData.contents[i].label+'</option>';
			}

			var componentOptions = '';
			for (i in SchemeData.components){
				componentOptions += '<option value="'+SchemeData.components[i].typeID+'">'+SchemeData.components[i].label+'</option>';
			}

		  	var fieldOptForms = {
		  		'boolean':'<fieldset class="options"><label><span>Checked Initially</span><input type="checkbox" class="check_default"/></label></fieldset>',
		  		'string':'<fieldset class="options"><label><span>Number of Rows</span><input type="text" class="string_numRows"/></label></fieldset>',
		  		'number':'<fieldset class="options"><label><span>Integer Only</span><input type="checkbox" class="number_integer"/></label><label><span>Min (blank for none)</span><input type="text" class="number_min"/></label><label><span>Max (blank for none)</span><input type="text" class="number_max"/></label></fieldset>',
		  		'date':'<fieldset class="options"></fieldset>',
                'page':'<fieldset class="options"></fieldset>',
		  		'select':'<fieldset class="options"><label><span>From Provider</span><select class="select_provider">'+providerOptions+'</select></label><label><span>Allow Empty</span><input type="checkbox" class="select_allowEmpty"/></label></fieldset>',
		  		'mlstring':'<fieldset class="options"><label><span>Number of Rows</span><input type="text" class="mlstring_numRows"/></label></fieldset>',
		  		'mlhtml':'<fieldset class="options"><label><span>Accept Images</span><input type="checkbox" class="mlhtml_acceptImages"/></label><label><span>Accept Files</span><input type="checkbox" class="mlhtml_acceptFiles"/></label><label><span>Accept Videos</span><input type="checkbox" class="mlhtml_acceptVideos"/></label><label><span>Accept Components</span><input type="checkbox" class="mlhtml_acceptComponents"/></label></fieldset>',
				'gallery':'<fieldset class="options"><label><span>Accept Images</span><input type="checkbox" class="gallery_acceptImages"/></label><label><span>Accept Videos</span><input type="checkbox" class="gallery_acceptVideos"/></label><label><span>Single Entry</span><input type="checkbox" class="gallery_single"/></label></fieldset>',
				'mlgallery':'<fieldset class="options"><label><span>Accept Images</span><input type="checkbox" class="gallery_acceptImages"/></label><label><span>Accept Videos</span><input type="checkbox" class="gallery_acceptVideos"/></label><label><span>Single Entry</span><input type="checkbox" class="gallery_single"/></label></fieldset>',
				'files':'<fieldset class="options"><label><span>Single Entry</span><input type="checkbox" class="files_single"/></label></fieldset>',
				'mlfiles':'<fieldset class="options"><label><span>Single Entry</span><input type="checkbox" class="files_single"/></label></fieldset>',
				'content':'<fieldset class="options"><label><span>From Provider</span><select class="contentTypes" multiple="multiple" size="3">'+contentOptions+'</select></label><label><span>Single Entry</span><input type="checkbox" class="content_single"/></label></fieldset>',
                'contentselect':'<fieldset class="options"><label><span>From Provider</span><select class="contentTypes" size="3">'+contentOptions+'</select></label></fieldset>',
				'component':'<fieldset class="options"><label><span>From Provider</span><select class="componentTypes" multiple="multiple" size="3">'+componentOptions+'</select></label></fieldset>'
			};
			

			tool_visible.click(function(e){
				e.preventDefault();
				$(this).toggleClass('on');
				updateValue();
			});

		    tool_lock_admin.click(function(e){
			  e.preventDefault();
			  $(this).toggleClass('on');
			  if ($(this).hasClass('on')){
				  tool_lock_user.addClass('on');
			  }
			  updateValue();
			});

		    tool_lock_user.click(function(e){
			  e.preventDefault();
			  $(this).toggleClass('on');
			  if (!$(this).hasClass('on')){
					tool_lock_admin.removeClass('on');
			  }
			  updateValue();
			});

			fieldType.change(function() {
				$(self).find('fieldset').not('.first').remove();
				var fType = $(this).val();	
				if (fType != ''){
					$(self).append(fieldOptForms[fType]);
				}		
				return false;
			});
			
			$(self).find('select.select_provider').on('change',function(){
				_d('change');
				var customOptionTemplate = '<fieldset><h2>Custom Options</h2><label><input class="select_custom" json="true" type="hidden"></label></fieldset>';
				
				if ($(this).val()=='-'){
					$(this).parents('li').append($(customOptionTemplate));
					//$(this).parents('li').find('input.select_custom').schemeFieldCustomList();
				} else {
					$(this).parents('li').find('fieldset').eq(2).remove();
				}
			});

			$(self).on('click',function(e){
				$(this).siblings().not(this).removeClass('selected');
				$(this).addClass('selected');
			});

			$(self).find('a.close').click(function(e) {
				$(self).remove();
				e.preventDefault();
			});

            $(self).on('keyup change click','input,select',function(){
                updateValue();
            });
            
            fieldName.on('keyup change',function(e){
                console.log(e);
                fieldLabel.val(($(this).val().charAt(0).toUpperCase()+$(this).val().substr(1)).split('_').join(' '));
                if (e.type == 'change'){
                    $(this).val($(this).val().split(' ').join('_'));
                }
                updateValue();
			});

			function updateValue(){
				var result = {};
				result.type=$(self).find('.fieldType').val();
				result.name=$(self).find('.fieldName').val();
				result.label=$(self).find('.fieldLabel').val();
				result.description=$(self).find('.fieldDescription').val();
				result.visible = $(self).find('a.tool.visibility').hasClass('on');
				result.lock_admin = $(self).find('a.tool.lock_admin').hasClass('on');
				result.lock_user = $(self).find('a.tool.lock_user').hasClass('on');
				result.options={};
				
				
				$(self).find('fieldset').not('.first').find('input[type=text],input[type=hidden],input[type=textarea],select').not('.no_include').each(function(){
					if ($(this).attr('json')=='true'){
						result.options[$(this).attr('class')]=$.parseJSON($(this).val());
					} else {
						result.options[$(this).attr('class')]=$(this).val();
					}
				});
				$(self).find('fieldset').not('.first').find('input[type=checkbox]').not('.no_include').each(function(){
					var val = false;
					if ($(this).is(':checked')){
						val = true;	
					}
					result.options[$(this).attr('class')]=val;
				});
				$(self).attr('data-Value',JSON.stringify(result));
				_d(result);
			};
			
			///loader
			
			if ($(self).attr('data-Value')!=null){
				var data = $.parseJSON($(self).attr('data-Value'));
				_d(data);
				$(self).find('.fieldName').val(data.name);
				$(self).find('.fieldLabel').val(data.label);
				$(self).find('.fieldDescription').val(data.description);
				$(self).find('.fieldType').val(data.type);
				$(self).find('.fieldType').trigger('change');
				if (data.visible) {
					$(self).find('a.tool.visibility').addClass('on');
				} else {
					$(self).find('a.tool.visibility').removeClass('on');
				}
				if (data.lock_admin) {
					$(self).find('a.tool.lock_admin').addClass('on');
				} else {
					$(self).find('a.tool.lock_admin').removeClass('on');
				}
				if (data.lock_user) {
					$(self).find('a.tool.lock_user').addClass('on');
				} else {
					$(self).find('a.tool.lock_user').removeClass('on');
				}

				for (option in data.options){
					var val;
					_d(data.options[option]);
					_d('THE TYPEOF IS:'+ typeof(data.options[option]));
					//if (typeof(data.options[option]) == 'string') {
						val = data.options[option];
					//} else {
					//	val = JSON.parse(data.options[option]);
					//}
					var ctrl = $(self).find('.'+option);
					
					if ($(ctrl).is('select')){
						_d('VAL IS:');
						_d(val);
						_d('THE TYPEOF VAL IS:'+ typeof(val));
						$(ctrl).val(val);

						//$(ctrl).trigger('change');
					} else if ($(ctrl).attr('type')=='text') {
						$(ctrl).val(val);
					} else if ($(ctrl).attr('type')=='checkbox'){
						if (val==true){
							ctrl.attr("checked","true");
						}
					} else if ($(ctrl).attr('type')=='hidden'){
						$(ctrl).val(val);
						$(ctrl).trigger('change');
					}
					
				}
			}
			
			
			
	    });
	}
})(jQuery);



$(document).ready(function() {
	if (SchemeData == ''){
		SchemeData = {}
		$.get('_schemeTypes.php',function(data){
			_d(data,'SCHEME TYPES');
			SchemeData = JSON.parse(data);
			var ps = $('input.pageScheme').pageScheme();

			$('form').submit(function(){
				$('input.pageScheme').pageScheme('calculate');
				//return false;
			});
		});
	}




	
});  //----- end of document.ready------