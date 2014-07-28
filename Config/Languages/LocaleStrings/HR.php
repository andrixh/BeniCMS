<?php
return [
    'months' => explode('_','sječanj_veljača_ožujak_travanj_svibanj_lipanj_srpanj_kolovoz_rujan_listopad_studeni_prosinac'),
    'monthsShort' => explode('_','sje._vel._ožu._tra._svi._lip._srp._kol._ruj._lis._stu._pro.'),
    'weekdays' => explode('_','nedjelja_ponedjeljak_utorak_srijeda_četvrtak_petak_subota'),
    'weekdaysShort' => explode('_','ned._pon._uto._sri._čet._pet._sub.'),
    'weekDaysMin' => explode('_','ne_po_ut_sr_če_pe_su'),
    'meridiem'=> ['AM','PM'],
    'meridiemShort'=>['A','P'],
    'formats' => [
        'LT' => 'H:mm',
        'L' => 'DD. MM. YYYY',
        'LL' => 'D. MMMM YYYY',
        'LLL' => 'D. MMMM YYYY LT',
        'LLLL' => 'dddd, D. MMMM YYYY LT'
    ]
];