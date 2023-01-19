<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour_id      = CView::post("sejour_id", "ref class|CSejour");
$UHCD           = CView::post("UHCD", "bool");
$type           = CView::post("type", "str");
$lit_id         = CView::post("lit_id", "ref class|CLit");
$uf_soins_id    = CView::post("uf_soins_id", "ref class|CUniteFonctionnelle");
$uf_medicale_id = CView::post("uf_medicale_id", "ref class|CUniteFonctionnelle");
$charge_id      = CView::post("charge_id", "ref class|CChargePriceIndicator");
$affectation_id = CView::post("affectation_id", "ref class|CAffectation");
$mode_entree    = CView::post("mode_entree", "str");
$mode_entree_id = CView::post("mode_entree_id", "str");
$provenance     = CView::post("provenance", "str");
$date_aff       = CView::post("date_aff", "dateTime default|now");
$postRedirect   = CView::post("postRedirect", "str");

$sejour = new CSejour();
$sejour->load($sejour_id);
$sejour->_create_affectations = false;

// Passage en UHCD ou ATU du séjour
$sejour->charge_id = $charge_id;
$sejour->UHCD = $UHCD;
$sejour->type = $type;
$msg = $sejour->store();

CAppUI::setMsg($msg ? : "CSejour-msg-modify", $msg ? UI_MSG_ERROR : UI_MSG_OK);

// Changement du box sur le RPU
$rpu = $sejour->loadRefRPU();
$rpu->loadRefConsult()->loadRefSejour()->_create_affectations = false;
$rpu->box_id = $lit_id;
$rpu->_store_affectation = false;
$msg = $rpu->store();

CAppUI::setMsg($msg ? : "CRPU-msg-modify", $msg ? UI_MSG_ERROR : UI_MSG_OK);

// Création de l'affectation
$affectation = new CAffectation();
$affectation->load($affectation_id);
$affectation->sortie = $date_aff;

$affectation_cut = new CAffectation();
$affectation_cut->sejour_id = $sejour->_id;
$affectation_cut->entree = $date_aff;
$affectation_cut->sortie = $sejour->sortie;
$affectation_cut->lit_id = $lit_id;
$affectation_cut->uf_soins_id = $uf_soins_id;
$affectation_cut->uf_medicale_id = $uf_medicale_id;
$affectation_cut->mode_entree = $mode_entree;
$affectation_cut->mode_entree_id = $mode_entree_id;
$affectation_cut->provenance = $provenance;
$affectation_cut->uhcd = $UHCD;
$affectation_cut->praticien_id = $affectation->praticien_id ? : $sejour->praticien_id;

CSejour::$_cutting_affectation = true;

if ($affectation->_id) {
  $msg = $affectation->store();

  CAppUI::setMsg($msg ?: "CAffectation-msg-modify", $msg ? UI_MSG_ERROR : UI_MSG_OK);
}

$msg = $affectation_cut->store();

CAppUI::setMsg($msg ? : "CAffectation-msg-create", $msg ? UI_MSG_ERROR : UI_MSG_OK);

CAppUI::redirect($postRedirect);
