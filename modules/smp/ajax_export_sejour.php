<?php
/**
 * @package Mediboard\Smp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Interop\Eai\CInteropActorFactory;
use Ox\Interop\Hprimxml\CHPrimXMLDebiteursVenue;
use Ox\Interop\Hprimxml\CHPrimXMLVenuePatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkAdmin();

// Si pas de tag patient et séjour
if (!CAppUI::conf("dPplanningOp CSejour tag_dossier") || !CAppUI::conf("dPpatients CPatient tag_ipp")) {
  CAppUI::stepAjax("Aucun tag (patient/séjour) de défini.", UI_MSG_ERROR);
}

if (!CAppUI::conf("smp export_dest")) {
  CAppUI::stepAjax("Aucun destinataire de défini pour l'export.", UI_MSG_ERROR);
}

// Filtre sur les enregistrements
$sejour = new CSejour();
$action = CValue::get("action", "start");

// Tous les départs possibles
$idMins = array(
  "start"    => "000000",
  "continue" => CValue::getOrSession("idContinue"),
  "retry"    => CValue::getOrSession("idRetry"),
);

$idMin = CValue::first(@$idMins[$action], "000000");
CValue::setSession("idRetry", $idMin);

// Requêtes
$where = array();
$where[$sejour->_spec->key] = "> '$idMin'";
$where['annule'] = " = '0'";

$smp_config = CAppUI::conf("smp");

// Bornes
if ($export_id_min = $smp_config["export_id_min"]) {
  $where[] = $sejour->_spec->key." >= '$export_id_min'";
}
if ($export_id_max = $smp_config["export_id_max"]) {
  $where[] = $sejour->_spec->key." <= '$export_id_max'";
}

if (preg_match("/(\d{4})-(\d{2})-(\d{2})/", $smp_config["export_date_min"]) && 
    preg_match("/(\d{4})-(\d{2})-(\d{2})/", $smp_config["export_date_max"])) {
  $where['entree'] = " BETWEEN '".$smp_config["export_date_min"]."' AND '".$smp_config["export_date_max"]."'";
}

// Comptage
$count = $sejour->countList($where);

$max = $smp_config["export_segment"];
$max = min($max, $count);
CAppUI::stepAjax("Export de $max sur $count objets de type 'CSejour' à partir de l'ID '$idMin'", UI_MSG_OK);

// Time limit
$seconds = max($max / 20, 120);
CAppUI::stepAjax("Limite de temps du script positionné à '$seconds' secondes", UI_MSG_OK);
CApp::setTimeLimit($seconds);

// Export réel
$errors = 0;
$sejours = $sejour->loadList($where, $sejour->_spec->key, "0, $max");

$echange = 0;
foreach ($sejours as $sejour) {
  $sejour->loadRefPraticien();
  $sejour->loadRefPatient();
  if ($sejour->_ref_prescripteurs) {
    $sejour->loadRefsPrescripteurs();
  }
  $sejour->loadRefAdresseParPraticien();
  $sejour->_ref_patient->loadRefsFwd();
  $sejour->loadRefsActes();
  foreach ($sejour->_ref_actes_ccam as $actes_ccam) {
    $actes_ccam->loadRefPraticien();
  }
  $sejour->loadRefsAffectations();
  $sejour->loadNDA();
  $sejour->loadLogs();
  $sejour->loadRefsConsultations();
  $sejour->loadRefsConsultAnesth();
      
  $sejour->_ref_last_log->type = "create";
  
  $dest_hprim =  (new CInteropActorFactory())->receiver()->makeHprimXML();
  $dest_hprim->load(CAppUI::conf("smp export_dest"));
  $dest_hprim->loadConfigValues();
  
  if (!$sejour->_NDA) {
    $nda = new CIdSante400();
    //Paramétrage de l'id 400
    $nda->object_class = "CSejour";
    $nda->object_id = $sejour->_id;
    $nda->tag = $dest_hprim->_tag_sejour;
    $nda->loadMatchingObject();

    $sejour->_NDA = $nda->id400;
  }
  
  if (!$sejour->_ref_patient->_IPP) {
    $IPP               = new CIdSante400();
    $IPP->object_class = "CPatient";
    $IPP->object_id    = $sejour->_ref_patient->_id;
    $IPP->tag          = $dest_hprim->_tag_patient;
    $IPP->loadMatchingObject();

    $sejour->_ref_patient->_IPP = $IPP->id400;
  }

  if (CAppUI::conf("smp send_sej_pa") && ($sejour->_etat != "preadmission")) {
    continue;
  }

  if (!CAppUI::conf("smp sej_no_numdos") && (!$sejour->_NDA || ($sejour->_NDA == "-"))) {
    continue;
  }

  if (!CAppUI::conf("sip pat_no_ipp") && (!$sejour->_ref_patient->_IPP || ($sejour->_ref_patient->_IPP == "-"))) {
    continue;
  }
  
  $domEvenementVenuePatient = new CHPrimXMLVenuePatient();
  $domEvenementVenuePatient->_ref_receiver = $dest_hprim;
  
  $dest_hprim->sendEvenementPatient($domEvenementVenuePatient, $sejour);
  
  if (!$domEvenementVenuePatient->_ref_echange_hprim->message_valide) {
    $errors++;
    trigger_error("Création de l'événement venue impossible.", E_USER_WARNING);
    CAppUI::stepAjax("Import de '$sejour->_view' échoué", UI_MSG_WARNING);
    continue;
  }
  
  if ($sejour->_ref_patient->code_regime) {
    $domEvenementDebiteursVenue = new CHPrimXMLDebiteursVenue();
    $domEvenementDebiteursVenue->_ref_receiver = $dest_hprim;
    
    $dest_hprim->sendEvenementPatient($domEvenementDebiteursVenue, $sejour);
    
    if (!$domEvenementDebiteursVenue->_ref_echange_hprim->message_valide) {
      $errors++;
      trigger_error("Création de l'événement debiteurs impossible.", E_USER_WARNING);
      CAppUI::stepAjax("Import de '$sejour->_view' échoué", UI_MSG_WARNING);
    }
  }
  
  /*if (CAppUI::conf("sip send_mvt")) {
    foreach ($sejour->_ref_affectations as $_affectation) {
      $_affectation->loadRefLit();
      $_affectation->_ref_lit->loadRefChambre();
      $_affectation->_ref_lit->_ref_chambre->loadRefService();
      $_affectation->loadLastLog();
      $_affectation->loadRefSejour();
      $_affectation->_ref_sejour->loadNDA();
      $_affectation->_ref_sejour->loadRefPatient();
      $_affectation->_ref_sejour->loadRefPraticien();
      
      $domEvenemenMouvementPatient = new CHPrimXMLMouvementPatient();
      $domEvenemenMouvementPatient->_ref_receiver = $dest_hprim;
      
      $dest_hprim->sendEvenementPatient($domEvenemenMouvementPatient, $_affectation);
      
      if (!$domEvenemenMouvementPatient->_ref_echange_hprim->message_valide) {
        $errors++;
        trigger_error("Création de l'événement mouvement impossible.", E_USER_WARNING);
        CAppUI::stepAjax("Import de '$sejour->_view' échoué", UI_MSG_WARNING);
      }
    }
  }*/
  
  if (!$errors) {
    $echange++;
  }
}

// Enregistrement du dernier identifiant dans la session
if (@$sejour->_id) {
  CValue::setSession("idContinue", $sejour->_id);
  CAppUI::stepAjax("Dernier ID traité : '$sejour->_id'", UI_MSG_OK);
  if (!$errors) {
    CAppUI::stepAjax("$echange de créés", UI_MSG_OK);
  }
}

CAppUI::stepAjax("Import terminé avec  '$errors' erreurs", $errors ? UI_MSG_WARNING : UI_MSG_OK);

