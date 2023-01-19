<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Description
 */
class CWhiteList extends CMbObject {
  /** @var integer Primary key */
  public $whitelist_id;

  // DB fields
  public $email;
  public $group_id;
  public $actif;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = "whitelist";
    $spec->key = "whitelist_id";
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["email"]    = "str notNull";
    $props["group_id"] = "ref class|CGroups back|white_lists";
    $props["actif"]    = "bool default|1";
    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    $this->_view = $this->email;
  }

  /**
   * @inheritdoc
   */
  function updatePlainFields() {
    parent::updatePlainFields();

    if (!$this->_id) {
      $this->group_id = CGroups::get()->_id;
    }
  }
}