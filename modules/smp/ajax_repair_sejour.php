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

$sip_config = CAppUI::conf("sip");

// Bornes
if (preg_match("/(\d{4})-(\d{2})-(\d{2})/", $sip_config["repair_date_min"])) {
  $where['entree'] = " >= '".$sip_config["repair_date_min"]."'";
}

if (preg_match("/(\d{4})-(\d{2})-(\d{2})/", $sip_config["repair_date_max"])) {
  $where['sortie'] = " <= '".$sip_config["repair_date_max"]."'";
}

$ljoin = array();
$ljoin["id_sante400"] = "sejour.sejour_id = id_sante400.object_id AND id_sante400.object_class = 'CSejour'";
$where["id_sante400.id_sante400_id"] = "IS NULL";

// Comptage
$count = $sejour->countList($where, null, $ljoin);
$max = $sip_config["repair_segment"];
$max = min($max, $count);
CAppUI::stepAjax("Export de $max sur $count objets de type 'CSejour' à partir de l'ID '$idMin'", UI_MSG_OK);

// Time limit
$seconds = max($max / 20, 120);
CAppUI::stepAjax("Limite de temps du script positionné à '$seconds' secondes", UI_MSG_OK);
CApp::setTimeLimit($seconds);

$errors = 0;
// Export réel
if (!$sip_config["verify_repair"]) {
  $sejours = $sejour->loadList($where, $sejour->_spec->key, "0, $max");

  if (!CAppUI::conf("dPplanningOp CSejour tag_dossier") || !CAppUI::conf("dPpatients CPatient tag_ipp")) {
    CAppUI::stepAjax("Aucun tag (patient/séjour) de défini pour la synchronisation.", UI_MSG_ERROR);
    return;
  }

  $echange = 0;
  foreach ($sejours as $sejour) {
    $sejour->loadRefPraticien();
    $sejour->loadRefPatient();
    $sejour->_ref_patient->loadIPP();
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
    $dest_hprim->message = "patients";
    $dest_hprim->loadMatchingObject();

    if (!$sejour->_NDA) {
      $nda = new CIdSante400();
      //Paramétrage de l'id 400
      $nda->object_class = "CSejour";
      $nda->object_id = $nda->_id;
      $nda->tag = $dest_hprim->_tag_sejour;
      $nda->loadMatchingObject();

      $sejour->_NDA = $nda->id400;
    }

    if (CAppUI::conf("sip send_sej_pa") && ($sejour->_etat != "preadmission")) {
      continue;
    }

    if (CAppUI::conf("sip sej_no_numdos") && $sejour->_NDA && ($sejour->_NDA != "-")) {
      continue;
    }

    $domEvenement = new CHPrimXMLVenuePatient();
    $domEvenement->emetteur     = CAppUI::conf('mb_id');
    $domEvenement->destinataire = $dest_hprim->nom;
    $domEvenement->group_id     = $dest_hprim->group_id;

    $messageEvtPatient = $domEvenement->generateTypeEvenement($sejour);
    $doc_valid = $domEvenement->schemaValidate();

    if (!$doc_valid) {
      $errors++;
      trigger_error("Création de l'événement séjour impossible.", E_USER_WARNING);
      CAppUI::stepAjax("Import de '$sejour->_view' échoué", UI_MSG_WARNING);
    }

    if ($sejour->_ref_patient->code_regime) {
      $domEvenement = new CHPrimXMLDebiteursVenue();
      $domEvenement->emetteur     = CAppUI::conf('mb_id');
      $domEvenement->destinataire = $dest_hprim->nom;
      $domEvenement->group_id     = $dest_hprim->group_id;

      $messageEvtPatient = $domEvenement->generateTypeEvenement($sejour);
      $doc_valid = $domEvenement->schemaValidate();

      if (!$doc_valid) {
        $errors++;
        trigger_error("Création de l'événement debiteurs impossible.", E_USER_WARNING);
        CAppUI::stepAjax("Import de '$sejour->_view' échoué", UI_MSG_WARNING);
      }
    }
    $echange++;
  }
  // Enregistrement du dernier identifiant dans la session
  if (@$sejour->_id) {
    CValue::setSession("idContinue", $sejour->_id);
    CAppUI::stepAjax("Dernier ID traité : '$sejour->_id'", UI_MSG_OK);
    CAppUI::stepAjax("$echange de créés", UI_MSG_OK);
  }

  CAppUI::stepAjax("Réparation terminé avec  '$errors' erreurs", $errors ? UI_MSG_WARNING : UI_MSG_OK);
} else {

}


