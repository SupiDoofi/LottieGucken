<?php
/**
 * Lottie Animation Exporter - API
 * Verarbeitet Anfragen für Bibliotheksprüfung und Export
 */

// Konfiguration laden
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

// Fehlerbehandlung
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

/**
 * Prüft verfügbare Export-Bibliotheken
 */
function checkLibraries() {
    $libraries = [];

    // ImageMagick prüfen
    $imagickAvailable = extension_loaded('imagick');
    if ($imagickAvailable) {
        $libraries['imagick'] = [
            'name' => 'ImageMagick (PHP Extension)',
            'available' => true,
            'formats' => ['png', 'jpg', 'gif'],
            'version' => phpversion('imagick')
        ];
    } else {
        $libraries['imagick'] = [
            'name' => 'ImageMagick (PHP Extension)',
            'available' => false,
            'formats' => ['png', 'jpg', 'gif'],
            'install_instructions' => getImageMagickInstructions()
        ];
    }

    // GD Library prüfen
    $gdAvailable = extension_loaded('gd');
    if ($gdAvailable) {
        $libraries['gd'] = [
            'name' => 'GD Library',
            'available' => true,
            'formats' => ['png', 'jpg'],
            'version' => GD_VERSION
        ];
    } else {
        $libraries['gd'] = [
            'name' => 'GD Library',
            'available' => false,
            'formats' => ['png', 'jpg'],
            'install_instructions' => getGDInstructions()
        ];
    }

    // ImageMagick Command Line prüfen
    $convertPath = trim(shell_exec('which convert 2>/dev/null'));
    if (!empty($convertPath)) {
        $version = trim(shell_exec('convert -version 2>/dev/null | head -n1'));
        $libraries['imagemagick_cli'] = [
            'name' => 'ImageMagick (CLI)',
            'available' => true,
            'formats' => ['png', 'jpg', 'gif'],
            'version' => $version,
            'path' => $convertPath
        ];
    } else {
        $libraries['imagemagick_cli'] = [
            'name' => 'ImageMagick (CLI)',
            'available' => false,
            'formats' => ['png', 'jpg', 'gif'],
            'install_instructions' => getImageMagickCLIInstructions()
        ];
    }

    // Chrome/Chromium für SVG-Export und Screenshot prüfen
    $chromePaths = [
        '/usr/bin/google-chrome',
        '/usr/bin/chromium',
        '/usr/bin/chromium-browser',
        '/snap/bin/chromium'
    ];

    $chromePath = null;
    foreach ($chromePaths as $path) {
        if (file_exists($path)) {
            $chromePath = $path;
            break;
        }
    }

    if ($chromePath) {
        $version = trim(shell_exec("$chromePath --version 2>/dev/null"));
        $libraries['chrome'] = [
            'name' => 'Chrome/Chromium',
            'available' => true,
            'formats' => ['png', 'jpg', 'svg'],
            'version' => $version,
            'path' => $chromePath
        ];
    } else {
        $libraries['chrome'] = [
            'name' => 'Chrome/Chromium',
            'available' => false,
            'formats' => ['png', 'jpg', 'svg'],
            'install_instructions' => getChromeInstructions()
        ];
    }

    return $libraries;
}

/**
 * Installationsanweisungen für ImageMagick PHP Extension
 */
function getImageMagickInstructions() {
    return [
        'title' => 'ImageMagick PHP Extension installieren',
        'steps' => [
            'Ubuntu/Debian: <code>sudo apt-get install php-imagick</code>',
            'CentOS/RHEL: <code>sudo yum install php-imagick</code>',
            'macOS: <code>brew install imagemagick && pecl install imagick</code>',
            'Webserver nach Installation neu starten'
        ]
    ];
}

/**
 * Installationsanweisungen für GD Library
 */
function getGDInstructions() {
    return [
        'title' => 'GD Library installieren',
        'steps' => [
            'Ubuntu/Debian: <code>sudo apt-get install php-gd</code>',
            'CentOS/RHEL: <code>sudo yum install php-gd</code>',
            'macOS: GD ist normalerweise standardmäßig enthalten',
            'Webserver nach Installation neu starten'
        ]
    ];
}

/**
 * Installationsanweisungen für ImageMagick CLI
 */
function getImageMagickCLIInstructions() {
    return [
        'title' => 'ImageMagick (Command Line) installieren',
        'steps' => [
            'Ubuntu/Debian: <code>sudo apt-get install imagemagick</code>',
            'CentOS/RHEL: <code>sudo yum install ImageMagick</code>',
            'macOS: <code>brew install imagemagick</code>'
        ]
    ];
}

/**
 * Installationsanweisungen für Chrome/Chromium
 */
