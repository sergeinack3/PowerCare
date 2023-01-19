<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Hospi\CTransmissionMedicale;

CCanDo::checkRead();

$sejour_id    = CView::get("sejour_id", "ref class|CSejour");
$libelle_ATC  = CView::get("libelle_ATC", "str");
$object_id    = CView::get("object_id", "num");
$object_class = CView::get("object_class", "str");
$cible_id     = CView::get("cible_id", "ref class|CCible");

CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

$transmission = new CTransmissionMedicale();
$transmissions = array();
$where = array();
$where["sejour_id"] = " = '$sejour_id'";
$where["cancellation_date"] = " IS NULL";

if ($cible_id) {
  $where["cible_id"] = "= '$cible_id'";
}
else if ($object_id) {
  $where["object_id"] = " = '$object_id'";
  $where["object_class"] = " = '$object_class'";
}
else {
  $where["libelle_ATC"] = " LIKE '".addslashes($libelle_ATC)."'";
}

$transmission->cible_id = $cible_id;
$transmission->object_class = $object_class;
$transmission->object_id = $object_id;
if ($transmission->cible_id) {
  $transmission->loadRefCible()->loadTargetObject();
  $transmission->_ref_cible->loadView();
}
elseif ($transmission->object_id) {
  $transmission->loadTargetObject();
}

$order_by = "DATE DESC, transmission_medicale_id DESC";

/** @var CTransmissionMedicale[] $transmissions */
$transmissions = $transmission->loadList($where, $order_by);

foreach ($transmissions as $_transmission) {
  $_transmission->loadRefUser();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("transmission" , $transmission);
$smarty->assign("transmissions", $transmissions);

$smarty->display("inc_list_transmissions_short.tpl");

