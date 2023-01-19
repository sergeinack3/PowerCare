<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Permet d'editer des relances pour les factures impayees
 */
class CRelance extends CMbObject {
  // DB Table key
  public $relance_id;
  
  // DB Fields
  public $object_id;
  public $object_class;
  public $date;
  public $etat;
  public $du_patient;
  public $du_tiers;
  public $numero;
  public $statut;
  public $poursuite;
  public $statut_envoi;
  public $request_date;

  public $_montant;
  public $_duplication;
  // Object References
  /** @var  CFactureCabinet|CFactureEtablissement $_ref_object*/
  public $_ref_object;

  static public $PREVIOUS_STATE_EN_ATTENTE = 1;
  static public $PREVIOUS_STATE_REGLEE = 2;
  static public $PREVIOUS_STATE_INACTIVE = 3;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'facture_relance';
    $spec->key   = 'relance_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["object_id"]    = "ref notNull class|CStoredObject meta|object_class back|relance_fact";
    $props["object_class"]  = "enum notNull list|CFactureCabinet|CFactureEtablissement default|CFactureCabinet";
    $props["date"]          = "date";
    $props["etat"]          = "enum notNull list|emise|regle|renouvelle default|emise";
    $props["numero"]        = "num notNull min|1 max|10 default|1";
    $props["du_patient"]    = "currency decimals|2";
    $props["du_tiers"]      = "currency decimals|2";
    $props["statut"]        = "enum list|inactive|first|second|third|contentieux|poursuite";
    $props["poursuite"]     = "enum list|defaut|continuation|etranger|faillite|hors_pays|deces|inactive|saisie|introuvable";
    $props["statut_envoi"]  = "enum notNull list|echec|non_envoye|envoye default|non_envoye";
    $props["request_date"]  = "dateTime";

