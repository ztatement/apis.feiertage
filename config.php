<?php

/**
  * Zentrale Konfiguration für die Feiertage API.
  * 
  * @author Thomas Boettcher @ztatement (github[at]ztatement[dot]com)
  * @copyright (c) 2026 ztatement
  *
  * @version 1.0.0.2026.06.16
  * @file $Id: config.php $
  * @created $Id: 1 Dienstag, 16. Juni 2026, 05:48:43 GMT+0200Z ztatement $
  *
  * @repository https://github.com/ztatement/apis.feiertage
  * @link https://timezone-api.demo-seite.com
  * @license MIT (https://opensource.org/license/MIT)
  * @see LICENSE
  */

  require_once './classes/core/Functions.php';
  use FTA\Core\Functions;
  
  // Globale Konfiguration
  define( 'CHARSET', 'UTF-8' );
  // define( 'BASE_PATH', dirname( __DIR__ ) . "/" );
  define( 'BASE_PATH', Functions::get_base_path() );
  define( 'BASE_URL', Functions::get_base_url() );

  // App Version für Cache-Busting (bei jeder Änderung an CSS/JS erhöhen)
  define('ASSETS_VERSION', '1.0.7');

  // Logfile
  define( 'LOG_DIR', BASE_PATH . '.logs' . DIRECTORY_SEPARATOR );
  define( 'LOG_PATH', LOG_DIR );
  define( 'LOG_FILE', LOG_DIR . 'fta.log' ); // Allgemeines Log
  define( 'LOG_FILE_DEBUG', LOG_DIR . 'debug.log' ); // Debug-Log
  define( 'LOG_FILE_ERROR', LOG_DIR . 'error.log' ); // Error-Log

  // Bootstrap
  define( 'BOOTSTRAP_CSS', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css' );
  define( 'BOOTSTRAP_JS', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js' );

  // FullCalendar v6
  define( 'FULLCALENDAR_CSS', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' );
  define( 'FULLCALENDAR_JS', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js' );


  return [
    'api_key_enabled' => false,
    'api_key' => 'MEIN-GEHEIMER-SCHLUESSEL-123',
    'cors_enabled' => true,                       // CORS-Unterstützung aktivieren/deaktivieren
    'cors_whitelist' => ['*'],                   // Erlaubte Domains (z.B. ['https://meine-app.de']) oder ['*'] für alle
  ];
