<?php
function addDependency($res, $dep){
	global $dependencies;
	if (!array_key_exists($res,$dependencies)){
		$dependencies[$res]=array($dep);
	} else {
		$dependencies[$res][]=$dep;
	}
}

$dependencies = array();
addDependency('Css/admin.css','Css/reset.css');
addDependency('Css/tables.css','Css/reset.css');
addDependency('Scripts/tables.js','Scripts/jquery.js');
addDependency('Scripts/tables.js','Css/tables.css');
addDependency('Scripts/adminMenu.js','Scripts/jquery.js');
addDependency('Scripts/adminMenu.js','Scripts/jquery.cookie.js');
addDependency('Scripts/jquery.cookie.js','Scripts/jquery.js');

//plupload
addDependency('Scripts/plupload/js/plupload.html5.js','Scripts/plupload/js/plupload.js');

// Jquery ui
addDependency('Scripts/jquery-ui.js','Scripts/jquery.js');
addDependency('Scripts/jquery-ui.js','Css/jquery-ui.css');

// Jquery SVG
addDependency('Scripts/jquery.svg.js','Scripts/jquery.js');
addDependency('Scripts/jquery.svgdom.js','Scripts/jquery.svg.js');

//Forms
addDependency('Scripts/uploadUtils.js','Scripts/jquery.js');
addDependency('Scripts/iframeForms.js','Scripts/jquery.js');
addDependency('Scripts/iframe.js','Scripts/jquery.js');

//Dialogs
addDependency('Scripts/dialog.js','Scripts/jquery-ui.js');
addDependency('Scripts/dialog.js','Css/dialog.css');


//Image Editor
addDependency('Scripts/imageEditor.js','Scripts/jquery-ui.js');
addDependency('Scripts/imageEditor.js','Scripts/jquery.transform.js');
addDependency('Scripts/imageEditor.js','Css/imageEditor.css');


//Items Viewer
addDependency('Scripts/itemView.js','Scripts/config.js');
addDependency('Scripts/itemView.js','Scripts/plupload/js/plupload.html5.js');
addDependency('Scripts/itemView.js','Scripts/dialog.js');
addDependency('Scripts/itemView.js','Scripts/Controls/control_mlstringBasic.js');
addDependency('Scripts/itemView.js','Css/itemView.css');
addDependency('Css/itemView.css','Css/reset.css');

//Image Viewer
addDependency('Scripts/imageView.js','Scripts/itemView.js');

//Files Viewer 
addDependency('Scripts/filesView.js','Scripts/itemView.js');

//Video Viewer
addDependency('Scripts/videoView.js','Scripts/itemView.js');


//Page Structure Editor
addDependency('Scripts/pageStruct.js','Scripts/jquery-ui.js');
addDependency('Scripts/pageStruct.js','Scripts/jquery.svg.js');
addDependency('Scripts/pageStruct.js','Css/pageStruct.css');
addDependency('Scripts/pageStruct.js','Scripts/browse_pageTypes.js');
addDependency('Scripts/pageStruct.js','Scripts/dialog.js');
addDependency('Scripts/pageStruct.js','Scripts/Controls/control_mlstringBasic.js');
addDependency('Css/pageStruct.css','Css/admin.css');

//Browsers

//Image Browser
addDependency('Scripts/browse_images.js','Scripts/jquery-ui.js');
addDependency('Scripts/browse_images.js','Css/browsers.css');

//Files Browser
addDependency('Scripts/browse_files.js','Scripts/jquery-ui.js');
addDependency('Scripts/browse_files.js','Css/browsers.css');


//Component Browser
addDependency('Scripts/browse_components.js','Scripts/jquery-ui.js');
addDependency('Scripts/browse_components.js','Css/browsers.css');

//Component Browser
addDependency('Scripts/browse_contents.js','Scripts/jquery-ui.js');
addDependency('Scripts/browse_contents.js','Css/browsers.css');

//PageType Browser
addDependency('Scripts/browse_pageTypes.js','Scripts/jquery-ui.js');
addDependency('Scripts/browse_pageTypes.js','Css/browsers.css');


//Controls
addDependency('Scripts/forms.js','Scripts/jquery.js');

//Image Select
addDependency('Scripts/Controls/control_imageSelect.js','Scripts/jquery.js');
addDependency('Scripts/Controls/control_imageSelect.js','Css/control_imageSelect.css');

//mlString Basic
addDependency('Scripts/Controls/control_mlstringBasic.js','Scripts/jquery.js');
addDependency('Scripts/Controls/control_mlstringBasic.js','Scripts/config.js');
addDependency('Scripts/Controls/control_mlstringBasic.js','Css/control_mlstringBasic.css');

