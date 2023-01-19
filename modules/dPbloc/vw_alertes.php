<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\PlanningOp\CIntervHorsPlage;
use Ox\Mediboard\PlanningOp\COperation;

$type      = CView::get("type", "str default|week");
$date      = CView::get("date", "date default|now");
$edit_mode = CView::get("edit_mode", "bool default|0");
$blocs_ids = CView::get("blocs_ids", "str");

CView::checkin();

if ($type == "week") {
  $date = CMbDT::date("last sunday", $date);
  $fin  = CMbDT::date("next sunday", $date);
  $date = CMbDT::date("+1 day", $date);
}
else {
  $fin = $date;
}

//alerts
$nbAlertes = 0;
$nbNonValide = 0;
$nbHorsPlage = 0;
$bloc            = new CBlocOperatoire();
$blocs           = $bloc->loadList(array("bloc_operatoire_id" => CSQLDataSource::prepareIn($blocs_ids)));
$where = array();

/** @var CBlocOperatoire[] $blocs */
foreach ($blocs as $_bloc) {
  $_bloc->loadRefsSalles();
  $alertes = $_bloc->loadRefsAlertesIntervs();
  foreach ($alertes as $_alerte) {
    $nbAlertes++;
    /** @var COperation $operation */
    $operation = $_alerte->loadTargetObject();
    $operation->loadExtCodesCCAM();
    $operation->loadRefPlageOp();
    $operation->loadRefPraticien()->loadRefFunction();
    $operation->loadRefPatient();
  }

  $operation = new COperation();

// Liste des interventions non validées
  $ljoin                  = array();
  $ljoin["plagesop"]      = "operations.plageop_id = plagesop.plageop_id";
  $where                  = array();
  $where["plagesop.date"] = "BETWEEN '$date' AND '$fin'";
  if ($_bloc->_id) {
    $salles                     = $_bloc->loadRefsSalles();
    $where["plagesop.salle_id"] = CSQLDataSource::prepareIn(array_keys($salles));
  }
  $where["operations.annulee"] = "= '0'";
  $where["operations.rank"]    = "= '0'";
  $order                       = "plagesop.date, plagesop.chir_id";

  /** @var COperation[] $listNonValidees */
  $listNonValidees[$_bloc->_id] = $operation->loadList($where, $order, null, null, $ljoin);

  foreach ($listNonValidees[$_bloc->_id] as $_operation) {
    $nbNonValide++;
    $_operation->loadRefPlageOp();
    $_operation->loadExtCodesCCAM();
    $_operation->loadRefPraticien()->loadRefFunction();
    $_operation->loadRefPatient();
  }

  $listHorsPlage[$_bloc->_id] = CIntervHorsPlage::getForDates($date, $fin, null, array_keys($_bloc->_ref_salles));
  foreach ($listHorsPlage[$_bloc->_id] as $_operation) {
    $nbHorsPlage++;
    $_operation->loadRefPlageOp();
    $_operation->loadExtCodesCCAM();
    $_operation->loadRefPraticien()->loadRefFunction();
    $_operation->loadRefPatient();
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("blocs"           , $blocs);
$smarty->assign("nbAlertes"       , $nbAlertes);
$smarty->assign("nbNonValide"     , $nbNonValide);
$smarty->assign("nbHorsPlage"     , $nbHorsPlage);
$smarty->assign("listNonValidees" , $listNonValidees);
$smarty->assign("listHorsPlage"   , $listHorsPlage);
$smarty->assign("edit_mode"       , $edit_mode);

$smarty->display("vw_alertes.tpl");