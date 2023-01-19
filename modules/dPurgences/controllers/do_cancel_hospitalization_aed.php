<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Mediboard\PlanningOp\CSejour;

$sejour_guid = CValue::post("sejour_guid");

/** @var CSejour $sejour */
$sejour = CMbObject::loadFromGuid($sejour_guid);

if ($sejour && !$sejour->_id) {
  CAppUI::setMsg("Sejour non renseigné", UI_MSG_ERROR);
}

$rpu             = $sejour->loadRefRPU();
$sejour_mutation = $rpu->loadRefSejourMutation();
//Envoie d'un A07 que si le A06 a été envoyé
if ($sejour->sortie_reelle) {
  $sejour_mutation->_cancel_hospitalization = true;
}

//On annule le relicat
$sejour->mode_sortie   = "";
$sejour->entree_reelle = "";
$sejour->annule        = "1";

//On remet le séjour d'hospi en séjour d'urgence
$sejour_mutation->type   = CAppUI::gconf("dPurgences CRPU type_sejour") === "urg_consult" ? "consult" : "urg";
$sejour_mutation->charge_id = $sejour->charge_id;
$sejour_mutation->praticien_id = $sejour->praticien_id;
$sejour_mutation->libelle = $sejour->libelle ? : "";
$rpu->sejour_id          = $sejour_mutation->_id;
$rpu->mutation_sejour_id = "";
$rpu->sortie_autorisee   = "0";
$rpu->gemsa              = "";

//On supprime les affectations d'hospi
$affectations = $sejour_mutation->loadRefsAffectations();

foreach ($affectations as $_affectation) {
  $service = $_affectation->loadRefService();
  if ($service->uhcd || $service->radiologie || $service->urgence) {
    continue;
  }
  $_affectation->_no_synchro     = true;
  $_affectation->_no_synchro_eai = true;
  if ($msg = $_affectation->delete()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
    return;
  }
}

// Arrêt du traitement si un check ne passe pas
$msg_sejour      = $sejour->check();
$msg_sejour_muta = $sejour_mutation->check();
$msg_rpu         = $rpu->check();

if ($msg_sejour || $msg_sejour_muta || $msg_rpu) {
  return;
}

if ($msg = $sejour->store()) {
  CAppUI::setMsg($msg, UI_MSG_ERROR);
}

if ($msg = $sejour_mutation->store()) {
  CAppUI::setMsg($msg, UI_MSG_ERROR);
}

if ($msg = $rpu->store()) {
  CAppUI::setMsg($msg, UI_MSG_ERROR);
}