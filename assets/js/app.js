/**
 * Lottie Animation Exporter - Frontend JavaScript
 */

// Globale Variablen
let lottieAnimation = null;
let currentAnimationData = null;
let totalFrames = 0;
let currentFrame = 0;
let libraries = {};
let originalWidth = 1920;
let originalHeight = 1080;

// DOM-Elemente
const fileInput = document.getElementById('file-input');
const uploadArea = document.getElementById('upload-area');
const fileName = document.getElementById('file-name');
const playerSection = document.getElementById('player-section');
const lottiePlayer = document.getElementById('lottie-player');
const playBtn = document.getElementById('play-btn');
const pauseBtn = document.getElementById('pause-btn');
const stopBtn = document.getElementById('stop-btn');
const frameSlider = document.getElementById('frame-slider');
const currentFrameDisplay = document.getElementById('current-frame');
const totalFramesDisplay = document.getElementById('total-frames');
const exportBtn = document.getElementById('export-btn');
const exportDialog = document.getElementById('export-dialog');
const closeModal = document.getElementById('close-modal');
const cancelExport = document.getElementById('cancel-export');
const confirmExport = document.getElementById('confirm-export');
const exportFormat = document.getElementById('export-format');
const exportLibrary = document.getElementById('export-library');
const exportWidth = document.getElementById('export-width');
const exportHeight = document.getElementById('export-height');
const keepAspectRatio = document.getElementById('keep-aspect-ratio');
const bgColor = document.getElementById('bg-color');
const backgroundRadios = document.querySelectorAll('input[name="background"]');
const libraryStatus = document.getElementById('library-status');

/**
 * Initialisierung
 */
document.addEventListener('DOMContentLoaded', () => {
    checkLibraries();
    initEventListeners();
});

/**
 * Event Listeners initialisieren
 */
function initEventListeners() {
    // Datei-Upload
    fileInput.addEventListener('change', handleFileSelect);
    uploadArea.addEventListener('dragover', handleDragOver);
    uploadArea.addEventListener('drop', handleDrop);

    // Player-Steuerung
    playBtn.addEventListener('click', () => {
        if (lottieAnimation) {
            lottieAnimation.play();
            playBtn.style.display = 'none';
            pauseBtn.style.display = 'inline-flex';
        }
    });

    pauseBtn.addEventListener('click', () => {
        if (lottieAnimation) {
            lottieAnimation.pause();
            pauseBtn.style.display = 'none';
            playBtn.style.display = 'inline-flex';
        }
    });

    stopBtn.addEventListener('click', () => {
        if (lottieAnimation) {
            lottieAnimation.stop();
            pauseBtn.style.display = 'none';
            playBtn.style.display = 'inline-flex';
            frameSlider.value = 0;
            currentFrame = 0;
            currentFrameDisplay.textContent = '0';
        }
    });

    // Frame-Slider
    frameSlider.addEventListener('input', (e) => {
        currentFrame = parseInt(e.target.value);
        if (lottieAnimation) {
            lottieAnimation.goToAndStop(currentFrame, true);
            currentFrameDisplay.textContent = currentFrame;
        }
    });

    // Export-Button
    exportBtn.addEventListener('click', openExportDialog);

    // Modal-Steuerung
    closeModal.addEventListener('click', closeExportDialog);
    cancelExport.addEventListener('click', closeExportDialog);
    confirmExport.addEventListener('click', performExport);

    // Export-Format-Änderung
    exportFormat.addEventListener('change', updateExportOptions);

    // Hintergrund-Optionen
    backgroundRadios.forEach(radio => {
        radio.addEventListener('change', (e) => {
            bgColor.disabled = e.target.value !== 'color';
        });
    });

    // Seitenverhältnis beibehalten
    exportWidth.addEventListener('input', () => {
        if (keepAspectRatio.checked && originalWidth > 0) {
            const ratio = originalHeight / originalWidth;
            exportHeight.value = Math.round(parseInt(exportWidth.value) * ratio);
        }
    });

    exportHeight.addEventListener('input', () => {
        if (keepAspectRatio.checked && originalHeight > 0) {
            const ratio = originalWidth / originalHeight;
            exportWidth.value = Math.round(parseInt(exportHeight.value) * ratio);
        }
    });

    // Modal außerhalb schließen
    exportDialog.addEventListener('click', (e) => {
        if (e.target === exportDialog) {
            closeExportDialog();
        }
    });
}

