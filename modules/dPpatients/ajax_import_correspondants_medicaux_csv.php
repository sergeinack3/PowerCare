<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CCSVImportCorrespondantMedical;

CCanDo::checkAdmin();

$force_update = CView::post("force_update", "bool default|0");

CView::checkin();

$file = isset($_FILES['formfile']) ? $_FILES['formfile']['tmp_name'] : null;

$results = array();

if ($file[0] && is_file($file[0])) {
  $import = new CCSVImportCorrespondantMedical($file[0]);
  $import->setOptions($force_update);
  $results = $import->import();
}

CAppUI::callbackAjax('$("systemMsg").insert', CAppUI::getMsg());

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("results", $results);
$smarty->display("inc_import_correspondants_medicaux_csv.tpl");
