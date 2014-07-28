<?php
return [
    'months' => explode('_','Ιανουαρίου_Φεβρουαρίου_Μαρτίου_Απριλίου_Μαΐου_Ιουνίου_Ιουλίου_Αυγούστου_Σεπτεμβρίου_Οκτωβρίου_Νοεμβρίου_Δεκεμβρίου'),
    'monthsShort' => explode('_','Ιαν_Φεβ_Μαρ_Απρ_Μαϊ_Ιουν_Ιουλ_Αυγ_Σεπ_Οκτ_Νοε_Δεκ'),
    'weekdays' => explode('_','Κυριακή_Δευτέρα_Τρίτη_Τετάρτη_Πέμπτη_Παρασκευή_Σάββατο'),
    'weekdaysShort' => explode('_','Κυρ_Δευ_Τρι_Τετ_Πεμ_Παρ_Σαβ'),
    'weekDaysMin' => explode('_','Κυ_Δε_Τρ_Τε_Πε_Πα_Σα'),
    'meridiem'=> ['ΜΜ','ΠΜ'],
    'meridiemShort'=>['μμ','πμ'],
    'formats' => [
        'LT' => 'h:mm A',
        'L' => 'DD/MM/YYYY',
        'LL' => 'D MMMM YYYY',
        'LLL' => 'D MMMM YYYY LT',
        'LLLL' => 'dddd, D MMMM YYYY LT'
    ]
];