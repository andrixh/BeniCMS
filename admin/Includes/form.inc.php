<?php
$formErrors = array();

function addFormError($index,$message){
	global $formErrors;
	$formErrors[$index]=$message;
}

function getFormError($index=''){
	global $formErrors;	
	$result = '';
	if ($index!='' && isset($formErrors[$index]) && $formErrors[$index]!=''){
		$result ='<span class="formError"><span>'.str_replace(' ','&nbsp;',$formErrors[$index]).'</span></span>';
	}
	return $result;
}

function formConstruct($actionLabel='Submit',$afterActions=array()){
	require_style('Css/admin.css');
	require_script('Scripts/forms.js');
	$formTemplate='<form method="post" autocomplete="off" enctype="multipart/form-data"><fieldset class="submit">  
    <label><span></span><input type="submit" value="Submit"/></label><input type="hidden" class="hidden" name="perform" value="1" />
	</fieldset></form>';
	$pForm = phpQuery::newDocument($formTemplate);
	$pForm->find('fieldset.submit input[type=submit]')->val($actionLabel);
	if (count($afterActions)!=0){
		$pActions = phpQuery::newDocument('<label><span>and then</span><select name="afterAction"></select></label>');
		foreach ($afterActions as $afterAction){
			$pOption=phpQuery::newDocument('<option></option>');
			$pOption->find('option')->html($afterAction['label'])->val($afterAction['url']);
			if (isset($_POST['afterAction'])){
				if ($afterAction['url'] == $_POST['afterAction']){	
					$pOption->find('option')->attr('selected','selected');
				}
			} else if (array_key_exists('default', $afterAction)){
				$pOption->find('option')->attr('selected','selected');
			}
			$pActions->find('select')->append($pOption);
		}
	$pForm->find('fieldset.submit')->append($pActions);		
	}

	return $pForm;
}

function label($label='', $description='', $disabled = false, $help=''){
	$labelTemplate = '<label><span class="label"></span><error/><input/></label>';
	$pLabel = phpQuery::newDocument($labelTemplate);
	if ($disabled) {
		$pLabel->find('label')->addClass('disabled');
	}

	$pLabel->find('span')->html($label);
	if ($description!=''){
		$pLabel->find('span')->after('<span class="description">'.htmlspecialchars($description).'</span>');
	}
	if ($help!=''){
		$pLabel->find('span')->attr('title',$help);
	}
	_d($pLabel->html());
	return $pLabel;
}

function label_checkbox($label='', $description='', $disabled = false, $help=''){
    $labelTemplate = '<label><input/><span class="label"></span><error/></label>';
    $pLabel = phpQuery::newDocument($labelTemplate);
    if ($disabled) {
        $pLabel->find('label')->addClass('disabled');
    }

    $pLabel->find('span')->html($label);

    if ($description!=''){
        $pLabel->find('error')->after('<span class="description">'.$description.'</span>');
    }
    if ($help!=''){
        $pLabel->find('span')->attr('title',$help);
    }

    _d($pLabel->html());
    return $pLabel;
}

function field($labelData,$controlData,$error=''){
	$pLabel = phpQuery::newDocument($labelData);
	$pControl = phpQuery::newDocument($controlData);
	$pLabel->find('input')->replaceWith($pControl);
	if ($error!='') {
		$errorData = getFormError($error);
		if ($errorData!=''){
			$pLabel->find('error')->replaceWith('<span class="errorHolder">'.$errorData.'</span>');
		}
	}
	$pLabel->find('error')->remove();
	return $pLabel;
}

function control_textInput($name,$value){
	_gc(__FUNCTION__);
	$controlTemplate = '<input type="text"/>';
	$pControl = phpQuery::newDocument($controlTemplate);
	$pControl->find('input')->attr('name',$name)->attr('id',$name)->val($value);
	_u();
	return $pControl;
}

function control_textArea($name,$value,$rows=3){
	_gc(__FUNCTION__);
	$controlTemplate = '<textarea><textarea/>';
	$pControl = phpQuery::newDocument($controlTemplate);
	$pControl->find('textarea')->attr('name',$name)->attr('id',$name)->attr('rows',$rows)->val($value);
	_u();
	return $pControl;
}

