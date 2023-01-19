<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CValue;
use Ox\Mediboard\System\CFirstNameAssociativeSex;

CCanDo::checkAdmin();

ini_set("auto_detect_line_endings", true);
$targetPath = "modules/system/resources/firstnames.csv";

$callback = CValue::post("callback");

CApp::setTimeLimit(600);
CApp::setMemoryLimit("512M");

CMbObject::$useObjectCache = false;

importFile($targetPath);

CMbObject::$useObjectCache = true;
CApp::rip();

/**
 * import the csv firstname file
 *
 * @param string $targetPath filepath
 *
 * @return void
 */
function importFile($targetPath) {
  $fp = fopen($targetPath, 'r');

  $line_nb = 0;
  while ($line = fgetcsv($fp, null, ";")) {
    if ($line_nb == 0) {
      $line_nb++;
      continue;
    }

    $found = false;
    $fn = CMbString::removeDiacritics(trim($line[0]));
    $sex = trim($line[1]);
    $language = CMbString::removeDiacritics(trim($line[2]));
    if ($sex == "m,f" || $sex == "f,m") {
      $sex = "u";
    }

    $firstname = new CFirstNameAssociativeSex();
    $firstname->firstname = $fn;
    $firstname->language = $language;
    $firstname->loadMatchingObjectEsc();

    if ($firstname->_id) { // found
      $found = true;
      if ($sex != $firstname->sex) {
        $firstname->sex = "u";
      }
    }
    else { // not found
      $firstname->sex = $sex;
    }

    // store & message
    if ($msg = $firstname->store()) {
      CAppUI::stepAjax($msg, UI_MSG_WARNING);
    }
    else {
      if ($found == true) {
        CAppUI::stepAjax("prénom <strong>$fn</strong>, mis à jour <strong>[$firstname->sex]</strong>");
      }
      else {
        CAppUI::stepAjax("prénom <strong>$fn</strong>, ajouté <strong>[$firstname->sex]</strong>");
      }
    }
    $line_nb++;
  }

  return;
}