    $props["_montant"]      = "currency decimals|2";
    return $props;
  }
  
  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = "Relance du ".$this->date;
    $this->_montant = $this->du_patient + $this->du_tiers;
  }
  
  /**
   * Chargement de l'objet facturable
   * 
   * @return CFacture
  **/
  function loadRefFacture() {
    return $this->_ref_object = $this->object_id ? $this->loadTargetObject() : new CFactureCabinet();
  }

  /**
   * Redefinition du store
   * 
   * @return void|string
  **/
  function store() {
    if (!$this->_id && $this->object_class && $this->object_id && !$this->_duplication) {
      $this->_ref_object = new $this->object_class;
      $this->_ref_object->load($this->object_id);
      $this->_ref_object->loadRefPatient();
      $this->_ref_object->loadRefPraticien();
      $this->_ref_object->loadRefsObjects();
      $this->_ref_object->loadRefsReglements();
      $this->_ref_object->loadRefsRelances();
  
      $this->date       = CMbDT::date();
      $this->du_patient = $this->_ref_object->_du_restant_patient + $this->_ref_object->_reglements_total_patient;
      $this->du_tiers   = $this->_ref_object->_du_restant_tiers + $this->_ref_object->_reglements_total_tiers;
      $previous_relance_state = $this->previousRelanceState();
      if ($previous_relance_state >= self::$PREVIOUS_STATE_REGLEE) {
        return CAppUI::tr("CRelance-previous-state-" . $previous_relance_state);
      }
      elseif ($previous_relance_state === self::$PREVIOUS_STATE_EN_ATTENTE) {
        $previous_relance = $this->_ref_object->_ref_last_relance;
        $this->numero = $previous_relance->numero + 1;
        $previous_relance->etat = "renouvelle";
        $previous_relance->store();
      }

      $this->setStatutByNumber($this->numero);
    }
    
    // Standard store
    if ($msg = parent::store()) {
      return $msg;
    }
  }

  /**
   * Récupération de l'état de la précédente relance (inactive, reglee ou renouvelee)
   *
   * @return int|null
   */
  function previousRelanceState(){
    $previous_relance = $this->_ref_object->_ref_last_relance;
    if (!$previous_relance || !$previous_relance->_id) {
      return null;
    }
    if ($previous_relance->statut == "inactive") {
      return self::$PREVIOUS_STATE_INACTIVE;
    }
    elseif ($previous_relance->etat != "regle") {
      return self::$PREVIOUS_STATE_EN_ATTENTE;
    }
    else {
      return self::$PREVIOUS_STATE_REGLEE;
    }
  }

  /**
   * Fixe le statut de la relance et applique le surplus en configuration
   */
  function setStatutByNumber($numero = "1") {
    if (!$numero) {
      $numero = "1";
    }
    switch ($numero) {
      case "1":
        $this->du_patient += CAppUI::gconf("dPfacturation CRelance add_first_relance");
        $this->statut = "first";
        break;
      case "2":
        $this->du_patient += CAppUI::gconf("dPfacturation CRelance add_second_relance");
        $this->statut = "second";
        break;
      case "3":
        $this->du_patient += CAppUI::gconf("dPfacturation CRelance add_third_relance");
        $this->statut = "third";
        break;
    }
  }

  /**
   * Redefinition du delete
   * 
   * @return void|string
  **/
  function delete() {
    //Supression possible que de la derniere relance d'une facture
    /** @var  CFactureCabinet|CFactureEtablissement $facture*/
    $facture = $this->loadRefFacture();
    $facture->loadRefsRelances();
    if (count($facture->_ref_relances) > 1 && $this->_id != $facture->_ref_last_relance->_id) {
      return "Vous ne pouvez supprimer que la derniere relance emise";
    }
    
    //Une relance reglee, ne peut pas etre supprimee
    if ($this->etat == "regle") {
      return "La relance est reglee, vous ne pouvez pas la supprimer"; 
    }
    
    // Standard store
    if ($msg = parent::delete()) {
      return $msg;
    }
    
    $facture->loadRefsRelances();
    $facture->_ref_last_relance->etat = "emise";
    $facture->_ref_last_relance->store();

    return null;
  }

  /**
   * @inheritdoc
   */
  function fillTemplate(&$template) {
    $this->fillLimitedTemplate($template);
  }

  /**
   * @see parent::fillLimitedTemplate()
   */
  function fillLimitedTemplate(&$template) {
    $this->updateFormFields();

    $this->notify(ObjectHandlerEvent::BEFORE_FILL_LIMITED_TEMPLATE(), $template);

    $this->loadRefFacture();
    $template->addProperty("Relance - Numéro"  , $this->numero);
    $template->addDateProperty("Relance - Date", $this->date);
    $template->addProperty("Relance - Etat"    , CAppUI::tr("CRelance.etat.".$this->etat));
    $template->addProperty("Relance - Montant" , $this->_montant);
    $template->addProperty("Relance - Statut"  , CAppUI::tr("CRelance.statut.".$this->statut));
    $template->addProperty("Relance - Facture" , $this->_ref_object->_view);
    $template->addProperty("Relance - Patient" , $this->_ref_object->loadRefPatient()->_view);

    $this->notify(ObjectHandlerEvent::AFTER_FILL_LIMITED_TEMPLATE(), $template);
  }

  /**
   * Compte les relances émises (non renouvellées ou réglées) pour un patient
   *
   * @param int $patient_id Patient
   *
   * @return CRelance[]
   */
  static function loadRelanceEmisePatient($patient_id) {
    $ljoin = array();
    $ljoin["facture_cabinet"] = "facture_cabinet.facture_id = facture_relance.object_id
                                 AND facture_relance.object_class = 'CFactureCabinet'";
    $ljoin2["facture_etablissement"] = "facture_etablissement.facture_id = facture_relance.object_id
                                        AND facture_relance.object_class = 'CFactureEtablissement'";
    $where = array();
    $where["facture_relance.etat"] = " = 'emise'";
    $where2 = $where;
    $where["facture_cabinet.patient_id"] = " = '$patient_id'";
    $where2["facture_etablissement.patient_id"] = " = '$patient_id'";
    $relance = new CRelance();
    $relances = $relance->loadList($where, null, null, "facture_relance.relance_id", $ljoin);
    $relances2 = $relance->loadList($where2, null, null, "facture_relance.relance_id", $ljoin2);
    return array_merge($relances, $relances2);
  }

  /**
   * @param CStoredObject $object
   * @deprecated
   * @todo redefine meta raf
   * @return void
   */
  public function setObject(CStoredObject $object) {
    CMbMetaObjectPolyfill::setObject($this, $object);
  }

  /**
   * @param bool $cache
   * @deprecated
   * @todo redefine meta raf
   * @return bool|CStoredObject|CExObject|null
   * @throws Exception
   */
  public function loadTargetObject($cache = true) {
    return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
  }

  /**
   * @inheritDoc
   * @todo remove
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadTargetObject();
  }
}
