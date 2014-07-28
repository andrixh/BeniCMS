<?php
return [
    'months' => explode('_','gennaio_febbraio_marzo_aprile_maggio_giugno_luglio_agosto_settembre_ottobre_novembre_dicembre'),
    'monthsShort' => explode('_','gen_feb_mar_apr_mag_giu_lug_ago_set_ott_nov_dic'),
    'weekdays' => explode('_','Domenica_Lunedì_Martedì_Mercoledì_Giovedì_Venerdì_Sabato'),
    'weekdaysShort' => explode('_','Dom_Lun_Mar_Mer_Gio_Ven_Sab'),
    'weekDaysMin' => explode('_','D_L_Ma_Me_G_V_S'),
    'meridiem'=> ['AM','PM'],
    'meridiemShort'=>['A','P'],
    'formats' => [
        'LT' => 'HH:mm',
        'L' => 'DD/MM/YYYY',
        'LL' => 'D MMMM YYYY',
        'LLL' => 'D MMMM YYYY LT',
        'LLLL' => 'dddd, D MMMM YYYY LT'
    ]
];