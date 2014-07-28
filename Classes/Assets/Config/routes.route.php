<?php
//Route File to be included inside router registration;

return [
    '_name_prefix' => 'asset_',
    '_namespace' => 'Turbina\Assets',


    'gfx'=> [
        'pattern' => '/{bundle}/{file}.{ext}',
        'controller' => 'Main:missingGfx',
        'requirements' => [
            'bundle' => '\w+',
            'file'=>'[A-Za-z0-9/_-]+',
            'ext'=>'jpg|png|gif|eot|woff|svg|ttf|otf'
        ]
    ]
];
