<?php
return [
    'DEBUG_REDIRECT'=>false,
    'DEBUG' => false,
    'DEBUG_REMOTE' => false,

// CHARS //
    'ALPHA_LOWER'=>'abcdefghijklmnopqrstuvwxyz',
    'ALPHA_UPPER'=>'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
    'ALPHA'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
    'DIGITS'=>'0123456789',
    'ALPHANUM'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
    'ALPHANUM_LOWER'=>'abcdefghijklmnopqrstuvwxyz0123456789',
    'ALPHANUM_UPPER'=>'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
    'DIACRITICS'=>'ÁáÅÄäΑαΆᾺἈἌἊἎἉἍἋἏᾹᾸάὰᾶἀἄἂἆἁἅἃἇᾱᾰᾼᾈᾌᾊᾎᾉᾍᾋᾏᾳᾴᾲᾷᾀᾄᾂᾆᾁᾅᾃᾇåÀàÂâÆæАаЪъĂăЬьЯяĄąÃãΒβБбçÇČčΞξЋћĆćЦцЧчĎďΔδДдЂђĐđЏџDd̂ëËÉéĚěΕεΈῈἘἜἚἙἝἛέὲἐἔἒἑἕἓÈèÊêЕеЁёЄєѢѣЭэĖėĘęΦφФфΓγГгҐґGgЃѓǴǵǵ̀̀ĞğΗηΉῊἨἬἪἮἩἭἫἯήὴῆἠἤἢἦἡἥἣἧῌᾘᾜᾚᾞᾙᾝᾛᾟῃῄῂῇᾐᾔᾒᾖᾑᾕᾓᾗÍíΙιΊῚἸἼἺἾἹἽἻἿΪῙῘίὶῖἰἴἲἶἱἵἳἷϊΐῒῗῑῐÎîÏïìÌИиIĪiīЇїİıЙйĬĭЈјJǰ̌ΚκКкkЌќḰḱХхΛλЛлЉљLl̂̂ŁłĹĺĽľΜμМмŇňΝνñНнЊњNn̂̂ŃńÓóØÖöΟοΌῸὈὌὊὉὍὋόὸὀὄὂὁὅὃÔôŒœőŐòÒоОøÕõΠπПпΘθŘřΡρῬῤῥРрŔŕŠšΣσςСсШшЩщŜŝŜŝŚśȘșŞşŤťΤτТтȚțŢţÚúŮůÜüΥυΎῪὙὝὛὟΫῩῨύὺῦὐὔὒὖὑὕὓὗϋΰῢῧῡῠÙùÛûűŰŬŭЮюВвΩωΏῺὨὬὪὮὩὭὫὯώὼῶὠὤὢὦὡὥὣὧῼᾨᾬᾪᾮᾩᾭᾫᾯῳῴῲῷᾠᾤᾢᾦᾡᾥᾣᾧΧχÝýΨψŸÿУуЎўЫыŽžΖζЖжẐẑŹźŻżзÆæß',
    'PUNCTUATION'=>'"<>?/.,!@#$%^&*()\' ',

// HOST //
    'HTTP_HOST_DEV'=>'beni.com.local', //host name for development server
    'HTTP_HOST_LIVE'=>'beni.com', //host name for live server
    'SITE_NAME'=>'Beni CMS', //website name to display in administration panel
    'ADMIN_BASE_PATH'=>'/admin', //base path for Admin


// DATABASE //
    'DB_HOST_NAME'=>'localhost', //db host name in live server
    'DB_NAME'=>'beni_db', // db name in live server
    'DB_NAME_ADMIN'=>'root', //db admin user in live server
    'DB_PASS_ADMIN'=>'root', //db admin password in live server
    'DB_NAME_VISITOR'=>'root', //db visitor user in live server
    'DB_PASS_VISITOR'=>'root', //db visitor password in live server

// security //

    'USER_NAME_LENGTH_MIN'=>3,
    'USER_NAME_LENGTH_MAX'=>20,
    'USER_PASS_LENGTH_MIN'=>8,

    'PASSWORD_HASH_COST'=>8,
    'PORTABLE_HASHES'=>false,

// Asset Manager

    'path.assets.definition'=>$_SERVER['DOCUMENT_ROOT'].'/Config/Assets/',
    'path.assets.source'=>$_SERVER['DOCUMENT_ROOT'].'/Asset_src',
    'path.assets.output'=>$_SERVER['DOCUMENT_ROOT'].'/Assets',

    'assets.minify'=> true,
    'assets.combine'=>true,
    'assets.gzip'=>true,
    'assets.force_recompile'=>false,

// CACHE //

    'cache.active' => true,
    'path.cache.structure'=>$_SERVER['DOCUMENT_ROOT'].'/Cache/Structure/',
    'path.cache.html'=>$_SERVER['DOCUMENT_ROOT'].'/Cache/Html/',

// FILE UPLOADS //
    'FILE_UPLOAD_DIRECTORY'=>'/Files/', //Relative to Admin Directory, don't forget the trailing '/'

// IMAGE UPLOADS //
    'IMAGE_UPLOAD_DIRECTORY'=>$_SERVER['DOCUMENT_ROOT'].'/Images/Upload/',
    'IMAGE_CONFORMED_DIRECTORY'=>'/Images/Conformed/',
];