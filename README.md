# Feiertags-API

Eine leichtgewichtige und performante PHP-API zur Berechnung von gesetzlichen und regionalen Feiertagen für Deutschland (DE), Österreich (AT), Schweiz (CH) und Polen (PL).

Die API berechnet sowohl fixe Feiertage (z. B. Neujahr) als auch bewegliche Feiertage basierend auf der Osterformel. Zusätzlich werden regionale Besonderheiten wie der Buß- und Bettag in Sachsen oder kantonale Feiertage in der Schweiz unterstützt.

## Features

- **Länderunterstützung:** DE, AT, CH, PL.
- **Regionale Feiertage:** Unterstützung für Bundesländer (DE) und Kantone (CH).
- **Flexible Zeiträume:** Berechnungen im Bereich von 1900 bis 2100 möglich.
- **Export-Formate:**
    - **JSON:** Für die Integration in Web-Apps.
    - **ICS (iCalendar):** Zum Import in Kalender wie Outlook, Google Calendar oder Apple Calendar.
    - **CSV:** Für Tabellenkalkulationen.
- **Übersetzungen:** Unterstützung für übersetzte Feiertagsnamen (z. B. Polnisch).
- **Demo-Oberfläche:** Integrierter JavaScript-Kalender (FullCalendar) zur Visualisierung. Eine Live-Demo finden Sie unter: [https://feiertag-api.demo-seite.com/](https://feiertag-api.demo-seite.com/)

## Systemvoraussetzungen

- PHP 8.1 oder höher
- Aktivierte PHP-Erweiterungen: `mbstring`, `calendar`
- Webserver (Apache mit `mod_rewrite` empfohlen oder Nginx)

## Installation

1. Klonen Sie das Repository in Ihr Web-Verzeichnis:
   ```bash
   git clone https://github.com/ztatement/apis.feiertage.git
   ```
2. Konfigurieren Sie die Anwendung in der `config.php`:
   - Passen Sie ggf. den `api_key` an.
   - Konfigurieren Sie die `cors_whitelist` für externe Zugriffe.
3. Stellen Sie sicher, dass das Verzeichnis `.logs/` für den Webserver beschreibbar ist.

## Fehlerbehandlung und Logging

Die API ist so konfiguriert, dass Fehler und Ausnahmen abgefangen und in einem konsistenten Format zurückgegeben werden:

- **API-Endpunkt (`feiertag.api.php`):** Bei Fehlern wird ein HTTP-Statuscode `400 Bad Request` gesetzt und eine JSON-Antwort mit der Fehlermeldung (`{"error": "Nachricht"}`) zurückgegeben.
- **ICS-Export (`ics.php`):** Bei Fehlern wird ein HTTP-Statuscode `400 Bad Request` gesetzt und eine einfache Textnachricht mit der Fehlermeldung zurückgegeben.

Für die Protokollierung von Fehlern und Debug-Informationen werden Log-Dateien verwendet. Die Pfade sind in der `config.php` definiert:
- `LOG_FILE`: Allgemeine Log-Datei.
- `LOG_FILE_DEBUG`: Spezifische Log-Datei für Debug-Informationen.
- `LOG_FILE_ERROR`: Spezifische Log-Datei für Fehler.

Stellen Sie sicher, dass das Verzeichnis `.logs/` (definiert als `LOG_DIR` in `config.php`) vom Webserver beschreibbar ist, damit Log-Einträge geschrieben werden können.

## Pretty URLs (Apache & Nginx)

Die API unterstützt "schöne" URLs, um die Integration zu vereinfachen.

**Beispiele:**
- **Alle Länder:** `/api/countries`
- **Regionen von DE:** `/api/DE/regions`
- **Feiertage DE 2026:** `/api/2026/DE`
- **Feiertage Bayern 2026:** `/api/2026/DE/BY`
- **ICS Export DE 2026:** `/api/2026/DE/ics`
- **ICS Export Bayern 2026:** `/api/2026/DE/BY/ics`

### Apache
Die Konfiguration befindet sich bereits in der `.htaccess`-Datei. Falls das Projekt in einem Unterverzeichnis liegt, passen Sie bitte die `RewriteBase` an (z. B. `RewriteBase /apis/feiertage/`).

### Nginx
Fügen Sie die Regeln aus der `nginx.conf` zu Ihrem `server`-Block hinzu. Achten Sie darauf, die Pfade anzupassen, falls die API nicht im Root-Verzeichnis Ihrer Domain liegt.

## API-Dokumentation

### 1. Feiertage abrufen (JSON)
**Endpunkt:** `feiertag.api.php`

**Parameter:**
- `year` (int): Das Jahr (1900-2100). Standard: aktuelles Jahr.
- `country` (string): ISO-Ländercode (DE, AT, CH, PL). Standard: DE.
- `region` (string, optional): Regionaler Code (z.B. `BY` für Bayern, `ZH` für Zürich).

**Beispiel:**
`feiertag.api.php?year=2026&country=DE&region=BY`

### 2. Regionen abrufen
**Endpunkt:** `feiertag.api.php?action=regions&country=DE`

Gibt eine Liste der verfügbaren Regionen für das angegebene Land zurück.

### 3. ICS-Export
**Endpunkt:** `ics.php`

Erzeugt einen Datei-Download im iCalendar-Format. Akzeptiert die gleichen Parameter wie der JSON-Endpunkt.

**Beispiel:**
`ics.php?year=2026&country=CH&region=GE`

## Projektstruktur

- `/assets`: CSS- und JavaScript-Dateien für die Demo-Seite.
- `/classes/core`:
    - `Feiertage.php`: Die Hauptlogik zur Berechnung der Daten.
    - `Functions.php`: Hilfsfunktionen für Pfade und Protokolle.
- `config.php`: Zentrale Konfigurationseinstellungen.
- `feiertag.api.php`: API-Einstiegspunkt für JSON-Anfragen.
- `ics.php`: Export-Script für Kalenderdateien.
- `index.php`: Die grafische Demo-Oberfläche.

## Integration (Beispiel PHP)

```php
use FTA\Core\Feiertage;

$api = new Feiertage(2026, 'DE', 'SN');
$holidays = $api->getFeiertage();

foreach ($holidays as $name => $info) {
    echo "Am {$info['date']} ist {$name} ({$info['type']})\n";
}
```

## Lizenz

Dieses Projekt ist unter der MIT-Lizenz lizenziert. Weitere Details finden Sie in der `LICENSE` Datei.

## Autor

**Thomas Boettcher** (@ztatement)
- GitHub: github.com/ztatement