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
use Ox\Interop\Eai\CEAISejour;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHPrimXMLMouvementPatient
 * Mouvement patient
 */

class CHPrimXMLMouvementPatient extends CHPrimXMLEvenementsPatients { 
  public $actions = array(
    'création'     => "création",
    'remplacement' => "remplacement",
    'modification' => "modification",
  );

  /**
   * Construct
   *
   * @return CHPrimXMLMouvementPatient
   */
  function __construct() {    
    $this->sous_type = "mouvementPatient";
            
    parent::__construct();
  }

  /**
   * @inheritdoc
   */
  function generateFromOperation(CMbObject $affectation, $referent = false) {
    $evenementsPatients = $this->documentElement;
    $evenementPatient   = $this->addElement($evenementsPatients, "evenementPatient");
    
    $mouvementPatient = $this->addElement($evenementPatient, "mouvementPatient");
    $actionConversion = array (
      "create" => "création",
      "store"  => "modification",
      "delete" => "suppression"
    );
    $affectation->loadLastLog();
    $action = $affectation->_ref_last_log->type ? $affectation->_ref_last_log->type : "create";
    $this->addAttribute($mouvementPatient, "action", $actionConversion[$action]);

    $affectation->loadRefSejour();
    $affectation->_ref_sejour->loadNDA();
    $affectation->_ref_sejour->loadRefPatient();
    $affectation->_ref_sejour->loadRefPraticien();

    $patient = $this->addElement($mouvementPatient, "patient");
    // Ajout du patient   
    $this->addPatient($patient, $affectation->_ref_sejour->_ref_patient, $referent);
    
    $venue = $this->addElement($mouvementPatient, "venue"); 
    // Ajout de la venue   
    $this->addVenue($venue, $affectation->_ref_sejour, $referent);
    
    // Ajout du mouvement (1 seul dans notre cas pas l'historique)
    $mouvements = $this->addElement($mouvementPatient, "mouvements"); 
    $this->addMouvement($mouvements, $affectation);

    // Traitement final
    $this->purgeEmptyElements();
  }

  /**
   * Get content XML
   *
   * @return array
   */
  public function getContentsXML(): array
  {
      $xpath = new CHPrimXPath($this);

      $query = "/hprim:evenementsPatients/hprim:evenementPatient";

      $evenementPatient = $xpath->queryUniqueNode($query);
      $mouvementPatient = $xpath->queryUniqueNode("hprim:mouvementPatient", $evenementPatient);

      $data['action'] = $this->getActionEvenement("hprim:mouvementPatient", $evenementPatient);

      $data['patient']       = $xpath->queryUniqueNode("hprim:patient", $mouvementPatient);
    $data['idSourcePatient'] = $this->getIdSource($data['patient']);
    $data['idCiblePatient']  = $this->getIdCible($data['patient']);
    
    $data['venue']         = $xpath->queryUniqueNode("hprim:venue", $mouvementPatient);
    $data['idSourceVenue'] = $this->getIdSource($data['venue']);
    $data['idCibleVenue']  = $this->getIdCible($data['venue']);
    
    $data['priseEnCharge'] = $xpath->queryUniqueNode("hprim:priseEnCharge", $mouvementPatient);
    $data['mouvements']    = $xpath->queryUniqueNode("hprim:mouvements"   , $mouvementPatient);
    $data['voletMedical']  = $xpath->queryUniqueNode("hprim:voletMedical" , $mouvementPatient);
    $data['dossierResume'] = $xpath->queryUniqueNode("hprim:dossierResume", $mouvementPatient);
        
    return $data;
  }
  
  /**
   * Fusion and recording a stay with an num_dos in the system
   *
   * @param CHPrimXMLAcquittementsPatients $dom_acq    Acquittement
   * @param CPatient                       $newPatient Patient
   * @param array                          $data       Data
   *
   * @return string acquittement 
   **/
  function mouvementPatient(CHPrimXMLAcquittementsPatients $dom_acq, CPatient $newPatient, $data) {
    $echg_hprim = $this->_ref_echange_hprim;
    $sender = $echg_hprim->_ref_sender;
    $sender->loadConfigValues();

    $this->_ref_sender = $sender;
    
    $newVenue = new CSejour();
    // Recherche si le séjour existe
    if (!$this->admitFound($newVenue, $data)) {
      return $echg_hprim->setAckError($dom_acq, "E014", null, $newVenue);
    }

    $codes = array();
    $avertissement = $comment = null;

    if (!CAppUI::conf("hprimxml mvtComplet")) {
      return $echg_hprim->setAck($dom_acq, $codes, $avertissement, $comment, $newVenue);
    }

    // Mapping des mouvements
    $msgMovement = $this->mappingMouvements($data['mouvements'], $newVenue);

    // Notifier les autres destinataires
    $newVenue->_eai_sender_guid = $sender->_guid;
    $newVenue->store();

    $codes = array ($msgMovement ? "A301" : "I301");

    if ($msgMovement) {
      $avertissement = $msgMovement." ";
    }

    $comment = CEAISejour::getComment($newVenue);

    return $echg_hprim->setAck($dom_acq, $codes, $avertissement, $comment, $newVenue);
  } 
}
