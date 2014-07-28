<?php
return [
    'months' => explode('_','януари_февруари_март_април_май_юни_юли_август_септември_октомври_ноември_декември'),
    'monthsShort' => explode('_','янр_фев_мар_апр_май_юни_юли_авг_сеп_окт_ное_дек'),
    'weekdays' => explode('_','неделя_понеделник_вторник_сряда_четвъртък_петък_събота'),
    'weekdaysShort' => explode('_','нед_пон_вто_сря_чет_пет_съб'),
    'weekDaysMin' => explode('_','нд_пн_вт_ср_чт_пт_сб'),
    'meridiem'=> ['AM','PM'],
    'meridiemShort'=>['A','P'],
    'formats' => [
        'LT' => 'H:mm',
        'L' => 'D.MM.YYYY',
        'LL' => 'D MMMM YYYY',
        'LLL' => 'D MMMM YYYY H:mm',
        'LLLL' => 'dddd, D MMMM YYYY H:mm'
    ]
];