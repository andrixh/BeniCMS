<?php
return [
    'months' => explode('_','January_February_March_April_May_June_July_August_September_October_November_December'),
    'monthsShort' => explode('_','Jan_Feb_Mar_Apr_May_Jun_Jul_Aug_Sep_Oct_Nov_Dec'),
    'weekdays' => explode('_','Sunday_Monday_Tuesday_Wednesday_Thursday_Friday_Saturday'),
    'weekdaysShort' => explode('_','Sun_Mon_Tue_Wed_Thu_Fri_Sat'),
    'weekDaysMin' => explode('_','Su_Mo_Tu_We_Th_Fr_Sa'),
    'meridiem'=> ['AM','PM'],
    'meridiemShort'=>['A','P'],
    'formats' => [
        'ST' => 'h:mm tt',
        'LT' => 'HH:mm',
        'L' => 'DD/MM/YYYY',
        'LL' => 'D MMMM YYYY',
        'LLL' => 'D MMMM YYYY LT',
        'LLLL' => 'dddd, D MMMM YYYY LT'
    ]
];