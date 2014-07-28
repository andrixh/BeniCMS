<?php
// Content
function contentCreate($typeScheme, $usedTable = ''){
    _gc(__FUNCTION__);
    _d(func_get_args(),'parameters');
    $result = array();
    foreach ($typeScheme as $type){
        if ($type->type == 'boolean'){
            $result[$type->name] = $type->options->check_default;
        } else if ($type->type == 'string'){
            $result[$type->name] = '';
        } else if ($type->type == 'number'){
            $result[$type->name] = 0;
        } else if ($type->type == 'date'){
            $tempDate = getdate();
            $result[$type->name] = $tempDate['year'].'-'.$tempDate['mon'].'-'.$tempDate['mday'];
        } else if ($type->type == 'select'){
            $result[$type->name] = '';
        } else if ($type->type == 'mlstring'){
            $result[$type->name] = mlstring::Create()->usedTable('pages')->postName('content_'.$type->name);
        } else if ($type->type == 'mlhtml'){
            $result[$type->name] = mlstring::Create()->usedTable('pages')->postName('content_'.$type->name);
        } else if ($type->type == 'gallery'){
            $result[$type->name] = '';
        } else if ($type->type == 'mlgallery'){
            $result[$type->name] = mlstring::Create()->usedTable('pages')->postName('content_'.$type->name);
        } else if ($type->type == 'files'){
            $result[$type->name] = '';
        } else if  ($type->type == 'mlfiles'){
            $result[$type->name] = mlstring::Create()->usedTable('pages')->postName('content_'.$type->name);
        } else if ($type->type == 'page'){
            $result[$type->name] = '';
        } else if ($type->type == 'content'){
            $result[$type->name] = '';
        }  else if ($type->type == 'contentselect'){
            $result[$type->name] = '';
        }
    }
    _d($result,'result');
    _u();
    return $result;
}

function contentFromPost($content,$typeScheme){
    _gc(__FUNCTION__);
    _d(role_is(ROLE_DEV),'role dev');
    _d(role_is(ROLE_ADMIN),'role admin');
    _d(role_is(ROLE_USER),'role user');
    _d($content,'incoming content');
    $result = $content;
    _d($result,'result initialization');
    foreach ($typeScheme as $type){
        if (role_is(ROLE_DEV) || (role_is(ROLE_ADMIN) && !$type->lock_admin) || (role_is(ROLE_USER) && !$type->lock_user)){
            _d($type->name,'type->name');
            _d($type->type.'type->type');
            if ($type->type == 'boolean'){
                $result[$type->name] = isset($_POST['content_'.$type->name])?true:false;
            } else if ($type->type == 'string'){
                $result[$type->name] = $_POST['content_'.$type->name];
            } else if ($type->type == 'number'){
                $result[$type->name] = $_POST['content_'.$type->name];
            } else if ($type->type == 'date'){
                $result[$type->name] = $_POST['content_'.$type->name];
            } else if ($type->type == 'select'){
                $result[$type->name] = $_POST['content_'.$type->name];
            } else if ($type->type == 'mlstring'){
                $result[$type->name]->fromPost();// = mlstring_fromPost('content_'.$type->name);
            } else if ($type->type == 'mlhtml'){
                $result[$type->name]->fromPost();// = mlstring_fromPost('content_'.$type->name);
            } else if ($type->type == 'gallery'){
                $result[$type->name] = $_POST['content_'.$type->name];
            } else if ($type->type == 'mlgallery'){
                $result[$type->name]->fromPost();// = mlstring_fromPost('content_'.$type->name);
            } else if ($type->type == 'files'){
                $result[$type->name] = $_POST['content_'.$type->name];
            } else if ($type->type == 'mlfiles'){
                $result[$type->name]->fromPost();// = mlstring_fromPost('content_'.$type->name);
            } else if ($type->type == 'page'){
                $result[$type->name] = $_POST['content_'.$type->name];
            } else if ($type->type == 'content'){
                $result[$type->name] = $_POST['content_'.$type->name];
            } else if ($type->type == 'contentselect'){
                $result[$type->name] = $_POST['content_'.$type->name];
            }

        }
    }
    _d($result);
    _u();
    return $result;
}

