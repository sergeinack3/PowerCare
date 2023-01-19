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
use Ox\Core\CMbString;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Class CHPrimXMLAcquittementsPatients
 */
class CHPrimXMLAcquittementsPatients extends CHPrimXMLAcquittements {
  public $_identifiant_acquitte;
  public $_sous_type_evt;
  
  public $_codes_erreurs        = array(
    "ok"  => "OK",
    "avt" => "avertissement",
    "err" => "erreur"
  );

  /**
   * Get version acquittements patients
   *
   * @return string
   */
  static function getVersionAcquittementsPatients() {    
    return "msgAcquittementsPatients".str_replace(".", "", CAppUI::conf('hprimxml evt_patients version'));
  }

  /**
   * @see parent::__construct
   */
  function __construct() {
    $this->evenement = "evt_patients";

    $version = CAppUI::conf('hprimxml evt_patients version');

    parent::__construct(
      "patients/v".str_replace(".", "_", $version),
      self::getVersionAcquittementsPatients()
    );
  }

  /**
   * @see parent::generateEnteteMessageAcquittement
   */
  function generateEnteteMessageAcquittement($statut, $codes = null, $commentaires = null) {
    $commentaires = strip_tags($commentaires);
    $echg_hprim   = $this->_ref_echange_hprim;
    $identifiant  = isset($echg_hprim->_id) ? str_pad($echg_hprim->_id, 6, '0', STR_PAD_LEFT) : "ES{$this->now}";

    $acquittementsPatients = $this->addElement($this, "acquittementsPatients", null, "http://www.hprim.org/hprimXML");

    $enteteMessageAcquittement = $this->addElement($acquittementsPatients, "enteteMessageAcquittement");
    $this->addAttribute($enteteMessageAcquittement, "statut", $statut);

    $this->addElement($enteteMessageAcquittement, "identifiantMessage", $identifiant);
    $this->addDateTimeElement($enteteMessageAcquittement, "dateHeureProduction");

    $emetteur = $this->addElement($enteteMessageAcquittement, "emetteur");
    $agents = $this->addElement($emetteur, "agents");
    $this->addAgent($agents, "application", "Mediboard", "Mediboard SIH");
    $group = CGroups::loadCurrent();
    $group->loadLastId400();
    $this->addAgent($agents, $this->getAttSysteme(), CAppUI::conf('mb_id'), $group->text);

    if (!$echg_hprim->_ref_sender) {
      $echg_hprim->loadRefsInteropActor();
    }
    // Pour un acquittement l'emetteur du message devient destinataire
    $destinataire = $this->addElement($enteteMessageAcquittement, "destinataire");
    $agents = $this->addElement($destinataire, "agents");
    $this->addAgent($agents, "application", $echg_hprim->_ref_sender->nom, $echg_hprim->_ref_sender->libelle);
    /* @todo Doit-on gérer le système du destinataire ? */
    //$this->addAgent($agents, "système", $group->_id, $group->text);

    $this->addElement($enteteMessageAcquittement, "identifiantMessageAcquitte", $this->_identifiant_acquitte);
    
    if ($statut == "OK") {
      if (is_array($codes)) {
        $_codes = $_libelle_codes = "";
        foreach ($codes as $code) {
          $_codes .= $code;
          $_libelle_codes .= CAppUI::tr("hprimxml-error-$code");
        }
        $this->addObservation($enteteMessageAcquittement, $_codes, $_libelle_codes, $commentaires);
      }
      else {
        $this->addObservation($enteteMessageAcquittement, $codes, CAppUI::tr("hprimxml-error-$codes"), $commentaires);
      }
    }
  }

