<?php
return [
    'months' => explode('_','Januar_Februar_März_April_Mai_Juni_Juli_August_September_Oktober_November_Dezember'),
    'monthsShort' => explode('_','Jan._Febr._Mrz._Apr._Mai_Jun._Jul._Aug._Sept._Okt._Nov._Dez.'),
    'weekdays' => explode('_','Sonntag_Montag_Dienstag_Mittwoch_Donnerstag_Freitag_Samstag'),
    'weekdaysShort' => explode('_','So._Mo._Di._Mi._Do._Fr._Sa.'),
    'weekDaysMin' => explode('_','So_Mo_Di_Mi_Do_Fr_Sa'),
    'meridiem'=> ['AM','PM'],
    'meridiemShort'=>['A','P'],
    'formats' => [
        'LT' => 'HH:mm [Uhr]',
        'L' => 'DD.MM.YYYY',
        'LL' => 'D. MMMM YYYY',
        'LLL' => 'D. MMMM YYYY LT',
        'LLLL' => 'dddd, D. MMMM YYYY LT'
    ]
];