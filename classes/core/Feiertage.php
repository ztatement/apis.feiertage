<?php

  declare(strict_types=1);

/**
  * Feiertags-API
  * 
  * @author Thomas Boettcher @ztatement (github[at]ztatement[dot]com)
  * @copyright (c) 2026 ztatement
  *
  * @version 1.0.0.2026.06.16
  * @file $Id: classes/core/Feiertage.php $
  * @created $Id: 1 Donnerstag, 7. Mai 2026, 06:23:07 GMT+0200Z ztatement $
  *
  * @repository https://github.com/ztatement/apis.feiertage
  * @link https://timezone-api.demo-seite.com
  * @license MIT (https://opensource.org/license/MIT)
  * @see LICENSE
  *
  * -------------------------------
  * Verwendung:
  * require 'Feiertage.php';
  * 
  * // Beispiel: Berlin 2026
  * $ft = new Feiertage(2026, 'DE', 'BE');
  * print_r($ft->getFeiertage());
  * 
  * // Beispiel: Ganz Deutschland ohne regionale Feiertage
  * $ft2 = new Feiertage(2026, 'DE');
  * print_r($ft2->getFeiertage());
  */

  namespace FTA\Core;

  use DateTime;
  use DateTimeZone;
  use Exception;


class Feiertage
{
  private int $year;
  private string $country;
  private ?string $region;
  private DateTimeZone $tz;

  /**
   * Liste der unterstützten Länder.
   * @var array
   */
  private static array $availableCountries = [
    'DE' => 'Deutschland',
    'AT' => 'Österreich',
    'CH' => 'Schweiz',
    'PL' => 'Polen',
  ];

  /**
   * Liste der verfügbaren Regionen/Kantone pro Land.
   * Diese Liste kann erweitert werden, auch wenn noch keine spezifischen Feiertage definiert sind.
   *
   * @var array
   */
  private static array $availableRegions = [
    'DE' => [
      'BW' => 'Baden-Württemberg',
      'BY' => 'Bayern',
      'BE' => 'Berlin',
      'BB' => 'Brandenburg',
      'HB' => 'Bremen',
      'HH' => 'Hamburg',
      'HE' => 'Hessen',
      'MV' => 'Mecklenburg-Vorpommern',
      'NI' => 'Niedersachsen',
      'NW' => 'Nordrhein-Westfalen',
      'RP' => 'Rheinland-Pfalz',
      'SL' => 'Saarland',
      'SN' => 'Sachsen',
      'ST' => 'Sachsen-Anhalt',
      'SH' => 'Schleswig-Holstein',
      'TH' => 'Thüringen',
    ],
    'CH' => [
      'AG' => 'Aargau',
      'AI' => 'Appenzell Innerrhoden',
      'AR' => 'Appenzell Ausserrhoden',
      'BL' => 'Basel-Landschaft',
      'BS' => 'Basel-Stadt',
      'BE' => 'Bern',
      'FR' => 'Freiburg',
      'GE' => 'Genf',
      'GL' => 'Glarus',
      'GR' => 'Graubünden',
      'JU' => 'Jura',
      'LU' => 'Luzern',
      'NE' => 'Neuenburg',
      'NW' => 'Nidwalden',
      'OW' => 'Obwalden',
      'SH' => 'Schaffhausen',
      'SZ' => 'Schwyz',
      'SO' => 'Solothurn',
      'SG' => 'St. Gallen',
      'TG' => 'Thurgau',
      'TI' => 'Tessin',
      'UR' => 'Uri',
      'VS' => 'Wallis',
      'VD' => 'Waadt',
      'ZG' => 'Zug',
      'ZH' => 'Zürich',
    ],
  ];

  public function __construct(int $year, string $country = 'DE', ?string $region = null, string $timezone = 'Europe/Berlin')
  {
    if ($year < 1900 || $year > 2100) {
      throw new Exception("Das Jahr muss zwischen 1900 und 2100 liegen.");
    }
    $this->year = $year;
    $this->country = strtoupper($country);
    $this->region = $region ? strtoupper($region) : null;
    $this->tz = new DateTimeZone($timezone);
  }

/**
  * Gibt die Liste der verfügbaren Länder zurück.
  * @return array Assoziatives Array [Code => Name]
  */
  public static function getAvailableCountries(): array
  {
    $countries = self::$availableCountries;
    asort($countries); // Sortiert alphabetisch nach den Ländernamen (Werten)
    return $countries;
  }

