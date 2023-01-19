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
use Ox\Core\CView;
use Ox\Interop\Ror\CRORException;
use Ox\Interop\Ror\CRORFactory;
use Ox\Interop\Ror\CRORSender;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Urgences\CExtractPassages;

CCanDo::checkAdmin();

CApp::setMemoryLimit("512M");

$debut_selection = CView::get("debut_selection", "dateTime");
$fin_selection   = CView::get("fin_selection", "dateTime");
CView::checkin();

$now    = $debut_selection ? $debut_selection : CMbDT::dateTime();
$before = CMbDT::dateTime("-4 DAY", $now);

// Chargement des rpu de la main courante
$sejour               = new CSejour;
$where                = array();
$ljoin["rpu"]         = "sejour.sejour_id = rpu.sejour_id";
$where["mode_sortie"] = " = 'deces'";
$where[]              = "sejour.entree BETWEEN '$before' AND '$now' 
  OR (sejour.sortie_reelle IS NULL AND sejour.entree BETWEEN '$before' AND '$now')";
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

  $entree_patient = CMbDT::date($_sejour->entree);

  if (!array_key_exists($entree_patient, $stats)) {
    $stats[$entree_patient] = array(
      "total"        => 0,
      "more_than_75" => 0,
    );
  }

  $stats[$entree_patient]["total"]++;

  // Statistiques  d'âge de patient
  $patient =& $_sejour->_ref_patient;

  if ($patient->_annees >= "75") {
    $stats[$entree_patient]["more_than_75"]++;
  }
}

if (count($stats) == 0) {
  $start_date = CMbDT::date($debut_selection);
  for ($i = 1; $i < 5; $i++) {
    $stats[CMbDT::date($start_date)]["total"]        = 0;
    $stats[CMbDT::date($start_date)]["more_than_75"] = 0;
    $start_date                                      = CMbDT::dateTime("-1 DAY", $start_date);
  }
}

$extractPassages                  = new CExtractPassages();
$extractPassages->date_extract    = CMbDT::dateTime();
$extractPassages->type            = "deces";
$extractPassages->debut_selection = $before;
$extractPassages->fin_selection   = $now;
$extractPassages->group_id        = CGroups::loadCurrent()->_id;
$extractPassages->store();

try {
  $rpuSender       = CRORFactory::getSender();
  $extractPassages = $rpuSender->extractDeces($extractPassages, $stats);
}
catch (CRORException $exception) {
  CAppUI::stepAjax($exception->getMessage(), UI_MSG_ERROR);
}

CAppUI::stepAjax("Extraction de $count_sejour séjours du " . CMbDT::dateToLocale($before) . " au " . CMbDT::dateToLocale($now) . " terminée.", UI_MSG_OK);
if (!$extractPassages->message_valide) {
  CAppUI::stepAjax("Le document produit n'est pas valide.", UI_MSG_WARNING);
}
else {
  CAppUI::stepAjax("Le document produit est valide.", UI_MSG_OK);
}

echo "<script>RPU_Sender.extract_passages_id = $extractPassages->_id;</script>";

