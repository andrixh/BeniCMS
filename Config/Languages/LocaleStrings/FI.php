<?php
return [
    'months' => explode('_','tammikuu_helmikuu_maaliskuu_huhtikuu_toukokuu_kesäkuu_heinäkuu_elokuu_syyskuu_lokakuu_marraskuu_joulukuu'),
    'monthsShort' => explode('_','tammi_helmi_maalis_huhti_touko_kesä_heinä_elo_syys_loka_marras_joulu'),
    'weekdays' => explode('_','sunnuntai_maanantai_tiistai_keskiviikko_torstai_perjantai_lauantai'),
    'weekdaysShort' => explode('_','su_ma_ti_ke_to_pe_la'),
    'weekDaysMin' => explode('_','su_ma_ti_ke_to_pe_la'),
    'meridiem'=> ['AM','PM'],
    'meridiemShort'=>['A','P'],
    'formats' => [
        'LT' => 'HH.mm',
        'L' => 'DD.MM.YYYY',
        'LL' => 'Do MMMM[ta] YYYY',
        'LLL' => 'Do MMMM[ta] YYYY, [klo] LT',
        'LLLL' => 'dddd, Do MMMM[ta] YYYY, [klo] LT'
    ]
];