<?php
$__menuItems = [
    'contents' => [
        'security'=>0,
        'label'=>'Contents',
        'items'=>[
            'pages'=>[
                'label'=>'Pages',
                'link'=>'pagesView.php',
                'security'=>0
            ]
        ],
    ],
    'resources' => [
        'security'=>0,
        'label'=>'Resources',
        'items'=>[
            'files'=>[
                'label'=>'Files',
                'link' =>'filesView.php',
                'security'=>0
            ],
            'images'=>[
                'label'=>'Images',
                'link' =>'imagesView.php',
                'security'=>0
            ],
            'videos'=>[
                'label'=>'Videos',
                'link' =>'videosView.php',
                'security'=>0
            ]
        ]
    ],
    'components' => [
        'label'=>'Components',
        'security'=>0,
        'items' => [

        ]
    ],

    'architecture' => [
        'security'=>2,
        'label'=>'Architecture',
        'items' => [
            'select'=>[
                'label'=>'Select Lists',
                'link' =>'selectProvidersView.php',
                'security'=>2,
                'items'=>[]
            ],
            'pagetypes'=>[
                'label'=>'Page Types',
                'link'=> 'pageTypesView.php',
                'security'=>2,
                'add' => [
                    'label' => 'Add Page Type',
                    'link' => 'pageTypesAdd.php',
                    'security'=>2
                ],
                'items'=>[]
            ],
            'contenttypes'=>[
                'label'=>'Content Types',
                'link'=> 'contentTypesView.php',
                'security'=>2,
                'add' => [
                    'label' => 'Add Content Type',
                    'link' => 'contentTypesAdd.php',
                    'security'=>2
                ],
                'items'=>[]
            ],
            'componenttypes'=>[
                'label'=>'Component Types',
                'link'=> 'componentTypesView.php',
                'security'=>2,
                'add' => [
                    'label' => 'Add Component Type',
                    'link' => 'componentTypesAdd.php',
                    'security'=>2
                ],
                'items'=>[]
            ]
        ]
    ],
    'configuration' => [
        'security'=>1,
        'label'=>'Configuration',
        'items' =>[
            'languages'=>[
                'label'=>'Languages',
                'link'=>'languageView.php',
                'security'=>1,
                'add' => [
                    'label' => 'Add Language',
                    'link' => 'languageAdd.php',
                    'security'=>1
                ]
            ],
            'site'=>[
                'label'=>'Site Definition',
                'link'=>'siteDefinition.php',
                'security'=>1
            ],
            'branding'=>[
                'label'=>'Admin Branding',
                'link'=>'adminBranding.php',
                'security'=>2
            ],
            'interface'=>[
                'label'=>'Interface Strings',
                'link'=>'interfaceView.php',
                'security'=>1,
                'add'=>[
                    'label'=>'Add Interface String',
                    'link'=>'interfaceAdd.php',
                    'security'=>1
                ]
            ],
            'mlstrings'=>[
                'label'=>'Multilingual Strings',
                'link' => 'mlstringsView.php',
                'security'=>2
            ]
        ]
    ],
    'users' => [
        'security'=>1,
        'label'=>'Users',
        'items' => [
            'view'=>[
                'label' => 'Administrators',
                'link' => 'adminView.php',
                'security'=>1,
                'add' => [
                    'label'=>'Add Administrator',
                    'link'=> 'adminAdd.php',
                    'security'=>1
                ]
            ]
        ],
    ],


];




