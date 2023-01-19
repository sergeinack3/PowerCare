<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Patients\CINSPatient;

$csv = new CCSVFile("modules/dPpatients/resources/insc/Echantillon_de_test_INSC.csv", CCSVFile::PROFILE_EXCEL);
$csv->jumpLine(2);
$resultat = array("correct"   => 0,
                  "incorrect" => 0,
                  "total"     => 0);

while ($line = $csv->readLine()) {
  list(
    $firstName,
    $birthDate,
    $nir,
    $nirKey,
    $insc_csv,
    $insc_csv_Key,
    ) = $line;

  $firstName = CINSPatient::formatString($firstName);
  $insc      = CINSPatient::calculInsc($nir, $nirKey, $firstName, $birthDate);
  if ($insc === $insc_csv . $insc_csv_Key) {
    $resultat["correct"]++;
  }
  else {
    $resultat["incorrect"]++;
  }
  $resultat["total"]++;
}

$smarty = new CSmartyDP();
$smarty->assign("result", $resultat);
$smarty->display("ins/inc_test_insc_auto.tpl");