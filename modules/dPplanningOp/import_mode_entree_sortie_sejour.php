<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;

$mode_class = CView::get("mode_class", "str");
$file       = CValue::read($_FILES, "import");
CView::checkin();

$smarty = new CSmartyDP();
$smarty->assign("mode_class", $mode_class);

if (!$file) {
  $smarty->display("inc_import_mode_entree_sortie_sejour");
  CApp::rip();
}

$group_id = CGroups::loadCurrent()->_id;

$csv = new CCSVFile($file["tmp_name"], CCSVFile::PROFILE_EXCEL);
$csv->readLine();

while ($line = $csv->readLine()) {
  [
    $code,
    $libelle,
    $mode,
    $actif,
    $provenance
    ] = $line;

  /** @var CModeEntreeSejour|CModeSortieSejour $mode_entree_sortie */
  $mode_entree_sortie = new $mode_class;
  $mode_entree_sortie->code     = $code;
  $mode_entree_sortie->libelle  = $libelle;
  $mode_entree_sortie->mode     = $mode;
  $mode_entree_sortie->group_id = $group_id;

  if ($mode_entree_sortie->_class === "CModeEntreeSejour") {
      $mode_entree_sortie->provenance = $provenance;
  }

  $mode_entree_sortie->loadMatchingObject();
  $mode_entree_sortie->actif    = $actif ? 1 : 0;


  if ($msg = $mode_entree_sortie->store()) {
    CAppUI::displayAjaxMsg($msg, UI_MSG_WARNING);
    continue;
  }
  CAppUI::displayAjaxMsg("importation terminée");
}

$smarty->display("inc_import_mode_entree_sortie_sejour");