function control_html($name,$value){
	_gc(__FUNCTION__);
	require_script('Scripts/Controls/control_html.js');
	$controlTemplate = '<textarea class="html"><textarea/>';
	$pControl = phpQuery::newDocument($controlTemplate);
	$pControl->find('textarea')->attr('name',$name)->attr('id',$name)->val(htmlspecialchars($value));
	_u();
	return $pControl;
}

function control_password($name,$value){
	_gc(__FUNCTION__);
	$controlTemplate = '<input type="password"/>';
	$pControl = phpQuery::newDocument($controlTemplate);
	$pControl->find('input')->attr('name',$name)->attr('id',$name)->val(htmlspecialchars($value));
	_u();
	return $pControl;
}

function control_checkbox($name,$value){
	_gc(__FUNCTION__);
	$controlTemplate = '<input type="checkbox"/>';
	$pControl = phpQuery::newDocument($controlTemplate);
	$pControl->find('input')->attr('name',$name)->attr('id',$name);
	if ($value != false){
		$pControl->find('input')->attr('checked','');
	}
	_u();
	return $pControl;
}

function control_hidden($name,$value){
	_gc(__FUNCTION__);
	$controlTemplate = '<input type="hidden"/>';
	$pControl = phpQuery::newDocument($controlTemplate);
	$pControl->find('input')->attr('name',$name)->attr('id',$name)->val(htmlspecialchars($value));
	_u();
	return $pControl;
}

function control_file($name){
	_gc(__FUNCTION__);
	$controlTemplate = '<input type="file"/>';
	$pControl = phpQuery::newDocument($controlTemplate);
	$pControl->find('input')->attr('name',$name)->attr('id',$name);
	_u();
	return $pControl;
}

function control_select($name, $value, $options=array(), $allowEmpty=false){
	_gc(__FUNCTION__);
	$controlTemplate = '<select></select>';
	$optionTemplate = '<option></option>';
	$pControl = phpQuery::newDocument($controlTemplate);
	$pControl->find('select')->attr('name',$name)->attr('id',$name);
	if ($allowEmpty){
		$pControl->find('select')->append('<option value="">(none)</option>');
	}
	foreach ($options as $key=>$label){
		$pOption = phpQuery::newDocument($optionTemplate);
		$pOption->find('option')->attr('value',$key)->text($label);
		if ($key==$value){
			_d($key,'set active');
			$pOption->find('option')->attr('selected','selected');
		}
		$pControl->find('select')->append($pOption);
	}
	_u();
	return $pControl;
}

function control_selectList($name, $value, $options=array(), $size=5, $allowEmpty=false){
	_gc(__FUNCTION__);
	$controlTemplate = '<select multiple></select>';
	$optionTemplate = '<option></option>';
	$pControl = phpQuery::newDocument($controlTemplate);
	$pControl->find('select')->attr('name',$name.'[]')->attr('id',$name)->attr('size',$size);
	if ($allowEmpty){
		$pControl->find('select')->append('<option value="">(none)</option>');
	}
	if (is_array($value)){
		$values = $value;
	} else {
		$values = explode(',', $value);
	}
	foreach ($options as $key=>$label){
		$pOption = phpQuery::newDocument($optionTemplate);
		$pOption->find('option')->attr('value',$key)->text($label);
		if (in_array($key, $values)){
			_d($key,'set active');
			$pOption->find('option')->attr('selected','selected');
		}
		$pControl->find('select')->append($pOption);
	}
	_u();
	return $pControl;
}

function control_selectImage($name,$value,$options){
	_gc(__FUNCTION__);	
	require_script('Scripts/Controls/control_imageSelect.js');
	$pControl = phpQuery::newDocument(control_select($name, $value, $options));
	$pControl->find('select')->addClass('imageSelect');
	_u();
	return $pControl;
}

