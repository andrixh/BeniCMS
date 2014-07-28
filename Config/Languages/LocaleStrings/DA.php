<?php
return [
    'months' => explode('_','januar_februar_marts_april_maj_juni_juli_august_september_oktober_november_december'),
    'monthsShort' => explode('_','jan_feb_mar_apr_maj_jun_jul_aug_sep_okt_nov_dec'),
    'weekdays' => explode('_','søndag_mandag_tirsdag_onsdag_torsdag_fredag_lørdag'),
    'weekdaysShort' => explode('_','søn_man_tir_ons_tor_fre_lør'),
    'weekDaysMin' => explode('_','sø_ma_ti_on_to_fr_lø'),
    'meridiem'=> ['AM','PM'],
    'meridiemShort'=>['A','P'],
    'formats' => [
        'LT' => 'HH:mm',
        'L' => 'DD/MM/YYYY',
        'LL' => 'D. MMMM YYYY',
        'LLL' => 'D. MMMM YYYY LT',
        'LLLL' => 'dddd D. MMMM YYYY HH:mm'
    ]
];