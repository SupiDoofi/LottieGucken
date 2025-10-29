<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test - Lottie Exporter</title>
    <style>
        body {
            font-family: monospace;
            padding: 20px;
            background: #1e1e1e;
            color: #d4d4d4;
        }
        .test-section {
            background: #252526;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid #3e3e42;
        }
        h1, h2 {
            color: #4fc3f7;
        }
        pre {
            background: #1e1e1e;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            border: 1px solid #3e3e42;
        }
        button {
            background: #007acc;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background: #005a9e;
        }
        .status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .status.ok {
            background: #4caf50;
            color: white;
        }
        .status.error {
            background: #f44336;
            color: white;
        }
        .status.warning {
            background: #ff9800;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Lottie Exporter - API Test</h1>

    <div class="test-section">
        <h2>1. PHP-Konfiguration</h2>
        <pre><?php
            echo "PHP Version: " . PHP_VERSION . "\n";
            echo "PHP SAPI: " . php_sapi_name() . "\n";
            echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "\n";
            echo "Post Max Size: " . ini_get('post_max_size') . "\n";
            echo "Memory Limit: " . ini_get('memory_limit') . "\n";
            echo "Max Execution Time: " . ini_get('max_execution_time') . "s\n";
        ?></pre>
    </div>

    <div class="test-section">
        <h2>2. Verzeichnisse</h2>
        <pre><?php
            $dirs = ['uploads' => __DIR__ . '/uploads/', 'exports' => __DIR__ . '/exports/'];
            foreach ($dirs as $name => $path) {
                $exists = is_dir($path);
                $writable = $exists ? is_writable($path) : false;
                $status = $exists && $writable ? 'OK' : 'ERROR';
                echo sprintf("%-10s: %s (exists: %s, writable: %s)\n",
                    $name,
                    $path,
                    $exists ? 'yes' : 'no',
                    $writable ? 'yes' : 'no'
                );
            }
        ?></pre>
    </div>

    <div class="test-section">
        <h2>3. PHP-Extensions</h2>
        <pre><?php
            $extensions = ['gd', 'imagick', 'json', 'zlib'];
            foreach ($extensions as $ext) {
                $loaded = extension_loaded($ext);
                $status = $loaded ? '✓' : '✗';
                echo "$status $ext\n";
            }
        ?></pre>
    </div>

    <div class="test-section">
        <h2>4. Shell-Funktionen</h2>
        <pre><?php
            $shell_enabled = function_exists('shell_exec');
            $disabled_functions = ini_get('disable_functions');
            echo "shell_exec verfügbar: " . ($shell_enabled ? 'ja' : 'nein') . "\n";
            echo "Deaktivierte Funktionen: " . ($disabled_functions ?: 'keine') . "\n";
        ?></pre>
    </div>

    <div class="test-section">
        <h2>5. API-Test: Bibliotheken prüfen</h2>
        <button onclick="testAPI()">API testen</button>
        <pre id="api-result">Klicken Sie auf "API testen"...</pre>
    </div>

    <div class="test-section">
        <h2>6. Export-Bibliotheken (direkte Prüfung)</h2>
        <pre><?php
            require_once __DIR__ . '/api.php';

            try {
                // Direkte Funktion aufrufen
                $libs = checkLibraries();

                echo "Gefundene Bibliotheken:\n\n";
                foreach ($libs as $key => $lib) {
                    $status = $lib['available'] ? '✓' : '✗';
                    $version = isset($lib['version']) ? " ({$lib['version']})" : '';
                    echo "$status {$lib['name']}$version\n";
                    echo "   Formate: " . implode(', ', $lib['formats']) . "\n";
                    if (!$lib['available'] && isset($lib['install_instructions'])) {
                        echo "   Status: Nicht installiert\n";
                    }
                    echo "\n";
                }
            } catch (Exception $e) {
                echo "FEHLER: " . $e->getMessage() . "\n";
            }
        ?></pre>
    </div>

    <div class="test-section">
        <h2>7. Systemkommandos</h2>
        <pre><?php
            if (function_exists('shell_exec')) {
                $commands = [
                    'which convert',
                    'which chromium-browser',
                    'which google-chrome'
                ];

                foreach ($commands as $cmd) {
                    $result = @shell_exec($cmd . ' 2>/dev/null');
                    $result = trim($result ?: 'nicht gefunden');
                    echo "$cmd\n  → $result\n\n";
                }
            } else {
                echo "shell_exec ist nicht verfügbar\n";
            }
        ?></pre>
    </div>

    <script>
        async function testAPI() {
            const resultElement = document.getElementById('api-result');
            resultElement.textContent = 'Teste API...';

            try {
                const response = await fetch('api.php?action=check_libraries');
                const text = await response.text();

                resultElement.textContent = 'HTTP Status: ' + response.status + '\n\n';
                resultElement.textContent += 'Response:\n' + text;

                // Versuche JSON zu parsen
                try {
                    const data = JSON.parse(text);
                    resultElement.textContent += '\n\n=== Geparste Daten ===\n';
                    resultElement.textContent += JSON.stringify(data, null, 2);
                } catch (e) {
                    resultElement.textContent += '\n\nJSON Parse Error: ' + e.message;
                }
            } catch (error) {
                resultElement.textContent = 'Fehler: ' + error.message;
            }
        }
    </script>
</body>
</html>
