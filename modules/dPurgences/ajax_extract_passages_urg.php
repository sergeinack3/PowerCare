<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CValue;
use Ox\Interop\Ror\CRORException;
use Ox\Interop\Ror\CRORFactory;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Urgences\CExtractPassages;

CCanDo::checkAdmin();

CApp::setMemoryLimit("512M");

$debut_selection = CValue::get("debut_selection");
$fin_selection   = CValue::get("fin_selection");

if (CModule::getActive('ror')) {
  $fin_selection   = $debut_selection ? $debut_selection : CMbDT::dateTime();
  $debut_selection = CMbDT::dateTime("-4 DAY", $debut_selection);
}
else {
  if (!$debut_selection || !$fin_selection) {
    $day_debut_selection   = CAppUI::gconf("ror General day_debut_selection");
    $heure_debut_selection = CAppUI::gconf("ror General heure_debut_selection");
    $day_fin_selection     = CAppUI::gconf("ror General day_fin_selection");
    $heure_fin_selection   = CAppUI::gconf("ror General heure_fin_selection");

    $fin_selection   = CMbDT::date("-$day_fin_selection DAY") . " $heure_fin_selection";
    $debut_selection = CMbDT::date("-$day_debut_selection DAY", $fin_selection) . " $heure_debut_selection";
  }
}

// Chargement des rpu de la main courante
$sejour       = new CSejour;
$where        = array();
$ljoin["rpu"] = "sejour.sejour_id = rpu.sejour_id";
$where[]      = "sejour.entree BETWEEN '$debut_selection' AND '$fin_selection' 
  OR (sejour.sortie_reelle IS NULL AND sejour.entree BETWEEN '$debut_selection' AND '$fin_selection')";
// RPUs
$where[]                  = "rpu.rpu_id IS NOT NULL";
$where["sejour.group_id"] = "= '" . CGroups::loadCurrent()->_id . "'";
$order                    = "sejour.entree ASC";
/** @var CSejour[] $sejours */
$sejours = $sejour->loadList($where, $order, null, null, $ljoin);

$count_sejour = 0;
$stats        = array();
// Détail du chargement
foreach ($sejours as &$_sejour) {
  $count_sejour++;

  $_sejour->loadRefsFwd(1);
  $_sejour->loadRefRPU();
  $_sejour->_ref_rpu->loadRefSejourMutation();

  $entree_patient = CMbDT::date($_sejour->entree);

  if (!array_key_exists($entree_patient, $stats)) {
    $stats[$entree_patient] = array(
      "total"                => 0,
      "mutations_count"      => 0,
      "mutations_uhcd_count" => 0,
      "transferts_count"     => 0,
      "less_than_1"          => 0,
      "more_than_75"         => 0,
    );
  }

  $stats[$entree_patient]["total"]++;

  // Statistiques de mutations de sejours
  $service_mutation = $_sejour->_ref_service_mutation;
  if ($service_mutation->_id) {
    $stats[$entree_patient]["mutations_count"]++;
    if ($service_mutation->uhcd) {
      $stats[$entree_patient]["mutations_uhcd_count"]++;
    }
  }

  // Statistiques de transferts de sejours
  $etablissement_tranfert = $_sejour->_ref_etablissement_transfert;
  if ($etablissement_tranfert->_id) {
    $stats[$entree_patient]["transferts_count"]++;
  }

  // Statistiques  d'âge de patient
  $patient =& $_sejour->_ref_patient;
  if ($patient->_annees < "1") {
    $stats[$entree_patient]["less_than_1"]++;
  }

  if ($patient->_annees >= "75") {
    $stats[$entree_patient]["more_than_75"]++;
  }
}

$extractPassages                  = new CExtractPassages();
$extractPassages->date_extract    = CMbDT::dateTime();
$extractPassages->type            = "urg";
$extractPassages->debut_selection = $debut_selection;
$extractPassages->fin_selection   = $fin_selection;
$extractPassages->group_id        = CGroups::loadCurrent()->_id;
$extractPassages->store();

$doc_valid = null;

// Appel de la fonction d'extraction du RPUSender
try {
  $rpuSender = CRORFactory::getSender();
  $extractPassages = $rpuSender->extractURG($extractPassages, $stats);

} catch (CRORException $exception) {
  CAppUI::stepAjax($exception->getMessage(), UI_MSG_ERROR);
}

CAppUI::stepAjax("Extraction de $count_sejour séjours du " . CMbDT::dateToLocale($debut_selection) . " au " . CMbDT::dateToLocale($fin_selection) . " terminée.", UI_MSG_OK);
if (!$extractPassages->message_valide) {
  CAppUI::stepAjax("Le document produit n'est pas valide.", UI_MSG_WARNING);
}
else {
  CAppUI::stepAjax("Le document produit est valide.", UI_MSG_OK);
}

echo "<script>RPU_Sender.extract_passages_id = $extractPassages->_id;</script>";

