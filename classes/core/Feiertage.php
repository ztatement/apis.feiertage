<?php

  declare(strict_types=1);

/**
  * Feiertags-API
  * 
  * @author Thomas Boettcher @ztatement (github[at]ztatement[dot]com)
  * @copyright (c) 2026 ztatement
  *
  * @version 1.1.2.2026.06.17
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
  private int $year; // Das Jahr für die Feiertagsberechnung
  private string $country; // Der Ländercode (z.B. 'DE', 'AT')
  private ?string $region; // Optionaler Regionencode (z.B. 'BY' für Bayern)
/**
  * Die Zeitzone für die Datumsberechnungen.
  * @var DateTimeZone
  */
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
    'AT' => [
      'B'  => 'Burgenland',
      'K'  => 'Kärnten',
      'N'  => 'Niederösterreich',
      'O'  => 'Oberösterreich',
      'S'  => 'Salzburg',
      'ST' => 'Steiermark',
      'T'  => 'Tirol',
      'V'  => 'Vorarlberg',
      'W'  => 'Wien',
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
  * Validiert die angegebene Sprache und gibt einen Fallback zurück, falls sie nicht unterstützt wird.
  * Standard-Fallback ist Englisch (EN), wenn nicht Deutsch (DE) angefordert wurde.
  * 
  * @param string|null $lang Der Sprachcode.
  * @return string Der validierte Sprachcode.
  */
  public static function resolveLanguage(?string $lang): string
  {
    if (empty($lang)) return 'DE';
    $lang = strtoupper($lang);
    // Wenn die Sprache unterstützt wird, nimm sie. Sonst Fallback auf EN.
    return array_key_exists($lang, self::$languageNames) ? $lang : 'EN';
  }

/**
  * Gibt die Liste der unterstützten Sprachen zurück.
  * @return array Assoziatives Array [Code => Name]
  */
  public static function getAvailableLanguages(): array
  {
    return self::$languageNames;
  }

/**
  * Ruft die Feiertage für das konfigurierte Land und die Region ab.
  * Die Methode delegiert die Anfrage an eine länderspezifische Methode (z.B. `getFeiertageDE()`).
  * @return array Ein assoziatives Array von Feiertagen, wobei jeder Feiertag ein Array mit 'date' und 'type' enthält.
  */
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

    // Berechnung der beweglichen Feiertage basierend auf Ostern
    // Bewegliche Feiertage
    $ostersonntag = new DateTime('@' . easter_date($this->year));
    $ostersonntag->setTimezone($this->tz);

    $feiertage["Neujahr"] = ["date" => "{$this->year}-01-01", "type" => "national"];
    $feiertage["Karfreitag"] = ["date" => $this->shiftDate($ostersonntag, -2), "type" => "national"];
    $feiertage["Ostersonntag"] = ["date" => $ostersonntag->format('Y-m-d'), "type" => "national"];
    $feiertage["Ostermontag"] = ["date" => $this->shiftDate($ostersonntag, 1), "type" => "national"];
    $feiertage["Tag der Arbeit"] = ["date" => "{$this->year}-05-01", "type" => "national"];
    $feiertage["Christi Himmelfahrt"] = ["date" => $this->shiftDate($ostersonntag, 39), "type" => "national"];
    $feiertage["Pfingstsonntag"] = ["date" => $this->shiftDate($ostersonntag, 49), "type" => "national"];
    $feiertage["Pfingstmontag"] = ["date" => $this->shiftDate($ostersonntag, 50), "type" => "national"];
    $feiertage["Tag der Deutschen Einheit"] = ["date" => "{$this->year}-10-03", "type" => "national"];
    $feiertage["1. Weihnachtstag"] = ["date" => "{$this->year}-12-25", "type" => "national"];
    $feiertage["2. Weihnachtstag"] = ["date" => "{$this->year}-12-26", "type" => "national"];

    // Definition der regionalen Feiertage pro Bundesland
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
      foreach ($regionale[$this->region] as $name => $date) {
        $feiertage[$name] = ["date" => $date, "type" => "regional"];
      }
    }

    // Sortiert die Feiertage alphabetisch nach Namen
    ksort($feiertage);
    return $feiertage;
  }

