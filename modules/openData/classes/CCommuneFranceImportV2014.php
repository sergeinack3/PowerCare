<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\OpenData;

use Ox\Core\CAppUI;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Files\CFile;

/**
 * Description
 */
class CCommuneFranceImportV2014 extends CCommuneImport {
  protected $file_url = "https://public.opendatasoft.com/explore/dataset/pop-sexe-age-nationalite-2014/download/
  ?format=csv&timezone=Europe/Berlin&use_labels_for_header=true";
  protected $zip_name = 'pop-sexe-age-nationalite-2014.zip';

  protected $_class = 'CCommuneDemographie';

  /**
   * @inheritdoc
   */
  function __construct() {
    parent::__construct($this->file_url);
    $this->file_path = rtrim(CFile::getDirectory(), '/\\') . '/upload/communes/pop-sexe-age-nationalite-2014.csv';
  }

  /**
   * @inheritdoc
   */
  function importFile($start = 0, $step = 100, $update = false) {
    $fp = fopen($this->file_path, 'r');
    $csv = new CCSVFile($fp, CCSVFile::PROFILE_EXCEL);

    // Setting columns names
    $csv->column_names = array_map('utf8_decode', $csv->readLine());

    if ($start > 1) {
      $csv->jumpLine($start-0);
    }

    $current_line = $start;
    while ($line = array_map('trim', array_map('utf8_decode', $csv->readLine(true, true)))) {
      if ($step > 0 && ($current_line-$start) >= $step) {
        break;
      }
      $current_line++;

      if (!isset($line['code géographique']) || !$line['code géographique']) {
        CAppUI::setMsg('mod-openData-insee-mandatory-line-%d', UI_MSG_WARNING, $current_line);
        continue;
      }

      $commune = new CCommuneFrance();
      $commune->loadByInsee($line['code géographique']);

      if (!$commune->_id || $update) {
        if (!isset($line['libellé géographique']) || !$line['libellé géographique']) {
          CAppUI::setMsg('mod-openData-libelle-mandatory-line-%d', UI_MSG_WARNING, $current_line);
          continue;
        }

        $commune->commune = $line['libellé géographique'];
        $commune->departement = isset($line['NOM_DEPT']) ? $line['NOM_DEPT'] : '';
        $commune->region = isset($line['NOM_REG']) ? $line['NOM_REG'] : '';

        $new = $commune->_id ? 'modify' : 'create';
        if ($msg = $commune->store()) {
          CAppUI::setMsg($msg, UI_MSG_WARNING);
          continue;
        }

        CAppUI::setMsg("CCommuneFrance-msg-$new", UI_MSG_OK);
      }
      else {
        CAppUI::setMsg("CCommuneFrance-msg-found", UI_MSG_OK);
      }

      if ($line['population'] === 0) {
        continue;
      }

      $demo = new CCommuneDemographie();
      $demo->commune_id = $commune->_id;
      $demo->annee = 2014;
      $demo->sexe = ($line['sexe'] == 'femmes') ? 'f' : 'm';
      switch ($line["âge regroupé (4 classes d'âges)"]) {
        case 'moins de 15 à 24 ans ans':
          $demo->age_max = 14;
          break;
        case '15 à 24 ans':
          $demo->age_min = 15;
          $demo->age_max = 24;
          break;
        case '25 à 54 ans':
          $demo->age_min = 25;
          $demo->age_max = 54;
          break;
        case '55 ans ou plus':
          $demo->age_min = 55;
          break;
        default:
          // Do nothing
      }
      $demo->nationalite = $line["indicateur de nationalité condensé (Français/Étranger)"] == "Français" ? 'francais' : "etranger";

      $demo->loadMatchingObjectEsc();

      if (!$demo->_id || $update) {
        $demo->population = $line['population'];

        $new = $demo->_id ? 'modify' : 'create';
        if ($msg = $demo->store()) {
          CAppUI::setMsg($msg, UI_MSG_WARNING);
          continue;
        }

        CAppUI::setMsg("CCommuneDemographie-msg-$new", UI_MSG_OK);
      }
      else {
        CAppUI::setMsg("CCommuneDemographie-msg-found", UI_MSG_OK);
      }
    }

    return $current_line;
  }
}
