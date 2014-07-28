<?php
return [
    'months' => explode('_','január_február_március_április_május_június_július_augusztus_szeptember_október_november_december'),
    'monthsShort' => explode('_','jan_feb_márc_ápr_máj_jún_júl_aug_szept_okt_nov_dec'),
    'weekdays' => explode('_','vasárnap_hétfő_kedd_szerda_csütörtök_péntek_szombat'),
    'weekdaysShort' => explode('_','vas_hét_kedd_sze_csüt_pén_szo'),
    'weekDaysMin' => explode('_','v_h_k_sze_cs_p_szo'),
    'meridiem'=> ['DE','DU'],
    'meridiemShort'=>['de','du'],
    'formats' => [
        'LT' => 'H:mm',
        'L' => 'YYYY.MM.DD.',
        'LL' => 'YYYY. MMMM D.',
        'LLL' => 'YYYY. MMMM D., LT',
        'LLLL' => 'YYYY. MMMM D., dddd LT'
    ]
];