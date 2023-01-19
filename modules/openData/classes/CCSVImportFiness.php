<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\OpenData;

use Ox\Core\Import\CMbCSVObjectImport;
use Ox\Core\CMbMath;
use Ox\Core\FileUtil\CCSVFile;

/**
 * Description
 */
class CCSVImportFiness extends CMbCSVObjectImport {
  protected $dir_path;
  protected $line;
  protected $update;
  protected $geolocalisation;

  protected $regexp_extract = "/(.*)\s?:\s(\d+)/";
  protected $categories = array("101", "106", "122", "128", "129", "131", "141", "146", "246", "355", "365", "405", "697");

  /**
   * @inheritdoc
   */
  function __construct($file_path, $dir_path, $update, $geolocalisation) {
    parent::__construct($file_path);
    $this->dir_path        = $dir_path;
    $this->geolocalisation = $geolocalisation;
    $this->update          = $update;
  }

  /**
   * @inheritdoc
   */
  function import() {
    //$executionStartTime = microtime(true);
    $this->openFile();
    $this->setColumnNames();
    $this->current_line = 1;

    $etablissementsET = array();

    $valid = false;
    do {
      $line = $this->readAndSanitizeLine();

      $etab         = new CHDEtablissement();
      $etab->finess = $line["nofinesset"];
      $used         = "et";
      $etab->loadMatchingObject();

      if (!$etab->_id) {
        $etab         = new CHDEtablissement();
        $etab->finess = $line["nofinessej"];
        $used         = "ej";
        $etab->loadMatchingObject();
      }


      if ($etab->_id) {
        if ($used == "ej") {
          if (in_array($line["categetab"], $this->categories)) {
            $valid = true;
          }
          else {
            $valid = false;
          }
        }
        else {
          if ($used == "et") {
            $valid = true;
          }
        }

        if ($valid) {
          // if(CMbString::isSimilar($etab->raison_sociale, $line["rs"], 80)
          // || CMbString::isSimilar($etab->raison_sociale, $line["rslongue"], 80)){
          $data          = $line["ligneacheminement"];
          $coordonnees   = explode(" ", $data);
          $cp            = $coordonnees[0];
          $ville         = str_replace($coordonnees[0] . " ", "", $data);
          $etab->ville   = $ville;
          $etab->cp      = $cp;
          $etab->adresse = $line["numvoie"] . " " . $line["typvoie"] . " " . $line["voie"];
          $etab->store();

          if ($this->geolocalisation) {
            $etablissementsET[$line["nofinesset"]] = $etab->finess;
          }
        }
      }


      $this->current_line++;
    } while (!empty($line));

    $this->csv->close();

    if ($this->geolocalisation) {
      //$geoexecutionStartTime = microtime(true);
      $math      = new CMbMath();
      $file_path = $this->dir_path . '/finess_geolocalisation.csv';
      $this->fp  = fopen($file_path, 'r');
      $this->csv = new CCSVFile($this->fp, $this->profile);

      $this->setColumnNames();
      $this->current_line = 1;

      do {
        $line = $this->readAndSanitizeLine();

        if (isset($etablissementsET[$line["nofinesset"]])) {
          $etab         = new CHDEtablissement();
          $etab->finess = $etablissementsET[$line["nofinesset"]];
          $etab->loadMatchingObject();
          if ($etab->_id) {
            $coordonnees = $math->lambert93ToWgs84($line["coordxet"], $line["coordyet"]);

            $etab->loadRefGeolocalisation();

            if (!$etab->_ref_geolocalisation || !$etab->_ref_geolocalisation->_id) {
              $etab->createGeolocalisationObject();
            }

            $etab->setLatLng($coordonnees["wgs84"]["lat"] . "," . $coordonnees["wgs84"]["long"]);
            $etab->setProcessed();
          }
        }

        $this->current_line++;
      } while (!empty($line));
      $this->csv->close();
      //$geoexecutionEndTime = microtime(true);
      //$seconds = $geoexecutionEndTime - $geoexecutionStartTime;
      //mt("geolocalisation.csv : ".$seconds);
    }

    //$executionEndTime = microtime(true);
    //$seconds = $executionEndTime - $executionStartTime;
    //mt("total : ".$seconds);

    //TODO ajouter création de conflits pour les établissements orphelins
  }

  /**
   * Return the fields from the CSV
   *
   * @param array $fields Associative array fields from a CHDClass
   *
   * @return array
   */
  function getFields($fields) {
    $return = array();
    foreach ($fields as $_csv_key => $_class_key) {
      $return[$_class_key] = $this->line[$_csv_key];
    }

    return $return;
  }

  function setAdress() {

  }
}
