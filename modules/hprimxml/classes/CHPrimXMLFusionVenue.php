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
use Ox\Interop\Eai\CInteropActorFactory;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CHPrimXMLFusionVenue
 * Mouvement patient
 */
class CHPrimXMLFusionVenue extends CHPrimXMLEvenementsPatients { 
  public $actions = array(
    'fusion' => "fusion"
  );

  /**
   * @see parent::__construct
   */
  function __construct() {    
    $this->sous_type = "fusionVenue";
            
    parent::__construct();
  }

  /**
   * @inheritdoc
   */
  function generateFromOperation(CMbObject $mbVenue, $referent = false) {
    $evenementsPatients = $this->documentElement;
    $evenementPatient = $this->addElement($evenementsPatients, "evenementPatient");
    
    $fusionVenue = $this->addElement($evenementPatient, "fusionVenue");
    $this->addAttribute($fusionVenue, "action", "fusion");
          
    // Ajout du patient
    $patient = $this->addElement($fusionVenue, "patient");
    $this->addPatient($patient, $mbVenue->_ref_patient, $referent);
    
    // Ajout de la venue   
    $venue = $this->addElement($fusionVenue, "venue");
    $this->addVenue($venue, $mbVenue, $referent);

    $venueEliminee = $this->addElement($fusionVenue, "venueEliminee");
    // Ajout de la venue a eliminer
    $this->addVenue($venueEliminee, $mbVenue->_sejour_elimine, $referent);
        
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
      $fusionVenue      = $xpath->queryUniqueNode("hprim:fusionVenue", $evenementPatient);

      $data['action'] = $this->getActionEvenement("hprim:fusionVenue", $evenementPatient);

      $data['patient']       = $xpath->queryUniqueNode("hprim:patient", $fusionVenue);
    $data['idSourcePatient'] = $this->getIdSource($data['patient']);
    $data['idCiblePatient']  = $this->getIdCible($data['patient']);
    
    $data['venue']         = $xpath->queryUniqueNode("hprim:venue", $fusionVenue);
    $data['idSourceVenue'] = $this->getIdSource($data['venue']);
    $data['idCibleVenue']  = $this->getIdCible($data['venue']);
    
    $data['venueEliminee']         = $xpath->queryUniqueNode("hprim:venueEliminee", $fusionVenue);
    $data['idSourceVenueEliminee'] = $this->getIdSource($data['venueEliminee']);
    $data['idCibleVenueEliminee']  = $this->getIdCible($data['venueEliminee']);
        
    return $data;
  }
  
