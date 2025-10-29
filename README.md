# Lottie Animation Exporter

Ein PHP-basiertes Web-Tool zum Laden, Anzeigen und Exportieren von Lottie-Animationen. Ermöglicht das Exportieren einzelner Frames in verschiedenen Formaten (SVG, PNG, JPG, GIF) mit konfigurierbarer Auflösung und Hintergrund-Optionen.

## Features

- **Datei-Upload**: Unterstützt .json und .tgs (komprimierte Telegram Sticker) Formate
- **Animation-Player**: Browser-basierte Wiedergabe mit vollständiger Steuerung
  - Play/Pause/Stop-Funktionen
  - Frame-by-Frame Navigation per Slider
  - Anzeige von aktueller Frame-Nummer und Gesamt-Frame-Anzahl
- **Export-Funktionen**:
  - **Formate**: SVG (Vektor), PNG, JPG, GIF
  - **Auflösung**: Frei konfigurierbar mit optionaler Seitenverhältnis-Beibehaltung
  - **Hintergrund**: Transparent oder benutzerdefinierte Farbe
  - **Bibliotheks-Auswahl**: Automatische oder manuelle Wahl der Export-Engine
- **Bibliotheks-Management**:
  - Automatische Erkennung verfügbarer Export-Bibliotheken
  - Status-Anzeige in übersichtlichem Info-Panel
  - Installationsanleitungen für fehlende Bibliotheken
- **Responsive Design**: Optimiert für Desktop, Tablet und Mobile
- **Dark Mode**: Automatische Anpassung an Systemeinstellungen

## Systemanforderungen

### Mindestanforderungen
- PHP 7.4 oder höher
- Apache oder Nginx Webserver
- Moderne Browser mit JavaScript-Unterstützung

### Empfohlene Bibliotheken

Für optimale Funktionalität sollten folgende Bibliotheken installiert sein:

#### 1. Chrome/Chromium (nur für GIF-Export)
```bash
# Ubuntu/Debian
sudo apt-get install chromium-browser

# CentOS/RHEL
sudo yum install chromium

# macOS
brew install --cask google-chrome
```

**Hinweis:** Chrome wird nur für GIF-Export benötigt. SVG, PNG und JPG funktionieren ohne Chrome direkt im Browser.

**Server-Umgebungen:** Das Script verwendet spezielle Headless-Flags (`--no-sandbox`, `--disable-dev-shm-usage`, etc.), um Chrome auch in eingeschränkten Umgebungen ohne Desktop-Session oder DBus zu betreiben.

#### 2. ImageMagick (für Bildkonvertierung)
```bash
# Ubuntu/Debian
sudo apt-get install imagemagick php-imagick

# CentOS/RHEL
sudo yum install ImageMagick php-imagick

# macOS
brew install imagemagick
pecl install imagick
```

#### 3. GD Library (Fallback für Bildbearbeitung)
```bash
# Ubuntu/Debian
sudo apt-get install php-gd

# CentOS/RHEL
sudo yum install php-gd
```

**Nach Installation von PHP-Extensions Webserver neu starten:**
```bash
# Apache
sudo systemctl restart apache2

# Nginx mit PHP-FPM
sudo systemctl restart php7.4-fpm
sudo systemctl restart nginx
```

## Installation

1. **Repository klonen oder herunterladen**:
   ```bash
   git clone https://github.com/SupiDoofi/LottieGucken.git
   cd LottieGucken
   ```

2. **Verzeichnisberechtigungen setzen**:
   ```bash
   chmod 755 uploads exports
   chmod 644 *.php
   ```

3. **Webserver konfigurieren**:

   **Apache**: Stellen Sie sicher, dass `.htaccess` aktiviert ist:
   ```apache
   <Directory /path/to/LottieGucken>
       AllowOverride All
   </Directory>
   ```

   **Nginx**: Fügen Sie zur Server-Konfiguration hinzu:
   ```nginx
   location ~ \.php$ {
       fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
       fastcgi_index index.php;
       include fastcgi_params;
       fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
   }

   location ~ ^/(uploads|exports)/ {
       deny all;
       return 403;
   }
   ```

4. **Zugriff testen**:
   Öffnen Sie `http://localhost/LottieGucken/` oder die entsprechende URL im Browser.

## Verwendung

### 1. Animation laden

- Klicken Sie auf den Upload-Bereich oder ziehen Sie eine Datei per Drag & Drop
- Unterstützte Formate: `.json` (Standard Lottie) und `.tgs` (Telegram Sticker)
- Die Animation wird automatisch geladen und angezeigt

### 2. Animation steuern

- **Abspielen/Pause**: Starten und pausieren der Animation
- **Stop**: Animation stoppen und zu Frame 0 zurückkehren
- **Frame-Slider**: Einzelne Frames direkt ansteuern

### 3. Frame exportieren