  /**
   * Ajout des erreurs et des avertissements dans l'acquittement
   *
   * @param string    $statut       Statut de l'acquittement
   * @param array     $codes        Codes d'erreurs
   * @param string    $commentaires Commentaire
   * @param CMbObject $mbObject     Object
   *
   * @return void
   */
  function addErreursAvertissements($statut, $codes, $commentaires = null, $mbObject = null) {
    $commentaires = CMbString::removeAllHTMLEntities($commentaires);
    $acquittementsPatients = $this->documentElement;
     
    $erreursAvertissements = $this->addElement($acquittementsPatients, "erreursAvertissements");
     
    if (is_array($codes)) {
      foreach ($codes as $code) {
        $translate = CAppUI::tr("hprimxml-error-$code");
        $this->addErreurAvertissement($erreursAvertissements, $statut, $code, $translate, $commentaires, $mbObject);
      }
    }
    else {
      $translate = CAppUI::tr("hprimxml-error-$codes");
      $this->addErreurAvertissement($erreursAvertissements, $statut, $codes, $translate, $commentaires, $mbObject);
    }   
  }

  /**
   * @inheritdoc
   */
  function generateAcquittements($statut, $codes, $commentaires = null, $mbObject = null, $data = array()) {
    if ($statut != "OK") {
      $this->generateEnteteMessageAcquittement($statut);
      $this->addErreursAvertissements($statut, $codes, $commentaires, $mbObject);
    }
    else {
      $this->generateEnteteMessageAcquittement($statut, $codes, $commentaires);
    }

    return utf8_encode($this->saveXML());
  }

  /**
   * @see parent::generateAcquittementsError
   */
  function generateAcquittementsError($code, $commentaire = null, CMbObject $mbObject = null) {
    $commentaire = strip_tags($commentaire);
    return $this->_ref_echange_hprim->setAckError($this, $code, $commentaire, $mbObject);
  }

  /**
   * @see parent::getStatutAcquittement
   */
  function getStatutAcquittement() {
    return $this->getStatutAcquittementPatient();
  }

  /**
   * Récupération du statut de l'acquittement patient
   *
   * @return string
   */
  function getStatutAcquittementPatient() {
    $xpath = new CHPrimXPath($this);
        
    return $xpath->queryAttributNode("/hprim:acquittementsPatients/hprim:enteteMessageAcquittement", null, "statut"); 
  }

  /**
   * Récupération de l'acquittement patient
   *
   * @return array
   */
  function getAcquittementsPatients() {
    $xpath = new CHPrimXPath($this);
    
    $xpath->queryAttributNode("/hprim:acquittementsPatients/hprim:enteteMessageAcquittement", null, "statut");
    
    $query = "/hprim:acquittementsPatients/hprim:enteteMessageAcquittement";
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
   * Récupération des observations de l'acquittement patient
   *
   * @return array
   */
  function getAcquittementObservationPatients() {
    $xpath = new CHPrimXPath($this);
    
    $statut = $xpath->queryAttributNode("/hprim:acquittementsPatients/hprim:enteteMessageAcquittement", null, "statut"); 
    
    $query = "/hprim:acquittementsPatients/hprim:enteteMessageAcquittement";
    $enteteMessageAcquittement = $xpath->queryUniqueNode($query);  
    
    $observations = array();
    if ($statut == "OK") {
      $d = array();
      $observations[] = &$d;
        
      $observation = $xpath->queryUniqueNode("hprim:observation", $enteteMessageAcquittement);
      $d['code'] = chunk_split($xpath->queryTextNode("hprim:code", $observation, "", false), 4, ' ');
      $d['libelle'] = $xpath->queryTextNode("hprim:libelle", $observation, "", false);
      $d['commentaire'] = $xpath->queryTextNode("hprim:commentaire", $observation, "", false);
    }
    else {
      $query = "/hprim:acquittementsPatients/hprim:erreursAvertissements/*";
      $erreursAvertissements = $xpath->query($query);   

      foreach ($erreursAvertissements as $erreurAvertissement) {
        $d = array();

        $observation = $xpath->queryUniqueNode("hprim:observations/hprim:observation", $erreurAvertissement);
        $d['code'] = chunk_split($xpath->queryTextNode("hprim:code", $observation, "", false), 4, ' ');
        $d['libelle'] = $xpath->queryTextNode("hprim:libelle", $observation, "", false);
        $d['commentaire'] = $xpath->queryTextNode("hprim:commentaire", $observation, "", false);
        $observations[] = $d;
      }
    }  
    
    return $observations;
  } 
}

