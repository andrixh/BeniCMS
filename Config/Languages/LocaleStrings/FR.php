<?php
return [
    'months' => explode('_','janvier_février_mars_avril_mai_juin_juillet_août_septembre_octobre_novembre_décembre'),
    'monthsShort' => explode('_','janv._févr._mars_avr._mai_juin_juil._août_sept._oct._nov._déc.'),
    'weekdays' => explode('_','dimanche_lundi_mardi_mercredi_jeudi_vendredi_samedi'),
    'weekdaysShort' => explode('_','dim._lun._mar._mer._jeu._ven._sam.'),
    'weekDaysMin' => explode('_','Di_Lu_Ma_Me_Je_Ve_Sa'),
    'meridiem'=> ['AM','PM'],
    'meridiemShort'=>['A','P'],
    'formats' => [
        'LT' => 'HH:mm',
        'L' => 'DD/MM/YYYY',
        'LL' => 'D MMMM YYYY',
        'LLL' => 'D MMMM YYYY LT',
        'LLLL' => 'dddd D MMMM YYYY LT'
    ]
];