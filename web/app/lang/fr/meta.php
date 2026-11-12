<?php
// Langue : Français (RDC)
return [
    'name'      => 'French',
    'native'    => 'Français',
    'locale'    => 'fr_CD',
    'dir'       => 'ltr',
    'decimal'   => ',',
    'thousands' => "\u{00A0}",
    'months'       => ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'],
    'months_short' => ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'],
    'days'         => ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'],
    'days_short'   => ['dim.', 'lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.'],
    // Formats d'affichage : clé = format utilisé dans le code, valeur = format français
    'formats' => [
        'M d, Y'       => 'd/m/Y',
        'M d, Y H:i'   => 'd/m/Y H:i',
        'M d, Y h:i A' => 'd/m/Y H:i',
        'M d, H:i'     => 'd/m H:i',
        'M d'          => 'd/m',
        'F Y'          => 'F Y',
        'l, F j, Y'    => 'l j F Y',
    ],
];