function constructMenu($activeItemUrl = '')
{
    global $__menuItems;

    function addMenuContentTypes($__menuItems)
    {

        $query = Query::Select('contenttypes')->fields('ID','typeID', 'label','hidden')->asc('rank');
        $ctypes = DB::get($query);


        if ($ctypes) {
            foreach ($ctypes as $ct) {
                $__menuItems['contents']['items'][$ct->typeID] = [
                    'label'=>ucfirst($ct->label),
                    'link'=>'contentsView.php?type='.$ct->typeID,
                    'security'=>$ct->hidden?1:0,
                    'add' => [
                        'security'=>$ct->hidden?1:0,
                        'label' => 'Add '.ucfirst($ct->label),
                        'link'=>'contentsAdd.php?type='.$ct->typeID
                    ]
                ];
                if (role_is(ROLE_DEV)){
                    $__menuItems['architecture']['items']['contenttypes']['items'][$ct->typeID] = [
                        'label'=>ucfirst($ct->label),
                        'link'=>'contentTypesEdit.php?id='.$ct->ID,
                        'security'=>2
                    ];
                }
            }
        } else {
            unset($__menuItems['Components']);
        }

        return $__menuItems;
    }


    function addMenuComponentTypes($__menuItems)
    {
        $query = Query::Select('componenttypes')->fields('ID','typeID', 'label','hidden')->asc('rank');
        $componentTypes = DB::get($query);

        $ctypes = DB::get($query);


        if ($ctypes) {
            foreach ($ctypes as $ct) {
                $__menuItems['components']['items'][$ct->typeID] = [
                    'label'=>ucfirst($ct->label),
                    'link'=>'componentsView.php?type='.$ct->typeID,
                    'security'=>$ct->hidden?1:0,
                    'add' => [
                        'security'=>$ct->hidden?1:0,
                        'label' => 'Add '.ucfirst($ct->label),
                        'link'=>'componentsAdd.php?type='.$ct->typeID
                    ]
                ];
                if (role_is(ROLE_DEV)){
                    $__menuItems['architecture']['items']['componenttypes']['items'][$ct->typeID] = [
                        'label'=>ucfirst($ct->label),
                        'link'=>'componentTypesEdit.php?id='.$ct->ID,
                        'security'=>2
                    ];
                }
            }
        } else {
            unset($__menuItems['components']);
        }
        return $__menuItems;
    }

    function addMenuPageTypes($__menuItems)
    {
        $query = Query::Select('pagetypes')->fields('ID','typeID', 'label','hidden')->asc('rank');
        $componentTypes = DB::get($query);

        $ctypes = DB::get($query);


        if ($ctypes) {
            foreach ($ctypes as $ct) {
                /*$__menuItems['contents']['items']['pages']['add']['items'][$ct->typeID] = [
                    'label'=>ucfirst($ct->label),
                    'link'=>'pagesAdd.php?type='.$ct->typeID,
                    'security'=>$ct->hidden?1:0
                ];*/
                if (role_is(ROLE_DEV)){
                    $__menuItems['architecture']['items']['pagetypes']['items'][$ct->typeID] = [
                        'label'=>ucfirst($ct->label),
                        'link'=>'pageTypesEdit.php?id='.$ct->ID,
                        'security'=>2
                    ];
                }
            }
        } else {
            unset($__menuItems['Components']);
        }
        return $__menuItems;
    }

    $__menuItems = addMenuComponentTypes($__menuItems);
    $__menuItems = addMenuContentTypes($__menuItems);
    $__menuItems = addMenuPageTypes($__menuItems);






    _gc('Constructing Menu');
    _d($__menuItems,'menu items to be constructed');

    require_script('Scripts/adminMenu.js');
    $menuTemplate = '
<div id="sideMenu">
    <div class="handle"></div>
    <div id="MenuHeader">
    <a class="logo" href="index.php"/></a>
    <div class="logoutZone">
        <p>Hello,
            <a class="account" href="adminChangePass.php">'.$_SESSION['loginFullName'].'</a>
            <a class="logout" href="login.php?doLogout=1">Logout</a>
        </p>
    </div>
    <nav></nav>
    </div>

</div>';

    // $_SESSION['loginFullName']

    function addButton($item,$activeItemUrl){
        $result = false;
        if (array_key_exists('security',$item) && !roleCheck($item['security'])) {
            $result = false;
        } else {
            if (array_key_exists('link',$item)) {
                $selected = $item['link']==$activeItemUrl?' class="add selected" ':' class="add" ';
                $result = '<a '.$selected.' href="' . $item['link'] . '" title="'.$item['label'].'">' . $item['label'] . '</a>';
            } else if (array_key_exists('items',$item)) {
                $result = '';
                foreach ($item['items'] as $currItem){
                    $listItem = subItem($currItem,$activeItemUrl);
                    if ($listItem) {
                        $result.= $listItem;
                    }
                }
                if ($result!=''){
                    $result = '<ul class="add">'.$result.'</ul>';
                }
            }
        }
        return $result;
    }

    function subItem($item,$activeItemUrl){
        $result = false;
        if (array_key_exists('security',$item) && !roleCheck($item['security'])) {
            $result = false;
        } else {
            $selected = $item['link']==$activeItemUrl?' class="selected" ':'';
            $result = '<li><a '.$selected.'href="'.$item['link'].'">'.$item['label'].'</a>';
            if (array_key_exists('add',$item)){
                $result.=addButton($item['add'],$activeItemUrl);
            }
            if (array_key_exists('items',$item) && count($item['items']>0)) {
                $subResult = '';
                foreach($item['items'] as $subItem) {
                    $subItemResult = subItem($subItem, $activeItemUrl);
                    if ($subItem) {
                        $subResult .= $subItemResult;
                    }
                }
                if ($subResult!=''){
                    $result.='<ul>'.$subResult.'</ul>';
                }
            }
            $result.='</li>';
        }
        return $result;
    }

    function topItem($key,$item,$activeItemUrl){
        $result = false;
        if (array_key_exists('security',$item) && !roleCheck($item['security'])) {
            $result = false;
        } else {
            if (array_key_exists('items',$item) && count($item['items'])>0){
                $result = '';
                foreach ($item['items'] as $subItem){
                    $menuInner = subItem($subItem,$activeItemUrl);
                    if ($menuInner) {
                        $result.=$menuInner;
                    }
                }
            }
        }
        if ($result != '') {
            $expanded = false;
            if (isset($_COOKIE['menu_active'])){
                if ($_COOKIE['menu_active'] == $key) {
                    $expanded = true;
                }
            }
            $result = '<li '.($expanded?' class="expanded" ':'').'key="'.$key.'"><a href="#">'.$item['label'].'</a><ul>'.$result.'</ul></li>';
        }
        return $result;
    }

    $menuArea = phpQuery::newDocument($menuTemplate);
    $menu = '<ul>';
    foreach ($__menuItems as $key=>$menuItem){
        $topItem = topItem($key,$menuItem,$activeItemUrl);
        if ($topItem){
            $menu.=$topItem;
        }
    }
    $menu.='</ul>';


    _u();
    return (str_replace('<nav></nav>','<nav>'.$menu.'</nav>',$menuTemplate));
}