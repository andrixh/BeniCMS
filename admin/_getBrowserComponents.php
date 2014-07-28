<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/errors.inc.php');
require_once('Routines/mlstrings.class.php');
require_once('Routines/templateHelper.class.php');

$result = [];

$componentTypes = DB::get(Query::Select('componenttypes')->fields('typeID','label','icon','scheme','hidden','listTemplate')->asc('rank'));

$outputComponentTypes = [];
$componentInstances = [];


$componentIndex = 0;
foreach ($componentTypes as $componentType){
	$requiredFields = ['componentID'];
	$tentativeFields = [
		'typeID',
		'label',
		'componentID',
		'icon'
	];

	$scheme = json_decode($componentType->scheme);

	$template = '<img class="icon" src="/admin/Gfx/PageTypes/16/{icon}.png"/><span>{label} - {componentID}</span>';

	$schemeFields = [];
	foreach ($scheme as $schemePart){
		$type = $schemePart->type;
		$name = $schemePart->name;
		$tentativeFields[] = $name;
		$schemeFields[$name] = $schemePart;
	}

	if ($componentType->listTemplate!= ''){
		$template = $componentType->listTemplate;
	}
	$componentTypes[$componentIndex]->listTemplate = $template;

	foreach ($tentativeFields as $tentativeField){
		if (strstr($template,'{'.$tentativeField.'}') !== false){
			if (!in_array($tentativeField, ['typeID','label','icon'])) {
				$requiredFields[] = $tentativeField;
			}
		}
	}

    $currentInstances = DB::get(Query::Select('components_'.$componentType->typeID)->fields($requiredFields));

	$componentInstances[$componentType->typeID] = ['none'];
	$componentInstances[$componentType->typeID] = $currentInstances;

	$outputComponentTypes[$componentType->typeID] = $componentTypes[$componentIndex];
	$componentIndex++;
}


$result['componentTypes'] = $outputComponentTypes;
$result['componentInstances']= $componentInstances;

echo json_encode($result);
