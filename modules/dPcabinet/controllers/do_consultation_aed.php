<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Praticien courant pour les prises de rendez-vous suivantes
use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CValue;

if ($chir_id = CValue::post("chir_id")) {
  CValue::setSession("chir_id", $chir_id);
}

// Consultation courante dans edit_consulation
if (CValue::post("del")) {
  CValue::setSession("selConsult");
}

// before basic job, do the multiple consultations
CAppUI::requireModuleFile("dPcabinet", "controllers/do_consultation_multiple");

// Cas de l'annulation / rétablissement / supression multiple
if ($consultation_ids = CValue::post("consultation_ids")) {
  $_POST_Temp = array(
    "consultation_ids" => CValue::post("consultation_ids"),
    "del"              => CValue::post("del"),
    "annule"           => CValue::post("annule", 0),
    "sejour_id"        => CValue::post("sejour_id"),
    "postRedirect"     => CValue::post("postRedirect"),
    "callback"         => CValue::post("callback"),
    "ajax"             => CValue::post("ajax"),
  );
  if (CValue::post("annule")) {
    $_POST_Temp["motif_annulation"] = CValue::post("motif_annulation");
    $_POST_Temp["rques"] = CValue::post("rques");
    $_POST_Temp["_cancel_sejour"] = CValue::post("_cancel_sejour");
  }
  $_POST = $_POST_Temp;
}

//consult n°1, classic use
$do = new CDoObjectAddEdit("CConsultation");
$do->doIt();