function contentValidate($content,$typeScheme){
    $valid = true;
    $e=100;
    foreach ($typeScheme as $type){
        $val = $content[$type->name];
        $ok = true;
        $errorText = '';

        if ($type->type == 'number'){
            if (!is_numeric($val)){
                $ok = false;
                $errorText = 'Please enter an integer.';
            }
            if ($ok && $type->options->number_integer == true){
                if (!is_int(intval($val))){
                    $ok = false;
                    $errorText = 'Please enter an integer.';
                }
            }
            if ($ok && $type->options->number_min != '' && $type->options->number_max == ''){
                if ($val<$type->options->number_min){
                    $ok = false;
                    $errorText = 'Must be '.$type->options->number_min.'or greater.';
                }
            }
            if ($ok && $type->options->number_max != '' && $type->options->number_min == ''){
                if ($val>$type->options->number_max){
                    $ok = false;
                    $errorText = 'Must be '.$type->options->number_max.'or smaller.';
                }
            }
            if ($ok && $type->options->number_max != '' && $type->options->number_min != ''){
                if ($val>$type->options->number_max || $val<$type->options->number_min){
                    $ok = false;
                    $errorText = 'Must be between '.$type->options->number_min.'and '.$type->options->number_max.'.';
                }
            }
            if (!$ok){
                addFormError($e, $errorText);
                $valid = false;
            }
        } else if ($type->type == 'select'){
            if ($val==''){
                $valid = false;
                addFormError($e, 'Please select an option.');
            }
        }
        $e++;
    }
    return $valid;
}

