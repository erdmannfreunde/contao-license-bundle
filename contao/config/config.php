<?php

declare(strict_types=1);

// Kein eigener Menüpunkt: Das Modul bleibt registriert (do=license erreichbar),
// wird aber aus der Backend-Navigation ausgeblendet. Erreicht wird es kontextuell
// über den "Lizenz"-Button der jeweiligen Erweiterung (siehe LicenseRedirectController).
$GLOBALS['BE_MOD']['system']['license'] = [
    'tables' => ['tl_license'],
    'hideInNavigation' => true,
];