  /**
   * Gibt eine Liste der verfügbaren Regionen/Kantone für ein bestimmtes Land zurück.
   *
   * @param string $country Der Ländercode (z.B. 'DE', 'CH').
   * @return array Ein assoziatives Array von Regionencodes zu Regionennamen.
   */
  public static function getAvailableRegions(string $country): array
  {
    $country = strtoupper($country);
    if (isset(self::$availableRegions[$country])) {
      $regions = self::$availableRegions[$country];
      asort($regions); // Sortiere alphabetisch nach Namen
      return $regions;
    }
    return [];
  }

/**
  * Liefert alle Feiertage als assoziatives Array: 'Name' => 'YYYY-MM-DD'
  */
  public function getFeiertage(): array
  {
    $method = "getFeiertage{$this->country}";
    if (method_exists($this, $method)) {
    
      return $this->$method();
    }
    throw new Exception("Feiertage für Land {$this->country} nicht definiert.");
  }

/**
  * Feiertage für Deutschland (inkl. regionaler Feiertage)
  */
  private function getFeiertageDE(): array
  {
    $feiertage = [];

    // Bewegliche Feiertage
    $ostersonntag = new DateTime('@' . easter_date($this->year));
    $ostersonntag->setTimezone($this->tz);

    $feiertage["Neujahr"] = "{$this->year}-01-01";
    $feiertage["Karfreitag"] = $this->shiftDate($ostersonntag, -2);
    $feiertage["Ostersonntag"] = $ostersonntag->format('Y-m-d');
    $feiertage["Ostermontag"] = $this->shiftDate($ostersonntag, 1);
    $feiertage["Tag der Arbeit"] = "{$this->year}-05-01";
    $feiertage["Christi Himmelfahrt"] = $this->shiftDate($ostersonntag, 39);
    $feiertage["Pfingstsonntag"] = $this->shiftDate($ostersonntag, 49);
    $feiertage["Pfingstmontag"] = $this->shiftDate($ostersonntag, 50);
    $feiertage["Tag der Deutschen Einheit"] = "{$this->year}-10-03";
    $feiertage["1. Weihnachtstag"] = "{$this->year}-12-25";
    $feiertage["2. Weihnachtstag"] = "{$this->year}-12-26";

    // Regionale Feiertage
    $regionale = [
      'BW' => [
        "Heilige Drei Könige" => "{$this->year}-01-06",
        "Fronleichnam" => $this->shiftDate($ostersonntag, 60)
      ],
      'BY' => [
        "Heilige Drei Könige" => "{$this->year}-01-06",
        "Fronleichnam" => $this->shiftDate($ostersonntag, 60),
        "Mariä Himmelfahrt" => "{$this->year}-08-15"
      ],
      'BE' => [
        "Internationaler Frauentag" => "{$this->year}-03-08",
        "Weltkindertag" => "{$this->year}-09-20"
      ],
      'BB' => [
        "Reformationstag" => "{$this->year}-10-31"
      ],
      'HE' => [
        "Fronleichnam" => $this->shiftDate($ostersonntag, 60)
      ],
      'NW' => [
        "Fronleichnam" => $this->shiftDate($ostersonntag, 60)
      ],
      'RP' => [
        "Fronleichnam" => $this->shiftDate($ostersonntag, 60)
      ],
      'SL' => [
        "Mariä Himmelfahrt" => "{$this->year}-08-15",
        "Fronleichnam" => $this->shiftDate($ostersonntag, 60)
      ],
      'SN' => [
        "Reformationstag" => "{$this->year}-10-31",
        "Buß- und Bettag" => $this->getBussUndBettag()
      ],
      'ST' => [
        "Heilige Drei Könige" => "{$this->year}-01-06",
        "Reformationstag" => "{$this->year}-10-31"
      ],
      'TH' => [
        "Reformationstag" => "{$this->year}-10-31"
      ]
    ];

    if ($this->region && isset($regionale[$this->region])) {

      $feiertage = array_merge($feiertage, $regionale[$this->region]);
    }

    ksort($feiertage);
    return $feiertage;
  }

/**
  * Platzhalter für Österreich
  */
  private function getFeiertageAT(): array
  {
    $feiertage = [];

    // Bewegliche Feiertage
    $ostersonntag = new DateTime('@' . easter_date($this->year));
    $ostersonntag->setTimezone($this->tz);

    $feiertage["Neujahr"] = "{$this->year}-01-01";
    $feiertage["Heilige Drei Könige"] = "{$this->year}-01-06";
    $feiertage["Ostersonntag"] = $ostersonntag->format('Y-m-d');
    $feiertage["Ostermontag"] = $this->shiftDate($ostersonntag, 1);
    $feiertage["Staatsfeiertag"] = "{$this->year}-05-01";
    $feiertage["Christi Himmelfahrt"] = $this->shiftDate($ostersonntag, 39);
    $feiertage["Pfingstsonntag"] = $this->shiftDate($ostersonntag, 49);
    $feiertage["Pfingstmontag"] = $this->shiftDate($ostersonntag, 50);
    $feiertage["Fronleichnam"] = $this->shiftDate($ostersonntag, 60);
    $feiertage["Mariä Himmelfahrt"] = "{$this->year}-08-15";
    $feiertage["Nationalfeiertag"] = "{$this->year}-10-26";
    $feiertage["Allerheiligen"] = "{$this->year}-11-01";
    $feiertage["Mariä Empfängnis"] = "{$this->year}-12-08";
    $feiertage["1. Weihnachtstag"] = "{$this->year}-12-25";
    $feiertage["2. Weihnachtstag"] = "{$this->year}-12-26";

    ksort($feiertage);
    return $feiertage;
  }


/**
  * Platzhalter für Schweiz
  */
  private function getFeiertageCH(): array
  {
    $feiertage = [];

    // Bewegliche Feiertage
    $ostersonntag = new DateTime('@' . easter_date($this->year));
    $ostersonntag->setTimezone($this->tz);

    // Bundesweite Feiertage
    $feiertage["Neujahr"] = "{$this->year}-01-01";
    $feiertage["Karfreitag"] = $this->shiftDate($ostersonntag, -2);
    $feiertage["Ostersonntag"] = $ostersonntag->format('Y-m-d');
    $feiertage["Ostermontag"] = $this->shiftDate($ostersonntag, 1);
    $feiertage["Auffahrt"] = $this->shiftDate($ostersonntag, 39); // Christi Himmelfahrt
    $feiertage["Pfingstsonntag"] = $this->shiftDate($ostersonntag, 49);
    $feiertage["Pfingstmontag"] = $this->shiftDate($ostersonntag, 50);
    $feiertage["Bundesfeier"] = "{$this->year}-08-01";
    $feiertage["1. Weihnachtstag"] = "{$this->year}-12-25";
    $feiertage["Stephanstag"] = "{$this->year}-12-26";

    // Kantons-spezifische Feiertage
    $kantonal = [
      'ZH' => [ // Zürich
        "Berchtoldstag" => "{$this->year}-01-02",
        "Sechseläuten" => $this->getSechselauten()
      ],
      'GE' => [ // Genf
        "Jeûne genevois" => $this->getJeuneGenevois()
      ],
      'TI' => [ // Tessin
        "San Giuseppe" => "{$this->year}-03-19",
        "Fronleichnam" => $this->shiftDate($ostersonntag, 60)
      ],
      'VS' => [ // Wallis
        "Mariä Himmelfahrt" => "{$this->year}-08-15",
        "Allerheiligen" => "{$this->year}-11-01",
        "Mariä Empfängnis" => "{$this->year}-12-08"
      ]
      // Weitere Kantone hier ergänzen...
    ];

    if ($this->region && isset($kantonal[$this->region])) {

      $feiertage = array_merge($feiertage, $kantonal[$this->region]);
    }

    ksort($feiertage);
    return $feiertage;
  }

/**
  * Sechseläuten (3. Montag im April)
  */
  private function getSechselauten(): string
  {
    $date = new DateTime("first monday of april {$this->year}", $this->tz);
    $date->modify('+2 weeks'); // 3. Montag
    return $date->format('Y-m-d');
  }

/**
  * Jeûne genevois (Donnerstag nach dem 1. Sonntag im September)
  */
  private function getJeuneGenevois(): string
  {
    $date = new DateTime("first sunday of september {$this->year}", $this->tz);
    $date->modify('+4 days'); // Donnerstag danach
    return $date->format('Y-m-d');
  }

