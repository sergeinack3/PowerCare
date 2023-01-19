<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Interop\Eai\CEAIPatient;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CMergeLog;
use Throwable;

/**
 * Class CHPrimXMLFusionPatient
 * Mouvement patient
 */
class CHPrimXMLFusionPatient extends CHPrimXMLEvenementsPatients { 
  public $actions = array(
    'fusion' => "fusion"
  );

  /**
   * @see parent::__construct
   */
  function __construct() {        
    $this->sous_type = "fusionPatient";

    parent::__construct();
  }

  /**
   * @inheritdoc
   */
  function generateFromOperation(CMbObject $mbPatient, $referent = false) {
    $evenementsPatients = $this->documentElement;
    $evenementPatient   = $this->addElement($evenementsPatients, "evenementPatient");
    
    $fusionPatient = $this->addElement($evenementPatient, "fusionPatient");
    $this->addAttribute($fusionPatient, "action", "fusion");
      
    $patient = $this->addElement($fusionPatient, "patient");
    // Ajout du nouveau patient   
    $this->addPatient($patient, $mbPatient, $referent);
      
    $patientElimine = $this->addElement($fusionPatient, "patientElimine");
    // Ajout du patient a eliminer
    $this->addPatient($patientElimine, $mbPatient->_patient_elimine, $referent);
        
    // Traitement final
    $this->purgeEmptyElements();
  }

  /**
   * @see parent::getContentsXML
   */
  public function getContentsXML(): array
  {
      $xpath = new CHPrimXPath($this);

      $query = "/hprim:evenementsPatients/hprim:evenementPatient";

      $evenementPatient = $xpath->queryUniqueNode($query);
      $fusionPatient    = $xpath->queryUniqueNode("hprim:fusionPatient", $evenementPatient);

      $data['action'] = $this->getActionEvenement("hprim:fusionPatient", $evenementPatient);

      $data['patient']      = $xpath->queryUniqueNode("hprim:patient", $fusionPatient);
    $data['patientElimine'] = $xpath->queryUniqueNode("hprim:patientElimine", $fusionPatient);

    $data['idSourcePatient'] = $this->getIdSource($data['patient']);
    $data['idCiblePatient']  = $this->getIdCible($data['patient']);
    
    $data['idSourcePatientElimine']= $this->getIdSource($data['patientElimine']);
    $data['idCiblePatientElimine'] = $this->getIdCible($data['patientElimine']);
    
    return $data;
  }
  
  /**
   * Fusion and recording a patient with an IPP in the system
   *
   * @param CHPrimXMLAcquittementsPatients $dom_acq    Acquittement
   * @param CPatient                       $newPatient Patient
   * @param array                          $data       Datas
   *
   * @return string acquittement 
   **/
  function fusionPatient(CHPrimXMLAcquittementsPatients$dom_acq, CPatient $newPatient, $data) {
    $echg_hprim = $this->_ref_echange_hprim;
    $commentaire = $avertissement = "";
    $codes = array();

    $sender = $echg_hprim->_ref_sender;
    $sender->loadConfigValues();
    $this->_ref_sender = $sender;

    $mbPatientElimine = new CPatient();
    $mbPatient = new CPatient();

    $sender = $echg_hprim->_ref_sender;

    // Acquittement d'erreur : identifiants source et cible non fournis pour le patient / patientElimine
    if (!$data['idSourcePatient'] && !$data['idCiblePatient'] && !$data['idSourcePatientElimine'] && !$data['idCiblePatientElimine']) {
      return $echg_hprim->setAckError($dom_acq, "E005", $commentaire, $newPatient);
    }

    $idexPatient = CIdSante400::getMatch("CPatient", $sender->_tag_patient, $data['idSourcePatient']);
    if ($mbPatient->load($data['idCiblePatient'])) {
      if ($mbPatient->_id != $idexPatient->object_id) {
        $commentaire  = "L'identifiant source fait référence au patient : $idexPatient->object_id et l'identifiant cible";
        $commentaire .= "au patient : $mbPatient->_id.";
        return $echg_hprim->setAckError($dom_acq, "E004", $commentaire, $newPatient);
      }
    }
    if (!$mbPatient->_id) {
      $mbPatient->load($idexPatient->object_id);
    }

    $idexPatientElimine = CIdSante400::getMatch("CPatient", $sender->_tag_patient, $data['idSourcePatientElimine']);
    if ($mbPatientElimine->load($data['idCiblePatientElimine'])) {
      if ($mbPatientElimine->_id != $idexPatientElimine->object_id) {
        $commentaire  = "L'identifiant source fait référence au patient : $idexPatientElimine->object_id et l'identifiant cible";
        $commentaire .= "au patient : $mbPatientElimine->_id.";

        return $echg_hprim->setAckError($dom_acq, "E041", $commentaire, $newPatient);
      }
    }
    if (!$mbPatientElimine->_id) {
      $mbPatientElimine->load($idexPatientElimine->object_id);
    }

    if (!$mbPatient->_id || !$mbPatientElimine->_id) {
      $commentaire = !$mbPatient->_id ?
         "Le patient $mbPatient->_id est inconnu dans Mediboard." :
         "Le patient $mbPatientElimine->_id est inconnu dans Mediboard.";
      return $echg_hprim->setAckError($dom_acq, "E012", $commentaire, $newPatient);
    }

    // Passage en trash de l'IPP du patient a éliminer
    $idexPatientElimine->tag = CAppUI::conf('dPpatients CPatient tag_ipp_trash').$sender->_tag_patient;
    $idexPatientElimine->store();

    $avertissement = null;

    $patientsElimine_array = array($mbPatientElimine);
    $first_patient_id = $mbPatient->_id;

    try {
        $mbPatient->checkMerge($patientsElimine_array);
    } catch (Throwable $t) {
        $commentaire = "La fusion de ces deux patients n'est pas possible à cause des problèmes suivants : {$t->getMessage()}";

        return $echg_hprim->setAckError($dom_acq, "E010", $commentaire, $newPatient);
    }

    $mbPatientElimine->_id;

    /** @todo mergePlainFields resets the _id */
    $mbPatient->_id = $first_patient_id;

    // Notifier les autres destinataires
    $mbPatient->_eai_sender_guid = $sender->_guid;

    $merge_log = CMergeLog::logStart(CUser::get()->_id, $mbPatient, $patientsElimine_array, false);
    $merge_log->logCheck();

    try {
        $mbPatient->merge($patientsElimine_array, false, $merge_log);
        $merge_log->logEnd();
        $msg = null;
    } catch (Throwable $t) {
        $merge_log->logFromThrowable($t);

        $msg = $t->getMessage();
    }

    $commentaire = CEAIPatient::getComment($newPatient, $mbPatientElimine);

    $codes = array ($msg ? "A010" : "I010");

    if ($msg) {
      $avertissement = $msg." ";
    }

    return $echg_hprim->setAck($dom_acq, $codes, $avertissement, $commentaire, $newPatient);
  }
}