  /**
   * Fusion and recording a stay with an num_dos in the system
   * 
   * @param CHPrimXMLAcquittementsPatients $dom_acq    Acquittement
   * @param CPatient                       $newPatient Patient
   * @param array                          $data       Datas
   *
   * @return string acquittement 
   **/
  function fusionVenue(CHPrimXMLAcquittementsPatients $dom_acq, CPatient $newPatient, $data) {
    $echg_hprim = $this->_ref_echange_hprim;
    $sender     = $echg_hprim->_ref_sender;
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

    $commentaire = $avertissement = "";

    $mbVenue         = new CSejour();
    $mbVenueEliminee = new CSejour();
    $newVenue        = new CSejour();

    // Acquittement d'erreur : identifiants source et cible non fournis pour le venue / venueEliminee
    if (!$data['idSourceVenue'] && !$data['idCibleVenue'] && !$data['idSourceVenueEliminee'] && !$data['idCibleVenueEliminee']) {
      return $dom_acq->generateAcquittementsError("E100", $commentaire, $newVenue);
    }

    $etatVenue         = CHPrimXMLEvenementsPatients::getEtatVenue($data['venue']);
    $etatVenueEliminee = CHPrimXMLEvenementsPatients::getEtatVenue($data['venueEliminee']);

    $tag = ($etatVenue == "préadmission") ?
      CAppUI::conf('dPplanningOp CSejour tag_dossier_pa').$sender->_tag_sejour : $sender->_tag_sejour;
    $idexVenue = CIdSante400::getMatch("CSejour", $tag, $data['idSourceVenue']);
    if ($mbVenue->load($data['idCibleVenue'])) {
      // Pas de test dans le cas ou la fusion correspond à un changement de numéro de dossier
      if (($etatVenue == "préadmission") || ($etatVenueEliminee != "préadmission")) {
        if ($idexVenue->object_id && ($mbVenue->_id != $idexVenue->object_id)) {
          $commentaire  = "L'identifiant source fait référence au séjour : $idexVenue->object_id ";
          $commentaire .= "et l'identifiant cible au séjour : $mbVenue->_id.";
          return $dom_acq->generateAcquittementsError("E104", $commentaire, $newVenue);
        }
      }
    }
    if (!$mbVenue->_id) {
      $mbVenue->_id = $idexVenue->object_id;
    }

    $tag = ($etatVenue == "préadmission") ?
      CAppUI::conf('dPplanningOp CSejour tag_dossier_pa').$sender->_tag_sejour : $sender->_tag_sejour;
    $idexVenueEliminee = CIdSante400::getMatch("CSejour", $tag, $data['idSourceVenueEliminee']);
    if ($mbVenueEliminee->load($data['idCibleVenueEliminee'])) {
      if ($idexVenueEliminee->object_id && ($mbVenueEliminee->_id != $idexVenueEliminee->object_id)) {
        $commentaire  = "L'identifiant source fait référence au séjour : $idexVenueEliminee->object_id ";
        $commentaire .= "et l'identifiant cible au séjour : $mbVenueEliminee->_id.";
        return $dom_acq->generateAcquittementsError("E141", $commentaire, $mbVenueEliminee);
      }
    }
    if (!$mbVenueEliminee->_id) {
      $mbVenueEliminee->_id = $idexVenueEliminee->object_id;
    }

    $messages = array();
    $avertissement = null;

    // Cas 0 : Aucun séjour
    if (!$mbVenue->_id && !$mbVenueEliminee->_id) {
      $newVenue->patient_id = $newPatient->_id;
      $newVenue->group_id   = CGroups::loadCurrent()->_id;
      $messages = $this->mapAndStoreVenue($newVenue, $data, $etatVenueEliminee, $idexVenue, $idexVenueEliminee);
    }
    // Cas 1 : 1 séjour
    else if ($mbVenue->_id || $mbVenueEliminee->_id) {
      // Suppression de l'identifiant du séjour trouvé
      if ($mbVenue->_id) {
        $newVenue->load($mbVenue->_id);
        $messages['msgNumDosVenue'] = $idexVenue->delete();
      }
      else if ($mbVenueEliminee->_id) {
        $newVenue->load($mbVenueEliminee->_id);
        $messages['msgNumDosVenueEliminee'] = $idexVenueEliminee->delete();
      }
      // Cas 0
      $messages = $this->mapAndStoreVenue($newVenue, $data, $etatVenueEliminee, $idexVenue, $idexVenueEliminee);

      $commentaire = "Séjour modifié : $newVenue->_id.";
    }
    // Cas 2 : 2 Séjour
    else if ($mbVenue->_id && $mbVenueEliminee->_id) {
      // Suppression des identifiants des séjours trouvés
      $messages['msgNumDosVenue'] = $idexVenue->delete();
      $messages['msgNumDosVenueEliminee'] = $idexVenueEliminee->delete();

      // Transfert des backsref
      $mbVenueEliminee->transferBackRefsFrom($mbVenue);

      // Suppression de la venue a éliminer
      $mbVenueEliminee->delete();

      // Cas 0
      $newVenue->load($mbVenue->_id);
      $messages = $this->mapAndStoreVenue($newVenue, $data, $etatVenueEliminee, $idexVenue, $idexVenueEliminee);
    }

    $codes = array ($messages['msgVenue'] ? (($messages['_code_Venue'] == "store") ? "A103" : "A102") :
                                            (($messages['_code_Venue'] == "store") ? "I102" : "I101"),
                    $messages['msgNumDosVenue'] ? "A105" : $messages['_code_NumDos']);

    if ($messages['msgVenue']) {
      $avertissement = $messages['msgVenue'];
    }

    $commentaire = CEAISejour::getComment($newVenue, $mbVenueEliminee);

    return $echg_hprim->setAck($dom_acq, $codes, $avertissement, $commentaire, $newVenue);
  }

  /**
   * Mapping et enregistrement de la venue
   *
   * @param CSejour     &$newVenue          Séjour
   * @param array       $data               Datas
   * @param string      $etatVenueEliminee  État de la venue à éliminer
   * @param CIdSante400 &$idexVenue         Idex de la venue
   * @param CIdSante400 &$idexVenueEliminee Idex de la venue à éliminer
   *
   * @return array
   */
  private function mapAndStoreVenue(&$newVenue, $data, $etatVenueEliminee, CIdSante400 &$idexVenue, CIdSante400 &$idexVenueEliminee) {
    $sender =  (new CInteropActorFactory())->receiver()->makeHprimXML();
    $sender->nom = $data['idClient'];
    $sender->loadMatchingObject();
    
    $messages = array();
    // Mapping de la venue a éliminer
    $newVenue = $this->mappingVenue($data['venueEliminee'], $newVenue);
    // Mapping de la venue a garder
    $newVenue = $this->mappingVenue($data['venue'], $newVenue);

    // Notifier les autres destinataires
    $newVenue->_eai_sender_guid = $sender->_guid;

    // Séjour retrouvé
    if ($newVenue->loadMatchingSejour() || $newVenue->_id) {
      $messages['_code_NumDos'] = "A121";
      $messages['_code_Venue'] = "store";
    }
    else {
      $messages['_code_NumDos'] = "I122";
      $messages['_code_Venue']  = "create";
    }

    $messages['msgVenue']    = $newVenue->store();
    $messages['commentaire'] = CEAISejour::getComment($newVenue);

    $idexVenue->object_id = $newVenue->_id;
    $messages['msgNumDosVenue'] = $idexVenue->store();
    
    $idexVenueEliminee->tag = ($etatVenueEliminee != "préadmission") ? 
      CAppUI::conf('dPplanningOp CSejour tag_dossier_cancel').$sender->_tag_sejour :
      CAppUI::conf('dPplanningOp CSejour tag_dossier_pa').$sender->_tag_sejour;
        
    $idexVenueEliminee->object_id = $newVenue->_id;
    $messages['msgNumDosVenueEliminee'] = $idexVenueEliminee->store();
    
    return $messages;
  }
}