function control_date($name,$value){
	_gc(__FUNCTION__);
	require_script('Scripts/Controls/control_datePicker.js');
	$controlTemplate = '<input type="text" class="datePicker"/>';
	$pControl = phpQuery::newDocument($controlTemplate);
	$pControl->find('input')->attr('name',$name)->attr('id',$name)->val(htmlspecialchars($value));
	_u();
	return $pControl;
}

function control_mlTextArea($name,$values,$rows=5){
	_gc(__FUNCTION__);	
	require_script('Scripts/Controls/control_mlstringBasic.js');
	$langs = mlstring::$languages_ext;
	$wrapperTemplate = '<fieldset class="mlstring basic" data-element="textarea"></fieldset>';
	$controlTemplate = '<textarea class="hidden"></textarea>';
	$pWrapper = phpQuery::newDocument($wrapperTemplate);
	$pWrapper->find('fieldset')->attr('data-basename',$name);
	foreach ($langs as $lang){
		$pControl = phpQuery::newDocument($controlTemplate);
		$pControl->find('textarea')->addClass('hidden')->attr('name',$name.'_'.$lang['langID'])->attr('id',$name.'_'.$lang['langID'])->attr('rows',$rows);
		$pControl->find('textarea')->val(htmlspecialchars($values[$lang['langID']]));
		$pWrapper->find('fieldset')->append($pControl);
	}
	//$pWrapper->find('fieldset')->append('<input type="hidden" name="'.$name.'_strID'.'" value="'.$values['strID'].'"/>');
	_u();
	return $pWrapper;
}

function control_mlTextInput($name,$values,$rows=5){
	_gc(__FUNCTION__);
	require_script('Scripts/Controls/control_mlstringBasic.js');
	$langs = mlstring::$languages_ext;
	$wrapperTemplate = '<fieldset class="mlstring basic" data-element="input"></fieldset>';
	$controlTemplate = '<input type="text" class="hidden">';
	$pWrapper = phpQuery::newDocument($wrapperTemplate);
	$pWrapper->find('fieldset')->attr('data-basename',$name);
	foreach ($langs as $lang){
		$pControl = phpQuery::newDocument($controlTemplate);
		$pControl->find('input')->addClass('hidden')->attr('name',$name.'_'.$lang['langID'])->attr('id',$name.'_'.$lang['langID']);
		$pControl->find('input')->val(htmlspecialchars($values[$lang['langID']]));
		$pWrapper->find('fieldset')->append($pControl);
	}
	//$pWrapper->find('fieldset')->append('<input type="hidden" name="'.$name.'_strID'.'" value="'.$values['strID'].'"/>');
	_u();
	return $pWrapper;
}