function getChromeInstructions() {
    return [
        'title' => 'Chrome/Chromium installieren',
        'steps' => [
            'Ubuntu/Debian: <code>sudo apt-get install chromium-browser</code>',
            'CentOS/RHEL: <code>sudo yum install chromium</code>',
            'macOS: <code>brew install --cask google-chrome</code>',
            'Alternative: <a href="https://www.google.com/chrome/" target="_blank">Chrome herunterladen</a>'
        ]
    ];
}

/**
 * Verarbeitet Datei-Upload
 */
function handleUpload() {
    if (!isset($_FILES['file'])) {
        throw new Exception('Keine Datei hochgeladen');
    }

    $file = $_FILES['file'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, ['json', 'tgs'])) {
        throw new Exception('Ungültiges Dateiformat. Nur .json und .tgs sind erlaubt.');
    }

    // Eindeutigen Dateinamen generieren
    $uploadDir = __DIR__ . '/uploads/';
    $fileName = uniqid('lottie_') . '.' . $extension;
    $targetPath = $uploadDir . $fileName;

    // Bei .tgs: Dekomprimieren
    if ($extension === 'tgs') {
        $compressed = file_get_contents($file['tmp_name']);
        $decompressed = gzdecode($compressed);
        if ($decompressed === false) {
            throw new Exception('Fehler beim Dekomprimieren der .tgs-Datei');
        }
        file_put_contents($targetPath, $decompressed);
    } else {
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Fehler beim Hochladen der Datei');
        }
    }

    // JSON validieren
    $jsonContent = file_get_contents($targetPath);
    $jsonData = json_decode($jsonContent);
    if (json_last_error() !== JSON_ERROR_NONE) {
        unlink($targetPath);
        throw new Exception('Ungültige JSON-Datei: ' . json_last_error_msg());
    }

    return [
        'success' => true,
        'file' => $fileName,
        'animation_data' => $jsonData
    ];
}

/**
 * Exportiert einen Frame
 */
function exportFrame() {
    $animationData = json_decode($_POST['animation_data'] ?? '{}');
    $frame = intval($_POST['frame'] ?? 0);
    $format = $_POST['format'] ?? 'png';
    $library = $_POST['library'] ?? 'auto';
    $width = intval($_POST['width'] ?? 1920);
    $height = intval($_POST['height'] ?? 1080);
    $background = $_POST['background'] ?? 'transparent';
    $bgColor = $_POST['bg_color'] ?? '#ffffff';

    if (empty($animationData)) {
        throw new Exception('Keine Animationsdaten vorhanden');
    }

    // Temporäres HTML für Rendering erstellen
    $tempDir = __DIR__ . '/exports/';
    $tempId = uniqid('export_');
    $htmlFile = $tempDir . $tempId . '.html';

    // HTML-Template für Rendering
    $html = createRenderHTML($animationData, $frame, $width, $height, $background, $bgColor);
    file_put_contents($htmlFile, $html);

    try {
        $outputFile = null;

        switch ($format) {
            case 'svg':
                $outputFile = exportToSVG($htmlFile, $tempId, $tempDir);
                break;
            case 'png':
                $outputFile = exportToRaster($htmlFile, $tempId, $tempDir, 'png', $library, $width, $height);
                break;
            case 'jpg':
                $outputFile = exportToRaster($htmlFile, $tempId, $tempDir, 'jpg', $library, $width, $height);
                break;
            case 'gif':
                $outputFile = exportToRaster($htmlFile, $tempId, $tempDir, 'gif', $library, $width, $height);
                break;
            default:
                throw new Exception('Ungültiges Format');
        }

        // Temporäre HTML-Datei löschen
        @unlink($htmlFile);

        return [
            'success' => true,
            'file' => basename($outputFile),
            'download_url' => 'exports/' . basename($outputFile)
        ];
    } catch (Exception $e) {
        @unlink($htmlFile);
        throw $e;
    }
}

/**
 * Erstellt HTML für Rendering
 */
function createRenderHTML($animationData, $frame, $width, $height, $background, $bgColor) {
    $bgStyle = $background === 'transparent' ? 'transparent' : $bgColor;
    $animationJson = json_encode($animationData);

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: $bgStyle;
            overflow: hidden;
        }
        #lottie {
            width: {$width}px;
            height: {$height}px;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
</head>
<body>
    <div id="lottie"></div>
    <script>
        var animationData = $animationJson;
        var animation = lottie.loadAnimation({
            container: document.getElementById('lottie'),
            renderer: 'svg',
            loop: false,
            autoplay: false,
            animationData: animationData
        });
        animation.goToAndStop($frame, true);
    </script>
</body>
</html>
HTML;
}

