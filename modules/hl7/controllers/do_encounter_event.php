<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\PlanningOp\CSejour;

$event    = CValue::post("event");
$callback = CValue::post("callback");

switch ($event) {
  case "A01":
    $sejour = new CSejour();
    $sejour->sortie_prevue = CValue::post("sortie_prevue");
    $sejour->entree_prevue = CValue::post("entree_prevue");
    $sejour->entree_reelle = CValue::post("entree_reelle");
    $sejour->patient_id    = CValue::post("patient_id");
    $sejour->group_id      = CValue::post("group_id");
    $sejour->type          = CValue::post("type");
    $sejour->praticien_id  = CValue::post("praticien_id");

    if ($msg = $sejour->store()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }
    break;
  case "A02":
    $sejour_id = CValue::post("sejour_id");
    $unique_lit_id  = CValue::post("_unique_lit_id");

    $sejour = new CSejour();
    $sejour->load($sejour_id);

    $affectation = new CAffectation();
    $affectation->sejour_id = $sejour_id;
    $affectation->lit_id = $unique_lit_id;
    $affectation->entree = $sejour->entree;
    $affectation->sortie = $sejour->sortie;

    if ($msg = $affectation->store()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }

    break;
  case "A03":
    $sejour_id = CValue::post("sejour_id");
    $sejour = new CSejour();
    $sejour->load($sejour_id);
    $sejour->sortie_reelle = CValue::post("sortie_reelle");

    if ($msg = $sejour->store()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }
    break;
  case "A04":
    $sejour = new CSejour();
    $sejour->sortie_prevue = CValue::post("sortie_prevue");
    $sejour->entree_prevue = CValue::post("entree_prevue");
    $sejour->entree_reelle = CValue::post("entree_reelle");
    $sejour->patient_id    = CValue::post("patient_id");
    $sejour->group_id      = CValue::post("group_id");
    $sejour->type          = CValue::post("type");
    $sejour->praticien_id  = CValue::post("praticien_id");

    if ($msg = $sejour->store()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }
    break;
  case "A05":
    $sejour = new CSejour();
    $sejour->sortie_prevue = CValue::post("sortie_prevue");
    $sejour->entree_prevue = CValue::post("entree_prevue");
    $sejour->patient_id    = CValue::post("patient_id");
    $sejour->group_id      = CValue::post("group_id");
    $sejour->type          = CValue::post("type");
    $sejour->praticien_id  = CValue::post("praticien_id");

    if ($msg = $sejour->store()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }
    break;
  case "A11":
    $sejour_id = CValue::post("sejour_id");
    $sejour = new CSejour();
    $sejour->load($sejour_id);
    $sejour->annule = "1";

    if ($msg = $sejour->store()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }
    break;
  case "A12":
    $affectation_id = CValue::post("affectation_id");
    $affectation = new CAffectation();
    $affectation->load($affectation_id);
    if ($msg = $affectation->delete()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }
    break;
  case "A13":
    $sejour_id = CValue::post("sejour_id");
    $sejour = new CSejour();
    $sejour->load($sejour_id);
    $sejour->sortie_reelle = "";

    if ($msg = $sejour->store()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }
    break;
  case "INSERT":
    $sejour_id = CValue::post("sejour_id");
    $sejour = new CSejour();
    $sejour->load($sejour_id);
    $sejour->type = CValue::post("type");

    if ($sejour->type == "ambu" || $sejour->type == "urg") {
      $sejour->sortie_prevue = CMbDT::dateTime("+4 HOURS", $sejour->entree);

      if ($sejour->sortie_reelle) {
        $sejour->sortie_reelle = CMbDT::dateTime("+4 HOURS", $sejour->entree);
      }

      /* TODO Supprimer ceci après l'ajout des times picker */
      $sejour->_hour_entree_prevue = null;
      $sejour->_min_entree_prevue  = null;
      $sejour->_hour_sortie_prevue = null;
      $sejour->_min_sortie_prevue  = null;
    }
    elseif ($sejour->type == "comp") {
      $sejour->sortie_prevue = CMbDT::dateTime("+72 HOURS", $sejour->entree);

      if ($sejour->sortie_reelle) {
        $sejour->sortie_reelle = CMbDT::dateTime("+72 HOURS", $sejour->entree);
      }

      /* TODO Supprimer ceci après l'ajout des times picker */
      $sejour->_hour_entree_prevue = null;
      $sejour->_min_entree_prevue  = null;
      $sejour->_hour_sortie_prevue = null;
      $sejour->_min_sortie_prevue  = null;
    }

    if ($msg = $sejour->store()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }
    break;
  default:
    CAppUI::stepAjax("L'évenement choisit n'est pas supporté", UI_MSG_ERROR);
}

CAppUI::stepAjax("Evenement effectué");

if ($callback) {
  CAppUI::callbackAjax($callback);
}

CApp::rip();