<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lancement officiel
    |--------------------------------------------------------------------------
    |
    | Tant que cette date n'est pas atteinte ET que APP_ENV=production, le site
    | affiche la page de compte à rebours. La date est lue dans le fuseau horaire
    | ci-dessous (Madagascar = UTC+3, sans heure d'été).
    |
    */

    'date' => env('LANCEMENT_DATE', '2026-10-05 08:00:00'),

    'fuseau' => env('LANCEMENT_FUSEAU', 'Indian/Antananarivo'),

    // Clé facultative : ouvrir /?acces=LA_CLE donne accès au vrai site à l'équipe
    // (cookie de 7 jours). Laisser vide pour désactiver.
    'cle_acces' => env('LANCEMENT_CLE'),

];