/**
 * Export als SVG
 */
function exportToSVG($htmlFile, $tempId, $tempDir) {
    $libraries = checkLibraries();

    if (!$libraries['chrome']['available']) {
        throw new Exception('Chrome/Chromium wird für SVG-Export benötigt');
    }

    $chromePath = $libraries['chrome']['path'];
    $outputFile = $tempDir . $tempId . '.svg';

    // Chrome im Headless-Modus verwenden, um SVG zu extrahieren
    // Dies ist eine vereinfachte Version - in der Praxis würden wir Puppeteer verwenden
    $screenshotFile = $tempDir . $tempId . '_temp.png';
    $cmd = escapeshellcmd($chromePath) . ' --headless --disable-gpu --screenshot=' .
           escapeshellarg($screenshotFile) . ' ' . escapeshellarg('file://' . $htmlFile) . ' 2>&1';

    exec($cmd, $output, $returnCode);

    // Für echte SVG-Extraktion müssten wir das SVG-Element aus dem DOM extrahieren
    // Als Workaround geben wir eine Fehlermeldung zurück
    throw new Exception('SVG-Export erfordert erweiterte Browser-Automatisierung (Puppeteer). Bitte verwenden Sie PNG-Export als Alternative.');
}

/**
 * Export als Rasterbild (PNG, JPG, GIF)
 */
function exportToRaster($htmlFile, $tempId, $tempDir, $format, $library, $width, $height) {
    $libraries = checkLibraries();

    // Automatische Bibliotheksauswahl
    if ($library === 'auto') {
        if ($libraries['chrome']['available']) {
            $library = 'chrome';
        } elseif ($libraries['imagick']['available']) {
            $library = 'imagick';
        } elseif ($libraries['gd']['available']) {
            $library = 'gd';
        } else {
            throw new Exception('Keine Export-Bibliothek verfügbar');
        }
    }

    // Mit Chrome/Chromium rendern
    if ($library === 'chrome' && $libraries['chrome']['available']) {
        $chromePath = $libraries['chrome']['path'];
        $outputFile = $tempDir . $tempId . '.' . $format;
        $screenshotFile = $tempDir . $tempId . '_chrome.png';

        // Screenshot mit Chrome erstellen
        $cmd = escapeshellcmd($chromePath) .
               ' --headless --disable-gpu' .
               ' --window-size=' . $width . ',' . $height .
               ' --screenshot=' . escapeshellarg($screenshotFile) .
               ' ' . escapeshellarg('file://' . $htmlFile) . ' 2>&1';

        exec($cmd, $output, $returnCode);

        if (!file_exists($screenshotFile)) {
            throw new Exception('Screenshot-Erstellung fehlgeschlagen: ' . implode("\n", $output));
        }

        // Format konvertieren falls nötig
        if ($format === 'png') {
            rename($screenshotFile, $outputFile);
        } else {
            convertImage($screenshotFile, $outputFile, $format);
            @unlink($screenshotFile);
        }

        return $outputFile;
    }

    throw new Exception("Bibliothek '$library' ist nicht verfügbar oder wird nicht unterstützt");
}

/**
 * Konvertiert Bild zwischen Formaten
 */
function convertImage($inputFile, $outputFile, $format) {
    $libraries = checkLibraries();

    // Mit ImageMagick Extension
    if ($libraries['imagick']['available']) {
        $image = new Imagick($inputFile);
        $image->setImageFormat($format);

        if ($format === 'jpg') {
            $image->setImageBackgroundColor('white');
            $image = $image->flattenImages();
        }

        $image->writeImage($outputFile);
        $image->clear();
        return;
    }

    // Mit GD Library
    if ($libraries['gd']['available'] && in_array($format, ['png', 'jpg'])) {
        $source = imagecreatefrompng($inputFile);

        if ($format === 'jpg') {
            $width = imagesx($source);
            $height = imagesy($source);
            $output = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($output, 255, 255, 255);
            imagefill($output, 0, 0, $white);
            imagecopy($output, $source, 0, 0, 0, 0, $width, $height);
            imagejpeg($output, $outputFile, 90);
            imagedestroy($output);
        } else {
            imagepng($source, $outputFile);
        }

        imagedestroy($source);
        return;
    }

    throw new Exception('Keine Bibliothek für Bildkonvertierung verfügbar');
}

// API-Endpunkte verarbeiten
try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'check_libraries':
            echo json_encode([
                'success' => true,
                'libraries' => checkLibraries()
            ]);
            break;

        case 'upload':
            echo json_encode(handleUpload());
            break;

        case 'export':
            echo json_encode(exportFrame());
            break;

        default:
            throw new Exception('Ungültige Aktion');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
