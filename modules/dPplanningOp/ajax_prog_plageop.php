<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

$chir_id    = CView::get("chir_id", "ref class|CMediusers");
$plageop_id = CView::get("plageop_id", "ref class|CPlageOp");
$multiple   = CView::get("multiple", "bool default|0");
$rank       = CView::get("rank", "num");

CView::checkin();

$plageop = new CPlageOp();
$plageop->load($plageop_id);
$plageop->loadRefSalle();
$where = array("chir_id" => "= '$chir_id'");
$plageop->loadRefsOperations(false, null, true, null, $where);
$plageop->guessHoraireVoulu();

$rank_validated = array();
$rank_not_validated = array();

$_op = new COperation();
$_last_op = null;

$sejours = CStoredObject::massLoadFwdRef($plageop->_ref_operations, "sejour_id");
CStoredObject::massLoadFwdRef($sejours, "patient_id");

foreach ($plageop->_ref_operations as $_op) {
  $_op->loadRefChir()->loadRefFunction();
  $_op->loadRefSejour()->loadRefPatient()->loadRefDossierMedical()->countAllergies();
  $_op->loadExtCodesCCAM();

  if ($_op->_horaire_voulu) {
    $_last_op = $_op;
  }
}

$horaire_voulu = $plageop->debut;

if ($_last_op) {
  $horaire_voulu = $_last_op->_horaire_voulu;
  $horaire_voulu = CMbDT::addTime($_last_op->temp_operation, $horaire_voulu);
  $horaire_voulu = CMbDT::addTime($plageop->temps_inter_op, $horaire_voulu);
  $horaire_voulu = CMbDT::addTime($_last_op->pause, $horaire_voulu);
  $horaire_voulu = CMbDT::addTime($_last_op->duree_bio_nettoyage, $horaire_voulu);
  $horaire_voulu = CMbDT::addTime($_last_op->duree_postop, $horaire_voulu);
}

$new_op = new COperation();
$new_op->_horaire_voulu = $horaire_voulu;
$plageop->_ref_operations[] = $new_op;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("plageop" , $plageop);
$smarty->assign("multiple", $multiple);
$smarty->assign("rank"    , $rank);

$smarty->display("inc_prog_plageop");