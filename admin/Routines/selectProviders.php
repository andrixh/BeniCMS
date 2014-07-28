<?php
function registerProvider($name,$label,$table,$valueField,$labelField,$editorUrl=''){
	_gc(__FUNCTION__);
	global $db;
	$optionFields = array();
    $foundOptions = DB::get(Query::Select($table)->fields(array($valueField,$labelField)),DB::ASSOC);
	
	foreach ($foundOptions as $option) {
		$optionFields[$option[$valueField]] = $option[$labelField];
	}
	
	
	$providerFields = array(
		'providerID'=>$name,
		'label'=>$label,
		'editorUrl'=>$editorUrl,
		'useCount'=>1,
		'options'=>json_encode($optionFields)
	);
	_d($providerFields,'$providerFields');
	$id = DB::val(Query::Select('selectproviders')->fields('ID')->eq('providerID',$name));
	_d($id);
	$query = '';
	if (!$id) {
        $query = Query::Insert('selectproviders')->pairs($providerFields);
	} else {
        $query = Query::Update('selectproviders')->pairs($providerFields)->eq('ID',$id);
	}
	_d($query);
	DB::query($query);
	_u();
}

function providerSetCount($providerID,$inc){
    $count = DB::val(Query::Select('selectproviders')->fields('useCount')->eq('providerID',$providerID));
	$count += $inc;
	DB::query(Query::Update('selectproviders')->pairs(array('useCount',$count))->eq('providerID',$providerID));
}

function enumProviders(){
    $providers = DB::get(Query::Select('selectproviders')->fields('providerID','label'));
	$result = array();
	foreach($providers as $provider){
		$result[$provider->providerID]=$provider->label;
	}
	return $result;
}

function countProvidersInPageScheme($pageScheme,$inc){
	_gc(__FUNCTION__);
	$fields = json_decode($pageScheme);
	foreach ($fields as $field) {
		if ($field->type == 'select' && $field->options->select_provider!='' && $field->options->select_provider!='-'){
			setCount('provider',$field->options->select_provider,$inc);
		}
	}
	_u();
}

function provide($provider){
	_gc(__FUNCTION__);
	$options = json_decode(DB::val(Query::Select('selectproviders')->fields('options')->eq('providerID', $provider)->limit(1)));
	_d($options);
	_u();
	return $options;
}
