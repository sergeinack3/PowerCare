<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Labo;

use Ox\Core\CMbObject;

/**
 * Class CPackItemExamenLabo
 *
 * Liaison entre un examen et son pack
 */
class CPackItemExamenLabo extends CMbObject {
  // DB Table key
  public $pack_item_examen_labo_id;

  // DB references
  public $pack_examens_labo_id;
  public $examen_labo_id;

  // Forward references
  public $_ref_pack_examens_labo;
  public $_ref_examen_labo;

  /**
   * CPackItemExamenLabo constructor.
   */
  function __construct() {
    parent::__construct();
    $this->_locked =& $this->_external;
  }

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'pack_item_examen_labo';
    $spec->key   = 'pack_item_examen_labo_id';
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function check() {
    if ($msg = parent::check()) {
      return $msg;
    }

    // Check unique item
    $other = new CPackItemExamenLabo;
    $other->pack_examens_labo_id = $this->pack_examens_labo_id;
    $other->examen_labo_id = $this->examen_labo_id;
    $other->loadMatchingObject();
    if ($other->_id && $other->_id != $this->_id) {
      return "$this->_class-unique-conflict";
    }

    return null;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["pack_examens_labo_id"] = "ref class|CPackExamensLabo notNull back|items_examen_labo";
    $props["examen_labo_id"]       = "ref class|CExamenLabo notNull back|items_pack_labo";
    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->loadRefsFwd();
    $this->_shortview = $this->_ref_examen_labo->_shortview;
    $this->_view      = $this->_ref_examen_labo->_view;
  }

  /**
   * Chargement du pack relié
   *
   * @return void
   */
  function loadRefPack() {
    $this->_ref_pack_examens_labo = new CPackExamensLabo();
    $this->_ref_pack_examens_labo->load($this->pack_examens_labo_id);
  }

  /**
   * Chargement de l'examen relié
   *
   * @return void
   */
  function loadRefExamen() {
    $this->_ref_examen_labo = new CExamenLabo();
    $this->_ref_examen_labo->load($this->examen_labo_id);
  }

  /**
   * @inheritdoc
   */
  function loadRefsFwd() {
    $this->loadRefPack();
    $this->loadRefExamen();
  }
}