 /*
  * Verwendung:
  * // Schweiz allgemein (nur bundesweite Feiertage)
  * $ch = new Feiertage(2026, 'CH');
  * print_r($ch->getFeiertage());
  * 
  * // Schweiz, Kanton Zürich
  * $chZH = new Feiertage(2026, 'CH', 'ZH');
  * print_r($chZH->getFeiertage());
  * 
  * // Schweiz, Kanton Genf
  * $chGE = new Feiertage(2026, 'CH', 'GE');
  * print_r($chGE->getFeiertage());
  */


/**
  * Platzhalter für Polen
  */
  private function getFeiertagePL(): array
  {
    $feiertage = [];

    // Bewegliche Feiertage
    $ostersonntag = new DateTime('@' . easter_date($this->year));
    $ostersonntag->setTimezone($this->tz);

    $feiertage["Neujahr"] = "{$this->year}-01-01";
    $feiertage["Heilige Drei Könige"] = "{$this->year}-01-06";
    $feiertage["Ostersonntag"] = $ostersonntag->format('Y-m-d');
    $feiertage["Ostermontag"] = $this->shiftDate($ostersonntag, 1);
    $feiertage["Tag der Arbeit"] = "{$this->year}-05-01";
    $feiertage["Tag der Verfassung"] = "{$this->year}-05-03";
    $feiertage["Pfingstsonntag"] = $this->shiftDate($ostersonntag, 49);
    $feiertage["Fronleichnam"] = $this->shiftDate($ostersonntag, 60);
    $feiertage["Mariä Himmelfahrt"] = "{$this->year}-08-15";
    $feiertage["Allerheiligen"] = "{$this->year}-11-01";
    $feiertage["Unabhängigkeitstag"] = "{$this->year}-11-11";
    $feiertage["1. Weihnachtstag"] = "{$this->year}-12-25";
    $feiertage["2. Weihnachtstag"] = "{$this->year}-12-26";

    ksort($feiertage);
    return $feiertage;

   /*
    * Aufruf:
    * $pl = new Feiertage(2026, 'PL');
    * print_r($pl->getFeiertage());
    */
  }


/**
  * Hilfsfunktion: Datum um X Tage verschieben
  */
  private function shiftDate(DateTime $date, int $days): string
  {
    return (clone $date)->modify("{$days} days")->format('Y-m-d');
  }

/**
  * Berechnet Buß- und Bettag (Mittwoch vor dem 23. November)
  */
  private function getBussUndBettag(): string
  {
    $date = new DateTime("{$this->year}-11-23", $this->tz);
    $date->modify('last wednesday');
    return $date->format('Y-m-d');
  }

/**
  * Feiertage als ICS-String exportieren
  * @return string
  */
  public function toICS(): string
  {
    $feiertage = $this->getFeiertage();

    $ics = "BEGIN:VCALENDAR\r\n";
    $ics .= "VERSION:2.0\r\n";
    $ics .= "PRODID:-//MeineFeiertagsAPI//DE\r\n";
    $ics .= "CALSCALE:GREGORIAN\r\n";
    $ics .= "METHOD:PUBLISH\r\n";

    foreach ($feiertage as $name => $datum) {

      $start = date('Ymd', strtotime($datum));
      $uid = uniqid() . "@feiertage.local";

      $ics .= "BEGIN:VEVENT\r\n";
      $ics .= "UID:$uid\r\n";
      $ics .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
      $ics .= "DTSTART;VALUE=DATE:$start\r\n";
      $ics .= "SUMMARY:" . $this->escapeICS($name) . "\r\n";
      $ics .= "TRANSP:TRANSPARENT\r\n";
      $ics .= "END:VEVENT\r\n";
    }

    $ics .= "END:VCALENDAR\r\n";
    return $ics;
  }

/**
  * Hilfsfunktion: Sonderzeichen für ICS escapen
  */
  private function escapeICS(string $text): string
  {
    return str_replace(
      ["\\", ",", ";", "\n"],
      ["\\\\", "\\,", "\\;", "\\n"],
      $text
    );
  }