/**
  * Platzhalter für Österreich
  */
  private function getFeiertageAT(): array
  {
    $feiertage = [];

    // Berechnung der beweglichen Feiertage basierend auf Ostern
    // Bewegliche Feiertage
    $ostersonntag = new DateTime('@' . easter_date($this->year));
    $ostersonntag->setTimezone($this->tz);

    $feiertage["Neujahr"] = ["date" => "{$this->year}-01-01", "type" => "national"];
    $feiertage["Heilige Drei Könige"] = ["date" => "{$this->year}-01-06", "type" => "national"];
    $feiertage["Ostersonntag"] = ["date" => $ostersonntag->format('Y-m-d'), "type" => "national"];
    $feiertage["Ostermontag"] = ["date" => $this->shiftDate($ostersonntag, 1), "type" => "national"];
    $feiertage["Staatsfeiertag"] = ["date" => "{$this->year}-05-01", "type" => "national"];
    $feiertage["Christi Himmelfahrt"] = ["date" => $this->shiftDate($ostersonntag, 39), "type" => "national"];
    $feiertage["Pfingstsonntag"] = ["date" => $this->shiftDate($ostersonntag, 49), "type" => "national"];
    $feiertage["Pfingstmontag"] = ["date" => $this->shiftDate($ostersonntag, 50), "type" => "national"];
    $feiertage["Fronleichnam"] = ["date" => $this->shiftDate($ostersonntag, 60), "type" => "national"];
    $feiertage["Mariä Himmelfahrt"] = ["date" => "{$this->year}-08-15", "type" => "national"];
    $feiertage["Nationalfeiertag"] = ["date" => "{$this->year}-10-26", "type" => "national"];
    $feiertage["Allerheiligen"] = ["date" => "{$this->year}-11-01", "type" => "national"];
    $feiertage["Mariä Empfängnis"] = ["date" => "{$this->year}-12-08", "type" => "national"];
    $feiertage["1. Weihnachtstag"] = ["date" => "{$this->year}-12-25", "type" => "national"];
    $feiertage["2. Weihnachtstag"] = ["date" => "{$this->year}-12-26", "type" => "national"];

    // Regionale Feiertage (Landesfeiertage / Patrozinium)
    $regionale = [
      'B' => ["Martinitag" => "{$this->year}-11-11"],
      'K' => [
        "Josefitag" => "{$this->year}-03-19",
        "Tag der Volksabstimmung" => "{$this->year}-10-10"
      ],
      'N' => ["Leopolditag" => "{$this->year}-11-15"],
      'O' => ["Florianitag" => "{$this->year}-05-04"],
      'S' => ["Rupertitag" => "{$this->year}-09-24"],
      'ST' => ["Josefitag" => "{$this->year}-03-19"],
      'T' => ["Josefitag" => "{$this->year}-03-19"],
      'V' => ["Josefitag" => "{$this->year}-03-19"],
      'W' => ["Leopolditag" => "{$this->year}-11-15"]
    ];

    // Wenn eine Region gesetzt ist und Feiertage dafür existieren, diese hinzufügen
    if ($this->region && isset($regionale[$this->region])) {
      foreach ($regionale[$this->region] as $name => $date) {
        $feiertage[$name] = ["date" => $date, "type" => "regional"];
      }
    }

    ksort($feiertage);
    // Sortiert die Feiertage alphabetisch nach Namen
    return $feiertage;
  }


