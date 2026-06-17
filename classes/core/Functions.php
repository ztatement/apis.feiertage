<?php

  declare(strict_types=1);

/**
  * Hilfsfunktionen für die Feiertags-API
  * 
  * @author Thomas Boettcher @ztatement (github[at]ztatement[dot]com)
  * @copyright (c) 2026 ztatement
  *
  * @version 1.1.2.2026.06.17
  * @file $Id: classes/core/Functions.php $
  * @created $Id: 1 Dienstag, 16. Juni 2026, 06:04:16 GMT+0200Z ztatement $
  *
  * @repository https://github.com/ztatement/apis.feiertage
  * @link https://timezone-api.demo-seite.com
  * @license MIT (https://opensource.org/license/MIT)
  * @see LICENSE
  */

  namespace FTA\Core;

  use Exception;


class Functions
{
/**
  * Ermittelt das Protokoll einer URL oder gibt das Standardprotokoll basierend auf der Serverkonfiguration zurück.
  *
  * Diese Funktion prüft, ob eine URL ein gültiges Protokoll enthält (z.B. `http://`, `https://`, `ftp://`, etc.).
  * Falls die URL kein Protokoll enthält, wird automatisch `http://` vorangestellt.
  * Wenn keine URL übergeben wird, ermittelt die Funktion das Protokoll basierend auf den aktuellen Servereinstellungen
  * und gibt entweder `http://` oder `https://` zurück, je nachdem, ob die Verbindung sicher ist.
  *
  * Beispielaufrufe:
  * - `get_protocol()` ohne Parameter: Gibt das Protokoll basierend auf der aktuellen Serverumgebung zurück.
  * - `get_protocol('www.demo-seite.com')`: Prüft, ob die URL bereits ein Protokoll hat. Falls nicht, wird `http://` hinzugefügt.
  *
  * @param string $url Die URL, deren Protokoll überprüft werden soll. (Optional)
  *        Falls keine URL übergeben wird, wird das Protokoll basierend auf der Serverkonfiguration ermittelt.
  *
  * @return string Das vollständige Protokoll für die angegebene URL oder das Standardprotokoll für die aktuelle Serverumgebung.
  */
  public static function get_protocol(string $url = ''): string
  {
    // Falls keine URL übergeben wird, ermitteln wir das Protokoll für die aktuelle Seite
    if (empty($url))
    { 
      // Prüft verschiedene Server-Variablen, um festzustellen, ob HTTPS verwendet wird
      return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
             (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
             (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') 
             ? 'https://' : 'http://';
    }

    // Überprüfen, ob die übergebene URL bereits ein Protokoll enthält
    // Verwendet mb_substr für Multibyte-sichere String-Operationen
    if (mb_substr($url, 0, 7, CHARSET) != 'http://' && 
        mb_substr($url, 0, 8, CHARSET) != 'https://' && 
        mb_substr($url, 0, 6, CHARSET) != 'ftp://' && 
        mb_substr($url, 0, 7, CHARSET) != 'sftp://' && 
        mb_substr($url, 0, 9, CHARSET) != 'gopher://' && 
        mb_substr($url, 0, 7, CHARSET) != 'news://')
    {
      // Wenn kein Protokoll vorhanden ist, fügen wir 'http://' hinzu
      $url = 'http://' . $url;
    }

    // Rückgabe der (gegebenen oder modifizierten) URL
    return $url;
  }

/**
  * Ermittelt die Basis-URL der Anwendung.
  *
  * Diese Funktion ermittelt die Basis-URL der Anwendung, indem sie das Protokoll,
  * den Hostnamen und das Verzeichnis des Skripts kombiniert. Sie kann optional
  * einen Teil der URL abschneiden, um die Basis-URL für Unterverzeichnisse zu erhalten.
  *
  * @param string $cut Ein optionaler Teil der URL, der abgeschnitten werden soll.
  *
  * @throws Exception Wenn der Hostname ungültig ist.
  *
  * @return string Die Basis-URL der Anwendung.
  */
  public static function get_base_url(string $cut = ''): string
  {
    // Protokoll ermitteln
    $protocol = self::get_protocol();

    // Sicherstellen, dass der Host korrekt und sicher ist
    // Bei Reverse Proxys den X-Forwarded-Host bevorzugen
    $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '';
    
    // Falls mehrere Hosts im Forward-Header stehen (Komma-separiert), den ersten nehmen
    if (str_contains($host, ','))
    {
      $host = trim(explode(',', $host)[0]);
    }

    // Optional: Host validieren (nur, wenn du zusätzliche Sicherheit wünschst)
    if (! filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME))
    {
      throw new Exception('Ungültiger Hostname: ' . $host);
    }

    // Skriptname anstelle von PHP_SELF verwenden
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

    // Basis-URL zusammenstellen
    $base_url = $protocol . $host . rtrim(dirname($scriptName), '/\\') . '/';

    // Wenn $cut angegeben ist, den Teil der URL abschneiden
    if ($cut !== '')
    {
      $pos = strrpos($base_url, $cut);

      if ($pos !== false)
      {
        $base_url = substr($base_url, 0, $pos);
      }
    }
    return $base_url;
  }


/**
  * Ermittelt den Basis-Pfad der Anwendung.
  *
  * Diese Funktion ermittelt den Basis-Pfad der Anwendung, indem sie das Verzeichnis
  * des Skripts verwendet. Sie kann optional einen Teil des Pfades abschneiden,
  * um den Basis-Pfad für Unterverzeichnisse zu erhalten.
  *
  * @param string $cut Ein optionaler Teil des Pfades, der abgeschnitten werden soll.
  *
  * @return string Der Basis-Pfad der Anwendung.
  */
  public static function get_base_path(string $cut = ''): string
  {
    // Basis-Pfad ermitteln
    $base_path = dirname($_SERVER['SCRIPT_FILENAME']) . '/';

    // Wenn $cut angegeben ist, den Teil des Pfades abschneiden
    if ($cut !== '')
    {
      $pos = strrpos($base_path, $cut);

      if ($pos !== false)
      {
        $base_path = substr($base_path, 0, $pos);
      }
    }
    return $base_path;

  }

}