  private $translations = [
    'PL' => [
      "Neujahr" => "Nowy Rok",
      "Heilige Drei Könige" => "Święto Trzech Króli",
      "Ostersonntag" => "Niedziela Wielkanocna",
      "Ostermontag" => "Poniedziałek Wielkanocny",
      "Tag der Arbeit" => "Święto Pracy",
      "Tag der Verfassung" => "Święto Konstytucji 3 Maja",
      "Pfingstsonntag" => "Zesłanie Ducha Świętego",
      "Fronleichnam" => "Boże Ciało",
      "Mariä Himmelfahrt" => "Wniebowzięcie Najświętszej Maryi Panny",
      "Allerheiligen" => "Wszystkich Świętych",
      "Unabhängigkeitstag" => "Narodowe Święto Niepodległości",
      "1. Weihnachtstag" => "Boże Narodzenie (pierwszy dzień)",
      "2. Weihnachtstag" => "Boże Narodzenie (drugi dzień)"
    ]
  ];

/**
  * Feiertage mit optionaler Übersetzung zurückgeben
  */
  public function getFeiertageTranslated(?string $lang = null): array
  {
    $holidays = $this->getFeiertage();

    if ($lang && isset($this->translations[$lang])) {

      foreach ($holidays as $name => $date) {
        if (isset($this->translations[$lang][$name])) {

          $holidays[$this->translations[$lang][$name]] = $date;
          unset($holidays[$name]);
        }
      }
    }
    return $holidays;
  }

  /*
   * Aufruf:
   * $pl = new Feiertage(2026, 'PL');
   * print_r($pl->getFeiertageTranslated('PL'));
   */

}
