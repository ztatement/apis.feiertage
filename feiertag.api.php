<?php

/**
  * API für Feiertage
  * 
  * @author Thomas Boettcher @ztatement (github[at]ztatement[dot]com)
  * @copyright (c) 2026 ztatement
  *
  * @version 1.0.0.2026.06.16
  * @file $Id: feiertag.api.php $
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


  // Immer UTF-8 und JSON ausgeben
  header('Content-Type: application/json; charset=utf-8');

  // Fehlerausgabe als JSON
  set_exception_handler(function ($e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
  });

  // Parameter einlesen und validieren
  $year    = isset($_GET['year']) && ctype_digit($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
  $country = isset($_GET['country']) ? strtoupper($_GET['country']) : 'DE';
  $region  = isset($_GET['region']) && $_GET['region'] !== '' ? strtoupper($_GET['region']) : null;
  $action  = $_GET['action'] ?? 'holidays'; // Standardaktion ist 'holidays'

  // Wenn die Aktion 'countries' ist, gib die verfügbaren Länder zurück
  if ($action === 'countries') {
    echo json_encode([
      'countries' => Feiertage::getAvailableCountries()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
  }

  // Wenn die Aktion 'regions' ist, gib die verfügbaren Regionen zurück
  if ($action === 'regions') {
    $regions = Feiertage::getAvailableRegions($country);
    echo json_encode([
      'country' => $country,
      'regions' => $regions
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit; // Beende die Skriptausführung nach der Ausgabe der Regionen
  }

  // Feiertage abrufen
  $feiertage = new Feiertage($year, $country, $region);
  $data = $feiertage->getFeiertage();

  // JSON ausgeben
  echo json_encode([
    'year'    => $year,
    'country' => $country,
    'region'  => $region,
    'count'   => count($data),
    'holidays' => $data
  ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
