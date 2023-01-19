<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/*
 * Pour acceder à cette page ==>
 * http://localhost/mediboard/index.php?m=dPhospi&raw=get_etat_lits_txt&login=user:password;
 * renvoi :
 * NOM;Prénom;patient_id;service_id;chambre_id;lit_id;sexe(m ou f);naissance(YYYYMMJJ);
 * entree(YYYYMMDD);entree(HHMM);sortie(YYYYMMDD);sortie(HHMM);type_hospi(comp ou ambu)
*/

/*
 * Ajout du paramètre detail_lit pour avoir :
 * NOM;Prénom;patient_id;NOM_NAISSANCE,service_id;chambre_id;LIT_NOM;lit_id;sexe;naissance;entree;entree;sortie;sortie;type_hospi
 */

/*
 * Ajout du paramètre IPP pour avoir :
 * NOM;Prénom;patient_id;NOM_NAISSANCE,service_id;chambre_id;LIT_NOM;lit_id;sexe;naissance;entree;entree;sortie;sortie;type_hospi
 */

// Date actuelle
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

$date       = CValue::get("date", CMbDT::dateTime());
$detail_lit = CValue::get("detail_lit", 0);
$nom_jf     = CValue::get("nom_jf", 1);
$with_ambu  = CValue::get("with_ambu", 1);
$next_day   = CValue::get("next_day", 0);
$IPP        = CValue::get("IPP", 0);
$NDA        = CValue::get("NDA", 0);
$service    = CValue::get("service", 0);
$id_chambre = CValue::get("id_chambre", 0);
$with_confidential = CValue::get("with_confidential", 1);

// Affectation a la date $date
$affectation = new CAffectation();

$ljoinAffect           = array();
$ljoinAffect["sejour"] = "sejour.sejour_id = affectation.sejour_id";

$whereAffect = array();

$max_date = $date;
// Inclut les affectations qui chevauchent de maintenant à J+1 à 23h59
if ($next_day) {
  $max_date = CMbDT::date("+1 DAY", $date) . " 23:59:59";
}

$whereAffect["affectation.entree"]    = "<= '$max_date'";
$whereAffect["affectation.sortie"]    = ">= '$date'";
$whereAffect["affectation.sejour_id"] = "!= '0'";
$whereAffect["sejour.group_id"]       = "= '" . CGroups::loadCurrent()->_id . "'";
$whereAffect["sejour.annule"]         = "= '0'";
if (!$with_ambu) {
  $whereAffect["sejour.type"] = "!= 'ambu'";
}
if (!$with_confidential) {
  $whereAffect["sejour.presence_confidentielle"] = " = '0'";
}

$groupAffect = "sejour_id";

/** @var CAffectation[] $affectations */
$affectations = $affectation->loadList($whereAffect, null, null, $groupAffect, $ljoinAffect);

// Chargements de masse
$lits    = CStoredObject::massLoadFwdRef($affectations, "lit_id");
$sejours = CStoredObject::massLoadFwdRef($affectations, "sejour_id");

$chambres = CStoredObject::massLoadFwdRef($lits, "chambre_id");
$services = CStoredObject::massLoadFwdRef($affectations, "service_id");

$praticiens = CStoredObject::massLoadFwdRef($sejours, "praticien_id");
$patients   = CStoredObject::massLoadFwdRef($sejours, "patient_id");

CSejour::massLoadNDA($sejours);
CPatient::massLoadIPP($patients);

$list_affectations = array();

foreach ($affectations as $key => $_affectation) {
  $lit = $_affectation->loadRefLit();
  $lit->loadRefChambre();

  $sejour = $_affectation->loadRefSejour();
  $sejour->loadRefPraticien();
  $patient = $_affectation->_ref_sejour->loadRefPatient();

  if (!$with_confidential && $patient->checkAnonymous()) {
    continue;
  }
  $list_affectations[$key]["nom"]          = $patient->nom;
  $list_affectations[$key]["prenom"]       = $patient->prenom;
  $list_affectations[$key]["id"]           = $IPP ? $patient->_IPP : $patient->_id;
  $list_affectations[$key]["service"]      = $_affectation->service_id;
  $list_affectations[$key]["chambre"]      = $lit->_ref_chambre->_id;
  $list_affectations[$key]["lit"]          = $lit->_id;
  $list_affectations[$key]["sexe"]         = $patient->sexe;
  $list_affectations[$key]["naissance"]    = CMbDT::format($patient->naissance, "%Y%m%d");
  $list_affectations[$key]["date_entree"]  = CMbDT::format(CMbDT::date($sejour->entree), "%Y%m%d");
  $list_affectations[$key]["heure_entree"] = CMbDT::format(CMbDT::time($sejour->entree), "%H%M");
  $list_affectations[$key]["date_sortie"]  = CMbDT::format(CMbDT::date($sejour->sortie), "%Y%m%d");
  $list_affectations[$key]["heure_sortie"] = CMbDT::format(CMbDT::time($sejour->sortie), "%H%M");
  $list_affectations[$key]["type"]         = $sejour->type;

  if ($detail_lit) {
    $list_affectations[$key]["lit_nom"]     = $lit->nom;
    $list_affectations[$key]["chambre_nom"] = $lit->_ref_chambre->nom;
    if ($nom_jf) {
      $list_affectations[$key]["nom_naissance"] = $patient->nom_jeune_fille;
    }
  }

  if ($NDA) {
    $list_affectations[$key]["NDA"] = $sejour->_NDA;
  }

  if ($service) {
    $list_affectations[$key]["libelle_service"] = $_affectation->loadRefService()->nom;
  }
}

header("Content-Type: text/plain;");

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("list_affectations", $list_affectations);
$smarty->assign("detail_lit", $detail_lit);
$smarty->assign("nom_jf", $nom_jf);
$smarty->assign("NDA", $NDA);
$smarty->assign("service", $service);
$smarty->assign("id_chambre", $id_chambre);

$smarty->display("get_etat_lits_txt");