function contentFields($content,$typeScheme,$typeFormTemplate){
    _gc(__FUNCTION__);
    _gc('params');_d($content,'$content');_d($typeScheme,'$typeScheme');_d($typeFormTemplate,'$typeFormTemplate');_u();
    if ($typeFormTemplate == ''){
        $pFormOutput = phpQuery::newDocument('<fieldset class="col2 first"></fieldset>');
        foreach ($typeScheme as $type){
            $pFormOutput->find('fieldset')->append('<control name="'.$type->name.'" />');
        }
    } else {
        $pFormOutput = phpQuery::newDocument($typeFormTemplate);
    }

    $e=100;	//starting error index, 0-99 are reserved for explicit fields
    $field = '';
    _d($typeScheme,'type scheme');
    foreach ($typeScheme as $type){
        $disabled = false;
        if ((role_is(ROLE_ADMIN) && $type->lock_admin) || (role_is(ROLE_USER) && $type->lock_user)){
            $disabled = true;
        }

        _d($type);
        _d($disabled,'Disabled is:');
        if ($type->type == 'boolean'){
            $field = field(label_checkbox($type->label,$type->description,$disabled,$type->name),control_checkbox('content_'.$type->name, $content[$type->name]),$e);
        } else if ($type->type == 'string'){
            if (!is_numeric($type->options->string_numRows) || $type->options->string_numRows <=1){
                $field = field(label($type->label,$type->description,$disabled,$type->name),control_textInput('content_'.$type->name, $content[$type->name]),$e);
            } else {
                $field = field(label($type->label,$type->description,$disabled,$type->name),control_textArea('content_'.$type->name, $content[$type->name],$type->options->string_numRows),$e);
            }
        } else if ($type->type == 'number'){
            $field =	 field(label($type->label,$type->description,$disabled,$type->name),control_textInput('content_'.$type->name, $content[$type->name]),$e);
        } else if ($type->type == 'date'){
            $field = field(label($type->label,$type->description,$disabled,$type->name),control_date('content_'.$type->name, $content[$type->name]),$e);
        } else if ($type->type == 'select'){
            $selectOptions = array();
            if ($type->options->select_provider == '-'){
                foreach ($type->options->select_custom as $key=>$value){
                    $selectOptions[$key]=$value;
                }
            } else if ($type->options->select_provider != ''){
                $selectOptions = provide($type->options->select_provider);
            }
            $field = field(label($type->label,$type->description,$disabled,$type->name),control_select('content_'.$type->name, $content[$type->name], $selectOptions),$e);
        } else if ($type->type == 'mlstring'){
            if (!is_numeric($type->options->mlstring_numRows) || $type->options->mlstring_numRows <=1){
                $field =	 field(label($type->label,$type->description,$disabled,$type->name),control_mlTextInput('content_'.$type->name, $content[$type->name]->getValues()),$e);
            } else {
                $field =	 field(label($type->label,$type->description,$disabled,$type->name),control_mlTextArea('content_'.$type->name, $content[$type->name]->getValues(),$type->options->mlstring_numRows),$e);
            }
        } else if ($type->type == 'mlhtml'){
            $field =	 field(label($type->label,$type->description,$disabled,$type->name),control_mlHtml('content_'.$type->name, $content[$type->name]->getValues(),$type->options->mlhtml_acceptImages,$type->options->mlhtml_acceptFiles,$type->options->mlhtml_acceptVideos,$type->options->mlhtml_acceptComponents ),$e);
        } else if ($type->type == 'gallery'){
            $field =	 field(label($type->label,$type->description,$disabled,$type->name),control_galleryField('content_'.$type->name, $content[$type->name], $type->options->gallery_acceptVideos, $type->options->gallery_acceptImages, $type->options->gallery_single),$e);
        } else if ($type->type == 'mlgallery'){
            $field =	 field(label($type->label,$type->description,$disabled,$type->name),control_mlGalleryField('content_'.$type->name, $content[$type->name]->getValues(), $type->options->gallery_acceptVideos, $type->options->gallery_acceptImages, $type->options->gallery_single),$e);
        } else if ($type->type == 'files'){
            $field =	 field(label($type->label,$type->description,$disabled,$type->name),control_fileField('content_'.$type->name, $content[$type->name], $type->options->files_single),$e);
        } else if ($type->type == 'mlfiles'){
            $field = field(label($type->label,$type->description,$disabled,$type->name),control_mlFileField('content_'.$type->name, $content[$type->name]->getValues(), $type->options->files_single),$e);
        } else if ($type->type == 'page'){
            $field = field(label($type->label,$type->description,$disabled,$type->name),control_pageSelect('content_'.$type->name, $content[$type->name]),$e);
        } else if ($type->type == 'content'){
            $field = field(label($type->label,$type->description,$disabled,$type->name),control_contentList('content_'.$type->name, $content[$type->name], $type->options->content_single, $type->options->contentTypes),$e);
        } else if ($type->type == 'contentselect'){
            $field = field(label($type->label,$type->description,$disabled,$type->name),control_contentselect('content_'.$type->name, $content[$type->name],$type->options->contentTypes),$e);
        }
        $e++;
        $pFormOutput->find('control[name='.$type->name.']')->replaceWith($field);
    }
    _u();
    return $pFormOutput;
}

function countContentResources($content,$typeScheme,$inc){
    foreach ($typeScheme as $type){
        if ($type->type == 'mlhtml'){
            countMlHtmlResources($content[$type->name]->getValues(),$inc);
        } else if ($type->type == 'gallery'){
            countGallery($content[$type->name],$inc);
        } else if  ($type->type == 'files'){
            countFileList($content[$type->name], $inc);
        } else if ($type->type == 'mlgallery'){
            countMlGallery($content[$type->name]->getValues(),$inc);
        } else if ($type->type == 'mlfiles'){
            countMlFileList($content[$type->name]->getValues(),$inc);
        } else if ($type->type == 'content'){
            countContentList($content[$type->name],$inc);
        } else if ($type->type == 'contentselect'){
            countContentList($content[$type->name],$inc);
        }
    }
}