/**
 * Verfügbare Bibliotheken prüfen
 */
async function checkLibraries() {
    try {
        const response = await fetch('api.php?action=check_libraries');
        const data = await response.json();

        if (data.success) {
            libraries = data.libraries;
            displayLibraryStatus();
            updateLibraryDropdown();
        }
    } catch (error) {
        console.error('Fehler beim Prüfen der Bibliotheken:', error);
        libraryStatus.innerHTML = '<p class="error">Fehler beim Prüfen der Bibliotheken</p>';
    }
}

/**
 * Bibliotheks-Status anzeigen
 */
function displayLibraryStatus() {
    let html = '<ul class="library-list">';
    let missingLibraries = [];

    for (const [key, lib] of Object.entries(libraries)) {
        const statusIcon = lib.available ? '✓' : '✗';
        const statusClass = lib.available ? 'available' : 'unavailable';
        const versionInfo = lib.version ? ` (${lib.version})` : '';

        html += `<li class="${statusClass}">
            <span class="status-icon">${statusIcon}</span>
            ${lib.name}${versionInfo}
            <span class="formats">${lib.formats.join(', ')}</span>
        </li>`;

        if (!lib.available) {
            missingLibraries.push(lib);
        }
    }

    html += '</ul>';

    if (missingLibraries.length > 0) {
        html += '<button class="btn btn-info btn-sm" onclick="showInstallInstructions()">Installationsanleitung anzeigen</button>';
    }

    libraryStatus.innerHTML = html;
}

/**
 * Installationsanleitung anzeigen
 */
function showInstallInstructions() {
    const missingLibs = Object.values(libraries).filter(lib => !lib.available);

    if (missingLibs.length === 0) {
        return;
    }

    let html = '<div class="install-instructions">';
    html += '<p>Folgende Bibliotheken sind nicht installiert und können für erweiterte Export-Optionen nachinstalliert werden:</p>';

    missingLibs.forEach(lib => {
        if (lib.install_instructions) {
            html += `<div class="install-section">
                <h3>${lib.install_instructions.title}</h3>
                <ol>`;

            lib.install_instructions.steps.forEach(step => {
                html += `<li>${step}</li>`;
            });

            html += `</ol></div>`;
        }
    });

    html += '</div>';

    document.getElementById('install-instructions').innerHTML = html;
    document.getElementById('install-dialog').style.display = 'flex';
}

/**
 * Bibliotheks-Dropdown aktualisieren
 */
function updateLibraryDropdown() {
    const availableLibs = Object.entries(libraries)
        .filter(([key, lib]) => lib.available);

    let html = '<option value="auto">Automatisch wählen</option>';

    availableLibs.forEach(([key, lib]) => {
        html += `<option value="${key}">${lib.name}</option>`;
    });

    exportLibrary.innerHTML = html;
}

/**
 * Drag & Drop Handler
 */
function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    uploadArea.classList.add('drag-over');
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    uploadArea.classList.remove('drag-over');

    const files = e.dataTransfer.files;
    if (files.length > 0) {
        handleFile(files[0]);
    }
}

/**
 * Dateiauswahl Handler
 */
function handleFileSelect(e) {
    const file = e.target.files[0];
    if (file) {
        handleFile(file);
    }
}

/**
 * Datei verarbeiten
 */
async function handleFile(file) {
    const extension = file.name.split('.').pop().toLowerCase();

    if (!['json', 'tgs'].includes(extension)) {
        alert('Bitte wählen Sie eine .json oder .tgs Datei');
        return;
    }

    fileName.innerHTML = `<p>Lade ${file.name}...</p>`;

    try {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'upload');

        const response = await fetch('api.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            currentAnimationData = data.animation_data;
            fileName.innerHTML = `<p>✓ ${file.name} geladen</p>`;
            loadAnimation(data.animation_data);
        } else {
            throw new Error(data.error || 'Upload fehlgeschlagen');
        }
    } catch (error) {
        console.error('Fehler beim Hochladen:', error);
        fileName.innerHTML = `<p class="error">Fehler: ${error.message}</p>`;
    }
}

