<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$service_id = CView::get('service_id', "ref class|CService", true);
$date_min   = CView::get('_filter_date_min', 'date', true);
$date_max   = CView::get('_filter_date_max', 'date', true);
CView::checkin();

$ljoin = array();
$where = array();

//Récupération des séjours étant affectés dans le service
$ljoin["affectation"]            = "sejour.sejour_id = affectation.sejour_id";
$where[]                         = "affectation.entree <= '$date_max 23:59:59'";
$where[]                         = "affectation.sortie >= '$date_min 00:00:00'";
$where["affectation.service_id"] = " = '$service_id'";

//Vérification de l'existance d'une prescription
$ljoin["prescription"]              = "prescription.object_id = sejour.sejour_id";
$where["prescription.object_class"] = " = 'CSejour'";
$where["prescription.type"]         = " = 'sejour'";

$sejour = new CSejour();
/* @var CSejour[] $sejours */
$sejours = $sejour->loadList($where, null, null, "sejour.sejour_id", $ljoin);

CMbObject::massLoadFwdRef($sejours, "patient_id");
foreach ($sejours as $_sejour) {
  $_sejour->loadRefPatient();
}

$order_nom = CMbArray::pluck($sejours, "_ref_patient", "nom");
array_multisort($order_nom, SORT_ASC, $sejours);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign('sejours', $sejours);

$smarty->display('inc_stock_inventory');
