<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Hospi\CCible;

CCanDo::checkRead();

$sejour_id       = CView::get("sejour_id", "ref class|CSejour");
$object_id       = CView::get("object_id", "num");
$object_class    = CView::get("object_class", "str");
$libelle_ATC     = CView::get("libelle_ATC", "str");
$cible_id        = CView::get("cible_id", "num");
$focus_area      = CView::get("focus_area", "str");
$transmission_id = CView::get("transmission_id", "ref class|CTransmissionMedicale");
$data_id         = CView::get("data_id", "ref class|CTransmissionMedicale");
$action_id       = CView::get("action_id", "ref class|CTransmissionMedicale");
$result_id       = CView::get("result_id", "ref class|CTransmissionMedicale");

CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

$cible            = new CCible();
$cible->sejour_id = $sejour_id;
if ($object_id && $object_class) {
  $cible->object_id    = $object_id;
  $cible->object_class = $object_class;
  $cible->loadTargetObject();
}
if ($libelle_ATC) {
  $cible->libelle_ATC = $libelle_ATC;
}

$cibles = array();
if (($object_id && $object_class) || $libelle_ATC) {
  $cibles = $cible->loadMatchingList();
  CStoredObject::massLoadBackRefs($cibles, "transmissions", "date ASC, transmission_medicale_id ASC");

  foreach ($cibles as $_cible) {
    $_cible->loadView();
  }
}
$smarty = new CSmartyDP();

$smarty->assign("cible", $cible);
$smarty->assign("cibles", $cibles);
$smarty->assign("first_cible", reset($cibles));
$smarty->assign("cible_id", $cible_id);
$smarty->assign("object_id", $object_id);
$smarty->assign("object_class", $object_class);
$smarty->assign("libelle_ATC", $libelle_ATC);
$smarty->assign("focus_area", $focus_area);
$smarty->assign("transmission_id", $transmission_id);
$smarty->assign("data_id", $data_id);
$smarty->assign("action_id", $action_id);
$smarty->assign("result_id", $result_id);

$smarty->display("inc_list_cibles.tpl");