<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Includes/db.tables.inc.php');
require_once('Includes/errors.inc.php');
require_once('Includes/counter.inc.php');
require_once('Lib/phpQuery/phpQuery.php');
require_once('Includes/webpage.inc.php');
require_once('Includes/menu.inc.php');
require_once('Includes/form.inc.php');
require_once('Routines/validators.php');
require_once('Routines/filesystem.php');
require_once('Routines/mlstrings.class.php');
require_once('Routines/content.php');
require_once('Routines/selectProviders.php');

if (!isset($_GET['type']) && !isset($_POST['type'])) {
    setError('Cannot find type of content!', 2);
    redirect($_SERVER['HTTP_REFERER']);
} else {
    $type = $_GET['type'];
    if (isset($_POST['type'])) {
        $type = $_POST['type'];
    }
}
$tableName = 'contents_' . $type;
$scheme = json_decode(DB::val(Query::Select('contenttypes')->fields('scheme')->eq('typeID', $type)->limit(1)));
$tableScheme = contentDef($scheme);
require_table($tableName);
//require_table($tableName,contentDef($scheme));

$afterActions = array(
    array(
        'label' => 'stay here',
        'url' => '',
        'default' => true,
    ), array(
        'label' => 'go back',
        'url' => 'contentsView.php?type=' . $type,
    )
);
$typeData = DB::row(Query::Select('contenttypes')->fields('scheme', 'label', 'formTemplate')->eq('typeID', $type)->limit(1));
_d($typeData);
$typeScheme = json_decode($typeData->scheme);
$typeFormTemplate = $typeData->formTemplate;

$perform = false;
// Init default form variables.
if (isset($_GET['id'])) {
    $ID = $_GET['id'];
    $currentRecord = DB::row(Query::Select($tableName)->eq('id', $ID)->limit(1), DB::ASSOC);

    $content = array();
    foreach ($typeScheme as $typeField) {
        if (array_search($typeField->type, getMlTypes())) {
            $content[$typeField->name] = mlString::Create($currentRecord[$typeField->name])->postName('content_' . $typeField->name);
        } else {
            $content[$typeField->name] = $currentRecord[$typeField->name];
        }
    }
    $contentID = $currentRecord['contentID'];

    _d($content, 'estivated content');
    countContentResources($content, $typeScheme, -1);
    _d($_counter);
}

//perform goes here
if (isset($_POST['perform']) && ($_POST['perform'] == 1)) { // if form is submitted
    $ID = $_POST['id'];
    $contentID = strtolower($_POST['contentID']);
    $content = contentFromPost($content, $typeScheme);


    $valid = contentValidate($content, $typeScheme);


    $contentID = str_replace(' ','_',$contentID);
    if (strlen($contentID) < 3 || strlen($contentID) >255 || !allowedChars($contentID,conf('ALPHANUM_LOWER').'_') || !allowedChars(substr($contentID,0,1),conf('ALPHA_LOWER'))) {
        $valid = false;
        addFormError('contentID','Must be 3-50 letters long and can only start with a letter. No spaces.');
    }

    $exists = DB::row(Query::Select($tableName)->fields('id')->eq('contentID', $contentID)->ieq('ID',$ID));
    if ($exists) {
        $valid = false;
        addFormError('contentID','This Page ID already exists');
    }

    if ($valid) {
        countContentResources($content, $typeScheme, 1);

        $queryFields = array(
            'contentID' => $contentID
        );

        foreach ($typeScheme as $typeField) {
            $queryFields[$typeField->name] = $content[$typeField->name];
        }


        $result = DB::query(Query::Update($tableName)->pairs($queryFields)->eq('id',$ID));
        if ($result === false) {
            setError('Database Error! Please contact your webmaster!', 2);
        } else {
            $mlstrings = pageContentExtractMlStrings($content, $typeScheme);
            foreach ($mlstrings as $mlstring) {
                $mlstring->usedTable($tableName)->usedID($ID)->save();
            }
            commitCounts();
            setError(ucfirst($typeData->label) . ' content "' . $contentID . '" modified', 0);
            if (isset($_POST['afterAction']) && $_POST['afterAction'] != '') {
                redirect(str_replace('%id%', $ID, $_POST['afterAction']));
            }
        }
    } else {
        setError('Your form contains errors, please review and post again!', 1);
    }
}


$form = formConstruct('Modify ' . ucfirst($typeData->label) . ' Content', $afterActions);
_gc('Hardcoded Controls');


$fieldset1 = '<fieldset class="col1 first">';
$fieldset1 .= field(label('Content ID','permalink - unique'),control_textInput('contentID', $contentID),'contentID');
$fieldset1 .= control_hidden('type', $type);
$fieldset1 .= '</fieldset>';

$form->find('fieldset.submit')->before($fieldset1);
$form->find('fieldset.submit')->append(control_hidden('id', $ID));
_u();
$fieldset2 = contentFields($content, $typeScheme, $typeFormTemplate);

$form->find('fieldset.submit')->before($fieldset2);

$webPage = webPageConstruct('Modify ' . ucfirst($typeData->label) . ' Content');
$webPage->find('h1')->before(constructMenu('contentsView.php?type=' . $type));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo outputWebPage($webPage);