$_mlHtmlEditorJoinedScripts = false;
function control_mlHtml($name,$values, $accept_images = false, $accept_files = false, $accept_videos=false, $accept_components=false){
	_gc(__FUNCTION__);
	require_script('Scripts/Controls/control_mlHtml.js');
	$langs = mlstring::$languages_ext;
	$wrapperTemplate = '<fieldset class="mlHtml"></fieldset>';
	$controlTemplate = '<textarea class="hidden"></textarea>';
	$pWrapper = phpQuery::newDocument($wrapperTemplate);
	$pWrapper->find('fieldset')->attr('data-languages',json_encode($langs))->attr('data-basename',$name);
	foreach ($langs as $lang){
		$pControl = phpQuery::newDocument($controlTemplate);
		$pControl->find('textarea')->/*addClass('hidden')->*/attr('name',$name.'_'.$lang['langID'])->attr('id',$name.'_'.$lang['langID']);
		$pControl->find('textarea')->val(htmlspecialchars($values[$lang['langID']]));
		$pWrapper->find('fieldset')->append($pControl);
	}
	//$pWrapper->find('fieldset')->append('<input type="hidden" name="'.$name.'_strID'.'" value="'.$values['strID'].'"/>');
	if ($accept_images) {
		require_script('Scripts/browse_images.js');
		$pWrapper->find('fieldset')->addClass('dropZone')->addClass('accept_image');
	}
	if ($accept_files) {
		require_script('Scripts/browse_files.js');
		$pWrapper->find('fieldset')->addClass('dropZone')->addClass('accept_file');
	}
	if ($accept_videos) {
		require_script('Scripts/browse_videos.js');
		$pWrapper->find('fieldset')->addClass('dropZone')->addClass('accept_video');
	}
	if ($accept_components) {
		require_script('Scripts/browse_components.js');
		$pWrapper->find('fieldset')->addClass('dropZone')->addClass('accept_component');
	}

	if (!file_exists($_SERVER['DOCUMENT_ROOT'].conf('ADMIN_BASE_PATH').'/Css/wymiframe.css') || $_SERVER['SERVER_NAME']== conf('HTTP_HOST_DEV')){ //this bit is for the WyM iFrame
		global $_mlHtmlEditorJoinedScripts;
		if (!$_mlHtmlEditorJoinedScripts){
			$_mlHtmlEditorJoinedScripts = true;

			require_once 'Lib/LessPhp/lessc.inc.php';

            $lessc = new lessc();
            $lessc->compileFile('Css/wymiframe.less', 'Css/wymiframe.css');

			$jquery = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/admin/Scripts/jquery.js');
			$jqueryui = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/admin/Scripts/jquery-ui.js');

			$w_debug = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/admin/Scripts/wymiframe.debug.js');
			$w_image = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/admin/Scripts/wymiframe.imageSettings.js');
			$w_video = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/admin/Scripts/wymiframe.videoSettings.js');
			$w_table = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/admin/Scripts/wymiframe.tableSettings.js');
			$w_messages = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/admin/Scripts/wymiframe.messages.js');
			$w_init = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/admin/Scripts/wymiframe.init.js');

			$w_allScripts = $jquery."\n".$jqueryui."\n".$w_debug."\n".$w_image."\n".$w_video."\n".$w_table."\n".$w_messages."\n".$w_init;

			file_put_contents($_SERVER['DOCUMENT_ROOT'].'/admin/Scripts/wymiframe.js',$w_allScripts);
		}
	}


	_u();
	return $pWrapper;
}



function control_pageScheme($name,$value){
	_gc(__FUNCTION__);
	require_script('Scripts/Controls/control_pageScheme.js');
	$controlTemplate = '<input type="text" class="pageScheme"/>';
	$pControl = phpQuery::newDocument($controlTemplate);
	$pControl->find('input')
		->attr('name',$name)
		->attr('id',$name)
		->val($value)
		->attr('data-providers',json_encode(enumProviders()));
	_u();
	return $pControl;
} 

function control_customList($name,$value){
	_gc(__FUNCTION__);	
	require_script('Scripts/Controls/control_customList.js');
	$controlTemplate = '<input type="hidden" class="customList"/>';
	$pControl = phpQuery::newDocument($controlTemplate);
	$pControl->find('input')->attr('name',$name)->attr('id',$name)->val($value);
	_u();
	return $pControl;
} 

function control_code($name, $value, $type='xml'){
	_gc(__FUNCTION__);
		_d($value);
		$modeName = $type;
		require_script('Scripts/Controls/control_code.js');
		if ($type == 'html'){
			$modeName = 'xml';
		}
		
		require_script('Scripts/CodeMirror/mode/'.strtolower($modeName).'/'.strtolower($modeName).'.js');
		
		$controlTemplate = '<textarea type="'.$type.'" class="codeMirror"></textarea>';
		$pControl = phpQuery::newDocument($controlTemplate);
		$pControl->find('textarea')->attr('name',$name)->attr('id',$name)->html(htmlentities($value));
		_d($pControl->html());
	_u();
	return $pControl;
}

