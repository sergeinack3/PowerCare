<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$sejour_id = CView::get("sejour_id", "ref class|CSejour");
CView::checkin();

// Chargement du séjour et des ses actes
$sejour  = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefPatient();
$sejour->loadRefPraticien();
$sejour->countActes();
$sejour->loadRefsActesNGAP(null, null, null, '0, 10');
$sejour->canDo();
$sejour->countExchanges();
$sejour->loadRefDossierMedical()->loadComplete();
$sejour->_ref_patient->loadRefDossierMedical()->loadComplete();

$actes_ngap = [$sejour->_guid => CActeNGAP::createEmptyFor($sejour)];

// Chargement des interventions et de leurs actes
foreach ($sejour->loadRefsOperations() as $_op) {
  $_op->loadRefPatient();
  $_op->loadRefAnesth()->loadRefFunction();
  $_op->loadRefPraticien()->loadRefFunction();
  $_op->loadRefPlageOp();
  $_op->loadRefSalle();
  $_op->countActes();
  $_op->loadRefsActesNGAP(null, null, null, '0, 10');
  $_op->canDo();
  $_op->countExchanges();
  $_op->loadRefsConsultAnesth()->loadRefConsultation()->loadRefPlageConsult();
  $actes_ngap[$_op->_guid] = CActeNGAP::createEmptyFor($_op);
}

// Chargement des consultations et de leurs actes
foreach ($sejour->loadRefsConsultations() as $_consult) {
  // On remet le séjour avec les loadRefs effectués en amont
  $_consult->_ref_sejour = $sejour;
  $_consult->loadRefPatient();
  $_consult->loadRefPraticien()->loadRefFunction();
  $_consult->countActes();
  $_consult->loadRefsActesNGAP(null, null, null, '0, 10');
  $_consult->canDo();
  $_consult->countExchanges();
  $actes_ngap[$_consult->_guid] = CActeNGAP::createEmptyFor($_consult);
}

// Déterminer le plus grand nombre d'actes du séjour ou de(s) l'intervention(s) pour sélectionner le volet qui contient le plus d'actes
$nb_actes = array($sejour->_guid => $sejour->_count_actes);

foreach ($sejour->_ref_operations as $_op) {
  $nb_actes += array($_op->_guid => $_op->_count_actes);
}

$guid_max_nb_actes = array_search(max($nb_actes), $nb_actes);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("guid_max_nb_actes", $guid_max_nb_actes);
$smarty->assign("actes_ngap", $actes_ngap);

$smarty->display("inc_vw_actes_pmsi");
