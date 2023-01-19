<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Urgences\CMotifSFMU;

$motif_path = "modules/dPurgences/resources/motif_sfmu.csv";

$motif_sfmu = new CMotifSFMU();
$ds         = $motif_sfmu->getDS();
$ds->exec("TRUNCATE TABLE motif_sfmu");
CAppUI::stepAjax("motifs supprimés", UI_MSG_OK);

$handle    = fopen($motif_path, "r");
$motif_csv = new CCSVFile($handle);
$motif_csv->jumpLine(1);
$count     = 0;
$categorie = null;

while ($line = $motif_csv->readLine()) {
  list($libelle, $code) = $line;
  if (!$code) {
    $categorie = ucfirst(strtolower($libelle));
    continue;
  }
  $motif_sfmu            = new CMotifSFMU();
  $motif_sfmu->code      = $code;
  $motif_sfmu->libelle   = $libelle;
  $motif_sfmu->categorie = $categorie;

  if ($msg = $motif_sfmu->store()) {
    CAppUI::stepAjax($msg, UI_MSG_ERROR);
    $count--;
  }
  $count++;
}

CAppUI::stepAjax("$count motifs ajoutés", UI_MSG_OK);