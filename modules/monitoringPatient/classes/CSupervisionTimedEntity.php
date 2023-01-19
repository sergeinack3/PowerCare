<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\MonitoringPatient;

use Exception;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * A supervision graph
 */
class CSupervisionTimedEntity extends CMbObject implements ISupervisionTimelineItem {
  public $owner_class;
  public $owner_id;

  public $title;
  public $disabled;

  /** @var CMbObject */
  public $_ref_owner;

  /** @var array */
  public $_graph_data = array();

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec                   = parent::getSpec();
    $spec->uniques["title"] = array("owner_class", "owner_id", "title");

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                = parent::getProps();
    $props["owner_class"] = "enum notNull list|CGroups";
    $props["owner_id"]    = "ref notNull meta|owner_class class|CMbObject";
    $props["title"]       = "str notNull";
    $props["disabled"]    = "bool notNull default|1";

    return $props;
  }

  /**
   * Load the owner entity
   *
   * @param bool $cache Use object cache
   *
   * @return CGroups
   * @throws Exception
   */
  function loadRefOwner($cache = true) {
    return $this->_ref_owner = $this->loadFwdRef("owner_id", $cache);
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->title;
  }

  /**
   * Get identifier for the timeline
   *
   * @return string
   */
  function getIdentifier() {
    return $this->_guid;
  }

  /**
   * Get data for the timeline
   *
   * @return array
   */
  function getData() {
    return $this->_graph_data;
  }

  /**
   * @inheritdoc
   */
  function getJsonFields() {
    $fields   = parent::getJsonFields();
    $fields[] = "_graph_data";

    return $fields;
  }
}
