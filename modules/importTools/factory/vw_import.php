<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CClassMap;
use Ox\Core\Import\CExternalDBImport;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();

$import_class = CView::get("import_class", "str");

$chir_id    = CView::get("chir_id", "str", true);
$last_id    = CView::get("last_id", "str", true);
$patient_id = CView::get("patient_id", "str", true);
$date_min   = CView::get("date_min", "str", true);
$date_max   = CView::get("date_max", "str", true);

CView::checkin();

$classes = $import_class::$import_sequence;

$instances = array();
foreach ($classes as $_class) {
  /** @var CExternalDBImport $_instance */
  $_instance = new $_class;
  if ($_instance->getStats()) {
    $instances[CClassMap::getInstance()->getShortName($_class)] = $_instance;
  }
}

// Création du template
$smarty = new CSmartyDP("modules/importTools");
$smarty->assign("instances", $instances);
$smarty->assign("chir_id", $chir_id);
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("last_id", $last_id);
$smarty->assign("patient_id", $patient_id);
$smarty->display("factory/vw_import.tpl");
