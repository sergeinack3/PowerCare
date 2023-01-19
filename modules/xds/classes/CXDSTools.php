<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds;

use DateTime;
use DateTimeZone;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\FileUtil\CCSVFile;

/**
 * Classe outils pour le module XDS
 */
class CXDSTools implements IShortNameAutoloadable {
  static $error = array();

  /**
   * Génération des jeux de valeurs en xml
   *
   * @return bool
   */
  static function generateXMLToJv() {
    $path = "modules/xds/resources/jeux_de_valeurs";
    $files = glob("$path/*.jv");

    foreach ($files as $_file) {
      self::jvToXML($_file, $path);
    }
    return true;
  }

  /**
   * Génére un xml d'après un jeu de valeurs
   *
   * @param String $file chemin du fichier
   * @param String $path Chemin du répertoire
   *
   * @return void
   */
  static function jvToXML($file, $path) {
    $name = self::deleteDate(basename($file));
    $csv = new CCSVFile($file);
    $csv->jumpLine(3);
    $xml = new CXDSXmlJvDocument();
    while ($line = $csv->readLine()) {
      [
        $oid,
        $code,
        $code_xds,
        ] = $line;
      $xml->appendLine($oid, $code, $code_xds);
    }
    $xml->save("$path/$name.xml");
  }

  /**
   * Supprime la date du nom des fichiers des jeux de valeurs
   *
   * @param String $name Nom du fichier
   *
   * @return string
   */
  static function deleteDate($name) {
    return substr($name, 0, strrpos($name, "_"));
  }

  /**
   * Retourne le datetime actuelle au format UTC
   *
   * @param String $date now
   *
   * @return string
   */
  static function getTimeUtc($date = "now") {
    $timezone_local = new DateTimeZone(CAppUI::conf("timezone"));
    $timezone_utc = new DateTimeZone("UTC");
    $date = new DateTime($date, $timezone_local);
    $date->setTimezone($timezone_utc);
    return $date->format("YmdHis");
  }

    /**
     * Retourne les informations de l'etablissement sous la forme HL7v2 XON
     *
     * @param String $libelle     Libelle
     * @param String $identifiant Identifiant
     *
     * @return string
     */
    public static function getXONetablissement($libelle, $identifiant): array
    {
        $comp1 = $libelle;
        $comp6 = "&1.2.250.1.71.4.2.2&ISO";
        $comp7 = null;

        $comp10 = $identifiant;

        return [
            'CX.1'  => $comp1,
            'CX.6'  => $comp6,
            'CX.7'  => $comp7,
            'CX.10' => $comp10,
        ];
    }

    /**
     * Retourne les informations du Mediuser sous la forme HL7v2 XCN
     *
     * @param String $identifiant Identifiant
     * @param String $lastname    Last name
     * @param String $firstname   First name
     *
     * @return array
     */
    public static function getXCNMediuser($identifiant, $lastname, $firstname): array
    {
        $comp1  = $identifiant;
        $comp2  = $lastname;
        $comp3  = $firstname;
        $comp9  = "&1.2.250.1.71.4.2.1&ISO";
        $comp10 = "D";
        $comp13 = "EI";

        return [
            'CX.1'  => $comp1,
            'CX.2'  => $comp2,
            'CX.3'  => $comp3,
            'CX.9'  => $comp9,
            'CX.10' => $comp10,
            'CX.13' => $comp13,
        ];
    }

  /**
   * Retourne l'INS sous la forme HL7v2
   *
   * @param String $ins  INS
   * @param String $type Type d'INS
   *
   * @return string
   */
  static function getINSPatient($ins, $type) {
    $comp1 = $ins;
    $comp4 = "1.2.250.1.213.1.4.2";
    $comp5 = "INS-$type";

    return "$comp1^^^&$comp4&ISO^$comp5";
  }

    /**
     * @param string|null $datetime
     *
     * @return float|int|string
     */
    public static function formatDatetime(?string $datetime)
    {
        return CMbDT::format($datetime, '%Y%m%d%H%M%S');
    }


    /**
     * @param string|null $datetime
     *
     * @return float|int|string
     */
    public static function formatDate(?string $datetime)
    {
        return CMbDT::format($datetime, '%Y%m%d');
    }

    /**
     * @param array  $components
     * @param string $prefix
     *
     * @return string
     */
    public static function serializeHL7v2Components(array $components, string $prefix = 'CX.'): string
    {
        $keys = array_map(
            function ($key) use ($prefix) {
                return intval(str_replace($prefix, '', $key));
            },
            array_keys($components)
        );

        if (empty($keys)) {
            return '';
        }
        $nb_components = max($keys);

        $parts = array_pad([], $nb_components, null);
        foreach ($components as $key => $component) {
            if ($component === null) {
                continue;
            }

            $key = intval(str_replace($prefix, '', $key)) - 1;

            $parts[$key] = $component;
        }

        return implode('^', $parts);
    }

    /**
     * @param array  $components
     * @param string $prefix
     *
     * @return array
     */
    public static function parseHL7v2Components(string $content, string $prefix = 'CX.'): array
    {
        $components = [];
        $parts = explode('^', $content);
        foreach ($parts as $key => $part) {
            $key = $key + 1;
            if ($part !== null && $part !== "") {
                $components[$prefix . $key] = $part;
            }
        }

        return $components;
    }
}
