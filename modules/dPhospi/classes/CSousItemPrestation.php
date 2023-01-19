<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CMbObject;

/**
 * Description
 */
class CSousItemPrestation extends CMbObject {

  public const RESOURCE_TYPE = "sousItemPrestation";

  /**
   * @var integer Primary key
   */
  public $sous_item_prestation_id;

  // DB Fields
  public $nom;
  public $item_prestation_id;
  public $niveau;
  public $actif;
  public $price;

  // Pour AppFine
  /** @var bool */
  public $_selected;

  // References
  /** @var CItemPrestation */
  public $_ref_item_prestation;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "sous_item_prestation";
    $spec->key   = "sous_item_prestation_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                       = parent::getProps();
    $props["nom"]                = "str fieldset|default";
    $props["item_prestation_id"] = "ref class|CItemPrestation back|sous_items fieldset|default";
    $props["niveau"]             = "enum list|jour|nuit fieldset|default";
    $props["actif"]              = "bool default|1 fieldset|default";
    $props["price"]              = "float fieldset|default";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->nom;
  }

  /**
   * Chargement de l'item associé
   *
   * @return CItemPrestation
   */
  function loadRefItemPrestation() {
    return $this->_ref_item_prestation = $this->loadFwdRef("item_prestation_id", true);
  }
}
