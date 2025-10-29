<?php
/**
 * Lottie Animation Exporter - Konfiguration
 * Zentrale Konfigurationseinstellungen
 */

// Fehleranzeige (für Produktion auf false setzen)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Verzeichnis-Pfade
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('EXPORT_DIR', __DIR__ . '/exports/');

// Upload-Einstellungen
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50 MB
define('ALLOWED_EXTENSIONS', ['json', 'tgs']);

// Export-Einstellungen
define('DEFAULT_EXPORT_WIDTH', 1920);
define('DEFAULT_EXPORT_HEIGHT', 1080);
define('MAX_EXPORT_WIDTH', 4096);
define('MAX_EXPORT_HEIGHT', 4096);

// Timeout für Export-Operationen (Sekunden)
define('EXPORT_TIMEOUT', 300);

// Automatische Bereinigung alter Dateien
define('AUTO_CLEANUP', true);
define('CLEANUP_AGE', 3600); // 1 Stunde in Sekunden

// Chrome/Chromium Pfade (werden automatisch erkannt, können hier überschrieben werden)
define('CHROME_PATH', null); // null = automatische Erkennung

/**
 * Automatische Bereinigung ausführen
 */
function autoCleanup() {
    if (!AUTO_CLEANUP) {
        return;
    }

    $directories = [UPLOAD_DIR, EXPORT_DIR];
    $cutoff = time() - CLEANUP_AGE;

    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            continue;
        }

        $files = glob($dir . '*');
        foreach ($files as $file) {
            if (is_file($file) && basename($file) !== '.gitkeep' && filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }
}

// Cleanup bei jedem API-Aufruf ausführen (mit 10% Wahrscheinlichkeit)
if (basename($_SERVER['PHP_SELF']) === 'api.php' && rand(1, 10) === 1) {
    autoCleanup();
}
