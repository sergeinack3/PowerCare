<?php

/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\Forms\CExClassEvent;

if ($praticien_id = CValue::post("praticien_id")) {
  CValue::setSession("praticien_id", $praticien_id);
}

$lit_id                  = CValue::post("lit_id");
$service_sortie_id       = CValue::post("service_sortie_id");
$mode_sortie             = CValue::post("mode_sortie");
$type                    = CValue::post("type");
$entree_preparee_trigger = CValue::post("_entree_preparee_trigger");
$sortie_preparee_trigger = CValue::post("_sortie_preparee_trigger");
$sejour_id               = CValue::post("sejour_id");
$create_aff              = CValue::post("_create_muta_aff", 1);

$create_affectation = CAppUI::conf("urgences create_affectation");

$sejour = new CSejour();
$sejour->load($sejour_id);

if ($sejour->type !== $type && $type !== "ambu") {
    $sejour->circuit_ambu = null;
}

$rpu = $sejour->loadRefRPU();

if ($rpu && $rpu->mutation_sejour_id) {
  $sejour_id = $sejour->_ref_rpu->mutation_sejour_id;
}

$sejour_hospit = new CSejour();
$sejour_hospit->load($sejour_id);

$curr_affectation_hospit = $sejour_hospit->loadRefCurrAffectation();

// Pour un séjour ayant comme mode de sortie urgence:
if ($create_aff && $create_affectation && $mode_sortie == "mutation" && $rpu && $rpu->_id
    && (!CModule::getActive('ecap') || CAppUI::gconf("ecap Urgences create_aff_sortie"))
    && ((!$lit_id || $curr_affectation_hospit->lit_id != $lit_id)
    || ($service_sortie_id && $curr_affectation_hospit->service_id != $service_sortie_id))
) {

  // Création de l'affectation d'hospitalisation
  $affectation_hospit = new CAffectation();
  $affectation_hospit->entree     = CValue::post("sortie_reelle") ? : CMbDT::dateTime();
  $affectation_hospit->lit_id     = $lit_id;
  $affectation_hospit->service_id = $service_sortie_id;
  $affectation_hospit->uf_medicale_id = $sejour_hospit->uf_medicale_id;

  // Mutation en provenance des urgences
  $affectation_hospit->_mutation_urg = true;
  $msg = $sejour_hospit->forceAffectation($affectation_hospit);

  if (!$msg instanceof CAffectation) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
    echo CAppUI::getMsg();
    return;
  }
}

// Lancement des formulaires automatiques sur le champ entrée préparée
if ($sejour->_id && $entree_preparee_trigger && CModule::getActive("forms")) {
  $ex_class_events = CExClassEvent::getForObject($sejour, "preparation_entree_auto", "required");
  echo CExClassEvent::getJStrigger($ex_class_events);
}

// Lancement des formulaires automatiques sur le champ sortie préparée
if ($sejour->_id && $sortie_preparee_trigger && CModule::getActive("forms")) {
  $ex_class_events = CExClassEvent::getForObject($sejour, "sortie_preparee_auto", "required");
  echo CExClassEvent::getJStrigger($ex_class_events);
}

$do = new CDoObjectAddEdit("CSejour");
$do->doIt();
