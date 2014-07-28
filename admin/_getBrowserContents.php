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

$contentTypes = DB::get(Query::Select('contenttypes')->fields('typeID','label','icon','scheme','hidden','listTemplate')->asc('rank'));

$outputContentTypes = [];
$contentInstances = [];


$contentIndex = 0;
foreach ($contentTypes as $contentType){
	$requiredFields = ['ID'];
	$tentativeFields = [
		'typeID',
		'label',
		'icon'
	];

	$scheme = json_decode($contentType->scheme);
	$schemeFields = [];
	/*foreach ($scheme as $schemePart){
		$type = $schemePart->type;
		$name = $schemePart->name;
		$tentativeFields[] = $name;
		$schemeFields[$name] = $schemePart;
	}*/

	$template = '<img class="icon" src="/admin/Gfx/PageTypes/16/{icon}.png"/><span>{label} - {ID} {contentID}</span>';


	if ($contentType->listTemplate!= ''){
		$template = $contentType->listTemplate;
	} else {
		$contentTypes[$contentIndex]->listTemplate = $template;
	}


	_g('TEMPLATE FIELDS');
	$templateHelper = new TemplateHelper();
	$templateHelper->setPurpose(TemplateHelper::CONTENT)->setScheme($scheme)->setTemplate($template)->calculateFields();
	_d($templateHelper);
	_u();

	$currentInstances = DB::get(Query::Select('contents_'.$contentType->typeID)->fields($templateHelper->getFields()),DB::ASSOC);

	if ($currentInstances){
		for ($i = 0; $i<count($currentInstances); $i++){
			$currentInstances[$i] = $templateHelper->extractInformation($currentInstances[$i]);
		}
	}
	$contentInstances[$contentType->typeID] = ['none'];
	$contentInstances[$contentType->typeID] = $currentInstances;

	$outputContentTypes[$contentType->typeID] = $contentTypes[$contentIndex];
	$contentIndex++;
}


$result['contentTypes'] = $outputContentTypes;
$result['contentInstances']= $contentInstances;

echo json_encode($result);
