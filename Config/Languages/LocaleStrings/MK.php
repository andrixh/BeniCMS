<?php
return [
    'months' => explode('_','јануари_февруари_март_април_мај_јуни_јули_август_септември_октомври_ноември_декември'),
    'monthsShort' => explode('_','јан_фев_мар_апр_мај_јун_јул_авг_сеп_окт_ное_дек'),
    'weekdays' => explode('_','недела_понеделник_вторник_среда_четврток_петок_сабота'),
    'weekdaysShort' => explode('_','нед_пон_вто_сре_чет_пет_саб'),
    'weekDaysMin' => explode('_','нe_пo_вт_ср_че_пе_сa'),
    'meridiem'=> ['AM','PM'],
    'meridiemShort'=>['A','P'],
    'formats' => [
        'LT' => 'H:mm',
        'L' => 'D.MM.YYYY',
        'LL' => 'D MMMM YYYY',
        'LLL' => 'D MMMM YYYY LT',
        'LLLL' => 'dddd, D MMMM YYYY LT'
    ]
];