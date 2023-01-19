<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\AppFine\Server\CEvenementMedical;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHPrimXMLAcquittementsServeurActivitePmsi
 */
class CHPrimXMLAcquittementsServeurActivitePmsi extends CHPrimXMLAcquittements {
  static $evenements = array(
    'evenementPMSI'                => "CHPrimXMLAcquittementsPmsi",
    'evenementServeurActe'         => "CHPrimXMLAcquittementsServeurActes",
    'evenementServeurEtatsPatient' => "CHPrimXMLAcquittementsServeurEtatsPatient",
    'evenementFraisDivers'         => "CHPrimXMLAcquittementsFraisDivers",
    'evenementServeurIntervention' => "CHPrimXMLAcquittementsServeurIntervention",
  );
  
  public $acquittement;
  
  public $_identifiant_acquitte;
  public $_sous_type_evt;
  public $_codes_erreurs        = array(
    "ok"  => "ok",
    "avt" => "avt",
    "err" => "err"
  );

  /**
   * Récupération du type de l'acquittement en fonction de l'évènement
   *
   * @param CHPrimXMLEvenementsServeurActivitePmsi $dom_evt Évènement du serveur d'activité PMSI
   *
   * @return CHPrimXMLAcquittementsFraisDivers|CHPrimXMLAcquittementsPmsi|CHPrimXMLAcquittementsServeurActes|CHPrimXMLAcquittementsServeurIntervention|
   */
  static function getEvtAcquittement(CHPrimXMLEvenementsServeurActivitePmsi $dom_evt) {
    if ($dom_evt instanceof CHPrimXMLEvenementsServeurActes) {
      return new CHPrimXMLAcquittementsServeurActes();
    }
    
    if ($dom_evt instanceof CHPrimXMLEvenementsPmsi) {
      return new CHPrimXMLAcquittementsPmsi();
    }
    
    if ($dom_evt instanceof CHPrimXMLEvenementsFraisDivers) {
      return new CHPrimXMLAcquittementsFraisDivers();
    }

    if ($dom_evt instanceof CHPrimXMLEvenementsServeurIntervention) {
      return new CHPrimXMLAcquittementsServeurIntervention();
    }

    return null;
  }

  /**
   * @see parent::generateEnteteMessageAcquittement()
   */
  function generateEnteteMessageAcquittement($statut, $codes = null, $commentaires = null) {
    $echg_hprim      = $this->_ref_echange_hprim;
    $identifiant     = $echg_hprim->_id ? str_pad($echg_hprim->_id, 6, '0', STR_PAD_LEFT) : "ES{$this->now}";
    
    $acquittementsServeurActivitePmsi = $this->addElement($this, $this->acquittement, null, "http://www.hprim.org/hprimXML");
    $this->addAttribute($acquittementsServeurActivitePmsi, "version", CAppUI::conf("hprimxml $this->evenement version"));
    
    $enteteMessageAcquittement = $this->addElement($acquittementsServeurActivitePmsi, "enteteMessage");
    $this->addAttribute($enteteMessageAcquittement, "statut", $statut);

    $this->addElement($enteteMessageAcquittement, "identifiantMessage", $identifiant);
    $this->addDateTimeElement($enteteMessageAcquittement, "dateHeureProduction");
    
    $emetteur = $this->addElement($enteteMessageAcquittement, "emetteur");
    $agents = $this->addElement($emetteur, "agents");
    $this->addAgent($agents, "application", "MediBoard", "Mediboard SIH");
    $group = CGroups::loadCurrent();
    $group->loadLastId400();
    $this->addAgent($agents, $this->getAttSysteme(), CAppUI::conf('mb_id'), $group->text);

    $echg_hprim->loadRefsInteropActor();
    // Pour un acquittement l'emetteur du message devient destinataire
    $destinataire = $this->addElement($enteteMessageAcquittement, "destinataire");
    $agents = $this->addElement($destinataire, "agents");
    $this->addAgent($agents, "application", $echg_hprim->_ref_sender->nom, $echg_hprim->_ref_sender->libelle);
    /* @todo Doit-on gérer le système du destinataire ? */
    //$this->addAgent($agents, "système", $group->_id, $group->text);

    $this->addElement($enteteMessageAcquittement, "identifiantMessageAcquitte", $this->_identifiant_acquitte);
  }

