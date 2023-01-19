<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CEtabExterne;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CProtocole;
use Ox\Mediboard\PlanningOp\CSejour;

$mode_operation  = CValue::get("mode_operation", 0);
$sejour_id       = CValue::get("sejour_id"     , 0);
$patient_id      = CValue::get("patient_id"    , 0);
$protocole_id    = CValue::get("protocole_id");
$contextual_call = CValue::get("contextual_call");

// Liste des Etablissements selon Permissions
$etablissements = new CMediusers();
$etablissements = $etablissements->loadEtablissements(PERM_READ);

// Chargement des prestations
$prestations = CPrestation::loadCurrentList();

$sejour = new CSejour;
$praticien = new CMediusers;
if ($sejour_id) {
  $sejour->load($sejour_id);

  CAccessMedicalData::logAccess($sejour);

  $sejour->loadRefsFwd();
  $praticien =& $sejour->_ref_praticien;
  $praticien->canDo();
  $patient =& $sejour->_ref_patient;
  $patient->loadRefsSejours();
  $sejours =& $patient->_ref_sejours;
}
else {
  $patient = new CPatient;
  $patient->load($patient_id);
  $patient->loadRefsSejours();
  $sejours =& $patient->_ref_sejours;
}

$sejour->makeDatesOperations();
$sejour->loadNDA();

$sejour->loadRefCurrAffectation()->loadRefService();
$sejour->_ref_curr_affectation->updateView();
$sejour->loadRefsAffectations();

$patient->loadRefsFwd();
$patient->loadRefMedecinTraitant();
$patient->loadRefsCorrespondants();
$patient->loadRefsCorrespondantsPatient();

$correspondantsMedicaux = array();
if ($patient->_ref_medecin_traitant->_id) {
  $correspondantsMedicaux["traitant"] = $patient->_ref_medecin_traitant;
}
foreach ($patient->_ref_medecins_correspondants as $correspondant) {
  $correspondantsMedicaux["correspondants"][] = $correspondant->_ref_medecin;
}

$medecin_adresse_par = "";
if ($sejour->adresse_par_prat_id && ($sejour->adresse_par_prat_id != $patient->_ref_medecin_traitant->_id)) {
  $medecin_adresse_par = $sejour->loadRefAdresseParPraticien();
}

// L'utilisateur est-il un praticien
$mediuser = CMediusers::get();

$sortie_sejour = CMbDT::dateTime();
if ($sejour->sortie_reelle) {
  $sortie_sejour = $sejour->sortie_reelle;
}

$where = array();
$where["entree"] = "<= '".$sortie_sejour."'";
$where["sortie"] = ">= '".$sortie_sejour."'";
$where["function_id"] = "IS NOT NULL";

$affectation = new CAffectation();
/** @var CAffectation[] $blocages_lit */
$blocages_lit = $affectation->loadList($where);

$where["function_id"] = "IS NULL";

foreach ($blocages_lit as $key => $blocage) {
  $blocage->loadRefLit()->loadRefChambre()->loadRefService();
  $where["lit_id"] = "= '$blocage->lit_id'";
  if (!$sejour->_id && $affectation->loadObject($where)) {
    $affectation->loadRefSejour();
    $affectation->_ref_sejour->loadRefPatient();
    $blocage->_ref_lit->_view .= " indisponible jusqu'à ".CMbDT::transform($affectation->sortie, null, "%Hh%Mmin %d-%m-%Y")." (".$affectation->_ref_sejour->_ref_patient->_view.")";
  }
}

// Configuration
$group = CGroups::loadCurrent();
$config = CAppUI::conf("dPplanningOp CSejour");
$hours = range($config["heure_deb"], $config["heure_fin"]);
$mins = range(0, 59, $config["min_intervalle"]);
$heure_sortie_ambu   = CAppUI::conf('dPplanningOp CSejour default_hours heure_sortie_ambu', $group);
$heure_sortie_autre  = CAppUI::conf('dPplanningOp CSejour default_hours heure_sortie_autre', $group);
$heure_entree_veille = CAppUI::conf('dPplanningOp CSejour default_hours heure_entree_veille', $group);
$heure_entree_jour   = CAppUI::conf('dPplanningOp CSejour default_hours heure_entree_jour', $group);

