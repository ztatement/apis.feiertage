<?php

/**
  * index.php – Startseite & Demo für die Feiertags-API mit FullCalendar
  * 
  * @author Thomas Boettcher @ztatement (github[at]ztatement[dot]com)
  * @copyright (c) 2026 ztatement
  *
  * @version 1.0.0.2026.06.16
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


  $year = (int)date('Y');
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

    <h1>Feiertags-API – Beispiele & Live-Kalender</h1>

    <p>Die API liefert Feiertage als JSON zurück. Beispiele:</p>
    <ul>
      <li><a href="feiertag.api.php?year=<?= $year ?>&country=DE">Deutschland <?= $year ?></a></li>
      <li><a href="feiertag.api.php?year=<?= $year ?>&country=DE&region=BE">Berlin <?= $year ?></a></li>
      <li><a href="feiertag.api.php?year=<?= $year ?>&country=PL">Polen <?= $year ?></a></li>
      <li><a href="feiertag.api.php?year=<?= $year ?>&country=CH&region=ZH">Schweiz – Zürich <?= $year ?></a></li>
    </ul>

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
    </div>

    <div id="apiUrlDisplay" class="alert alert-info py-2" style="display:none; font-size: 0.85rem; word-break: break-all;"></div>
    <div id="result"></div>
    <div class="json-output">
      <h3>Rohdaten (JSON)</h3>
      <pre id="jsonData"></pre>
    </div>

    <h2>Kalenderansicht</h2>
    <div id="calendar"></div>

    <script src="<?= FULLCALENDAR_JS ?>"></script>
    <script src="<?= BASE_URL ?>assets/js/calendar-demo.js?v=<?= ASSETS_VERSION ?>"></script>
    <br>
    <p>Export als ics Datei zB. https://feiertag-api.demo-seite.com/ics.php?year=<?= $year . '&country=' . $country; ?></p>

  </div>
</body>

</html>