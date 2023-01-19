<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Labo;

use Ox\Core\CMbFieldSpecFact;
use Ox\Core\CMbObject;

/**
 * Class CPrescriptionLaboExamen
 */
class CPrescriptionLaboExamen extends CMbObject {
  // DB Table key
  public $prescription_labo_examen_id;

  // DB references
  public $prescription_labo_id;
  public $examen_labo_id;
  public $pack_examens_labo_id;
  public $resultat;
  public $date;
  public $commentaire;

  // Forward references
  /** @var  CPrescriptionLabo */
  public $_ref_prescription_labo;
  /** @var  CExamenLabo */
  public $_ref_examen_labo;
  /** @var  CPackExamensLabo */
  public $_ref_pack;

  // Distant fields
  public $_hors_limite;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'prescription_labo_examen';
    $spec->key = 'prescription_labo_examen_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["prescription_labo_id"] = "ref class|CPrescriptionLabo notNull back|prescription_labo_examen";
    $props["examen_labo_id"] = "ref class|CExamenLabo notNull back|prescriptions";
    $props["pack_examens_labo_id"] = "ref class|CPackExamensLabo back|prescriptions_labo_examen";
    $props["resultat"] = "str";
    $props["date"] = "date";
    $props["commentaire"] = "text helped";
      
    return $props;
  }

  /**
   * @inheritdoc
   */
  function check() {
    if ($msg = parent::check()) {
      return $msg;
    }

    // Check unique item
    $other = new CPrescriptionLaboExamen;
    $clone = null;
    if ($this->_id) {
      $clone = new CPrescriptionLaboExamen;
      $clone->load($this->_id);
    }
    else {
      $clone = $this;
    }
    $other->prescription_labo_id = $clone->prescription_labo_id;
    $other->examen_labo_id = $clone->examen_labo_id;
    $other->loadMatchingObject();
    if ($other->_id && $other->_id != $this->_id) {
      return "$this->_class - unique - conflict";
    }

    // Check prescription status
    $clone->loadRefPrescription();
    $clone->_ref_prescription_labo->loadRefsBack();
    if ($clone->_ref_prescription_labo->_status >= CPrescriptionLabo::VALIDEE) {
      return "Prescription déjà validée";
    }
    // Get the analysis to check resultat
    if (!$this->examen_labo_id) {
      if (!$clone) {
        $clone = new CPrescriptionLaboExamen;
        $clone->load($this->_id);
      }
      $this->examen_labo_id = $clone->examen_labo_id;
    }

    // Check resultat according to type
    $this->loadRefExamen();
    $resultTest = CMbFieldSpecFact::getSpec($this, "resultat", $this->_ref_examen_labo->type);

    return $resultTest->checkPropertyValue($this);
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->loadRefExamen();
    $examen =& $this->_ref_examen_labo;
    $borne_inf = $examen->min && $examen->min > $this->resultat;
    $borne_sup = $examen->max && $examen->max < $this->resultat;
    $this->_hors_limite = $this->resultat &&
      ($examen->type === "num" || $examen->type === "float") &&
      ($borne_inf || $borne_sup);
    $this->_shortview = $examen->_shortview;
    $this->_view = $examen->_view;
  }

  /**
   * Chargement de la prescription
   *
   * @return CPrescriptionLabo
   */
  function loadRefPrescription() {
    return $this->_ref_prescription_labo = $this->loadFwdRef("prescription_labo_id");
  }

  /**
   * Chargement de l'examen
   *
   * @return CExamenLabo
   */
  function loadRefExamen() {
    return $this->_ref_examen_labo = $this->loadFwdRef("examen_labo_id");
  }

  /**
   * Chargement du pack d'examen
   *
   * @return CPackExamensLabo
   */
  function loadRefPack() {
    return $this->_ref_pack = $this->loadFwdRef("pack_examens_labo_id");
  }

  /**
   * @inheritdoc
   */
  function loadRefsFwd() {
    $this->loadRefPrescription();
    $this->loadRefExamen();
    $this->loadRefPack();
  }

  /**
   * @param int $limit Nombre max de résultats
   *
   * @return CPrescriptionLaboExamen[]
   */
  function loadSiblings($limit = 10) {
    return $this->loadResults($this->_ref_prescription_labo->patient_id, $this->examen_labo_id, $limit);
  }

  /**
   * Load results items with given patient and exam
   *
   * @param int $patient_id     Patient concerné
   * @param int $examen_labo_id Examen concerné
   * @param int $limit          Nombre max de résultats
   *
   * @return array
   */
  function loadResults($patient_id, $examen_labo_id, $limit = 10) {
    $examen = new CExamenLabo;
    $examen->load($examen_labo_id);

    $order = "date DESC";
    $prescription = new CPrescriptionLabo;
    $prescription->patient_id = $patient_id;
    $prescriptions = $prescription->loadMatchingList($order);

    // Load items for each prescription to preserve prescription date ordering
    $items = array();
    $item = new CPrescriptionLaboExamen;
    foreach ($prescriptions as $_prescription) {
      $item->prescription_labo_id = $_prescription->_id;
      $item->examen_labo_id = $examen_labo_id;
      foreach ($item->loadMatchingList($order) as $_item) {
        $items[$_item->_id] = $_item;
      }
    }

    foreach ($items as &$item) {
      $item->_ref_prescription_labo =& $prescriptions[$item->prescription_labo_id];
      $item->_ref_examen_labo =& $examen;
    }

    return $items;
  }
}