  /**
   * Ajout des éléments Reponses
   *
   * @param string    $statut       Statut de l'acquittement
   * @param array     $codes        Codes d'erreurs
   * @param string    $commentaires Commentaire
   * @param CMbObject $mbObject     Object
   * @param array     $data         Datas
   *
   * @return void
   */
  function addReponses($statut, $codes, $commentaires = null, $mbObject = null, $data = array()) {
    $acquittementsServeurActivitePmsi = $this->documentElement;

    $mbPatient = $mbSejour = null;
    if ($mbObject instanceof CSejour) {
      $mbPatient = $mbObject->loadRefPatient();
      $mbSejour  = $mbObject;
    }
    if ($mbObject instanceof COperation) {
      $mbPatient = $mbObject->loadRefSejour()->loadRefPatient();
      $mbSejour  = $mbObject->_ref_sejour;
    }
    if ($mbObject instanceof CEvenementPatient) {
      $mbPatient = $mbObject->loadRefPatient();
    }
    if ($mbObject instanceof CEvenementMedical) {
      $mbPatient = $mbObject->loadRefPatient();
    }

    // Ajout des réponses
    $reponses = $this->addElement($acquittementsServeurActivitePmsi, "reponses");

    // Ajout du patient et de la venue
    $patient = $this->addElement($reponses, "patient");
    if (isset($mbPatient->_id)) {
      $this->addPatient($patient, $mbPatient, false, true);
    }

    $venue = $this->addElement($reponses, "venue");
    if (isset($mbSejour->_id)) {
      $this->addVenue($venue, $mbSejour, false, true);
    }

    if (!is_array($codes)) {
      if (!isset($mbPatient->_id)) {
        $this->addPatientError($reponses, $data);
      }
      $this->addReponseGeneral($reponses, $statut, $codes, null, $mbObject, $commentaires, $data);
      return;
    }
    
    // Génération des réponses en fonction du type de l'acquittement
    switch (get_class($this)) {
      case CHPrimXMLAcquittementsServeurActes::class:
        if (!$data && !CMbArray::get($data, "CCAM") && !CMbArray::get($data, "NGAP")) {
          break;
        }
        $actesCCAM = $data["CCAM"];
        $actesNGAP = $data["NGAP"];

        foreach ($actesCCAM as $_acteCCAM) {
          $code_ccam = $codes[$_acteCCAM["idSourceActeCCAM"]];
          $this->addReponseCCAM(
            $reponses, $_acteCCAM["statut"], $code_ccam["code"], $_acteCCAM, $mbObject, $code_ccam["commentaires"]
          );
        }

        foreach ($actesNGAP as $_acteNGAP) {
          $code_ngap = $codes[$_acteNGAP["idSourceActeNGAP"]];
          $this->addReponseNGAP(
            $reponses, $_acteNGAP["statut"], $code_ngap["code"], $_acteNGAP, $mbObject, $code_ngap["commentaires"]
          );
        }
        
        break;
      case CHPrimXMLAcquittementsServeurIntervention::class:
        $this->addReponseIntervention($reponses, $statut, $codes, null, $mbObject, $commentaires);
        break;
      case CHPrimXMLAcquittementsFraisDivers::class:
        if (!$data && !CMbArray::get($data, "prestations")) {
          break;
        }

        foreach ($data["prestations"] as $_presta) {
          $this->addReponseFraisDivers($reponses, $_presta);
        }
        break;
      default:
    }
  }

