<?php
return [
    'months' => explode('_','Janar_Shkurt_Mars_Prill_Maj_Qershor_Korrik_Gusht_Shtator_Tetor_Nëntor_Dhjetor'),
    'monthsShort' => explode('_','Jan_Shk_Mar_Pri_Maj_Qer_Kor_Gush_Sht_Tet_Nën_Dhj'),
    'weekdays' => explode('_','E Diel_E Hënë_E Martë_E Mërkurë_E Enjte_E Premte_E Shtunë'),
    'weekdaysShort' => explode('_','Die_Hën_Mar_Mër_Enj_Pre_Sht'),
    'weekDaysMin' => explode('_','D_H_Ma_Më_E_P_Sh'),
    'meridiem'=> ['PD','MD'],
    'meridiemShort'=>['PD','MD'],
    'formats' => [
        'LT' => 'HH:mm',
        'L' => 'DD/MM/YYYY',
        'LL' => 'D MMMM YYYY',
        'LLL' => 'D MMMM YYYY LT',
        'LLLL' => 'dddd, D MMMM YYYY LT'
    ]
];