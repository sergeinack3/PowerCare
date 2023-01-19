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
 * Description
 */
class CRessourceCab extends CMbObject {
  /** @var integer Primary key */
  public $ressource_cab_id;

  // DB fields
  public $function_id;
  public $owner_id;
  public $libelle;
  public $description;
  public $color;
  public $actif;
  public $in_charge;

  // Form fields
  public $_ref_plages;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = "ressource_cab";
    $spec->key   = "ressource_cab_id";
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["function_id"] = "ref class|CFunctions notNull back|ressources";
    $props["owner_id"]    = "ref class|CMediusers notNull back|ressources";
    $props["libelle"]     = "str";
    $props["description"] = "text";
    $props["color"]       = "color";
    $props["actif"]       = "bool default|1";
    $props["in_charge"]   = "str";
    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->libelle;
  }

  /**
   * Charge les plages associées à la ressource
   *
   * @return CPlageRessourceCab[]
   */
  function loadRefsPlages() {
    return $this->_ref_plages = $this->loadBackRefs("plages_cab", "date");
  }
}
