<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Ox\Core\CMbObject;

/**
 * Réponse aux informations à transmettre au patient par établissement
 */
class CInfoChecklistItem extends CMbObject {
  public $info_checklist_item_id;

  // DB fields
  public $info_checklist_id;
  public $consultation_id;
  public $consultation_class;
  public $reponse;

  // Object References
  /** @var  CConsultAnesth */
  public $_ref_consult_anesth;
  /** @var  CInfoChecklist */
  public $_ref_info_checklist;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'info_checklist_item';
    $spec->key   = 'info_checklist_item_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["info_checklist_id"]  = "ref class|CInfoChecklist notNull back|info_checklist_item";
    $props["consultation_id"]    = "ref class|CMbObject meta|consultation_class notNull cascade back|info_check_item";
    $props["consultation_class"] = "enum notNull list|CConsultAnesth|CConsultation default|CConsultAnesth";
    $props["reponse"]            = "bool show|0";
    return $props;
  }

  /**
   * @see parent::store()
   */
  function store() {
    $this->completeField("info_checklist_id", "consultation_id", "consultation_class");

    if (!$this->_id) {
      $this->_id = CInfoChecklistItem::itemInfoChecklistId(
        $this->info_checklist_id, $this->consultation_id, $this->consultation_class
      );
    }

    return parent::store();
  }

  /**
   * Charge la consultation associée
   *
   * @return CConsultation
   */
  function loadRefConsult() {
    return $this->_ref_consult = $this->loadFwdRef("consultation_id", true);
  }

  /**
   * Charge la checklist associée
   *
   * @return CInfoChecklist
   */
  function loadRefInfoChecklist() {
    $this->_ref_info_checklist = $this->loadFwdRef("info_checklist_id", true);
    $this->_view = $this->_ref_info_checklist->libelle;
    return $this->_ref_info_checklist;
  }

  /**
   * Identifiant d'item lié à l'objet fourni.
   *
   * @param integer $info_checklist_id  Identifiant de l'info de checklist
   * @param integer $consultation_id    Identifiant de la consultation
   * @param string  $consultation_class Consultation d'anesthésie ou normale
   *
   * @return integer Id de l'item
   */
  static function itemInfoChecklistId($info_checklist_id, $consultation_id, $consultation_class) {
    $item = new CInfoChecklistItem();
    $item->info_checklist_id  = $info_checklist_id;
    $item->consultation_id    = $consultation_id;
    $item->consultation_class = $consultation_class;
    $item->loadMatchingObject();
    return $item->_id;
  }
}