$config = CAppUI::conf("dPplanningOp COperation");
$hours_duree = range($config["duree_deb"], $config["duree_fin"]);
$hours_urgence = range($config["hour_urgence_deb"], $config["hour_urgence_fin"]);
$mins_duree = range(0, 59, $config["min_intervalle"]);

// Chargement des etablissements externes
$etab = new CEtabExterne();
$count_etab_externe = $etab->countList();

// Récupération des services
$service = new CService();
$where = array();
$where["group_id"]  = "= '".CGroups::loadCurrent()->_id."'";
$where["cancelled"] = "= '0'";
$order = "nom";
$listServices = $service->loadListWithPerms(PERM_READ, $where, $order);
foreach ($listServices as $_service) {
  $_service->loadRefUFSoins();
}

if (CModule::getActive("maternite")) {
  $sejour->loadRefGrossesse();
}

$list_mode_sortie = array();
if (CAppUI::conf("dPplanningOp CSejour use_custom_mode_sortie")) {
  $mode_sortie = new CModeSortieSejour();
  $where = array(
    "actif" => "= '1'",
  );
  $list_mode_sortie = $mode_sortie->loadGroupList($where);
}

if (CModule::getActive("appFineClient")) {
  CAppFineClient::loadIdex($patient, $sejour->group_id);
  $patient->loadRefStatusPatientUser();
}

$protocole = new CProtocole();
$protocole->load($protocole_id);
$protocole->loadRefChir();

$value_medecin_traitant = '';
$value_medecin_traitant_id = '';

if ($sejour->_ref_patient->patient_id) {
    $value_medecin_traitant = $sejour->medecin_traitant_id ? $sejour->_ref_medecin_traitant->_view : $patient->_ref_medecin_traitant->_view;
    $value_medecin_traitant_id = $sejour->medecin_traitant_id ? $sejour->medecin_traitant_id : $patient->_ref_medecin_traitant->_id;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejours_collision", $patient->getSejoursCollisions());

$smarty->assign("urgInstalled", CModule::getInstalled("dPurgences"));
$smarty->assign("isPrescriptionInstalled", CModule::getActive("dPprescription"));
$smarty->assign("heure_sortie_ambu",   $heure_sortie_ambu);
$smarty->assign("heure_sortie_autre",  $heure_sortie_autre);
$smarty->assign("heure_entree_veille", $heure_entree_veille);
$smarty->assign("heure_entree_jour",   $heure_entree_jour);
$smarty->assign("hours"        , $hours);
$smarty->assign("mins"         , $mins);
$smarty->assign("hours_duree"  , $hours_duree);
$smarty->assign("hours_urgence", $hours_urgence);
$smarty->assign("mins_duree"   , $mins_duree);
$smarty->assign("ufs"          , CUniteFonctionnelle::getUFs($sejour));
$smarty->assign("cpi_list"     , CChargePriceIndicator::getList());
$smarty->assign("list_mode_sortie", $list_mode_sortie);
$smarty->assign("protocole", $protocole);
$smarty->assign("contextual_call", $contextual_call);
$smarty->assign("apply_op_protocole", false);

$smarty->assign("sejour"   , $sejour);
$smarty->assign("op"       , new COperation);
$smarty->assign("praticien", $praticien);
$smarty->assign("patient"  , $patient);
$smarty->assign("sejours"  , $sejours);

$smarty->assign("correspondantsMedicaux", $correspondantsMedicaux);
$smarty->assign("count_etab_externe", $count_etab_externe);
$smarty->assign("medecin_adresse_par", $medecin_adresse_par);

$smarty->assign("listServices"  , $listServices);

$smarty->assign("mode_operation", $mode_operation);
$smarty->assign("etablissements", $etablissements);
$smarty->assign("prestations"   , $prestations);
$smarty->assign("blocages_lit"  , $blocages_lit);
$smarty->assign("current_date", CMbDT::dateTime());

$smarty->display("inc_form_sejour");