  /**
   * @inheritdoc
   */
  function generateAcquittements($statut, $codes, $commentaires = null, $mbObject = null, $data = array()) {
    $this->date_production = CMbDT::dateTime();

    $this->generateEnteteMessageAcquittement($statut);
    $this->addReponses($statut, $codes, $commentaires, $mbObject, $data);
     
    // Traitement final
    $this->purgeEmptyElements();

    return utf8_encode($this->saveXML());
  }

  /**
   * @see parent::getStatutAcquittement()
   */
  function getStatutAcquittement() {
    return $this->getStatutAcquittementServeurActivitePmsi();
  }

  /**
   * Récupération du statut de l'acquittement du serveur d'activité PMSI
   *
   * @return string
   */
  function getStatutAcquittementServeurActivitePmsi() {
    $xpath = new CHPrimXPath($this);

    return $xpath->queryAttributNode("/hprim:$this->acquittement/hprim:enteteMessage", null, "statut"); 
  }

  /**
   * Récupération de l'acquittement du serveur d'activité PMSI
   *
   * @return array
   */
  function getAcquittementsServeurActivitePmsi() {
    $xpath = new CHPrimXPath($this);
    
    $xpath->queryAttributNode("/hprim:$this->acquittement/hprim:enteteMessage", null, "statut");
    
    $query = "/hprim:$this->evenement/hprim:enteteMessage";
    $enteteMessageAcquittement = $xpath->queryUniqueNode($query);  
    
    $data['identifiantMessage'] = $xpath->queryTextNode("hprim:identifiantMessage", $enteteMessageAcquittement);
    $agents = $xpath->queryUniqueNode("hprim:emetteur/hprim:agents", $enteteMessageAcquittement);
    $systeme = $xpath->queryUniqueNode("hprim:agent[@categorie='".$this->getAttSysteme()."']", $agents);
    $data['idClient'] = $xpath->queryTextNode("hprim:code", $systeme);
    $data['libelleClient'] = $xpath->queryTextNode("hprim:libelle", $systeme);
    
    $data['identifiantMessageAcquitte'] = $xpath->queryTextNode("hprim:identifiantMessageAcquitte", $enteteMessageAcquittement);
    
    return $data;
  }

  /**
   * Récupération des éléments Reponses de l'acquittement
   *
   * @return array
   */
  function getAcquittementReponsesServeurActivitePmsi() {
    $xpath = new CHPrimXPath($this);
    
    $statut = $xpath->queryAttributNode("/hprim:a$this->acquittement/hprim:enteteMessage", null, "statut"); 
    
    $query = "/hprim:$this->evenement/hprim:enteteMessageAcquittement";
    $enteteMessageAcquittement = $xpath->queryUniqueNode($query);  
    
    $observations = array();
    if ($statut == "ok") {
      $d = array();
      $observations[] = &$d;
        
      $observation = $xpath->queryUniqueNode("hprim:observation", $enteteMessageAcquittement);
      $d['code'] = chunk_split($xpath->queryTextNode("hprim:code", $observation, "", false), 4, ' ');
      $d['libelle'] = $xpath->queryTextNode("hprim:libelle", $observation, "", false);
      $d['commentaire'] = $xpath->queryTextNode("hprim:commentaire", $observation, "", false);
    }
    else {
      $query = "/hprim:$this->evenement/hprim:reponses/*";
      $reponses = $xpath->query($query);   

      foreach ($reponses as $_reponse) {
        $d = array();

        $observation = $xpath->queryUniqueNode("hprim:observations/hprim:observation", $_reponse);
        $d['code'] = chunk_split($xpath->queryTextNode("hprim:code", $observation, "", false), 4, ' ');
        $d['libelle'] = $xpath->queryTextNode("hprim:libelle", $observation, "", false);
        $d['commentaire'] = $xpath->queryTextNode("hprim:commentaire", $observation, "", false);
        $observations[] = $d;
      }
    }  
    
    return $observations;
  } 
}
