<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Urgences\CRPU;

CCanDo::checkEdit();

$rpu_id        = CView::get("rpu_id", "ref class|CRPU");
$sortie_reelle = CView::get("sortie_reelle", "dateTime");

CView::checkin();

// Chargement du RPU
$rpu = new CRPU();
$rpu->load($rpu_id);
$rpu->loadRefSejourMutation();

// Chargement du séjour
$sejour = $rpu->loadRefSejour();
$sejour->loadRefPatient()->loadIPP();
$sejour->loadNDA();
$sejour->loadRefsConsultations();
$sejour->loadRefCurrAffectation();

// Horaire par défaut
$sejour->sortie_reelle = $sortie_reelle;
if (!$sejour->sortie_reelle) {
  $sejour->sortie_reelle = CMbDT::dateTime();
}

$where           = array();
$where["entree"] = "<= '$sejour->sortie_reelle'";
$where["sortie"] = ">= '$sejour->sortie_reelle'";
//on recherche uniquement les lits bloqués pour les urgences(function_id == function_id d'urgence)
$where["function_id"] = "IS NOT NULL";

$affectation = new CAffectation();
/** @var CAffectation[] $blocages_lit */
$blocages_lit = $affectation->loadList($where);

$where["function_id"] = "IS NULL";

foreach ($blocages_lit as $blocage) {
  $blocage->loadRefLit()->loadRefChambre()->loadRefService();
  $where["lit_id"] = "= '$blocage->lit_id'";

  if ($affectation->loadObject($where)) {
    $affectation->loadRefSejour();
    $patient = $affectation->_ref_sejour->loadRefPatient();

    $blocage->_ref_lit->_view .= " indisponible jusqu'à " . CMbDT::transform($affectation->sortie, null, "%Hh%Mmin %d-%m-%Y");
    $blocage->_ref_lit->_view .= " (" . $patient->_view . " (" . strtoupper($patient->sexe) . ") ";
    $blocage->_ref_lit->_view .= CAppUI::conf("dPurgences age_patient_rpu_view") ? $patient->_age . ")" : ")";
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("rpu", $rpu);
$smarty->assign("blocages_lit", $blocages_lit);

$smarty->display("inc_form_sortie_lit");