1. Gewünschten Frame mit dem Slider auswählen
2. Auf "Frame exportieren" klicken
3. Export-Dialog öffnet sich mit folgenden Optionen:
   - **Format**: SVG, PNG, JPG oder GIF
   - **Bibliothek**: Automatisch oder spezifische Engine wählen
   - **Auflösung**: Breite und Höhe in Pixeln (nicht für SVG)
   - **Seitenverhältnis**: Option zum Beibehalten des Original-Verhältnisses
   - **Hintergrund**: Transparent oder benutzerdefinierte Farbe
4. Auf "Exportieren" klicken
5. Datei wird automatisch heruntergeladen

## Projektstruktur

```
LottieGucken/
├── index.php              # Haupt-Weboberfläche
├── api.php                # Backend-API für Upload und Export
├── .htaccess              # Apache-Konfiguration
├── README.md              # Diese Datei
├── assets/
│   ├── css/
│   │   └── style.css      # Responsive Stylesheet
│   └── js/
│       └── app.js         # Frontend-JavaScript
├── uploads/               # Temporäre Upload-Dateien
└── exports/               # Exportierte Frames
```

## Technische Details

### Unterstützte Export-Bibliotheken

| Bibliothek | Formate | Qualität | Performance | Hinweis |
|------------|---------|----------|-------------|---------|
| Browser (Canvas) | PNG, JPG, SVG | Excellent | Excellent | **Standard** - keine Installation nötig |
| Chrome/Chromium | GIF | Sehr gut | Gut | Optional für GIF-Export |
| ImageMagick (PHP) | PNG, JPG, GIF | Sehr gut | Sehr gut | Optional für Formatkonvertierung |
| GD Library | PNG, JPG | Gut | Excellent | Optional für Formatkonvertierung |

**Empfehlung:** Für die meisten Anwendungsfälle ist keine zusätzliche Bibliothek erforderlich. SVG, PNG und JPG werden direkt im Browser exportiert und funktionieren ohne Server-Abhängigkeiten.

### Dateigrößen-Limits

- Standard Upload-Limit: 50 MB
- Maximale Auflösung: Nur durch verfügbaren RAM begrenzt
- Export-Timeout: 300 Sekunden

Diese Werte können in der `.htaccess` oder `php.ini` angepasst werden.

## Sicherheit

- Upload-Validierung auf Dateiendungen und JSON-Struktur
- Schutz der Upload- und Export-Verzeichnisse vor direktem Zugriff
- Automatische Bereinigung temporärer Dateien
- Keine Ausführung von hochgeladenem Code möglich

## Fehlerbehebung

### "Keine Export-Bibliothek verfügbar"
- Installieren Sie mindestens eine der empfohlenen Bibliotheken
- Prüfen Sie mit `php -m | grep -i imagick` bzw. `php -m | grep -i gd`
- Starten Sie den Webserver nach Installation neu

### "Export fehlgeschlagen"
- Prüfen Sie die Browser-Konsole auf detaillierte Fehlermeldungen
- Stellen Sie sicher, dass `exports/` beschreibbar ist: `chmod 755 exports`
- Erhöhen Sie `memory_limit` in `php.ini` bei sehr großen Animationen

### "Screenshot-Erstellung fehlgeschlagen" (GIF-Export)
- Stellen Sie sicher, dass Chrome/Chromium installiert ist
- Prüfen Sie den Pfad mit: `which chromium-browser` oder `which google-chrome`
- Chrome benötigt ggf. zusätzliche Dependencies: `sudo apt-get install libnss3`
- Bei DBus-Fehlern: Die zusätzlichen Headless-Flags (--no-sandbox, etc.) sollten diese unterdrücken
- **Hinweis:** PNG und JPG funktionieren ohne Chrome direkt im Browser

### Animation wird nicht angezeigt
- Prüfen Sie die Browser-Konsole auf JavaScript-Fehler
- Stellen Sie sicher, dass die Lottie-Web-Library geladen wurde
- Testen Sie mit einer bekannten funktionierenden Lottie-Datei

## Browser-Kompatibilität

- Chrome/Chromium: Vollständig unterstützt
- Firefox: Vollständig unterstützt
- Safari: Vollständig unterstützt
- Edge: Vollständig unterstützt
- Mobile Browser: Unterstützt (responsive Design)

## Lizenz

Dieses Projekt ist Open Source und frei verwendbar.

## Mitwirkende

Entwickelt für die Bearbeitung und den Export von Lottie-Animationen.

## Support

Bei Fragen oder Problemen erstellen Sie bitte ein Issue im GitHub-Repository.

## Weiterentwicklung

Geplante Features:
- Batch-Export mehrerer Frames
- Animation als Video exportieren
- Anpassung von Animationsparametern vor Export
- Cloud-Storage-Integration
- API für automatisierte Verarbeitung

## Credits

- [Lottie-Web](https://github.com/airbnb/lottie-web) von Airbnb
- Icons von [Feather Icons](https://feathericons.com/)