//mlString Html
addDependency('Scripts/Controls/control_mlHtml.js','Scripts/jquery.js');
addDependency('Scripts/Controls/control_mlHtml.js','Scripts/jquery-ui.js');
addDependency('Scripts/Controls/control_mlHtml.js','Css/control_mlstringBasic.css');
addDependency('Scripts/Controls/control_mlHtml.js','Scripts/wymeditor/jquery.wymeditor.js');
addDependency('Scripts/wymeditor/jquery.wymeditor.js','Scripts/wymeditor/skins/preferred/skin.css');

//custom list editor
addDependency('Scripts/Controls/control_customList.js','Scripts/jquery.js');
addDependency('Scripts/Controls/control_customList.js','Css/control_customList.css');

// page scheme
addDependency('Scripts/Controls/control_pageScheme.js','Scripts/jquery-ui.js');
addDependency('Scripts/Controls/control_pageScheme.js','Css/control_pageScheme.css');

//gallery
addDependency('Scripts/Controls/control_galleryField.js','Scripts/jquery-ui.js');
addDependency('Scripts/Controls/control_galleryField.js','Css/control_galleryField.css');

//files
addDependency('Scripts/Controls/control_fileField.js','Scripts/jquery-ui.js');
addDependency('Scripts/Controls/control_fileField.js','Css/control_fileField.css');
addDependency('Scripts/Controls/control_fileField.js','Scripts/browse_files.js');

//files
addDependency('Scripts/Controls/control_contentList.js','Scripts/jquery-ui.js');
addDependency('Scripts/Controls/control_contentList.js','Css/control_contentList.css');
addDependency('Scripts/Controls/control_contentList.js','Scripts/browse_contents.js');

// Date Picker
addDependency('Scripts/Controls/control_datePicker.js','Scripts/jquery-ui.js');

// Html Editor
addDependency('Scripts/Controls/control_html.js','Scripts/jquery-ui.js');
addDependency('Scripts/Controls/control_html.js','Css/control_html.css');

//Code Mirror
addDependency('Scripts/Controls/control_code.js','Scripts/jquery.js');
addDependency('Scripts/Controls/control_code.js','Scripts/CodeMirror/lib/codemirror.js');
addDependency('Scripts/CodeMirror/lib/codemirror.js','Scripts/CodeMirror/lib/codemirror.css');
//Code Mirror Modes
addDependency('Scripts/CodeMirror/mode/xml/xml.js','Scripts/CodeMirror/lib/codemirror.js');
addDependency('Scripts/CodeMirror/mode/javascript/javascript.js','Scripts/CodeMirror/lib/codemirror.js');
addDependency('Scripts/CodeMirror/mode/css/css.js','Scripts/CodeMirror/lib/codemirror.js');
addDependency('Scripts/CodeMirror/mode/clike/clike.js','Scripts/CodeMirror/lib/codemirror.js');
addDependency('Scripts/CodeMirror/mode/php/php.js','Scripts/CodeMirror/lib/codemirror.js');
addDependency('Scripts/CodeMirror/mode/php/php.js','Scripts/CodeMirror/mode/xml/xml.js');
addDependency('Scripts/CodeMirror/mode/php/php.js','Scripts/CodeMirror/mode/javascript/javascript.js');
addDependency('Scripts/CodeMirror/mode/php/php.js','Scripts/CodeMirror/mode/css/css.js');
addDependency('Scripts/CodeMirror/mode/php/php.js','Scripts/CodeMirror/mode/clike/clike.js');
addDependency('Scripts/CodeMirror/mode/twig/twig.js','Scripts/CodeMirror/lib/codemirror.js');

addDependency('Scripts/Controls/idNamer.js','Scripts/Controls/control_code.js');
addDependency('Scripts/Controls/templateEnabler.js','Scripts/Controls/control_code.js');

//Content View Editor
addDependency('Scripts/Controls/control_contentView.js','Scripts/CodeMirror/mode/twig/twig.js');
addDependency('Scripts/Controls/control_contentView.js','Scripts/jquery.js');
addDependency('Scripts/Controls/control_contentView.js','Css/control_contentView.css');

//Branding Color
addDependency('Scripts/Controls/control_brandingColor.js','Scripts/jquery.js');
addDependency('Scripts/Controls/control_brandingColor.js','Scripts/jquery-ui.js');
addDependency('Scripts/Controls/control_brandingColor.js','Css/control_brandingColor.css');