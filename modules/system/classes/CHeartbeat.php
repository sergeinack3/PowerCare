<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;

/**
 * Class CSourcePOP
 */
class CHeartbeat extends CStoredObject {
  /* NO KEY */

  // Plain Fields
  public $server_id;
  public $ts;
  public $file;
  public $position;
  public $relay_master_log_file;
  public $exec_master_log_pos;
  public $hostname;

  // Form fields
  public $_datetime;
  public $_lag;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->dsn   = "cluster";
    $spec->table = "heartbeat";
    $spec->key   = "server_id";
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    // Plain fields
    $props["server_id"]             = "num notNull";
    $props["ts"]                    = "str notNull length|26";
    $props["file"]                  = "str";
    $props["position"]              = "num maxLength|20";
    $props["relay_master_log_file"] = "str";
    $props["exec_master_log_pos"]   = "num maxLength|20";
    $props["hostname"]              = "str";

    // Form fields
    $props["_datetime"]             = "dateTime";
    $props["_lag"]                  = "num";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function initialize() {
    parent::initialize();

    // Add hostname SQL column if necessary
    // Don't use cached self::hasFieldInstalled()
    static $checked = false;
    if (!$checked) {
      $ds = $this->_spec->ds;
      $table = $this->_spec->table;
      if ($ds && !$ds->loadField($table, "hostname")) {
        $db_spec = $this->_specs["hostname"]->getDBSpec();
        $query = "ALTER TABLE `$table` ADD `hostname` $db_spec";
        $ds->exec($query);
      }
      $checked = true;
    }
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_datetime = CMbDT::dateTimeFromXMLDateTime($this->ts);
    $this->_lag = strtotime(CMbDT::datetime()) - strtotime($this->_datetime);

  }
}