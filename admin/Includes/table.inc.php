<?php
function constructTable ($tableName, $fields, $fieldLabels, $pageSize, $pageNum, $sortColumn, $sortDir, $searchString, $searchField, $specialFields, $specialData){
	//---------if all fields requested, get field names--------
	$result = '';
	require_script('Scripts/tables.js');


	if ($fields == 'all'){
		$extFields = '';
		$table = DB::row(Query::Select($tableName)->limit(1),DB::ASSOC);
		$colInfo=array_keys($table);
		
		if (count($colInfo)>0){
			for ($i=0;$i<count($colInfo);$i++){
				$fieldName=$colInfo[$i];
				if ($extFields==''){
					$extFields = $fieldName;			
				} else {
					$extFields = $extFields.','.$fieldName;
				}
			}
		}
		$fields=$extFields;
	}
	
	
	$result = '<form id="searchBox" class="searchForm">
				<fieldset>
	  				<label><span>Search:</span><input id="searchText"  type="text" value="" /></label>
	  				<label><select id="searchField">';
	$flds = explode(',',$fields);
	$fldLabels = explode(',', $fieldLabels);
	if ($specialFields==''){
		for ($i = 0; $i<count($flds); $i++){
			$result.='<option value="'.trim($flds[$i]).'" ';
			if ($searchField == $flds[$i]) {
				$result.='selected="selected"';
			}
			$result.='>'.trim($flds[$i]).'</option>';
		}
  	} else {
		$specialFlds = explode(',',$specialFields);
		for ($i = 0; $i<count($flds); $i++){
			$fieldAllowed = true;
			for ($j = 0; $j<count($specialFlds); $j++){
				if ($specialFlds[$j]==$flds[$i]) {$fieldAllowed == true;}
			} 
			$result.='<option value="'.trim($flds[$i]).'" ';
			if ($searchField == $flds[$i]) {
				$result.='Selected="Selected"';
			}
			$result.='>'.trim($fldLabels[$i]).'</option>';	
		}
	}
    $result.='</select></label></fieldset></form>';

	$result.= '<script>';
	$result.= 'hastable = true;';
	$result.= 'tableName = "'.$tableName.'";';
	$result.= 'fields = "'.$fields.'";';
	$result.= 'fieldLabels = "'.$fieldLabels.'";';
	$result.= 'pageSize = "'.$pageSize.'";';
	$result.= 'pageNum = "'.$pageNum.'";';
	$result.= 'sortColumn = "'.$sortColumn.'";';
	$result.= 'sortDir = "'.$sortDir.'";';
	$result.= 'searchString = "'.$searchString.'";';
	$result.= 'searchField = "'.$searchField.'";';
	$result.= 'specialFields = "'.$specialFields.'";';
	$result.= 'specialData = "'.$specialData.'";';
	$result.= '</script>';
	
	$result.= '<div id="table"></div>';
	return $result;
}


function constructActions($actions){
	$result = '<div class="actions"><ul>';
	foreach ($actions as $action){
		$pItem = phpQuery::newDocument('<li><a></a></li>');
		if(is_array($action)){
			$pItem->find('a')->html($action['label'])->attr('href',$action['link']);
			if(array_key_exists('rel', $action)){
				$pItem->find('a')->attr('rel',$action['rel']);
			}
			if(array_key_exists('rev', $action)){
				$pItem->find('a')->attr('rev',$action['rev']);
			}
			if(array_key_exists('target', $action)){
				$pItem->find('a')->attr('target',$action['target']);
			}
		} else if (is_string($action)){
			if ($action=='separator'){
				$pItem->find('li')->addClass('separator')->find('a')->remove();
			}
		}
		$result.= $pItem->html();
	}
    $result.= '</ul></div>';
	return $result;
}