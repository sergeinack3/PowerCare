<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences;

use Ox\Core\CMbObject;

/**
 * Motif SFMU
 */
class CMotifSFMU extends CMbObject {
  /**
   * @var integer Primary key
   */
  public $motif_sfmu_id;
  public $code;
  public $libelle;
  public $categorie;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "motif_sfmu";
    $spec->key   = "motif_sfmu_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props["code"]      = "str";
    $props["libelle"]   = "str";
    $props["categorie"] = "str";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->libelle;
  }
}
