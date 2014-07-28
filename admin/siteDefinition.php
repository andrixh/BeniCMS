<?php 
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once ('_loginLock.php');
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
require_once('Lib/floicon/floicon.php');

require_table('site');


$faviconPath = $_SERVER['DOCUMENT_ROOT'].'/favicon.ico';

$perform=false;
// Init default form variables.
$name = mlString::Create();
$hasFavIcon = file_exists($_SERVER['DOCUMENT_ROOT'].'/favicon.ico');
$faviconIndex = 0;
$metaDescription = mlString::Create();
$metaKeywords = mlString::Create();
$trackingCode = '';
$faviconClear = false;

$row = DB::row(Query::Select('site')->limit(1));

if ($row){
    $name = mlString::Create($row->name);
    $hasFavIcon = $row->hasFavicon && file_exists($_SERVER['DOCUMENT_ROOT'].'/favicon.ico');
    $faviconIndex = $row->faviconIndex;
    $metaDescription = mlString::Create($row->metaDescription);
    $metaKeywords = mlString::Create($row->metaKeywords);
    $trackingCode = $row->trackingCode;
}


//perform goes here
if (isset($_POST['perform']) && ($_POST['perform']==1)){ // if form is submitted

    $valid = true;



    $name->fromPost('name');
    $metaDescription->fromPost('metaDescription');
    $metaKeywords->fromPost('metaKeywords');
    $trackingCode=$_POST['trackingCode'];
    $faviconClear = isset($_POST['favicon_clear']);

    if ($_FILES['favicon']['error'] > 0) {
        $valid = false;
        addFormError('favicon','Upload Error');
    } else {
        if ($_FILES['favicon']['type'] == 'image/x-icon' && pathinfo($_FILES['favicon']['name'],PATHINFO_EXTENSION) == 'ico'){
            move_uploaded_file($_FILES['favicon']['tmp_name'],$faviconPath);
            $hasFavIcon = true;
            $faviconIndex++;
        } else {
            $valid = false;
            addFormError('favicon','Please provide an .ico file');
        }
    }

    if ($faviconClear){
        if (file_exists($faviconPath)){
            unlink($faviconPath);
            $hasFavIcon = false;
            $faviconIndex++;
        }
    }


	if ($valid){

		$queryFields = array(
			'name'=>$name->strID,
            'hasFavicon'=>$hasFavIcon,
            'faviconIndex'=>$faviconIndex,
			'metaDescription'=>$metaDescription->strID,
            'metaKeywords'=>$metaKeywords->strID,
            'trackingCode'=>$trackingCode
		);

        DB::query('TRUNCATE TABLE `site`');
        $result = DB::query(Query::Insert('site')->pairs($queryFields));

		if ($result===false) {
			setError('Database Error! Please contact your webmaster!',2);
		} else {
			$name->usedTable('site')->usedID('0')->save();
			$metaDescription->usedTable('site')->usedID('0')->save();
			$metaKeywords->usedTable('site')->usedID('0')->save();
            setError('Site Definition saved',0);
		}
	} else {
		setError('Your form contains errors, please review and post again!',1);
	}
}


$form = formConstruct('Save');

$fieldset1='<fieldset class="col1 first">';
$fieldset1.='<h2>Basic Information</h2>';
$fieldset1.=field(label('Site Name'),control_mlTextInput('name', $name->getValues()),'name');
if ($hasFavIcon){
    $fi = new floIcon();
    $fi->readICO($faviconPath);
    $i = $fi->getImage(0);
    _d($i,'image resource');

    ob_start();
    imagepng($i);
    $image_data = ob_get_contents ();
    ob_end_clean();
    //_d(base64_encode(imagepng($i)),'base64');
    $fieldset1.='<label>Current Favicon: <img src="data:image/png;base64,'.base64_encode($image_data).'"/></label>';
}

$fieldset1.=field(label('Upload New favicon','overwrites existing one'),'<input type="file" name="favicon"/>','favicon');
$fieldset1.=field(label_checkbox('Clear existing favicon'),control_checkbox('favicon_clear', $faviconClear),'clearfavicon');
$fieldset1.=field(label('Meta Description','default'),control_mlTextArea('metaDescription', $metaDescription->getValues()),'metaDescription');
$fieldset1.=field(label('Meta Keywords','default'),control_mlTextArea('metaKeywords', $metaKeywords->getValues()),'metaDescription');
$fieldset1.=field(label('Tracking code','normally from Google Analytics'),control_textArea('trackingCode', $trackingCode),'trackingCode');
$fieldset1.='</fieldset>';


$form->find('fieldset.submit')->before($fieldset1);

$webPage = webPageConstruct('Site Definition');
$webPage->find('h1')->before(constructMenu('siteDefinition.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo  outputWebPage($webPage);