/**
 * Animation laden und anzeigen
 */
function loadAnimation(animationData) {
    // Vorherige Animation zerstören
    if (lottieAnimation) {
        lottieAnimation.destroy();
    }

    // Player-Bereich anzeigen
    playerSection.style.display = 'block';

    // Animation laden
    lottieAnimation = lottie.loadAnimation({
        container: lottiePlayer,
        renderer: 'svg',
        loop: true,
        autoplay: false,
        animationData: animationData
    });

    // Originalgröße ermitteln
    originalWidth = animationData.w || 1920;
    originalHeight = animationData.h || 1080;

    // Frame-Informationen aktualisieren
    lottieAnimation.addEventListener('DOMLoaded', () => {
        totalFrames = Math.floor(lottieAnimation.totalFrames);
        totalFramesDisplay.textContent = totalFrames;
        frameSlider.max = totalFrames;
        frameSlider.value = 0;
        currentFrame = 0;
        currentFrameDisplay.textContent = '0';

        // Export-Auflösung auf Originalgröße setzen
        exportWidth.value = originalWidth;
        exportHeight.value = originalHeight;
    });

    // Frame-Update bei Wiedergabe
    lottieAnimation.addEventListener('enterFrame', () => {
        currentFrame = Math.floor(lottieAnimation.currentFrame);
        frameSlider.value = currentFrame;
        currentFrameDisplay.textContent = currentFrame;
    });

    // Zur ersten Frame springen
    lottieAnimation.goToAndStop(0, true);
}

/**
 * Export-Dialog öffnen
 */
function openExportDialog() {
    exportDialog.style.display = 'flex';
    updateExportOptions();
}

/**
 * Export-Dialog schließen
 */
function closeExportDialog() {
    exportDialog.style.display = 'none';
    document.getElementById('export-progress').style.display = 'none';
    document.getElementById('export-error').style.display = 'none';
}

/**
 * Export-Optionen basierend auf Format aktualisieren
 */
function updateExportOptions() {
    const format = exportFormat.value;
    const resolutionGroup = document.getElementById('resolution-group');
    const backgroundGroup = document.getElementById('background-group');

    // SVG benötigt keine Auflösung
    if (format === 'svg') {
        resolutionGroup.style.display = 'none';
        backgroundGroup.style.display = 'none';
    } else {
        resolutionGroup.style.display = 'block';
        backgroundGroup.style.display = 'block';
    }
}

/**
 * Export durchführen
 */
async function performExport() {
    const format = exportFormat.value;
    const library = exportLibrary.value;
    const width = parseInt(exportWidth.value);
    const height = parseInt(exportHeight.value);
    const background = document.querySelector('input[name="background"]:checked').value;
    const backgroundColor = bgColor.value;

    // Validierung
    if (format !== 'svg' && (width < 1 || height < 1)) {
        showError('Bitte geben Sie eine gültige Auflösung ein');
        return;
    }

    if (!currentAnimationData) {
        showError('Keine Animation geladen');
        return;
    }

    // Progress anzeigen
    document.getElementById('export-progress').style.display = 'block';
    document.getElementById('export-error').style.display = 'none';
    confirmExport.disabled = true;

    try {
        const formData = new FormData();
        formData.append('action', 'export');
        formData.append('animation_data', JSON.stringify(currentAnimationData));
        formData.append('frame', currentFrame);
        formData.append('format', format);
        formData.append('library', library);
        formData.append('width', width);
        formData.append('height', height);
        formData.append('background', background);
        formData.append('bg_color', backgroundColor);

        const response = await fetch('api.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Download starten
            const link = document.createElement('a');
            link.href = data.download_url;
            link.download = data.file;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Dialog schließen
            setTimeout(() => {
                closeExportDialog();
                confirmExport.disabled = false;
            }, 500);
        } else {
            throw new Error(data.error || 'Export fehlgeschlagen');
        }
    } catch (error) {
        console.error('Fehler beim Export:', error);
        showError(error.message);
        confirmExport.disabled = false;
    }
}

/**
 * Fehlermeldung anzeigen
 */
function showError(message) {
    const errorDiv = document.getElementById('export-error');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    document.getElementById('export-progress').style.display = 'none';
}
