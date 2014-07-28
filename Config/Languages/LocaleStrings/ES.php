<?php
return [
    'months' => explode('_','enero_febrero_marzo_abril_mayo_junio_julio_agosto_septiembre_octubre_noviembre_diciembre'),
    'monthsShort' => explode('_','ene._feb._mar._abr._may._jun._jul._ago._sep._oct._nov._dic.'),
    'weekdays' => explode('_','domingo_lunes_martes_miércoles_jueves_viernes_sábado'),
    'weekdaysShort' => explode('_','dom._lun._mar._mié._jue._vie._sáb.'),
    'weekDaysMin' => explode('_','Do_Lu_Ma_Mi_Ju_Vi_Sá'),
    'meridiem'=> ['AM','PM'],
    'meridiemShort'=>['A','P'],
    'formats' => [
        'LT' => 'H:mm',
        'L' => 'DD/MM/YYYY',
        'LL' => 'D [de] MMMM [del] YYYY',
        'LLL' => 'D [de] MMMM [del] YYYY LT',
        'LLLL' => 'dddd, D [de] MMMM [del] YYYY LT'
    ]
];