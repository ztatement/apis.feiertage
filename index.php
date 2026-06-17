<?php

/**
  * index.php – Startseite & Demo für die Feiertags-API mit FullCalendar
  * 
  * @author Thomas Boettcher @ztatement (github[at]ztatement[dot]com)
  * @copyright (c) 2026 ztatement
  *
  * @version 1.1.2.2026.06.17
  * @file $Id: index.php $
  * @created $Id: 1 Donnerstag, 7. Mai 2026, 06:23:31 GMT+0200Z ztatement $
  *
  * @repository https://github.com/ztatement/apis.feiertage
  * @link https://timezone-api.demo-seite.com
  * @license MIT (https://opensource.org/license/MIT)
  * @see LICENSE
  */

  require_once './config.php';
  require_once './classes/core/Feiertage.php';
  use FTA\Core\Feiertage;


  $year = (int)date('Y'); // Aktuelles Jahr als Standardwert
  $country = 'DE'; // Initial country for the demo links
?>
<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="<?= CHARSET ?>">
  <title>Feiertags-API – Demo mit Kalender</title>
  <link href="<?= BOOTSTRAP_CSS ?>" rel="stylesheet">
  <link href="<?= FULLCALENDAR_CSS ?>" rel="stylesheet">
  <link href="<?= BASE_URL ?>assets/css/style.css?v=<?= ASSETS_VERSION ?>" rel="stylesheet">
</head>

<body>
  <div class="container">

    <!-- Hauptüberschrift der Seite -->
    <h1>Feiertags-API – Beispiele & Live-Kalender</h1>

    <p>Die API liefert Feiertage als JSON zurück. Beispiele:</p>
    <ul>
      <li><a href="feiertag.api.php?year=<?= $year ?>&country=DE">Deutschland <?= $year ?></a></li>
      <li><a href="feiertag.api.php?year=<?= $year ?>&country=DE&region=BE">Berlin <?= $year ?></a></li>
      <li><a href="feiertag.api.php?year=<?= $year ?>&country=PL&lang=PL">Polen <?= $year ?> (PL)</a></li>
      <li><a href="feiertag.api.php?year=<?= $year ?>&country=CH&region=ZH">Schweiz – Zürich <?= $year ?></a></li>
    </ul>

    <!-- Live-Demo Formular zur Auswahl von Jahr, Land und Region -->
    <h2>Live-Demo</h2>
    <div class="flex">
      <label>Jahr:
        <input type="number" id="year" value="<?= $year ?>" min="1900" max="2100">
      </label>
      <label>Land:
        <select id="country">
          <?php foreach (Feiertage::getAvailableCountries() as $code => $name): ?>
            <option value="<?= $code ?>" <?= $code === $country ? 'selected' : '' ?>><?= $name ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Region/Kanton:
        <select id="region">
          <option value="">(optional)</option>
        </select>
      </label>
      <label>Sprache:
        <select id="language">
          <?php $defaultLang = 'DE'; ?>
          <?php foreach (Feiertage::getAvailableLanguages() as $code => $name): ?>
            <option value="<?= $code ?>" <?= $code === $defaultLang ? 'selected' : '' ?>><?= $name ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>

    <!-- Anzeige der generierten API-URL -->
    <div id="apiUrlDisplay" class="alert alert-info py-2" style="display:none; font-size: 0.85rem; word-break: break-all;"></div>
    <!-- Bereich für die Feiertagstabelle und Export-Buttons -->
    <div id="result"></div>
    <!-- Bereich für die Rohdaten-Ausgabe im JSON-Format -->
    <div class="json-output">
      <h3>Rohdaten (JSON)</h3>
      <pre id="jsonData"></pre>
      <button id="toggleJsonBtn" class="btn btn-link btn-sm p-0" style="display:none; text-decoration:none;">Mehr anzeigen</button>
    </div>

    <!-- FullCalendar-Container -->
    <h2 class="mt-3">Kalenderansicht</h2>
    <div id="calendar"></div>
    
    <!-- Legende für den Kalender -->
    <div class="calendar-legend">
      <div class="legend-item"><span class="legend-box event-national"></span> Nationaler Feiertag</div>
      <div class="legend-item"><span class="legend-box event-regional"></span> Regionaler Feiertag</div>
      <div class="legend-item"><span class="legend-box event-combined"></span> Überlappung (National & Regional)</div>
    </div>

    <!-- Einbindung der FullCalendar JavaScript-Bibliothek und des eigenen Skripts -->
    <script src="<?= FULLCALENDAR_JS ?>"></script>
    <script src="<?= BASE_URL ?>assets/js/calendar-demo.js?v=<?= ASSETS_VERSION ?>"></script>

    <div class="mt-4 mb-5 text-center">
      <button id="bottomIcsExportBtn" class="btn btn-outline-info">
        Aktuelle Auswahl als ICS-Kalender exportieren
      </button>
    </div>

  </div>
</body>

</html>