<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$sejour_id     = CView::get("sejour_id", "ref class|CSejour");
$sortie_reelle = CView::get("sortie_reelle", "dateTime");

CView::checkin();

// Chargement du sejour
$sejour = new CSejour();
$sejour->load($sejour_id);

// Chargement du séjour
$sejour->loadRefPatient()->loadIPP();
$sejour->loadNDA();
$sejour->loadRefsConsultations();
$sejour->_ref_patient->loadRefsAffectations();

// Horaire par défaut
$sejour->sortie_reelle = $sortie_reelle;

if (!$sejour->sortie_reelle) {
  $sejour->sortie_reelle = CMbDT::dateTime();
}

$service = new CService();
$services = $service->loadGroupList();

$where                = array();
$where["affectation.service_id"] = CSQLDataSource::prepareIn(array_keys($services));
$where["chambre.annule"] = "= '0'";
$where["lit.annule"]  = " = '0'";
$where["entree"]      = "<= '$sejour->sortie_reelle'";
$where["sortie"]      = ">= '$sejour->sortie_reelle'";
$where["function_id"] = "IS NOT NULL";

$ljoin = array(
  "chambre"     => "chambre.chambre_id = lit.chambre_id",
  "affectation" => "affectation.lit_id = lit.lit_id"
);
$lit = new CLit();

//Lit réservé pour les urgences
$lits_urgence = $lit->loadList($where, null, null, null, $ljoin);

$where["function_id"] = "IS NULL";
$where["sejour_id"]   = "IS NULL";
$where["lit.lit_id"]  = CSQLDataSource::prepareIn(array_keys($lits_urgence));

//lit qui sont bloqués
$lits_bloque = $lit->loadList($where, null, null, null, $ljoin);

$affectation = new CAffectation();
unset($where["lit.lit_id"]);
unset($where["sejour_id"]);
unset($where["lit.annule"]);
unset($where["chambre.annule"]);

if ($sejour->_ref_patient->_ref_curr_affectation->lit_id) {
  $lit = $sejour->_ref_patient->_ref_curr_affectation->loadRefLit();
  $lits_urgence[$lit->_id] = $lit;
}

/** @var CLit $_lit */
foreach ($lits_urgence as $_lit) {
  $sortie      = CMbDT::transform($affectation->sortie, null, "%Hh%M %d-%m-%Y");
  $_lit->loadRefService()->loadRefsChambres();
  if (array_key_exists($_lit->_id, $lits_bloque)) {
    $_lit->_view .= " (bloqué jusqu'au $sortie)";
    continue;
  }

  //On recherche une affectation d'un patient dans le lit d'urgence
  $where["lit_id"] = "= '$_lit->_id'";
  if (!$affectation->loadObject($where)) {
    continue;
  }

  $patient = $affectation->loadRefSejour()->loadRefPatient();

  // Lit avec un patient
  if ($patient->_id) {
    $_lit->_view .= " (".$patient->_view." (".strtoupper($patient->sexe)."))";
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("lits"  , $lits_urgence);

$smarty->display("inc_form_sortie_lit.tpl");
