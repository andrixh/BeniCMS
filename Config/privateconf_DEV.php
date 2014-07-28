<?php
return [
    'DEBUG_REDIRECT'=>false,
    'DEBUG' => true,
    'DEBUG_REMOTE' => false,

    // DATABASE //

    'DB_HOST_NAME'=>'localhost', //db host name in development server
    'DB_NAME'=>'beni_db', // db name in development server
    'DB_NAME_ADMIN'=>'root', //db admin user in development server
    'DB_PASS_ADMIN'=>'root', //db admin password in development server
    'DB_NAME_VISITOR'=>'root', //db visitor user in development server
    'DB_PASS_VISITOR'=>'root', //db visitor password in development server

    // Asset Manager

    'assets.minify'=> false,
    'assets.combine'=>false,
    'assets.gzip'=>false,
    'assets.force_recompile'=>true,

    // Cache
    'cache.active' => false
];