function countMlHtmlResources ($mlString,$inc){
    foreach ($mlString as $key=>$value){
        if ($key!='strID'){
            $doc = phpQuery::newDocument($value);
            $imgs = pq('p.img');
            foreach ($imgs as $img){
                $dataImg = json_decode(pq($img)->attr('data-image'));
                setCount('image',$dataImg->physicalName,$inc);
            }
            $vids = pq('p.video');
            foreach ($vids as $vid){
                $dataVid = json_decode(pq($vid)->attr('data-video'));
                setCount('video',$dataVid->physicalName,$inc);
            }
            $comps = pq('p.component');
            foreach ($comps as $comp){
                $dataComp = json_decode(pq($comp)->attr('data-component'));
                setCount('components_'.$dataComp->typeID,$dataComp->componentID,$inc);
            }
            $files = pq('a.file');
            foreach ($files as $file){
                setCount('file',pq($file)->attr('href'),$inc);
            }
        }
    }
}

/*function countContent($contentData,$contentType,$inc){
    if ($contentData!=''){
        setCount('contents_'.$contentType,$contentData,$inc);
    }
}*/

function countContentList($contentData,$inc){
    if ($contentData!=''){
        $data = json_decode($contentData);
        if (is_array($data)) {
            foreach ($data as $cont) {
                setcount('contents_' . $cont->typeID, $cont->ID, $inc);
            }
        } else if (is_object($data)) {
            setcount('contents_' . $contentData->typeID, $contentData->ID, $inc);
        }
    }
}


function countFileList($fileData,$inc){
    if ($fileData!=''){
        $data = json_decode($fileData);
        foreach ($data as $res){
            setCount($res->resourceType,$res->physicalName,$inc);
        }
    }
}

function countMlFileList ($mlString,$inc){
    foreach ($mlString as $key=>$value){
        //if ($key!='strID'){
        countFileList($value, $inc);
        //}
    }
}

function countGallery($galleryData,$inc){
    if ($galleryData!=''){
        $data = json_decode($galleryData);
        foreach ($data as $res){
            setCount($res->resourceType,$res->physicalName,$inc);
        }
    }
}

function countMlGallery ($mlString,$inc){
    foreach ($mlString as $key=>$value){
        countGallery($value, $inc);
    }
}


function getMlTypes(){
    _gc(__FUNCTION__);
    _d(func_get_args());
    $result = array('NONE','mlhtml','mlstring','mlgallery','mlfiles');
    _d($result, 'result');
    _u();
    return $result;
}
/*
 * Prepares the contents for db storage. replaces all mlstring info with just the strid 
 */
function pageContentHibernate($content,$typeScheme){
    _gc(__FUNCTION__);

    $output = $content;
    foreach ($typeScheme as $type){
        _d($type,'type');
        if (array_search($type->type, getMlTypes())){
            _d('is a ml type');
            $output[$type->name] = $output[$type->name]->strID;
        }
    }
    _d($output, 'output');
    _u();
    return $output;
}

function pageContentExtractMlStrings($content,$typeScheme){
    _gc(__FUNCTION__);
    $output = array();
    foreach ($typeScheme as $type){
        if (in_array($type->type, getMlTypes())){
            $output[]=$content[$type->name];
        }
    }
    _d($output, 'output');
    _u();
    return $output;
}

function pageContentEstivate($content,$typeScheme){
    _gc(__FUNCTION__);
    $output = $content;
    foreach ($typeScheme as $type){
        _d($type,'type');
        if (in_array($type->type, getMlTypes())){
            _d('is a ml type');
            $output[$type->name] = mlString::Create($content[$type->name])->postName('content_'.$type->name);
        }
    }
    _d($output, 'output');
    _u();
    return $output;
}

function isParentOf($child,$parent){
    if ($child == $parent){
        return true;
    }
    $query = Query::Select('pages')->fields('pageID','rank','parent','main')->desc('main')->asc('rank');
    $pages = DB::get($query);

    $map = array();
    foreach ($pages as $page){
        $map[$page->pageID] = $page->parent;
    }

    $par = $child;
    $parentFound =false;
    while ($par!='') {
        $par = $map[$par];
        if ($par == $parent) {
            $parentFound = true;
            break;
        }
    }
    return $parentFound;
}


