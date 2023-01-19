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
 * Class CCirconstance
 */
class CCirconstance extends CMbObject {
  public $circonstance_id;

  // DB Fields
  public $code;
  public $libelle;
  public $commentaire;
  public $actif;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "circonstance";
    $spec->key   = "circonstance_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                = parent::getProps();
    $props["code"]        = "str notNull";
    $props["libelle"]     = "str notNull seekable";
    $props["commentaire"] = "text notNull seekable";
    $props["actif"]       = "bool";

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
