<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sante400;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;

class CTriggerMark extends CMbObject {
  public $mark_id;

  // DB Fields
  public $trigger_class;
  public $trigger_number;
  public $when;
  public $mark;
  public $done;

  // Filter fields
  public $_date_min;
  public $_date_max;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->table    = "trigger_mark";
    $spec->key      = "mark_id";
    $spec->loggable = false;

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                   = parent::getProps();
    $props["trigger_class"]  = "str notNull";
    $props["trigger_number"] = "numchar notNull maxLength|10";
    $props["when"]           = "dateTime";
    $props["done"]           = "bool notNull";
    $props["mark"]           = "str notNull";

    $props["_date_min"] = "dateTime";
    $props["_date_max"] = "dateTime moreThan|_date_min";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = "Mark for " . CAppUI::tr($this->trigger_class) . " #$this->trigger_number";
  }
}
