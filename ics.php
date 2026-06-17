<?php

  declare(strict_types=1);

/**
  * ICS-Export für Feiertage
  * 
  * @author Thomas Boettcher @ztatement (github[at]ztatement[dot]com)
  * @copyright (c) 2026 ztatement
  *
  * @version 1.1.2.2026.06.17
  * @file $Id: ics.php $
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


  // Fehlerausgabe
  set_exception_handler(function ($e) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Fehler bei der ICS-Generierung: " . $e->getMessage();
    exit;
  });


  // Parameter aus der URL auslesen und validieren
  $year    = isset($_GET['year']) && ctype_digit($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
  $country = isset($_GET['country']) ? strtoupper($_GET['country']) : 'DE';
  $region  = isset($_GET['region']) && $_GET['region'] !== '' ? strtoupper($_GET['region']) : null;
  $lang    = Feiertage::resolveLanguage($_GET['lang'] ?? null);

  // Erstellt eine Instanz der Feiertage-Klasse
  $feiertage = new Feiertage($year, $country, $region);

  // Setzt die HTTP-Header für den Dateidownload
  header('Content-Type: text/calendar; charset=utf-8');
  header('Content-Disposition: attachment; filename="feiertage_'.$year.'_'.$country.($region ? '_'.$region : '').($lang !== 'DE' ? '_'.$lang : '').'.ics"');

  // Gibt den generierten ICS-String aus
  echo $feiertage->toICS($lang);
