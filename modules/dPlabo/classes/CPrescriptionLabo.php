<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Labo;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Interop\Webservices\CSourceSOAP;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CExchangeSource;

/**
 * Class CPrescriptionLabo
 */
class CPrescriptionLabo extends CMbObject {

  // Status const
  const VIERGE       = 16;
  const PRELEVEMENTS = 32;
  const VEROUILLEE   = 48;
  const TRANSMISE    = 64;
  const SAISIE       = 80;
  const VALIDEE      = 96;
  const FERMEE       = 112;

  // DB Table key
  public $prescription_labo_id;

  // DB Fields
  public $date;
  public $verouillee;
  public $validee;
  public $urgence;

  // DB references
  public $patient_id;
  public $praticien_id;

  // Form Fields
  public $_status;

  // Forward references
  public $_ref_patient;
  /** @var  CMediusers */
  public $_ref_praticien;

  // Back references
  /** @var CPrescriptionLaboExamen[] */
  public $_ref_prescription_items;

  // Distant references
  /** @var CExamenLabo[] */
  public $_ref_examens;
  /** @var CExamenLabo[] */
  public $_ref_internal_items;
  /** @var CExamenLabo[] */
  public $_ref_external_items;
  public $_ref_classification_roots;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'prescription_labo';
    $spec->key   = 'prescription_labo_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specsParent = parent::getProps();
    $specs = array (
      "patient_id"   => "ref class|CPatient notNull seekable back|prescriptions_labo",
      "praticien_id" => "ref class|CMediusers notNull back|prescriptions_labo",
      "date"         => "dateTime",
      "verouillee"   => "bool",
      "validee"      => "bool",
      "urgence"      => "bool"
    );
    return array_merge($specsParent, $specs);
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_shortview = $this->date;
    $this->_view      = "Prescription du ".CMbDT::format($this->date, CAppUI::conf("datetime"));
  }

  /**
   * @return string
   */
  function loadIdPresc() {
    $tagCatalogue = CAppUI::gconf("dPlabo CCatalogueLabo remote_name");

    $this->loadRefsFwd();
    $prat =& $this->_ref_praticien;

    $tagCode4 = "labo code4";
    $idSantePratCode4 = new CIdSante400();
    $idSantePratCode4->loadLatestFor($prat, $tagCode4);

    $idPresc = new CIdSante400();
    $idPresc->tag = "$tagCatalogue Prat:".str_pad($idSantePratCode4->id400, 4, '0', STR_PAD_LEFT); // tag LABO Prat: 0017
    $idPresc->object_class = "CPrescriptionLabo";
    $idPresc->loadMatchingObject("id400 DESC");

    return $idPresc->id400;
  }

  /**
   * @return CIdSante400
   */
  function getIdExterne() {
    $idExterne = new CIdSante400();
    // Chargement de l'id externe de la prescription (tag: Imeds)
    $idExterne->loadLatestFor($this, "iMeds");
    if (!$idExterne->_id) {
      // Afactoriser : assez complexe (concatenation du code 4 praticien et du code 4 prescription)
      $tagCatalogue = CAppUI::gconf("dPlabo CCatalogueLabo remote_name");
      $this->loadRefsFwd();
      $prat =& $this->_ref_praticien;

      $tagCode4 = "labo code4";
      $idSantePratCode4 = new CIdSante400();
      $idSantePratCode4->loadLatestFor($prat, $tagCode4);

      $idPresc = new CIdSante400();
      $idPresc->tag = "$tagCatalogue Prat:".str_pad($idSantePratCode4->id400, 4, '0', STR_PAD_LEFT); // tag LABO Prat: 0017
      $idPresc->object_class = "CPrescriptionLabo";
      $idPresc->loadMatchingObject("id400 DESC");
      $numprovisoire = str_pad($idSantePratCode4->id400, 4, '0', STR_PAD_LEFT).str_pad($idPresc->id400, 4, '0', STR_PAD_LEFT);

      // Envoi à la source créée 'get_id_prescriptionlabo' (SOAP)
        /** @var CSourceSOAP $exchange_source */
      $exchange_source = CExchangeSource::get("get_id_prescriptionlabo", CSourceSOAP::TYPE);
      $exchange_source->setData(array("NumMedi" => $numprovisoire, "pwd" =>$exchange_source->password));
      $exchange_source->getClient()->send("NDOSLAB");

      $acq = $exchange_source->getACQ();
      $idExterne->tag = "iMeds";
      $idExterne->object_class = "CPrescriptionLabo";
      $idExterne->object_id = $this->_id;
      $idExterne->id400 = is_object($acq) ? $acq->NDOSLABResult : $acq['NDOSLABResult'];
      $idExterne->store();
    }
    return $idExterne;
  }
  
  function loadRefPatient() {
    return $this->_ref_patient = $this->loadFwdRef("patient_id");
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    $this->loadRefPatient();

    if (!$this->_ref_praticien) {
      $this->_ref_praticien = new CMediusers();
      $this->_ref_praticien->load($this->praticien_id);
    }
  }

  /**
   * @see parent::loadRefsBack()
   */
  function loadRefsBack() {
    if (!$this->_ref_prescription_items) {
      // Chargement des items
      $item = new CPrescriptionLaboExamen();
      $item->prescription_labo_id = $this->_id;
      $this->_ref_prescription_items = $item->loadMatchingList();
      $this->_ref_examens = array();

      // Classement des examens
      foreach ($this->_ref_prescription_items as $_item) {
        $_item->_ref_prescription_labo = $this;
        $_item->loadRefsFwd();
        $examen = $_item->_ref_examen_labo;
        $this->_ref_examens[$examen->_id] = $examen;
      }

      // Classement des items internes et externes
      $this->_ref_external_items = array();          
      $this->_ref_internal_items = array();          
      foreach ($this->_ref_prescription_items as $_item) {
        $examen = $_item->_ref_examen_labo;
        $examen->loadExternal();

        // Remplissage des collections
        if ($examen->_external) {
          $this->_ref_external_items[$_item->_id] =& $_item;          
        }
        else {
          $this->_ref_internal_items[$_item->_id] =& $_item;          
        }
      }      
    }
    $this->checkStatus();
  }

  /**
   * @return int
   */
  function checkStatus() {
    $numFiles = $this->countFiles();

    // Vérification de l'état validée
    if ($this->validee) {
      $this->_status = self::VALIDEE;
      return $this->_status;
    }

    // Vérification de l'etat saisie
    $saisie = count($this->_ref_prescription_items);
    foreach ($this->_ref_internal_items as $_item) {
      if (null === $_item->resultat) {
        $saisie = false;
        break;
      }
    }

    if ($saisie && ($numFiles || !count($this->_ref_external_items))) {
      $this->_status = self::SAISIE;
      return $this->_status;
    }

    // Vérification de l'état transmise
    if ($numFiles) {
      $this->_status = self::TRANSMISE;
      return $this->_status;
    }

    // Vérification de l'état vérouillée
    if ($this->verouillee) {
      $this->_status = count($this->_ref_external_items) ? 
        self::VEROUILLEE : 
        self::TRANSMISE;
      return $this->_status;
    }

    // Vérification de l'état prélèvements
    if ($this->countBackRefs("prescription_labo_examen")) {
      $this->_status = self::PRELEVEMENTS;
      return $this->_status;
    }

    // Sinon vierge
    $this->_status = self::VIERGE;
    return $this->_status;
  }

  /**
   * Load minimal catalogue classification to cover the prescription analyses
   *
   * @return int
   */
  function loadClassification() {
    /** @var CCatalogueLabo[] $catalogues */
    $catalogues = array();

    // Load needed catalogues
    foreach ($this->_ref_examens as $examen) {
      $catalogue_id = $examen->catalogue_labo_id;
      if (!array_key_exists($catalogue_id, $catalogues)) {
        $catalogue = new CCatalogueLabo();
        $catalogue->load($catalogue_id);
        $catalogue->_ref_catalogues_labo = array();
        $catalogues[$catalogue->_id] = $catalogue;
      }
    }

    // Complete catalogue hierarchy
    foreach ($catalogues as $_catalogue) {
      $child_catalogue = $_catalogue;
      while ($child_catalogue->pere_id && !array_key_exists($child_catalogue->pere_id, $catalogues)) {
        $catalogue = new CCatalogueLabo;
        $catalogue->load($child_catalogue->pere_id);
        $catalogues[$catalogue->_id] = $catalogue;
        $child_catalogue = $catalogue;
      }
    }

    // Prepare catalogues collections
    foreach ($catalogues as &$ref_catalogue) {
      $ref_catalogue->_ref_catalogues_labo = array();
      $ref_catalogue->_ref_prescription_items = array();
    }

    // Feed prescription items
    foreach ($this->_ref_prescription_items as $_item) {
      $catalogue_id = $_item->_ref_examen_labo->catalogue_labo_id;
      $catalogues[$catalogue_id]->_ref_prescription_items[$_item->_id] = $_item;
    }

    // Link catalogue hierarchy
    foreach ($catalogues as &$link_catalogue) {
      if ($parent_id = $link_catalogue->pere_id) {
        $parent_catalogue =& $catalogues[$parent_id];
        $parent_catalogue->_ref_catalogues_labo[$link_catalogue->_id] =& $link_catalogue;
        $link_catalogue->_ref_pere =& $parent_catalogue;
      } 
    }

    // Find classifications roots
    foreach ($catalogues as &$root_catalogue) {
      if ($root_catalogue->computeLevel() == 0) {
        $this->_ref_classification_roots[$root_catalogue->_id] =& $root_catalogue;
      }
    }
  } 

  /**
   * @see parent::getPerm()
   */
  function getPerm($permType) {
    $this->loadRefsFwd();
    return $this->_ref_praticien->getPerm($permType);
  }
}
