<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\ESatis\CEsatisConsent;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$sejour_id = CView::get("sejour_id", "ref class|CSejour");
$module    = CView::get("module", "str");
$callback  = CView::get("callback", "str");

CView::checkin();

$group = CGroups::loadCurrent();

$sejour = new CSejour();
$sejour->load($sejour_id);
$sejour->loadNDA();

$sejour->loadRefServiceMutation();
$sejour->loadRefEtablissementTransfert();
$sejour->loadRefEtablissementProvenance();
$sejour->loadRefsOperations();

//Cas des urgences
if (CModule::getActive("dPurgences")) {
  $sejour->loadRefRPU()->loadRefSejourMutation();
}

$patient = $sejour->loadRefPatient();
$patient->loadIPP();

// maternité
if (CModule::getActive("maternite") && $sejour->grossesse_id) {
  $sejour->loadRefsNaissances();
  foreach ($sejour->_ref_naissances as $_naissance) {
    /** @var CNaissance $_naissance */
    $_naissance->loadRefSejourEnfant()->loadRefPatient();
  }
  $sejour->_sejours_enfants_ids = CMbArray::pluck($sejour->_ref_naissances, "sejour_enfant_id");

  // Décalage si l'admission est avancée :
  // Entrée prévue => entrée réelle
  // Sortie prévue => entrée réelle + n nuits (durée initiale du séjour)
  if (!$sejour->entree_reelle && CMbDT::daysRelative(CMbDT::dateTime(), $sejour->entree_prevue) > 0) {
    $days = CMbDT::daysRelative($sejour->entree_prevue, $sejour->sortie_prevue);
    $sejour->entree_prevue = CMbDT::dateTime();
    $sejour->sortie_prevue = CMbDT::dateTime("+$days DAYS", CMbDT::dateTime());
  }
}

// E-Satis
$eSatis_active = CModule::getActive("eSatis");
if ($eSatis_active) {
  $esatis_consent = new CEsatisConsent();
  $esatis_consent->sejour_id = $sejour->_id;
  $esatis_consent->loadMatchingObject();
}

// Liste des modes d'entrée
$mode_entree = new CModeEntreeSejour();
$mode_entree->actif = 1;
$mode_entree->group_id = $group->_id;
$modes_entree = $mode_entree->loadMatchingList("libelle");

// Liste des UF de soins SSI :
// * pas déjà saisie,
// * pas d'affectation,
// * que le mode est "obligatoire"
// * qu'on en a paramétré au moins une
$list_uf_soin = array();
$show_list_uf = !$sejour->uf_soins_id && CAppUI::gconf("dPplanningOp CSejour required_uf_soins") == "obl";

if ($show_list_uf) {
  $affectations = $sejour->loadRefsAffectations();

  if (count($affectations) > 0) {
    $show_list_uf = false;
  }
}

if ($show_list_uf) {
  $uf_soin = new CUniteFonctionnelle();
  $uf_soin->group_id = $group->_id;
  $uf_soin->type = "soins";
  $list_uf_soin = $uf_soin->loadMatchingList("libelle");

  if (count($list_uf_soin) == 0) {
    $show_list_uf = false;
  }
}

// Idem pour l'UF médicale
$list_uf_med = array();
$show_list_uf_med = !$sejour->uf_medicale_id && CAppUI::gconf("dPplanningOp CSejour required_uf_med") === "obl";

if ($show_list_uf_med) {
  $affectations = $sejour->loadRefsAffectations();

  if (count($affectations) > 0) {
    $show_list_uf_med = false;
  }
}

if ($show_list_uf_med) {
  $uf_med = new CUniteFonctionnelle();
  $uf_med->group_id = $group->_id;
  $uf_med->type = "medicale";
  $list_uf_med = $uf_med->loadMatchingList("libelle");

  if (!count($list_uf_med)) {
    $show_list_uf_med = false;
  }
}

$service = new CService();
$services = $service->loadGroupList();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("callback"        , stripslashes($callback));
$smarty->assign("sejour"          , $sejour);
$smarty->assign("module"          , $module);
$smarty->assign("list_mode_entree", $modes_entree);
$smarty->assign("list_uf_soin"    , $list_uf_soin);
$smarty->assign("show_list_uf"    , $show_list_uf);
$smarty->assign("list_uf_med"     , $list_uf_med);
$smarty->assign("show_list_uf_med", $show_list_uf_med);
$smarty->assign("services"        , $services);

// E-Satis
if ($eSatis_active) {
  $smarty->assign("esatis_consent", $esatis_consent);
}
$smarty->display("inc_edit_entree.tpl");
