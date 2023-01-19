<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\CompteRendu\CAideSaisie;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkEdit();

$user = CMediusers::get();

$date_min = CMbDT::date();
$date_max = CMbDT::date("+6 weeks");

$criteres = array();
$details = array();
$praticiens = $user->isPraticien() ?
  array($user) :
  $user->loadPraticiens(PERM_EDIT, $user->function_id);

$group = CGroups::loadCurrent();
$cidc = $group->loadLastId400("eCap")->id400;

foreach ($praticiens as $_praticien) {
  $_praticien->loadRefFunction()->loadRefGroup();
  
  //----- Level 1
  // Plages de consultations
  $where = array();
  $where["date"] = "BETWEEN '$date_min' AND '$date_max'";
  $where["chir_id"] = "= '$_praticien->_id'";
  $plage = new CPlageconsult();
  $criteres["level1"]["plages"][$_praticien->_id] = $plage->countList($where);
  
  // Connexion LDAP
  $idex = CIdSante400::getMatchFor($_praticien->_ref_user, "ldap");
  $criteres["level1"]["ldap"][$_praticien->_id] = $idex->_id ? true : false;

  // Critères eCap
  if (CModule::getActive("ecap")) {
    // Identifiants CPRT
    $idex = CIdSante400::getMatchFor($_praticien, "eCap CIDC:$cidc");
    $criteres["level1"]["cprt"][$_praticien->_id] = $idex->_id ? true : false;
  
    // Paramétrage eCap (appel d'un web service)
    $criteres["level1"]["ecap"][$_praticien->_id] = null;
  }
  
  // Vue offline paramétrée et accessible
  $criteres["level1"]["offline"][$_praticien->_id] = false;

  //----- Level 2
  // Aides à la saisie
  $aides = array();
  $aide = new CAideSaisie();
  $aide->user_id = $_praticien->_id;
  $aides["user"] = $aide->countMatchingList();
  $aide = new CAideSaisie();
  $aide->function_id = $_praticien->_ref_function->_id;
  $aides["func"] = $aide->countMatchingList();
  $aide = new CAideSaisie();
  $aide->group_id = $_praticien->_ref_function->_ref_group->_id;
  $aides["group"] = $aide->countMatchingList();
  
  $criteres["level2"]["aides"][$_praticien->_id] = array_sum($aides) ? true : false;
  $details["level2"]["aides"][$_praticien->_id] = $aides;
  
  // Tarifs
  $tarifs = array();
  $tarif = new CTarif();
  $tarif->chir_id = $_praticien->_id;
  $tarifs["user"] = $tarif->countMatchingList();
  $tarif = new CTarif();
  $tarif->function_id = $_praticien->_ref_function->_id;
  $tarifs["func"] = $tarif->countMatchingList();
  
  $criteres["level2"]["tarifs"][$_praticien->_id] = array_sum($tarifs) ? true : false;
  $details["level2"]["tarifs"][$_praticien->_id] = $tarifs;
    
  // Modèles bodies
  $modeles = array();
  $modele = new CCompteRendu();
  $modele->object_class = "CConsultation";
  $modele->type = "body";
  $modele->user_id = $_praticien->_id;
  $modeles["user"] = $modele->countMatchingList();
  $modele = new CCompteRendu();
  $modele->object_class = "CConsultation";
  $modele->type = "body";
  $modele->function_id = $_praticien->_ref_function->_id;
  $modeles["func"] = $modele->countMatchingList();
  $modele = new CCompteRendu();
  $modele->object_class = "CConsultation";
  $modele->type = "body";
  $modele->group_id = $_praticien->_ref_function->_ref_group->_id;
  $modeles["group"] = $modele->countMatchingList();
  
  $criteres["level2"]["modele_bodies"][$_praticien->_id] = array_sum($modeles) ? true : false;
  $details["level2"]["modele_bodies"][$_praticien->_id] = $modeles;
  
  // Modèles components
  $modeles = array();
  $modele = new CCompteRendu();
  $modele->object_class = "CConsultation";
  $modele->type = "header";
  $modele->user_id = $_praticien->_id;
  $modeles["user"] = $modele->countMatchingList();
  $modele = new CCompteRendu();
  $modele->object_class = "CConsultation";
  $modele->type = "header";
  $modele->function_id = $_praticien->_ref_function->_id;
  $modeles["func"] = $modele->countMatchingList();
  $modele = new CCompteRendu();
  $modele->object_class = "CConsultation";
  $modele->type = "header";
  $modele->group_id = $_praticien->_ref_function->_ref_group->_id;
  $modeles["group"] = $modele->countMatchingList();
  
  $criteres["level2"]["modele_comps"][$_praticien->_id] = array_sum($modeles) ? true : false;
  $details["level2"]["modele_comps"][$_praticien->_id] = $modeles;
  
  // Days Patients
  $patient_count = 0;
  $plage = new CPlageconsult();
  $plage->date = CMbDT::date();
  $plage->chir_id = $_praticien->_id;
  /** @var CPlageconsult $_plage */
  foreach ($plage->loadMatchingList() as $_plage) {
    $patient_count += $_plage->countPatients();
  }
  
  $criteres["level2"]["patient_count"][$_praticien->_id] = $patient_count;
}
 
// Création du template
$smarty = new CSmartyDP();

$smarty->assign("utypes"    , CUser::$types);
$smarty->assign("user"      , $user        );
$smarty->assign("praticiens", $praticiens  );
$smarty->assign("criteres"  , $criteres    );
$smarty->assign("details"   , $details     );

$smarty->display("check_params.tpl");
