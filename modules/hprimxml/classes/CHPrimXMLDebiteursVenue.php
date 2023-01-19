<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\Core\CMbObject;
use Ox\Interop\Eai\CEAIPatient;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class CHPrimXMLDebiteursVenue
 */
class CHPrimXMLDebiteursVenue extends CHPrimXMLEvenementsPatients { 
  public $actions = array(
    'création' => "création",
    'remplacement' => "remplacement",
    'modification' => "modification",
  );

  /**
   * @see parent::__construct
   */
  function __construct() {    
    $this->sous_type = "debiteursVenue";
            
    parent::__construct();
  }

  /**
   * @inheritdoc
   */
  function generateFromOperation(CMbObject $mbVenue, $referent = false) {
    $evenementsPatients = $this->documentElement;
    $evenementPatient = $this->addElement($evenementsPatients, "evenementPatient");
    
    $debiteursVenue = $this->addElement($evenementPatient, "debiteursVenue");
    $actionConversion = array (
      "create" => "création",
      "store"  => "modification",
      "delete" => "suppression"
    );
    $this->addAttribute($debiteursVenue, "action", $actionConversion[$mbVenue->_ref_last_log->type]);
    
    $patient = $this->addElement($debiteursVenue, "patient");
    // Ajout du patient   
    $this->addPatient($patient, $mbVenue->_ref_patient, $referent, true);
    
    $venue = $this->addElement($debiteursVenue, "venue"); 
    // Ajout de la venue   
    $this->addVenue($venue, $mbVenue, $referent, true);

    // Ajout des débiteurs
    $debiteurs = $this->addElement($debiteursVenue, "debiteurs");
    $this->addDebiteurs($debiteurs, $mbVenue->_ref_patient);
    
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
      $debiteursVenue   = $xpath->queryUniqueNode("hprim:debiteursVenue", $evenementPatient);

      $data['action'] = $this->getActionEvenement("hprim:debiteursVenue", $evenementPatient);

      $data['patient']  = $xpath->queryUniqueNode("hprim:patient", $debiteursVenue);
    $data['venue'] = $xpath->queryUniqueNode("hprim:venue", $debiteursVenue);
    $data['employeurs'] = $xpath->queryUniqueNode("hprim:employeurs", $debiteursVenue);
    $data['debiteurs'] = $xpath->queryUniqueNode("hprim:debiteurs", $debiteursVenue);

    $data['idSourcePatient'] = $this->getIdSource($data['patient']);
    $data['idCiblePatient']  = $this->getIdCible($data['patient']);
    
    $data['idSourceVenue'] = $this->getIdSource($data['venue']);
    $data['idCibleVenue'] = $this->getIdCible($data['venue']);
    
    return $data;
  }
  
  /**
   * Gestion des débiteurs d'une venue de patient
   *
   * @param CHPrimXMLAcquittementsPatients $dom_acq    Acquittement
   * @param CPatient                       $newPatient Patient
   * @param array                          $data       Datas
   *
   * @return CHPrimXMLAcquittementsPatients $msgAcq 
   **/
  function debiteursVenue($dom_acq, $newPatient, $data) {
    $echg_hprim = $this->_ref_echange_hprim;
    $sender = $echg_hprim->_ref_sender;
    $sender->loadConfigValues();
    $this->_ref_sender = $sender;
    
    // Traitement du patient
    $domEnregistrementPatient = new CHPrimXMLEnregistrementPatient();
    $domEnregistrementPatient->_ref_echange_hprim = $echg_hprim;
    $msgAcq = $domEnregistrementPatient->enregistrementPatient($dom_acq, $newPatient, $data);
    if ($echg_hprim->statut_acquittement != "OK") {
      return $msgAcq;
    }
    
    $dom_acq = new CHPrimXMLAcquittementsPatients();
    $dom_acq->_identifiant_acquitte = $data['identifiantMessage'];
    $dom_acq->_sous_type_evt        = $this->sous_type;
    $dom_acq->_ref_echange_hprim    = $echg_hprim;

    $avertissement = $commentaire = null;

    $sender = $echg_hprim->_ref_sender;

    // Mapping des mouvements
    $newPatient = $this->mappingDebiteurs($data['debiteurs'], $newPatient);
    $newPatient->repair();

    $msgPatient  = CEAIPatient::storePatient($newPatient, $sender);
    $commentaire = CEAIPatient::getComment($newPatient);

    $codes = array ($msgPatient ? "A003" : "I002");

    if ($msgPatient) {
      $avertissement = $msgPatient." ";
    }

    return $echg_hprim->setAck($dom_acq, $codes, $avertissement, $commentaire, $newPatient);
  }
}

