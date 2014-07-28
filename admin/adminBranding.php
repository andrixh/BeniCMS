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
require_once('Lib/floicon/floicon.php');

require_table('site');


$faviconPath = $_SERVER['DOCUMENT_ROOT'] . '/favicon.ico';

$perform = false;
// Init default form variables.

$logoLargeFileName = $_SERVER['DOCUMENT_ROOT'] . '/branding/LogoLarge.png';
$logoSmallFileName = $_SERVER['DOCUMENT_ROOT'] . '/branding/Logo.png';

$brandingLess = $_SERVER['DOCUMENT_ROOT'] . conf('ADMIN_BASE_PATH') . '/Css/branding.less';
$cssFolder = $_SERVER['DOCUMENT_ROOT'] . conf('ADMIN_BASE_PATH') . '/Css/';


$brandHue = 80;
$brandSaturation = 1;

if (file_exists($brandingLess)) {
    $currentContents = file_get_contents($brandingLess);
    $currentContentsParts = explode(';',$currentContents);
    foreach ($currentContentsParts as $line) {
        if (strpos(trim($line),'@HuePrimary')!== false) {
            $brandHue = trim(str_replace(['@HuePrimary',':'],'',trim($line)));
        }
        if (strpos(trim($line),'@SaturationMultiplier')!== false) {
            $brandSaturation = trim(str_replace(['@SaturationMultiplier',':'],'',trim($line)));
        }
    }
}

//perform goes here
if (isset($_POST['perform']) && ($_POST['perform'] == 1)) { // if form is submitted
    // large Logo
    if ($_FILES['logoLarge']['error'] == 0) {
        if ($_FILES['logoLarge']['type'] == 'image/png') {
            list($width, $height) = getimagesize($_FILES['logoLarge']['tmp_name']);
            if ($width < 300 && $height < 150) {
                move_uploaded_file($_FILES['logoLarge']['tmp_name'], $logoLargeFileName);
            } else {
                addFormError('logoLarge', 'Image too Large. Must be at most 300x150px');
            }
        } else {
            addFormError('logoLarge', 'Not a PNG Image');
        }
    }

    // small Logo
    if ($_FILES['logoSmall']['error'] == 0) {
        if ($_FILES['logoSmall']['type'] == 'image/png') {
            list($width, $height) = getimagesize($_FILES['logoSmall']['tmp_name']);
            if ($width < 150 && $height < 150) {
                if ($width / $height < 1.2 && $width / $height > 0.8) {
                    move_uploaded_file($_FILES['logoSmall']['tmp_name'], $logoSmallFileName);
                } else {
                    addFormError('logoSmall', 'Image needs to be more square');
                }
            } else {
                addFormError('logoSmall', 'Image too Large. Must be at most 150x150px');
            }
        } else {
            addFormError('logoSmall', 'Not a PNG Image');
        }
    }

    // brand Color
    if (isset($_POST['brand_hue'])) {
        _d('brand_hue isset');
        if (is_numeric($_POST['brand_hue'])) {
            _d('brand_hue is int');
            if ($_POST['brand_hue'] >= 0 && $_POST['brand_hue'] <= 360) {
                _d('brandHue ok');

                if (isset($_POST['brand_saturation'])) {
                    if (is_numeric($_POST['brand_saturation'])) {
                        if ($_POST['brand_saturation'] >= 0 && $_POST['brand_saturation'] <= 2) {
                            _d('brand_saturation ok');
                            $brandHue = $_POST['brand_hue'];
                            $brandSaturation = $_POST['brand_saturation'];
                        }
                    }
                }
            }
        }
    }

    $fileContents = '';
    $fileContents .= "@HuePrimary: " . $brandHue . ";\n";
    $fileContents .= "@SaturationMultiplier: " . $brandSaturation . ";\n";
    if (file_exists($logoLargeFileName)) {
        $fileContents .= "@BrandLogoLarge: '/branding/LogoLarge.png';\n";
    }
    if (file_exists($logoSmallFileName)) {
        $fileContents .= "@BrandLogoSmall: '/branding/Logo.png';";
    }

    file_put_contents($brandingLess, $fileContents);

    $cssFiles = glob($cssFolder . '*.css');
    foreach ($cssFiles as $cssFile) {
        unlink($cssFile);
    }


}


$form = formConstruct('Save');

$fieldset1 = '<fieldset class="col1">';
$fieldset1 .= '<h2>Brand Logo</h2>';
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/branding/LogoLarge.png')) {
    $fieldset1 .= '<label><span class="label">Uploaded Large Logo</span><img src="/branding/LogoLarge.png"/></label>';
}
$fieldset1 .= field(label('Upload Large Logo', 'PNG'), '<input type="file" name="logoLarge"/>', 'logoLarge');
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/branding/LogoLarge.png')) {
    $fieldset1 .= '<label><span class="label">Uploaded Small Logo</span><img src="/branding/Logo.png"/></label>';
}
$fieldset1 .= field(label('Upload Small Logo', 'PNG Square'), '<input type="file" name="logoSmall"/>', 'logoSmall');
$fieldset1 .= '</fieldset>';
$fieldset1 .= '<fieldset class="col1">';
$fieldset1 .= '<h2>Brand Colors</h2>';
$fieldset1 .= field(label('Brand Hue'), control_textInput('brand_hue', $brandHue), 'logoLarge');
$fieldset1 .= field(label('Brand Saturation'), control_textInput('brand_saturation', $brandSaturation), 'brand_saturation');
require_script('Scripts/Controls/control_brandingColor.js');
$fieldset1 .= '</fieldset>';

$form->find('fieldset.submit')->before($fieldset1);

$webPage = webPageConstruct('Admin Panel Branding');
$webPage->find('h1')->before(constructMenu('adminBranding.php'));
$webPage->find('h1')->after($form);
$webPage->find('h1')->after(generateMessageBar());
echo outputWebPage($webPage);