function control_contentViews($name, $titles, $values) {
    _gc(__FUNCTION__);
    _d($titles);
    _d($values);

    require_script('Scripts/Controls/control_contentView.js');


    $controlTemplate = '<fieldset class="contentViews first"><div class="editor"></div><ul class="tabs"></ul></fieldset>';
    $pControl = phpQuery::newDocument($controlTemplate);
    for ($i=0; $i<count($titles); $i++){
        $tab = phpQuery::newDocument('<li><input type="hidden"/></li>');
        $tab->find('input')->attr('name',$name.'_title[]');
        if ($i == 0){
            $tab->find('input')->val('default');
        } else {
            $tab->find('input')->val($titles[$i]);
        }
        $pControl->find('ul')->append($tab);

        $input = phpQuery::newDocument('<textarea class="hidden"></textarea>');
        $input->find('textarea')->attr('name',$name.'_value[]')->html(htmlentities($values[$i]));
        if ($i == 0){
            $input->find('textarea')->addClass('selected');
        }
        $pControl->find('div.editor')->append($input);
    }
    _d($pControl->html());
    _u();
    return $pControl;
}

function control_galleryField($name, $value='', $accept_videos=false, $accept_images = true, $single=false){ //Create a Gallery Drop Area (ul) and associated input named after $identifier.
	_gc(__FUNCTION__);	
	require_script('Scripts/Controls/control_galleryField.js');
	$controlTemplate = '<input class="galleryField" type="text"/>';
	$imgPath = conf('IMAGE_RESIZED_DIRECTORY');
	$pControl = phpQuery::newDocument($controlTemplate);
	if (is_array($value)){
		$val = implode(',',$value);
	} else {
		$val = $value;
	}
	$pControl->find('input')->attr('name',$name)->attr('id',$name)->attr('data-imagePath',$imgPath)->val($val);
	if ($accept_videos){
		require_script('Scripts/browse_videos.js');
		$pControl->find('input')->addClass('accept_video');
	}
	if ($accept_images){
		require_script('Scripts/browse_images.js');
		$pControl->find('input')->addClass('accept_image');
	}
	if ($single){
		$pControl->find('input')->addClass('single');
	}
	_d($pControl->html());
	_u();
	return $pControl;
}

function control_mlGalleryField($name, $values, $accept_videos=false, $accept_images = true, $single=false){
	_gc(__FUNCTION__);
	_d($values,'values');
	require_script('Scripts/Controls/control_mlstringBasic.js');
	$langs = mlString::$languages_ext;
	$wrapperTemplate = '<fieldset class="mlstring basic" data-element="input"></fieldset>';
	$controlTemplate = '<input type="text" class="hidden galleryField">';
	$pWrapper = phpQuery::newDocument($wrapperTemplate);
	$pWrapper->find('fieldset')->attr('data-languages',json_encode($langs))->attr('data-basename',$name);
	foreach ($langs as $lang){
		$pControl = phpQuery::newDocument(control_galleryField($name, $values[$lang['langID']] , $accept_videos, $accept_images, $single));
		$pControl->find('input')->addClass('hidden')->attr('name',$name.'_'.$lang['langID'])->attr('id',$name.'_'.$lang['langID']);
		$pControl->find('input')->val($values[$lang['langID']]);
		$pWrapper->find('fieldset')->append($pControl);
	}
	//$pWrapper->find('fieldset')->append('<input type="hidden" name="'.$name.'_strID'.'" value="'.$values['strID'].'"/>');
	_u();
	return $pWrapper;
}


function control_fileField($name, $value='', $single=false){ //Create a Gallery Drop Area (ul) and associated input named after $identifier.
	_gc(__FUNCTION__);	
	require_script('Scripts/Controls/control_fileField.js');
	$controlTemplate = '<input class="fileField" type="hidden"/>';
	//$imgPath = conf('IMAGE_RESIZED_DIRECTORY');
	$pControl = phpQuery::newDocument($controlTemplate);
	if (is_array($value)){
		$val = implode(',',$value);
	} else {
		$val = $value;
	}
	$pControl->find('input')->attr('name',$name)->attr('id',$name)->val($val);
	if ($single){
		$pControl->find('input')->addClass('single');
	}
	_u();
	return $pControl;
}