/**
  * Platzhalter für Schweiz
  */
  private function getFeiertageCH(): array
  {
    $feiertage = [];

    // Berechnung der beweglichen Feiertage basierend auf Ostern
    // Bewegliche Feiertage
    $ostersonntag = new DateTime('@' . easter_date($this->year));
    $ostersonntag->setTimezone($this->tz);

    // Bundesweite Feiertage
    $feiertage["Neujahr"] = ["date" => "{$this->year}-01-01", "type" => "national"];
    $feiertage["Karfreitag"] = ["date" => $this->shiftDate($ostersonntag, -2), "type" => "national"];
    $feiertage["Ostersonntag"] = ["date" => $ostersonntag->format('Y-m-d'), "type" => "national"];
    $feiertage["Ostermontag"] = ["date" => $this->shiftDate($ostersonntag, 1), "type" => "national"];
    $feiertage["Auffahrt"] = ["date" => $this->shiftDate($ostersonntag, 39), "type" => "national"];
    $feiertage["Pfingstsonntag"] = ["date" => $this->shiftDate($ostersonntag, 49), "type" => "national"];
    $feiertage["Pfingstmontag"] = ["date" => $this->shiftDate($ostersonntag, 50), "type" => "national"];
    $feiertage["Bundesfeier"] = ["date" => "{$this->year}-08-01", "type" => "national"];
    $feiertage["1. Weihnachtstag"] = ["date" => "{$this->year}-12-25", "type" => "national"];
    $feiertage["Stephanstag"] = ["date" => "{$this->year}-12-26", "type" => "national"];

    // Definition der kantonalen Feiertage pro Kanton
    // Kantons-spezifische Feiertage
    $kantonal = [
      'AG' => [ // Aargau
        "Berchtoldstag" => "{$this->year}-01-02",
        "Fronleichnam" => $this->shiftDate($ostersonntag, 60),
        "Allerheiligen" => "{$this->year}-11-01",
        "Mariä Empfängnis" => "{$this->year}-12-08"
      ],
      'BE' => [ // Bern
        "Berchtoldstag" => "{$this->year}-01-02"
      ],
      'BS' => [ // Basel-Stadt
        "Tag der Arbeit" => "{$this->year}-05-01"
      ],
      'GE' => [ // Genf
        "Jeûne genevois" => $this->getJeuneGenevois(),
        "Restauration de la République" => "{$this->year}-12-31"
      ],
      'GL' => [ // Glarus
        "Näfelser Fahrt" => $this->getNaefelserFahrt()
      ],
      'LU' => [ // Luzern
        "Berchtoldstag" => "{$this->year}-01-02",
        "Fronleichnam" => $this->shiftDate($ostersonntag, 60),
        "Mariä Himmelfahrt" => "{$this->year}-08-15",
        "Allerheiligen" => "{$this->year}-11-01",
        "Mariä Empfängnis" => "{$this->year}-12-08"
      ],
      'SG' => [ // St. Gallen
        "Allerheiligen" => "{$this->year}-11-01"
      ],
      'TI' => [ // Tessin
        "San Giuseppe" => "{$this->year}-03-19",
        "Fronleichnam" => $this->shiftDate($ostersonntag, 60)
      ],
      'VS' => [ // Wallis
        "Mariä Himmelfahrt" => "{$this->year}-08-15",
        "Allerheiligen" => "{$this->year}-11-01",
        "Mariä Empfängnis" => "{$this->year}-12-08"
      ],
      'ZH' => [ // Zürich
        "Berchtoldstag" => "{$this->year}-01-02",
        "Sechseläuten" => $this->getSechselauten()
      ]
    ];

    if ($this->region && isset($kantonal[$this->region])) {
      foreach ($kantonal[$this->region] as $name => $date) {
        $feiertage[$name] = ["date" => $date, "type" => "regional"];
      }
    }

    // Sortiert die Feiertage alphabetisch nach Namen
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

/**
  * Näfelser Fahrt (1. Donnerstag im April, außer bei Gründonnerstag, dann 2. Donnerstag)
  */
  private function getNaefelserFahrt(): string
  {
    $date = new DateTime("first thursday of april {$this->year}", $this->tz);
    $ostersonntag = new DateTime('@' . easter_date($this->year));
    $ostersonntag->setTimezone($this->tz);
    $gruendonnerstag = (clone $ostersonntag)->modify('-3 days');

    if ($date->format('Y-m-d') === $gruendonnerstag->format('Y-m-d')) {
      $date->modify('+7 days');
    }

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
  * Feiertage für Polen
  * Hinweis: In Polen gibt es keine regionalen arbeitsfreien Feiertage. 
  * Alle gesetzlichen Feiertage gelten landesweit.
  */
  private function getFeiertagePL(): array
  {
    $feiertage = [];

    // Berechnung der beweglichen Feiertage basierend auf Ostern
    // Bewegliche Feiertage
    $ostersonntag = new DateTime('@' . easter_date($this->year));
    $ostersonntag->setTimezone($this->tz);

    $feiertage["Neujahr"] = ["date" => "{$this->year}-01-01", "type" => "national"];
    $feiertage["Heilige Drei Könige"] = ["date" => "{$this->year}-01-06", "type" => "national"];
    $feiertage["Ostersonntag"] = ["date" => $ostersonntag->format('Y-m-d'), "type" => "national"];
    $feiertage["Ostermontag"] = ["date" => $this->shiftDate($ostersonntag, 1), "type" => "national"];
    $feiertage["Tag der Arbeit"] = ["date" => "{$this->year}-05-01", "type" => "national"];
    $feiertage["Tag der Verfassung"] = ["date" => "{$this->year}-05-03", "type" => "national"];
    $feiertage["Pfingstsonntag"] = ["date" => $this->shiftDate($ostersonntag, 49), "type" => "national"];
    $feiertage["Fronleichnam"] = ["date" => $this->shiftDate($ostersonntag, 60), "type" => "national"];
    $feiertage["Mariä Himmelfahrt"] = ["date" => "{$this->year}-08-15", "type" => "national"];
    $feiertage["Allerheiligen"] = ["date" => "{$this->year}-11-01", "type" => "national"];
    $feiertage["Unabhängigkeitstag"] = ["date" => "{$this->year}-11-11", "type" => "national"];
    $feiertage["1. Weihnachtstag"] = ["date" => "{$this->year}-12-25", "type" => "national"];
    $feiertage["2. Weihnachtstag"] = ["date" => "{$this->year}-12-26", "type" => "national"];

    ksort($feiertage);
    // Sortiert die Feiertage alphabetisch nach Namen
    return $feiertage;

   /*
    * Aufruf:
    * $pl = new Feiertage(2026, 'PL');
    * print_r($pl->getFeiertage());
    */
  }


/**
  * Hilfsfunktion: Datum um X Tage verschieben
  * Erstellt ein neues DateTime-Objekt, um das Originalobjekt nicht zu modifizieren.
  *
  * @param DateTime $date Das ursprüngliche Datum.
  * @param int $days Die Anzahl der Tage, um die das Datum verschoben werden soll (positiv oder negativ).
  * @return string Das verschobene Datum im Format 'YYYY-MM-DD'.
  */
  private function shiftDate(DateTime $date, int $days): string
  {
    return (clone $date)->modify("{$days} days")->format('Y-m-d');
  }

/**
  * Berechnet Buß- und Bettag (Mittwoch vor dem 23. November)
  *
  * @return string Das Datum des Buß- und Bettags im Format 'YYYY-MM-DD'.
  * @throws Exception Wenn das Jahr außerhalb des gültigen Bereichs liegt (durch DateTime-Konstruktor).
  * @see https://de.wikipedia.org/wiki/Bu%C3%9F-_und_Bettag
  */
  private function getBussUndBettag(): string
  {
    $date = new DateTime("{$this->year}-11-23", $this->tz);
    $date->modify('last wednesday');
    return $date->format('Y-m-d');
  }

/**
  * Generiert eine iCalendar (ICS)-Datei für alle Feiertage des konfigurierten Jahres, Landes und der Region.
  * Jedes Event enthält eine 24-Stunden-Erinnerung.
  *
  * @return string Der vollständige ICS-String.
  * @throws Exception Wenn die Feiertage für das Land nicht definiert sind (durch getFeiertage()).
  * Feiertage als ICS-String exportieren
  * @param string|null $lang
  * @return string
  */
  public function toICS(?string $lang = null): string
  {
    $feiertage = $this->getFeiertageTranslated($lang);

    $ics = "BEGIN:VCALENDAR\r\n";
    $ics .= "VERSION:2.0\r\n";
    $ics .= "PRODID:-//MeineFeiertagsAPI//DE\r\n";
    $ics .= "CALSCALE:GREGORIAN\r\n";
    $ics .= "METHOD:PUBLISH\r\n";

    foreach ($feiertage as $name => $info) {
      // Formatiert das Startdatum für ICS (YYYYMMDD)
      $start = date('Ymd', strtotime($info['date'])); 
      // Generiert eine eindeutige ID für das Kalenderereignis
      $uid = uniqid() . "@feiertage.local"; 

      // Start eines Kalenderereignisses
      $ics .= "BEGIN:VEVENT\r\n";
      $ics .= "UID:$uid\r\n";
      $ics .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
      $ics .= "DTSTART;VALUE=DATE:$start\r\n";
      $ics .= "SUMMARY:" . $this->escapeICS($name) . "\r\n";
      $ics .= "TRANSP:TRANSPARENT\r\n";
      $ics .= "BEGIN:VALARM\r\n";
      $ics .= "ACTION:DISPLAY\r\n"; // Aktion: Anzeige einer Nachricht
      $ics .= "DESCRIPTION:Erinnerung: " . $this->escapeICS($name) . "\r\n"; // Beschreibung der Erinnerung
      $ics .= "TRIGGER:-PT24H\r\n"; // Trigger: 24 Stunden vor dem Ereignis
      $ics .= "END:VALARM\r\n";
      // Fügt eine Kategorie hinzu, um Feiertage in einigen Kalenderanwendungen besser zu filtern/färben
      $ics .= "CATEGORIES:HOLIDAY\r\n"; 
      $ics .= "END:VEVENT\r\n";
    }

    $ics .= "END:VCALENDAR\r\n";
    return $ics;
  }

/**
  * Escaped spezielle Zeichen für die Verwendung in ICS-Dateien.
  * Kommas, Semikolons, Backslashes und Zeilenumbrüche müssen escaped werden.
  *
  * @param string $text Der zu escapende Text.
  * @return string Der escapte Text.
  * @see https://icalendar.org/rdata/RFC5545.html#_3_3_11_Text
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

/**
  * Liste der unterstützten Sprachen mit ihren Anzeigenamen.
  * @var array
  */
  private static array $languageNames = [
    'DE' => 'Deutsch',
    'EN' => 'English',
    'PL' => 'Polski',
  ];
/**
  * Assoziatives Array für Übersetzungen von Feiertagsnamen in verschiedene Sprachen.
  */
  private static array $translations = [
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
    ],
    'EN' => [
      "Neujahr" => "New Year's Day",
      "Heilige Drei Könige" => "Epiphany",
      "Karfreitag" => "Good Friday",
      "Ostersonntag" => "Easter Sunday",
      "Ostermontag" => "Easter Monday",
      "Tag der Arbeit" => "Labour Day",
      "Christi Himmelfahrt" => "Ascension Day",
      "Pfingstsonntag" => "Whit Sunday",
      "Pfingstmontag" => "Whit Monday",
      "Tag der Deutschen Einheit" => "Day of German Unity",
      "1. Weihnachtstag" => "Christmas Day",
      "2. Weihnachtstag" => "St. Stephen's Day / Boxing Day",
      "Internationaler Frauentag" => "International Women's Day",
      "Weltkindertag" => "World Children's Day",
      "Reformationstag" => "Reformation Day",
      "Fronleichnam" => "Corpus Christi",
      "Mariä Himmelfahrt" => "Assumption Day",
      "Buß- und Bettag" => "Repentance and Prayer Day",
      "Staatsfeiertag" => "State Holiday",
      "Nationalfeiertag" => "National Holiday",
      "Allerheiligen" => "All Saints' Day",
      "Mariä Empfängnis" => "Immaculate Conception",
      "Martinitag" => "Saint Martin's Day",
      "Josefitag" => "Saint Joseph's Day",
      "Tag der Volksabstimmung" => "Carinthian Plebiscite Day",
      "Leopolditag" => "Saint Leopold's Day",
      "Florianitag" => "Saint Florian's Day",
      "Rupertitag" => "Saint Rupert's Day",
      "Auffahrt" => "Ascension Day",
      "Bundesfeier" => "Swiss National Day",
      "Stephanstag" => "Saint Stephen's Day",
      "Berchtoldstag" => "Berchtold's Day",
      "Jeûne genevois" => "Genevan Fast",
      "Restauration de la République" => "Restoration of the Republic",
      "Näfelser Fahrt" => "Näfelser Fahrt",
      "San Giuseppe" => "Saint Joseph's Day",
      "Sechseläuten" => "Sechseläuten",
      "Tag der Verfassung" => "Constitution Day",
      "Unabhängigkeitstag" => "Independence Day"
    ]
  ];

/**
  * Gibt die Feiertage zurück, optional mit übersetzten Namen.
  * Wenn eine unterstützte Sprache angegeben wird, werden die Feiertagsnamen übersetzt.
  *
  * @param string|null $lang Der Ländercode der Zielsprache (z.B. 'PL').
  * @return array Ein assoziatives Array von Feiertagen, mit übersetzten Namen, falls verfügbar.
  * @throws Exception Wenn die Feiertage für das Land nicht definiert sind (durch getFeiertage()).
  * Feiertage mit optionaler Übersetzung zurückgeben
  */
  public function getFeiertageTranslated(?string $lang = null): array
  {
    $lang = self::resolveLanguage($lang);
    $holidays = $this->getFeiertage();

    if (isset(self::$translations[$lang])) {

      foreach ($holidays as $name => $info) {
        if (isset(self::$translations[$lang][$name])) {

          $holidays[self::$translations[$lang][$name]] = $info;
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
