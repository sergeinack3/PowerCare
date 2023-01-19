w<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Personnel\CPlageConge;
use Ox\Mediboard\Ssr\CEvenementSSR;

CCanDo::checkRead();
//$date           = CView::get("date"        , "date default|now", true);
$sejour_id    = CView::get("sejour_id", "ref class|CSejour");
$remplacer_id = CView::get("remplacer_id", "num");
$plage_id     = CView::get("plage_id", "ref class|CPlageConge");
CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

// Plage de congés
$conge = new CPlageConge();
$conge->load($plage_id);

//Evénements du planing du praticien à remplacer
$ljoin = array();
$ljoin["sejour"] = "sejour.sejour_id = evenement_ssr.sejour_id";
$where                  = array();
$where[]                = "debut BETWEEN '$conge->date_debut' AND '$conge->date_fin'";
$where["therapeute_id"] = " = '$conge->user_id'";
$add_sejour = $sejour_id ? " AND sejour.sejour_id = '$sejour_id'" : "";
$where[] = "sejour.sejour_id IS NOT NULL".$add_sejour;
$seance_ssr             = new CEvenementSSR();
$evenements_to_replaced = $seance_ssr->loadList($where, "debut", null, "evenement_ssr_id", $ljoin);

//Evénements du planing du remplaçant
$where["therapeute_id"]                    = " = '$remplacer_id'";
$evenement                                 = new CEvenementSSR();
$evenements_remplacant                     = $evenement->loadList($where, "debut", null, "evenement_ssr_id", $ljoin);

$conflits = array();
foreach ($evenements_to_replaced as $evt_to_replaced) {
  foreach ($evenements_remplacant as $evt_replacant) {
    $evt_replacant->updateFormFields();
    $fin = CMbDT::date($evt_replacant->debut) . " " . $evt_replacant->_heure_fin;
    if ($evt_to_replaced->debut >= $evt_replacant->debut && $evt_to_replaced->debut <= $fin) {
      $conflits[$evt_to_replaced->_id] = $evt_to_replaced;
      continue;
    }
  }
}
/* @var CEvenementSSR[] $conflits */
foreach ($conflits as $_conflit) {
  $_conflit->loadRefSejour()->loadRefPatient();
}

$order = CMbArray::pluck($conflits, "debut");
array_multisort($order, SORT_ASC, $conflits);

$smarty = new CSmartyDP();

$smarty->assign("conflits", $conflits);
$smarty->assign("remplacement", 1);

$smarty->display("vw_alert_conflit_planification");
