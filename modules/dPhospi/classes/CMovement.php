<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CMovement
 */
class CMovement extends CMbObject {
  // DB Table key
  public $movement_id;

  // DB fields
  public $sejour_id;
  public $affectation_id;
  public $movement_type;
  public $original_trigger_code;
  public $start_of_movement;
  public $last_update;
  public $cancel;

  public $_current = true;

  /** @var CSejour */
  public $_ref_sejour;

  /** @var CAffectation */
  public $_ref_affectation;

  // Filter fields
  public $_date_min;
  public $_date_max;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->table    = 'movement';
    $spec->key      = 'movement_id';
    $spec->loggable = false;

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                          = parent::getProps();
    $props["sejour_id"]             = "ref notNull class|CSejour seekable back|movements";
    $props["affectation_id"]        = "ref class|CAffectation seekable nullify back|movements";
    $props["movement_type"]         = "enum notNull list|PADM|ADMI|MUTA|SATT|SORT|AABS|RABS|EATT|TATT";
    $props["original_trigger_code"] = "str length|3";
    $props["start_of_movement"]     = "dateTime";
    $props["last_update"]           = "dateTime notNull";
    $props["cancel"]                = "bool default|0";

    $props["_date_min"] = "dateTime";
    $props["_date_max"] = "dateTime";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = "$this->movement_type-$this->_id";
  }

  /**
   * Load sejour
   *
   * @return CMbObject|null
   */
  function loadRefSejour() {
    return $this->_ref_sejour = $this->loadFwdRef("sejour_id", 1);
  }

  /**
   * Load affectation
   *
   * @return CMbObject|null
   */
  function loadRefAffectation() {
    return $this->_ref_affectation = $this->loadFwdRef("affectation_id", 1);
  }

  /**
   * @inheritdoc
   */
  function loadMatchingObject($order = null, $group = null, $ljoin = null, $index = null, bool $strict = true) {
    $order = "last_update DESC";

    return parent::loadMatchingObject($order, $group, $ljoin, null, $strict);
  }

  /**
   * @inheritdoc
   */
  function loadMatchingList($order = null, $limit = null, $group = null, $ljoin = null, $index = null, bool $strict = true) {
    $order = "movement_id DESC, start_of_movement DESC";

    return parent::loadMatchingList($order, $limit, $group, $ljoin, null, $strict);
  }

  /**
   * @inheritdoc
   */
  function store() {
    if (!$this->_id) {
      $this->last_update = "now";
    }

    // On modifie la date uniquement si le mouvement a été modifié
    if ($this->objectModified()) {
      $this->last_update = "now";
    }

    return parent::store();
  }

  /**
   * Get movement
   *
   * @param CMbObject $object Object
   *
   * @return void
   */
  function getMovement(CMbObject $object) {
    if ($object instanceof CSejour) {
      $this->sejour_id = $object->_id;
    }
    if ($object instanceof CAffectation) {
      $sejour               = $object->loadRefSejour();
      $this->sejour_id      = $sejour->_id;
      $this->affectation_id = $object->_id;
    }

    $this->movement_type = $object->getMovementType();
    $this->loadMatchingObject();
  }

  /**
   * Construit le tag d'un mouvement en fonction des variables de configuration
   *
   * @param string $group_id Permet de charger l'id externe d'un mouvement pour un établissement donné si non null
   *
   * @return string
   */
  static function getTagMovement($group_id = null) {
    // Pas de tag mouvement
    if (null == $tag_movement = CAppUI::gconf("dPhospi mouvements tag")) {
      return null;
    }

    // Permettre des id externes en fonction de l'établissement
    $group = CGroups::loadCurrent();
    if (!$group_id) {
      $group_id = $group->_id;
    }

    return str_replace('$g', $group_id, $tag_movement);
  }

  /**
   * @inheritdoc
   */
  function getDynamicTag() {
    return CAppUI::gconf("dPhospi mouvements tag");
  }
}