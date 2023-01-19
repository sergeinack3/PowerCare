<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Type d'affichage
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CSejour;

$view_sortie = CView::get("view_sortie", "str default|tous");
$date = CView::get("date", "date default|now");
CView::checkin();

// Chargement des urgences prises en charge
$ljoin                 = array();
$ljoin["rpu"]          = "sejour.sejour_id = rpu.sejour_id";
$ljoin["consultation"] = "consultation.sejour_id = sejour.sejour_id";

// Selection de la date
$date_tolerance    = CAppUI::conf("dPurgences date_tolerance");
$date_before       = CMbDT::date("-$date_tolerance DAY", $date);
$date_after        = CMbDT::date("+1 DAY", $date);
$where             = array();
$group             = CGroups::loadCurrent();
$where["group_id"] = " = '$group->_id'";
$where[]           = "sejour.entree BETWEEN '$date' AND '$date_after' 
  OR (sejour.sortie_reelle IS NULL AND sejour.entree BETWEEN '$date_before' AND '$date_after')";

// RPU Existants
$where["rpu.rpu_id"] = "IS NOT NULL";

if ($view_sortie == "sortie") {
  $where["sortie_reelle"] = "IS NULL";
}

if (in_array($view_sortie, array("normal", "mutation", "transfert", "deces"))) {
  $where["sortie_reelle"] = "IS NOT NULL";
  $where["mode_sortie"]   = "= '$view_sortie'";
}

$order = "sejour.sortie_reelle ASC, rpu.date_sortie_aut ASC, consultation.heure ASC";
$sejour = new CSejour();
/** @var CSejour[] $listSejours */
$listSejours = $sejour->loadList($where, $order, null, "sejour.sejour_id", $ljoin);
$sejours_to_order = array("no_date_sortie_aut" => array(), "sortie_reelle" => array());
foreach ($listSejours as &$_sejour) {
  $_sejour->loadRefsFwd();
  $_sejour->loadRefRPU();
  $_sejour->loadNDA();
  $_sejour->loadRefsConsultations();
  $_sejour->_veille = CMbDT::date($_sejour->entree) != $date;

  // Détail du RPU
  $rpu =& $_sejour->_ref_rpu;
  $rpu->loadRefSejourMutation();
  $rpu->loadRefConsult();
  $rpu->_ref_consult->countActes();

  // Détail du patient
  $patient =& $_sejour->_ref_patient;
  $patient->loadIPP();
  //Réordonnancement des séjours pour mettre en premier les patients ayant une sortie autorisee, puis ceux sans prise en charge
  // Puis ceux sortis
  if (!$rpu->date_sortie_aut) {
    unset($listSejours[$_sejour->_id]);
    $sejours_to_order["no_date_sortie_aut"][$_sejour->_id] = $_sejour;
  }
  elseif ($_sejour->sortie_reelle) {
    unset($listSejours[$_sejour->_id]);
    $sejours_to_order["sortie_reelle"][$_sejour->_id] = $_sejour;
  }
}
foreach ($sejours_to_order as $_sejours_to_add) {
  foreach ($_sejours_to_add as $_sejour_to_add) {
    $listSejours[$_sejour_to_add->_id] = $_sejour_to_add;
  }
}


// Chargement des services
$where              = array();
$where["externe"]   = "= '0'";
$where["cancelled"] = "= '0'";
$service            = new CService();
$services           = $service->loadGroupList($where);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("date", $date);
$smarty->assign("listSejours", $listSejours);
$smarty->assign("services", $services);
$smarty->assign("print", true);
$smarty->display("print_sortie_patients");
