<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lottie Animation Exporter</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Lottie Animation Exporter</h1>
            <p>Lottie-Animationen laden, anzeigen und einzelne Frames exportieren</p>
        </header>

        <!-- Bibliotheks-Info -->
        <div id="library-info" class="info-box">
            <h3>Verfügbare Export-Bibliotheken</h3>
            <div id="library-status">
                <p>Prüfe verfügbare Bibliotheken...</p>
            </div>
        </div>

        <!-- Upload-Bereich -->
        <div class="upload-section card">
            <h2>Lottie-Datei laden</h2>
            <div class="upload-area" id="upload-area">
                <input type="file" id="file-input" accept=".json,.tgs" style="display: none;">
                <label for="file-input" class="upload-label">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    <p>Klicken Sie hier oder ziehen Sie eine Datei hierher</p>
                    <p class="file-info">Unterstützte Formate: .json, .tgs</p>
                </label>
            </div>
            <div id="file-name" class="file-name"></div>
        </div>

        <!-- Animation-Player -->
        <div id="player-section" class="player-section card" style="display: none;">
            <h2>Animation</h2>
            <div class="animation-container">
                <div id="lottie-player"></div>
            </div>

            <div class="controls">
                <button id="play-btn" class="btn btn-primary">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                    </svg>
                    Abspielen
                </button>
                <button id="pause-btn" class="btn btn-primary" style="display: none;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="6" y="4" width="4" height="16"></rect>
                        <rect x="14" y="4" width="4" height="16"></rect>
                    </svg>
                    Pause
                </button>
                <button id="stop-btn" class="btn btn-secondary">Stop</button>
            </div>

            <div class="frame-control">
                <label for="frame-slider">Frame: <span id="current-frame">0</span> / <span id="total-frames">0</span></label>
                <input type="range" id="frame-slider" class="frame-slider" min="0" max="100" value="0">
            </div>

            <div class="export-section">
                <button id="export-btn" class="btn btn-success">Frame exportieren</button>
            </div>
        </div>
    </div>

    <!-- Export-Dialog -->
    <div id="export-dialog" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Frame exportieren</h2>
                <button id="close-modal" class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="export-format">Export-Format:</label>
                    <select id="export-format" class="form-control">
                        <option value="svg">SVG (Vektor)</option>
                        <option value="png">PNG</option>
                        <option value="jpg">JPG</option>
                        <option value="gif">GIF</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="export-library">Export-Bibliothek:</label>
                    <select id="export-library" class="form-control">
                        <!-- Wird dynamisch befüllt -->
                    </select>
                    <small class="form-hint">Wählen Sie die Bibliothek für den Export</small>
                </div>

                <div class="form-group" id="resolution-group">
                    <label for="export-width">Breite (px):</label>
                    <input type="number" id="export-width" class="form-control" value="1920" min="1">

                    <label for="export-height">Höhe (px):</label>
                    <input type="number" id="export-height" class="form-control" value="1080" min="1">

                    <label>
                        <input type="checkbox" id="keep-aspect-ratio" checked>
                        Seitenverhältnis beibehalten
                    </label>
                </div>

                <div class="form-group" id="background-group">
                    <label>Hintergrund:</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="background" value="transparent" checked>
                            Transparent
                        </label>
                        <label>
                            <input type="radio" name="background" value="color">
                            Farbe:
                            <input type="color" id="bg-color" value="#ffffff" disabled>
                        </label>
                    </div>
                </div>

                <div id="export-progress" class="progress" style="display: none;">
                    <div class="progress-bar">Exportiere...</div>
                </div>

                <div id="export-error" class="alert alert-error" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button id="cancel-export" class="btn btn-secondary">Abbrechen</button>
                <button id="confirm-export" class="btn btn-success">Exportieren</button>
            </div>
        </div>
    </div>

    <!-- Installationsanleitung-Dialog -->
    <div id="install-dialog" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Fehlende Bibliotheken</h2>
                <button class="close-btn" onclick="document.getElementById('install-dialog').style.display='none'">&times;</button>
            </div>
            <div class="modal-body">
                <div id="install-instructions"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="document.getElementById('install-dialog').style.display='none'">Schließen</button>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
