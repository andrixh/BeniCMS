selectedItem = ''; //variable to hold selected ID of table
var tableImageViewTimeout;
$(document).ready(function() {
	if(typeof(hastable) !== 'undefined'){												// Functions if a table is present
		if(hastable){
			loadTable();
			
			$('#searchBox').keyup(function(){												//--SearchBox
					searchString =$('#searchText').val();
					searchField = $('#searchField').val();
				if (searchString == ''){
						//alert('empty!');
						searchField ='';
					}
				if ((searchString.length>2)||searchString == ''){
					loadTable();
				}
			});
			
			$('#searchField').change(function(){									//--Search Field
				searchField = $('#searchField').val();			
				if (searchString != ''){
						searchString =$('#searchText').val();
						loadTable();
					} else {
						searchField ='';
					}
			});
		
			$('div.actions a').click(function(){									//--actions
				if ((selectedItem == '') && ($(this).attr('target')!='noSelect')){
					return false;
				}
				if ($(this).attr('href')!='#'){		
						var okToGo = true;
						_d($(this).attr('rel'));
						if ($(this).attr('rel')!==undefined){ okToGo = confirm($(this).attr('rel'));}
						//returnUrl = $(this).attr('rev')
						if (okToGo) {
							if ($(this).attr('href').indexOf('?') == -1){
								window.location.href = $(this).attr('href')+'?id='+selectedItem;
							} else {
								window.location.href = $(this).attr('href')+'&id='+selectedItem;
							}
						}
				}
				return false;
			});
	
	
			
	
		}//----end of table functions----\	
	}//----end of table functions------/
	$('a.imageView').live('click',function(e){
		e.preventDefault();
	});	
		
	$('a.imageView').live('mouseenter',function(){
		
		imageView = $(this);
		tableImageViewTimeout = setTimeout("previewTableImage(imageView)",300);
	});
												
	$('a.imageView').live('mouseleave',function(){
		clearTimeout(tableImageViewTimeout);	
		$('div.imageView').remove();	
	});

	/*$('div.imageView').live('mouseenter',function(){
		clearTimeout(tableImageViewTimeout);	
		$('div.imageView').remove();	
	});*/
	
});  //----- end of document.ready------

function previewTableImage(imageView){
    $(imageView).append('<div class="imageView"></div>');
	$('div.imageView').hide();
	var chunks = $(imageView).find('img').attr('src').split('.');
	var ext = chunks[chunks.length-1];
	var pname = $('div.imageView').parent().attr('rel');
	$('div.imageView').html('<img src="/Images/Resized/'+pname+'_200_200_B_30_t.'+ext+'"/>');
	$('div.imageView').show();
}




function loadTable(){
    selectedItem = '';
	
	url = '_getTable.php?t='+tableName+'&f='+fields+'&fl='+encodeURIComponent(fieldLabels)+'&sc='+sortColumn+'&sd='+sortDir+'&s='+searchString+'&sf='+searchField+'&ps='+pageSize+'&pn='+pageNum+'&spf='+specialFields+'&spd='+specialData;
		$('div#table').html('');
		$('div#Content').addClass('loading');
		$('div#table').load(url,function(){
			$('div#Content').removeClass('loading');
			setupTables();
		});	
}

function setupTables(){
	$('td').click(function(){																	//--Select row
		if(!$(this).parent().hasClass('tableHead')){
			$(this).parent().parent().find('tr').removeClass('selected');
			$(this).parent().addClass('selected');
			selectedItem = $(this).parent().find('td:first-child').html();
		}
	});
		
	
		
	$('div#table th a').click(function(){									//--Sort Columns
		sortColumn = $(this).attr('rel');
		if ($(this).hasClass('sortASC')){
			sortDir='DESC';
		} else {
			sortDir='ASC';
		}
		loadTable();
		return false;
	});
	
	$('div.tableNav a').click(function(){									//--Table Navigation
		
		if ($(this).attr('href')!='#'){
			
			pageNum = $(this).attr('href');
			loadTable();
		}
		return false;
	});
	
	$('div.pageCount a').click(function(){								//--Page Size
		if ($(this).attr('href')!='#'){
			pageSize = $(this).attr('href');
			pageNum = 1;
			loadTable();
		}
		return false;
	});

    $('td.rank a').click(function(e){
        e.preventDefault();
        var id = $(this).parents('tr').find('td').eq(0).text();
        $.get('_rerank.php?t='+tableName+'&dir='+$(this).index()+'&id='+id,function(){
            loadTable();
        });

    });

    $('td.boolean.toggle, td.boolean.exclusive').click(function(e){
        var id = $(this).parents('tr').find('td').eq(0).text();
        var exclusive = '&x=0';
        if ($(this).hasClass('exclusive')) {
            exclusive = '&x=1';
        }
        $.get('_boolChange.php?t=' + tableName + '&id=' + id+ '&f=' + $(this).attr('field')+exclusive, function () {
            loadTable();
        });
    });
} //---end setuptables