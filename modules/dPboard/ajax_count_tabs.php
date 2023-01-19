<?php
/**
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSecondaryFunction;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

// get
$user            = CUser::get();
$boardItem       = CView::get("boardItem", "bool default|0");
$plageconsult_id = CView::get("plageconsult_id", "ref class|CPlageconsult");
$board           = CView::get("board", "bool default|0");
// get or session
$date       = CView::get("date", "date default|now", true);
$date_op    = CView::get("date_op", "date default|now", true);
$prat_id    = CView::get("chirSel", "num default|" . $user->_id, true);
$selConsult = CView::get("selConsult", "num", true);
$vue        = CView::get("vue2", "bool default|0", true);
$withClosed = CView::get("withClosed", "bool default|0", true);

$canceled     = CView::get("canceled", "bool default|0", true);
$hiddenPlages = CView::get("hiddenPlages", "str");
$praticien_id = CView::get("praticien_id", "str", true);

$nb_consult = 0;
$nb_op      = 0;
$countArray = array();

$today = CMbDT::date();
if (!$board && !$boardItem) {
  $withClosed = 1;
}
else {
  $vue = 0;
}

$consult = new CConsultation();
// Test compliqué afin de savoir quelle consultation charger
if (isset($_GET["selConsult"])) {
  if ($consult->load($selConsult)) {
    $consult->loadRefPlageConsult(1);
    $prat_id = $consult->_ref_plageconsult->chir_id;
    CView::setSession("chirSel", $prat_id);
  }
  else {
    CView::setSession("selConsult");
  }
}
else {
  if ($consult->load($selConsult)) {
    $consult->loadRefPlageConsult(1);
    if ($prat_id !== $consult->_ref_plageconsult->chir_id) {
      $consult = new CConsultation();
      CView::setSession("selConsult");
    }
  }
}

// On charge le praticien
$userSel = CMediusers::get($prat_id);

if ($consult->_id) {
  $date = $consult->_ref_plageconsult->date;
}
//Fermeture de session (après les set sessions)
CView::checkin();
// Récupération des plages de consultation du jour et chargement des références
$plage          = new CPlageconsult();
$plage->chir_id = $userSel->_id;
$plage->date    = $date;
if ($plageconsult_id && $boardItem) {
  $plage->plageconsult_id = $plageconsult_id;
}
$order = "debut";
/** @var CPlageconsult[] $listPlage */
$listPlage = $plage->loadMatchingList($order);

foreach ($listPlage as $_plage) {
  $consultations = $_plage->loadRefsConsultations(false, !$vue && $withClosed);
  $nb_consult    = count($consultations);
}

//operations
$current_group = CGroups::loadCurrent();

// Urgences du jour
$operation = new COperation();

// Liste des opérations du jour sélectionné
$list_plages = array();

if ($userSel->_id) {
  $userSel->loadBackRefs("secondary_functions");
  $secondary_specs = array();
  /** @var CSecondaryFunction $_sec_spec */
  foreach ($userSel->_back["secondary_functions"] as $_sec_spec) {
    $secondary_specs[] = $_sec_spec->function_id;
  }
  $where                  = array();
  $where["plagesop.date"] = "= '$date_op'";

  $in = "";
  if (count($secondary_specs)) {
    $in = " OR plagesop.spec_id " . CSQLDataSource::prepareIn($secondary_specs);
  }

  $where[] = "plagesop.chir_id = '$userSel->_id'
              OR plagesop.anesth_id = '$userSel->_id'
              OR operations.anesth_id = '$userSel->_id'
              OR plagesop.spec_id = '$userSel->function_id' $in
              OR (plagesop.chir_id IS NULL AND plagesop.spec_id IS NULL AND plagesop.urgence = '1')";
  $order   = "debut, salle_id";

  $ljoin = array(
    "operations" => "plagesop.plageop_id = operations.plageop_id"
  );

  $plageop = new CPlageOp();

  /** @var CPlageOp[] $list_plages */
  $list_plages = $plageop->loadList($where, $order, null, "plagesop.plageop_id", $ljoin);

  foreach ($list_plages as $_plage) {

    $where = array();
    if ($userSel->_id) {
      if ($userSel->isAnesth()) {
        if (!$_plage->anesth_id) {
          $where["anesth_id"] = "= '$userSel->_id'";
        }
      }
      else {
        $where["chir_id"] = "= '$userSel->_id'";
      }
    }

    $operations = $_plage->loadRefsOperations($canceled, "annulee ASC, rank, rank_voulu, horaire_voulu", true, null, $where);

    $nb_op = count($operations);
  }
}

// Compteur
$countArray["consultations"] = $nb_consult;
$countArray["operations"]    = $nb_op;

CApp::json($countArray);