function control_mlFileField($name, $values, $single=false){
	_gc(__FUNCTION__);
	_d($values,'values');
	require_script('Scripts/Controls/control_mlstringBasic.js');
	$langs = mlString::$languages_ext;
	$wrapperTemplate = '<fieldset class="mlstring basic" data-element="input"></fieldset>';
	$controlTemplate = '<input type="text" class="hidden fileField">';
	$pWrapper = phpQuery::newDocument($wrapperTemplate);
	$pWrapper->find('fieldset')->attr('data-languages',json_encode($langs))->attr('data-basename',$name);
	foreach ($langs as $lang){
		$pControl = phpQuery::newDocument(control_fileField($name, $values[$lang['langID']] , $single));
		$pControl->find('input')->addClass('hidden')->attr('name',$name.'_'.$lang['langID'])->attr('id',$name.'_'.$lang['langID']);
		$pControl->find('input')->val($values[$lang['langID']]);
		$pWrapper->find('fieldset')->append($pControl);
	}
	//$pWrapper->find('fieldset')->append('<input type="hidden" name="'.$name.'_strID'.'" value="'.$values['strID'].'"/>');
	_u();
	return $pWrapper;
}

function control_contentList($name, $value='', $single=false, $types=array()){ //Create a Gallery Drop Area (ul) and associated input named after $identifier.
	_gc(__FUNCTION__);
	require_script('Scripts/Controls/control_contentList.js');
	$controlTemplate = '<input class="contentList" type="hidden"/>';
	//$imgPath = conf('IMAGE_RESIZED_DIRECTORY');
	$pControl = phpQuery::newDocument($controlTemplate);
	if (is_array($value)){
		$val = implode(',',$value);
	} else {
		$val = $value;
	}
	$pControl->find('input')->attr('name',$name)->attr('id',$name)->val($val);
	if ($single){
		$pControl->find('input')->addClass('single');
	}
	foreach ($types as $type){
		$pControl->find('input')->addClass('accept_content_'.$type);
	}
	_u();
	return $pControl;
}

function control_contentSelect($name, $value = '', $type){
    _gc(__FUNCTION__);
    $controlTemplate = '<select></select>';
    $optionTemplate = '<option></option>';
    $pControl = phpQuery::newDocument($controlTemplate);
    $pControl->find('select')->attr('name',$name)->attr('id',$name);
    $options = DB::get(Query::Select('contents_'.$type)->fields(array('ID','contentID')));
    foreach ($options as $option){
        $pOption = phpQuery::newDocument($optionTemplate);
        $pOption->find('option')->attr('value',$option->ID)->text($option->contentID);
        if ($option->ID == $value){
            $pOption->find('option')->attr('selected','selected');
        }
        $pControl->find('select')->append($pOption);
    }
    _u();
    return $pControl;
}




function control_pageSelect($name, $value) {
	_gc(__FUNCTION__);	

    $query = Query::Select('pages')->fields('pageID','rank','parent','main')->desc('main')->asc('rank');
	$pages = DB::get($query);
	_d($pages,'pages');
		
	$map = array();
	foreach ($pages as $page){
		$map[$page->pageID] = $page->parent;
	}
	
	_d($map);
	$tree = array();
	 _buildTree($tree, $map, '');
	_d($tree);

	
	$controlTemplate = '<select></select>';
	$optionTemplate = '<option></option>';
	$pControl = phpQuery::newDocument($controlTemplate);
	$pControl->find('select')->attr('name',$name)->attr('id',$name);
	
	$pControl->find('select')->append('<option value="">(none)</option>');
	
	foreach ($tree as $key=>$val){
		$pOption = phpQuery::newDocument($optionTemplate);
		$pOption->find('option')->attr('value',$key)->text($key)->addClass('inset'.$val);
		if ($key==$value){
			$pOption->find('option')->attr('selected','selected');
		}
		$pControl->find('select')->append($pOption);
	}

	_u();
	return $pControl;
}

function _buildTree(&$tree,$map,$iParent,$level=0){
	$result = array();
	foreach($map as $pageID=>$parent) {
		
		if ($parent==$iParent){
			$tree[$pageID]=$level;
			$result[$pageID]=_buildTree($tree,$map,$pageID,$level+1);
		}
	}
	return $result;
}
